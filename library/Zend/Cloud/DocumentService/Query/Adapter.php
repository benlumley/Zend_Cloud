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
 * This interface describes the API that concrete query adapter should implement
 * 
 * TODO Look into preventing a query injection attack.
 *
 * @category   Zend
 * @package    Zend_Cloud
 * @subpackage DocumentService
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
interface Zend_Cloud_DocumentService_Query_Adapter
{
    /**
     * SELECT clause (fields to be selected)
     * 
     * @param string $select
     * @return Zend_Cloud_DocumentService_Query_Adapter
     */
    public function select($select);

    /**
     * FROM clause (table name)
     * 
     * @param string $from
     * @return Zend_Cloud_DocumentService_Query_Adapter
     */
    public function from($from);

    /**
     * WHERE clause (conditions to be used)
     * 
     * @param string $where
     * @param mixed $value Value or array of values to be inserted instead of ?
     * @param string $op Operation to use to join where clauses (AND/OR)
     * @return Zend_Cloud_DocumentService_Query_Adapter
     */
    public function where($where, $value = null, $op = 'and');

    /**
     * WHERE clause for item ID
     * 
     * This one should be used when fetching specific rows since some adapters
     * have special syntax for primary keys
     * 
     * @param mixed $value Row ID for the document
     * @return Zend_Cloud_DocumentService_Query_Adapter
     */
    public function whereId($value);

    /**
     * LIMIT clause (how many rows ot return)
     * 
     * @param int $limit
     * @return Zend_Cloud_DocumentService_Query_Adapter
     */
    public function limit($limit);

    /**
     * ORDER BY clause (sorting)
     * 
     * @param string $sort Column to sort by
     * @param string $direction Direction - asc/desc
     * @return Zend_Cloud_DocumentService_Query_Adapter
     */
    public function order($sort, $direction = 'asc');
}
