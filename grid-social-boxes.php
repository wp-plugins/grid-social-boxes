<?php
/**
 * Plugin Name: Grid Social Boxes
 * Plugin URI: https://github.com/palasthotel/wordpress-grid-box-social
 * Description: Some social network boxes. Facebook and Twitter for now.
 * Version: 1.3.2
 * Author: Palasthotel <rezeption@palasthotel.de> (in person: Benjamin Birkenhake, Edward Bock, Enno Welbers)
 * Author URI: http://www.palasthotel.de
 * License: http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 * @copyright Copyright (c) 2014, Palasthotel
 * @package Palasthotel\Grid-WordPress-Box-Social
 */



class Grid_Social_Boxes{
	/**
	 * init grid boxes
	 * register actions and filters
	 */
	function __construct(){
		add_action("grid_load_classes", array($this,"load_classes") );
		add_filter("grid_templates_paths", array($this,"template_paths") );
		add_action( 'admin_menu', array($this, 'social_boxes_admin_menu') );
	}

	/**
	 * load grid box classes
	 */
	public function load_classes(){
		/**
		 * twitter box
		 */
		$this->social_boxes_include_twitter_api();
		require( 'grid_twitterbox/grid_wp_twitterboxes.php' );
		/**
		 * facebook box
		 */
		require( 'grid_facebook_like_box/grid_fb_like_box_box.php' );

	}

	/**
	 * add grid templates suggestion path
	 * @param $paths
	 * @return arraya
	 */
	public function template_paths($paths){
		$paths[] = dirname(__FILE__)."/templates";
		return $paths;
	}

	/**
	 * register admin menu paths
	 */
	public function social_boxes_admin_menu() {
		add_submenu_page( 'options-general.php', 'Grid Social Boxes', 'Grid Social Boxes', 'manage_options', 'grid_social_boxes_settings', array( $this, 'social_boxes_settings') );
		add_submenu_page( null, 'Grid Twitter Callback', 'Grid Twitter Callback', 'manage_options', 'grid_social_boxes_twitterbox_callback', array( $this, 'social_boxes_twitterbox_callback') );
	}
	/**
	 * render the settings page
	 */
	public function social_boxes_settings() {
		$this->social_boxes_include_twitter_api();
		if ( isset( $_POST ) && ! empty( $_POST ) ) {
			update_site_option( 'grid_twitterbox_consumer_key', $_POST['grid_twitterbox_consumer_key'] );
			update_site_option( 'grid_twitterbox_consumer_secret', $_POST['grid_twitterbox_consumer_secret'] );

			$connection = new TwitterOAuth( get_site_option( 'grid_twitterbox_consumer_key', '' ), get_site_option( 'grid_twitterbox_consumer_secret', '' ) );
			$request_token = $connection->getRequestToken( add_query_arg( array( 'page' => 'grid_social_boxes_twitterbox_callback', 'noheader' => true ), admin_url( 'admin.php' ) ) );
			session_start();
			$_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
			$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
			$url = $connection->getAuthorizeURL( $token );
			header( 'Location: ' . $url );
			die();
		} else {
			?>
			<h2>Twitter Settings</h2>
			<form method="POST" action="<?php echo add_query_arg( array( 'noheader' => true, 'page' => 'grid_social_boxes_settings' ), admin_url( 'options-general.php' ) ) ?>">
				<p>
					<label for="grid_twitterbox_consumer_key">Consumer Key:</label>
					<input type="text" name="grid_twitterbox_consumer_key" value="<?php echo get_site_option( 'grid_twitterbox_consumer_key', '' );?>">
					<label for="grid_twitterbox_consumer_secret">Consumer Secret:</label>
					<input type="text" name="grid_twitterbox_consumer_secret" value="<?php echo get_site_option( 'grid_twitterbox_consumer_secret', '' );?>">
				</p>
				<?php echo get_submit_button( "Save" ); ?>
			</form>

			<p>Access Token:</p>
			<pre>
			<?php
			var_dump( get_site_option( 'grid_twitterbox_accesstoken', 'none' ) );
			?>
			</pre>

			<?php
		}
	}

	/**
	 * callback for twitter settings page
	 */
	public function social_boxes_twitterbox_callback() {
		$this->social_boxes_include_twitter_api();
		session_start();
		$connection = new TwitterOAuth(
			get_site_option( 'grid_twitterbox_consumer_key', '' ),
			get_site_option( 'grid_twitterbox_consumer_secret', '' ),
			$_SESSION['oauth_token'],
			$_SESSION['oauth_token_secret']
		);

		/* Request access tokens from twitter */
		$access_token = $connection->getAccessToken( $_REQUEST['oauth_verifier'] );
		update_site_option( 'grid_twitterbox_accesstoken', $access_token );
		echo 'Done! We\'re authenticated';
	}

	/**
	 * include twitter api if not already included
	 */
	public function social_boxes_include_twitter_api(){
		if(!class_exists("TwitterOAuth")){
			require_once 'grid_twitterbox/twitteroauth/twitteroauth.php';
		}
	}
}
new Grid_Social_Boxes();














?>