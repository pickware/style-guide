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
        return [
            T_WHITESPACE
        ];
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

        // Find last token of the previous line
        $prevFinalPointer = $stackPointer;
        do {
            $prevFinalPointer -= 1;
            $prevFinalToken = $tokens[$prevFinalPointer];
        } while ($prevFinalToken['line'] === $currentNewlineToken['line'] && $prevFinalPointer > 0);
        if ($prevFinalToken['code'] !== T_WHITESPACE && $prevFinalToken['code'] !== T_COMMENT) {
            return;
        }

        // Check previous line for being empty
        if (!self::isTokenEndingEmptyLine($prevFinalPointer, $tokens)) {
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

        // Check whether the token ends with a newline (PHP CS treats newlines at the end of a comment as a part of that
        // comment)
        if (mb_substr($currentToken['content'], -1) !== "\n") {
            return false;
        }

        // Check whether the newline ends an empty line
        return $tokenPointer === 0 || $tokens[$tokenPointer - 1]['line'] !== $currentToken['line'];
    }
}
