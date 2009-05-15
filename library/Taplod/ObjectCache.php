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
            self::$_instance = new self();
        } else {
            return self::$_instance;
        }
    }

    public static function set($tag,$className) {
        $cache = self::getInstance();
        if (!array_key_exists($tag,$cache->_objects)) {
            $cache->_objects[$tag] = $className;
            return true;
        }
        return false;
    }

    public static function get($tag) {
        $cache = self::getInstance();
        if (!array_key_exists($tag,$cache->_objects)) {
            require_once 'Taplod/Exception.php';
			throw new Taplod_Exception('No entry registered for '. $tag);
        }
        return $cache->_objects[$tag];
    }
	
	public static function isCached($tag) {
		$cache = self::getInstance();
		return array_key_exists($tag,$cache->_objects)
	}

    public function __get($tag) {
		$cache = self::getInstance();
        return $cache->get($tag);
    }

    public function __set($tag,$value) {
		$cache = self::getInstance();
        return $cache->set($tag,$value);
    }
	
	public function __unset($tag) {
		$cache = self::getInstance();
		if (array_key_exists($tag,$cache->_objects)) {
			unset($cache->_objects[$tag]);
		}
	}
	
    public function __isset($tag) {
        if (self::$_instance == null) {
            return false;
        }
        return array_key_exists($tag,$cache->_objects);
    }
}
