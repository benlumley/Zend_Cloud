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
 * Class representing filters in row queries. The query object model is
 * designed to be as simple and small as possible. This class stores the
 * condition to be applied to filtered rows. Only one condition is allowed,
 * although conditions may be nested.
 *
 * @category   Zend
 * @package    Zend_Cloud
 * @subpackage DocumentService
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_DocumentService_Query_Condition
    implements Zend_Cloud_DocumentService_Query_ClauseInterface
{
    protected $_condition;

    /**
     * Creates a filter clause. Only one condition is allowed, although
     * conditions may be nested.
     *
     * @param $condition
     */
    public function __construct($condition) {
        $this->_condition = $condition;
    }
}