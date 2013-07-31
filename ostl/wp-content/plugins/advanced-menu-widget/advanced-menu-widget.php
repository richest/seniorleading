<?php
/*
 * Plugin Name: Advanced Menu Widget
 * Plugin URI: http://www.techforum.sk/
 * Description: Enhanced Navigation Menu Widget
 * Version: 0.3
 * Author: Ján Bočínec
 * Author URI: http://johnnypea.wp.sk/
 * License: GPL2+
*/

function add_rem_menu_widget() {
	//unregister_widget( 'WP_Nav_Menu_Widget' );
	register_widget('Advanced_Menu_Widget');
}
add_action('widgets_init', 'add_rem_menu_widget');

function selective_display($itemID, $children_elements, $strict_sub = false) {
	global $wpdb;
	if ( ! empty($children_elements[$itemID]) ) {
		foreach ( $children_elements[$itemID] as &$childchild ) {
			$childchild->display = 1;
			if ( ! empty($children_elements[$childchild->ID]) && ! $strict_sub ) {
				selective_display($childchild->ID, $children_elements);
			}
		}
	}
	
}

//the idea for this extented class is from here http://wordpress.stackexchange.com/questions/2802/display-a-only-a-portion-of-the-menu-tree-using-wp-nav-menu/2930#2930
class Related_Sub_Items_Walker extends Walker_Nav_Menu
{	
	var $ancestors = array();
	var	$selected_children = 0;
	var $direct_path = 0;
	var $include_parent = 0;
	var	$start_depth = 0;

    function start_lvl(&$output, $depth, $args) {
      if ( !$args->dropdown )
      	parent::start_lvl($output, $depth, $args);
      else
      	$indent = str_repeat("\t", $depth); // don't output children opening tag (`<ul>`)
    }

    function end_lvl(&$output, $depth, $args) {
      if ( !$args->dropdown )
      	parent::end_lvl($output, $depth, $args);
      else
      	$indent = str_repeat("\t", $depth); // don't output children closing tag
    }

