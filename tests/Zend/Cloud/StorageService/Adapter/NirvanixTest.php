<?php
// Call Zend_Cloud_StorageService_Adapter_NirvanixTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    // @TODO Switch to relative path when this is added to ZF
    require_once 'TestHelper.php';
    define("PHPUnit_MAIN_METHOD", "Zend_Cloud_StorageService_Adapter_NirvanixTest::main");
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
 * @see Zend_Config_Ini
 */
require_once 'Zend/Config/Ini.php';

/**
 * @see Zend_Cloud_StorageService_Factory
 */
require_once 'Zend/Cloud/Storage/Factory.php';

/**
 * @see Zend_Cloud_StorageService_IStorageTestCase
 */
require_once 'Zend/Cloud/StorageServiceTestCase.php';

/**
 * @category   Zend
 * @package    Zend_Cloud
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_StorageService_Adapter_NirvanixTest extends Zend_Cloud_StorageServiceTestCase
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

        $suite  = new PHPUnit_Framework_TestSuite("Zend_Cloud_StorageService_Adapter_NirvanixTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
        return $this->main();
    }

    public function testFetchItemStream() {
        // The Nirvanix client library doesn't support streams
        $this->markTestSkipped('The Nirvanix client library doesn\'t support streams.');
    }

    public function testStoreItemStream() {
        // The Nirvanix client library doesn't support streams
        $this->markTestSkipped('The Nirvanix client library doesn\'t support streams.');
    }

    /**
     * Sets up this test case
     *
     * @return void
     */
    public function setUp() {
        $config = $this->_getConfig();

        $this->_commonStorage = Zend_Cloud_StorageService_Factory::getAdapter(
                                    $config
                                );

        // Some Nirvanix operations need a long time to take effect
        $this->_waitPeriod = 5;

        parent::setUp();
    }

    protected function _getConfig() {
        $config = new Zend_Config(array('storage_adapter' => const('TESTS_ZEND_CLOUD_STORAGE_ADAPTER_NIRVANIX',
                                        'remote_directory' => const('TESTS_ZEND_CLOUD_ADAPTER_NIRVANIX_REMOTE_DIRECTORY'),
                                        'auth_accesskey' => const('TESTS_ZEND_SERVICE_NIRVANIX_ONLINE_ACCESSKEY'),
                                        'auth_username' => const('TESTS_ZEND_SERVICE_NIRVANIX_ONLINE_USERNAME'),
                                        'auth_password' => const('TESTS_ZEND_SERVICE_NIRVANIX_ONLINE_PASSWORD'));

        return $config;
    }
}
