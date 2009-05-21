<?php
/**
 * @category Taplod
 * @package Taplod_Url
 * @copyright Copyright (c) 2009, Bellière Ludovic
 * @license http://opensource.org/licenses/mit-license.php MIT license
 */

/**
 * @category Taplod
 * @package Taplod_Url
 * @copyright Copyright (c) 2009, Bellière Ludovic
 * @license http://opensource.org/licenses/mit-license.php MIT license
 */
class Taplod_Url {

	protected $_baseUri = '/';
	protected $_baseUrl = '';
	protected $_uri = '';
	protected $_page;
	protected $_category = false;
	protected $_arguments = array();
	protected $_applicationPath = '';

	protected static $_instance;

	/**
	 * Constructeur
	 *
	 * défini les prérequis.
	 */
	protected function __construct($config) {
		if (!array_key_exists('application_path',$config)) {
			require_once 'Taplod/Url/Exception.php';
			throw new Taplod_Taplod_Url_Exception("Configuration array must have a 'application_path' key.");
		}

		$this->_applicationPath = $config['application_path'];

		if (!array_key_exists('baseUrl',$config)) {
			if (isset($_SERVER['HTTP_HOST'])) {
				$baseUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/';
			} else {
				require_once 'Taplod/Url/Exception.php';
				throw new Taplod_Taplod_Url_Exception("Configuration array must have a 'baseUrl' key.");
			}
		}
		$this->_baseUrl = $config['baseUrl'];

		if (array_key_exists('baseUri',$config)) {
			if (substr($config['baseUri'],0,1) != '/') {
				$config['baseUri'] = '/' . $config['baseUri'];
			}
			$this->_setBaseUri($config['baseUri']);
		}

		if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] != str_replace(array('http://','/'),'',$this->_baseUrl)) {
			self::redirect(false);
		}

		self::_init();
	}

	/**
	 * Singleton instance
	 *
	 * @param array $config
	 * @return Url
	 */
	public static function getInstance($config=array()) {
		if (null === self::$_instance) {
			self::$_instance = new self($config);
		}

		return self::$_instance;
	}

	public function init() {}

	/**
	 * Initialise toute les données nécessaire au bon déroulement des opérations
	 *
	 * La fonction va récolter le nom de la page et le niveau a laquelle est cituée
	 * dans l'url ainsi que les différentes catégories et, si existant, les
	 * différents arguments passé dans l'url.
	 *
	 * @throws Taplod_Url_Exception
	 */
	private function _init() {
		$uri = str_replace($this->getBaseUri(),'',$_SERVER['REQUEST_URI']);

		if ( empty($uri) || $uri[strlen($uri)-1] == '/' ) {
			$uri.= 'index';
		}

		$_uri_data = explode('/',$uri);
		$_count   = count($_uri_data);
		$_pagepos = 0;
		if ($_count > 1) {
			for($i=0; $i<$_count ;$i++) {
				$_data = array_pop($_uri_data);
				if (strpos($_data,':') !== false) {
					$this->_arguments = explode('-',$_data) ;
					$_pagepos++;
				} else {
					if ($i == $_pagepos) {
						if ($_data == 'bootstrap') {
							require_once 'Taplod/Url/Exception.php';
							throw new Taplod_Url_Exception('bootstrap file can\'t be used as page.');
						}
						$this->_page = '/' . $_data;
					} else {
						$this->_category = '/' . $_data . $this->_category;
					}
				}
			}
		} else {
			$this->_page = '/' . $uri;
		}

		if (is_array($this->_arguments) && !empty($this->_arguments)) {
			foreach ($this->_arguments as $action) {
				list($key,$value) = explode(':',$action);
				$_GET[$key] = $value;
			}
			unset($key,$value,$action);
		}

		$this->init();
	}

	/**
	 * Défini l'uri de base
	 */
	private function _setBaseUri($base) {
		$this->_baseUri = $base;
	}

	/**
	 * vérifie si la page courante existe.
	 *
	 * @return boolean
	 */
	protected function _currentPageExists() {
		return $this->pageExists($this->_page,$this->_category);
	}

	/**
	 * retourne la l'uri de base, sans page.
	 *
	 * @return string
	 */
	public function getBaseUri() {
		return $this->_baseUri;
	}

	/**
	 * Retoure l'uri de base pour une page.
	 *
	 * Déprécié depuis que buildUri existe.
	 *
	 * @return string
	 */
	public function getUriForPage($page) {
		return $this->getBaseUri() . $page;
	}

	/**
	 * vérifie si une page existe dans le path application
	 *
	 * @param string $page
	 * @param string $category
	 * @return boolean
	 */
	public function pageExists($page,$category=false) {
		if ($category) {
			return file_exists($this->_applicationPath . $category . $page . '.php');
		} else {
			return file_exists($this->_applicationPath . '/' . $page . '.php');
		}
	}

	/**
	 * Retourne le chemin complet vers la page courante.
	 *
	 * @throws Taplod_Url_Exception
	 * @return string
	 */
	public function getPagePath() {
		if (!$this->_currentPageExists()) {
			throw new Taplod_Url_Exception('This page (' . $this->_applicationPath . $this->_category . '/' . $this->_page . ') doesn\'t exists.');
		}

		if ($this->_category) {
			return $this->_applicationPath . $this->_category . '/' . $this->_page;
		} else {
			return $this->_applicationPath . $this->_page;
		}
	}

	/**
	 * Renvoie l'utilisateur sur une autre page.
	 *
	 * @see function buildUri
	 * @param array $page
	 * @param string|boolean $anchor
	 * @return void
	 */
	public function redirect($page, $anchor=false) {
		if (!headers_sent($filename, $linenum)) {
			if (is_array($page)) {
				$toPage = call_user_func_array(array('self','buildUri'),$page);
				$toPage.= $anchor ? "#$anchor" : '';
			} else {
				$toPage = $this->getBaseUri() . $page;
			}
			header('Location: ' . $this->_baseUrl . $toPage);
			die;
		} else {
			require_once 'Url/Exception.php';
			throw new Taplod_Url_Exception("Headers already sent in $filename on line $linenum. Cannot redirect.");
		}
	}

	/**
	 * Redirige l'utilisateur vers une page et ajoute un message dans la session.
	 *
	 * @see function buildUri
	 * @param array $page
	 * @param string $message
	 * @return void
	 */
	public function redirectError( $page, $message) {
		self::addMessageInSession($message);
		$this->redirect($page,'redirect_message_box');
	}
	
	public function addMessageInSession($message='') {
		if (session_id() == '') {
			session_name('taplod_default');
			session_start();
		}
		$_SESSION['session_messages'][] = $message;
	}

	/**
	 * Construit une url selon les arguments passé a la fonction
	 *
	 * $page sera le fichier ciblé.
	 * $arguments doit être un tableau key=>value qui sera transformé en chaine
	 *     et passé dans l'uri en tant que key:value-key:value
	 * $category est soit une chaine, soit un array, il représente les différents
	 *     niveau pour arriver a la page.
	 *
	 * @param string $page La page vers laquelle le lien pointera
	 * @param array|boolean $arguments Les arguments qui seront passés a la page
	 * @param array|boolean $category Les dossiers dans lesquels on trouvera la page
	 * @throws Taplod_Url_Exception
	 * @return string
	 */
	public function buildUri($page, $arguments=false, $category=false) {
		if ($arguments !== false) {
			if (!is_array($arguments)) {
				require_once 'Taplod/Url/Exception.php';
				throw new Taplod_Url_Exception ('Argument 2 passed to ' . __CLASS__ . '::' . __FUNCTION__ . ' must be a string, ' . gettype($arguments) . ' given.');
			}
				
			$params = array();
			foreach ($arguments as $key => $value) {
				$params[] = "$key:$value";
			}
		}

		if (!is_bool($category) && !is_array($category)) {
			$category = array($category);
		}
		
		if (!is_bool($category)) {
			$category = implode('/',$category) . '/';
		} else {
			$category = '';
		}
			
		if (!$this->pageExists($page,$category)) {
			// do something -> page doesn't exists.
		}

		return $this->getBaseUri() . $category . $page . ((isset($params)) ? "/" . implode('-',$params) : '');

	}
}