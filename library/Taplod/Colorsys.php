<?php
/**
 * @category Taplod
 * @package Taplod_Colorsys
 * @copyright Copyright (c) 2009, Bellière Ludovic
 * @license http://opensource.org/licenses/mit-license.php MIT license
 */

class Taplod_Colorsys {

	const HEX = 'hex';
	const RGB = 'rgb';

	protected $_type;
	protected $_color = array(
		self::HEX => '',
		self::RGB => ''
	);
	protected $_current_color;

	function __construct($color) {
		if (!is_array($color)) {
			if ($color == 'random')
				$color = self::random('hex');
			$color = str_replace('#','',$color);
			$this->_type = self::HEX;
		} else {
			$this->_type = self::RGB;
		}
		
		$this->_current_color = $color;
		$this->_color[$this->_type] = $color;
		$this->_color = array(
			self::RGB => self::hex2rgb(),
			self::HEX => self::rgb2hex(),
		);

	}

	function getRgb() {
		if (isset($this->_color[self::RGB])) {
			return $this->_color[self::RGB];
		} elseif ($this->_type == self::HEX) {
			$this->_color[self::RGB] = self::hex2rgb($this->_current_color);
			return $this->_color[self::RGB];
		} else {
			return $this->_current_color;
		}
	}
	
	function getHex() {
		if (isset($this->_color[self::HEX])) {
			return $this->_color[self::HEX];
		} elseif ($this->_type == self::RGB) {
			$this->_color[self::HEX] = self::rgb2hex($this->_current_color);
			return $this->_color[self::HEX];
		} else {
			return $this->_current_color;
		}
	}

	/**
	 * Retourne la valeur inversée d'une couleur.
	 */
	function revert($hex=false,$rgb=false) {
		if (!$hex && !$rgb) {
			$type = $this->_type;
			switch ($type) {
				case self::HEX:
					return self::getRgb();
					break;
				case self::RGB:
					return self::getHex();
					break;
			}
		} else {
			if (!$hex) {
				if (!is_array($rgb)) {
					throw new exception('Invalid type given for RGB. An array is expected, '.gettype($rgb).' given.');
				}
				$type = self::RGB;
				$color = self::rgb2hex($rgb);
				$color = implode('',$color);
			} else {
				$type = self::HEX;
				$color = $hex;
			}
		}
		
		$color = str_replace('#','',$color);

		$r = str_pad(dechex(255 - hexdec(substr($color,0,2))),2,0);
		$g = str_pad(dechex(255 - hexdec(substr($color,2,2))),2,0);
		$b = str_pad(dechex(255 - hexdec(substr($color,-2))),2,0);

		return "#$r$g$b";
	}

	/**
	 * Transforme une couleur RGB en son homologue HTML
	 */
	function rgb2hex($rgb=false) {
		if (!$rgb) {
			if ($this->_type == self::RGB)
				$rgb = $this->_current_color;
			else
				return $this->_current_color;
		} else {
			if (!is_array($rgb)) {
				throw new exception('Invalid type given for RGB. An array is expected, '.gettype($rgb).' given.');
			}
		}

		$r = dechex(substr($rgb,0,3));
		$g = dechex(substr($rgb,0,3));
		$b = dechex(substr($rgb,-3));

		return compact('r','g','b');
	}
	
	/**
	 * Transforme une couleur html en son homologue RGB
	 */
	function hex2rgb($hex=false) {
		if (!$hex) {
			if ($this->_type == self::HEX)
				$hex = $this->_current_color;
			else
				return $this->_current_color;
		}

		$r = hexdec(substr($hex,0,2));
		$g = hexdec(substr($hex,2,2));
		$b = hexdec(substr($hex,-2));

		return compact('r', 'g', 'b');
	}
	
	/**
	 * Transforme une couleur RGB en son homologue HSV
	 */
	static function rgb2hsv($r, $g=0, $b=0) {
		if (is_array($r) && count($r)==3) {
			$b = $r['b'];
			$g = $r['g'];
			$r = $r['r'];
		}
		$max = max($r, $g, $b);
		$min = min($r, $g, $b);
		$delta = $max-$min;

		if ($max == 0)
			return array('h'=>0, 's'=>0, 'v'=>0);

		$s = $delta / $max;
		$v = $max;

		switch ($max) {
			case $g:
				if ($delta != 0) {
					$h = 2 + ($b - $r) / $delta;
				} else {
					$s = 0;
					$h = 2 + $b - $r;
				}
				break;
			case $b:
				if ($delta != 0) {
					$h = 4 + ($r - $g) / $delta;
				} else {
					$s = 0;
					$h = 4 + $r - $g;
				}
				break;
			case $r:
				if ($delta != 0) {
					$h = ($g - $b) / $delta;
				} else {
					$h = $g - $b;
				}
				break;
		}

		$h*=60;
		if ($h<0)
			$h+=360;
		$h = round($h);
		$s = round($s*255);

		return compact('h','s','v');
	}

	static function random($format='dec') {
		$color = rand(0, hexdec('ffffff'));
		if ($format=='hex') {
			return dechex($color);
		} else {
			return array(
				'r' => substr($color,0,2),
				'b' => substr($color,2,2),
				'r' => substr($color,-2));
		}
	}
}