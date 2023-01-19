<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$marketPlaces = array( 'fruugo' );
$marketPlace  = is_array( $marketPlaces ) && ! empty( $marketPlaces ) ? $marketPlaces[0] : -1;
$marketplace  = isset( $_REQUEST['section'] ) ? sanitize_text_field($_REQUEST['section']) : $marketPlace;

// product listing class.
require_once CED_FRUUGO_DIRPATH . 'admin/helper/class-ced-umb-product-listing.php';
// feed manager helper class for handling bulk actions.
require_once CED_FRUUGO_DIRPATH . 'admin/helper/class-feed-manager.php';
// header file.
require_once CED_FRUUGO_DIRPATH . 'admin/pages/header.php';

$notices = array();

if ( isset( $_POST['doaction'] ) ) {

	
	


	check_admin_referer( 'bulk-ced_fruugo_mps' );

	$action1 = isset( $_POST['action'] ) ? sanitize_text_field($_POST['action']) : -1;

	$marketPlaces = fruugoget_enabled_marketplaces();


	
	/*
	$marketPlace = is_array($marketPlaces) && !empty($marketPlaces) ? $marketPlaces[0] : -1;
	$marketplace = isset($_REQUEST['section']) ? $_REQUEST['section'] : $marketPlace;*/
	$marketplace     = 'fruugo';
	$sanitized_array = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
	$proIds          = isset( $sanitized_array['post'] ) ? $sanitized_array['post'] : array();
	$allset          = true;
	

	if ( empty( $action1 ) || -1 == $action1) {
		$allset    = false;
		$message   = __( 'Please select the bulk actions to perform action!', 'ced-fruugo' );
		$classes   = 'error is-dismissable';
		$notices[] = array(
			'message' => $message,
			'classes' => $classes,
		);
	}
	// echo $marketplace;die;
	if ( empty( $marketplace ) || -1 == $marketplace ) {
		$allset    = false;
		$message   = __( 'Any marketplace is not activated!', 'ced-fruugo' );
		$classes   = 'error is-dismissable';
		$notices[] = array(
			'message' => $message,
			'classes' => $classes,
		);
	}

	if ( ! is_array( $proIds ) ) {
		$allset    = false;
		$message   = __( 'Please select products to perform bulk action!', 'ced-fruugo' );
		$classes   = 'error is-dismissable';
		$notices[] = array(
			'message' => $message,
			'classes' => $classes,
		);
	}
	if ( $allset ) {

		if ( class_exists( 'CED_FRUUGO_Feed_Manager' ) ) {

			$feed_manager = CED_FRUUGO_Feed_Manager::get_instance();
			$notice       = $feed_manager->process_feed_request( $action1, $marketplace, $proIds );
			$notice_array = json_decode( $notice, true );
			if ( is_array( $notice_array ) ) {

				$message   = isset( $notice_array['message'] ) ? $notice_array['message'] : '';
				$classes   = isset( $notice_array['classes'] ) ? $notice_array['classes'] : 'error is-dismissable';
				$notices[] = array(
					'message' => $message,
					'classes' => $classes,
				);
			} else {
				$message   = __( 'Product will be added to CSV please set cron you can get path from other section.', 'ced-fruugo' );
				$classes   = 'notice notice-success is-dismissable';
				$notices[] = array(
					'message' => $message,
					'classes' => $classes,
				);
			}
		}
	}
}

if ( count( $notices ) ) {
	foreach ( $notices as $notice_array ) {
		$message = isset( $notice_array['message'] ) ? esc_html( $notice_array['message'] ) : '';
		$classes = isset( $notice_array['classes'] ) ? esc_attr( $notice_array['classes'] ) : 'error is-dismissable';
		if ( ! empty( $message ) ) {?>
			 <div class="<?php esc_html_e($classes); ?>">
				<?php esc_html_e($message); ?>
			 </div>
			<?php
		}
	}
	unset( $notices );
}

