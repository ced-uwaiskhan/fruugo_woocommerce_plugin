<?php
/**
 * All Marketplaces Listing Section
 *
 * @package  Shopify_connector_for_woocommerce_admin
 * @version  1.0.0
 * @link     https://cedcommerce.com
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( is_array( $active_marketplaces ) && ! empty( $active_marketplaces ) ) {
	?>
	<div class="ced-marketplaces-heading-main-wrapper">
		<div class="ced-marketplaces-heading-wrapper">
			<h2><?php esc_html_e( 'Active Marketplaces', 'woocommerce-shopee-integration' ); ?></h2>
		</div>
	</div>
	<div class="ced-marketplaces-card-view-wrapper">
		<?php
		foreach ( $active_marketplaces as $key => $value ) {
			$url = admin_url( 'admin.php?page=' . $value['menu_link'] );
			?>
			<div class="ced-marketplace-card <?php echo esc_attr( $value['name'] ); ?>">
				<a href="<?php echo esc_url( $url ); ?>">
					<div class="thumbnail">
						<div class="thumb-img">
							<img class="img-responsive center-block integration-icons" src="<?php echo esc_url( $value['card_image_link'] ); ?>" height="auto" width="auto" alt="how to sell on vip marketplace">
						</div>
					</div>
					<div class="mp-label"><?php echo esc_attr( $value['name'] ); ?></div>
				</a>
			</div>
			<?php
		}
		?>
	</div>

	<!-- <input type="button" class="ced_dropbox_test" value="Authorise"> -->
	<?php
}
?>
