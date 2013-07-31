<?php
/*
Copyright 2013 DIYthemes, LLC. Patent pending. All rights reserved.
DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC.
License: DIYthemes Software License Agreement
License URI: http://diythemes.com/thesis/rtfm/software-license-agreement/
*/
class thesis_colors {
	public $black = array(
		'r' => 0,
		'g' => 0,
		'b' => 0);
	public $white = array(
		'r' => 255,
		'g' => 255,
		'b' => 255);
	public $half = array(
		'r' => 128,
		'g' => 128,
		'b' => 128);
	public $conflict = array(
		'bisque',
		'indigo',
		'maroon',
		'orange',
		'orchid',
		'purple',
		'red',
		'salmon',
		'sienna',
		'silver',
		'tan',
		'tomato',
		'violet',
		'yellow');
	private $v = array(
		'v1' => array(0, 1, 2),
		'v2' => array(0, 2, 1),
		'v3' => array(1, 0, 2),
		'v4' => array(2, 0, 1),
		'v5' => array(1, 2, 0),
		'v6' => array(2, 1, 0));
	private $wheel = array(
		array(2, 3),
		array(3, 3),
		array(4, 3),
		array(5, 3),
		array(4, 4),
		array(3, 4),
		array(2, 4),
		array(1, 5),
		array(2, 2),
		array(3, 2),
		array(4, 2),
		array(5, 1),
		array(4, 1),
		array(3, 1),
		array(2, 1),
		array(1, 3),
		array(2, 6),
		array(3, 6),
		array(4, 6),
		array(5, 5),
		array(4, 5),
		array(3, 5),
		array(2, 5),
		array(1, 1));
	private $complements = array(
		array('r', 'b', 'g'),
		array('b', 'r', 'g'),
		array('b', 'g', 'r'),
		array('g', 'r', 'b'),
		array('g', 'b', 'r'));
	private $tolerance = 0.5;

	public function css($color) {
		return (strlen($color) == 3 || strlen($color) == 6) && !in_array($color, $this->conflict) ? "#$color" : $color;
	}

	public function delta($dmin = 40, $dmax = 160, $interval = 15) {
		if (!is_numeric($dmin) || !is_numeric($dmax) || !is_numeric($interval)) return;
		$delta = array();
		for ($d = $dmin; $d <= $dmax; $d = $d + $interval) {
			$a = round($d * 0.6);
			$b = round($d * 0.5);
			$c = round($d * 0.4);
			$e = round($d * 0.2);
			$f = round(sqrt(pow($d, 2) - 2 * pow($g = round(sqrt(pow($d, 2) / 3)), 2)));
			$h = round(sqrt(pow($d, 2) - pow($b, 2) - pow($e, 2)));
			$delta[$d] = array(
				't1' => array($f, -$g, -$g), // cardinals (3)
				't2' => array(-round(sqrt(pow($d, 2) - pow($a, 2) - pow($c, 2))), $a, -$c), // flanking (6)
				't3' => array(-$h, $b, -$e), // interstitials (6)
				't4' => array(-round(sqrt(pow($d, 2) - pow($c, 2))), $c, 0), // hexes (6)
				't5' => array(-round(sqrt(pow($d, 2) - 2 * pow($e, 2))), $e, $e)); // anti-cardinals (3)
		}
		return $delta;
	}

	public function rgb($hex) {
		if (empty($hex)) return false;
		$rgb = array();
		if (strlen($hex) == 6) {
			$rgb['r'] = hexdec($hex[0]. $hex[1]);
			$rgb['g'] = hexdec($hex[2]. $hex[3]);
			$rgb['b'] = hexdec($hex[4]. $hex[5]);
		}
		elseif (strlen($hex) == 3) {
			$rgb['r'] = hexdec($hex[0]. $hex[0]);
			$rgb['g'] = hexdec($hex[1]. $hex[1]);
			$rgb['b'] = hexdec($hex[2]. $hex[2]);
		}
		return $rgb;
	}

	public function hex($rgb) {
		if (!is_array($rgb)) return false;
		return sprintf('%02s', dechex($rgb['r'])). sprintf('%02s', dechex($rgb['g'])). sprintf('%02s', dechex($rgb['b']));
	}

	public function distance($rgb1, $rgb2) {
		if (!is_array($rgb1) || !is_array($rgb2)) return;
		return sqrt(pow($rgb1['r'] - $rgb2['r'], 2) + pow($rgb1['g'] - $rgb2['g'], 2) + pow($rgb1['b'] - $rgb2['b'], 2));
	}

