<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
global $wpdb, $ced_fruugo_helper;
// profile listing class.
require_once CED_FRUUGO_DIRPATH . 'admin/helper/class-ced-umb-order-listing.php';
// header file.
 require_once CED_FRUUGO_DIRPATH . 'admin/pages/header.php';

$order_lister = new CED_FRUUGO_Order_Lister();
$order_lister->prepare_items();

$notices = array();
if ( isset( $_POST['umb_fetch_fruugo_order'] ) ) {

	$marketplace = isset( $_POST['umb_slctd_marketplace'] ) ? sanitize_text_field( $_POST['umb_slctd_marketplace'] ) : 'all';
	if ( 'all' != $marketplace) {
		$marketplace = 'fruugo';
		$file_name   = CED_FRUUGO_DIRPATH . 'marketplaces/' . $marketplace . '/class-' . $marketplace . '.php';
		if ( file_exists( $file_name ) ) {

			require_once $file_name;
			$class_name = 'CED_FRUUGO_manager';
			if ( class_exists( $class_name ) ) {
				$instance = $class_name::get_instance();
				if ( ! is_wp_error( $instance ) ) {

					$notices = $instance->fetchOrders();

				} else {
					$message   = __( 'An unexpected error occured, please try again!', 'ced-fruugo' );
					$classes   = 'error is-dismissable';
					$error1    = array(
						'message' => $message,
						'classes' => $classes,
					);
					$notices[] = $error1;
				}
			} else {
				$message   = __( 'Class missing to perform operation, please check if extension configured successfully!', 'ced-fruugo' );
				$classes   = 'error is-dismissable';
				$error1    = array(
					'message' => $message,
					'classes' => $classes,
				);
				$notices[] = $error1;
			}
		} else {
			$message   = __( 'Please check if selected marketplace is active!', 'ced-fruugo' );
			$classes   = 'error is-dismissable';
			$error1    = array(
				'message' => $message,
				'classes' => $classes,
			);
			$notices[] = $error1;
		}
	}
}

if ( isset( $_POST['ced_fruugo_refund_submit'] ) && isset( $_REQUEST['orderid'] ) && isset( $_REQUEST['framework'] ) ) {
	if ( ! isset( $_POST['fruugo_order_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fruugo_order_actions'] ) ), 'fruugo_order' ) ) {
		return;
	}
	if ( ! isset( $_POST['ced_fruugo_refund_check'] ) ) {
		$message   = __( 'Please check any line item', 'ced-fruugo' );
		$classes   = 'error is-dismissable';
		$error1    = array(
			'message' => $message,
			'classes' => $classes,
		);
		$notices[] = $error1;
		return;
	}
	$linenumbers  = sanitize_text_field($_POST['ced_fruugo_refund_check']);
	$comments1    = isset($_POST['ced_fruugo_refund_comment']) ? sanitize_text_field($_POST['ced_fruugo_refund_comment']) :'';
	$reasons      = isset($_POST['ced_fruugo_refund_reason']) ? sanitize_text_field($_POST['ced_fruugo_refund_reason']):'';
	$orderId      = sanitize_text_field($_REQUEST['orderid']);
	$order_detail = get_post_meta( $orderId, 'order_detail', true );
	$framework    = sanitize_text_field($_REQUEST['framework']);

	do_action( 'ced_fruugo_order_refund_processing_' . $framework, $linenumbers, $orderId, $order_detail, $reasons, $comments1 );

}

