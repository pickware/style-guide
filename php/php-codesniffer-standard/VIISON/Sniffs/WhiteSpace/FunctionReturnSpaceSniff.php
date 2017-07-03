<?php
namespace VIISON\StyleGuide\PHPCS\Standards\VIISON\Sniffs\WhiteSpace;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Checks that there is an empty line before any 'return' statement. If the 'return' statement is preceeded by a
 * comment, that comment must be preceeded by an empty line instead.
 */
class FunctionReturnSpaceSniff implements Sniff
{
    /**
     * @inheritdoc
     */
    public function register()
    {
        return [
            T_RETURN
        ];
    }

    /**
     * @inheritdoc
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $returnToken = $tokens[$stackPtr];

        $currentLine = $returnToken['line'];
        $prevLineOffset = 1;
        $lineContainsComment = false;
        for ($i = ($stackPtr - 1); $i >= 0; $i--) {
            // Check for a line change
            if ($tokens[$i]['line'] !== $currentLine) {
                $currentLine = $tokens[$i]['line'];
                if ($lineContainsComment) {
                    // The succeeding line contains only whitespace and comments, hence continue searching on this line
                    $prevLineOffset++;
                }
                $lineContainsComment = false;
            }

            // Check for valid search scope
            if ($currentLine >= $returnToken['line']) {
                // Same line as 'return' token
                continue;
            } elseif ($currentLine < ($returnToken['line'] - $prevLineOffset)) {
                // Line out of search sope
                break;
            }

            // Check token
            if ($tokens[$i]['code'] === T_WHITESPACE) {
                // Whitespace is always allowed in the lines before 'return' tokens
                continue;
            } elseif (in_array($tokens[$i]['code'], Tokens::$commentTokens)) {
                // Comments are alloed in the lines before a 'return' token, but in that case they must be preceeded
                // by an empty line instead
                $lineContainsComment = true;
                continue;
            } elseif (in_array($tokens[$i]['code'], [T_OPEN_CURLY_BRACKET, T_COLON])) {
                // An opening curly bracked is allowed to preceed a 'return' token
                break;
            } else {
                // Any other token than consequtive whitespace, comments or an opening curly bracket, which is invalid
                $errorPtr = $i;
                while ($tokens[$errorPtr]['line'] === $tokens[$i]['line']) {
                    $errorPtr++;
                }
                $error = ($prevLineOffset > 1) ? 'Comments before return statements must be preceeded by an empty line.' : 'Return statements must be preceeded by an empty line.';
                $fix = $phpcsFile->addFixableError($error, $errorPtr, 'NewlineBefore');
                if ($fix === true) {
                    $phpcsFile->fixer->addNewlineBefore($errorPtr);
                }
                break;
            }
        }
    }
}
