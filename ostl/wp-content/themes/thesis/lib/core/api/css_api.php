<?php
/*---:[ Copyright DIYthemes, LLC. Patent pending. All rights reserved. DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC. ]:---*/
class thesis_css_api {
	public function __construct() {
		global $thesis;
		require_once(THESIS_API . '/fonts.php');
		$this->fonts = new thesis_fonts;
		$this->strings = $this->strings();
		$this->properties = $this->properties();
		$this->options = $this->options();
	}

	private function strings() {
		return array(
			'font' => __('Font', 'thesis'),
			'color' => __('Color', 'thesis'),
			'background' => __('Background', 'thesis'),
			'width' => __('Width', 'thesis'),
			'height' => __('Height', 'thesis'),
			'position' => __('Position', 'thesis'),
			'margin' => __('Margin', 'thesis'),
			'padding' => __('Padding', 'thesis'),
			'top' => __('Top', 'thesis'),
			'right' => __('Right', 'thesis'),
			'bottom' => __('Bottom', 'thesis'),
			'left' => __('Left', 'thesis'),
			'links' => __('Links', 'thesis'),
			'text' => __('Text', 'thesis'),
			'border' => __('Border', 'thesis'),
			'style' => __('Style', 'thesis'),
			'default' => __('default', 'thesis'),
			'normal' => __('normal', 'thesis'),
			'none' => __('none', 'thesis'));
	}

