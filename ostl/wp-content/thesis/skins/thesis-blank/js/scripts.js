jQuery(document).ready(function($) {

    // enables default text in search box in browsers that do not support the placeholder attribute, i.e. 7+
    $(":input[placeholder]").placeholder();

    // makes the orange navigation bar under the menu item appear in the main nav
    $(".main_nav a").hover(function(){
       $(this).parent().addClass('hover-main-menu');
    },function(){
        $(this).parent().removeClass('hover-main-menu');
    });

    $(".main_nav .menu > li").hover(function(){
        $(this).find('.sub-menu').show();
    },function(){
        $(this).find('.sub-menu').hide();
    });

    $('.carousel').carousel()

    function sticky_relocate() {
      var window_top = $(window).scrollTop();
      var div_top = $('.sticky-anchor').offset().top;
      if (window_top > div_top)
        $('.sticky-element').addClass('sticky')
      else
        $('.sticky-element').removeClass('sticky');
      }
     $(function() {
      $(window).scroll(sticky_relocate);
      sticky_relocate();
      });
});