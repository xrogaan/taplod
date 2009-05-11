<?php
/**
 * @category Taplod
 * @package Taplod_Db
 * @copyright Copyright (c) 2009, Bellière Ludovic
 * @license http://opensource.org/licenses/mit-license.php MIT license
 */

class Taplod_Db_Adapter_Pdo_Mysql extends Taplod_Db_Adapter {
	protected $_fetchMode = PDO::FETCH_ASSOC;
	protected function _connect() {
		
	}
}