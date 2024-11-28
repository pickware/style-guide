<?php
namespace VIISON\StyleGuide\PHPCS\Standards\VIISON\Sniffs\Strings;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * This sniff checks for the usage of 'UTC_TIMESTAMP()' in string literals and suggests passing 3 as an argument.
 */
class NoUtcTimestampWithoutArgumentInMySqlSniff implements Sniff
{
    /**
     * @inheritdoc
     */
    public function register()
    {
        return [
            T_CONSTANT_ENCAPSED_STRING,
            T_HEREDOC,
            T_NOWDOC
        ];
    }

    /**
     * @inheritdoc
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $token = $phpcsFile->getTokens()[$stackPtr];
        $content = $token['content'];

        if (preg_match('/UTC_TIMESTAMP\(\s*?[012456789]?\s*?\)/', $content)) {
            $error = 'Always pass 3 as an argument for UTC_TIMESTAMP() to ensure a consistent precision.';
            $phpcsFile->addError($error, $stackPtr, 'FoundNowFunction');
        }
    }
}
