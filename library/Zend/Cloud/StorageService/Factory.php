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

require_once 'Zend/Cloud/StorageService.php';
require_once 'Zend/Loader.php';
require_once 'Zend/Cloud/StorageService/Exception.php';

abstract class Zend_Cloud_StorageService_Factory
{
    const STORAGE_ADAPTER_KEY = 'storage_adapter';

    public static function getAdapter($options = array()) {
        if($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        if(array_key_exists(self::STORAGE_ADAPTER_KEY, $options)) {
            $classname = $options[self::STORAGE_ADAPTER_KEY];
            unset($options[self::STORAGE_ADAPTER_KEY]);
            Zend_Loader::loadClass($classname);
            return new $classname($options);
        } else {
            throw new Zend_Cloud_StorageService_Exception('Class must be specified using the \'' .
            self::STORAGE_ADAPTER_KEY . '\' key.');
        }
    }
}