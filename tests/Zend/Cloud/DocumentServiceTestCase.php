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
 * @package    Zend_Cloud
 * @subpackage DocumentService
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

require_once 'TestHelper.php';

/**
 * @see Zend_Cloud_DocumentService
 */
require_once 'Zend/Cloud/DocumentService.php';

/**
 * @see Zend_Cloud_DocumenteService_Document
 */
require_once 'Zend/Cloud/DocumentService/Document.php';


/**
 * @category   Zend
 * @package    Zend_Cloud
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 *
 * This class forces the adapter tests to implement tests for all methods on
 * Zend_Cloud_DocumentService.
 */
abstract class Zend_Cloud_DocumentServiceTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * Reference to Document adapter to test
     *
     * @var Zend_Cloud_DocumentService
     */
    protected $_commonDocument;

    protected $_dummyCollectionNamePrefix = 'TestCollection';

    protected $_dummyDataPrefix = 'TestData';

    const ID_FIELD = "__id";
    
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

    public function testDocumentService()
    {
        $this->assertTrue($this->_commonDocument instanceof Zend_Cloud_DocumentService); 
    } 
    
    protected function _collectionName($name)
    {
        return $this->_dummyCollectionNamePrefix.$name;
    }
    
    public function testCreateCollection() 
    {
        $this->_commonDocument->deleteCollection($this->_collectionName("test1"));
        $this->_wait();
        
        $this->_commonDocument->createCollection($this->_collectionName("test1"));
        $this->_wait();
        
        $collections = $this->_commonDocument->listCollections();
        $this->assertContains($this->_collectionName("test1"), $collections, "New collection not in the list");
        $this->_wait();
        
        $this->_commonDocument->deleteCollection($this->_collectionName("test1"));
    }

    public function testDeleteCollection() 
    {
        $this->_commonDocument->createCollection($this->_collectionName("test2"));
        $this->_wait();
        
        $collections = $this->_commonDocument->listCollections();
        $this->assertContains($this->_collectionName("test2"), $collections, "New collection not in the list");
        $this->_wait();

        $this->_commonDocument->deleteCollection($this->_collectionName("test2"));
        $this->_wait();
        $this->_wait();

        $collections = $this->_commonDocument->listCollections();
        $this->assertNotContains($this->_collectionName("test2"), $collections, "New collection not in the list");
    }

    public function testListCollections() 
    {
        $this->_commonDocument->createCollection($this->_collectionName("test3"));
        $this->_commonDocument->createCollection($this->_collectionName("test4"));
        $this->_wait();
        
        $collections = $this->_commonDocument->listCollections();
        $this->assertContains($this->_collectionName("test3"), $collections, "New collection test3 not in the list");
        $this->assertContains($this->_collectionName("test4"), $collections, "New collection test4 not in the list");
        $this->_wait();

        $this->_commonDocument->deleteCollection($this->_collectionName("test3"));
        $this->_commonDocument->deleteCollection($this->_collectionName("test4"));
    }

    public function testInsertDocument() 
    {
        $data = $this->_getDocumentData();
        $name = $this->_collectionName("testID");
        $this->_commonDocument->createCollection($name);
        
        $doc = $this->_makeDocument($data[0]);
        $this->_commonDocument->insertDocument($name, $doc);
        $this->_wait();
        
        $fetchdoc = $this->_commonDocument->fetchDocument($name, $doc->getID());
        $this->assertTrue($fetchdoc instanceof Zend_Cloud_DocumentService_Document, "New document not found");

        $this->assertEquals($doc->name, $fetchdoc->name, "Name field wrong");
        $this->assertEquals($doc->keyword, $fetchdoc->keyword, "Keyword field wrong");
        
        $this->_commonDocument->deleteCollection($name);
    }

    public function testDeleteDocument()
    {
        $data = $this->_getDocumentData();
        $name = $this->_collectionName("testDel");
        $this->_commonDocument->createCollection($name);
        
        $doc1 = $this->_makeDocument($data[0]);
        $this->_commonDocument->insertDocument($name, $doc1);
        $this->_wait();
        
        $doc2 = $this->_makeDocument($data[1]);
        $this->_commonDocument->insertDocument($name, $doc2);
        $this->_wait();
        
        $this->_commonDocument->deleteDocument($name, $doc1->getID());
        $this->_wait();
        
        $fetchdoc = $this->_commonDocument->fetchDocument($name, $doc1->getID());
        $this->assertFalse($fetchdoc, "Delete failed");
        
        $fetchdoc = $this->_commonDocument->fetchDocument($name, $doc2->getID());
        $this->assertTrue($fetchdoc instanceof Zend_Cloud_DocumentService_Document, "New document not found");
        $this->assertEquals($doc2->name, $fetchdoc->name, "Name field wrong");
        
        $this->_commonDocument->deleteCollection($name);
    }

    public function testReplaceDocument() 
    {
        $data = $this->_getDocumentData();
        $name = $this->_collectionName("testRD");
        $this->_commonDocument->createCollection($name);
        
        $doc1 = $this->_makeDocument($data[0]);
        $this->_commonDocument->insertDocument($name, $doc1);
        $doc2 = $this->_makeDocument($data[1]);
        $this->_commonDocument->insertDocument($name, $doc2);
        $this->_wait();
        
        $doc3 = $this->_makeDocument($data[2]);
        $newdoc = new Zend_Cloud_DocumentService_Document($doc1->getID(), $doc3->getFields());
        $this->_commonDocument->replaceDocument($name, $newdoc);
        
        $fetchdoc = $this->_commonDocument->fetchDocument($name, $doc1->getID());
        $this->assertTrue($fetchdoc instanceof Zend_Cloud_DocumentService_Document, "New document not found");
        $this->assertEquals($doc3->name, $fetchdoc->name, "Name field did not update");
        $this->assertEquals($doc3->keywords, $fetchdoc->keywords, "Keywords did not update");
        
        $this->_commonDocument->deleteCollection($name);
    }
    
    public function testUpdateDocument() 
    {
        $data = $this->_getDocumentData();
        $name = $this->_collectionName("testUD");
        $this->_commonDocument->createCollection($name);
        
        $doc = $this->_makeDocument($data[0]);
        $this->_commonDocument->insertDocument($name, $doc);
        $this->_wait();
        $doc1 = $this->_makeDocument($data[1]);
        $this->_commonDocument->updateDocument($name, $doc->getID(), $doc1->getFields());
        $this->_wait();
        
        $fetchdoc = $this->_commonDocument->fetchDocument($name, $doc->getID());
        $this->assertTrue($fetchdoc instanceof Zend_Cloud_DocumentService_Document, "New document not found");
        $this->assertEquals($doc1->name, $fetchdoc->name, "Name field did not update");
        
        $this->_commonDocument->deleteCollection($name);
    }

    public function testQuery() {}

    protected function _wait() {
        sleep($this->_waitPeriod);
    }
    
    protected function _makeDocument($arr)
    {
        $id = $arr[self::ID_FIELD];
        unset($arr[self::ID_FIELD]);
        return new Zend_Cloud_DocumentService_Document($id, $arr);    
    }
    
    public function setUp() 
    {
        $this->_config = $this->_getConfig();
        $this->_commonDocument = Zend_Cloud_DocumentService_Factory::getAdapter($this->_config);
        parent::setUp();
    } 
    
    public function tearDown()
    {
        parent::tearDown();
    }
    
    abstract protected function _getConfig();
    abstract protected function _getDocumentData();
}


class Zend_Cloud_DocumentService_Adapter_Skip extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->markTestSkipped('No access key available in ${configFilename}');
    }

    public function testNothing()
    {
    }
}
