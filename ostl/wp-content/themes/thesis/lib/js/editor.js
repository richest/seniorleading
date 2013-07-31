/*---:[ Copyright DIYthemes, LLC. Patent pending. All rights reserved. DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC. ]:---*/
var thesis_editor;
(function($) {
thesis_editor = {
	init: function() {
		thesis_editor.get_canvas(thesis_canvas.url);
		thesis_editor.pane($('.t_pane_switch:first').addClass('t_on'));
		$('.t_pane_switch').click(function() {
			$('.t_pane_switch').removeClass('t_on');
			$(this).addClass('t_on');
			thesis_editor.pane(this);
		});
		$('#t_backup').click(function() {
			var note = prompt('Add a note to this backup so you can reference it later.');
			if (note != null)
				thesis_editor.backup(note);
		});
		thesis_editor.init_backups();
		$('#t_import').click(function() { thesis_ui.popup('#popup_import_skin'); });
		$('#t_import_submit').click(function() {
			return confirm("Are you sure you want to do this? If you import Skin data, you will lose the current state of your Skin unless you make a backup first.\n\nHit cancel to return to the manager and make a backup, or hit OK, and this window will refresh with your imported Skin data!") ? true : false;
		});
		$('#t_restore_default').click(function() {
			if (confirm("Are you sure you want to do this? If you restore default Skin data, you will lose the current state of your Skin unless you make a backup first.\n\nHit cancel to return to the manager and make a backup, or hit OK, and this window will refresh with the default Skin data."))
				thesis_editor.default();
		});
	},
	pane: function(pane_switch) {
		$('.t_pane').hide();
		$('#t_' + $(pane_switch).attr('data-pane')).show();
	},
	get_canvas: function(url) {
		thesis_editor.canvas = window.open(url, thesis_canvas.name);
	},
	init_backups: function() {
		$('.t_restore_backup').on('click', function() {
			if (confirm("Are you sure you want to do this? If you restore from a backup, you will lose the current state of your Skin unless you make a backup first.\n\nHit cancel to return to the manager and make a backup, or hit OK, and this window will refresh with your restored Skin."))
				thesis_editor.restore($(this).attr('data-id'));
		});
		$('.t_export_backup').on('click', function() {
			$('#t_export_id').val($(this).attr('data-id'));
			thesis_ui.popup('#popup_export_skin');
		});
		$('.t_delete_backup').on('click', function() {
			if (confirm('Are you sure you want to do this? Once you delete a backup, it cannot be recovered.'))
				thesis_editor.delete($(this).attr('data-id'));
		});
	},
	backup: function(note) {
		var nonce = $('#_wpnonce-thesis-skin-manager').val();
		$('#t_backup').prop('disabled', true);
		$.post(thesis_ajax.url, { action: 'backup_skin', note: note, nonce: nonce }, function(saved) {
			$('#t_backup').prop('disabled', false);
			if (saved) {
				$('#t_manager').prepend(saved);
				$('#manager_saved').css({'left': $('#t_manager_head span').outerWidth()+12+'px'});
				$('#manager_saved').fadeOut(3000, function() { $(this).remove(); });
				$.post(thesis_ajax.url, { action: 'update_backup_skin_table', nonce: nonce }, function(html) {
					$('#t_restore_table').html(html);
					thesis_editor.init_backups();
				});
			}
		});
	},
	restore: function(id) {
		var nonce = $('#_wpnonce-thesis-skin-manager').val();
		$.post(thesis_ajax.url, { action: 'restore_skin_backup', id: id, nonce: nonce }, function() {
			if (thesis_editor.canvas.window != null)
				thesis_editor.canvas.close();
			window.location.reload();
		});
	},
	delete: function(id) {
		var nonce = $('#_wpnonce-thesis-skin-manager').val();
		$.post(thesis_ajax.url, { action: 'delete_skin_backup', id: id, nonce: nonce }, function(deleted) {
			if (deleted) {
				$('#t_restore').prepend(deleted);
				$('#manager_saved').css({'left': $('#t_restore_head span').outerWidth()+12+'px'});
				$('#manager_saved').fadeOut(3000, function() { $(this).remove(); });
				$.post(thesis_ajax.url, { action: 'update_backup_skin_table', nonce: nonce }, function(html) {
					$('#t_restore_table').html(html);
					thesis_editor.init_backups();
				});
			}
		});
	},
	default: function() {
		var nonce = $('#_wpnonce-thesis-skin-manager').val();
		$('#t_restore_default').prop('disabled', true);
		$.post(thesis_ajax.url, { action: 'restore_skin_default', nonce: nonce }, function(response) {
			$('#t_restore_default').prop('disabled', false);
			if (response == 'true') {
				if (thesis_editor.canvas.window != null)
					thesis_editor.canvas.close();
				window.location.reload();
			}
			else {
				$('#t_manager').prepend(response);
				$('#manager_saved').css({'left': $('#t_manager_head span').outerWidth()+12+'px'});
				$('#manager_saved').fadeOut(3000, function() { $(this).remove(); });
			}
		});
	}
};
$(document).ready(function($){ thesis_editor.init(); });
})(jQuery);