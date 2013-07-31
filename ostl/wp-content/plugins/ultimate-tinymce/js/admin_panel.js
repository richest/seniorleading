//<![CDATA[ 

function get_radio_value() {
	var inputs = document.getElementsByName("defaults_load");
	for (var i = 0; i < inputs.length; i++) {
	  if (inputs[i].checked) {
		return inputs[i].value;
	  }
	  else {
		return 'none';
	  }
	}
};

function jwlDefaults()
{
   var setting = get_radio_value();
   var credit = document.getElementById('credit');
	   if (credit.checked){
		   var credit2 = 'true';
	   } else {
		   var credit2 = 'false';
	   }
   var beautify = document.getElementById('beautify');
       if (beautify.checked){
		   var beautify2 = 'true';
	   } else {
		   var beautify2 = 'false';
	   }
   location.href='admin.php?page=ultimate-tinymce&dontshowpopup=1&defaults='+setting+'&credit='+credit2+'&beautify='+beautify2;
};


// Used to animate through the main tabs section     NOTE:  Need to switch this to the jquery tabs.
jQuery(document).ready( 
	function($){  
		$(".menu > li").click(function(e){ 
			switch(e.target.id){  
				case "news": $("#news").addClass("active"); $("#tutorials").removeClass("active"); $("#spread").removeClass("active"); $("#gettingstarted").removeClass("active"); $("#tips").removeClass("active"); $("#defaults").removeClass("active"); $("#links").removeClass("active"); $("div.news").fadeIn(); $("div.tutorials").css("display", "none"); $("div.spread").css("display", "none"); $("div.gettingstarted").css("display", "none"); $("div.tips").css("display", "none"); $("div.defaults").css("display", "none"); $("div.links").css("display", "none");  
				break;  
				case "tutorials": $("#news").removeClass("active"); $("#tutorials").addClass("active"); $("#spread").removeClass("active"); $("#gettingstarted").removeClass("active"); $("#tips").removeClass("active"); $("#defaults").removeClass("active"); $("#links").removeClass("active"); $("div.tutorials").fadeIn(); $("div.news").css("display", "none"); $("div.spread").css("display", "none"); $("div.gettingstarted").css("display", "none"); $("div.tips").css("display", "none"); $("div.defaults").css("display", "none"); $("div.links").css("display", "none");  
				break; 
				case "spread": $("#news").removeClass("active"); $("#tutorials").removeClass("active"); $("#spread").addClass("active"); $("#gettingstarted").removeClass("active"); $("#tips").removeClass("active"); $("#defaults").removeClass("active"); $("#links").removeClass("active"); $("div.spread").fadeIn(); $("div.tips").css("display", "none"); $("div.news").css("display", "none"); $("div.gettingstarted").css("display", "none"); $("div.tutorials").css("display", "none"); $("div.links").css("display", "none"); $("div.defaults").css("display", "none");
				break; 
				case "gettingstarted": $("#news").removeClass("active"); $("#tutorials").removeClass("active"); $("#spread").removeClass("active"); $("#gettingstarted").addClass("active"); $("#tips").removeClass("active"); $("#defaults").removeClass("active"); $("#links").removeClass("active"); $("div.gettingstarted").fadeIn(); $("div.spread").css("display", "none"); $("div.tips").css("display", "none"); $("div.news").css("display", "none"); $("div.tutorials").css("display", "none"); $("div.links").css("display", "none"); $("div.defaults").css("display", "none");
				break; 
				case "tips": $("#news").removeClass("active"); $("#tutorials").removeClass("active"); $("#spread").removeClass("active"); $("#gettingstarted").removeClass("active"); $("#tips").addClass("active"); $("#defaults").removeClass("active"); $("#links").removeClass("active"); $("div.tips").fadeIn(); $("div.spread").css("display", "none"); $("div.gettingstarted").css("display", "none"); $("div.news").css("display", "none"); $("div.tutorials").css("display", "none"); $("div.links").css("display", "none"); $("div.defaults").css("display", "none");
				break; 
				case "defaults": $("#news").removeClass("active"); $("#tutorials").removeClass("active"); $("#spread").removeClass("active"); $("#gettingstarted").removeClass("active"); $("#defaults").addClass("active"); $("#tips").removeClass("active"); $("#links").removeClass("active"); $("div.defaults").fadeIn(); $("div.tips").css("display", "none"); $("div.spread").css("display", "none"); $("div.gettingstarted").css("display", "none"); $("div.news").css("display", "none"); $("div.tutorials").css("display", "none"); $("div.links").css("display", "none");
				break; 
				case "links": $("#news").removeClass("active"); $("#tutorials").removeClass("active"); $("#spread").removeClass("active"); $("#gettingstarted").removeClass("active"); $("#tips").removeClass("active"); $("#defaults").removeClass("active"); $("#links").addClass("active"); $("div.links").fadeIn(); $("div.news").css("display", "none"); $("div.tutorials").css("display", "none"); $("div.spread").css("display", "none"); $("div.gettingstarted").css("display", "none"); $("div.tips").css("display", "none"); $("div.defaults").css("display", "none");
				break;  
			} return false; 
		});  
	}
);

