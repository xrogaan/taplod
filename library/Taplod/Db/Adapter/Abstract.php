<?php
/**
 * @category Taplod
 * @package Taplod_Db
 * @copyright Copyright (c) 2009, Bellière Ludovic
 * @license http://opensource.org/licenses/mit-license.php MIT license
 */

abstract class Taplod_Db_Adapter_Abstract {
	protected $_connection = null;
	protected $_config = array();
	protected $_fetchMode;
	protected $_pdoType;
	
	
	/**
	 * $config est une liste de clef/valeurs nécessaire a la connexion + paramètrage
	 * des adaptateurs.
	 *
	 * clefs requises :
	 * dbname    => nom de la base de donnée liée a l'utilisateur
	 * username  => utilisateur
	 * password  => mot de passe de l'utilisateur
	 * host      => (défaut: localhost)
	 */
	public function __construct($config) {
		if (!is_array($config) {
			require_once 'Taplod/Db/Adapter/Exception.php';
			throw new Taplod_Db_Adapter_Exception('Argument 1 passed to ' . __CLASS__ . '::' . __FUNCTION__ . ' must be an array, ' . gettype($config) . ' given.');
		}
		
		if (!array_key_exists('dbname', $config)) {
			require_once 'Taplod/Db/Adapter/Exception.php';
			throw new Taplod_Db_Adapter_Exception("Configuration array must have a 'dbname' key.");
		}
		
		if (!array_key_exists('username', $config)) {
			require_once 'Taplod/Db/Adapter/Exception.php';
			throw new Taplod_Db_Adapter_Exception("Configuration array must have a 'username' key.");
		}
		
		if (!array_key_exists('password', $config)) {
			require_once 'Taplod/Db/Adapter/Exception.php';
			throw new Taplod_Db_Adapter_Exception("Configuration array must have a 'password' key.");
		}
		
		$this->_config = array_merge($this->_config, $config);
	}
	
	/**
	 * Formate la requête avec les arguments fournis.
	 *
	 * Effectue la requête, la met dans le log de bas de page avec son temps, renvoie la requête.
	 * Les %s dans la requête sql sont remplacés par les arguments qui sont transformés en chaînes mysql.
	 *
	 * $obj->query( sql, arg1, arg2... )
	 * Exemple :
	 * 	$obj->query( 'UPDATE hop SET a=%s WHERE b=%s', 1,'popo' )
	 * 	donne    UPDATE hop SET a='1' WHERE b='popo'
	 *
	 * @return PDOStatement_Timer
	 */
	public function query () {
		if (is_null($this->_connection)) {
			$this->getConnection();
		}
		$args = func_get_args();
		$sql = call_user_func_array(array('self','_autoQuote'), $args);
		$r = parent::query($sql);
		return $r;
	}
	
	/**
	 * See PDO::exec
	 */
	public function exec($sql) {
		return $this->getConnection()->exec($sql);
	}
	
	/**
	 *  Fetches the next row from a result set 
	 */
	public function fetch($sql) {
		return $this->getConnection()->fetch($sql,$this->_fetchMode);
	}
	
	/**
	 * Exécute la requête et renvoie tous ses résultats dans un tableau de tableaux, groupés par la colonne $key
	 *
	 * exemple: $result[$row[$key]][] = $row;
	 *
	 * @return array
	 */
	public function fetchAllGroupBy() {
		$args = func_get_args();
		
		if (count($args) < 2) {
			require_once 'Taplod/Db/Adapter/Exception.php';
			throw new Taplod_Db_Adapter_Exception('fetchAllGroupBy need more argument ('.count($args).')');
			die;
		}
		
		$key = array_shift($args);
		$query = call_user_func_array(array('self', 'query'), $args);
		
		$result = array();
		while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
			$result[$row[$key]][] = $row;
		}
		
		$this->_markQueriesLog();
		return $result;
	}
	
	/**
	 * Exécute la requête et renvoie tous ses résultats dans un tableau indexé sur le champ $key
	 *
     * exemple : $r = $obj->fetchAllAsDict( 'id', "SELECT * FROM users" )
     * $r[1] = user d'ID 1
	 * @return array
	 */
	public function fetchAllAsDict() {
		$args = func_get_args();
		
		if (count($args) < 2) {
			require_once 'Taplod/Db/Adapter/Exception.php';
			throw new Taplod_Db_Adapter_Exception('fetchAllAsDict need more argument ('.count($args).')');
			die;
		}
		
		$key = array_shift($args);
		$query = call_user_func_array(array('self', 'query'), $args);
		
		$result = array();
		while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
			$result[$row[$key]] = $row;
		}
		
