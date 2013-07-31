/*---:[ Copyright DIYthemes, LLC. Patent pending. All rights reserved. DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC. ]:---*/
var thesis_ui;
(function($) {
thesis_ui = {
	box_form: {
		init: function() {
			$('#t_boxes .rotator').each(function() { thesis_ui.box.rotator(this); });
			thesis_ui.box_form.queues();
			$('#t_boxes .switch_options').each(function() { thesis_ui.box.options(this); });
		},
		queues: function() {
			$('#queues').children('.rotator').children('.sortable').sortable('option', 'disabled', false);
			$('#add_boxes').draggable({ containment: '#t_boxes', handle: '> h4', stop: function() {
				if (parseInt($('#add_boxes').css('top')) < -(parseInt($('#box_queue').css('height')) + 24))
					$('#add_boxes').css('top', -(parseInt($('#box_queue').css('height')) + 24) + 'px');
			}});
			$('#add_boxes').children('h4').droppable('option', 'disabled', true);
			$('#box_class').change(function() {
				var fields = $(this).parent().parent().siblings('.option_field').add($(this).parent().parent().siblings('#add_box'));
				if ($(this).val()) $(fields).show();
				else $(fields).hide();
			});
			$('#add_box').click(function() {
				thesis_ui.box.add({ class: $('#box_class').val(), name: $('#box_name').val() });
				$(this).siblings('.option_field').children().children('input[type="text"]').add($('#box_class')).val('');
				$(this).siblings('.option_field').add($(this)).hide();
				$(this).siblings('.option_field:first').show();
				return false;
			});
			$('#delete_boxes').draggable({ containment: '#queues', handle: '> h4', stop: function() {
				var top = parseInt($('#delete_boxes').css('top')),
					should = parseInt($('#box_queue').css('height')) + 24;
				if (top < -(should))
					$('#delete_boxes').css('top', -should + 'px');
			}});
			$('#delete_boxes').children('h4').each(function() { thesis_ui.box.deletable(this); });
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
					ui.draggable.hide(1, function() { $(this).removeAttr('style').prepend(location).prependTo(sortable).show('fast'); });
				}
			});
			$(sortable).children().not('.t_popup').each(function() { thesis_ui.box.init(this); });
			thesis_ui.box.sortable(sortable);
			thesis_ui.box.tray(tray);
			if ($(toggle).hasClass('toggled') || $(rotator).attr('data-root')) {
				$(sortable).sortable('enable').show();
				if (tray) {
					$(tray).toggle();
					$(tray).children('h5').droppable('enable');
				}
			}
			$(toggle).on('click', function() {
				$(this).toggleClass('toggled');
				$(sortable).sortable('option', 'disabled', !($(this).hasClass('toggled'))).toggle();
				if (tray) {
					$(tray).toggle();
					$(tray).children('h5').droppable('option', 'disabled', !($(this).hasClass('toggled')));
				}
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
							$('.ui-droppable').not($('.ui-droppable-disabled').add($('#delete_boxes > h4'))).addClass('can_accept');
							if (ui.item.hasClass('instance'))
								$('#delete_boxes > h4').addClass('can_accept');
						}
					}
					else {
						$('#t_boxes').find('h4').not($('#add_boxes').children('h4')).droppable({ disabled: true });
						$(this).siblings('.tray').children('h5').addClass('tray_dropper').show();
					}
				},
				stop: function(event,ui) {
					$(this).siblings('.tray').children('h5').removeClass('tray_dropper').hide();
					$('#t_boxes').find('.rotator > h4').not($('#add_boxes').children('h4')).droppable('enable');
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
					$(this).removeAttr('style').
					prepend('<input type=\"hidden\" class=\"box_location\" name=\"boxes['+$(this).parent('.tray_list').parent('.tray_body').parent('.tray').parent('.rotator').attr('data-id')+'][]\" value=\"'+$(this).attr('data-id')+'\" />').
					appendTo($(this).parent('.tray_list').parent('.tray_body').parent('.tray').siblings('.sortable')).show('fast').unbind();
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
		},
		add: function(box) {
			$('#add_box').prop('disabled', true);
			$.post(thesis_ajax.url, { action: 'add_box', box: box, nonce: $('#_wpnonce-thesis-add-box').val() }, function(html) {
				$('#add_box').prop('disabled', false);
				$('#add_boxes > .sortable').append(html);
				var new_box = $('#add_boxes > .sortable > .instance:last');
				if ($(new_box).hasClass('rotator')) {
					thesis_ui.box.rotator(new_box);
					$(new_box).find('.rotator').each(function() { thesis_ui.box.rotator(this); });
				}
				$(new_box).find('h4').each(function() { thesis_ui.box.options($(this).children('.switch_options')); });
			});
		}
	},
	popup: function(popup) {
		$('body').addClass('no-scroll');
		$(popup).show();
		$(popup).find('input, select').keypress(function(event) { return event.keyCode != 13; });
		if ($(popup).hasClass('triggered') && !$(popup).hasClass('force_trigger')) return;
		var menu = $(popup+' .t_popup_menu'),
			body = $(popup+' .t_popup_body');
		// initial states
		$(popup).addClass('triggered');
		$(body).css({'margin-top': $(popup+' .t_popup_head').outerHeight()});
		$(menu).children('li:first').addClass('active');
		$(body).find('.pane_'+$(menu).children('li:first').attr('data-pane')).show();
		$(body).find('.control').each(function() {
			var control = $(this).attr('title');
			if ($(this).is(':checked') || $(this).is(':selected')) $(this).parents('.option_field').siblings('.dependent_' + control).show();
		});
		// event listeners
		$(popup+' .t_popup_name').on('change', function() { $('#'+$(this).attr('data-id')).html($(this).val()); });
		$(menu).children('li').on('click', function() {
			$(this).siblings().removeClass('active');
			$(this).addClass('active');
			$(body).find('.pane').hide();
			$(body).find('.pane_'+$(this).attr('data-pane')).show();
		});
		$('.t_popup_close').on('click', function() {
			$(popup).hide();
			$('body').removeClass('no-scroll');
		});
		$(body).find('label .toggle_tooltip').on('click', function() {
			$(this).parents('label').parents('p').siblings('.tooltip:first').toggle();
			return false;
		});
		$(body).find('.list_label .toggle_tooltip').on('click', function() {
			$(this).parents('.list_label').siblings('.tooltip:first').toggle();
			return false;
		});
		$(body).find('.tooltip').on('mouseleave', function() { $(this).hide(); });
		$(body).find('.option_group > label').on('click', function() {
			$(this).children('.toggle_group').toggleClass('toggled');
			$(this).siblings('.group_fields').toggle();
		});
		$(body).find('.control_group .checkboxes input').on('change', function() {
			$(this).parents('.option_field').siblings('.dependent_'+$(this).attr('title')).hide();
			$(this).parents('.checkboxes').children('li').each(function() {
				if ($(this).children('.control').is(':checked'))
					$(this).parents('.option_field').siblings('.dependent_'+$(this).children('.control').attr('title')).show();
			});
		});
		$(body).find('.control_group .radio input').on('change', function() {
			$(this).parents('.option_field').siblings('.dependent_'+$(this).parents('.radio').attr('title')).hide();
			$(this).parents('.radio').children('li').each(function() {
				if ($(this).children('.control').is(':checked'))
					$(this).parents('.option_field').siblings('.dependent_'+$(this).children('.control').attr('title')).show();
			});
		});
		$(body).find('.control_group select').on('change', function() {
			$(this).parents('.option_field').siblings('.dependent_'+$(this).attr('title')).hide();
			$(this).children('.control').each(function() {
				if ($(this).is(':selected'))
					$(this).parents('.option_field').siblings('.dependent_'+$(this).attr('title')).show();
			});
		});
	}
};
})(jQuery);