<?php
/*---:[ Copyright DIYthemes, LLC. Patent pending. All rights reserved. DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC. ]:---*/
class thesis_package {
	// a title is required in ALL package extensions
	public $title;					// (string) package title; must be defined in translate() for translation
	// optional (but recommended) property for package extensions
	public $selector;				// (string) default CSS selector this package is intended to target
	// reserved properties NOT intended for use in package extensions
	public $_class;					// (string) quick reference for this package's class
	public $_id;					// (string) unique identifier for the current instance
	private $_options = array();	// (array) ALL default option fields for this package
	private $_fields = false;		// (array) only populated if the package has options()
	// reserved properties intended for use by package extensions
	public $options = array();		// (array) currently-set options for this package
	protected $colors = array();	// (array) quick reference array with formatting help for any options set as color inputs
	public $clearfix = false;		// set this to an array of CSS selectors, and Thesis will automatically clearfix the selectors

	public function __construct($id = false, $options = false) {
		global $thesis;
		$this->_class = strtolower(get_class($this));
		$this->_id = $id ? $id : $this->_class;
		if (method_exists($this, 'translate'))
			$this->translate();
		$this->options = $this->_get_options(($this->_options = method_exists($this, 'options') && is_array($this->_fields = apply_filters("{$this->_class}_options", $this->options())) ?
			array_merge($this->_options(), $this->_css(), $this->_fields) :
			array_merge($this->_options(), $this->_css())), $options);
		$this->selector = !empty($options['_selector']) ? trim(stripslashes($options['_selector'])) : $this->selector;
		$this->construct();
	}

	protected function construct() {
		// To be used by package extensions
	}

	private function _get_options($fields, $values) {
		if (!is_array($fields)) return array();
		$values = is_array($values) ? $values : array();
		$options = array();
		$color_names = array('bisque', 'indigo', 'maroon', 'orange', 'orchid', 'purple', 'red', 'salmon', 'sienna', 'silver', 'tan', 'tomato', 'violet', 'yellow');
		foreach ($fields as $id => $field)
			if (is_array($field)) {
				if ($field['type'] == 'group') {
					if (is_array($field['fields']))
						$options = is_array($group = $this->_get_options($field['fields'], $values)) ? array_merge($options, $group) : $options;
				}
				else {
					if ($field['type'] == 'checkbox' && is_array($field['options']))
						foreach ($field['options'] as $option => $option_value) {
							$options[$id][$option] = isset($values[$id][$option]) ? (bool) $values[$id][$option] : (bool) (isset($field['default'][$option]) ? $field['default'][$option] : false);
							if (!$options[$id][$option])
								unset($options[$id][$option]);
						}
					else
						$options[$id] = !empty($values[$id]) ? $values[$id] : (!empty($field['default']) ? $field['default'] : false);
					if ($field['type'] == 'color' && !empty($options[$id]))
						$this->colors[$id] = (strlen($options[$id]) == 3 || strlen($options[$id]) == 6) && !in_array($options[$id], $color_names) && strpos($options[$id], "\$") === false ?
							"#{$options[$id]}" : $options[$id];
				}
				if (empty($options[$id]))
					unset($options[$id]);
			}
		return $options;
	}

	private function _options() {
		global $thesis;
		return array(
			'_name' => array(
				'type' => 'text',
				'width' => 'medium',
				'req' => '*',
				'label' => $thesis->api->strings['name'],
				'tooltip' => sprintf(__('Name your package so you can pick it out easily while writing %s.', 'thesis'), $thesis->api->base['css'])),
			'_ref' => array(
				'type' => 'text',
				'width' => 'medium',
				'code' => true,
				'req' => '*',
				'label' => $thesis->api->strings['reference'],
				'tooltip' => sprintf(__('The reference is how you will refer to this particular package in your %s.', 'thesis'), $thesis->api->base['css'])),
			'_selector' => array(
				'type' => 'text',
				'width' => 'medium',
				'code' => true,
				'req' => '*',
				'label' => sprintf(__('%s Selector', 'thesis'), $thesis->api->base['css']),
				'tooltip' => sprintf(__('Enter a %1$s selector here to apply this package&#8217;s styles to a particular %2$s element.', 'thesis'), $thesis->api->base['css'], $thesis->api->base['html']),
				'default' => $this->selector));
	}

	private function _css() {
		global $thesis;
		$this->_add = sprintf(__('Additional %s', 'thesis'), $thesis->api->base['css']);
		return array(
			'_css' => array(
				'type' => 'textarea',
				'rows' => 6,
				'code' => true,
				'label' => $this->_add,
				'tooltip' => sprintf(__('If you want to tweak, override, or enhance this package&#8217;s %1$s output in any way, you can provide your own %1$s declarations here.', 'thesis'), $thesis->api->base['css'])));
	}

	public function _edit() {
		global $thesis;
		$li = $panes = '';
		$package = $thesis->api->form->fields($this->_options(), $this->options, 't_pkg', 'options', 500, 2);
		$pane['package'] = array(
			'menu' => __('Package', 'thesis'),
			'pane' => $package['output']);
		if (is_array($this->_fields)) {
			$options = $thesis->api->form->fields($this->_fields, $this->options, false, 'options', 600, 2);
			$pane['options'] = array(
				'menu' => __('Options', 'thesis'),
				'pane' => $options['output']);
		}
		$css = $thesis->api->form->fields($this->_css(), $this->options, 't_pkg', 'options', 700, 2);
		$pane['css'] = array(
			'menu' => $this->_add,
			'pane' => $css['output']);
		foreach ($pane as $type => $item)
			if (is_array($item)) {
				$li .= "\t\t\t<li data-pane=\"$type\">{$item['menu']}</li>\n";
				$panes .= "<div class=\"pane pane_$type\">\n{$item['pane']}</div>\n";
			}
		return
			"<form id=\"t_package_form\" class=\"t_popup_form\" action=\"\" method=\"post\">\n".
			"\t<div class=\"t_popup_head\" data-style=\"box\">\n".
			"\t\t<button class=\"cancel_options\" data-style=\"button\">{$thesis->api->strings['cancel']}</button>\n".
			"\t\t<button class=\"save_options\" data-style=\"button save\">{$thesis->api->strings['save']}</button>\n".
			"\t\t<h4>$this->title</h4>\n".
			"\t\t<ul class=\"t_popup_menu\">\n".
			$li.
			"\t\t</ul>\n".
			"\t</div>\n".
			"\t<div class=\"t_popup_body\">\n".
			$panes.
			"\t\t<div class=\"options_controls\">\n".
			"\t\t\t<button class=\"delete_options\" data-style=\"button delete\">" . sprintf(__('%1$s %2$s', 'thesis'), $thesis->api->strings['delete'], $thesis->api->strings['package']) . "</button>\n".
			"\t\t</div>\n".
			"\t</div>\n".
			"\t<input type=\"hidden\" id=\"t_pkg_id\" name=\"id\" value=\"$this->_id\" />\n".
			"\t<input type=\"hidden\" id=\"t_pkg_class\" name=\"class\" value=\"$this->_class\" />\n".
			"\t<input type=\"hidden\" id=\"t_pkg_title\" name=\"title\" value=\"" . esc_attr(strip_tags($this->title)) . "\" />\n".
			"\t" . wp_nonce_field('thesis-save-package', '_wpnonce-thesis-save-package', true, false) . "\n".
			"</form>\n";
	}

	public function _save($pkg) {
		global $thesis;
		if (!is_array($pkg) || empty($pkg['id']) || !is_array($pkg['options']) || empty($pkg['options']['_name']) || empty($pkg['options']['_ref']) || $pkg['class'] != $this->_class) return false;
		return is_array($package = $thesis->api->set_options($this->_options, $pkg['options'])) ? array(
			'class' => $this->_class,
			'id' => $pkg['id'],
			'pkg' => $package) : false;
	}

	public function css() {
		// To be overwritten by extensions
		return false;
	}
}

