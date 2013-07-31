/*
Copyright 2012 DIYthemes, LLC. Patent pending. All rights reserved.
DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC.
License: DIYthemes Software License Agreement
License URI: http://diythemes.com/thesis/rtfm/software-license-agreement/
*/
var thesis_css;
(function($) {
thesis_css = {
	doing_save: false,
	init: function() {
		var menu = '#t_css_header ul';
		thesis_css.codeMirror = CodeMirror.fromTextArea(document.getElementById("t_css_skin"), {
			lineNumbers: true,
			indentUnit: 4,
			lineWrapping: true,
			indentWithTabs: true
		});
		$('button[data-pane="css"]').one('click', function(){
			thesis_css.codeMirror.refresh();
		});
		$('.CodeMirror-lines > div > div:nth-child(3)').droppable({
			accept: '.css_draggable',
			tolerance: 'pointer',
			drop: function(e, ui) {
				var text = $(e.originalEvent.target).data('value');
				thesis_css.codeMirror.replaceSelection(text, 'end');
				thesis_css.css();
			}
		});
		$('.t_edit_item').each(function() { thesis_css.editable(this); });
		$('#t_create_var').click(function() {
			$(this).prop('disabled', true);
			thesis_css.variable.edit({ id: 'new' });
		});
		$('#t_select_package').change(function() {
			if ($(this).val()) $('#t_add_package').show();
			else $('#t_add_package').hide();
		});
		$('#t_add_package').click(function() {
			$(this).prop('disabled', true);
			thesis_css.package.edit({ class: $('#t_select_package').val(), id: 'new_package' });
			return false;
		});
		$('.CodeMirror').keyup(function() {
			if (typeof thesis_editor.canvas == "object" && typeof thesis_editor.canvas.document.getElementById('t_live_css') == "object")
				thesis_css.css();
		});
		thesis_css.timeout = false;
		$('#t_save_css').click(function() {
			thesis_css.save();
			return false;
		});
		$('body').keydown(function(e){
			return thesis_css.maybe_save(e);
		});
	},
	editable: function(element) {
		$(element).on('mouseover', function() {
			var	w = $(this).parent().parent().width();
			$(this).parent().append('<div class="t_ajax_alert" style="width: '+w+'px; left: -'+(w + 11)+'px;"><div class="t_message"><p>'+$(this).data('tooltip')+'</p></div></div>');
		}).on('mouseout', function() { $(this).siblings('.t_ajax_alert').remove(); }).on('click', function() {
			var item = {
					type: $(this).data('type'),
					id: $(this).data('id') };
			if (item.type == 'pkg') {
				item.class = $(this).data('class');
				thesis_css.package.edit(item);
			}
			else if (item.type == 'var')
				thesis_css.variable.edit(item);
			return false;
		});
		$(element).draggable({ helper: 'clone' });
	},
	init_popup: function(type, options) {
		$(options + ' .cancel_options').on('click', function() {
			$(options).hide();
			$('body').removeClass('no-scroll');
			return false;
		});
		$(options + ' .save_options').on('click', function() {
			save = type == 'pkg' ? thesis_css.package.get() : (type == 'var' ? thesis_css.variable.get() : false);
			if (save.name && save.ref) {
				$(options).hide();
				if (type == 'pkg')
					thesis_css.package.save(save);
				else if (type == 'var')
					thesis_css.variable.save(save);
				$('body').removeClass('no-scroll');
			}
			else
				alert('Whoa there! You need to supply a name and a reference before you can save this.');
			return false;
		});
		$(options + ' .delete_options').on('click', function() {
			if (confirm('Are you sure you want to delete this? This action cannot be undone!')) {
				$(options).hide();
				if (type == 'pkg')
					thesis_css.package.delete(thesis_css.package.get());
				else if (type == 'var')
					thesis_css.variable.delete(thesis_css.variable.get());
				$('body').removeClass('no-scroll');
			}
			return false;
		});
	},
	package: {
		get: function() {
			return {
				form: $('#t_package_form').serialize(),
				class: $('#t_pkg_class').val(),
				id: $('#t_pkg_id').val(),
				title: $('#t_pkg_title').val(),
				name: $('#t_pkg_name').val(),
				ref: $('#t_pkg_ref').val() };
		},
		edit: function(pkg) {
			$.post(thesis_ajax.url, { action: 'edit_package', pkg: pkg, nonce: $('#_wpnonce-thesis-save-css').val() }, function(html) {
				$('#t_add_package').prop('disabled', false);
				if (html) {
					$('#t_css_popup').children('.t_popup_html').html(html);
					thesis_ui.popup('#t_css_popup');
					thesis_css.init_popup('pkg', '#t_css_popup');
					jscolor.bind();
				}
			});
		},
		save: function(pkg) {
			$.post(thesis_ajax.url, { action: 'save_css_package', pkg: pkg.form }, function(saved) {
				var found = false;
				$('#t_packages li').each(function() {
					if ($(this).children('code').data('id') == pkg.id) {
						found = true;
						$(this).children('code').html('&amp;'+pkg.ref).data({ 'value': '&'+pkg.ref, 'tooltip': pkg.name+' ('+pkg.title+')' }).attr('data-value', '&'+pkg.ref);
					}
				});
				if (!found) {
					var new_pkg = '<li><code class="t_edit_item css_draggable" data-type="pkg" data-class="'+pkg.class+'" data-id="'+pkg.id+'" data-value="&'+pkg.ref+'" data-tooltip="'+pkg.name+' ('+pkg.title+')" title="click to edit">&amp;'+pkg.ref+'</code></li>\n';
					$('#t_packages ul').append(new_pkg);
					thesis_css.editable($('#t_packages ul li:last').children('.t_edit_item'));
				}
				if (saved && $('#package_saved').length === 0) {
					$('#t_packages').prepend(saved);
					$('#package_saved').css({'right': $('#t_packages').width()+11+'px'});
					$('#package_saved').fadeOut(3000, function() { $(this).remove(); });
				}
				thesis_css.css();
			});
		},
		delete: function(pkg) {
			$.post(thesis_ajax.url, { action: 'delete_css_package', pkg: pkg.form }, function(deleted) {
				if (deleted) {
					$('#t_packages li').each(function() {
						if ($(this).children('code').data('id') == pkg.id)
							$(this).remove();
					});
					$('#t_packages').prepend(deleted);
					$('#package_deleted').css({'right': $('#t_packages').width()+11+'px'});
					$('#package_deleted').fadeOut(3000, function() { $(this).remove(); });
				}
			});
		}
	},
	variable: {
		get: function() {
			return {
				id: $('#t_var_id').val(),
				name: $('#t_var_name').val(),
				ref: $('#t_var_ref').val(),
				css: $('#t_var_css').val(),
				symbol: $('#t_var_symbol').val() };
		},
		edit: function(item) {
			$.post(thesis_ajax.url, { action: 'edit_variable', item: item, nonce: $('#_wpnonce-thesis-save-css').val() }, function(html) {
				$('#t_create_var').prop('disabled', false);
				$('#t_css_popup').children('.t_popup_html').html(html);
				thesis_ui.popup('#t_css_popup');
				thesis_css.init_popup('var', '#t_css_popup');
			});
		},
		save: function(item) {
			$.post(thesis_ajax.url, { action: 'save_css_variable', item: item, nonce: $('#_wpnonce-thesis-save-css-variable').val() }, function(saved) {
				var found = false;
				$('#t_vars li').each(function() {
					if ($(this).children('code').data('id') == item.id) {
						found = true;
						$(this).children('code').html(item.symbol+item.ref).data({ 'value': item.symbol+item.ref, 'tooltip': item.name+' &rarr; '+item.css }).attr('data-value', item.symbol+item.ref);
					}
				});
				if (!found) {
					$('#t_vars ul').append('<li><code class="t_edit_item css_draggable" data-type="var" data-id="'+item.id+'" data-value="'+item.symbol+item.ref+'" data-tooltip="'+item.name+' &rarr; '+item.css+'" title="click to edit">'+item.symbol+item.ref+'</code></li>\n');
					thesis_css.editable($('#t_vars ul li:last').children('.t_edit_item'));
				}
				if (saved && $('#var_saved').length === 0) {
					$('#t_vars').prepend(saved);
					$('#var_saved').css({'right': $('#t_vars').width()+11+'px'});
					$('#var_saved').fadeOut(3000, function() { $(this).remove(); });
				}
				thesis_css.css();
			});
		},
		delete: function(item) {
			$.post(thesis_ajax.url, { action: 'delete_css_variable', item: item, nonce: $('#_wpnonce-thesis-save-css-variable').val() }, function(deleted) {
				if (deleted) {
					$('#t_vars li').each(function() {
						if ($(this).children('code').data('id') == item.id)
							$(this).remove();
					});
					$('#t_vars').prepend(deleted);
					$('#var_deleted').css({'right': $('#t_vars').width()+11+'px'});
					$('#var_deleted').fadeOut(3000, function() { $(this).remove(); });
				}
			});
		}
	},
	css: function() {
		$.post(thesis_ajax.url, { action: 'live_css', skin: thesis_css.codeMirror.getValue(), nonce: $('#_wpnonce-thesis-save-css').val() }, function(css) {
			if (typeof thesis_editor.canvas == "object" && typeof thesis_editor.canvas.document.getElementById('t_live_css') == "object")
				thesis_editor.canvas.document.getElementById('t_live_css').innerHTML = css;
		});
	},
	save: function(external) {
		$('#t_save_css').prop('disabled', true);
		$.post(thesis_ajax.url, { action: 'save_css', skin: thesis_css.codeMirror.getValue(), nonce: $('#_wpnonce-thesis-save-css').val() }, function(saved) {
			if (external == true) {
				thesis_editor.login.css_saved = true;
			}
			else if ($('#css_saved').length === 0) {
				$('#t_save_css').prop('disabled', false);
				if (saved && $('#css_saved').length === 0) {
					$('#t_css').append(saved);
					$('#css_saved').css({'right': $('#t_save_css').outerWidth()+11+'px'});
					$('#css_saved').fadeOut(3000, function() { $(this).remove(); });
				}
			}
		});
	},
	maybe_save: function(e) {
		if ($('#t_css').css('display') == 'block' && e.keyCode == 83) {
			// separate different OS
			if ((/Mac/i.test(navigator.userAgent) && e.metaKey) || (/Win/i.test(navigator.userAgent) && e.ctrlKey)) {
				thesis_css.save();
				return false;
			}
		}
		return true;
	}
};
$(document).ready(function($){ thesis_css.init(); });
})(jQuery);