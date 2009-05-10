<?php
/**
 * @category Taplod
 * @package Taplod_Db
 * @copyright Copyright (c) 2009, Bellière Ludovic
 * @license http://opensource.org/licenses/mit-license.php MIT license
 */

require_once 'Taplod/Loader.php';

/**
 * @category Taplod
 * @package Taplod_Db
 * @copyright Copyright (c) 2009, Bellière Ludovic
 * @license http://opensource.org/licenses/mit-license.php MIT license
 */
class Taplod_Db {

	public static function factory ($adapter, $config = array()) {
		if (!is_array($config)) {
			require_once 'Taplod/Db/Exception.php';
			throw new Taplod_Db_Exception('Adapter parameters must be in an array');
		}
		
		if (!is_string($adapter) || empty($adapter)) {
			/**
			 * @see Taplod_Db_Exception
			 */
			require_once 'Taplod/Db/Exception.php';
			throw new Taplod_Db_Exception('Adapter name must be specified in a string');
		}
		
		$adapterNamespace = 'Taplod_Db_Adapter';
		if (isset($config['adapterNamespace'])) {
			$adapterNamespace = $config['adapterNamespace'];
		}
		
		$adapterName = $adapterNamespace . '_' . $adapter;
	}

}
