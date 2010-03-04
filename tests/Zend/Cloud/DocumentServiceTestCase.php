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

    protected $_dummyCollectionNamePrefix = '/TestCollection';

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

    public function testDocumentService()
    {
        $this->assertTrue($this->_commonDocument instanceof Zend_Cloud_DocumentService); 
    } 
    
    
    public function testCreateCollection() {}

    public function testDeleteCollection() {}

    public function testListCollections() {}

    public function testInsertDocument() {}

    public function testUpdateDocument() {}

    public function testReplaceDocument() {}
    
    public function testDeleteDocument(){}

    public function testQuery() {}

    protected function _wait() {
        sleep($this->_waitPeriod);
    }
    
    public function setUp() 
    {
        $this->_config = $this->_getConfig();
        $this->_commonDocument = Zend_Cloud_DocumentService_Factory::getAdapter($this->_config);
    } 
    
    abstract protected function _getConfig();
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
