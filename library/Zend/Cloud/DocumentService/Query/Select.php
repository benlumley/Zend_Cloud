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
 * @see Zend_Cloud_DocumentService_Query_ClauseInterface
 */
require_once 'Zend/Cloud/DocumentService/Query/Clause.php';

/**
 * Class representing projection of fields or a select operation. Currently
 * only an array of field names or a count() operation is allowed. All
 * operations must be one of the types listed in this class' constants.
 *
 *
 * @category   Zend
 * @package    Zend_Cloud
 * @subpackage DocumentService
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_DocumentService_Query_Select
    implements Zend_Cloud_DocumentService_Query_Clause
{
    // Allowed operations
    const COUNT = 'count';

    protected $_operation;
    protected $_fields;

    /**
     * Creates a select clause. $argument is either an array or one of the
     * operations listed above as a constant. If $argument is null, or an
     * operations is specified, it is assumed that all fields should be
     * returned.
     *
     * @param $fields
     * @param $operation
     */
    public function __construct($fields = null, $operation = null) 
    {
        $this->_fields = $fields;

        // TODO Verify that the operation is a valid operation from the list
        // of constants in this class.
        $this->_operation = $operation;
    }

    /**
     * Returns an array of the fields to project.
     *
     * @return array
     */
    public function getFields() 
    {
        return $this->_fields;
    }

    /**
     * Returns the aggregation operation to perform on results of this query.
     *
     * @return string
     */
    public function getOperation() 
    {
        return $this->_operation;
    }
}
