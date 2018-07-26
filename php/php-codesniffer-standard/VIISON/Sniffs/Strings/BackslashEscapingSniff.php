<?php
namespace VIISON\StyleGuide\PHPCS\Standards\VIISON\Sniffs\Strings;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Finds strings that contain unescaped backslashes, which are not escaping another character. These backslashes must be
 * escaped themself.
 */
class BackslashEscapingSniff implements Sniff
{
    const ERROR_MESSAGE = 'Non-escaping backslashes must be escaped themself';

    /**
     * @inheritdoc
     */
    public function register()
    {
        return [
            T_CONSTANT_ENCAPSED_STRING,
        ];
    }

    /**
     * @inheritdoc
     */
    public function process(File $phpcsFile, $stackPointer)
    {
        $tokens = $phpcsFile->getTokens();

        // If tabs are being converted to spaces by the tokeniser, the original content should be used instead of the
        // converted content
        if (isset($tokens[$stackPointer]['orig_content'])) {
            $string = $tokens[$stackPointer]['orig_content'];
        } else {
            $string = $tokens[$stackPointer]['content'];
        }

        if (mb_substr($string, 0, 1) === '"' && mb_substr($string, -1) === '"') {
            // Double quoted string (list of escapable characters copied from
            // PHP_CodeSniffer\Standards\Squiz\Sniffs\Strings\DoubleQuoteUsageSniff and adjusted to our needs)
            $escapableCharacters = [
                '0',
                '1',
                '2',
                '3',
                '4',
                '5',
                '6',
                '7',
                'n',
                'r',
                'f',
                't',
                'v',
                'x',
                'b',
                'e',
                'u',
                '\\',
                '"',
            ];
        } else {
            // Single quoted string
            $escapableCharacters = [
                '\\',
                '\'',
            ];
        }

        $fix = false;
        $violationIndices = [];
        for ($i = 1; $i < (mb_strlen($string) - 1); $i++) {
            // Check whether the current character is a backslash
            $character = mb_substr($string, $i, 1);
            if ($character !== '\\') {
                continue;
            }

            // Check whether the backslash escapes another character
            $nextCharacter = mb_substr($string, ($i + 1), 1);
            if (!in_array($nextCharacter, $escapableCharacters)) {
                // Only report the error once for each string
                if (count($violationIndices) === 0) {
                    $fix = $phpcsFile->addFixableError(self::ERROR_MESSAGE, $stackPointer, 'Found');
                }

                // Save all occurrences of violations
                $violationIndices[] = $i;
            } else {
                // The backslash is escaping, hence skip over the escaped character
                $i += 1;
            }
        }

        // Fix all violations at once
        if ($fix) {
            $fixedString = $string;
            foreach ($violationIndices as $counter => $violationIndex) {
                $fixedString = substr_replace($fixedString, '\\', ($violationIndex + $counter), 0);
            }
            $phpcsFile->fixer->beginChangeset();
            $phpcsFile->fixer->replaceToken($stackPointer, $fixedString);
            $phpcsFile->fixer->endChangeset();
        }
    }
}
