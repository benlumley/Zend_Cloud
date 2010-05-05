<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	protected function _initConfig()
	{
		return  new Zend_Config($this->getOptions());
	}
	
	protected function _initResourceInjector()
    {
        Zend_Controller_Action_HelperBroker::addHelper(
            new CloudExplorer_ResourceInjector()
        );
    }
}
