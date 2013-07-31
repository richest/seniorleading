<?php
/*
Plugin Name: Loan Officers
Plugin URI: http://www.julianjosephs.com
Description: Declares a plugin that will create a custom post type displaying loan officers and loan teams.
Version: 1.0
Author: Julian Josephs
Author URI: http://www.julianjosephs.com
License: GPLv2
*/

add_action( 'init', 'create_loan_officers' );

function create_loan_officers() {
    register_post_type( 'loan_officers',
        array(
            'labels' => array(
                'name' => 'Loan Officers',
                'singular_name' => 'Loan Officer',
                'add_new' => 'Add New',
                'add_new_item' => 'Add New Loan Officer',
                'edit' => 'Edit',
                'edit_item' => 'Edit Loan Officer',
                'new_item' => 'New Loan Officer',
                'view' => 'View',
                'view_item' => 'View Loan Officer',
                'search_items' => 'Search Loan Officers',
                'not_found' => 'No Loan Officers found',
                'not_found_in_trash' => 'No Loan Officers found in Trash',
                'parent' => 'Parent Loan Officer'
            ),
 
            'public' => true,
            'menu_position' => 15,
            'supports' => array( 'title', 'editor', 'comments', 'thumbnail', 'excerpt' ),
            'taxonomies' => array( '' ),
            'menu_icon' => plugins_url( 'images/image.png', __FILE__ ),
            'has_archive' => true
        )
    );
	
	flush_rewrite_rules();
}



add_action( 'admin_init', 'my_admin' );

function my_admin() {
    add_meta_box( 'loan_officer_meta_box',
        'Loan Officer Details',
        'display_loan_officer_meta_box',
        'loan_officers', 'side', 'default'
    );
}

function display_loan_officer_meta_box( $loan_officer ) {
    // Retrieve current name of the Director and Movie Rating based on review ID
    $loan_officer_team_lead = esc_html( get_post_meta( $loan_officer->ID, 'loan_officer_team_lead', true ) );
    $loan_officer_phone_number = esc_html( get_post_meta( $loan_officer->ID, 'loan_officer_phone_number', true ) );
	$loan_officer_email = esc_html( get_post_meta( $loan_officer->ID, 'loan_officer_email', true ) );
	$loan_officer_apply_now_link = esc_html( get_post_meta( $loan_officer->ID, 'loan_officer_apply_now_link', true ) );
	$loan_officer_pre_qual_link = esc_html( get_post_meta( $loan_officer->ID, 'loan_officer_pre_qual_link', true ) );
	$loan_officer_rate_quote_link = esc_html( get_post_meta( $loan_officer->ID, 'loan_officer_rate_quote_link', true ) );
	$loan_officer_loan_stat_link = esc_html( get_post_meta( $loan_officer->ID, 'loan_officer_loan_stat_link', true ) );
	$loan_officer_email_me_link = esc_html( get_post_meta( $loan_officer->ID, 'loan_officer_email_me_link', true ) );
    ?>
    <table>
		<tr>
            <td style="width: 150px">TEAM LEAD POST</td>
            <td>
                <select style="width: 100px" name="loan_officer_team_lead">
                <?php
                // Generate all items of drop-down list
                for ( $item = 0; $item <= 1; $item ++ ) {
                ?>
                    <option value="<?php echo $item; ?>" <?php echo selected( $item, $loan_officer_team_lead ); ?>>
                    <?php echo ($item == 1? 'YES' : 'NO'); ?> <?php } ?>
                </select>
            </td>
        </tr>
        <tr>
            <td style="width: 100%">Phone Number</td>
            <td><input type="text" size="30" name="loan_officer_phone_number" value="<?php echo $loan_officer_phone_number; ?>" /></td>
        </tr>
        <tr>
            <td style="width: 100%">Email</td>
            <td><input type="text" size="30" name="loan_officer_email" value="<?php echo $loan_officer_email; ?>" /></td>
        </tr>
        <tr>
            <td style="width: 100%">Apply Now (Link)</td>
            <td><input type="text" size="30" name="loan_officer_apply_now_link" value="<?php echo $loan_officer_apply_now_link; ?>" /></td>
        </tr>
        <tr>
            <td style="width: 100%">Pre-Qualification (Link)</td>
            <td><input type="text" size="30" name="loan_officer_pre_qual_link" value="<?php echo $loan_officer_pre_qual_link; ?>" /></td>
        </tr>
        <tr>
            <td style="width: 100%">Rate Quote (Link)</td>
            <td><input type="text" size="30" name="loan_officer_rate_quote_link" value="<?php echo $loan_officer_rate_quote_link; ?>" /></td>
        </tr>
        <tr>
            <td style="width: 100%">Loan Status (Link)</td>
            <td><input type="text" size="30" name="loan_officer_loan_stat_link" value="<?php echo $loan_officer_loan_stat_link; ?>" /></td>
        </tr>
        <tr>
            <td style="width: 100%">Email Me (Link)</td>
            <td><input type="text" size="30" name="loan_officer_email_me_link" value="<?php echo $loan_officer_email_me_link; ?>" /></td>
        </tr>
    </table>
    <?php
}



