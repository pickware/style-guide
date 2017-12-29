<?php
namespace VIISON\StyleGuide\PHPCS\Standards\VIISON\Sniffs\Arrays;

use Exception;
use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * This sniff is mostly duplicated from the two array syntax sniffs that are part of the 'Generic' coding standard (v3.1.1):
 *
 * https://github.com/squizlabs/PHP_CodeSniffer/blob/d667e245d5dcd4d7bf80f26f2c947d476b66213e/src/Standards/Generic/Sniffs/Arrays/DisallowLongArraySyntaxSniff.php
 * https://github.com/squizlabs/PHP_CodeSniffer/blob/d667e245d5dcd4d7bf80f26f2c947d476b66213e/src/Standards/Generic/Sniffs/Arrays/DisallowShortArraySyntaxSniff.php
 *
 * This sniff expects a config value 'viisonArraySyntaxType', which can be set as follows:
 *
 *  1) By passing `--config-set viisonArraySyntaxType <TYPE>` to the `phpcs` or `phpcbf` CLI commands
 *  2) By assing `<config name="viisonArraySyntax" value="<TYPE>" />` e.g. to your phpcs.xml file
 */
class ConsistentArraySyntaxSniff implements Sniff
{
    const ARRAY_SYNTAX_TYPE_LONG = 'long';
    const ARRAY_SYNTAX_TYPE_SHORT = 'short';

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(
            T_ARRAY,
            T_OPEN_SHORT_ARRAY,
        );
    }

    /**
     * Processes this sniff, when one of its tokens is encountered.
     *
     * @param File $phpcsFile
     * @param int $stackPtr
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        // Make sure the syntax type is configured correctly
        $arraySyntaxType = mb_strtolower(Config::getConfigData('viisonArraySyntaxType'));
        if ($arraySyntaxType !== self::ARRAY_SYNTAX_TYPE_LONG && $arraySyntaxType !== self::ARRAY_SYNTAX_TYPE_SHORT) {
            throw new Exception(sprintf(
                '"viisonArraySyntaxType" must be configured as either "%s" or "%s".',
                self::ARRAY_SYNTAX_TYPE_LONG,
                self::ARRAY_SYNTAX_TYPE_SHORT
            ));
        }

        $tokens = $phpcsFile->getTokens();
        $token = $tokens[$stackPtr];
        if ($token['code'] === T_ARRAY && $arraySyntaxType === self::ARRAY_SYNTAX_TYPE_SHORT) {
            // Found long array syntax, but must use short syntax
            $error = 'Short array syntax must be used to define arrays';
            $fix = $phpcsFile->addFixableError($error, $stackPtr, 'Found');
            if ($fix) {
                $opener = $token['parenthesis_opener'];
                $closer = $token['parenthesis_closer'];
                $phpcsFile->fixer->beginChangeset();
                if ($opener === null) {
                    $phpcsFile->fixer->replaceToken($stackPtr, '[]');
                } else {
                    $phpcsFile->fixer->replaceToken($stackPtr, '');
                    $phpcsFile->fixer->replaceToken($opener, '[');
                    $phpcsFile->fixer->replaceToken($closer, ']');
                }
                $phpcsFile->fixer->endChangeset();
            }
        } elseif ($token['code'] === T_OPEN_SHORT_ARRAY && $arraySyntaxType === self::ARRAY_SYNTAX_TYPE_LONG) {
            // Found short array syntax, but must use long syntax
            $error = 'Long array syntax must be used to define arrays';
            $fix = $phpcsFile->addFixableError($error, $stackPtr, 'Found');
            if ($fix === true) {
                $opener = $token['bracket_opener'];
                $closer = $token['bracket_closer'];
                $phpcsFile->fixer->beginChangeset();
                $phpcsFile->fixer->replaceToken($opener, 'array(');
                $phpcsFile->fixer->replaceToken($closer, ')');
                $phpcsFile->fixer->endChangeset();
            }
        }
    }
}
