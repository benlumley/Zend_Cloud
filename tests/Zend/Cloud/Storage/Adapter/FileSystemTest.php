<?php
// Call Zend_Cloud_Storage_Adapter_FileSystemTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    // @TODO Switch to relative path when this is added to ZF
    require_once 'TestHelper.php';
    define("PHPUnit_MAIN_METHOD", "Zend_Cloud_Storage_Adapter_FileSystemTest::main");
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
 * @package    Zend_Cloud_Storage
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @see Zend_Config_Ini
 */
require_once 'Zend/Config/Ini.php';

/**
 * @see Zend_Cloud_Storage_Factory
 */
require_once 'Zend/Cloud/Storage/Factory.php';

/**
 * @see Zend_Cloud_StorageServiceTestCase
 */
require_once 'Zend/Cloud/StorageServiceTestCase.php';

/**
 * @see Zend_Cloud_Storage_Adapter_FileSystem
 */
require_once 'Zend/Cloud/Storage/Adapter/FileSystem.php';

/**
 * @category   Zend
 * @package    Zend_Cloud
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_Storage_Adapter_FileSystemTest extends Zend_Cloud_StorageServiceTestCase
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

        $suite  = new PHPUnit_Framework_TestSuite("Zend_Cloud_Storage_Adapter_FileSystemTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
        return $this->main();
    }

    /**
     * Sets up this test case
     *
     * @return void
     */
    public function setUp() {
        // No need to wait
        $this->_waitPeriod = 0;
        
        $config = $this->_getConfig();
        
        $this->_commonStorage = Zend_Cloud_Storage_Factory::getAdapter(
                                    $config
                                );
        
        $path = $config->local_directory;
        
        // If the test directory exists, remove it and replace it
        if(file_exists($path)) {
            $this->_rmRecursive($path);
        }
        mkdir($path, 0755);
        
        parent::setUp();
    }
    
    // TODO: Create a custom test for FileSystem that checks fetchMetadata() with file system MD.
    public function testFetchMetadata() {
        $this->markTestIncomplete('FileSystem doesn\'t support writable metadata.');
    }
    
    public function testStoreMetadata() {
        $this->markTestSkipped('FileSystem doesn\'t support writable metadata.');
    }
    
    public function testDeleteMetadata() {
        $this->markTestSkipped('FileSystem doesn\'t support writable metadata.');
    }
    
	/**
     * Tears down this test case
     *
     * @return void
     */
    public function tearDown() {
        $config = $this->_getConfig();
        
        $path = $config->local_directory;
        
        // If the test directory exists, remove it
        if(file_exists($path)) {
            $this->_rmRecursive($path);
        }

        parent::tearDown();
    }
    
    protected function _rmRecursive($path) {
        // Tidy up the path
        $path = realpath($path);
        
        if (!file_exists($path)) {
            return true;
        } else if (!is_dir($path)) {
            return unlink($path);
        } else {
            foreach (scandir($path) as $item) {
                if (!($item == '.' || $item == '..')) {
                    $this->_rmRecursive($item);
                }
            }
            return rmdir($path);
        }
    }
    
    protected function _getConfig() {
        $config = new Zend_Config_Ini(realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . 
        		                      DIRECTORY_SEPARATOR . '_files/config/filesystem.ini'), null, true);
        
        $directoryConfig = new Zend_Config(array(Zend_Cloud_Storage_Adapter_FileSystem::LOCAL_DIRECTORY =>
                           dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $config->local_directory));

        $config->merge($directoryConfig);
        
        return $config;
    }
}
