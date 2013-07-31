<?php
/*
Copyright 2012 DIYthemes, LLC. Patent pending. All rights reserved.
DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC.
License: DIYthemes Software License Agreement
License URI: http://diythemes.com/thesis/rtfm/software-license-agreement/
*/
class thesis_fonts {
	public function __construct() {
		add_action('init', array($this, 'init'), 11);
	}

	public function init() {
	 	$this->list = apply_filters('thesis_fonts', array(
			'arial' => array(
				'name' => 'Arial',
				'family' => 'Arial, "Helvetica Neue", Helvetica, sans-serif',
				'mu' => 2.26,
				'web_safe' => true),
			'arial_black' => array(
				'name' => 'Arial Black',
				'family' => '"Arial Black", "Arial Bold", Arial, sans-serif',
				'mu' => 1.82,
				'web_safe' => true),
			'arial_narrow' => array(
				'name' => 'Arial Narrow',
				'family' => '"Arial Narrow", Arial, "Helvetica Neue", Helvetica, sans-serif',
				'mu' => 2.76,
				'web_safe' => true),
			'courier_new' => array(
				'name' => 'Courier New',
				'family' => '"Courier New", Courier, Verdana, sans-serif',
				'mu' => 1.67,
				'web_safe' => true,
				'monospace' => true),
			'georgia' => array(
				'name' => 'Georgia',
				'family' => 'Georgia, "Times New Roman", Times, serif',
				'mu' => 2.27,
				'web_safe' => true),
			'times_new_roman' => array(
				'name' => 'Times New Roman',
				'family' => '"Times New Roman", Times, Georgia, serif',
				'mu' => 2.48,
				'web_safe' => true),
			'trebuchet_ms' => array(
				'name' => 'Trebuchet MS',
				'family' => '"Trebuchet MS", "Lucida Grande", "Lucida Sans Unicode", "Lucida Sans", Arial, sans-serif',
				'mu' => 2.2,
				'web_safe' => true),
			'verdana' => array(
				'name' => 'Verdana',
				'family' => 'Verdana, sans-serif',
				'mu' => 1.96,
				'web_safe' => true),
			'american_typewriter' => array(
				'name' => 'American Typewriter',
				'family' => '"American Typewriter", Georgia, serif',
				'mu' => 2.09),
			'andale' => array(
				'name' => 'Andale Mono',
				'family' => '"Andale Mono", Consolas, Monaco, Menlo, Courier, Verdana, sans-serif',
				'mu' => 1.67,
				'monospace' => true),
			'baskerville' => array(
				'name' => 'Baskerville',
				'family' => 'Baskerville, "Times New Roman", Times, serif',
				'mu' => 2.51),
			'calibri' => array(
				'name' => 'Calibri',
				'family' => 'Calibri, "Helvetica Neue", Helvetica, Arial, Verdana, sans-serif'),
			'cambria' => array(
				'name' => 'Cambria',
				'family' => 'Cambria, Georgia, "Times New Roman", Times, serif'),
			'candara' => array(
				'name' => 'Candara',
				'family' => 'Candara, Verdana, sans-serif'),
			'consolas' => array(
				'name' => 'Consolas',
				'family' => 'Consolas, Monaco, Menlo, Courier, Verdana, sans-serif',
				'monospace' => true),
			'constantia' => array(
				'name' => 'Constantia',
				'family' => 'Constantia, Georgia, "Times New Roman", Times, serif'),
			'corbel' => array(
				'name' => 'Corbel',
				'family' => 'Corbel, "Lucida Grande", "Lucida Sans Unicode", Arial, sans-serif'),
			'gill_sans' => array(
				'name' => 'Gill Sans',
				'family' => '"Gill Sans", "Gill Sans MT", Calibri, "Trebuchet MS", sans-serif',
				'mu' => 2.47),
			'helvetica' => array(
				'name' => 'Helvetica Neue',
				'family' => '"Helvetica Neue", Helvetica, Arial, sans-serif',
				'mu' => 2.24),
			'hoefler' => array(
				'name' => 'Hoefler Text',
				'family' => '"Hoefler Text", Garamond, "Times New Roman", Times, sans-serif',
				'mu' => 2.39),
			'lucida_grande' => array(
				'name' => 'Lucida Grande',
				'family' => '"Lucida Grande", "Lucida Sans", "Lucida Sans Unicode", sans-serif',
				'mu' => 2.05),
			'menlo' => array(
				'name' => 'Menlo',
				'family' => 'Menlo, "Andale Mono", Consolas, Monaco, Menlo, Courier, Verdana, sans-serif',
				'mu' => 1.66,
				'monospace' => true),
			'monaco' => array(
				'name' => 'Monaco',
				'family' => 'Monaco, Consolas, Menlo, Courier, Verdana, sans-serif',
				'mu' => 1.67,
				'monospace' => true),
			'palatino' => array(
				'name' => 'Palatino',
				'family' => '"Palatino Linotype", Palatino, Georgia, "Times New Roman", Times, serif',
				'mu' => 2.26),
			'tahoma' => array(
				'name' => 'Tahoma',
				'family' => 'Tahoma, Geneva, Verdana, sans-serif',
				'mu' => 2.25)));
		foreach ($this->list as $id => $font)
			$this->select[$id] = $font['name'];
	}

	public function family($font) {
		return !empty($font) && !empty($this->list) && !empty($this->list[$font]) ?
			$this->list[$font]['family'] : false;
	}
}