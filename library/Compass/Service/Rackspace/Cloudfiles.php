<?php

/**
* PHP API class for Rackspace Cloudfiles
* Created by Ixpleo for Compass Web Publisher
* http://www.compasswebpublisher.com
* http://www.ixpleo.com
*
* Links back are always appreciated.
* http://www.compasswebpublisher.com/php/rackspace-cloudfiles-php-api
*
* Licensed under the BSD http://creativecommons.org/licenses/BSD/
*
* API requires the Zend Framework to work
* http://framework.zend.com for download instructions
* Check the Zend Framework license (http://framework.zend.com/license/new-bsd) for their license restrictions.
*
*/

/**
 * @see Zend_Version
 */
require_once 'Zend/Version.php';

/**   
 * @see Zend_Service_Abstract
 */
require_once 'Zend/Service/Abstract.php';

final class Compass_Service_Rackspace_Cloudfiles extends Zend_Service_Abstract
{
	/**
	 * Cloudfiles username
	 *
	 * @var null|string
	 */
	private $_user = null;
	
	/**
	 * Cloudfiles api key
	 *
	 * @var null|string
	 */
	private $_authKey = null;
	
	/**
	 * Cloudfiles username set via static call
	 *
	 * @var null|string
	 */
	private static $_defaultUser = null;
	
	/**
	 * Cloudfiles api key set via static call
	 *
	 * @var null|string
	 */
	private static $_defaultAuthKey = null;
	
	/**
	 * Cloudfiles auth token used to make api calls
	 *
	 * @var null|string
	 */
	private $_authToken = null;
	
	/**
	 * List of urls used for api calls
	 *
	 * @var array
	 */
	private $_urls = array();
	
	/* Constants */
	const CF_URL_AUTH 					= 	'https://api.mosso.com/auth';
	const CF_ERR_CONTAINER_NOT_FOUND	=	'Container does not exist';
	const CF_ERR_CONTAINER_NOT_EMPTY	=	'Container is not empty';
	const CF_ERR_AUTH_FAILED			=	'Invalid acount or access key';
	const CF_META_PREFIX				=	'X-Object-Meta-';
	
	public function __construct($user = false, $authKey = false)
	{
		if ($user && $authKey) {
			$this->_user = $user;
			$this->_authKey = $authKey;
		} else {
			$this->_user = self::$_defaultUser;
			$this->_authKey = self::$_defaultAuthKey;
		}
	}
	
	/**
	 * Attempts to authenticate you against Cloudfiles and sets class variables for your auth token and url
	 *
	 * @throws Compass_Service_Rackspace_Cloudfiles_Exception
	 * @return void
	 */
	public function auth()
	{
		$client = self::getHttpClient();
		$client->setConfig(array(
			'strict'		=>	false,
			'adapter'		=>	'Zend_Http_Client_Adapter_Socket',
			'ssltransport'	=>	'tls'
		));
		$client->setHeaders(array(
			'X-Auth-User'	=>	$this->_user,
			'X-Auth-Key'	=>	$this->_authKey
		));
		$client->setUri(self::CF_URL_AUTH);
		
		$response = $client->request();
        if ($response->getStatus() != 204) {
			/**
             * @see Compass_Service_Rackspace_Cloudfiles_Exception
             */
            require_once 'Compass/Service/Rackspace/Cloudfiles/Exception.php';
			throw new Compass_Service_Rackspace_Cloudfiles_Exception(self::CF_ERR_AUTH_FAILED);
		}

		// Set Token
		$this->_authToken = $response->getHeader('X-auth-token');

		// Set Urls
		$this->_setUrl('storage', $response->getHeader('X-storage-url'))
			 ->_setUrl('cdn', $response->getHeader('X-cdn-management-url'));
	}
	
	public function getAuthToken()
	{
		return $this->_authToken;
	}

	/**
	 * Sets username and authkey used to authenticate with Cloudfiles
	 *
	 * @param string $user
	 * @param string $authKey
	 * @return void
	 */
	public static function setKeys($user, $authKey)
	{
		self::$_defaultUser = $user;
		self::$_defaultAuthKey = $authKey;
	}
	
	/**
	 * Sets a url used for the api that is given by cloudfiles upon authentication
	 *
	 * @param string the name of the url referenced
	 * @param string the url
	 * @return Compass_Service_Rackspace_Cloudfiles
	 */
	private function _setUrl($name, $value)
	{
		$this->_urls[$name] = $value;
		
		return $this;
	}
	
	/**
	 * Gets a url used for the api based on its name
	 *
	 * @param string the name of the url referenced
	 * @return string|false the url on success or boolean false if not found
	 */
	private function _getUrl($name)
	{
		if (isset($this->_urls[$name])) {
			return $this->_urls[$name];
		}
		
		return false;
	}
	
