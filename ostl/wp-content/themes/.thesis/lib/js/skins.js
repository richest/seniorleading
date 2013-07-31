/*
Copyright 2012 DIYthemes, LLC. Patent pending. All rights reserved.
DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC.
License: DIYthemes Software License Agreement
License URI: http://diythemes.com/thesis/rtfm/software-license-agreement/
*/
var thesis_skins;
(function($) {
thesis_skins = {
	init: function() {
		$('.skin_delete').on('click', function() {
			thesis_skins.delete($(this).attr('data-id'));
			return false;
		});
		$('#skin_upload').click(function() { thesis_skins.popup('#popup_skin_uploader'); return false; });
	},
	popup: function(popup) {
		$('body').addClass('no-scroll');
		$(popup).show();
		if ($(popup).hasClass('triggered') && !$(popup).hasClass('force_trigger')) return;
		var body = $(popup+' .t_popup_body');
		$(popup).addClass('triggered');
		$(body).css({'margin-top': $(popup+' .t_popup_head').outerHeight()});
		$('.t_popup_close').on('click', function() {
			$(popup).hide();
			$('body').removeClass('no-scroll');
		});
		$(body).find('label .toggle_tooltip').on('click', function() {
			$(this).parents('label').parents('p').siblings('.tooltip:first').toggle();
			return false;
		});
		$(body).find('.tooltip').on('mouseleave', function() { $(this).hide(); });
	},
	add_item: function(iframe, div, append, url) {
		if (div !== false)
			$(iframe).contents().find(div).insertAfter(append);
		setTimeout(function(){
			$(iframe).attr('src', url);
		}, 5000);
	},
	delete: function(id) {
		if (typeof id == "string" && confirm("Are you sure you want to delete this skin? You will lose any data associated with it, but you can always re-install this skin at a later time."))
			$.post(ajaxurl, { action: 'delete_skin', id: id }, function(popup) {
				$('#popup_skin_uploader').after(popup);
				thesis_skins.popup('#popup_delete_'+id);
			});
	}
};
$(document).ready(function($){ thesis_skins.init(); });
})(jQuery);