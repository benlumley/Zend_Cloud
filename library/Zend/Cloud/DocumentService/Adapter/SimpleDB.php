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
    
    const MERGE_OPTION = "merge";
    
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
    public function createCollection($name, $options = null) 
    {
        try {
            $this->_simpleDb->createDomain($name);
        } catch(Zend_Service_Amazon_Exception $e) {
            throw new Zend_Cloud_DocumentService_Exception('Error on domain creation: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Delete collection.
     *
     * @param  string $name
     * @param  array  $options
     * @return boolean
     */
    public function deleteCollection($name, $options = null) 
    {
        try {
            $this->_simpleDb->deleteDomain($name);
        } catch(Zend_Service_Amazon_Exception $e) {
            throw new Zend_Cloud_DocumentService_Exception('Error on collection deletion: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

	/**
     * List collections.
     *
     * @param  array  $options
     * @return array
     */
    public function listCollections($options = null) 
    {
        try {
            // TODO package this in Pages
            $domains = $this->_simpleDb->listDomains()->getData();
        } catch(Zend_Service_Amazon_Exception $e) {
            throw new Zend_Cloud_DocumentService_Exception('Error on collection deletion: '.$e->getMessage(), $e->getCode(), $e);
        }

        return $domains;
    }

    /**
     * List documents. Returns a key=>value array of document names to document objects.
     *
     * @param  array $options
     * @return array
     */
    public function listDocuments($collectionName, $options = null) 
    {
        // TODO package this in Pages
        try {
            $attributes = $this->_simpleDb->getAttributes($collectionName)->getData();
        } catch(Zend_Service_Amazon_Exception $e) {
            throw new Zend_Cloud_DocumentService_Exception('Error on listing documents: '.$e->getMessage(), $e->getCode(), $e);
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
            $this->_simpleDb->putAttributes($collectionName,
                                            $document->getID(),
                                            $this->_makeAttributes($document->getID(),
                                                    $document->getFields())
                                           );
        } catch(Zend_Service_Amazon_Exception $e) {
            throw new Zend_Cloud_DocumentService_Exception('Error on document insertion: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    public function replaceDocument($collectionName, $document, $options = null)
    {
        try {
            $replace = array();
            foreach($document->getFields() as $key => $value) {
                $replace[$key] = true;
            }
            $this->_simpleDb->putAttributes($collectionName,
                                            $document->getID(),
                                            $this->_makeAttributes($document->getID(),
                                                    $document->getFields()),
                                            $replace
                                            );
        } catch(Zend_Service_Amazon_Exception $e) {
            throw new Zend_Cloud_DocumentService_Exception('Error on document insertion: '.$e->getMessage(), $e->getCode(), $e);
        }
    }
    
    /**
     * Update document. The new document replaces the existing document.
     *
     * Option 'merge' specifies to add all attributes (if true) or
     * specific attributes ("attr" => true) instead of replacing them.
     * By default, attributes are replaced.   
     * 
     * @param  string $collectionName
     * @param  mixed $documentID Document ID, adapter-dependent
     * @param  array|Zend_Cloud_DocumentService_Document $fieldset Set of fields to update
     * @param  array           		$options
     * @return boolean
     */
    public function updateDocument($collectionName, $documentID, $fieldset, $options = null)
    {
        if($fieldset instanceof Zend_Cloud_DocumentService_Document) {
            if(empty($documentID)) {
                $documentID = $fieldset->getID();
            }
            $fieldset = $fieldset->getFields();
        }
        
        $replace = array();
        if(empty($options[self::MERGE_OPTION])) {
            // no merge option - we replace all
            foreach($fieldset as $key => $value) {
                    $replace[$key] = true;
            }
        } else {
            if(is_array($options[self::MERGE_OPTION])) {
	            foreach($fieldset as $key => $value) {
	                if(empty($options[self::MERGE_OPTION][$key])) {
	                    // if there's merge key, we add it, otherwise we replace it
	                    $replace[$key] = true;
	                }
	            }
            } // otherwise $replace is empty - all is merged
        }
        
        try {
            $this->_simpleDb->putAttributes($collectionName,
                                            $documentID,
                                            $this->_makeAttributes($documentID, $fieldset),
                                            $replace
                                            );
        } catch(Zend_Service_Amazon_Exception $e) {
            throw new Zend_Cloud_DocumentService_Exception('Error on document update: '.$e->getMessage(), $e->getCode(), $e);
        }
        return true;
    }

    /**
     * Delete document.
     *
     * @param  mixed  $document Document ID or Document object.
     * @param  array  $options
     * @return boolean
     */
    public function deleteDocument($collectionName, $documentID, $options = null)
    {
        try {
            $this->_simpleDb->deleteAttributes($collectionName, $documentID);
        } catch(Zend_Service_Amazon_Exception $e) {
            throw new Zend_Cloud_DocumentService_Exception('Error on document deletion: '.$e->getMessage(), $e->getCode(), $e);
        }
        return true;
    }

    /**
     * Fetch single document by ID
     * 
     * @param string $collectionName Collection name
     * @param mixed $documentID Document ID, adapter-dependent
     * @param array $options
     * @return Zend_Cloud_DocumentService_Document
     */
    public function fetchDocument($collectionName, $documentID, $options = null)
    {
        try {
            $attributes = $this->_simpleDb->getAttributes($collectionName, $documentID);
            if($attributes == false || count($attributes) == 0) {
                return false;
            }
            return new Zend_Cloud_DocumentService_Document($documentID, $this->_resolveAttributes($attributes));
        } catch(Zend_Service_Amazon_Exception $e) {
            throw new Zend_Cloud_DocumentService_Exception('Error on fetching document: '.$e->getMessage(), $e->getCode(), $e);
        }
    }
    /**
     * Query for documents stored in the document service. If a string is passed in
     * $query, the query string will be passed directly to the service.
     *
     * @param  string $collectionName Collection name
     * @param  string $query
     * @param  array $options
     * @return array Array of Zend_Cloud_DocumentService_FieldSet
     */
    public function query($collectionName, $query, $options = null)
    {
        try {
            // TODO package this in Pages
            $result = $this->_simpleDb->select($query);
            $docs = array();
            foreach($result->getData() as $item) {
                $docs[] = $this->_resolveAttributes($item);
            }
        } catch(Zend_Service_Amazon_Exception $e) {
            throw new Zend_Cloud_DocumentService_Exception('Error on document query: '.$e->getMessage(), $e->getCode(), $e);
        }

        return $docs;
    }

    /**
     * Create query statement
     * 
     * @param string $fields
     * @return Zend_Cloud_DocumentService_Query
     */
    public function select($fields = null)
    {
        require_once 'Zend/Cloud/OperationNotAvailableException.php';
            throw new Zend_Cloud_OperationNotAvailableException('No support for structured queries for SimpleDB yet');
    }
    
    /**
     * Get the concrete service adapter
     * @return Zend_Service_Amazon_SimpleDB
     */
    public function getAdapter()
    {
        return $this->_simpleDb;
    }
    
    /**
     * Convert array of key-value pairs to array of Amazon attributes
     * 
     * @param string $name
     * @param array $attributes
     * @return array
     */
    protected function _makeAttributes($name, $attributes)
    {
        $result = array();
        foreach($attributes as $key => $attr) {
            $result[] = new Zend_Service_Amazon_SimpleDB_Attribute($name, $key, $attr);
        }
        return $result;
    }
    
    /**
     * Convert array of Amazon attributes to array of key-value pairs 
     * 
     * @param array $attributes
     * @return array
     */
    protected function _resolveAttributes($attributes)
    {
        $result = array();
        foreach($attributes as $attr) {
            $value = $attr->getValues();
            if(count($value) == 0) {
                $value = null;
            } elseif(count($value) == 1) {
                $value = $value[0];
            }
            $result[$attr->getName()] = $value;
        }
        return $result;
    }
}