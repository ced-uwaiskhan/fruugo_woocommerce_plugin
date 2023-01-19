<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
global $ced_fruugo_helper;
$current_page = 'umb-fruugo';
$section      =isset($_GET['section'])?sanitize_text_field($_GET['section']):'configuration-view';
if ( isset( $_GET['page'] ) ) {
	$current_page = sanitize_text_field($_GET['page']);
}
// if('ced_fruugo' == $current_page && isset($_GET['section'])){
// 		switch($_GET['section']){
// 			case 'configuration-view':
// 				require_once CED_FRUUGO_DIRPATH . 'admin/pages/marketplaces.php';
// 				break;
// 			case 'category-mapping' : 
// 				require_once CED_FRUUGO_DIRPATH . 'admin/pages/category_mapping.php';
// 				break;
// 			case 'profile-view' : 
// 				require_once CED_FRUUGO_DIRPATH . 'admin/pages/profile.php';
// 				break;
// 			case 'products-view' : 
// 				require_once CED_FRUUGO_DIRPATH . 'admin/pages/profile.php';
// 				break;
// 			case 'bulk-action' : 
// 				require_once CED_FRUUGO_DIRPATH . 'admin/pages/profile.php';
// 				break;
// 			case 'orders-view' : 
// 				require_once CED_FRUUGO_DIRPATH . 'admin/pages/profile.php';
// 				break;
// 			case 'others-view' : 
// 				require_once CED_FRUUGO_DIRPATH . 'admin/pages/profile.php';
// 				break;
// 			}
// 		}
?>
<div id="ced_fruugo_marketplace_loader" class="loading-style-bg" style="display: none;">
	<img src="<?php esc_html_e(plugin_dir_url( __dir__ )); ?>/images/BigCircleBall.gif">
</div>
<div class="">
<div class="navigation-wrapper ced_fruugo_navigation_wrap">
	<ul class="navigation ced_fruugo_navigation">
					<li>
			<a href="<?php echo esc_attr( admin_url( 'admin.php?page=ced_fruugo&section=marketplaces&user_id=' . $user_id ) ); ?>" class="
								<?php
								if ( 'marketplaces' == $section ) {
									echo 'active ced_fruugo_navigation_active';}
								?>
			"><?php esc_attr_e( 'Configuration', 'ebay-integration-for-woocommerce' ); ?></a>
		</li>
		<li>
			<a href="<?php echo esc_attr( admin_url( 'admin.php?page=ced_fruugo&section=category_mapping&user_id=' . $user_id ) ); ?>" class="
								<?php
								if ( 'category_mapping' == $section ) {
									echo 'active ced_fruugo_navigation_active';}
								?>
			"><?php esc_attr_e( 'Category Mapping', 'ebay-integration-for-woocommerce' ); ?></a>
		</li>
		<li>
			<a class="
			<?php
			if ( 'profile' == $section ) {
				echo 'active ced_fruugo_navigation_active';}
			?>
			" href="<?php echo esc_attr( admin_url( 'admin.php?page=ced_fruugo&section=profile&user_id=' . $user_id ) ); ?>"><?php esc_attr_e( 'Profile', 'ebay-integration-for-woocommerce' ); ?></a>
		</li>
		<li>
			<a class="
			<?php
			if ( 'manage_products' == $section ) {
				echo 'active ced_fruugo_navigation_active';}
			?>
			" href="<?php echo esc_attr( admin_url( 'admin.php?page=ced_fruugo&section=manage_products&user_id=' . $user_id ) ); ?>"><?php esc_attr_e( 'Manage Products', 'ebay-integration-for-woocommerce' ); ?></a>
		</li>
		<li>
		<a class="
		<?php
		if ( 'bulk-action' == $section ) {
			echo 'active ced_fruugo_navigation_active';
		}
		?>
		" href="<?php echo esc_attr( admin_url( 'admin.php?page=ced_fruugo&section=bulk-action&user_id=' . $user_id ) ); ?>"><?php esc_attr_e( 'Bulk Action', 'ebay-integration-for-woocommerce' ); ?></a>
		</li>
		<li>
			<a class="
			<?php
			if ( 'orders' == $section ) {
				echo 'active ced_fruugo_navigation_active';}
			?>
			" href="<?php echo esc_attr( admin_url( 'admin.php?page=ced_fruugo&section=orders&user_id=' . $user_id ) ); ?>"><?php esc_attr_e( 'Orders', 'ebay-integration-for-woocommerce' ); ?></a>
		</li>
		<li>
		<a class="
		<?php
		if ( 'auto_acknowledge' == $section ) {
			echo 'active ced_fruugo_navigation_active';
		}
		?>
		" href="<?php echo esc_attr( admin_url( 'admin.php?page=ced_fruugo&section=auto_acknowledge&user_id=' . $user_id ) ); ?>"><?php esc_attr_e( 'Others', 'ebay-integration-for-woocommerce' ); ?></a>
		</li>
		</ul>
</div>
<?php
if ( 'umb-fruugo-main' == $current_page) {
	
	$activated_marketplaces = ced_fruugo_available_marketplace();
	if ( ! isset( $_POST['ced_fruugo_config_setting_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ced_fruugo_config_setting_nonce'] ) ), 'fruugo_config_setting' ) ) {
		return;
	}
	if ( isset( $_POST['ced_fruugo_save_credentials_button'] ) ) {
		if (isset($_POST['ced_fruugo_username_string']) && isset($_POST['ced_fruugo_password_string'])) {
			if ( '' == sanitize_text_field($_POST['ced_fruugo_username_string']) && '' == sanitize_text_field($_POST['ced_fruugo_password_string']) ) {
				$validation_notice   = array();
				$notice['message']   = __( 'Please fill details', 'ced-fruugo' );
				$notice['classes']   = 'notice notice-error';
				$validation_notice[] = $notice;
				
			} else {

				$validation_notice   = array();
				$notice['message']   = __( 'Configuration Setting Saved', 'ced-fruugo' );
				$notice['classes']   = 'notice notice-success';
				$validation_notice[] = $notice;
			}
		}
	}
	if ( isset( $_POST['ced_fruugo_authorize'] ) ) {
		// error_reporting(~0);
			// ini_set('display_errors', 1);
		if ( '' == sanitize_text_field($_POST['ced_fruugo_username_string']) && '' == sanitize_text_field($_POST['ced_fruugo_password_string']) ) {
			$validation_notice   = array();
			$notice['message']   = __( 'Please fill details', 'ced-fruugo' );
			$notice['classes']   = 'notice notice-error';
			$validation_notice[] = $notice;
		} else {
			$validate_fruugo    = get_option( 'ced_validate_fruugo', true );
			$ced_fruugo_details = get_option( 'ced_fruugo_details', true );
			if ( 'yes' == $validate_fruugo && '' != $ced_fruugo_details['userString'] && '' != $ced_fruugo_details['passString'] ) {
				$validation_notice   = array();
				$notice['message']   = __( 'Validation Done', 'ced-fruugo' );
				$notice['classes']   = 'notice notice-success';
				$validation_notice[] = $notice;
			}
		}
	}

	if ( isset( $validation_notice ) && count( $validation_notice ) ) {

		$ced_fruugo_helper->umb_print_notices( $validation_notice );
		unset( $validation_notice );
	}
}
?>
