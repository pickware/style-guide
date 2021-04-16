<?php
namespace VIISON\StyleGuide\PHPCS\Standards\VIISON\Sniffs\Methods;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Finds test methods which have methods names that do not match their \@testdox description.
 */
class UnitTestMethodNameSniff implements Sniff
{
    const METHOD_NAME_DOES_NOT_EQUAL_TESTDOX = 'Unit test method "%s" does not equal @testdox. Expected "%s"';

    /**
     * @inheritdoc
     */
    public function register()
    {
        return [
            T_DOC_COMMENT_OPEN_TAG,
        ];
    }

    /**
     * @inheritdoc
     */
    public function process(File $phpcsFile, $stackPointer)
    {
        $tokens = $phpcsFile->getTokens();
        $testdox = $this->getTestdox($tokens, $stackPointer);
        if ($testdox === null) {
            return;
        }

        $actualMethodName = $this->getMethodName($tokens, $stackPointer);
        if ($actualMethodName === null) {
            return;
        }

        $expectedMethodName = $this->getExpectedMethodNameFromTestdox($testdox);
        if ($actualMethodName === $expectedMethodName) {
            return;
        }

        $fixMethodName = $phpcsFile->addFixableError(sprintf(
            self::METHOD_NAME_DOES_NOT_EQUAL_TESTDOX,
            $actualMethodName,
            $expectedMethodName
        ), $stackPointer, 'EqualsTestdox');

        if ($fixMethodName === true) {
            $phpcsFile->fixer->beginChangeset();
            $phpcsFile->fixer->replaceToken($stackPointer, $expectedMethodName);
            $phpcsFile->fixer->endChangeset();
        }
    }

    private function getTestdox($tokens, &$index)
    {
        $docComment = $this->getDocComment($tokens, $index);
        if ($docComment === null) {
            return null;
        }

        $lines = array_map(function ($line) {
            return trim($line);
        }, explode("\n", $docComment));
        $testdoxLines = array_values(array_filter($lines, function ($line) {
            return mb_strpos($line, '@testdox') === 0;
        }));

        if (count($testdoxLines) === 1) {
            return trim(mb_substr($testdoxLines[0], 8));
        } else {
            return null;
        }
    }

    private function getDocComment($tokens, &$index)
    {
        $tokensToSkip = [
            'T_DOC_COMMENT_OPEN_TAG',
            'T_DOC_COMMENT_STAR',
        ];
        $tokensToInclude = [
            'T_DOC_COMMENT_WHITESPACE',
            'T_DOC_COMMENT_TAG',
            'T_DOC_COMMENT_STRING',
        ];
        $docComment = '';

        while ($index < count($tokens)) {
            $token = $tokens[$index];
            $index += 1;

            if (in_array($token['type'], $tokensToSkip)) {
                continue;
            } elseif (in_array($token['type'], $tokensToInclude)) {
                $docComment .= $token['content'];
            } elseif ($token['type'] === 'T_DOC_COMMENT_CLOSE_TAG') {
                return trim($docComment);
            } else {
                return null;
            }
        }
    }

    private function getMethodName($tokens, &$index)
    {
        $expectedTokens = [
            'T_PUBLIC',
            'T_FUNCTION',
        ];

        while ($index < count($tokens)) {
            $token = $tokens[$index];
            $index += 1;

            if ($token['type'] === 'T_WHITESPACE') {
                continue;
            }

            if (count($expectedTokens) > 0 && $token['type'] === $expectedTokens[0]) {
                array_shift($expectedTokens);
                continue;
            }

            if ($token['type'] === 'T_STRING') {
                // The index was already increased by 1 and is currently pointing to the next token. Decrease the
                // index so that it points to the current token.
                $index -= 1;

                return $token['content'];
            } else {
                return null;
            }
        }
    }

    private function getExpectedMethodNameFromTestdox($testdox)
    {
        $testdoxWithoutApostropheS = preg_replace('/\'s/', 's', $testdox);
        $words = preg_split('/[^a-zA-Z0-9]/', $testdoxWithoutApostropheS);
        $methodName = 'test_';
        foreach ($words as $index => $word) {
            if (mb_strlen($word) === 0) {
                continue;
            }

            $firstLetter = mb_substr($word, 0, 1);

            $isWordUppercasedAcronym = $word == mb_strtoupper($word);
            if ($isWordUppercasedAcronym === true) {
                $followingLetters = mb_substr(mb_strtolower($word), 1);
            } else {
                $followingLetters = mb_substr($word, 1);
            }

            if ($index === 0) {
                $methodName .= mb_strtolower($firstLetter) . $followingLetters;
            } else {
                $methodName .= mb_strtoupper($firstLetter) . $followingLetters;
            }
        }

        return $methodName;
    }
}
