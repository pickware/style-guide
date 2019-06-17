import execa from 'execa';
import fs from 'fs';
import os from 'os';
import path from 'path';
import rimraf from 'rimraf';
import 'source-map-support/register';
import { promisify } from 'util';

/**
 * Commonly used program exit codes.
 *
 * See <file:///usr/include/sysexits.h>.
 */
const enum SysExitCode {
    /** Exit code indicating success.  */
    EX_OK = 0,
    /** Program error code for bad usage by caller. */
    EX_USAGE = 64,
    /** Program error code for an internal error. */
    EX_SOFTWARE = 70,
}

// Assume an error occurred until we know better.
process.exitCode = SysExitCode.EX_SOFTWARE;

/** Returns a name to refer to this script in user-facing messages. */
const getScriptName = (process: NodeJS.Process) => process.argv[1] || __filename;

const usage = (process: NodeJS.Process) =>
    `USAGE: ${process.argv.slice(0, 2).join(' ')} < --pre-commit | --pre-push > -- <child program ...>`;

const doesWorktreeDifferFromIndex = async (rootDirectoryName: string) => {
    // If git ls-files outputs anything, assume something as changed in the worktree that is not in the index.
    const worktreeFiles = await execa(
        'git',
        ['ls-files', '--modified', '--deleted', '--unmerged', '--others', '--exclude-standard', '--full-name', '-z'],
        { cwd: rootDirectoryName, stderr: 'inherit' },
    );

    // If the output is empty, nothing has changed in the worktree.
    // Note: This ignores effects of .gitignore'd files.
    return worktreeFiles.stdout.length !== 0;
};

/**
 * Runs the given command line after running `yarn --frozen-lock-file`.
 *
 * @param process the NodeJS process data structure
 * @param console a console to which to report errors
 * @param childArgv the child program command line that shall be run
 * @param options options to supply to execa calls (will be augmented)
 * @returns exit code (0 on success)
 */
const runChildProgram = async (
    process: NodeJS.Process,
    console: Console,
    childArgv: readonly string[],
    options?: execa.Options
) => {
    const baseExecOptions = {
        ...options,
        env: {
            // Do not (re-)install Husky hooks inside the checkout:
            HUSKY_SKIP_INSTALL: '1',
            ...((options && options.env) || {}),
        },
    }

    await execa('yarn', ['--frozen-lock-file'], baseExecOptions);
    const childBinaryName: string | undefined = childArgv[0];
    if (!childBinaryName) {
        throw new Error('Not given any arguments to run child program');
    }
    const childArguments = childArgv.slice(1);
    const result = await execa(childBinaryName, childArguments, {
        ...baseExecOptions,
        reject: false,
    });

    const exitCode = Number.isInteger(result.code) ? result.code : SysExitCode.EX_SOFTWARE;
    if (result.killed || exitCode !== SysExitCode.EX_OK) {
        console.error(
            '%s',
            [
                `Failed running specified child program [${exitCode}]: ${childArgv.join(' ')}`,
                `    Ran in working directory: ${(options && options.cwd) || process.cwd()}`
            ].map((line) => `ERROR: ${getScriptName(process)}: ${line}`).join('\n')
        );
    }

    return exitCode;
};

/** Returns arguments for cp to make it prefer using reflinks (to speed up copying), if supported. */
const getCpReflinkArgument = async () => {
    const tempDir = await fs.promises.mkdtemp(`${os.tmpdir()}${path.sep}`);
    try {
        const inTestFile = path.join(tempDir, 'test-copy-with-reflink.in');
        const outTestFile = path.join(tempDir, 'test-copy-with-reflink.out');
        await fs.promises.writeFile(inTestFile, '');
        const reflinkArgument = '--reflink=auto';

        await execa('cp', ['-aR', reflinkArgument, '--', inTestFile, outTestFile]);

        return [reflinkArgument];
    } catch (err) {
        // Assume that if there was an error, the --reflink argument is not supported.
        return [];
    } finally {
        await promisify(rimraf)(tempDir);
    }
};

