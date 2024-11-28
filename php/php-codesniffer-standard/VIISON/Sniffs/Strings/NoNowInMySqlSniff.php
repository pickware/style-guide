<?php
namespace VIISON\StyleGuide\PHPCS\Standards\VIISON\Sniffs\Strings;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * This sniff disallows the usage of the method NOW() and CURRENT_TIMESTAMP() in SQL queries as they have weird
 * timezone behavior. It suggests using UTC_TIMESTAMP() instead.
 */
class NoNowInMySqlSniff implements Sniff
{
    /**
     * @inheritdoc
     */
    public function register()
    {
        return [
            T_CONSTANT_ENCAPSED_STRING,
            T_HEREDOC,
            T_NOWDOC,
        ];
    }

    /**
     * @inheritdoc
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $token = $phpcsFile->getTokens()[$stackPtr];
        $content = $token['content'];

        if (stripos($content, 'NOW(') !== false) {
            $error = 'The usage of the method NOW() in SQL queries is not allowed. Use UTC_TIMESTAMP() instead.';
            $phpcsFile->addError($error, $stackPtr, 'NoNowInSql');
        }

        if (stripos($content, 'CURRENT_TIMESTAMP(') !== false) {
            $error = 'The usage of the method CURRENT_TIMESTAMP() in SQL queries is not allowed. Use UTC_TIMESTAMP() instead.';
            $phpcsFile->addError($error, $stackPtr, 'NoCurrentTimestampInSql');
        }
    }
}
