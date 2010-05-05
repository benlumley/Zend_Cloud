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
 * Class representing conditions in row queries. The query object model is
 * designed to be as simple and small as possible. This class therefore
 * encapsulates all possible operators with any number of operands, which are
 * passed in to the constructor as an ordered array.
 *
 * Conditions can be nested to compose a complex conditional clause.
 *
 * @category   Zend
 * @package    Zend_Cloud
 * @subpackage DocumentService
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_DocumentService_Query_Condition
{
    // Comparison operators
    const EQUALS                 = '=';
    const NOT_EQUALS             = '!=';
    const GREATER_THAN           = '>';
    const GREATER_THAN_OR_EQUALS = '>=';
    const LESS_THAN              = '<';
    const LESS_THAN_OR_EQUALS    = '<=';
    const BETWEEN                = 'between';
    const IS_NULL                = 'null?';
    const IS_NOT_NULL            = '!null?';

    // Logical operators
    const LOGICAL_AND = 'and';
    const LOGICAL_OR  = 'or';
    const LOGICAL_NOT = 'not';
                   
    protected $_operator;
    protected $_operands;

    /**
     * Creates a condition with the specified operator and operands. The
     * operands are indexed by their position in the expression. In other words,
     * operands must be ordered in the array. Operands may be nested conditions
     * for logical operators.
     *
     * @param $operator
     * @param $operands
     */
    public function __construct($operator, $operands) 
    {
        $this->_operator = $operator;
        $this->_operands = $operands;
    }

    /**
     * Returns the operand for this condition. This operand must be one of the
     * operand constants in this class.
     *
     * @return string
     */
    public function getOperator () 
    {
        return $this->_operator;
    }

    /**
     * Get ordered array of condition operands. Operands may be nested
     * conditions for logical operators.
     *
     * @return array
     */
    public function getOperands() 
    {
        return $this->_operands;
    }
}
