<?php
/*---:[ Copyright DIYthemes, LLC. Patent pending. All rights reserved. DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC. ]:---*/
class thesis_css_variables {
	public $items = array();		// (array) saved/active variables
	private $symbol = '$';			// (string) character symbol to use as variable delimiter
	private $css = array();			// (array) variable references and their associated values
	private $scrub = array();		// (array) variables yet to be scrubbed
	private $scrubbed = array();	// (array) scrubbed variables

	public function __construct($saved = false) {
		global $thesis;
		$this->items = is_array($saved) ? $saved : $this->items;
		if ($thesis->environment == 'ajax')
			add_action('wp_ajax_edit_variable', array($this, 'edit'));
	}

	private function options() {
		global $thesis;
		return array(
			'name' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => $thesis->api->strings['name'],
				'req' => '*'),
			'ref' => array(
				'type' => 'text',
				'width' => 'medium',
				'code' => true,
				'label' => $thesis->api->strings['reference'],
				'req' => '*'),
			'css' => array(
				'type' => 'textarea',
				'rows' => 3,
				'code' => true,
				'label' =>  sprintf(__('%s Value', 'thesis'), $thesis->api->strings['variable']),
				'tooltip' => sprintf(__('You can use variables to represent anything in your %1$s. Ideally, you will create variables for repeating items, such as colors, sizes, or even <code>property: value</code> pairs. Bonus: You can use variables within variables, too. Inception, you guys.', 'thesis'), $thesis->api->base['css'])));
	}

	public function items($depth) {
		global $thesis;
		$tab = str_repeat("\t", $depth);
		$list = '';
		foreach ($this->items as $id => $item)
			$list .=
				"$tab\t<li>".
				"<a class=\"t_edit_item\" href=\"\" data-type=\"var\" data-id=\"$id\" data-tooltip=\"" . $thesis->api->esc($item['css']) . '" title="'. esc_attr($thesis->api->strings['click_to_edit']) . '">' . $thesis->api->esch($item['name']) . " <code>$this->symbol" . $thesis->api->esch($item['ref']) . '</code></a>'.
				"</li>\n";
		return
			"$tab<ul class=\"t_item_list\">\n".
			$list.
			"$tab</ul>\n";
	}

	private function form($id) {
		global $thesis;
		if (!$id) return;
		$values = array(
			'name' => !empty($this->items[$id]['name']) ? $this->items[$id]['name'] : '',
			'ref' => !empty($this->items[$id]['ref']) ? $this->items[$id]['ref'] : '',
			'css' => !empty($this->items[$id]['css']) ? $this->items[$id]['css'] : '');
		$form = $thesis->api->form->fields($this->options(), $values, "t_var_", '', 500, 2);
		$title = sprintf(__('%1$s %2$s', 'thesis'), $thesis->api->strings['edit'], $thesis->api->strings['variable']);
		return
			"<form id=\"t_var_form\" class=\"t_popup_form\" action=\"\" method=\"post\">\n".
			"\t<div class=\"t_popup_head\" data-style=\"box\">\n".
			"\t\t<button class=\"cancel_options\" data-style=\"button\">{$thesis->api->strings['cancel']}</button>\n".
			"\t\t<button class=\"save_options\" data-style=\"button save\">{$thesis->api->strings['save']}</button>\n".
			"\t\t<h4>$title</h4>\n".
			"\t</div>\n".
			"\t<div class=\"t_popup_body\">\n".
			$form['output'].
			"\t\t<div class=\"options_controls\">\n".
			"\t\t\t<button class=\"delete_options\" data-style=\"button delete\">{$thesis->api->strings['delete']}</button>\n".
			"\t\t</div>\n".
			"\t</div>\n".
			"\t<input type=\"hidden\" id=\"t_var_id\" name=\"id\" value=\"$id\" />\n".
			"\t<input type=\"hidden\" id=\"t_var_symbol\" name=\"symbol\" value=\"$this->symbol\" />\n".
			"\t" . wp_nonce_field('thesis-save-css-variable', '_wpnonce-thesis-save-css-variable', true, false) . "\n".
			"</form>\n";
	}

	public function edit() {
		global $thesis;
		$thesis->wp->nonce($_POST['nonce'], 'thesis-save-css');
		$item = $_POST['item'];
		if (empty($item)) return;
		$id = $item['id'] == 'new' ? 'var_' . time() : $item['id'];
		echo $this->form($id);
		if ($thesis->environment == 'ajax') die();
	}

	public function save($item) {
		if (!is_array($item) || (!$item['id'] || !$item['name'] || !$item['ref'])) return false;
		$this->items[$item['id']] = array(
			'name' => $item['name'],
			'ref' => $item['ref'],
			'css' => $item['css']);
		return $this->items;
	}

	public function delete($item) {
		if (!is_array($item) || (!$item['id'] || !$item['name'] || !$item['ref'])) return false;
		unset($this->items[$item['id']]);
		return $this->items;
	}

	private function scrub($to_scrub) {
		if (!(is_array($to_scrub) && !empty($to_scrub))) return;
		foreach ($to_scrub as $ref => $css) {
			if (strpos($css, $this->symbol) !== false)
				$this->scrub[$ref] = preg_replace_callback("/\\{$this->symbol}" . '[A-Za-z0-9-_]+' . '((?=[\\$\s\r\n;}])|\Z)/i', array($this, 'replace'), $css);
			else {
				if (isset($this->scrub[$ref]))
					unset($this->scrub[$ref]);
				$this->scrubbed[$ref] = $css;
			}
		}
		if (!empty($this->scrub))
			$this->scrub($this->scrub);
	}

	public function replace($matches) {
		return is_array($matches) && !empty($matches[0]) && ($ref = trim($matches[0], $this->symbol)) ? (array_key_exists($ref, $this->scrubbed) ?
			$this->scrubbed[$ref] : (array_key_exists($ref, $this->css) ?
			$this->css[$ref] : '')) : '';
	}

	public function css($css) {
		foreach ($this->items as $id => $var)
			$this->css[$var['ref']] = $this->scrub[$var['ref']] = stripslashes($var['css']);
		$this->scrub($this->scrub);
		foreach ($this->scrubbed as $ref => $var_css)
			$css = preg_replace("/\\{$this->symbol}$ref" . '((?=[\s\r\n;}])|\Z)/i', $var_css, $css);
		return $css;
	}
}