add_action( 'save_post', 'add_loan_officer_fields', 10, 2 );

function add_loan_officer_fields( $loan_officer_id, $loan_officer ) {
    // Check post type for movie reviews
    if ( $loan_officer->post_type == 'loan_officers' ) {
        // Store data in post meta table if present in post data
		if ( isset( $_POST['loan_officer_team_lead'] ) && $_POST['loan_officer_team_lead'] != '' ) {
            update_post_meta( $loan_officer_id, 'loan_officer_team_lead', $_POST['loan_officer_team_lead'] );
        }
        if ( isset( $_POST['loan_officer_phone_number'] ) && $_POST['loan_officer_phone_number'] != '' ) {
            update_post_meta( $loan_officer_id, 'loan_officer_phone_number', $_POST['loan_officer_phone_number'] );
        }
        if ( isset( $_POST['loan_officer_email'] ) && $_POST['loan_officer_email'] != '' ) {
            update_post_meta( $loan_officer_id, 'loan_officer_email', $_POST['loan_officer_email'] );
        }
        if ( isset( $_POST['loan_officer_apply_now_link'] ) && $_POST['loan_officer_apply_now_link'] != '' ) {
            update_post_meta( $loan_officer_id, 'loan_officer_apply_now_link', $_POST['loan_officer_apply_now_link'] );
        }
        if ( isset( $_POST['loan_officer_pre_qual_link'] ) && $_POST['loan_officer_pre_qual_link'] != '' ) {
            update_post_meta( $loan_officer_id, 'loan_officer_pre_qual_link', $_POST['loan_officer_pre_qual_link'] );
        }
        if ( isset( $_POST['loan_officer_rate_quote_link'] ) && $_POST['loan_officer_rate_quote_link'] != '' ) {
            update_post_meta( $loan_officer_id, 'loan_officer_rate_quote_link', $_POST['loan_officer_rate_quote_link'] );
        }
        if ( isset( $_POST['loan_officer_loan_stat_link'] ) && $_POST['loan_officer_loan_stat_link'] != '' ) {
            update_post_meta( $loan_officer_id, 'loan_officer_loan_stat_link', $_POST['loan_officer_loan_stat_link'] );
        }
        if ( isset( $_POST['loan_officer_email_me_link'] ) && $_POST['loan_officer_email_me_link'] != '' ) {
            update_post_meta( $loan_officer_id, 'loan_officer_email_me_link', $_POST['loan_officer_email_me_link'] );
        }
    }
}




add_action( 'init', 'create_my_taxonomies', 0 );