class thesis_package_basic extends thesis_package {
	public $selector = 'body';

	protected function translate() {
		$this->title = __('Single Element Styles', 'thesis');
	}

	protected function options() {
		global $thesis;
		$o = $thesis->api->css->options;
		return array(
			'font' => $o['font'],
			'color' => $o['color'],
			'bg' => $o['background'],
			'box-sizing' => $o['box-sizing'],
			'width' => $o['width'],
			'border' => $o['border'],
			'margin' => $o['margin'],
			'padding' => $o['padding'],
			'typography' => $o['typography']);
	}

	public function css() {
		global $thesis;
		$e = array();
		if ($width = !empty($this->options['typography']) && is_numeric($this->options['typography']) ? $this->options['typography'] : false) {
			$size = !empty($this->options['font-size']) && is_numeric($this->options['font-size']) ? $this->options['font-size'] : 16;
			$typo = $thesis->api->typography->type($size, $width);
			$height = !!$width ? $typo['given']['height']['best'] : $typo['optimal']['height']['best'];
		}
		$e['width'] = !empty($this->options['width']) ? 'width: ' . $thesis->api->css->number($this->options['width']) . ';' : false;
		$e['size'] = !empty($this->options['font-size']) ? 'font-size: ' . $thesis->api->css->number($this->options['font-size']) . ';' : false;
		$e['height'] = !empty($this->options['line-height']) || !!$width ?
			'line-height: ' . (!empty($this->options['line-height']) ?
				$thesis->api->css->number($this->options['line-height']) :
				"{$height}px") . ';' : false;
		$e['family'] = !empty($this->options['font-family']) ? ($this->options['font-family'] == 'inherit' ?
			"font-family: inherit;" : (!empty($thesis->api->css->fonts) && !empty($thesis->api->css->fonts->fonts[$this->options['font-family']]) ?
			"font-family: {$thesis->api->css->fonts->fonts[$this->options['font-family']]['family']};" : false)) : false;
		$e['weight'] = !empty($this->options['font-weight']) ? "font-weight: {$this->options['font-weight']};" : false;
		$e['style'] = !empty($this->options['font-style']) ? "font-style: {$this->options['font-style']};" : false;
		$e['variant'] = !empty($this->options['font-variant']) ? "font-variant: {$this->options['font-variant']};" : false;
		$e['transform'] = !empty($this->options['text-transform']) ? "text-transform: {$this->options['text-transform']};" : false;
		$e['align'] = !empty($this->options['text-align']) ? "text-align: {$this->options['text-align']};" : false;
		$e['letter-spacing'] = !empty($this->options['letter-spacing']) ? "letter-spacing: " . $thesis->api->css->number($this->options['letter-spacing']) . ";" : false;
		$e['color'] = !empty($this->colors['color']) ? "color: {$this->colors['color']};" : false;
		$e['bg-color'] = !empty($this->colors['background-color']) ? "background-color: {$this->colors['background-color']};" : false;
		if (!empty($this->options['background-image'])) {
			$e['bg-image'] = "background-image: url('" . $thesis->api->esc($this->options['background-image']) . "');";
			$e['bg-position'] = !empty($this->options['background-position']) ? 'background-position: ' . stripslashes($this->options['background-position']) . ';' : false;
			$e['bg-attachment'] = !empty($this->options['background-attachment']) ? "background-attachment: {$this->options['background-attachment']};" : false;
			$e['bg-repeat'] = !empty($this->options['background-repeat']) ? "background-repeat: {$this->options['background-repeat']};" : false;
		}
		$e['border-width'] = ($bw = $thesis->api->css->number((!empty($this->options['border-width']) ? $this->options['border-width'] : ''))) ? "border-width: $bw;" : false;
		$e['border-style'] = !empty($this->options['border-style']) ? "border-style: {$this->options['border-style']};" : ($bw ? 'border-style: solid;' : false);
		$e['border-color'] = !empty($this->colors['border-color']) ? "border-color: {$this->colors['border-color']};" : false;
		$e['margin'] = $thesis->api->css->trbl('margin', array(
			'margin-top' => !empty($this->options['margin-top']) ? $this->options['margin-top'] : '',
			'margin-right' => !empty($this->options['margin-right']) ? $this->options['margin-right'] : '',
			'margin-bottom' => !empty($this->options['margin-bottom']) ? $this->options['margin-bottom'] : '',
			'margin-left' => !empty($this->options['margin-left']) ? $this->options['margin-left'] : ''));
		$e['padding'] = $thesis->api->css->trbl('padding', array(
			'padding-top' => !empty($this->options['padding-top']) ? $this->options['padding-top'] : '',
			'padding-right' => !empty($this->options['padding-right']) ? $this->options['padding-right'] : '',
			'padding-bottom' => !empty($this->options['padding-bottom']) ? $this->options['padding-bottom'] : '',
			'padding-left' => !empty($this->options['padding-left']) ? $this->options['padding-left'] : ''));
		$e['box-sizing'] = !empty($this->options['box-sizing']) ? $thesis->api->css->prefix('box-sizing', $this->options['box-sizing']) : false;
		if (is_array($e = array_filter($e)) && !empty($e))
			return "$this->selector { " . implode(' ', $e) . " }";
	}
}

class thesis_package_links extends thesis_package {
	public $selector = 'a';

	protected function translate() {
		$this->title = __('Links', 'thesis');
	}

	protected function options() {
		global $thesis;
		$o = $thesis->api->css->options;
		$options['links'] = $o['links'];
		$options['links']['fields'] = array_merge($options['links']['fields'], array('link-bg' => $o['background']['fields']['background-color'], 'padding' => $o['padding']));
		$options['links-hovered'] = $o['links-hovered'];
		$options['links-hovered']['fields'] = array_merge($options['links-hovered']['fields'], array('link-hover-bg' => $o['background']['fields']['background-color']));
		$options['links-visited'] = $o['links-visited'];
		$options['links-visited']['fields'] = array_merge($options['links-visited']['fields'], array('link-visited-bg' => $o['background']['fields']['background-color']));
		$options['links-active'] = $o['links-active'];
		$options['links-active']['fields'] = array_merge($options['links-active']['fields'], array('link-active-bg' => $o['background']['fields']['background-color']));
		return $options;
	}

	public function css() {
		global $thesis;
		$a = array();
		$colors = array('a' => array('link', 'link-bg'), 'hover' => array('link-hover', 'link-hover-bg'), 'visited' => array('link-visited', 'link-visited-bg'), 'active' => array('link-active', 'link-active-bg'));
		foreach ($colors as $state => $options)
			if (is_array($options))
				foreach ($options as $option)
					if (!empty($this->colors[$option]))
						$a[$state][$option] = (strpos($option, 'bg') ? "background-" : '') . "color: {$this->colors[$option]};";
		$a['a']['text'] = !empty($this->options['link-decoration']) ? "text-decoration: {$this->options['link-decoration']};" : false;
		$a['hover']['text'] = !empty($this->options['link-hover-decoration']) ? "text-decoration: {$this->options['link-hover-decoration']};" : false;
		$a['a']['padding'] = $thesis->api->css->trbl('padding', array(
			'padding-top' => !empty($this->options['padding-top']) ? $this->options['padding-top'] : '',
			'padding-right' => !empty($this->options['padding-right']) ? $this->options['padding-right'] : '',
			'padding-bottom' => !empty($this->options['padding-bottom']) ? $this->options['padding-bottom'] : '',
			'padding-left' => !empty($this->options['padding-left']) ? $this->options['padding-left'] : ''));
		foreach ($a as $pseudo => $properties) {
			$a[$pseudo] = array_filter($properties);
			if (empty($a[$pseudo]))
				unset($a[$pseudo]);
		}
		return trim((!empty($a['a']) && is_array($a['a']) ?
			"$this->selector { " . implode(' ', $a['a']) . " }\n" : '').
			(!empty($a['visited']) && is_array($a['visited']) ?
			"$this->selector:visited { " . implode(' ', $a['visited']) . " }\n" : '').
			(!empty($a['hover']) && is_array($a['hover']) ?
			"$this->selector:hover { " . implode(' ', $a['hover']) . " }\n" : '').
			(!empty($a['active']) && is_array($a['active']) ?
			"$this->selector:active { " . implode(' ', $a['active']) . " }\n" : ''));
	}
}

