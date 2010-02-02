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
 * @package    Zend_Cloud_QueueService
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: AllTests.php 11973 2008-10-15 16:00:56Z matthew $
 */


if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Zend_Cloud_QueueService_Adapter_AllTests::main');
}

/**
 * Test helper
 */
require_once dirname(__FILE__) . '/../../../../TestHelper.php';

/**
 * @see Zend_Cloud_QueueService_Adapter_SQSTest
 */
require_once 'Zend/Cloud/Queue/Adapter/SQSTest.php';

/**
 * @see Zend_Cloud_QueueService_Adapter_ZendQueueTest
 */
require_once 'Zend/Cloud/QueueService/Adapter/ZendQueueTest.php';

/**
 * @category   Zend
 * @package    Zend_Cloud_QueueService_Adapter
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_QueueService_Adapter_AllTests
{
    /**
     * Runs this test suite
     *
     * @return void
     */
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    /**
     * Creates and returns this test suite
     *
     * @return PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Zend Framework - Zend_Cloud');

        $suite->addTestSuite('Zend_Cloud_QueueService_Adapter_SQSTest');
        $suite->addTestSuite('Zend_Cloud_QueueService_Adapter_ZendQueueTest');

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Zend_Cloud_QueueService_Adapter_AllTests::main') {
    Zend_Cloud_QueueService_Adapter_AllTests::main();
}
