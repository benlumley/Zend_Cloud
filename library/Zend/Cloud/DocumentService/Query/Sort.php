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
 * Class representing fields to sort by in row queries. Services that
 * do not support this functionality should throw a Zend_Cloud_DocumentService_Exception
 * in the query renderer indicating that the service does not support the feature.
 * Limit should not be emulated on the client side without making it apparent to
 * the user that this is happening on their behalf behind the interface.
 *
 * Currently only one field is supported.
 *
 * @category   Zend
 * @package    Zend_Cloud
 * @subpackage DocumentService
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_DocumentService_Query_Sort
    implements Zend_Cloud_DocumentService_Query_ClauseInterface
{
    protected $_field;

    /**
     * Creates a limit clause.
     *
     * @param $condition
     */
    public function __construct($field) {
        $this->_field = $field;
    }

    /**
     * Returns the field to sort by.
     *
     * @return int
     */
    public function getField() {
        return $this->_field;
    }
}