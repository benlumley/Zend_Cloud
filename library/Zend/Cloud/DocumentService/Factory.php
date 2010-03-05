<?php
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
 * @package    Zend_Cloud_DocumentService
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

require_once 'Zend/Cloud/FactoryAbstract.php';

class Zend_Cloud_DocumentService_Factory extends Zend_Cloud_FactoryAbstract
{
    const DOCUMENT_ADAPTER_KEY = 'document_adapter';

    private function __construct()
    {
        // private ctor - should not be used
    }
    
    public static function getAdapter($options = array()) 
    {
        $adapter = parent::_getAdapter(self::DOCUMENT_ADAPTER_KEY, $options);
        if(!$adapter) {
            require_once 'Zend/Cloud/DocumentService/Exception.php';
            throw new Zend_Cloud_DocumentService_Exception('Class must be specified using the \'' .
            self::DOCUMENT_ADAPTER_KEY . '\' key');
        }
        return $adapter;
    }
}