function create_my_taxonomies() {
    register_taxonomy(
        'loan_officers_state',
        'loan_officers',
        array(
            'labels' => array(
                'name' => 'US State',
                'add_new_item' => 'Add New State',
                'new_item_name' => "New Movie State"
            ),
            'show_ui' => true,
            'show_tagcloud' => false,
            'hierarchical' => true,
			'rewrite' => array( 'hierarchical' => true, 'slug' => 'meet-your-loan-officer/state' )
        )
    );
	
	register_taxonomy(
        'loan_officers_loan_team',
        'loan_officers',
        array(
            'labels' => array(
                'name' => 'Loan Team',
                'add_new_item' => 'Add New Loan Team',
                'new_item_name' => "New Loan Team"
            ),
            'show_ui' => true,
            'show_tagcloud' => false,
            'hierarchical' => true,
			'rewrite' => array( 'hierarchical' => true, 'slug' => 'meet-your-loan-officer/team' )
        )
    );
	
	$state_list = array('alabama'=>"Alabama",  
			'alaska'=>"Alaska",  
			'arizona'=>"Arizona",  
			'arkansas'=>"Arkansas",  
			'california'=>"California",  
			'colorado'=>"Colorado",  
			'connecticut'=>"Connecticut",  
			'delaware'=>"Delaware",  
			'dc'=>"District Of Columbia",  
			'florida'=>"Florida",  
			'georgia'=>"Georgia",  
			'hawaii'=>"Hawaii",  
			'idaho'=>"Idaho",  
			'illinois'=>"Illinois",  
			'indiana'=>"Indiana",  
			'iowa'=>"Iowa",  
			'kansas'=>"Kansas",  
			'kentucky'=>"Kentucky",  
			'louisiana'=>"Louisiana",  
			'maine'=>"Maine",  
			'maryland'=>"Maryland",  
			'massachusetts'=>"Massachusetts",  
			'michigan'=>"Michigan",  
			'minnesota'=>"Minnesota",  
			'mississippi'=>"Mississippi",  
			'missouri'=>"Missouri",  
			'montana'=>"Montana",
			'nebraska'=>"Nebraska",
			'nevada'=>"Nevada",
			'new-hampshire'=>"New Hampshire",
			'new-jersey'=>"New Jersey",
			'new-mexico'=>"New Mexico",
			'new-york'=>"New York",
			'north-carolina'=>"North Carolina",
			'north-dakota'=>"North Dakota",
			'ohio'=>"Ohio",  
			'oklahoma'=>"Oklahoma",  
			'oregon'=>"Oregon",  
			'pennsylvania'=>"Pennsylvania",  
			'rhode-island'=>"Rhode Island",  
			'south-carolina'=>"South Carolina",  
			'south-dakota'=>"South Dakota",
			'tennessee'=>"Tennessee",  
			'texas'=>"Texas",  
			'utah'=>"Utah",  
			'vermont'=>"Vermont",  
			'virginia'=>"Virginia",  
			'washington'=>"Washington",  
			'west-virginia'=>"West Virginia",  
			'wisconsin'=>"Wisconsin",  
			'wyoming'=>"Wyoming");
	
	foreach($state_list as $slug => $term ){
		$parent_term = term_exists( $term, 'loan_officers_state' ); // array is returned if taxonomy is given
		$parent_term_id = $parent_term['term_id']; // get numeric term id
		
		if($parent_term == 0){
			wp_insert_term(
			  $term, // the term 
			  'loan_officers_state', // the taxonomy
			  array(
				'slug' => $slug,
				'parent'=> $parent_term_id
			  )
			);
		}
	}
	
}





add_filter( 'manage_edit-loan_officers_columns', 'my_columns' );

function my_columns( $columns ) {
    $columns['loan_officer_phone_number'] = 'Phone';
    $columns['loan_officer_email'] = 'Email';
    unset( $columns['comments'] );
    return $columns;
}




add_action( 'manage_posts_custom_column', 'populate_columns' );

function populate_columns( $column ) {
    if ( 'loan_officer_phone_number' == $column ) {
        $loan_officer_phone_number = esc_html( get_post_meta( get_the_ID(), 'loan_officer_phone_number', true ) );
        echo $loan_officer_phone_number;
    }
    elseif ( 'loan_officer_email' == $column ) {
        $loan_officer_email = get_post_meta( get_the_ID(), 'loan_officer_email', true );
        echo $loan_officer_email;
    }
}




