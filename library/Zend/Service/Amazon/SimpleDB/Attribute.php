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
 * @package    Zend_Service_Amazon
 * @subpackage SimpleDB
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Response.php 17539 2009-08-10 22:51:26Z mikaelkael $
 */

/**
 * @category   Zend
 * @package    Zend_Service_Amazon
 * @subpackage SimpleDB
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Amazon_SimpleDB_Attribute
{
    protected $_itemName;
    protected $_name;
    protected $_values;

    function __construct($itemName, $name, $values) 
    {
        $this->_itemName = $itemName;
        $this->_name = $name;
        if(!is_array($values)) {
            $this->_values = array($values);
        } else {
            $this->_values = $values;
        }
    }

	/**
     * @return the $_itemName
     */
    public function getItemName ()
    {
        return $this->_itemName;
    }

	/**
     * @return $_values
     */
    public function getValues()
    {
        return $this->_values;
    }

	/**
     * @return the $_name
     */
    public function getName ()
    {
        return $this->_name;
    }
    
    public function addValue($value)
    {
        if(is_array($value)) {
             $this->_values += $value;   
        } else {
            $this->_values[] = $value;
        }
    }
}
