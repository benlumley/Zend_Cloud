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
 * Class representing tables to query in row queries. Currently only one table
 * is supported. Joins are specifically not supported. There are currently no
 * plans to support multiple tables in the future.
 *
 * @category   Zend
 * @package    Zend_Cloud
 * @subpackage DocumentService
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_DocumentService_Query_From
    implements Zend_Cloud_DocumentService_Query_Clause
{
    protected $_condition;

    /**
     * Creates a from clause. Only one table is allowed, and joins are
     * specifically not supported.
     *
     * @param $condition
     */
    public function __construct($table) {
        $this->_table = $table;
    }
}
