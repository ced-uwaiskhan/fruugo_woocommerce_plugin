<?php
require_once '../../../../wp-blog-header.php';

if ( ! defined( 'ABSPATH' ) ) {
	die;
}
/**
 * Cron to fetch order and auto acknowledge
 *
 * @class    Class_CED_Fruugo_Cron
 * @version  1.0.0
 * @package Class
 * 
 */

class Class_CED_Fruugo_Cron {

	public function __construct() {

		do_action( 'ced_fruugo_cron_job' );
		// do_action('ced_fruugo_cron_inventory');
	}
}
$marketplace_cron_obj = new Class_CED_Fruugo_Cron();

