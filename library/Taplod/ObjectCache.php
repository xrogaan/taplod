<?php
/**
 * @category Taplod
 * @package Taplod_ObjectCache
 * @copyright Copyright (c) 2009, Bellière Ludovic
 * @license http://opensource.org/licenses/mit-license.php MIT license
 */

/**
 * @category Taplod
 * @package Taplod_ObjectCache
 * @copyright Copyright (c) 2009, Bellière Ludovic
 * @license http://opensource.org/licenses/mit-license.php MIT license
 */
class Taplod_ObjectCache {
    private static $_instance = null;

    private static $_objects = array();

    private function __construct() {
    }

    public function getInstance() {
        if (self::$_instance == null) {
            self::$_instance = new Taplod_ObjectCache();
        } else {
            return self::$_instance;
        }
    }

    public static function set($tag,$className) {
        $cache = self::getInstance();
        if (!in_array($cache->_objects,$tag)) {
            $cache->_objects[$tag] = $className;
            return true;
        }
        return false;
    }

    public static function get($tag) {
        $cache = self::getInstance();
        if (in_array($cache->_objects,$tag)) {
            return $cache->_objects[$tag];
        }
        return null;
    }

    public function __get($tag) {
        return self::get($tag);
    }

    public function __set($tag,$value) {
        return self::set($tag,$value);
    }
	
	public function __unset($tag) {
		$cache = self::getInstance();
		if (in_array($cache->_objects,$tag)) {
			unset($cache->_objects[$tag]);
		}
	}
	
    public function __isset($tag) {
        if (self::$_instance == null) {
            return false;
        }
        return isset(self::$_objects[$tag]);
    }
}