class thesis_package_wp_nav extends thesis_package {
	public $selector = '.menu';

	protected function translate() {
		$this->title = __('Horizontal Dropdown Menu (WP)', 'thesis');
	}

	protected function options() {
		global $thesis;
		$o = $thesis->api->css->options;
		$options['font'] = $o['font'];
		$options['menu-links'] = array(
			'type' => 'group',
			'label' => __('Link Settings', 'thesis'),
			'fields' => array('links' => $o['links'], 'links-hovered' => $o['links-hovered'], 'links-active' => $o['links-active']));
		$options['menu-links']['fields']['links']['fields']['link-bg'] = $options['menu-links']['fields']['links-hovered']['fields']['link-hover-bg'] = $options['menu-links']['fields']['links-active']['fields']['link-active-bg'] = $o['background']['fields']['background-color'];
		$options['menu-links']['fields'] = array_merge($options['menu-links']['fields'], array(
			'current-links' => array(
				'type' => 'group',
				'label' => __('Current Links', 'thesis'),
				'fields' => array(
					'link-current' => $options['menu-links']['fields']['links']['fields']['link'],
					'link-current-decoration' => $options['menu-links']['fields']['links']['fields']['link-decoration'],
					'link-current-bg' => $options['menu-links']['fields']['links']['fields']['link-bg'])),
			'parent-links' => array(
				'type' => 'group',
				'label' => __('Current Parent Links', 'thesis'),
				'fields' => array(
					'link-current-parent' => $options['menu-links']['fields']['links']['fields']['link'],
					'link-current-parent-decoration' => $options['menu-links']['fields']['links']['fields']['link-decoration'],
					'link-current-parent-bg' => $options['menu-links']['fields']['links']['fields']['link-bg']))));
		$options['padding'] = $o['padding'];
		$options['padding']['label'] = sprintf(__('Menu Item %s', 'thesis'), $options['padding']['label']);
		$options['borders'] = array(
			'type' => 'group',
			'label' => __('Border Settings', 'thesis'),
			'fields' => array_merge(array(
				'border-type' => array(
					'type' => 'radio',
					'label' => __('Choose a Menu Border Style:', 'thesis'),
					'options' => array(
						'' => __('none', 'thesis'),
						'tabbed' => __('tabbed', 'thesis'),
						/*'links' => __('links only', 'thesis')*/),
					'dependents' => array(
						'tabbed' => true,
						'links' => true))), $o['border']['fields']));
		$parents['parent'] = array('border-type' => array('tabbed', 'links'));
		$options['borders']['fields']['border-width'] = array_merge($options['borders']['fields']['border-width'], $parents);
		$options['borders']['fields']['border-style'] = array_merge($options['borders']['fields']['border-style'], $parents);
		$options['borders']['fields']['border-color'] = array_merge($options['borders']['fields']['border-color'], $parents);
		$options['submenu-width'] = array(
			'type' => 'text',
			'width' => 'tiny',
			'label' => __('Submenu Width', 'thesis'),
			'tooltip' => __('Enter a width for your dropdown submenus. If you&#8217;d like to use a unit besides pixels, be sure to include that unit here as well (ex: 15em).', 'thesis'),
			'placeholder' => 150);
		return $options;
	}

	public function css() {
		global $thesis;
		$s = $this->selector;
		$styles = array();
		$border = array('width' => '', 'style' => '', 'color' => '', 'a' => '', 'ul_a' => '');
		$bw = false;
		$padding = ($padding = $thesis->api->css->trbl('padding', array(
			'padding-top' => !empty($this->options['padding-top']) ? $this->options['padding-top'] : false,
			'padding-right' => !empty($this->options['padding-right']) ? $this->options['padding-right'] : false,
			'padding-bottom' => !empty($this->options['padding-bottom']) ? $this->options['padding-bottom'] : false,
			'padding-left' => !empty($this->options['padding-left']) ? $this->options['padding-left'] : false))) ? "$padding " : '';
		if (!empty($this->options['border-type'])) {
			$border['width'] = !empty($this->options['border-width']) && ($bw = $thesis->api->css->number($this->options['border-width'])) ? "border-width: $bw; " : '';
			$border['style'] = !empty($this->options['border-style']) ? "border-style: {$this->options['border-style']}; " : ($bw ? 'border-style: solid; ' : '');
			$border['color'] = !empty($this->colors['border-color']) ? "border-color: {$this->colors['border-color']}; " : '';
			if ($this->options['border-type'] == 'tabbed') {
				$border['a'] = $border['width'] . "border-left-width: 0; " . $border['style'];
				$border['ul_a'] = !empty($border['width']) ? "border-left-width: $bw; " . $border['style'] : '';
			}
		}
		$styles['links'] =
			"$s a { " . (!empty($this->options['font-family']) ? ($this->options['font-family'] == 'inherit' ?
			 	"font-family: inherit; " : (!empty($thesis->api->css->fonts) && !empty($thesis->api->css->fonts->fonts[$this->options['font-family']]) ?
				"font-family: {$thesis->api->css->fonts->fonts[$this->options['font-family']]['family']}; " : '')) : '') . (!empty($this->options['font-size']) && is_numeric($this->options['font-size']) ?
				"font-size: {$this->options['font-size']}px; " : '') . (!empty($this->options['line-height']) ?
				"line-height: " . $thesis->api->css->number($this->options['line-height']) . '; ' : '') . (!empty($this->options['font-weight']) ?
				"font-weight: {$this->options['font-weight']}; " : '') . (!empty($this->options['font-style']) ?
				"font-style: {$this->options['font-style']}; " : '') . (!empty($this->options['font-variant']) ?
				"font-variant: {$this->options['font-variant']}; " : '') . (!empty($this->options['text-transform']) ?
				"text-transform: {$this->options['text-transform']}; " : '') . (!empty($this->options['text-align']) ?
				"text-align: {$this->options['text-align']}; " : '') . (!empty($this->options['letter-spacing']) ?
				"letter-spacing: " . $thesis->api->css->number($this->options['letter-spacing']) . '; ' : '') . (!empty($this->options['link-decoration']) ?
				"text-decoration: {$this->options['link-decoration']}; " : '').
				$padding . $border['a'] . "}\n".
			"$s ul a { width: auto; {$border['ul_a']}}";
		if (!empty($this->options['border-type']) && $this->options['border-type'] == 'tabbed') {
			$styles['border-width-tabbed'] = $bw ?
				"$s { border-width: 0 0 $bw $bw; {$border['style']}}\n".
				"$s li ul { border-bottom-width: $bw; }\n".
				"$s li { margin-bottom: -$bw; }\n".
				"$s li ul { margin-top: -$bw; }\n".
				"$s ul ul { margin-top: 0; }" : false;
			$styles['border-colors-tabbed'] = $bw && !empty($border['color']) ?
				"$s, $s a, $s li ul { {$border['color']}}\n".
				"$s ul .current a, $s ul .current-cat a, $s .current ul a, $s .current-cat ul a, $s ul .current-menu-item a { border-bottom-color: {$this->colors['border-color']}; }" : false;
			$styles['border-current-tabbed'] = $bw && !empty($this->colors['link-current-bg']) ?
				"$s .current a, $s .current-cat a, $s .current-menu-item a { border-bottom-color: {$this->colors['link-current-bg']}; }" : false;
			$styles['border-position-tabbed'] = "$s li:hover ul, $s a:hover ul { left: " . ($bw ? "-$bw" : 0) . "; }";
		}
		$styles['link-colors'] = !empty($this->colors['link']) || !empty($this->colors['link-bg']) ?
			"$s a, $s .current ul a, $s .current-cat ul a, $s .current-menu-item ul a { " . (!empty($this->colors['link']) ?
				"color: {$this->colors['link']}; " : '') . (!empty($this->colors['link-bg']) ?
				"background-color: {$this->colors['link-bg']}; " : '') . "}" : false;
		$styles['hover'] = !empty($this->colors['link-hover']) || !empty($this->colors['link-hover-bg']) || !empty($this->options['link-hover-decoration']) ?
			"$s a:hover, $s .current ul a:hover, $s .current-cat ul a:hover, $s .current-parent a:hover, $s .current-menu-item ul a:hover, $s .current-menu-ancestor a:hover { " . (!empty($this->colors['link-hover']) ?
				"color: {$this->colors['link-hover']}; " : '') . (!empty($this->colors['link-hover-bg']) ?
				"background-color: {$this->colors['link-hover-bg']}; " : '') . (!empty($this->options['link-hover-decoration']) ?
				"text-decoration: {$this->options['link-hover-decoration']}; " : '') . "}" : false;
		$styles['active'] = !empty($this->colors['link-active']) || !empty($this->colors['link-active-bg']) || !empty($this->options['link-active-decoration']) ?
			"$s a:active, $s .current ul a:active, $s .current-cat ul a:active, $s .current-parent a:active, $s .current-menu-item ul a:active, $s .current-menu-ancestor a:active { " . (!empty($this->colors['link-active']) ?
				"color: {$this->colors['link-active']}; " : '') . (!empty($this->colors['link-active-bg']) ?
				"background-color: {$this->colors['link-active-bg']}; " : '') . (!empty($this->options['link-active-decoration']) ?
				"text-decoration: {$this->options['link-active-decoration']}; " : '') . "}" : false;
		$styles['current'] = !empty($this->colors['link-current']) || !empty($this->colors['link-current-bg']) || !empty($this->colors['link-current-decoration']) ?
			"$s .current a, $s .current a:hover, $s .current-cat a, $s .current-cat a:hover, $s .current-menu-item a, $s .current-menu-item a:hover { " . (!empty($this->colors['link-current']) ?
				"color: {$this->colors['link-current']}; " : '') . (!empty($this->colors['link-current-bg']) ?
				"background: {$this->colors['link-current-bg']}; " : '') . (!empty($this->options['link-current-decoration']) ?
				"text-decoration: {$this->options['link-current-decoration']}; " : '') . "}" : false;
		$styles['parent'] = !empty($this->colors['link-current-parent']) || !empty($this->colors['link-current-parent-bg']) ?
			"$s .current-parent > a, $s .current-cat-parent > a, $s .current-menu-ancestor > a { " . (!empty($this->colors['link-current-parent']) ?
				"color: {$this->colors['link-current-parent']}; " : '') . (!empty($this->colors['link-current-parent-bg']) ?
				"background: {$this->colors['link-current-parent-bg']}; " : '') . "}" : false;
		$submenu_width = $thesis->api->css->number((!empty($this->options['submenu-width']) ? $this->options['submenu-width'] : ''), '150px');
		foreach ($styles as $style => $value)
			if (empty($value))
				unset($styles[$style]);
		$styles = implode("\n", $styles);
		$this->clearfix = array($s);
		return
			"$s { position: relative; list-style: none; z-index: 50; }\n".
			"$s li { position: relative; float: left; }\n".
			"$s ul { position: absolute; visibility: hidden; list-style: none; z-index: 110; }\n".
			"$s ul li { clear: both; }\n".
			"$s a { display: block; }\n".
			"$s ul ul { position: absolute; top: 0; }\n".
			"$s li:hover ul, $s a:hover ul, $s :hover ul :hover ul, $s :hover ul :hover ul :hover ul { visibility: visible; }\n".
			"$s :hover ul ul, $s :hover ul :hover ul ul { visibility: hidden; }\n".
			"$s ul, $s ul li { width: $submenu_width; }\n".
			"$s ul ul, $s :hover ul :hover ul { left: $submenu_width; }\n".
			"$styles";
	}
}

