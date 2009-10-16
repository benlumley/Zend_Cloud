<?php
// This test case cannot be executed directly.

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
 * @package    Zend_Cloud_AdapterTestCase
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

require_once 'TestHelper.php';

/**
 * @see Zend_Cloud_StorageService
 */
require_once 'Zend/Cloud/StorageService.php';

/**
 * @see Zend_Http_Client_Adapter_Socket
 */
require_once 'Zend/Http/Client/Adapter/Socket.php';

/**
 * @category   Zend
 * @package    Zend_Cloud
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * 
 * This class forces the adapter tests to implement tests for all methods on
 * Zend_Cloud_Storage_IStorage.
 */
abstract class Zend_Cloud_StorageServiceTestCase extends PHPUnit_Framework_TestCase
{   
    /**
     * Reference to storage adapter to test
     *
     * @var Zend_Cloud_StorageService
     */
    protected $_commonStorage;

    protected $_dummyNamePrefix = 'TestItem';

    protected $_dummyDataPrefix = 'TestData';
    
    /**
     * Config object
     * 
     * @var Zend_Config
     */
    
    protected $_config;
    
    /**
     * Period to wait for propagation in seconds
     * Should be set by adapter
     *
     * @var int
     */
    protected $_waitPeriod = 1;

	/**
     * Test fetch item
     *
     * @return void
     */
    public function testFetchItemString() {
        $dummyNameText = null;
        $dummyNameStream = null;
        try {
            $originalData = $this->_dummyDataPrefix . 'FetchItem';
            $dummyNameText = $this->_dummyNamePrefix . 'ForFetchText';
            $this->_clobberItem($originalData, $dummyNameText);
            $this->_wait();
            $returnedData = $this->_commonStorage->fetchItem($dummyNameText);
            $this->assertEquals($originalData, $returnedData);
            $this->_commonStorage->deleteItem($dummyNameText);
            $this->_wait();
            $this->assertFalse($this->_commonStorage->fetchItem($dummyNameText));
        } catch(Exception $e) {
            try {
                $this->_commonStorage->deleteItem($dummyNameText);
            } catch(Zend_Cloud_Exception $ignoreMe) {}
            throw $e;
        }
    }
    
	/**
     * Test fetch item
     *
     * @return void
     */
    public function testFetchItemStream() {
        
        // TODO Add support for streaming to Zend_Http.
        $this->markTestSkipped('Zend_Http doesn\'t support streaming.');
        $dummyNameText = null;
        $dummyNameStream = null;
        try {
            $originalFilename = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Storage/_files/data/dummy_data.txt');
            $dummyNameStream = $this->_dummyNamePrefix . 'ForFetchStream';
            $stream = fopen($originalFilename, 'r');
            $this->_clobberItem($stream, $dummyNameStream);
            $this->_wait();
            $returnedData = $this->_commonStorage->fetchItem($dummyNameStream);
            $this->assertEquals(file_get_contents($originalFilename), $returnedData);
            $this->_commonStorage->deleteItem($dummyNameStream);
        } catch(Exception $e) {
            try {
                $this->_commonStorage->deleteItem($dummyNameStream);
            } catch(Zend_Cloud_Exception $ignoreMe) {}
            throw $e;
        }
    }

    /**
     * Test store item
     *
     * @return void
     */
    public function testStoreItemText() {
        $dummyNameText = null;
        try {
            // Test string data
            $originalData = $this->_dummyDataPrefix . 'StoreItem';
            $dummyNameText = $this->_dummyNamePrefix . 'ForStoreText';
            $this->_clobberItem($originalData, $dummyNameText);
            $this->_wait();
            $returnedData = $this->_commonStorage->fetchItem($dummyNameText);
            $this->assertEquals($originalData, $returnedData);
            $this->_commonStorage->deleteItem($dummyNameText);
        } catch(Exception $e) {
            try {
                $this->_commonStorage->deleteItem($dummyNameText);
            } catch(Zend_Cloud_Exception $ignoreMe) {}
            throw $e;
        }
    }
    
	/**
     * Test store item
     *
     * @return void
     */
    public function testStoreItemStream() {
        
        // TODO Add support for streaming to Zend_Http.
        $this->markTestSkipped('Zend_Http doesn\'t support streaming.');
        $dummyNameStream = null;
        try {
            // Test stream data
            $originalFilename = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Storage/_files/data/dummy_data.txt');
            $dummyNameStream = $this->_dummyNamePrefix . 'ForStoreStream';
            $stream = fopen($originalFilename, 'r');
            $this->_commonStorage->storeItem($stream, $dummyNameStream);
            $this->_wait();
            $returnedData = $this->_commonStorage->fetchItem($dummyNameStream);
            $this->assertEquals(file_get_contents($originalFilename), $returnedData);
            $this->_commonStorage->deleteItem($dummyNameStream);
        } catch(Exception $e) {
            try {
                $this->_commonStorage->deleteItem($dummyNameStream);
            } catch(Zend_Cloud_Exception $ignoreMe) {}
            throw $e;
        }
    }
    
