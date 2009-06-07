<?php
/**
 * @category Taplod
 * @package Taplod_Templates
 * @copyright Copyright (c) 2009, Bellière Ludovic
 * @license http://opensource.org/licenses/mit-license.php MIT license
 */

/**
 * @author Bellière Ludovic
 * @category Taplod
 * @package Taplod_Templates
 * @copyright Copyright (c) 2009, Bellière Ludovic
 * @license http://opensource.org/licenses/mit-license.php MIT license
 */
class Taplod_Templates {
	/**
	 * File list
	 *
	 * @var array
	 */
	protected $_files = array();
	protected $_partialFile = array();

	/**
	 * Path to the templates files
	 *
	 * @var string
	 */
	protected $_templatePaths;

	protected $_options = array();

	private $_escape = array('htmlentities');
	private $_data = array();

	protected $_helpers = array();
	private $_helperLoaded = array();

	public function __construct($options=array()) {

		if ($options instanceof Taplod_Config) {
			$options = $options->toArray();
		}
		
		if (!isset($options['templatePath'])) {
			if (defined('TEMPLATE_PATH')) {
				$options['templatePath'] = TEMPLATE_PATH;
			} else {
				require_once 'Taplod/Templates/Exception.php';
				throw new Taplod_Templates_Exception("Can't find template path");
			}
		}
		
		if (!isset($options['templatePartialPath'])) {
			$options['templatePartialPath'] = $this->_templatePath . '/_partial/';
		}
		
		$this->_options = array_merge($this->_options,$options);
	}
	
	/**
	 * Return an option
	 *
	 * @param string $key search for $key in $_options
	 * @param string|null $default default value returned instead of empty data
	 * @return string|array
	 */
	function getOptions($key=null,$default=null) {
		if (is_null($key)) {
			return $this->_options;
		} elseif (isset($this->_options[$key])) {
			return $this->_options[$key];
		} else {
			return $default;
		}
	}

	/**
	 * Return the current tempalte path
	 *
	 * @return string
	 */
	public function getTemplatePath() {
		return $this->_option['templatePath'];
	}

	/**
	 * Add a template file
	 *
	 * @param string $tag Template id
	 * @param string $name Template filename.
	 * @return void
	 */
	public function addFile($tag,$name) {
		$this->_files[$tag] = $name;
		return $this;
	}
	
	public function loadPartialFile($name,$file) {
		if (file_exists($this->getOptions('templatePartialPath') . $file)) {
			$this->_partialFile[$name] = $file;
		}
	}

	/**
	 * Process the templates files by $tag
	 * $tag can be an array for multi-templates page.
	 *
	 * @param array|string $tag
	 */
	public function render($tag) {
		ob_start();
		try {
			if (isset($this->_files['_begin'])) {
				include $this->_file('_begin');
			}
			if (!is_array($tag)) {
				include $this->_file($tag);
			} else {
				foreach ($tag as $ttag) {
					include $this->_file($ttag);
				}
			}
			if (isset($this->_files['_end'])) {
				include $this->_file('_end');
			}
		} catch (Taplod_Exception $e) {
			ob_end_clean();
			throw $e;
			return;
		}
		return ob_get_clean();
	}

	/**
	 * Used to set some functions who escape the content
	 *
	 * @param function $ref
	 */
	public function setEscape($ref) {
		if (!in_array($ref,$this->_escape)) {
			$this->_escape[] = $ref;
		}
	}

