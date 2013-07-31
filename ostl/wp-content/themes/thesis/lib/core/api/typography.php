<?php
/*---:[ Copyright DIYthemes, LLC. Patent pending. All rights reserved. DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC. ]:---*/
class thesis_typography_api {
	public function __construct() {
		$this->phi = (1 + sqrt(5)) / 2;
	}

	public function fit($value) {
		$fit['exact'] = $value;
		$fit['upper'] = ceil($value);
		$fit['lower'] = floor($value);
		$fit['best'] = abs($value - $fit['upper']) < abs($value - $fit['lower']) ? $fit['upper'] : $fit['lower'];
		return $fit;
	}

	public function type($f = false, $w = false, $cpl = false) {
		$a = 1 / (2 * $this->phi);
		$type = false;
		if ($f) {
			$wo = pow($f * $this->phi, 2);
			$type['optimal']['size'] = $f;
			$type['optimal']['height'] = $this->fit($f * $this->phi);
			$type['optimal']['width'] = $this->fit($wo * (1 + (2 * $this->phi) * (($type['optimal']['height']['best'] / $f) - $this->phi)));
		}
		if ($f && $w) {
			$calculated['height'] = $f * ($this->phi - $a * (1 - ($w / $wo)));
			$type['given']['height'] = $this->fit($calculated['height']); // best fit line height for the given width
		}
		if ($w) {
			$calculated['font_size'] = sqrt($w) / $this->phi;
			$font = $this->fit($calculated['font_size']); // best fit font size for the calculated font size
			$type['best']['size'] = $font['best'];
			$type['best']['height'] = $this->fit($type['best']['size'] * ($this->phi - $a * (1 - ($w / pow($type['best']['size'] * $this->phi, 2)))));
			$type['second']['size'] = $font['upper'] != $font['best'] ? $font['upper'] : $font['lower'];
			$type['second']['height'] = $this->fit($type['second']['size'] * ($this->phi - $a * (1 - ($w / pow($type['second']['size'] * $this->phi, 2)))));
		}
		return $type;
	}

	public function scale($size) {
		$scale = array();
		if (!is_numeric($size)) return $scale;
		$scale['title'] = round($size * pow($this->phi, 2), 0);
		$scale['headline'] = round($size * $this->phi, 0);
		$scale['subhead'] = round($size * sqrt($this->phi), 0);
		$scale['text'] = $size;
		$scale['aux'] = round($size * (1 / sqrt($this->phi)), 0);
		return $scale;
	}

	public function spacing($size, $height, $unit = false) {
		$spacing = array();
		$px['single'] = $height;
		$px['half'] = round($px['single'] / 2, 0);
		$px['3over2'] = $px['single'] + $px['half'];
		$px['double'] = $px['single'] * 2;
		foreach ($px as $dim => $value) {
			$px[$dim] = "{$value}px";
			$em[$dim] = round($value / $size, 6) . "em";
		}
		return $unit == 'em' ? $em : $px;
	}
}