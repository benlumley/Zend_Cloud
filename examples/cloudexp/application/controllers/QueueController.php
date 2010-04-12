<?php

class QueueController extends Zend_Controller_Action
{
    
    public $dependencies = array('config');
    
    /**
     * @var Zend_Cloud_QueueService_QueueService
     */
    protected $_queue = null;

    public function preDispatch()
    {
        $this->_queue = Zend_Cloud_QueueService_Factory::getAdapter($this->config->queue);
    }

    public function indexAction()
    {
        $this->view->qs = $this->_queue->listQueues();
    }

    public function createAction()
    {
        $request = $this->getRequest();
        if(!$request->isPost()) {
            return;
        }
        $name = $this->_getParam('name', false);
        if(!$name) {
            return;
        }
        $this->_queue->createQueue($name);
        return $this->_helper->redirector('index');
    }

    public function sendAction()
    {
        $this->view->qs = $this->_queue->listQueues();
    	$request = $this->getRequest();
        $name = $this->view->name = $this->_getParam('name', false);
     	if(!$name) {
            return;
        }
        if(!$request->isPost()) {
            return;
        }
        $message = $this->_getParam('message', false);
     	if(!$message) {
            return;
        }
        $ret = $this->_queue->sendMessage($name, $message);
        return $this->_helper->redirector('index');
    }

    public function receiveAction()
    {    
        $this->view->qs = $this->_queue->listQueues();
    	$request = $this->getRequest();
        $name = $this->view->name = $this->_getParam('name', false);
     	if(!$name) {
            return;
        }
        $messages = $this->_queue->receiveMessages($name);
        foreach($messages as $msg) {
        	$texts[] = $msg->getBody();
        	// remove messages from the queue
        	$this->_queue->deleteMessage($name, $msg);
        }
        $this->view->messages = $texts;
    }

}







