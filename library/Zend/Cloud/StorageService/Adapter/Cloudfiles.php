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
 * @subpackage StorageService
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

require_once 'Compass/Service/Rackspace/Cloudfiles.php';
require_once 'Zend/Cloud/StorageService/Adapter.php';
require_once 'Zend/Cloud/StorageService/Exception.php';

/**
 * S3 adapter for unstructured cloud storage.
 *
 * @category   Zend
 * @package    Zend_Cloud
 * @subpackage StorageService
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_StorageService_Adapter_Cloudfiles
    implements Zend_Cloud_StorageService_Adapter
{
    /*
     * Options array keys for the S3 adapter.
     */
    const CONTAINER_NAME      = 'container_name';
    const FETCH_STREAM     = 'fetch_stream';
    const METADATA         = 'metadata';

    /**
     * AWS constants
     */
    const CF_USER   = 'cf_user';
    const CF_APIKEY   = 'cf_apikey';

    /**
     * S3 service instance.
     * @var Zend_Service_Amazon_S3
     */
    protected $_cf;
    protected $_defaultContainerName = null;

    /**
     * Constructor
     *
     * @param  array|Zend_Config $options
     * @return void
     */
    public function __construct($options = array())
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        if (!is_array($options)) {
            throw new Zend_Cloud_StorageService_Exception('Invalid options provided');
        }

        if (!isset($options[self::CF_USER]) || !isset($options[self::CF_APIKEY])) {
            throw new Zend_Cloud_StorageService_Exception('CF auth data not specified!');
        }

        try {
            $this->_cf = new Compass_Service_Rackspace_Cloudfiles($options[self::CF_USER],
                                                $options[self::CF_APIKEY]);
            $this->_cf->auth();
        } catch (Compass_Service_Rackspace_CloudFiles_Exception  $e) {
            throw new Zend_Cloud_StorageService_Exception('Error on create: '.$e->getMessage(), $e->getCode(), $e);
        }

        if (isset($options[self::HTTP_ADAPTER])) {
            $this->_cf->getHttpClient()->setAdapter($options[self::HTTP_ADAPTER]);
        }

        if (isset($options[self::CONTAINER_NAME])) {
            $this->_defaultContainerName = $options[self::CONTAINER_NAME];
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
    public function fetchItem($path, $options = array())
    {
        $fullPath = $this->_getFullPath($path, $options);
        try {
            if (!empty($options[self::FETCH_STREAM])) {
                return $this->_cf->getObjectStream($fullPath, $options[self::FETCH_STREAM]);
            } else {
                return $this->_cf->getObject($fullPath);
            }
        } catch (Compass_Service_Rackspace_CloudFiles_Exception  $e) {
            throw new Zend_Cloud_StorageService_Exception('Error on fetch: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Store an item in the storage service.
     *
     * WARNING: This operation overwrites any item that is located at
     * $destinationPath.
     *
     * @TODO Support streams
     *
     * @param string $destinationPath
     * @param string|resource $data
     * @param  array $options
     * @return void
     */
    public function storeItem($destinationPath, $data, $options = array())
    {
        try {
            $fullPath = $this->_getFullPath($destinationPath, $options);
            return $this->_cf->putObject(
                $fullPath,
                $data,
                empty($options[self::METADATA]) ? null : $options[self::METADATA]
            );
        } catch (Compass_Service_Rackspace_CloudFiles_Exception  $e) {
            throw new Zend_Cloud_StorageService_Exception('Error on store: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Delete an item in the storage service.
     *
     * @param  string $path
     * @param  array $options
     * @return void
     */
    public function deleteItem($path, $options = array())
    {
        try {
            $this->_cf->removeObject($this->_getFullPath($path, $options));
        } catch (Compass_Service_Rackspace_CloudFiles_Exception  $e) {
            throw new Zend_Cloud_StorageService_Exception('Error on delete: '.$e->getMessage(), $e->getCode(), $e);
        }
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
    public function copyItem($sourcePath, $destinationPath, $options = array())
    {
        try {
            // TODO We *really* need to add support for object copying in the S3 adapter
            $item = $this->fetch($_getFullPath(sourcePath), $options);
            $this->storeItem($item, $destinationPath, $options);
        } catch (Compass_Service_Rackspace_CloudFiles_Exception  $e) {
            throw new Zend_Cloud_StorageService_Exception('Error on copy: '.$e->getMessage(), $e->getCode(), $e);
        }
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
    public function moveItem($sourcePath, $destinationPath, $options = array())
    {
        try {
            // TODO We *really* need to add support for object copying in the S3 adapter
            $item = $this->fetch($sourcePath, $options);
            $this->storeItem($item, $destinationPath, $options);
            $this->deleteItem($sourcePath, $options);
        } catch (Compass_Service_Rackspace_CloudFiles_Exception  $e) {
            throw new Zend_Cloud_StorageService_Exception('Error on move: '.$e->getMessage(), $e->getCode(), $e);
        }
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
        require_once 'Zend/Cloud/OperationNotAvailableException.php';
        throw new Zend_Cloud_OperationNotAvailableException('Rename not implemented');
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
        try {
            // TODO Support 'prefix' parameter for Zend_Service_Amazon_S3::getObjectsByContainer()
            return $this->_cf->getObjectsByContainer($this->_defaultContainerName);
        } catch (Compass_Service_Rackspace_CloudFiles_Exception  $e) {
            throw new Zend_Cloud_StorageService_Exception('Error on list: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Get a key/value array of metadata for the given path.
     *
     * @param  string $path
     * @param  array $options
     * @return array
     */
    public function fetchMetadata($path, $options = array())
    {
        try {
            return $this->_cf->getInfo($this->_getFullPath($path, $options));
        } catch (Compass_Service_Rackspace_CloudFiles_Exception  $e) {
            throw new Zend_Cloud_StorageService_Exception('Error on fetch: '.$e->getMessage(), $e->getCode(), $e);
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
    public function storeMetadata($destinationPath, $metadata, $options = array())
    {
        require_once 'Zend/Cloud/OperationNotAvailableException.php';
        throw new Zend_Cloud_OperationNotAvailableException('Storing separate metadata is not supported, use storeItem() with \'metadata\' option key');
    }

    /**
     * Delete a key/value array of metadata at the given path.
     *
     * @param  string $path
     * @param  array $options
     * @return void
     */
    public function deleteMetadata($path)
    {
        require_once 'Zend/Cloud/OperationNotAvailableException.php';
        throw new Zend_Cloud_OperationNotAvailableException('Deleting metadata not supported');
    }

    /**
     * Get full path, including container, for an object
     *
     * @param  string $path
     * @param  array $options
     * @return void
     */
    protected function _getFullPath($path, $options)
    {
        if (isset($options[self::CONTAINER_NAME])) {
            $container = $options[self::CONTAINER_NAME];
        } else if (isset($this->_defaultContainerName)) {
            $container = $this->_defaultContainerName;
        } else {
            require_once 'Zend/Cloud/StorageService/Exception.php';
            throw new Zend_Cloud_StorageService_Exception('Container name must be specified for CF adapter.');
        }


        return trim($container) . '/' . trim($path);
    }

    /**
     * Get the concrete client.
     * @return Zend_Service_Amazon_S3
     */
    public function getClient()
    {
         return $this->_cf;
    }
}
