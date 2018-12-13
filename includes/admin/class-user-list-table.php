<?php

namespace BCE_User_List\Includes\Admin;
use BCE_User_List\Includes\Libraries;

/**
 * Class for displaying registered WordPress Users
 * in a WordPress-like Admin Table with row actions to
 * perform user meta opeations
 *
 *
 * @link       https://github.com/kenshin23/bce-user-list
 * @since      1.0.0
 *
 * @author     Carlos Paparoni
 */
class User_List_Table extends Libraries\WP_List_Table  {

	/**
	 * The text domain of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_text_domain    The text domain of this plugin.
	 */
	protected $plugin_text_domain;

    /*
	 * Call the parent constructor to override the defaults $args
	 *
	 * @param string $plugin_text_domain	Text domain of the plugin.
	 *
	 * @since 1.0.0
	 */
	public function __construct( $plugin_text_domain ) {

		$this->plugin_text_domain = $plugin_text_domain;

		parent::__construct( array(
				'plural'	=>	'users',	// Plural value used for labels and the objects being listed.
				'singular'	=>	'user',		// Singular label for an object being listed, e.g. 'post'.
				'ajax'		=>	false,		// If true, the parent class will call the _js_vars() method in the footer
			) );
	}

	/**
	 * Prepares the list of items for displaying.
	 *
	 * Query, filter data, handle sorting, and pagination, and any other data-manipulation required prior to rendering
	 *
	 * @since   1.0.0
	 */
	public function prepare_items() {

		// check if a search was performed.
		$user_search_key = isset( $_REQUEST['s'] ) ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';

		$this->_column_headers = $this->get_column_info();

		// check and process any actions such as bulk actions.
		$this->handle_table_actions();

		// fetch table data
		$table_data = $this->fetch_table_data();
		// filter the data in case of a search.
		if( $user_search_key ) {
			$table_data = $this->filter_table_data( $table_data, $user_search_key );
		}

		// required for pagination
		$users_per_page = $this->get_items_per_page( 'users_per_page' );
		$table_page = $this->get_pagenum();

		// provide the ordered data to the List Table.
		// we need to manually slice the data based on the current pagination.
		$this->items = array_slice( $table_data, ( ( $table_page - 1 ) * $users_per_page ), $users_per_page );

		// set the pagination arguments
		$total_users = count( $table_data );
		$this->set_pagination_args( array (
					'total_items' => $total_users,
					'per_page'    => $users_per_page,
					'total_pages' => ceil( $total_users/$users_per_page )
				) );
	}

	/**
	 * Get a list of columns. The format is:
	 * 'internal-name' => 'Title'
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_columns() {

		$table_columns = array(
			// Bonus point 2:
			'cb'				=> '<input type="checkbox" />', // to display the checkbox.
			'user_login'		=>	__( 'User Login', $this->plugin_text_domain ),
			'first_name'		=>	__( 'First Name', $this->plugin_text_domain ),
			'last_name'			=>	__( 'Last Name', $this->plugin_text_domain ),
			'user_email'		=>	__( 'Email', $this->plugin_text_domain ),
			'role'				=>	__( 'Role', $this->plugin_text_domain ),
		);

		return $table_columns;
	}

	/**
	 * Get a list of sortable columns. The format is:
	 * 'internal-name' => 'orderby'
	 * or
	 * 'internal-name' => array( 'orderby', true )
	 *
	 * The second format will make the initial sorting order be descending
	 *
	 * @since 1.1.0
	 *
	 * @return array
	 */
	protected function get_sortable_columns() {

		/*
		 * actual sorting still needs to be done by prepare_items.
		 * specify which columns should have the sort icon.
		 *
		 * key => value
		 * column name_in_list_table => columnname in the db
		 */
		$sortable_columns = array (
				'ID' => array( 'ID', true ),
				'first_name'=>'first_name',
				'last_name'=>'last_name'
			);

		return $sortable_columns;
	}

	/**
	 * Text displayed when no user data is available
	 *
	 * @since   1.0.0
	 *
	 * @return void
	 */
	public function no_items() {
		_e( 'No users avaliable.', $this->plugin_text_domain );
	}

	/*
	 * Fetch table data from the WordPress database.
	 *
	 * @since 1.0.0
	 *
	 * @return	Array
	 */

