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

	public function PartialContent($partial,$page,$data=null) {
		$template = $this->cloneTemplates();
		$template->clearVars();
		$url = Taplod_Url::getInstance();
		$template->loadPartialFile('partial-' . $partial, $page . '-' . $partial . '.tpl.phtml');
		
		if (is_array($data)) {
			$template->assign($data);
		}
		
		return $template->render('partial-' . $partial);
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