const copyFilesRecursivelyToDirectory = async (sourceFiles: readonly string[], targetDirectory: string) =>
    execa('cp', ['-aR', ...(await getCpReflinkArgument()), '--', ...sourceFiles, `${targetDirectory}${path.sep}`], {
        stdio: 'inherit',
    });

/** Returns the project's git dir as an absolute file name (e.g.: `/a/project/.git`). */
const getProjectGitDir = async (rootDirectoryName: string) => {
    const { stdout: gitDir } = await execa('git', ['rev-parse', '--absolute-git-dir'], {
        cwd: rootDirectoryName,
        stderr: 'inherit',
    });

    return gitDir;
};

const copyNodeModules = async (sourceDirectoryName: string, targetDirectoryName: string) => {
    const exists = promisify(fs.exists);

    // Copy node_modules directory from work tree to temporary checkout to speed up build.
    const projectNodeModules = path.join(sourceDirectoryName, 'node_modules');
    if (await exists(projectNodeModules)) {
        await copyFilesRecursivelyToDirectory([projectNodeModules], targetDirectoryName);
    }

    // For yarn workspaces, also copy all workspaces' node_modules directories:
    const packageJsonFileName = path.join(sourceDirectoryName, 'package.json');
    if (await exists(packageJsonFileName)) {
        const rootPackage = JSON.parse((await fs.promises.readFile(packageJsonFileName)).toString());
        const workspaces = rootPackage.workspaces;
        if (Array.isArray(workspaces)) {
            const nodeModulesCopies = workspaces
                // Only handle workspaces defined as strings and do not consider absolute directory names:
                .filter((workspaceDirectory) => (
                    typeof workspaceDirectory === 'string'
                    && !path.isAbsolute(workspaceDirectory)
                ))
                // Copy each workspace's node_modules directory:
                .map(async (workspaceDirectory) => {
                    const resolvedSourceWorkspaceDirectory = path.resolve(sourceDirectoryName, workspaceDirectory);
                    const from = path.join(resolvedSourceWorkspaceDirectory, 'node_modules');
                    if (!(await exists(from))) {
                        return null;
                    }
                    const resolvedTargetWorkspaceDirectory = path.resolve(targetDirectoryName, workspaceDirectory);

                    return copyFilesRecursivelyToDirectory([from], resolvedTargetWorkspaceDirectory)
                });
            await Promise.all(nodeModulesCopies);
        }
    }
};

/**
 * Runs the given child program command line in a temporary directory, after using the given `checkoutToDirectory`
 * callback to setup the desired project state.
 *
 * This also performs further optimizing setup steps, such as copying the current project root's node_modules directory
 * and rebuilding the project by calling `yarn --frozen-lock-file` thereafter.
 *
 * @param process the NodeJS process data structure
 * @param console a console to which to report errors
 * @param rootDirectoryName the root directory of the project
 * @param checkoutToDirectory a function that will copy the desired project state to a given directory
 * @param childArgv the child program command line that shall be run
 *
 * @returns the exit code of the child program command line (0 on success)
 */
const runChildProgramInTemporaryCheckout = async (
    process: NodeJS.Process,
    console: Console,
    rootDirectoryName: string,
    checkoutToDirectory: (targetDirectoryName: string) => Promise<void>,
    childArgv: readonly string[],
) => {
    const tempDir = await fs.promises.mkdtemp(`${os.tmpdir()}${path.sep}`);
    try {
        await checkoutToDirectory(tempDir);
        await copyNodeModules(rootDirectoryName, tempDir);

        return await runChildProgram(process, console, childArgv, { cwd: tempDir, stdio: 'inherit' });
    } finally {
        if (process.env.GIT_HOOK_ENV_KEEP_TMP !== '1') {
            await promisify(rimraf)(tempDir);
        }
    }
};

/**
 * Runs the given child program command line (after running `yarn --frozen-lock-file`) in the context of the
 * to-be-committed project state.
 *
 * @param process the NodeJS process data structure
 * @param console a Console implementation for user-facing output
 * @param rootDirectoryName the project directory
 * @param childArgv the child program command line that shall be run
 * @returns exit code (0 on success)
 */
