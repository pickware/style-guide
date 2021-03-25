<?php
namespace VIISON\StyleGuide\PHPCS\Standards\VIISON;

use PHP_CodeSniffer\Tests\Standards\AllSniffs;
use PHP_CodeSniffer\Util\Standards;
use PHP_CodeSniffer\Autoload;
use PHPUnit\TextUI\TestRunner;
use PHPUnit\Framework\TestSuite;
use PHP_CodeSniffer\Tests\FileList;

/**
 * Unfortunately, the phpcs tests do not really allow for easy testing of custom standards. You have to provide all
 * test classes yourself so that they can be tested. This is the easiest implementation I could come up with to allow
 * our custom sniffs to be tested.
 */
class TestSuit extends AllSniffs
{
    /**
     * @inheritdoc
     */
    public static function suite()
    {
        // Some phpcs magic. The AbstractSniffUnitTest needs these globals to be set in order to find the test directory
        // and the sniffs themselves.
        $GLOBALS['PHP_CODESNIFFER_STANDARD_DIRS'] = [];
        $GLOBALS['PHP_CODESNIFFER_TEST_DIRS'] = [];
        $GLOBALS['PHP_CODESNIFFER_SNIFF_CODES'] = [];
        $GLOBALS['PHP_CODESNIFFER_FIXABLE_CODES'] = [];
        $GLOBALS['PHP_CODESNIFFER_SNIFF_CASE_FILES'] = [];

        $suite = new TestSuite('PHP CodeSniffer Standards');
        $fileList = new FileList(__DIR__ . '/Tests');

        // Loop over all files and extract the test classes.
        foreach ($fileList->getList() as $file) {
            if (!preg_match('/Test\\.php$/', $file)) {
                continue;
            }

            // Calculate the class name.
            $relativePath = mb_substr($file, mb_strlen(__DIR__));
            $relativePathWithoutEnding = preg_replace('/\\.php$/', '', $relativePath);
            $className = 'VIISON\\StyleGuide\\PHPCS\\Standards\\VIISON' . preg_replace(
                '#' . DIRECTORY_SEPARATOR . '#',
                '\\',
                $relativePathWithoutEnding
            );

            // Load the class since the phpcs testing environment can not do this by itself.
            include_once $file;

            // Set the specific test directories for the tests. Needed by AbstractSniffUnitTest.
            $GLOBALS['PHP_CODESNIFFER_STANDARD_DIRS'][$className] = __DIR__;
            $GLOBALS['PHP_CODESNIFFER_TEST_DIRS'][$className] = __DIR__ . '/Tests/';

            $suite->addTestSuite($className);
        }

        return $suite;
    }
}
