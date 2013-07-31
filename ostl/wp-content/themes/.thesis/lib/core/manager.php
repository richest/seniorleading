<?php
/*
Copyright 2012 DIYthemes, LLC. Patent pending. All rights reserved.
DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC.
License: DIYthemes Software License Agreement
License URI: http://diythemes.com/thesis/rtfm/software-license-agreement/
*/
final class thesis_skin_manager {
	public $table_suffix = 'thesis_backups';
	public $table;
	public $class;
	public $options = array('boxes', 'templates', 'packages', 'vars', 'css', 'css_custom', '_design', '_display');

	public function __construct($skin = array()) {
		global $wpdb, $thesis;
		if (empty($skin) || !$thesis->wp_customize && ($thesis->environment === false || !is_array($skin))) return;	// allow in all environments except the front end
		extract($skin); // name, author, description, version, class, folder
		$this->class = trim($thesis->api->verify_class_name($class));	// set class…
		$this->name = isset($name) ? $name : false;
		if (!get_option("{$this->class}_templates"))
			$this->defaults(array(), true);
		$this->table = $wpdb->prefix . $this->table_suffix;				// set the table name
		if (!$this->table())											// check if table exists
			return false;
	}

	public function editor() {
		global $thesis;
		$tab = str_repeat("\t", $depth = 2);
		$export = $thesis->api->form->fields(array(
			'export' => array(
				'type' => 'checkbox',
				'label' => __('Export the Following Skin Data:', 'thesis'),
				'tooltip' => __('Share your masterpiece, move your design, or get help from an expert by exporting your Skin in whole or in part. Choose the options you want to share, and Thesis will create a handy export file for you.', 'thesis'),
				'options' => array(
					'boxes' => __('Boxes', 'thesis'),
					'templates' => __('Templates', 'thesis'),
					'vars' => sprintf(__('%s Variables', 'thesis'), $thesis->api->base['css']),
					'css' => sprintf(__('Skin %s', 'thesis'), $thesis->api->base['css']),
					'css_custom' => sprintf(__('Custom %s', 'thesis'), $thesis->api->base['css']),
					'_design' => __('Design Options', 'thesis'),
					'_display' => __('Display Options', 'thesis'),
					'packages' => sprintf(__('%s Packages (deprecated)', 'thesis'), $thesis->api->base['css'])),
				'default' => array(
					'boxes' => true,
					'templates' => true,
					'packages' => true,
					'vars' => true,
					'css' => true,
					'css_custom' => true,
					'_design' => true,
					'_display' => true))), array(), 'thesis_export_', '', 900, 6);
		$default = $thesis->api->form->fields(array(
			'restore' => array(
				'type' => 'checkbox',
				'label' => __('Restore Default Settings:', 'thesis'),
				'tooltip' => __('Thesis allows you to restore individual parts of your Skin or the whole shebang.', 'thesis'),
				'options' => array(
					'boxes' => __('Boxes', 'thesis'),
					'templates' => __('Templates', 'thesis'),
					'vars' => sprintf(__('%s Variables', 'thesis'), $thesis->api->base['css']),
					'css' => sprintf(__('Skin %s', 'thesis'), $thesis->api->base['css']),
					'css_custom' => sprintf(__('Custom %1$s (this will delete your custom %1$s)', 'thesis'), $thesis->api->base['css']),
					'_design' => __('Design Options', 'thesis'),
					'_display' => __('Display Options', 'thesis'),
					'packages' => sprintf(__('%s Packages (deprecated)', 'thesis'), $thesis->api->base['css'])),
				'default' => array(
					'boxes' => true,
					'templates' => true,
					'packages' => true,
					'vars' => true,
					'css' => true,
					'css_custom' => false,
					'_design' => true,
					'_display' => true))), array(), 'thesis_export_', '', 900, 6);
		return
			"$tab<h3 id=\"t_manager_head\"><span>". sprintf(__('Manage %s Skin', 'thesis'), $this->name). "</span></h3>\n".
			"$tab<div class=\"t_manager_box\" data-style=\"box\">\n".
			"$tab\t<h4>". __('Backup Skin Data', 'thesis'). "</h4>\n".
			"$tab\t<p>". __('Create a Skin backup that you can restore at any time.', 'thesis'). "</p>\n".
			"$tab\t<button id=\"t_backup\" data-style=\"button save\">". __('Create New Backup', 'thesis'). "</button>\n".
			"$tab</div>\n".
			"$tab<div class=\"t_manager_box\" data-style=\"box\">\n".
			"$tab\t<h4>". __('Import Skin Data', 'thesis'). "</h4>\n".
			"$tab\t<p>". __('Import Skin data from a Thesis Skin export file.', 'thesis'). "</p>\n".
			"$tab\t<button id=\"t_import\" data-style=\"button action\">". __('Import Skin Data', 'thesis'). "</button>\n".
			"$tab</div>\n".
			"$tab<div class=\"t_manager_box t_manager_default\" data-style=\"box\">\n".
			"$tab\t<h4>". __('Restore Default Data', 'thesis'). "</h4>\n".
			"$tab\t<p>". sprintf(__('Restore default data for the %s Skin.', 'thesis'), $this->name). "</p>\n".
			"$tab\t<button id=\"t_select_defaults\" data-style=\"button action\">". __('Restore Defaults', 'thesis'). "</button>\n".
			"$tab</div>\n".
			"$tab<div id=\"t_restore\">\n".
			"$tab\t<h3 id=\"t_restore_head\"><span>". sprintf(__('%s Skin Backups', 'thesis'), $this->name). "</span></h3>\n".
			"$tab\t<div id=\"t_restore_table\">\n".
			$this->backup_table().
			"$tab\t</div>\n".
			"$tab</div>\n".
			$thesis->api->popup(array(
				'id' => 'export_skin',
				'title' => sprintf(__('Export %s Data', 'thesis'), $this->name),
				'depth' => $depth,
				'body' =>
					"$tab\t\t\t<form id=\"t_export_form\" method=\"post\" action=\"". (admin_url('admin-post.php?action=export_skin')). "\">\n".
					$export['output'].
					"$tab\t\t\t\t<input type=\"hidden\" id=\"t_export_id\" name=\"export[id]\" value=\"\" />\n".
					"$tab\t\t\t\t<button id=\"t_export\" data-style=\"button action\">". __('Export Skin', 'thesis'). "</button>\n".
					"$tab\t\t\t\t". wp_nonce_field('thesis-skin-export', '_wpnonce-thesis-skin-export', true, false). "\n".
					"$tab\t\t\t</form>\n")).
			$thesis->api->popup(array(
				'id' => 'import_skin',
				'title' => sprintf(__('Import %s Data', 'thesis'), $this->name),
				'depth' => $depth,
				'body' => $thesis->api->uploader('import_skin'))).
			$thesis->api->popup(array(
				'id' => 'skin_default',
				'title' => sprintf(__('Restore %s Skin Defaults', 'thesis'), $this->name),
				'depth' => $depth,
				'body' => 
					"$tab\t\t\t<form id=\"t_default_form\" method=\"post\" action=\"\">\n".
					(file_exists(THESIS_USER_SKIN. '/default.php') ?
					$default['output'].
					"$tab\t\t\t\t<button id=\"t_restore_default\" data-style=\"button save\">". __('Restore Selected Defaults', 'thesis'). "</button>\n" :
					"$tab\t\t\t\t<p>". __('Your skin does not have the ability to restore individual data components. Please click the button below to restore <strong>all</strong> default settings.', 'thesis'). "</p>\n".
					"$tab\t\t\t\t<button id=\"t_restore_default\" data-style=\"button save\">". __('Restore Defaults', 'thesis'). "</button>\n").
					"$tab\t\t\t\t". wp_nonce_field('thesis-restore-defaults', '_wpnonce-thesis-restore-defaults', true, false). "\n".
					"$tab\t\t\t</form>\n")).
			"$tab". wp_nonce_field('thesis-skin-manager', '_wpnonce-thesis-skin-manager', true, false). "\n";
	}