	/**
     * Add a new container
     *
     * @param  string $container
     * @return boolean
     */
    public function createContainer($container)
    {
        $len = strlen($container);
        if ($len < 3 || $len > 255) {
            /**
             * @see Compass_Service_Rackspace_Exception
             */
            require_once 'Compass/Service/Rackspace/Cloudfiles/Exception.php';
            throw new Compass_Service_Rackspace_Cloudfiles_Exception("Container name \"$container\" must be between 3 and 255 characters long");
        }

        if (preg_match('/[^a-z0-9\._-]/', $container)) {
            /**
             * @see Compass_Service_Rackspace_Exception
             */
            require_once 'Compass/Service/Rackspace/Cloudfiles/Exception.php';
            throw new Compass_Service_Rackspace_Cloudfiles_Exception("Container name \"$container\" contains invalid characters");
        }

        if (preg_match('/(\d+).(\d+).(\d+).(\d+)/', $container)) {
            /**
             * @see Compass_Service_Rackspace_Exception
             */
            require_once 'Compass/Service/Rackspace/Cloudfiles/Exception.php';
            throw new Compass_Service_Rackspace_Cloudfiles_Exception("Container name \"$container\" cannot be an IP address");
        }

        $response = $this->_makeRequest($this->_getUrl('storage'), 'PUT', $container);

		// Response of 202 means container already existed
        return ($response->getStatus() == 201);
    }

	/**
	 * Removes an empty container
	 *
	 * @return bool
	 */
	public function removeContainer($container)
	{
		$response = $this->_makeRequest($this->_getUrl('storage'), 'DELETE', $container);
		
		return ($response->getStatus() == 204);
	}

	/**
	 * Grabs info on an object
	 *
	 * @param string object name prefixed with the container name
	 * @return array|false
	 * 
	 * TODO: Grab metadata
	 */
	public function getInfo($object) 
	{
		$response = $this->_makeRequest($this->_getUrl('storage'), 'HEAD', $object);
		
		if ($response->getStatus() == 204) {
			return array(
				'type'		=>		$response->getHeader('Content-Type'),
				'size'		=>		$response->getHeader('Content-Length'),
				'mtime'		=>		strtotime($response->getHeader('Last-Modified')),
				'etag'		=>		$response->getHeader('ETag')
			);
		}
	
		return false;
	}

	/**
	 * List the Cloudfiles containers
	 *
	 * @param int $limit A limit to the number of containers Cloudfiles should return
	 * @param string $marker A marker indicating where Cloudfiles should start when returning the list of containers
	 * @return array|false
	 */
	public function getContainers($limit = null, $marker = null)
	{
		$params = array();
		if (!is_null($limit) && is_int($limit)) {
			$params['limit'] = $limit;
		}
		if (!is_null($marker) && is_string($marker)) {
			$params['marker'] = $marker;
		}
		
		$response = $this->_makeRequest($this->_getUrl('storage'), 'GET', '', $params);
		
		if ($response->getStatus() == 200) {
			return preg_split('/(\r\n|\n)/', $response->getBody(), -1, PREG_SPLIT_NO_EMPTY);
		}
		
		return false;
	}
	
	/**
     * Remove all objects in the container.
     *
     * @param string $container
     * @return boolean
     */
	public function cleanContainer($container, $params=array())
	{
		$objects = $this->getObjectsByContainer($container, $params);
        if (!$objects) {
            return false;
        }

        foreach ($objects as $object) {
            $this->removeObject("$container/$object");
        }
        return true;
	}

	/**
	 * List objects in a Cloudfiles container filtered by options parameters
	 *
	 * @param string $container Container name
	 * @param array $params optional params for filtering the container
	 *		Options include limit, marker, prefix, format, and path
	 * @return array|false
	 */
	public function getObjectsByContainer($container, $params=array()) 
	{
		$response = $this->_makeRequest($this->_getUrl('storage'), 'GET', $container, $params);
		
		if ($response->getStatus() != 200) {
			return false;
		}
		
		return preg_split('/(\r\n|\n)/', $response->getBody(), -1, PREG_SPLIT_NO_EMPTY);
	}

	/**
	 * Grab an objects data
	 *
	 * @param string object name prefixed with the container
	 * @return string|false
	 */
	public function getObject($object)
	{
		$response = $this->_makeRequest($this->_getUrl('storage'), 'GET', $object);

        if ($response->getStatus() != 200) {
            return false;
        }

        return $response->getBody();
	}

