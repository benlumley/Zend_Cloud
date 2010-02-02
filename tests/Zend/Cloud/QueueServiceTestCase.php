<?php
// This test case cannot be executed directly.

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
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

require_once 'TestHelper.php';

/**
 * @see Zend_Cloud_QueueService
 */
require_once 'Zend/Cloud/QueueService.php';

/**
 * @see Zend_Http_Client_Adapter_Socket
 */
require_once 'Zend/Http/Client/Adapter/Socket.php';

/**
 * @category   Zend
 * @package    Zend_Cloud
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 *
 * This class forces the adapter tests to implement tests for all methods on
 * Zend_Cloud_QueueService.
 */
abstract class Zend_Cloud_QueueServiceTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * Reference to queue adapter to test
     *
     * @var Zend_Cloud_QueueService
     */
    protected $_commonQueue;

    protected $_dummyNamePrefix = '/TestItem';

    protected $_dummyDataPrefix = 'TestData';

    /**
     * Config object
     *
     * @var Zend_Config
     */

    protected $_config;

    /**
     * Period to wait for propagation in seconds
     * Should be set by adapter
     *
     * @var int
     */
    protected $_waitPeriod = 1;

    public function testCreateQueue() {
        $queueURL = null;
        try{
            // Create timeout default should be 30 seconds
            $startTime = time();
            $queueURL = $this->_commonQueue->createQueue('testCreateQueue');
            $endTime = time();
            $this->assertNotNull($queueURL);
            $this->assertLessThan(30, $endTime - $startTime);

            $this->_commonQueue->deleteQueue($queueURL);
        } catch(Exception $e) {
            $this->_commonQueue->deleteQueue($queueURL);
            throw $e;
        }
    }

    public function testDeleteQueue() {
    $queueURL = null;
        try{
            $queueURL = $this->_commonQueue->createQueue('testDeleteQueue');
            $this->assertNotNull($queueURL);

            $this->_commonQueue->deleteQueue($queueURL);

            $this->_wait();
            $this->_wait();
            $this->_wait();
            try {
                $this->_commonQueue->receiveMessages($queueURL);
            } catch(Zend_Cloud_QueueService_Exception $e) {
                $this->assertTrue(true);
                $this->_commonQueue->deleteQueue($queueURL);
                return;
            }
            $this->fail('An exception should have been thrown if the queue has been deleted');
        } catch(Exception $e) {
            $this->_commonQueue->deleteQueue($queueURL);
            throw $e;
        }
    }

    public function testListQueues() {
        $queueURL1 = null;
        $queueURL2 = null;
        try{
            $queueURL1 = $this->_commonQueue->createQueue('testListQueue1');
            $this->assertNotNull($queueURL1);
            $queueURL2 = $this->_commonQueue->createQueue('testListQueue2');
            $this->assertNotNull($queueURL2);
            $this->_wait();

            $queues = $this->_commonQueue->listQueues();
            $this->assertEquals(2, sizeof($queues));

            // PHPUnit does an identical comparison for assertContains(), so we just
            // use assertTrue and in_array()
            $this->assertTrue(in_array($queueURL1, $queues));
            $this->assertTrue(in_array($queueURL2, $queues));

            $this->_commonQueue->deleteQueue($queueURL1);
            $this->_commonQueue->deleteQueue($queueURL2);
        } catch(Exception $e) {
            $this->_commonQueue->deleteQueue($queueURL1);
            $this->_commonQueue->deleteQueue($queueURL2);
            throw $e;
        }
    }

    public function testFetchQueueMetadata() {
        $queueURL = null;
        try{
            $queueURL = $this->_commonQueue->createQueue('testFetchQueueMetadata');
            $this->assertNotNull($queueURL);
            $this->_wait();
            $metadata = $this->_commonQueue->fetchQueueMetadata($queueURL);
            $this->assertTrue(is_array($metadata));
            $this->assertGreaterThan(1, count($metadata));
            $this->_commonQueue->deleteQueue($queueURL);
        } catch(Exception $e) {
            $this->_commonQueue->deleteQueue($queueURL);
            throw $e;
        }
    }

    public function testStoreQueueMetadata() {
        $this->markTestIncomplete();
    }

    public function testSendMessage() {
        $queueURL = null;
        try{
            $queueURL = $this->_commonQueue->createQueue('testSendMessage');
            $this->assertNotNull($queueURL);
            $this->_wait();
            $message = 'testSendMessage - Message 1';
            $this->_commonQueue->sendMessage($message, $queueURL);
            $this->_wait();
            $receivedMessages = $this->_commonQueue->receiveMessages($queueURL);
            $this->assertTrue(is_array($receivedMessages));
            $this->assertContains($message, $receivedMessages[0]);
            $this->assertEquals(1, count($receivedMessages));
        } catch(Exception $e) {
            $this->_commonQueue->deleteQueue($queueURL);
            throw $e;
        }
    }

    public function testReceiveMessages() {
        $queueURL = null;
        try{
            $queueURL = $this->_commonQueue->createQueue('testReceiveMessages');
            $this->assertNotNull($queueURL);
            $this->_wait();

            $message1 = 'testReceiveMessages - Message 1';
            $message2 = 'testReceiveMessages - Message 2';
            $this->_commonQueue->sendMessage($message1, $queueURL);
            $this->_commonQueue->sendMessage($message2, $queueURL);
            $this->_wait();
            $this->_wait();

            $receivedMessages1 = $this->_commonQueue->receiveMessages($queueURL);
            $this->assertTrue(is_array($receivedMessages1));
            $this->assertEquals(1, count($receivedMessages1));
            $receivedMessage1 = array_pop($receivedMessages1);
            $this->_commonQueue->deleteMessage($receivedMessage1['message_id'], $queueURL);
            $receivedMessage2 = array_pop($receivedMessages1);
            $this->_commonQueue->deleteMessage($receivedMessage2['message_id'], $queueURL);

            $this->_commonQueue->sendMessage($message1, $queueURL);
            $this->_commonQueue->sendMessage($message2, $queueURL);
            $this->_wait();

            $receivedMessages2 = $this->_commonQueue->receiveMessages($queueURL, 2);
            $this->assertTrue(is_array($receivedMessages2));
            $this->assertEquals(2, count($receivedMessages2));
            $receivedMessage1 = array_pop($receivedMessages2);
            $receivedMessage2 = array_pop($receivedMessages2);
            $this->assertContains($message1, $receivedMessage1 + $receivedMessage2);
            $this->assertContains($message2, $receivedMessages1 + $receivedMessages2);
            $this->_commonQueue->deleteQueue($queueURL);
        } catch(Exception $e) {
            $this->_commonQueue->deleteQueue($queueURL);
            throw $e;
        }
    }

    public function testDeleteMessage() {
        $queueURL = null;
        try{
            $queueURL = $this->_commonQueue->createQueue('testDeleteMessages');
            $this->assertNotNull($queueURL);
            $this->_wait();
            $message1 = 'testDeleteMessage - Message 1';
            $this->_commonQueue->sendMessage($message1, $queueURL);
            $this->_wait();

            $receivedMessages1 = $this->_commonQueue->receiveMessages($queueURL);
            $this->assertTrue(is_array($receivedMessages1));
            $this->assertContains($message1, $receivedMessages1[0]);
            $this->assertEquals(1, count($receivedMessages1));
            $receivedMessage1 = array_pop($receivedMessages1);
            $this->_commonQueue->deleteMessage($receivedMessage1['message_id'], $queueURL);

            $receivedMessages2 = $this->_commonQueue->receiveMessages($queueURL);
            $this->assertTrue(is_array($receivedMessages2));
            $this->assertEquals(0, count($receivedMessages2));
        } catch(Exception $e) {
            $this->_commonQueue->deleteQueue($queueURL);
            throw $e;
        }
    }

    public function testPeekMessage() {
    $queueURL = null;
        try{
            $queueURL = $this->_commonQueue->createQueue('testPeekMessage');
            $this->assertNotNull($queueURL);
            $this->_wait();
            $message1 = 'testPeekMessage - Message 1';
            $messageID = $this->_commonQueue->sendMessage($message1, $queueURL);
            $this->_wait();

            $peekedMessage = $this->_commonQueue->peekMessage($messageID, $queueURL);
            $this->assertEquals($message1, $peekedMessage);
            $this->_commonQueue->deleteMessage(array_pop($peekedMessages), $queueURL);
        } catch(Exception $e) {
            $this->_commonQueue->deleteQueue($queueURL);
            throw $e;
        }
    }

    protected function _wait() {
        sleep($this->_waitPeriod);
    }
}


class Zend_Cloud_QueueService_Adapter_Skip extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->markTestSkipped('No access key available in ${configFilename}');
    }

    public function testNothing()
    {
    }
}
