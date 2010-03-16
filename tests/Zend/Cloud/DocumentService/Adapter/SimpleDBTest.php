<?php
// Call Zend_Cloud_DocumentService_Adapter_SDBTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    // @TODO Switch to relative path when this is added to ZF
    require_once 'TestHelper.php';
    define("PHPUnit_MAIN_METHOD", "Zend_Cloud_DocumentService_Adapter_SDBTest::main");
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
 * @package    Zend_Cloud_DocumentService
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @see Zend_Cloud_DocumentServiceTestCase
 */
require_once 'Zend/Cloud/DocumentService/TestCase.php';

/**
 * @see Zend_Cloud_DocumenteService_Adapter_SimpleDB
 */
require_once 'Zend/Cloud/DocumentService/Adapter/SimpleDB.php';

/**
 * @category   Zend
 * @package    Zend_Cloud
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_DocumentService_Adapter_SimpleDBTest extends Zend_Cloud_DocumentService_TestCase
{
    /**
     * Period to wait for propagation in seconds
     * Should be set by adapter
     *
     * @var int
     */
    protected $_waitPeriod = 10;

	/**
     * Runs the test methods of this class.
     *
     * @access public
     * @static
     */
    public static function main()
    {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("Zend_Cloud_DocumentService_Adapter_SimpleDBTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
        return $this->main();
    }
    
    public function testUpdateDocumentMergeAll() 
    {
        $data = $this->_getDocumentData();
        $name = $this->_collectionName("testMerge");
        $this->_commonDocument->createCollection($name);

        $doc = $this->_makeDocument($data[0]);
        $this->_commonDocument->insertDocument($name, $doc);
        $doc1 = $this->_makeDocument($data[1]);
        $this->_wait();
        $this->_commonDocument->updateDocument($name, $doc->getID(), $doc1, 
            array(Zend_Cloud_DocumentService_Adapter_SimpleDB::MERGE_OPTION => true));
        $this->_wait();
        
        $fetchdoc = $this->_commonDocument->fetchDocument($name, $doc->getID());
        $this->assertTrue($fetchdoc instanceof Zend_Cloud_DocumentService_Document, "New document not found");
        $this->assertContains($doc->name, $fetchdoc->name, "Name field did not update");
        $this->assertContains($doc1->name, $fetchdoc->name, "Name field did not update");
        $this->assertContains($doc->year, $fetchdoc->year, "Year field did not update");
        $this->assertContains($doc1->year, $fetchdoc->year, "Year field did not update");
        
        $this->_commonDocument->deleteCollection($name);
    }
    
    public function testUpdateDocumentMergeSome() 
    {
        $data = $this->_getDocumentData();
        $name = $this->_collectionName("testMerge");
        $this->_commonDocument->createCollection($name);

        $doc = $this->_makeDocument($data[0]);
        $this->_commonDocument->insertDocument($name, $doc);
        $doc1 = $this->_makeDocument($data[1]);
        $this->_wait();
        $this->_commonDocument->updateDocument($name, $doc->getID(), $doc1, 
            array(Zend_Cloud_DocumentService_Adapter_SimpleDB::MERGE_OPTION => 
                array("year" => true, "pages" => true)));
        $this->_wait();
        
        $fetchdoc = $this->_commonDocument->fetchDocument($name, $doc->getID());
        $this->assertTrue($fetchdoc instanceof Zend_Cloud_DocumentService_Document, "New document not found");
        $this->assertEquals($doc1->name, $fetchdoc->name, "Name field did not update");
        $this->assertContains($doc1->pages, $fetchdoc->pages, "Page field did not update");
        $this->assertContains($doc->pages, $fetchdoc->pages, "Page field did not update");
        $this->assertContains($doc1->year, $fetchdoc->year, "Year field did not update");
        $this->assertContains($doc->year, $fetchdoc->year, "Year field did not update");
        
        $this->_commonDocument->deleteCollection($name);
    }
    
    static function getConfigArray()
    {
        return array(
                Zend_Cloud_DocumentService_Factory::DOCUMENT_ADAPTER_KEY => 'Zend_Cloud_DocumentService_Adapter_SimpleDB',
                Zend_Cloud_DocumentService_Adapter_SimpleDB::AWS_ACCESS_KEY => constant('TESTS_ZEND_SERVICE_AMAZON_ONLINE_ACCESSKEYID'),
                Zend_Cloud_DocumentService_Adapter_SimpleDB::AWS_SECRET_KEY => constant('TESTS_ZEND_SERVICE_AMAZON_ONLINE_SECRETKEY'),
            );
    } 

    protected function _getConfig() 
    {
        if (!defined('TESTS_ZEND_SERVICE_AMAZON_ONLINE_ENABLED') ||
            !constant('TESTS_ZEND_SERVICE_AMAZON_ONLINE_ENABLED') ||
            !defined('TESTS_ZEND_SERVICE_AMAZON_ONLINE_ACCESSKEYID') ||
            !defined('TESTS_ZEND_SERVICE_AMAZON_ONLINE_SECRETKEY')) {
            $this->markTestSkipped("Amazon SimpleDB access not configured, skipping test");        
        }        
        
        $config = new Zend_Config(self::getConfigArray());

        return $config;
    }
    
    protected function _getDocumentData()
    {
        return array( 
            array(
	        	parent::ID_FIELD => "0385333498",
	        	"name" =>	"The Sirens of Titan",
	        	"author" =>	"Kurt Vonnegut", 
	        	"year"	=> 1959,
	        	"pages" =>	336,
	        	"keyword" => array("Book", "Paperback")
	        	),
            array(
	        	parent::ID_FIELD => "0802131786",
	        	"name" =>	"Tropic of Cancer",
	        	"author" =>	"Henry Miller", 
	        	"year"	=> 1934,
	        	"pages" =>	318,
	        	"keyword" => array("Book")
	        	),
            array(
	        	parent::ID_FIELD => "B000T9886K",
	        	"name" =>	"In Between",
	        	"author" =>	"Paul Van Dyk", 
	        	"year"	=> 2007,
	        	"keyword" => array("CD", "Music")
	        	),
	        array(
	        	parent::ID_FIELD => "1579124585",
	        	"name" =>	"The Right Stuff",
	        	"author" =>	"Tom Wolfe", 
	        	"year"	=> 1979,
	        	"pages" =>	304,
	        	"keyword" => array("American", "Book", "Hardcover")
	        	),
        );
    }
    
    protected function _queryString($domain, $s1, $s2)
    {
        return "select * from $domain where itemName() = '$s1' OR itemName() = '$s2'";
    }
    
}
