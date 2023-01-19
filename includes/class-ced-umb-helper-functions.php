<?php
/**
 * The file that defines the global helper functions using throughout the plugin.
 *
 * @since      1.0.0
 *
 * @package    Woocommerce fruugo Integration
 * @subpackage Woocommerce fruugo Integration/includes
 */
class CED_FRUUGO_Helper {

	/**
	 * The instance of CED_FRUUGO_Helper.
	 *
	 * @since    1.0.0
	 */
	private static $_instance;

	/**
	 * CED_FRUUGO_Helper Instance.
	 *
	 * Ensures only one instance of CED_FRUUGO_Helper is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return CED_FRUUGO_Helper - Main instance.
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Print notices.
	 *
	 * @since 1.0.0
	 */
	public function umb_print_notices( $notices = array() ) {
		if ( count( $notices ) ) {
			foreach ( $notices as $notice_array ) {

				$message = isset( $notice_array['message'] ) ? esc_html( $notice_array['message'] ) : '';
				$classes = isset( $notice_array['classes'] ) ? esc_attr( $notice_array['classes'] ) : 'error is-dismissable';
				if ( ! empty( $message ) ) { ?>
					 <div class="<?php echo esc_attr( $classes ); ?>">
						 <p><?php echo esc_attr( $message ); ?></p>
					 </div>
					<?php
				}
			}
		}
	}

	/**
	 * Get conditional product id.
	 *
	 * @since 1.0.0
	 */
	public function umb_get_product_by( $params ) {
		global $wpdb;

		$where1 = '';
		if ( count( $params ) ) {
			$Flag = false;
			foreach ( $params as $meta_key => $meta_value ) {
				if ( ! empty( $meta_value ) && ! empty( $meta_key ) ) {
					if ( ! $Flag ) {
						//$where1 .= 'meta_key="' . sanitize_key( $meta_key ) . '" AND meta_value="' . $meta_value . '"';
						$product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE 'meta_key'=%s AND 'meta_value'=%s LIMIT 1" , $meta_key, $meta_value ) );
						$Flag       = true;
					} else {
						//$where1 .= ' OR meta_key="' . sanitize_key( $meta_key ) . '" AND meta_value="' . $meta_value . '"';
						$product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE 'meta_key'=%s AND 'meta_value'=%s LIMIT 1" , $meta_key, $meta_value ) );
					}
				}
			}
			if ( $Flag ) {
				//$product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE $where1 LIMIT 1" ) );
				//$profiles       = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table_name}_fruugoprofiles WHERE `active`=%s", 1 ), 'ARRAY_A' );
				if ( $product_id ) {
					return $product_id;
				}
			}
		}
		return false;
	}

	/**
	 * Writing logs.
	 *
	 * @since 1.0.0
	 * @param string $filename
	 * @param string $stringTowrite
	 */
	public function umb_write_logs( $filename, $stringTowrite ) {
		$dirTowriteFile = CED_FRUUGO_LOG_DIRECTORY;
		if (defined('CED_FRUUGO_LOG_DIRECTORY')) {
			if (!is_dir($dirTowriteFile)) {
				if (!mkdir($dirTowriteFile, 0755)) {
					return;
				}
			}
			$fileTowrite = $dirTowriteFile . "/$filename";
			$fp          = fopen($fileTowrite, 'a');
			if (!$fp) {
				return;
			}
			$fr = fwrite($fp, $stringTowrite . "\n");
			fclose($fp);
		} else {
			return;
		}
	}

	/**
	 * Get profile details,
	 *
	 * @since 1.0.0
	 */
	public function ced_fruugo_profile_details( $params = array() ) {
		global $wpdb;
		$profile_name = '';
		if ( isset( $params['id'] ) ) {
			$id        = $params['id'];
			$prefix    = $wpdb->prefix . CED_FRUUGO_PREFIX;
			$tablename = $prefix . '_fruugoprofiles';
			//$profile_name = $wpdb->get_var( $wpdb->prepare( "SELECT `name` FROM $prefix{_fruugoprofiles} WHERE `id` = %s", $id ) );
			$profile_name = $wpdb->get_var( $wpdb->prepare( "SELECT `name` FROM {$wpdb->prefix}ced_fruugo_fruugoprofiles WHERE `id`=%d", $id ));
			// var_dump($profile_name);
			// die;
			
		}
		return $profile_name;
	}

	/**
	 * Get profile details,
	 *
	 * @since 1.0.0
	 */
	public function ced_fruugo_notifcation_mail( $params ) {
		$cronRelatedData = get_option( 'ced_fruugo_cronRelatedData', false );
		if ( isset( $cronRelatedData['ced_fruugo_allow_access_to_dev'] ) ) {
			if ( 'yes' == $cronRelatedData['ced_fruugo_allow_access_to_dev'] ) {
				if ( isset( $params['action'] ) ) {
					$home_url = home_url();

					$action  = $params['action'];
					$issue   = $params['issue'];
					$subject = 'Tracking Mail from Domain: ' . $home_url;

					$to = 'aaronmiller@cedcommerce.com';

					$message  = "Hi CedCommerce,\r\nThis email is send as tracking purpose for update ourselves that client is facing any issue or not.\r\n";
					$message .= "Domain : $home_url\r\n";
					$message .= "Action : $action\r\n";
					$message .= "Error/Issue : $issue\r\nThanks";

					wp_mail( $to, $subject, $message );
				}
			}
		}
	}
}
?>
