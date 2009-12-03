<?php

/**
 * @see Zend_Service_Amazon_Abstract
 */
require_once 'Zend/Service/Amazon/Abstract.php';

/**
 * @see Zend_Service_Amazon_DevPay_Response
 */
require_once 'Zend/Service/Amazon/DevPay/Response.php';

/**
 * @see Zend_Service_Amazon_DevPay_Page
 */
require_once 'Zend/Service/Amazon/DevPay/Page.php';

/**
 * @see Zend_Service_Amazon_DevPay_Attribute
 */
require_once 'Zend/Service/Amazon/DevPay/Attribute.php';

/**
 * @see Zend_Service_Amazon_DevPay_Exception
 */
require_once 'Zend/Service/Amazon/DevPay/Exception.php';

class Zend_Service_Amazon_DevPay extends Zend_Service_Amazon_Abstract
 {
    /* Notes */
    // TODO SSL is required

    /**
     * The HTTP query server
     */
    protected $_endpoint = 'ls.amazonaws.com';

    /**
     * Period after which HTTP request will timeout in seconds
     */
    protected $_httpTimeout = 10;

    /**
     * The API version to use
     */
    protected $_sdbApiVersion = '2008-04-28';

    /**
     * Signature Version
     */
    protected $_signatureVersion = '1';

    /**
     * Signature Encoding Method
     */
    protected $_signatureMethod = 'HmacSHA256';

    /**
     * @var string Amazon Secret Key
     */
    protected $_secretKey;

    /**
     * @var string Amazon Access Key
     */
    protected $_accessKey;

    /**
     * @var boolean May be set to authenticate API calls
     */
    protected $_authenticate = true;

    /**
     * Create Amazon DevPay client.
     *
     * @param  string $access_key       Override the default Access Key
     * @param  string $secret_key       Override the default Secret Key
     * @param  string $region           Sets the AWS Region
     * @return void
     */
    public function __construct($accessKey=null, $secretKey=null, $authenticate = true)
    {
        if(!$accessKey || !$secretKey) {
            require_once 'Zend/Service/Amazon/Exception.php';
            throw new Zend_Service_Amazon_Exception("AWS keys were not supplied");
        }
        $this->_accessKey = $accessKey;
        $this->_secretKey = $secretKey;
        $this->_authenticate = $authenticate;

        $this->setEndpoint("http://" . $this->_endpoint);
    }

	/**
     * Set DevPay endpoint to use
     *
     * @param string|Zend_Uri_Http $endpoint
     * @return Zend_Service_Amazon_DevPay
     */
    public function setEndpoint($endpoint)
    {
    	if(!($endpoint instanceof Zend_Uri_Http)) {
    		$endpoint = Zend_Uri::factory($endpoint);
    	}
    	if(!$endpoint->valid()) {
    		require_once 'Zend/Service/Amazon/DevPay/Exception.php';
    		throw new Zend_Service_Amazon_DevPay_Exception("Invalid endpoint supplied");
    	}
    	$this->_endpoint = $endpoint;
    	return $this;
    }

    /**
     * Get DevPay endpoint
     *
     * @return Zend_Uri_Http
     */
    public function getEndpoint() {
    	return $this->_endpoint;
    }


    /**
     * @param $activationKey
     * @param $productToken
     * @param $tokenExpiration
     * @return array Array containing 'AWSAccessKeyId', 'SecretAccessKey', and 'UserToken'
     */
	public function activateDesktopProduct($activationKey,
	                                       $productToken,
	                                       $tokenExpiration) {}
    /**
     * @param $activationKey
     * @param $awsAccessKeyId
     * @param $tokenExpiration
     * @param $productToken
     * @return array Array containing 'PersistentIdentifier' and 'UserToken'
     */
    public function activateHostedProduct($activationKey,
                                          $awsAccessKeyId,
                                          $tokenExpiration,
                                          $productToken) {}
    /**
     * @param $awsAccessKeyId
     * @param $userToken
     * @return array Array containing one or more product codes.
     */
    public function getActiveSubscriptionsByPid($awsAccessKeyId,
                                                $persistentIdentifier) {}

    /**
     * @param $additionalTokens
     * @param $awsAccessKeyId
     * @param $userToken
     * @return string The refreshed user token
     */
    public function refreshUserToken($additionalTokens,
                                     $awsAccessKeyId,
                                     $userToken) {}
    /**
     * @param $additionalTokens
     * @param $awsAccessKeyId
     * @param $userToken
     * @return string The refreshed user token
     */
    public function verifyProductSubscriptionByPid($awsAccessKeyId,
                                                   $persistentIdentifier,
                                                   $productCode) {}

    /**
     * @param $awsAccessKeyId
     * @param $productToken
     * @param $userToken
     * @return boolean True if the user is subscribed to the product, false otherwise
     */
    public function verifyProductSubscriptionByTokens($awsAccessKeyId,
                                                      $productToken,
                                                      $userToken) {}

   /**
     * Sends a HTTP request to the DevPay service using Zend_Http_Client
     *
     * @param array $params         List of parameters to send with the request
     * @return Zend_Service_Amazon_DevPay_Response
     * @throws Zend_Service_Amazon_DevPay_Exception
     */
    protected function _sendRequest(array $params = array())
    {
        $url = 'https://' . $this->_getRegion() . $this->_endpoint . '/';

        // UTF-8 encode all parameters
        foreach($params as $name => $value) {
            unset($params[$name]);
            $params[utf8_encode($name)] = $value;

        }

        $params = $this->_addRequiredParameters($params);


        try {
            /* @var $request Zend_Http_Client */
            $request = self::getHttpClient();
            $request->resetParameters();

            $request->setConfig(array(
                'timeout' => $this->_httpTimeout
            ));


            $request->setUri($url);
            $request->setMethod(Zend_Http_Client::POST);
            $request->setEncType('application/x-www-form-urlencoded');
            $request->setParameterPost($params);

            $httpResponse = $request->request();
        } catch (Zend_Http_Client_Exception $zhce) {
            $message = 'Error in request to AWS service: ' . $zhce->getMessage();
            throw new Zend_Service_Amazon_DevPay_Exception($message, $zhce->getCode());
        }
        $response = new Zend_Service_Amazon_DevPay_Response($httpResponse);
        simplexml_import_dom($response->getDocument())->asXML();
        $request->getLastRequest();
        $this->_checkForErrors($response);
        return $response;
    }

    /**
     * Adds required authentication and version parameters to an array of
     * parameters
     *
     * The required parameters are:
     * - AWSAccessKey
     * - SignatureVersion
     * - Timestamp
     * - Version and
     * - Signature
     *
     * If a required parameter is already set in the <tt>$parameters</tt> array,
     * it is overwritten.
     *
     * @param array $parameters the array to which to add the required
     *                          parameters.
     *
     * @return array
     */
    protected function _addRequiredParameters(array $parameters)
    {
        $parameters['AWSAccessKeyId']   = $this->_getAccessKey();
        $parameters['SignatureVersion'] = $this->_signatureVersion;
        $parameters['Timestamp']        = gmdate('c');
        $parameters['Version']          = $this->_sdbApiVersion;
        $parameters['SignatureMethod']  = $this->_signatureMethod;

        // Now authenticate
        $authService                    = new Zend_Service_Amazon_Authentication_V1();
        $parameters['Signature']        = $authService->generateSignature($parameters);

        return $parameters;
    }

    /**
     * Checks for errors responses from Amazon
     *
     * @param Zend_Service_Amazon_DevPay_Response $response the response object to
     *                                                   check.
     *
     * @return void
     *
     * @throws Zend_Service_Amazon_DevPay_Exception if one or more errors are
     *         returned from Amazon.
     */
    private function _checkForErrors(Zend_Service_Amazon_DevPay_Response $response)
    {
        $xpath = new DOMXPath($response->getDocument());
        $list  = $xpath->query('//Error');
        if ($list->length > 0) {
            $node    = $list->item(0);
            $code    = $xpath->evaluate('string(Code/text())', $node);
            $message = $xpath->evaluate('string(Message/text())', $node);
            throw new Zend_Service_Amazon_DevPay_Exception($message, 0, $code);
        }
    }

    /**
     * Method to fetch the Access Key
     *
     * @return string
     */
    protected function _getAccessKey()
    {
        return $this->_accessKey;
    }

    /**
     * Method to fetch the Secret AWS Key
     *
     * @return string
     */
    protected function _getSecretKey()
    {
        return $this->_secretKey;
    }
}