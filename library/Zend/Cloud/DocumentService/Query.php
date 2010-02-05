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
    protected $_select;
    protected $_from;
    protected $_filter;
    protected $_limit;
    protected $_sort;

    /**
     * Creates row query with the specified clauses.
     */
    public function _construct($select = null,
                               $from,
                               $filter = null,
                               $limit  = null,
                               $sort   = null) {
        $this->_select = $select;
        $this->_from =   $from;
        $this->_filter = $filter;
        $this->_limit  = $limit;
        $this->_sort = $sort;
    }

    public function getSelect() {
        return $this->_select;
    }

    public function getFrom() {
        return $this->_select;
    }

    public function getFilter() {
        return $this->_filter;
    }

    public function getLimit() {
        return $this->_limit;
    }

    public function getSort() {
        return $this->_sort;
    }
}