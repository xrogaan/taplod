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

	/**
	 * @param $translation array Translate the raw categories and page name into a more expressive name.
	 * @return string
	 */
	public function MakeBreadcrumb() {
		$url = Taplod_Url::getInstance();
		
		$breadcrumb = array_merge($url->getCurrentCategories(), array($url->getCurrentPage()));

		$attribs = array(
			'ul' => array('class'=>'breadcrumb'),
			'li' => array(
				0 => array( 'class' => 'first' ),
			),
		);
		
		return parent::MakeList(self::translate($breadcrumb), $attribs);
	}

	protected function translate (array $items) {
        if (!Taplod_ObjectCache::isCached('PAGES')) {
            throw new Taplod_Exception('requested component "Taplod_Pages" not initialized.');
        }

        $pages = Taplod_ObjectCache::get('PAGES');
        $translated = array();
        foreach ($items as $k => $name) {
            $translated[$k] = $pages->getLabelFor($name);
        }
        return $translated;
    }
}
