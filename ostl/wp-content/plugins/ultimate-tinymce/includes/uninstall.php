<?php

if ( isset( $_POST['uninstall'], $_POST['uninstall_confirm'] ) ) {
    ultimate_tinymce_uninstall();
}

function ultimate_tinymce_uninstall() {
	
	delete_option('jwl_options_group','jwl_options_group'); // From prior plugin single array
	
	delete_option('jwl_options_group1','jwl_options_group1');
	delete_option('jwl_options_group2','jwl_options_group2');
	delete_option('jwl_options_group9','jwl_options_group9');
	delete_option('jwl_options_group3','jwl_options_group3');
	delete_option('jwl_options_group4','jwl_options_group4');
	delete_option('jwl_utmce_load_defaults', 'jwl_utmce_load_defaults');
 
    // Do not change (this deactivates the plugin)
    $current = get_option('active_plugins');
    array_splice($current, array_search( $_POST['plugin'], $current), 1 ); // Array-function!
    update_option('active_plugins', $current);
    header('Location: plugins.php?deactivate=true');
}

function jwl_ultimate_tinymce_form_uninstall() {
	?>
    <span class="content_wrapper_title"><?php _e('Uninstall Ultimate Tinymce','jwl-ultimate-tinymce'); ?></span><br /><br />
    <?php
    if ( isset( $_POST['uninstall'] ) && ! isset( $_POST['uninstall_confirm'] ) ) { 
	?><div id="message" class="error">
  			<p>
    		<?php _e('You must also check the confirm box before options will be uninstalled and deleted.','jwl-ultimate-tinymce'); ?>
  			</p>
		</div>
 	  <?php
    }
	?>
    <div style="width:100%;">
        <div style="width:60%;float:left;display:block;">
            <?php _e('The options for this plugin are not removed upon deactivation to ensure that no data is lost unintentionally. If you wish to remove all plugin information from your database be sure to run this uninstall utility first.<br /><br />This is a great way to "reset" the plugin, in case you experience problems with the editor. This option is NOT reversible. Ultimate Tinymce plugin settings will need to be re-configured if deleted.','jwl-ultimate-tinymce'); ?>
        </div>
        <div style="width:30%;float:left;display:block;margin-left:40px;">
            <form method="post">
            <input id="plugin" name="plugin" type="hidden" value="ultimate-tinymce/main.php" />
            <input name="uninstall_confirm" id="uninstall_confirm" type="checkbox" value="1" /><label for="uninstall_confirm"></label> <strong><?php _e('Please confirm before proceeding<br /><br />','jwl-ultimate-tinymce'); ?></strong>
            <input class="button-primary" name="uninstall" type="submit" value="<?php _e('Uninstall','jwl-ultimate-tinymce'); ?>" />
            </form>
        </div>
    </div>
    <div style="clear:both;"></div>
<?php
}
/* End Uninstalling Database Values */

// admin notice for not verifying to uninstall database
if ( isset( $_POST['uninstall'] ) && ! isset( $_POST['uninstall_confirm'] ) ) {
	function jwl_tinymce_top_uninstall_notice() {
		echo '<div id="message" class="error"><p>';
		_e('You must also check the confirm box before options will be uninstalled and deleted.','jwl-ultimate-tinymce');
    	echo '</p></div>';
	}
	add_action('admin_notices','jwl_tinymce_top_uninstall_notice');
}
?>