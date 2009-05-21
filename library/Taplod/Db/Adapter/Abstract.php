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
	
	protected $_mark_query_time;
	protected $_queries_log;
	
	
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
		if (!is_array($config)) {
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
		
		if (!array_key_exists('host', $config)) {
			$config['host'] = 'localhost';
		}
		
		$this->_config = array_merge($this->_config, $config);
	}
	
	
	/**
	 * Formate la requête avec les arguments fournis et quote les valeurs de manière intelligente.
	 *
	 * Les %s dans la requête sql sont remplacés par les arguments qui sont transformés en chaînes mysql.
	 * self::_autoQuote( sql, arg1, arg2... )
	 *
	 * Exemple :
	 * <code>
	 * <?php
	 * // give: UPDATE fuck SET a='1' WHERE b='popo'
	 * echo self::_autoQuote( 'UPDATE hop SET a=%s WHERE b=%s', 1,'popo' );
	 * ?>
	 * </code>
	 *
	 * @return string
	 */
	private function _autoQuote() {
		$args = func_get_args();
		list($_, $sql) = each($args);
		
		if (count($args) == 1) return $sql;
		
		$params = array();
		while ( list( $_, $val ) = each($args) ) {
			switch(gettype($val)) {
				case 'integer':
					$type = PDO::PARAM_INT;
					break;
				case 'double':
					$type = PDO::PARAM_INT;
					break;
				case 'boolean':
					$type = PDO::PARAM_BOOL;
					break;
				case NULL:
					$type = PDO::PARAM_NULL;
					break;
				case 'string':
				default:
					$type = PDO::PARAM_STR;
			}
			$params[] = self::quote($val, $type);
		}
		return vsprintf($sql, $params);
	}
	
	public function quote($data,$type) {
		switch ($type) {
			case PDO::PARAM_INT:
				return $data;
				break;
			case PDO::PARAM_BOOL:
				return ($data) ? true : false ;
				break;
			case PDO::PARAM_NULL:
				return 'NULL';
				break;
			case PDO::PARAM_STR:
			default:
				return $this->getConnection()->quote($data);
				break;
		}
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
		
		$t = microtime(true);
		$sql = call_user_func_array(array('self','_autoQuote'), $args);
		$r = $this->getConnection()->query($sql);
		$this->_mark_query_time = microtime(true);
		
		$this->_queries_log[] = array($this->_mark_query_time-$t, 'query', $args);
		
		return $r;
	}
	
	/**
	 * See PDO::exec
	 */
	public function exec() {
		if (is_null($this->_connection)) {
			$this->getConnection();
		}
		$args = func_get_args();
		
		$t = microtime(true);
		$sql = call_user_func_array(array('self','_autoQuote'), $args);
		$r = $this->getConnection()->exec($sql);
		$this->_mark_query_time = microtime(true);
		
		$this->_queries_log[] = array($this->_mark_query_time-$t, 'exec', $args);
		
		return $r;
	}
	
	/**
	 *  Fetches the next row from a result set
	 */
	public function fetch() {
		$args = func_get_args();
		
		$query = call_user_func_array(array('self', 'query'), $args);
		return $query->fetch($this->_fetchMode);
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
	 * Cette fonction va construire une requête d'insertion et va automatiquement
	 * échapper les valeurs selon leurs type.
	 * Exemple: $obj->insert('table',array('id'=>1,'data'=>'Mon premier insert'));
	 *    va passer a query: "INSERT INTO `table` (id, data) VALUES (1, 'Mon premier insert')"
	 *
	 * @see function _autoQuote
	 * @return PDOStatement_Timer
	 */
	public function insert($table, $data) {
		$columns = array();
		$values  = array();
		
		foreach ($data as $key => $value) {
			$columns[] = $key;
			$values[]  = '%s'; //gettype($value) == 'integer' ? '%d' : '%s';
		}
		
		$sqlTemplate[] = 'INSERT INTO `' . $table . '` (' . implode(',', $columns) . ') VALUES (' . implode(',', $values) . ')';
		$sql = call_user_func_array(array('self','_autoQuote'), array_merge($sqlTemplate,array_values($data)));
		self::query($sql);
		return $this->getConnection()->lastInsertId();
	}
	
	/**
	 * Build and exec a update query
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
	 * Add an item into the log
	 */
	protected function _markQueriesLog($pdostatement=false) {
		if ($pdostatement) {
			$this->_queries_log['PDOStatement'][count($this->_queries_log['PDOStatement'])-1][0] += microtime(true)-$this->_mark_query_time;
		} else {
			$this->_queries_log[count($this->_queries_log)-2][0] += microtime(true)-$this->_mark_query_time;
		}
	}
	
	/**
	 * Used to return the logs
	 *
	 * @return array
	 */
	public function getQueriesLog() {
		return $this->_queries_log;
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
	
	public function __call($name,$args) {
		$t = microtime(true);
		$r = call_user_func_array(array($this->_connection,$name),$args);

		/*$this->_mark_query_time = microtime(true);
		$this->_queries_log[] = array($this->_mark_query_time-$t, $name, $args);*/
		
		if ($r instanceof PDOStatement) {
			//return new PDOStatement_Timer($r,$this);
		}
		
		return $r;
	}
}