class thesis_package_post_format extends thesis_package {
	public $selector = '.post_box';

	protected function translate() {
		$this->title = __('Post Formatting', 'thesis');
	}

	protected function options() {
		global $thesis;
		$o = $thesis->api->css->options;
		$options['subhead'] = $options['headline'] = $options['text'] = $o['font'];
		foreach (array('text', 'headline', 'subhead') as $e)
			foreach ($o['font']['fields'] as $name => $option) {
				unset($options[$e]['fields'][$name]);
				if (!($e == 'text' && in_array($name, array('font-style', 'font-variant', 'text-transform', 'letter-spacing'))))
					$options[$e]['fields']["$e-$name"] = $option;
			}
		$options['headline']['label'] = __('Headlines', 'thesis');
		$options['subhead']['label'] = __('Sub-headlines', 'thesis');
		$options['lists'] = $o['lists'];
		$options['typography'] = $o['typography'];
		return $options;
	}

	public function css() {
		global $thesis;
		$s = $this->selector;
		$size = $type = $height = $scale = $font = $spacing = $list = array();
		$css = '';
		$elements = array(
			'text' => "$s, $s h4",
			'headline' => "$s h1, $s .headline",
			'subhead' => "$s .post_content h2, $s h3",
			'aux' => "$s h5, $s .small");
		$width = !empty($this->options['typography']) && is_numeric($this->options['typography']) ? $this->options['typography'] : false;
		foreach ($elements as $e => $selector) {
			$size[$e] = !empty($this->options["$e-font-size"]) && is_numeric($this->options["$e-font-size"]) ? $this->options["$e-font-size"] : ($e == 'text' ? 16 : $scale[$e]);
			$type[$e] = $thesis->api->typography->type($size[$e], $width);
			$height[$e] = !!$width ? $type[$e]['given']['height']['best'] : $type[$e]['optimal']['height']['best'];
			if ($e == 'text')
				$spacing = $thesis->api->typography->spacing($size[$e], $height[$e], apply_filters("{$this->_class}_unit", false));
			$font[$e]['size'] = "font-size: {$size[$e]}px;";
			$font[$e]['height'] = "line-height: " . (!empty($this->options["$e-line-height"]) ?
				$thesis->api->css->number($this->options["$e-line-height"]) :
				"{$height[$e]}px") . ';';
			$font[$e]['family'] = !empty($this->options["$e-font-family"]) ? ($this->options["$e-font-family"] == 'inherit' ?
				"font-family: inherit;" : (!empty($thesis->api->css->fonts) && !empty($thesis->api->css->fonts->fonts[$this->options["$e-font-family"]]) ?
				"font-family: {$thesis->api->css->fonts->fonts[$this->options["$e-font-family"]]['family']};" : false)) : false;
			$font[$e]['weight'] = !empty($this->options["$e-font-weight"]) ? "font-weight: " . $this->options["$e-font-weight"] . ";" : false;
			$font[$e]['style'] = !empty($this->options["$e-font-style"]) ? "font-style: " . $this->options["$e-font-style"] . ";" : false;
			$font[$e]['variant'] = !empty($this->options["$e-font-variant"]) ? "font-variant: " . $this->options["$e-font-variant"] . ";" : false;
			$font[$e]['transform'] = !empty($this->options["$e-text-transform"]) ? "text-transform: " . $this->options["$e-text-transform"] . ";" : false;
			$font[$e]['align'] = !empty($this->options["$e-text-align"]) ? "text-align: {$this->options["$e-text-align"]};" : false;
			$font[$e]['letter-spacing'] = !empty($this->options["$e-letter-spacing"]) ? "letter-spacing: " . $thesis->api->css->number($this->options["$e-letter-spacing"]) . ";" : false;
			if ($e == 'subhead')
				$font[$e]['margins'] = "margin-top: {$spacing['3over2']}; margin-bottom: {$spacing['half']};";
			$css .= "$selector { " . implode(' ', array_filter($font[$e])) . " }\n";
			if ($e == 'text')
				$scale = $thesis->api->typography->scale($size[$e]);
		}
		$cap['size'] = "font-size: " . ((!empty($this->options['text-line-height']) && is_numeric($this->options['text-line-height']) ? $this->options['text-line-height'] : $height['text']) * 2) . "px;";
		$cap['line-height'] = "line-height: 1em;";
		$cap['margin'] = "margin-right: " . round((!empty($this->options['text-line-height']) && is_numeric($this->options['text-line-height']) ? $this->options['text-line-height'] : $height['text']) / 3, 0) . "px;";
		$cap['float'] = "float: left;";
		$css .= "$s .drop_cap { " . implode(' ', $cap) . " }\n";
		$list['style'] = !empty($this->options['list-style-type']) ? "$s ul { list-style-type: {$this->options['list-style-type']};" . (!empty($this->options['list-style-position']) ? " list-style-position: {$this->options['list-style-position']};" : '') . " }" : false;
		$list['indent'] = !empty($this->options['list-indent']['on']) ? "$s ul, $s ol { margin-left: {$spacing['single']}; }" : false;
		$list['item-margin'] = ($li = (!empty($this->options['list-item-margin']) ? $this->options['list-item-margin'] : false)) ?
			"$s li { margin-bottom: {$spacing[$li]}; }\n".
			"$s ul ul, $s ul ol, $s ol ul, $s ol ol { margin-top: {$spacing[$li]}; }" : false;
		$list = ($l = array_filter($list)) && !empty($l) ? implode("\n", array_filter($l)) . "\n" : '';
		$this->clearfix = array($s, "$s .post_content");
		return
			$css.
			"$s p, $s ul, $s ol, $s blockquote, $s pre, $s dl, $s dd { margin-bottom: {$spacing['single']}; }\n".
			$list.
			"$s ul ul, $s ul ol, $s ol ul, $s ol ol { margin-left: {$spacing['single']}; }\n".
			"$s ul ul, $s ul ol, $s ol ul, $s ol ol, .wp-caption p { margin-bottom: 0; }\n".
			"$s .left, $s .alignleft, $s .ad_left { margin-bottom: {$spacing['single']}; margin-right: {$spacing['single']}; }\n".
			"$s .right, $s .alignright, $s .ad { margin-bottom: {$spacing['single']}; margin-left: {$spacing['single']}; }\n".
			"$s .center, $s .aligncenter { margin-bottom: {$spacing['single']}; }\n".
			"$s .block, $s .alignnone { margin-bottom: {$spacing['single']}; }\n".
			"$s .stack { margin-left: {$spacing['single']}; }";
	}
}