$availableMarketPlaces = array( 'fruugo' );
if ( is_array( $availableMarketPlaces ) && ! empty( $availableMarketPlaces ) ) {
	$section = $availableMarketPlaces[0];
	if ( isset( $_GET['section'] ) ) {
		$section = sanitize_text_field( $_GET['section'] );
	}
	$product_lister = new CED_FRUUGO_Product_Lister();
	$product_lister->prepare_items();
	?>
	<div class="ced_fruugo_wrap">
		<?php do_action( 'ced_fruugo_manage_product_before_start' ); ?>
		
		<h2 class="ced_fruugo_setting_header"><?php esc_html_e( 'Manage Products', 'ced-fruugo' ); ?></h2>
		
		<?php do_action( 'ced_fruugo_manage_product_after_start' ); ?>
		
		<form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>">
			<input type="hidden" name="page" value="<?php isset($_REQUEST['page'])? esc_html_e(sanitize_text_field($_REQUEST['page'])):''; ?>" />
			<input type="hidden" name="section" value="<?php isset($_REQUEST['section'])? esc_html_e(sanitize_text_field($_REQUEST['section'])):''; ?>" />
			<?php
			$name = '';
			$sku  = '';
			if ( isset( $_GET['ced_fruugo_search_by'] ) && 'name' == $_GET['ced_fruugo_search_by']) {
				$name = 'selected';
			} elseif ( isset( $_GET['ced_fruugo_search_by'] ) && 'sku' == $_GET['ced_fruugo_search_by'] ) {
				$sku = 'selected';
			}
			?>
			<select name="ced_fruugo_search_by">
				<option value="name" <?php esc_html_e($name); ?>><?php esc_html_e( 'Search by Product Name', 'ced-umb-fruugo' ); ?></option>
				<option value="sku" <?php esc_html_e($sku); ?>><?php esc_html_e( 'Search by Sku', 'ced-umb-fruugo' ); ?></option>
			</select>
			<?php $product_lister->search_box( 'Search Products', 'search_id' ); ?>
	
			
			
		</form>
		<?php fruugorenderMarketPlacesLinksOnTop( 'ced_fruugo' ); ?> 

		<form method="get" action="">
			<input type="hidden" name="page" value="<?php isset($_REQUEST['page'])? esc_html_e(sanitize_text_field($_REQUEST['page'])):''; ?>" />
			<input type="hidden" name="section" value="<?php isset($_REQUEST['section'])? esc_html_e(sanitize_text_field($_REQUEST['section'])):''; ?>" />
			<?php
			/** Sorting By Status  **/
			$status_actions           = array(
				'published'   => __( 'Uploaded', 'ced-fruugo' ),
				'notUploaded' => __( 'Not Uploaded', 'ced-fruugo' ),
			);
			$previous_selected_status = isset( $_GET['status_sorting'] ) ? sanitize_text_field($_GET['status_sorting']) : '';


			$stock_status_filter           = array(
				'instock'   => __( 'Instock', 'ced-fruugo' ),
				'outofstock' => __( 'Outofstock', 'ced-fruugo' ),
			);
			$previous_selected_status = isset( $_GET['pro_status_sorting'] ) ? sanitize_text_field($_GET['pro_status_sorting']) : '';


			$product_per_page = array(
				'10'  => __( '10 per page', 'ced-fruugo' ),
				'20'  => __( '20 per page', 'ced-fruugo' ),
				'50'  => __( '50 per page', 'ced-fruugo' ),
				'100' => __( '100 per page', 'ced-fruugo' ),
			);
			$previous_selected_pro_per_page     = isset( $_GET['pro_per_page'] ) ? sanitize_text_field( wp_unslash( $_GET['pro_per_page'] ) ) : '';

			$product_categories = get_terms( 'product_cat', array( 'hide_empty' => false ) );
			$temp_array         = array();
			foreach ( $product_categories as $key => $value ) {
				$temp_array[ $value->term_id ] = $value->name;
			}
			$product_categories    = $temp_array;
			$previous_selected_cat = isset( $_GET['pro_cat_sorting'] ) ? sanitize_text_field($_GET['pro_cat_sorting']) : '';


			$product_types = get_terms( 'product_type', array( 'hide_empty' => false ) );
			$temp_array    = array();
			foreach ( $product_types as $key => $value ) {
				if ( 'simple' == $value->name ||  'variable' == $value->name) {
					$temp_array[ $value->term_id ] = ucfirst( $value->name );
				}
			}
			$product_types          = $temp_array;
			$previous_selected_type = isset( $_GET['pro_type_sorting'] ) ? sanitize_text_field($_GET['pro_type_sorting']) : '';
			// $productId = 116;
			// $product = wc_get_product( $productId );
			// die ($product);


			// $product_inv = get_terms( '_stock_status', array( 'hide_empty' => false ) );
			// $temp_array    = array();

			// // var_dump($product_inv);
			// // die;
			// foreach ( $product_inv as $key => $value ) {
			// 	if ( 'Instock' == $value->name ||  'Outofstock' == $value->name) {
			// 		$temp_array[ $value->term_id ] = $value->name ;
					
			// 	}
			// }
			// $product_inv       = $temp_array;
			// $previous_selected_type = isset( $_GET['pro_inv_sorting'] ) ? sanitize_text_field($_GET['pro_inv_sorting']) : '';







			echo '<div class="ced_fruugo_top_wrapper">';
				echo '<select name="status_sorting">';
				echo '<option value="">' . esc_html( 'Product Status', 'ced-fruugo' ) . '</option>';
			foreach ( $status_actions as $name => $title1 ) {
				$selectedStatus = ( $previous_selected_status == $name ) ? 'selected="selected"' : '';
				$class          = 'edit' === $name ? ' class="hide-if-no-js"' : '';
				echo '<option ' . esc_html($selectedStatus) . ' value="' . esc_html($name) . '"' . esc_html($class) . '>' . esc_html($title1) . '</option>';
			}
				echo '</select>';

				echo '<select name="pro_cat_sorting">';
				echo '<option value="">' . esc_html( 'Product Category', 'ced-fruugo' ) . '</option>';
			foreach ( $product_categories as $name => $title1 ) {
				$selectedCat = ( $previous_selected_cat == $name ) ? 'selected="selected"' : '';
				$class       = 'edit' === $name ? ' class="hide-if-no-js"' : '';
				echo '<option ' . esc_html($selectedCat) . ' value="' . esc_html($name) . '"' . esc_html($class) . '>' . esc_html($title1) . '</option>';
			}
				echo '</select>';

				//class="select_boxes_product_page"
				echo '<select name="pro_status_sorting">';
						echo '<option value="">' . esc_html( __( 'Filter By Stock Status', 'ced-fruugo' ) ) . '</option>';
						foreach ( $stock_status_filter as $index => $value ) {
							$selected_status = ( $previous_selected_sort_status == $index ) ? 'selected="selected"' : '';
							$class         = 'edit' === $name ? ' class="hide-if-no-js"' : '';
							echo '<option value="' . esc_attr( $index ) . '" ' . esc_attr( $selected_status ) . '>' . esc_attr( $value ) . '</option>';
						}
						echo '</select>';

						// echo '<select name="pro_status_sorting">';
						// echo '<option value="">' . esc_html( __( 'Filter By Stock Status', 'ced-fruugo' ) ) . '</option>';
						// foreach ( $stock_status_filter as $name => $title1 ) {
						// 	$selected_status = ( $previous_selected_sort_status == $name) ? 'selected="selected"' : '';
						// 	$class          = 'edit' === $name ? ' class="hide-if-no-js"' : '';
						// 	echo '<option value="' . esc_attr( $name ) . '" ' . esc_attr( $selected_status ) . '>' . esc_attr( $title1 ) . '</option>';
						// }
						// echo '</select>';

						//class="select_boxes_product_page"
						echo '<select name="pro_per_page">';
					echo '<option value="">' . esc_html( __( 'Product Per Page', 'ced-fruugo' ) ) . '</option>';
					foreach ( $product_per_page as $name => $title ) {
						$selected_type = ( $previous_selected_pro_per_page == $name ) ? 'selected="selected"' : '';
						$class         = 'edit' === $name ? ' class="hide-if-no-js"' : '';
						echo '<option ' . esc_attr( $selected_type ) . ' value="' . esc_attr( $name ) . '"' . esc_attr( $class ) . '>' . esc_attr( $title ) . '</option>';
					}
					echo '</select>';

					// echo '<select name="product_error">';
					// echo '<option value="">' . esc_html( __( 'Product have errors', 'ced-fruugo' ) ) . '</option>';
					// foreach ( $Product_errors  as $name => $title ) {
					// 	$selected_type = ( $previous_selected_pro_per_page == $name ) ? 'selected="selected"' : '';
					// 	$class         = 'edit' === $name ? ' class="hide-if-no-js"' : '';
					// 	echo '<option ' . esc_attr( $selected_type ) . ' value="' . esc_attr( $name ) . '"' . esc_attr( $class ) . '>' . esc_attr( $title ) . '</option>';
					// }
					// echo '</select>';
					// $Product_errors           = array(
					// 	'isReady'   => __( 'Products Ready', 'ced-fruugo' ),
					// 	'errors' => __( 'Products have errors', 'ced-fruugo' ),
					// );


			

				echo '<select name="pro_type_sorting">';
				echo '<option value="">' . esc_html( 'Product Type', 'ced-fruugo' ) . '</option>';
			foreach ( $product_types as $name => $title1 ) {
				$selectedType = ( $previous_selected_type == $name ) ? 'selected="selected"' : '';
				$class        = 'edit' === $name ? ' class="hide-if-no-js"' : '';
				echo '<option ' . esc_html($selectedType) . ' value="' . esc_html($name) . '"' . esc_html($class) . '>' . esc_html($title1) . '</option>';
			}
				echo '</select>';

				submit_button( esc_html( 'Filter', 'ced-fruugo' ), 'action', '', false, array() );
			echo '</div>';
			?>
		</form>

		<form id="ced_fruugo_products" method="post">
		<?php $product_lister->views(); ?> 	
			
		<?php $product_lister->display(); ?>
		</form>
	 <?php if ( $product_lister->has_items() ) : ?>
		<?php $product_lister->inline_edit(); ?>
		<?php endif; ?> 
			<?php $product_lister->profle_section(); ?>
	</div>
	<?php
} else {
	esc_html( '<h3>You need to enable the fruugo from the CONFIGURATION tab</h3>', 'ced-fruugo' );
}
?>
