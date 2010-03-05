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
require_once 'Zend/Service/WindowsAzure/Storage/Queue.php';
require_once 'Zend/Cloud/QueueService.php';
require_once 'Zend/Cloud/QueueService/Exception.php';

/**
 * WindowsAzure adapter for simple queue service.
 *
 * @category   Zend
 * @package    Zend_Cloud
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_QueueService_Adapter_WindowsAzure implements Zend_Cloud_QueueService
{
    /*
     * Options array keys for the Windows Azure adapter.
     */
    const ACCOUNT_NAME = 'storage_accountname';
    const ACCOUNT_KEY = 'storage_accountkey';
    const HOST = "storage_host";
    const HTTP_ADAPTER = 'HTTP Adapter';
    const PROXY_HOST = "storage_proxy_host";
    const PROXY_PORT = "storage_proxy_port";
    const PROXY_CREDENTIALS = "storage_proxy_credentials";
    // list options
    const LIST_PREFIX = 'prefix';
    const LIST_MAX_RESULTS = 'max_results';
    const LIST_MARKER = "marker";
    // message options
    const MESSAGE_TTL = 'ttl';
    const VISIBILITY_TIMEOUT = 'Visibility Timeout';
    /**
     * Storage client
     * 
     * @var Zend_Service_Azure_Storage_Queue
     */
    protected $_storageClient = null;
    
    public function __construct ($options = array())
    {
        // Build Zend_Service_WindowsAzure_Storage_Blob instance
        if (! isset($options[self::HOST]))
            throw new Zend_Cloud_Storage_Exception('No Windows Azure host name provided.');
        if (! isset($options[self::ACCOUNT_NAME]))
            throw new Zend_Cloud_Storage_Exception('No Windows Azure account name provided.');
        if (! isset($options[self::ACCOUNT_KEY]))
            throw new Zend_Cloud_Storage_Exception('No Windows Azure account key provided.');
            // TODO: support $usePathStyleUri and $retryPolicy
        $this->_storageClient = new Zend_Service_WindowsAzure_Storage_Queue(
            $options[self::HOST], $options[self::ACCOUNT_NAME], $options[self::ACCOUNT_KEY]);
        // Parse other options
        if (! empty($options[self::PROXY_HOST])) {
            $proxyHost = $options[self::PROXY_HOST];
            $proxyPort = isset($options[self::PROXY_PORT]) ? $options[self::PROXY_PORT] : 8080;
            $proxyCredentials = isset($options[self::PROXY_CREDENTIALS]) ? $options[self::PROXY_CREDENTIALS] : '';
            $this->_storageClient->setProxy(true, $proxyHost, $proxyPort, $proxyCredentials);
        }
        if (isset($options[self::HTTP_ADAPTER])) {
            $this->_storageClient->setHttpClientChannel($httpAdapter);
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
    public function createQueue ($name, $options = null)
    {
        try {
            return $this->_storageClient->createQueue($name, $options);
        } catch (Zend_Service_WindowsAzure_Exception $e) {
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
    public function deleteQueue ($queueId, $options = null)
    {
        try {
            if($queueId instanceof Zend_Service_WindowsAzure_Storage_QueueInstance) {
                $queueId = $queueId->Name;
            }
            return $this->_storageClient->deleteQueue($queueId);
        } catch (Zend_Service_WindowsAzure_Exception $e) {
            throw new Zend_Cloud_QueueService_Exception('Error on queue deletion: '.$e->getMessage(), $e->getCode(), $e);
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
        $prefix = $maxResults = $marker = null;
        if (is_array($options)) {
            isset($options[self::LIST_PREFIX]) ? $prefix = $options[self::LIST_PREFIX] : null;
            isset($options[self::LIST_MAX_RESULTS]) ? $maxResults = $options[self::LIST_MAX_RESULTS] : null;
            isset($options[self::LIST_MARKER]) ? $marker = $options[self::LIST_MARKER] : null;
        }
        try {
            return $this->_storageClient->listQueues($prefix, $maxResults, $marker);
        } catch (Zend_Service_WindowsAzure_Exception $e) {
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
    public function fetchQueueMetadata ($queueId, $options = null)
    {
        try {
            if($queueId instanceof Zend_Service_WindowsAzure_Storage_QueueInstance) {
                $queueId = $queueId->Name;
            }
            return $this->_storageClient->getQueueMetadata($queueId);
        } catch (Zend_Service_WindowsAzure_Exception $e) {
            throw new Zend_Cloud_QueueService_Exception('Error on fetching queue metadata: '.$e->getMessage(), $e->getCode(), $e);
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
            if($queueId instanceof Zend_Service_WindowsAzure_Storage_QueueInstance) {
                $queueId = $queueId->Name;
            }
            return $this->_storageClient->setQueueMetadata($queueId, $metadata);
        } catch (Zend_Service_WindowsAzure_Exception $e) {
            throw new Zend_Cloud_QueueService_Exception('Error on setting queue metadata: '.$e->getMessage(), $e->getCode(), $e);
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
            if($queueId instanceof Zend_Service_WindowsAzure_Storage_QueueInstance) {
                $queueId = $queueId->Name;
            }
            return $this->_storageClient->putMessage($queueId, $message, 
                $options[self::MESSAGE_TTL]);
        } catch (Zend_Service_WindowsAzure_Exception $e) {
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
    public function receiveMessages ($queueId, $max = 1, $options = null)
    {
        try {
            if($queueId instanceof Zend_Service_WindowsAzure_Storage_QueueInstance) {
                $queueId = $queueId->Name;
            }
            return $this->_storageClient->getMessages($queueId, $max, 
                $options[self::VISIBILITY_TIMEOUT], false);
        } catch (Zend_Service_WindowsAzure_Exception $e) {
            throw new Zend_Cloud_QueueService_Exception('Error on recieving messages: '.$e->getMessage(), $e->getCode(), $e);
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
            if($queueId instanceof Zend_Service_WindowsAzure_Storage_QueueInstance) {
                $queueId = $queueId->Name;
            }
            if($message instanceof Zend_Service_WindowsAzure_Storage_QueueMessage) {
                return $this->_storageClient->deleteMessage($queueId, $message);
            } else {
                throw new Zend_Cloud_QueueService_Exception('Cannot delete the message: message object required');
            }
        } catch (Zend_Service_WindowsAzure_Exception $e) {
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
    public function peekMessage ($queueId, $messageId, $options = null)
    {
        require_once 'Zend/Cloud/OperationNotAvailableException.php';
        throw new Zend_Cloud_OperationNotAvailableException('WindowsAzure doesn\'t currently support message peeking');
    }
    
    /**
     * Get Azure implementation
     * @return Zend_Service_Azure_Storage_Queue 
     */
    public function getAdapter()
    {
        return $this->_storageClient;
    }
    
}