add_filter( 'manage_edit-loan_officers_sortable_columns', 'sort_me' );

function sort_me( $columns ) {
    $columns['loan_officer_phone_number'] = 'loan_officer_phone_number';
    $columns['loan_officer_email'] = 'loan_officer_email';
 
    return $columns;
}




add_filter( 'request', 'column_orderby' );
 
function column_orderby ( $vars ) {
    if ( !is_admin() )
        return $vars;
    if ( isset( $vars['orderby'] ) && 'loan_officer_phone_number' == $vars['orderby'] ) {
        $vars = array_merge( $vars, array( 'meta_key' => 'loan_officer_phone_number', 'orderby' => 'meta_value' ) );
    }
    elseif ( isset( $vars['orderby'] ) && 'loan_officer_email' == $vars['orderby'] ) {
        $vars = array_merge( $vars, array( 'meta_key' => 'loan_officer_email', 'orderby' => 'meta_value' ) );
    }
    return $vars;
}



add_action( 'restrict_manage_posts', 'my_filter_list' );

function my_filter_list() {
    $screen = get_current_screen();
    global $wp_query;
    if ( $screen->post_type == 'loan_officers' ) {
        wp_dropdown_categories( array(
            'show_option_all' => 'Show All States',
            'taxonomy' => 'loan_officers_state',
            'name' => 'loan_officers_state',
            'orderby' => 'name',
            'selected' => ( isset( $wp_query->query['loan_officers_state'] ) ? $wp_query->query['loan_officers_state'] : '' ),
            'hierarchical' => false,
            'depth' => 3,
            'show_count' => false,
            'hide_empty' => true,
        ) );
		
		wp_dropdown_categories( array(
            'show_option_all' => 'Show All Loan Teams',
            'taxonomy' => 'loan_officers_loan_team',
            'name' => 'loan_officers_loan_team',
            'orderby' => 'name',
            'selected' => ( isset( $wp_query->query['loan_officers_loan_team'] ) ? $wp_query->query['loan_officers_loan_team'] : '' ),
            'hierarchical' => false,
            'depth' => 3,
            'show_count' => false,
            'hide_empty' => true,
        ) );
    }
}




add_filter( 'parse_query','perform_filtering' );

function perform_filtering( $query ) {
    $qv = &$query->query_vars;
    if ( ( $qv['loan_officers_state'] ) && is_numeric( $qv['loan_officers_state'] ) ) {
        $term = get_term_by( 'id', $qv['loan_officers_state'], 'loan_officers_state' );
        $qv['loan_officers_state'] = $term->slug;
    }
	
	if ( ( $qv['loan_officers_loan_team'] ) && is_numeric( $qv['loan_officers_loan_team'] ) ) {
        $term = get_term_by( 'id', $qv['loan_officers_loan_team'], 'loan_officers_loan_team' );
        $qv['loan_officers_loan_team'] = $term->slug;
    }
}





add_filter( 'template_include', 'include_template_function', 1 );

function include_template_function( $template_path ) {
    if ( get_post_type() == 'loan_officers' ) {
        if ( is_single() ) {
            // checks if the file exists in the theme first,
            // otherwise serve the file from the plugin
            if ( $theme_file = locate_template( array ( 'single-loan_officers.php' ) ) ) {
                $template_path = $theme_file;
            } else {
                $template_path = plugin_dir_path( __FILE__ ) . '/single-loan_officers.php';
            }
        }
    }
    return $template_path;
}




add_action( 'wp_enqueue_scripts', 'team_list_modal' );

function team_list_modal() {
	wp_enqueue_script(
		'custom-script',
		plugins_url( 'js/jquery.simplemodal.1.4.4.min.js' , __FILE__ ),
		array( 'jquery' )
	);
}


?>