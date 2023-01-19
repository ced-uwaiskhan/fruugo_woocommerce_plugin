<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$section     = 'prerequisite';
$pre_active  = '';
$step_active = '';

if ( isset( $_GET['section'] ) ) {
	$section = sanitize_text_field($_GET['section']);
	if ( 'prerequisite' == $section) {
		$pre_active = 'current';
	} else {
		$step_active = 'current';
	}
} else {
	$pre_active = 'current';
}

 require_once CED_FRUUGO_DIRPATH . 'admin/pages/header.php';?>
<div class="ced_fruugo_prerequisite_wrapper">
	<div class="ced_fruugo_wrap">
		<ul class="subsubsub">
			<li><a class="<?php esc_html_e($pre_active); ?>" href="<?php esc_html_e (admin_url()); ?>/admin.php?page=ced_fruugo&amp;section=prerequisite"><?php esc_html_e( 'PREREQUISITES', 'ced-fruugo' ); ?></a>|</li>
			<li><a class="<?php esc_html_e( $step_active); ?>" href="<?php esc_html_e(admin_url()); ?>/admin.php?page=ced_fruugo&amp;section=steptofollow"><?php esc_html_e( 'TIMELINE', 'ced-fruugo' ); ?></a></li>
		</ul>
	
		<?php
		if ( 'prerequisite' == $section) {
			?>
		<h2 class="ced_fruugo_setting_header" style="text-align:center"><?php esc_html_e( 'prerequisite', 'ced_fruggo' ); ?></h2>
		<div class = "ced_fruugo_prerequisite_table_wrap wrap">
			<!-- <table class="wp-list-table widefat fixed striped ced_fruugo_prerequisite"> -->
			<table class="wp-list-table widefat fixed striped">
				<tr>
					<th colspan="4"><b><?php esc_html_e( 'Parameters', 'ced_fruggo' ); ?></b></th>
					<th><b><?php esc_html_e( 'Status', 'ced_fruggo' ); ?></b></th>
				</tr>
				<?php
				if ( version_compare( PHP_VERSION, '5.5.5' ) == -1 ) {
					$php = 'not_compatible';
				} else {
					$php = 'compatible';
				}
				if ( ! extension_loaded( 'curl' ) ) {
					$curl = 'not_compatible';
				} else {
					$curl = 'compatible';
				}
				$marketPlaces  = ced_fruugo_available_marketplace();
				$credentials   = 'Valid';
				$preRequisites = array(
					'curl'            => $curl,
					'php version 5.5' => $php,
					'credentials'     => $credentials,
				);
				foreach ( $preRequisites as $key => $preRequisite ) {
					?>
					<tr>
						<?php
						if ( 'credentials' == $key) {
							?>
							<td colspan="4"><?php esc_html_e( strtoupper( $key ), 'ced_fruggo' ); ?></td>
							<td>
								<?php
								foreach ( $marketPlaces as $marketPlace ) {
									$validation = get_option( "ced_fruugo_validate_$marketPlace" );
									$validation = ( isset( $validation ) &&  'yes' == $validation ) ? 'Valid' : 'Invalid';
									?>
									<p><?php esc_html_e(strtoupper( $marketPlace )); ?>:
											<?php
											$saved_fruugo_details = get_option( 'ced_fruugo_details', array() );
											$outh_secret_token    = isset( $saved_fruugo_details['oauth_secret'] ) ? $saved_fruugo_details['oauth_secret'] : '';
											if ( isset( $saved_fruugo_details['access_token'] ) ) {
												?>
											<img src = "<?php esc_html_e(CED_FRUUGO_URL); ?>/admin/images/check.png">
												<?php
											} else {

												$toUseUrl = get_admin_url();
												$toUseUrl = $toUseUrl . 'admin.php?page=umb-fruugo-main';
												?>
											<a href="<?php esc_html_e($toUseUrl); ?>">
												<img src = "<?php esc_html_e(CED_FRUUGO_URL); ?>/admin/images/cross.png">
											</a>

											<?php } ?>
									</p>
									<?php
								}
								?>
							</td>
							<?php
						} else {
							?>
							<td colspan="4"><?php esc_html_e(strtoupper( $key )); ?></td>
							<td>
							<?php
							if ( 'compatible' == $preRequisite) {
								?>
								<img src = "<?php esc_html_e(CED_FRUUGO_URL); ?>/admin/images/check.png">
								<?php
							} else {
								?>
								<img src = "<?php esc_html_e(CED_FRUUGO_URL); ?>/admin/images/cross.png">
								<?php } ?>
							 </td>
							<?php
						}
						?>
					</tr>
					<?php
				}
				?>
			</table>
		
	
	<br/>
	<h2 class="ced_fruugo_setting_header ced_fruugo_bottom_margin" style="text-align:center"><?php esc_html_e( 'Guidelines', 'ced_fruggo' ); ?></h2>
			<table class="wp-list-table widefat fixed striped">
		
			<tr>
				<th colspan="4"><b><?php esc_html_e( 'Parameters', 'ced_fruggo' ); ?></b></th>
				<th><b><?php esc_html_e( 'Status', 'ced_fruggo' ); ?></b></th>
			</tr>
			<?php
			$required = array(
				'Cron'                => __( 'Cron should be working properly on server.', 'ced-fruugo' ),
				'Product Identifier'  => __( 'A valid product identifier with valid product identifier code.', 'ced-fruugo' ),
				'Product Taxcode'     => __( 'A valid product Tax code.', 'ced-fruugo' ),
				'Product Description' => __( 'Product description should be availble for the products to be uploaded.', 'ced-fruugo' ),
				'Product Variation'   => __( 'fruugo allows only 2 attributes for a variations. ( Example - Size & Color )', 'ced-fruugo' ),
			);
			foreach ( $required as $k => $v ) {
				?>
			<tr>
				<td colspan = '4'><?php esc_html_e(strtoupper( $k )); ?></td>
				<td><?php esc_html_e($v); ?></td>
			</tr>
				<?php
			}
			?>
		</table>
		<br/>
		</div>
	
			<?php
		}
		if (  'steptofollow' == $section ) {
			$marketPlaces         = ced_fruugo_available_marketplace();
			$saved_fruugo_details = get_option( 'ced_fruugo_details', array() );
			if ( isset( $marketPlaces ) && ! empty( $marketPlaces ) ) {
				?>
				<div class="ced_fruugo_steptofollow">
			<h2 class="ced_fruugo_setting_header" style="text-align:center"><?php esc_html_e( 'Your Timeline', 'ced_fruggo' ); ?></h2>
		
				<table class="wp-list-table widefat fixed striped ced_fruugo_steptofollow">
				<tr>
					<th>
						<b><?php esc_html_e( 'STEPS', 'ced_fruggo' ); ?></b>
					</th>
				<?php
				foreach ( $marketPlaces as $marketPlace ) {
					?>
					<td>
						<b><?php esc_html_e( 'Steps Completed', 'ced-fruugo' ); ?></b>
					</td>
					<?php
				}
				?>
				</tr>
				<tr>
					<th>
						<b><?php esc_html_e( 'Configuration Save', 'ced_fruggo' ); ?></b>
					</th>
				
				
					<td>
						<?php

						if ( ! empty( $saved_fruugo_details ) ) {
							echo 'YES'
							?>
							<img src="<?php esc_html_e(CED_FRUUGO_URL . 'admin/images/check.png'); ?>">
							<?php
						} else {
							$toUseUrl = get_admin_url();
							$toUseUrl = $toUseUrl . 'admin.php?page=umb-fruugo-main';
							echo 'Please Save the Configuration Details.'
							?>
							<a href="<?php esc_html_e($toUseUrl); ?>"><img src="<?php esc_html_e(CED_FRUUGO_URL . 'admin/images/cross.png'); ?>"></a>
							<?php
						}
						?>
					</td>
					
				</tr>
				<tr>
					<th>
						<b><?php esc_html_e( 'Configuration Validation', 'ced_fruggo' ); ?></b>
					</th>
				
					<td>
					<?php
					if ( isset( $saved_fruugo_details['access_token'] ) ) {
						echo 'YES'
						?>
							<img src="<?php esc_html_e(CED_FRUUGO_URL . 'admin/images/check.png'); ?>">
							<?php
					} else {
						$toUseUrl = get_admin_url();
						$toUseUrl = $toUseUrl . 'admin.php?page=umb-fruugo-main';
						echo 'Please Authorize your account.'
						?>
							<a href="<?php esc_html_e($toUseUrl); ?>"><img src="<?php esc_html_e(CED_FRUUGO_URL . 'admin/images/cross.png'); ?>"></a>
							<?php
					}
					?>
					</td>
				</tr>
				<tr>
					<th>
						<b><?php esc_html_e( 'Category Mapping', 'ced_fruggo' ); ?></b>
					</th>
				
					<td>
						<?php
						$catmap = get_option( 'ced_fruugo_selected_categories', array() );
						//die('ggg');
						if ( ! empty( $catmap ) ) {
							echo 'YES'
							?>
							<img src="<?php esc_html_e(CED_FRUUGO_URL . 'admin/images/check.png'); ?>">
							<?php
						} else {
							$toUseUrl = get_admin_url();
							$toUseUrl = $toUseUrl . 'admin.php?page=umb-fruugo-cat-map';
							echo 'Please select the categories for your Products.'
							?>
							<a href="<?php esc_html_e($toUseUrl); ?>"><img src="<?php esc_html_e(CED_FRUUGO_URL . 'admin/images/cross.png'); ?>"></a>
							<?php
						}
						?>
					</td>
				</tr>
				<tr>
					<th>
						<b><?php esc_html_e( 'Profile Creation', 'ced_fruggo' ); ?></b>
					</th>
				
					<td>
						<?php
						global $wpdb;
						// $prefix    = $wpdb->prefix . CED_FRUUGO_PREFIX;
						// $tableName = $prefix . '_fruugoprofiles';
						// $sql       = "SELECT * FROM `$tableName`";
						//$result    = $wpdb->get_results( $sql, 'ARRAY_A' );
						
						$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ced_fruugo_fruugoprofiles" ), 'ARRAY_A' );
						$count  = count( $result );
						if ( $count > 0 ) {
							echo 'YES'
							?>
							<img src="<?php esc_html_e(CED_FRUUGO_URL . 'admin/images/check.png'); ?>">
							<?php
						} else {
							$toUseUrl = get_admin_url();
							$toUseUrl = $toUseUrl . 'admin.php?page=umb-fruugo-profile';
							echo 'Profile not created.'
							?>
							<a href="<?php esc_html_e($toUseUrl); ?>"><img src="<?php esc_html_e(CED_FRUUGO_URL . 'admin/images/cross.png'); ?>"></a>
							<?php
						}
						?>
					</td>
				</tr>
				<tr>
					<th>
						<b><?php esc_html_e( 'Product Upload', 'ced_fruggo' ); ?></b>
					</th>
				
					<td>
						<?php
						$store_product = get_posts(
							array(
								'numberposts'  => -1,
								'post_type'    => 'product',
								'meta_key'     => 'fruugoSkuId',
								'meta_compare' => 'Exists',
							)
						);
						if ( ! empty( $store_product ) ) {
							echo 'YES'
							?>
							<img src="<?php esc_html_e(CED_FRUUGO_URL . 'admin/images/check.png'); ?>">
							<?php
						} else {
							$toUseUrl = get_admin_url();
							$toUseUrl = $toUseUrl . 'admin.php?page=umb-fruugo-pro-mgmt';
							echo 'Not Yet uploaded a product.'
							?>
							<a href="<?php esc_html_e($toUseUrl); ?>"><img src="<?php esc_html_e(CED_FRUUGO_URL . 'admin/images/cross.png'); ?>"></a>
							<?php
						}
						?>
					</td>
				</tr>
				<tr>
					<th>
						<b><?php esc_html_e( 'Fetch Order', 'ced_fruggo' ); ?></b>
					</th>
				
					<td>
						<?php
						$order_status = get_option( 'ced_fruugo_orders_fetched', false );
						if ( $order_status ) {
							echo 'YES'
							?>
							<img src="<?php esc_html_e(CED_FRUUGO_URL . 'admin/images/check.png'); ?>">
							<?php
						} else {
							$toUseUrl = get_admin_url();
							$toUseUrl = $toUseUrl . 'admin.php?page=umb-fruugo-orders';
							echo 'No Order Fetched.'
							?>
							<a href="<?php esc_html_e($toUseUrl); ?>"><img src="<?php esc_html_e( CED_FRUUGO_URL . 'admin/images/cross.png'); ?>"></a>
							<?php
						}
						?>
					</td>
				</tr>
				
				</table>
				</div>
				<?php
			}
		}
		?>
	</div>
</div>
