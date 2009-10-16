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
 * @package    Zend_Service_WindowsAzure_Http
 * @subpackage Transport
 * @version    $Id: Curl.php 22249 2009-06-18 09:49:55Z unknown $
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @see Zend_Service_WindowsAzure_Http_Transport_Exception
 */
require_once 'Zend/Service/WindowsAzure/Http/Transport/Exception.php';

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
 * @package    Zend_Service_WindowsAzure_Http
 * @subpackage Transport
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_WindowsAzure_Http_Transport_Curl extends Zend_Service_WindowsAzure_Http_Transport
{
    /**
     * Zend_Service_WindowsAzure_Http_Transport_Curl constructor
     */
    public function __construct() 
    {
        if (!extension_loaded('curl')) {
            throw new Zend_Service_WindowsAzure_Http_Transport_Exception('cURL extension has to be loaded to use Zend_Service_WindowsAzure_Http_Transport_Curl.');
        }
    }
    
    /**
     * Perform request
     * 
     * @param $httpVerb         Http verb to use in the request
     * @param $url              Url to request
     * @param $variables        Array of key-value pairs to use in the request
     * @param $headers          Array of key-value pairs to use as additional headers
     * @param $rawBody          Raw body to send to server
     * @return Zend_Service_WindowsAzure_Http_Response
     */
    public function request($httpVerb, $url, $variables = array(), $headers = array(), $rawBody = null)
    {
        // Create a new cURL instance
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_USERAGENT,       $this->_userAgent);
        curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION,  true);
        curl_setopt($curlHandle, CURLOPT_TIMEOUT,         120);

        // Set URL
        curl_setopt($curlHandle, CURLOPT_URL,             $url);
        
        // Set HTTP parameters (version and request method)
        curl_setopt($curlHandle, CURL_HTTP_VERSION_1_1,   true);
        switch ($httpVerb) {
            case Zend_Service_WindowsAzure_Http_Transport::VERB_GET:
                curl_setopt($curlHandle, CURLOPT_HTTPGET, true);
                break;
            case Zend_Service_WindowsAzure_Http_Transport::VERB_POST:
                curl_setopt($curlHandle, CURLOPT_POST,    true);
                break;
            /*case Zend_Service_WindowsAzure_Http_Transport::VERB_PUT:
                curl_setopt($curlHandle, CURLOPT_PUT,     true);
                break;*/
            case Zend_Service_WindowsAzure_Http_Transport::VERB_HEAD:
                // http://stackoverflow.com/questions/770179/php-curl-head-request-takes-a-long-time-on-some-sites
                curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST,  'HEAD');
                curl_setopt($curlHandle, CURLOPT_NOBODY, true);
                break;
            default:
                curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST,  $httpVerb);
                break;
        }

        // Clear Content-Length header
        $headers["Content-Length"] = 0;

        // Ensure headers are returned
        curl_setopt($curlHandle, CURLOPT_HEADER,          true);
        
        // Set proxy?
        if ($this->_useProxy)
        {
            curl_setopt($curlHandle, CURLOPT_PROXY,        $this->_proxyUrl); 
            curl_setopt($curlHandle, CURLOPT_PROXYPORT,    $this->_proxyPort); 
            curl_setopt($curlHandle, CURLOPT_PROXYUSERPWD, $this->_proxyCredentials); 
        }
        
        // Ensure response is returned
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER,  true);
        
        // Set post fields / raw data
        // http://www.php.net/manual/en/function.curl-setopt.php#81161
        if (!is_null($rawBody) || (!is_null($variables) && count($variables) > 0))
        {
            if (!is_null($rawBody))
            {
                unset($headers["Content-Length"]);
                $headers["Content-Length"] = strlen($rawBody);   
            }
            curl_setopt($curlHandle, CURLOPT_POSTFIELDS,  is_null($rawBody) ? $variables : $rawBody);
        }

        // Set Content-Type header if required
        if (!isset($headers["Content-Type"])) {
            $headers["Content-Type"] = '';
        }
        
        // Disable Expect: 100-Continue
        // http://be2.php.net/manual/en/function.curl-setopt.php#82418
        $headers["Expect"] = '';

        // Add additional headers to cURL instance
        $curlHeaders = array();
        foreach ($headers as $key => $value)
        {
            $curlHeaders[] = $key.': '.$value;
        }
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER,      $curlHeaders);
        
        // DEBUG: curl_setopt($curlHandle, CURLINFO_HEADER_OUT, true);
                
        // Execute request
        $rawResponse = curl_exec($curlHandle);
        $response    = null;
        if ($rawResponse)
        {
            $response = Zend_Service_WindowsAzure_Http_Response::fromString($rawResponse);
            // DEBUG: var_dump($url);  
            // DEBUG: var_dump(curl_getinfo($curlHandle,CURLINFO_HEADER_OUT));    
            // DEBUG: var_dump($rawResponse);
        }
        else
        {
            throw new Zend_Service_WindowsAzure_Http_Transport_Exception('cURL error occured during request for ' . $url . ': ' . curl_errno($curlHandle) . ' - ' . curl_error($curlHandle));
        }
        curl_close($curlHandle);
        
        return $response;
    }
}
