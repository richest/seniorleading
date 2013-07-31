<?php
/*
	This file is for skin specific customizations. Be careful not to change your skin's skin.php file as that will be upgraded in the future and your work will be lost.
	If you are more comfortabe with PHP, we reccomend using the super powerful Thesis Box system to create elements that you can interact with in the Thesis HTML Editor.
*/

function sButton($atts, $content = null) {
   extract(shortcode_atts(array('link' => '#'), $atts));
   return '<a class="about-cta" href="'.$link.'"><span>' . do_shortcode($content) . '</span></a>';
}
add_shortcode('button', 'sButton');


function redButton($atts, $content = null) {
   extract(shortcode_atts(array('link' => '#'), $atts));
   return '<a class="red-cta" href="'.$link.'"><span>' . do_shortcode($content) . '</span></a>';
}
add_shortcode('button_red', 'redButton');



function stepButton($atts, $content = null) {
   extract(shortcode_atts(array('link' => '#'), $atts));
   return '<a class="step-button" href="'.$link.'"><span>' . do_shortcode($content) . '</span></a>';
}
add_shortcode('step_button', 'stepButton');




function home_readmore(){
	echo '<a class="read-more" href="'. get_permalink( get_the_ID() ) . '">Read Full story</a>';
}
add_action('thesis_hook_post_box_home_post_box_bottom','home_readmore');

function new_excerpt_more( $more ) {
	return ' ';
}
add_filter('excerpt_more', 'new_excerpt_more');


function state_listing() {

    if (!empty($_GET['state']) ) {
    	$lo_state = $_GET['state'];
    }
    else{
     	echo 'state empty';    
    }
    
    $args = array(
	'posts_per_page'  => 20,
	'offset'          => 0,
	'category'        => '',
	'orderby'         => 'post_date',
	'order'           => 'DESC',
	'include'         => '',
	'exclude'         => '',
	'meta_key'        => '',
	'meta_value'      => '',
	'post_type'       => 'loan_officers',
	'post_mime_type'  => '',
	'post_parent'     => '',
	'post_status'     => 'publish',
	'loan_officers_state' => $lo_state,
	'suppress_filters' => true );
    
    $posts_array = get_posts( $args );
    
    if(!$posts_array){
    	echo 'Sorry, there are no Loan Officers in this state!';
    }
    
    foreach($posts_array as $post){

	$featured_image_url = get_the_post_thumbnail($post->ID, array(100,77)); 
	$excerpt = ($post->post_excerpt != '' ? $post->post_excerpt : '<p></p>');
	
	$terms = get_the_terms( $post->ID, 'loan_officers_state' );
	
	if ( $terms && ! is_wp_error( $terms ) ){

		$state_links = array();

		foreach ( $terms as $term ) {
			$state_links [] = $term->name;
		}
						
		$list_of_states = join( ", ", $state_links );
	
	}else{
		$list_of_states = 'No states found'; 
	}
	
	
	
	$terms_team = get_the_terms( $post->ID, 'loan_officers_loan_team' );
	
	if ( $terms_team && ! is_wp_error( $terms_team ) ){

		$term_links = array();

		foreach ( $terms_team as $term ) {
			$term_links [] = $term->slug;
		}
						
		$list_of_teams = join( ", ", $term_links );
	
	}else{
		$list_of_teams = 'No teams found'; 
	}
	
	
	$show_person = 'yes';
	
	if( $list_of_teams != 'No teams found' ){
		$show_person = 'no';
		
		if( $post->loan_officer_team_lead == 1 ){
			$show_person = 'yes';
		}
	}
	
	if($list_of_teams == 'No teams found'){
		$link = '<h3 class="headline"><a href="loan-officer?id=' . $post->ID . '">' . $post->post_title . '</a></h3>';
		$link2 = 'loan-officer?id=' . $post->ID;
	}else{
		$link = '<h3 class="headline"><a href="team-listing?team=' . $list_of_teams . '">' . $post->post_title . '</a></h3>';
		$link2 = 'team-listing?team=' . $list_of_teams;
	}
    
    	if( $show_person == 'yes' ){
    	echo '<div class="query_box state-list">';
	    	echo '<a class="thumb_link" href="http://previewyournewwebsite.info/otsl/loan_officers/john-doe" title="click to read"></a><div class="loan-officer-thumb">';
	    	
	    		echo $featured_image_url;
	    		
	    		if($post->loan_officer_team_lead == 1){
		    		echo '<p class="lo-position">';
		    		echo 'internal loan officer';
		    	}else{
		    		echo '<p class="lo-position external">';
		    		echo 'external loan officer';
		    	}
		    	echo '</p>';
	    	echo '</div>';
	    	
	    	echo '<div class="state-list-body">';
	    		echo $link;
	    		echo '<div class="post_excerpt">';
	    			echo $excerpt;
	    		echo '</div>';
	    		
	    		echo '<div class="post_cats">';
	    			echo '<span class="title_of_cat">TERRITORIES:</span>';
	    			echo '<p></p>';
	    			echo '<span class="list_of_cats">' . $list_of_states . '</span>';
	    		echo '</div>';
	    		
	    		echo '<div class="team-info-btn-container">';
				echo '<a href="' . $link2 . '" class="team-info-btn"></a>';
			echo '</div>';
	    	echo '</div>';
    	echo '</div>';
    	}
    }
    
}