    function start_el(&$output, $item, $depth, $args){
      if ( !$args->dropdown ) {
		  parent::start_el($output, $item, $depth, $args);
      } else {
	      // add spacing to the title based on the depth
	      $item->title = str_repeat("&nbsp;", $depth * 3).$item->title;

	      parent::start_el($output, $item, $depth, $args);

		  $_root_relative_current = untrailingslashit( $_SERVER['REQUEST_URI'] );
		  $current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_root_relative_current );      

	      $selected = ( $current_url == untrailingslashit( $item->url ) ) ? ' selected="selected"' : '';

	      // no point redefining this method too, we just replace the li tag...
	      $output = str_replace('<li', '<option value="'.$item->url.'"'.$selected, $output);
      }
	  if ( $args->description )
	  	$output .= sprintf(' <small class="nav_desc">%s</small>', esc_html($item->description));      
    }

    function end_el(&$output, $item, $depth, $args){
      if ( !$args->dropdown )
      	parent::end_el($output, $item, $depth, $args);
      else    	
      	$output .= "</option>\n"; // replace closing </li> with the option tag
    }	

	function display_element( $element, &$children_elements, $max_depth, $depth=0, $args, &$output ) {
		
		if ( !$element )
			return;
		
		$id_field = $this->db_fields['id'];

		//display this element
		if ( is_array( $args[0] ) )
			$args[0]['has_children'] = ! empty( $children_elements[$element->$id_field] );
		$cb_args = array_merge( array(&$output, $element, $depth), $args);
		if ( is_object( $args[0] ) ) {
	        $args[0]->has_children = ! empty( $children_elements[$element->$id_field] );
	    }    

		$display = ( isset($element->display) ) ? $element->display : 0;

		if ( ( ($this->selected_children && $display) || !$this->selected_children ) && ( ($this->start_depth && $depth >= $this->start_depth) || !$this->start_depth ) ) {
			if ( ($args[0]->only_related && ($element->menu_item_parent == 0 || (in_array($element->menu_item_parent, $this->ancestors) || $display)))
				|| (!$args[0]->only_related && ($display || !$args[0]->filter_selection) ) )
					call_user_func_array(array(&$this, 'start_el'), $cb_args);
		}

		$id = $element->$id_field;
	    
		// descend only when the depth is right and there are childrens for this element
		if ( ($max_depth == 0 || $max_depth > $depth+1 ) && isset( $children_elements[$id]) ) {

			foreach( $children_elements[ $id ] as $child ){

				$current_element_markers = array( 'current-menu-item', 'current-menu-parent', 'current-menu-ancestor', 'current_page_item' );
							
				$descend_test = array_intersect( $current_element_markers, $child->classes );

				if ( $args[0]->strict_sub || !in_array($child->menu_item_parent, $this->ancestors) && !$display )
						$temp_children_elements = $children_elements;
									
				if ( !isset($newlevel) ) {
					$newlevel = true;
					//start the child delimiter
					$cb_args = array_merge( array(&$output, $depth), $args);

					if ( ( ($this->selected_children && $display) || !$this->selected_children ) && ( ($this->start_depth && $depth >= $this->start_depth) || !$this->start_depth ) ) {	
						if ( ($args[0]->only_related && ($element->menu_item_parent == 0 || (in_array($element->menu_item_parent, $this->ancestors) || $display)))
							|| (!$args[0]->only_related && ($display || !$args[0]->filter_selection) ) )
									call_user_func_array(array(&$this, 'start_lvl'), $cb_args);
					}
				}												

				if ( $args[0]->only_related && !$args[0]->filter_selection && ( !in_array($child->menu_item_parent, $this->ancestors) && !$display && !$this->direct_path )
					|| ( $args[0]->strict_sub && empty( $descend_test ) && !$this->direct_path ) )
							unset ( $children_elements );		

				if ( ( $this->direct_path && !empty( $descend_test ) ) || !$this->direct_path ) {	
					$this->display_element( $child, $children_elements, $max_depth, $depth + 1, $args, $output );
				}

				if ($args[0]->strict_sub || !in_array($child->menu_item_parent, $this->ancestors) && !$display)
						$children_elements = $temp_children_elements;				
			}
			unset( $children_elements[ $id ] );
		}

		if ( isset($newlevel) && $newlevel ){
			//end the child delimiter
			$cb_args = array_merge( array(&$output, $depth), $args);
			if ( ( ($this->selected_children && $display) || !$this->selected_children ) && ( ($this->start_depth && $depth >= $this->start_depth) || !$this->start_depth ) ) {	
				if ( ($args[0]->only_related && ($element->menu_item_parent == 0 || (in_array($element->menu_item_parent, $this->ancestors) || $display)))
					|| (!$args[0]->only_related && ($display || !$args[0]->filter_selection) ) )
							call_user_func_array(array(&$this, 'end_lvl'), $cb_args);
			}
		}

		//end this element
		$cb_args = array_merge( array(&$output, $element, $depth), $args);
		if ( ( ($this->selected_children && $display) || !$this->selected_children ) && ( ($this->start_depth && $depth >= $this->start_depth) || !$this->start_depth ) ) {
			if ( ($args[0]->only_related && ($element->menu_item_parent == 0 || (in_array($element->menu_item_parent, $this->ancestors) || $display)))
				|| (!$args[0]->only_related && ($display || !$args[0]->filter_selection) ) )
						call_user_func_array(array(&$this, 'end_el'), $cb_args);
		}
	}
	
	function walk( $elements, $max_depth) {

		$args = array_slice(func_get_args(), 2);

		if ( ! empty($args[0]->include_parent) )
			$this->include_parent = 1;

		if ( ! empty($args[0]->start_depth) )
			$this->start_depth = $args[0]->start_depth;

		if ( $args[0]->filter == 1 )
			$this->direct_path = 1;
		elseif ( $args[0]->filter == 2 )
			$this->selected_children = 1;

		$output = '';

		if ($max_depth < -1) //invalid parameter
			return $output;

		if (empty($elements)) //nothing to walk
			return $output;

		$id_field = $this->db_fields['id'];
		$parent_field = $this->db_fields['parent'];
		
		// flat display
		if ( -1 == $max_depth ) {
			$empty_array = array();
			foreach ( $elements as $e )
				$this->display_element( $e, $empty_array, 1, 0, $args, $output );
			return $output;
		}

		/*
		 * need to display in hierarchical order
		 * separate elements into two buckets: top level and children elements
		 * children_elements is two dimensional array, eg.
		 * children_elements[10][] contains all sub-elements whose parent is 10.
		 */
		$top_level_elements = array();
		$children_elements  = array();
		foreach ( $elements as $e) {
			if ( 0 == $e->$parent_field )
				$top_level_elements[] = $e;
			else
				$children_elements[ $e->$parent_field ][] = $e;
		}

		/*
		 * when none of the elements is top level
		 * assume the first one must be root of the sub elements
		 */
		if ( empty($top_level_elements) ) {

			$first = array_slice( $elements, 0, 1 );
			$root = $first[0];

			$top_level_elements = array();
			$children_elements  = array();
			foreach ( $elements as $e) {
				if ( $root->$parent_field == $e->$parent_field )
					$top_level_elements[] = $e;
				else
					$children_elements[ $e->$parent_field ][] = $e;
			}
		}

		if ( $args[0]->only_related || $this->include_parent || $this->selected_children ) {
			foreach ( $elements as &$el ) {
				$post_parent = $args[0]->post_parent ? in_array('current-menu-parent',$el->classes) : 0;

				if ( $this->selected_children )
				{	
					if ( $el->current || $post_parent )
							$args[0]->filter_selection = $el->ID;
							
				}
				elseif ( $args[0]->only_related ) 
				{
					if ( $el->current || $post_parent ) {
						$el->display = 1;
						selective_display($el->ID, $children_elements);
						
						$ancestors = array();
						$menu_parent = $el->menu_item_parent;
			      		while ( $menu_parent && ! in_array( $menu_parent, $ancestors ) ) {
			                    $ancestors[] = (int) $menu_parent;
			                    $temp_menu_paret = get_post_meta($menu_parent, '_menu_item_menu_item_parent', true);
			                    $menu_parent = $temp_menu_paret;
			            }
			            $this->ancestors = $ancestors;
					}
				}
				if ( $this->include_parent ) {				
					if ( $el->ID == $args[0]->filter_selection )
							$el->display = 1;	
				}

			}
		}	
		$strict_sub_arg = ( $args[0]->strict_sub ) ? 1 : 0;
		if ( $args[0]->filter_selection || $this->selected_children )
				$top_parent = selective_display($args[0]->filter_selection, $children_elements, $strict_sub_arg);			
		
		$current_element_markers = array( 'current-menu-item', 'current-menu-parent', 'current-menu-ancestor', 'current_page_item' );

		foreach ( $top_level_elements as $e ) {				
			
			if ( $args[0]->only_related ) {

				$temp_children_elements = $children_elements;
				
				// descend only on current tree
				$descend_test = array_intersect( $current_element_markers, $e->classes );
				if ( empty( $descend_test ) && !$this->direct_path )  
					unset ( $children_elements );

				if ( ( $this->direct_path && !empty( $descend_test ) ) || ( !$this->direct_path ) ) {	
					$this->display_element( $e, $children_elements, $max_depth, 0, $args, $output );
				}

				$children_elements = $temp_children_elements;

			} elseif ( (! empty ($top_parent) && $top_parent == $e->ID ) || empty($top_parent) ) {
				$this->display_element( $e, $children_elements, $max_depth, 0, $args, $output );
			}
		}

		return $output;
	}

}

