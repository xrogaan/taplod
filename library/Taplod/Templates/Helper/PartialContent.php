<?php
/**
 * @category Taplod
 * @package Taplod_Templates
 * @subpackage Helper
 * @copyright Copyright (c) 2009, Bellière Ludovic
 * @license http://opensource.org/licenses/mit-license.php MIT license
 */
 
/**
 * @see Taplod_Templates_Helper_Abstract
 */
require_once 'Taplod/Templates/Helper/Abstract.php';

/**
 * @category Taplod
 * @package Taplod_Templates
 * @subpackage Helper
 * @copyright Copyright (c) 2009, Bellière Ludovic
 * @license http://opensource.org/licenses/mit-license.php MIT license
 */
class Taplod_Templates_Helper_PartialContent extends Taplod_Templates_Helper_Abstract {
	
	protected $page = null;
	protected $partialName = null;
	
	protected function init($partialName,$page) {
		if (!isset($this->partialName) && !isset($this->page)) {
			$this->partialName = $partialName;
			$this->page = $page;
		}
		return $this;
	}

	public function PartialContent($partial,$page,$data=null) {
		
		if ($partial == null && $page == null) {
			require_once 'Taplod/Template/Exception.php';
			throw new Taplod_Template_Exception('');
		} else {
			self::init($partial,$page);
		}
		
		if (is_null($data)) {
			return $this;
		}
		
		$template = $this->cloneTemplates();
		$template->clearVars();
		$url = Taplod_Url::getInstance();
		$template->loadPartialFile('partial-' . $this->partialName, $this->page . '-' . $this->partialName . '.tpl.phtml');
		
		if (is_array($data)) {
			$template->assign($data);
		} else {
			$template->assign('partialVars',$data);
		}
		
		return $template->render('partial-' . $this->partialName);
	}
	
	public function loop(array $data) {
		$content = '';
		$counter = 0;
		foreach($data as $value) {
			$value = array_merge($value,array('partialCounter',$counter));
			$content.= $this->PartialContent(null, null, $value);
			$counter++;
		}
		return $content;
	}
	
	/**
	 * Clone current Template
	 *
	 * Get a fresh and fully configured instance of Tempaltes
	 *
	 * @return Taplod_Templates
	 */
	protected function cloneTemplates() {
		$template = clone $this->template;
		$template->clearVars();
		return $template;
	}
}
