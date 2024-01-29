<?php
namespace VIISON\StyleGuide\PHPCS\Standards\VIISON\Sniffs\Enum;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class EnumCaseSniff implements Sniff
{
    /**
     * {@inheritDoc}
     */
    public function register()
    {
        return [
            T_ENUM_CASE,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $enumName = $tokens[$stackPtr]['content'];

        // Überprüfen Sie, ob der Enum-Name im UpperCamelCase geschrieben ist
        if (!preg_match('/^[A-Z][a-zA-Z0-9]*$/', $enumName)) {
            $error = 'Enum cases should be written in UpperCamelCase';
            $phpcsFile->addError($error, $stackPtr, 'NotUpperCamelCase');
        }
    }
}