/**
 * Advanced Menu Widget class
 */
 class Advanced_Menu_Widget extends WP_Widget {

	function Advanced_Menu_Widget() {
		$widget_ops = array( 'description' => 'Use this widget to add one of your custom menus as a widget.' );
		parent::WP_Widget( 'advanced_menu', 'Advanced Menu', $widget_ops );
	}

	function widget($args, $instance) {
		
		$items_wrap = !empty( $instance['dropdown'] ) ? '<select id="amw-'.$this->number.'" class="%2$s amw" onchange="onNavChange(this)"><option value="">Select</option>%3$s</select>' : '<ul id="%1$s" class="%2$s">%3$s</ul>';
		$only_related_walker = ( $instance['only_related'] == 2 || $instance['only_related'] == 3 || 1 == 1 )? new Related_Sub_Items_Walker : new Walker_Nav_Menu;
		$strict_sub = $instance['only_related'] == 3 ? 1 : 0;
		$only_related = $instance['only_related'] == 2 || $instance['only_related'] == 3 ? 1 : 0;
		$depth = $instance['depth'] ? $instance['depth'] : 0;		
		$container = isset( $instance['container'] ) ? $instance['container'] : 'div';
		$container_id = isset( $instance['container_id'] ) ? $instance['container_id'] : '';
		$menu_class = isset( $instance['menu_class'] ) ? $instance['menu_class'] : 'menu';
		$before = isset( $instance['before'] ) ? $instance['before'] : '';
		$after = isset( $instance['after'] ) ? $instance['after'] : '';
		$link_before = isset( $instance['link_before'] ) ? $instance['link_before'] : '';
		$link_after = isset( $instance['link_after'] ) ? $instance['link_after'] : '';
		$filter = !empty($instance['filter']) ? $instance['filter'] : 0;
		$filter_selection = $instance['filter_selection'] ? $instance['filter_selection'] : 0;
		$custom_widget_class  = isset( $instance['custom_widget_class'] ) ? trim($instance['custom_widget_class']) : '';
		$include_parent = !empty( $instance['include_parent'] ) ? 1 : 0;
		$post_parent = !empty( $instance['post_parent'] ) ? 1 : 0;
		$description = !empty( $instance['description'] ) ? 1 : 0;
		$start_depth = !empty($instance['start_depth']) ? absint($instance['start_depth']) : 0;
		$hide_title = !empty( $instance['hide_title'] ) ? 1 : 0;
		$container_class ='';


		// Get menu
		$menu = wp_get_nav_menu_object( $instance['nav_menu'] );

		if ( !$menu )
			return;

		$wp_nav_menu = wp_nav_menu( array( 'echo' => false, 'items_wrap' => '%3$s','fallback_cb' => '', 'menu' => $menu, 'walker' => $only_related_walker, 'depth' => $depth, 'only_related' => $only_related, 'strict_sub' => $strict_sub, 'filter_selection' => $filter_selection, 'container' => false,'container_id' => $container_id,'menu_class' => $menu_class, 'before' => $before, 'after' => $after, 'link_before' => $link_before, 'link_after' => $link_after, 'filter' => $filter, 'include_parent' => $include_parent, 'post_parent' => $post_parent, 'description' => $description, 'start_depth' => $start_depth, 'dropdown' => $instance['dropdown'] ) );

		if ( !$wp_nav_menu && $hide_title )
			return;

		if ( $custom_widget_class ) {
			echo str_replace ('class="', 'class="' . "$custom_widget_class ", $args['before_widget']);
		} else {
			echo $args['before_widget'];			
		}

		$instance['title'] = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);

		if ( !empty($instance['title']) )
			echo $args['before_title'] . $instance['title'] . $args['after_title'];

		if ( $wp_nav_menu ) {

			static $menu_id_slugs = array();

			$nav_menu ='';

			$show_container = false;
			if ( $container ) {
				$allowed_tags = apply_filters( 'wp_nav_menu_container_allowedtags', array( 'div', 'nav' ) );
				if ( in_array( $container, $allowed_tags ) ) {
					$show_container = true;
					$class = $container_class ? ' class="' . esc_attr( $container_class ) . '"' : ' class="menu-'. $menu->slug .'-container"';
					$id = $container_id ? ' id="' . esc_attr( $container_id ) . '"' : '';
					$nav_menu .= '<'. $container . $id . $class . '>';
				}
			}

			// Attributes
			if ( ! empty( $menu_id ) ) {
				$wrap_id = $menu_id;
			} else {
				$wrap_id = 'menu-' . $menu->slug;
				while ( in_array( $wrap_id, $menu_id_slugs ) ) {
					if ( preg_match( '#-(\d+)$#', $wrap_id, $matches ) )
						$wrap_id = preg_replace('#-(\d+)$#', '-' . ++$matches[1], $wrap_id );
					else
						$wrap_id = $wrap_id . '-1';
				}
			}
			$menu_id_slugs[] = $wrap_id;

			$wrap_class = $menu_class ? $menu_class : '';

			$nav_menu .= sprintf( $items_wrap, esc_attr( $wrap_id ), esc_attr( $wrap_class ), $wp_nav_menu );

			if ( $show_container )
				$nav_menu .= '</' . $container . '>';		

			echo $nav_menu;

if ( $instance['dropdown'] ) : ?>
<script type='text/javascript'>
/* <![CDATA[ */
	function onNavChange(dropdown) {
		if ( dropdown.options[dropdown.selectedIndex].value ) {
			location.href = dropdown.options[dropdown.selectedIndex].value;
		}
	}
/* ]]> */
</script>
<?php endif;
		}

		echo $args['after_widget'];
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( stripslashes($new_instance['title']) );		
		$instance['nav_menu'] = (int) $new_instance['nav_menu'];
		$instance['depth'] = (int) $new_instance['depth'];
		$instance['only_related'] = !$new_instance['filter_selection'] ? (int) $new_instance['only_related'] : 0;
		$instance['filter_selection'] = (int) $new_instance['filter_selection'];			
		$instance['container'] = $new_instance['container'];
		$instance['container_id'] = $new_instance['container_id'];
		$instance['menu_class'] = $new_instance['menu_class'];
		$instance['before'] = $new_instance['before'];
		$instance['after'] = $new_instance['after'];
		$instance['link_before'] = $new_instance['link_before'];
		$instance['link_after'] = $new_instance['link_after'];
		$instance['filter'] = !empty($new_instance['filter']) ? $new_instance['filter'] : 0;
		$instance['include_parent'] = !empty($new_instance['include_parent']) ? 1 : 0;
		$instance['post_parent'] = !empty( $new_instance['post_parent'] ) ? 1 : 0;
		$instance['description'] = !empty( $new_instance['description'] ) ? 1 : 0;
		$instance['dropdown'] = !empty($new_instance['dropdown']) ? 1 : 0;
		$instance['custom_widget_class'] = $new_instance['custom_widget_class'];
		$instance['start_depth'] = absint( $new_instance['start_depth'] );
		$instance['hide_title'] = !empty($new_instance['hide_title']) ? 1 : 0;

		return $instance;
	}

	function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		$nav_menu = isset( $instance['nav_menu'] ) ? $instance['nav_menu'] : '';
		$only_related = isset( $instance['only_related'] ) ? (int) $instance['only_related'] : 1;
		$depth = isset( $instance['depth'] ) ? (int) $instance['depth'] : 0;		
		$container = isset( $instance['container'] ) ? $instance['container'] : 'div';
		$container_id = isset( $instance['container_id'] ) ? $instance['container_id'] : '';
		$menu_class = isset( $instance['menu_class'] ) ? $instance['menu_class'] : 'menu';
		$before = isset( $instance['before'] ) ? $instance['before'] : '';
		$after = isset( $instance['after'] ) ? $instance['after'] : '';
		$link_before = isset( $instance['link_before'] ) ? $instance['link_before'] : '';
		$link_after = isset( $instance['link_after'] ) ? $instance['link_after'] : '';
		$filter_selection = isset( $instance['filter_selection'] ) ? (int) $instance['filter_selection'] : 0;
		$custom_widget_class = isset( $instance['custom_widget_class'] ) ? $instance['custom_widget_class'] : '';
		$start_depth = isset($instance['start_depth']) ? absint($instance['start_depth']) : 0;
				
		// Get menus
		$menus = get_terms( 'nav_menu', array( 'hide_empty' => false ) );

		// If no menus exists, direct the user to go and create some.
		if ( !$menus ) {
			echo '<p>'. sprintf( __('No menus have been created yet. <a href="%s">Create some</a>.'), admin_url('nav-menus.php') ) .'</p>';
			return;
		}
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:') ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $title; ?>" />
		</p>
		<p><input id="<?php echo $this->get_field_id('hide_title'); ?>" name="<?php echo $this->get_field_name('hide_title'); ?>" type="checkbox" <?php checked(isset($instance['hide_title']) ? $instance['hide_title'] : 0); ?> />&nbsp;<label for="<?php echo $this->get_field_id('hide_title'); ?>"><?php _e('Hide title if menu is empty'); ?></label>
		</p>		
		<p>
			<label for="<?php echo $this->get_field_id('custom_widget_class'); ?>"><?php _e('Custom Widget Class:') ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('custom_widget_class'); ?>" name="<?php echo $this->get_field_name('custom_widget_class'); ?>" value="<?php echo $custom_widget_class; ?>" />
		</p>				
		<p>
			<label for="<?php echo $this->get_field_id('nav_menu'); ?>"><?php _e('Select Menu:'); ?></label>
			<select id="<?php echo $this->get_field_id('nav_menu'); ?>" name="<?php echo $this->get_field_name('nav_menu'); ?>">
		<?php
			foreach ( $menus as $menu ) {
				$selected = $nav_menu == $menu->term_id ? ' selected="selected"' : '';
				echo '<option'. $selected .' value="'. $menu->term_id .'">'. $menu->name .'</option>';
			}
		?>
			</select>
		</p>
		<p><input id="<?php echo $this->get_field_id('dropdown'); ?>" name="<?php echo $this->get_field_name('dropdown'); ?>" type="checkbox" <?php checked(isset($instance['dropdown']) ? $instance['dropdown'] : 0); ?> />&nbsp;<label for="<?php echo $this->get_field_id('dropdown'); ?>"><?php _e('Show as dropdown'); ?></label>
		</p>
		<p>		
		<p><label for="<?php echo $this->get_field_id('only_related'); ?>"><?php _e('Show hierarchy:'); ?></label>
		<select name="<?php echo $this->get_field_name('only_related'); ?>" id="<?php echo $this->get_field_id('only_related'); ?>" class="widefat">
			<option value="1"<?php selected( $only_related, 1 ); ?>><?php _e('Display all'); ?></option>
			<option value="2"<?php selected( $only_related, 2 ); ?>><?php _e('Only related sub-items'); ?></option>
			<option value="3"<?php selected( $only_related, 3 ); ?>><?php _e( 'Only strictly related sub-items' ); ?></option>
		</select>
		</p>
		<p><label for="<?php echo $this->get_field_id('start_depth'); ?>"><?php _e('Starting depth:'); ?></label>
		<input id="<?php echo $this->get_field_id('start_depth'); ?>" name="<?php echo $this->get_field_name('start_depth'); ?>" type="text" value="<?php echo $start_depth; ?>" size="3" />
		</p>
		<p><label for="<?php echo $this->get_field_id('depth'); ?>"><?php _e('How many levels to display:'); ?></label>
		<select name="<?php echo $this->get_field_name('depth'); ?>" id="<?php echo $this->get_field_id('depth'); ?>" class="widefat">
			<option value="0"<?php selected( $depth, 0 ); ?>><?php _e('Unlimited depth'); ?></option>
			<option value="1"<?php selected( $depth, 1 ); ?>><?php _e( '1 level deep' ); ?></option>
			<option value="2"<?php selected( $depth, 2 ); ?>><?php _e( '2 levels deep' ); ?></option>
			<option value="3"<?php selected( $depth, 3 ); ?>><?php _e( '3 levels deep' ); ?></option>
			<option value="4"<?php selected( $depth, 4 ); ?>><?php _e( '4 levels deep' ); ?></option>
			<option value="5"<?php selected( $depth, 5 ); ?>><?php _e( '5 levels deep' ); ?></option>
			<option value="-1"<?php selected( $depth, -1 ); ?>><?php _e( 'Flat display' ); ?></option>
		</select>
		<p>
		<p><label for="<?php echo $this->get_field_id('filter_selection'); ?>"><?php _e('Filter selection from:'); ?></label>
		<select name="<?php echo $this->get_field_name('filter_selection'); ?>" id="<?php echo $this->get_field_id('filter_selection'); ?>" class="widefat">
		<option value="0"<?php selected( $only_related, 0 ); ?>><?php _e('Display all'); ?></option>
		<?php 
		$menu_id = ( $nav_menu ) ? $nav_menu : $menus[0]->term_id;
		$menu_items = wp_get_nav_menu_items($menu_id); 
		foreach ( $menu_items as $menu_item ) {
			echo '<option value="'.$menu_item->ID.'"'.selected( $filter_selection, $menu_item->ID ).'>'.$menu_item->title.'</option>';
		}
		?>		
		</select>
		</p>
		<p>Select the filter:</p>
		<p>
			<label for="<?php echo $this->get_field_id('filter'); ?>_0">
			<input id="<?php echo $this->get_field_id('filter'); ?>_0" name="<?php echo $this->get_field_name('filter'); ?>" type="radio" value="0" <?php checked( $instance['filter'] || empty($instance['filter']) ); ?> /> None
			</label><br />
            <label for="<?php echo $this->get_field_id('filter'); ?>_1">
            <input id="<?php echo $this->get_field_id('filter'); ?>_1" name="<?php echo $this->get_field_name('filter'); ?>" type="radio" value="1" <?php checked("1" , $instance['filter']); ?> /> Display direct path
			</label><br />
			<label for="<?php echo $this->get_field_id('filter'); ?>_2">
            <input id="<?php echo $this->get_field_id('filter'); ?>_2" name="<?php echo $this->get_field_name('filter'); ?>" type="radio" value="2" <?php checked("2" , $instance['filter']); ?> /> Display only children of selected item
			</label>
		</p>	
		<p><input id="<?php echo $this->get_field_id('include_parent'); ?>" name="<?php echo $this->get_field_name('include_parent'); ?>" type="checkbox" <?php checked(isset($instance['include_parent']) ? $instance['include_parent'] : 0); ?> />&nbsp;<label for="<?php echo $this->get_field_id('include_parent'); ?>"><?php _e('Include parents'); ?></label>
		</p>
		<p><input id="<?php echo $this->get_field_id('post_parent'); ?>" name="<?php echo $this->get_field_name('post_parent'); ?>" type="checkbox" <?php checked(isset($instance['post_parent']) ? $instance['post_parent'] : 0); ?> />&nbsp;<label for="<?php echo $this->get_field_id('post_parent'); ?>"><?php _e('Post related parents'); ?></label>
		</p>	
		<p><input id="<?php echo $this->get_field_id('description'); ?>" name="<?php echo $this->get_field_name('description'); ?>" type="checkbox" <?php checked(isset($instance['description']) ? $instance['description'] : 0); ?> />&nbsp;<label for="<?php echo $this->get_field_id('description'); ?>"><?php _e('Include descriptions'); ?></label>
		</p>			
		<p>
			<label for="<?php echo $this->get_field_id('container'); ?>"><?php _e('Container:') ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('container'); ?>" name="<?php echo $this->get_field_name('container'); ?>" value="<?php echo $container; ?>" />
			<small><?php _e( 'Whether to wrap the ul, and what to wrap it with.' ); ?></small>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('container_id'); ?>"><?php _e('Container ID:') ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('container_id'); ?>" name="<?php echo $this->get_field_name('container_id'); ?>" value="<?php echo $container_id; ?>" />
			<small><?php _e( 'The ID that is applied to the container.' ); ?></small>			
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('menu_class'); ?>"><?php _e('Menu Class:') ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('menu_class'); ?>" name="<?php echo $this->get_field_name('menu_class'); ?>" value="<?php echo $menu_class; ?>" />
			<small><?php _e( 'CSS class to use for the ul element which forms the menu.' ); ?></small>						
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('before'); ?>"><?php _e('Before the link:') ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('before'); ?>" name="<?php echo $this->get_field_name('before'); ?>" value="<?php echo $before; ?>" />
			<small><?php _e( htmlspecialchars('Output text before the <a> of the link.') ); ?></small>			
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('after'); ?>"><?php _e('After the link:') ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('after'); ?>" name="<?php echo $this->get_field_name('after'); ?>" value="<?php echo $after; ?>" />
			<small><?php _e( htmlspecialchars('Output text after the <a> of the link.') ); ?></small>						
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('link_before'); ?>"><?php _e('Before the link text:') ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('link_before'); ?>" name="<?php echo $this->get_field_name('link_before'); ?>" value="<?php echo $link_before; ?>" />
			<small><?php _e( 'Output text before the link text.' ); ?></small>			
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('link_after'); ?>"><?php _e('After the link text:') ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('link_after'); ?>" name="<?php echo $this->get_field_name('link_after'); ?>" value="<?php echo $link_after; ?>" />
			<small><?php _e( 'Output text after the link text.' ); ?></small>			
		</p>	
		<?php
	}
}

// shortcode => [advMenu id=N]
function advMenu_func( $atts ) {
	$instance =	shortcode_atts(array(
					'nav_menu' 				=> '',
					'title' 				=> '',
					'dropdown' 				=> '',
					'only_related' 			=> '',
					'depth' 				=> '',
					'container' 			=> '',
					'container_id' 			=> '',
					'menu_class'			=> '',
					'before' 				=> '',
					'after' 				=> '',
					'link_before' 			=> '',
					'link_after' 			=> '',
					'filter' 				=> '',	
					'filter_selection' 		=> '',	
					'include_parent' 		=> '',		
					'start_depth' 			=> '',
					'hide_title' 			=> '',
					'custom_widget_class' 	=> ''
				), $atts);

	ob_start();
	the_widget('Advanced_Menu_Widget', $instance, '' );
	$output = ob_get_contents();
  	ob_end_clean();

  	return $output;
}
add_shortcode( 'advMenu', 'advMenu_func' );
