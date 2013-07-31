<?php
/*---:[ Copyright DIYthemes, LLC. Patent pending. All rights reserved. DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC. ]:---*/
final class thesis_skin_manager {
	public $table_suffix = 'thesis_backups';
	public $table;
	public $class;
	public $options = array('boxes', 'templates', 'packages', 'vars', 'css', 'css_custom');

	public function __construct($skin = array()) {
		global $wpdb, $thesis;
		if (!$thesis->wp_customize && ($thesis->environment === false || !is_array($skin))) return;	// allow in all environments except the front end
		extract($skin); // name, author, description, version, class, folder
		$this->class = trim($thesis->api->verify_class_name($class));	// set class…
		$this->name = isset($name) ? $name : false;
		if (!get_option("{$this->class}_boxes"))
			$this->defaults();
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
					'packages' => sprintf(__('%s Packages', 'thesis'), $thesis->api->base['css']),
					'vars' => sprintf(__('%s Variables', 'thesis'), $thesis->api->base['css']),
					'css' => sprintf(__('Skin %s', 'thesis'), $thesis->api->base['css']),
					'css_custom' => sprintf(__('Custom %s', 'thesis'), $thesis->api->base['css'])),
				'default' => array(
					'boxes' => true,
					'templates' => true,
					'packages' => true,
					'vars' => true,
					'css' => true,
					'css_custom' => true))), array(), 'thesis_export_', '', 900, 6);
		$args = array(
			'title' => __('Thesis Upload Box', 'thesis'),
			'prefix' => 'thesis_box_uploader',
			'file_type' => 'zip',
			'folder' => 'box');
		return
			"$tab<h3 id=\"t_manager_head\"><span>" . __('Manage Skin:', 'thesis') . " $this->name</span></h3>\n".
			"$tab<div class=\"t_manager_box\" data-style=\"box\">\n".
			"$tab\t<h4>" . __('Backup Skin Data', 'thesis') . "</h4>\n".
			"$tab\t<p>" . __('Create a Skin backup that you can restore at any time.', 'thesis') . "</p>\n".
			"$tab\t<button id=\"t_backup\" data-style=\"button save\">" . __('Create New Backup', 'thesis') . "</button>\n".
			"$tab</div>\n".
			"$tab<div class=\"t_manager_box\" data-style=\"box\">\n".
			"$tab\t<h4>" . __('Import Skin Data', 'thesis') . "</h4>\n".
			"$tab\t<p>" . __('Import Skin data from a Thesis Skin export file.', 'thesis') . "</p>\n".
			"$tab\t<button id=\"t_import\" data-style=\"button action\">" . __('Import Skin Data', 'thesis') . "</button>\n".
			"$tab</div>\n".
			"$tab<div class=\"t_manager_box t_manager_default\" data-style=\"box\">\n".
			"$tab\t<h4>" . __('Restore Default Data', 'thesis') . "</h4>\n".
			"$tab\t<p>" . sprintf(__('Restore default data for the %s Skin.', 'thesis'), $this->name) . "</p>\n".
			"$tab\t<button id=\"t_restore_default\" data-style=\"button delete\">" . __('Restore Default', 'thesis') . "</button>\n".
			"$tab</div>\n".
			"$tab<div id=\"t_restore\">\n".
			"$tab\t<h3 id=\"t_restore_head\"><span>$this->name " . __('Backups', 'thesis') . "</span></h3>\n".
			"$tab\t<div id=\"t_restore_table\">\n".
			$this->backup_table().
			"$tab\t</div>\n".
			"$tab</div>\n".
			$thesis->api->popup(array(
				'id' => 'export_skin',
				'title' => sprintf(__('Export %s Data', 'thesis'), $this->name),
				'depth' => $depth,
				'body' =>
					"$tab\t\t\t<form id=\"t_export_form\" method=\"post\" action=\"" . (admin_url('admin-post.php?action=export_skin')) . "\">\n".
					$export['output'].
					"$tab\t\t\t\t<input type=\"hidden\" id=\"t_export_id\" name=\"export[id]\" value=\"\" />\n".
					"$tab\t\t\t\t<button id=\"t_export\" data-style=\"button action\">" . __('Export Skin', 'thesis') . "</button>\n".
					"$tab\t\t\t\t" . wp_nonce_field('thesis-skin-export', '_wpnonce-thesis-skin-export', true, false) . "\n".
					"$tab\t\t\t</form>\n")).
			$thesis->api->popup(array(
				'id' => 'import_skin',
				'title' => sprintf(__('Import %s Data', 'thesis'), $this->name),
				'depth' => $depth,
				'body' => $thesis->api->uploader('import_skin'))).
			"$tab" . wp_nonce_field('thesis-skin-manager', '_wpnonce-thesis-skin-manager', true, false) . "\n";
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
				"\t\t\t\t\t\t<td><button class=\"t_restore_backup\" data-style=\"button save\" data-id=\"$id\">" . __('Restore', 'thesis') . "</button></td>\n".
				"\t\t\t\t\t\t<td><button class=\"t_export_backup\" data-style=\"button action\" data-id=\"$id\">" . __('Export', 'thesis') . "</button></td>\n".
				"\t\t\t\t\t\t<td><button class=\"t_delete_backup\" data-style=\"button delete\" data-id=\"$id\">" . __('Delete', 'thesis') . "</button></td>\n".
				"\t\t\t\t\t</tr>\n";
		}
		return
			"\t\t\t<table>\n".
			"\t\t\t\t<thead>\n".
			"\t\t\t\t\t<tr>\n".
			"\t\t\t\t\t\t<th>" . __('Backup Date', 'thesis') . "</th>\n".
			"\t\t\t\t\t\t<th class=\"t_backup_notes\">" . __('Notes', 'thesis') . "</th>\n".
			"\t\t\t\t\t\t<th>" . __('Restore', 'thesis') . "</th>\n".
			"\t\t\t\t\t\t<th>" . __('Export', 'thesis') . "</th>\n".
			"\t\t\t\t\t\t<th>" . __('Delete', 'thesis') . "</th>\n".
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

	public function defaults() {
		global $thesis;
		$directory = file_exists(THESIS_USER_SKIN) ? THESIS_USER_SKIN : ($thesis->wp_customize === true && !file_exists(THESIS_USER_SKIN) ? THESIS_SKINS : false);
		if ($this->class === 'thesis_blank')
			foreach ($this->options as $option)
				delete_option('thesis_blank_' . $option);
		elseif (file_exists($directory . '/seed.php')) {
			include_once($directory . '/seed.php');
			if (function_exists($this->class . '_defaults'))
				call_user_func($this->class . '_defaults');
			else return false;
		}
		else
			return false;
		wp_cache_flush();
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

	public function export($options = array(), $seed = false) {				// assumed to be ALL if left empty. send $options['id'] to target a specific id
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
			if (empty($options))												// if nothing, we're sending everything
				$options = $this->options;
			$options = array_intersect($options, $this->options);				// check to see what was requested
			if(!($data = $this->get_entry($id)) || $data['class'] !== $this->class)
				return false;
			unset($data['ID']);
			unset($data['notes']);
			unset($data['time']);
			$data = array_filter($data);										// get rid of empty entries
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
		header('Content-Type: text/plain; charset=' . get_option('blog_charset'));
		header('Content-Disposition: attachment; filename="'. str_replace('_', '-', $this->class) .'-'. @date('Y\-m\-d\-H\-i') .'.txt"');
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
		if (!empty($exists))
			return true;
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
				PRIMARY KEY (ID)
			) COLLATE utf8_general_ci;";				// force utf8 collation to avoid latin1: destroyer of worlds
			$query = $wpdb->query($sql);
			return (bool) $query;
		}
	}
}