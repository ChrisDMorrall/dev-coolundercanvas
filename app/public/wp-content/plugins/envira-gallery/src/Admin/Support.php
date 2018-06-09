<?php
/**
 * Support class.
 *
 * @since 1.8.1
 *
 * @package Envira_Gallery
 * @author  Envira Gallery Team
 */

namespace Envira\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

class Support {

	/**
	 * Holds the submenu pagehook.
	 *
	 * @since 1.7.0
	 *
	 * @var string
	 */
	public $hook;

	public $gallery_transient_status;

    /**
     * Path to the file.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $file = __FILE__;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.8.1
	 */
	public function __construct() {

		// Add custom addons submenu.
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 15 );

		add_action( 'admin_init', array( $this, 'clear_all_envira_cache' ), 10 );
		add_action( 'admin_init', array( $this, 'clear_all_envira_options' ), 10 );
		add_action( 'admin_init', array( $this, 'clear_all_transients' ), 10 );
		add_action( 'admin_init', array( $this, 'test_api' ), 10 );
		add_action( 'admin_init', array( $this, 'settings' ), 10 );
		add_action( 'admin_init', array( $this, 'toggle_gallery_transient_setting' ), 10 );		
		add_action( 'admin_init', array( $this, 'toggle_album_transient_setting' ), 10 );
		add_action( 'envira_support_notices', array( $this, 'tools_fix_image_links_gallery' ), 10 );

		// Load admin assets.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );

        // Load Admin Bar
        add_action( 'admin_bar_menu', array( $this, 'admin_bar_support_button' ), 9999 );
	
