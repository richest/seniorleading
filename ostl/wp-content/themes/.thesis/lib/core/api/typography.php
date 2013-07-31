<?php
/*
Copyright 2012 DIYthemes, LLC. Patent pending. All rights reserved.
DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC.
License: DIYthemes Software License Agreement
License URI: http://diythemes.com/thesis/rtfm/software-license-agreement/
*/
class thesis_typography {
	public function __construct() {
		$this->phi = (1 + sqrt(5)) / 2;
	}

	public function height($size, $width = false, $font = false, $cpl = false) {
		global $thesis;
		$a = 1 / (2 * $this->phi);
		$mu = !empty($font) && !empty($thesis->api->fonts->list[$font]) && !empty($thesis->api->fonts->list[$font]['mu']) ?
			$thesis->api->fonts->list[$font]['mu'] : 2.27;
		$cpl = !empty($cpl) && is_numeric($cpl) ? $cpl : (is_numeric($filtered = apply_filters('thesis_cpl', false)) ? $filtered : 75);
		return empty($size) || !is_numeric($size) ? false :
			$size * (1 + $a + $a * ((!empty($width) && is_numeric($width) ? $width : $size * $cpl / $mu) / ($size * $cpl / $mu)));
	}

	public function scale($size) {
		return empty($size) || !is_numeric($size) ? false : array(
			'title' => round($size * pow($this->phi, 2), 0),
			'headline' => round($size * $this->phi, 0),
			'subhead' => round($size * sqrt($this->phi), 0),
			'text' => $size,
			'aux' => round($size * (1 / sqrt($this->phi)), 0));
	}

	public function space($height) {
		return empty($height) || !is_numeric($height) ? false : array(
			'single' => ($height = round($height, 0)),
			'half' => ($half = round($height / 2, 0)),
			'3over2' => $height + $half,
			'double' => $height * 2);
	}

	// Everything below this line is deprecated and will be removed in version 2.2
	public function type($f = false, $w = false) {
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

	public function spacing($size, $height, $unit = false) {
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

	public function fit($value) {
		$fit['exact'] = $value;
		$fit['upper'] = ceil($value);
		$fit['lower'] = floor($value);
		$fit['best'] = abs($value - $fit['upper']) < abs($value - $fit['lower']) ? $fit['upper'] : $fit['lower'];
		return $fit;
	}
}