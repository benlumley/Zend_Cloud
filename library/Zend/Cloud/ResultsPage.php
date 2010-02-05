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
 * @package    Zend_Cloud
 * @subpackage Ec2
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Exception.php 17539 2009-08-10 22:51:26Z mikaelkael $
 */

/**
 * @see Zend_Cloud_Exception
 */
require_once 'Zend/Cloud/Exception.php';

/**
 * The Custom Exception class that allows you to have access to the AWS Error Code.
 *
 * @category   Zend
 * @package    Zend_Cloud
 * @subpackage Cloud
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_Page
{
    protected $_data;
    protected $_token;

    public function __construct($data, $token = null) {
        $this->_data = $data;
        $this->_token = $token;
    }

    public function getData() {
        return $this->_data;
    }

    public function getToken() {
        return $this->_token;
    }

    public function isLast() {
        return !isset($this->_token);
    }
}