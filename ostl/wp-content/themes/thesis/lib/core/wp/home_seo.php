<?php
/*---:[ Copyright DIYthemes, LLC. Patent pending. All rights reserved. DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC. ]:---*/
class thesis_home_seo {
	public $seo = array();	// (array) saved home page SEO info

	public function __construct() {
		global $thesis;
		$this->seo = is_array($seo = $thesis->api->get_option('thesis_home_seo')) ? $seo : $this->seo;
		if (!$thesis->environment) return;
		$this->title = sprintf(__('%1$s %2$s', 'thesis'), $thesis->api->strings['home_page'], $thesis->api->base['seo']);
		add_filter('thesis_site_menu', array($this, 'site_menu'), 11);
		if ($thesis->environment == 'admin' && (!empty($_GET['canvas']) && $_GET['canvas'] == 'home_seo')) {
			add_action('thesis_admin_canvas', array($this, 'canvas'));
			add_action('init', array($this, 'queue_script'));
		}
		if ($thesis->environment == 'ajax')
			add_action('wp_ajax_save_home_seo', array($this, 'save'));
	}
	
	public function queue_script() {
		global $thesis;
		wp_enqueue_style('thesis-objects', THESIS_CSS_URL . '/objects.css', array('thesis-admin'), $thesis->version);
		wp_enqueue_script('thesis-home-seo', THESIS_JS_URL . '/home_seo.js', array('thesis-menu'), $thesis->version);
	}

	public function site_menu($site) {
		global $thesis;
		$menu['home_seo'] = array(
			'text' => $this->title,
			'url' => admin_url('admin.php?page=thesis&canvas=home_seo'));
		return is_array($site) ? array_merge($site, $menu) : $menu;
	}

	private function options() {
		global $thesis;
		return array(
			'title' => array(
				'type' => 'text',
				'width' => 'full',
				'label' => $thesis->api->strings['title_tag'],
				'counter' => $thesis->api->strings['title_counter']),
			'description' => array(
				'type' => 'textarea',
				'rows' => 2,
				'label' => $thesis->api->strings['meta_description'],
				'counter' => $thesis->api->strings['description_counter']),
			'keywords' => array(
				'type' => 'text',
				'width' => 'full',
				'label' => $thesis->api->strings['meta_keywords']),
			'robots' => array(
				'type' => 'checkbox',
				'label' => $thesis->api->strings['meta_robots'],
				'options' => array(
					'noindex' => sprintf(__('<code>noindex</code> %1$s %2$s', 'thesis'), $thesis->api->strings['this_page'], $thesis->api->strings['not_recommended']),
					'nofollow' => sprintf(__('<code>nofollow</code> %1$s %2$s', 'thesis'), $thesis->api->strings['this_page'], $thesis->api->strings['not_recommended']),
					'noarchive' => sprintf(__('<code>noarchive</code> %1$s %2$s', 'thesis'), $thesis->api->strings['this_page'], $thesis->api->strings['not_recommended']))));
	}

	public function canvas() {
		global $thesis;
		$fields = $thesis->api->form->fields($this->options(), $this->seo, false, false, 10, 3);
		echo
			"\t\t<h3>$this->title</h3>\n".
			"\t\t<form id=\"t_home_seo\" method=\"post\" action=\"\">\n".
			$fields['output'].
			"\t\t\t" . wp_nonce_field('thesis-save-home-seo', '_wpnonce-thesis-ajax', true, false) . "\n".
			"\t\t\t<input type=\"submit\" data-style=\"button save\" class=\"t_save\" id=\"save_home_seo\" name=\"save_home_seo\" value=\"" . sprintf(__('%1$s %2$s', 'thesis'), $thesis->api->strings['save'], strip_tags($this->title)) . "\" />\n".
			"\t\t</form>\n";
	}

	public function save() {
		global $thesis;
		$thesis->wp->check('edit_theme_options');
		parse_str(stripslashes($_POST['form']), $form);
		$thesis->wp->nonce($form['_wpnonce-thesis-ajax'], 'thesis-save-home-seo');
		if (!is_array($form))
			echo $thesis->api->alert(sprintf(__('Home page %s not saved.', 'thesis'), $thesis->api->base['seo']), 'home_seo_saved', true);
		else {
			if ($save = $thesis->api->set_options($this->options(), $form))
				update_option('thesis_home_seo', $save);
			else
				delete_option('thesis_home_seo');
			echo $thesis->api->alert(sprintf(__('Home page %s saved!', 'thesis'), $thesis->api->base['seo']), 'home_seo_saved', true);
		}
		if ($thesis->environment == 'ajax') die();
	}
}