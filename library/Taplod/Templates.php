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

    /**
     * Path to the templates files
     *
     * @var string
     */
    protected $_templatePath;
    
    protected $_options = array();

    private $_escape = array('htmlentities');
	private $_data = array();
	
	protected $_helpers = array();

    public function __construct($template_path='',$options=array()) {
		if (!empty($template_path)) {
			$this->_templatePath = $template_path;
		} else {
			$this->_templatePath = TEMPLATE_PATH;
		}
        $this->_options = array_merge($this->_options,$options);
    }

    /**
     * Return the current tempalte path
     *
     * @return string
     */
    public function getTemplatePath() {
        return $this->_templatePath;
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

    /**
     * Process the templates files by $tag
     * $tag can be an array for multi-templates page.
     *
     * @param array|string $tag
     */
    public function render($tag) {
        try {
			//ob_start();
            if (!is_array($tag)) {
                if (isset($this->_files['_begin'])) {
                    include $this->_file('_begin');
                }
                include $this->_file($tag);
                if (isset($this->_files['_end'])) {
                    include $this->_file('_end');
                }
            } else {
                if (isset($this->_files['_begin'])) {
                    include $this->_file('_begin');
                }
                $tags = $tag;
                unset($tag);
                foreach ($tags as $tag) {
                    include $this->_file($tag);
                }
                if (isset($this->_files['_end'])) {
                    include $this->_file('_end');
                }
           }
//            ob_end_flush();
        } catch (templates_exception $e) {
            die ($e->getMessage());
        } catch (Exception $e) {
            die('ex error: '.$e->getMessage());
        }
    }

    /**
     * Check if the template file is readable and returns its name
     *
     * @param string $tag
	 * @return string
	 * @throws templates_exception
     */
    private function _file($tag) {
        if (is_readable($this->_templatePath.$this->_files[$tag])) {
            return $this->_templatePath.$this->_files[$tag];
        } else {
			require_once 'Templates/Exception.php';
            throw new Taplod_Templates_Exception('The file <em>'.$this->_templatePath.$this->_files[$tag]. '</em> isn\'t readable');
        }
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
    
	public function __set($name,$data) {
		if ('_' != substr($name, 0, 1)) {
			$this->$name = $data;
			return;
		}
		
		require_once 'Templates/Exception.php';
		throw new Taplod_Templates_Exception('Setting private or protected class members is not allowed.',$this);
	}
	
	public function __unset($name) {
		if ('_' != substr($name, 0, 1) && isset($this->_data[$name])) {
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
	
	public function getHelper($name) {
		$name = strtolower($name);
	
		$prefix      = 'Taplod_Template_Helper';
		$prefix_path = 'Taplod/Template/Helper/';
		
		if (array_key_exists($name,$this->_helpers)) {
			return $this->_helpers[$name];
		} else {
			$name = ucfirst($name);
			Taplod_Loader::loadClass($prefix . $name);
			$this->_helpers[$name] = new $name();
		}
	}
}
