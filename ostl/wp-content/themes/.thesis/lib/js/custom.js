/*
Copyright 2013 DIYthemes, LLC. Patent pending. All rights reserved.
DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC.
License: DIYthemes Software License Agreement
License URI: http://diythemes.com/thesis/rtfm/software-license-agreement/
*/
(function($) {
thesis_custom = {
	init: function() {
		thesis_custom.codeMirror = CodeMirror.fromTextArea(document.getElementById('t_css_custom'), {
			lineNumbers: true,
			indentUnit: 4,
			lineWrapping: true,
			indentWithTabs: true
		});
		thesis_custom.height();
		$(window).resize(function() {
			thesis_custom.height();
		});
		thesis_custom.set_flyout_width();
		$('.t_edit_item').on('mouseover', function() {
			var w = 300, position = $(this).offset();
			$('#t_flyout p').text($(this).attr('data-tooltip'));
			$('#t_flyout').css({
				top: (position.top - ($(this).parent('li').outerHeight() / 2) - $(document).scrollTop()) + 'px',
				right: $('.slideout').outerWidth() + 18 + 'px',
				display: 'block'
			});
		}).on('mouseout', function() {
			$('#t_flyout').hide();
		}).click(function() {
			thesis_custom.codeMirror.replaceSelection($(this).data('value'));
		});
		$('.slideout_toggle').click(function() {
			thesis_custom.slideout(this);
		});
		$('#t_save_css').click(function() {
			thesis_custom.save();
			return false;
		});
		$('body').keydown(function(e) {
			return thesis_custom.maybe_save(e);
		});
		$('#t_canvas_launch').click(function() {
			if (typeof thesis_custom.canvas == 'undefined' || thesis_custom.canvas.closed) {
				thesis_custom.canvas = window.open(thesis_canvas.url, thesis_canvas.name, 'modal=yes,scrollbars=1,resizable=1,width=' + $(window).width() + ',height=' + $(window).height());
				thesis_custom.canvas.onload = function() {
					thesis_custom.update();
				};
			}
			return false;
		});
		$(window).unload(function() {
			if (typeof thesis_custom.canvas == 'object')
				thesis_custom.canvas.close();
		});
		$('.CodeMirror').keydown(function(e) {
			if (typeof thesis_custom.canvas == 'object') {
				if (typeof window.t_custom_timeout != 'undefined')
					clearTimeout(window.t_custom_timeout);
				window.t_custom_timeout = setTimeout(function() {
					thesis_custom.update();
					thesis_custom.onclick_child();
				}, 600);
			}
		});
	},
	height: function() {
		var adjustment = ($(document).outerHeight() - $(window).outerHeight()) > 65 ? 65 : 0;
		$('.CodeMirror, .slideout').outerHeight($(window).height() - 229 + adjustment + 'px');
	},
	slideout: function(toggle) {
		var slideout = $(toggle).siblings('.slideout');
		if ($(slideout).hasClass('active')) {
			$(toggle).html('&#43;').animate({ right: 0 }, 100);
			$(slideout).removeClass('active').animate({ width: 0 }, 100);
		}
		else {
			$(toggle).html('&#8722;').animate({ right: thesis_custom.flyout_width }, 100);
			$(slideout).addClass('active').animate({ width: thesis_custom.flyout_width }, 100);
		}
	},
	save: function() {
		if ($('#css_saved').length === 0) {
			$('#t_save_css').prop('disabled', true);
			$.post(ajaxurl, { action: 'save_css', custom: thesis_custom.codeMirror.getValue(), nonce: $('#nonce').val() }, function(saved) {
				$('#t_save_css').prop('disabled', false);
				if (saved) {
					$('#t_canvas').append(saved);
					$('#css_saved').css({'right': $('#t_save_css').outerWidth()+35+'px'});
					$('#css_saved').fadeOut(3000, function() { $(this).remove(); });
				}
			});
		}
	},
	set_flyout_width: function() {
		var widest = false;
		$('.t_item_list li code').each(function() {
			var width = $(this).outerWidth();
			if (widest == false || width > widest)
				widest = width;
		});
		thesis_custom.flyout_width = widest + 54;
	},
	maybe_save: function(e) {
		if (e.keyCode == 83 && ((/Mac/i.test(navigator.userAgent) && e.metaKey) || (/Win/i.test(navigator.userAgent) && e.ctrlKey))) {
			thesis_custom.save();
			return false;
		}
		return true;
	},
	update: function() {
		if (typeof thesis_custom.canvas == 'object') {
			var head = thesis_custom.canvas.document.head || thesis_custom.canvas.document.getElementsByTagName('head')[0],
				style_tag = thesis_custom.canvas.document.getElementById('t_custom_css'),
				css = thesis_custom.codeMirror.getValue();
			if ($(style_tag).length == 0) {
				// create the style tag
				style_tag = thesis_custom.canvas.document.createElement('style');
				style_tag.type = 'text/css';
				style_tag.setAttribute('id', 't_custom_css');
				head.appendChild(style_tag);
			}
			$.post(thesis_ajax.url, { action: 'live_css', custom: thesis_custom.codeMirror.getValue(), nonce: $('#nonce').val() }, function(css) {
				$(style_tag).text(css);
			});
		}
	},
	regulate: function(e) {
		if (e.target.localName == 'a' && e.target.host == window.location.host) {
			var protocol = host = path = search = hash = thesis_query = url = '';
			protocol = e.target.protocol + '//';
			host = e.target.host;
			thesis_query = 'thesis_canvas=2';
			if (typeof e.target.pathname == 'string')
				path = (e.target.pathname.charAt(0) != '/' ? '/' :'') + e.target.pathname + (e.target.pathname.charAt(e.target.pathname.length - 1) != '/' ? '/' :'');
			if (typeof e.target.search == 'string')
				search = e.target.search.replace(/thesis_canvas=2/, '');
			if (typeof e.target.hash == 'string')
				hash = e.target.hash;
			url = protocol + host + path + (search.charAt(0) != '?' || search.length == 0 ? '?' : search + '&') + thesis_query + hash;
			thesis_custom.canvas.location.assign(url);
		}
		return false;
	},
	onclick_child: function() {
		thesis_custom.canvas.document.onclick = function(e) {
			return thesis_custom.regulate(e);
		};
	}
};
$(document).ready(function(){ thesis_custom.init(); });
})(jQuery);