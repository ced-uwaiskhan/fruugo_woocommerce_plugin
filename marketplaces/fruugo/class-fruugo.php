<?php
/**
 * Main class for handling reqests.
 *
 * @since      1.0.0
 *
 * @package    Woocommerce fruugo Integration
 * @subpackage Woocommerce fruugo Integration/marketplaces/fruugo
 */

if ( ! class_exists( 'CED_FRUUGO_Manager' ) ) :

	/**
	 * Single product related functionality.
	 *
	 * Manage all single product related functionality required for listing product on marketplaces.
	 *
	 * @since      1.0.0
	 * @package    Woocommerce fruugo Integration
	 * @subpackage Woocommerce fruugo Integration/marketplaces/fruugo
	 */
	class CED_FRUUGO_Manager {

		/**
		 * The Instace of CED_FRUUGO_fruugo_Manager.
		 *
		 * @since    1.0.0
		 * @var      $_instance   The Instance of CED_FRUUGO_fruugo_Manager class.
		 */
		private static $_instance;
		private static $authorization_obj;
		private static $client_obj;
		/**
		 * CED_FRUUGO_fruugo_Manager Instance.
		 *
		 * Ensures only one instance of CED_FRUUGO_fruugo_Manager is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 * @return CED_FRUUGO_fruugo_Manager instance.
		 */
		public static function get_instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		public $marketplaceID   = 'fruugo';
		public $marketplaceName = 'fruugo';


		/**
		 * Constructor.
		 *
		 * Registering actions and hooks for fruugo.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
		// 	 ini_set('display_errors', 1);
        //   ini_set('display_startup_errors', 1);
        //     error_reporting(E_ALL);
			
			 // print_r(get_post_meta(30,'_umb_fruugo_category',true));die();
			add_action( 'ced_fruugo_fetch_return_requests', array( $this, 'ced_fruugo_fetch_return_requests' ) );
			add_action( 'ced_fruugo_custom_action', array( $this, 'ced_fruugo_return_page' ), 1 );
			// add_action('ced_fruugo_after_fetch_order', array($this, 'ced_fruugo_return_requests'));
			add_action( 'admin_init', array( $this, 'ced_fruugo_required_files' ) );
			// add_filter( 'ced_fruugo_add_new_available_marketplaces' , array( $this, 'ced_fruugo_add_new_available_marketplaces' ), 10, 1 );
			add_filter( 'ced_fruugo_render_marketplace_configuration_settings', array( $this, 'ced_fruugo_render_marketplace_configuration_settings' ), 10, 3 );
			add_action( 'ced_fruugo_license_panel', array( $this, 'ced_fruugo_license_panel' ) );
			add_filter( 'ced_fruugo_license_check', array( $this, 'ced_fruugo_license_check_function' ), 10, 1 );
			// add_action( 'ced_fruugo_save_marketplace_configuration_settings' , array( $this, 'ced_fruugo_save_marketplace_configuration_settings'), 10, 2 );
			add_action( 'ced_fruugo_validate_marketplace_configuration_settings', array( $this, 'ced_fruugo_validate_marketplace_configuration_settings' ), 10, 2 );

			add_filter( 'ced_fruugo_required_product_fields', array( $this, 'add_fruugo_required_fields' ), 11, 2 );

			add_action( 'ced_fruugo_render_different_input_type', array( $this, 'ced_fruugo_render_different_input_type' ), 10, 2 );

			add_action( 'ced_fruugo_required_fields_process_meta_variable', array( $this, 'ced_fruugo_required_fields_process_meta_variable' ), 11, 1 );
			/*loading scripts*/
			add_action( 'admin_enqueue_scripts', array( $this, 'load_fruugo_scripts' ) );
			add_action('wp_ajax_ced_fruugo_run_cron',array($this,'ced_fruugo_cron_manager'));
			add_action( 'ced_fruugo_cron_job', array( $this, 'ced_fruugo_cron_manager' ) );
			//add_action( 'ced_fruugo_product_order', array( $this, 'ced_fruugo_product_order' ) );
			add_action( 'ced_fruugo_import_data_from_csv_to_DB', array( $this, 'ced_fruugo_import_data_from_csv_to_DB' ), 10, 2 );
			// $this->loadDependency();
			// add_action( 'woocommerce_order_status_completed',array( $this, 'ced_send_shipping' ));
			add_action( 'init', array( $this, 'custom_taxonomy_Item' ), 0 );
			// 16-03-2021
			// add_action('init',array($this, 'ced_inventory_update_status' ),0);
			// add_action('init',array($this, 'ced_fruugo_cron_job_inventory' ),0);
		}
		public function custom_taxonomy_Item() {
			$labels = array(
				'name'                       => 'Items',
				'singular_name'              => 'Item',
				'menu_name'                  => 'Item',
				'all_items'                  => 'All Items',
				'parent_item'                => 'Parent Item',
				'parent_item_colon'          => 'Parent Item:',
				'new_item_name'              => 'New Item Name',
				'add_new_item'               => 'Add New Item',
				'edit_item'                  => 'Edit Item',
				'update_item'                => 'Update Item',
				'separate_items_with_commas' => 'Separate Item with commas',
				'search_items'               => 'Search Items',
				'add_or_remove_items'        => 'Add or remove Items',
				'choose_from_most_used'      => 'Choose from the most used Items',
			);
			$args   = array(
				'labels'            => $labels,
				'hierarchical'      => true,
				'public'            => true,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_nav_menus' => true,
				'show_tagcloud'     => true,
			);
			// print_r($args);
			register_taxonomy( 'item', 'product', $args );
			// register_taxonomy_for_object_type( 'item', 'product' );
		}

		public function ced_fruugo_import_data_from_csv_to_DB( $data, $header_array ) {

			$Sku = isset( $data[0] ) ? $data[0] : null;
			global $wpdb;
			$Sku = isset( $data[0] ) ? $data[0] : null;
			// $proId = wc_get_product_id_by_sku( $Sku );
			$proId = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value=%s LIMIT 1", $Sku ) );
			if ( empty( $proId ) ) {
				$proId = $Sku;
			}

			if ( ! empty( $proId ) ) {
				$combine_array = array_combine( $header_array, $data );
				$filecount     = get_option( 'ced_fruugo_filecount', '' );
				// foreach ( $combine_array as $key => $value ) {
				// 	$updated_key = 'ced_status' . $filecount;
				// 	update_post_meta( $proId, $key, $value );
				// 	update_post_meta( $proId, $updated_key, 'Updated' );
				// }

				update_post_meta( $proId, '_umb_fruugo_standard_code_val', $data[6]);
				update_post_meta( $proId, '_umb_fruugo_brand', $data[7]);
				update_post_meta( $proId, '_umb_fruugo_category', $data[8]);
				update_post_meta( $proId, '_umb_fruugo_vat', $data[10]);
				update_post_meta( $proId, '_ced_fruugo_language_section', $data[11]);
				update_post_meta( $proId, '_ced_fruugo_attributeSize', $data[12]);
				update_post_meta( $proId, '_ced_fruugo_attributeColor', $data[13]);
				update_post_meta( $proId, '_ced_fruugo_attribute1', $data[14]);
				update_post_meta( $proId, '_ced_fruugo_attribute2', $data[15]);
				update_post_meta( $proId, '_ced_fruugo_attribute3', $data[16]);
				update_post_meta( $proId, '_ced_fruugo_attribute4', $data[17]);
				update_post_meta( $proId, '_ced_fruugo_attribute5', $data[18]);
				update_post_meta( $proId, '_ced_fruugo_attribute6', $data[19]);
				update_post_meta( $proId, '_ced_fruugo_attribute7', $data[20]);
				update_post_meta( $proId, '_ced_fruugo_attribute8', $data[21]);
				update_post_meta( $proId, '_ced_fruugo_attribute9', $data[22]);
				update_post_meta( $proId, '_ced_fruugo_attribute10', $data[23]);
				update_post_meta( $proId, '_ced_fruugo_currency', $data[24]);
				update_post_meta( $proId, '_ced_fruugo_leadTime', $data[25]);
				update_post_meta( $proId, '_ced_fruugo_packageWeight', $data[26]);
				update_post_meta( $proId, '_umb_fruugo_discount_price', $data[27]);
				update_post_meta( $proId, 'ced_fruugo_updated_products', 'yes');  

				

			}
		}
		public function ced_inventory_update_status() {
			$ced_fruugo_save_details = get_option( 'ced_fruugo_details' );
			$this->get_request_inventory( $ced_fruugo_save_details );
		}
		public function get_request_inventory( $ced_fruugo_save_details ) {
			include_once CED_FRUUGO_DIRPATH . 'marketplaces/fruugo/lib/xmltoarray.php';
			$error   = '';
			$apiHost = 'https://www.fruugo.com/';
			$url     = $apiHost . 'stockstatus-api';
			if ( empty( $ced_fruugo_save_details ) ) {
				return false;
			}
			$ced_fruugo_keystring     = $ced_fruugo_save_details['userString'];
			$ced_fruugo_shared_string = $ced_fruugo_save_details['passString'];
			$fruugoUser               = $ced_fruugo_keystring;
			$fruugoPass               = $ced_fruugo_shared_string;
			$headers                  = array();
			$headers[]                = 'Content-Type: application/x-www-form-urlencoded';
			$ch                       = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt( $ch, CURLOPT_USERPWD, "$fruugoUser:$fruugoPass" );
			// curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

			// curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			 curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'GET' );
			$serverOutput = curl_exec( $ch );
			$header_size  = curl_getinfo( $ch, CURLINFO_HEADER_SIZE );
			$header       = substr( $serverOutput, 0, $header_size );
			$body         = substr( $serverOutput, $header_size );
			$info         = curl_getinfo( $ch );
			$error        = curl_error( $ch );

			// }
			curl_close( $ch );
			print_r( $error );
			$resultInv = XML2Array::createArray( $serverOutput );
			if ( ! empty( $resultInv ) ) {
				// echo '<pre>';
				// print_r($resultInv);
				// die;
				foreach ( $resultInv as $key => $value ) {
					foreach ( $value as $key1 => $value1 ) {
						foreach ( $value1 as $key2 => $value3 ) {
							// echo '<pre>';
							// print_r($value3['@attributes']['fruugoSkuId']);
							$pid = wc_get_product_id_by_sku( $value3['@attributes']['fruugoSkuId'] );

							if ( 0 != $pid ) {
								update_post_meta( $pid, 'uploaded_product', 'upload' );
								update_post_meta( $pid, 'previous_stock', $value3['itemsInStock'] );
								update_post_meta( $pid, 'fruggo_sku', $value3['@attributes']['merchantSkuId'] );
							}
							// if($sku!=0)
							// print_r($sku);
						}
						// echo '<pre>';
						// print_r($value1[0]);
					}
				}
				// die;
			}
		}

		public function ced_fruugo_cron_job_inventory() {

			$products = get_option( 'ced_option_inventory', array() );
			if ( empty( $products ) ) {
				$args     = array(
					'post_type'   => array( 'product', 'product_variation' ),
					'numberposts' => -1,
					'meta_query'  => array(

						array(
							'key'     => 'uploaded_product',
							'value'   => 'upload',
							'compare' => '=',
						),
					),
					'fields'      => 'ids',
				);
				$products = get_posts( $args );
				$products = array_chunk( $products, 2 );
			}
			// $products=get_option('ced_option_inventory',$products,1);
			$xml_to_inventory = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><skus>';
			foreach ( $products[0] as $key => $value ) {
				// echo '<pre>';
				// print_r($value);
				 $previous_stock = get_post_meta( $value, 'previous_stock', 1 );
				 $current_stock  = get_post_meta( $value, '_stock', 1 );
				if ( $previous_stock != $current_stock ) {
							$fruugo_pro_id     = get_post_meta( $value, 'fruggo_sku', true );
							$xml_to_inventory .= ' <sku fruugoSkuId="' . $fruugo_pro_id . '">';
							// $inventory_to_fruugo = get_post_meta($value_sku , '_stock' , true);
					if ( ! empty( $current_stock ) && $current_stock > 0 ) {
						$xml_to_inventory .= ' <availability>INSTOCK</availability><itemsInStock>' . $current_stock . '</itemsInStock></sku>';
					} else {
						$xml_to_inventory .= ' <availability>OUTOFSTOCK</availability><itemsInStock>0</itemsInStock></sku>';
					}

						// $xmlFileName = 'inventory.xml';
						// $this->writeXMLStringToFile( $xml_to_inventory_update, $xmlFileName );
						// $inventory_update = $fruugo_inventory->post_request_inventory('stockstatus-api' , $$xml_to_inventory );

				}
			}
			$xml_to_inventory .= '</skus>';
			// echo $xml_to_inventory;
			// die;
			// $inventory_update = $this->post_request_inventory('stockstatus-api' , $$xml_to_inventory );
			echo '<b>Inventory Sent</b>';

			unset( $products[0] );
			$products = array_values( $products );
			update_option( 'ced_option_inventory', $products );
			// $products=get_option('ced_option_inventory',$products,1);
			// echo '<pre>';
			// print_r($products);
			// die;
		}
		public function post_request_inventory( $stockstatusapi, $postFields ) {
			$apiHost                  = 'https://www.fruugo.com/';
			$url                      = $apiHost . 'stockstatus-api';
			$ced_fruugo_keystring     = $ced_fruugo_save_details['userString'];
			$ced_fruugo_shared_string = $ced_fruugo_save_details['passString'];
			$fruugoUser               = $ced_fruugo_keystring;
			$fruugoPass               = $ced_fruugo_shared_string;
			$headers                  = array();
			$headers[]                = 'Content-Type: application/xml';
			$ch                       = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'POST' );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $postFields );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/xml' ) );
			curl_setopt( $ch, CURLOPT_HEADER, 1 );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_USERPWD, $fruugoUser . ':' . $fruugoPass );
			$serverOutput = curl_exec( $ch );
			$header_size  = curl_getinfo( $ch, CURLINFO_HEADER_SIZE );
			$header       = substr( $serverOutput, 0, $header_size );
			$body         = substr( $serverOutput, $header_size );
			curl_close( $ch );
			return $body;

		}


		public function ced_fruugo_admin_notices() {
			$active_shop          = get_option( 'ced_fruugo_active_shop_is', '' );
			$saved_fruugo_details = get_option( 'ced_fruugo_details', array() );
			if ( '' != $active_shop ) {
				$saved_shop_fruugo_details = $saved_fruugo_details[ $active_shop ];

				if ( ! isset( $saved_shop_fruugo_details['access_token'] ) || empty( $saved_shop_fruugo_details['access_token'] ) ) {
					?>
					<div class="ced_fruugo_active_shop_not_selected notice notice-warning">
						<p>
							<?php esc_attr_e( 'The shop selected is not authorized', 'ced-fruugo' ); ?>
						</p>
					</div>
					<?php
				}
			} else {
				?>
				<div class="ced_fruugo_active_shop_not_selected notice notice-warning">
					<p>
						<?php esc_attr_e( 'Active Shop not selected ! ', 'ced-fruugo' ); ?><a href="<?php esc_html_e( admin_url()) . 'admin.php?page=umb-fruugo-shop-settings'; ?>"><?php esc_attr_e( 'Click Here', 'ced-fruugo' ); ?></a><?php esc_attr_e( ' to select a shop', 'ced-fruugo' ); ?>
					</p>
				</div>
				<?php
			}
		}

		public function ced_fruugo_license_check_function( $check ) {
			$fruugo_license        = get_option( 'ced_fruugo_lincense', false );
			$fruugo_license_key    = get_option( 'ced_fruugo_lincense_key', false );
			$fruugo_license_module = get_option( 'ced_fruugo_lincense_module', false );

			if ( ! empty( $fruugo_license ) ) {
				$response = json_decode( $fruugo_license, true );
				$ced_hash = '';

				if ( isset( $response['hash'] ) && isset( $response['level'] ) ) {
					$ced_hash  = $response['hash'];
					$ced_level = $response['level'];
					{
						$i     = 1;
					for ( $i = 1;$i <= $ced_level;$i++ ) {
						$ced_hash = base64_decode( $ced_hash );
					}
					}
				}

				$fruugo_license = json_decode( $ced_hash, true );

				if ( isset( $fruugo_license['license'] ) && isset( $fruugo_license['module_name'] ) ) {
					if ( empty( $_SERVER['HTTP_HOST'] ) ) {
						$_SERVER['HTTP_HOST'] = '';
					}
					if ( $fruugo_license['license'] == $fruugo_license_key && $fruugo_license['module_name'] == $fruugo_license_module && $fruugo_license['domain'] == $_SERVER['HTTP_HOST'] ) {
						$check = true;
					}
				}
			}
			return $check;
		}

		public function ced_fruugo_license_panel() {
			include_once plugin_dir_path( __FILE__ ) . '/partials/fruugo-license.php';
		}
		/**
		 * Marketplace Configuration Setting
		 *
		 * @name ced_fruugo_render_marketplace_configuration_settings
		 * @since 1.0.0
		 */
		public function ced_fruugo_render_marketplace_configuration_settings( $configSettings, $marketplaceID, $saved_fruugo_details = array() ) {
			if ( $marketplaceID != $this->marketplaceID ) {
				return $configSettings;
			} else {
				$configSettings = array();

				// $saved_fruugo_details = get_option( 'ced_fruugo_details', array() );
				if ( isset( $_POST['ced_fruugo_save_credentials_button'] ) ) {
					if ( ! isset( $_POST['fruugo_credentials_button_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fruugo_credentials_button_actions'] ) ), 'fruugo_credentials_button' ) ) {
						return;
					}

					$saved_fruugo_details = array();
					$userString           = isset( $_POST['ced_fruugo_username_string'] ) ? sanitize_text_field( $_POST['ced_fruugo_username_string'] ) : '';
					// print_r($userString);die;
					$saved_fruugo_details['userString'] = $userString;
					$passString                         = isset( $_POST['ced_fruugo_password_string'] ) ? sanitize_text_field( $_POST['ced_fruugo_password_string'] ) : '';
					
					// print_r($passString);die;
					$saved_fruugo_details['passString'] = $passString;

					// print_r($saved_fruugo_details);die;
					if ( isset( $saved_fruugo_details ) ) {
						update_option( 'ced_fruugo_details', $saved_fruugo_details );

					}
				}

				$ced_fruugo_save_details          = get_option( 'ced_fruugo_details' );
				$ced_fruugo_keystring             = $ced_fruugo_save_details['userString'];
				$ced_fruugo_shared_string         = $ced_fruugo_save_details['passString'];
				$configSettings['configSettings'] = array(
					'ced_fruugo_username_string'         => array(
						'name'  => __( 'Enter User Name', 'ced-fruugo' ),
						'type'  => 'text',
					
						'value' => $ced_fruugo_keystring,
					),
					'ced_fruugo_password_string'         => array(
						'name'  => __( 'Enter Password', 'ced-fruugo' ),
						'type'  => 'text',
						//'placeholder'  => 'Enter Password',
						
						'value' => $ced_fruugo_shared_string,

					),
					'ced_fruugo_save_credentials_button' => array(
						'name'  => __( 'Save Credentials', 'ced-fruugo' ),
						'type'  => 'ced_fruugo_save_credentials_button',
						'value' => '',
					),
				/*
				'ced_fruugo_authorize_details' => array(
					'name' => __('Authorize Your Account', 'ced-fruugo'),
					'type' => 'ced_fruugo_validate_button',
					'value' => ''
				),*/
				);

				$configSettings['showUpdateButton'] = false;
				$configSettings['marketPlaceName']  = $this->marketplaceName;
				return $configSettings;
			}
		}

		/**
		 * Render different input types.
		 */
		public function ced_fruugo_render_different_input_type( $type, $saved_fruugo_details = array() ) {
			 $ced_fruugo_shop_name = isset( $saved_fruugo_details['details']['ced_fruugo_shop_name'] ) ? esc_attr( $saved_fruugo_details['details'] )['ced_fruugo_shop_name'] : '';
			if ( 'ced_fruugo_validate_button' == $type ) {
				echo "<input type='button' data-shopname='" . esc_html($ced_fruugo_shop_name) . "'  class='ced_fruugo_authorize button button-primary' value='Authorize'>";
			}
			if ( 'ced_fruugo_save_credentials_button' == $type ) {
				wp_nonce_field( 'fruugo_credentials_button', 'fruugo_credentials_button_actions' );
				echo "<input type='submit' data-shopname='" . esc_html($ced_fruugo_shop_name) . "' class='ced_fruugo_save_credentials_button button button-primary' value='Save Credentials' name='ced_fruugo_save_credentials_button'>";
			}
			// $saved_fruugo_details = get_option( 'ced_fruugo_details', array() );
			$ced_fruugo_upload_product_type = isset( $saved_fruugo_details['details']['ced_fruugo_upload_product_type'] ) ? esc_attr( $saved_fruugo_details['details'] )['ced_fruugo_upload_product_type'] : '';
			if ( 'ced_fruugo_upload_product_type' == $type ) {
				$draft  = '';
				$active = '';
				if ( 'draft' == $ced_fruugo_upload_product_type ) {
					$draft = 'selected';
				} elseif ( 'active' == $ced_fruugo_upload_product_type ) {
					$active = 'selected';
				}
				$e = "<select class='ced_fruugo_upload_product_type' id='ced_fruugo_upload_product_type'> <option vlaue=''>" . __( '--Select--', 'ced-fruugo' ) . "</option><option value='draft' " . $draft . '>' . __( 'Draft', 'ced-fruugo' ) . "</option><option value='active' " . $active . '>' . __( 'Active', 'ced-fruugo' ) . '</option> </select>';
				esc_attr( $e );
			}
		}
		/**
		 * Validate Marketplace Configuration Setting
		 *
		 * @name ced_fruugo_validate_marketplace_configuration_settings
		 * @since 1.0.0
		 */

		public function ced_fruugo_validate_marketplace_configuration_settings( $configSettingsToSave, $marketplaceID ) {
			global $ced_fruugo_helper;
			try {
				if ( $marketplaceID == $this->marketplaceID ) {
					delete_option( 'ced_fruugo_validate_' . $this->marketplaceID );
					$saved_fruugo_details = get_option( 'ced_fruugo_configuration', true );

					$fruugo_service_url    = isset( $saved_fruugo_details['service_url'] ) ? sanitize_text_field( $saved_fruugo_details['service_url'] ) : '';
					$fruugo_marketplace_id = isset( $saved_fruugo_details['marketplace_id'] ) ? sanitize_text_field( $saved_fruugo_details['marketplace_id'] ) : '';
					$fruugo_merchant_id    = isset( $saved_fruugo_details['merchant_id'] ) ? sanitize_text_field( $saved_fruugo_details['merchant_id'] ) : '';
					$fruugo_key_id         = isset( $saved_fruugo_details['key_id'] ) ? sanitize_text_field( $saved_fruugo_details['key_id'] ) : '';
					$fruugo_secret_key     = isset( $saved_fruugo_details['secret_key'] ) ? sanitize_text_field( $saved_fruugo_details['secret_key'] ) : '';
					$fruugo_auth_token     = isset( $saved_fruugo_details['auth_token'] ) ? sanitize_text_field( $saved_fruugo_details['auth_token'] ) : '';

					if ( $fruugo_service_url && $fruugo_marketplace_id && $fruugo_merchant_id && $fruugo_key_id && $fruugo_secret_key && $fruugo_auth_token ) {
						$this->fruugo_lib->setFeedStatuses( array( '_DONE_' ) );
						$this->fruugo_lib->fetchFeedSubmissions(); // this is what actually sends the request
						$list = $this->fruugo_lib->getFeedList();
						if ( isset( $list ) && is_array( $list ) ) {
							update_option( 'ced_fruugo_validate_' . $this->marketplaceID, 'yes' );
							$notice['message']   = __( 'Configuration setting is Validated Successfully', 'ced-fruugo' );
							$notice['classes']   = 'notice notice-success';
							$validation_notice[] = $notice;
							$ced_fruugo_helper->umb_print_notices( $validation_notice );
						}
					} else {
						$notice['message']   = __( 'Consumer Id and Private Key can\'t be blank', 'ced-fruugo' );
						$notice['classes']   = 'notice notice-error';
						$validation_notice[] = $notice;
						$ced_fruugo_helper->umb_print_notices( $validation_notice );
						unset( $validation_notice );
					}
				}
			} catch ( Exception $e ) {
				$message         = $e->getMessage();
				$param['action'] = 'API CREDENTIAL VALIDATION';
				$param['issue']  = "API Cerdentials is not valid. Please check again. Issue is : $message";
				$ced_fruugo_helper->ced_fruugo_notifcation_mail( $param );

				$notice['message']   = 'API Cerdentials is not valid. Please check again.';
				$notice['classes']   = 'notice notice-error';
				$validation_notice[] = $notice;
				$ced_fruugo_helper->umb_print_notices( $validation_notice );
				unset( $validation_notice );
			}
		}

		/**
		 * Include all required files
		 */
		public function ced_fruugo_required_files() {
			if ( is_file( CED_FRUUGO_DIRPATH . 'marketplaces/fruugo/partials/class-ajax-handler.php' ) ) {
				require_once CED_FRUUGO_DIRPATH . 'marketplaces/fruugo/partials/class-ajax-handler.php';
				$ajaxhandler = new Ced_Fruugo_Ajax_Handler();
			}
			if ( is_file( CED_FRUUGO_DIRPATH . 'marketplaces/fruugo/partials/class-fruugo-upload.php' ) ) {
				require_once CED_FRUUGO_DIRPATH . 'marketplaces/fruugo/partials/class-ajax-handler.php';
			}
		}
		/**
		 * Function to enqueue scripts
		 *
		 * @name load_fruugo_scripts
		 *
		 * @version 1.0.0
		 */
		public function load_fruugo_scripts() {
			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : '';
			$param     = isset( $_GET['marketplaceID'] ) ? sanitize_text_field( $_GET['marketplaceID'] ) : '';
			$action    = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
			$page      = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';
			wp_enqueue_style( 'ced_fruugo_css', plugin_dir_url( __FILE__ ) . 'css/fruugo.css', array(), '1.0.0' );
			// print_r( $screen_id );die;
			if ( 'toplevel_page_umb-fruugo-main' == $screen_id ) {
				wp_register_script( 'ced_fruugo_auth', plugin_dir_url( __FILE__ ) . 'js/authorization.js', array( 'jquery' ), time(), true );
				$localization_params = array(
					'ajax_url'  => admin_url( 'admin-ajax.php' ),
					'admin_url' => get_admin_url(),
				);
				wp_localize_script( 'ced_fruugo_auth', 'ced_fruugo_auth', $localization_params, '1.0.0' );
				wp_enqueue_script( 'ced_fruugo_auth' );
				/**
				 ** woocommerce scripts to show tooltip :: start
				 */

				wp_register_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), '1.0.0' );
				wp_enqueue_style( 'woocommerce_admin_menu_styles' );
				wp_enqueue_style( 'woocommerce_admin_styles' );

				$suffix = '';
				wp_register_script( 'woocommerce_admin', WC()->plugin_url() . '/assets/js/admin/woocommerce_admin' . $suffix . '.js', array( 'jquery', 'jquery-blockui', 'jquery-ui-sortable', 'jquery-ui-widget', 'jquery-ui-core', 'jquery-tiptip' ), WC_VERSION );
				wp_register_script( 'jquery-tiptip', WC()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip' . $suffix . '.js', array( 'jquery' ), WC_VERSION, true );
				wp_enqueue_script( 'woocommerce_admin' );

				wp_enqueue_style( 'ced-fruugo-style-jqueru-ui', plugin_dir_url( __FILE__ ) . 'css/jquery-ui.css', array(), '1.0.0' );
				wp_enqueue_script( 'jquery-ui-datepicker' );
				/**
				 ** woocommerce scripts to show tooltip :: end
				 */
			}

			wp_register_script( 'ced_fruugo_cat', plugin_dir_url( __FILE__ ) . 'js/category.js', array( 'jquery' ), time(), true );
			$localization_params = array(
				'ajax_url'    => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'frugo_nonce' ),
				'plugins_url' => CED_FRUUGO_URL,
			);
			wp_localize_script( 'ced_fruugo_cat', 'ced_fruugo_cat', $localization_params );
			wp_enqueue_script( 'ced_fruugo_cat' );

			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : '';
			if ( in_array( $screen_id, array( 'edit-product', 'product' ) ) ) {
				wp_register_script( 'ced_fruugo_edit_product', plugin_dir_url( __FILE__ ) . 'js/product-edit.js', array( 'jquery' ), time(), true );
				global $post;
				if ( ! empty( $post ) ) {
					wp_localize_script(
						'ced_fruugo_edit_product',
						'ced_fruugo_edit_product_script_AJAX',
						array(
							'ajax_url'   => admin_url( 'admin-ajax.php' ),
							'product_id' => $post->ID,
						)
					);
				}
				wp_enqueue_script( 'ced_fruugo_edit_product' );
			}
			if ( 'fruugo_page_umb-fruugo-manage_message' == $screen_id ) {
				wp_register_script( 'ced_fruugo_manage_message', CED_FRUUGO_URL . 'admin/js/ced_fruugo_manage_message.js', array( 'jquery' ), time(), true );
				wp_localize_script(
					'ced_fruugo_manage_message',
					'ced_fruugo_manage_message',
					array(
						'ajax_url' => admin_url( 'admin-ajax.php' ),
					)
				);
				wp_enqueue_script( 'ced_fruugo_manage_message' );
			}

			if ( 'fruugo_page_umb-fruugo-manage_feedback' == $screen_id ) {
				wp_register_script( 'ced_fruugo_manage_feedback', CED_FRUUGO_URL . 'admin/js/ced_fruugo_manage_feedback.js', array( 'jquery' ), time(), true );
				wp_localize_script(
					'ced_fruugo_manage_feedback',
					'ced_fruugo_manage_feedback',
					array(
						'ajax_url' => admin_url( 'admin-ajax.php' ),
					)
				);
				wp_enqueue_script( 'ced_fruugo_manage_feedback' );
			}

		}

		/**
		 * Function to category selection field on product single page
		 *
		 * @name add_fruugo_required_fields
		 */
		public function add_fruugo_required_fields( $fields = array(), $post = '' ) {
			$savedCategories = array();
			$postId          = isset( $post->ID ) ? intval( $post->ID ) : 0;
			$cat_val=get_post_meta($post->ID,'_umb_fruugo_category',true);
			// $selectedfruugoCategories = get_option('ced_fruugo_selected_categories');
			// if(isset($selectedfruugoCategories) && is_array($selectedfruugoCategories))
			// {
			// foreach ($selectedfruugoCategories as $key => $value) {
			// $catID = preg_replace('/\s+/', '', $key);
			// $catName = preg_replace('/\s+/', '', $value);
			// $savedCategories[$catID]=$catName;
			// update_option('ced_fruugo_selected_categories',$savedCategories);
			// }
			// }
			$selectedfruugoCategories = get_option( 'ced_fruugo_selected_categories' );
			//$selectedfruugoCategories = !empty (get_option( 'ced_fruugo_selected_categories' )) ? get_option( 'ced_fruugo_selected_categories' ): array();
			if(is_array($selectedfruugoCategories))
			$selectedfruugoCategories=array_merge(array("Select_Category" => 'Select Category'),$selectedfruugoCategories);
			// echo '<pre>';
			// print_r($selectedfruugoCategories);
			// die;
			// $Select_Category='Select Category';
			// $selectedfruugoCategories=array_unshift($selectedfruugoCategories,'Select Category');
			$selectedfruugoCategories = ( is_array( $selectedfruugoCategories ) && ! empty( $selectedfruugoCategories ) ) ? $selectedfruugoCategories : array();
			// $selectedfruugoCategories = $newInedx + $selectedfruugoCategories;
			$fields[] = array(
				'type'   => '_select',
				'id'     => '_umb_fruugo_category',
				'fields' => array(
					'id'          => '_umb_fruugo_category',
					'label'       => __( 'fruugo Category', 'ced-fruugo' ),
					'options'     => $selectedfruugoCategories,
					'desc_tip'    => true,
					'description' => __( 'Identify the category specification. There is only one category can be used for any single item. NOTE: Once an item is created, this information cannot be updated.', 'ced-fruugo' ),
				),
				'value' =>$cat_val
			);
			return $fields;
		}


		/**
		 * Process Meta data for variable product
		 *
		 * @name ced_fruugo_required_fields_process_meta_variable
		 * 
		 * @since 1.0.0
		 */

		public function ced_fruugo_required_fields_process_meta_variable( $postID ) {
			if ( ! isset( $_POST['fruugo_marketplace_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fruugo_marketplace_actions'] ) ), 'fruugo_marketplace' ) ) {
				return;
			}
			$sanitized_array = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
			$marketPlace     = 'ced_fruugo_attributes_ids_array';
			if ( isset($sanitized_array[ $marketPlace ] ) ) {
				$attributesArray = array_unique(  $sanitized_array[ $marketPlace ] );
				foreach ( $attributesArray as $field_name ) {
					$sanitized_array['variable_post_id'] = isset( $sanitized_array['variable_post_id'] ) ?  $sanitized_array['variable_post_id'] : '';
					foreach ( $sanitized_array['variable_post_id'] as $key => $post_id ) {
						if ( isset( $sanitized_array[ $field_name ][ $key ] ) ) {
							update_post_meta( $post_id, $field_name, $sanitized_array[ $field_name ][ $key ] );
						}
					}
				}
			}
		}


		/**
		 * Validate the function.
		 *
		 * @since 1.0.0
		 */
		public function validate( $proId, $forDashbiard = false ) {
			$simpleIDs = array( $proId );
			if ( file_exists( CED_FRUUGO_DIRPATH . 'marketplaces/fruugo/partials/class-fruugo-upload.php' ) ) {
				require_once CED_FRUUGO_DIRPATH . 'marketplaces/fruugo/partials/class-fruugo-upload.php';
				$fruugoUpload = CedfruugoUpload::get_instance();
				$fruugoUpload->fetchAssignedProfileDataOfProduct( $proId );

				$missingValues = array();
				$missingValues = $fruugoUpload->fruugoCheckRequiredfields( $simpleIDs, true );
				if ( is_array( $missingValues ) && ! empty( $missingValues ) ) {
					$statusArray['isReady']     = false;
					$statusArray['missingData'] = $missingValues;
					return $statusArray;
				} else {
					$statusArray['isReady'] = true;
					return $statusArray;
				}
			}
		}


		/**
		 * Upload selected products on fruugo.
		 *
		 * @since 1.0.0
		 * @param array $proIds
		 */
		public function upload( $proIds = array(), $isWriteXML = true ) {
			if ( file_exists( CED_FRUUGO_DIRPATH . 'marketplaces/fruugo/partials/class-fruugo-upload.php' ) ) {
				require CED_FRUUGO_DIRPATH . 'marketplaces/fruugo/partials/class-fruugo-upload.php';
				$fruugoUploadInstance = CedfruugoUpload::get_instance();
				// print_r($proIds);echo "1";
				$uploadRequest = $fruugoUploadInstance->upload( $proIds );
				// print_r($uploadRequest);die;
				return $uploadRequest;
			}
		}

		public function upload_all() {
			
	
			$store_products = get_posts(
				array(
					'numberposts' => -1,
					'post_status' => array( 'publish' ),
					'post_type'   => 'product',
					'fields'      => 'ids',

				)
			);
			// var_dump(count($store_products));
			 //die("333");
			if ( file_exists( CED_FRUUGO_DIRPATH . 'marketplaces/fruugo/partials/class-fruugo-upload.php' ) ) {
				require_once CED_FRUUGO_DIRPATH . 'marketplaces/fruugo/partials/class-fruugo-upload.php';
				$this->upload( $store_products );
			}
		}
		/**
		 * This function to fetch order from fruugo seller panel having status "CREATED"
		 *
		 * @name fetchOrders
		 * @since 1.0.0
		 */

		public function fetchOrders() {
			if ( file_exists( CED_FRUUGO_DIRPATH . 'marketplaces/fruugo/partials/class-fruugo-orders.php' ) ) {
				require_once CED_FRUUGO_DIRPATH . 'marketplaces/fruugo/partials/class-fruugo-orders.php';
				$fruugoOrders = new CedfruugoOrders();
				$orders       = $fruugoOrders->getOrders();
				// echo "<pre>";
				// print_r($orders);die;
				 if ( ! empty( $orders ) ) {
					$createOrder = $fruugoOrders->createLocalOrder( $orders );
				}
			}
		}
		public function ced_fruugo_revenue() {

			include_once CED_FRUUGO_DIRPATH . 'marketplaces/fruugo/lib/xmltoarray.php';
			require_once CED_FRUUGO_DIRPATH . 'marketplaces/fruugo/lib/class-fruugo-request.php';
			// $last_fetch_date = '2010-09-27';
			$OrderDownload = new FruugoRequest();
			// $fruugo_order = $OrderDownload->CGetRequest('orders/download?from='.$last_fetch_date);
			// $result = XML2Array::createArray($fruugo_order);
			$fruugo_get_inventory = $OrderDownload->CGetRequest( 'stockstatus-api' );
			$resultInv            = XML2Array::createArray( $fruugo_get_inventory );
			// echo '<pre>';
			// print_r($resultInv);
			// die;
			$returnSkuto = array();

			$return_sku  = 0;
			if ( is_array( $resultInv['skus']['sku'] ) ) {

				foreach ( $resultInv['skus']['sku'] as $key => $value ) {
					if ( 'INSTOCK' == $value['availability'] ) {
							++$return_sku;
					}
					$total_sku = count( $resultInv['skus']['sku'] );
				}
			}
			$returnSkuto['disabledSkus'] = 0;
			$returnSkuto['liveSkus']     = $return_sku;
			$returnSkuto['uploadedSkus'] = $total_sku;

			// print_r($returnSkuto);die;
			return $returnSkuto;

		}

		public function getCurrentMonthOrder( $dates ) {
			$returnsOrderData = array();
			$OrderResult      = $returnsOrderData;
			$startDate        = gmdate( 'Y-m-d H:i:s', strtotime( $dates['from'] ) );
			$endDate          = gmdate( 'Y-m-d H:i:s', strtotime( $dates['to'] ) );
			// print_r($startDate);die;
			// $startDate = date('Y-m-d H:i:s', strtotime('2017-09-20 14:59:52'));
			// $endDate = date('Y-m-d H:i:s', strtotime('2018-01-20 15:09:32'));
			global $wpdb;
			
			$retrieve_sku = $wpdb->get_results( "select post_id from $wpdb->postmeta where meta_key = '_umb_marketplace'", ARRAY_A );
			$sku_array    = array();
			if ( isset( $retrieve_sku ) && is_array( $retrieve_sku ) ) {
				foreach ( $retrieve_sku as $key => $value ) {
					$sku_array[] = $value['post_id'];
				}
			}
			$returnsOrderData = array();
			if ( isset( $sku_array ) && is_array( $sku_array ) ) {

				foreach ( $sku_array as $key => $value ) {
					$ordersData = get_post_meta( $value, 'order_detail' );
					$count      = 0;
					foreach ( $ordersData as $orderData ) {
						$orderDate = gmdate( 'Y-m-d H:i:s', strtotime( $orderData['o:orderDate'] ) );
						// print_r($orderDate);die;
						if ( $orderDate >= $startDate && $orderDate <= $endDate ) {
							$returnsOrderData['revenueTotal'][ $count ]['fruugo_order_id']  = $orderData['o:orderId'];
							$returnsOrderData['revenueTotal'][ $count ]['total_paid']       = $orderData['o:shippingCostInclVAT'] + $orderData['o:orderLines']['o:orderLine']['o:totalPriceInclVat'];
							$returnsOrderData['revenueTotal'][ $count ]['order_place_date'] = $orderDate;

						}
						$count++;
					}
				}
			}
			return $returnsOrderData;
		}
		public function ced_fruugo_return_page( $action ) {
			$action = isset( $action ) ? $action : false;
			if ( 'fruugoreturn' == $action ) {
				$filename = CED_FRUUGO_DIRPATH . 'marketplaces/fruugo/partials/fruugo-returns.php';
				if ( file_exists( $filename ) ) {
					require_once $filename;
				}
			}
		}
		public function ced_fruugo_fetch_return_requests() {
			if ( file_exists( CED_FRUUGO_DIRPATH . 'marketplaces/fruugo/lib/fruugoOrders.php' ) ) {
				require_once CED_FRUUGO_DIRPATH . 'marketplaces/fruugo/lib/fruugoOrders.php';
				$fruugoDetails  = get_option( 'ced_fruugo_details' );
				$token          = $fruugoDetails['token']['fruugoAuthToken'];
				$siteID         = $fruugoDetails['siteID'];
				$orderInstance  = fruugoOrders::get_instance( $siteID, $token );
				$returnrequests = $orderInstance->returnRequests();
				if ( is_array( $returnrequests ) && ! empty( $returnrequests ) ) {
					foreach ( $returnrequests['returns'] as $key => $value ) {
						$valueToSave[ $value['returnId'] ] = $value;
					}
					update_option( 'ced_fruugo_return_requsts', $valueToSave );
				}
			}
		}

		


		public function joinFiles( array $files, $result ) {
			if ( ! is_array( $files ) ) {
				throw new Exception( '`$files` must be an array' );
			}
			$wpuploadDir = wp_upload_dir();
			$baseDir     = $wpuploadDir['basedir'];
			$uploadDir   = $baseDir . '/cedcommerce_fruugouploads';
			$nameTime    = time();
			if ( ! is_dir( $uploadDir ) ) {
				mkdir( $uploadDir, 0777, true );
			}

			$wH = fopen( $uploadDir . '/Merchant.csv', 'w+' );
			$normal_price_header=get_option( 'ced_normal_price_header', '');

			if(empty($normal_price_header))
			$normal_price_header="NormalPriceWithoutVAT";

			$discount_price_header=get_option( 'ced_discount_price_header', '');

			if(empty($discount_price_header))
			$discount_price_header="DiscountPriceWithoutVAT";
			fputcsv( $wH, array( 'SkuId', 'ProductId', 'Title', 'StockQuantity', 'Description', $normal_price_header, 'EAN', 'Brand', 'Category', 'Imageurl1', 'VATRate', 'Language', 'AttributeSize', 'AttributeColor', 'Attribute1', 'Attribute2', 'Attribute3', 'Attribute4', 'Attribute5', 'Attribute6', 'Attribute7', 'Attribute8', 'Attribute9', 'Attribute10', 'Currency', 'LeadTime', 'PackageWeight', $discount_price_header, 'StockStatus', 'Imageurl2', 'Imageurl3', 'Imageurl4', 'Imageurl5', 'Country' ) );
			// fclose($file);
			// $wH = fopen($result, "w+");
			if ( isset( $files ) && ! empty( $files ) && is_array( $files ) ) {

				foreach ( $files as $file ) {
					$file = $uploadDir . '/' . $file . '.csv';
					// print_r($files);die;
					$fh   = fopen( $file, 'r' );
					$line = fgetcsv( $fh );
					while ( ! feof( $fh ) ) {
						fwrite( $wH, fgets( $fh ) );
					}
					fclose( $fh );
					unset( $fh );
					// fwrite($wH, "\n"); //usually last line doesn't have a newline
				}
			}
			fclose( $wH );
			unset( $wH );
			print_r( 'Created' );
		}

		public function ced_products_upload() {

			
			if ( file_exists( CED_FRUUGO_DIRPATH . 'marketplaces/fruugo/partials/class-fruugo-upload.php' ) ) {
				require_once CED_FRUUGO_DIRPATH . 'marketplaces/fruugo/partials/class-fruugo-upload.php';
				$pro_upload = new CedfruugoUpload();


				global $wpdb;
				$table_cron_daily = $wpdb->prefix . 'fruugo_products_upload';
				$Offset           = get_option( 'fruugo_prod_offset' );
				if ( empty( $Offset ) || '' == $Offset ) {
					$Offset = 0;
				}
				$qry = "SELECT * from `$table_cron_daily` LIMIT 1 Offset $Offset ;";
				//$resultdata = $wpdb->get_results( $qry );
				$resultdata =$wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fruugo_products_upload LIMIT 1 Offset %d", $Offset ), 'ARRAY_A' );
				// print_r($resultdata);die;
				if ( isset( $resultdata ) && ! empty( $resultdata ) && is_array( $resultdata ) ) {
					foreach ( $resultdata as $key => $pid ) {
						set_time_limit( -1 );
						ignore_user_abort( true );

						//ini_set( 'memory_limit', '-1' );
						$json_decode = json_decode( $pid['pids'] );
						// foreach (json_decode($pid->pids) as $key_send => $val_send){

						// }
					}
					// $json_decode = array_chunk(array_unique($json_decode), 500);
					// print_r(($json_decode));die;
					if ( isset( $json_decode ) && ! empty( $json_decode ) && is_array( $json_decode ) ) {

						$pro_upload->prepareItems( $json_decode, 'cron_products', $Offset );
					}

						// print_r(array_unique($json_decode));die;
				} else {
					$csv_files = get_option( 'fruuggo_prod_files' );
					$result    = 'Merchant.csv';
					if ( ! empty( $csv_files ) && is_array( $csv_files ) ) {

						$this->joinFiles( $csv_files, $result );
						// echo 'csv created';
						update_option( 'fruuggo_prod_files', ' ' );
						update_option( 'fruugo_prod_offset', ' ' );

					}
				}
			}
		}

		/*
		* Sync inventory for uploaded
		* products
		*/
		public function ced_fruugo_cron_manager() {
			 $this->fetchOrders();

			 $this->ced_products_upload();
			
			


			
			  
			// if (isset($_POST['ced_api_check']) && $_POST['ced_api_check']=="checked" )
			// {
			// $this->ced_inventory_update();
			// }
			// error_reporting(~0);
			// ini_set('display_errors', 1);
			// die('df');
		}

	

		public function ced_inventory_update() {

			global $wpdb;
			include_once CED_FRUUGO_DIRPATH . 'marketplaces/fruugo/lib/xmltoarray.php';

			try {
				require_once CED_FRUUGO_DIRPATH . 'marketplaces/fruugo/lib/class-fruugo-request.php';
				$table_cron_daily_option = get_option( 'fruugo_cron_inventory' );
				if ( empty( $table_cron_daily_option ) || null == $table_cron_daily_option ) {
					$table_cron_daily = $wpdb->prefix . 'fruugo_cron_inventory';
					if ( $table_cron_daily != $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_cron_daily )) ) {
						$table_cron = "CREATE TABLE {$table_cron_daily} (id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,`pids` TEXT DEFAULT '',PRIMARY KEY (id));";
						$wpdb->query( $wpdb->prepare("CREATE TABLE {$wpdb->prefix}fruugo_cron_inventory (id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,`pids` TEXT DEFAULT '',PRIMARY KEY (id)") );
						update_option( 'fruugo_cron_inventory', 'fruugo_cron_inventory_updated' );
					}
				}
				$fruugo_inventory     = new FruugoRequest();
				$fruugo_get_inventory = $fruugo_inventory->CGetRequest( 'stockstatus-api' );

				$result = XML2Array::createArray( $fruugo_get_inventory );
				// echo '<pre>';
				// print_r($result);
				// die();
				if ( isset( $result['skus']['sku'] ) && is_array( $result['skus']['sku'] ) ) {
					foreach ( $result['skus']['sku'] as $key_skus => $value_skus ) {
						// $product_id_inv = wc_get_product_id_by_sku( $value_skus['@attributes']['merchantSkuId'] );
						$sku_fruugo     = $value_skus['@attributes']['merchantSkuId'];
						$product_id_inv = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value=%s LIMIT 1", $sku_fruugo ) );
						if ( isset( $value_skus['@attributes']['fruugoSkuId'] ) ) {

							update_post_meta( $product_id_inv, 'fruugoSkuId', $value_skus['@attributes']['fruugoSkuId'] );
						}
					}
				}
				$retrieve_skuu = $wpdb->get_results( " SELECT `post_id` FROM `wp_postmeta` WHERE `meta_key` LIKE 'fruugoSkuId' AND `meta_value` != ''  " );
				// print_r($retrieve_skuu);die;
				if ( isset( $retrieve_skuu ) && is_array( $retrieve_skuu ) ) {
					foreach ( $retrieve_skuu as $key => $value ) {
						$sku_array[] = $value->post_id;
					}
					$xml_to_inventory_update = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><skus>';
					if ( isset( $sku_array ) && is_array( $sku_array ) ) {
						foreach ( $sku_array as $key_sku => $value_sku ) {
							$fruugo_pro_id            = get_post_meta( $value_sku, 'fruugoSkuId', true );
							$xml_to_inventory_update .= ' <sku fruugoSkuId="' . $fruugo_pro_id . '">';
							$inventory_to_fruugo      = get_post_meta( $value_sku, '_stock', true );
							if ( ! empty( $inventory_to_fruugo ) && $inventory_to_fruugo > 0 ) {
								$xml_to_inventory_update .= ' <availability>INSTOCK</availability><itemsInStock>' . $inventory_to_fruugo . '</itemsInStock></sku>';
							} else {
								$xml_to_inventory_update .= ' <availability>OUTOFSTOCK</availability><itemsInStock>0</itemsInStock></sku>';
							}
						}
						$xml_to_inventory_update .= '</skus>';
						$xmlFileName              = 'inventory.xml';
						$this->writeXMLStringToFile( $xml_to_inventory_update, $xmlFileName );
						$inventory_update = $fruugo_inventory->CPostRequest( 'stockstatus-api', $xml_to_inventory_update );
						esc_attr( '<b>Inventory Sent</b>' );
						if ( ! isset( $_POST['fruugo_scheduler_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fruugo_scheduler_actions'] ) ), 'fruugo_scheduler' ) ) {
							return;
						}
						if ( isset( $_POST['_umb_fruggo_id_scheduler'] ) && 'null' != $_POST['_umb_fruggo_id_scheduler'] ) {
							esc_attr( '<b> & Scheduler is Set.</b>' );
						}
					}
				}
			} catch ( Exception $e ) {
				$result = $e->getMessage();
			}

		}
		public function writeXMLStringToFile( $xmlString, $fileName ) {
			$XMLfilePath = ABSPATH . 'wp-content/uploads/fruugo/';
			if ( ! is_dir( $XMLfilePath ) ) {
				if ( ! mkdir( $XMLfilePath, 0755 ) ) {
					return false;
				}
			}
			$XMLfilePath = $XMLfilePath . 'ced/';
			if ( ! is_dir( $XMLfilePath ) ) {
				if ( ! mkdir( $XMLfilePath, 0755 ) ) {
					return false;
				}
			}

			if ( ! is_writable( $XMLfilePath ) ) {
				return false;
			}
			$XMLfilePath .= $fileName;
			$XMLfile      = fopen( $XMLfilePath, 'w' );
			fwrite( $XMLfile, $xmlString );
			fclose( $XMLfile );
		}
	}
endif;
?>
