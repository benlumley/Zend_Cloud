<?php
/**
 * Copyright (c) 2009, RealDolmen
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of RealDolmen nor the
 *       names of its contributors may be used to endorse or promote products
 *       derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY RealDolmen ''AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL RealDolmen BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage Storage
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Blob.php 14561 2009-05-07 08:05:12Z unknown $
 */

/**
 * @see Zend_Service_WindowsAzure_Credentials
 */
require_once 'Zend/Service/WindowsAzure/Credentials.php';

/**
 * @see Zend_Service_WindowsAzure_SharedKeyCredentials
 */
require_once 'Zend/Service/WindowsAzure/SharedKeyCredentials.php';

/**
 * @see Zend_Service_WindowsAzure_SharedKeyLiteCredentials
 */
require_once 'Zend/Service/WindowsAzure/SharedKeyLiteCredentials.php';

/**
 * @see Zend_Service_WindowsAzure_RetryPolicy
 */
require_once 'Zend/Service/WindowsAzure/RetryPolicy.php';

/**
 * @see Zend_Service_WindowsAzure_Http_Transport
 */
require_once 'Zend/Service/WindowsAzure/Http/Transport.php';

/**
 * @see Zend_Service_WindowsAzure_Http_Response
 */
require_once 'Zend/Service/WindowsAzure/Http/Response.php';

/**
 * @see Zend_Service_WindowsAzure_Storage
 */
require_once 'Zend/Service/WindowsAzure/Storage.php';

/**
 * @see Zend_Service_WindowsAzure_Storage_BatchStorage
 */
require_once 'Zend/Service/WindowsAzure/Storage/BatchStorage.php';

/**
 * @see Zend_Service_WindowsAzure_Storage_TableInstance
 */
require_once 'Zend/Service/WindowsAzure/Storage/TableInstance.php';

/**
 * @see Zend_Service_WindowsAzure_Storage_TableEntity
 */
require_once 'Zend/Service/WindowsAzure/Storage/TableEntity.php';

/**
 * @see Zend_Service_WindowsAzure_Storage_DynamicTableEntity
 */
require_once 'Zend/Service/WindowsAzure/Storage/DynamicTableEntity.php';

/**
 * @see Zend_Service_WindowsAzure_Storage_TableEntityQuery
 */
require_once 'Zend/Service/WindowsAzure/Storage/TableEntityQuery.php';

/**
 * @see Zend_Service_WindowsAzure_Exception
 */
require_once 'Zend/Service/WindowsAzure/Exception.php';


