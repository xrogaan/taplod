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
	 * $data est
	 *
	 * @param array $items
	 * @param array $attribs
	 * @param array $data
	 * @return string
	 */
	public function MakeList(array $items,$attribs=false,$data=false) {
		
		if ($attribs) {
			if (($liExists = isset($attribs['li']))) {
				$liAttribs = $attribs['li'];
			}
			if (isset($attribs['ul'])) {
				$attribs = $attribs = self::_getAttribs($attribs['ul']);
			} else {
				$attribs = '';
			}
			
			if ($liExists) {
				$tmp = array();
				foreach ($liAttribs as $name => $attrib) {
					$attribsLi[$name] = self::_getAttribs($attrib);
				}
			}
		} else {
			$attribs = '';
		}
		
		$list = '';
		foreach ($items as $name => $item) {
			if (is_array($item)) {
				$toPass = (isset($data[$name])) ? $data[$name] : false;
				$list.= $this->MakeList($item, $attribs, $toPass);
			} else {
				if ($data && isset($data[$item])) {
					$item = $data[$item];
				}
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
	protected function _getAttribs($data) {
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