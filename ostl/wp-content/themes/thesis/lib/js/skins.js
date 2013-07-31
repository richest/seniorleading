/*---:[ Copyright DIYthemes, LLC. Patent pending. All rights reserved. DIYthemes, Thesis, and the Thesis Theme are registered trademarks of DIYthemes, LLC. ]:---*/
var thesis_skins;
(function($) {
thesis_skins = {
	init: function() {
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
		$(iframe).contents().find(div).insertAfter(append);
		setTimeout(function(){
			$(iframe).attr('src', url);
		}, 5000);
	},
	delete_popup: function(class_name, url) {
		if (confirm("Are you sure you want to delete this skin? You will lose any data associated with it, but you can always re-install this skin at a later time.") &&  typeof class_name == "string" && typeof url == "string") {
			class_name = escape(class_name);
			popup_html = '<div id="popup_delete_'+ class_name +'" class="t_popup triggered" style="display:block;"><div class="t_popup_html"><div class="t_popup_head" data-style="box"><span class="t_popup_close" data-style="close" title="click to close" onclick="jQuery(\'#popup_delete_'+ class_name +'\').remove()">\'</span><h4>Delete Skin</h4></div><div class="t_popup_body" style="margin-top: 55px;"><iframe style="width:100%;height:100%;" frameborder="0" src="'+ encodeURI(url) +'" id="thesis_delete_'+ class_name +'"></iframe></div></div></div>';
			$('#popup_skin_uploader').after(popup_html);
			return false;
		}
	}
};
$(document).ready(function($){ thesis_skins.init(); });
})(jQuery);