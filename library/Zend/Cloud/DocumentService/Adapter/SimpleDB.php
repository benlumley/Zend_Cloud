<?php
/**
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
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

require_once 'Zend/Service/Amazon/SimpleDB.php';
require_once 'Zend/Cloud/DocumentService/Exception.php';
require_once 'Zend/Cloud/OperationNotAvailableException.php';

/**
 * SimpleDB adapter for document service.
 *
 * @category   Zend
 * @package    Zend_Cloud_DocumentService
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_DocumentService_Adapter_SimpleDB implements Zend_Cloud_DocumentService
{
    /*
     * Options array keys for the SimpleDB adapter.
     */
    const HTTP_ADAPTER = 'HTTP Adapter';
    const MAX_NUMBER_OF_DOMAINS = 'Max Number of Domains';
    const NEXT_TOKEN = 'Next Token';
    const AWS_ACCESS_KEY = 'aws_accesskey';
    const AWS_SECRET_KEY = 'aws_secretkey';
    
    /**
     * SQS service instance.
     * @var Zend_Service_Amazon_SimpleDB
     */
    protected $_simpleDb;

    public function __construct($options = array()) 
    {

        $this->_simpleDb = new Zend_Service_Amazon_SimpleDB($options[self::AWS_ACCESS_KEY],
                                                  $options[self::AWS_SECRET_KEY]);

        if(isset($options[self::HTTP_ADAPTER])) {
            $httpAdapter = $options[self::HTTP_ADAPTER];
            $this->_sqs->getHttpClient()->setAdapter($httpAdapter);
        } 
    }

    /**
     * Create collection.
     *
     * @param  string $name
     * @param  array  $options
     * @return boolean
     */
    public function createCollection($name, $options = null) {
        try {
            $this->_simpleDb->createDomain($name);
        } catch(Zend_Service_Amazon_Exception $e) {
            throw new Zend_Cloud_DocumentService_Exception('Error on domain creation',
                                                    $previous = $e);
        }
    }

    /**
     * Delete collection.
     *
     * @param  string $name
     * @param  array  $options
     * @return boolean
     */
    public function deleteCollection($name, $options = null) {
        try {
            $this->_simpleDb->deleteDomain($name);
        } catch(Zend_Service_Amazon_Exception $e) {
            throw new Zend_Cloud_DocumentService_Exception('Error on collection deletion',
                                                    $previous = $e);
        }
    }

	/**
     * List collections.
     *
     * @param  array  $options
     * @return array
     */
    public function listCollections($options = null) {
        // TODO package this in Pages
        try {
            $domains = $this->_simpleDb->listDomains($name)->getData();
        } catch(Zend_Service_Amazon_Exception $e) {
            throw new Zend_Cloud_DocumentService_Exception('Error on collection deletion',
                                                    $previous = $e);
        }

        return $domains;
    }

    /**
     * List documents. Returns a key=>value array of document names to document objects.
     *
     * @param  array $options
     * @return array
     */
    public function listDocuments($collectionName, $options = null) {
        // TODO package this in Pages
        try {
            $attributes = $this->_simpleDb->getAttributes($collectionName)->getData();
        } catch(Zend_Service_Amazon_Exception $e) {
            throw new Zend_Cloud_DocumentService_Exception('Error on listing documents',
                                                    $previous = $e);
        }
    }

    /**
     * Insert document
     *
     * @param  Zend_Cloud_DocumentService_Document $document
     * @param  array                 	    $options
     * @return boolean
     */
    public function insertDocument($collectionName, $document, $options = null)
    {
        try {
            $this->_simpleDb->putAttributes($document->getCollection(),
                                            $document->getID(),
                                            $document->getFields());
        } catch(Zend_Service_Amazon_Exception $e) {
            throw new Zend_Cloud_DocumentService_Exception('Error on document insertion',
                                                    $previous = $e);
        }
    }

    public function replaceDocument($collectionName, $document, $options = null)
    {
    }
    
    /**
     * Update document. The new document replaces the existing document.
     *
     * @param  Zend_Cloud_DocumentService_Document $document
     * @param  array                 		$options
     * @return boolean
     */
    public function updateDocument($collectionName, $documentID, $fieldset, $options = null)
    {
        try {
            $this->_simpleDb->putAttributes($collectionName,
                                            $documentID,
                                            $fieldset,
                                            true
                                            );
        } catch(Zend_Service_Amazon_Exception $e) {
            throw new Zend_Cloud_DocumentService_Exception('Error on document update',
                                                    $previous = $e);
        }
    }

    /**
     * Delete document.
     *
     * @param  mixed  $document Document ID or Document object.
     * @param  array  $options
     * @return void
     */
    public function deleteDocument($collectionName, $documentID, $options = null)
    {
        try {
            $this->_simpleDb->deleteDomain($document->getCollection());
        } catch(Zend_Service_Amazon_Exception $e) {
            throw new Zend_Cloud_DocumentService_Exception('Error on domain deletion',
                                                    $previous = $e);
        }
    }

    public function fetchDocument($collectionName, $documentID, $options = null)
    {
        
    }
    /**
     * Query for documents stored in the document service. If a string is passed in
     * $query, the query string will be passed directly to the service.
     *
     * @param  mixed $query
     * @param  array $options
     * @return array
     */
    public function query($collectionName, $query, $options = null)
    {
        try {
            // TODO package this in Pages
            $result = $this->_simpleDb->select($query);
        } catch(Zend_Service_Amazon_Exception $e) {
            throw new Zend_Cloud_DocumentService_Exception('Error on document query',
                                                    $previous = $e);
        }

        return $domains;
    }

    /**
     * Get the concrete service adapter
     * @return Zend_Service_Amazon_SimpleDB
     */
    public function getAdapter()
    {
        return $this->_simpleDb;
    }
}