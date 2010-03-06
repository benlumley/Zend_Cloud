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

require_once 'Zend/Service/Amazon/Sqs.php';
require_once 'Zend/Cloud/QueueService.php';
require_once 'Zend/Cloud/QueueService/Exception.php';

/**
 * SQS adapter for simple queue service.
 *
 * @category   Zend
 * @package    Zend_Cloud
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_QueueService_Adapter_SQS implements Zend_Cloud_QueueService
{
    /*
     * Options array keys for the SQS adapter.
     */
    const HTTP_ADAPTER = 'HTTP Adapter';
    const VISIBILITY_TIMEOUT = 'Visibility Timeout';
    const AWS_ACCESS_KEY = 'aws_accesskey';
    const AWS_SECRET_KEY = 'aws_secretkey';

    /**
     * Defaults
     */
    const CREATE_TIMEOUT = 30;

    /**
     * SQS service instance.
     * @var Zend_Service_Amazon_Sqs
     */
    protected $_sqs;

    public function __construct($options = array()) 
    {
        try {
            $this->_sqs = new Zend_Service_Amazon_Sqs($options[self::AWS_ACCESS_KEY],
                                                  $options[self::AWS_SECRET_KEY]);
        } catch(Zend_Service_Amazon_Exception $e) {
            throw new Zend_Cloud_QueueService_Exception('Error on create: '.$e->getMessage(), $e->getCode(), $e);
        }

        if(isset($options[self::HTTP_ADAPTER])) {
            $httpAdapter = $options[self::HTTP_ADAPTER];
            $this->_sqs->getHttpClient()->setAdapter($httpAdapter);
        } 
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
    public function createQueue($name, $options = null) 
    {
        try {
            return $this->_sqs->create($name, $options[self::CREATE_TIMEOUT]);
        } catch(Zend_Service_Amazon_Exception $e) {
            throw new Zend_Cloud_QueueService_Exception('Error on queue creation: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Delete a queue. All messages in the queue will also be deleted.
     *
     * @param  string $queueId
     * @param  array  $options
     * @return boolean true if successful, false otherwise
     */
    public function deleteQueue($queueId, $options = null) {
        try {
            return $this->_sqs->delete($queueId);
        } catch(Zend_Service_Amazon_Exception $e) {
            throw Zend_Cloud_QueueService_Exception::adapterException('Error on queue deletion: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * List all queues.
     *
     * @param  array $options
     * @return array Queue IDs
     */
    public function listQueues($options = null) {
        try {

            return $this->_sqs->getQueues();
        } catch(Zend_Service_Amazon_Exception $e) {
            throw new Zend_Cloud_QueueService_Exception('Error on listing queues: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Get a key/value array of metadata for the given queue.
     *
     * @param  string $queueId
     * @param  array  $options
     * @return array
     */
    public function fetchQueueMetadata($queueId, $options = null) {
        try {
            // TODO: ZF-9050 Fix the SQS client library in trunk to return all attribute values
            $attributes = $this->_sqs->getAttribute($queueId, 'All');
            if(is_array($attributes)) {
                return $attributes;
            } else {
                return array('All' => $this->_sqs->getAttribute($queueId, 'All'));
            }
        } catch(Zend_Service_Amazon_Exception $e) {
            throw new Zend_Cloud_QueueService_Exception('Error on fetching queue metadata: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Store a key/value array of metadata for the specified queue.
     * WARNING: This operation overwrites any metadata that is located at
     * $destinationPath. Some adapters may not support this method.
     *
     * @param  array  $metadata
     * @param  string $queueId
     * @param  array  $options
     * @return void
     */
    public function storeQueueMetadata($queueId, $metadata, $options = null) 
    {
        // TODO Add support for SetQueueAttributes to client library
        require_once 'Zend/Cloud/OperationNotAvailableException.php';
        throw new Zend_Cloud_OperationNotAvailableException('Amazon SQS doesn\'t currently support storing metadata');
    }

    /**
     * Send a message to the specified queue.
     *
     * @param  string $message
     * @param  string $queueId
     * @param  array  $options
     * @return string Message ID
     */
    public function sendMessage($queueId, $message, $options = null) 
    {
        try {
            return $this->_sqs->send($queueId, $message);
        } catch(Zend_Service_Amazon_Exception $e) {
            throw new Zend_Cloud_QueueService_Exception('Error on sending message: '.$e->getMessage(), $e->getCode(), $e);
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
    public function receiveMessages($queueId, $max = 1, $options = null) 
    {
        try {
            return $this->_sqs->receive($queueId, $max, $options[self::VISIBILITY_TIMEOUT]);
        } catch(Zend_Service_Amazon_Exception $e) {
            throw new Zend_Cloud_QueueService_Exception('Error on recieving messages: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Delete the specified message from the specified queue.
     *
     * @param  string $messageId
     * @param  string $queueId
     * @param  array  $options
     * @return void
     */
    public function deleteMessage($queueId, $messageId, $options = null) 
    {
        try {
            return $this->_sqs->deleteMessage($queueId, $messageId);
        } catch(Zend_Service_Amazon_Exception $e) {
            throw new Zend_Cloud_QueueService_Exception('Error on deleting a message: '.$e->getMessage(), $e->getCode(), $e);
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
    public function peekMessage($queueId, $messageId, $options = null) 
    {
        require_once 'Zend/Cloud/OperationNotAvailableException.php';
        throw new Zend_Cloud_OperationNotAvailableException(
        	'Amazon SQS doesn\'t currently support message peeking'
        );
    }

    /**
     * Get SQS implementation
     * @return Zend_Service_Amazon_Sqs 
     */
    public function getAdapter()
    {
        return $this->_sqs;
    }
}