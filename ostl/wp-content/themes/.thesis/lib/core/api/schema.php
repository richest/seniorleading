<?php
/*
Copyright 2013 DIYthemes, LLC. Patent pending. All rights reserved.
DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC.
License: DIYthemes Software License Agreement
License URI: http://diythemes.com/thesis/rtfm/software-license-agreement/
*/
class thesis_schema {
	public $types = array(
		'article' => 'http://schema.org/Article',
		'creativework' => 'http://schema.org/CreativeWork',
		'recipe' => 'http://schema.org/Recipe',
		'review' => 'http://schema.org/Review');

	public function __construct() {
		add_action('init', array($this, 'init'), 12);
	}

	public function init() {
		$this->types = is_array(($types = apply_filters('thesis_schema_types', $this->types))) ? $types : $this->types;
		$this->options = is_array(($options = apply_filters('thesis_schema_options', array(
			'' => __('no schema', 'thesis'),
			'article' => __('Article', 'thesis'),
			'creativework' => __('CreativeWork', 'thesis'),
			'recipe' => __('Recipe', 'thesis'),
			'review' => __('Review', 'thesis'))))) ? $options : array();
	}

	public function select() {
		return array(
			'type' => 'select',
			'label' => __('Schema', 'thesis'),
			'tooltip' => sprintf(__('Enrich your pages by adding a <a href="%s" target="_blank">markup schema</a> that is universally recognized by search engines.', 'thesis'), 'http://schema.org/'),
			'options' => $this->options);
	}
}