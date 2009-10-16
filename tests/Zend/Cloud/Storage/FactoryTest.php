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

// Call Zend_Cloud_Storage_FactoryTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "Zend_Cloud_Storage_FactoryTest::main");
}

/**
 * @see Zend_Config_Ini
 */
require_once 'Zend/Config/Ini.php';

/**
 * @see Zend_Cloud_Storage_Factory
 */
require_once 'Zend/Cloud/Storage/Factory.php';

/**
 * Test class for Zend_Cloud_Storage_Factory
 *
 * @category   Zend
 * @package    Zend_Cloud
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Cloud
 */
class Zend_Cloud_Storage_FactoryTest extends PHPUnit_Framework_TestCase 
{
    /**
     * Runs the test methods of this class.
     *
     * @return void
     */
    public static function main()
    {
        $suite  = new PHPUnit_Framework_TestSuite("Zend_Cloud_Storage_FactoryTest");
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

    public function testGetStorageAdapterKey()
    {
        $this->assertTrue(is_string(Zend_Cloud_Storage_Factory::STORAGE_ADAPTER_KEY));
    }
    
    public function testGetAdapterWithConfig() {
        
        // Nirvanix adapter
        $nirvanixConfig = new Zend_Config_Ini(realpath(dirname(__FILE__) . '/_files/config/nirvanix.ini'));

        $nirvanixAdapter = Zend_Cloud_Storage_Factory::getAdapter(
                                    $nirvanixConfig
                                );
        
        $this->assertEquals('Zend_Cloud_Storage_Adapter_Nirvanix', get_class($nirvanixAdapter));
        
        // S3 adapter
        $s3Config = new Zend_Config_Ini(realpath(dirname(__FILE__) . '/_files/config/s3.ini'));

        $s3Adapter = Zend_Cloud_Storage_Factory::getAdapter(
                                    $s3Config
                                );
        
        $this->assertEquals('Zend_Cloud_Storage_Adapter_S3', get_class($s3Adapter));
        
        // file system adapter
        $fileSystemConfig = new Zend_Config_Ini(realpath(dirname(__FILE__) . '/_files/config/filesystem.ini'));

        $fileSystemAdapter = Zend_Cloud_Storage_Factory::getAdapter(
                                    $fileSystemConfig
                                );
        
        $this->assertEquals('Zend_Cloud_Storage_Adapter_FileSystem', get_class($fileSystemAdapter));
        
        // Azure adapter
//        $azureConfig = new Zend_Config_Ini(realpath(dirname(__FILE__) . '/_files/config/azure.ini'));
//
//        $azureAdapter = Zend_Cloud_Storage_Factory::getAdapter(
//                                    $azureConfig
//                                );
//        
//        $this->assertEquals('Zend_Cloud_Storage_Adapter_Azure', get_class($azureAdapter));
    }
    
    public function testGetAdapterWithArray() {
        
        // No need to overdo it; we'll test the array config with just one adapter.
        $fileSystemConfig = array(Zend_Cloud_Storage_Factory::STORAGE_ADAPTER_KEY =>
        					     'Zend_Cloud_Storage_Adapter_FileSystem');

        $fileSystemAdapter = Zend_Cloud_Storage_Factory::getAdapter(
                                    $fileSystemConfig
                                );
        
        $this->assertEquals('Zend_Cloud_Storage_Adapter_FileSystem', get_class($fileSystemAdapter));
    }
}

// Call Zend_Cloud_Storage_FactoryTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == "Zend_Cloud_Storage_FactoryTest::main") {
    Zend_Cloud_Storage_FactoryTest::main();
}
