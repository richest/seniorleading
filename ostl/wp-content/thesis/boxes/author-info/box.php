<?php
/*
	Name: Author Info
	Author: ThesisAwesome.com
	Description: The Awesome author info box.
	Version: 1.4
	Class: ta_author_info
*/



class ta_author_info extends thesis_box {
	
	protected function translate() {
		$this->title = __('Author Info', 'thesisawesome');
	}
	
	protected function construct() {
		if (!is_admin()) {
			wp_enqueue_style('author-info', THESIS_USER_BOXES_URL . "/author-info/css/style.css");
		}
	}
	
	protected function options() {
		return array(
			'display' => array(
				'type' => 'checkbox',
					'label' => __('Display Options', 'thesisawesome'),
					'options' => array(
						'url' => __('Show Website URL', 'thesisawesome'),
						'twitter' => __('Show Twitter follow me button', 'thesisawesome'),
						'facebook' => __('Show Facebook profile link', 'thesisawesome'),
						'gplus' => __('Show Google+ profile link', 'thesisawesome')
					),
					'default' => array(
						'url' => true,
						'twitter' => true,
						'facebook' => true,
						'gplus' => true
					)
				)
			);
	}

	public function html() {
		global $thesis;
		// get options
		$options = $thesis->api->get_options($this->_get_options(), $this->options);
		
		// ------------------------------------------------------------------------
		// display the author info box on single post
		// ------------------------------------------------------------------------
		if (is_single()) {
		
		?>
		<div class="ta_author_info ta_box">
			<div class="ta_author_avatar">
				<?php echo get_avatar( get_the_author_meta('ID') , 80 ); ?>
			</div>
			<div class="ta_author_desc">
				<h4><a href="<?php echo get_author_posts_url(get_the_author_meta( 'ID' )); ?>" title="Posts by <? the_author_meta('display_name'); ?>" rel="author"><? the_author_meta('display_name'); ?></a></h4>
				<p><?php the_author_meta('description'); ?></p>
                <p class="ta_author_archive_url"><strong>View all contributions by <a href="<?php echo get_author_posts_url(get_the_author_meta( 'ID' )); ?>" title="Posts by <? the_author_meta('display_name'); ?>" rel="author"><? the_author_meta('display_name'); ?></a></strong></p>
                
                <?php if(get_the_author_meta('blog_title')){ ?>
						 <p class="ta_author_site_url">Website → <a href="<?php the_author_meta('user_url'); ?>" title="<?php the_author_meta('blog_title'); ?>" target="_blank"><?php the_author_meta('blog_title'); ?></a></p><?php } ?>
			</div>
			
            <div>
				<ul class="ta_author_social_profiles">
                    
                    <?php if(get_the_author_meta('twitter')){
	
						$fb_twitter_id = get_the_author_meta('twitter');
				
						if ($fb_twitter_id[0] == '@') {$fb_twitter = substr($fb_twitter_id, 1);} else {$fb_twitter = $fb_twitter_id;}
					?>
                    		<li class="twitter">
            					<a href="https://twitter.com/<?php echo $fb_twitter; ?>" class="twitter-follow-button">Follow @<?php echo $fb_twitter; ?></a>
                        		<script src="http://platform.twitter.com/widgets.js" type="text/javascript"></script>
							</li>
					<?php } ?> 

					<?php if(get_the_author_meta('facebook')){ ?>
                    	<li class="facebook">
            				<a rel="nofollow" href="<?php the_author_meta('facebook'); ?>" title="Facebook" target="_blank">Facebook</a>
						</li>
					<?php } ?>
            
					<?php if(get_the_author_meta('google_plus')){ ?>
						<li class="gplus">
            				<a href="<?php the_author_meta('google_plus'); ?>" title="Google+" target="_blank">Google+</a>
						</li>
					<?php } ?>
				</ul>
			</div>
			<div class="clr"></div>
		</div>
        <?php
        }
		
		// ------------------------------------------------------------------------
		// display the author info box on author archive page
		// ------------------------------------------------------------------------
		if (is_author()) {
			
			// get user data
			if(get_query_var('author_name')) :
				$curauth = get_user_by('slug', get_query_var('author_name'));
			else :
				$curauth = get_userdata(get_query_var('author'));
			endif;
			
			?>
		<div class="ta_author_info ta_box ta_archive">
			<div class="ta_author_archive_avatar">
				<?php echo get_avatar( $curauth->ID , 130 ); ?>
			</div>
			<div class="ta_author_desc">
				<h1><a href="<?php echo $curauth->user_url; ?>" title="Posts by <? echo $curauth->display_name; ?>" rel="author"><? echo $curauth->display_name; ?></a></h1>
				<p><?php echo $curauth->description; ?></p>
                
                <?php if( $curauth->blog_title && $options['display']['url'] ){ ?>
						 <p class="ta_author_site_url"><a href="<?php echo $curauth->user_url; ?>" title="<?php echo $curauth->blog_title; ?>" target="_blank"><?php echo $curauth->blog_title; ?>  →</a></p><?php } ?>
			</div>
			
            <div>
				<ul class="ta_author_social_profiles">
                    
                    <?php if( $curauth->twitter && $options['display']['twitter'] ){
	
						$fb_twitter_id = $curauth->twitter;
				
						if ( $fb_twitter_id[0] == '@') {$fb_twitter = substr($fb_twitter_id, 1);} else {$fb_twitter = $fb_twitter_id;}
					?>
                    		<li class="twitter">
            					<a href="https://twitter.com/<?php echo $curauth->twitter; ?>" class="twitter-follow-button" data-show-count="false">Follow @<?php echo $fb_twitter; ?></a>
                        		<script src="http://platform.twitter.com/widgets.js" type="text/javascript"></script>
							</li>
					<?php } ?> 

					<?php if( $curauth->facebook && $options['display']['facebook'] ){ ?>
                    	<li class="facebook">
            				<a rel="nofollow" href="<?php echo $curauth->facebook; ?>" title="Facebook" target="_blank">Facebook</a>
						</li>
					<?php } ?>
            
					<?php if( $curauth->google_plus && $options['display']['gplus'] ){ ?>
						<li class="gplus">
            				<a href="<?php echo $curauth->google_plus; ?>" title="Google+" target="_blank">Google+</a>
						</li>
					<?php } ?>
				</ul>
			</div>
			<div class="clr"></div>
		</div>
        <?php
        }
	}
}
	


	// ------------------------------------------------------------------------
	// filters
	// remove un-used profile fields, and add social profile fields
	// ------------------------------------------------------------------------
    add_filter('user_contactmethods','ttplus_profile_fields',10,1);
	add_filter('user_contactmethods','ttplus_add_contactmethods',10,1);
	
	
	// ------------------------------------------------------------------------
	// remove fields from profile
	// ------------------------------------------------------------------------
	function ttplus_profile_fields( $contactmethods ) {
		// remove Yahoo AIM
		if ( isset( $contactmethods['aim'] ) )
			unset($contactmethods['aim']);
		// remove Jabber / Google Talk
		if ( isset( $contactmethods['jabber'] ) )
			unset($contactmethods['jabber']);
		// remove Yahoo IM
		if ( isset( $contactmethods['yim'] ) )
		unset($contactmethods['yim']);
		
		return $contactmethods;
	}
	
    
	// ------------------------------------------------------------------------
	// add social profile fields
	// ------------------------------------------------------------------------
	function ttplus_add_contactmethods( $contactmethods ) {
		// Add blog title field
		if ( !isset( $contactmethods['blog_title'] ) )
		$contactmethods['blog_title'] = 'Blog Title';
		// Add Google profile
		if ( !isset( $contactmethods['google_plus'] ) )
		$contactmethods['google_plus'] = 'Google+ URL';
		// Add Twitter
		if ( !isset( $contactmethods['twitter'] ) )
		$contactmethods['twitter'] = 'Twitter ID';
		//add Facebook
		if ( !isset( $contactmethods['facebook'] ) )
		$contactmethods['facebook'] = 'Facebook Profile URL';

		return $contactmethods;
	}
