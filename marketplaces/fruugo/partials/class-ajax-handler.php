<?php
if ( ! class_exists( 'Ced_Fruugo_Ajax_Handler' ) ) {
	class Ced_Fruugo_Ajax_Handler {

		/**
		 * Construct
		 *
		 * @version 1.0.0
		 */
		public function __construct() {
			add_action( 'wp_ajax_umb_fruugo_acknowledge_order', array( $this, 'umb_fruugo_acknowledge_order' ) );
			add_action( 'wp_ajax_ced_fruugo_fetchCat', array( $this, 'ced_fruugo_fetchCat' ) );
			add_action( 'wp_ajax_ced_fruugo_process_fruugo_cat', array( $this, 'ced_fruugo_process_fruugo_cat' ) );
			add_action( 'ced_fruugo_required_fields_process_meta_simple', array( $this, 'ced_fruugo_required_fields_process_meta_simple' ), 11, 1 );
			add_action( 'ced_fruugo_required_fields_process_meta_variable', array( $this, 'ced_fruugo_required_fields_process_meta_variable' ), 11, 1 );
			add_action( 'wp_ajax_umb_fruugo_cancel_order', array( $this, 'umb_fruugo_cancel_order' ) );
			add_filter( 'umb_save_additional_profile_info', array( $this, 'umb_save_additional_profile_info' ), 11, 1 );
			/**For Shistation automation*/
			add_action( 'woocommerce_shipstation_shipnotify', 'ced_fruugo_check_shipstation_data', 999, 2 );
			//delete_option( 'ced_fruugo_selected_categories');
		}

		// add_action( 'init', 'custom_taxonomy_Item' );



		public function ced_fruugo_extra_action( $actions ) {
			$actions['update']     = 'Update';
			$actions['delete']     = 'Remove from fruugo';
			$actions['deactivate'] = 'Deactivate';
			return $actions;
		}

		/**
		 * Save Profile Information
		 *
		 * @name umb_save_additional_profile_info
		 * 
		 * @since 1.0.0
		 */

		public function umb_save_additional_profile_info( $profile_data ) {
			if ( ! isset( $_POST['fruugo_marketplace_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fruugo_marketplace_actions'] ) ), 'fruugo_marketplace' ) ) {
				return;
			}
			$sanitized_array = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
			if ( isset( $sanitized_array['ced_fruugo_attributes_ids_array'] ) ) {
				foreach ( $sanitized_array['ced_fruugo_attributes_ids_array'] as $key ) {
					if ( isset( $sanitized_array[ $key ] ) ) {
						$fieldid                  = isset( $key ) ? $key : '';
						$fieldvalue               = isset( $sanitized_array[ $key ] ) ? $sanitized_array[ $key ][0] : null;
						$fieldattributemeta       = isset( $sanitized_array[ $key . '_attibuteMeta' ] ) ? $sanitized_array[ $key . '_attibuteMeta' ] : null;
						$profile_data[ $fieldid ] = array(
							'default' => $fieldvalue,
							'metakey' => $fieldattributemeta,
						);
					}
				}
			}
			return $profile_data;
		}

		/**
		 * Function to get category specifics on profile edit page
		 *
		 * @name fetch_fruugo_attribute_for_selected_category_for_profile_section
		 */
		public function fetch_fruugo_attribute_for_selected_category_for_profile_section() {
			if ( ! isset( $_POST['fruugo_profile_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fruugo_profile_actions'] ) ), 'fruugo_profile' ) ) {
				return;
			}
			if ( isset( $_POST['profileID'] ) ) {
				$profileid = sanitize_text_field($_POST['profileID']);
			}
			global $wpdb;
			$table_name   = $wpdb->prefix . CED_FRUUGO_PREFIX . '_fruugoprofiles';
			$profile_data = array();
			if ( $profileid ) {
				$query = "SELECT * FROM `$table_name` WHERE `id`=$profileid";
				//$profile_data = $wpdb->get_results( $query, 'ARRAY_A' );
				$profile_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ced_fruugo_fruugoprofiles WHERE `id`=%s", $profileid ), 'ARRAY_A' );
							// echo '<pre>';  print_r($profile_data);die('fsdf');

				if ( is_array( $profile_data ) ) {
					$profile_data = isset( $profile_data[0] ) ? $profile_data[0] : $profile_data;
					$profile_data = isset( $profile_data['profile_data'] ) ? json_decode( $profile_data['profile_data'], true ) : array();
				}
			}

			/* select dropdown setup */
			$attributes    = wc_get_attribute_taxonomies();
			$attrOptions   = array();
			$addedMetaKeys = get_option( 'CedUmbProfileSelectedMetaKeys', false );

			if ( $addedMetaKeys && count( $addedMetaKeys ) > 0 ) {
				foreach ( $addedMetaKeys as $metaKey ) {
					$attrOptions[ $metaKey ] = $metaKey;
				}
			}
			if ( ! empty( $attributes ) ) {
				foreach ( $attributes as $attributesObject ) {
					$attrOptions[ 'umb_pattr_' . $attributesObject->attribute_name ] = $attributesObject->attribute_label;
				}
			}
			/* select dropdown setup */

			$categoryID = isset( $_POST['categoryID'] ) ? sanitize_text_field($_POST['categoryID']) : '';
			$productID  = isset( $_POST['productID'] ) ? sanitize_text_field($_POST['productID']) : '';
			global $client_obj;

			// $api = new fruugo\fruggoApi($client_obj);
			$variation_category_attribute = $api->findPropertySet( array( 'category_id' => $categoryID ) );

			$variation_category_attribute_property = $variation_category_attribute['results']['0']['properties'];

			$attributes    = wc_get_attribute_taxonomies();
			$attrOptions   = array();
			$addedMetaKeys = get_option( 'CedUmbProfileSelectedMetaKeys', false );

			if ( $addedMetaKeys && count( $addedMetaKeys ) > 0 ) {
				foreach ( $addedMetaKeys as $metaKey ) {
					$attrOptions[ $metaKey ] = $metaKey;
				}
			}
			if ( ! empty( $attributes ) ) {
				foreach ( $attributes as $attributesObject ) {
					$attrOptions[ 'umb_pattr_' . $attributesObject->attribute_name ] = $attributesObject->attribute_label;
				}
			}

			$fruugoDetails = get_option( 'ced_fruugo_details' );
			$token         = $fruugoDetails['access_token'];
			$siteID        = $fruugoDetails['siteID'];
			// if ( empty( $token ) ) {
			// 	// echo json_encode(array('status'=>'401','reason'=>'Token unavailable'));die;
			// }

			/* render framework specific fields */
			$pFieldInstance     = CED_FRUUGO_Product_Fields::get_instance();
			$framework_specific = $pFieldInstance->get_custom_fields( 'framework_specific', false );

			?>
			<div class="ced_fruugo_cmn active">
			  <table class="wp-list-table widefat fixed" style="border: none !important;">
				 <tbody>
				 </tbody>
				 <tbody>
					<?php
					global $global_CED_FRUUGO_Render_Attributes;
					$marketPlace = 'ced_fruugo_required_common';
					$productID   = 0;
					$categoryID  = '';
					$indexToUse  = 0;
							// echo "<pre>";           print_r($variation_category_attribute);die('fsdfsd');
					$attributesList = array();
					if ( isset( $variation_category_attribute_property ) ) {
						foreach ( $variation_category_attribute_property as $variation_category_attribute_property_key => $variation_category_attribute_property_value ) {

							$attributesList[] = array(
								'type'   => '_text_input',
								'id'     => '_ced_fruugo_property_id_' . $variation_category_attribute_property_value['property_id'],
								'fields' => array(
									'id'          => '_ced_fruugo_property_id_' . $variation_category_attribute_property_value['property_id'],
									'label'       => $variation_category_attribute_property_value['name'] . '<span class="ced_fruugo_varition" style="color:green"> [ ' . __( 'For Variation', 'ced-fruugo' ) . ' ]</span>',
									'desc_tip'    => true,
									'description' => $variation_category_attribute_property_value['description'],
									'type'        => 'text',
									'class'       => 'wc_input_price',
								),
							);
						}
					}

					foreach ( $attributesList as $value ) {
						$isText   = true;
						$field_id = trim( $value['fields']['id'], '_' );
						$default  = isset( $profile_data[ $value['fields']['id'] ] ) ? $profile_data[ $value['fields']['id'] ] : '';
						$default  = $default['default'];
						echo '<tr>';
						echo '<td>';
						if ( '_select' == $value['type'] ) {
							$valueForDropdown     = $value['fields']['options'];
							$tempValueForDropdown = array();
							foreach ( $valueForDropdown as $key => $_value ) {
								$tempValueForDropdown[ $_value ] = $_value;
							}
							$valueForDropdown = $tempValueForDropdown;

							$global_CED_FRUUGO_Render_Attributes->renderDropdownHTML(
								$field_id,
								ucfirst( $value['fields']['label'] ),
								$valueForDropdown,
								$categoryID,
								$productID,
								$marketPlace,
								$value['fields']['description'],
								$indexToUse,
								array(
									'case'  => 'profile',
									'value' => $default,
								)
							);
							$isText = false;
						} else {
							$global_CED_FRUUGO_Render_Attributes->renderInputTextHTML(
								$field_id,
								ucfirst( $value['fields']['label'] ),
								$categoryID,
								$productID,
								$marketPlace,
								$value['fields']['description'],
								$indexToUse,
								array(
									'case'  => 'profile',
									'value' => $default,
								)
							);
						}
						echo '</td>';
						echo '<td>';
						if ( $isText ) {
							  $previousSelectedValue = 'null';
							if ( isset( $profile_data[ $value['fields']['id'] ] ) && 'null' != $profile_data[ $value['fields']['id'] ] ) {
								$previousSelectedValue = $profile_data[ $value['fields']['id'] ]['metakey'];
							}

							$selectDropdownHTML  = fruggorenderMetaSelectionDropdownOnProfilePage( $value['fields']['description'] );
							$updatedDropdownHTML = str_replace( '{{*fieldID}}', $value['fields']['id'], $selectDropdownHTML );
							$updatedDropdownHTML = str_replace( 'value="' . $previousSelectedValue . '"', 'value="' . $previousSelectedValue . '" selected="selected"', $updatedDropdownHTML );
							print_r($updatedDropdownHTML);
						}
						echo '</td>';
						echo '</tr>';
					}
					?>
	 </tbody>
	 <tfoot>
	 </tfoot>
 </table>
