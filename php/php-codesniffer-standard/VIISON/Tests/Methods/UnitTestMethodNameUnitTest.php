<?php
namespace VIISON\StyleGuide\PHPCS\Standards\VIISON\Tests\Methods;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

class UnitTestMethodNameUnitTest extends AbstractSniffUnitTest
{
    /**
     * @inheritdoc
     */
    public function getErrorList($testFile = 'UnitTestMethodNameUnitTest.inc')
    {
        switch ($testFile) {
            case 'UnitTestMethodNameUnitTest.inc':
                return [
                    8 => 1,
                    16 => 1,
                    24 => 1,
                    32 => 1,
                    40 => 1,
                    48 => 1,
                    56 => 1,
                    64 => 1,
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
