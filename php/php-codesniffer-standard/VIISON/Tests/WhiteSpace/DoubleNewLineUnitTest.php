<?php
namespace VIISON\StyleGuide\PHPCS\Standards\VIISON\Tests\WhiteSpace;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

class DoubleNewLineUnitTest extends AbstractSniffUnitTest
{
    /**
     * @inheritdoc
     */
    public function getErrorList($testFile = 'DoubleNewLineUnitTest.inc')
    {
        switch ($testFile) {
            case 'DoubleNewLineUnitTest.inc':
                return [
                    4 => 1,
                    9 => 1,
                    12 => 1,
                    23 => 1,
                    24 => 1,
                    38 => 1,
                    42 => 1,
                    57 => 1,
                ];
            default:
                return [];
        }
    }

    /**
     * @inheritdoc
     */
    public function getWarningList()
    {
        return [];
    }
}