const runPreCommit = async (
    process: NodeJS.Process,
    console: Console,
    rootDirectoryName: string,
    childArgv: readonly string[],
) => {
    if (!(await doesWorktreeDifferFromIndex(rootDirectoryName))) {
        // Nothing has changed in the worktree. It is equal to the current HEAD and the current index
        // (besides .gitignore'd files possibly being present).
        // Hence, we can run the given program directly.
        console.error(`${getScriptName(process)}: Running checks inside worktree …`);

        return runChildProgram(process, console, childArgv, { cwd: rootDirectoryName, stdio: 'inherit' });
    }

    return runChildProgramInTemporaryCheckout(
        process,
        console,
        rootDirectoryName,
        async (targetDirectoryName: string) => {
            console.error(
                `${getScriptName(process)}: Running checks against index inside temporary checkout (${targetDirectoryName}) …`,
            );

            // For a pre-commit hook, checkout the index that would be committed:
            const checkoutPrefix = `${targetDirectoryName}${path.sep}`;
            await execa('git', ['checkout-index', '--all', `--prefix=${checkoutPrefix}`], {
                cwd: rootDirectoryName,
                stdio: 'inherit',
            });

            const projectGitDir = await getProjectGitDir(rootDirectoryName);
            const targetGitDir = path.join(targetDirectoryName, '.git');

            // Copy the original git directory to the target, so that git-reliant tooling (e.g., Danger JS) works:
            await copyFilesRecursivelyToDirectory([projectGitDir], targetDirectoryName);
            // Prevent git hooks from being executed in temporary checkout by deleting them:
            await promisify(rimraf)(path.join(targetGitDir, 'hooks'));
        },
        childArgv,
    );
};

/**
 * For each pushed reference, runs the given child program command line (after running `yarn --frozen-lock-file`) in the
 * context of the to-be-pushed project state.
 *
 * As any `git push` invocation supports multiple pushed references, git will provide a list of the pushed references
 * via stdin.
 * Husky supplies them in the `HUSKY_GIT_STDIN` environment variable, which is expected to be defined.
 * The child program is executed for each of the specified to-be-pushed references.
 *
 * Fails early. I.e., it will return a non-zero exit code for the first invocation of child program that fails.
 *
 * @param process the NodeJS process data structure
 * @param console a Console implementation for user-facing output
 * @param rootDirectoryName the project directory
 * @param childArgv the child program command line that shall be run
 * @returns exit code (0 on success)
 */
