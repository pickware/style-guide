#! /usr/bin/env node

// Wrapper script for git-hook-env.ts.
// It must be referenced this project's package.json, so that it will be available in dependents' `./node_modules/.bin`
// directory.
// Using a wrapper script makes npm/yarn not fail when the compiled script is unavailable during installation, because
// they do not ensure the package's prepare step has been executed at the time of checking the availability of bin
// files.

require('../dist/git-hooks/tooling/git-hook-env');
