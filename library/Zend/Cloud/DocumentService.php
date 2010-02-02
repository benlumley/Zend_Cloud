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
 * @subpackage DocumentService
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

require_once 'Zend/Cloud/DocumentService/Document.php';
require_once 'Zend/Cloud/DocumentService/Query.php';

/**
 * Common interface for document storage services in the cloud. This interface
 * supports most document services and provides some flexibility for
 * vendor-specific features and requirements via an optional $options array in
 * each method signature. Classes implementing this interface should implement
 * URI construction for collections and documents from the parameters given in each
 * method and the account data passed in to the constructor. Classes
 * implementing this interface are also responsible for security; access control
 * isn't currently supported in this interface, although we are considering
 * access control support in future versions of the interface.
 *
 * @category   Zend
 * @package    Zend_Cloud
 * @subpackage DocumentService
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
interface Zend_Cloud_DocumentService
{
    /**
     * Create collection.
     *
     * @param  string $name
     * @param  array  $options
     * @return array
     */
    public function createCollection($name, $options = null);

    /**
     * Delete collection.
     *
     * @param  string $name
     * @param  array  $options
     * @return void
     */
    public function deleteCollection($name, $options = null);

   	/**
     * List collections.
     *
     * @param  array  $options
     * @return boolean
     */
    public function listCollections($options = null);

    /**
     * List documents. Returns a key=>value array of document names to document objects.
     *
     * @param  array $options
     * @return array
     */
    public function listDocuments($collectionName, $options = null);

    /**
     * Insert document
     *
     * @param  Zend_Cloud_DocumentService_Document $document
     * @param  array                 		$options
     * @return boolean
     */
    public function insertDocument($document, $options = null);

    /**
     * Update document. The new document replaces the existing document.
     *
     * @param  Zend_Cloud_DocumentService_Document $document
     * @param  array                 		$options
     * @return boolean
     */
    public function updateDocument($document, $options = null);

    /**
     * Delete document.
     *
     * @param  mixed  $document Document ID or Document object.
     * @param  array  $options
     * @return void
     */
    public function deleteDocument($document, $options = null);

    /**
     * Query for documents stored in the document service. If a string is passed in
     * $query, the query string will be passed directly to the service.
     *
     * @param  mixed $query
     * @param  array $options
     * @return array
     */
    public function query($query, $options = null);
}