class thesis_package_columns extends thesis_package {
	public $selector = '.columns';

	protected function translate() {
		$this->title = __('Columns', 'thesis');
	}

	protected function options() {
		global $thesis;
		$c = __('Column', 'thesis');
		$cols = array(1, 2, 3, 4);
		$o = $thesis->api->css->options;
		$options = array(
			'columns' => array(
				'type' => 'select',
				'label' => sprintf(__('Select Number of %s', 'thesis'), $this->title),
				'tooltip' => sprintf(__('<strong>Attention!</strong> If you&#8217;re using columns to construct your main layout, please note that for %1$s purposes, the primary content <strong>must</strong> come first in the %2$s markup.<br /><br />Because of this, we suggest using a combination of two-column structures if you wish to construct a 3-column layout where the primary content is in the middle.', 'thesis'), $thesis->api->base['seo'], $thesis->api->base['html']),
				'options' => array(
					2 => "2 $this->title",
					3 => "3 $this->title",
					4 => "4 $this->title"),
				'default' => 2,
				'dependents' => array(
					2 => true,
					3 => true,
					4 => true)),
			'column-1' => array(
				'type' => 'group',
				'label' => "$c 1",
				'parent' => array('columns' => array(2, 3, 4)),
				'fields' => array()),
			'column-2' => array(
				'type' => 'group',
				'label' => "$c 2",
				'parent' => array('columns' => array(2, 3, 4)),
				'fields' => array()),
			'column-3' => array(
				'type' => 'group',
				'label' => "$c 3",
				'parent' => array('columns' => array(3, 4)),
				'fields' => array()),
			'column-4' => array(
				'type' => 'group',
				'label' => "$c 4",
				'parent' => array('columns' => 4),
				'fields' => array()));
		foreach ($cols as $col) {
			$options["column-$col"]['fields']["$col-selector"] = array(
				'type' => 'text',
				'width' => 'medium',
				'code' => true,
				'label' => sprintf(__('Column %1$s %2$s Selector', 'thesis'), $col, $thesis->api->base['css']),
				'tooltip' => sprintf(__('Enter your column %s selector here. If you don&#8217;t enter anything, Thesis will use a default selector of <code>.cX</code>, where X is the column number.', 'thesis'), $thesis->api->base['css']));
			$options["column-$col"]['fields']["$col-width"] = $o['width'];
			$options["column-$col"]['fields']["$col-width"]['label'] = sprintf(__('%1$s %2$s %3$s', 'thesis'), $c, $col, $thesis->api->css->strings['width']);
			$options["column-$col"]['fields']["$col-float"] = $o['float'];
			$options["column-$col"]['fields']["$col-float"]['label'] = sprintf(__('%1$s %2$s %3$s', 'thesis'), $c, $col, $thesis->api->strings['alignment']);
			$options["column-$col"]['fields']['padding'] = $o['padding'];
			$options["column-$col"]['fields']['padding']['label'] = sprintf(__('%1$s %2$s %3$s', 'thesis'), $c, $col, $thesis->api->css->strings['padding']);
			foreach ($options["column-$col"]['fields']['padding']['fields'] as $name => $option) {
				$options["column-$col"]['fields']['padding']['fields']["$col-$name"] = $option;
				unset($options["column-$col"]['fields']['padding']['fields'][$name]);
			}
		}
		return $options;
	}

	public function css() {
		global $thesis;
		$columns = array();
		$boxes = array($this->selector);
		for ($c = 1; $c <= $this->options['columns']; $c++) {
			$columns[$c]['selector'] = !empty($this->options["$c-selector"]) ? trim(strip_tags(stripslashes($this->options["$c-selector"]))) : ".c$c";
			$columns[$c]['width'] = !empty($this->options["$c-width"]) ? 'width: ' . $thesis->api->css->number($this->options["$c-width"]) . ';' : false;
			$columns[$c]['float'] = !empty($this->options["$c-float"]) ? "float: {$this->options["$c-float"]};" : false;
			$columns[$c]['padding'] = $thesis->api->css->trbl('padding', array(
				'padding-top' => !empty($this->options["$c-padding-top"]) ? $this->options["$c-padding-top"] : '',
				'padding-right' => !empty($this->options["$c-padding-right"]) ? $this->options["$c-padding-right"] : '',
				'padding-bottom' => !empty($this->options["$c-padding-bottom"]) ? $this->options["$c-padding-bottom"] : '',
				'padding-left' => !empty($this->options["$c-padding-left"]) ? $this->options["$c-padding-left"] : ''));
			$boxes[] = "$this->selector > {$columns[$c]['selector']}";
		}
		$css = implode(', ', $boxes) . " { -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box; }\n";
		foreach ($columns as $c => $column) {
			$sel = $column['selector'];
			unset($column['selector']);
			$css .= "$this->selector > $sel { " . implode(' ', array_filter($column)) . " }\n";
		}
		$this->clearfix = array($this->selector);
		return trim($css);
	}
}

class thesis_package_wp_comments extends thesis_package {
	public $selector = '.comment';

	protected function translate() {
		global $thesis;
		$this->title = $thesis->api->strings['comments'];
	}