	public function backup_table() {
		global $thesis;
		$backups = '';
		foreach ((is_array($points = $this->get()) ? $points : array()) as $id => $backup) {
			$td = '';
			if (is_array($backup))
				foreach ($backup as $prop => $val) {
					$class = $prop == 'notes' ? ' class="t_backup_notes"' : '';
					$value = $prop == 'time' ? date('M j, Y [H:i]', $val) : ($prop == 'notes' ? trim($thesis->api->escht($val, true)) : false);
					$td .= "\t\t\t\t\t\t<td$class>$value</td>\n";
				}
			$backups .=
				"\t\t\t\t\t<tr>\n".
				$td.
				"\t\t\t\t\t\t<td><button class=\"t_restore_backup\" data-style=\"button save\" data-id=\"$id\">". __('Restore', 'thesis'). "</button></td>\n".
				"\t\t\t\t\t\t<td><button class=\"t_export_backup\" data-style=\"button action\" data-id=\"$id\">". __('Export', 'thesis'). "</button></td>\n".
				"\t\t\t\t\t\t<td><button class=\"t_delete_backup\" data-style=\"button delete\" data-id=\"$id\">". __('Delete', 'thesis'). "</button></td>\n".
				"\t\t\t\t\t</tr>\n";
		}
		return
			"\t\t\t<table>\n".
			"\t\t\t\t<thead>\n".
			"\t\t\t\t\t<tr>\n".
			"\t\t\t\t\t\t<th>". __('Backup Date', 'thesis'). "</th>\n".
			"\t\t\t\t\t\t<th class=\"t_backup_notes\">". __('Notes', 'thesis'). "</th>\n".
			"\t\t\t\t\t\t<th>". __('Restore', 'thesis'). "</th>\n".
			"\t\t\t\t\t\t<th>". __('Export', 'thesis'). "</th>\n".
			"\t\t\t\t\t\t<th>". __('Delete', 'thesis'). "</th>\n".
			"\t\t\t\t\t</tr>\n".
			"\t\t\t\t</thead>\n".
			"\t\t\t\t<tbody>\n".
			$backups.
			"\t\t\t\t</tbody>\n".
			"\t\t\t</table>\n";
	}

