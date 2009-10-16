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
 * @see Zend_Service_WindowsAzure_Storage
 */
require_once 'Zend/Service/WindowsAzure/Storage.php';

/**
 * @see Zend_Service_WindowsAzure_Credentials
 */
require_once 'Zend/Service/WindowsAzure/Credentials.php';

/**
 * @see Zend_Service_WindowsAzure_Exception
 */
require_once 'Zend/Service/WindowsAzure/Exception.php';

/**
 * @see Zend_Service_WindowsAzure_Storage_Batch
 */
require_once 'Zend/Service/WindowsAzure/Storage/Batch.php';

/**
 * @see Zend_Service_WindowsAzure_Http_Transport
 */
require_once 'Zend/Service/WindowsAzure/Http/Transport.php';

/**
 * @see Zend_Service_WindowsAzure_Http_Response
 */
require_once 'Zend/Service/WindowsAzure/Http/Response.php';

/**
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage Storage
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Service_WindowsAzure_Storage_BatchStorage extends Zend_Service_WindowsAzure_Storage
{	
    /**
     * Current batch
     * 
     * @var Zend_Service_WindowsAzure_Storage_Batch
     */
    protected $_currentBatch = null;
    
    /**
     * Set current batch
     * 
     * @param Zend_Service_WindowsAzure_Storage_Batch $batch Current batch
     * @throws Zend_Service_WindowsAzure_Exception
     */
    public function setCurrentBatch(Zend_Service_WindowsAzure_Storage_Batch $batch = null)
    {
        if (!is_null($batch) && $this->isInBatch())
        {
            throw new Zend_Service_WindowsAzure_Exception('Only one batch can be active at a time.');
        }
        $this->_currentBatch = $batch;
    }
    
    /**
     * Get current batch
     * 
     * @return Zend_Service_WindowsAzure_Storage_Batch
     */
    public function getCurrentBatch()
    {
        return $this->_currentBatch;
    }
    
    /**
     * Is there a current batch?
     * 
     * @return boolean
     */
    public function isInBatch()
    {
        return !is_null($this->_currentBatch);
    }
    
    /**
     * Starts a new batch operation set
     * 
     * @return Zend_Service_WindowsAzure_Storage_Batch
     * @throws Zend_Service_WindowsAzure_Exception
     */
    public function startBatch()
    {
        return new Zend_Service_WindowsAzure_Storage_Batch($this, $this->getBaseUrl());
    }
	
	/**
	 * Perform batch using Zend_Service_WindowsAzure_Http_Transport channel, combining all batch operations into one request
	 *
	 * @param array $operations Operations in batch
	 * @param boolean $forTableStorage Is the request for table storage?
	 * @param boolean $isSingleSelect Is the request a single select statement?
	 * @param string $resourceType Resource type
	 * @param string $requiredPermission Required permission
	 * @return Zend_Service_WindowsAzure_Http_Response
	 */
	public function performBatch($operations = array(), $forTableStorage = false, $isSingleSelect = false, $resourceType = Zend_Service_WindowsAzure_Storage::RESOURCE_UNKNOWN, $requiredPermission = Zend_Service_WindowsAzure_Credentials::PERMISSION_READ)
	{
	    // Generate boundaries
	    $batchBoundary = 'batch_' . md5(time() . microtime());
	    $changesetBoundary = 'changeset_' . md5(time() . microtime());
	    
	    // Set headers
	    $headers = array();
	    
		// Add version header
		$headers['x-ms-version'] = $this->_apiVersion;
		
		// Add content-type header
		$headers['Content-Type'] = 'multipart/mixed; boundary=' . $batchBoundary;

		// Set path and query string
		$path           = '/$batch';
		$queryString    = '';
		
		// Set verb
		$httpVerb = Zend_Service_WindowsAzure_Http_Transport::VERB_POST;
		
		// Generate raw data
    	$rawData = '';
    		
		// Single select?
		if ($isSingleSelect)
		{
		    $operation = $operations[0];
		    $rawData .= '--' . $batchBoundary . "\n";
            $rawData .= 'Content-Type: application/http' . "\n";
            $rawData .= 'Content-Transfer-Encoding: binary' . "\n\n";
            $rawData .= $operation; 
            $rawData .= '--' . $batchBoundary . '--';
		} 
		else 
		{
    		$rawData .= '--' . $batchBoundary . "\n";
    		$rawData .= 'Content-Type: multipart/mixed; boundary=' . $changesetBoundary . "\n\n";
    		
        		// Add operations
        		foreach ($operations as $operation)
        		{
                    $rawData .= '--' . $changesetBoundary . "\n";
                	$rawData .= 'Content-Type: application/http' . "\n";
                	$rawData .= 'Content-Transfer-Encoding: binary' . "\n\n";
                	$rawData .= $operation;
        		}
        		$rawData .= '--' . $changesetBoundary . '--' . "\n";
    		    		    
    		$rawData .= '--' . $batchBoundary . '--';
		}

		// Generate URL and sign request
		$requestUrl     = $this->_credentials->signRequestUrl($this->getBaseUrl() . $path . $queryString, $resourceType, $requiredPermission);
		$requestHeaders = $this->_credentials->signRequestHeaders($httpVerb, $path, $queryString, $headers, $forTableStorage, $resourceType, $requiredPermission);

		$requestClient  = Zend_Service_WindowsAzure_Http_Transport::createChannel();
		if ($this->_useProxy)
		{
		    $requestClient->setProxy($this->_useProxy, $this->_proxyUrl, $this->_proxyPort, $this->_proxyCredentials);
		}
		$response = $this->_retryPolicy->execute(
		    array($requestClient, 'request'),
		    array($httpVerb, $requestUrl, array(), $requestHeaders, $rawData)
		);
		
		$requestClient = null;
		unset($requestClient);

		return $response;
	}
}
