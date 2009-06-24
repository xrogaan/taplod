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
class Taplod_Templates_Helper_MakeList extends Taplod_Templates_Helper_Abstract {
	
	public function MakeList(array $items,$attribs=false) {
		$list = '';
		foreach ($items as $item) {
			if (is_array($item)) {
				$list.= $this->MakeList($item, $attribs);
			} else {
				$list.= '<li>' . $item . '</li>' . "\n";
			}
		}
		
		if ($attribs) {
			$attribs = self::_getAttribs($attribs);
		} else {
			$attribs = '';
		}
		
		return '<ul' . $attribs . '>' . "\n" . $list . '</ul>' . "\n";
		
	}
	
	private function _getAttribs($data) {
		$attribs = '';
		foreach ($data as $name => $attrib) {
			if (strpos($attrib,'"')) {
				$attribs.=" $name='$attrib'";
			} else {
				$attribs.=' ' . $name . '="'.$attrib.'"';
			}
		}
		return $attribs;
	}
}