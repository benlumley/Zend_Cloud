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

/**
 * FileSystem adapter for unstructured cloud storage.
 *
 * @category   Zend
 * @package    Zend_Cloud
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_Storage_Adapter_FileSystem implements Zend_Cloud_StorageService
{
    
    /*
     * Options array keys for the file system adapter.
     */
    const LOCAL_DIRECTORY = 'local_directory';
    
    protected $_directory = null;
    
    public function __construct($options = array()) {
        if(isset($options[self::LOCAL_DIRECTORY])) {
            $this->_directory = $options[self::LOCAL_DIRECTORY];
        } else {
            $this->_directory = realpath(sys_get_temp_dir());
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
        $filepath = $this->_getFullPath($path);
        return file_get_contents(realpath($filepath));
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
        $path = $this->_getFullPath($destinationPath);
        file_put_contents($path, $data);
        chmod($path, 0777);
    }
    
    /**
     * Delete an item in the storage service.
     *
     * @param  string $path
     * @param  array $options
     * @return void
     */
    public function deleteItem($path, $options = array()) {
        if(!isset($path)) {
            return;
        }
        
        $filepath = $this->_getFullPath($path);
        if(file_exists($filepath)) {
            unlink($filepath);
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
    public function copyItem($sourcePath, $destinationPath, $options = array()) {
        copy($this->_getFullPath($sourcePath), $this->_getFullPath($destinationPath));
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
        rename($this->_getFullPath($sourcePath), $this->_getFullPath($destinationPath));
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
    public function renameItem($path, $name, $options = null) {
        rename($this->_getFullPath($path), dirname($this->_getFullPath($path)) .
                                                   DIRECTORY_SEPARATOR .
                                                   $name);
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
    public function listItems($path, $options = null) {
        $listing = scandir($this->_getFullPath($path));
        
        // Remove the hidden navigation directories
        $listing = array_diff($listing, array('.', '..'));
        
        return $listing;
    }
    
    /**
     * Get a key/value array of metadata for the given path.
     *
     * @param  string $path
     * @param  array $options
     * @return array
     */
    public function fetchMetadata($path, $options = array()) {
        $fullPath = $this->_getFullPath($path);
        $metadata = null;
        if(file_exists($fullPath)) {
            $metadata = stat(realpath($fullPath));
        }
        
        return isset($metadata) ? $metadata : false;
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
    
    private function _getFullPath($path) {
        return $this->_directory . DIRECTORY_SEPARATOR . $path;
    }
}