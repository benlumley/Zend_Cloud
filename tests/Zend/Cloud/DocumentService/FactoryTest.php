<?php
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
 * @package    Zend_Cloud
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: FactoryTest.php 17573 2009-08-13 18:01:41Z alexander $
 */

// Call Zend_Cloud_DocumentService_FactoryTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "Zend_Cloud_DocumentService_FactoryTest::main");
}

/**
 * @see Zend_Config_Ini
 */
require_once 'Zend/Config/Ini.php';

/**
 * @see Zend_Cloud_FactoryTest
 */
require_once 'Zend/Cloud/FactoryTest.php';

/**
 * @see Zend_Cloud_DocumentService_Factory
 */
require_once 'Zend/Cloud/DocumentService/Factory.php';

/**
 * Test class for Zend_Cloud_DocumentService_Factory
 *
 * @category   Zend
 * @package    Zend_Cloud
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Cloud
 */
class Zend_Cloud_DocumentService_FactoryTest extends Zend_Cloud_FactoryTest
{
    /**
     * Runs the test methods of this class.
     *
     * @return void
     */
    public static function main()
    {
        $suite  = new PHPUnit_Framework_TestSuite("Zend_Cloud_DocumentService_FactoryTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp()
    {
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    public function tearDown()
    {
    }

    public function testGetQueueAdapterKey()
    {
        $this->assertTrue(is_string(Zend_Cloud_DocumentService_Factory::ADAPTER));
    }

    public function testGetAdapterWithConfig() {
        // SimpleDB adapter
        $simpleDBAdapter = Zend_Cloud_DocumentService_Factory::getAdapter(
                                    Zend_Cloud_DocumentService_Adapter_SimpleDBTest::getConfig()
                                );

        $this->assertEquals('Zend_Cloud_StorageService_Adapter_SimpleDB', get_class($simpleDBAdapter));
    }

    public function testGetAdapterWithArray() {

        // No need to overdo it; we'll test the array config with just one adapter.
        $simpleDBConfig = array(Zend_Cloud_DocumentService_Factory::ADAPTER =>
        					     'Zend_Cloud_DocumentService_Adapter_SimpleDB');

        $simpleDBAdapter = Zend_Cloud_DocumentService_Factory::getAdapter(
                                    $simpleDBConfig
                                );

        $this->assertEquals('Zend_Cloud_DocumentService_Adapter_SimpleDB', get_class($simpleDBAdapter));
    }
}

// Call Zend_Cloud_DocumentService_FactoryTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == "Zend_Cloud_DocumentService_FactoryTest::main") {
    Zend_Cloud_DocumentService_FactoryTest::main();
}
