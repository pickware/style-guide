<?php
namespace VIISON\StyleGuide\PHPCS\Standards\VIISON\Sniffs\Arrays;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * This sniff is mostly duplicated from the sniff that is part of the 'Squiz' coding standard (v3.1.1):
 *
 * https://github.com/squizlabs/PHP_CodeSniffer/blob/d667e245d5dcd4d7bf80f26f2c947d476b66213e/src/Standards/Squiz/Sniffs/Arrays/ArrayDeclarationSniff.php
 */
class ArrayDeclarationSniff implements Sniff
{
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
    }//end register()

    /**
     * Processes this sniff, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The current file being checked.
     * @param int                         $stackPtr  The position of the current token in
     *                                               the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if ($tokens[$stackPtr]['code'] === T_ARRAY) {
            $phpcsFile->recordMetric($stackPtr, 'Short array syntax used', 'no');

            // Array keyword should be lower case.
            if ($tokens[$stackPtr]['content'] !== strtolower($tokens[$stackPtr]['content'])) {
                if ($tokens[$stackPtr]['content'] === strtoupper($tokens[$stackPtr]['content'])) {
                    $phpcsFile->recordMetric($stackPtr, 'Array keyword case', 'upper');
                } else {
                    $phpcsFile->recordMetric($stackPtr, 'Array keyword case', 'mixed');
                }

                $error = 'Array keyword should be lower case; expected "array" but found "%s"';
                $data  = array($tokens[$stackPtr]['content']);
                $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'NotLowerCase', $data);
                if ($fix === true) {
                    $phpcsFile->fixer->replaceToken($stackPtr, 'array');
                }
            } else {
                $phpcsFile->recordMetric($stackPtr, 'Array keyword case', 'lower');
            }

            $arrayStart = $tokens[$stackPtr]['parenthesis_opener'];
            if (isset($tokens[$arrayStart]['parenthesis_closer']) === false) {
                return;
            }

            $arrayEnd = $tokens[$arrayStart]['parenthesis_closer'];

            if ($arrayStart !== ($stackPtr + 1)) {
                $error = 'There must be no space between the "array" keyword and the opening parenthesis';

                $next = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), $arrayStart, true);
                if (isset(Tokens::$commentTokens[$tokens[$next]['code']]) === true) {
                    // We don't have anywhere to put the comment, so don't attempt to fix it.
                    $phpcsFile->addError($error, $stackPtr, 'SpaceAfterKeyword');
                } else {
                    $fix = $phpcsFile->addFixableError($error, $stackPtr, 'SpaceAfterKeyword');
                    if ($fix === true) {
                        $phpcsFile->fixer->beginChangeset();
                        for ($i = ($stackPtr + 1); $i < $arrayStart; $i++) {
                            $phpcsFile->fixer->replaceToken($i, '');
                        }

                        $phpcsFile->fixer->endChangeset();
                    }
                }
            }
        } else {
            $phpcsFile->recordMetric($stackPtr, 'Short array syntax used', 'yes');
            $arrayStart = $stackPtr;
            $arrayEnd   = $tokens[$stackPtr]['bracket_closer'];
        }//end if

        // Check for empty arrays.
        $content = $phpcsFile->findNext(T_WHITESPACE, ($arrayStart + 1), ($arrayEnd + 1), true);
        if ($content === $arrayEnd) {
            // Empty array, but if the brackets aren't together, there's a problem.
            if (($arrayEnd - $arrayStart) !== 1) {
                $error = 'Empty array declaration must have no space between the parentheses';
                $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'SpaceInEmptyArray');

                if ($fix === true) {
                    $phpcsFile->fixer->beginChangeset();
                    for ($i = ($arrayStart + 1); $i < $arrayEnd; $i++) {
                        $phpcsFile->fixer->replaceToken($i, '');
                    }

                    $phpcsFile->fixer->endChangeset();
                }
            }

            // We can return here because there is nothing else to check. All code
            // below can assume that the array is not empty.
            return;
        }

        if ($tokens[$arrayStart]['line'] === $tokens[$arrayEnd]['line']) {
            $this->processSingleLineArray($phpcsFile, $stackPtr, $arrayStart, $arrayEnd);
        } else {
            $this->processMultiLineArray($phpcsFile, $stackPtr, $arrayStart, $arrayEnd);
        }
    }//end process()

    /**
     * Processes a single-line array definition.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile  The current file being checked.
     * @param int                         $stackPtr   The position of the current token
     *                                                in the stack passed in $tokens.
     * @param int                         $arrayStart The token that starts the array definition.
     * @param int                         $arrayEnd   The token that ends the array definition.
     *
     * @return void
     */
    public function processSingleLineArray($phpcsFile, $stackPtr, $arrayStart, $arrayEnd)
    {
        $tokens = $phpcsFile->getTokens();

        // Check if there are multiple values. If so, then it has to be multiple lines
        // unless it is contained inside a function call or condition.
        $valueCount = 0;
        $commas     = array();
        for ($i = ($arrayStart + 1); $i < $arrayEnd; $i++) {
            // Skip bracketed statements, like function calls.
            if ($tokens[$i]['code'] === T_OPEN_PARENTHESIS) {
                $i = $tokens[$i]['parenthesis_closer'];
                continue;
            }

            if ($tokens[$i]['code'] === T_COMMA) {
                // Before counting this comma, make sure we are not
                // at the end of the array.
                $next = $phpcsFile->findNext(T_WHITESPACE, ($i + 1), $arrayEnd, true);
                if ($next !== false) {
                    $valueCount++;
                    $commas[] = $i;
                } else {
                    // There is a comma at the end of a single line array.
                    $error = 'Comma not allowed after last value in single-line array declaration';
                    $fix   = $phpcsFile->addFixableError($error, $i, 'CommaAfterLast');
                    if ($fix === true) {
                        $phpcsFile->fixer->replaceToken($i, '');
                    }
                }
            }
        }//end for

        // Now check each of the double arrows (if any).
        $nextArrow = $arrayStart;
        while (($nextArrow = $phpcsFile->findNext(T_DOUBLE_ARROW, ($nextArrow + 1), $arrayEnd)) !== false) {
            if ($tokens[($nextArrow - 1)]['code'] !== T_WHITESPACE) {
                $content = $tokens[($nextArrow - 1)]['content'];
                $error   = 'Expected 1 space between "%s" and double arrow; 0 found';
                $data    = array($content);
                $fix     = $phpcsFile->addFixableError($error, $nextArrow, 'NoSpaceBeforeDoubleArrow', $data);
                if ($fix === true) {
                    $phpcsFile->fixer->addContentBefore($nextArrow, ' ');
                }
            } else {
                $spaceLength = $tokens[($nextArrow - 1)]['length'];
                if ($spaceLength !== 1) {
                    $content = $tokens[($nextArrow - 2)]['content'];
                    $error   = 'Expected 1 space between "%s" and double arrow; %s found';
                    $data    = array(
                        $content,
                        $spaceLength,
                    );

                    $fix = $phpcsFile->addFixableError($error, $nextArrow, 'SpaceBeforeDoubleArrow', $data);
                    if ($fix === true) {
                        $phpcsFile->fixer->replaceToken(($nextArrow - 1), ' ');
                    }
                }
            }//end if

            if ($tokens[($nextArrow + 1)]['code'] !== T_WHITESPACE) {
                $content = $tokens[($nextArrow + 1)]['content'];
                $error   = 'Expected 1 space between double arrow and "%s"; 0 found';
                $data    = array($content);
                $fix     = $phpcsFile->addFixableError($error, $nextArrow, 'NoSpaceAfterDoubleArrow', $data);
                if ($fix === true) {
                    $phpcsFile->fixer->addContent($nextArrow, ' ');
                }
            } else {
                $spaceLength = $tokens[($nextArrow + 1)]['length'];
                if ($spaceLength !== 1) {
                    $content = $tokens[($nextArrow + 2)]['content'];
                    $error   = 'Expected 1 space between double arrow and "%s"; %s found';
                    $data    = array(
                        $content,
                        $spaceLength,
                    );

                    $fix = $phpcsFile->addFixableError($error, $nextArrow, 'SpaceAfterDoubleArrow', $data);
                    if ($fix === true) {
                        $phpcsFile->fixer->replaceToken(($nextArrow + 1), ' ');
                    }
                }
            }//end if
        }//end while

        if ($valueCount > 0) {
            $nestedParenthesis = false;
            if (isset($tokens[$stackPtr]['nested_parenthesis']) === true) {
                $nested            = $tokens[$stackPtr]['nested_parenthesis'];
                $nestedParenthesis = array_pop($nested);
            }

            if ($nestedParenthesis === false
                || $tokens[$nestedParenthesis]['line'] !== $tokens[$stackPtr]['line']
            ) {
                $error = 'Array with multiple values cannot be declared on a single line';
                $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'SingleLineNotAllowed');
                if ($fix === true) {
                    $phpcsFile->fixer->beginChangeset();
                    $phpcsFile->fixer->addNewline($arrayStart);
                    $phpcsFile->fixer->addNewlineBefore($arrayEnd);
                    $phpcsFile->fixer->endChangeset();
                }

                return;
            }

            // We have a multiple value array that is inside a condition or
            // function. Check its spacing is correct.
            foreach ($commas as $comma) {
                if ($tokens[($comma + 1)]['code'] !== T_WHITESPACE) {
                    $content = $tokens[($comma + 1)]['content'];
                    $error   = 'Expected 1 space between comma and "%s"; 0 found';
                    $data    = array($content);
                    $fix     = $phpcsFile->addFixableError($error, $comma, 'NoSpaceAfterComma', $data);
                    if ($fix === true) {
                        $phpcsFile->fixer->addContent($comma, ' ');
                    }
                } else {
                    $spaceLength = $tokens[($comma + 1)]['length'];
                    if ($spaceLength !== 1) {
                        $content = $tokens[($comma + 2)]['content'];
                        $error   = 'Expected 1 space between comma and "%s"; %s found';
                        $data    = array(
                            $content,
                            $spaceLength,
                        );

                        $fix = $phpcsFile->addFixableError($error, $comma, 'SpaceAfterComma', $data);
                        if ($fix === true) {
                            $phpcsFile->fixer->replaceToken(($comma + 1), ' ');
                        }
                    }
                }//end if

                if ($tokens[($comma - 1)]['code'] === T_WHITESPACE) {
                    $content     = $tokens[($comma - 2)]['content'];
                    $spaceLength = $tokens[($comma - 1)]['length'];
                    $error       = 'Expected 0 spaces between "%s" and comma; %s found';
                    $data        = array(
                        $content,
                        $spaceLength,
                    );

                    $fix = $phpcsFile->addFixableError($error, $comma, 'SpaceBeforeComma', $data);
                    if ($fix === true) {
                        $phpcsFile->fixer->replaceToken(($comma - 1), '');
                    }
                }
            }//end foreach
        }//end if
    }//end processSingleLineArray()

    /**
     * Processes a multi-line array definition.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile  The current file being checked.
     * @param int                         $stackPtr   The position of the current token
     *                                                in the stack passed in $tokens.
     * @param int                         $arrayStart The token that starts the array definition.
     * @param int                         $arrayEnd   The token that ends the array definition.
     *
     * @return void
     */
    public function processMultiLineArray($phpcsFile, $stackPtr, $arrayStart, $arrayEnd)
    {
        $tokens       = $phpcsFile->getTokens();
        $keywordStart = $tokens[$stackPtr]['column'];
        $firstWhiteSpaceOnLine = $phpcsFile->findFirstOnLine(T_WHITESPACE, $stackPtr);
        $keywordIndentation = ($tokens[$firstWhiteSpaceOnLine]['column'] === 1) ? $tokens[$firstWhiteSpaceOnLine]['length'] : 0;
        $keywordIndentation += 1;

        // Check the closing bracket is on a new line.
        $lastContent = $phpcsFile->findPrevious(T_WHITESPACE, ($arrayEnd - 1), $arrayStart, true);
        if ($tokens[$lastContent]['line'] === $tokens[$arrayEnd]['line']) {
            $error = 'Closing parenthesis of array declaration must be on a new line';
            $fix   = $phpcsFile->addFixableError($error, $arrayEnd, 'CloseBraceNewLine');
            if ($fix === true) {
                $phpcsFile->fixer->addNewlineBefore($arrayEnd);
            }
        } else if ($tokens[$arrayEnd]['column'] !== $keywordIndentation) {
            // Check the closing bracket is lined up under the "a" in array.
            $expected = ($keywordIndentation - 1);
            $found    = ($tokens[$arrayEnd]['column'] - 1);
            $error    = 'Closing parenthesis not aligned correctly; expected %s space(s) but found %s';
            $data     = array(
                $expected,
                $found,
            );

            $fix = $phpcsFile->addFixableError($error, $arrayEnd, 'CloseBraceNotAligned', $data);
            if ($fix === true) {
                if ($found === 0) {
                    $phpcsFile->fixer->addContent(($arrayEnd - 1), str_repeat(' ', $expected));
                } else {
                    $phpcsFile->fixer->replaceToken(($arrayEnd - 1), str_repeat(' ', $expected));
                }
            }
        }//end if

        $keyUsed    = false;
        $singleUsed = false;
        $indices    = array();
        $maxLength  = 0;

        if ($tokens[$stackPtr]['code'] === T_ARRAY) {
            $lastToken = $tokens[$stackPtr]['parenthesis_opener'];
        } else {
            $lastToken = $stackPtr;
        }

        // Find all the double arrows that reside in this scope.
        for ($nextToken = ($stackPtr + 1); $nextToken < $arrayEnd; $nextToken++) {
            // Skip bracketed statements, like function calls.
            if ($tokens[$nextToken]['code'] === T_OPEN_PARENTHESIS
                && (isset($tokens[$nextToken]['parenthesis_owner']) === false
                || $tokens[$nextToken]['parenthesis_owner'] !== $stackPtr)
            ) {
                $nextToken = $tokens[$nextToken]['parenthesis_closer'];
                continue;
            }

            if ($tokens[$nextToken]['code'] === T_ARRAY
                || $tokens[$nextToken]['code'] === T_OPEN_SHORT_ARRAY
                || $tokens[$nextToken]['code'] === T_CLOSURE
            ) {
                // Let subsequent calls of this test handle nested arrays.
                if ($tokens[$lastToken]['code'] !== T_DOUBLE_ARROW) {
                    $indices[] = array('value' => $nextToken);
                    $lastToken = $nextToken;
                }

                if ($tokens[$nextToken]['code'] === T_ARRAY) {
                    $nextToken = $tokens[$tokens[$nextToken]['parenthesis_opener']]['parenthesis_closer'];
                } else if ($tokens[$nextToken]['code'] === T_OPEN_SHORT_ARRAY) {
                    $nextToken = $tokens[$nextToken]['bracket_closer'];
                } else {
                    // T_CLOSURE.
                    $nextToken = $tokens[$nextToken]['scope_closer'];
                }

                $nextToken = $phpcsFile->findNext(T_WHITESPACE, ($nextToken + 1), null, true);
                if ($tokens[$nextToken]['code'] !== T_COMMA) {
                    $nextToken--;
                } else {
                    $lastToken = $nextToken;
                }

                continue;
            }//end if

            if ($tokens[$nextToken]['code'] !== T_DOUBLE_ARROW
                && $tokens[$nextToken]['code'] !== T_COMMA
            ) {
                continue;
            }

            $currentEntry = array();

            if ($tokens[$nextToken]['code'] === T_COMMA) {
                $stackPtrCount = 0;
                if (isset($tokens[$stackPtr]['nested_parenthesis']) === true) {
                    $stackPtrCount = count($tokens[$stackPtr]['nested_parenthesis']);
                }

                $commaCount = 0;
                if (isset($tokens[$nextToken]['nested_parenthesis']) === true) {
                    $commaCount = count($tokens[$nextToken]['nested_parenthesis']);
                    if ($tokens[$stackPtr]['code'] === T_ARRAY) {
                        // Remove parenthesis that are used to define the array.
                        $commaCount--;
                    }
                }

                if ($commaCount > $stackPtrCount) {
                    // This comma is inside more parenthesis than the ARRAY keyword,
                    // then there it is actually a comma used to separate arguments
                    // in a function call.
                    continue;
                }

                if ($keyUsed === true && $tokens[$lastToken]['code'] === T_COMMA) {
                    $error = 'No key specified for array entry; first entry specifies key';
                    $phpcsFile->addError($error, $nextToken, 'NoKeySpecified');

                    return;
                }

                if ($keyUsed === false) {
                    if ($tokens[($nextToken - 1)]['code'] === T_WHITESPACE) {
                        $content = $tokens[($nextToken - 2)]['content'];
                        if ($tokens[($nextToken - 1)]['content'] === $phpcsFile->eolChar) {
                            $spaceLength = 'newline';
                        } else {
                            $spaceLength = $tokens[($nextToken - 1)]['length'];
                        }

                        $error = 'Expected 0 spaces between "%s" and comma; %s found';
                        $data  = array(
                            $content,
                            $spaceLength,
                        );

                        $fix = $phpcsFile->addFixableError($error, $nextToken, 'SpaceBeforeComma', $data);
                        if ($fix === true) {
                            $phpcsFile->fixer->replaceToken(($nextToken - 1), '');
                        }
                    }

                    $valueContent = $phpcsFile->findNext(
                        Tokens::$emptyTokens,
                        ($lastToken + 1),
                        $nextToken,
                        true
                    );

                    $indices[]  = array('value' => $valueContent);
                    $singleUsed = true;
                }//end if

                $lastToken = $nextToken;
                continue;
            }//end if

            if ($tokens[$nextToken]['code'] === T_DOUBLE_ARROW) {
                if ($singleUsed === true) {
                    $error = 'Key specified for array entry; first entry has no key';
                    $phpcsFile->addError($error, $nextToken, 'KeySpecified');

                    return;
                }

                $currentEntry['arrow'] = $nextToken;
                $keyUsed = true;

                // Find the start of index that uses this double arrow.
                $indexEnd   = $phpcsFile->findPrevious(T_WHITESPACE, ($nextToken - 1), $arrayStart, true);
                $indexStart = $phpcsFile->findStartOfStatement($indexEnd);

                if ($indexStart === $indexEnd) {
                    $currentEntry['index']         = $indexEnd;
                    $currentEntry['index_content'] = $tokens[$indexEnd]['content'];
                } else {
                    $currentEntry['index']         = $indexStart;
                    $currentEntry['index_content'] = $phpcsFile->getTokensAsString($indexStart, ($indexEnd - $indexStart + 1));
                }

                $indexLength = strlen($currentEntry['index_content']);
                if ($maxLength < $indexLength) {
                    $maxLength = $indexLength;
                }

                // Find the value of this index.
                $nextContent = $phpcsFile->findNext(
                    Tokens::$emptyTokens,
                    ($nextToken + 1),
                    $arrayEnd,
                    true
                );

                $currentEntry['value'] = $nextContent;
                $indices[] = $currentEntry;
                $lastToken = $nextToken;
            }//end if
        }//end for

        /*
            This section checks for arrays that don't specify keys.

            Arrays such as:
               array(
                'aaa',
                'bbb',
                'd',
               );
        */

        if ($keyUsed === false && empty($indices) === false) {
            $count     = count($indices);
            $lastIndex = $indices[($count - 1)]['value'];

            $trailingContent = $phpcsFile->findPrevious(
                Tokens::$emptyTokens,
                ($arrayEnd - 1),
                $lastIndex,
                true
            );

            if ($tokens[$trailingContent]['code'] !== T_COMMA) {
                $phpcsFile->recordMetric($stackPtr, 'Array end comma', 'no');
                $error = 'Comma required after last value in array declaration';
                $fix   = $phpcsFile->addFixableError($error, $trailingContent, 'NoCommaAfterLast');
                if ($fix === true) {
                    $phpcsFile->fixer->addContent($trailingContent, ',');
                }
            } else {
                $phpcsFile->recordMetric($stackPtr, 'Array end comma', 'yes');
            }

            $lastValueLine = false;
            foreach ($indices as $value) {
                if (empty($value['value']) === true) {
                    // Array was malformed and we couldn't figure out
                    // the array value correctly, so we have to ignore it.
                    // Other parts of this sniff will correct the error.
                    continue;
                }

                if ($lastValueLine !== false && $tokens[$value['value']]['line'] === $lastValueLine) {
                    $error = 'Each value in a multi-line array must be on a new line';
                    $fix   = $phpcsFile->addFixableError($error, $value['value'], 'ValueNoNewline');
                    if ($fix === true) {
                        if ($tokens[($value['value'] - 1)]['code'] === T_WHITESPACE) {
                            $phpcsFile->fixer->replaceToken(($value['value'] - 1), '');
                        }

                        $phpcsFile->fixer->addNewlineBefore($value['value']);
                    }
                } else if ($tokens[($value['value'] - 1)]['code'] === T_WHITESPACE) {
                    $expected = $keywordIndentation + 3;

                    $first = $phpcsFile->findFirstOnLine(T_WHITESPACE, $value['value'], true);
                    $found = ($tokens[$first]['column'] - 1);
                    if ($found !== $expected) {
                        $error = 'Array value not aligned correctly; expected %s spaces but found %s';
                        $data  = array(
                            $expected,
                            $found,
                        );

                        $fix = $phpcsFile->addFixableError($error, $value['value'], 'ValueNotAligned', $data);
                        if ($fix === true) {
                            if ($found === 0) {
                                $phpcsFile->fixer->addContent(($value['value'] - 1), str_repeat(' ', $expected));
                            } else {
                                $phpcsFile->fixer->replaceToken(($value['value'] - 1), str_repeat(' ', $expected));
                            }
                        }
                    }
                }//end if

                $lastValueLine = $tokens[$value['value']]['line'];
            }//end foreach
        }//end if

        /*
            Below the actual indentation of the array is checked.
            Errors will be thrown when a key is not aligned, when
            a double arrow is not aligned, and when a value is not
            aligned correctly.
            If an error is found in one of the above areas, then errors
            are not reported for the rest of the line to avoid reporting
            spaces and columns incorrectly. Often fixing the first
            problem will fix the other 2 anyway.

            For example:

            $a = array(
                  'index'  => '2',
                 );

            or

            $a = [
                  'index'  => '2',
                 ];

            In this array, the double arrow is indented too far, but this
            will also cause an error in the value's alignment. If the arrow were
            to be moved back one space however, then both errors would be fixed.
        */

        $numValues = count($indices);

        $indicesStart  = ($keywordIndentation + 4);
        $indexLine     = $tokens[$stackPtr]['line'];
        $lastIndexLine = null;
        foreach ($indices as $index) {
            if (isset($index['index']) === false) {
                // Array value only.
                if ($tokens[$index['value']]['line'] === $tokens[$stackPtr]['line'] && $numValues > 1) {
                    $error = 'The first value in a multi-value array must be on a new line';
                    $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'FirstValueNoNewline');
                    if ($fix === true) {
                        $phpcsFile->fixer->addNewlineBefore($index['value']);
                    }
                }

                continue;
            }

            $lastIndexLine = $indexLine;
            $indexLine     = $tokens[$index['index']]['line'];

            if ($indexLine === $tokens[$stackPtr]['line']) {
                $error = 'The first index in a multi-value array must be on a new line';
                $fix   = $phpcsFile->addFixableError($error, $index['index'], 'FirstIndexNoNewline');
                if ($fix === true) {
                    $phpcsFile->fixer->addNewlineBefore($index['index']);
                }

                continue;
            }

            if ($indexLine === $lastIndexLine) {
                $error = 'Each index in a multi-line array must be on a new line';
                $fix   = $phpcsFile->addFixableError($error, $index['index'], 'IndexNoNewline');
                if ($fix === true) {
                    if ($tokens[($index['index'] - 1)]['code'] === T_WHITESPACE) {
                        $phpcsFile->fixer->replaceToken(($index['index'] - 1), '');
                    }

                    $phpcsFile->fixer->addNewlineBefore($index['index']);
                }

                continue;
            }

            if ($tokens[$index['index']]['column'] !== $indicesStart) {
                $expected = ($indicesStart - 1);
                $found    = ($tokens[$index['index']]['column'] - 1);
                $error    = 'Array key not aligned correctly; expected %s spaces but found %s';
                $data     = array(
                    $expected,
                    $found,
                );

                $fix = $phpcsFile->addFixableError($error, $index['index'], 'KeyNotAligned', $data);
                if ($fix === true) {
                    if ($found === 0) {
                        $phpcsFile->fixer->addContent(($index['index'] - 1), str_repeat(' ', $expected));
                    } else {
                        $phpcsFile->fixer->replaceToken(($index['index'] - 1), str_repeat(' ', $expected));
                    }
                }

                continue;
            }

            if ($tokens[$index['arrow'] - 1]['code'] !== T_WHITESPACE || $tokens[$index['arrow'] - 1]['length'] !== 1) {
                $error = 'Array double arrow must be preceded by exactly one space';
                $fix = $phpcsFile->addFixableError($error, $index['arrow'], 'DoubleArrowPrecedingWhitespace', array());
                if ($fix === true) {
                    if ($tokens[$index['arrow'] - 1]['code'] !== T_WHITESPACE) {
                        $phpcsFile->fixer->addContent(($index['arrow'] - 1), ' ');
                    } else {
                        // Too much whitespace
                        $phpcsFile->fixer->replaceToken(($index['arrow'] - 1), ' ');
                    }
                }

                continue;
            }
            if ($tokens[$index['arrow'] + 1]['code'] !== T_WHITESPACE || $tokens[$index['arrow'] + 1]['length'] !== 1) {
                $error = 'Array double arrow must be followed by exactly one space';
                $fix = $phpcsFile->addFixableError($error, $index['arrow'], 'DoubleArrowFollowingWhitespace', array());
                if ($fix === true) {
                    if ($tokens[$index['arrow'] + 1]['code'] !== T_WHITESPACE) {
                        $phpcsFile->fixer->addContent($index['arrow'], ' ');
                    } else {
                        // Too much whitespace
                        $phpcsFile->fixer->replaceToken(($index['arrow'] + 1), ' ');
                    }
                }

                continue;
            }

            $expectedValueOffset = $tokens[$index['arrow']]['column'] + $tokens[$index['arrow']]['length'] + 1;
            if ($tokens[$index['value']]['column'] !== $expectedValueOffset) {
                $found    = ($tokens[$index['value']]['column'] - ($tokens[$index['arrow']]['length'] + $tokens[$index['arrow']]['column']));
                if ($found < 0) {
                    $found = 'newline';
                }

                $error = 'Array value not aligned correctly; expected %s space(s) but found %s';
                $data  = array(
                    $expectedValueOffset,
                    $found,
                );

                $fix = $phpcsFile->addFixableError($error, $index['arrow'], 'ValueNotAligned', $data);
                if ($fix === true) {
                    if ($found === 'newline') {
                        $prev = $phpcsFile->findPrevious(T_WHITESPACE, ($index['value'] - 1), null, true);
                        $phpcsFile->fixer->beginChangeset();
                        for ($i = ($prev + 1); $i < $index['value']; $i++) {
                            $phpcsFile->fixer->replaceToken($i, '');
                        }

                        $phpcsFile->fixer->replaceToken(($index['value'] - 1), str_repeat(' ', $expectedValueOffset));
                        $phpcsFile->fixer->endChangeset();
                    } else if ($found === 0) {
                        $phpcsFile->fixer->addContent(($index['value'] - 1), str_repeat(' ', $expectedValueOffset));
                    } else {
                        $phpcsFile->fixer->replaceToken(($index['value'] - 1), str_repeat(' ', $expectedValueOffset));
                    }
                }
            }//end if

            // Check each line ends in a comma.
            $valueLine = $tokens[$index['value']]['line'];
            $nextComma = false;
            for ($i = $index['value']; $i < $arrayEnd; $i++) {
                // Skip bracketed statements, like function calls.
                if ($tokens[$i]['code'] === T_OPEN_PARENTHESIS) {
                    $i         = $tokens[$i]['parenthesis_closer'];
                    $valueLine = $tokens[$i]['line'];
                    continue;
                }

                if ($tokens[$i]['code'] === T_ARRAY) {
                    $i         = $tokens[$tokens[$i]['parenthesis_opener']]['parenthesis_closer'];
                    $valueLine = $tokens[$i]['line'];
                    continue;
                }

                // Skip to the end of multi-line strings.
                if (isset(Tokens::$stringTokens[$tokens[$i]['code']]) === true) {
                    $i = $phpcsFile->findNext($tokens[$i]['code'], ($i + 1), null, true);
                    $i--;
                    $valueLine = $tokens[$i]['line'];
                    continue;
                }

                if ($tokens[$i]['code'] === T_START_HEREDOC || $tokens[$i]['code'] === T_START_NOWDOC) {
                    // Here/nowdoc closing tags must not be followed by a comma,
                    // so it must be on the next line.
                    $i         = $tokens[$i]['scope_closer'];
                    $valueLine = ($tokens[$i]['line'] + 1);
                    continue;
                }

                if ($tokens[$i]['code'] === T_OPEN_SHORT_ARRAY) {
                    $i         = $tokens[$i]['bracket_closer'];
                    $valueLine = $tokens[$i]['line'];
                    continue;
                }

                if ($tokens[$i]['code'] === T_CLOSURE) {
                    $i         = $tokens[$i]['scope_closer'];
                    $valueLine = $tokens[$i]['line'];
                    continue;
                }

                if ($tokens[$i]['code'] === T_COMMA) {
                    $nextComma = $i;
                    break;
                }
            }//end for

            if ($nextComma === false || ($tokens[$nextComma]['line'] !== $valueLine)) {
                $error = 'Each line in an array declaration must end in a comma';
                $fix   = $phpcsFile->addFixableError($error, $index['value'], 'NoComma');

                if ($fix === true) {
                    // Find the end of the line and put a comma there.
                    for ($i = ($index['value'] + 1); $i < $arrayEnd; $i++) {
                        if ($tokens[$i]['line'] > $valueLine) {
                            break;
                        }
                    }

                    $phpcsFile->fixer->addContentBefore(($i - 1), ',');
                }
            }

            // Check that there is no space before the comma.
            if ($nextComma !== false && $tokens[($nextComma - 1)]['code'] === T_WHITESPACE) {
                // Here/nowdoc closing tags must have the command on the next line.
                $prev = $phpcsFile->findPrevious(Tokens::$emptyTokens, ($nextComma - 1), null, true);
                if ($tokens[$prev]['code'] !== T_END_HEREDOC && $tokens[$prev]['code'] !== T_END_NOWDOC) {
                    $content     = $tokens[($nextComma - 2)]['content'];
                    $spaceLength = $tokens[($nextComma - 1)]['length'];
                    $error       = 'Expected 0 spaces between "%s" and comma; %s found';
                    $data        = array(
                        $content,
                        $spaceLength,
                    );

                    $fix = $phpcsFile->addFixableError($error, $nextComma, 'SpaceBeforeComma', $data);
                    if ($fix === true) {
                        $phpcsFile->fixer->replaceToken(($nextComma - 1), '');
                    }
                }
            }
        }//end foreach
    }//end processMultiLineArray()
}//end class
