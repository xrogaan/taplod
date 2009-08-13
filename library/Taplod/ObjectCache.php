<?php
/**
 * @category Taplod
 * @package Taplod_ObjectCache
 * @copyright Copyright (c) 2009, Bellière Ludovic
 * @license http://opensource.org/licenses/mit-license.php MIT license
 */
die ('test 1');
/**
 * @category Taplod
 * @package Taplod_ObjectCache
 * @copyright Copyright (c) 2009, Bellière Ludovic
 * @license http://opensource.org/licenses/mit-license.php MIT license
 */
class Taplod_ObjectCache extends CachingIterator {
    private static $_instance = null;

    private function __construct() {
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
        if (!$cache->offsetExists($tag)) {
            $cache->offsetSet($tag,&$classRef);
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
        if ($cache->offsetExists($tag)) {
            return $cache->offsetGet($tag);
        } else {
            require_once 'Taplod/Exception.php';
            throw new Taplod_Exception('No entry registered for '. $tag);
        }
    }

    /**
     * Vérifie si l'entrée $tag existe dans le registre.
     * @param $tag
     * @return boolean
     */
    public static function isCached($tag) {
        $cache = self::getInstance();
        return $cache->offsetExists($tag);
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
        if ($cache->offsetExists($tag)) {
            $cache->offsetUnset($tag);
            return true;
        }
        return false;
    }

    public function __isset($tag) {
        throw exception('__isset is deprecated. Use isCached or offsetExists instead.');
    }
}