	private function properties() {
		return array(
			'font-family' => array_merge(array('' => 'select a font:'), array_merge($this->fonts->list, array('inherit' => __('inherit', 'thesis')))),
			'font-weight' => array(
				'' => $this->strings['default'],
				'bold' => __('bold', 'thesis'),
				'bolder' => __('bolder', 'thesis'),
				'lighter' => __('lighter', 'thesis'),
				100 => 100,
				200 => 200,
				300 => 300,
				400 => 400,
				500 => 500,
				600 => 600,
				700 => 700,
				800 => 800,
				900 => 900,
				'normal' => $this->strings['normal']),
			'font-style' => array(
				'' => $this->strings['default'],
				'italic' => __('italic', 'thesis'),
				'oblique' => __('oblique', 'thesis'),
				'normal' => $this->strings['normal']),
			'font-variant' => array(
				'' => $this->strings['default'],
				'small-caps' => __('small caps', 'thesis'),
				'normal' => $this->strings['normal']),
			'text-transform' => array(
				'' => $this->strings['default'],
				'capitalize' => __('capitalize', 'thesis'),
				'uppercase' => __('uppercase', 'thesis'),
				'lowercase' => __('lowercase', 'thesis'),
				'none' => $this->strings['none']),
			'text-align' => array(
				'' => $this->strings['default'],
				'left' => __('left', 'thesis'),
				'center' => __('center', 'thesis'),
				'right' => __('right', 'thesis'),
				'justify' => __('justify', 'thesis')),
			'text-decoration' => array(
				'' => $this->strings['default'],
				'none' => $this->strings['none'],
				'underline' => __('underline', 'thesis'),
				'overline' => __('overline', 'thesis'),
				'line-through' => __('line through', 'thesis'),
				'blink' => __('blink', 'thesis')),
			'background-repeat' => array(
				'' => sprintf(__('repeat (%s)', 'thesis'), $this->strings['default']),
				'no-repeat' => __('no repeat', 'thesis'),
				'repeat-x' => __('repeat-x', 'thesis'),
				'repeat-y' => __('repeat-y', 'thesis')),
			'background-attachment' => array(
				'' => $this->strings['default'],
				'scroll' => __('scrolls with page', 'thesis'),
				'fixed' => __('fixed&#8212;does not scroll', 'thesis')),
			'border-style' => array(
				'' => $this->strings['default'],
				'solid' => __('solid', 'thesis'),
				'dotted' => __('dotted', 'thesis'),
				'dashed' => __('dashed', 'thesis'),
				'double' => __('double', 'thesis'),
				'groove' => __('groove', 'thesis'),
				'ridge' => __('ridge', 'thesis'),
				'inset' => __('inset', 'thesis'),
				'outset' => __('outset', 'thesis'),
				'none' => $this->strings['none']),
			'position' => array(
				'' => $this->strings['default'],
				'absolute' => __('absolute', 'thesis'),
				'relative' => __('relative', 'thesis'),
				'fixed' => __('fixed', 'thesis')),
			'display' => array(
				'' => $this->strings['default'],
				'block' => __('block', 'thesis'),
				'inline' => __('inline', 'thesis'),
				'inline-block' => __('inline block', 'thesis'),
				'table' => __('table', 'thesis'),
				'inline-table' => __('inline table', 'thesis')),
			'float' => array(
				'' => $this->strings['default'],
				'left' => __('left', 'thesis'),
				'right' => __('right', 'thesis')),
			'clear' => array(
				'' => $this->strings['default'],
				'left' => __('left', 'thesis'),
				'right' => __('right', 'thesis'),
				'both' => __('both', 'thesis'),
				'none' => $this->strings['none']),
			'visibility' => array(
				'' => $this->strings['default'],
				'hidden' => __('hidden', 'thesis'),
				'visible' => __('visible', 'thesis')),
			'overflow' => array(
				'' => $this->strings['default'],
				'visible' => __('visible', 'thesis'),
				'hidden' => __('hidden', 'thesis'),
				'scroll' => __('scroll', 'thesis'),
				'auto' => __('auto', 'thesis')),
			'box-sizing' => array(
				'' => sprintf(__('content box (%s)', 'thesis'), $this->strings['default']),
				'border-box' => __('border box', 'thesis')),
			'cursor' => array(
				'' => __('unspecified', 'thesis'),
				'auto' => __('auto', 'thesis'),
				'default' => $this->strings['default'],
				'pointer' => __('pointer', 'thesis'),
				'crosshair' => __('crosshair', 'thesis'),
				'help' => __('help', 'thesis'),
				'move' => __('move', 'thesis'),
				'text' => __('text', 'thesis')),
			'list-style-type' => array(
				'' => $this->strings['default'],
				'circle' => __('circle', 'thesis'),
				'decimal' => __('decimal', 'thesis'),
				'decimal-leading-zero' => __('decimal with leading zero', 'thesis'),
				'disc' => __('disc', 'thesis'),
				'lower-alpha' => __('lower alpha', 'thesis'),
				'lower-roman' => __('lower Roman', 'thesis'),
				'none' => $this->strings['none'],
				'square' => __('square', 'thesis'),
				'upper-alpha' => __('upper alpha', 'thesis'),
				'upper-roman' => __('upper Roman', 'thesis')));
	}

