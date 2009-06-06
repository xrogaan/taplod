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
    private $_objects;

    private function __construct() {
		$this->_objects = array();
    }

    public function getInstance() {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
		return self::$_instance;
    }

    /**
     * Add a row to the registry
     * @param string $tag Nom de l'entrée
     * @param $classRef
     * @return boolean
     */
    public static function set($tag,&$classRef) {
        $cache = self::getInstance();
        if (!array_key_exists($tag,$cache->_objects)) {
            $cache->_objects[$tag] = &$classRef;
            return true;
        }
        return false;
    }

    /**
     * Retourne l'objet enregistré, s'il existe.
     * @param $tag
     * @return object
     */
    public static function get($tag) {
        $cache = self::getInstance();
        if (!array_key_exists($tag,$cache->_objects)) {
            require_once 'Taplod/Exception.php';
			throw new Taplod_Exception('No entry registered for '. $tag);
        }
        return $cache->_objects[$tag];
    }
	
    /**
     * Vérifie si l'entrée $tag existe dans le registre.
     * @param $tag
     * @return boolean
     */
	public static function isCached($tag) {
		$cache = self::getInstance();
		return array_key_exists($cache->_objects,$tag);
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
        return array_key_exists($tag,self::$_instance->_objects);
    }
}
