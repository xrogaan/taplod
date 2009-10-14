<?php
/**
 * @category Taplod
 * @package Taplod_Templates
 * @subpackage Helper
 * @copyright Copyright (c) 2009, Bellière Ludovic
 * @license http://opensource.org/licenses/mit-license.php MIT license
 */

require_once 'Taplod/Templates/Helper/MakeList.php';

/**
 * @category Taplod
 * @package Taplod_Templates
 * @subpackage Helper
 * @copyright Copyright (c) 2009, Bellière Ludovic
 * @license http://opensource.org/licenses/mit-license.php MIT license
 */
class Taplod_Templates_Helper_MakeBreadcrumb extends Taplod_Templates_Helper_MakeList {

	public function MakeBreadcrumb(array $items) {
		return parent::MakeList($items, array('ul'=>array('class'=>'breadcrumb')));
	}
	
}
