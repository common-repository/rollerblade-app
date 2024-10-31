<?php
/**
* Plugin Name: Rollerblade
* Plugin URI: https://rollerbladeapp.com/support/
* Description: Rollerblade is a dead simple way for your clients to leave visual feedback on your WP sites and gives developers and designers information rich tickets tracking the feedback.
* Version: 1.1.0
* Author: Webatix
* Author URI: http://webatix.com
* Text Domain: rollerblade
* Domain Path: /lang/
* License: GPL2
*/

if ( ! defined( 'ABSPATH' ) ) {

	exit; // Exit if accessed directly

}

class Rollerblade {
	
	/**
	 * Remote server URL
	 * 
	 * @var string
	 */
	private static $remote_server_url = 'rollerbladeapp.com';
	
	
	/**
	 * Plugin version number
	 * 
	 * @var string
	 */
	private static $version_number = '1.0.1';
	
	
	/**
	 * Triggered on plugin activation
	 * 
	 * @return void
	 */
	public function activate() {
		
		add_option( 'rb_plugin_activated', true );
		
	}
	
	
	/**
	 * Initializes the plugin
	 * 
	 * @return void
	 */
	public static function initialize_plugin() {
		
		//display user instructions only on plugin activation
		if ( true == get_option( 'rb_plugin_activated', false ) ) {
				
			delete_option( 'rb_plugin_activated' );
				
			add_action( 'admin_footer', array( 'Rollerblade', 'rb_display_user_instructions' ) );
				
		}
		
		//add plugin links
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( 'Rollerblade', 'action_links' ) );
		
		//enqueue scripts and styles
		add_action( 'wp_enqueue_scripts', array( 'Rollerblade', 'enqueue_scripts_and_styles' ) );
		add_action( 'admin_enqueue_scripts', array( 'Rollerblade', 'enqueue_scripts_and_styles' ) );
		
		//print out rollerblade button on front-end and back-end
		add_action( 'wp_footer', array( 'Rollerblade', 'print_the_rollerblade_button' ) );
		add_action( 'admin_footer', array( 'Rollerblade', 'print_the_rollerblade_button' ) );
		
		//AJAX calls hander
		add_action( 'wp_ajax_send_rb_request', array( 'Rollerblade', 'send_rb_request' ) );
		add_action( 'wp_ajax_nopriv_send_rb_request', array( 'Rollerblade', 'send_rb_request' ) );
		
		add_action( 'wp_ajax_rb_save_subdomain',  array( 'Rollerblade', 'rb_save_subdomain' ) );
		
