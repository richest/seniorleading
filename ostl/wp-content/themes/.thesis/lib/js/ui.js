/*
Copyright 2012 DIYthemes, LLC. Patent pending. All rights reserved.
DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC.
License: DIYthemes Software License Agreement
License URI: http://diythemes.com/thesis/rtfm/software-license-agreement/
*/
var thesis_ui;
(function($) {
thesis_ui = {
	box_form: {
		init: function() {
			$('#t_boxes .rotator').each(function() { thesis_ui.box.rotator(this); });
			thesis_ui.box_form.manager();
			$('#t_boxes .switch_options').each(function() { thesis_ui.box.options(this); });
		},
		manager: function() {
			thesis_ui.box_form.draggable('#box_manager');
			thesis_ui.box_form.draggable('#delete_boxes');
			thesis_ui.box.sortable('#box_manager > .sortable');
			thesis_ui.box_form.removable('#remove_boxes');
			thesis_ui.box.deletable('#delete_boxes .dropzone');
			$('#box_id').change(function() {
				if ($(this).val() && $(this).val() != '*') $('#add_box').show();
				else $('#add_box').hide();
			});
			$('#add_box').click(function() {
				thesis_ui.box_form.add($('#box_id').val());
				$('#box_id').val('');
				$(this).hide();
				return false;
			});
			$('#box_manager > .sortable').sortable('option', 'disabled', false);			
		},
		draggable: function (draggable) {
			$(draggable).draggable({
				containment: '#t_boxes',
				handle: '> .dropzone',
				stop: function() {
					if (parseInt($(draggable).css('top')) < -(parseInt($('#t_boxes').css('height')) + 24))
						$(draggable).css('top', -(parseInt($('#t_boxes').css('height')) + 24) + 'px');
				}
			});
		},
		removable: function (dropzone) {
			if (!dropzone) return;
			$(dropzone).droppable({
				accept: '.draggable',
				hoverClass: 'accept_box',
				tolerance: 'pointer',
				greedy: true,
				drop: function(event, ui) {
					thesis_ui.box_form.remove(ui.draggable);
					ui.draggable.hide(1, function() { $(this).remove(); });
				}
			});
		},
		remove: function(box) {
			if (!box) return;
			var id = $(box).attr('data-id'),
				name = $(box).children('h4').children('.box_name').html(),
				add = true;
			$('#box_id option').each(function() {
				if ($(this).attr('value') == id)
					add = false;
			});
			if (add)
				$('#box_id').append('<option value="'+id+'">'+name+'</option>');
			$('#popup_'+id).remove();
			if ($(box).hasClass('rotator'))
				$(box).children('.sortable').children().each(function() {
					if (!$(this).hasClass('t_popup') && !$(this).hasClass('child'))
						thesis_ui.box_form.remove(this);
				});
		},
		add: function(id) {
			$('#add_box').prop('disabled', true);
			$.post((typeof thesis_ajax == 'object' ? thesis_ajax.url : ajaxurl), { action: 'add_box', id: id, nonce: $('#_wpnonce-thesis-add-box').val() }, function(html) {
				$('#add_box').prop('disabled', false);
				$('#box_manager > .sortable').append(html);
				var box = $('#box_manager > .sortable > .draggable:last');
				if ($(box).hasClass('rotator')) {
					thesis_ui.box.rotator(box);
					$(box).find('.rotator').each(function() { thesis_ui.box.rotator(this); });
				}
				$(box).find('h4').each(function() { thesis_ui.box.options($(this).children('.switch_options')); });
				$('#box_id option').each(function() {
					if ($(this).attr('value') == id && $(this).html().indexOf('*') != 0)
						$(this).remove();
				});
			});
		},
		reset: function() {
			$('#t_boxes .delete_box_input').remove();
			$('#delete_boxes').children('.sortable').html('');
		}
	},
	box: {
		init: function(box) {
			$(box).prepend('<input type=\"hidden\" class=\"box_location\" name=\"boxes['+$(box).parent().parent('.rotator').attr('data-id')+'][]\" value=\"'+$(box).attr('data-id')+'\" />');
		},
		options: function(trigger) {
			$(trigger).click(function() {
				thesis_ui.popup('#popup_'+$(this).parent('h4').parent().attr('data-id'));
				return false;
			});
		},
		rotator: function(rotator) {
			var h4 = $(rotator).children('h4'),
				toggle = $(h4).children('.toggle_box'),
				sortable = $(rotator).children('.sortable'),
				tray = $(rotator).children('.tray');
			$(h4).droppable({
				accept: '.draggable',
				hoverClass: 'accept_box',
				tolerance: 'pointer',
				greedy: true,
				drop: function(event, ui) {
					var box = ui.draggable.attr('data-id'),
						parent = $(rotator).attr('data-id'),
						location = parent ? '<input type=\"hidden\" class=\"box_location\" name=\"boxes['+parent+'][]\" value=\"'+box+'\" />' : '';
					$('#delete_box_'+box).remove();
					ui.draggable.children('.box_location').remove();
					if (event.altKey)
						ui.draggable.hide(1, function() { $(this).removeAttr('style').prepend(location).appendTo(sortable).show('fast'); });
					else
						ui.draggable.hide(1, function() { $(this).removeAttr('style').prepend(location).prependTo(sortable).show('fast'); });
					$('#popup_'+box).appendTo(sortable);
				}
			});
			$(sortable).children().not('.t_popup').each(function() { thesis_ui.box.init(this); });
			thesis_ui.box.sortable(sortable);
			thesis_ui.box.tray(tray);
			if ($(toggle).hasClass('toggled') || $(rotator).attr('data-root') || $(rotator).attr('id') == 'delete_boxes') {
				$(sortable).sortable('enable').show();
				if ($(toggle).hasClass('toggled'))
					$(toggle).html('&#8863;');
				if (tray) {
					$(tray).toggle();
					$(tray).children('h5').droppable('enable');
				}
			}
			$(h4).on('mouseover', function() {
				if ($(rotator).parent().attr('id') != 'queues')
					$(this).add($(sortable)).css({ 'background-color': '#fef8c2' });
			}).on('mouseout', function() {
				if ($(rotator).parent().attr('id') != 'queues')
					$(this).add($(sortable)).css({ 'background-color': '' });
			});
			$(toggle).on('click', function() {
				$(this).toggleClass('toggled');
				$(sortable).sortable('option', 'disabled', !($(this).hasClass('toggled'))).toggle();
				if (tray) {
					$(tray).toggle();
					$(tray).children('h5').droppable('option', 'disabled', !($(this).hasClass('toggled')));
				}
				if ($(this).hasClass('toggled'))
					$(this).html('&#8863;');
				else
					$(this).html('&#8862;');
				return false;
			});
		},
		sortable: function(sortable) {
			if (!sortable) return;
			$(sortable).sortable({
				disabled: true,
				handle: '> h4',
				cursor: 'move',
				placeholder: 'placeholder',
				items: '> .draggable, > .box, > .rotator',
				distance: 5,
				opacity: 0.6,
				start: function(event, ui) {
					ui.placeholder.height(ui.item.height());
					if (event.shiftKey) {
						$('.placeholder').hide();
						if (ui.item.hasClass('draggable')) {
							$('.ui-droppable').not($('.ui-droppable-disabled').add($('.box > h4')).add($('#delete_boxes > h4'))).addClass('can_accept');
							if (ui.item.hasClass('instance'))
								$('#delete_boxes > h4').addClass('can_accept');
						}
					}
					else {
						$('#t_boxes').find('h4').add($('#remove_boxes')).droppable({ disabled: true });
						$(this).siblings('.tray').children('h5').addClass('tray_dropper').show();
					}
				},
				stop: function(event,ui) {
					$(this).siblings('.tray').children('h5').removeClass('tray_dropper').hide();
					$('#t_boxes').find('.rotator > h4').add($('#remove_boxes')).droppable('enable');
					if (ui.item.hasClass('draggable'))
						$('.ui-droppable').not($('.ui-droppable-disabled')).removeClass('can_accept');
				}
			});
		},
		tray: function(tray) {
			if (!tray) return;
			var body = $(tray).children('.tray_body'),
				list = $(body).children('.tray_list');
			$(tray).children('h5').droppable({
				accept: '.parent_' + $(tray).parent('.rotator').attr('data-id'),
				disabled: true,
				hoverClass: 'accept_child',
				tolerance: 'pointer',
				greedy: true,
				drop: function(event, ui) {
					ui.draggable.children('.box_location').remove();
					ui.draggable.hide(100, function() {
						thesis_ui.box.dependent(this);
						$(this).removeAttr('style').prependTo(list).show('fast');
					});
				}
			});
			$(list).children('.child').each(function() { thesis_ui.box.dependent(this); });
			$(tray).children('.tray_bar').children('.toggle_tray').on('click', function() {
				$(body).slideToggle(100);
				$(this).toggleClass('tray_on');
				if ($(this).hasClass('tray_on')) $(this).html('hide tray &uarr;');
				else $(this).html('show tray &darr;');
				return false;
			});
		},
		dependent: function(dependent) {
			$(dependent).on('click', function() {
				$(this).hide(200, function() {
					var destination = $(this).parent('.tray_list').parent('.tray_body').parent('.tray').siblings('.sortable');
					$(this).removeAttr('style').
					prepend('<input type=\"hidden\" class=\"box_location\" name=\"boxes['+$(this).parent('.tray_list').parent('.tray_body').parent('.tray').parent('.rotator').attr('data-id')+'][]\" value=\"'+$(this).attr('data-id')+'\" />').
					appendTo(destination).show('fast').unbind();
					$('#popup_'+$(this).attr('data-id')).appendTo(destination);
				});
				return false;
			});
		},
		deletable: function(h4) {
			$(h4).droppable({
				accept: '.instance',
				hoverClass: 'accept_box',
				tolerance: 'pointer',
				greedy: true,
				drop: function(event, ui) {
					ui.draggable.children('.box_location').remove();
					var box = ui.draggable.attr('data-id');
					$('#delete_box_'+box).remove();
					$('#t_boxes').append('<input type=\"hidden\" class=\"delete_box_input\" id=\"delete_box_'+box+'\" name=\"delete_boxes[]\" value=\"'+box+'\" />');
					ui.draggable.hide(170, function() { $(this).removeAttr('style').prependTo($(h4).siblings('.sortable')).show('fast'); });
				}
			});
		}
	},
	popup: function(popup) {
		if (!$(popup).is('*')) return;
		$('body').addClass('no-scroll');
		$(popup).show();
		$(popup).find('input, select').keypress(function(event) { return event.keyCode != 13; });
		if ($(popup).hasClass('triggered') && !$(popup).hasClass('force_trigger')) return;
		var menu = popup+' .t_popup_menu',
			body = popup+' .t_popup_body';
		$(popup).addClass('triggered');
		$(body).css({'margin-top': $(popup+' .t_popup_head').outerHeight()});
		$(menu).children('li:first').addClass('active');
		$(body).find('.pane_'+$(menu).children('li:first').attr('data-pane')).show();
		$(menu).children('li').on('click', function() {
			$(this).siblings().removeClass('active');
			$(this).addClass('active');
			$(body).find('.pane').hide();
			$(body).find('.pane_'+$(this).attr('data-pane')).show();
		});
		$(popup+' .t_popup_name').on('keyup', function() { $('#'+$(this).attr('data-id')).html($(this).val()); });
		$('.t_popup_close').on('click', function() {
			$(popup).hide();
			$('body').removeClass('no-scroll');
		});
		thesis_options.init(body);
	}
};
})(jQuery);