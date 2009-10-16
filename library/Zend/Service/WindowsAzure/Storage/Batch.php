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
 * @version    $Id: Storage.php 21617 2009-06-12 10:46:31Z unknown $
 */

/**
 * @see Zend_Service_WindowsAzure_Exception
 */
require_once 'Zend/Service/WindowsAzure/Exception.php';

/**
 * @see Zend_Service_WindowsAzure_Storage_BatchStorage
 */
require_once 'Zend/Service/WindowsAzure/Storage/BatchStorage.php';

/**
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage Storage
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_WindowsAzure_Storage_Batch
{	
    /**
     * Storage client the batch is defined on
     * 
     * @var Zend_Service_WindowsAzure_Storage_BatchStorage
     */
    protected $_storageClient = null;
    
    /**
     * For table storage?
     * 
     * @var boolean
     */
    protected $_forTableStorage = false;
    
    /**
     * Base URL
     * 
     * @var string
     */
    protected $_baseUrl;
    
    /**
     * Pending operations
     * 
     * @var unknown_type
     */
    protected $_operations = array();
    
    /**
     * Does the batch contain a single select?
     * 
     * @var boolean
     */
    protected $_isSingleSelect = false;
    
    /**
     * Creates a new Zend_Service_WindowsAzure_Storage_Batch
     * 
     * @param Zend_Service_WindowsAzure_Storage_BatchStorage $storageClient Storage client the batch is defined on
     */
    public function __construct(Zend_Service_WindowsAzure_Storage_BatchStorage $storageClient = null, $baseUrl = '')
    {
        $this->_storageClient = $storageClient;
        $this->_baseUrl = $baseUrl;
        $this->beginBatch();
    }
    
	/**
	 * Get base URL for creating requests
	 *
	 * @return string
	 */
	public function getBaseUrl()
	{
		return $this->_baseUrl;
	}
    
    /**
     * Starts a new batch operation set
     * 
     * @throws Zend_Service_WindowsAzure_Exception
     */
    protected function beginBatch()
    {
        $this->_storageClient->setCurrentBatch($this);
    }
    
    /**
     * Cleanup current batch
     */
    protected function clean()
    {
        unset($this->_operations);
        $this->_storageClient->setCurrentBatch(null);
        $this->_storageClient = null;
        unset($this);
    }

	/**
	 * Enlist operation in current batch
	 *
	 * @param string $path Path
	 * @param string $queryString Query string
	 * @param string $httpVerb HTTP verb the request will use
	 * @param array $headers x-ms headers to add
	 * @param boolean $forTableStorage Is the request for table storage?
	 * @param mixed $rawData Optional RAW HTTP data to be sent over the wire
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function enlistOperation($path = '/', $queryString = '', $httpVerb = Zend_Service_WindowsAzure_Http_Transport::VERB_GET, $headers = array(), $forTableStorage = false, $rawData = null)
	{
	    // Set _forTableStorage
	    if ($forTableStorage)
	    {
	        $this->_forTableStorage = true;
	    }
	    
	    // Set _isSingleSelect
	    if ($httpVerb == Zend_Service_WindowsAzure_Http_Transport::VERB_GET)
	    {
	        if (count($this->_operations) > 0)
	            throw new Zend_Service_WindowsAzure_Exception("Select operations can only be performed in an empty batch transaction.");
	        $this->_isSingleSelect = true;
	    }
	    
	    // Clean path
		if (strpos($path, '/') !== 0) 
			$path = '/' . $path;
			
		// Clean headers
		if (is_null($headers))
		    $headers = array();
		    
		// URL encoding
		$path           = Zend_Service_WindowsAzure_Storage::urlencode($path);
		$queryString    = Zend_Service_WindowsAzure_Storage::urlencode($queryString);

		// Generate URL
		$requestUrl     = $this->getBaseUrl() . $path . $queryString;
		
		// Generate $rawData
		if (is_null($rawData))
		    $rawData = '';
		    
		// Add headers
		if ($httpVerb != Zend_Service_WindowsAzure_Http_Transport::VERB_GET)
		{
    		$headers['Content-ID'] = count($this->_operations) + 1;
    		if ($httpVerb != Zend_Service_WindowsAzure_Http_Transport::VERB_DELETE)
    		    $headers['Content-Type'] = 'application/atom+xml;type=entry';
    		$headers['Content-Length'] = strlen($rawData);
		}
		    
		// Generate $operation
		$operation = '';
		$operation .= $httpVerb . ' ' . $requestUrl . ' HTTP/1.1' . "\n";
		foreach ($headers as $key => $value)
		{
		    $operation .= $key . ': ' . $value . "\n";
		}
		$operation .= "\n";
		
		// Add data
		$operation .= $rawData;

		// Store operation
		$this->_operations[] = $operation;	        
	}
    
    /**
     * Commit current batch
     * 
     * @return Zend_Service_WindowsAzure_Http_Response
     * @throws Zend_Service_WindowsAzure_Exception
     */
    public function commit()
    {
        // Perform batch
        $response = $this->_storageClient->performBatch($this->_operations, $this->_forTableStorage, $this->_isSingleSelect);
        
        // Dispose
        $this->clean();
        
        // Parse response
        $errors = null;
        preg_match_all('/<message (.*)>(.*)<\/message>/', $response->getBody(), $errors);
        
        // Error?
        if (count($errors[2]) > 0)
        {
            throw new Zend_Service_WindowsAzure_Exception('An error has occured while committing a batch: ' . $errors[2][0]);
        }
        
        // Return
        return $response;
    }
    
    /**
     * Rollback current batch
     */
    public function rollback()
    {
        // Dispose
        $this->clean();
    }
    
    /**
     * Get operation count
     * 
     * @return integer
     */
    public function getOperationCount()
    {
        return count($this->_operations);
    }
    
    /**
     * Is single select?
     * 
     * @return boolean
     */
    public function isSingleSelect()
    {
        return $this->_isSingleSelect;
    }
}