	public function fetch_table_data() {

		global $wpdb;

		$wpdb_table = $wpdb->prefix . 'users';
		$wpdb_usermeta = $wpdb->prefix . 'usermeta';
		$orderby = ( isset( $_GET['orderby'] ) ) ? esc_sql( $_GET['orderby'] ) : 'user_registered';
		$order = ( isset( $_GET['order'] ) ) ? esc_sql( $_GET['order'] ) : 'ASC';

		$user_query = "SELECT
							user_login,
							display_name,
							user_registered,
							user_email,
							ID,
						    m1.meta_value AS first_name,
						    m2.meta_value AS last_name
						FROM $wpdb_table AS u1
						JOIN $wpdb_usermeta m1 ON (m1.user_id = u1.id AND m1.meta_key = 'first_name')
						JOIN $wpdb_usermeta m2 ON (m2.user_id = u1.id AND m2.meta_key = 'last_name')
						ORDER BY $orderby $order
						";

		// query output_type will be an associative array with ARRAY_A.
		$query_results = $wpdb->get_results( $user_query, ARRAY_A  );

		// return result array to prepare_items.
		return $query_results;
	}

	/*
	 * Filter the table data based on the user search key
	 *
	 * @since 1.0.0
	 *
	 * @param array $table_data
	 * @param string $search_key
	 * @returns array
	 */
	public function filter_table_data( $table_data, $search_key ) {
		$filtered_table_data = array_values( array_filter( $table_data, function( $row ) use( $search_key ) {
			foreach( $row as $row_val ) {
				if( stripos( $row_val, $search_key ) !== false ) {
					return true;
				}
			}
		} ) );

		return $filtered_table_data;

	}

	/**
	 * Render a column when no column specific method exists.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {

		switch ( $column_name ) {
			case 'display_name':
			case 'user_registered':
			case 'ID':
				return $item[$column_name];
			default:
			  return $item[$column_name];
		}
	}

	/**
	 * Get value for checkbox column.
	 *
	 * The special 'cb' column
	 *
	 * @param object $item A row's data
	 * @return string Text to be placed inside the column <td>.
	 */
	protected function column_cb( $item ) {
		return sprintf(
				'<label class="screen-reader-text" for="user_' . $item['ID'] . '">' . sprintf( __( 'Select %s' ), $item['user_login'] ) . '</label>'
				. "<input type='checkbox' name='users[]' id='user_{$item['ID']}' value='{$item['ID']}' />"
			);
	}