	/**
	 * Used to remove a function from the pool of escape
	 *
	 * @param function $ref
	 * @param boolean $id
	 * @return boolean
	 */
	public function remEscape($ref,$id=false) {
		if ($id && isset($this->_escape[$id])) {
			unset($this->_escape[$id]);
			return true;
		} elseif (!$id) {
			foreach ($this->_escape as $key => $val) {
				if ($val == $ref) {
					unset($this->_escape[$key]);
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Force string $var escaping.
	 */
	public function escape($var) {
		foreach($this->_escape as $fnct) {
			if (in_array($fnct, array('htmlentities','htmlspecialchars'))) {
				$var = call_user_func_array($fnct, array($var,ENT_COMPAT,'utf-8'));
			} else {
				$var = call_user_func($fnct, $var);
			}
		}
		return $var;
	}
	
	public function assign($name,$data=null) {
		try {
			if (is_string($name)) {
				self::__set($name, $data);
			} elseif (is_array($name)) {
				foreach ($name as $key => $value) {
					self::__set($key,$vale);
				}
			} else {
				require_once 'Taplod/Templates/Exception.php';
				throw new Taplod_Templates_Exception('Argument 2 passed to ' . __CLASS__ . '::' . __FUNCTION__ . ' must be a string or an array, ' . gettype($arguments) . ' given.');
			}
		} catch (Taplod_Templates_Exception $e) {
			throw $e;
		}
		return $this;
	}
	
	/**
	 * Remove all variable assigned via __set()
	 * @return void
	 */
	public function clearVars () {
		$vars = get_object_vars($template);
		foreach ($vars as $key => $value) {
			if (substr($key, 0, 1) != '_') {
				unset($template->$key);
			}
		}
	}

	public function __set($name,$data) {
		if ('_' != substr($name, 0, 1)) {
			$this->$name = $data;
			return;
		}

		require_once 'Templates/Exception.php';
		throw new Taplod_Templates_Exception('Setting private or protected class members is not allowed.',$this);
	}

	public function __unset($name) {
		if ('_' != substr($name, 0, 1) && isset($this->$name)) {
			unset($this->$name);
		}
	}

	public function __get($name) {
		if ('_' != substr($name,0,1) && isset($this->$name)) {
			return $this->$name;
		}
		require_once 'Templates/Exception.php';
		throw new Taplod_Templates_Exception('Getting private or protected class members is not allowed');
	}

	public function __isset($key) {
		$strpos = mb_strpos($key,'_');
		if (!is_bool($strpos) && $strpos !== 0) {
			return isset($this->$key);
		}
		return false;
	}

	/**
	 * Access helper object from within a script
	 * Based in large part on the example at
	 * http://framework.zend.com/manual/en/zend.view.helpers.html
	 *
	 * @param string $name
	 * @param array $args
	 */
	public function __call($name, $args) {
		// is the helper already loaded?
		$helper = $this->getHelper($name);

		// call the helper method
		return call_user_func_array(
		array($helper, $name),
		$args
		);
	}
	
	public function getHelpers() {
		return $this->_helpers;
	}
	
	public function getHelper($name) {
		$name = strtolower($name);

		$prefix      = 'Taplod_Templates_Helper';
		$prefix_path = 'Taplod/Templates/Helper/';

		if (!array_key_exists($name,$this->_helpers)) {
			$file = $prefix . '_' . ucfirst($name);
			try {
				Taplod_Loader::loadClass($file);
			} catch (Taplod_Exception $exception) {
				require_once 'Taplod/Templates/Exception.php';
				throw new Taplod_Templates_Exception("Cannot load '$name' helper.<br/>" . $exception->getMessage());
			}
			$this->_helpers[$name] = new $file();
			if (method_exists($this->_helpers[$name],'setTemplate')) {
				$this->_helpers[$name]->setTemplate($this);
			}
		}
		return $this->_helpers[$name];
	}

	/**
	 * Check if the template file is readable and returns its name
	 *
	 * @param string $tag
	 * @return string
	 * @throws templates_exception
	 */
	private function _file($tag) {
		if (isset($this->_partialFile[$tag])) {
			return $this->_option['templatePartialPath'] . $this->_partialFile[$tag];
		} else {
			if (is_readable($this->_option['templatePath'] . $this->_files[$tag])) {
				return $this->_option['templatePath'] . $this->_files[$tag];
			} else {
				require_once 'Templates/Exception.php';
				throw new Taplod_Templates_Exception('The file <em>'.$this->_templatePath.$this->_files[$tag]. '</em> isn\'t readable');
			}
		}
	}
}
