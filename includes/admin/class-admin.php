<?php

namespace BCE_User_List\Includes\Admin;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @link       https://github.com/kenshin23/bce-user-list
 * @since      1.0.0
 *
 * @author    Carlos Paparoni
 */
class Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The text domain of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_text_domain    The text domain of this plugin.
	 */
	private $plugin_text_domain;

	/**
	 * WP_List_Table object
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      user_list_table    $user_list_table
	 */
	private $user_list_table;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string $plugin_name	The name of this plugin.
	 * @param    string $version	The version of this plugin.
	 * @param	 string $plugin_text_domain	The text domain of this plugin
	 */
	public function __construct( $plugin_name, $version, $plugin_text_domain ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->plugin_text_domain = $plugin_text_domain;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/bce-user-list-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		$params = array ( 'ajaxurl' => admin_url( 'admin-ajax.php' ) );
		wp_enqueue_script( 'bce_ajax_handle', plugin_dir_url( __FILE__ ) . 'js/bce-user-list-admin.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( 'bce_ajax_handle', 'params', $params );
	}

	/**
	 * Callback for the user sub-menu in define_admin_hooks() for class Init.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {
		$page_hook = add_users_page(
						__( 'BCE User List', $this->plugin_text_domain ),
						__( 'BCE User List', $this->plugin_text_domain ),
						'manage_options',
						$this->plugin_name,
						array( $this, 'load_user_list_table' )
					);

		/*
		 * The $page_hook_suffix can be combined with the load-($page_hook) action hook
		 * https://codex.wordpress.org/Plugin_API/Action_Reference/load-(page)
		 *
		 * The callback below will be called when the respective page is loaded
		 *
		 */
		add_action( 'load-'.$page_hook, array( $this, 'load_user_list_table_screen_options' ) );

	}

	/**
	* Screen options for the List Table
	*
	* Callback for the load-($page_hook_suffix)
	* Called when the plugin page is loaded
	*
	* @since    1.0.0
	*/
	public function load_user_list_table_screen_options() {

		$arguments	=	array(
						'label'		=>	__( 'Users Per Page', $this->plugin_text_domain ),
						'default'	=>	5,
						'option'	=>	'users_per_page'
					);

		add_screen_option( 'per_page', $arguments );

		// instantiate the User List Table
		$this->user_list_table = new User_List_Table( $this->plugin_text_domain );

	}

	/*
	 * Display the User List Table
	 *
	 * Callback for the add_users_page() in the add_plugin_admin_menu() method of this class.
	 *
	 * @since	1.0.0
	 */
	public function load_user_list_table(){

		// query, filter, and sort the data
		$this->user_list_table->prepare_items();

		// render the List Table
		include_once( 'views/partials-wp-list-display.php' );
	}

	/**
	 * Handles the form submission for 'Edit User Details' page
	 * @since    1.0.0
	 */
	public function the_form_response() {

		if( isset( $_POST['bce_edit_user_nonce'] ) && wp_verify_nonce( $_POST['bce_edit_user_nonce'], 'bce_edit_user_form_nonce') ) {

			// Good to go, process the data:
			$bce_user_id = intval( $_POST['user_id'] );
			$bce_first_name = sanitize_text_field( $_POST['first_name'] );
			$bce_last_name = sanitize_text_field( $_POST['last_name'] );

			$userdata = array(
				'ID' 			=> $bce_user_id,
				'first_name'	=> $bce_first_name,
				'last_name' 	=> $bce_last_name,
			);

			// server processing logic via AJAX. Not currently used.
			if( isset( $_POST['ajaxrequest'] ) && $_POST['ajaxrequest'] === 'true' ) {
				// server response
				echo '<pre>';
					print_r( $_POST );
				echo '</pre>';
				wp_die();
            }

			$user_id = wp_update_user( $userdata );

			if ( is_wp_error( $user_id ) ) {
				$admin_notice = 'error';
			} else {
				$admin_notice = 'success';
			}

			// server response
			$this->custom_redirect( $admin_notice, $_POST );
			exit;
		} else {
			wp_die( __( 'Invalid nonce specified', $this->plugin_name ), __( 'Error', $this->plugin_name ), array(
						'response' 	=> 403,
						'back_link' => 'admin.php?page=' . $this->plugin_name,

				) );
		}
	}

	/**
	 * Handles a user deactivation
	 * @since    1.0.0
	 */
	public function deactivate_user() {

		if( isset( $_POST['bce_deactivate_user_nonce'] ) && wp_verify_nonce( $_POST['bce_deactivate_user_nonce'], 'bce_deactivate_user_form_nonce') ) {

			// Good to go, process the data:
			$bce_user_id = intval( $_POST['user_id'] );
			$bce_deactivate = sanitize_key( $_POST['deactivate'] );

			$userdata = array(
				'ID' 			=> $bce_user_id,
				'first_name'	=> $bce_first_name,
				'last_name' 	=> $bce_last_name,
			);

			// WordPress doesn't really have such a thing as a deactivation, so
			// it'll be emulated by setting a custom user meta ('user_status')
			// to 'inactive':
			update_user_meta( $bce_user_id, 'user_status', 'inactive');
			// This doesn't really do anything, except mark a user as 'spam' (1)
			// or 'ham' (0)
			// @see: https://codex.wordpress.org/Function_Reference/update_user_status
			// update_user_status( $bce_user_id, 'user_status', 1 );

			if ( is_wp_error( $user_id ) ) {
				$admin_notice = 'error';
			} else {
				$admin_notice = 'success';
			}

			// server response
			$this->custom_redirect( $admin_notice, $_POST );
			exit;
		} else {
			wp_die( __( 'Invalid nonce specified', $this->plugin_name ), __( 'Error', $this->plugin_name ), array(
						'response' 	=> 403,
						'back_link' => 'admin.php?page=' . $this->plugin_name,

				) );
		}
	}

	/**
	 * Redirect to admin page, optionally displaying a notice:
	 *
	 * @since    1.0.0
	 */
	public function custom_redirect( $admin_notice, $response ) {
		wp_redirect( esc_url_raw( add_query_arg( array(
						'bce_admin_add_notice' => $admin_notice,
						'bce_response' => $response,
						),
						admin_url( 'admin.php?page='. $this->plugin_name )
						)
					)
		);

	}


	/**
	 * Print Admin Notices
	 *
	 * @since    1.0.0
	 */
	public function print_plugin_admin_notices() {
		  if ( isset( $_REQUEST['bce_admin_add_notice'] ) ) {
			if( $_REQUEST['bce_admin_add_notice'] === "success") {
				$html =	'<div class="notice notice-success is-dismissible">
							<p><strong>The request was successful. </strong></p><br>';
				$html .= '<pre>' . htmlspecialchars( print_r( $_REQUEST['bce_response'], true) ) . '</pre></div>';
				echo $html;
			}
			// handle other types of form notices
		  } else {
			  return;
		  }
	}
}