	/**
	 * Create on object on Cloudfiles
	 *
	 * @param string object name prefixed with the container
	 * @param string the object's contents/data
	 * @param null|array optional headers
	 * @return bool
	 */
	public function putObject($object, $data, $meta=null) 
	{
		$headers = is_array($meta) ? $meta : array();
		

    if(!is_resource($data)) {
      $eTagSource = md5($data);
      $headers['ETag'] = $eTagSource;
    }

       //$headers['Content-MD5'] = base64_encode(md5($data, true));
        $headers['Expect'] = '100-continue';

        if (!isset($headers['Content-Type'])) {
            $headers['Content-Type'] = self::getMimeType($object);
        }
      if (!isset($headers['Content-Length']) && !is_resource($data)) {
        $headers['Content-Length'] = strlen($data);
      }

        $response = $this->_makeRequest($this->_getUrl('storage'), 'PUT', $object, null, $headers, $data);

        // Check the MD5 Etag returned by Cloudfiles against and MD5 of the buffer
        if ($response->getStatus() == 201) {
            // It is escaped by double quotes for some reason
            $etag = str_replace('"', '', $response->getHeader('ETag'));

            if ($etag == $eTagSource) {
                return true;
            }
        }

        return false;
	}

	/**
	 * Copy a file from local filesystem to Cloudfiles
	 *
	 * @param string the absolute path to the file locally
	 * @param string the desired object name prefixed with the container
	 * @param null|array optional headers
	 * @return bool
	 */
	public function putFile($path, $object, $meta=null) 
	{
		$data = @file_get_contents($path);
        if ($data === false) {
            /**
             * @see Compass_Service_Rackspace_Cloudfiles_Exception
             */
            require_once 'Compass/Service/Rackspace/Cloudfiles/Exception.php';
            throw new Compass_Service_Rackspace_Cloudfiles_Exception("Cannot read file $path");
        }
		
		return $this->putObject($object, $data, $meta);
	}

	/**
	 * Remove an object from Cloudfiles
	 *
	 * @param string the object name prefixed with the container
	 * @return bool
	 */
	public function removeObject($object) 
	{
		$response = $this->_makeRequest($this->_getUrl('storage'), 'DELETE', $object);
		
		return ($response->getStatus() == 204);
	}

	/**
	 * Return information about your Cloufiles account.
	 *
	 * @return array an array containing two keys: 0 => the number of containers, and 2 => the total bytes used
	 */
	public function accountInfo() 
	{
		$response = $this->_makeRequest($this->_getUrl('storage'), 'HEAD');
		
		if ($response->getStatus() != 204) {
			return false;
		}
		
		return array(
			$response->getHeader('X-Account-Container-Count'),
			$response->getHeader('X-Account-Total-Bytes-Used')
		);
	}
	
	/**
	 * Return information about a specific container.
	 *
	 * @return array an array containing two keys: 0 => the number of objects in container, and 2 => the total bytes used
	 * @throws Compass_Service_Rackspace_Cloudfiles_Exception
	 */
	public function containerInfo($container)
	{
		$response = $this->_makeRequest($this->_getUrl('storage'), 'HEAD', $container);
		
		if ($response->getStatus() == 404) {
			throw new Compass_Service_Rackspace_Cloudfiles_Exception(self::CF_ERR_CONTAINER_NOT_FOUND);
		}
		
		return array(
			$response->getHeader('X-Container-Object-Count'),
			$response->getHeader('X-Container-Bytes-Used')
		);
	}
	
	/**
	 * Retrive info on a container
	 *
	 * @param string container name
	 * @return array|false
	 */
	public function cdnInfo($container)
	{
		$response = $this->_makeRequest($this->_getUrl('cdn'), 'HEAD', $container);
		
		if ($response->getStatus() == 204) {
			return array(
				$response->getHeader('X-CDN-Enabled'),
				$response->getHeader('X-CDN-URI'),
				$response->getHeader('X-CDN-TTL'),
				$response->getHeader('X-Log-Retention')
			);
		}
		
		return false;
	}
	
	/**
	 * CDN Enable a container (make it publicly accessible)
	 * TTL = X_TTL; Log Regention = X-Log-Retention
	 *
	 * @param string container name
	 * @param array option params (currently time to live and log retention)
	 * @return array|false
	 */
	public function cdnEnableContainer($container, $headers=array())
	{
		$response = $this->_makeRequest($this->_getUrl('cdn'), 'PUT', $container, array(), $headers);
		
		if ($response->getStatus() == 201 || $response->getStatus() == 202) {
			return true;
		}
		
		return false;
	}
	
	public function createDirectory($directory)
	{
		return $this->putObject($directory, '', array('Content-Type' => 'application/directory'));
	}