	private function options() {
		global $thesis;
		$s = array(
			'text_color' => sprintf(__('%1$s %2$s', 'thesis'), $this->strings['text'], $this->strings['color']),
			'text_decoration' => sprintf(__('%1$s Decoration', 'thesis'), $this->strings['text']),
			'list' => __('List', 'thesis'),
			'space' => __('space', 'thesis'),
			'num_tooltip' => __('If you enter only a number, Thesis will assume you want your output in pixels. If you want to use any other unit, please supply it here.', 'thesis'));
		return array(
			'font' => array(
				'type' => 'group',
				'label' => sprintf(__('%s Settings', 'thesis'), $this->strings['font']),
				'fields' => array(
					'font-family' => array(
						'type' => 'select',
						'label' => $this->strings['font'],
						'options' => $this->properties['font-family']),
					'font-size' => array(
						'type' => 'text',
						'width' => 'tiny',
						'label' => sprintf(__('%s Size', 'thesis'), $this->strings['font']),
						'description' => 'px'),
					'line-height' => array(
						'type' => 'text',
						'width' => 'short',
						'label' => sprintf(__('Line %s', 'thesis'), $this->strings['height']),
						'tooltip' => sprintf(__('You can specify a line height directly, or you can allow the Thesis typography %1$s to generate your line heights according to <a href="%2$s" target="_blank">Golden Ratio Typography</a>.', 'thesis'), $thesis->api->base['api'], esc_url('http://pearsonified.com/2011/12/golden-ratio-typography.php'))),
					'font-weight' => array(
						'type' => 'select',
						'label' => sprintf(__('%s Weight (bold)', 'thesis'), $this->strings['font']),
						'options' => $this->properties['font-weight']),
					'font-style' => array(
						'type' => 'select',
						'label' => sprintf(__('%1$s %2$s (italic)', 'thesis'), $this->strings['font'], $this->strings['style']),
						'options' => $this->properties['font-style']),
					'font-variant' => array(
						'type' => 'select',
						'label' => sprintf(__('%s Variant', 'thesis'), $this->strings['font']),
						'options' => $this->properties['font-variant']),
					'text-transform' => array(
						'type' => 'select',
						'label' => sprintf(__('%s Transform', 'thesis'), $this->strings['text']),
						'options' => $this->properties['text-transform']),
					'text-align' => array(
						'type' => 'select',
						'label' => sprintf(__('%s Align', 'thesis'), $this->strings['text']),
						'options' => $this->properties['text-align']),
					'letter-spacing' => array(
						'type' => 'text',
						'width' => 'tiny',
						'label' => __('Letter Spacing', 'thesis'),
						'description' => 'px'))),
			'color' => array(
				'type' => 'color',
				'label' => $s['text_color']),
			'background' => array(
				'type' => 'group',
				'label' => $this->strings['background'],
				'fields' => array(
					'background-color' => array(
						'type' => 'color',
						'label' => sprintf(__('%1$s %2$s', 'thesis'), $this->strings['background'], $this->strings['color'])),
					'background-image' => array(
						'type' => 'text',
						'width' => 'long',
						'code' => true,
						'label' => sprintf(__('%1$s Image', 'thesis'), $this->strings['background']),
						'tooltip' => sprintf(__('Enter a relative path or full %s to your image. For example, if you want to use an image from the Images tab called <code>myimage.png</code>, you would enter: <code>images/myimage.png</code>. Also: No quotes!', 'thesis'), $thesis->api->base['url'])),
					'background-position' => array(
						'type' => 'text',
						'width' => 'short',
						'label' => sprintf(__('%1$s %2$s', 'thesis'), $this->strings['background'], $this->strings['position']),
						'tooltip' => __('This field requires input in the form of <code>X Y</code>. <strong>Note:</strong> You must specify a unit (px, em, %, etc) with your input!', 'thesis')),
					'background-attachment' => array(
						'type' => 'radio',
						'label' => sprintf(__('%s Attachment', 'thesis'), $this->strings['background']),
						'options' => $this->properties['background-attachment']),
					'background-repeat' => array(
						'type' => 'radio',
						'label' => sprintf(__('%s Repeat', 'thesis'), $this->strings['background']),
						'options' => $this->properties['background-repeat']))),
			'width' => array(
				'type' => 'text',
				'width' => 'short',
				'label' => $this->strings['width'],
				'tooltip' => $s['num_tooltip']),
			'box-sizing' => array(
				'type' => 'radio',
				'label' => __('Box Sizing', 'thesis'),
				'options' => $this->properties['box-sizing']),
			'float' => array(
				'type' => 'select',
				'label' => __('Float', 'thesis'),
				'options' => $this->properties['float']),
			'margin' => array(
				'type' => 'group',
				'label' => $this->strings['margin'],
				'fields' => array(
					'margin-top' => array(
						'type' => 'text',
						'width' => 'short',
						'label' => sprintf(__('%1$s %2$s', 'thesis'), $this->strings['top'], $this->strings['margin'])),
					'margin-right' => array(
						'type' => 'text',
						'width' => 'short',
						'label' => sprintf(__('%1$s %2$s', 'thesis'), $this->strings['right'], $this->strings['margin'])),
					'margin-bottom' => array(
						'type' => 'text',
						'width' => 'short',
						'label' => sprintf(__('%1$s %2$s', 'thesis'), $this->strings['bottom'], $this->strings['margin'])),
					'margin-left' => array(
						'type' => 'text',
						'width' => 'short',
						'label' => sprintf(__('%1$s %2$s', 'thesis'), $this->strings['left'], $this->strings['margin'])))),
			'padding' => array(
				'type' => 'group',
				'label' => $this->strings['padding'],
				'fields' => array(
					'padding-top' => array(
						'type' => 'text',
						'width' => 'short',
						'label' => sprintf(__('%1$s %2$s', 'thesis'), $this->strings['top'], $this->strings['padding'])),
					'padding-right' => array(
						'type' => 'text',
						'width' => 'short',
						'label' => sprintf(__('%1$s %2$s', 'thesis'), $this->strings['right'], $this->strings['padding'])),
					'padding-bottom' => array(
						'type' => 'text',
						'width' => 'short',
						'label' => sprintf(__('%1$s %2$s', 'thesis'), $this->strings['bottom'], $this->strings['padding'])),
					'padding-left' => array(
						'type' => 'text',
						'width' => 'short',
						'label' => sprintf(__('%1$s %2$s', 'thesis'), $this->strings['left'], $this->strings['padding'])))),
			'border' => array(
				'type' => 'group',
				'label' => $this->strings['border'],
				'fields' => array(
					'border-width' => array(
						'type' => 'text',
						'width' => 'short',
						'label' => sprintf(__('%1$s %2$s', 'thesis'), $this->strings['border'], $this->strings['width']),
						'tooltip' => $s['num_tooltip']),
					'border-style' => array(
						'type' => 'select',
						'label' => sprintf(__('%1$s %2$s', 'thesis'), $this->strings['border'], $this->strings['style']),
						'options' => $this->properties['border-style']),
					'border-color' => array(
						'type' => 'color',
						'label' => sprintf(__('%1$s %2$s', 'thesis'), $this->strings['border'], $this->strings['color']),
						'tooltip' => __('If you&#8217;ve specified a border width, your border will be the color you input here.', 'thesis')))),
			'links' => array(
				'type' => 'group',
				'label' => $this->strings['links'],
				'fields' => array(
					'link' => array(
						'type' => 'color',
						'label' => $s['text_color']),
					'link-decoration' => array(
						'type' => 'select',
						'label' => $s['text_decoration'],
						'options' => $this->properties['text-decoration']))),
			'links-hovered' => array(
				'type' => 'group',
				'label' => sprintf(__('Hovered %s', 'thesis'), $this->strings['links']),
				'fields' => array(
					'link-hover' => array(
						'type' => 'color',
						'label' => $s['text_color']),
					'link-hover-decoration' => array(
						'type' => 'select',
						'label' => $s['text_decoration'],
						'options' => $this->properties['text-decoration']))),
			'links-visited' => array(
				'type' => 'group',
				'label' => sprintf(__('Visited %s', 'thesis'), $this->strings['links']),
				'fields' => array(
					'link-visited' => array(
						'type' => 'color',
						'label' => $s['text_color']),
					'link-visited-decoration' => array(
						'type' => 'select',
						'label' => $s['text_decoration'],
						'options' => $this->properties['text-decoration']))),
			'links-active' => array(
				'type' => 'group',
				'label' => sprintf(__('Active %s', 'thesis'), $this->strings['links']),
				'fields' => array(
					'link-active' => array(
						'type' => 'color',
						'label' => $s['text_color']),
					'link-active-decoration' => array(
						'type' => 'select',
						'label' => $s['text_decoration'],
						'options' => $this->properties['text-decoration']))),
			'lists' => array(
				'type' => 'group',
				'label' => __('Lists', 'thesis'),
				'fields' => array(
					'list-style-type' => array(
						'type' => 'select',
						'label' => sprintf(__('%1$s %2$s', 'thesis'), $s['list'], $this->strings['style']),
						'options' => $this->properties['list-style-type']),
					'list-style-position' => array(
						'type' => 'select',
						'label' => sprintf(__('Bullet %s', 'thesis'), $this->strings['position']),
						'options' => array(
							'' => __('outside (default)', 'thesis'),
							'inside' => __('inside', 'thesis'))),
					'list-indent' => array(
						'type' => 'checkbox',
						'options' => array(
							'on' => __('Indent list (add left margin)', 'thesis'))),
					'list-item-margin' => array(
						'type' => 'radio',
						'label' => sprintf(__('%1$s Item %2$s %3$s', 'thesis'), $s['list'], $this->strings['bottom'], $this->strings['margin']),
						'options' => array(
							'' => sprintf(__('no %s', 'thesis'), strtolower($this->strings['margin'])),
							'half' => sprintf(__('half %s', 'thesis'), $s['space']),
							'single' => sprintf(__('single %s', 'thesis'), $s['space']))))),
			'typography' => array(
				'type' => 'text',
				'width' => 'tiny',
				'label' => __('Typography: Enter Width of Text Area', 'thesis'),
				'tooltip' => __('For perfect typography, enter the exact width of your text area, and Thesis will make precise typographical adjustments to fit this context.<br /><br /><strong>Note:</strong> The value you enter here will not affect the width of your text area, but it <em>will</em> ensure that your typography is awesome!', 'thesis'),
				'description' => 'px'));
	}