	public function gray($color) {
		if (empty($color)) return;
		$channel = round(sqrt(pow($this->distance((is_array($color) ? $color : $this->rgb($color)), $this->black), 2) / 3), 0);
		$gray = array('r' => $channel, 'g' => $channel, 'b' => $channel);
		return array(
			'hex' => $this->hex($gray),
			'rgb' => $gray);
	}

	public function scheme($scheme, $values, $name) {
		if (!is_array($scheme) || !is_array($values) || empty($name) || empty($scheme['id']) || !is_array($scheme['colors'])) return;
		$inputs = $scale = '';
		$class = 'scheme'. (!empty($scheme['text']) ? '_text' : '');
		foreach ($scheme['colors'] as $id => $label)
			$inputs .=
				"\t\t<div class=\"scheme_color\">\n".
				"\t\t\t<input type=\"text\" class=\"$class color {required:false,adjust:false}\" id=\"{$scheme['id']}-$id\" name=\"{$name}[$id]\" value=\"". esc_attr(stripslashes($values[$id])). "\" />\n".
				"\t\t\t<span class=\"complement\" data-style=\"icon\" data-id=\"{$scheme['id']}-$id\" title=\"". __('complementary colors', 'thesis'). "\">&#128166;</span>\n".
				"\t\t\t<div class=\"complements\">\n".
				"\t\t\t</div>\n".
				"\t\t\t<label for=\"{$scheme['id']}-$id\">$label</label>\n".
				"\t\t</div>\n";
		if (!empty($scheme['scale']) && !empty($scheme['default']))
			$scale =
				"\t<div class=\"scheme_color_scale\">\n".
				"\t\t<button data-style=\"flat_button\" class=\"color_scale\">". __('Thesis ColorScale', 'thesis'). " <span data-style=\"icon\">&#59395;</span></button>\n".
				$this->scale_picker($scheme['id'], $scheme['scale'], $scheme['default']).
				"\t</div>\n";
		if (!empty($inputs))
			return
				"\t<div class=\"scheme_colors\">\n".
				$inputs.
				"\t</div>\n".
				$scale;
	}

	public function scale_picker($scheme, $colors, $defaults, $depth = false) {
		if (empty($scheme) || !is_array($colors) || !is_array($defaults)) return;
		$tab = str_repeat("\t", !empty($depth) && is_numeric($depth) ? $depth : 0);
		$scales = $swatches = array();
		$grays = $default_colors = '';
		foreach ($colors as $id => $hex)
			$scales[$id] = $this->transform($this->rgb($hex));
		foreach ($scales as $id => $deltas) {
			$grays .= "<input type=\"hidden\" class=\"grayscale\" data-scheme=\"$scheme\" data-id=\"$id\" data-value=\"{$colors[$id]}\" />";
			$default_colors .= "<input type=\"hidden\" class=\"defaults\" data-scheme=\"$scheme\" data-id=\"$id\" data-value=\"{$defaults[$id]}\" />";
		}
		$picker =
			"$tab\t<div class=\"default_row\">\n".
			(!empty($default_colors) ?
			"$tab\t\t<span class=\"control_swatch default_swatch\" title=\"". __('defaults', 'thesis'). "\" data-value=\"defaults\">". __('Default Colors', 'thesis'). "</span>\n".
			"$tab\t\t$default_colors\n" : '').
			"$tab\t\t<span class=\"default_swatch home_swatch\">". __('ColorScale', 'thesis'). "</span>\n".
			(!empty($grays) ?
			"$tab\t\t<span class=\"control_swatch default_swatch\" title=\"". __('grayscale', 'thesis'). "\" data-value=\"grayscale\">". __('Grayscale', 'thesis'). "</span>\n".
			"$tab\t\t$grays\n" : '').
			"$tab\t</div>\n";
		foreach (($swatches = $this->transform($this->half)) as $delta => $transforms) {
			$row = '';
			foreach ($this->wheel as $combo) {
				$inputs = '';
				foreach ($scales as $id => $deltas)
					if (!empty($deltas[$delta]["t{$combo[0]}"]["v{$combo[1]}"])) {
						$v = $this->hex($deltas[$delta]["t{$combo[0]}"]["v{$combo[1]}"]);
						$inputs .= "<input type=\"hidden\" class=\"d{$delta}t{$combo[0]}v{$combo[1]}\" data-scheme=\"$scheme\" data-id=\"$id\" data-value=\"$v\" />";
					}
				if (!empty($swatches[$delta]["t{$combo[0]}"]["v{$combo[1]}"])) {
					$variant = $this->hex($swatches[$delta]["t{$combo[0]}"]["v{$combo[1]}"]);
					$row .=
						"$tab\t\t<span class=\"control_swatch color_swatch\" style=\"background: #$variant;\" title=\"$variant\" data-value=\"d{$delta}t{$combo[0]}v{$combo[1]}\"></span>\n".
						(!empty($inputs) ?
						"$tab\t\t$inputs\n" : '');
				}
			}
			$picker .= !empty($row) ? "$tab\t<div class=\"color_row\">\n$row$tab\t</div>\n" : '';
		}
		if (!empty($picker))
			return
				"$tab<div class=\"color_picker\">\n".
				$picker.
				"$tab</div>\n";
	}

