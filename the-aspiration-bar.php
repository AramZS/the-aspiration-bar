<?php
/*                                                                                                                                                                                                                                                             
Plugin Name: The Aspiration Bar
Plugin URI: http://aramzs.me/wordpress
Description: A fun little WordPress toy 
Version: 1.11
Author: Aram Zucker-Scharff
Author URI: http://aramzs.me
Author Email: aramzs@hacktext.com
*/

//Set up some constants
define( 'TABAR_SLUG', 'tabar' );
define( 'TABAR_TITLE', 'Aspiration Bar' );
define( 'TABAR_MENU_SLUG', TABAR_SLUG . '-menu' );
define( 'TABAR_NOM_EDITOR', 'edit.php?post_type=aspiration' );
define( 'TABAR_NOM_POSTER', 'post-new.php?post_type=aspiration' );
define( 'TABAR_ROOT', dirname(__FILE__) );
define( 'TABAR_FILE_PATH', TABAR_ROOT . '/' . basename(__FILE__) );
define( 'TABAR_URL', plugins_url('/', __FILE__) );

	$wpver = get_bloginfo('version');
	$floatWPVer = floatval($wpver);

	//echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>';
	//Ref:WordPress Bible pg 90
	
	if ($floatWPVer >= 3.4){

		function tabar_jq_setup() {
				
				wp_enqueue_script('jquery');
				wp_enqueue_script('aspire-imp', TABAR_URL . 'includes/aspire-imp.js', array('jquery'));
		}

		add_action('wp_enqueue_scripts', 'tabar_jq_setup');
	} else {

		function tabar_jq_setup() {
		
						wp_dequeue_script( 'jquery' );
						wp_deregister_script( 'jquery' );
						wp_register_script('jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js', '', '');
						wp_enqueue_script('jquery');
						wp_enqueue_script('aspire-imp', TABAR_URL . 'includes/aspire-imp.js', array('jquery'));
		}
		add_action('wp_enqueue_scripts', 'tabar_jq_setup');
	}


	function create_tabar_post_type() {
		$args = array(
					'labels' => array(
										'name' => __( 'Aspirations' ),
										'singular_name' => __( 'Aspiration' ),
										'add_new' => __('Add New Aspiration'),
										'add_new_item' => __('Add New Aspiration'),
										'edit_item' => __('Edit Aspiration'),
										'new_item' => __('New Aspiration'),
										'all_items' => __('All Aspirations'),
										'view_item' => __('View Aspiration'),
										'search_items' => __('Search Aspirations'),
										'not_found' => __('No aspiration listed'),
										'not_found_in_trash' => __('No aspirations listed in Trash')
									),
					'description' => 'Aspirations',
					'public' => true,
					'show_ui' => true,
					'show_in_menu' => true,
					'show_in_admin_bar' => true,
					'capability_type' => 'post',
					'supports' => array('title', 'thumbnail'),
					'has_archive' => true
				);
		
		register_post_type('aspiration', $args);

	}
	
	add_action('init', 'create_tabar_post_type' );
	
	function tabarAjaxery(){
		// Verify nonce
		if ( !wp_verify_nonce( $_POST['_nonce'], 'aspiration') )
			die( __( "Nonce check failed. Please ensure you're supposed to be nominating stories.", 'aspiration' ) );
			
		$time = current_time('mysql', $gmt = 0); 
		
		$aspiration_entry = strip_tags(htmlspecialchars($_POST['aspiration-entry']));
		
		$data = array(
			'post_status' => 'publish',
			'post_type' => 'aspiration',
			//'post_date' => $time,		
			'post_title' => $aspiration_entry,//$item_title,
			
		);
		print_r($data); die();
		wp_insert_post( $data );
		
	}
	
		add_action( 'wp_ajax_nopriv_tabarAjaxery', 'tabarAjaxery' );
		add_action( 'wp_ajax_tabarAjaxery', 'tabarAjaxery' );	

class the_aspiration_bar extends WP_Widget {

	// Constructor //

		function the_aspiration_bar() {
			$widget_ops = array( 'classname' => 'the-aspiration-bar', 'description' => 'Displays aspirations.' ); // Widget Settings
			$control_ops = array( 'id_base' => 'the_aspiration_bar' ); // Widget Control Settings
			$this->WP_Widget( 'the_aspiration_bar', 'The Aspiration Bar', $widget_ops, $control_ops ); // Create the widget
		}

	// Extract Args //

		function widget($args, $instance) {
			extract( $args );
			$title 		= apply_filters('widget_title', $instance['title']); // the widget title
			$count	 	= $instance['count']; // the number of posts to show

	// Before widget //

			echo $before_widget;

	// Title of widget //

			if ( $title ) { echo $before_title . $title . $after_title; }

	// Widget output //

	// Using http://codex.wordpress.org/Template_Tags/wp_list_categories
	// Going to need to construct these things manually check wp-includes/category-template.php ln 412 
	// also http://codex.wordpress.org/Function_Reference/get_the_category and http://codex.wordpress.org/Function_Reference/get_category_link 
			?>
			
			<div class="the-aspiration-bar">
				<h3> Let's... </h3>
				<div class="tabar-entry">	
					<form>
						<?php wp_nonce_field('aspiration', TABAR_SLUG . '_nonce', false); ?>
						<input type="text" id="aspiration-entry" name="aspiration-entry" />
					</form>
				</div>
				<div class="tabar-loop">
					<?php
						$args = array( 'post_type' => 'aspiration', 'posts_per_page' => $count );
						$loop = new WP_Query( $args );
						while ( $loop->have_posts() ) : $loop->the_post();
							echo '<h5 class="aspiration-title">' . get_the_title() . '</h5>';
						endwhile;
					?>
				</div>
			</div>
		
			<?php
	// After widget //

			echo $after_widget;
			
		}

	// Update Settings //

		function update($new_instance, $old_instance) {
			$instance['title'] = strip_tags($new_instance['title']);
			$instance['count'] = strip_tags($new_instance['count']);
			return $instance;
		}

	// Widget Control Panel //

		function form($instance) {

		$defaults = array( 'title' => '', 'count' => 5 );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>'" type="text" value="<?php echo $instance['title']; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e('Number of aspirations to show.'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" type="text" value="<?php echo $instance['count']; ?>" />
		</p>

        <?php }

}
	

// End class the_aspiration_bar

add_action('widgets_init', create_function('', 'return register_widget("the_aspiration_bar");'));
?>