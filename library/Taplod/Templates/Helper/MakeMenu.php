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
class Taplod_Templates_Helper_MakeMenu extends Taplod_Templates_Helper_MakeList {
	
	public function MakeMenu(array $items) {
		$list = '';
		foreach ($items as $pageName => $pageDetails) {
			if (isset($pageDetails['attribs'])) {
				$attribs = self::_getAttribs($pageDetails['attribs']);
			} else {
				$attribs = '';
			}
			if (Taplod_ObjectCache::get('URL')->isCurrentPage($pageName, $pageDetails['url']['category'])) {
				$attribs.= ' class="active"';
			}
			
			$args = isset($pageDetails['url']['arguments']) ? $pageDetails['url']['arguments'] : false;
			$pageDetails['url']['category'] = (array) $pageDetails['url']['category'];
			
			$url = Taplod_ObjectCache::get('URL')->buildUri($pageName, $args, $pageDetails['url']['category']);
			$list.= '<li' . $attribs . '><a href="' . $url . '">' . $pageDetails['displayName'] . '</a></li>' . "\n";
		}
		return '<ul>' . $list . '</ul>';
	}
	 
}