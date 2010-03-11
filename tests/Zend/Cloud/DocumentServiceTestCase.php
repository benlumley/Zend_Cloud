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
        return $this->_dummyCollectionNamePrefix.$name.mt_rand();
    }
    
    public function testCreateCollection() 
    {
        $name = $this->_collectionName("testCreate");
        $this->_commonDocument->deleteCollection($name);
        $this->_wait();
        
        $this->_commonDocument->createCollection($name);
        $this->_wait();
        
        $collections = $this->_commonDocument->listCollections();
        $this->assertContains($name, $collections, "New collection not in the list");
        $this->_wait();
        
        $this->_commonDocument->deleteCollection($name);
    }

    public function testDeleteCollection() 
    {
        $name = $this->_collectionName("testDC");
        $this->_commonDocument->createCollection($name);
        $this->_wait();
        
        $collections = $this->_commonDocument->listCollections();
        $this->assertContains($name, $collections, "New collection not in the list");
        $this->_wait();

        $this->_commonDocument->deleteCollection($name);
        $this->_wait();
        $this->_wait();

        $collections = $this->_commonDocument->listCollections();
        $this->assertNotContains($name, $collections, "New collection not in the list");
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
        $this->assertEquals($doc3->keyword, $fetchdoc->keyword, "Keywords did not update");
        
        $this->_commonDocument->deleteCollection($name);
    }
    
    public function testUpdateDocumentIDFields() 
    {
        $data = $this->_getDocumentData();
        $name = $this->_collectionName("testUD1");
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

    public function testUpdateDocumentIDDoc() 
    {
        $data = $this->_getDocumentData();
        $name = $this->_collectionName("testUD2");
        $this->_commonDocument->createCollection($name);
        // id is specified, fields from another doc
        $doc1 = $this->_makeDocument($data[1]);
        $this->_commonDocument->insertDocument($name, $doc1);
        $doc2 = $this->_makeDocument($data[2]);
        $this->_commonDocument->updateDocument($name, $doc1->getID(), $doc2);
        $this->_wait();
        
        $fetchdoc = $this->_commonDocument->fetchDocument($name, $doc1->getID());
        $this->assertTrue($fetchdoc instanceof Zend_Cloud_DocumentService_Document, "New document not found");
        $this->assertEquals($doc2->name, $fetchdoc->name, "Name field did not update");
        $this->assertEquals($doc2->keyword, $fetchdoc->keyword, "Keywords did not update");   

         $this->_commonDocument->deleteCollection($name);
    }
    
    public function testUpdateDocumentDoc() 
    {
        $data = $this->_getDocumentData();
        $name = $this->_collectionName("testUD3");
        $this->_commonDocument->createCollection($name);
        // id is not specified
        $doc2 = $this->_makeDocument($data[2]);
        $doc3 = new Zend_Cloud_DocumentService_Document($doc2->getID(), $this->_makeDocument($data[3])->getFields());
        $this->_commonDocument->insertDocument($name, $doc2);
        $this->_wait();
        $this->_commonDocument->updateDocument($name, null, $doc3);
        $this->_wait();
        
        $fetchdoc = $this->_commonDocument->fetchDocument($name, $doc2->getID());
        $this->assertTrue($fetchdoc instanceof Zend_Cloud_DocumentService_Document, "New document not found");
        $this->assertEquals($doc3->name, $fetchdoc->name, "Name field did not update");
        $this->assertEquals($doc3->keyword, $fetchdoc->keyword, "Keywords did not update");        
        
        $this->_commonDocument->deleteCollection($name);
    }

    protected function _loadData($name)
    {
        $data = $this->_getDocumentData();
        $this->_commonDocument->createCollection($name);
        for($i=0; $i<count($data); $i++) {
            $doc[$i] = $this->_makeDocument($data[$i]);
            $this->_commonDocument->insertDocument($name, $doc[$i]);
        }
        $this->_wait();
        return $doc;
    }
    
    public function testQueryString() 
    {
        $name = $this->_collectionName("testQuery");
        $doc = $this->_loadData($name);

        $query = $this->_queryString($name, $doc[1]->getID(), $doc[2]->getID());
        $fetchdocs = $this->_commonDocument->query($name, $query);

        $this->assertTrue(count($fetchdocs) >= 2, "Query failed to fetch 2 fields");
        foreach($fetchdocs as $fdoc) {
            $this->assertContains($fdoc["name"], array($doc[1]->name, $doc[2]->name), "Wrong name in results");
            $this->assertContains($fdoc["author"], array($doc[1]->author, $doc[2]->author), "Wrong name in results");
        }

        $this->_commonDocument->deleteCollection($name);
    }
    
    public function testQueryStruct() 
    {
        $name = $this->_collectionName("testStructQuery1");
        $doc = $this->_loadData($name);
        
        // query by ID
        $query = $this->_commonDocument->select();
        $this->assertTrue($query instanceof Zend_Cloud_DocumentService_Query);
        $query->from($name)->whereID($doc[1]->getID());
        $fetchdocs = $this->_commonDocument->query($name, $query);
        $this->assertEquals(1, count($fetchdocs));
        $fdoc = $fetchdocs[0];
        $this->assertEquals($doc[1]->name, $fdoc["name"], "Wrong name in results");
        $this->assertEquals($doc[1]->author, $fdoc["author"], "Wrong name in results");

        $this->_commonDocument->deleteCollection($name);
    }
        
    public function testQueryStructWhere() 
    {
        $name = $this->_collectionName("testStructQuery2");
        $doc = $this->_loadData($name);
        
        // query by field condition
        $query = $this->_commonDocument->select()
            ->from($name)->where("year > ?", array(1945));
        $fetchdocs = $this->_commonDocument->query($name, $query);
        $this->assertEquals(3, count($fetchdocs));
        foreach($fetchdocs as $fdoc) {
            $this->assertTrue($fdoc["year"] > 1945);
        }

        $this->_commonDocument->deleteCollection($name);
    }
    
    public function testQueryStructLimit() 
    {  
        $name = $this->_collectionName("testStructQuery3");
        $doc = $this->_loadData($name);
        
        // query with limit
        $query = $this->_commonDocument->select()
            ->from($name)->where("year > ?", array(1945))->limit(1);
        $fetchdocs = $this->_commonDocument->query($name, $query);
        $this->assertEquals(1, count($fetchdocs));
        foreach($fetchdocs as $fdoc) {
            $this->assertTrue($fdoc["year"] > 1945);
            $this->assertContains($fdoc["name"], array($doc[0]->name, $doc[2]->name, $doc[3]->name), "Wrong name in results");
        }
        
        $this->_commonDocument->deleteCollection($name);
    }

    public function testQueryStructOrder() 
    {  
        $name = $this->_collectionName("testStructQuery4");
        $doc = $this->_loadData($name);
        
        // query with sort
        $query = $this->_commonDocument->select()
            ->from($name)->where("year > ?", array(1945))->order("author");
        $fetchdocs = $this->_commonDocument->query($name, $query);
        $this->assertEquals(3, count($fetchdocs));
        $this->assertEquals($fetchdocs[0]["name"], $doc[1]->name);

        $this->_commonDocument->deleteCollection($name);
    }
    
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
    
    abstract protected function _getConfig();
    abstract protected function _getDocumentData();
    abstract protected function _queryString($domain, $s1, $s2);
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
