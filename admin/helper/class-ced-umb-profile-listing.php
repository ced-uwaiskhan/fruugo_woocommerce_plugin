<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}


if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * 
 * Profile listing.
 *
 * @since      1.0.0
 *
 * @package    Woocommerce fruugo Integration
 * @subpackage Woocommerce fruugo Integration/admin/helper
 */

if ( ! class_exists( 'CED_FRUUGO_Profile_Lister' ) ) :

	/**
	 * Product listing on manage product.

	 List profiles
	 create profile
	 edit profile
	 delete profile

	 @since      1.0.0
	 @package    Woocommerce fruugo Integration
	 @subpackage Woocommerce fruugo Integration/admin/helper
	 
	 */
	class CED_FRUUGO_Profile_Lister extends WP_List_Table {

		/**
		 * Table name.
		 *
		 * @since 1.0.0
		 */
		private $_table;

		/**
		 * Per page entries.
		 *
		 * @since 1.0.0
		 */
		private $_perpage = 15;

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			global $status, $page, $wpdb;

			parent::__construct(
				array(
					'singular' => 'ced_fruugo_profile',
					'plural'   => 'ced_fruugo_profiles',
					'ajax'     => true,
				)
			);

			$this->_table = $wpdb->prefix . CED_FRUUGO_PREFIX . '_fruugoprofiles';
		}

		/**
		 * Columns of the profile listing.
		 *
		 * @since 1.0.0
		 * @see WP_List_Table::get_columns()
		 */
		public function get_columns() {
			$columns = array(
				'cb'          => '<input type="checkbox" />',
				'name'        => __( 'Name', 'ced-fruugo' ),
				'marketplace' => __( 'Marketplace', 'ced-fruugo' ),
				'status'      => __( 'Status', 'ced-fruugo' ),
				'action'      => __( 'Action', 'ced-fruugo' ),
			);
			return $columns;
		}

		/**
		 * Preparing the table data for all profiles.
		 *
		 * @since 1.0.0
		 * @see WP_List_Table::prepare_items()
		 */
		public function prepare_items() {
			global $wpdb;

			$columns               = $this->get_columns();
			$hidden                = array();
			$sortable              = $this->get_sortable_columns();
			$this->_column_headers = array( $columns, $hidden, $sortable );
			$current_page          = $this->get_pagenum();

			$query       = "SELECT COUNT( 1 )FROM `$this->_table` WHERE 1;";
			$total_items = intval($wpdb->get_var( $wpdb->prepare( "SELECT COUNT( 1 ) FROM {$wpdb->prefix}ced_fruugo_fruugoprofiles WHERE 1"), 'ARRAY_A' ));
			//$total_items = intval( $wpdb->get_var( $query ) );
			$this->set_pagination_args(
				array(
					'total_items' => $total_items,
					'per_page'    => $this->_perpage,
					'total_pages' => ceil( $total_items / $this->_perpage ),
				)
			);
		}

		/**
		 * Profiles available for listing.
		 *
		 * @since 1.0.0
		 * @see WP_List_Table::has_items()
		 */
		public function has_items() {
			global $wpdb;
			$current_page = $this->get_pagenum();
			$from         = ( 1 == $current_page ) ? 0 : ( $current_page - 1 ) * $this->_perpage;
			$to           = ( 1 == $current_page ) ? $this->_perpage : ( $current_page * $this->_perpage );
			$query        = "SELECT `id` FROM `$this->_table` LIMIT $from,$to;";
			$results      = $wpdb->get_var( $wpdb->prepare( "SELECT `id` FROM {$wpdb->prefix}ced_fruugo_fruugoprofiles LIMIT %d,%d", $from, $to), 'ARRAY_A' );
			//$results      = $wpdb->get_var( $query );
			if ( $results ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Display profiles.
		 *
		 * @since 1.0.0
		 * @see WP_List_Table::display_rows()
		 */
		public function display_rows() {

			$columns = $this->get_columns();
			$data    = $this->get_profile_listing_data();
			if ( is_array( $data ) ) {
				if ( count( $data ) ) {
					foreach ( $data as $row ) {
						echo '<tr class="ced_fruugo_profile_row">';
						foreach ( $columns as $column_id => $column_name ) {
							$this->print_profile_column( $column_id, $row );
						}
						echo '</tr>';
					}
				} else {
					echo '<tr><td colspan="4">' . esc_html_e( 'No profile found!', 'ced-fruugo' ) . '</td></tr>';
				}
			} else {
				echo '<tr><td colspan="4">' . esc_html_e( 'No profile found!', 'ced-fruugo' ) . '</td></tr>';
			}
		}

		/**
		 * Print profile row.
		 *
		 * @since 1.0.0
		 */
		public function print_profile_column( $column_name, $row = array() ) {

			if ( is_array( $row ) ) {
				$row_id    = isset( $row['id'] ) ? intval( $row['id'] ) : 0;
				$edit_link = get_admin_url() . 'admin.php?page=ced_fruugo&section=profile-view&action=edit&id=' . $row_id;

				$classes = "$column_name column-$column_name";
				$data    = 'data-colname="' . $column_name . '"';
				switch ( $column_name ) {

					case 'cb':
						echo '<td class="' . esc_html($classes) . '" ' . esc_html($data) . '>';
						echo '<input id="cb-select-' . esc_html($row_id) . '" type="checkbox" name="post[]" value="' . esc_html($row_id) . '" />';
						echo '</td>';
						break;
					case 'name':
						$profile_name = isset( $row['name'] ) ? esc_attr( $row['name'] ) : '';
						echo '<td>' . esc_html($profile_name) . '</td>';
						break;
					case 'marketplace':
						$marketplace = isset( $row['marketplace'] ) ? esc_attr( $row['marketplace'] ) : '';
						echo '<td>' . esc_html($marketplace) . '</td>';
						break;
					case 'status':
						$status = isset( $row['active'] ) ? $row['active'] : true;
						if ( 1 ==  $status ) {
							$status = 'Active';
						} else {
							$status = 'Not active';
						}
						echo '<td>' . esc_html($status) . '</td>';
						break;
					case 'action':
						echo '<td>';
						echo '<a href="' . esc_html($edit_link) . '" class="ced_fruugo_profile_edit">' . esc_html_e( 'Edit', 'ced-fruugo' ) . '</a>';
						echo '</td>';
				}
			}
		}

		/**
		 * Profile listing data.
		 *
		 * @since 1.0.0
		 */
		public function get_profile_listing_data() {

			global $wpdb;
			$current_page = $this->get_pagenum();
			$from         = ( 1 == $current_page ) ? 0 : ( $current_page - 1 ) * $this->_perpage;
			$to           = ( 1 == $current_page ) ? $this->_perpage : ( $current_page * $this->_perpage );
			$query        = "SELECT `id`,`name`,`active`,`marketplace` FROM `$this->_table` LIMIT $from,$to;";
			//$results      = $wpdb->get_results( $query, 'ARRAY_A' );
			$results = $wpdb->get_var( $wpdb->prepare( "SELECT `id`,`name`,`active`,`marketplace` FROM {$wpdb->prefix}ced_fruugo_fruugoprofiles LIMIT %d,%d", $from, $to), 'ARRAY_A' );
			if ( is_array( $results ) ) {
				return $results;
			} else {
				return array();
			}
		}
	}
endif;
