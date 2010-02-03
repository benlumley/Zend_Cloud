<?php
// Call Zend_Cloud_StorageService_Adapter_AzureTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    // @TODO Switch to relative path when this is added to ZF
    require_once dirname(__FILE__) . '/../../../../TestHelper.php';
    define("PHPUnit_MAIN_METHOD", "Zend_Cloud_StorageService_Adapter_WindowsAzureTest::main");
}

/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Cloud_StorageService
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @see Zend_Cloud_StorageService_StorageServiceTestCase
 */
require_once 'Zend/Cloud/StorageServiceTestCase.php';

/**
 * @see Zend_Cloud_StorageService_Adapter_WindowsAzure
 */
require_once 'Zend/Cloud/StorageService/Adapter/WindowsAzure.php';


/**
 * @category   Zend
 * @package    Zend_Cloud
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_StorageService_Adapter_WindowsAzureTest extends Zend_Cloud_StorageServiceTestCase
{
	/**
     * Runs the test methods of this class.
     *
     * @access public
     * @static
     */
    public static function main()
    {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("Zend_Cloud_StorageService_Adapter_WindowsAzureTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
        return $this->main();
    }

    protected function _getConfig() 
    {
        if (!defined('TESTS_ZEND_SERVICE_AZURE_ONLINE_ENABLED') ||
            !constant('TESTS_ZEND_SERVICE_AZURE_ONLINE_ENABLED') ||
            !defined('TESTS_ZEND_SERVICE_AZURE_ONLINE_ACCOUNTNAME') ||
            !defined('TESTS_ZEND_SERVICE_AZURE_ONLINE_ACCOUNTKEY') ||
            !defined('TESTS_ZEND_CLOUD_STORAGE_AZURE_CONTAINER')) {
            $this->markTestSkipped("Windows Azure access not configured, skipping test");        
        }        
        
        $config = new Zend_Config(array(
            Zend_Cloud_StorageService_Factory::STORAGE_ADAPTER_KEY => 'Zend_Cloud_StorageService_Adapter_WindowsAzure',
            Zend_Cloud_StorageService_Adapter_WindowsAzure::ACCOUNT_NAME => constant('TESTS_ZEND_SERVICE_AZURE_ONLINE_ACCOUNTNAME'),
            Zend_Cloud_StorageService_Adapter_WindowsAzure::ACCOUNT_KEY => constant('TESTS_ZEND_SERVICE_AZURE_ONLINE_ACCOUNTKEY'),
            Zend_Cloud_StorageService_Adapter_WindowsAzure::HOST => constant('TESTS_ZEND_SERVICE_AZURE_ONLINE_STORAGE_HOST'),
            Zend_Cloud_StorageService_Adapter_WindowsAzure::PROXY_HOST => constant('TESTS_ZEND_SERVICE_AZURE_ONLINE_STORAGE_PROXY_HOST'),
            Zend_Cloud_StorageService_Adapter_WindowsAzure::PROXY_PORT => constant('TESTS_ZEND_SERVICE_AZURE_ONLINE_STORAGE_PROXY_PORT'),
            Zend_Cloud_StorageService_Adapter_WindowsAzure::PROXY_CREDENTIALS => constant('TESTS_ZEND_SERVICE_AZURE_ONLINE_STORAGE_PROXY_CREDENTIALS'),
            Zend_Cloud_StorageService_Adapter_WindowsAzure::CONTAINER => constant('TESTS_ZEND_CLOUD_STORAGE_AZURE_CONTAINER'),
        ));

        return $config;
    }
}