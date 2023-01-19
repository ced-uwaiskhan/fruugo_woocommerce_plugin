<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Single product managment related functionality helper class.
 *
 * @since      1.0.0
 *
 * @package    Woocommerce fruugo Integration
 * @subpackage Woocommerce fruugo Integration/admin/helper
 */

if ( ! class_exists( 'CED_FRUUGO_Product_Fields' ) ) :

	/**
	 * Single product related functionality.
	 *
	 * Manage all single product related functionality required for listing product on marketplaces.
	 *
	 * @since      1.0.0
	 * @package    Woocommerce fruugo Integration
	 * @subpackage Woocommerce fruugo Integration/admin/helper
	 * 
	 */
	class CED_FRUUGO_Product_Fields {

		/**
		 * The Instace of CED_FRUUGO_Product_Fields.
		 *
		 * @since    1.0.0
		 * 
		 * @var      $_instance   The Instance of CED_FRUUGO_Product_Fields class.
		 */
		private static $_instance;

		/**
		 * CED_FRUUGO_Product_Fields Instance.
		 *
		 * Ensures only one instance of CED_FRUUGO_Product_Fields is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 * @return CED_FRUUGO_Product_Fields instance.
		 */
		public static function get_instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Adding tab on product edit page.
		 *
		 * @since 1.0.0
		 * @param array $tabs   single product page tabs.
		 * @return array  $tabs
		 */
		public function umb_required_fields_tab( $tabs ) {

			$tabs['umb_fruugo_required_fields'] = array(
				'label'  => __( 'fruugo', 'ced-fruugo' ),
				'target' => 'ced_fruugo_fields',
				'class'  => array( 'show_if_simple', 'ced_fruugo_required_fields' ),
			);

			return $tabs;
		}

		/**
		 * Fields on UMB Required Fields product edit page tab.
		 *
		 * @since 1.0.0
		 */
		public function umb_required_fields_panel() {
			global $post;
			$terms =wp_get_object_terms( $post->ID, 'product_type' );
			if ( $terms ) {
				$product_type = sanitize_title( current( $terms )->name );
			} else {
				$product_type = apply_filters( 'default_product_type', 'simple' );
			}

			if ( 'simple' == $product_type ) {
				require_once CED_FRUUGO_DIRPATH . 'admin/partials/umb_product_fields.php';
			}
		}

		/* For Variable Product */
		public function umb_render_product_fields_html_for_variations( $loop, $variation_data, $variation ) {
			include CED_FRUUGO_DIRPATH . 'admin/partials/umb_product_fields.php';
		}

		public function umb_render_variation_html( $field_array, $loop, $variation ) {
			$requiredInAnyCase = array( '_umb_fruugo_id_type', '_umb_fruugo_id_val', '_umb_fruugo_brand' );
			$type              = esc_attr( $field_array['type'] );
			if ( '_text_input' ==  $type ) {
				$previousValue = get_post_meta( $variation->ID, $field_array['fields']['id'], true );

				if ( in_array( $field_array['fields']['id'], $requiredInAnyCase ) ) {
					$nameToRender = ucfirst( $field_array['fields']['label'] );
					//$nameToRender                  .= '<span class="ced_fruugo_wal_required"> [ ' . __( 'Required', 'ced-fruugo' ) . ' ]</span>';
					$field_array['fields']['label'] = $nameToRender;
				}

				?>
			<p class="form-field _umb_brand_field ">
				<label for="<?php esc_html_e($field_array['fields']['id']); ?>"><?php esc_html_e($field_array['fields']['label']); ?></label>
				<input class="short" style="" name="<?php esc_html_e($field_array['fields']['id']); ?>[<?php esc_html_e($loop); ?>]" id="<?php esc_html_e($field_array['fields']['id']); ?>" value="<?php esc_html_e($previousValue); ?>" placeholder="" 
															   <?php
																if ( 'number' == $type) {
																	echo 'type="number"';
																} else {
																	echo 'type="text"';}
																?>
				> 
				<?php
				if ( '1' == $field_array['fields']['desc_tip'] ) {
					$description = $field_array['fields']['description'];
					echo wc_help_tip( __( $description, 'woocommerce' ) );
				}
				?>
			</p>
				<?php
			} elseif ( '_select' == $type ) {
				$previousValue = get_post_meta( $variation->ID, $field_array['fields']['id'], true );

				if ( in_array( $field_array['fields']['id'], $requiredInAnyCase ) ) {
					$nameToRender = ucfirst( $field_array['fields']['label'] );
					//$nameToRender                  .= '<span class="ced_fruugo_wal_required"> [ ' . esc_html( 'Required', 'ced-fruugo' ) . ' ]</span>';
					$field_array['fields']['label'] = $nameToRender;
				}

				?>
			<p class="form-field _umb_id_type_field ">
				<label for="<?php esc_html_e($field_array['fields']['id']); ?>"><?php esc_html_e($field_array['fields']['label']); ?></label>
				<select id="<?php esc_html_e($field_array['fields']['id']); ?>" name="<?php esc_html_e($field_array['fields']['id']); ?>[<?php esc_html_e($loop); ?>]" class="select short" style="">
					<?php
					foreach ( $field_array['fields']['options'] as $key => $value ) {
						if ( $previousValue == $key ) {
							echo '<option value="' . esc_html($key) . '" selected="selected">' . esc_html($value) . '</option>';
						} else {
							echo '<option value="' . esc_html($key) . '">' . esc_html($value) . '</option>';
						}
					}
					?>
				</select> 
					<?php
					if ( '1' == $field_array['fields']['desc_tip'] ) {
						$description = $field_array['fields']['description'];
						echo wc_help_tip( __( $description, 'woocommerce' ) );
					}
					?>
			</p>
				<?php
			} elseif ( '_checkbox' == $type ) {
				$previousValue = get_post_meta( $variation->ID, $field_array['fields']['id'], true );

				if ( in_array( $field_array['fields']['id'], $requiredInAnyCase ) ) {
					$nameToRender = ucfirst( $field_array['fields']['label'] );
					//$nameToRender                  .= '<span class="ced_fruugo_wal_required"> [ ' . __( 'Required', 'ced-fruugo' ) . ' ]</span>';
					$field_array['fields']['label'] = $nameToRender;
				}

				?>
			<p class="form-field _umb_custom_price_field ">

				<label for="<?php esc_html_e($field_array['fields']['id']); ?>"><?php esc_html_e($field_array['fields']['label']); ?></label>
				<input class="checkbox" style="" name="<?php esc_html_e($field_array['fields']['id']); ?>[<?php esc_html_e($loop); ?>]" id="<?php esc_html_e($field_array['fields']['id']); ?>" value="yes" placeholder="" type="checkbox" 
																  <?php
																	if ( 'yes' == $previousValue  ) {
																		echo 'checked';}
																	?>
				 /> 
				<?php
				if ( '1' == $field_array['fields']['desc_tip'] ) {
					$description = $field_array['fields']['description'];
					echo wc_help_tip( __( $description, 'woocommerce' ) );
				}
				?>
			</p>
				<?php
			} elseif ('lwh' == $type ) {
				?>
			<p class="form-field dimensions_field">
				<label for="<?php esc_html_e($field_array['fields']['id']); ?>"><?php esc_html_e($field_array['fields']['label']); ?></label>
				<span class="wrap">
					<input placeholder="<?php esc_attr_e( 'Length', 'ced-fruugo' ); ?>" class="input-text wc_input_decimal" size="6" type="text" name="<?php esc_html_e($field_array['fields']['id']); ?>[<?php esc_html_e($loop) ; ?>]" value="<?php echo esc_attr( wc_format_localized_decimal( get_post_meta( $variation->ID, $id . '_length', true ) ) ); ?>" />
					<input placeholder="<?php esc_attr_e( 'Width', 'ced-fruugo' ); ?>" class="input-text wc_input_decimal" size="6" type="text" name="<?php esc_html_e($field_array['fields']['id']) ; ?>[<?php esc_html_e($loop) ; ?>]" value="<?php echo esc_attr( wc_format_localized_decimal( get_post_meta( $variation->ID, $id . '_width', true ) ) ); ?>" />
					<input placeholder="<?php esc_attr_e( 'Height', 'ced-fruugo' ); ?>" class="input-text wc_input_decimal last" size="6" type="text" name="<?php esc_html_e($field_array['fields']['id']) ; ?>[<?php esc_html_e($loop) ; ?>]" value="<?php echo esc_attr( wc_format_localized_decimal( get_post_meta( $variation->ID, $id . '_height', true ) ) ); ?>" />
				</span>
				<?php
				if ( '1' == $field_array['fields']['desc_tip'] ) {
					$description = $field_array['fields']['description'];
					echo wc_help_tip( __( $description, 'woocommerce' ) );
				}
				?>
			</p>
				<?php
			}
		}

		/**
		 * Processing product meta required fields for listing
		 * product on marketplace.
		 *
		 * @since 1.0.0
		 * @var int  $post_id
		 */
		public function  umb_required_fields_process_meta( $post_id ) {
		
			if ( ! isset( $_POST['fruugo_product_field_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fruugo_product_field_actions'] ) ), 'fruugo_product_field' ) ) {
				return;
			}
			if ( 'variable' == isset($_POST['product-type']) ? sanitize_text_field($_POST['product-type']):'') {

				$required_fields_ids   = $this->get_custom_fields( 'required', true );
				$required_fields_ids[] = '_umb_fruugo_variation_title';// for saving variation title field only in case of variation product
				$extra_fields_ids      = $this->get_custom_fields( 'extra_fields', true );
				$framework_fields      = array();
				$framework_fields_ids  = array();
				$framework_fields      = $this->get_custom_fields( 'framework_specific', false );
				if ( count( $framework_fields ) ) {
					foreach ( $framework_fields as $fields_data ) {
						if ( is_array( $fields_data ) ) {
							foreach ( $fields_data as $fields_array ) {
								if ( isset( $fields_array['id'] ) ) {
									$framework_fields_ids[] = esc_attr( $fields_array['id'] );
								}
							}
						}
					}
				}

				$checkbox_meta_fields = array(
					'_umb_fruugo_custom_price',
					'_umb_fruugo_custom_stock',
					'_umb_fruugo_prop65',
					'_umb_fruugo_overage18verification',
				);

				$all_fields = array();
				$all_fields = array_merge( $required_fields_ids, $extra_fields_ids, $framework_fields_ids );
				$all_fields = array_unique( $all_fields );

				foreach ( $all_fields as $field_name ) {
					if ( in_array( $field_name, $checkbox_meta_fields ) ) {
						continue;
					}
					$sanitized_array = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
					if ( isset( $sanitized_array[ $field_name ] ) && ! empty( $sanitized_array[ $field_name ] ) ) {
						if ( is_array( $sanitized_array[ $field_name ] ) ) {
							foreach ( $sanitized_array[ $field_name ] as $index => $value ) {
								$product_id = isset($sanitized_array['variable_post_id'][ $index ])? sanitize_text_field($sanitized_array['variable_post_id'][ $index ]):'';
								update_post_meta( $product_id, $field_name, sanitize_text_field( $value ) );
							}
						}
					}
				}

				$var_post_id = isset( $sanitized_array['variable_post_id'] ) ? sanitize_text_field($sanitized_array['variable_post_id']) : array();
				if ( is_array( $var_post_id ) ) {
					foreach ( $var_post_id as $index => $product_id ) {
						foreach ( $checkbox_meta_fields as $field_name ) {
							if ( isset( $sanitized_array[ $field_name ][ $index ] ) && 'yes' == $sanitized_array[ $field_name ][ $index ] ) {
								update_post_meta( $product_id, $field_name, 'yes' );
							} else {
								update_post_meta( $product_id, $field_name, 'no' );
							}
						}
					}
				}
				do_action( 'ced_fruugo_required_fields_process_meta_variable', $post_id );
			} else {
				$required_fields_ids  = $this->get_custom_fields( 'required', true );
				$extra_fields_ids     = $this->get_custom_fields( 'extra_fields', true );
				$framework_fields     = array();
				$framework_fields_ids = array();

				$framework_fields = $this->get_custom_fields( 'framework_specific', false );
				if ( count( $framework_fields ) ) {
					foreach ( $framework_fields as $fields_data ) {
						if ( is_array( $fields_data ) ) {
							foreach ( $fields_data as $fields_array ) {
								if ( isset( $fields_array['id'] ) ) {
									$framework_fields_ids[] = esc_attr( $fields_array['id'] );
								}
							}
						}
					}
				}
				$all_fields = array();
				$all_fields = array_merge( $required_fields_ids, $extra_fields_ids, $framework_fields_ids );
				foreach ( $all_fields as $field_name ) {
					if ( isset( $sanitized_array[ $field_name ] ) ) {
						update_post_meta( $post_id, $field_name, sanitize_text_field( $sanitized_array[ $field_name ] ) );
					} else {
						update_post_meta( $post_id, $field_name, false );
					}
				}

				do_action( 'ced_fruugo_required_fields_process_meta_simple', $post_id );
			}
		}

		/**
		 * Get product custom fields for preparing
		 * product data information to send on different
		 * marketplaces accoding to there requirement.
		 *
		 * @since 1.0.0
		 * @param string $type  required|framework_specific|common
		 * @param bool   $ids  true|false
		 * @return array  fields array
		 */
		public static function get_custom_fields( $type, $is_fields = false ) {
			global $post;
			$fields                    = array();
			$active_shop               = get_option( 'ced_fruugo_active_shop_is', '' );
			$saved_fruugo_details      = get_option( 'ced_fruugo_details', array() );
			$saved_shop_fruugo_details = isset( $saved_fruugo_details[ $active_shop ] ) ? $saved_fruugo_details[ $active_shop ] : array();

			$ced_fruugo_keystring     = isset( $saved_shop_fruugo_details['details']['ced_fruugo_keystring'] ) ? esc_attr( $saved_shop_fruugo_details['details']['ced_fruugo_keystring'] ) : '';
			$ced_fruugo_shared_string = isset( $saved_shop_fruugo_details['details']['ced_fruugo_shared_string'] ) ? esc_attr( $saved_shop_fruugo_details['details']['ced_fruugo_shared_string'] ) : '';
			$ced_fruugo_shop_name     = isset( $saved_shop_fruugo_details['details']['ced_fruugo_shop_name'] ) ? $saved_shop_fruugo_details['details']['ced_fruugo_shop_name'] : '';
			$ced_fruugo_user_name     = isset( $saved_shop_fruugo_details['details']['ced_fruugo_user_name'] ) ? $saved_shop_fruugo_details['details']['ced_fruugo_user_name'] : '';
			global $client_obj;

			if ( 'required' == $type  ) {
				$required_fields = array(


					array(
						'type'   => '_text_input',
						'id'     => '_umb_fruugo_simple_title',
						'fields' => array(
							'id'          => '_umb_fruugo_simple_title',
							'label'       => __( 'Product Title', 'ced-umb' ),
							'desc_tip'    => true,
							'description' => __( 'Product Title, for the selected "Identifier Type" above.', 'ced-umb' ),
						),
					),
					array(
						'type'   => '_text_input',
						'id'     => '_umb_fruugo_simple_desc',
						'fields' => array(
							'id'          => '_umb_fruugo_simple_desc',
							'label'       => __( 'Product Description', 'ced-umb' ),
							'desc_tip'    => true,
							'description' => __( 'Product Description, for the selected "Identifier Type" above.', 'ced-umb' ),
						),
					),


					// array(
					// 	'type'   => '_text_input',
					// 	'id'     => '_umb_fruugo_simple_title',
					// 	'fields' => array(
					// 		'id'          => '_umb_fruugo_simple_desc',
					// 		'label'       => __( 'Variable Title', 'ced-umb' ),
					// 		'desc_tip'    => true,
					// 		'description' => __( 'Variable Title, for the selected "Identifier Type" above.', 'ced-umb' ),
					// 	),
					// ),


					// array(
					// 	'type'   => '_text_input',
					// 	'id'     => '_umb_fruugo_simple_desc',
					// 	'fields' => array(
					// 		'id'          => '_umb_fruugo_simple_desc',
					// 		'label'       => __( 'Variation Description', 'ced-umb' ),
					// 		'desc_tip'    => true,
					// 		'description' => __( 'Variation Description, for the selected "Identifier Type" above.', 'ced-umb' ),
					// 	),
					// ),




					array(
						'type'   => '_select',
						'id'     => '_umb_fruggo_id_type',
						'fields' => array(
							'id'          => '_umb_fruggo_id_type',
							'label'       => __( 'Identifier Type', 'ced-umb' ),
							'options'     => array(
								'null' => __( '--select--', 'ced-umb' ),
								'UPC'  => __( 'UPC', 'ced-umb' ),
								'EAN'  => __( 'EAN', 'ced-umb' ),
							),
							'desc_tip'    => true,
							'description' => __( 'Unique identifier type your product must have to list on fruugo.', 'ced-umb' ),
						),
					),
					
					array(
						'type'   => '_text_input',
						'id'     => '_umb_fruugo_standard_code_val',
						'fields' => array(
							'id'          => '_umb_fruugo_standard_code_val',
							'label'       => __( 'Identifier Value', 'ced-umb' ),
							'desc_tip'    => true,
							'description' => __( 'Identifier value, for the selected "Identifier Type" above.', 'ced-umb' ),
						),
					),
					array(
						'type'   => '_text_input',
						'id'     => '_umb_fruugo_brand',
						'fields' => array(
							'id'          => '_umb_fruugo_brand',
							'label'       => __( 'Product Brand', 'ced-umb' ),
							'desc_tip'    => true,
							'description' => __( 'Product brand for sending on fruugo.', 'ced-umb' ),
						),
					),
					array(
						'type'   => '_text_input',
						'id'     => '_umb_fruugo_vat',
						'fields' => array(
							'id'          => '_umb_fruugo_vat',
							'label'       => __( 'Product Vat', 'ced-umb' ),
							'desc_tip'    => true,
							'description' => __( 'Product vat for sending on fruugo.', 'ced-umb' ),
						),
					),
					array(
						'type'   => '_text_input',
						'id'     => '_umb_fruugo_discount_price',
						'fields' => array(
							'id'          => '_umb_fruugo_discount_price',
							'label'       => __( 'Discount Price', 'ced-umb' ),
							'desc_tip'    => true,
							'description' => __( 'Product discounted price for fruugo.', 'ced-umb' ),
						),
					),
					array(
						'type'   => '_text_input',
						'id'     => '_ced_increase_price_fruugo',
						'fields' => array(
							'id'          => '_ced_increase_price_fruugo',
							'label'       => __( 'Increase By Price(in percentage)', 'ced-umb' ),
							'desc_tip'    => true,
							'description' => __( 'Put percentage amount which you want to increase on main price.', 'ced-umb' ),
							'type'        => 'text',
							'class'       => 'wc_input_price',
						),
					),
				);

				$fields = is_array( apply_filters( 'ced_fruugo_required_product_fields', $required_fields, $post ) ) ? apply_filters( 'ced_fruugo_required_product_fields', $required_fields, $post ) : array();
				// $fields = is_array( $required_fields ) ? $required_fields : array() ;
				

			} elseif ( 'extra_fields' ==  $type  ) {

				$required_fields = array(
					array(
						'type'   => '_select',
						'id'     => '_ced_fruugo_language_section',
						'fields' => array(
							'id'          => '_ced_fruugo_language_section',
							'options'     => array(
								'null' => __( '--select--', 'ced-fruugo' ),
								'en'   => __( 'English', 'ced-fruugo' ),
								'fr'   => __( 'French', 'ced-fruugo' ),
								'de'   => __( 'German', 'ced-fruugo' ),
								'es'   => __( 'Spanish', 'ced-fruugo' ),
								'pt'   => __( 'Portuguese', 'ced-fruugo' ),
								'pl'   => __( 'Polish', 'ced-fruugo' ),
								'it'   => __( 'Italian', 'ced-fruugo' ),
								'da'   => __( 'Danish', 'ced-fruugo' ),
								'sv'   => __( 'Swedish', 'ced-fruugo' ),
								'fi'   => __( 'Finnish', 'ced-fruugo' ),
								'no'   => __( 'Norwegian', 'ced-fruugo' ),
								'ru'   => __( 'Russian', 'ced-fruugo' ),
								'zh'   => __( 'Chinese', 'ced-fruugo' ),
								'jp'   => __( 'Japanese', 'ced-fruugo' ),
								'hi'   => __( 'Hindi', 'ced-fruugo' ),
								'ar'   => __( 'Arabic', 'ced-fruugo' ),
								'nl'   => __( 'Dutch', 'ced-fruugo' ),
							),
							'label'       => __( 'Language', 'ced-fruugo' ),
							'desc_tip'    => true,
							'description' => __( 'Specify the language.', 'ced-fruugo' ),
						),
					),
					array(
						'type'   => '_text_input',
						'id'     => '_ced_fruugo_attributeSize',
						'fields' => array(
							'id'          => '_ced_fruugo_attributeSize',
							'label'       => __( 'Product AttributeSize', 'ced-fruugo' ),
							'desc_tip'    => true,
							'description' => __( 'Specifies the Product Related AttributeSize. Enter multiple tags comma ( , ) seperated', 'ced-fruugo' ),
							'type'        => 'text',
						),
					),
					array(
						'type'   => '_text_input',
						'id'     => '_ced_fruugo_attributeColor',
						'fields' => array(
							'id'          => '_ced_fruugo_attributeColor',
							'label'       => __( 'Product AttributeColor', 'ced-fruugo' ),
							'desc_tip'    => true,
							'description' => __( 'Specifies the Product Related AttributeSize. Enter multiple tags comma ( , ) seperated', 'ced-fruugo' ),
							'type'        => 'text',
						),
					),

					array(
						'type'   => '_select',
						'id'     => '_ced_fruugo_currency',
						'fields' => array(
							'id'          => '_ced_fruugo_currency',
							'label'       => __( 'Product currency', 'ced-fruugo' ),
							'options'     => array(
								'null' => __( '--select--', 'ced-fruugo' ),
								'GBP'  => __( 'Great British Pound', 'ced-fruugo' ),
								'EUR'  => __( 'Euro', 'ced-fruugo' ),
								'PLN'  => __( 'Polish Zloty', 'ced-fruugo' ),
								'DKK'  => __( 'Danish Krona', 'ced-fruugo' ),
								'SEK'  => __( 'Swedish Krona', 'ced-fruugo' ),
								'NOK'  => __( 'Norwegian Krona', 'ced-fruugo' ),
								'CHF'  => __( 'Swiss Franc', 'ced-fruugo' ),
								'RUB'  => __( 'Russian Ruble', 'ced-fruugo' ),
								'ZAR'  => __( 'South African Rand', 'ced-fruugo' ),
								'USD'  => __( 'United States Dollar', 'ced-fruugo' ),
								'CAD'  => __( 'Candian Dollar', 'ced-fruugo' ),
								'AUD'  => __( 'Australian Dollar', 'ced-fruugo' ),
								'NZD'  => __( 'New Zealand Dollar', 'ced-fruugo' ),
								'CNY'  => __( 'Chinese Yuan', 'ced-fruugo' ),
								'JPY'  => __( 'Japanese Yen', 'ced-fruugo' ),
								'INR'  => __( 'Indian Rupee', 'ced-fruugo' ),
								'SAR'  => __( 'Saudi Riyal', 'ced-fruugo' ),
								'QAR'  => __( 'Qatari Rial', 'ced-fruugo' ),
								'BHD'  => __( 'Bahraini Dinar', 'ced-fruugo' ),
								'AED'  => __( 'United Arab Emirates Dirham', 'ced-fruugo' ),
								'EGP'  => __( 'Egyptian Pound', 'ced-fruugo' ),
								'KWD'  => __( 'Kuwaiti Dinar', 'ced-fruugo' ),
							),
							'desc_tip'    => true,
							'description' => __( 'Three letter ISO code (Upper Case) of the currency of the price fields', 'ced-fruugo' ),
							'type'        => 'text',
						),
					),
					array(
						'type'   => '_text_input',
						'id'     => '_ced_fruugo_leadTime',
						'fields' => array(
							'id'          => '_ced_fruugo_leadTime',
							'label'       => __( 'Lead Time', 'ced-fruugo' ),
							'desc_tip'    => true,
							'description' => __( ' Only to be used if the time exceeds 24 hours as 1 day is the default product value.', 'ced-fruugo' ),
							'type'        => 'text',
						),
					),
					array(
						'type'   => '_text_input',
						'id'     => '_ced_fruugo_packageWeight',
						'fields' => array(
							'id'          => '_ced_fruugo_packageWeight',
							'label'       => __( 'PackageWeight', 'ced-fruugo' ),
							'desc_tip'    => true,
							'description' => __( 'The shipping weight of the product provided in grams with no decimal places or unit of measurement. For example, 190.', 'ced-fruugo' ),
							'type'        => 'text',
						),
					),
					array(
						'type'   => '_text_input',
						'id'     => '_ced_fruugo_attribute1',
						'fields' => array(
							'id'          => '_ced_fruugo_attribute1',
							'label'       => __( 'Product Attribute1', 'ced-fruugo' ),
							'desc_tip'    => true,
							'description' => __( 'Specifies the Product Related Attribute. Enter multiple tags comma ( , ) seperated', 'ced-fruugo' ),
							'type'        => 'text',
						),
					),
					array(
						'type'   => '_text_input',
						'id'     => '_ced_fruugo_attribute2',
						'fields' => array(
							'id'          => '_ced_fruugo_attribute2',
							'label'       => __( 'Product Attribute2', 'ced-fruugo' ),
							'desc_tip'    => true,
							'description' => __( 'Specifies the Product Related Attribute. Enter multiple tags comma ( , ) seperated', 'ced-fruugo' ),
							'type'        => 'text',
						),
					),
					array(
						'type'   => '_text_input',
						'id'     => '_ced_fruugo_attribute3',
						'fields' => array(
							'id'          => '_ced_fruugo_attribute3',
							'label'       => __( 'Product Attribute3', 'ced-fruugo' ),
							'desc_tip'    => true,
							'description' => __( 'Specifies the Product Related Attribute. Enter multiple tags comma ( , ) seperated', 'ced-fruugo' ),
							'type'        => 'text',
						),
					),
					array(
						'type'   => '_text_input',
						'id'     => '_ced_fruugo_attribute4',
						'fields' => array(
							'id'          => '_ced_fruugo_attribute4',
							'label'       => __( 'Product Attribute4', 'ced-fruugo' ),
							'desc_tip'    => true,
							'description' => __( 'Specifies the Product Related Attribute. Enter multiple tags comma ( , ) seperated', 'ced-fruugo' ),
							'type'        => 'text',
						),
					),
					array(
						'type'   => '_text_input',
						'id'     => '_ced_fruugo_attribute5',
						'fields' => array(
							'id'          => '_ced_fruugo_attribute5',
							'label'       => __( 'Product Attribute5', 'ced-fruugo' ),
							'desc_tip'    => true,
							'description' => __( 'Specifies the Product Related Attribute. Enter multiple tags comma ( , ) seperated', 'ced-fruugo' ),
							'type'        => 'text',
						),
					),
					array(
						'type'   => '_text_input',
						'id'     => '_ced_fruugo_attribute6',
						'fields' => array(
							'id'          => '_ced_fruugo_attribute6',
							'label'       => __( 'Product Attribute6', 'ced-fruugo' ),
							'desc_tip'    => true,
							'description' => __( 'Specifies the Product Related Attribute. Enter multiple tags comma ( , ) seperated', 'ced-fruugo' ),
							'type'        => 'text',
						),
					),
					array(
						'type'   => '_text_input',
						'id'     => '_ced_fruugo_attribute7',
						'fields' => array(
							'id'          => '_ced_fruugo_attribute7',
							'label'       => __( 'Product Attribute7', 'ced-fruugo' ),
							'desc_tip'    => true,
							'description' => __( 'Specifies the Product Related Attribute. Enter multiple tags comma ( , ) seperated', 'ced-fruugo' ),
							'type'        => 'text',
						),
					),
					array(
						'type'   => '_text_input',
						'id'     => '_ced_fruugo_attribute8',
						'fields' => array(
							'id'          => '_ced_fruugo_attribute8',
							'label'       => __( 'Product Attribute8', 'ced-fruugo' ),
							'desc_tip'    => true,
							'description' => __( 'Specifies the Product Related Attribute. Enter multiple tags comma ( , ) seperated', 'ced-fruugo' ),
							'type'        => 'text',
						),
					),
					array(
						'type'   => '_text_input',
						'id'     => '_ced_fruugo_attribute9',
						'fields' => array(
							'id'          => '_ced_fruugo_attribute9',
							'label'       => __( 'Product Attribute9', 'ced-fruugo' ),
							'desc_tip'    => true,
							'description' => __( 'Specifies the Product Related Attribute. Enter multiple tags comma ( , ) seperated', 'ced-fruugo' ),
							'type'        => 'text',
						),
					),
					array(
						'type'   => '_text_input',
						'id'     => '_ced_fruugo_attribute10',
						'fields' => array(
							'id'          => '_ced_fruugo_attribute10',
							'label'       => __( 'Product Attribute10', 'ced-fruugo' ),
							'desc_tip'    => true,
							'description' => __( 'Specifies the Product Related Attribute. Enter multiple tags comma ( , ) seperated', 'ced-fruugo' ),
							'type'        => 'text',
						),
					),
					array(
						'type' => '_select2',
					),
				);

				$fields = is_array( $required_fields ) ? $required_fields : array();
			} elseif ( 'category' == $type ) {
				$required_fields = array();
				$fields          = is_array( apply_filters( 'ced_fruugo_required_product_fields', $required_fields, $post ) ) ? apply_filters( 'ced_fruugo_required_product_fields', $required_fields, $post ) : array();
			
			} elseif ( 'framework_specific' == $type ) {

				$framework_fields = array();
				$fields           = is_array( apply_filters( 'ced_fruugo_framework_product_fields', $framework_fields, $post ) ) ? apply_filters( 'ced_fruugo_framework_product_fields', $framework_fields, $post ) : array();
				return $fields;
			}
			if ( $is_fields ) {
				$fields_array = array();
				if ( is_array( $fields ) ) {

					foreach ( $fields as $field_data ) {
						$fieldID = isset( $field_data['id'] ) ? esc_attr( $field_data['id'] ) : null;
						if ( ! is_null( $fieldID ) ) {
							$fields_array[] = $fieldID;
						}
					}
					return $fields_array;
				} else {
					return array();
				}
			} else {
				if ( is_array( $fields ) ) {
					return $fields;
				} else {
					return array();
				}
			}
		}

		/**
		 * Custom fields html.
		 *
		 * @since 1.0.0
		 * @param array
		 */
		public function custom_field_html( $fieldsArray ) {
			if ( is_array( $fieldsArray ) ) {
				foreach ( $fieldsArray as $data ) {
					$type        = isset( $data['type'] ) ? esc_attr( $data['type'] ) : '_text_input';
					$fields      = isset( $data['fields'] ) ? is_array( $data['fields'] ) ? $data['fields'] : array() : array();
					$label       = isset( $fields['label'] ) ? esc_attr( $fields['label'] ) : '';
					$description = isset( $fields['description'] ) ? esc_attr( $fields['description'] ) : '';
					$desc_tip    = isset( $fields['desc_tip'] ) ? esc_attr(intval( $fields['desc_tip'] )) : ( ! empty( $description ) ? 1 : 0 );
					$fieldvalue  = isset( $fields['value'] ) ? esc_attr($fields['value']) : null;
					// echo '<pre>';
					// print_r($fields);
					echo '<div class="ced_fruugo_profile_field">';
					echo '<label class="ced_fruugo_label">';
					echo '<span>' . esc_attr($label) . '</span>';
					echo '</label>';
					switch ( $type ) {
						case '_select':
							$id = isset( $fields['id'] ) ? esc_attr( $fields['id'] ) : ( isset( $data['id'] ) ? esc_attr( $data['id'] ) : null );
							if ( ! is_null( $id ) ) {
								$select_values = isset( $fields['options'] ) ? ( is_array( $fields['options'] ) ? $fields['options'] : array() ) : array();

								echo '<select data-id="0" name="' . esc_html($id) . '" id="' . esc_html($id) . '">';
								if ( is_array( $select_values ) ) {
									foreach ( $select_values as $key => $value ) {
										echo '<option value="' . esc_html($key) . '"' . selected( $fieldvalue, $key, false ) . '>';
										esc_html_e($value);
										echo '</option>';
									}
								}
								echo '</select>';
							}
							break;
						case '_text_input':
							$id = isset( $fields['id'] ) ? esc_attr( $fields['id'] ) : ( isset( $data['id'] ) ? esc_attr( $data['id'] ) : null );
							if ( ! is_null( $id ) ) {
								echo '<input type="text" id="' . esc_html($id) . '" name="' . esc_html($id) . '" value="' . esc_html($fieldvalue) . '">';
							}
							break;
						case '_checkbox':
							$id = isset( $fields['id'] ) ? esc_attr( $fields['id'] ) : ( isset( $data['id'] ) ? esc_attr( $data['id'] ) : null );
							if ( ! is_null( $id ) ) {
								echo '<input type="checkbox" id="' . esc_html($id) . '" name="' . esc_html($id) . '" ' . checked( $fieldvalue, 'on' ) . '>';
							}
							break;
						case '_umb_fruugo_select':
							$id           = isset( $fields['id'] ) ? esc_attr( $fields['id'] ) : ( isset( $data['id'] ) ? esc_attr( $data['id'] ) : null );
							$options      = isset( $fields['options'] ) ? $fields['options'] : array();
							$optionsHtml  = '';
							$optionsHtml .= '<option value="null">' . __( '--select--', 'ced-fruugo' ) . '</option>';
							if ( is_array( $options ) ) {
								foreach ( $options as $industry => $subcats ) {

									if ( is_array( $subcats ) ) {
										$optionsHtml .= '<option value="null" class="umb_parent" disabled>' . esc_html($industry) . '</option>';
										foreach ( $subcats as $subcatid => $name ) {

											$optionsHtml .= '<option value="' . esc_html($subcatid) . '" ' . selected( $fieldvalue, $subcatid, false ) . '>' . esc_html($name) . '</option>';
										}
									}
								}
							}
							echo '<p class="form-field ' . esc_html($id) . '">';
							echo '<select name="' . esc_html($id) . '" id="' . esc_html($id) . '">';
							esc_html_e($optionsHtml);
							echo '</select>';
							echo '</p>';
							break;
					}
					echo '</div>';
				}
			}
		}

		/**
		 * Quick edit save product data from manage product
		 * page of umb so that admin can quickly change the product
		 * entries and upload them to selected marketplace with minimal
		 * required changes.
		 *
		 * @since 1.0.0
		 * @param int    $post_id
		 * @param object $post
		 */
		public function quick_edit_save_data( $post_id, $post ) {

			// If this is an autosave, our form has not been submitted, so we don't want to do anything.
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return $post_id;
			}

			// Don't save revisions and autosaves
			if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
				return $post_id;
			}

			// Check post type is product
			if ( 'product' != $post->post_type && 'product_variation' != $post->post_type ) {
				return $post_id;
			}

			// Check user permission
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}

			// Check nonces
			if ( ! isset( $_REQUEST['ced_fruugo_quick_edit_nonce'] ) && ! isset( $_REQUEST['ced_fruugo_quick_edit_nonce'] ) ) {
				return $post_id;
			}

			// Get the product and save
			$product = wc_get_product( $post );

			if ( ! empty( $_REQUEST['ced_fruugo_quick_edit'] ) ) {
				$request_data = $_REQUEST;
				$this->process_quick_edit( $request_data, $product );
				$this->response_updated_product_html( $post, $product );
			}

			// Clear transient
			wc_delete_product_transients( $post_id );

			wp_die();
		}

		/**
		 * Processing the data edited by admin
		 * from quick edit link of product listed in
		 * manage product page of UMB.
		 *
		 * @since 1.0.0
		 * @param array  $request_data
		 * @param object $product
		 */
		public function process_quick_edit( $data, $product ) {

			$product_id = isset( $product->variation_id ) ? intval( $product->variation_id ) : 0;
			if ( WC()->version < '3.0.0' ) {
				if ( ! $product_id ) {
					$product_id = isset( $product->id ) ? intval( $product->id ) : 0;
				}
			} else {
				if ( ! $product_id ) {
					if ( $product->get_id() ) {
						$product_id = intval( $product->get_id() );
					} else {
						$product_id = 0;
					}
				}
			}
			if ( ! $product_id ) {
				return;
			}
			$required_fields = $this->get_custom_fields( 'required', true );
			if ( ! is_array( $required_fields ) ) {
				return;
			}

			$required_fields[] = '_sku';

			foreach ( $data as $key => $value ) {
				$key   = esc_attr( $key );
				$value = sanitize_text_field( $value );
				if ( in_array( $key, $required_fields ) ) {
					update_post_meta( $product_id, $key, $value );
				}
			}
		}

		/**
		 * Updated product html after quick edit
		 * for listing on manage products page of UMB.
		 *
		 * @since 1.0.0
		 */
		public function response_updated_product_html( $post, $product ) {

			if ( ! class_exists( 'CED_FRUUGO_Product_Lister' ) ) {
				require_once CED_FRUUGO_DIRPATH . 'admin/helper/class-ced-umb-product-listing.php';
				$product_lister = new CED_FRUUGO_Product_Lister();
				if ( 'product_variation' == $post->post_type ) {
					$variation_id = $post->ID;
					$post         = get_post( $post->post_parent );
					return $product_lister->get_product_row_html_variation( $post, $variation_id );
				} else {
					return $product_lister->get_product_row_html( $post );
				}
			}
			return $post->ID;
		}
	}

endif;
