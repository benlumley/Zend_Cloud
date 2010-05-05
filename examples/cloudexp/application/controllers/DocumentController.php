<?php

class DocumentController extends Zend_Controller_Action
{
    
    public $dependencies = array('config');
    
    /**
     * @var Zend_Cloud_DocumentService_Adapter
     */
    protected $_doc = null;

    public function preDispatch()
    {
        $this->_doc = Zend_Cloud_DocumentService_Factory::getAdapter(
            $this->config->document
        );
    }

    public function indexAction()
    {
        $this->view->collections = $this->_doc->listCollections();
    }

    public function showAction()
    {
        $request = $this->getRequest();
        if (!$name = $this->view->collection = $this->_getParam('collection', false)) {
            return;
        }
        $q = $this->_doc->select("*");
        $this->view->data = $this->_doc->query($name, $q, array(
            Zend_Cloud_DocumentService_Adapter_SimpleDB::RETURN_DOCUMENTS => true
        ));
    }

    public function createAction()
    {    
    	$request = $this->getRequest();
        if (!$request->isPost()) {
            return;
        }
        if (!$name = $this->_getParam('name', false)) {
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
        if (!$request->isPost()) {
            return;
        }
        if (!$name = $this->view->name =  $this->_getParam('name', false)) {
            return;
        }
        if (!$id = $this->_getParam('id', false)) {
            return;
        }
        $fields = array();
        foreach ($this->_getParam('field', array()) as $field) {
            if (!$field["name"]) {
                continue;
            }
        	$fields[$field["name"]] = $field["value"];
        }
        if (empty($fields)) {
        	return;
        }
        $document = new Zend_Cloud_DocumentService_Document($id, $fields);
		$this->_doc->insertDocument($name, $document);
        return $this->_helper->redirector('show', null, null, array("collection" => $name));
    }

    public function deletedocAction()
    {   
    	$request = $this->getRequest();
        if (!$request->isPost()) {
            return;
        }
        if (!$name = $this->view->name =  $this->_getParam('name', false)) {
            return;
        }
        if (!$id = $this->_getParam('id', false)) {
            return;
        }
        $this->_doc->deleteDocument($name, $id);
        return $this->_helper->redirector('show', null, null, array("collection" => $name));
   }
}
