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
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
require_once 'Zend/Queue.php';
require_once 'Zend/Cloud/QueueService.php';
require_once 'Zend/Cloud/QueueService/Exception.php';
require_once 'Zend/Cloud/OperationNotAvailableException.php';
/**
 * WindowsAzure adapter for simple queue service.
 *
 * @category   Zend
 * @package    Zend_Cloud
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_QueueService_Adapter_ZendQueue implements Zend_Cloud_QueueService
{
    /*
     * Options array keys for the Zend_Queue adapter.
     */
    const ADAPTER = 'adapter';
    const TIMEOUT = 'timeout';
    
    /**
     * Storage client
     * 
     * @var Zend_Queue
     */
    protected $_queue = null;
    
    public function __construct ($options = array())
    {
        // Build Zend_Service_WindowsAzure_Storage_Blob instance
        if (! isset($options[self::ADAPTER])) {
            throw new Zend_Cloud_Storage_Exception('No Zend_Queue adapter provided');
        } else {
            $adapter = $options[self::ADAPTER];
            unset($options[self::ADAPTER]);
        }
        $this->_queue = new Zend_Queue($adapter, $options);
    }
    
    /**
     * Create a queue. Returns the ID of the created queue (typically the URL).
     * It may take some time to create the queue. Check your vendor's
     * documentation for details.
     *
     * @param  string $name
     * @param  array  $options
     * @return string Queue ID (typically URL)
     */
    public function createQueue ($name, $options = null)
    {
        try {
            return $this->_queue->createQueue($name, isset($options[Zend_Queue::TIMEOUT])?$options[Zend_Queue::TIMEOUT]:null);
        } catch (Zend_Queue_Exception $e) {
            throw new Zend_Cloud_QueueService_Exception('Error on queue creation: '.$e->getMessage(), $e);
        }
    }
    
    /**
     * Delete a queue. All messages in the queue will also be deleted.
     *
     * @param  string $queueId
     * @param  array  $options
     * @return boolean true if successful, false otherwise
     */
    public function deleteQueue ($queueId, $options = null)
    {
        try {
            // TODO: maybe we should track IDs and accept string ID too?
            if($queueId instanceof Zend_Queue) {
                return $queueId->deleteQueue();
            } else {
                throw new Zend_Cloud_QueueService_Exception('Error on queue deletion: queue object should be passed');
            }
        } catch (Zend_Queue_Exception $e) {
            throw new Zend_Cloud_QueueService_Exception('Error on queue deletion: '.$e->getMessage(), $e);
        }
    }
    
    /**
     * List all queues.
     *
     * @param  array $options
     * @return array Queue IDs
     */
    public function listQueues ($options = null)
    {
        try {
            return $this->_queue->listQueues();
        } catch (Zend_Queue_Exception $e) {
            throw new Zend_Cloud_QueueService_Exception('Error on listing queues: '.$e->getMessage(), $e);
        }
    }
    
    /**
     * Get a key/value array of metadata for the given queue.
     *
     * @param  string $queueId
     * @param  array  $options
     * @return array
     */
    public function fetchQueueMetadata ($queueId, $options = null)
    {
        try {
            // TODO: maybe we should track IDs and accept string ID too?
            if($queueId instanceof Zend_Queue) {
                return $queueId->getOptions();
            } else {
                throw new Zend_Cloud_QueueService_Exception('Error on fetching queue metadata: queue object should be passed');
            }
        } catch (Zend_Queue_Exception $e) {
            throw new Zend_Cloud_QueueService_Exception('Error on fetching queue metadata: '.$e->getMessage(), $e);
        }
    }
    
    /**
     * Store a key/value array of metadata for the specified queue.
     * WARNING: This operation overwrites any metadata that is located at 
     * $destinationPath. Some adapters may not support this method.
     * 
     * @param  string $queueId
     * @param  array  $metadata
     * @param  array  $options
     * @return void
     */
    public function storeQueueMetadata ($queueId, $metadata, $options = null)
    {
        try {
            // TODO: maybe we should track IDs and accept string ID too?
            if($queueId instanceof Zend_Queue) {
                return $queueId->setOptions($metadata);
            } else {
                throw new Zend_Cloud_QueueService_Exception('Error on setting queue metadata: queue object should be passed');
            }
        } catch (Zend_Queue_Exception $e) {
            throw new Zend_Cloud_QueueService_Exception('Error on setting queue metadata: '.$e->getMessage(), $e);
        }
    }
    
    /**
     * Send a message to the specified queue.
     * 
     * @param  string $queueId
     * @param  string $message
     * @param  array  $options
     * @return string Message ID
     */
    public function sendMessage ($queueId, $message, $options = null)
    {
        try {
            // TODO: maybe we should track IDs and accept string ID too?
            if($queueId instanceof Zend_Queue) {
                return $queueId->send($message);
            } else {
                throw new Zend_Cloud_QueueService_Exception('Error on sending messag: queue object should be passed');
            }
        } catch (Zend_Queue_Exception $e) {
            throw new Zend_Cloud_QueueService_Exception('Error on sending message: '.$e->getMessage(), $e);
        }
    }
    
    /**
     * Recieve at most $max messages from the specified queue and return the
     * message IDs for messages recieved.
     * 
     * @param  string $queueId
     * @param  int    $max
     * @param  array  $options
     * @return array
     */
    public function receiveMessages ($queueId, $max = 1, $options = null)
    {
        try {
            // TODO: maybe we should track IDs and accept string ID too?
            if($queueId instanceof Zend_Queue) {
                $res = $queueId->receive($max, isset($options[Zend_Queue::TIMEOUT])?$options[Zend_Queue::TIMEOUT]:null);
                if($res instanceof Iterator) {
                    $messages = array();
                    foreach($res as $message) {
                        $messages[] = $message;
                    }
                    return $messages; 
                } else {
                    return $res;
                }
            } else {
                throw new Zend_Cloud_QueueService_Exception('Error on recieving messages: queue object should be passed');
            }
        } catch (Zend_Queue_Exception $e) {
            throw new Zend_Cloud_QueueService_Exception('Error on recieving messages: '.$e->getMessage(), $e);
        }
    }
    
    /**
     * Delete the specified message from the specified queue.
     * 
     * @param  string $queueId
     * @param  Zend_Service_WindowsAzure_Storage_QueueMessage $message Message ID or message 
     * @param  array  $options
     * @return void
     */
    public function deleteMessage ($queueId, $message, $options = null)
    {
        try {
            if(!($message instanceof Zend_Queue_Message)) {
                throw new Zend_Cloud_QueueService_Exception('Cannot delete the message: Zend_Queue_Message object required');
            }
            
            // TODO: maybe we should track IDs and accept string ID too?
            if($queueId instanceof Zend_Queue) {
                return $queueId->deleteMessage($message);
            } else {
                throw new Zend_Cloud_QueueService_Exception('Error on deleting a message: queue object should be passed');
            }
        } catch (Zend_Queue_Exception $e) {
            throw new Zend_Cloud_QueueService_Exception('Error on deleting a message: '.$e->getMessage(), $e);
        }
    }
    
    /**
     * Peek at the specified message from the specified queue.
     * WARNING: This operation may block other receivers from recieving the
     * message until the message is released from the peeker for services
     * that do not natively support message peeking. This may impact
     * performance and/or introduce concurrency issues in your applications.
     * Check your cloud vendor's documentation for more details.
     *
     * @param  string $messageId
     * @param  string $queueId
     * @param  array  $options
     * @return string Message body
     */
    public function peekMessage ($queueId, $messageId, $options = null)
    {
        throw new Zend_Cloud_OperationNotAvailableException('WindowsAzure doesn\'t currently support message peeking');
    }
    
    /**
     * Get Azure implementation
     * @return Zend_Queue 
     */
    public function getAdapter()
    {
        return $this->_queue;
    }
    
}