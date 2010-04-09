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
 * @see Zend_Cloud_QueueService_QueueService
 */
require_once 'Zend/Cloud/QueueService/QueueService.php';
/**
 * @see Zend_Config_Ini
 */
require_once 'Zend/Config/Ini.php';

/**
 * @see Zend_Cloud_Queue_Factory
 */
require_once 'Zend/Cloud/QueueService/Factory.php';

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
abstract class Zend_Cloud_QueueService_TestCase extends PHPUnit_Framework_TestCase
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

    public function setUp()
    {
        $this->_config = $this->_getConfig();
        $this->_commonQueue = Zend_Cloud_QueueService_Factory::getAdapter($this->_config);
    }
    
    public function testCreateQueue()
    {
        try {
            // Create timeout default should be 30 seconds
            $startTime = time();
            $queueURL = $this->_commonQueue->createQueue('test-create-queue');
            $endTime = time();
            $this->assertNotNull($queueURL);
            $this->assertLessThan(30, $endTime - $startTime);
            $this->_commonQueue->deleteQueue($queueURL);
        } catch (Exception $e) {
            if(isset($queueURL)) $this->_commonQueue->deleteQueue($queueURL);
            throw $e;
        }
    }

    public function testDeleteQueue()
    {
        try {
            $queueURL = $this->_commonQueue->createQueue('test-delete-queue');
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
        } catch (Exception $e) {
            if(isset($queueURL)) $this->_commonQueue->deleteQueue($queueURL);
            throw $e;
        }
    }

    public function testListQueues()
    {
        try {
            $queueURL1 = $this->_commonQueue->createQueue('test-list-queue1');
            $this->assertNotNull($queueURL1);
            $queueURL2 = $this->_commonQueue->createQueue('test-list-queue2');
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
        } catch (Exception $e) {
            if(isset($queueURL1)) $this->_commonQueue->deleteQueue($queueURL1);
            if(isset($queueURL2)) $this->_commonQueue->deleteQueue($queueURL2);
            throw $e;
        }
    }

    public function testFetchQueueMetadata()
    {
        try {
            $queueURL = $this->_commonQueue->createQueue('test-fetch-queue-metadata');
            $this->assertNotNull($queueURL);
            $this->_wait();
            $metadata = $this->_commonQueue->fetchQueueMetadata($queueURL);
            $this->assertTrue(is_array($metadata));
            $this->assertGreaterThan(1, count($metadata));
            $this->_commonQueue->deleteQueue($queueURL);
        } catch (Exception $e) {
            if(isset($queueURL)) $this->_commonQueue->deleteQueue($queueURL);
            throw $e;
        }
    }

    public function testStoreQueueMetadata()
    {
        $this->markTestIncomplete();
    }

    public function testSendMessage()
    {
        try {
            $queueURL = $this->_commonQueue->createQueue('test-send-message');
            $this->assertNotNull($queueURL);
            $this->_wait();
            $message = 'testSendMessage - Message 1';
            $this->_commonQueue->sendMessage($queueURL, $message);
            $this->_wait();
            $receivedMessages = $this->_commonQueue->receiveMessages($queueURL);
            $this->assertTrue(is_array($receivedMessages));
            $this->assertEquals(1, count($receivedMessages));
            $this->assertEquals($message, $receivedMessages[0]->getBody());
		  $this->_commonQueue->deleteQueue($queueURL);
        } catch (Exception $e) {
            if(isset($queueURL)) $this->_commonQueue->deleteQueue($queueURL);
            throw $e;
        }
    }

    public function testReceiveMessages()
    {
        $queueURL = null;
        try {
            $queueURL = $this->_commonQueue->createQueue('test-receive-messages');
            $this->assertNotNull($queueURL);
            $this->_wait();

            $message1 = 'testReceiveMessages - Message 1';
            $message2 = 'testReceiveMessages - Message 2';
            $this->_commonQueue->sendMessage($queueURL, $message1);
            $this->_commonQueue->sendMessage($queueURL, $message2);
            $this->_wait();
            $this->_wait();
            // receive one message
            $receivedMessages1 = $this->_commonQueue->receiveMessages($queueURL);
            $this->assertTrue(is_array($receivedMessages1));
            $this->assertEquals(1, count($receivedMessages1));
            $receivedMessage1 = array_pop($receivedMessages1);
            $this->assertTrue($receivedMessage1 instanceof Zend_Cloud_QueueService_Message);
            // cleanup the queue
            $this->_commonQueue->deleteQueue($queueURL);
            $this->_wait();
            $queueURL = $this->_commonQueue->createQueue('test-receive-messages');
            $this->_wait();
            $this->assertNotNull($queueURL);
            // send 2 messages again
            $this->_commonQueue->sendMessage($queueURL, $message1);
            $this->_commonQueue->sendMessage($queueURL, $message2);
            $this->_wait();
            // receive both messages
            $receivedMessages2 = $this->_commonQueue->receiveMessages($queueURL, 2);
            $this->assertTrue(is_array($receivedMessages2));
            if(count($receivedMessages2) < 2) {
                // try once more
                $this->_wait();
                $receivedMessages22 = $this->_commonQueue->receiveMessages($queueURL, 2);
                $receivedMessages2 = array_merge($receivedMessages2, $receivedMessages22);
            }
            $this->assertEquals(2, count($receivedMessages2));
            $receivedMessage1 = array_pop($receivedMessages2);
            $receivedMessage2 = array_pop($receivedMessages2);
            $texts = array($receivedMessage1->getBody(), $receivedMessage2->getBody());
            $this->assertContains($message1, $texts);
            $this->assertContains($message2, $texts);
            
            $this->_commonQueue->deleteQueue($queueURL);
        } catch (Exception $e) {
            if(isset($queueURL)) $this->_commonQueue->deleteQueue($queueURL);
            throw $e;
        }
    }

    public function testDeleteMessage()
    {
        try {
            $queueURL = $this->_commonQueue->createQueue('test-delete-messages');
            $this->assertNotNull($queueURL);
            $this->_wait();
            $message1 = 'testDeleteMessage - Message 1';
            $this->_commonQueue->sendMessage($queueURL, $message1);
            $this->_wait();
            $receivedMessages1 = $this->_commonQueue->receiveMessages($queueURL);
            // should receive one $message1
            $this->assertTrue(is_array($receivedMessages1));
            $this->assertEquals(1, count($receivedMessages1));
            $this->assertEquals($message1, $receivedMessages1[0]->getBody());
            $receivedMessage1 = array_pop($receivedMessages1);
            $this->_commonQueue->deleteMessage($queueURL, $receivedMessage1);
            $this->_wait();
            // now there should be no messages left
            $receivedMessages2 = $this->_commonQueue->receiveMessages($queueURL);
            $this->assertTrue(is_array($receivedMessages2));
		    $this->assertEquals(0, count($receivedMessages2));
			
		    $this->_commonQueue->deleteQueue($queueURL);
        } catch (Exception $e) {
            if(isset($queueURL)) $this->_commonQueue->deleteQueue($queueURL);
            throw $e;
        }
    }

    public function testPeekMessages()
    {
        try {
            $queueURL = $this->_commonQueue->createQueue('test-peek-messages');
            $this->assertNotNull($queueURL);
            $this->_wait();
            $message1 = 'testPeekMessage - Message 1';
            $this->_commonQueue->sendMessage($queueURL, $message1);
            $this->_wait();
            $peekedMessages = $this->_commonQueue->peekMessages($queueURL, 1);
            $this->assertEquals($message1, $peekedMessages[0]->getBody());
            // and again
            $peekedMessages = $this->_commonQueue->peekMessages($queueURL, 1);
            $this->assertEquals($message1, $peekedMessages[0]->getBody());

            $this->_commonQueue->deleteQueue($queueURL);
        } catch (Exception $e) {
            if(isset($queueURL)) $this->_commonQueue->deleteQueue($queueURL);
            throw $e;
        }
    }

    protected function _wait()
    {
        sleep($this->_waitPeriod);
    }

    /**
     * Get adapter configuration for concrete test
     * 
     * @returns Zend_Config
     */
    abstract protected function _getConfig();
}

class Zend_Cloud_QueueService_Adapter_Skip extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->markTestSkipped('No access key available in ${configFilename}');
    }

    public function testNothing()
    {}
}
