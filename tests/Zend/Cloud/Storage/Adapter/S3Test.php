<?php
// Call Zend_Cloud_Storage_Adapter_S3Test::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    // @TODO Switch to relative path when this is added to ZF
    require_once 'TestHelper.php';
    define("PHPUnit_MAIN_METHOD", "Zend_Cloud_Storage_Adapter_S3Test::main");
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
 * @category   Zend
 * @package    Zend_Cloud
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_Storage_Adapter_S3Test extends Zend_Cloud_StorageServiceTestCase
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

        $suite  = new PHPUnit_Framework_TestSuite("Zend_Cloud_Storage_Adapter_S3Test");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
        return $this->main();
    }

    /**
     * Sets up this test case
     *
     * @return void
     */
    public function setUp() {
        $config = new Zend_Config_Ini(realpath(dirname(__FILE__) . '/../_files/config/s3.ini'));

        $this->_commonStorage = Zend_Cloud_Storage_Factory::getAdapter(
                                    $config
                                );
        
        // Create the bucket here
        $s3 = new Zend_Service_Amazon_S3($config->aws_accesskey, $config->aws_secretkey);
        $s3->createBucket($config->bucket_name);
        parent::setUp();
    }
    
    // TODO: Create a custom test for S3 that checks fetchMetadata() with an object that has custom metadata.
    public function testFetchMetadata() {
        $this->markTestIncomplete('S3 doesn\'t support storing metadata after an item is created.');
    }
    
    public function testStoreMetadata() {
        $this->markTestSkipped('S3 doesn\'t support storing metadata after an item is created.');
    }
    
    public function testDeleteMetadata() {
        $this->markTestSkipped('S3 doesn\'t support storing metadata after an item is created.');
    }
    
    
	/**
     * Tears down this test case
     *
     * @return void
     */
    public function tearDown() {
        $config = new Zend_Config_Ini(realpath(dirname(__FILE__) . '/../_files/config/s3.ini'));
        
        // Delete the bucket here
        $s3 = new Zend_Service_Amazon_S3($config->aws_accesskey, $config->aws_secretkey);
        $s3->removeBucket($config->bucket_name);
        parent::setUp();
    }
}
