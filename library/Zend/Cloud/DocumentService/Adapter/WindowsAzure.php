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

require_once 'Zend/Cloud/DocumentService/Exception.php';
require_once 'Zend/Cloud/DocumentService/DocumentService.php';
require_once 'Zend/Service/WindowsAzure/Storage/Table.php';

/**
 * SimpleDB adapter for document service.
 *
 * @category   Zend
 * @package    Zend_Cloud_DocumentService
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_DocumentService_Adapter_WindowsAzure implements Zend_Cloud_DocumentService_DocumentService
{
    /*
     * Options array keys for the Azure adapter.
     */
    const ACCOUNT_NAME = 'storage_accountname';
    const ACCOUNT_KEY = 'storage_accountkey';
    const HOST = "storage_host";
    const HTTP_ADAPTER = 'http_adapter';
    const PROXY_HOST = "storage_proxy_host";
    const PROXY_PORT = "storage_proxy_port";
    const PROXY_CREDENTIALS = "storage_proxy_credentials";

    const PARTITION_KEY = 'PartitionKey';
    const ROW_KEY = 'RowKey';
    const VERIFY_ETAG = "verify_etag";
    
    const DEFAULT_HOST = Zend_Service_WindowsAzure_Storage::URL_CLOUD_TABLE;
    /**
     * Azure  service instance.
     * 
     * @var Zend_Service_WindowsAzure_Storage_Table
     */
    protected $_storageClient;

    public function __construct($options = array()) 
    {

        // Build Zend_Service_WindowsAzure_Storage_Blob instance
		if (!isset($options[self::HOST])) {
			$host = self::DEFAULT_HOST;
		} else {
		    $host = $options[self::HOST];
		}
        if (! isset($options[self::ACCOUNT_NAME])) {
            throw new Zend_Cloud_DocumentService_Exception('No Windows Azure account name provided.');
        }
        if (! isset($options[self::ACCOUNT_KEY])) {
            throw new Zend_Cloud_DocumentService_Exception('No Windows Azure account key provided.');
        }
        // TODO: support $usePathStyleUri and $retryPolicy
        try {
            $this->_storageClient = new Zend_Service_WindowsAzure_Storage_Table(
                    $host, $options[self::ACCOUNT_NAME], $options[self::ACCOUNT_KEY]);
	        // Parse other options
	        if (! empty($options[self::PROXY_HOST])) {
	            $proxyHost = $options[self::PROXY_HOST];
	            $proxyPort = isset($options[self::PROXY_PORT]) ? $options[self::PROXY_PORT] : 8080;
	            $proxyCredentials = isset($options[self::PROXY_CREDENTIALS]) ? $options[self::PROXY_CREDENTIALS] : '';
	            $this->_storageClient->setProxy(true, $proxyHost, $proxyPort, $proxyCredentials);
	        }
	        if (isset($options[self::HTTP_ADAPTER])) {
	            $this->_storageClient->setHttpClientChannel($options[self::HTTP_ADAPTER]);
	        }
        } catch(Zend_Service_WindowsAzure_Exception $e) {
            throw new Zend_Cloud_DocumentService_Exception('Error on document service creation: '.$e->getMessage(), $e->getCode(), $e);
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
            $this->_storageClient->createTable($name);
        } catch(Zend_Service_WindowsAzure_Exception $e) {
            if(strpos($e->getMessage(), "table specified already exists") === false) {
                throw new Zend_Cloud_DocumentService_Exception('Error on collection creation: '.$e->getMessage(), $e->getCode(), $e);
            }
        }
        return true;
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
            $this->_storageClient->deleteTable($name);
        } catch(Zend_Service_WindowsAzure_Exception $e) {
            if(strpos($e->getMessage(), "does not exist") === false) {
                throw new Zend_Cloud_DocumentService_Exception('Error on collection deletion: '.$e->getMessage(), $e->getCode(), $e);
            }
        }
        return true;
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
            $tables = $this->_storageClient->listTables();
            $restables = array();
            foreach($tables as $table) {
                $restables[] = $table->name;
            }
            return $restables;
        } catch(Zend_Service_WindowsAzure_Exception $e) {
            throw new Zend_Cloud_DocumentService_Exception('Error on collection list: '.$e->getMessage(), $e->getCode(), $e);
        }

        return $tables;
    }

    /**
     * Check that $key is valid Azure document key
     * 
     * @param array $key
     * @return string
     */
    protected function _validateKey($key)
    {
       if(!is_array($key) || count($key) != 2) {
	        throw new Zend_Cloud_DocumentService_Exception('Invalid document key');
	    }
        return true;
    }
    
    /**
     * Create suitable document from array of fields
     * 
     * @param array $document
     * @return Zend_Cloud_DocumentService_Document
     */
    protected function _getDocumentFromArray($document)
    {
      if(!isset($document[Zend_Cloud_DocumentService_Document::KEY_FIELD])) {
        if(isset($document[self::ROW_KEY]) && isset($document[self::PARTITION_KEY])) { 
	        $key = array($document[self::PARTITION_KEY], $document[self::ROW_KEY]);
	        unset($document[self::ROW_KEY]);
	        unset($document[self::PARTITION_KEY]);
	    } else {
	        throw new Zend_Cloud_DocumentService_Exception('Fields array should contain the key field '.Zend_Cloud_DocumentService_Document::KEY_FIELD);
	    }
	  } else {
	      $key = $document[Zend_Cloud_DocumentService_Document::KEY_FIELD];
	      unset($document[Zend_Cloud_DocumentService_Document::KEY_FIELD]);
	   }
	   return new Zend_Cloud_DocumentService_Document($key, $document);
    }
    
    /**
     * Insert document
     *
     * @param  array|Zend_Cloud_DocumentService_Document $document
     * @param  array                 	    $options
     * @return boolean
     */
    public function insertDocument($collectionName, $document, $options = null)
    {
        if(is_array($document)) {
            $document =  $this->_getDocumentFromArray($document);
        } 
        
        if(!($document instanceof Zend_Cloud_DocumentService_Document)) {
            throw new Zend_Cloud_DocumentService_Exception('Invalid document supplied');
        }
        
        $key = $document->getID();
        
        try {
            $this->_validateKey($key);
        
            $entity = new Zend_Service_WindowsAzure_Storage_DynamicTableEntity($key[0], $key[1]);
        	$entity->setAzureValues($document->getFields(), true);
            $this->_storageClient->insertEntity($collectionName, $entity);
        } catch(Zend_Service_WindowsAzure_Exception $e) {
            throw new Zend_Cloud_DocumentService_Exception('Error on document insertion: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    
    /**
     * Replace document. 
     * 
     * The new document replaces the existing document.
     *
     * @param  Zend_Cloud_DocumentService_Document $document
     * @param  array                 		$options
     * @return boolean
     */
    public function replaceDocument($collectionName, $document, $options = null)
    {
        if(is_array($document)) {
            $document =  $this->_getDocumentFromArray($document);
        } 
        
        if(!($document instanceof Zend_Cloud_DocumentService_Document)) {
            throw new Zend_Cloud_DocumentService_Exception('Invalid document supplied');
        }
        
        $key = $document->getID();
        $this->_validateKey($key);
        try {
            $entity = new Zend_Service_WindowsAzure_Storage_DynamicTableEntity($key[0], $key[1]);
        	$entity->setAzureValues($document->getFields(), true);
        	if(isset($options[self::VERIFY_ETAG])) {
        	    $entity->setEtag($options[self::VERIFY_ETAG]);
        	}
        	
        	$this->_storageClient->updateEntity($collectionName, $entity, isset($options[self::VERIFY_ETAG]));
        } catch(Zend_Service_WindowsAzure_Exception $e) {
            throw new Zend_Cloud_DocumentService_Exception('Error on document replace: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Update document. 
     * 
     * The new document is merged the existing document.
     *
     * @param  Zend_Cloud_DocumentService_Document $document
     * @param  array                 		$options
     * @return boolean
     */
    public function updateDocument($collectionName, $documentID, $fieldset, $options = null)
    {
	    if($fieldset instanceof Zend_Cloud_DocumentService_Document) {
            if($documentID == null) {
                $documentID = $fieldset->getID();
            }
	        $fieldset = $fieldset->getFields();
	    }
	    
	    $this->_validateKey($documentID);
        try {
            $entity = new Zend_Service_WindowsAzure_Storage_DynamicTableEntity($documentID[0], $documentID[1]);
        	$entity->setAzureValues($fieldset, true);
        	if(isset($options[self::VERIFY_ETAG])) {
        	    $entity->setEtag($options[self::VERIFY_ETAG]);
        	}
        	
        	$this->_storageClient->mergeEntity($collectionName, $entity, isset($options[self::VERIFY_ETAG]));
        } catch(Zend_Service_WindowsAzure_Exception $e) {
            throw new Zend_Cloud_DocumentService_Exception('Error on document update: '.$e->getMessage(), $e->getCode(), $e);
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
        $this->_validateKey($documentID);
        try {
        	$entity = new Zend_Service_WindowsAzure_Storage_DynamicTableEntity($documentID[0], $documentID[1]);
        	if(isset($options[self::VERIFY_ETAG])) {
        	    $entity->setEtag($options[self::VERIFY_ETAG]);
        	}
            $this->_storageClient->deleteEntity($collectionName, $entity, isset($options[self::VERIFY_ETAG]));
        } catch(Zend_Service_WindowsAzure_Exception $e) {
            if(strpos($e->getMessage(), "does not exist") === false) {
                throw new Zend_Cloud_DocumentService_Exception('Error on document deletion: '.$e->getMessage(), $e->getCode(), $e);
            }
        }
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
        $this->_validateKey($documentID);
        try {
            $entity = $this->_storageClient->retrieveEntityById($collectionName, $documentID[0], $documentID[1]);
            return new Zend_Cloud_DocumentService_Document(array($entity->getPartitionKey(), $entity->getRowKey()), $this->_resolveAttributes($entity));
        } catch(Zend_Service_WindowsAzure_Exception $e) {
            if(strpos($e->getMessage(), "does not exist") !== false) {
                return false;
            }
            throw new Zend_Cloud_DocumentService_Exception('Error on document fetch: '.$e->getMessage(), $e->getCode(), $e);
        }
    }
    
    /**
     * Assemble concrete query from generic query clauses
     * 
     * @param Zend_Cloud_DocumentService_Query $query
     * @return Zend_Service_WindowsAzure_Storage_TableEntityQuery
     */
    protected function _assembleQuery(Zend_Cloud_DocumentService_Query $query)
    {
        $clauses = $query->getClauses();
        $azureSelect = new Zend_Service_WindowsAzure_Storage_TableEntityQuery();
        foreach($clauses as $clause) {
            list($name, $args) = $clause;
            switch($name) {
                case Zend_Cloud_DocumentService_Query::QUERY_FROM:
                    $azureSelect->from($args);
                    break;
                case Zend_Cloud_DocumentService_Query::QUERY_WHERE:
                    call_user_func_array(array($azureSelect, "where"), $args);
                    break;    
                case Zend_Cloud_DocumentService_Query::QUERY_WHEREID:
                    $this->_validateKey($args[0]);
                    $azureSelect->wherePartitionKey($args[0][0])->whereRowKey($args[0][1]);
                    break;    
                case Zend_Cloud_DocumentService_Query::QUERY_LIMIT:
                    $azureSelect->top($args[0]);
                    break;
                case Zend_Cloud_DocumentService_Query::QUERY_SELECT:
                    break;
                default:
                    // TODO: should we ignore unknown clauses or throw and exception?
                    require_once 'Zend/Cloud/OperationNotAvailableException.php';
                    throw new Zend_Cloud_OperationNotAvailableException("Query clause $name is not supported by Azure yet");
            }
        }
        return $azureSelect;
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
            if($query instanceof Zend_Cloud_DocumentService_Query) {
                $entities = $this->_storageClient->retrieveEntities($this->_assembleQuery($query));
            } else {
                $entities = $this->_storageClient->retrieveEntities($collectionName, $query);
            }
            $result = array();
            foreach($entities as $entity) {
                $result[] = new Zend_Cloud_DocumentService_Document(array($entity->getPartitionKey(), $entity->getRowKey()), 
                    $this->_resolveAttributes($entity));
            }
        } catch(Zend_Service_WindowsAzure_Exception $e) {
            throw new Zend_Cloud_DocumentService_Exception('Error on document query: '.$e->getMessage(), $e->getCode(), $e);
        }

        return $result;
    }
    
    /**
     * Create query statement
     *
     * @return Zend_Cloud_DocumentService_Query
     */
    public function select($fields = null)
    {
        require_once 'Zend/Cloud/DocumentService/Query.php';
        $query = new Zend_Cloud_DocumentService_Query();
        $query->select($fields);
        return $query;        
    }
    
    /**
     * Get the concrete service adapter
     * @return Zend_Service_WindowsAzure_Storage_Table
     */
    public function getAdapter()
    {
        return $this->_storageClient;
    }
    
    protected function _resolveAttributes(Zend_Service_WindowsAzure_Storage_TableEntity $entity)
    {
        $result = array();
        foreach($entity->getAzureValues() as $attr) {
            $result[$attr->Name] = $attr->Value;
        }
        return $result;
    }
    
}