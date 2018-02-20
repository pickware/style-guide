<?php
namespace VIISON\StyleGuide\PHPCS\Standards\VIISON\Sniffs\Classes;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Finds classes that are declared 'abstract' but whose name does not start with 'Abstract', as well as non-abstract
 * classes whose name does start with 'Abstract'.
 */
// phpcs:ignore VIISON.Classes.AbstractClassName
class AbstractClassNameSniff implements Sniff
{
    const ABSTRACT_PREFIX = 'Abstract';
    const ABSTRACT_CLASS_MISSES_PREFIX_ERROR = 'Classes declared as "abstract" must use the "Abstract" prefix in their name';
    const NON_ABSTRACT_CLASS_HAS_PREFIX_ERROR = 'Only classes declared as "abstract" can use the "Abstract" prefix in their name';

    /**
     * @inheritdoc
     */
    public function register()
    {
        return [
            T_CLASS,
        ];
    }

    /**
     * @inheritdoc
     */
    public function process(File $phpcsFile, $stackPointer)
    {
        $tokens = $phpcsFile->getTokens();
        $classToken = $tokens[$stackPointer];

        // Check whether class is declared 'abstract' and/or the name contains the 'Abstract' prefix
        $abstractToken = self::findPrevTokenOnSameLine($stackPointer, $tokens, T_ABSTRACT);
        $isAbstract = $abstractToken !== null;
        $classNameToken = self::findNextTokenOnSameLine($stackPointer, $tokens, T_STRING);
        $usesPrefix = mb_strpos($classNameToken['content'], self::ABSTRACT_PREFIX) === 0;

        if ($isAbstract && !$usesPrefix) {
            $phpcsFile->addError(self::ABSTRACT_CLASS_MISSES_PREFIX_ERROR, $stackPointer, 'Found');
        } elseif (!$isAbstract && $usesPrefix) {
            $phpcsFile->addError(self::NON_ABSTRACT_CLASS_HAS_PREFIX_ERROR, $stackPointer, 'Found');
        }
    }

    /**
     * @param int $startPointer
     * @param array $tokens
     * @param int $tokenType
     * @return array|null
     */
    private static function findPrevTokenOnSameLine($startPointer, array $tokens, $tokenType)
    {
        $matchingTokenPointer = $startPointer;
        do {
            $matchingTokenPointer -= 1;
            $matchingToken = $tokens[$matchingTokenPointer];
        } while ($matchingToken['line'] === $tokens[$startPointer]['line'] && $matchingToken['code'] !== $tokenType && $matchingTokenPointer > 0);

        // Double check type to not match the first token in the file
        return ($matchingToken['code'] === $tokenType) ? $matchingToken : null;
    }

    /**
     * @param int $startPointer
     * @param array $tokens
     * @param int $tokenType
     * @return array|null
     */
    private static function findNextTokenOnSameLine($startPointer, array $tokens, $tokenType)
    {
        $matchingTokenPointer = $startPointer;
        do {
            $matchingTokenPointer += 1;
            $matchingToken = $tokens[$matchingTokenPointer];
        } while ($matchingToken['line'] === $tokens[$startPointer]['line'] && $matchingToken['code'] !== $tokenType && ($matchingTokenPointer + 1) < count($tokens));

        // Double check type to not match the last token in the file
        return ($matchingToken['code'] === $tokenType) ? $matchingToken : null;
    }
}
