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
        $enumNamePosition = $phpcsFile->findNext(T_STRING, $stackPtr);
        if ($enumNamePosition === false) {
            return;
        }
        $tokens = $phpcsFile->getTokens();
        $enumName = $tokens[$enumNamePosition]['content'];

        // Check whether the enum case is written in  UpperCamelCase
        if (!preg_match('/^((([A-Z][a-z0-9]+)((\\d)|([A-Z0-9][a-z0-9]+))*([A-Z])?)|[A-Z])$/', $enumName)) {
            $error = 'Enum cases should be written in UpperCamelCase';
            $phpcsFile->addError($error, $stackPtr, 'NotUpperCamelCase');
        }
    }
}
