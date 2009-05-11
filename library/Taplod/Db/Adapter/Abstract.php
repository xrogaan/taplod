<?php
/**
 * @category Taplod
 * @package Taplod_Db
 * @copyright Copyright (c) 2009, Bellière Ludovic
 * @license http://opensource.org/licenses/mit-license.php MIT license
 */

abstract class Taplod_Db_Adapter_Abstract {
	protected $_connection = null;
	protected $_config;
	protected $_fetchMode;
	
	public function query() {}
	public function fetchAll() {}
	public function fetchAllGroupBy() {}
}