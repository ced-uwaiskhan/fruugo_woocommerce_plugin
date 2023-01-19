<?php
session_start();
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Adds extended functionality as needed in core plugin.
 *
 * @class    CED_FRUUGO_Extended_Manager
 * @version  1.0.0
 * @package Class
 * 
 */

class CED_FRUUGO_Extended_Manager {

	public function __construct() {
		$this->ced_fruugo_extended_manager_add_hooks_and_filters();
	}

	/**
	 * This function hooks into all filters and actions available in core plugin.
	 *
	 * @name ced_fruugo_extended_manager_add_hooks_and_filters()
	 * 
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_fruugo_extended_manager_add_hooks_and_filters() {
		add_action( 'admin_enqueue_scripts', array( $this, 'ced_fruugo_extended_manager_admin_enqueue_scripts' ) );
		add_action( 'wp_ajax_fetch_all_meta_keys_related_to_selected_product', array( $this, 'fetch_all_meta_keys_related_to_selected_product' ) );
		add_action( 'wp_ajax_ced_fruugo_searchProductAjaxify', array( $this, 'ced_fruugo_searchProductAjaxify' ) );

		/* CSV Functionality */
		add_action( 'init', array( $this, 'ced_fruugo_csv_import_export_module_export_csv_format' ) );
		add_action( 'wp_ajax_ced_fruugo_csv_import_export_module_read_csv', array( $this, 'ced_fruugo_csv_import_export_module_read_csv' ) );

		add_action( 'wp_ajax_do_marketplace_folder_update', array( $this, 'do_marketplace_folder_update' ) );

		add_action( 'wp_ajax_ced_fruugo_updateMetaKeysInDBForProfile', array( $this, 'ced_fruugo_updateMetaKeysInDBForProfile' ) );

		// adding cron timing
		add_filter( 'cron_schedules', array( $this, 'my_cron_schedules' ) );

