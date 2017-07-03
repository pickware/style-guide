<?php
namespace VIISON\StyleGuide\PHPCS\Standards\VIISON\Tests\WhiteSpace;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

class FunctionReturnSpaceUnitTest extends AbstractSniffUnitTest
{
    /**
     * @inheritdoc
     */
    public function getErrorList($testFile = 'FunctionReturnSpaceUnitTest.inc')
    {
        switch ($testFile) {
            case 'FunctionReturnSpaceUnitTest.inc':
                return [
                    66 => 1,
                    72 => 1,
                    81 => 1,
                    126 => 1,
                    129 => 1,
                    135 => 1
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