	public function transform($rgb) {
		if (empty($rgb)) return false;
		$new = array();
		foreach ($this->delta() as $delta => $transforms)
			foreach ($transforms as $t => $transform)
				if (is_array($add = $this->rgb_transform($rgb, $transform)) && !empty($add))
					$new[$delta][$t] = !empty($new[$delta][$t]) && is_array($new[$delta][$t]) ?
						array_merge($new[$delta][$t], $add) : $add;
		return $new;
	}

	public function rgb_transform($rgb, $transform) {
		if (!is_array($rgb) || !is_array($transform)) return false;
		$new = $unique = $transformed = array();
		$f = 1 - (abs(($tune = sqrt(3 * pow(255, 2)) / 2) - ($db = $this->distance($rgb, $this->black))) / $tune);
		foreach ($this->v as $v => $variant) {
			foreach ($variant as $key => $index) {
				$channel = $key == 1 ? 'g' : ($key == 2 ? 'b' : 'r');
				$new[$v][$channel] = ($value = round($rgb[$channel] + $f * $transform[$index], 0)) > 255 ? 255 : ($value < 0 ? 0 : $value);
			}
			if (($test = (abs(($nb = $this->distance($new[$v], $this->black)) - $db) / $db)) <= $f * $this->tolerance)
				$unique[$v] = $this->hex($new[$v]);
		}
		foreach (array_unique($unique) as $v => $hex)
			$transformed[$v] = $this->rgb($hex);
		return $transformed;
	}

	public function complement($original) {
		global $thesis;
		if (empty($original) || !(strlen($original) == 3 || strlen($original) == 6) || !is_array($orgb = $this->rgb($original))) return false;
		$colors = $hexes = array();
		$swatches = '';
		$distance = 0;
		foreach ($this->complements as $k => $complement) {
			$rgb = array();
			foreach ($complement as $i => $channel) {
				$ch = $i == 1 ? 'g' : ($i == 2 ? 'b' : 'r');
				$rgb[$ch] = $orgb[$channel];
			}
			if (($d = $this->distance($orgb, $rgb)) > 0 && !in_array(($hex = $this->hex($rgb)), $hexes)) {
				$colors[$k] = array();
				$colors[$k]['rgb'] = $rgb;
				$colors[$k]['hex'] = $hexes[] = $hex;
				$colors[$k]['swatch'] = "<span class=\"color_swatch complement_swatch\" style=\"background: #{$colors[$k]['hex']};\" data-value=\"{$colors[$k]['hex']}\" title=\"color: {$colors[$k]['hex']}, distance: ". round($d, 2). "\"></span>\n";
				$distance = $distance + ($colors[$k]['distance'] = $d);
			}
		}
		if (empty($colors)) return false;
		$davg = $distance / count($colors);
		foreach ($colors as $k => $color)
			if ($color['distance'] >= $davg)
				$swatches .= $color['swatch'];
		return !empty($swatches) ? $swatches : false;
	}

	/* The following methods will probably be deprecated in the future,
	 * as they only pertain to a <select> or radio style input that is
	 * not even used by core Thesis components. The scheme() picker
	 * above is much more powerful and flexible. */

	public function scheme_options($schemes, $default = false) {
		if (!is_array($schemes)) return;
		$options = array();
		foreach ($schemes as $id => $colors)
			if (is_array($colors))
				$options[$id] = implode('', array_map(array($this, 'wrap_color'), $colors));
		return $options;
	}

	private function wrap_color($color) {
		return sprintf('<span class="t_color_scheme" style="background: %1$s;" title="%1$s"></span>', $this->css($color));
	}
}