/**
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage Storage
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_WindowsAzure_Storage_Table extends Zend_Service_WindowsAzure_Storage_BatchStorage
{
    /**
     * ODBC connection string
     * 
     * @var string
     */
    protected $_odbcConnectionString;
    
    /**
     * ODBC user name
     * 
     * @var string
     */
    protected $_odbcUsername;
    
    /**
     * ODBC password
     * 
     * @var string
     */
    protected $_odbcPassword;
    
	/**
	 * Creates a new Zend_Service_WindowsAzure_Storage_Table instance
	 *
	 * @param string $host Storage host name
	 * @param string $accountName Account name for Windows Azure
	 * @param string $accountKey Account key for Windows Azure
	 * @param boolean $usePathStyleUri Use path-style URI's
	 * @param Zend_Service_WindowsAzure_RetryPolicy $retryPolicy Retry policy to use when making requests
	 */
	public function __construct($host = Zend_Service_WindowsAzure_Storage::URL_DEV_TABLE, $accountName = Zend_Service_WindowsAzure_Credentials::DEVSTORE_ACCOUNT, $accountKey = Zend_Service_WindowsAzure_Credentials::DEVSTORE_KEY, $usePathStyleUri = false, Zend_Service_WindowsAzure_RetryPolicy $retryPolicy = null)
	{
		parent::__construct($host, $accountName, $accountKey, $usePathStyleUri, $retryPolicy);

	    // Always use SharedKeyLite authentication
	    $this->_credentials = new Zend_Service_WindowsAzure_SharedKeyLiteCredentials($accountName, $accountKey, $this->_usePathStyleUri);
	    
	    // API version
		$this->_apiVersion = '2009-04-14';
	}
	
	/**
	 * Set ODBC connection settings - used for creating tables on development storage
	 * 
	 * @param string $connectionString  ODBC connection string
	 * @param string $username          ODBC user name
	 * @param string $password          ODBC password
	 */
	public function setOdbcSettings($connectionString, $username, $password)
	{
	    $this->_odbcConnectionString = $connectionString;
	    $this->_odbcUserame = $username;
	    $this->_odbcPassword = $password;
	}

	/**
	 * Generate table on development storage
	 * 
	 * Note 1: ODBC settings must be specified first using setOdbcSettings()
	 * Note 2: Development table storage MST BE RESTARTED after generating tables. Otherwise newly create tables will NOT be accessible!
	 * 
	 * @param string $entityClass Entity class name
	 * @param string $tableName   Table name
	 */
	public function generateDevelopmentTable($entityClass, $tableName)
	{
	    // Check if we can do this...
	    if (!function_exists('odbc_connect'))
	        throw new Zend_Service_WindowsAzure_Exception('Function odbc_connect does not exist. Please enable the php_odbc.dll module in your php.ini.');
	    if ($this->_odbcConnectionString == '')
	        throw new Zend_Service_WindowsAzure_Exception('Please specify ODBC settings first using setOdbcSettings().');
	    if ($entityClass === '')
			throw new Zend_Service_WindowsAzure_Exception('Entity class is not specified.');
			
	    // Get accessors
	    $accessors = Zend_Service_WindowsAzure_Storage_TableEntity::getAzureAccessors($entityClass);
	    
	    // Generate properties
	    $properties = array();
	    foreach ($accessors as $accessor)
	    {
	        if ($accessor->AzurePropertyName == 'Timestamp'
	            || $accessor->AzurePropertyName == 'PartitionKey'
	            || $accessor->AzurePropertyName == 'RowKey')
	            {
	                continue;
	            }

	        switch (strtolower($accessor->AzurePropertyType))
	        {
	            case 'edm.int32':
	            case 'edm.int64':
	                $sqlType = '[int] NULL'; break;
	            case 'edm.guid':
	                $sqlType = '[uniqueidentifier] NULL'; break;
	            case 'edm.datetime':
	                $sqlType = '[datetime] NULL'; break;
	            case 'edm.boolean':
	                $sqlType = '[bit] NULL'; break;
	            case 'edm.double':
	                $sqlType = '[decimal] NULL'; break;
	            default:
	                $sqlType = '[nvarchar](1000) NULL'; break;
	        }
	        $properties[] = '[' . $accessor->AzurePropertyName . '] ' . $sqlType;
	    }
	    
	    // Generate SQL
	    $sql = 'CREATE TABLE [dbo].[{tpl:TableName}](
                	{tpl:Properties} {tpl:PropertiesComma}
                	[Timestamp] [datetime] NULL,
                	[PartitionKey] [nvarchar](1000) NOT NULL,
                	[RowKey] [nvarchar](1000) NOT NULL
                );
                ALTER TABLE [dbo].[{tpl:TableName}] ADD PRIMARY KEY (PartitionKey, RowKey);';

        $sql = $this->fillTemplate($sql, array(
            'TableName'       => $tableName,
        	'Properties'      => implode(',', $properties),
        	'PropertiesComma' => count($properties) > 0 ? ',' : ''
        ));
        
        // Connect to database
        $db = @odbc_connect($this->_odbcConnectionString, $this->_odbcUserame, $this->_odbcPassword);
        if (!$db)
        {
            throw new Zend_Service_WindowsAzure_Exception('Could not connect to database via ODBC.');
        }
        
        // Create table
        odbc_exec($db, $sql);
        
        // Close connection
        odbc_close($db);
	}
	
	/**
	 * Check if a table exists
	 * 
	 * @param string $tableName Table name
	 * @return boolean
	 */
	public function tableExists($tableName = '')
	{
		if ($tableName === '')
			throw new Zend_Service_WindowsAzure_Exception('Table name is not specified.');
			
		// List tables
        $tables = $this->listTables($tableName);
        foreach ($tables as $table)
        {
            if ($table->Name == $tableName)
                return true;
        }
        
        return false;
	}
	
	/**
	 * List tables
	 *
	 * @param  string $nextTableName Next table name, used for listing tables when total amount of tables is > 1000.
	 * @return array
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function listTables($nextTableName = '')
	{
	    // Build query string
	    $queryString = '';
	    if ($nextTableName != '')
	    {
	        $queryString = '?NextTableName=' . $nextTableName;
	    }
	    
		// Perform request
		$response = $this->performRequest('Tables', $queryString, Zend_Service_WindowsAzure_Http_Transport::VERB_GET, null, true);
		if ($response->isSuccessful())
		{	    
		    // Parse result
		    $result = $this->parseResponse($response);	
		    
		    if (!$result || !$result->entry)
		        return array();
	        
		    $entries = null;
		    if (count($result->entry) > 1)
		    {
		        $entries = $result->entry;
		    } 
		    else 
		    {
		        $entries = array($result->entry);
		    }

		    // Create return value
		    $returnValue = array();		    
		    foreach ($entries as $entry)
		    {
		        $tableName = $entry->xpath('.//m:properties/d:TableName');
		        $tableName = (string)$tableName[0];
		        
		        $returnValue[] = new Zend_Service_WindowsAzure_Storage_TableInstance(
		            (string)$entry->id,
		            $tableName,
		            (string)$entry->link['href'],
		            (string)$entry->updated
		        );
		    }
		    
			// More tables?
		    if (!is_null($response->getHeader('x-ms-continuation-NextTableName')))
		    {
		        $returnValue = array_merge($returnValue, $this->listTables($response->getHeader('x-ms-continuation-NextTableName')));
		    }

		    return $returnValue;
		}
		else
		{
			throw new Zend_Service_WindowsAzure_Exception($this->getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}
	
	/**
	 * Create table
	 *
	 * @param string $tableName Table name
	 * @return Zend_Service_WindowsAzure_Storage_TableInstance
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function createTable($tableName = '')
	{
		if ($tableName === '')
			throw new Zend_Service_WindowsAzure_Exception('Table name is not specified.');
			
		// Generate request body
		$requestBody = '<?xml version="1.0" encoding="utf-8" standalone="yes"?>
                        <entry xml:base="{tpl:BaseUrl}" xmlns:d="http://schemas.microsoft.com/ado/2007/08/dataservices" xmlns:m="http://schemas.microsoft.com/ado/2007/08/dataservices/metadata" xmlns="http://www.w3.org/2005/Atom">
                          <id>{tpl:BaseUrl}/Tables(\'{tpl:TableName}\')</id>
                          <title type="text"></title>
                          <updated>{tpl:Updated}</updated>
                          <author>
                            <name />
                          </author>
                          <link rel="edit" title="Tables" href="Tables(\'{tpl:TableName}\')" />
                          <category term="{tpl:AccountName}.Tables" scheme="http://schemas.microsoft.com/ado/2007/08/dataservices/scheme" />
                          <content type="application/xml">
                            <m:properties>
                              <d:TableName>{tpl:TableName}</d:TableName>
                            </m:properties>
                          </content>
                        </entry>';
		
        $requestBody = $this->fillTemplate($requestBody, array(
            'BaseUrl' => $this->getBaseUrl(),
            'TableName' => $tableName,
        	'Updated' => $this->isoDate(),
            'AccountName' => $this->_accountName
        ));

        // Add header information
        $headers = array();
        $headers['Content-Type'] = 'application/atom+xml';

		// Perform request
		$response = $this->performRequest('Tables', '', Zend_Service_WindowsAzure_Http_Transport::VERB_POST, $headers, true, $requestBody);
		if ($response->isSuccessful())
		{
		    // Parse response
		    $entry = $this->parseResponse($response);
		    
		    $tableName = $entry->xpath('.//m:properties/d:TableName');
		    $tableName = (string)$tableName[0];
		        
		    return new Zend_Service_WindowsAzure_Storage_TableInstance(
		        (string)$entry->id,
		        $tableName,
		        (string)$entry->link['href'],
		        (string)$entry->updated
		    );
		}
		else
		{
			throw new Zend_Service_WindowsAzure_Exception($this->getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}
	
	/**
	 * Delete table
	 *
	 * @param string $tableName Table name
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function deleteTable($tableName = '')
	{
		if ($tableName === '')
			throw new Zend_Service_WindowsAzure_Exception('Table name is not specified.');

        // Add header information
        $headers = array();
        $headers['Content-Type'] = 'application/atom+xml';

		// Perform request
		$response = $this->performRequest('Tables(\'' . $tableName . '\')', '', Zend_Service_WindowsAzure_Http_Transport::VERB_DELETE, $headers, true, null);
		if (!$response->isSuccessful())
		{
			throw new Zend_Service_WindowsAzure_Exception($this->getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}
	
	/**
	 * Insert entity into table
	 * 
	 * @param string                              $tableName   Table name
	 * @param Zend_Service_WindowsAzure_Storage_TableEntity $entity      Entity to insert
	 * @return Zend_Service_WindowsAzure_Storage_TableEntity
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function insertEntity($tableName = '', Zend_Service_WindowsAzure_Storage_TableEntity $entity = null)
	{
		if ($tableName === '')
			throw new Zend_Service_WindowsAzure_Exception('Table name is not specified.');
		if (is_null($entity))
			throw new Zend_Service_WindowsAzure_Exception('Entity is not specified.');
		                     
		// Generate request body
		$requestBody = '<?xml version="1.0" encoding="utf-8" standalone="yes"?>
                        <entry xmlns:d="http://schemas.microsoft.com/ado/2007/08/dataservices" xmlns:m="http://schemas.microsoft.com/ado/2007/08/dataservices/metadata" xmlns="http://www.w3.org/2005/Atom">
                          <title />
                          <updated>{tpl:Updated}</updated>
                          <author>
                            <name />
                          </author>
                          <id />
                          <content type="application/xml">
                            <m:properties>
                              {tpl:Properties}
                            </m:properties>
                          </content>
                        </entry>';
		
        $requestBody = $this->fillTemplate($requestBody, array(
        	'Updated'    => $this->isoDate(),
            'Properties' => $this->generateAzureRepresentation($entity)
        ));

        // Add header information
        $headers = array();
        $headers['Content-Type'] = 'application/atom+xml';

		// Perform request
	    $response = null;
	    if ($this->isInBatch())
		{
		    $this->getCurrentBatch()->enlistOperation($tableName, '', Zend_Service_WindowsAzure_Http_Transport::VERB_POST, $headers, true, $requestBody);
		    return null;
		}
		else
		{
		    $response = $this->performRequest($tableName, '', Zend_Service_WindowsAzure_Http_Transport::VERB_POST, $headers, true, $requestBody);
		}
		if ($response->isSuccessful())
		{
		    // Parse result
		    $result = $this->parseResponse($response);
		    
		    $timestamp = $result->xpath('//m:properties/d:Timestamp');
		    $timestamp = (string)$timestamp[0];

		    $etag      = $result->attributes('http://schemas.microsoft.com/ado/2007/08/dataservices/metadata');
		    $etag      = (string)$etag['etag'];
		    
		    // Update properties
		    $entity->setTimestamp($timestamp);
		    $entity->setEtag($etag);

		    return $entity;
		}
		else
		{
			throw new Zend_Service_WindowsAzure_Exception($this->getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}
	
	/**
	 * Delete entity from table
	 * 
	 * @param string                              $tableName   Table name
	 * @param Zend_Service_WindowsAzure_Storage_TableEntity $entity      Entity to delete
	 * @param boolean                             $verifyEtag  Verify etag of the entity (used for concurrency)
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function deleteEntity($tableName = '', Zend_Service_WindowsAzure_Storage_TableEntity $entity = null, $verifyEtag = false)
	{
		if ($tableName === '')
			throw new Zend_Service_WindowsAzure_Exception('Table name is not specified.');
		if (is_null($entity))
			throw new Zend_Service_WindowsAzure_Exception('Entity is not specified.');
		                     
        // Add header information
        $headers = array();
        if (!$this->isInBatch()) // http://social.msdn.microsoft.com/Forums/en-US/windowsazure/thread/9e255447-4dc7-458a-99d3-bdc04bdc5474/
            $headers['Content-Type']   = 'application/atom+xml';
        $headers['Content-Length'] = 0;
        if (!$verifyEtag)
        {
            $headers['If-Match']       = '*';
        } 
        else 
        {
            $headers['If-Match']       = $entity->getEtag();
        }

		// Perform request
	    $response = null;
	    if ($this->isInBatch())
		{
		    $this->getCurrentBatch()->enlistOperation($tableName . '(PartitionKey=\'' . $entity->getPartitionKey() . '\', RowKey=\'' . $entity->getRowKey() . '\')', '', Zend_Service_WindowsAzure_Http_Transport::VERB_DELETE, $headers, true, null);
		    return null;
		}
		else
		{
		    $response = $this->performRequest($tableName . '(PartitionKey=\'' . $entity->getPartitionKey() . '\', RowKey=\'' . $entity->getRowKey() . '\')', '', Zend_Service_WindowsAzure_Http_Transport::VERB_DELETE, $headers, true, null);
		}
		if (!$response->isSuccessful())
		{
		    throw new Zend_Service_WindowsAzure_Exception($this->getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}
	
	/**
	 * Retrieve entity from table, by id
	 * 
	 * @param string $tableName    Table name
	 * @param string $partitionKey Partition key
	 * @param string $rowKey       Row key
	 * @param string $entityClass  Entity class name* 
	 * @return Zend_Service_WindowsAzure_Storage_TableEntity
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function retrieveEntityById($tableName = '', $partitionKey = '', $rowKey = '', $entityClass = 'Zend_Service_WindowsAzure_Storage_DynamicTableEntity')
	{
		if ($tableName === '')
			throw new Zend_Service_WindowsAzure_Exception('Table name is not specified.');
		if ($partitionKey === '')
			throw new Zend_Service_WindowsAzure_Exception('Partition key is not specified.');
		if ($rowKey === '')
			throw new Zend_Service_WindowsAzure_Exception('Row key is not specified.');
		if ($entityClass === '')
			throw new Zend_Service_WindowsAzure_Exception('Entity class is not specified.');

			
		// Check for combined size of partition key and row key
		// http://msdn.microsoft.com/en-us/library/dd179421.aspx
		if (strlen($partitionKey . $rowKey) >= 256)
		{
		    // Start a batch if possible
		    if ($this->isInBatch())
		        throw new Zend_Service_WindowsAzure_Exception('Entity cannot be retrieved. A transaction is required to retrieve the entity, but another transaction is already active.');
		        
		    $this->startBatch();
		}
		
		// Fetch entities from Azure
        $result = $this->retrieveEntities(
            $this->select()
                 ->from($tableName)
                 ->wherePartitionKey($partitionKey)
                 ->whereRowKey($rowKey),
            '',
            $entityClass
        );
        
        // Return
        if (count($result) == 1)
        {
            return $result[0];
        }
        
        return null;
	}
	
	/**
	 * Create a new Zend_Service_WindowsAzure_Storage_TableEntityQuery
	 * 
	 * @return Zend_Service_WindowsAzure_Storage_TableEntityQuery
	 */
	public function select()
	{
	    return new Zend_Service_WindowsAzure_Storage_TableEntityQuery();
	}
	
	/**
	 * Retrieve entities from table
	 * 
	 * @param string $tableName|Zend_Service_WindowsAzure_Storage_TableEntityQuery    Table name -or- Zend_Service_WindowsAzure_Storage_TableEntityQuery instance
	 * @param string $filter                                                Filter condition (not applied when $tableName is a Zend_Service_WindowsAzure_Storage_TableEntityQuery instance)
	 * @param string $entityClass                                           Entity class name
	 * @param string $nextPartitionKey                                      Next partition key, used for listing entities when total amount of entities is > 1000.
	 * @param string $nextRowKey                                            Next row key, used for listing entities when total amount of entities is > 1000.
	 * @return array Array of Zend_Service_WindowsAzure_Storage_TableEntity
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function retrieveEntities($tableName = '', $filter = '', $entityClass = 'Zend_Service_WindowsAzure_Storage_DynamicTableEntity', $nextPartitionKey = null, $nextRowKey = null)
	{
		if ($tableName === '')
			throw new Zend_Service_WindowsAzure_Exception('Table name is not specified.');
		if ($entityClass === '')
			throw new Zend_Service_WindowsAzure_Exception('Entity class is not specified.');

		// Convenience...
		if (class_exists($filter))
		{
		    $entityClass = $filter;
		    $filter = '';
		}
			
		// Query string
		$queryString = '';

		// Determine query
		if (is_string($tableName))
		{
		    // Option 1: $tableName is a string
		    
		    // Append parentheses
		    $tableName .= '()';
		    
    	    // Build query
    	    $query = array();
    	    
    		// Filter?
    		if ($filter !== '')
    		{
    		    $query[] = '$filter=' . rawurlencode($filter);
    		}
    		    
    	    // Build queryString
    	    if (count($query) > 0)
    	    {
    	        $queryString = '?' . implode('&', $query);
    	    }
		}
		else if (get_class($tableName) == 'Zend_Service_WindowsAzure_Storage_TableEntityQuery')
		{
		    // Option 2: $tableName is a Zend_Service_WindowsAzure_Storage_TableEntityQuery instance

		    // Build queryString
		    $queryString = $tableName->assembleQueryString(true);

		    // Change $tableName
		    $tableName = $tableName->assembleFrom(true);
		}
		else
		{
		    throw new Zend_Service_WindowsAzure_Exception('Invalid argument: $tableName');
		}
		
		// Add continuation querystring parameters?
		if (!is_null($nextPartitionKey) && !is_null($nextRowKey))
		{
		    if ($queryString !== '')
		        $queryString .= '&';
		        
		    $queryString .= '&NextPartitionKey=' . rawurlencode($nextPartitionKey) . '&NextRowKey=' . rawurlencode($nextRowKey);
		}

		// Perform request
	    $response = null;
	    if ($this->isInBatch() && $this->getCurrentBatch()->getOperationCount() == 0)
		{
		    $this->getCurrentBatch()->enlistOperation($tableName, $queryString, Zend_Service_WindowsAzure_Http_Transport::VERB_GET, array(), true, null);
		    $response = $this->getCurrentBatch()->commit();
		    
		    // Get inner response (multipart)
		    $innerResponse = $response->getBody();
		    $innerResponse = substr($innerResponse, strpos($innerResponse, 'HTTP/1.1 200 OK'));
		    $innerResponse = substr($innerResponse, 0, strpos($innerResponse, '--batchresponse'));
		    $response = Zend_Service_WindowsAzure_Http_Response::fromString($innerResponse);
		}
		else
		{
		    $response = $this->performRequest($tableName, $queryString, Zend_Service_WindowsAzure_Http_Transport::VERB_GET, array(), true, null);
		}
		if ($response->isSuccessful())
		{
		    // Parse result
		    $result = $this->parseResponse($response);
		    if (!$result)
		        return array();

		    $entries = null;
		    if ($result->entry)
		    {
    		    if (count($result->entry) > 1)
    		    {
    		        $entries = $result->entry;
    		    }
    		    else
    		    {
    		        $entries = array($result->entry);
    		    }
		    }
		    else
		    {
		        // This one is tricky... If we have properties defined, we have an entity.
		        $properties = $result->xpath('//m:properties');
		        if ($properties)
		        {
		            $entries = array($result);
		        } 
		        else
		        {
		            return array();
		        }
		    }

		    // Create return value
		    $returnValue = array();		    
		    foreach ($entries as $entry)
		    {
    		    // Parse properties
    		    $properties = $entry->xpath('.//m:properties');
    		    $properties = $properties[0]->children('http://schemas.microsoft.com/ado/2007/08/dataservices');
    		    
    		    // Create entity
    		    $entity = new $entityClass('', '');
    		    $entity->setAzureValues((array)$properties, true);
    		    
    		    // If we have a Zend_Service_WindowsAzure_Storage_DynamicTableEntity, make sure all property types are OK
    		    if ($entity instanceof Zend_Service_WindowsAzure_Storage_DynamicTableEntity)
    		    {
    		        foreach ($properties as $key => $value)
    		        {  
    		            $attributes = $value->attributes('http://schemas.microsoft.com/ado/2007/08/dataservices/metadata');
    		            $type = (string)$attributes['type'];
    		            if ($type !== '')
    		            {
    		                $entity->setAzurePropertyType($key, $type);
    		            }
    		        }
    		    }
    
    		    // Update etag
    		    $etag      = $entry->attributes('http://schemas.microsoft.com/ado/2007/08/dataservices/metadata');
    		    $etag      = (string)$etag['etag'];
    		    $entity->setEtag($etag);
    		    
    		    // Add to result
    		    $returnValue[] = $entity;
		    }

			// More entities?
		    if (!is_null($response->getHeader('x-ms-continuation-NextPartitionKey')) && !is_null($response->getHeader('x-ms-continuation-NextRowKey')))
		    {
		        if (strpos($queryString, '$top') === false)
		            $returnValue = array_merge($returnValue, $this->retrieveEntities($tableName, $filter, $entityClass, $response->getHeader('x-ms-continuation-NextPartitionKey'), $response->getHeader('x-ms-continuation-NextRowKey')));
		    }
		    
		    // Return
		    return $returnValue;
		}
		else
		{
		    throw new Zend_Service_WindowsAzure_Exception($this->getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}
	
	/**
	 * Update entity by replacing it
	 * 
	 * @param string                              $tableName   Table name
	 * @param Zend_Service_WindowsAzure_Storage_TableEntity $entity      Entity to update
	 * @param boolean                             $verifyEtag  Verify etag of the entity (used for concurrency)
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function updateEntity($tableName = '', Zend_Service_WindowsAzure_Storage_TableEntity $entity = null, $verifyEtag = false)
	{
	    return $this->changeEntity(Zend_Service_WindowsAzure_Http_Transport::VERB_PUT, $tableName, $entity, $verifyEtag);
	}
	
	/**
	 * Update entity by adding properties
	 * 
	 * @param string                              $tableName   Table name
	 * @param Zend_Service_WindowsAzure_Storage_TableEntity $entity      Entity to update
	 * @param boolean                             $verifyEtag  Verify etag of the entity (used for concurrency)
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function mergeEntity($tableName = '', Zend_Service_WindowsAzure_Storage_TableEntity $entity = null, $verifyEtag = false)
	{
	    return $this->changeEntity(Zend_Service_WindowsAzure_Http_Transport::VERB_MERGE, $tableName, $entity, $verifyEtag);
	}
	
	/**
	 * Get error message from Zend_Service_WindowsAzure_Http_Response
	 * 
	 * @param Zend_Service_WindowsAzure_Http_Response $response Repsonse
	 * @param string $alternativeError Alternative error message
	 * @return string
	 */
	protected function getErrorMessage(Zend_Service_WindowsAzure_Http_Response $response, $alternativeError = 'Unknwon error.')
	{
		$response = $this->parseResponse($response);
		if ($response && $response->message)
		    return (string)$response->message;
		else
		    return $alternativeError;
	}
	
	/**
	 * Update entity / merge entity
	 * 
	 * @param string                              $httpVerb    HTTP verb to use (PUT = update, MERGE = merge)
	 * @param string                              $tableName   Table name
	 * @param Zend_Service_WindowsAzure_Storage_TableEntity $entity      Entity to update
	 * @param boolean                             $verifyEtag  Verify etag of the entity (used for concurrency)
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	protected function changeEntity($httpVerb = Zend_Service_WindowsAzure_Http_Transport::VERB_PUT, $tableName = '', Zend_Service_WindowsAzure_Storage_TableEntity $entity = null, $verifyEtag = false)
	{
		if ($tableName === '')
			throw new Zend_Service_WindowsAzure_Exception('Table name is not specified.');
		if (is_null($entity))
			throw new Zend_Service_WindowsAzure_Exception('Entity is not specified.');
		                     
        // Add header information
        $headers = array();
        $headers['Content-Type']   = 'application/atom+xml';
        $headers['Content-Length'] = 0;
        if (!$verifyEtag) 
        {
            $headers['If-Match']       = '*';
        } 
        else 
        {
            $headers['If-Match']       = $entity->getEtag();
        }

	    // Generate request body
		$requestBody = '<?xml version="1.0" encoding="utf-8" standalone="yes"?>
                        <entry xmlns:d="http://schemas.microsoft.com/ado/2007/08/dataservices" xmlns:m="http://schemas.microsoft.com/ado/2007/08/dataservices/metadata" xmlns="http://www.w3.org/2005/Atom">
                          <title />
                          <updated>{tpl:Updated}</updated>
                          <author>
                            <name />
                          </author>
                          <id />
                          <content type="application/xml">
                            <m:properties>
                              {tpl:Properties}
                            </m:properties>
                          </content>
                        </entry>';
		
        $requestBody = $this->fillTemplate($requestBody, array(
        	'Updated'    => $this->isoDate(),
            'Properties' => $this->generateAzureRepresentation($entity)
        ));

        // Add header information
        $headers = array();
        $headers['Content-Type'] = 'application/atom+xml';
	    if (!$verifyEtag) 
	    {
            $headers['If-Match']       = '*';
        } 
        else 
        {
            $headers['If-Match']       = $entity->getEtag();
        }
        
		// Perform request
		$response = null;
	    if ($this->isInBatch())
		{
		    $this->getCurrentBatch()->enlistOperation($tableName . '(PartitionKey=\'' . $entity->getPartitionKey() . '\', RowKey=\'' . $entity->getRowKey() . '\')', '', $httpVerb, $headers, true, $requestBody);
		    return null;
		}
		else
		{
		    $response = $this->performRequest($tableName . '(PartitionKey=\'' . $entity->getPartitionKey() . '\', RowKey=\'' . $entity->getRowKey() . '\')', '', $httpVerb, $headers, true, $requestBody);
		}
		if ($response->isSuccessful())
		{
		    // Update properties
			$entity->setEtag($response->getHeader('Etag'));
			$entity->setTimestamp($response->getHeader('Last-modified'));

		    return $entity;
		}
		else
		{
			throw new Zend_Service_WindowsAzure_Exception($this->getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}
	
	/**
	 * Generate RFC 1123 compliant date string
	 * 
	 * @return string
	 */
	protected function rfcDate()
	{
	    return gmdate('D, d M Y H:i:s', time()) . ' GMT'; // RFC 1123
	}
	
	/**
	 * Fill text template with variables from key/value array
	 * 
	 * @param string $templateText Template text
	 * @param array $variables Array containing key/value pairs
	 * @return string
	 */
	protected function fillTemplate($templateText, $variables = array())
	{
	    foreach ($variables as $key => $value)
	    {
	        $templateText = str_replace('{tpl:' . $key . '}', $value, $templateText);
	    }
	    return $templateText;
	}
	
	/**
	 * Generate Azure representation from entity (creates atompub markup from properties)
	 * 
	 * @param Zend_Service_WindowsAzure_Storage_TableEntity $entity
	 * @return string
	 */
	protected function generateAzureRepresentation(Zend_Service_WindowsAzure_Storage_TableEntity $entity = null)
	{
		// Generate Azure representation from entity
		$azureRepresentation = array();
		$azureValues         = $entity->getAzureValues();
		foreach ($azureValues as $azureValue)
		{
		    $value = array();
		    $value[] = '<d:' . $azureValue->Name;
		    if ($azureValue->Type != '')
		        $value[] = ' m:type="' . $azureValue->Type . '"';
		    if (is_null($azureValue->Value))
		        $value[] = ' m:null="true"'; 
		    $value[] = '>';
		    
		    if (!is_null($azureValue->Value))
		    {
		        if (strtolower($azureValue->Type) == 'edm.boolean')
		        {
		            $value[] = ($azureValue->Value == true ? '1' : '0');
		        }
		        else
		        {
		            $value[] = $azureValue->Value;
		        }
		    }
		    
		    $value[] = '</d:' . $azureValue->Name . '>';
		    $azureRepresentation[] = implode('', $value);
		}

		return implode('', $azureRepresentation);
	}
}
