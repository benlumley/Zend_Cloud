<?php
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
 * @package    Zend_Service_WindowsAzure
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: SharedKeyCredentials.php 28585 2009-09-07 12:12:56Z unknown $
 */

/**
 * @see Zend_Service_WindowsAzure_Credentials
 */
require_once 'Zend/Service/WindowsAzure/Credentials.php';

/**
 * @see Zend_Service_WindowsAzure_Storage
 */
require_once 'Zend/Service/WindowsAzure/Storage.php';

/**
 * @see Zend_Service_WindowsAzure_Http_Transport
 */
require_once 'Zend/Service/WindowsAzure/Http/Transport.php';

/**
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */ 
class Zend_Service_WindowsAzure_SharedKeyCredentials extends Zend_Service_WindowsAzure_Credentials
{
    /**
	 * Sign request URL with credentials
	 *
	 * @param string $requestUrl Request URL
	 * @param string $resourceType Resource type
	 * @param string $requiredPermission Required permission
	 * @return string Signed request URL
	 */
	public function signRequestUrl($requestUrl = '', $resourceType = Zend_Service_WindowsAzure_Storage::RESOURCE_UNKNOWN, $requiredPermission = Zend_Service_WindowsAzure_Credentials::PERMISSION_READ)
	{
	    return $requestUrl;
	}
	
	/**
	 * Sign request headers with credentials
	 *
	 * @param string $httpVerb HTTP verb the request will use
	 * @param string $path Path for the request
	 * @param string $queryString Query string for the request
	 * @param array $headers x-ms headers to add
	 * @param boolean $forTableStorage Is the request for table storage?
	 * @param string $resourceType Resource type
	 * @param string $requiredPermission Required permission
	 * @return array Array of headers
	 */
	public function signRequestHeaders($httpVerb = Zend_Service_WindowsAzure_Http_Transport::VERB_GET, $path = '/', $queryString = '', $headers = null, $forTableStorage = false, $resourceType = Zend_Service_WindowsAzure_Storage::RESOURCE_UNKNOWN, $requiredPermission = Zend_Service_WindowsAzure_Credentials::PERMISSION_READ)
	{
		// http://github.com/sriramk/winazurestorage/blob/214010a2f8931bac9c96dfeb337d56fe084ca63b/winazurestorage.py

		// Determine path
		if ($this->_usePathStyleUri)
			$path = substr($path, strpos($path, '/'));

		// Determine query
		$queryString = $this->prepareQueryStringForSigning($queryString);
	
		// Canonicalized headers
		$canonicalizedHeaders = array();
		
		// Request date
		$requestDate = '';
		if (isset($headers[self::PREFIX_STORAGE_HEADER . 'date']))
		{
		    $requestDate = $headers[self::PREFIX_STORAGE_HEADER . 'date'];
		}
		else 
		{
		    $requestDate = gmdate('D, d M Y H:i:s', time()) . ' GMT'; // RFC 1123
		    $canonicalizedHeaders[] = self::PREFIX_STORAGE_HEADER . 'date:' . $requestDate;
		}
		
		// Build canonicalized headers
		if (!is_null($headers))
		{
			foreach ($headers as $header => $value) {
				if (is_bool($value))
					$value = $value === true ? 'True' : 'False';

				$headers[$header] = $value;
				if (substr($header, 0, strlen(self::PREFIX_STORAGE_HEADER)) == self::PREFIX_STORAGE_HEADER)
				    $canonicalizedHeaders[] = strtolower($header) . ':' . $value;
			}
		}
		sort($canonicalizedHeaders);

		// Build canonicalized resource string
		$canonicalizedResource  = '/' . $this->_accountName;
		if ($this->_usePathStyleUri)
			$canonicalizedResource .= '/' . $this->_accountName;
		$canonicalizedResource .= $path;
		if ($queryString !== '')
		    $canonicalizedResource .= $queryString;

		// Create string to sign   
		$stringToSign = array();
		$stringToSign[] = strtoupper($httpVerb); 	// VERB
    	$stringToSign[] = "";						// Content-MD5
    	$stringToSign[] = "";						// Content-Type
    	$stringToSign[] = "";
        // Date already in $canonicalizedHeaders
    	// $stringToSign[] = self::PREFIX_STORAGE_HEADER . 'date:' . $requestDate; // Date
    	
    	if (!$forTableStorage && count($canonicalizedHeaders) > 0)
    		$stringToSign[] = implode("\n", $canonicalizedHeaders); // Canonicalized headers
    		
    	$stringToSign[] = $canonicalizedResource;		 			// Canonicalized resource
    	$stringToSign = implode("\n", $stringToSign);
    	$signString = base64_encode(hash_hmac('sha256', $stringToSign, $this->_accountKey, true));

    	// Sign request
    	$headers[self::PREFIX_STORAGE_HEADER . 'date'] = $requestDate;
    	$headers['Authorization'] = 'SharedKey ' . $this->_accountName . ':' . $signString;
    	
    	// Return headers
    	return $headers;
	}
}