add_action('thesis_hook_container_add_state_list_bottom', 'state_listing');




function state_listing_head() {

	if (!empty($_GET['state']) ) {
    		$lo_state = $_GET['state'];
	}
	else{
		echo 'state empty';    
	}

	//get_term_by( $field, $value, $taxonomy, $output, $filter );
	$state_taxonomy = get_term_by('slug', $lo_state, 'loan_officers_state');
	
	echo '<h2 class="headline">Loan Officers from ' . $state_taxonomy->name . '</h2>';
	echo '<div class="post_content">';
		echo '<p>' . $state_taxonomy->description . '</p>';
	echo '</div>';
}

add_action('thesis_hook_container_add_state_head_bottom', 'state_listing_head');





function team_listing_head() {

	if (!empty($_GET['team']) ) {
    		$lo_team = $_GET['team'];
	}
	else{
		echo 'team empty';    
	}

	//get_term_by( $field, $value, $taxonomy, $output, $filter );
	$team_taxonomy = get_term_by('slug', $lo_team, 'loan_officers_loan_team');
	
	
	$args = array(
	'name' => $lo_team,
	'posts_per_page'  => 20,
	'offset'          => 0,
	'category'        => '',
	'orderby'         => 'post_date',
	'order'           => 'DESC',
	'include'         => '',
	'exclude'         => '',
	'meta_key'        => '',
	'meta_value'      => '',
	'post_type'       => 'loan_officers',
	'post_mime_type'  => '',
	'post_parent'     => '',
	'post_status'     => 'publish',
	'suppress_filters' => true );
    
	$posts_array = get_posts( $args );
	
	if(!$posts_array){
		echo 'Sorry, this team does not exist!';
	}else{
	
		foreach($posts_array as $post){
			$terms = get_the_terms( $post->ID, 'loan_officers_state' );
		
			if ( $terms && ! is_wp_error( $terms ) ){
		
				$state_links = array();
		
				foreach ( $terms as $term ) {
					$state_links [] = $term->name;
				}
								
				$list_of_states = join( ", ", $state_links );
			
			}else{
				$list_of_states = 'No states found'; 
			}
			
			$featured_image_url = get_the_post_thumbnail($post->ID, array(101,178)); 
			$description = $post->post_content;
		}
	}
	
	
	
	echo '<div class="team-feature-image">';
		echo $featured_image_url;
	
		echo '<span class="team-position">internal loan officer</span>';
	echo '</div>';
	
	echo '<div class="lead_page">';
		echo '<div class="lead-page-text-state">';
			echo '<div class="text_box">';
				echo '<div class="query_box lead-page-text-state team-list">';
					echo '<h2 class="headline">' . $team_taxonomy->name . '</h2>';
					echo '<div class="post_excerpt">';
						echo $team_taxonomy->description;
					echo '</div>';
					echo '<div class="post_cats">';
						echo '<span class="title_of_cat">TERRITORIES:</span>';
						echo '<p><span class="list_of_cats">' . $list_of_states . '</span></p>';
					echo '</div>';
				echo '</div>';
			echo '</div>';
		echo '</div>';
	echo '</div>';
	
	echo '<div class="post_content">';
	echo '<p>' . $description .'</p>';
	echo '</div>';
	
	
	
	$args = array(
	'posts_per_page'  => 20,
	'offset'          => 0,
	'category'        => '',
	'orderby'         => 'post_date',
	'order'           => 'DESC',
	'include'         => '',
	'exclude'         => '',
	'meta_key'        => '',
	'meta_value'      => '',
	'post_type'       => 'loan_officers',
	'post_mime_type'  => '',
	'post_parent'     => '',
	'post_status'     => 'publish',
	'loan_officers_loan_team' => $lo_team,
	'suppress_filters' => true );
    
	$posts_array = get_posts( $args );
	
	if(!$posts_array){
		echo 'Sorry, there are no Loan Officers in this team!';
	}
	
	echo '<div class="text_box">';
		foreach($posts_array as $post){
			if( $post->loan_officer_team_lead == 0 ){
			
			$excerpt = ($post->post_excerpt != '' ? $post->post_excerpt : '<p></p>');
			$featured_image_url = get_the_post_thumbnail($post->ID, array(100,100));
			
			echo '<div class="query_box state-list team-list">';
				echo '<a class="thumb_link" href="http://previewyournewwebsite.info/otsl/loan_officers/john-doe" title="click to read"></a>';
				echo '<div class="loan-officer-thumb">';
					echo '<p>';
						echo $featured_image_url;
					echo '</p>';
				echo '</div>';
				
				echo '<div class="state-list-body">';
					echo '<h3 class="headline">' . $post->post_title . '</h3>';
					echo '<div class="post_excerpt">';
						echo $excerpt;
					echo '</div>';
					echo '<div>';
						echo '<a href="#" class="team-info-btn"></a>';
					echo '</div>';
					
					echo '<p></p>';
				echo '</div>';
			echo '</div>';	
			}
		}	
	echo '</div>';
	
}