	protected function options() {
		global $thesis;
		$o = $thesis->api->css->options;
		$options['nested-author'] = $options['author'] = $options['nested'] = $options['comments'] = array(
			'type' => 'group',
			'fields' => array());
		$options['comments']['fields']['subhead'] = $options['comments']['fields']['text'] = $o['font'];
		$options['comments']['label'] = $this->title;
		$options['nested']['label'] = __('Nested Comments', 'thesis');
		$options['author']['label'] = __('Author Comments', 'thesis');
		$options['nested-author']['label'] = __('Nested Author Comments', 'thesis');
		foreach (array('comments', 'nested', 'author', 'nested-author') as $c) {
			if ($c == 'comments') {
				foreach (array('text', 'subhead') as $e) {
					foreach ($o['font']['fields'] as $name => $option) {
						unset($options[$c]['fields'][$e]['fields'][$name]);
						if (!($e == 'text' && in_array($name, array('font-style', 'font-variant', 'text-transform', 'letter-spacing'))))
							$options[$c]['fields'][$e]['fields']["$e-$name"] = $option;
					}
					if ($e == 'subhead')
						$options[$c]['fields']['subhead']['label'] = __('Comment Author', 'thesis');
				}
				$options[$c]['fields']['lists'] = $o['lists'];
				$options[$c]['fields']['box-sizing'] = $o['box-sizing'];
				$options[$c]['fields']['width'] = $o['width'];
				$options[$c]['fields']['typography'] = $o['typography'];
			}
			$options[$c]['fields']["$c-color"] = $o['color'];
			$options[$c]['fields']['background'] = $o['background'];
			$options[$c]['fields']['border'] = $o['border'];
			$options[$c]['fields']['margin'] = $o['margin'];
			$options[$c]['fields']['padding'] = $o['padding'];
			foreach (array('background', 'border', 'margin', 'padding') as $type)
				foreach ($o[$type]['fields'] as $name => $option) {
					unset($options[$c]['fields'][$type]['fields'][$name]);
					$options[$c]['fields'][$type]['fields']["$c-$name"] = $option;
				}
		}
		return $options;
	}

	private function props($c) {
		global $thesis;
		if (!$c) return false;
		$prop = array();
		$prop['color'] = !empty($this->colors["$c-color"]) ? "color: {$this->colors["$c-color"]};" : false;
		$prop['bg-color'] = !empty($this->colors["$c-background-color"]) ? "background-color: {$this->colors["$c-background-color"]};" : false;
		if (!empty($this->options["$c-background-image"])) {
			$prop['bg-image'] = "background-image: url('" . $thesis->api->esc($this->options["$c-background-image"]) . "');";
			$prop['bg-position'] = !empty($this->options["$c-background-position"]) ? 'background-position: ' . stripslashes($this->options["$c-background-position"]) . ';' : false;
			$prop['bg-attachment'] = !empty($this->options["$c-background-attachment"]) ? "background-attachment: {$this->options["$c-background-attachment"]};" : false;
			$prop['bg-repeat'] = !empty($this->options["$c-background-repeat"]) ? "background-repeat: {$this->options["$c-background-repeat"]};" : false;
		}
		$prop['border-width'] = ($bw = $thesis->api->css->number((!empty($this->options["$c-border-width"]) ? $this->options["$c-border-width"] : ''))) ? "border-width: $bw;" : false;
		$prop['border-style'] = !empty($this->options["$c-border-style"]) ? "border-style: {$this->options["$c-border-style"]};" : ($bw ? 'border-style: solid;' : false);
		$prop['border-color'] = !empty($this->colors["$c-border-color"]) ? "border-color: {$this->colors["$c-border-color"]};" : false;
		$prop['margin'] = $thesis->api->css->trbl('margin', array(
			'margin-top' => !empty($this->options["$c-margin-top"]) ? $this->options["$c-margin-top"] : '',
			'margin-right' => !empty($this->options["$c-margin-right"]) ? $this->options["$c-margin-right"] : '',
			'margin-bottom' => !empty($this->options["$c-margin-bottom"]) ? $this->options["$c-margin-bottom"] : '',
			'margin-left' => !empty($this->options["$c-margin-left"]) ? $this->options["$c-margin-left"] : ''));
		$prop['padding'] = $thesis->api->css->trbl('padding', array(
			'padding-top' => !empty($this->options["$c-padding-top"]) ? $this->options["$c-padding-top"] : '',
			'padding-right' => !empty($this->options["$c-padding-right"]) ? $this->options["$c-padding-right"] : '',
			'padding-bottom' => !empty($this->options["$c-padding-bottom"]) ? $this->options["$c-padding-bottom"] : '',
			'padding-left' => !empty($this->options["$c-padding-left"]) ? $this->options["$c-padding-left"] : ''));
		return array_filter($prop);
	}

	public function css() {
		global $thesis;
		$s = $this->selector;
		$styles = $size = $type = $height = $scale = $font = $prop = $spacing = $list = array();
		$css = '';
		$items = array(
			'comments' => $s,
			'comment-author' => "$s .comment_author",
			'comment-aux' => "$s .comment_aux",
			'nested' => ".children $s",
			'author' => '.bypostauthor',
			'nested-author' => ".children .bypostauthor");
		foreach ($items as $c => $sel) {
			$styles[$c] = array();
			if ($c == 'comments') {
				$styles[$c]['width'] = !empty($this->options['width']) ? 'width: ' . $thesis->api->css->number($this->options['width']) . ';' : false;
				$width = !empty($this->options['typography']) && is_numeric($this->options['typography']) ? $this->options['typography'] : false;
				foreach (array('text', 'subhead', 'aux') as $e) {
					$size[$e] = !empty($this->options["$e-font-size"]) && is_numeric($this->options["$e-font-size"]) ? $this->options["$e-font-size"] : ($e == 'text' ? 16 : $scale[$e]);
					$type[$e] = $thesis->api->typography->type($size[$e], $width);
					$height[$e] = !empty($width) ? $type[$e]['given']['height']['best'] : $type[$e]['optimal']['height']['best'];
					$font[$e]['size'] = "font-size: {$size[$e]}px;";
					$font[$e]['height'] = "line-height: " . (!empty($this->options["$e-line-height"]) ?
						$thesis->api->css->number($this->options["$e-line-height"]) :
						"{$height[$e]}px") . ';';
					$font[$e]['family'] = !empty($this->options["$e-font-family"]) ? ($this->options["$e-font-family"] == 'inherit' ?
						"font-family: inherit;" : (!empty($thesis->api->css->fonts) && !empty($thesis->api->css->fonts->fonts[$this->options["$e-font-family"]]) ?
						"font-family: {$thesis->api->css->fonts->fonts[$this->options["$e-font-family"]]['family']};" : false)) : false;
					$font[$e]['weight'] = !empty($this->options["$e-font-weight"]) ? "font-weight: " . $this->options["$e-font-weight"] . ";" : false;
					$font[$e]['style'] = !empty($this->options["$e-font-style"]) ? "font-style: " . $this->options["$e-font-style"] . ";" : false;
					$font[$e]['variant'] = !empty($this->options["$e-font-variant"]) ? "font-variant: " . $this->options["$e-font-variant"] . ";" : false;
					$font[$e]['transform'] = !empty($this->options["$e-text-transform"]) ? "text-transform: " . $this->options["$e-text-transform"] . ";" : false;
					$font[$e]['align'] = !empty($this->options["$e-text-align"]) ? "text-align: {$this->options["$e-text-align"]};" : false;
					$font[$e]['letter-spacing'] = !empty($this->options["$e-letter-spacing"]) ? "letter-spacing: " . $thesis->api->css->number($this->options["$e-letter-spacing"]) . ";" : false;
					$font[$e] = array_filter($font[$e]);
					if (!empty($e) && $e == 'text') {
						$scale = $thesis->api->typography->scale($size[$e]);
						if (!empty($font[$e]))
							$styles[$c] = array_merge($styles[$c], $font[$e]);
					}
				}
				$spacing = $thesis->api->typography->spacing($size['text'], $height['text'], apply_filters("{$this->_class}_unit", false));
			}
			elseif ($c == 'comment-author')
				$styles[$c] = !empty($font['subhead']) ? $font['subhead'] : false;
			elseif ($c == 'comment-aux')
				$styles[$c] = !empty($font['aux']) ? $font['aux'] : false;
			elseif ($c == 'nested')
				$styles[$c]['list'] = "list-style-type: none;";
			$styles[$c] = is_array($props = $this->props($c)) ? array_merge($styles[$c], $props) : $styles[$c];
			if ($c == 'comments')
				$styles[$c]['box-sizing'] = !empty($this->options['box-sizing']) ? $thesis->api->css->prefix('box-sizing', $this->options['box-sizing']) : false;
			if (is_array($scrubbed = array_filter($styles[$c])) && !empty($scrubbed))
				$css .= "$sel { " . implode(' ', array_filter($scrubbed)) . " }\n";
		}
		$list['style'] = !empty($this->options['list-style-type']) ?
			"$s .comment_text ul { list-style-type: {$this->options['list-style-type']};" . (!empty($this->options['list-style-position']) ? " list-style-position: {$this->options['list-style-position']};" : '') . " }" : false;
		$list['indent'] = !empty($this->options['list-indent']['on']) ?
			"$s .comment_text ul, $s .comment_text ol { margin-left: {$spacing['single']}; }" : false;
		$list['item-margin'] = ($li = (!empty($this->options['list-item-margin']) ? $this->options['list-item-margin'] : '')) ?
			"$s .comment_text li { margin-bottom: {$spacing[$li]}; }\n".
			"$s .comment_text li ul, $s .comment_text li ol { margin-top: {$spacing[$li]}; }" : false;
		$list = ($l = array_filter($list)) && !empty($l) ? implode("\n", array_filter($l)) . "\n" : '';
		$this->clearfix = array("$s .comment_text");
		return
			$css.
			$list.
			"$s p, $s .comment_text ul, $s .comment_text ol, $s .comment_text blockquote, $s .comment_text pre { margin-bottom: {$spacing['single']}; }\n".
			"$s .comment_text li ul, $s .comment_text li ol { margin-left: {$spacing['single']}; margin-bottom: 0; }\n".
			"$s .comment_text .left, $s .comment_text .alignleft { margin-bottom: {$spacing['single']}; margin-right: {$spacing['single']}; }\n".
			"$s .comment_text .right, $s .comment_text .alignright { margin-bottom: {$spacing['single']}; margin-left: {$spacing['single']}; }\n".
			"$s .comment_text .center, $s .comment_text .aligncenter { margin: 0 auto {$spacing['single']} auto; }\n".
			"$s .comment_text .block, $s .comment_text .alignnone { margin: 0 auto {$spacing['single']} 0; }";
	}
}

