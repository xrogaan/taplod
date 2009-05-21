<?php
/**
 * @category Taplod
 * @package Taplod_Templates
 * @subpackage Helper
 * @copyright Copyright (c) 2009, Bellière Ludovic
 * @license http://opensource.org/licenses/mit-license.php MIT license
 */

/**
 * @category Taplod
 * @package Taplod_Templates
 * @subpackage Helper
 * @copyright Copyright (c) 2009, Bellière Ludovic
 * @license http://opensource.org/licenses/mit-license.php MIT license
 */
abstract class Taplod_Templates_Abstract {
	/**
	 * Template object
	 * @var Taplod_Templates
	 */
	public $template;
	
	public function setTemplate(Taplod_Templates $template) {
		$this->template = $template;
		return $this;
	}
}