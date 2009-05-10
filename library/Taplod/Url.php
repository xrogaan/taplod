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
	
	protected static $_instance;

	/**
	 * 
	 */
	protected function __construct($baseUrl=false, $baseUri=false) {
		if (!$baseUrl) {
			if (isset($_SERVER['HTTP_HOST'])) {
				$baseUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/';
			} else {
				require_once 'Taplod/Url/Exception.php';
				throw new Taplod_Taplod_Url_Exception('baseUrl don\'t send');
				die;
			}
		}
		$this->_baseUrl = $baseUrl;

		if ($baseUri) {
			$this->_setBaseUri($baseUri);
		}
		
		if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] != str_replace(array('http://','/'),'',$this->_baseUrl)) {
			header('Location: ' . $this->_baseUrl . '/' . $this->getBaseUri()); 
		}
		
		self::_init();
	}
	
	/**
     * Singleton instance
     *
     * @return Url
     */
	public static function getInstance($baseUrl=false, $baseUri=false) {
        if (null === self::$_instance) {
            self::$_instance = new self($baseUrl, $baseUri);
        }

        return self::$_instance;
    }
	
	public function init() {}
	
	/**
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
							throw new Taplod_Url_Exception('bootstrap file can\'t be used as page.');
							die;
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
	 * 
	 */
	private function _setBaseUri($base) {
		$this->_baseUri = $base;
	}
	
	/**
	 * 
	 */
	protected function _currentPageExists() {
		return $this->pageExists($this->_page,$this->_category);
	}
	
	/**
	 * 
	 */
	public function getBaseUri() {
		return $this->_baseUri;
	}
	
	/**
	 * 
	 */
	public function getUriForPage($page) {
		return $this->getBaseUri() . $page;
	}
	
	/**
	 * 
	 */
	public function pageExists($page,$category=false) {
		if ($category) {
			return file_exists(APPLICATION_PATH . $category . $page . '.php');
		} else {
			return file_exists(APPLICATION_PATH . '/' . $page . '.php');
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
			throw new Taplod_Url_Exception('This page (' . APPLICATION_PATH . $this->_category . '/' . $this->_page . ') doesn\'t exists.');
		}
	
		if ($this->_category) {
			return APPLICATION_PATH . $this->_category . '/' . $this->_page;
		} else {
			return APPLICATION_PATH . $this->_page;
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
	public function redirect(array $page, $anchor=false) {
		$toPage = call_user_func_array(array('self','buildUri'),$page);
		$toPage.= $anchor ? "#$anchor" : '';
		header('Location: '.$this->_baseUrl. $this->getBaseUri() . $toPage);
		die;
	}
	
	/**
	 * Redirige l'utilisateur vers une page et ajoute un message dans la session.
	 *
	 * @see function buildUri
	 * @param array $page
	 * @param string $message
	 * @return void
	 */
	public function redirectError(array $page, $message) {
		addMessageInSession($message);
		$this->redirect($page,'redirect_message_box');
	}
	
	/**
	 * Construit une url selon les arguments passé a la fonction
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
		
		$category = implode('/',$category) . '/';
			
		if (!$this->pageExists($page,$category)) {
			// do something -> page doesn't exists.
		}
		
		return $category . $page . ((isset($params)) ? "/" . implode('-',$params) : '');
		
	}
}