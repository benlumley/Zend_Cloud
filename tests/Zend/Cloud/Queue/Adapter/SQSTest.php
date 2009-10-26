<?php
// Call Zend_Cloud_Queue_Adapter_SQSTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    // @TODO Switch to relative path when this is added to ZF
    require_once 'TestHelper.php';
    define("PHPUnit_MAIN_METHOD", "Zend_Cloud_Queue_Adapter_SQSTest::main");
}

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
 * @package    Zend_Cloud_Queue
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @see Zend_Config_Ini
 */
require_once 'Zend/Config/Ini.php';

/**
 * @see Zend_Cloud_Queue_Factory
 */
require_once 'Zend/Cloud/Queue/Factory.php';

/**
 * @see Zend_Cloud_QueueServiceTestCase
 */
require_once 'Zend/Cloud/QueueServiceTestCase.php';

/**
 * @category   Zend
 * @package    Zend_Cloud
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_Queue_Adapter_SQSTest extends Zend_Cloud_QueueServiceTestCase
{
    /**
     * Period to wait for propagation in seconds
     * Should be set by adapter
     *
     * @var int
     */
    protected $_waitPeriod = 10;
    
	/**
     * Runs the test methods of this class.
     *
     * @access public
     * @static
     */
    public static function main()
    {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("Zend_Cloud_Queue_Adapter_SQSTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
        return $this->main();
    }

    /**
     * Sets up this test case
     *
     * @return void
     */
    public function setUp() {
        $config = new Zend_Config_Ini(realpath(dirname(__FILE__) . '/../_files/config/sqs.ini'));

        $this->_commonQueue = Zend_Cloud_Queue_Factory::getAdapter(
                                    $config
                                );
        
        // Isolate the tests from slow deletes
        $this->_wait();
        parent::setUp();
    }
    
    public function testStoreQueueMetadata() {
        $this->markTestSkipped('SQS does not currently support storing metadata');
    }
    
    public function testPeekMessage() {
        $this->markTestSkipped('The SQS client library does not currently support peeking messages');
    }
}
