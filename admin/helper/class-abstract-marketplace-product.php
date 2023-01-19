<?php
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**

 * Main class for handling reqests.
 *
 * @since      1.0.0
 *
 * @package    Woocommerce fruugo Integration
 * @subpackage Woocommerce fruugo Integration/helper
 */

if ( ! class_exists( 'CED_FRUUGO_Abstract_Product' ) ) :

	/**
	 * Single product related functionality.

	 Manage all single product related functionality required for listing product on marketplaces.

	 @since      1.0.0
	 @package    Woocommerce fruugo Integration
	 @subpackage Woocommerce fruugo Integration/helper
	 */
	class CED_FRUUGO_Abstract_Product {

		/**
		 * Product id.
		 *
		 * @since 1.0.0
		 * @var int  product id.
		 */
		public $pro_id = '';

		/**
		 * Catching product data.
		 *
		 * @since 1.0.0
		 * @var object   product data.
		 */
		public $product_data = '';

		/**
		 * Caching profile details.
		 *
		 * @since 1.0.0
		 */
		public $_profileDetail;

		/**
		 * Constructor for fetching product detail
		 * required for uploading product.
		 *
		 * @since 1.0.0
		 * @param int $pro_id
		 */
		public function __construct( $pro_id = '' ) {

			$this->pro_id = $pro_id;
		}

		/**
		 * Store product id for this class instance.
		 *
		 * @since 1.0.0
		 * @param int $pro_id
		 */
		public function set_id( $pro_id ) {
			$this->pro_id = $pro_id;
		}

		/**
		 * Fetching stored product data.
		 *
		 * @since 1.0.0
		 */
		public function get_product_data() {

			$product_data = $this->product_data;

			if ( ! is_null( $product_data ) && is_object( $product_data ) ) {
				$cached_id     = isset( $product_data->id ) ? intval( $product_data->id ) : '';
				$cached_pro_id = isset( $this->pro_id ) ? intval( $this->pro_id ) : '';

				if ( ! is_null( $cached_pro_id ) && $cached_pro_id == $cached_id ) {
					return $product_data;
				}
			} else {
				if ( ! is_null( $this->pro_id ) ) {
					$product = wc_get_product( $this->pro_id );
					if ( ! is_wp_error( $product ) && is_object( $product ) ) {
						$this->product_data = $product;
						return $this->product_data;
					}
				}
			}
			return false;
		}

		/**
		 * Prepare an array of bullet points if available
		 * for the given product id or $this->pro_id.
		 *
		 * @since 1.0.0
		 * @param int   product id.
		 */
		public function prepare_bullet_points_array( $pro_id = '' ) {
			if ( empty( $pro_id ) ) {
				$pro_id = $this->pro_id;
			}
			if ( ! empty( $pro_id ) ) {
				$bullets_array = array();
				for ( $i = 1;$i < 6;$i++ ) {
					$bullet = get_post_meta( $this->pro_id, "_umb_bullet_$i", true );
					if ( ! empty( $bullet ) && ! is_null( $bullet ) ) {
						$bullets_array[] = esc_attr( $bullet );
					}
				}
				return $bullets_array;
			}
			return false;
		}

		/**
		 * Fetching conditional package dimensions.
		 *
		 * @since 1.0.0
		 * @param object product object
		 * @param string length|width|height
		 * @return float dimension in inches
		 */
		public function get_conditional_package( $_product, $which ) {
			$proid = $this->pro_id;
			if ( empty( $proid ) || ! is_object( $_product ) || is_null( $_product ) ) {
				return false;
			}

			switch ( $which ) {
				case 'length':
					$custom_length = get_post_meta( $this->pro_id, '_umb_p_length', true );
					if ( $custom_length > 0 &&  true !== $custom_length) {
						return $custom_length;
					} else {
						return wc_get_weight( $_product->length, 'lbs' );
					}
					break;
				case 'width':
					$custom_width = get_post_meta( $this->pro_id, '_umb_p_width', true );
					if ( $custom_width > 0 && true !== $custom_width) {
						return $custom_width;
					} else {
						return wc_get_weight( $_product->width, 'lbs' );
					}
					break;
				case 'height':
					$custom_height = get_post_meta( $this->pro_id, '_umb_p_height', true );
					if ( $custom_height > 0 && true !== $custom_height  ) {
						return $custom_height;
					} else {
						return wc_get_weight( $_product->height, 'lbs' );
					}
					break;
				default:
					return 0;
				break;
			}
		}

		/**
		 * Set meta values.
		 *
		 * @since 1.0.0
		 */
		public function _getMeta( $FieldOptions ) {

			if ( is_array( $FieldOptions ) ) {

				$metaKey = isset( $FieldOptions['MetaKey'] ) ? esc_attr( $FieldOptions['MetaKey'] ) : '';
				if ( is_null( $metaKey ) ) {
					return false;
				}

				$proid = isset( $FieldOptions['proid'] ) ? $FieldOptions['proid'] : 0;

				if ( ! $proid ) {
					$proid = $this->pro_id;
				}

				$default = isset( $FieldOptions['Default'] ) ? $FieldOptions['Default'] : '';
				if ( 'ProID' ==  $default ) {
					$default = $proid;
				}

				$metaValue = get_post_meta( $proid, $metaKey );
				if ( isset( $metaValue[0] ) ) {
					$metaValue = $metaValue[0];
				}

				if ( $metaValue ) {
					if ( '_weight' ==$metaKey ) {
						$metaValue = $metaValue ? $metaValue : 0;
						if ( $metaValue ) {
							$metaValue = wc_get_weight( $metaValue, 'lbs' );
							if ( $metaValue ) {
								return round( $metaValue, 2 );
							}
						}
						return false;
					} elseif ( '_umb_mpr' == $metaKey ) {

						if ( ! empty( $metaValue ) ) {
							return $metaValue;
						}
						$identifier_type = get_post_meta( $proid, '_umb_id_type', true );
						if ( 'UPC' == $identifier_type ) {
							$identifier_type_val = get_post_meta( $proid, '_umb_id_val', true );
							if ( ! empty( $identifier_type_val ) ) {
								return $identifier_type_val;
							}
						}
					}
					return $metaValue;
				} else {
					return $default;
				}
			}
			return false;
		}

	}
endif;