		/*
		* Queue Upload AJAX Request Handling
		*/
		add_action( 'wp_ajax_ced_fruugo_render_queue_upload_main_section', array( $this, 'ced_fruugo_render_queue_upload_main_section' ) );
		add_action( 'wp_ajax_ced_fruugo_add_product_to_upload_queue_on_marketplace', array( $this, 'ced_fruugo_add_product_to_upload_queue_on_marketplace' ) );
		add_action( 'wp_ajax_ced_fruugo_marketplace_allow_split_variation', array( $this, 'ced_fruugo_marketplace_allow_split_variation' ) );

	}

	/*
	* Upload Your Queue html and processing
	*/
	public function ced_fruugo_render_queue_upload_main_section() {
		$check_ajax = check_ajax_referer( 'frugo_nonce', 'nonce' );
		if ( !$check_ajax ) {
			return;
		}
		$selectedMarketPlace = isset( $_POST['marketplaceId'] ) ? sanitize_text_field($_POST['marketplaceId']) : '';
		if ( $selectedMarketPlace ) {
			$items_in_queue                       = get_option( 'ced_fruugo_' . $selectedMarketPlace . '_upload_queue', array() );
			$items_count                          = count( $items_in_queue );
			$ced_fruugo_delete_queue_after_upload = get_option( 'ced_fruugo_delete_queue_after_upload_' . $selectedMarketPlace, 'no' );
			if ( 'yes' == $ced_fruugo_delete_queue_after_upload  ) {
				$ced_fruugo_delete_queue_after_upload = 'checked="checked"';
			} else {
				$ced_fruugo_delete_queue_after_upload = '';
			}
			?>
			<div class="ced_fruugo_queue_upload_main_section">
				<h3 class="ced_fruugo_white_txt"><?php esc_html_e( 'There are ', 'ced-fruugo' ) . esc_html($items_count) . esc_html( ' items in your queue to upload.', 'ced-fruugo' ); ?></h3>
				<h4 class="ced_fruugo_white_txt">
					<input type="checkbox" name="ced_fruugo_delete_queue_after_upload" id="ced_fruugo_delete_queue_after_upload" <?php esc_html_e($ced_fruugo_delete_queue_after_upload); ?> >
					<label for="ced_fruugo_delete_queue_after_upload"><?php esc_html_e( 'Delete Queue After Uplaod.', 'ced-fruugo' ); ?></label>
				</h4>
				<p>
					<input type="submit" name="ced_fruugo_queue_upload_button" class="button button-ced_fruggo" value="<?php esc_html( 'Upload', 'ced-fruugo' ); ?>">
				</p>
			</div>
			<?php
		}
		wp_die();
	}

	/*
	* Adding Product to upload queue
	*/
	public function ced_fruugo_add_product_to_upload_queue_on_marketplace() {
		$check_ajax = check_ajax_referer( 'frugo_nonce', 'nonce' );
		if ( !$check_ajax ) {
			return;
		}
		$marketplaceId  = isset( $_POST['marketplaceId'] ) ? sanitize_text_field($_POST['marketplaceId']) : '';
		$items_in_queue = get_option( 'ced_fruugo_' . $marketplaceId . '_upload_queue', array() );
		$productId      = isset( $_POST['productId'] ) ? sanitize_text_field($_POST['productId']) : '';
		if ( in_array( $productId, $items_in_queue ) ) {
			unset( $items_in_queue[ $productId ] );
		} else {
			$items_in_queue[ $productId ] = $productId;
		}
		update_option( 'ced_fruugo_' . esc_html($marketplaceId) . '_upload_queue', esc_html($items_in_queue ));
		wp_die();
	}

	public function ced_fruugo_marketplace_allow_split_variation() {
		$check_ajax = check_ajax_referer( 'frugo_nonce', 'nonce' );
		if ( !$check_ajax ) {
			return;
		}
		$marketplaceId = isset( $_POST['marketplaceId'] ) ? sanitize_text_field($_POST['marketplaceId']) : '';
		$productId     = isset( $_POST['productId'] ) ? sanitize_text_field($_POST['productId']) : '';
		$already       = get_post_meta( $productId, 'ced_fruugo_allow_split_variation', true );
		if ( 'yes' == $already) {
			update_post_meta( $productId, 'ced_fruugo_allow_split_variation', 'no' );
		} else {
			update_post_meta( $productId, 'ced_fruugo_allow_split_variation', 'yes' );
		}
		wp_die();
	}

	public function ced_fruugo_updateMetaKeysInDBForProfile() {
		$check_ajax = check_ajax_referer( 'frugo_nonce', 'nonce' );
		if ( !$check_ajax ) {
			return;
		}
		$metaKey     = isset($_POST['metaKey'])? sanitize_text_field($_POST['metaKey']):'';
		$actionToDo  = isset($_POST['actionToDo']) ? sanitize_text_field($_POST['actionToDo']):'';
		$allMetaKeys = get_option( 'CedUmbProfileSelectedMetaKeys', array() );
		if ( 'append' == $actionToDo ) {
			if ( ! in_array( $metaKey, $allMetaKeys ) ) {
				$allMetaKeys[] = $metaKey;
			}
		} else {

			if ( in_array( $metaKey, $allMetaKeys ) ) {
				$key = array_search( $metaKey, $allMetaKeys );
				if ( false != $key ) {
					unset( $allMetaKeys[ $key ] );
				}
			}
		}
		update_option( 'CedUmbProfileSelectedMetaKeys', $allMetaKeys );
		wp_die();

	}

	public function my_cron_schedules( $schedules ) {
		// die("okkkk");
		if ( ! isset( $schedules['ced_fruugo_6min'] ) ) {
			$schedules['ced_fruugo_6min'] = array(
				'interval' => 10,
				'display'  => __( 'Once every 6 minutes' ),
			);
		}
		if ( ! isset( $schedules['ced_fruugo_10min'] ) ) {
			$schedules['ced_fruugo_10min'] = array(
				'interval' => 10 * 60,
				'display'  => __( 'Once every 10 minutes' ),
			);
		}
		if ( ! isset( $schedules['ced_fruugo_15min'] ) ) {
			$schedules['ced_fruugo_15min'] = array(
				'interval' => 15 * 60,
				'display'  => __( 'Once every 15 minutes' ),
			);
		}
		if ( ! isset( $schedules['ced_fruugo_30min'] ) ) {
			$schedules['ced_fruugo_30min'] = array(
				'interval' => 30 * 60,
				'display'  => __( 'Once every 30 minutes' ),
			);
		}
		return $schedules;
	}


	public function do_marketplace_folder_update() {
		$check_ajax = check_ajax_referer( 'frugo_nonce', 'nonce' );
		if ( !$check_ajax ) {
			return;
		}
		$marketplaceId = isset( $_POST['marketplaceId'] ) ? sanitize_text_field($_POST['marketplaceId']) : '';
		if ( '' == $marketplaceId) {
			return;
		}
		// echo $marketplaceId;die;
		$default_headers = array(
			'MarketPlace' => 'MarketPlace',
			'Version'     => 'Version',
			'Description' => 'Description',
				// Site Wide Only is deprecated in favor of Network.
		);
		$packageDir = esc_html(WP_PLUGIN_DIR) . "/woocommerce-fruugo-integration/marketplaces/$marketplaceId/class-$marketplaceId.php";
		$allheader  = ced_fruugo_get_package_header_data( $packageDir, $default_headers );
		$referer    = isset($_SERVER['HTTP_HOST'])?sanitize_text_field($_SERVER['HTTP_HOST']):'';
		$requestUrl = 'http://demo.cedcommerce.com/woocommerce/update_notifications/marketplaces/' . $marketplaceId . '/update.php';
		// echo $requestUrl;die;
		$headers   = array();
		$headers[] = "REFERER:$referer";
		$headers[] = 'ACTION:update';
		$ch        = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $requestUrl );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $ch, CURLOPT_HEADER, 1 );
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $allheader );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		$server_output = curl_exec( $ch );
		$header_size   = curl_getinfo( $ch, CURLINFO_HEADER_SIZE );
		$header        = substr( $server_output, 0, $header_size );
		$response      = substr( $server_output, $header_size );
		$response      = json_decode( $response );
		$error_number  = curl_errno( $ch );
		curl_close( $ch );
		if ( $error_number > 0 ) {
			return curl_error( $ch );
		}
		if ( '200' == $response->status ) {
			$file    = $response->url;
			$newfile = WP_PLUGIN_DIR . '/woocommerce-fruugo-integration/admin/temp/tmp_file.zip';
			if ( ! copy( $file, $newfile ) ) {
				echo '103';
				die;
			}
			$rootPath = WP_PLUGIN_DIR . '/woocommerce-fruugo-integration/marketplaces/';
			$zip      = new ZipArchive();
			if ( $zip->open( $newfile ) === true ) {
				if ( $zip->extractTo( $rootPath ) ) {
					$zip->close();
					unlink( $newfile );
					echo '200';
					die;
				}
			} else {
				echo '101';
				die;
			}
		} elseif ( '100' == $response->status ) {
			echo '100';
			die;
		} else {
			echo '102';
			die;
		}
		/** Do Marketplace Update Code Here */
		wp_die();
	}

	/*
	* Search product on manage product page
	*/
	public function ced_fruugo_searchProductAjaxify( $x = '', $post_types = array( 'product' ) ) {
		$check_ajax = check_ajax_referer( 'frugo_nonce', 'nonce' );
		if ( !$check_ajax ) {
			return;
		}
		global $wpdb;

		ob_start();
		if (isset($_POST['term'])) {
			$_POST['term'] = sanitize_text_field($_POST['term']);
		} else {
			$_POST['term'] ='';
		}
		$term = (string) wc_clean( stripslashes( sanitize_text_field($_POST['term'] )) );
		if ( empty( $term ) ) {
			die();
		}

		$like_term = '%' . $wpdb->esc_like( $term ) . '%';

		if ( is_numeric( $term ) ) {
			$post_types = implode( "','", array_map( 'esc_sql', $post_types ) );
			$posts      = array_unique( $wpdb->get_col( $wpdb->prepare(
				"
				SELECT ID FROM {$wpdb->posts} posts LEFT JOIN {$wpdb->postmeta} postmeta ON posts.ID = postmeta.post_id
				WHERE posts.post_status = 'publish'
				AND (
				posts.post_parent = %s
				OR posts.ID = %s
				OR posts.post_title LIKE %s
				OR (
				postmeta.meta_key = '_sku' AND postmeta.meta_value LIKE %s
				)
				)
				AND posts.post_type IN (%s)",
				$term,
				$term,
				$term,
				$like_term,
				$post_types
			)));
		} else {
			$post_types = implode( "','", array_map( 'esc_sql', $post_types ) );
			$posts      = array_unique( $wpdb->get_col( $wpdb->prepare(
				"
				SELECT ID FROM {$wpdb->posts} posts LEFT JOIN {$wpdb->postmeta} postmeta ON posts.ID = postmeta.post_id
				WHERE posts.post_status = 'publish'
				AND (
				posts.post_title LIKE %s
				or posts.post_content LIKE %s
				OR (
				postmeta.meta_key = '_sku' AND postmeta.meta_value LIKE %s
				)
				)
				AND posts.post_type IN (%s)",
				$like_term,
				$like_term,
				$like_term,
				$post_types
			)));
		}

		//$query .= " AND posts.post_type IN ('" . implode( "','", array_map( 'esc_sql', $post_types ) ) . "')";

		$posts          = array_unique( $posts);
		$found_products = array();

		global $product;

		$proHTML = '';
		if ( ! empty( $posts ) ) {
			$proHTML .= '<table class="wp-list-table fixed striped" id="ced_fruugo_products_matched">';
			foreach ( $posts as $post ) {
				$product = wc_get_product( $post );
				if ( WC()->version < '3.0.0' ) {
					if ( 'variable' == $product->product_type) {
						$variations = $product->get_available_variations();
						foreach ( $variations as $variation ) {
							$proHTML .= '<tr><td product-id="' . $variation['variation_id'] . '">' . get_the_title( $variation['variation_id'] ) . '</td></tr>';
						}
					} else {
						$proHTML .= '<tr><td product-id="' . $post . '">' . get_the_title( $post ) . '</td></tr>';
					}
				} else {
					if ( 'variable' == $product->get_type()) {
						$variations = $product->get_available_variations();
						foreach ( $variations as $variation ) {
							$proHTML .= '<tr><td product-id="' . $variation['variation_id'] . '">' . get_the_title( $variation['variation_id'] ) . '</td></tr>';
						}
					} else {
						$proHTML .= '<tr><td product-id="' . $post . '">' . get_the_title( $post ) . '</td></tr>';
					}
				}
			}
			$proHTML .= '</table>';
		} else {
			$proHTML .= '<ul class="woocommerce-error ccas_searched_product_ul"><li class="ccas_searched_pro_list"><strong>No Matches Found</strong><br/></li></ul>';
		}
		echo ($proHTML);
		wp_die();
	}


	/**
	 * This function exports the format of wholelsale-market-csv
	 *
	 * @name ced_fruugo_csv_import_export_module_export_csv_format()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_fruugo_csv_import_export_module_export_csv_format() {

			$csvHeaderArray = array(

				'SkuId',
				'ProductId',
				'Title',
				'StockQuantity',
				'Description',
				'NormalPriceWithoutVAT',
				'EAN',
				'Brand',
				'Category',
				'Imageurl1',
				'VATRate',
				'Language',
				'AttributeSize',
				'AttributeColor',
				'Attribute1',
				'Attribute2',
				'Attribute3',
				'Attribute4',
				'Attribute5',
				'Attribute6',
				'Attribute7',
				'Attribute8',
				'Attribute9',
				'Attribute10',
				'Currency',
				'LeadTime',
				'PackageWeight',
				'DiscountPriceWithoutVAT',
				'StockStatus',
				'Imageurl2',
				'Imageurl3',
				'Imageurl4',
				'Imageurl5',
				'Country',

			);
			update_option( 'ced_fruugo_latest_csv_header', $csvHeaderArray );
	}

	/**
	 * This function to read data from csv and prepare response
	 *
	 * @name ced_fruugo_csv_import_export_module_read_csv()
	 * 
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_fruugo_csv_import_export_module_read_csv() {
		$check_ajax = check_ajax_referer( 'frugo_nonce', 'nonce' );
		if ( !$check_ajax ) {
			return;
		}
		$productArr   = array();
		$bufferArr    = array();
		$header_array = array();
		$filestore    = isset($_POST['filepath']) ? sanitize_text_field($_POST['filepath']):'';
		$offset       = isset($_POST['offset']) ? sanitize_text_field($_POST['offset']):'';
		$limit        = isset($_POST['limit']) ? sanitize_text_field($_POST['limit']):'';
		$repeat       = isset($_POST['repeat'])? sanitize_text_field($_POST['repeat']):'';
		$offset       = (int) $offset;
		$limit        = (int) $limit;
		if ( 'true' == $repeat ) {
			$filecount = get_option( 'ced_fruugo_filecount', '' );
			if ( empty( $filecount ) ) {
				$filecount = 0;
			}
			$filecount = (int) $filecount + 1;
			update_option( 'ced_fruugo_filecount', $filecount );
		}
		if ( file_exists( CED_FRUUGO_DIRPATH . 'vendor/autoload.php' ) ) {
			require_once CED_FRUUGO_DIRPATH . 'vendor/autoload.php';
		}

		$readerEntityFactory = new Box\Spout\Reader\Common\Creator\ReaderEntityFactory();
		$excelReader         = $readerEntityFactory->createReaderFromFile( $filestore );
		$excelReader->setShouldPreserveEmptyRows( true );
		$excelReader->open( $filestore );
		foreach ( $excelReader->getSheetIterator() as $sheet ) {
			foreach ( $sheet->getRowIterator() as $rowNumber => $row ) {
				$rowAsArray = $row->toArray();
				array_push( $productArr, $rowAsArray );
			}
		}
		$total_size = count( $productArr );
		$temp_array = array();
		$bufferArr  = array();
		for ( $i = 0;$i < 1;$i++ ) {
			foreach ( $productArr[ $i ] as $key => $value ) {
					$header_array[] = $value;
			}
		}
		for ( $i = $offset;$i <= $limit;$i++ ) {
			foreach ( $productArr[ $i ] as $key => $value ) {
				$temp_array[] = $value;
			}
			array_push( $bufferArr, $temp_array );
			$temp_array = array();
		}
		foreach ( $bufferArr as $key => $value ) {
			$this->ced_cwsm_write_csv_content_to_DB( $value, $header_array );
		}
			$left_size = $limit + 100;
			echo 'uploaded ,' . esc_html($offset) . ',' . esc_html($limit) . ',' . esc_html($total_size) . ',' . esc_html($left_size);
			$bufferArr = array();
			wp_die();
	}
	public function ced_cwsm_write_csv_content_to_DB( $data, $header_array ) {
		// print_r($header_array);
		// die;
		do_action( 'ced_fruugo_import_data_from_csv_to_DB', $data, $header_array );
	}


	/**
	 * This function to get all meta keys related to a product
	 *
	 * @name fetch_all_meta_keys_related_to_selected_product()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function fetch_all_meta_keys_related_to_selected_product() {
		$check_ajax = check_ajax_referer( 'frugo_nonce', 'nonce' );
		if ( !$check_ajax ) {
			return;
		}
		fruggorenderMetaKeysTableOnProfilePage(isset($_POST['selectedProductId'] )? sanitize_text_field( $_POST['selectedProductId'] ):'' );
		wp_die();
	}


	/**
	 * This function includes custom js needed by module.
	 *
	 * @name ced_fruugo_extended_manager_admin_enqueue_scripts()
	 * 
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_fruugo_extended_manager_admin_enqueue_scripts() {
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';
		$section=isset($_GET['section'])? sanitize_text_field($_GET['section']):'';
		//echo $screen_id;die;
		if (true) {
			wp_enqueue_style( 'ced_fruugo_manage_products_css', esc_html(CED_FRUUGO_URL) . '/admin/css/manage_products.css', array( 'css' ), '1.0', true );
		}

		if (  'fruugo_page_umb-fruugo-cat-map' == $screen_id) {
			wp_enqueue_style( 'ced_fruugo_category_mapping_css', esc_html(CED_FRUUGO_URL) . '/admin/css/category_mapping.css', array( 'css' ), '1.0', true );
		}

		if ( true ) {
			wp_enqueue_style( 'ced_fruugo_shop_settings_page_css', esc_attr(CED_FRUUGO_URL) . '/admin/css/profile_page_css.css', array(), '1.0.0', true );
		}
		
		if ( 'profile-view' == $section && isset( $_GET['action'] ) ) {
			wp_enqueue_script( 'ced_fruugo_profile_edit_add_js', esc_html(CED_FRUUGO_URL) . '/admin/js/profile-edit-add.js', array( 'jquery' ), '1.0', true );
			wp_localize_script(
				'ced_fruugo_profile_edit_add_js',
				'ced_fruugo_profile_edit_add_script_AJAX',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'frugo_nonce' ),

				)
			);
			wp_enqueue_script( 'ced_fruugo_profile_jquery_dataTables_js', esc_attr(CED_FRUUGO_URL) . '/admin/js/jquery.dataTables.min.js', array( 'jquery' ), '1.0', true );
			wp_enqueue_style( 'ced_fruugo_profile_jquery_dataTables_css', esc_attr(CED_FRUUGO_URL) . '/admin/css/jquery.dataTables.min.css', array( 'css' ), '1.0', true );
			wp_enqueue_style( 'ced_fruugo_profile_page_css', esc_attr(CED_FRUUGO_URL) . '/admin/css/profile_page_css.css', array('css'), '1.0.0', true );

			/**
			** woocommerce scripts to show tooltip :: start
			*/

			/* woocommerce style */
			wp_register_style( 'woocommerce_admin_styles', esc_html(WC()->plugin_url()) . '/assets/css/admin.css', array(), WC_VERSION );
			wp_enqueue_style( 'woocommerce_admin_menu_styles' );
			wp_enqueue_style( 'woocommerce_admin_styles' );

			/* woocommerce script */
			$suffix = '';
			wp_register_script( 'woocommerce_admin', esc_html(WC()->plugin_url()) . '/assets/js/admin/woocommerce_admin' . $suffix . '.js', array( 'jquery', 'jquery-blockui', 'jquery-ui-sortable', 'jquery-ui-widget', 'jquery-ui-core', 'jquery-tiptip' ), WC_VERSION );
			wp_register_script( 'jquery-tiptip', esc_html(WC()->plugin_url()) . '/assets/js/jquery-tiptip/jquery.tipTip' . $suffix . '.js', array( 'jquery' ), WC_VERSION, true );

			$params = array(
				/*
				 translators: %s: decimal */
				// 'i18n_decimal_error'                => sprintf( __( 'Please enter in decimal (%s) format without thousand separators.', 'woocommerce' ), $decimal ),
				/* translators: %s: price decimal separator */
				'i18n_mon_decimal_error'           => sprintf( __( 'Please enter in monetary decimal (%s) format without thousand separators and currency symbols.', 'woocommerce' ), wc_get_price_decimal_separator() ),
				'i18n_country_iso_error'           => __( 'Please enter in country code with two capital letters.', 'woocommerce' ),
				'i18_sale_less_than_regular_error' => __( 'Please enter in a value less than the regular price.', 'woocommerce' ),
				// 'decimal_point'                     => $decimal,
				'mon_decimal_point'                => wc_get_price_decimal_separator(),
				'strings'                          => array(
					'import_products' => __( 'Import', 'woocommerce' ),
					'export_products' => __( 'Export', 'woocommerce' ),
				),
				'urls'                             => array(
					'import_products' => esc_url_raw( admin_url( 'edit.php?post_type=product&page=product_importer' ) ),
					'export_products' => esc_url_raw( admin_url( 'edit.php?post_type=product&page=product_exporter' ) ),
				),
			);

			wp_localize_script( 'woocommerce_admin', 'woocommerce_admin', $params );
			wp_enqueue_script( 'woocommerce_admin' );

			/**
			** woocommerce scripts to show tooltip :: end
			*/
		}

		if ( 'toplevel_page_umb-main' == $screen_id) {
			wp_enqueue_script( 'ced_fruugo_update_marketplace_js', esc_html(CED_FRUUGO_URL) . '/admin/js/update_marketplace.js', array( 'jquery' ), '1.0', true );
			wp_localize_script(
				'ced_fruugo_update_marketplace_js',
				'ced_fruugo_update_marketplace_script_AJAX',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'frugo_nonce' ),
				)
			);
		}

		if ( true ) {
			wp_enqueue_script( 'ced_fruugo_select2_js', esc_html(CED_FRUUGO_URL) . 'admin/js/select2.min.js', array( 'jquery' ), '1.0', true );
			wp_enqueue_style( 'ced_fruugo_select2_css', esc_html(CED_FRUUGO_URL) . 'admin/css/select2.min.css');
		}

		if ( isset( $_GET['page'] ) &&  'ced_fruugo' == sanitize_text_field($_GET['page']) && isset( $_GET['sub-section'] ) && 'csv_upload_section' == sanitize_text_field($_GET['sub-section'] )) {
			wp_enqueue_script( 'ced_fruugo_csv_upload_script_js', esc_html(CED_FRUUGO_URL) . '/admin/js/csv_upload.js', array( 'jquery' ), '1.0', true );
			wp_localize_script(
				'ced_fruugo_csv_upload_script_js',
				'ced_fruugo_csv_upload_script_js_ajax',
				array(
					'ajax_url'      => admin_url( 'admin-ajax.php' ),
					'nonce'    => 	wp_create_nonce( 'frugo_nonce' ),
					'loading_image' => esc_html(CED_FRUUGO_URL) . '/admin/css/clock-loading.gif',
				)
			);
			wp_enqueue_style( 'ced_fruugo_csv_upload_script_css', esc_html(CED_FRUUGO_URL) . '/admin/css/csv_upload.css' );
		}
		if ( isset( $_GET['sub-section'] ) && 'bulk_product_upload_queue' == sanitize_text_field($_GET['sub-section'] )) {
			wp_enqueue_script( 'ced_fruugo_upload_queue_script_js', esc_html(CED_FRUUGO_URL) . '/admin/js/ced-umb-queue-upload.js', array( 'jquery' ), '1.0', true );
			wp_localize_script(
				'ced_fruugo_upload_queue_script_js',
				'ced_fruugo_upload_queue_script_js_ajax',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'frugo_nonce' ),
				)
			);
		}

	}

}
new CED_FRUUGO_Extended_Manager();
?>
