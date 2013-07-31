/*---:[ Copyright DIYthemes, LLC. Patent pending. All rights reserved. DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC. ]:---*/
var thesis_templates;
(function($) {
thesis_templates = {
	init: function() {
		thesis_ui.box_form.init();
		thesis_templates.manager();
		$('#switch_template_options').click(function() { thesis_ui.popup('#popup_template'); });
		$('#save_template').click(function() {
			thesis_templates.save();
			return false;
		});
	},
	manager: function() {
		$('.edit_templates').click(function() { $(this).toggleClass('active_manager'); $('#t_template_manager').slideToggle(100); });
		$('.toggle_child_templates').click(function() { $(this).siblings('.child_templates').toggle(); });
		$('.edit_template').click(function() {
			thesis_templates.change($(this).attr('data-template'));
			return false;
		});
		$('#add_template').click(function() { thesis_ui.popup('#popup_new_template'); });
		$('#create_template').click(function() {
			thesis_templates.create($('#add_template_title').val());
			return false;
		});
		$('.delete_template').click(function() {
			thesis_templates.delete($(this).attr('data-template'));
			return false;
		});
		$('#copy_from').change(function() {
			if ($(this).val()) $('#copy_template').show();
			else $('#copy_template').hide();
		});
		$('#copy_template').click(function() {
			thesis_templates.copy($('#copy_to').val(), $('#copy_from').val());
			return false;
		});
	},
	get: function(template) {
		if (!template) return;
		if (template != $('#current_template').val())
			thesis_templates.change(template);
	},
	change: function(template, alert) {
		if (!template) return;
		$.post(thesis_ajax.url, { action: 'change_template', thesis_template: template, nonce: $('#_wpnonce-thesis-ajax').val() }, function(html) {
			$('body').removeClass('no-scroll');
			$('#t_html').html(html);
			if (typeof alert == 'object') {
				$('#t_html').append('<div id="'+alert.id+'" class="t_ajax_alert" style="right: '+$('#save_template').outerWidth()+11+'px;"><div class=\"t_message\"><p>'+alert.message+'</p></div></div>');
				$('#'+alert.id).fadeOut(3000, function() { $(this).remove(); });
			}
			thesis_templates.init();
		});
	},
	create: function(title) {
		if (!title) {
			alert('You must enter a title if you wish to create a template.');
			return;
		}
		$.post(thesis_ajax.url, { action: 'create_template', title: title, nonce: $('#_wpnonce-thesis-ajax').val() }, function(template) {
			if (template)
				thesis_templates.change(template);
		});
	},
	delete: function(template) {
		if (!template || !confirm("Are you sure you want to delete this template? This cannot be undone!\n\nAny posts or pages that were using this template will be reverted to their respective default templates.")) return;
		$.post(thesis_ajax.url, { action: 'delete_template', template: template, nonce: $('#_wpnonce-thesis-ajax').val() }, function(deleted) {
			if (deleted) {
				$('#t_template_manager .custom_template').each(function() {
					if ($(this).hasClass(template))
						$(this).remove();
				});
				$('#t_html').append(deleted);
				$('#template_deleted').css({'right': $('#save_template').outerWidth()+11+'px'});
				$('#template_deleted').fadeOut(3000, function() { $(this).remove(); });
			}
		});
	},
	copy: function(to, from) {
		if (!to || !from || !confirm("Are you sure you want to replace this template by copying from an existing one? This cannot be undone.")) return;
		$('#copy_template').prop('disabled', true);
		$.post(thesis_ajax.url, { action: 'copy_template', to: to, from: from, nonce: $('#_wpnonce-thesis-ajax').val() }, function(copied) {
			$('#copy_template').prop('disabled', false);
			if (copied) {
				thesis_templates.change(to, { id: 'template_copied', message: copied });
				if ($('#current_template').val() == thesis_editor.canvas.template)
					thesis_editor.canvas.window.location.reload(true);
			}
		});
	},
	save: function() {
		$('#save_template').prop('disabled', true);
		$.post(thesis_ajax.url, { action: 'save_template', template: $('#current_template').val(), form: $('#t_boxes').serialize() }, function(saved) {
			if ($('#current_template').val() == thesis_editor.canvas.template && thesis_editor.canvas.window != null)
				thesis_editor.canvas.window.location.reload(true);
			$('#save_template').prop('disabled', false);
			$('#t_html').append(saved);
			$('#template_saved').css({'right': $('#save_template').outerWidth()+11+'px'});
			$('#template_saved').fadeOut(3000, function() { $(this).remove(); });
			thesis_ui.box_form.reset();
		});
	}
};
$(document).ready(function($){ thesis_templates.init(); });
})(jQuery);