add_action('thesis_hook_container_add_team_head_top', 'team_listing_head');





function loan_officer_head() {

	if (!empty($_GET['id']) ) {
    		$p_id = $_GET['id'];
	}
	else{
		echo 'No ID found';    
	}


	$args = array(
	'name' 		  => $p_id,
	'posts_per_page'  => 20,
	'offset'          => 0,
	'category'        => '',
	'orderby'         => 'post_date',
	'order'           => 'DESC',
	'include'         => '',
	'exclude'         => '',
	'meta_key'        => '',
	'meta_value'      => '',
	'post_type'       => 'loan_officers',
	'post_mime_type'  => '',
	'post_parent'     => '',
	'post_status'     => 'publish',
	'suppress_filters' => true );
    
	$post = get_post( $p_id );
	
	if(!$post){
		echo 'Sorry, there are no Loan Officers in this team!';
	}
	
	
		
	$featured_image_url = get_the_post_thumbnail($post->ID, array(101,178)); 
	$description = do_shortcode($post->post_content);
	$lo_title = $post->post_title;
	$lo_excerpt = $post->post_excerpt;
	
	
	$terms = get_the_terms( $post->ID, 'loan_officers_state' );
	
	if ( $terms && ! is_wp_error( $terms ) ){

		$state_links = array();

		foreach ( $terms as $term ) {
			$state_links [] = $term->name;
		}
						
		$list_of_states = join( ", ", $state_links );
	
	}else{
		$list_of_states = 'No states found'; 
	}
	
	
	echo '<div class="team-feature-image">';
		echo $featured_image_url;
	
		echo '<span class="team-position external">external loan officer</span>';
	echo '</div>';
	
	echo '<div class="lead_page">';
		echo '<div class="lead-page-text-state">';
			echo '<div class="text_box">';
				echo '<div class="query_box lead-page-text-state team-list">';
					echo '<h2 class="headline">' . $lo_title . '</h2>';
					echo '<div class="post_excerpt">';
						echo $lo_excerpt;
					echo '</div>';
					
					echo '<div class="post_cats"><span class="title_of_cat">TERRITORIES:</span>';
						echo '<p><span class="list_of_cats">' . $list_of_states .'</span></p>';
					echo '</div>';
				echo '</div>';
			echo '</div>';
		echo '</div>';
	echo '</div>';
	
	echo '<div class="post_content">';
		echo apply_filters('the_content', $description);
	echo '</div>';
	
}