		$this->gallery_transient_status = get_option('eg_t_gallery_status');

	}

	/**
	 * Register and enqueue addons page specific CSS.
	 *
	 * @since 1.8.1
	 */
	public function admin_styles() {

		wp_register_style( ENVIRA_SLUG . '-support-style', plugins_url( 'assets/css/support.css', ENVIRA_FILE ), array(), time() );
		wp_enqueue_style( ENVIRA_SLUG . '-support-style' );

		do_action('envira_support_styles');

	}

	public function tools_fix_image_links_gallery() {

		if ( empty( $_GET['page'] ) || $_GET['page'] != 'envira-gallery-support-tools' || ( $_GET['page'] == 'envira-gallery-support-tools' && empty($_POST) ) ) {
			return;
		}

		if ( $_POST['action'] != 'tools-fix-image-links' ) {
			return;
		}

		if ( !isset($_POST['gallery_id']) || intval($_POST['gallery_id']) == 0  ) {
			return;
		}


		$gallery_id = intval($_POST['gallery_id']);

		?>

		<div class="notice notice-warning">

			<?php 

		        // Get gallery data from Post Meta
		        $data = get_post_meta( $gallery_id, '_eg_gallery_data', true );
		        $updated = 0;
		        $post = get_post($gallery_id);
		        $gallery_title = $post->post_name;

		        foreach ($data['gallery'] as $item_id => $item ) {
		        	 if ( empty( $data['gallery'][$item_id]['link'] ) && !empty( $data['gallery'][$item_id]['src'] ) ) { 
		        	 	$data['gallery'][$item_id]['link'] = $data['gallery'][$item_id]['src'];
		        	 	echo '<p>Updated link of image #' . $item_id. ' to ' . $data['gallery'][$item_id]['src'] . '</p>';
		        	 	$updated++;
		        	 }
		        }

				if ( $updated > 0 ) {
					update_post_meta( $gallery_id, '_eg_gallery_data', $data );
				}

				echo '<p>Updated a total of <strong>' .$updated. '</strong> items for the <strong>' . $gallery_title . '</strong> gallery.</p></div>';

			?>

		</div>

		<?php 

	}

	public function clear_all_transients() {


		if ( empty( $_GET['page'] ) || $_GET['page'] != 'envira-gallery-support-general' || ( $_GET['page'] == 'envira-gallery-support-general' && empty($_POST) ) ) {
			return;
		}

		if ( $_POST['action'] == 'clear-all' ) {

		    global $wpdb;
		 
		    $sql = 'DELETE FROM ' . $wpdb->options . ' WHERE option_name LIKE "_transient_%"';
		    $wpdb->query($sql);

		}



	}

	public function total_transients() {

		global $wpdb;

		// get current PHP time, offset by a minute to avoid clashes with other tasks
		$threshold = time() - MINUTE_IN_SECONDS;

		// count transient expiration records, total and expired
		$sql = "
			select count(*) as `total`, count(case when option_value < '$threshold' then 1 end) as `expired`
			from $wpdb->options
			where (option_name like '\_transient\_timeout\_%' or option_name like '\_site\_transient\_timeout\_%')
		";
		$counts = $wpdb->get_row($sql);

		// count never-expire transients
		$sql = "
			select count(*)
			from $wpdb->options
			where (option_name like '\_transient\_%' or option_name like '\_site\_transient\_%')
			and option_name not like '%\_timeout\_%'
			and autoload = 'yes'
		";
		$counts->never_expire = $wpdb->get_var($sql);

		return $counts;

	}

	public function total_envira_transients() {

		global $wpdb;

		$results = $wpdb->get_results( "SELECT option_name, option_value FROM $wpdb->options WHERE 
			option_name LIKE ('%_transient__eg_%') 
			OR option_name LIKE ('%_transient_timeout__eg_%') 
			OR option_name LIKE ('%_transient__ea_%');" );

		// print_r ($results); exit;

		return sizeof( $results );

	}

	public function clear_all_envira_cache() {

		if ( empty( $_GET['page'] ) || $_GET['page'] != 'envira-gallery-support-general' || ( $_GET['page'] == 'envira-gallery-support-general' && empty($_POST) ) ) {
			return;
		}

		if ( $_POST['action'] == 'flush-cache' ) {

			if ( function_exists('envira_flush_all_cache') ) {
				envira_flush_all_cache();
			}

		}

	}

	public function total_options() {

		global $wpdb;

		$results = $wpdb->get_results( "SELECT option_name, option_value FROM $wpdb->options WHERE 
			option_name LIKE ('eg_%');" );

		// print_r ($results); exit;

		return $results;

	}

	public function clear_all_envira_options() {

		if ( empty( $_GET['page'] ) || $_GET['page'] != 'envira-gallery-support-general' || ( $_GET['page'] == 'envira-gallery-support-general' && empty($_POST) ) ) {
			return;
		}

		if ( $_POST['action'] == 'delete-options' ) {

			global $wpdb;

			$results = $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE ('eg_%');" );

		}

	}

	public function test_api() {

		if ( empty( $_GET['page'] ) || $_GET['page'] != 'envira-gallery-support-api' || ( $_GET['page'] == 'envira-gallery-support-api' && empty($_POST) ) ) {
			return;
		}

		if ( $_POST['action'] == 'test-api' ) {

			$license = new \Envira\Admin\License();

			$key    = envira_get_license_key();
			$action = 'verify-key';
			$headers = array();

			// Build the body of the request.
			$body = wp_parse_args(
				array( 'tgm-updater-key' => $key ),
				array(
					'tgm-updater-action'     => $action,
					'tgm-updater-key'        => $key,
					'tgm-updater-wp-version' => get_bloginfo( 'version' ),
					'tgm-updater-referer'    => site_url()
				)
			);
			$body = http_build_query( $body, '', '&' );

			// Build the headers of the request.
			$headers = wp_parse_args(
				$headers,
				array(
					'Content-Type'   => 'application/x-www-form-urlencoded',
					'Content-Length' => strlen( $body )
				)
			);

			// Setup variable for wp_remote_post.
			$post = array(
				'headers'   => $headers,
				'body'      => $body
			);

			// Perform the query and retrieve the response.
			$response      = wp_remote_post( 'https://enviragallery.com', $post );
			$response_code = wp_remote_retrieve_response_code( $response ); /* log this for API issues */
			$response_body = wp_remote_retrieve_body( $response );

			update_option( 'envira_tb_api_response_code', $response_code, null, 'no' );
			update_option( 'envira_tb_api_response_body', $response_body, null, 'no' );
			update_option( 'envira_tb_api_response', $response, null, 'no' );
			update_option( 'envira_tb_api_time_tested', time(), null, 'no' );

		}

	}

	public function settings() {

		if ( empty( $_GET['page'] ) || $_GET['page'] != 'envira-gallery-support-settings' || ( $_GET['page'] == 'envira-gallery-support-settings' && empty($_POST) ) ) {
			return;
		}

		if ( $_POST['action'] == 'settings' ) {

			if ( isset( $_POST['support_show_admin_bar'] ) && intval($_POST['support_show_admin_bar']) == 1 ) {
				update_option( 'eg_admin_bar', 1, null, 'no' );				
			} else {
				delete_option( 'eg_admin_bar');	
			}

			do_action('envira_support_save_settings');

		}

	}

	public function get_api_info() {

		return ( array( 'response' => get_option('envira_tb_api_response'), 'response_body' => get_option('envira_tb_api_response_body'), 'response_code' => get_option('envira_tb_api_response_code'), 'time_tested' => get_option('envira_tb_api_time_tested') ) );

	}

	public function total_galleries() {

		return ( wp_count_posts('envira')->publish );

	}

	public function total_albums() {

		if ( class_exists('Envira_Albums') ) {
			return ( wp_count_posts('envira_album')->publish );
		} else {
			return 0;
		}

	}

	public function get_problem_galleries( $image_limit = 100 ) {

		$galleries = _envira_get_galleries( true, null, 100 );
		$problem_galleries = array();
		if ( empty( $galleries ) ) {
			return false;
		}
		// print_r ($galleries); exit;
		foreach ( $galleries as $gallery ) {
			$image_count = envira_get_gallery_image_count( $gallery['id'] );
			if ( $image_count >= $image_limit ) {
				$problem_galleries[] = array( 'link' => admin_url( 'post.php?post=' . $gallery['id'] . '&action=edit' ), 'title' => ( 'Gallery #' . $gallery['id'] ), 'image_count' => $image_count );
			}
		}

		return $problem_galleries;

	}

	public function is_gallery_on_or_off() {

			if ( get_option( 'eg_t_gallery_status' ) ) {
				return 'Off';
			} else {
				return "On";
			}

	}

	public function is_album_on_or_off() {

			if ( get_option( 'eg_t_album_status' ) ) {
				return 'Off';
			} else {
				return "On";
			}

	}

	public function toggle_gallery_transient_setting() {

		if ( empty( $_GET['page'] ) || $_GET['page'] != 'envira-gallery-support-general' || ( $_GET['page'] == 'envira-gallery-support-general' && empty($_POST) ) ) {
			return;
		}

		if ( $_POST['action'] == 'toggle-envira-gallery-transients' ) {

			wp_cache_delete('eg_t_gallery_status');

			if ( get_option( 'eg_t_gallery_status' ) !== false ) {
					delete_option( 'eg_t_gallery_status' );
				} else {
					add_option( 'eg_t_gallery_status', 'off' );
				}

		}

		$link = admin_url( '/edit.php?post_type=envira&page=envira-gallery-support-general' );
		wp_redirect( $link );
		exit;

	}

	public function toggle_album_transient_setting() {

		if ( empty( $_GET['page'] ) || $_GET['page'] != 'envira-gallery-support-general' || ( $_GET['page'] == 'envira-gallery-support-general' && empty($_POST) ) ) {
			return;
		}

		if ( $_POST['action'] == 'toggle-envira-album-transients' ) {

			wp_cache_delete('eg_t_album_status');

			if ( get_option( 'eg_t_album_status' ) !== false ) {
					delete_option( 'eg_t_album_status' );
				} else {
					add_option( 'eg_t_album_status', 'off' );
				}

		}

		$link = admin_url( '/edit.php?post_type=envira&page=envira-gallery-support-general' );
		wp_redirect( $link );
		exit;

	}


	/**
	 * Output tab navigation
	 *
	 * @since 2.2.0
	 *
	 * @param string $tab Tab to highlight as active.
	 */
	public static function tab_navigation( $tab = 'general' ) {
	?>

		<h3 class="nav-tab-wrapper">
			<a class="nav-tab <?php if ( $_GET['page'] == 'envira-gallery-support-general' ) : ?>nav-tab-active<?php endif; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'post_type' => 'envira', 'page' => 'envira-gallery-support-general' ), 'edit.php' ) ) ); ?>">
				<?php esc_html_e( 'General', 'envira-gallery' ); ?>
			</a>
			<a class="nav-tab <?php if ( $_GET['page'] == 'envira-gallery-support-api' ) : ?>nav-tab-active<?php endif; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'post_type' => 'envira', 'page' => 'envira-gallery-support-api' ), 'edit.php' ) ) ); ?>">
				<?php esc_html_e( 'API', 'envira-gallery' ); ?>
			</a>
			<a class="nav-tab <?php if ( $_GET['page'] == 'envira-gallery-support-tools' ) : ?>nav-tab-active<?php endif; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'post_type' => 'envira', 'page' => 'envira-gallery-support-tools' ), 'edit.php' ) ) ); ?>">
				<?php esc_html_e( 'Tools', 'envira-gallery' ); ?>
			</a>
			<a class="nav-tab <?php if ( $_GET['page'] == 'envira-gallery-support-logs' ) : ?>nav-tab-active<?php endif; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'post_type' => 'envira', 'page' => 'envira-gallery-support-logs' ), 'edit.php' ) ) ); ?>">
				<?php esc_html_e( 'Logs', 'envira-gallery' ); ?>
			</a>
			<a class="nav-tab <?php if ( $_GET['page'] == 'envira-gallery-support-settings' ) : ?>nav-tab-active<?php endif; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'post_type' => 'envira', 'page' => 'envira-gallery-support-settings' ), 'edit.php' ) ) ); ?>">
				<?php esc_html_e( 'Settings', 'envira-gallery' ); ?>
			</a>
		</h3>

	<?php
	}


	/**
	 * Register the Support submenu item for Envira.
	 *
	 * @since 1.8.1
	 */
	public function admin_menu() {

		// Register the submenu.
		
		add_submenu_page(
			null,
			__( ( apply_filters('envira_whitelabel', false ) ? '' : 'Envira Gallery ' ) . 'Support: General', 'envira-gallery' ),
			'<span style="color:#FFA500"> ' . __( 'Support', 'envira-gallery' ) . '</span>',
			apply_filters( 'envira_gallery_menu_cap', 'manage_options' ),
			ENVIRA_SLUG . '-support-general',
			array( $this, 'general_page' )
		);

		add_submenu_page(
			null,
			__( ( apply_filters('envira_whitelabel', false ) ? '' : 'Envira Gallery ' ) . 'Support: API', 'envira-gallery' ),
			'<span style="color:#FFA500"> ' . __( 'Support: API', 'envira-gallery' ) . '</span>',
			apply_filters( 'envira_gallery_menu_cap', 'manage_options' ),
			ENVIRA_SLUG . '-support-api',
			array( $this, 'api_page' )
		);

		add_submenu_page(
			null,
			__( ( apply_filters('envira_whitelabel', false ) ? '' : 'Envira Gallery ' ) . 'Support: Tools', 'envira-gallery' ),
			'<span style="color:#FFA500"> ' . __( 'Support: Tools', 'envira-gallery' ) . '</span>',
			apply_filters( 'envira_gallery_menu_cap', 'manage_options' ),
			ENVIRA_SLUG . '-support-tools',
			array( $this, 'tools_page' )
		);

		add_submenu_page(
			null,
			__( ( apply_filters('envira_whitelabel', false ) ? '' : 'Envira Gallery ' ) . 'Support: Logs', 'envira-gallery' ),
			'<span style="color:#FFA500"> ' . __( 'Support: Logs', 'envira-gallery' ) . '</span>',
			apply_filters( 'envira_gallery_menu_cap', 'manage_options' ),
			ENVIRA_SLUG . '-support-logs',
			array( $this, 'logs_page' )
		);

		add_submenu_page(
			null,
			__( ( apply_filters('envira_whitelabel', false ) ? '' : 'Envira Gallery ' ) . 'Support: Settings', 'envira-gallery' ),
			'<span style="color:#FFA500"> ' . __( 'Support: Settings', 'envira-gallery' ) . '</span>',
			apply_filters( 'envira_gallery_menu_cap', 'manage_options' ),
			ENVIRA_SLUG . '-support-settings',
			array( $this, 'settings_page' )
		);

	}



	/**
	 * Output the general screen.
	 *
	 * @since 1.8.1
	 */
	public function general_page() {
	?>

		<div class="envira-welcome-wrap envira-welcome">

				<div class="wrap">

					<h1>Troubleshooting</h1>

					<?php echo $this->tab_navigation(); ?>

					<?php do_action('envira_support_notices'); ?>

					<div class="card">
						<h2 class="title">Information</h2>
						<p><strong>Number of Galleries:</strong>&nbsp;&nbsp;<?php echo $this->total_galleries(); ?>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;<strong>Number of Albums:</strong>&nbsp;&nbsp;<?php echo $this->total_albums(); ?></p>
						<blockquote style="line-height: 13px;">
							<ul>
								<?php 

									$galleries = $this->get_problem_galleries();

									if ( !empty( $galleries ) ) { 

										foreach( $galleries as $gallery ) {

								?>
								<li><span class="alert" style="font-weight: 600; color:orange;">Note:</span> <a href="<?php echo $gallery['link']; ?>"><?php echo $gallery['title']; ?></a> has <?php echo $gallery['image_count']; ?> images.</li>
								<?php } } ?>								
								<?php

									$time = ini_get('max_execution_time');
									if ( $time <= 60 ) {

								?>
								<li><span class="alert" style="font-weight: 600; color:orange;">Note:</span> Maximum Limit For Runtime Set To <strong><?php echo $time; ?> Seconds</strong></li>
								<?php } ?>
								<?php 

									$php_version = phpversion();
									if (version_compare(phpversion(), '5.6', '<')) {

								?>
								<li><span class="alert" style="font-weight: 800; color:red;">Warning:</span> Site is running on an unsupported version of PHP (PHP <?php echo $php_version; ?>)</li>
								<?php } ?>
							</ul>
						</blockquote>
						<div style="display: inline-block;">
							<a href="<?php echo admin_url( '/edit.php?post_type=envira&page=envira-gallery-settings#!envira-tab-debug' ); ?>"  class="button button-warning"><?php _e( 'View Support Information', 'envira-gallery' ); ?></a>
						</div>
					</div>

					<div class="card">
						<?php

								$total = $this->total_transients();

						?>
						<h2 class="title">Cache</h2>
						<ul>
						<li>Envira transients: <?php echo $this->total_envira_transients(); ?></li>
						<li>All transients: <?php echo $total->total; ?></li>
						<li>Expired transients: <?php echo $total->expired; ?></li>
						<li>Never Expire transients: <?php echo $total->never_expire; ?></li>
						</ul>
						<div style="display: inline-block; margin: 5px;">
							<form action="" method="post">
								<input type="hidden" name="action" value="flush-cache" />
								<input type="submit" class="button button-primary" value="<?php _e( 'Flush All Envira Cache', 'envira-gallery' ); ?>" />
							</form>
						</div>
						<div style="display: inline-block; margin: 5px;">
							<form action="" method="post">
								<input type="hidden" name="action" value="clear-all" />
								<input type="submit" class="button button-warning" value="<?php _e( 'Clear ALL Transients', 'envira-gallery' ); ?>" />
							</form>
						</div>
						<div style="display: inline-block; margin: 5px;">
							<?php
								$gallery_on_or_off = $this->is_gallery_on_or_off();
								$style_color = $gallery_on_or_off == 'On' ? 'red' : '';
								//echo '-'; echo get_option( 'eg_t_gallery_status' );
							?>
							<form action="" method="post">
								<input type="hidden" name="action" value="toggle-envira-gallery-transients" />
								<input type="submit" class="button button-error" style="color: <?php echo $style_color; ?>" value="<?php _e( 'Turn Gallery Transients ' . $gallery_on_or_off, 'envira-gallery' ); ?>" />
							</form>
						</div>
						<?php

						if ( class_exists('Envira_Albums') ) {

						?>
						<div style="display: inline-block; margin: 5px;">
							<?php
								$album_on_or_off = $this->is_album_on_or_off();
								$style_color = $album_on_or_off == 'On' ? 'red' : '';
							?>
							<form action="" method="post">
								<input type="hidden" name="action" value="toggle-envira-album-transients" />
								<input type="submit" class="button button-warning" style="color: <?php echo $style_color; ?>" value="<?php _e( 'Turn Album Transients ' . $album_on_or_off, 'envira-gallery' ); ?>" />
							</form>
						</div>
						<?php } ?>
					</div>

					<div class="card">
						<?php

						$envira_options = $this->total_options();

						?>
						<h2 class="title">Envira Options</h2>
						<p>Current number of options: <?php echo sizeof( $envira_options ); ?></p></p>
						<blockquote>
							<?php if ( !empty( $envira_options ) ) { ?>
							<ul>
								<?php foreach ($envira_options as $option ) { ?>
									<li><?php echo $option->option_name; ?></li>
								<?php } ?>
							</ul>
							<?php } ?>
						</blockquote>
						<form action="" method="post">
							<input type="hidden" name="action" value="delete-options" />
							<input type="submit" class="button button-primary" value="<?php _e( 'Clear Envira Specific Options', 'envira-gallery' ); ?>" />
						</form>
					</div>



				</div>


		</div> <!-- wrap -->






		<?php
	}


	/**
	 * Output the api screen.
	 *
	 * @since 1.8.1
	 */
	public function api_page() {
	?>

		<div class="envira-welcome-wrap envira-welcome">

				<div class="wrap">

					<h1>Troubleshooting</h1>

					<?php echo $this->tab_navigation(); ?>

					<?php do_action('envira_support_notices'); ?>

					<div class="card">
						<?php

						$api_results = $this->get_api_info();

						?>
						<h2 class="title">API</h2>
						<p><small>This uses the API call for confirming license keys.</small></p>
						<form action="" method="post">
							<input type="hidden" name="action" value="test-api" />
							<input type="submit" class="button button-primary" value="<?php _e( 'Test API', 'envira-gallery' ); ?>" />
						</form>

						<br/>

						<hr/>

						<h4>Last Attempt</h4>



						<ul>
							<?php 

								if ( empty( $api_results['time_tested'] ) ) {

							?>
							<li>No Test Attempt Has Been Logged</li>
							<?php } else { ?>
							<li><?php echo date(DATE_RFC2822, $api_results['time_tested']); ?><br/>
								<strong>RESPONSE CODE: </strong> <strong style="color: green; font-weight: 800;"><?php echo $api_results['response_code']; ?></strong></li>
							<?php } ?>
							<?php //if ( $api_results['response_code'] != '200' ) { ?>
							<li><strong>Response Code Response Body:</strong> <?php print_r ( $api_results['response_body'] ); ?></li>
							<li><strong>Response Code Response:</strong> 
								<?php echo highlight_string("<?php\n\$data =\n" . var_export($api_results['response'], true) . ";\n?>"); ?>
							</li>
							<?php //} ?>
						</ul>

						
					</div>

				</div>


		</div> <!-- wrap -->


		<?php
	}

	/**
	 * Output the tools screen.
	 *
	 * @since 1.8.1
	 */
	public function tools_page() {

		$galleries = _envira_get_galleries();

	?>

		<div class="envira-welcome-wrap envira-welcome">

				<div class="wrap">

					<h1>Troubleshooting</h1>

					<?php echo $this->tab_navigation(); ?>

					<?php do_action('envira_support_notices'); ?>

					<div class="card">
						<h2 class="title">Fix Image Links</h2>
						<p class="subtitle"><small>Assigns the image URL to all images (see Github Ticket #1904).</small></p>
						<form action="" method="post">

						<p><label>Select Gallery:</label>
							<?php if ( !empty( $galleries ) ) { ?>
								<select name="gallery_id" id="fix-image-links-gallery">
									<?php foreach ($galleries as $gallery ) { 
										// print_r ($gallery); exit;
										$post = get_post( $gallery['id'] );
										?>
										<option value="<?php echo $gallery['id']; ?>"><?php echo $post->post_title; ?> -- <?php echo sizeof( $gallery['gallery'] ); ?> Images (ID: <?php echo $gallery['id']; ?>)</option>
									<?php } ?>
								</select>
							<?php } ?>

						</p>													
								<input type="hidden" name="action" value="tools-fix-image-links" />
								<input type="submit" class="button button-primary" value="<?php _e( 'Fix Gallery Links', 'envira-gallery' ); ?>" />
							</form>
					</div>


				</div>


		</div> <!-- wrap -->






		<?php
	}

	/**
	 * Output the logs screen.
	 *
	 * @since 1.8.1
	 */
	public function logs_page() {
	?>

		<div class="envira-welcome-wrap envira-welcome">

				<div class="wrap">

					<h1>Troubleshooting</h1>

					<?php echo $this->tab_navigation(); ?>

					<?php do_action('envira_support_notices'); ?>

					<div class="card">
						
						<?php $this->envira_debug_log(); ?>	

					</div>

				</div>


		</div> <!-- wrap -->


		<?php
	}


	public function envira_debug_log() {
		
		$toobig = apply_filters( 'debug_log_too_big', 5 );// how many MB throws a warning?
		$latest = apply_filters( 'debug_log_latest_count', 15 );// sets the number of latest error lines
			
		if ( ! WP_DEBUG_LOG ) {
			?>
	<div class="notice notice-warning">
		<p>Debug Log is not enabled.  <a href="https://codex.wordpress.org/Debugging_in_WordPress#WP_DEBUG_LOG" target="_blank">See the codex.</a>  Essentially, open wp-config.php and replace <code>define( 'WP_DEBUG', false );</code> with the code below.</p>
	</div>
	<pre>
	define( 'WP_DEBUG', true );// just toggle this line to false to turn off
	if ( WP_DEBUG ) {
		define( 'WP_DEBUG_DISPLAY', false );
		define( 'WP_DEBUG_LOG', true );
		@ini_set( 'display_errors', 0 );
		define( 'SCRIPT_DEBUG', true );
	}
	</pre><?php
			return;
		}
		
		$path = WP_CONTENT_DIR .'/debug.log';
		
		if ( ! file_exists( $path ) ) {		
			echo '<div class="notice notice-success"><p>No log found at '. $path .'.  Hopefully this means you have no errors.</p></div>';
			return;
		}
		
		$nonce = isset( $_REQUEST['delete-log'] ) ? $_REQUEST['delete-log'] : null;
		if ( wp_verify_nonce( $nonce, 'delete-log' ) ) {
			if ( unlink( $path ) ) {
				echo '<div class="notice notice-success"><p>Deleted Log</p></div>';
			} else {
				echo '<div class="notice notice-error"><p>Error deleting '. $path .'</p></div>';
			}
			return;
		}
		
		$link = admin_url( 'edit.php?post_type=envira&page=envira-gallery-support-logs' );
		
		
		if ( ! isset( $_GET['loadanyhow'] ) ){
			
			$size = round( filesize( $path ) / pow(1024, 2), 2 );// Can use MB_IN_BYTES but it would only work for 4.4 and up
			if ( $size > $toobig ) {
				echo '<div class="notice notice-warning"><p>Log is '. $size .'MB... Do you really want to load it here?</p><p><a href="'. $link .'&loadanyhow">Yes, load it anyhow.</a></p></div>';
				$toobig = false;
			}
		}
		
		$nonce = wp_create_nonce( 'delete-log' );
		?>
		<div class="wrap">
			<form action="<?php echo $link ?>" method="post" style="position:fixed;">
				<input type="hidden" name="delete-log" value="<?php echo $nonce ?>">
				<input type="submit" class="button button-primary" value="Delete Log">
			</form>
		<?php
		
		echo '<div style="padding-top:28px;">';

		if ( $toobig ) {// $toobig is the safty switch.  Is set to false by clicking through the warning or by filtering the initial value
			
			$log = file( $path, FILE_IGNORE_NEW_LINES );
			
			if ( $latest ) {
				$lines = count( $log );
				if ( $lines > 25 ) {// Avoid scrolling
					echo '<h2>Latest Errors</h2>';
					echo '<div style="font-family:monospace;word-wrap:break-word;">';
					for ( $l = $lines - $latest; $l < $lines; ) {
						$i = $l++;
						echo "<p><span>{$l}</span> {$log[ $i ]}</p>";
					}
					echo '</div>';
					echo '<h2>Archives</h2>';
				}
			}
			
			echo '<div style="font-family:monospace;word-wrap:break-word;">';
			foreach ( $log as $no => $line ) {
				echo "<p><span>";
				echo $no + 1;
				echo "</span> {$line}</p>";
			}
			echo '</div></div>';
		}
		echo '</div>';
	}

	/**
	 * Output the api screen.
	 *
	 * @since 1.8.1
	 */
	public function settings_page() {
	?>

		<div class="envira-welcome-wrap envira-welcome">

				<div class="wrap">

					<h1>Troubleshooting</h1>

					<?php echo $this->tab_navigation(); ?>

					<?php do_action('envira_support_notices'); ?>

					<div class="card">
						<?php


						$admin_option = get_option('eg_admin_bar');
						$admin_bar = !empty( $admin_option ) && $admin_option == true ? true : false;

						?>
						<h2 class="title">Settings</h2>
						<form action="" method="post">
							<input type="hidden" name="action" value="settings" />

							<table class="form-table">
							<tbody>
								<!-- Admin Bar -->
								<tr id="envira-media-position-box">
									<th scope="row">
										<label for="envira-media-position">Show Link In Admin Bar?</label>
									</th>
									<td>
										<select name="support_show_admin_bar" id="support_show_admin_bar" name="support_show_admin_bar">
											<option value="0" <?php if ( !$admin_bar ) { ?> selected="selected" <?php } ?>>No</option>
											<option value="1" <?php if ( $admin_bar ) { ?> selected="selected" <?php } ?>>Yes</option>
										</select>
										<p>Only logged in admin users will see this link.</p>
										<p><small><span style="color:red">Warning:</span> Be careful if you do this on a public or customer based site.</small></p>
									</td>
								</tr>

								<?php do_action('envira_support_display_settings'); ?>

							</tbody>
							</table>


							<input type="submit" class="button button-primary" value="<?php _e( 'Update Settings', 'envira-gallery' ); ?>" />
						</form>


						
					</div>

				</div>


		</div> <!-- wrap -->


		<?php
	}


	/**
	 * Add toolbar node
	 *
	 * @access  public
	 * @return  void
	 * @since   1.6
	*/
	public function admin_bar_support_button( $wp_admin_bar ) {

		if ( ! current_user_can( 'manage_options' ) ) {
		    return;
		}

		$admin_option = get_option('eg_admin_bar');
		$admin_bar = !empty( $admin_option ) && $admin_option == true ? true : false;

		if ( !$admin_bar ) {
			return;
		}

		$label = '<span style="color:#FFA500">Envira Support</span>';

		$args = array(
			'id'     => 'envira-support',
			'title'  => $label,
			'parent' => 'top-secondary',
			'href'   => admin_url( add_query_arg( array( 'post_type' => 'envira', 'page' => 'envira-gallery-support-general' ), 'edit.php' ) )
		);
		$wp_admin_bar->add_node( $args );

	}



	
}