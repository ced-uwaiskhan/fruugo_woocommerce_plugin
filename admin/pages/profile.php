<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

// header file.
require_once CED_FRUUGO_DIRPATH . 'admin/pages/header.php';
?>
<div id="ced_fruugo_marketplace_loader" class="loading-style-bg" style="display: none;">
	<img src="<?php esc_html_e(plugin_dir_url( __dir__ )); ?>/images/BigCircleBall.gif">
</div>
<?php
	global $ced_fruugo_helper;
if ( ! session_id() ) {
	session_start();
}
if ( isset( $_SESSION['ced_fruugo_validation_notice'] ) ) {
	$value = $_SESSION['ced_fruugo_validation_notice'];
	$ced_fruugo_helper->umb_print_notices( $value );
	unset( $_SESSION['ced_fruugo_validation_notice'] );
}
?>
<?php
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}



class CED_FRUUGO_Profile_Table_List extends WP_List_Table {

	
	
	
	/** Class constructor */
	public function __construct() {
// 		ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
		parent::__construct(
			array(
				'singular' => __( 'Profile', 'ced-fruugo' ), // singular name of the listed records
				'plural'   => __( 'Profiles', 'ced-fruugo' ), // plural name of the listed records
				'ajax'     => false, // does this table support ajax?
			)
		);
	}

	/**
	 * Retrieve Fruugo profile details
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public function get_profiles( $per_page = 5, $page_number = 1 ) {

		global $wpdb;
		// $prefix    = $wpdb->prefix . CED_FRUUGO_PREFIX;
		// $tableName = $prefix . '_fruugoprofiles';

		// $sql  = "SELECT `id`,`name`,`active`,`marketplace` FROM `$tableName` ORDER BY `id` DESC";
		// $sql .= " LIMIT $per_page";
		// $sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

		// $result = $wpdb->get_results( $sql, 'ARRAY_A' );

		//$result = $wpdb->get_results($wpdb->prepare( "SELECT `id`,`name`,`active`,`marketplace`, COUNT(p.`post_id`) as 'product_count' FROM {$wpdb->prefix}ced_fruugo_fruugoprofiles pp INNER JOIN {$wpdb->prefix}postmeta p ON (p.`meta_value` = pp.`id`) ORDER BY `id` DESC"),'ARRAY_A');
		$result = $wpdb->get_results($wpdb->prepare( "SELECT `id`,`name`,`active`,`marketplace`, COUNT(p.`post_id`) as 'product_count' FROM {$wpdb->prefix}ced_fruugo_fruugoprofiles pp LEFT JOIN {$wpdb->prefix}postmeta p ON (p.`meta_value` = pp.`id`) GROUP BY `id` "),'ARRAY_A');
		//$result = $wpdb->get_results( $wpdb->prepare( "SELECT `id`,`name`,`active`,`marketplace` FROM {$wpdb->prefix}ced_fruugo_fruugoprofiles ORDER BY `id` DESC"), 'ARRAY_A' ); 
		//echo '<pre>';print_r($result); die('<br>aaaa');
		return $result;

	}

	/**
	 * Function to count number of responses in result
	 */
	public function get_count() {
		global $wpdb;
		// $prefix    = $wpdb->prefix . CED_FRUUGO_PREFIX;
		// $tableName = $prefix . '_fruugoprofiles';
		// $sql       = "SELECT * FROM `$tableName`";
		// $result    = $wpdb->get_results( $sql, 'ARRAY_A' );
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ced_fruugo_fruugoprofiles"), 'ARRAY_A' );
		// echo '<pre>'; print_r($result); die('<br>aaaa');
		return count( $result );
	}