add_action('thesis_hook_container_add_loan_officer_head_top', 'loan_officer_head');





function lo_contact_info() {

	$show = 'no';

	if (!empty($_GET['id']) ) {
    		$lo_id = $_GET['id'];
    		
    		$post = get_post( $lo_id );
    		
    		$loan_officer_phone_number = $post->loan_officer_phone_number; 
    		$loan_officer_email = $post->loan_officer_email; 
    		$loan_officer_apply_now_link = $post->loan_officer_apply_now_link; 
    		$loan_officer_pre_qual_link = $post->loan_officer_pre_qual_link; 
    		$loan_officer_rate_quote_link = $post->loan_officer_rate_quote_link; 
    		$loan_officer_loan_stat_link = $post->loan_officer_loan_stat_link; 
    		$loan_officer_email_me_link = $post->loan_officer_email_me_link; 
    		
    		$show = 'yes';
    		
	}elseif (!empty($_GET['team']) ) {
    		$lo_id = $_GET['team'];
    		
    		$args=array(
  			'name' => $lo_id,
			'post_type' => 'loan_officers',
			'post_status' => 'publish',
			'posts_per_page' => 1
  		);
  		
  		$my_posts = get_posts($args);
  		
  		$post = get_post( $my_posts[0]->ID );
  		
  		
    		$loan_officer_phone_number = $post->loan_officer_phone_number; 
    		$loan_officer_email = $post->loan_officer_email; 
    		$loan_officer_apply_now_link = $post->loan_officer_apply_now_link; 
    		$loan_officer_pre_qual_link = $post->loan_officer_pre_qual_link; 
    		$loan_officer_rate_quote_link = $post->loan_officer_rate_quote_link; 
    		$loan_officer_loan_stat_link = $post->loan_officer_loan_stat_link; 
    		$loan_officer_email_me_link = $post->loan_officer_email_me_link;
  		
  		$show = 'yes';
	}

	if ( $show == 'yes'){
		echo '<div class="loan-contact-details">';
			echo '<div class="loan_officer_phone_number">Call: <a href="#">' . $loan_officer_phone_number . '</a></div>';
			echo '<div class="loan_officer_email">Email: <a href="mailto:' . $loan_officer_email . '">' . $loan_officer_email . '</a></div>';
			echo '<div class="loan_officer_apply_now_link"><a href="' . $loan_officer_apply_now_link . '">APPLY NOW!</a></div>';
			echo '<div class="loan_officer_pre_qual_link"><a href="' . $loan_officer_pre_qual_link . '">Pre-Qualification</a></div>';
			echo '<div class="loan_officer_rate_quote_link"><a href="' . $loan_officer_rate_quote_link . '">Rate Quote</a></div>';
			echo '<div class="loan_officer_loan_stat_link"><a href="' . $loan_officer_loan_stat_link . '">Loan Status</a></div>';
			echo '<div class="loan_officer_email_me_link">Need to talk to me first? <a href="'. $loan_officer_email_me_link . '">Email Me</a></div>';
		echo '</div>';
	}
}

add_action('thesis_hook_container_loan_officer_contact_info_top', 'lo_contact_info');



function theme_sidebar() {
	get_sidebar();
}


add_action('thesis_hook_container_theme_sidebar_top', 'theme_sidebar');