// Used to animate through the settings sections     NOTE:  Need to switch this to the jquery tabs.
jQuery(document).ready( 
	function($){  
		$(".menu2 > li").click(function(e){ 
			switch(e.target.id){  
				case "buttons1_tab": $("#buttons1_tab").addClass("active"); $("#buttons2_tab").removeClass("active"); $("#misc_tab").removeClass("active"); $("#admin_tab").removeClass("active"); $("#editor_tab").removeClass("active"); $("#roles_tab").removeClass("active"); $("div.buttons1_tab").fadeIn(); $("div.buttons2_tab").css("display", "none"); $("div.misc_tab").css("display", "none"); $("div.admin_tab").css("display", "none"); $("div.editor_tab").css("display", "none"); $("div.roles_tab").css("display", "none");
				break;  
				case "buttons2_tab": $("#buttons1_tab").removeClass("active"); $("#buttons2_tab").addClass("active"); $("#misc_tab").removeClass("active"); $("#admin_tab").removeClass("active"); $("#editor_tab").removeClass("active"); $("#roles_tab").removeClass("active"); $("div.buttons2_tab").fadeIn(); $("div.buttons1_tab").css("display", "none"); $("div.misc_tab").css("display", "none"); $("div.admin_tab").css("display", "none"); $("div.editor_tab").css("display", "none"); $("div.roles_tab").css("display", "none");
				break; 
				case "misc_tab": $("#buttons1_tab").removeClass("active"); $("#buttons2_tab").removeClass("active"); $("#misc_tab").addClass("active"); $("#admin_tab").removeClass("active"); $("#editor_tab").removeClass("active"); $("#roles_tab").removeClass("active"); $("div.misc_tab").fadeIn(); $("div.editor_tab").css("display", "none"); $("div.buttons1_tab").css("display", "none"); $("div.admin_tab").css("display", "none"); $("div.buttons2_tab").css("display", "none");$("div.roles_tab").css("display", "none");
				break; 
				case "admin_tab": $("#buttons1_tab").removeClass("active"); $("#buttons2_tab").removeClass("active"); $("#misc_tab").removeClass("active"); $("#admin_tab").addClass("active"); $("#editor_tab").removeClass("active"); $("#roles_tab").removeClass("active"); $("div.admin_tab").fadeIn(); $("div.misc_tab").css("display", "none"); $("div.editor_tab").css("display", "none"); $("div.buttons1_tab").css("display", "none"); $("div.buttons2_tab").css("display", "none"); $("div.roles_tab").css("display", "none");
				break; 
				case "editor_tab": $("#buttons1_tab").removeClass("active"); $("#buttons2_tab").removeClass("active"); $("#misc_tab").removeClass("active"); $("#admin_tab").removeClass("active"); $("#editor_tab").addClass("active"); $("#roles_tab").removeClass("active"); $("div.editor_tab").fadeIn(); $("div.misc_tab").css("display", "none"); $("div.admin_tab").css("display", "none"); $("div.buttons1_tab").css("display", "none"); $("div.buttons2_tab").css("display", "none"); $("div.roles_tab").css("display", "none");
				break; 
				case "roles_tab": $("#buttons1_tab").removeClass("active"); $("#buttons2_tab").removeClass("active"); $("#misc_tab").removeClass("active"); $("#admin_tab").removeClass("active"); $("#roles_tab").addClass("active"); $("#editor_tab").removeClass("active"); $("div.roles_tab").fadeIn(); $("div.editor_tab").css("display", "none"); $("div.misc_tab").css("display", "none"); $("div.admin_tab").css("display", "none"); $("div.buttons1_tab").css("display", "none"); $("div.buttons2_tab").css("display", "none");
				break;
			} return false; 
		});  
	}
);

// Used to show/hide the additional qr code options
jQuery(document).ready( 
	function($) {
    	//Hide div w/id jwl_hide
		if ($("#jwl_qr_code").is(":checked") || $("#jwl_qr_code_pages").is(":checked")) { 
			$('.jwl_hide').fadeIn('slow', function() { 
				$(".jwl_hide").css("display","block"); 
			}); 
		} else { 
			$('.jwl_hide').fadeOut('fast', function() { 
				$(".jwl_hide").css("display","none"); 
			}); 
		} 
		
		$("#jwl_qr_code").click(function(){ 
			$('.jwl_hide').fadeIn('slow', function() { 
				if ($("#jwl_qr_code").is(":checked") || $("#jwl_qr_code_pages").is(":checked")) { 
					$(".jwl_hide").css("display","block"); 
				} else { 
					$('.jwl_hide').fadeOut('slow', function() { 
						$(".jwl_hide").css("display","none"); 
					}); 
				} 
			}); 
		}); 
		
		$("#jwl_qr_code_pages").click(function(){ 
			$('.jwl_hide').fadeIn('slow', function() { 
				if ($("#jwl_qr_code_pages").is(":checked") || $("#jwl_qr_code").is(":checked")) { 
					$(".jwl_hide").css("display","block"); 
				} else { 
					$('.jwl_hide').fadeOut('slow', function() {  
						$(".jwl_hide").css("display","none"); 
					}); 
				} 
			}); 
		});
	}
);
  
//]]>