	public function add($notes = false) {
		global $wpdb;
		$data = array(); 												// start
		wp_cache_flush(); 												// make sure we have the latest by flushing the cache first
		foreach ($this->options as $option)
			$data[$option] = get_option("{$this->class}_{$option}");	// fetch options
		$data = array_filter($data); 									// filter out empty options
		if (empty($data))
			return true;												// there are no options, so we don't need to save anything.
		if (!empty($notes)) 											// if we got to here, add notes, only if they're present
			$data['notes'] = sanitize_text_field($notes);
		$data = array_map('maybe_serialize', $data); 					// returns an array of serialized data
		$data['time'] = time(); 										// add timestamp
		$data['class'] = esc_attr($this->class);						// add skin class
		return (bool) $wpdb->insert($this->table, $data); 				// return true on success, false on failure
	}

	public function defaults($form = array(), $new = false) {
		global $thesis;
		$restore = !empty($form['restore']) ? array_filter(array_map('intval', $form['restore'])) : ($new ? array_combine($this->options, array_fill(0, count($this->options), 1)) : array());
		$directory = defined('THESIS_USER_SKIN') && file_exists(THESIS_USER_SKIN) ? THESIS_USER_SKIN : ($thesis->wp_customize === true && !file_exists(THESIS_USER_SKIN) ? THESIS_SKINS : false);
		if (isset($restore['css_custom'])) {
			unset($restore['css_custom']);
			delete_option("{$thesis->skins->skin['class']}_css_custom");
		}
		$this->add(__('[Automatic backup: Restore defaults]', 'thesis'));	// create a backup in case they forgot to
		if ($this->class === 'thesis_blank')
			foreach ($this->options as $option)
				delete_option('thesis_blank_'. $option);
		elseif (file_exists($directory. '/default.php')) {
			include_once($directory. '/default.php');
			if (function_exists($this->class. '_defaults')) {
				// nuke the design and display options. these should never be present in a default.php file
				delete_option("{$this->class}__design");
				delete_option("{$this->class}__display");
				$default_data = call_user_func($this->class. '_defaults');
				foreach (array_keys($restore) as $option) {
					if (isset($default_data[$option]))
						update_option("{$this->class}_$option", $default_data[$option]);
					else
						delete_option("{$this->class}_$option");
				}
				wp_cache_flush();
				if (isset($default_data['vars']) && property_exists($thesis->skin, '_css'))
					$thesis->skin->_css->restore_vars($default_data['vars']);
			}
			else return false;
		}
		elseif (file_exists($directory. '/seed.php')) {
			// old style. nukes everything.
			include_once($directory. '/seed.php');
			if (function_exists($this->class. '_defaults')) {
				call_user_func($this->class. '_defaults');
				wp_cache_flush();
				$thesis->skin->_css->restore_vars(get_option("{$this->class}_vars"));
			}
			else return false;
		}
		else return false;
		if (property_exists($thesis, 'skin'))
			$thesis->skin->_write_css();
		return true;
	}