		//add RB options menu item
		add_action( 'admin_menu', array( 'Rollerblade', 'add_rollerblade_options_menu_item' ) );
		
	}
	
	
	/**
	 * Displays user instructions on plugin activation
	 *
	 * @return void
	 */
	public function rb_display_user_instructions() {
	
		wp_enqueue_script( 'jquery-ui-dialog' );
	
		wp_enqueue_style( 'jqueryui', plugins_url( 'css/smoothness-jquery-ui.css', __FILE__ ) );
		
		$subdomain = get_option( '_rb_subdomain', '' );
	
		?><div id="rb-user-instructions">
		
			<div id="rb-wizard-step-1" class="rb-wizard-step">
			
				<?php _e( 'You’ve successfully activated Rollerblade. Let’s get it set up.<br />You need an account on RollerbladeApp.com to get started.<a href="#" id="rb-already-have-acc">I already have an account</a><a href="#" id="rb-dont-have-acc">I don’t have an account or am not sure</a>', 'rollerblade' ); ?>
				
			</div>
			
			<div id="rb-wizard-step-2-positive" class="rb-wizard-step">
			
				<?php _e( 'What is your RollerbladeApp account sub-domain?', 'rollerblade' ); ?>
				
				<div id="rb-set-subdomain-wrapper">
				
					<input type="text" id="rb-set-subdomain" name="rb-set-subdomain" value="<?php echo $subdomain; ?>" />.<?php echo self::$remote_server_url; ?>
					
				</div>
				
				<a href="#" id="rb-save-subdomain-to-settings"><?php _e( 'Save to Settings', 'rollerblade' ); ?></a>
				
				<a href="#" id="rb-wizard-i-dont-know-it"><?php _e( 'I don’t know it', 'rollerblade' ); ?></a>
				
			</div>
			
			<div id="rb-wizard-step-2-negative" class="rb-wizard-step">
			
				<?php _e( 'Please create an account on RollerbladeApp.com then follow the instructions to add this WP site to your Account Settings.<a href="http://' . self::$remote_server_url . '/signup/">Take me there</a>', 'rollerblade' ); ?>
				
			</div>
			
			<div id="rb-wizard-step-3" class="rb-wizard-step">
			
				<?php _e( 'Next, add ' . get_bloginfo( 'url' ) . ' to your authorized sites on RollerbladeApp.com<a href="#" id="rb-wizard-success">I have already manually done this</a><a href="#" id="rb-external-settings">Take me to those settings!</a>', 'rollerblade' ); ?>
				
			</div>
			
			<div id="rb-wizard-step-4" class="rb-wizard-step">
			
				<?php _e( 'Congratulations. Your set up is complete if you’ve:', 'rollerblade' ); ?>

				<ol>
				
					<li><?php _e( 'Added your Rollerblade account sub-domain to your plugin settings on this site.', 'rollerblade' ); ?></li>
					
					<li><?php _e( 'Added this site’s URL to your Rollerblade account settings.', 'rollerblade' ); ?></li>
					
				</ol>	

				<?php _e( 'To get started, Go the front end of this site and click the Rollerblade icon in the upper left-hand corner.<br />Thanks for choosing Rollerblade. If you need help, it’s here: <a href="http://' . self::$remote_server_url . '/support" target="_blank">Support</a>', 'rollerblade' ); ?>
				
			</div>
			
		</div>
		
		<script type="text/javascript">

			jQuery( function($) {

				//initialize the wizard
				$( '#rb-user-instructions' ).dialog( {

					title: '<?php _e( 'Rollerblade Setup Wizard', 'rollerblade' ); ?>', width: 420

				} );

				//step 1 positive answer
				$( '#rb-already-have-acc' ).click( function( event ) {

					event.preventDefault();

					$( '#rb-wizard-step-1' ).hide();

					$( '#rb-wizard-step-2-positive' ).show();
					
				} );

				//step 1 negative answer
				$( '#rb-dont-have-acc' ).click( function( event ) {

					event.preventDefault();

					$( '#rb-wizard-step-1' ).hide();

					$( '#rb-wizard-step-2-negative' ).show();
					
				} );

				//step 2-positive positive answer ("Save to Settings")
				$( '#rb-save-subdomain-to-settings' ).click( function( event ) {

					event.preventDefault();

					//ajax save subdomain
					$.ajax( {
						url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
						dataType: 'json',
						type: 'POST',
						data: {
							action: 'rb_save_subdomain',
							security: '<?php echo wp_create_nonce( 'rb-wizard-security' ); ?>',
							subdomain: $( '#rb-set-subdomain' ).val(),
						},
						success: function( response ) {

							if ( ! response.subdomain.length ) {

								alert( 'AJAX request failed!' );

								return;
								
							}

							$( '#rb-external-settings' ).attr( 'href', 'http://' + response.subdomain + '.<?php echo self::$remote_server_url; ?>/wp-admin/admin.php?page=rollerblade-options' );
							
							$( '#rb-wizard-step-2-positive' ).hide();

							$( '#rb-wizard-step-3' ).show();
							
						},
						error: function() {
							alert( 'AJAX request failed!' );
						},
					} );
					
				} );

				//step 2-positive negative answer
				$( '#rb-wizard-i-dont-know-it' ).click( function( event ) {

					event.preventDefault();

					$( '#rb-wizard-step-2-positive' ).hide();

					$( '#rb-wizard-step-1' ).show();
					
				} );

				//step 3 positive answer
				$( '#rb-wizard-success' ).click( function( event ) {

					event.preventDefault();

					$( '#rb-wizard-step-3' ).hide();

					$( '#rb-wizard-step-4' ).show();
					
				} );

			} );

		</script><?php
			
	}
	
	
	/**
	 * Saves subdomain entered in Setup Wizard
	 * 
	 * @return void
	 */
	public static function rb_save_subdomain() {
		
		check_ajax_referer( 'rb-wizard-security', 'security' );
		
		//do nothing if it's not an admin who's trying to change subdomain
		if ( ! current_user_can( 'manage_options' ) ) {

			exit;

		}
		
		$subdomain = sanitize_text_field( $_POST['subdomain'] );
		
		if ( ! empty( $subdomain ) ) {

			update_option( '_rb_subdomain', $subdomain );

		}
		
		echo json_encode( array( 'subdomain' => $subdomain ) );
		
		exit;

	}
	
	
	/**
	 * Adds "Settings" link to plugin row on plugins page
	 *
	 * @param array $links
	 *
	 * @return array $links
	 */
	public static function action_links( $links ) {
	
		$plugin_links = array(
				'<a href="' . admin_url( 'options-general.php?page=rollerblade' ) . '">' . __( 'Settings', 'rollerblade' ) . '</a>',
				'<a href="http://' . self::$remote_server_url . '/support">' . __( 'Support', 'rollerblade' ) . '</a>',
		);
	
		return array_merge( $plugin_links, $links );
	
	}
	
	
	/**
	 * Enqueues scripts and styles
	 * 
	 * @return void
	 */
	public static function enqueue_scripts_and_styles() {
		
		//rollerblade styles are always in use
		wp_register_style( 'rollerblade', plugins_url( 'css/rollerblade.css' , __FILE__ ) );

		wp_enqueue_style( 'rollerblade' );

		//make sure that subdomain is set and current user is allowed to use Rollerblade
		$usage_allowed = self::is_rb_usage_allowed();
		
		if ( ! $usage_allowed ) {
			
			return;

		}
		
		wp_register_script( 'html2canvas', plugins_url( 'scripts/html2canvas.js' , __FILE__ ), array( 'jquery' ) );
		
		wp_enqueue_script( 'html2canvas' );
		
		wp_register_script( 'feedback', plugins_url( 'scripts/feedback.js' , __FILE__ ), array( 'jquery', 'html2canvas' ) );
		
		wp_enqueue_script( 'feedback' );
		
		wp_register_script( 'rollerblade', plugins_url( 'scripts/rollerblade.js' , __FILE__ ), array( 'jquery', 'feedback', 'jquery-ui-draggable' ) );
		
		wp_enqueue_script( 'rollerblade' );
		
		wp_register_style( 'feedback', plugins_url( 'css/feedback.css' , __FILE__ ) );
		
		wp_enqueue_style( 'feedback' );

		wp_enqueue_script( 'jquery-ui-draggable', array( 'jquery' ) );
		
	}
	
	
	/**
	 * Prints out the Rollerblade button
	 * 
	 * @return void
	 */
	public static function print_the_rollerblade_button() {
		
		//make sure that subdomain is set and current user is allowed to use Rollerblade
		if ( ! self::is_rb_usage_allowed() ) {

			return;
			
		}

		$subdomain = get_option( '_rb_subdomain' );
		
		$ajax_nonce = wp_create_nonce( 'rb-request-nonce' );
		
		//Rollerblade Button and Remote Panel
		?>
		
			<div id="rollerblade-button-wrapper">
			
				<div id="rollerblade-button"><div id="rb-button-drag-area"></div></div>
				
				<a href="#" id="rb-remote-panel-button" title="<?php _e( 'Show/Hide RB Remote Panel', 'rollerblade' ); ?>"></a>
				
				<div id="rb-remote-panel">
					
						<div id="rb-remote-log-in-credentials-wrapper">
					
							<h4><?php _e( 'Rollerblade Remote Log In', 'rollerblade' ); ?></h4>
							
							<div class="rb-log-in-field-wrapper">
							
								<label for="rb-remote-user-login"><?php _e( 'Username', 'rollerblade' ); ?></label>
								
								<input type="text" id="rb-remote-user-login" />
								
							</div>
							
							<div class="rb-log-in-field-wrapper">
							
								<label for="rb-remote-user-pass"><?php _e( 'Password', 'rollerblade' ); ?></label>
								
								<input type="password" id="rb-remote-user-pass" />
								
							</div>
							
							<div class="rb-log-in-field-wrapper">
							
								<a href="#" id="rb-remote-log-in"><?php _e( 'Log In', 'rollerblade' ); ?></a>
								
								<span id="rb-remote-log-in-error-message"></span>
							
							</div>
							
						</div>
						
						<div id="rb-remote-user-details-wrapper">
						
							<div id="rb-remote-user-details">
							
								<?php _e( 'You\'re logged in as ', 'rollerblade' ); ?>
								
								<span id="rb-remote-user-display-name"></span>
								
							</div>
							
							<a href="#" id="rb-remote-log-out" title="<?php _e( 'RB Remote Log Out', 'rollerblade' ); ?>"></a>
						
						</div>
						
					</div>
					
			</div>
			
			<div id="mouse-tip"><?php _e( 'Click and drag to highlight the area', 'rollerblade' ); ?></div>
			
			<div id="feedback-highlighter-next-clone"></div>
			
			<script type="text/javascript">var rollerblade_ajax_url = "<?php echo admin_url( 'admin-ajax.php' ); ?>"; var rollerblade_nonce = "<?php echo $ajax_nonce; ?>";</script>
		
		<?php 
		
	}
	
	
	/**
	 * Returns true if subdomain is set and current user is allowed to use Rollerblade. Otherwise false.
	 *  
	 * @return boolean $is_allowed
	 */
	public static function is_rb_usage_allowed() {
		
		//make sure subdomain is set
		$subdomain = get_option( '_rb_subdomain' );
		
		if ( empty( $subdomain ) ) {
				
			return false;
				
		}
		
		//restrict RB presence by selected user roles
		$active_user_roles = get_option( '_rb_active_user_roles', array( 'administrator' ) );
		
		//if visitors are allowed to use RB, everyone can
		if ( in_array( 'visitor', $active_user_roles ) ) {
			
			return true;
			
		}
		
		//if visitor is not allowed, let's check whether user has right permissions
		foreach( $active_user_roles as $role ) {
			
			if ( current_user_can( $role ) ) {
				
				return true;
				
			}
			
		}
		
		return false;
		
	}
	
	
	/**
	 * Handles AJAX call to send a remote request to RB site.
	 * 
	 * @return void
	 */
	public static function send_rb_request() {
		
		check_ajax_referer( 'rb-request-nonce', 'security' );
		
		if ( ! self::is_rb_usage_allowed() ) {
			
			die();		//this call was not from our tool, die silently
			
		}
		
		$subdomain = get_option( '_rb_subdomain' );
		
		$data = $_POST['request_data'];
		
		//save user
		if ( is_user_logged_in() ) {

			global $current_user;

			get_currentuserinfo();

			$data['browser']['user'] = $current_user->display_name;
			
		} else {

			$data['browser']['user'] = __( 'Not Logged In', 'rollerblade' );

		}
		
		if ( 'ticket_submission' === $_POST['request_type'] ) {		//ticket submission request
		
			$data['system'] = self::rb_get_system_data();
			
			$remote_url = 'http://' . self::$remote_server_url . '/api/' . $subdomain . '/ticket/add';
			
		} else if ( 'user_signin' === $_POST['request_type'] ) {

			$remote_url = 'http://' . self::$remote_server_url . '/api/' . $subdomain . '/user/signin';
			
		}	
		
		$res = wp_remote_post( 
				$remote_url,
				array(
						'headers' => array( 'Content-type' => 'application/json' ),
						'body' => json_encode( $data ),
						'timeout' => 60,
				)
		);
		
		if ( ! is_wp_error( $res ) ) {
		
			$response = json_decode( $res['body'] );

			//if request wasn't successful, send a helpscout report
			if ( isset( $response->status ) && ( 'error' === $response->status ) && ( 'user_signin' !== $_POST['request_type'] ) ) {		//let's not send helpscout reports  for user sign-in requests

				self::submit_helpscout_report( $response );
				
			} else  if ( 'ticket_submission' === $_POST['request_type'] ) {		//unset visited pages only on successful ticket submission requests
			
				//unset cookie that stores visited pages
				setcookie( 'RB_pages_visited', '', time() - 86400, '/' );
				
			}	

			echo $res['body'];
			
		} else {

			echo json_encode( array( 'status' => 'error', 'error_code' => 10081, 'user_message' => __( 'Oops! Remote request call has failed, which usually indicates an issue with this site. If you need help please search or contact us through the <a href="http://support.rollerbladeapp.com" target="_blank">support portal</a>.', 'rollerblade' ), 'error_message' => 'Could not send remote request. Details: ' . maybe_serialize( $res ) ) );
			
		}	
		
		die();
		
	}
	
	
	/**
	 * Returns system details
	 * 
	 * @return array $system
	 */
	private static function rb_get_system_data() {

		$system = array(
			'active_plugins' => array(),
			'inactive_plugins' => array(),
			'theme' => array(),
			'WordPress_environment' => array(),
			'server_environment' => array(),
		);
		
		//get plugins
		$all_plugins = array_merge( get_plugins(), get_mu_plugins() );
		
		if ( ! empty( $all_plugins ) ) {

			foreach( $all_plugins as $plugin_path => $plugin ) {

				$plugin_name = $plugin['Name'];

				if ( ! empty( $plugin_data['PluginURI'] ) ) {

					$plugin_name = '<a href="' . esc_url( $plugin_data['PluginURI'] ) . '" target="_blank">' . $plugin_name . '</a>';
						
				}
				
				if ( is_plugin_active( $plugin_path ) ) {

					$type = 'active_plugins';

				} else {

					$type = 'inactive_plugins';

				}
				
				array_push( $system[ $type ], array(
					'name' => '<a href="' . $plugin['PluginURI'] . '" target="_blank">' . $plugin_name . '</a>',
					'author' => '<a href="' . $plugin['AuthorURI'] . '" target="_blank">' . $plugin['Author'] . '</a>',
					'version' => $plugin['Version'],
				) );

			}

		}
		
		//get the theme details
		$active_theme = wp_get_theme();

		$system['theme'] = array(
			'name' => $active_theme->Name,
			'version' => $active_theme->Version,
			'author_URL' => '<a href="' . $active_theme->{'Author URI'} . '" target="_blank">' . $active_theme->{'Author URI'}. '</a>',
			'is_child_theme' => is_child_theme(),
		);

		if( is_child_theme() ) {

			$system['theme']['parent_theme'] = wp_get_theme( $active_theme->Template );
			
		}
		
		//get WP environment
		$system['WordPress_environment'] = array(
			'home_URL' => '<a href="' . home_url() . '" target="_blank">' . home_url() . '</a>',
			'site_URL' => '<a href="' . site_url() . '" target="_blank">' . site_url() . '</a>',
			'WP_version' => get_bloginfo( 'version' ),
			'is_multisite' => is_multisite(),
			'memory_limit' => WP_MEMORY_LIMIT,
			'debug_mode' => ( bool ) ( defined( 'WP_DEBUG' ) && WP_DEBUG ),
			'language' => get_locale(),
		);

		//get server environment
		if ( function_exists( 'phpversion' ) ) {

			$php_version = phpversion();
			
		} else {

			$php_version = __( 'Not detected', 'rollerblade' );
			
		}
		$system['server_environment'] = array(
			'server_info' => $_SERVER['SERVER_SOFTWARE'],
			'PHP_version' => $php_version,
		);

		if ( function_exists( 'ini_get' ) ) {

			$system['server_environment']['PHP_post_max_size'] = ini_get( 'post_max_size' );
			
			$system['server_environment']['PHP_time_limit'] = ini_get( 'max_execution_time' );
			
			$system['server_environment']['PHP_max_input_vars'] = ini_get( 'max_input_vars' );
			
			$system['server_environment']['SUHOSIN_installed'] = extension_loaded( 'suhosin' );

		}
		
		global $wpdb;
		
		$system['server_environment']['MySQL_version'] = $wpdb->db_version();
		
		$system['server_environment']['max_upload_size'] = size_format( wp_max_upload_size() );
		
		$system['server_environment']['default_timezone_is_UTC'] = ( bool ) ( 'UTC' === date_default_timezone_get() );
		
		$system['server_environment']['fsockopen/cURL'] = ( bool ) ( 'UTC' === date_default_timezone_get() );

		return $system;

	}
	
	
	/**
	 * Submits helpscout report
	 * 
	 * @param object $data
	 * 
	 * @return void
	 */
	private static function submit_helpscout_report( $data ) {

		$url = 'https://c2b7de744c0a8b6b83eb59e286cbd54cf9da207a:X@api.helpscout.net/v1/conversations.json';
		
		$message = 'Error Code: ' . $data->error_code . '<br />Error Message: ' . $data->error_message;
		$message .= '<br />Site Admin: ' . get_bloginfo( 'admin_email' ) . '<br />Site Address: ' . get_bloginfo( 'url' );
		$message .= '<br />Plugin version number: ' . self::$version_number . '<br />Subdomain: http://' . get_option( '_rb_subdomain' ) . '.' . self::$remote_server_url;
		
		$post_data = new stdClass();
		
		$post_data->type = 'email';
		
		$post_data->customer = (object) array( 'email' => 'mike.solonevich@gmail.com' );
		
		$post_data->subject = 'Submission failed on ' . site_url();
		
		$post_data->mailbox = (object) array( 'id' => 46369 );
		
		$post_data->threads = array( (object) array(
			'type' => 'message',
			'createdBy' => (object) array( 'id' => 96196, 'firstName' => 'Rollerblade Support', 'lastName' => 'Bot', 'email' => 'mike.solonevich@gmail.com', 'type' => 'user' ),
			'body' => $message,
			'status' => 'active',
		) );

		$result = wp_remote_post( $url, array( 'headers' => array( 'Content-Type' => 'application/json' ), 'body' => json_encode($post_data ) ) );

	}
	
	/**
	 * Adds Rollerblade Options Menu Item
	 * 
	 * @return void
	 */
	public static function add_rollerblade_options_menu_item() {
		
		add_options_page( __( 'Rollerblade', 'rollerblade' ), __( 'Rollerblade', 'rollerblade' ), 'manage_options', 'rollerblade', array( 'Rollerblade', 'options_page_view' ) );
		
	}
	
	
	/**
	 * Rollerblade Options page view
	 * 
	 * @return void
	 */
	public static function options_page_view() {
		
		if ( ! current_user_can( 'manage_options' ) )
			return false;
		
		//save the old subdomain to show a notification if it was changed
		$old_subdomain = get_option( '_rb_subdomain' );
		
		//process data if it was submitted
		if ( isset( $_POST['rb-subdomain'] ) ) {
				
			$subdomain = htmlspecialchars( strip_tags( trim( $_POST['rb-subdomain'] ) ) );
			
			update_option( '_rb_subdomain', $subdomain );
				
		} else {
			
			$subdomain = get_option( '_rb_subdomain', '' );
			
		}
		
		$active_user_roles = get_option( '_rb_active_user_roles', array( 'administrator' ) );
		
		if ( isset( $_POST['rb-options-form'] ) ) {
			
			$active_user_roles = array();
			
			if ( isset( $_POST['rb-active-user-roles'] ) ) {
			
				$active_user_roles = $_POST['rb-active-user-roles'];
				
			}	
			
			update_option( '_rb_active_user_roles', $active_user_roles );
			
		}
		
		?>
		
			<div id="rb-options-header">
			
				 <div id="rb-logo-wrapper">
				
					<div id="rb-logo">

							<img id="rb-options-icon" src="<?php echo plugins_url( 'img/rb-options-icon.svg', __FILE__ ); ?>" />
							
							<img id="rb-options-logo" src="<?php echo plugins_url( 'img/rb-options-logo.svg', __FILE__ ); ?>" />
						
					</div>
					
				</div>
			
				<div id="rb-options-header-text-wrapper">
				
					<p id="rb-options-slogan"><?php _e( 'We\'re changing the way Wordpress teams work', 'rollerblade' ); ?></p>
					
					<p id="rb-options-header-links">
					
						<a href="<?php if ( ! empty( $subdomain ) ) { echo 'http://' . $subdomain . '.' . self::$remote_server_url . '/wp-admin/admin.php?page=rollerblade-options'; } else { echo '#'; } ?>" target="_blank"><?php _e( 'Your Account', 'rollerblade' ); ?></a> <span id="after-first-item">&nbsp; &#x7c; &nbsp;</span><a href="<?php if ( ! empty( $subdomain ) ) { echo 'http://' . $subdomain . '.' . self::$remote_server_url . '/'; } else { echo '#'; } ?>" target="_blank"><?php _e( 'Your Project Tickets', 'rollerblade' ); ?></a> <span id="after-second-item">&nbsp; &#x7c; &nbsp;</span><a href="http://<?php echo self::$remote_server_url; ?>/support/" target="_blank"><?php _e( 'Support and Docs', 'rollerblade' ); ?></a>
						
					</p>
					
				</div>
				
			</div>
		
			<form method="POST" action="<?php echo add_query_arg( array( 'page' => 'rollerblade', 'message' => 1 ), admin_url( 'admin.php', 'http' ) ); ?>">
			
				<input type="hidden" name="rb-options-form" value="1" />
			
				<table class="form-table">
			
					<tbody>
					
						<tr>
						
							<th scope="row">
							
								<label for="rb-subdomain"><?php _e( 'Rollerblade Sub-domain', 'rollerblade' ); ?></label>
							
							</th>
							
							<td>
							
								http://<input type="text" id="rb-subdomain" name="rb-subdomain" value="<?php echo esc_attr( $subdomain ); ?>" />.<?php echo self::$remote_server_url; ?>
							
							</td>
						
						</tr>
					
						<tr>
						
							<th scope="row">
							
								<label for="rb-active-user-roles"><?php _e( 'Who should be able to use Rollerblade on this Site?', 'rollerblade' ); ?></label>
							
							</th>
							
							<td>
							
								<?php
									
									$roles = new WP_Roles(); 
									
									$all_user_roles = $roles->get_names();
									
									$all_user_roles['visitor'] = __( 'Visitor', 'rollerblade' );
									
								?>
										
									<ul>

									<?php 
										
										foreach( $all_user_roles as $role => $name ) {
									
									?>
										
											<li><input type="checkbox" id="rb-user-role-<?php echo esc_attr( $role ); ?>" class="rb-active-user-roles" name="rb-active-user-roles[]"<?php if ( in_array( $role, $active_user_roles ) ) echo 'checked="checked"'; ?> value="<?php echo esc_attr( $role ); ?>" /> <label for="rb-user-role-<?php echo esc_attr( $role ); ?>"><?php echo $name; ?></label></li>
											
									<?php 
								
										}
										
									?>
										
									</ul>
							
							</td>
						
						</tr>
					
					</tbody>
					
				</table>	
			
				<p class="submit">
				
					<input id="save-rb-data" class="button-primary" type="submit" value="<?php _e( 'Save Options', 'rollerblade' ); ?>" />
				
				</p>
			
			</form>
			
			<?php
			
				//print out a notification if subdomain was changed (and it's not empty)
				if ( ( $old_subdomain !== $subdomain ) && ! empty( $subdomain ) ) {

					?>
					
						<div id="rb-subdomain-saved"><p><?php printf( __( 'Your sub-domain has been saved and tickets will be sent to %s if you\'ve already set it up and authorized this site.<br /><br />Next use the checkboxes to the left to select the roles that will see the Rollerblade tool on your site.', 'rollerblade' ), '<a href="http://' . $subdomain . '.' . self::$remote_server_url . '" target="_blank">http://' . $subdomain . '.' . self::$remote_server_url . '</a>' ); ?></p></div>
					
					<?php 

				}
			
			?>
			
			<script type="text/javascript">
			
				jQuery( function( $ ) {

					function mobileViewMenu() {

						$( '#rb-options-header-links' ).css( { display: 'none', height: '0px', width: window.innerWidth, left: '-' + ( window.innerWidth - 49 ) + 'px' } );
							
						$( '#rb-options-header-text-wrapper' ).hover( function() {
								
							$( '#rb-options-header-links' ).css( { display: 'block' } ).stop().animate( {
								height: '256px'
							}, 'slow' );
								
						}, function() {
								
							$( '#rb-options-header-links' ).stop().animate( {
								height: '0px'
							}, 'slow', function() {
								$( '#rb-options-header-links' ).css({ display: 'none' });
							} );
								
						} );
						
					}
					
					//accordion menu for mobile version
					$( window ).resize( function() {

						if ( window.innerWidth <= 480 ) {

							mobileViewMenu();

						} else {

							//discard all the dynamic changes, made for mobile view
							$( '#rb-options-header-text-wrapper, #rb-options-header-links' ).off( 'hover' ).removeAttr( 'style' );
							
						}

					} ).resize();

					<?php 
							
						//show a notification if subdomain was changed (and it's not empty)
						if ( ( $old_subdomain !== $subdomain ) && ! empty( $subdomain ) ) {
									
							wp_enqueue_script( 'jquery-ui-dialog' );
							
							wp_enqueue_style( 'jqueryui', plugins_url( 'css/smoothness-jquery-ui.css', __FILE__ ) );
							
							?>
		
							if ( $( '#rb-subdomain-saved' ).length ) {		//if there's a notification, let's show it
							
								$( '#rb-subdomain-saved' ).dialog( {
			
									title: '<?php _e( 'Sub-domain has been saved!', 'rollerblade' ); ?>', width: 420
			
								} );
		
							}
									
							<?php 
									
						}
									
					?>
					
				} );
			
			</script>
			
			<?php 
		
	}
	
}

//initialize plugin
add_action( 'init', array( 'Rollerblade', 'initialize_plugin' ) );

register_activation_hook( __FILE__, array( 'Rollerblade', 'activate' ) );

