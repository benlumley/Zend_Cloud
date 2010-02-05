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

/**
 * Class encapsulating documents. Fields are stored in a name/value
 * array. Data are represented as strings.
 *
 * TODO Can fields be large enough to warrant support for streams?
 *
 * @category   Zend
 * @package    Zend_Cloud
 * @subpackage DocumentService
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_DocumentService_Document
{
    /**
     * ID of this document.
     *
     * @var string
     */
	protected $_id;

    /**
     * Collection name that this document is associated with.
     *
     * @var string
     */
	protected $_collection;

	/**
	 * Name/value array of field names to values.
	 *
	 * @var array
	 */
	protected $_fields;

	/**
	 * Construct an instance of Zend_Cloud_DocumentService_Document.
	 *
	 * @param string $collection
	 * @param array  $fields
	 */
	public function __construct($id, $collection, $fields) {
	    $this->_id = $id;
        $this->_collection = $collection;
        $this->_fields = $fields;
	}

	/**
	 * Get ID name.
	 *
	 * @return string
	 */
	public function getID() {
	    return $this->_id;
	}

	/**
	 * Get collection name.
	 *
	 * @return string
	 */
	public function getCollection() {
	    return $this->_collection;
	}

	/**
	 * Get fields as array.
	 *
	 * @return array
	 */
	public function getFields() {
	    return $this->_fields;
	}

	/**
	 * Get field by name.
	 *
	 * @param  string $name
	 * @return string
	 */
    public function getField($name) {
	    return $this->_fields[$name];
	}
}