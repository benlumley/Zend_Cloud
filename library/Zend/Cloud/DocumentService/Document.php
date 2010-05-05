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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_DocumentService_Document implements ArrayAccess
{
    const KEY_FIELD = "__zend_key";
    /**
     * ID of this document.
     *
     * @var mixed
     */
    protected $_id;

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
    public function __construct($id, $fields)
    {
        $this->_id = $id;
        $this->_fields = $fields;
    }

    /**
     * Get ID name.
     *
     * @return string
     */
    public function getID() 
    {
        return $this->_id;
    }

    /**
     * Get fields as array.
     *
     * @return array
     */
    public function getFields() 
    {
        return $this->_fields;
    }

    /**
     * Get field by name.
     *
     * @param  string $name
     * @return string
     */
    public function getField($name)
    {
        return $this->_fields[$name];
    }
    
    /**
     * Set field by name.
     *
     * @param  string $name
     * @param  mixed $value
     * @return string
     */
    public function setField($name, $value) 
    {
        $this->_fields[$name] = $value;
        return $this;
    }
    
    public function __get($name)
    {
        return $this->_fields[$name];
    }

    public function __set($name, $value)
    {
        $this->_fields[$name] = $value;
    }
    
    public function offsetExists($name)
    {
        return isset($this->_fields[$name]);
    }
    
    public function offsetGet($name)
    {
        return $this->getField($name);
    }
    
    public function offsetSet($name, $value)
    {
        $this->setField($name, $value);
    }
    
    public function offsetUnset($name)
    {
        unset($this->_fields[$name]);
    }
    
    public function __call($name, $args)
    {
        if(substr($name, 0, 3) == 'get') {
            $option = substr($name, 3);
            // get value
            return $this->getField($option);
        } elseif(substr($name, 0, 3) == 'set') {
            $option = substr($name, 3);
            // set value
            return $this->setField($option, $args[0]);
        } else {
            require_once 'Zend/Cloud/OperationNotAvailableException.php';
            throw new Zend_Cloud_OperationNotAvailableException("Unknown operation $name");
        }        
    }
}