class thesis_package_input extends thesis_package {
	public $selector = 'input';

	protected function translate() {
		$this->title = __('Form Input', 'thesis');
	}

	protected function options() {
		global $thesis;
		$o = $thesis->api->css->options;
		$options = array(
			'input' => array(
				'type' => 'group',
				'label' => __('Input', 'thesis'),
				'fields' => array(
					'font' => $o['font'],
					'color' => $o['color'],
					'background' => $o['background'],
					'box-sizing' => $o['box-sizing'],
					'width' => $o['width'],
					'border' => $o['border'],
					'margin' => $o['margin'],
					'padding' => $o['padding'])),
			'focus' => array(
				'type' => 'group',
				'label' => __('Input:focus', 'thesis'),
				'fields' => array(
					'focus-color' => $o['color'],
					'background' => $o['background'],
					'border' => $o['border'])));
		foreach ($options['focus']['fields'] as $item_name => $item)
			if (isset($o[$item_name]) && $o[$item_name]['type'] == 'group')
				foreach ($o[$item_name]['fields'] as $name => $option) {
					unset($options['focus']['fields'][$item_name]['fields'][$name]);
					$options['focus']['fields'][$item_name]['fields']["focus-$name"] = $option;
				}
		return $options;
	}

	public function css() {
		global $thesis;
		$css = array();
		$i = $f = array();
		$i['width'] = !empty($this->options['width']) ? 'width: ' . $thesis->api->css->number($this->options['width']) . ';' : false;
		$i['size'] = 'font-size: ' . (!empty($this->options['font-size']) ? $thesis->api->css->number($this->options['font-size']) . ';' : 'inherit;');
		$i['height'] = 'line-height: ' . (!empty($this->options['line-height']) ? $thesis->api->css->number($this->options['line-height']) : '1em') . ';';
		$i['family'] = "font-family: " . (!empty($this->options['font-family']) && !empty($thesis->api->css->fonts) && !empty($thesis->api->css->fonts->fonts[$this->options['font-family']]) ?
			$thesis->api->css->fonts->fonts[$this->options['font-family']]['family'] : 'inherit') . ';';
		$i['weight'] = !empty($this->options['font-weight']) ? "font-weight: {$this->options['font-weight']};" : false;
		$i['style'] = !empty($this->options['font-style']) ? "font-style: {$this->options['font-style']};" : false;
		$i['variant'] = !empty($this->options['font-variant']) ? "font-variant: {$this->options['font-variant']};" : false;
		$i['transform'] = !empty($this->options['text-transform']) ? "text-transform: {$this->options['text-transform']};" : false;
		$i['align'] = !empty($this->options['text-align']) ? "text-align: {$this->options['text-align']};" : false;
		$i['letter-spacing'] = !empty($this->options['letter-spacing']) ? "letter-spacing: " . $thesis->api->css->number($this->options['letter-spacing']) . ";" : false;
		$i['color'] = !empty($this->colors['color']) ? "color: {$this->colors['color']};" : false;
		$i['bg-color'] = !empty($this->colors['background-color']) ? "background-color: {$this->colors['background-color']};" : false;
		if (!empty($this->options['background-image'])) {
			$i['bg-image'] = "background-image: url('" . $thesis->api->esc($this->options['background-image']) . "');";
			$i['bg-position'] = !empty($this->options['background-position']) ? 'background-position: ' . stripslashes($this->options['background-position']) . ';' : false;
			$i['bg-attachment'] = !empty($this->options['background-attachment']) ? "background-attachment: {$this->options['background-attachment']};" : false;
			$i['bg-repeat'] = !empty($this->options['background-repeat']) ? "background-repeat: {$this->options['background-repeat']};" : false;
		}
		$i['border-width'] = !empty($this->options['border-width']) && ($bw = $thesis->api->css->number($this->options['border-width'])) ? "border-width: $bw;" : false;
		$i['border-style'] = !empty($this->options['border-style']) ? "border-style: {$this->options['border-style']};" : ($bw ? 'border-style: solid;' : false);
		$i['border-color'] = !empty($this->colors['border-color']) ? "border-color: {$this->colors['border-color']};" : false;
		$i['margin'] = $thesis->api->css->trbl('margin', array(
			'margin-top' => !empty($this->options['margin-top']) ? $this->options['margin-top'] : '',
			'margin-right' => !empty($this->options['margin-right']) ? $this->options['margin-right'] : '',
			'margin-bottom' => !empty($this->options['margin-bottom']) ? $this->options['margin-bottom'] : '',
			'margin-left' => !empty($this->options['margin-left']) ? $this->options['margin-left'] : ''));
		$i['padding'] = $thesis->api->css->trbl('padding', array(
			'padding-top' => !empty($this->options['padding-top']) ? $this->options['padding-top'] : '',
			'padding-right' => !empty($this->options['padding-right']) ? $this->options['padding-right'] : '',
			'padding-bottom' => !empty($this->options['padding-bottom']) ? $this->options['padding-bottom'] : '',
			'padding-left' => !empty($this->options['padding-left']) ? $this->options['padding-left'] : ''));
		$i['box-sizing'] = !empty($this->options['box-sizing']) ? $thesis->api->css->prefix('box-sizing', $this->options['box-sizing']) : false;
		$f['color'] = !empty($this->colors['focus-color']) ? "color: {$this->colors['focus-color']};" : false;
		$f['bg-color'] = !empty($this->colors['focus-background-color']) ? "background-color: {$this->colors['focus-background-color']};" : false;
		if (!empty($this->options['focus-background-image'])) {
			$f['bg-image'] = "background-image: url('" . $thesis->api->esc($this->options['focus-background-image']) . "');";
			$f['bg-position'] = !empty($this->options['focus-background-position']) ? 'background-position: ' . stripslashes($this->options['focus-background-position']) . ';' : false;
			$f['bg-attachment'] = !empty($this->options['focus-background-attachment']) ? "background-attachment: {$this->options['focus-background-attachment']};" : false;
			$f['bg-repeat'] = !empty($this->options['focus-background-repeat']) ? "background-repeat: {$this->options['focus-background-repeat']};" : false;
		}
		$f['border-width'] = ($bw = $thesis->api->css->number((!empty($this->options['focus-border-width']) ? $this->options['focus-border-width'] : ''))) ? "border-width: $bw;" : false;
		$f['border-style'] = !empty($this->options['focus-border-style']) ? "border-style: {$this->options['focus-border-style']};" : ($bw ? 'border-style: solid;' : false);
		$f['border-color'] = !empty($this->colors['focus-border-color']) ? "border-color: {$this->colors['focus-border-color']};" : false;
		if (is_array($i = array_filter($i)) && !empty($i))
			$css['input'] = "$this->selector { " . implode(' ', $i) . " }";
		if (is_array($f = array_filter($f)) && !empty($f))
			$css['focus'] = "$this->selector:focus { " . implode(' ', $f) . " }";
		return implode("\n", $css);
	}
}