</div>
			<?php

			wp_die();
		}

		/**Function to request for category fetching
		 *
		 * @name ced_fruugo_fetchCat
		 * function to request for category fetching
		 *
		 * @version 1.0.0
		 */
		public function ced_fruugo_fetchCat() {
			// $cat= get_option( 'ced_fruugo_selected_categories',true);
			// print_r($cat);
			//  die;
			$check_ajax = check_ajax_referer( 'frugo_nonce', 'nonce' );
			if ( !$check_ajax ) {
				return;
			}
			$nonce               = isset( $_POST['_nonce'] ) ? sanitize_text_field($_POST['_nonce']) : '';
			$nextLevelCategories = array();
			if ( 'ced_fruugo_fetch_next_level' == $nonce) {
				$sanitized_array = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
				$catDetails      = isset( $sanitized_array['catDetails'] ) ? $sanitized_array['catDetails'] : array();
				$catLevel        = isset( $catDetails['catLevel'] ) ? $catDetails['catLevel'] : '';
				$catID           = isset( $catDetails['catID'] ) ? $catDetails['catID'] : '';
				$catName         = isset( $catDetails['catName'] ) ? $catDetails['catName'] : '';
				$parentCatName   = isset( $catDetails['parentCatName'] ) ? $catDetails['parentCatName'] : '';
				if ( '' != $catID ) {
					$folderName        = CED_FRUUGO_DIRPATH . 'marketplaces/fruugo/lib/json/';
					$catFirstLevelFile = $folderName . 'category.json';
					// print_r($catFirstLevelFile);die;
					if ( file_exists( $catFirstLevelFile ) ) {
						$catFirstLevel = file_get_contents( $catFirstLevelFile );
						$catFirstLevel = json_decode( $catFirstLevel, true );
					}
					// print_r($catFirstLevel);die;
					$catLevel_next = $catLevel + 1;
					$lev_cat       = 'level' . $catLevel_next;
					$lev_par_cat   = 'level' . $catLevel;
					foreach ( $catFirstLevel as $key => $value ) {
						//print_r($value);die;
						if ( $value[ $lev_par_cat ] == $catName ) {
							$nextLevelCategories[] = $value[ $lev_cat ];
							$nextLevelCategories   = array_unique( $nextLevelCategories );
							$cat_end               = $catLevel_next + 1;
							// print_r($value[$lev_cat]);
							// echo " --> ";
							// print_r($value['level'.$cat_end]);
							// echo "<br>";
							$selectedCategories[ $value[ $lev_cat ] ] = $value[ 'level' . $cat_end ];
							// $selectedCategories = array_unique($selectedCategories);
						}
					}
					// print_r($selectedCategories);die;
					$savedCategories = get_option( 'ced_fruugo_selected_categories' );
					
					if ( is_array( $nextLevelCategories ) && ! empty( $nextLevelCategories ) ) {
						echo json_encode(
							array(
								'status'          => '200',
								'nextLevelCat'    => $nextLevelCategories,
								'selectedCat'     => $selectedCategories,
								'savedCategories' => $savedCategories,
							)
						);
						wp_die();
					}
				}
			}
			wp_die();
		}
		/**
		 * Function to process selected categories
		 *
		 * @name ced_fruugo_process_fruugo_cat
		 */
		public function ced_fruugo_process_fruugo_cat() {
			
			$check_ajax = check_ajax_referer( 'frugo_nonce', 'nonce' );
			if ( !$check_ajax ) {
				return;
			}
			$nonce = isset( $_POST['_nonce'] ) ? sanitize_text_field($_POST['_nonce']) : false;
			if ( 'ced_fruugo_save' == $nonce ) {
				$sanitized_array = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
				$cat             = isset( $sanitized_array['cat'] ) ? $sanitized_array['cat'] : false;
				$catID           = isset( $cat['catID'] ) ? $cat['catID'] : false;
				$catName         = isset( $cat['catName'] ) ? $cat['catName'] : false;
				$catID           = trim( $catName );
				$catID           = preg_replace( '/\s+/', '', $catID );
				//$catName = preg_replace( '/\s+/', '', $catName );
				if ( $catID && $catName ) {
					$savedCategories           = get_option( 'ced_fruugo_selected_categories' );
					$savedCategories           = isset( $savedCategories ) ? $savedCategories : array();
					$savedCategories[ $catID ] = $catName;
					
					if ( update_option( 'ced_fruugo_selected_categories', array_unique( $savedCategories ) ) ) {
						echo json_encode( array( 'status' => '200' ) );
						die;
					}
					echo json_encode( array( 'status' => '400' ) );
					die;
				}
				echo json_encode( array( 'status' => '401' ) );
				die;
			}
			if ( 'ced_fruugo_remove' == $nonce ) {
				$cat   = isset( $_POST['cat'] ) ? sanitize_text_field($_POST['cat']) : false;
				$catID = isset( $cat['catName'] ) ? trim( $cat['catName'] ) : false;
				$catID = preg_replace( '/\s+/', '', $catID );
				if ( $catID ) {
					$savedCategories = get_option( 'ced_fruugo_selected_categories' );
					$savedCategories = isset( $savedCategories ) ? $savedCategories : array();
					// print_r( $savedCategories );
					if ( is_array( $savedCategories ) && ! empty( $savedCategories ) ) {
						foreach ( $savedCategories as $key => $value ) {
							if ( trim( $key ) == $catID ) {
								unset( $savedCategories[ $key ] );
							}
						}
					}
					if ( update_option( 'ced_fruugo_selected_categories', array_unique( $savedCategories ) ) ) {
						echo json_encode( array( 'status' => '500' ) );
						die;
					}
					echo json_encode( array( 'status' => '400' ) );
					die;
				}
				echo json_encode( array( 'status' => '401' ) );
				die;
			}
		}

		/**
		 * Process Meta data for Simple product
		 *
		 * @name ced_fruugo_required_fields_process_meta_simple
		 * 
		 * @since 1.0.0
		 */

		public function ced_fruugo_required_fields_process_meta_simple( $post_id ) {
			$marketPlace = 'ced_fruugo_attributes_ids_array';
			if ( ! isset( $_POST['fruugo_marketplace_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fruugo_marketplace_actions'] ) ), 'fruugo_marketplace' ) ) {
				return;
			}
			$sanitized_array = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
			if ( isset( $sanitized_array[ $marketPlace ] ) ) {
				foreach ( $sanitized_array[ $marketPlace ] as $key => $field_name ) {
					update_post_meta( $post_id, $field_name, $sanitized_array[ $field_name ][0] );
				}
			}
		}
		/**
		 * Process Meta data for variable product
		 *
		 * @name ced_fruugo_required_fields_process_meta_variable
		 * 
		 * @since 1.0.0
		 */

		public function ced_fruugo_required_fields_process_meta_variable( $postID ) {
			$marketPlace = 'ced_fruugo_attributes_ids_array';
			if ( ! isset( $_POST['fruugo_marketplace_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fruugo_marketplace_actions'] ) ), 'fruugo_marketplace' ) ) {
				return;
			}
			$sanitized_array = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
			if ( isset( $sanitized_array[ $marketPlace ] ) ) {
				$attributesArray = array_unique($sanitized_array[ $marketPlace ] );
				foreach ( $attributesArray as $field_name ) {
					if (isset($sanitized_array['variable_post_id'])) {
						foreach ( $sanitized_array['variable_post_id'] as $key => $post_id ) {
							$field_name_md5 = md5( $field_name );
							if ( isset( $sanitized_array[ $field_name_md5 ][ $key ] ) ) {
								update_post_meta( $post_id, $field_name, $sanitized_array[ $field_name_md5 ][ $key ] );
							}
						}
					}
				}
			}
		}
		/**Umb_fruugo_acknowledge_order
		 *
		 * @name umb_fruugo_acknowledge_order
		 */
		public function umb_fruugo_acknowledge_order() {
			$check_ajax = check_ajax_referer( 'frugo_nonce', 'nonce' );
			if ( !$check_ajax ) {
					return;
			}
			$oID = isset( $_POST['order_id'] ) ? sanitize_text_field($_POST['order_id']) : '';
			if ( $oID ) {
				$order_id = $oID;

				if ( $acknowledge ) {
					update_post_meta( $order_id, '_fruugo_umb_order_status', 'Acknowledged' );
					echo json_encode( array( 'status' => '200' ) );
					die;
				} else {
					echo json_encode( array( 'status' => '402' ) );
					die;
				}
			}
		}
		public function umb_fruugo_cancel_order() {
			$check_ajax = check_ajax_referer( 'frugo_nonce', 'nonce' );
			if ( !$check_ajax ) {
					return;
			}
			$order_id = isset( $_POST['order_id'] ) ? sanitize_text_field($_POST['order_id']) : false;
			if ( $order_id ) {
				$file             = CED_FRUUGO_DIRPATH . 'marketplaces/fruugo/lib/fruugoCancelOrders.php';
				$renderDependency = $this->renderDependency( $file );
				if ( $renderDependency ) {
					$merchant_order_id   = get_post_meta( $order_id, 'merchant_order_id', true );
					$fulfillment_node    = get_post_meta( $order_id, 'fulfillment_node', true );
					$order_detail        = get_post_meta( $order_id, 'order_detail', true );
					$purchaseOrderId     = $order_detail['OrderID'];
					$purchaseOrderId     = get_post_meta( $order_id, 'purchaseOrderId', true );
					$order_items         = get_post_meta( $order_id, 'order_items', true );
					$fruggoDetails       = get_option( 'ced_fruugo_details' );
					$token               = $fruggoDetails['token']['fruugoAuthToken'];
					$siteID              = $fruggoDetails['siteID'];
					$fruugoOrderInstance = CancelfruugoOrders::get_instance( $siteID, $token );
					$cancelRequest       = $fruugoOrderInstance->cancelOrder( $purchaseOrderId, $oID, $order_items );
					if ( $cancelRequest ) {
						update_post_meta( $order_id, '_fruugo_umb_order_status', 'Cancelled' );
						echo json_encode( array( 'status' => '200' ) );
						die;
					} else {
						echo json_encode( array( 'status' => '402' ) );
						die;
					}
				}
			}
		}


		/**
		 * Function to include dependencies
		 *
		 * @name renderDependency
		 * @return boolean
		 */
		public function renderDependency( $file ) {
			if ( null != $file || '' != $file ) {
				require_once "$file";
				return true;
			}
			return false;
		}

		/**Ced_fruugo_check_shipstation_data
		 *
		 * @name ced_fruugo_check_shipstation_data
		 * Function to check shipstaion data
		 */
		public function ced_fruugo_check_shipstation_data( $orders_details, $ship_details ) {
			if ( file_exists( CED_FRUUGO_DIRPATH . 'marketplaces/fruugo/partials/class-fruugo-orders.php' ) ) {
				require_once CED_FRUUGO_DIRPATH . 'marketplaces/fruugo/partials/class-fruugo-orders.php';
				$fruugoOrders        = CedfruugoOrders::get_instance();
				$shipstationShipment = $fruugoOrders->shipShipstationFullfilledOrders( $orders_details, $ship_details );
			}

		}

	}
}
