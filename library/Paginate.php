<?php
/**
 * @category Taplod
 * @package Taplod_Paginate
 * @copyright Copyright (c) 2009, Bellière Ludovic
 * @license http://opensource.org/licenses/mit-license.php MIT license
 */

/**
 * @author Bellière Ludovic
 * @category Taplod
 * @package Taplod_Paginate
 * @copyright Copyright (c) 2009, Bellière Ludovic
 * @license http://opensource.org/licenses/mit-license.php MIT license
 */
class Taplod_Paginate {

	/**
	 * Nombre d'éléments par page.
	 *
	 * @var integer
	 */
	public $limit_by_page=10;
	
	/**
	 * Nombre d'éléments total
	 *
	 * @var integer
	 */
	public $elements=0;
	
	/**
	 * Nombre total de page qui seront générées
	 * 
	 * @var integer
	 */
	public $number_of_pages;
	
	/**
	 * Variable a regarder pour connaître la page courrante.
	 *
	 * @var string
	 */
	protected $page_variable="p";
	
	/**
	 * Numéro de la page courrante
	 * 
	 * @var integer
	 */
	protected $current_page=1;
	
	public function __construct($elements,$limit_by_page=10) {
		$this->limit_by_page = (int) $limit_by_page;
		$this->elements = (int) $elements;
		self::set_current_page();
	}

	public function paginate($pageurl) {
		$this->number_of_pages = ceil($this->elements / $this->limit_by_page);
		if ($this->number_of_pages == 1) {
			return "Page : 1.";
		} else {
                     $pageurl = strpos($pageurl, '?') ? $pageurl.'&amp;' : $pageurl.'?';
			$i=1;
			$txt = ($this->current_page === 1) ? '' : '&lt; <a href="'.$pageurl.$this->page_variable.'='.($this->current_page-1).'">précédent</a> | ';
			while($i < $this->number_of_pages) {
				if ($i == $this->current_page) {
					$txt.= "page $i, ";
				} else {
					$txt.= '<a href="'.$pageurl.$this->page_variable.'='.$i.'" class="paginator">page '.$i.'</a>, ';
				}
				$i++;
			}
			return substr($txt,0,-2).(($this->number_of_pages > $this->current_page) ? ' | <a href="'.$pageurl.$this->page_variable.'='.($this->current_page+1).'">Suivant</a> &gt; ':'').'.';
		}
	}
	
	public function get_sql_limit_statement() {
		$lines = $this->limit_by_page;
		$offset = $this->limit_by_page*($this->current_page-1);
		return "LIMIT $offset, $lines";
	}
	
	protected function set_current_page() {
		if (empty($_GET['p'])) {
			$this->current_page = 1;
		} else {
			$this->current_page = intval($_GET['p']);
		}
	}
	
	public function get_current_page() {
		return $this->current_page;
	}
}

?>