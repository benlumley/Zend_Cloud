<?php

class DocumentController extends Zend_Controller_Action
{
    
    public $dependencies = array('config');
    
    /**
     * @var Zend_Cloud_DocumentService_DocumentService
     */
    protected $_doc = null;

    public function preDispatch()
    {
        $this->_doc = Zend_Cloud_DocumentService_Factory::getAdapter($this->config->document);
    }

    public function indexAction()
    {
        $this->view->collections = $this->_doc->listCollections();
    }

    public function showAction()
    {
        $request = $this->getRequest();
        $name = $this->view->collection = $this->_getParam('collection', false);
        if(!$name) {
            return;
        }
        $q = $this->_doc->select("*");
        $this->view->data = $this->_doc->query($name, $q, array(
        Zend_Cloud_DocumentService_Adapter_SimpleDB::RETURN_DOCUMENTS => true));
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
        $this->_doc->createCollection($name);
        return $this->_helper->redirector('index');
    }

    public function addocAction()
    {
    	$this->view->fieldcount = 5;
    	$this->view->collections = $this->_doc->listCollections();
    	$request = $this->getRequest();
        if(!$request->isPost()) {
            return;
        }
        $name = $this->view->name =  $this->_getParam('name', false);
        if(!$name) {
            return;
        }
		$id = $this->_getParam('id', false);
        if(!$id) {
            return;
        }
        $fields = array();
        foreach($this->_getParam('field', array()) as $field) {
        	if(!$field["name"]) continue;
        	$fields[$field["name"]] = $field["value"];
        }
        if(!$fields) {
        	return;
        }
        $document = new Zend_Cloud_DocumentService_Document($id, $fields);
		$this->_doc->insertDocument($name, $document);
        return $this->_helper->redirector('show', null, null, array("collection" => $name));
    }

    public function deletedocAction()
    {   
    	$request = $this->getRequest();
        if(!$request->isPost()) {
            return;
        }
        $name = $this->view->name =  $this->_getParam('name', false);
        if(!$name) {
            return;
        }
		$id = $this->_getParam('id', false);
        if(!$id) {
            return;
        }
        $this->_doc->deleteDocument($name, $id);
        return $this->_helper->redirector('show', null, null, array("collection" => $name));
   }
}