	/**
     * Make a request to Cloudfiles
     *
	 * @param  string $endpoint
     * @param  string $method
     * @param  string $path
     * @param  array  $params
     * @param  array  $headers
     * @param  string $data
     * @return Zend_Http_Response
     */
    public function _makeRequest($endpoint, $method, $path='', $params=null, $headers=array(), $data=null)
    {
        $retry_count = 0;

        if (!is_array($headers)) {
            $headers = array($headers);
        }

		$headers['X-Auth-Token'] = $this->_authToken;
        $headers['Date'] = gmdate(DATE_RFC1123, time());

        $client = self::getHttpClient();

        $client->resetParameters();
		
        $client->setUri($endpoint . '/' . $path);
        $client->setHeaders($headers);

        if (is_array($params)) {
            foreach ($params as $name=>$value) {
                $client->setParameterGet($name, $value);
            }
         }

         if (($method == 'PUT') && ($data !== null)) {
             if (!isset($headers['Content-Type'])) {
                 $headers['Content-Type'] = self::getMimeType($path);
             }
             $client->setRawData($data, $headers['Content-Type']);
         }

         do {
            $retry = false;

            $response = $client->request($method);
            $response_code = $response->getStatus();

            // Some 5xx errors are expected, so retry automatically
            if ($response_code >= 500 && $response_code < 600 && $retry_count <= 5) {
                $retry = true;
                $retry_count++;
                sleep($retry_count / 4 * $retry_count);
            }
            else if ($response_code == 307) {
                // Need to redirect, new endpoint given
                // This should never happen as Zend_Http_Client will redirect automatically
            }
        } while ($retry);

        return $response;
    }

	/**
     * Attempt to get the content-type of a file based on the extension
     *
     * @param  string $path
     * @return string
     */
    public static function getMimeType($path)
    {
		$path = basename($path);
        $ext = substr(strrchr($path, '.'), 1);

        if (!$ext) {
            // directory
            return 'application/directory';
        }

        switch ($ext) {
            case 'xls':
                $content_type = 'application/excel';
                break;
            case 'hqx':
                $content_type = 'application/macbinhex40';
                break;
            case 'doc':
            case 'dot':
            case 'wrd':
                $content_type = 'application/msword';
                break;
            case 'pdf':
                $content_type = 'application/pdf';
                break;
            case 'pgp':
                $content_type = 'application/pgp';
                break;
            case 'ps':
            case 'eps':
            case 'ai':
                $content_type = 'application/postscript';
                break;
            case 'ppt':
                $content_type = 'application/powerpoint';
                break;
            case 'rtf':
                $content_type = 'application/rtf';
                break;
            case 'tgz':
            case 'gtar':
                $content_type = 'application/x-gtar';
                break;
            case 'gz':
                $content_type = 'application/x-gzip';
                break;
            case 'php':
            case 'php3':
            case 'php4':
                $content_type = 'application/x-httpd-php';
                break;
            case 'js':
                $content_type = 'application/x-javascript';
                break;
            case 'ppd':
            case 'psd':
                $content_type = 'application/x-photoshop';
                break;
            case 'swf':
            case 'swc':
            case 'rf':
                $content_type = 'application/x-shockwave-flash';
                break;
            case 'tar':
                $content_type = 'application/x-tar';
                break;
            case 'zip':
                $content_type = 'application/zip';
                break;
            case 'mid':
            case 'midi':
            case 'kar':
                $content_type = 'audio/midi';
                break;
            case 'mp2':
            case 'mp3':
            case 'mpga':
                $content_type = 'audio/mpeg';
                break;
            case 'ra':
                $content_type = 'audio/x-realaudio';
                break;
            case 'wav':
                $content_type = 'audio/wav';
                break;
            case 'bmp':
                $content_type = 'image/bitmap';
                break;
            case 'gif':
                $content_type = 'image/gif';
                break;
            case 'iff':
                $content_type = 'image/iff';
                break;
            case 'jb2':
                $content_type = 'image/jb2';
                break;
            case 'jpg':
            case 'jpe':
            case 'jpeg':
                $content_type = 'image/jpeg';
                break;
            case 'jpx':
                $content_type = 'image/jpx';
                break;
            case 'png':
                $content_type = 'image/png';
                break;
            case 'tif':
            case 'tiff':
                $content_type = 'image/tiff';
                break;
            case 'wbmp':
                $content_type = 'image/vnd.wap.wbmp';
                break;
            case 'xbm':
                $content_type = 'image/xbm';
                break;
            case 'css':
                $content_type = 'text/css';
                break;
            case 'txt':
                $content_type = 'text/plain';
                break;
            case 'htm':
            case 'html':
                $content_type = 'text/html';
                break;
            case 'xml':
                $content_type = 'text/xml';
                break;
            case 'xsl':
                $content_type = 'text/xsl';
                break;
            case 'mpg':
            case 'mpe':
            case 'mpeg':
                $content_type = 'video/mpeg';
                break;
            case 'qt':
            case 'mov':
                $content_type = 'video/quicktime';
                break;
            case 'avi':
                $content_type = 'video/x-ms-video';
                break;
            case 'eml':
                $content_type = 'message/rfc822';
                break;
            default:
                $content_type = 'binary/octet-stream';
                break;
        }

        return $content_type;
    }
}
