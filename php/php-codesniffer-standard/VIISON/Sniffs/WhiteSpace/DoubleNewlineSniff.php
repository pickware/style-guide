<?php
namespace VIISON\StyleGuide\PHPCS\Standards\VIISON\Sniffs\WhiteSpace;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Finds (and removes) more than one consecutive empty line.
 */
class DoubleNewlineSniff implements Sniff
{
    const ERROR_MESSAGE = 'Consecutive empty lines are not allowed';

    /**
     * @inheritdoc
     */
    public function register()
    {
        return array(
            T_WHITESPACE
        );
    }

    /**
     * @inheritdoc
     */
    public function process(File $phpcsFile, $stackPointer)
    {
        $tokens = $phpcsFile->getTokens();

        // Check current line for being empty
        if (!self::isTokenEndingEmptyLine($stackPointer, $tokens)) {
            return;
        }
        $currentNewlineToken = $tokens[$stackPointer];

        // Find previous newline
        $prevNewlinePointer = $stackPointer;
        do {
            $prevNewlinePointer -= 1;
            $prevNewlineToken = $tokens[$prevNewlinePointer];
        } while ($prevNewlineToken['content'] !== "\n" && $prevNewlinePointer > 0);
        if ($prevNewlineToken['code'] !== T_WHITESPACE) {
            return;
        }

        // Check previous line for being empty
        if (!self::isTokenEndingEmptyLine($prevNewlinePointer, $tokens)) {
            return;
        }

        // Found two consecutive empty lines, hence add fixable error
        $fix = $phpcsFile->addFixableError(self::ERROR_MESSAGE, $stackPointer, 'Found');
        if ($fix) {
            $phpcsFile->fixer->beginChangeset();
            $phpcsFile->fixer->replaceToken($stackPointer, '');
            $phpcsFile->fixer->endChangeset();
        }
    }

    /**
     * @param int $tokenPointer
     * @param array $tokens
     * @param return bool
     */
    private static function isTokenEndingEmptyLine($tokenPointer, array $tokens)
    {
        $currentToken = $tokens[$tokenPointer];

        // Check whether token is a newline
        if ($currentToken['content'] !== "\n") {
            return false;
        }

        // Check whether the newline ends an empty line
        return $tokenPointer === 0 || $tokens[$tokenPointer - 1]['line'] !== $currentToken['line'];
    }
}
