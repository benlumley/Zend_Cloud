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

/**
 * Common interface for document storage services in the cloud. This interface
 * supports most document services and provides some flexibility for
 * vendor-specific features and requirements via an optional $options array in
 * each method signature. Classes implementing this interface should implement
 * URI construction for collections and documents from the parameters given in each
 * method and the account data passed in to the constructor. Classes
 * implementing this interface are also responsible for security; access control
 * isn't currently supported in this interface, although we are considering
 * access control support in future versions of the interface. Query
 * optimization mechanisms are also not supported in this version.
 *
 * TODO Look into preventing a query injection attack.
 *
 * @category   Zend
 * @package    Zend_Cloud
 * @subpackage DocumentService
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_DocumentService_Query
{
    /**
     * Known query types
     */
    const QUERY_SELECT = 'select';
    const QUERY_FROM = 'from';
    const QUERY_WHERE = 'where';
    const QUERY_WHEREID = 'whereid';
    const QUERY_LIMIT = 'limit';
    const QUERY_ORDER = 'order';
    /**
     * Clause list
     * 
     * @var array
     */
    protected $_clauses;

    public function __call($name, $args) 
    {
        $this->_clauses[] = array(strtolower($name), $args);
        return $this;
    }
    
    public function from($name)
    {
        if(!is_string($name)) {
            require_once 'Zend/Cloud/DocumentService/Exception.php';           
            throw new Zend_Cloud_DocumentService_Exception("FROM argument must be a string");
        }
        $this->_clauses[] = array("from", $name);
        return $this;
    }
    
    public function where($cond, $args, $op = 'and')
    {
        if(!is_string($cond)) {
            require_once 'Zend/Cloud/DocumentService/Exception.php';           
            throw new Zend_Cloud_DocumentService_Exception("WHERE argument must be a string");
        }
        $this->_clauses[] = array("where", array($cond, $args, $op));
        return $this;
    }
    
    public function getClauses()
    {
         return $this->_clauses;   
    }
}