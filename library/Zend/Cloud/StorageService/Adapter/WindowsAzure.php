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
 * @package    Zend_Cloud
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

require_once 'Zend/Cloud/StorageService.php';
require_once 'Zend/Cloud/OperationNotAvailableException.php';
require_once 'Zend/Service/WindowsAzure/Storage/Blob.php';
require_once 'Zend/Cloud/StorageService/Exception.php';

/**
 *
 * Windows Azure Blob Service abstraction
 * 
 * @category   Zend
 * @package    Zend_Cloud
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_StorageService_Adapter_WindowsAzure implements Zend_Cloud_StorageService
{
    const ACCOUNT_NAME = 'storage_accountname';
    const ACCOUNT_KEY = 'storage_accountkey';
    const HOST = "storage_host";
    const PROXY_HOST = "storage_proxy_host";
    const PROXY_PORT = "storage_proxy_port";
    const PROXY_CREDENTIALS = "storage_proxy_credentials";
    const CONTAINER = "storage_container";
    const HTTP_ADAPTER = 'HTTP Adapter';
    
    /**
	 * Storage container to operate on
	 * 
	 * @var string
	 */
	protected $_container;
	
	/**
	 * Storage client
	 * 
	 * @var Zend_Service_Azure_Storage_Blob
	 */
	protected $_storageClient = null;

	/**
	 * Creates a new Zend_Cloud_Storage_WindowsAzure instance
	 * 
	 * @param array  $options   Options for the Zend_Cloud_Storage_WindowsAzure instance
	 */
	public function __construct($options = array())
	{		
		// Build Zend_Service_WindowsAzure_Storage_Blob instance
		if (!isset($options[self::HOST])) {
			throw new Zend_Cloud_Storage_Exception('No Windows Azure host name provided.');
		}
		if (!isset($options[self::ACCOUNT_NAME])) {
			throw new Zend_Cloud_Storage_Exception('No Windows Azure account name provided.');
		}
		if (!isset($options[self::ACCOUNT_KEY])) {
			throw new Zend_Cloud_Storage_Exception('No Windows Azure account key provided.');
		}
			
		$this->_storageClient = new Zend_Service_WindowsAzure_Storage_Blob($options[self::HOST],
		     $options[self::ACCOUNT_NAME], $options[self::ACCOUNT_KEY]);
		
		// Parse other options
		if (!empty($options[self::PROXY_HOST]))
		{
			$proxyHost = $options[self::PROXY_HOST];
			$proxyPort = isset($options[self::PROXY_PORT]) ? $options[self::PROXY_PORT] : 8080;
			$proxyCredentials = isset($options[self::PROXY_CREDENTIALS]) ? $options[self::PROXY_CREDENTIALS] : '';
			
			$this->_storageClient->setProxy(true, $proxyHost, $proxyPort, $proxyCredentials);
		}
		
		// Set container
		$this->_container = $options[self::CONTAINER];

		if(isset($options[self::HTTP_ADAPTER])) {
            $this->_storageClient->setHttpClientChannel($httpAdapter);
		}
		
		// Make sure the container exists
		if (!$this->_storageClient->containerExists($this->_container))
		{
			$this->_storageClient->createContainer($this->_container);
		}
	}
	
    /**
     * Get an item from the storage service.
     *
     * @param  string $path
     * @param  array $options
     * @return mixed
     */
    public function fetchItem($path, $options = null)
    {
    	// Options
    	$returnType = 2; // 1: return a path, 2: return a string, 3: return a resource
    	$returnPath = tempnam('', 'azr');
    	$openMode   = 'r';
    	
    	// Parse options
    	if (is_array($options))
    	{
    		if (isset($options['returntype']))
    			$returnType = $options['returntype'];
    			
    		if (isset($options['returnpath']))
    			$returnPath = $options['returnpath'];
    			
    			if (isset($options['openmode']))
    			$openMode = $options['openmode'];
    	}
    	
    	// Fetch the blob
    	try
    	{
	    	$this->_storageClient->getBlob(
	    		$this->_container,
	    		$path,
	    		$returnPath
	    	);
    	}
    	catch (Zend_Service_WindowsAzure_Exception $e)
    	{
    		if (strpos($e->getMessage(), "does not exist") !== false)
    			return false;
    		throw $e;
    	}
    	
    	// Return value
    	if ($returnType == 1)
    		return $returnPath;
    	if ($returnType == 2)
    		return file_get_contents($returnPath);
    	if ($returnType == 3)
    		return fopen($returnPath, $openMode);
    }
    
    /**
     * Store an item in the storage service.
     * WARNING: This operation overwrites any item that is located at 
     * $destinationPath.
     * @param string $destinationPath
     * @param mixed  $data
     * @param  array $options
     * @return boolean
     */
    public function storeItem($destinationPath,
                              $data,
                              $options = null)
    {
    	// Create a temporary file that will be uploaded
    	$temporaryFilePath = '';
    	$removeTemporaryFilePath = false;
    	if (is_resource($data))
    	{
    		$temporaryFilePath = tempnam('', 'azr');
    		$fpDestination = fopen($temporaryFilePath, 'w');

    		$fpSource = $data;
    		rewind($fpSource);
			while (!feof($fpSource)) {
				fwrite($fpDestination, fread($fpSource, 8192));
			}
    		
    		fclose($fpDestination);
    		
    		$removeTemporaryFilePath = true;
    	}
    	else if (file_exists($data))
    	{
    		$temporaryFilePath = $data;
    		
    		$removeTemporaryFilePath = false;
    	}
    	else
    	{
    		$temporaryFilePath = tempnam('', 'azr');
    		file_put_contents($temporaryFilePath, $data);
    		
    		$removeTemporaryFilePath = true;
    	}
		
    	// Upload data
    	$this->_storageClient->putBlob(
    		$this->_container,
    		$destinationPath,
    		$temporaryFilePath
    	);

    	if ($removeTemporaryFilePath)
    		@unlink($temporaryFilePath);
    }
    
    /**
     * Delete an item in the storage service.
     *
     * @param  string $path
     * @param  array  $options
     * @return void
     */
    public function deleteItem($path, $options = null)
    {
    	try {
	    	$this->_storageClient->deleteBlob(
	    		$this->_container,
	    		$path
	    	);
    	}
    	catch (Zend_Service_WindowsAzure_Exception $e) { }
    }
    
    /**
     * Copy an item in the storage service to a given path.
     *
     * @param  string $sourcePath
     * @param  string $destinationPath
     * @param  array  $options
     * @return void
     */
    public function copyItem($sourcePath, $destinationPath, $options = null)
    {
    	$this->_storageClient->copyBlob(
    		$this->_container,
    		$sourcePath,
    		$this->_container,
    		$destinationPath
    	);
    }
    
    /**
     * Move an item in the storage service to a given path.
     *
     * @param  string $sourcePath
     * @param  string $destinationPath
     * @param  array  $options
     * @return void
     */
    public function moveItem($sourcePath, $destinationPath, $options = null)
    {
    	$this->_storageClient->copyBlob(
    		$this->_container,
    		$sourcePath,
    		$this->_container,
    		$destinationPath
    	);
    	
    	$this->_storageClient->deleteBlob(
    		$this->_container,
    		$sourcePath
    	);
    }
    
    /**
     * Rename an item in the storage service to a given name.
     *
     *
     * @param  string $path
     * @param  string $name
     * @param  array $options
     * @return void
     */
    public function renameItem($path, $name, $options = null)
    {
    	// TODO return $this->moveItem($path, $name, $options)
    }
    
    /**
     * List items in the given directory in the storage service
     * 
     * The $path must be a directory
     *
     *
     * @param  string $path Must be a directory
     * @param  array $options
     * @return array A list of item names
     */
    public function listItems($path, $options = null)
    {
        // Options
    	$returnType = 1; // 1: return list of paths, 2: return raw output from underlying provider
    	
    	// Parse options
    	if (is_array($options))
    	{
    		if (isset($options['returntype']))
    			$returnType = $options['returntype'];
    	}
    	
    	// Fetch list
    	$blobList = $this->_storageClient->listBlobs(
    		$this->_container,
    		$path
    	);
    	
    	// Return
    	if ($returnType == 2)
    		return $blobList;
    	
    	$returnValue = array();
    	foreach ($blobList as $blob)
    		$returnValue[] = $blob->Name;
    		
    	return $returnValue;
    }

    /**
     * Get a key/value array of metadata for the given path.
     *
     * @param  string $path
     * @param  array  $options
     * @return array
     */
    public function fetchMetadata($path, $options = null)
    {
    	try {
	    	return $this->_storageClient->getBlobMetaData(
	    		$this->_container,
	    		$path
	    	);
    	} catch (Zend_Service_WindowsAzure_Exception $e) {
    		if (strpos($e->getMessage(), "could not be accessed") !== false) {
    			return false;
    		}
    		throw $e;
    	}
    }
    
    /**
     * Store a key/value array of metadata at the given path.
     * WARNING: This operation overwrites any metadata that is located at 
     * $destinationPath.
     *
     * @param  string $destinationPath
     * @param  array $options
     * @return void
     */
    public function storeMetadata($destinationPath, $metadata, $options = null)
    {
    	try	{
    		$this->_storageClient->setBlobMetadata($this->_container, $destinationPath, $metadata);
    	} catch (Zend_Service_WindowsAzure_Exception $e) {
    		if (strpos($e->getMessage(), "could not be accessed") === false) {
    			throw $e;
    		}
    	}
    }
    
    /**
     * Delete a key/value array of metadata at the given path.
     *
     * @param  string $path
     * @param  array $options
     * @return void
     */
    public function deleteMetadata($path, $options = null)
    {
    	try
    	{
	    	$this->_storageClient->setBlobMetadata($this->_container, $destinationPath, array());
	    }
    	catch (Zend_Service_WindowsAzure_Exception $e)
    	{
    		if (strpos($e->getMessage(), "could not be accessed") === false)
    			throw $e;
    	}
    }
    
    /**
     * Delete container
     * 
     * @return void
     */
    public function deleteContainer()
    {
    	$this->_storageClient->deleteContainer($this->_container);
    }
}
