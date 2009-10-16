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

require_once 'Zend/Service/Amazon/S3.php';
require_once 'Zend/Cloud/StorageService.php';

/**
 * S3 adapter for unstructured cloud storage.
 *
 * @category   Zend
 * @package    Zend_Cloud
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_Storage_Adapter_S3 implements Zend_Cloud_StorageService
{
    
    /*
     * Options array keys for the S3 adapter.
     */
    const BUCKET_NAME = 'bucket_name';
    const BUCKET_AS_DOMAIN = 'bucket_as_domain?';
    const HTTP_ADAPTER = 'HTTP Adapter';
    
    /**
     * AWS constants
     */
    const ACCESS_KEY = 'aws_accesskey';
    const SECRET_KEY = 'aws_secretkey';
    
    /**
     * Defaults
     */
    const DEFAULT_HTTP_ADAPTER_NAME = 'Zend_Http_Client_Adapter_Socket';
    
    /**
     * S3 service instance.
     */
    protected $_s3;
    protected $_defaultBucketName = null;
    protected $_defaultBucketAsDomain = false;
    
    public function __construct($options = array()) {
                                    
        $this->_s3 = new Zend_Service_Amazon_S3($options[self::ACCESS_KEY],
                                                $options[self::SECRET_KEY]);
        
        if(isset($options[self::HTTP_ADAPTER])) {
            $httpAdapter = $options[self::HTTP_ADAPTER];
        } else {
            $adapterName = self::DEFAULT_HTTP_ADAPTER_NAME;
            $httpAdapter = new $adapterName;
        }
        $this->_s3->getHttpClient()
                  ->setAdapter($httpAdapter);
                  
        if(isset($options[self::BUCKET_NAME])) {
            $this->_defaultBucketName = $options[self::BUCKET_NAME];
        }       

        if(isset($options[self::BUCKET_AS_DOMAIN])) {
            $this->_defaultBucketAsDomain = $options[self::BUCKET_AS_DOMAIN];
        }
    }

    /**
     * Get an item from the storage service.
     *
     * @TODO Support streams
     * 
     * @param  string $path
     * @param  array $options
     * @return string
     */
    public function fetchItem($path, $options = array()) {
        $fullPath = $this->_getFullPath($path, $options);
        return $this->_s3->getObject($fullPath);
    }
    
    /**
     * Store an item in the storage service.
     * 
     * WARNING: This operation overwrites any item that is located at 
     * $destinationPath.
     * 
     * @TODO Support streams
     * 
     * @param mixed $data
     * @param string $destinationPath
     * @param  array $options
     * @return void
     */
    public function storeItem($data,
                              $destinationPath,
                              $options = array()) {
                                  
        $fullPath = $this->_getFullPath($destinationPath, $options);
        return $this->_s3->putObject($fullPath,
                                     $data);
    }
    
    /**
     * Delete an item in the storage service.
     *
     * @param  string $path
     * @param  array $options
     * @return void
     */
    public function deleteItem($path, $options = array()) {
        $this->_s3->removeObject($this->_getFullPath($path, $options));
    }
    
    /**
     * Copy an item in the storage service to a given path.
     * 
     * WARNING: This operation is *very* expensive for services that do not
     * support copying an item natively.
     * 
     * @TODO Support streams for those services that don't support natively
     *
     * @param  string $sourcePath
     * @param  string $destination path
     * @param  array $options
     * @return void
     */
    public function copyItem($sourcePath, $destinationPath, $options = array()) {
        // TODO We *really* need to add support for object copying in the S3 adapter
        $item = $this->fetchItem($_getFullPath(sourcePath), $options);
        $this->storeItem($item, $destinationPath, $options);
    }
    
    /**
     * Move an item in the storage service to a given path.
     * 
     * WARNING: This operation is *very* expensive for services that do not
     * support moving an item natively.
     * 
     * @TODO Support streams for those services that don't support natively
     *
     * @param  string $sourcePath
     * @param  string $destination path
     * @param  array $options
     * @return void
     */
    public function moveItem($sourcePath, $destinationPath, $options = array()) {
        // TODO We *really* need to add support for object copying in the S3 adapter
        $item = $this->fetchItem($sourcePath, $options);
        $this->storeItem($item, $destinationPath, $options);
        $this->deleteItem($sourcePath, $options);
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
    public function renameItem($path, $name, $options = null) {}
    
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
    public function listItems($path, $options = null) {
        // TODO Support 'prefix' parameter for Zend_Service_Amazon_S3::getObjectsByBucket()
        return $this->_s3->getObjectsByBucket($this->_defaultBucketName);
    }
    
    /**
     * Get a key/value array of metadata for the given path.
     *
     * @param  string $path
     * @param  array $options
     * @return array
     */
    public function fetchMetadata($path, $options = array()) {
        return $this->_s3->getInfo($this->_getFullPath($path, $options));
    }
    
    /**
     * Store a key/value array of metadata at the given path.
     * WARNING: This operation overwrites any metadata that is located at 
     * $destinationPath.
     *
     * @param  string $path
     * @param  array $options
     * @return void
     */
    public function storeMetadata($metadata, $destinationPath, $options = array()) {
        throw new Zend_Cloud_Storage_Exception('Method not implemented.');
    }
    
    /**
     * Delete a key/value array of metadata at the given path.
     *
     * @param  string $path
     * @param  array $options
     * @return void
     */
    public function deleteMetadata($path) {
        throw new Zend_Cloud_Storage_Exception('Method not implemented.');
    }
    
    protected function _getFullPath($path, $options) {
        if(isset($options[self::BUCKET_NAME])) {
            $bucket = $options[self::BUCKET_NAME];    
        } else if(isset($this->_defaultBucketName)) {
            $bucket = $this->_defaultBucketName;
        } else {
            throw new Zend_Cloud_Storage_Exception('Bucket name must be specified for S3 adapter.');
        }
        
        if(isset($options[self::BUCKET_AS_DOMAIN])) {
            throw new Zend_Cloud_Storage_Exception('The S3 adapter does not support buckets in domain names.'); 
        }
        
        return trim($bucket) . '/' . trim($path);
    }
}