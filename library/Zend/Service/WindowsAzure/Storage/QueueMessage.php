<?php
/**
 * Copyright (c) 2009, RealDolmen
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of RealDolmen nor the
 *       names of its contributors may be used to endorse or promote products
 *       derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY RealDolmen ''AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL RealDolmen BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage Storage
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: BlobContainer.php 17553 2009-05-15 10:40:55Z unknown $
 */

/**
 * @see Zend_Service_WindowsAzure_Exception
 */
require_once 'Zend/Service/WindowsAzure/Exception.php';


/**
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage Storage
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 *   
 * @property string $MessageId         Message ID
 * @property string $InsertionTime     Insertion time
 * @property string $ExpirationTime    Expiration time
 * @property string $PopReceipt  	   Receipt verification for deleting the message from queue.
 * @property string $TimeNextVisible   Next time the message is visible in the queue
 * @property string $MessageText       Message text
 */
class Zend_Service_WindowsAzure_Storage_QueueMessage
{
    /**
     * Data
     * 
     * @var array
     */
    protected $_data = null;
    
    /**
     * Constructor
     * 
     * @param string $messageId         Message ID
     * @param string $insertionTime     Insertion time
     * @param string $expirationTime    Expiration time
     * @param string $popReceipt  	    Receipt verification for deleting the message from queue.
     * @param string $timeNextVisible   Next time the message is visible in the queue
     * @param string $messageText       Message text
     */
    public function __construct($messageId, $insertionTime, $expirationTime, $popReceipt, $timeNextVisible, $messageText) 
    {
        $this->_data = array(
            'messageid'       => $messageId,
            'insertiontime'   => $insertionTime,
            'expirationtime'  => $expirationTime,
            'popreceipt'      => $popReceipt,
            'timenextvisible' => $timeNextVisible,
            'messagetext'     => $messageText
        );
    }
    
    /**
     * Magic overload for setting properties
     * 
     * @param string $name     Name of the property
     * @param string $value    Value to set
     */
    public function __set($name, $value) {
        if (array_key_exists(strtolower($name), $this->_data)) {
            $this->_data[strtolower($name)] = $value;
            return;
        }

        throw new Exception("Unknown property: " . $name);
    }

    /**
     * Magic overload for getting properties
     * 
     * @param string $name     Name of the property
     */
    public function __get($name) {
        if (array_key_exists(strtolower($name), $this->_data)) {
            return $this->_data[strtolower($name)];
        }

        throw new Exception("Unknown property: " . $name);
    }
}