	public function delete($id = false) {
		global $wpdb;
		if ($id === false || !is_integer($id) || !($check = $this->get_entry(abs($id))))	// make sure we're being passed an id and that the class was set up
			return false;
		$where = array(																		// if we're here, we found something. let's delete it.
			'class' => esc_attr($this->class),
			'ID' => absint($id));
		return (bool) $wpdb->delete($this->table, $where);
	}

	public function export($options = array(), $seed = false) {					// assumed to be ALL if left empty. send $options['id'] to target a specific id
		global $wpdb;
		
		if ($options === false and $seed === true) {
			$new = array();
			foreach ($this->options as $option)
				$new[$option] = get_option($this->class . '_' . $option);
			$new = array_filter($new);
			$new['class'] = $this->class;
			return $new;
		}
		else {
			if (empty($options['id']))
				return false;
			$id = (int) $options['id'];
			unset($options['id']);
			$options = array_keys($options);
			if (empty($options))											// if nothing, we're sending everything
				$options = $this->options;
			$options = array_intersect($options, $this->options);			// check to see what was requested
			if(!($data = $this->get_entry($id)) || $data['class'] !== $this->class)
				return false;
			unset($data['ID']);
			unset($data['notes']);
			unset($data['time']);
			$data = array_filter($data);									// get rid of empty entries
			$new = array();
			foreach ($options as $option)
				if (isset($data[$option]))
					$new[$option] = maybe_unserialize($data[$option]);
			$new['class'] = $data['class'];
		}
		if (empty($new) || !($serialized = serialize($new)))				// serialize the whole shebang
			return false;													// this means serialize failed or there are no options
		$md5 = md5($serialized);											// get hash of data
		$hash_added = array('data' => $new, 'checksum' => $md5);			// add hash
		if (!($out = serialize($hash_added)))								// serialize it all
			return false;
		header('Content-Type: text/plain; charset='. get_option('blog_charset'));
		header('Content-Disposition: attachment; filename="'. str_replace('_', '-', $this->class). '-'. @date('Y\-m\-d\-H\-i'). '.txt"');
		printf('%s', $out);
		exit;
	}