	/** Text displayed when no customer data is available */
	public function no_items() {
		esc_html_e( 'No profiles avaliable.', 'ced-fruugo' );
	}


	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array  $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'marketplace':
			case 'active':
				if ( $item[ $column_name ] ) {
					return __( 'enable', 'ced-fruugo' );
				} else {
					return __( 'disable', 'ced-fruugo' );

				}
			case 'product_count':
				if($item[ $column_name]){
					return __($item[$column_name],'ced-fruugo');
				}
				// return $item[ $column_name ];
			default:
				//return print_r( $item, true ); // Show the whole array for troubleshooting purposes
		}
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="profile_ids[]" value="%s" />',
			$item['id']
		);
	}


	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	public function column_name( $item ) {
		$title = '<strong>' . $item['name'] . '</strong>';
		if (isset($_REQUEST['page'])) {
		$actions = array(
			'edit'   => sprintf( '<a href="?section=profile-view&page=%s&action=%s&profileID=%s">Edit</a>', esc_attr( sanitize_text_field($_REQUEST['page']) ), 'edit', $item['id'] ),
			'delete' => sprintf( '<a href="?section=profile&page=%s&action=%s&profileID=%s">Delete</a>', esc_attr( sanitize_text_field($_REQUEST['page']) ), 'delete', $item['id'] ),
		);
		}
		return $title . $this->row_actions( $actions );
	}


	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'     => '<input type="checkbox" />',
			'name'   => __( 'Name', 'ced-fruugo' ),

			'active' => __( 'Status', 'ced-fruugo' ),
			'product_count' => __( 'Product Count', 'ced-fruugo' ),
		);
	
		$columns = apply_filters( 'ced_fruugo_alter_feed_table_columns', $columns );
		return $columns;
	}


	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array();
		return $sortable_columns;
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'bulk-delete'  => 'Delete',
		);
		return $actions;
	}


	
	// <form id="" method="post">

	// $product_lister->search_box( 'Search profile', 'post_id' ); 

	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {

		global $wpdb;

		$per_page = apply_filters( 'ced_fruugo_list_profiles_per_page', 10 );
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		// Column headers
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$current_page = $this->get_pagenum();
		if ( 1 < $current_page ) {
			$offset = $per_page * ( $current_page - 1 );
		} else {
			$offset = 0;
		}

		$this->items = self::get_profiles( $per_page, $current_page );

		$count = self::get_count();

		// Set the pagination
		$this->set_pagination_args(
			array(
				'total_items' => $count,
				'per_page'    => $per_page,
				'total_pages' => ceil( $count / $per_page ),
			)
		);

		if ( ! $this->current_action() ) {
			$this->items = self::get_profiles( $per_page, $current_page );
			$this->renderHTML();
		} else {
			$this->process_bulk_action();
		}

	}

	/**
	 * Function to get changes in html
	 */
	public function renderHTML() {
		?>
			
		<div class="ced_fruugo_wrap ced_fruugo_wrap_extn">
			<h2 class="ced_fruugo_setting_header"><?php esc_html_e( 'Profiles', 'ced-fruugo' ); ?></h2>
			<?php echo '<a href="' . esc_attr(get_admin_url()) . 'admin.php?page=ced_fruugo&section=profile-view&action=add_new" class="button button-ced_fruggo page-title-action">Add Profile</a>'; ?>
			<div>
				<?php
				if ( ! session_id() ) {
					session_start();
				}
				if ( isset( $_SESSION['ced_fruugo_validation_notice'] ) ) {
					$value = $_SESSION['ced_fruugo_validation_notice'];
					unset( $_SESSION['ced_fruugo_validation_notice'] );
				}
				?>
				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								
								$this->display();
								wp_nonce_field( 'fruugo_profile', 'ced_fruugo_profile_nonce' );
								?>
							
							</form>

						</div>
					</div>
					<div class="clear"></div>
				</div>
				<br class="clear">
			</div>
		</div>
		<?php
	}

	/**
	 * Function to process bulk action
	 */
	public function process_bulk_action() {
		if ( ! session_id() ) {
			session_start();
		}
		/** Render configuration setup html of fruugo */
		if ( 'edit' === $this->current_action() || ( isset( $_GET['action'] ) && 'edit' === $_GET['action'] ) ) {
			require_once CED_FRUUGO_DIRPATH . 'admin/partials/profile-view.php';
		}

		if ( 'add_new' === $this->current_action() || ( isset( $_GET['action'] ) && 'add_new' === $_GET['action'] ) ) {
			require_once CED_FRUUGO_DIRPATH . 'admin/partials/profile-view.php';
		}

		if ( 'delete' === $this->current_action() || ( isset( $_GET['action'] ) && 'delete' === $_GET['action'] ) ) {

			$profileID = isset($_GET['profileID']) ? sanitize_text_field( $_GET['profileID'] ):'';
			global $wpdb;
			$prefix       = $wpdb->prefix . CED_FRUUGO_PREFIX;
			$tableName    = $prefix . '_fruugoprofiles';
			$deleteStatus = $wpdb->delete( $tableName, array( 'id' => $profileID ) );
			if ( $deleteStatus ) {
				$notice['message']                        = __( 'Profile Deleted Successfully.', 'ced-fruugo' );
				$notice['classes']                        = 'notice notice-success';
				$validation_notice[]                      = $notice;
				$_SESSION['ced_fruugo_validation_notice'] = $validation_notice;
			} else {
				$notice['message']                        = __( 'Some Error Encountered.', 'ced-fruugo' );
				$notice['classes']                        = 'notice notice-error';
				$validation_notice[]                      = $notice;
				$_SESSION['ced_fruugo_validation_notice'] = $validation_notice;
			}

			$redirectURL = get_admin_url() . 'admin.php?section=profile&page=ced_fruugo';
			wp_redirect( $redirectURL );
		}

		if ( 'bulk-delete' === $this->current_action() ) {
			
			if ( ! isset( $_POST['ced_fruugo_profile_nonce'] ) || ! wp_verify_nonce(  wp_unslash( sanitize_text_field($_POST['ced_fruugo_profile_nonce'] ) ), 'fruugo_profile' ) ) {
				return;
			}
			
			if ( isset( $_POST['profile_ids'] ) ) {
				$sanitized_array = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
				$feedsToDelete   = $sanitized_array['profile_ids'];

				global $wpdb;
				// $prefix    = $wpdb->prefix . CED_FRUUGO_PREFIX;
				// $tableName = $prefix . '_fruugoprofiles';
				// $sql       = 'DELETE FROM `' . $tableName . '` WHERE `id` IN (';
				// foreach ( $feedsToDelete as $id ) {
				// 	$sql .= $id . ',';
				// }

				// $sql          = rtrim( $sql, ',' );
				// $sql         .= ')';
				// $deleteStatus = $wpdb->get_results( $query, 'ARRAY_A' );
				foreach ( $feedsToDelete as $id ) {
					$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}ced_fruugo_fruugoprofiles WHERE `id` = %d", $id ) );
					//print_r($wpdb);
				}
				//die;
				// $table_name = $wpdb->prefix . CED_FRUUGO_PREFIX . '_fruugoprofiles';
				// $query      = "SELECT `id`, `name` FROM `$table_name` WHERE 1";
				// $profiles   = $wpdb->get_results( $query, 'ARRAY_A' );
				if ( $deleteStatus ) {
					$notice['message']                        = __( 'Profiles Deleted Successfully.', 'ced-fruugo' );
					$notice['classes']                        = 'notice notice-success';
					$validation_notice[]                      = $notice;
					$_SESSION['ced_fruugo_validation_notice'] = $validation_notice;
				} else {
					$notice['message']                        = __( 'Some Error Encountered.', 'ced-fruugo' );
					$notice['classes']                        = 'notice notice-error';
					$validation_notice[]                      = $notice;
					$_SESSION['ced_fruugo_validation_notice'] = $validation_notice;
				}

				$redirectURL = get_admin_url() . 'admin.php?section=profile&page=ced_fruugo';
				wp_redirect( $redirectURL );
			} else {
					$notice['message']                        = __( 'Please select atleast one profile to delete it.', 'ced-fruugo' );
					$notice['classes']                        = 'notice notice-error';
					$validation_notice[]                      = $notice;
					$_SESSION['ced_fruugo_validation_notice'] = $validation_notice;
					$redirectURL                              = get_admin_url() . 'admin.php?section=profile&page=ced_fruugo';
					wp_redirect( $redirectURL );
			}
		}

	}
}
$ced_fruugo_profile_table_list = new CED_FRUUGO_Profile_Table_List();
$ced_fruugo_profile_table_list->prepare_items();