const runPrePush = async (
    process: NodeJS.Process,
    console: Console,
    rootDirectoryName: string,
    childArgv: readonly string[],
) => {
    if (typeof process.env.HUSKY_GIT_STDIN !== 'string') {
        // Note: HUSKY_GIT_STDIN need only be defined. It may be empty if there is nothing to push.
        throw new Error('Hook type is pre-push but environment variable HUSKY_GIT_STDIN is not defined');
    }

    /** Each item represents a line describing a pushed reference. */
    const pushSpecLines = process.env.HUSKY_GIT_STDIN.split('\n').filter((line) => line !== '');

    for (const pushSpecLine of pushSpecLines) {
        /** See: githooks(5) */
        const pushSpecPattern = /^(?<localRef>\S+) (?<localSha>\S+) (?<remoteRef>\S+) (?<remoteSha>\S+)/u;
        const parsedPushSpec = pushSpecLine.match(pushSpecPattern);
        if (!parsedPushSpec || !parsedPushSpec.groups) {
            throw new Error(
                `Could not parse a line in git push specification (stdin to git pre-push hook): ${pushSpecLine}`,
            );
        }
        // No automatic support for named capture groups in TypeScript yet, check for presence manually:
        interface RegexGroupMatch {
            readonly [maybeGroupName: string]: string | undefined;
        }
        const localRef = (parsedPushSpec.groups as RegexGroupMatch).localRef;
        const localSha = (parsedPushSpec.groups as RegexGroupMatch).localSha;
        const remoteRef = (parsedPushSpec.groups as RegexGroupMatch).remoteRef;
        if (!localRef || !localSha || !remoteRef) {
            throw new Error(
                `Could not extract expected values from a line in git push specification (stdin to git pre-push hook): ${pushSpecLine}`,
            );
        }

        if (localRef === '(delete)') {
            // We will not run the child program for to-be-deleted branches.
            continue;
        }

        if (pushSpecLines.length > 1) {
            console.error(
                `${getScriptName(process)}: Verifying commit ${localSha} from local ref ${localRef} for remote ref ${remoteRef} …`,
            );
        }

        const childExitCode = await runChildProgramInTemporaryCheckout(
            process,
            console,
            rootDirectoryName,
            async (targetDirectoryName) => {
                console.error(
                    `${getScriptName(process)}: Running checks against commit ${localSha} inside temporary checkout (${targetDirectoryName}) …`,
                );

                const projectGitDir = await getProjectGitDir(rootDirectoryName);
                const targetGitDir = path.join(targetDirectoryName, '.git');

                // Copy the original git directory to the target, so that we can checkout the intended commit there:
                await copyFilesRecursivelyToDirectory([projectGitDir], targetDirectoryName);
                // Prevent git hooks from being executed in temporary checkout by deleting them:
                await promisify(rimraf)(path.join(targetGitDir, 'hooks'));

                // For a pre-push hook, checkout the intended commit:
                await execa('git', ['checkout', '--detach', '--force', localSha], {
                    cwd: targetDirectoryName,
                });
            },
            childArgv,
        );

        if (childExitCode !== 0) {
            // Fail early if this iteration's invocation of the child program for the pushSpecLine failed.
            return childExitCode;
        }
    }

    return SysExitCode.EX_OK;
};

/**
 * Runs the given command line arguments to this process as a child process (after running `yarn --frozen-lock-file`)
 * for the desired project state, depending on the given type of git hook (pre-push/pre-commit).
 *
 * This function represents a command line interface.
 *
 * @returns proposed exit code for the process (0 on success)
 */
const main = async (process: NodeJS.Process, console: Console): Promise<number> => {
    const givenArguments = process.argv.slice(2); // Skip "node script.js"

    if (givenArguments.length < 3) {
        console.error(usage(process));

        return SysExitCode.EX_USAGE;
    }

    // First argument determines type of hook:
    const hookTypeArgument = givenArguments[0];
    const hookType = (() => {
        switch (hookTypeArgument) {
            case '--pre-push':
                return 'pre-push' as const;
            case '--pre-commit':
                return 'pre-commit' as const;
            default:
                throw new Error(`Unsupported git hook type given: ${hookTypeArgument}`);
        }
    })();

    if (givenArguments[1] !== '--') {
        console.error(usage(process));

        return SysExitCode.EX_USAGE;
    }

    const { stdout: rootDirectoryName } = await execa('git', ['rev-parse', '--show-toplevel'], { stderr: 'inherit' });
    const childProgramArgv = givenArguments.slice(2); // Skip "<hook-type> --"

    switch (hookType) {
        case 'pre-commit':
            return runPreCommit(process, console, rootDirectoryName, childProgramArgv);
        case 'pre-push':
            return runPrePush(process, console, rootDirectoryName, childProgramArgv);
    }
};

main(process, console)
    .then((exitCode) => {
        process.exitCode = exitCode;

        return exitCode;
    })
    .catch((err) => {
        console.error(`${usage(process)}\n`);
        console.error(`${getScriptName(process)} failed with an error:`, err instanceof Error ? err.stack : err);
        const errCode = Number.isInteger(err.code) ? Math.abs(err.code) : SysExitCode.EX_SOFTWARE;
        const exitCode = errCode > 0 && errCode < 256 ? errCode : SysExitCode.EX_SOFTWARE;
        process.exitCode = exitCode;
    });
