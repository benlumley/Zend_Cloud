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
 * @package    Zend_Cloud_StorageService
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

class Zend_Cloud_FactoryAbstract
{
    private function __construct()
    {
        // private ctor - should not be used
    }
    
    protected static function _getAdapter($adapterOption, $options) 
    {
        if($options instanceof Zend_Config) {
            $options = $options->toArray();
        }
        if(array_key_exists($adapterOption, $options)) {
            $classname = $options[$adapterOption];
            unset($options[$adapterOption]);
            if(!class_exists($classname)) {
                require_once 'Zend/Loader.php';
                Zend_Loader::loadClass($classname);
            }
            return new $classname($options);
        } else {
            return null;
        }
    }
}