if ( count( $notices ) ) {
	$ced_fruugo_helper->umb_print_notices( $notices );
	unset( $notices );
}
if ( isset( $_REQUEST['page'] ) && isset( $_REQUEST['sub-section'] ) && isset( $_REQUEST['orderid'] ) ) {
	$subSection = sanitize_text_field($_REQUEST['sub-section']);
	$orderId    = sanitize_text_field($_REQUEST['orderid']);
	// echo "it is single order page";
	$order_id          = $orderId;
	$merchant_order_id = get_post_meta( $order_id, 'merchant_order_id', true );
	$purchaseOrderId   = get_post_meta( $order_id, 'purchaseOrderId', true );
	$fulfillment_node  = get_post_meta( $order_id, 'fulfillment_node', true );
	$order_detail      = get_post_meta( $order_id, 'order_detail', true );
	$order_item        = get_post_meta( $order_id, 'order_items', true );
	// print_r($order_detail);echo "<br>";
	// print_r($order_item);
	$i        = 1;
	$urlToUse = get_admin_url() . 'admin.php?page=umb-fruugo-orders';

	$refundReasons = array(
		'TaxExemptCustomer'           => __( 'Tax Exempt Customer', 'ced-fruugo' ),
		'ItemNotAsAdvertised'         => __( 'Item not as Advertised', 'ced-fruugo' ),
		'IncorrectItemReceived'       => __( 'Incorrect Item Received', 'ced-fruugo' ),
		'CancelledYetShipped'         => __( 'Cancelled order was shipped', 'ced-fruugo' ),
		'ItemNotReceivedByCustomer'   => __( 'Customer did not receive item', 'ced-fruugo' ),
		'IncorrectShippingPrice'      => __( 'Shipping Price Discrepancy', 'ced-fruugo' ),
		'DamagedItem'                 => __( 'Damaged Item', 'ced-fruugo' ),
		'DefectiveItem'               => __( 'Defective Item', 'ced-fruugo' ),
		'CustomerChangedMind'         => __( 'Customer Changed Mind', 'ced-fruugo' ),
		'CustomerReceivedItemLater'   => __( 'Customer received the item later than max', 'ced-fruugo' ),
		'Missing Parts/ Instructions' => __( 'Missing Parts Instructions', 'ced-fruugo' ),
		'Finance -> Goodwill'         => __( 'Finance Goodwill', 'ced-fruugo' ),
		'Finance -> Rollback'         => __( 'Finance Rollback', 'ced-fruugo' ),
	);

	$refundReasonsDropdown = '<select name="name">';
	foreach ( $refundReasons as $key => $value ) {
		$refundReasonsDropdown .= '<option value="' . $key . '">' . $value . '</option>';
	}
	$refundReasonsDropdown .= '</select>';

	?>
	<div class="ced_fruugo_wrap">
		<div class="back">
			<a href="<?php esc_html_e($urlToUse); ?>"><?php esc_html_e( 'Go Back', 'ced-fruugo' ); ?></a>
		</div>
		<h2 class="ced_fruugo_setting_header ced_fruugo_bottom_margin"><?php esc_html_e( 'Refund Section', 'ced-fruugo' ); ?></h2>
	<form action = "" method="post">
	
	<table class="wp-list-table widefat striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Select', 'ced-fruugo' ); ?></th>
				<th><?php esc_html_e( 'Name', 'ced-fruugo' ); ?></th>
				<th><?php esc_html_e( 'Reason', 'ced-fruugo' ); ?></th>
				<th><?php esc_html_e( 'Comments', 'ced-fruugo' ); ?></th>
			</tr>
		</thead>
		<tbody>
	<?php
	if ( isset( $order_item ) && ! empty( $order_item ) ) {
		foreach ( $order_item as $orderline ) {
			?>
			<tr>
				<td>
					<input type="checkbox" value="<?php esc_html_e($orderline['lineNumber']); ?>" name="ced_fruugo_refund_check[<?php esc_html_e($i); ?>]">
				</td>
				<td>
					<?php esc_html_e($orderline['item']['productName']); ?>
				</td>
				<td>
					<?php
					$selectToRender = str_replace( 'name="name"', 'name="ced_fruugo_refund_reason[' . $i . ']"', $refundReasonsDropdown );
					esc_html_e($selectToRender);
					?>
				</td>
				<td>
					<input type ="text" placeholder = "<?php esc_html_e( 'Comments', 'ced-fruugo' ); ?>" name="ced_fruugo_refund_comment[<?php esc_html_e($i); ?>]">
				</td>
			</tr>
			<?php
			$i++;
		}
	}
	?>
		</tbody>
	</table>
		<p class="ced_fruugo_button_right">
			<?php wp_nonce_field( 'fruugo_order', 'fruugo_order_actions' ); ?>
			
			<input type="submit" name="ced_fruugo_refund_submit" value="<?php esc_html_e( 'Refund', 'ced-fruugo' ); ?>" class="button button-ced_fruggo">
		</p>
	</form>
	</div>
	<?php
} elseif ( isset( $_REQUEST['customaction'] ) ) {
	$customaction = isset( $_REQUEST['customaction'] ) ? sanitize_text_field($_REQUEST['customaction']) : false;
	if ( $customaction ) {
		do_action( 'ced_fruugo_custom_action', $customaction );
	}
} else {
	?>
	<div class="ced_fruugo_wrap">
		<h2 class="ced_fruugo_setting_header"><?php esc_html_e( 'Manage Orders', 'ced-fruugo' ); ?></h2>
		<form id="ced_fruugo_orders" method="post">
			
		<input type="hidden" name="page" value="<?php esc_html_e(sanitize_text_field($_REQUEST['page'])); ?>" />
		<?php $order_lister->top_actions(); ?>
        <?php $order_lister->search_box( 'Search Order', 'order_id' ); ?>
		<?php $order_lister->display(); ?>
		</form>
		 
	</div>
<?php } ?>