	/*
	 * Method for rendering the user_login column.
	 *
	 * Adds row action links to the user_login column.
	 *
	 * @param object $item A singular item (one full row's worth of data).
	 * @return string Text to be placed inside the column <td>.
	 *
	 */
	protected function column_user_login( $item ) {

		/*
		 *  Build usermeta row actions.
		 *
		 * e.g. /users.php?page=bce-user-list&action=view_usermeta&user=18&_wpnonce=1984253e5e
		 */

		$admin_page_url =  admin_url( 'users.php' );

		// row actions to edit user.
		$query_args_edit_user = array(
			'page'		=>  wp_unslash( $_REQUEST['page'] ),
			'action'	=> 'edit_user',
			'user_id'		=> absint( $item['ID']),
			'_wpnonce'	=> wp_create_nonce( 'edit_user_nonce' ),
		);
		$edit_user_link = esc_url( add_query_arg( $query_args_edit_user, $admin_page_url ) );
		$actions['edit_user'] = '<a href="' . $edit_user_link . '">' . __( 'Edit User', $this->plugin_text_domain ) . '</a>';

		// row actions to deactivate user.
		$query_args_deactivate_user = array(
			'page'		=>  wp_unslash( $_REQUEST['page'] ),
			'action'	=> 'deactivate_user',
			'user_id'	=> absint( $item['ID']),
			'_wpnonce'	=> wp_create_nonce( 'deactivate_user_nonce' ),
		);
		$deactivate_user_link = esc_url( add_query_arg( $query_args_deactivate_user, $admin_page_url ) );
		$actions['deactivate_user'] = '<a href="' . $deactivate_user_link . '">' . __( 'Deactivate', $this->plugin_text_domain ) . '</a>';


		$row_value = '<strong>' . $item['user_login'] . '</strong>';
		return $row_value . $this->row_actions( $actions );
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @since    1.0.0
	 *
	 * @return array
	 */
	public function get_bulk_actions() {

		/*
		 * on hitting apply in bulk actions the url paramas are set as
		 * ?action=bulk-deactivate&paged=1&action2=-1
		 *
		 * action and action2 are set based on the triggers above or below the table
		 *
		 */
		 $actions = array(
			 'bulk-deactivate' => 'Deactivate'
		 );

		 return $actions;
	}

	/**
	 * Process actions triggered by the user
	 *
	 * @since    1.0.0
	 *
	 */
	public function handle_table_actions() {

		/*
		 * Note: Table bulk_actions can be identified by checking $_REQUEST['action'] and $_REQUEST['action2']
		 *
		 * action - is set if checkbox from top-most select-all is set, otherwise returns -1
		 * action2 - is set if checkbox the bottom-most select-all checkbox is set, otherwise returns -1
		 */

		// check for individual row actions
		$the_table_action = $this->current_action();

		if ( 'deactivate_user' === $the_table_action ) {
			$nonce = wp_unslash( $_REQUEST['_wpnonce'] );
			// verify the nonce.
			if ( ! wp_verify_nonce( $nonce, 'deactivate_user_nonce' ) ) {
				$this->invalid_nonce_redirect();
			}
			else {
				$this->page_deactivate_user( absint( $_REQUEST['user_id']) );
				$this->graceful_exit();
			}
		}

		if ( 'edit_user' === $the_table_action ) {
			echo "42095t6842356-09423865-094238623";
			$nonce = wp_unslash( $_REQUEST['_wpnonce'] );
			// verify the nonce.
			if ( ! wp_verify_nonce( $nonce, 'edit_user_nonce' ) ) {
				$this->invalid_nonce_redirect();
			}
			else {
				$this->page_edit_user( absint( $_REQUEST['user_id']) );
				$this->graceful_exit();
			}
		}

		// check for table bulk actions
		if ( ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'bulk-deactivate' ) || ( isset( $_REQUEST['action2'] ) && $_REQUEST['action2'] === 'bulk-deactivate' ) ) {

			$nonce = wp_unslash( $_REQUEST['_wpnonce'] );
			// verify the nonce.
			/*
			 * Note: the nonce field is set by the parent class
			 * wp_nonce_field( 'bulk-' . $this->_args['plural'] );
			 *
			 */
			if ( ! wp_verify_nonce( $nonce, 'bulk-users' ) ) {
				$this->invalid_nonce_redirect();
			}
			else {
				$this->page_bulk_download( $_REQUEST['users']);
				$this->graceful_exit();
			}
		}

	}

	/**
	 * Edit a user's info:
	 *
	 * @since   1.0.0
	 *
	 * @param int $user_id  user's ID
	 */
	public function page_edit_user( $user_id ) {

		$user = get_user_by( 'id', $user_id );
		include_once( 'views/partials-wp-list-edit-user.php' );
	}

	/**
	 * Deactivate a user.
	 *
	 * @since   1.0.0
	 *
	 * @param int $user_id  user's ID
	 */
	public function page_deactivate_user( $user_id ) {

		$user = get_user_by( 'id', $user_id );
		include_once( 'views/partials-wp-list-deactivate.php' );
	}

	/**
	 * Bulk process users.
	 *
	 * @since   1.0.0
	 *
	 * @param array $bulk_user_ids
	 */
	public function page_bulk_deactivate( $bulk_user_ids ) {
		include_once( 'views/partials-wp-list-bulk-deactivate.php' );
	}

	/**
	 * Stop execution and exit
	 *
	 * @since    1.0.0
	 *
	 * @return void
	 */
	public function graceful_exit() {
		 exit;
	}

	/**
	 * Die when the nonce check fails.
	 *
	 * @since    1.0.0
	 *
	 * @return void
	 */
	public function invalid_nonce_redirect() {
		wp_die( __( 'Invalid Nonce', $this->plugin_text_domain ),
				__( 'Error', $this->plugin_text_domain ),
				array(
						'response' 	=> 403,
						'back_link' =>  esc_url( add_query_arg( array( 'page' => wp_unslash( $_REQUEST['page'] ) ) , admin_url( 'users.php' ) ) ),
					)
		);
	}
}
