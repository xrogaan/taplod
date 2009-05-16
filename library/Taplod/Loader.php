<?php
/**
 * @category Taplod
 * @package Taplod_Loader
 * @copyright Copyright (c) 2009, Bellière Ludovic
 * @license http://opensource.org/licenses/mit-license.php MIT license
 */

require_once 'Taplod/Exception.php';

/**
 * @category Taplod
 * @package Taplod_Loader
 * @copyright Copyright (c) 2009, Bellière Ludovic
 * @license http://opensource.org/licenses/mit-license.php MIT license
 */
class Taplod_Loader {
	/**
	 * Load a class from a php file.
	 */
	public static function loadClass($class) {
		if (class_exists($class, false) || interface_exists($class, false)) {
			return false;
		}
		
		$file = str_replace('_',DIRECTORY_SEPARATOR,$class) . '.php';
		
		self::loadFile($file);
		
		if (!class_exists($class, false) && !interface_exists($class, false)) {
			require_once 'Taplod/Exception.php';
			throw new Taplod_Exception("Class \"$class\" was not found in the source file \"$file\".");
		}
		
	}
	
	public static function loadFile($filename) {
		$filename = trim($filename);
		
		$path = explode(PATH_SEPARATOR,ini_get('include_path'));
		foreach ($path as $dir) {
			$file = $dir.DIRECTORY_SEPARATOR.$filename;
			if (file_exists($file)) {
				include_once $file;
				return;
			}
		}
		
		require_once 'Taplod/Exception.php';
		throw new Taplod_Exception("File '$file' was not found.");
		
	}
	
	/**
	 * spl_autload() implementation
	 */
	public static function autoload($class) {
		try {
			@self::loadClass($class);
			return $class;
		} catch(Taplod_Exception $e) {
			return false;
		}
	}
	
	/**
	 * Register & unregister autoload class with spl_autload_(un)register
	 *
	 * @param string $class
	 * @param boolean $enabled
	 * @return void
	 * @throws Taplod_Exception if spl_autoload() is not found
	 */
	public static function registerAutoload($class='Taplod_Loader', $enabled = true) {
		if (!function_exists('spl_autoload')) {
			require_once 'Taplod/Exception.php';
			throw new Taplod_Exception('spl_autload doesn\'t exists in this php installation');
		}
		
		self::loadClass($class);
		
		if ($enabled == true) {
			spl_autoload_register(array($class, 'autoload'));
		} else {
			spl_autoload_unregister(array($class, 'autoload'));
		}
	}
}