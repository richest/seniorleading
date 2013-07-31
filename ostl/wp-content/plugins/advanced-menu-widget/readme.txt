=== Advanced Menu Widget ===
Contributors: JohnnyPea
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=FUT8H7SGMYE5E
Tags: menu,widget,widgets,navigation,nav,custom menus,custom menu,shortcode
Requires at least: 3.0
Tested up to: 3.5.1
Stable tag: 0.3
License: GPLv2 or later

Enhanced Navigation Menu Widget.

== Description ==

This plugin adds enhanced "Navigation Menu" widget. It offers many options which could be set to customize the output of the custom menu through the widget. 

Features include:

* Custom hierarchy - "Only related sub-items" or "Only strictly related sub-items".
* Starting depth and maximum level to display + flat display.
* Display all menu items starting with the selected one.
* Display only direct path to current element or only children of selected item (option to include the parent item).
* Display menu as drop down.
* Custom class for a widget block.
* And almost all the parameters for the [wp_nav_menu](http://codex.wordpress.org/Function_Reference/wp_nav_menu) function.
* shortcode `[advMenu]`
* Display menu items descriptions.

**Are you missing something or isn't it working as expected ? I am open to suggestions to improve the plugin !**

Thanks the [Slovak WordPress community](http://wp.sk/) and [webikon.sk](http://www.webikon.sk/) for the support. You can find free support for WordPress related stuff on [Techforum.sk](http://www.techforum.sk/). For more information about me check out my [personal page](http://johnnypea.wp.sk/).

== Installation ==

Activate the plugin.

You can set everything right on the widget control panel.

== Frequently Asked Questions ==

= Can I email you with the support questions ? =

No. Please use integrated forum support system.

= Do you provide some extra "premium" customization ? =

Yes. You can email me in this case.

== Options ==

* "Title" - set the title for your widget
* "Custom Widget Class" - custom class for widget block
* "Select Menu" - menu from the custom menus you want show
* "Show hierarchy" - you can set to display only related or strictly related items
* "Starting depth" - display only menu items which depth is greater then this
* "How many levels to display" - limit the display from 1 to 5 levels or flat display
* "Filter selection from" - only items which are children or grandchildren of selected element will be displayed
* "Select the filter" - filter the direct path for the current menu item (like breadcrumb navigation) or display only its children
* "Include parents" - when checked it display also parent item upon filters (e.x. Display only children of selected item)

== Shortcode ==

You can use `[advMenu]` shortcode with the parameters listed below:

`
'nav_menu' (menu ID)				
'title'				
'dropdown' 				
'only_related' 			
'depth' 				
'container' 			
'container_id' 			
'menu_class'			
'before' 				
'after' 				
'link_before' 			
'link_after' 			
'filter' 					
'filter_selection' 			
'include_parent' 				
'start_depth' 			
'hide_title' 			
'custom_widget_class'
`

e.g. `[advMenu title="Menu Title" nav_menu=2]`

== Screenshots ==

1. Widget options.

== Changelog ==

= 0.3 =
* Added option to display menu as dropdown.
* Added descriptions.
* Added shortcode [advMenu]
* Post related parents are now accepted.
* Bug fixes and enhancements.

= 0.2 =
* Enhanced custom hierarchy (you are seeing really only related elements).
* Added option for a starting depth.
* Added option for a starting element.
* Added filter to display only some menu part based on chosen item.
* Added direct path.
* Added option to display only children elements of selected item.
* Added custom class for a widget block.

= 0.1 =
* initial release

== Upgrade Notice ==

= 0.3 =
New features, bug fixes and enhancements.

= 0.2 =
New features and enhancements.

= 0.1 =
initial release