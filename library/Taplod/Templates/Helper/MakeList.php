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
	
	/**
	 * Génère une liste html.
	 * Si $items est un tableau multidimentionnel, la liste sera imbriquée
	 *
	 * $attribs peut-être un tableau multi-dimmentionnel contenant les
	 * attributs des éléments li et ul. Dans ce cas, l'élément li sera
	 * lui-même multidimmentionnel pour chaque élément de $items. Chaque
	 * attribut li devrat-être indexé selon le nom (id) de l'éléments $items.
	 * Si $attribs est un simple tableau, il ne sera généré que les attributs
	 * de l'élément ul.
	 *
	 *
	 * @param array $items
	 * @param array $attribs
	 * @return string
	 */
	public function MakeList(array $items,$attribs=false) {
		
		if ($attribs) {
			if (($liExists = isset($attribs['li'])) || ($ulExists = isset($attribs['ul']))) {
				if ($liExists) {
					$tmp = array();
					foreach ($attribs['li'] as $name => $attrib) {
						$attribsLi[$name] = self::_getAttribs($attrib);
					}
				}
				if ($ulExists) {
					$attribs = self::_getAttribs($attribs['ul']);
				}
			} else {
				$attribs = self::_getAttribs($attribs);
			}
		} else {
			$attribs = '';
		}
		
		$list = '';
		foreach ($items as $name => $item) {
			if (is_array($item)) {
				$list.= $this->MakeList($item, $attribs);
			} else {
				$list.= '<li'.(isset($attribsLi[$name]) ? $attribsLi[$name] : '').'>' . $item . '</li>' . "\n";
			}
		}
		
		return '<ul' . $attribs . '>' . "\n" . $list . '</ul>' . "\n";
		
	}
	
	/**
	 * Génère des attributs html en se basant sur un tableau
	 * @param array $data
	 * @return string
	 */
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