class thesis_package_wp_widgets extends thesis_package {
	public $selector = '.widget';

	protected function translate() {
		$this->title = __('Widgets', 'thesis');
	}

	protected function options() {
		global $thesis;
		$o = $thesis->api->css->options;
		$elements = array('text', 'subhead');
		$subs = array('margin', 'padding');
		$options['text'] = array(
			'type' => 'group',
			'label' => __('Widgets', 'thesis'));
		$options['subhead'] = array(
			'type' => 'group',
			'label' => __('Widget Titles', 'thesis'));
		$options['subhead']['fields'] = $options['text']['fields'] = array('font' => $o['font'], 'margin' => $o['margin'], 'padding' => $o['padding']);
		foreach ($elements as $e) {
			foreach ($o['font']['fields'] as $name => $option) {
				unset($options[$e]['fields']['font']['fields'][$name]);
				if (!($e == 'text' && in_array($name, array('font-style', 'font-variant', 'text-transform', 'letter-spacing'))))
					$options[$e]['fields']['font']['fields']["$e-$name"] = $option;
			}
			foreach ($subs as $sub)
				foreach ($o[$sub]['fields'] as $name => $option) {
					unset($options[$e]['fields'][$sub]['fields'][$name]);
					$options[$e]['fields'][$sub]['fields']["$e-$name"] = $option;
				}
		}
		$options['lists'] = $o['lists'];
		$options['lists']['label'] = __('Widget Lists', 'thesis');
		$options['typography'] = $o['typography'];
		return $options;
	}

	public function css() {
		global $thesis;
		$s = $this->selector;
		$size = $type = $height = $scale = $prop = $spacing = array();
		$css = '';
		$width = !empty($this->options['typography']) && is_numeric($this->options['typography']) ? $this->options['typography'] : false;
		foreach (array('text' => $s, 'subhead' => "$s .widget_title") as $e => $selector) {
			$size[$e] = !empty($this->options["$e-font-size"]) && is_numeric($this->options["$e-font-size"]) ? $this->options["$e-font-size"] : ($e == 'text' ? 16 : $scale[$e]);
			$type[$e] = $thesis->api->typography->type($size[$e], $width);
			$height[$e] = !empty($width) ? $type[$e]['given']['height']['best'] : $type[$e]['optimal']['height']['best'];
			$prop[$e]['size'] = "font-size: {$size[$e]}px;";
			$prop[$e]['height'] = "line-height: " . (!empty($this->options["$e-line-height"]) ?
				$thesis->api->css->number($this->options["$e-line-height"]) :
				"{$height[$e]}px") . ';';
			$prop[$e]['family'] = !empty($this->options["$e-font-family"]) ? ($this->options["$e-font-family"] == 'inherit' ?
				"font-family: inherit;" : (!empty($thesis->api->css->fonts) && !empty($thesis->api->css->fonts->fonts[$this->options["$e-font-family"]]) ?
				"font-family: {$thesis->api->css->fonts->fonts[$this->options["$e-font-family"]]['family']};" : false)) : false;
			$prop[$e]['weight'] = !empty($this->options["$e-font-weight"]) ? "font-weight: " . $this->options["$e-font-weight"] . ";" : false;
			$prop[$e]['style'] = !empty($this->options["$e-font-style"]) ? "font-style: " . $this->options["$e-font-style"] . ";" : false;
			$prop[$e]['variant'] = !empty($this->options["$e-font-variant"]) ? "font-variant: " . $this->options["$e-font-variant"] . ";" : false;
			$prop[$e]['transform'] = !empty($this->options["$e-text-transform"]) ? "text-transform: " . $this->options["$e-text-transform"] . ";" : false;
			$prop[$e]['align'] = !empty($this->options["$e-text-align"]) ? "text-align: {$this->options["$e-text-align"]};" : false;
			$prop[$e]['letter-spacing'] = !empty($this->options["$e-letter-spacing"]) ? "letter-spacing: " . $thesis->api->css->number($this->options["$e-letter-spacing"]) . ";" : false;
			$prop[$e]['margin'] = $thesis->api->css->trbl('margin', array(
				'margin-top' => !empty($this->options["$e-margin-top"]) ? $this->options["$e-margin-top"] : '',
				'margin-right' => !empty($this->options["$e-margin-right"]) ? $this->options["$e-margin-right"] : '',
				'margin-bottom' => !empty($this->options["$e-margin-bottom"]) ? $this->options["$e-margin-bottom"] : '',
				'margin-left' => !empty($this->options["$e-margin-left"]) ? $this->options["$e-margin-left"] : ''));
			$prop[$e]['padding'] = $thesis->api->css->trbl('padding', array(
				'padding-top' => !empty($this->options["$e-padding-top"]) ? $this->options["$e-padding-top"] : '',
				'padding-right' => !empty($this->options["$e-padding-right"]) ? $this->options["$e-padding-right"] : '',
				'padding-bottom' => !empty($this->options["$e-padding-bottom"]) ? $this->options["$e-padding-bottom"] : '',
				'padding-left' => !empty($this->options["$e-padding-left"]) ? $this->options["$e-padding-left"] : ''));
			$css .= "$selector { " . implode(' ', array_filter($prop[$e])) . " }\n";
			if (!empty($e) && $e == 'text')
				$scale = $thesis->api->typography->scale($size[$e]);
		}
		$spacing = $thesis->api->typography->spacing($size['text'], $height['text'], apply_filters("{$this->_class}_unit", false));
		$list['style'] = !empty($this->options['list-style-type']) ?
			"$s ul { list-style-type: {$this->options['list-style-type']};" . (!empty($this->options['list-style-position']) ? " list-style-position: {$this->options['list-style-position']};" : '') . " }" : false;
		$list['indent'] = !empty($this->options['list-indent']['on']) ?
			"$s ul, $s ol { margin-left: {$spacing['single']}; }" : false;
		$list['item-margin'] = ($li = (!empty($this->options['list-item-margin']) ? $this->options['list-item-margin'] : '')) ?
			"$s li { margin-bottom: {$spacing[$li]}; }\n".
			"$s li ul, $s li ol { margin-top: {$spacing[$li]}; }" : false;
		$list = ($l = array_filter($list)) && !empty($l) ? implode("\n", array_filter($l)) : '';
		return trim($css.
			"$s p, $s ul { margin-bottom: {$spacing['single']}; }\n".
			"$s li ul { margin-bottom: 0; }\n".
			$list);
	}
}