	public function get_font_styles($options, $prefix = false) {
		if (!is_array($options)) return;
		$styles = array();
		$p = !empty($prefix) ? "$prefix-" : '';
		$styles['font-family'] = !empty($options["{$p}font-family"]) ? ($options["{$p}font-family"] == 'inherit' ?
		 	"font-family: inherit;" : (!empty($this->fonts) && !empty($this->fonts->fonts[$options["{$p}font-family"]]) ?
			"font-family: {$this->fonts->fonts[$options["{$p}font-family"]]['family']};" : false)) : false;
		$styles['font-size'] = !empty($options["{$p}font-size"]) ?
			'font-size: ' . $this->number($options["{$p}font-size"]) . ';' : false;
		$styles['line-height'] = !empty($options["{$p}line-height"]) ?
			'line-height: ' . $this->number($options["{$p}line-height"]) . ';' : false;
		$styles['font-weight'] = !empty($options["{$p}font-weight"]) ?
			"font-weight: {$options["{$p}font-weight"]};" : false;
		$styles['font-style'] = !empty($options["{$p}font-style"]) ?
			"font-style: {$options["{$p}font-style"]};" : false;
		$styles['font-variant'] = !empty($options["{$p}font-variant"]) ?
			"font-variant: {$options["{$p}font-variant"]};" : false;
		$styles['text-transform'] = !empty($options["{$p}text-transform"]) ?
			"text-transform: {$options["{$p}text-transform"]};" : false;
		$styles['text-align'] = !empty($options["{$p}text-align"]) ?
			"text-align: {$options["{$p}text-align"]};" : false;
		$styles['letter-spacing'] = !empty($options["{$p}letter-spacing"]) ?
			"letter-spacing: " . $this->number($options["{$p}letter-spacing"]) . ';' : false;
		return array_filter($styles);
	}

	public function trbl($type, $values) {
		if (!$type || !is_array($values)) return false;
		$dims = array('top', 'right', 'bottom', 'left');
		$props = array();
		if ($type == 'margin' || $type == 'padding')
			foreach ($dims as $dim)
				if (!empty($values["$type-$dim"]))
					$props[$dim] = "$type-$dim: " . $this->number($values["$type-$dim"]) . ';';
		return is_array($props) ? implode(' ', $props) : false;
	}

	public function number($value, $default = false) {
		return !empty($value) ? (is_numeric($value) ? "{$value}px" : stripslashes($value)) : ($default ? $default : false);
	}

	public function prefix($property, $value) {
		if (!$property || !$value) return false;
		$css = array();
		$vendors = array('-webkit', '-moz');
		foreach ($vendors as $vendor)
			$css[] = "$vendor-$property: $value;";
		return !empty($css) ? implode(' ', $css) . " $property: $value;" : false;
	}
}