    /**
     * Test delete item
     *
     * @return void
     */
    public function testDeleteItem() {
        $dummyName = null;
        try {
            // Test string data
            $originalData = $this->_dummyDataPrefix . 'DeleteItem';
            $dummyName = $this->_dummyNamePrefix . 'ForDelete';
            $this->_clobberItem($originalData, $dummyName);
            $this->_wait();
            $returnedData = $this->_commonStorage->fetchItem($dummyName);
            $this->assertEquals($originalData, $returnedData);
            $this->_wait();
            $this->_commonStorage->deleteItem($dummyName);
            $this->_wait();
            $this->assertFalse($this->_commonStorage->fetchItem($dummyName));
        } catch(Exception $e) {
            $this->_commonStorage->deleteItem($dummyName);
            throw $e;
        }
    }

    /**
     * Test copy item
     *
     * @return void
     */
    public function testCopyItem() {
        $this->markTestSkipped('This test should be re-enabled when the semantics of "copy" change');
        try {
            // Test string data
            $originalData = $this->_dummyDataPrefix . 'CopyItem';
            $dummyName1 = $this->_dummyNamePrefix . 'ForCopy1';
            $dummyName2 = $this->_dummyNamePrefix . 'ForCopy2';
            $this->_clobberItem($originalData, $dummyName1);
            $this->_wait();
            $returnedData = $this->_commonStorage->fetchItem($dummyName1);
            $this->assertEquals($originalData, $returnedData);
            $this->_wait();
            $this->_commonStorage->copyItem($dummyName1, $dummyName2);
            $copiedData = $this->_commonStorage->fetchItem($dummyName2);
            $this->assertEquals($originalData, $copiedData);
            $this->_commonStorage->deleteItem($dummyName1);
            $this->_commonStorage->fetchItem($dummyName1);
            $this->_commonStorage->deleteItem($dummyName2);
            $this->_commonStorage->fetchItem($dummyName2);
        } catch(Exception $e) {
            $this->_commonStorage->deleteItem($dummyName1);
            $this->_commonStorage->deleteItem($dummyName2);
            throw $e;
        }
    }
    
	/**
     * Test move item
     *
     * @return void
     */
    public function testMoveItem() {
        $this->markTestSkipped('This test should be re-enabled when the semantics of "move" change');
        
        try {
            // Test string data
            $originalData = $this->_dummyDataPrefix . 'MoveItem';
            $dummyName1 = $this->_dummyNamePrefix . 'ForMove1';
            $dummyName2 = $this->_dummyNamePrefix . 'ForMove2';
            $this->_clobberItem($originalData, $dummyName1);
            $this->_wait();
            $this->_commonStorage->moveItem($dummyName1, $dummyName2);
            $this->_wait();
            $movedData = $this->_commonStorage->fetchItem($dummyName2);
            $this->assertEquals($originalData, $movedData);
            $this->assertFalse($this->_commonStorage->fetchItem($dummyName1));
            $this->_commonStorage->deleteItem($dummyName2);
            $this->assertFalse($this->_commonStorage->fetchItem($dummyName2));
        } catch(Exception $e) {
            $this->_commonStorage->deleteItem($dummyName1);
            $this->_commonStorage->deleteItem($dummyName2);
            throw $e;
        }
    }
    
	/**
     * Test fetch metadata
     *
     * @return void
     */
    public function testFetchMetadata() {
        try {
            // Test string data
            $data = $this->_dummyDataPrefix . 'FetchMetadata';
            $dummyName = $this->_dummyNamePrefix . 'ForMetadata';
            $this->_clobberItem($data, $dummyName);
            $this->_wait();
            $this->_commonStorage->storeMetadata(array('zend' => 'zend'), $dummyName);
            $this->_wait();
           
            // Hopefully we can assert more about the metadata in the future :/
            $this->assertTrue(is_array($this->_commonStorage->fetchMetadata($dummyName)));
            $this->_commonStorage->deleteItem($dummyName);
        } catch(Exception $e) {
            $this->_commonStorage->deleteItem($dummyName);
            throw $e;
        }
    }
    
	/**
     * Test list items
     *
     * @return void
     */
    public function testListItems() {
        try {

            $dummyName1 = $this->_dummyNamePrefix . 'ForListItem1';
            $dummyData1 = $this->_dummyDataPrefix . 'Item1';
            $this->_clobberItem($dummyData1, $dummyName1);
            
            $dummyName2 = $this->_dummyNamePrefix . 'ForListItem2';
            $dummyData2 = $this->_dummyDataPrefix . 'Item2';
            $this->_clobberItem($dummyData2, $dummyName2);
            $this->_wait();
            
            $objects = $this->_commonStorage->listItems('');
            
            $this->assertEquals(2, sizeof($objects));
            
            // PHPUnit does an identical comparison for assertContains(), so we just
            // use assertTrue and in_array()
            $this->assertTrue(in_array($dummyName1, $objects));
            $this->assertTrue(in_array($dummyName2, $objects));
            
            $this->_commonStorage->deleteItem($dummyName1);
            $this->_commonStorage->deleteItem($dummyName2);
        } catch(Exception $e) {
            $this->_commonStorage->deleteItem($dummyName1);
            $this->_commonStorage->deleteItem($dummyName2);
            throw $e;
        }
    }
    
    protected function _wait() {
        sleep($this->_waitPeriod);
    }
    
    protected function _clobberItem($data, $path) {
        if($this->_commonStorage->fetchItem($path)) {
            $this->_commonStorage->deleteItem($path);
        }
        $this->_wait();
        $this->_commonStorage->storeItem($data, $path);
    }
}



class Zend_Cloud_Storage_Adapter_Skip extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->markTestSkipped('No access key available in ${configFilename}');
    }

    public function testNothing()
    {
    }
}
