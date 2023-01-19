<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Product meta related functionalities.
 *
 * @since      1.0.0
 *
 * @package    Woocommerce fruugo Integration
 * @subpackage Woocommerce fruugo Integration/admin/helper
 */

if ( ! class_exists( 'CED_FRUUGO_ProductMeta' ) ) :

	/**
	 * Product meta fields get/set functionalities
	 * for each framework.
	 *
	 @since      1.0.0
	 @package    Woocommerce fruugo Integration
	 @subpackage Woocommerce fruugo Integration/admin/helper
	 
	 */
	class CED_FRUUGO_ProductMeta {

		/**
		 * The Instace of CED_FRUUGO_ProductMeta.
		 *
		 * @since    1.0.0
		 * 
		 * @var      $_instance   The Instance of CED_FRUUGO_ProductMeta class.
		 */
		private static $_instance;

		/**
		 * CED_FRUUGO_ProductMeta Instance.
		 *
		 * Ensures only one instance of CED_FRUUGO_ProductMeta is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 * @return CED_FRUUGO_ProductMeta instance.
		 */
		public static function get_instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Get conditional price.
		 *
		 * @since 1.0.0
		 */
		public function get_conditional_price( $ProId, $marketplace ) {

			if ( $ProId ) {
				$priceCondition = get_post_meta();
			}
		}
	}
endif;