	public function get() {
		global $wpdb;
		$sql = $wpdb->prepare("SELECT ID,time,notes FROM {$this->table} WHERE class = %s", $this->class);
		if (!($results = $wpdb->get_results($sql, ARRAY_A)))
			return false;
		if (is_object($results)) // not sure why we'd get an object, but I'll make sure it isn't one
			$results = array($results);
		$valid = array();
		foreach ($results as $result)
			if (is_array($result) && !empty($result['ID']) && !empty($result['time'])) {
				$valid[absint(maybe_unserialize($result['ID']))] = array(
					'time' => absint(maybe_unserialize($result['time'])),
					'notes' => !empty($result['notes']) ? sanitize_text_field(wp_specialchars_decode(maybe_unserialize(stripslashes($result['notes'])))) : false);
			}
		krsort($valid);
		return empty($valid) ? array() : $valid;
	}

	public function get_entry($id = false) {
		global $wpdb;
		if (!is_object($this) || !is_integer($id) || empty($this->class))
			return false;
		$result = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table} WHERE ID = %d", absint($id)), ARRAY_A);
		return !empty($result['class']) && $result['class'] === $this->class ? $result : false;
	}

	public function import($location = false) {
		global $thesis;
		if (empty($_FILES[$location]) || $_FILES[$location]['error'] > 0 || !($unserialize = $thesis->api->verify_data_file($_FILES[$location], $this->class)))
			return false;
		$data = array();
		foreach ($unserialize as $option => $value)
			if ($option != 'class')
				update_option($this->class . "_" . $option, $value);
		wp_cache_flush();
		return true;
	}

	public function restore($id = false) {
		global $wpdb;
		if (empty($id) || !is_integer($id))
			return false;
		if (!($result = $this->get_entry(absint($id))) || empty($result['class']))
			return null; 			// null so that we know the row wasn't found
		unset($result['ID']);		// do…
		unset($result['time']);		// …not…
		unset($result['class']);	// …need…
		unset($result['notes']);	// …these.
		$verified = array();
		$need = array_filter($result);		
		if (!empty($need) && is_array($need))
			foreach ($need as $key => $check) 			// run through and unserialize everything to make sure we don't have a screw up
				if (in_array($key, $this->options) && ($save = maybe_unserialize($check)))
					$verified[$key] = $save;					
		if ($check = array_diff_key($need, $verified)) 	// something happened, likely in unserialization. do not restore from broken deal.
			return array_keys($check);		
		foreach ($verified as $what => $data) 			// everything is money, so update the options
			update_option("{$this->class}_$what", $data);
		wp_cache_flush();
		return true;
	}

	private function table() {
		global $wpdb;
		$exists = $wpdb->get_var("SHOW TABLES LIKE '{$this->table}'");
		$return = true;
		if ($exists && ! (bool) $wpdb->query("SHOW COLUMNS FROM {$this->table} LIKE '_design'")) {
			$return = (bool) $wpdb->query("ALTER TABLE {$this->table} ADD _design longtext NOT NULL");
		}
		if ($exists && ! (bool) $wpdb->query("SHOW COLUMNS FROM {$this->table} LIKE '_display'")) {
			$return = (bool) $wpdb->query("ALTER TABLE {$this->table} ADD _display longtext NOT NULL");
		}
		if (!empty($exists))
			return $return && true;
		else {											// make the table
			$sql = "CREATE TABLE {$this->table} (
				ID bigint(20) unsigned NOT NULL auto_increment,
				time bigint(20) NOT NULL,
				class varchar(200) NOT NULL,
				boxes longtext NOT NULL,
				templates longtext NOT NULL,
				packages longtext NOT NULL,
				vars longtext NOT NULL,
				css longtext NOT NULL,
				css_custom longtext NOT NULL,
				notes longtext NOT NULL,
				_design longtext NOT NULL,
				_display longtext NOT NULL,
				PRIMARY KEY (ID)
			) COLLATE utf8_general_ci;";				// force utf8 collation to avoid latin1: destroyer of worlds
			$query = $wpdb->query($sql);
			return (bool) $query && $return;
		}
	}
}