		$this->_markQueriesLog();
		return $result;
	}
	
	/**
	 * Exécute la requête et renvoie tous ses résultats dans un tableau indexé sur le champ $key
	 *
     * exemple : $r = $obj->fetchAllAsDict( 'id', "SELECT * FROM users" )
     * $r[1] = user d'ID 1
	 * @return array
	 */
	public function fetchAllAsDict2() {
		$args = func_get_args();
		
		if (count($args) < 3) {
			require_once 'Taplod/Db/Adapter/Exception.php';
			throw new Taplod_Db_Adapter_Exception('fetchAllAsDict need more argument ('.count($args).')');
			die;
		}
		
		$key1 = array_shift($args);
		$key2 = array_shift($args);
		$query = call_user_func_array(array('self', 'query'), $args);
		
		$result = array();
		while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
			$result[$row[$key1]][$row[$key2]] = $row;
		}
		
		$this->_markQueriesLog();
		return $result;
	}
	
	/**
	 * Exécute la requête et renvoie tous ses résultats dans un tableau indexé clé => valeur.
	 *
	 * La clé est le premier champ du SELECT, la valeur le second.
	 * Pratique pour foreach( $r as $key=>$value ) echo "$key : $value";
	 * exemple: $r = $obj->fetchPairs( 'SELECT id, name FROM table' )
	 * $r[1] = le nom du truc dont l'ID est 1.
	 *
	 * @return array
	 */
	public function fetchPairs() {
		$args = func_get_args();
		$query = call_user_func_array(array('self', 'query'), $args);
		
		$result = array();
		while( $row = $query->fetch(PDO::FETCH_NUM) ) {
			$result[$row[0]] = $row[1];
		}
		$this->_markQueriesLog();
		return $result;
	}
	
	/**
	 * Build & exec insert query
	 *
	 * @return PDOStatement_Timer
	 */
	public function insert($table, $data) {
		$columns = array();
		$values  = array();
		
		foreach ($data as $key => $value) {
			$columns[] = $key;
			$values[]  = '%s';
		}
		
		$sql = 'INSERT INTO ' . $table . '(' . implode(',', $columns) . ') VALUES (' . implode(',', $values) . ')';
		$sql = vsprintf($sql, array_map(array('self', '_autoQuote')), array_values($data));
		return self::query($sql);
	}
	
	/**
	 *
	 */
	public function update($table,array $data,$where) {
		if (!is_string($where)) {
			require_once 'Taplod/Db/Adapter/Exception.php';
			throw new Taplod_Db_Adapter_Exception('Argument 3 passed to ' . __CLASS__ . '::' . __FUNCTION__ . ' must be a string, ' . gettype($where) . ' given.');
			die;
		}
		
		$set = array();
		foreach($data as $key => $value) {
			$set[] = $key . '=' . self::_autoQuote($value);
		}
		if ($set) {
			return self::exec('UPDATE ' . $table . ' SET ' . implode(',', $set) . $where );
		} else {
			return false;
		}
	}
	
	/**
	 * Construit un morceau de requête sql.
	 *
	 * @param array $data
	 * @param array $where Optionnal
	 * @return string|void
	 */
	public function where(array $data,array $where=array()) {
		foreach ($data as $key => $value) {
			$where[] = is_null($value) ? $key . ' IS NULL' : $key . '=' . self::_autoQuote($value);
		}
		
		if ($where) {
			return 'WHERE ' . implode(' AND ',$where);
		} else {
			return '';
		}
	}
	
	/**
	 * Get initialized instance of an adapter or create one.
	 * @return Taplod_Db_Adapter_Abstract
	 */
	public function getConnection() {
		$this->_connect();
		return $this->_connection;
	}
	
	/**
     * Creates a connection to the database.
     *
     * @return void
     */
	abstract protected function _connect();
	
	/**
     * Test if a connection is active
     *
     * @return boolean
     */
    abstract public function isConnected();

    /**
     * Force the connection to close.
     *
     * @return void
     */
    abstract public function closeConnection();
}