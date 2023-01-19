<?php
require_once 'save-profile-view-data.php';
$profileID    = ( isset( $_GET['profileID'] ) ? sanitize_text_field($_GET['profileID']) : '' );
$profile_data = array();
if ( $profileID ) {
	//$query        = "SELECT * FROM `$table_name` WHERE `id`=$profileID";
	$profile_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ced_fruugo_fruugoprofiles WHERE `id`=%s", $profileID ), 'ARRAY_A' );
	//$profile_data = $wpdb->get_results( $query, 'ARRAY_A' );
	if ( is_array( $profile_data ) ) {
		$profile_data = isset( $profile_data[0] ) ? $profile_data[0] : $profile_data;

		/* fetcing basic information */
		$profile_name     = isset( $profile_data['name'] ) ? esc_attr( $profile_data['name'] ) : '';
		$enable           = isset( $profile_data['active'] ) ? $profile_data['active'] : false;
		$enable           = ( $enable ) ? 'yes' : 'no';
		$marketplaceName  = isset( $profile_data['marketplace'] ) ? esc_attr( $profile_data['marketplace'] ) : 'all';
		$all_marketplaces = fruugoget_enabled_marketplaces();
		array_unshift( $all_marketplaces, 'all' );

		$data = isset( $profile_data['profile_data'] ) ? json_decode( $profile_data['profile_data'], true ) : array();
		// echo '<pre>';
		// print_r($data);
		// die;
	}
} else {
	/* fetcing basic information */
	$profile_name     = isset( $profile_data['name'] ) ? esc_attr( $profile_data['name'] ) : '';
	$enable           = isset( $profile_data['active'] ) ? $profile_data['active'] : false;
	$enable           = ( $enable ) ? 'yes' : 'no';
	$marketplaceName  = isset( $profile_data['marketplace'] ) ? esc_attr( $profile_data['marketplace'] ) : 'null';
	$all_marketplaces = fruugoget_enabled_marketplaces();
	array_unshift( $all_marketplaces, 'all' );
}

echo '<form method="post" class="ced_fruugo_profile_save_form">';
echo '<div class="ced_fruugo_wrap ced_fruugo_wrap_opt">';
echo '<div class="back"><a href="' . esc_attr(get_admin_url()) . 'admin.php?section=profile&page=ced_fruugo">Go Back</a></div>';
?>
<?php
global $ced_fruugo_helper;
if ( ! session_id() ) {
	session_start();
}
if ( isset( $_SESSION['ced_fruugo_validation_notice'] ) ) {
	$value = $_SESSION['ced_fruugo_validation_notice'];
	$ced_fruugo_helper->umb_print_notices( $value );
	unset( $_SESSION['ced_fruugo_validation_notice'] );
}
?>

<div class="ced_fruugo_profile_timeline_wrapper">
	<div class="ced_fruugo_profile_timeline_heading">
		<?php esc_attr_e( 'Steps to Follow', 'ced-fruugo' ); ?>
	</div>	
	<div class="ced_fruugo_profile_timeline">
		<ul class="ced_fruugo_profile_timeline_ul">
			<li title="<?php esc_attr_e( 'Click to Select Metakeys', 'ced-fruugo' ); ?>" style="color: black;" data-target="ced_fruugo_select_metakeys_wrapper" class="ced_fruugo_profile_timeline_ul ced_fruugo_profile_metakeys_li active"><?php esc_attr_e( 'Select MetaKeys', 'ced_fruggo' ); ?></li>
			<li data-target="ced_fruugo_profile_basic_information_wrapper" class="ced_fruugo_profile_timeline_ul ced_fruugo_profile_basic_detail_li">Basic Details</li>
			<li data-target="ced_fruugo_profile_required_fields_wrapper" class="ced_fruugo_profile_timeline_ul ced_fruugo_profile_required_field_li">Required fields</li>
			<li data-target="ced_fruugo_profile_category_li" class="ced_fruugo_profile_timeline_ul ced_fruugo_profile_category_li">Category</li>
		</ul>
	</div>
 </div> 

	<div class="ced_fruugo_profile_instruction_to_use_wrapper">
		<div class="ced_fruugo_profile_instruction_to_use_heading">
			Instruction To Use
		</div>	
		<div class="ced_fruugo_profile_instruction_to_use">
			<p style="font-weight:bold; font-size:14px;">Profile can be created to assign similar type of values and categories to multiple products.</p>
			<p class="ced_fruugo_sel_metakey"><strong>1.</strong> Use "Select Product And Corresponding MetaKeys" section to select the metakeys of product you consider can be useful in mapping. Once you select the metakeys click on UPDATE. This step is not always necessary. If you have done it before, you can skip it for the next time you create a profile</p>
			<p class="ced_fruugo_basic_set"><strong>2.</strong> Under "BASIC SETTINGS" tab, you have option to setup basic information for your profile. Here you can give your profile a name and enable/disable it.</p>
			<p class="ced_fruugo_req_field"><strong>3.</strong> Under "REQUIRED FIELDS" sections, you have to fill in all the details that are mandatory for the products.'</p>
			<p class="ced_fruugo_category_sel"><strong>4.</strong> After Required Fields its time to select the fruugo category from "fruugo CATEGORY" section. Once you select the category you will get fields for variation products that need to be filled in if you wish to list variation products on fruugo'</p>
			<p><strong>5.</strong> Once done with above steps you can fill in the Extra information that can be sent with the products under "EXTRA FIELDS" section.'</p>
			<p><strong>6.</strong> If you have read above instructions carefully, you are good to go.</p>
		</div>
	</div>
	<?php
	$products_IDs  = array();
	$all_products  = new WP_Query(
		array(
			'post_type'      => array( 'product', 'product_variation' ),
			'post_status'    => 'publish',
			'posts_per_page' => 10,
		)
	);
	$products      = $all_products->posts;
	$selectedProID = $all_products->posts['0']->ID;
	foreach ( $products as $product ) {
		$product_IDs[] = $product->ID;
	}

	if ( isset( $data['selected_product_id'] ) ) {
		$selectedProID   = $data['selected_product_id'];
		$selectedProName = $data['selected_product_name'];
	} else {
		$selectedProID   = $product_IDs[0];
		$selectedProName = '';
	}

	?>
	<div class="ced_fruugo_profile_basic_information_wrapper" id="ced_fruugo_profile_basic_information_wrapper">
		<div class="ced_fruugo_profile_basic_information_heading">
			<?php esc_html_e( 'Basic Settings', 'ced-fruugo' ); ?>
		</div>
		<div class="ced_fruugo_profile_basic_information">
			<table>
				<tbody>
					<tr>
						<td>
							<span>
								<label>
									<?php
									esc_html_e( 'Profile Name', 'ced-fruugo' );
									?>
								</label>
								<input type="text" placeholder="<?php esc_html_e( 'Enter name for Profile', 'ced-fruugo' ); ?>" class="ced_fruugo_profile_name" name="profile_name"  value="<?php esc_attr_e($profile_name); ?>"></input>
							</span>
						</td>
					</tr>
					<tr>
						<td>
							<?php $checked = ( 'yes' == $enable ) ? 'checked="checked"' : ''; ?>
							<span>
								<label>
									<?php
										esc_html_e( 'Enable Profile', 'ced-fruugo' );
									?>
								</label>
								<input type="checkbox" name="enable" id="ced_fruugo_enable_marketpalce" <?php esc_attr_e($checked); ?> >
							</span>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<div class="ced_fruugo_profile_required_fields_wrapper" id="ced_fruugo_profile_required_fields_wrapper">
		<div class="ced_fruugo_profile_required_fields_heading">
			<?php esc_html_e( 'Required Fields', 'ced-fruugo' ); ?>
		</div>
		<div class="ced_fruugo_profile_required_fields">
			<?php
			$pFieldInstance = CED_FRUUGO_Product_Fields::get_instance();
			if ( is_wp_error( $pFieldInstance ) ) {
				$message = esc_html_e( 'Something went wrong please try again later!', 'ced-fruugo' );
				wp_die( esc_html($message) );
			}
			$fields = $pFieldInstance->get_custom_fields( 'required', false );
			// echo '<pre>';
			// print_r($fields);
			// die;
			?>
			<table>
				<tbody>
					<?php
					$requiredInAnyCase = array( '_umb_id_type', '_umb_id_val', '_umb_brand' );
					global $global_CED_FRUUGO_Render_Attributes;
					$marketPlace        = 'ced_fruugo_required_common';
					$productID          = 0;
					$categoryID         = '';
					$indexToUse         = 0;
					$selectDropdownHTML = fruugorenderMetaSelectionDropdownOnProfilePage();
					 //var_dump($selectDropdownHTML);

					foreach ( $fields as $value ) {
						if ( '_umb_fruugo_category' ==  $value['id'] ) {
							continue; }
						$isText   = true;
						$field_id = trim( $value['fields']['id'], '_' );
						if ( in_array( $value['fields']['id'], $requiredInAnyCase ) ) {
							$attributeNameToRender = ucfirst( $value['fields']['label'] );
							//$attributeNameToRender .= '<span class="ced_fruugo_wal_required"> [ Required ]</span>';
						} else {
							$attributeNameToRender = ucfirst( $value['fields']['label'] );
						}

						$default = isset( $data[ $value['fields']['id'] ]['default'] ) ? $data[ $value['fields']['id'] ]['default'] : '';
						echo '<tr>';
						echo '<td>';
						if ( '_select' == $value['type'] ) {
							$valueForDropdown = $value['fields']['options'];
							if ('_umb_id_type' == $value['fields']['id'] ) {
								unset( $valueForDropdown['null'] );
							}
							$valueForDropdown = apply_filters( 'ced_fruugo_alter_data_to_render_on_profile', $valueForDropdown, $field_id );
							
							$global_CED_FRUUGO_Render_Attributes->renderDropdownHTML(
								$field_id,
								$attributeNameToRender,
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
						} elseif ( '_text_input' == $value['type'] ) {
							$global_CED_FRUUGO_Render_Attributes->renderInputTextHTML(
								$field_id,
								$attributeNameToRender,
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
						} else {
							do_action( 'ced_fruugo_render_extra_data_on_profile', $value, $pFieldInstance );
							$isText = false;
						}
						echo '</td>';
						echo '<td>';
						if ( $isText ) {
							$previousSelectedValue = 'null';
							if ( isset( $data[ $value['fields']['id'] ]['metakey'] ) && 'null' != $data[ $value['fields']['id'] ]['metakey'] ) {
								$previousSelectedValue = $data[ $value['fields']['id'] ]['metakey'];
							}
							$updatedDropdownHTML = str_replace( '{{*fieldID}}', $value['fields']['id'], $selectDropdownHTML );
							$updatedDropdownHTML = str_replace( 'value="' . $previousSelectedValue . '"', 'value="' . $previousSelectedValue . '" selected="selected"', $updatedDropdownHTML );
							print_r($updatedDropdownHTML);
						}
						echo '</td>';
						echo '</tr>';
					}
					?>
					<?php wp_nonce_field( 'fruugo_marketplace', 'fruugo_marketplace_actions' ); ?>
				</tbody>
			</table>
		</div>
	</div>
<!-------------category required----------->
<div class="ced_fruugo_profile_required_fields_wrapper" id="ced_fruugo_profile_category_li">
		<div class="ced_fruugo_profile_required_fields_heading">
			<?php esc_html_e( 'Category', 'ced-fruugo' ); ?>
		</div>
		<div class="ced_fruugo_profile_required_fields">
			<?php
			if ( is_wp_error( $pFieldInstance ) ) {
				$message = esc_html_e( 'Something went wrong please try again later!', 'ced-fruugo' );
				wp_die( esc_html($message) );
			}
			$fields = $pFieldInstance->get_custom_fields( 'required', false );
			// echo '<pre>'; print_r($fields);
			?>
			<table>
				<tbody>
					<?php
					$requiredInAnyCase = array( '_umb_id_type', '_umb_id_val', '_umb_brand' );
					global $global_CED_FRUUGO_Render_Attributes;
					$marketPlace        = 'ced_fruugo_required_common';
					$productID          = 0;
					$categoryID         = '';
					$indexToUse         = 0;
					$selectDropdownHTML = fruugorenderMetaSelectionDropdownOnProfilePage();
				// print_r($selectDropdownHTML);die('f');

					foreach ( $fields as $value ) {
						if ( '_umb_fruugo_category' != $value['id'] ) {
							continue; }
						$isText   = true;
						$field_id = trim( $value['fields']['id'], '_' );
						if ( in_array( $value['fields']['id'], $requiredInAnyCase ) ) {
							$attributeNameToRender = ucfirst( $value['fields']['label'] );
							//$attributeNameToRender .= '<span class="ced_fruugo_wal_required"> [ Required ]</span>';
						} else {
							$attributeNameToRender = ucfirst( $value['fields']['label'] );
						}

						$default = isset( $data[ $value['fields']['id'] ]['default'] ) ? $data[ $value['fields']['id'] ]['default'] : '';
						echo '<tr>';
						echo '<td>';
						if ( '_select' == $value['type'] ) {
							$valueForDropdown = $value['fields']['options'];
							if ( '_umb_id_type' == $value['fields']['id'] ) {
								unset( $valueForDropdown['null'] );
							}
							$valueForDropdown = apply_filters( 'ced_fruugo_alter_data_to_render_on_profile', $valueForDropdown, $field_id );
							$global_CED_FRUUGO_Render_Attributes->renderDropdownHTML(
								$field_id,
								$attributeNameToRender,
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
						} elseif ( '_text_input' == $value['type'] ) {
							$global_CED_FRUUGO_Render_Attributes->renderInputTextHTML(
								$field_id,
								$attributeNameToRender,
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
						} else {
							do_action( 'ced_fruugo_render_extra_data_on_profile', $value, $pFieldInstance );
							$isText = false;
						}
						echo '</td>';
						echo '<td>';
						if ( $isText ) {
							$previousSelectedValue = 'null';
							if ( isset( $data[ $value['fields']['id'] ]['metakey'] ) && 'null' != $data[ $value['fields']['id'] ]['metakey'] ) {
								$previousSelectedValue = $data[ $value['fields']['id'] ]['metakey'];
							}
							$updatedDropdownHTML = str_replace( '{{*fieldID}}', $value['fields']['id'], $selectDropdownHTML );
							$updatedDropdownHTML = str_replace( 'value="' . $previousSelectedValue . '"', 'value="' . $previousSelectedValue . '" selected="selected"', $updatedDropdownHTML );
							print_r($updatedDropdownHTML);
						}
						echo '</td>';
						echo '</tr>';
					}

					?>
					<?php wp_nonce_field( 'fruugo_marketplace', 'fruugo_marketplace_actions' ); ?>
				</tbody>
			</table>
		</div>
</div>
<!-------------category required----------->
	<div class="ced_fruugo_profile_extra_fields_wrapper">
		<div class="ced_fruugo_profile_extra_fields_heading">
			<?php esc_html_e( 'Extra Fields', 'ced-fruugo' ); ?>
		</div>
		<div class="ced_fruugo_profile_extra_fields">

			<?php
			$pFieldInstance = CED_FRUUGO_Product_Fields::get_instance();
			if ( is_wp_error( $pFieldInstance ) ) {
				$message = esc_html_e( 'Something went wrong please try again later!', 'ced-fruugo' );
				wp_die( esc_html($message) );
			}
			$fields = $pFieldInstance->get_custom_fields( 'extra_fields', false );
			?>
			<table>
				<tbody>
					<?php
					$requiredInAnyCase = array( '_umb_id_type', '_umb_id_val', '_umb_brand' );
					global $global_CED_FRUUGO_Render_Attributes;
					$marketPlace        = 'ced_fruugo_required_common';
					$productID          = 0;
					$categoryID         = '';
					$indexToUse         = 0;
					$selectDropdownHTML = fruugorenderMetaSelectionDropdownOnProfilePage();
					// print_r($selectDropdownHTML);die('f');
					foreach ( $fields as $value ) {
						$isText = true;
						if ( isset( $value['fields'] ) ) {
							$field_id = trim( $value['fields']['id'], '_' );
							if ( in_array( $value['fields']['id'], $requiredInAnyCase ) ) {
								$attributeNameToRender  = ucfirst( $value['fields']['label'] );
								$attributeNameToRender .= '<span class="ced_fruugo_wal_required"> [ Required ]</span>';
							} else {
								$attributeNameToRender = ucfirst( $value['fields']['label'] );
							}

							$default = isset( $data[ $value['fields']['id'] ]['default'] ) ? $data[ $value['fields']['id'] ]['default'] : '';
						}
						echo '<tr>';
						echo '<td>';
						if ( '_select' == $value['type'] ) {
							$valueForDropdown = $value['fields']['options'];
							if ( '_umb_id_type' == $value['fields']['id'] ) {
								unset( $valueForDropdown['null'] );
							}
							$valueForDropdown = apply_filters( 'ced_fruugo_alter_data_to_render_on_profile', $valueForDropdown, $field_id );
							$global_CED_FRUUGO_Render_Attributes->renderDropdownHTML(
								$field_id,
								$attributeNameToRender,
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
						} elseif ( '_text_input' == $value['type'] ) {
							$global_CED_FRUUGO_Render_Attributes->renderInputTextHTML(
								$field_id,
								$attributeNameToRender,
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
						} elseif ( '_select2' == $value['type'] ) {
								$cat_name = array(
									'null' => __( '--select--', 'ced-fruugo' ),
									'AU'   => __( 'Australia', 'ced-fruugo' ),
									'AT'   => __( 'Austria', 'ced-fruugo' ),
									'BH'   => __( 'Bahrain', 'ced-fruugo' ),
									'BE'   => __( 'Belgium', 'ced-fruugo' ),
									'CA'   => __( 'Canada', 'ced-fruugo' ),
									'CN'   => __( 'China', 'ced-fruugo' ),
									'DK'   => __( 'Denmark', 'ced-fruugo' ),
									'EG'   => __( 'Egypt', 'ced-fruugo' ),
									'FI'   => __( 'Finland', 'ced-fruugo' ),
									'FR'   => __( 'France', 'ced-fruugo' ),
									'DE'   => __( 'Germany', 'ced-fruugo' ),
									'IN'   => __( 'India', 'ced-fruugo' ),
									'IT'   => __( 'Italy', 'ced-fruugo' ),
									'JP'   => __( 'Japan', 'ced-fruugo' ),
									'KW'   => __( 'Kuwait', 'ced-fruugo' ),
									'LU'   => __( 'Luxembourg', 'ced-fruugo' ),
									'NL'   => __( 'Netherlands', 'ced-fruugo' ),
									'NZ'   => __( 'New Zealand', 'ced-fruugo' ),
									'NO'   => __( 'Norway', 'ced-fruugo' ),
									'PL'   => __( 'Poland', 'ced-fruugo' ),
									'PT'   => __( 'Portugal', 'ced-fruugo' ),
									'QA'   => __( 'Qatar', 'ced-fruugo' ),
									'IE'   => __( 'Republic of Ireland', 'ced-fruugo' ),
									'RU'   => __( 'Russia', 'ced-fruugo' ),
									'SA'   => __( 'Saudi Arabia', 'ced-fruugo' ),
									'ZA'   => __( 'South Africa', 'ced-fruugo' ),
									'ES'   => __( 'Spain', 'ced-fruugo' ),
									'SE'   => __( 'Sweden', 'ced-fruugo' ),
									'CH'   => __( 'Switzerland', 'ced-fruugo' ),
									'AE'   => __( 'United Arab Emirates', 'ced-fruugo' ),
									'GB'   => __( 'United Kingdom', 'ced-fruugo' ),
									'US'   => __( 'United States of America', 'ced-fruugo' ),
								);
								?>
							<p class="form-field _umb_id_type_field ">
							<input type="hidden" name="ced_fruugo_required_common[]" value="_umb_fruggo_id_type" />
							<label for="">Country Restriction</label>
							<select name="_ced_fruugo_country_list[]" id="umb_bulk_act_category" multiple>
							<?php
							foreach ( $cat_name as $k => $val ) {
								$select = '';
								if ( isset( $_GET['profileID'] ) ) {
									$selected_cat = $data['selected_product_country'];
								}
								if(is_array($selected_cat )){
								if ( in_array( $k, $selected_cat ) ) {
									$select = 'selected="selected"';
								}
							}
								?>
							<option value="<?php esc_attr_e($k); ?>" <?php esc_attr_e($select) ; ?>><?php esc_attr_e($val); ?></option>
							
								<?php
							}
							echo '</select><span class="woocommerce-help-tip" data-tip="Unique identifier type your product must have to list on fruugo."></span>		</p>';
							$isText = false;
						} else {
							do_action( 'ced_fruugo_render_extra_data_on_profile', $value, $pFieldInstance );
							$isText = false;
						}
						echo '</td>';
						echo '<td>';
						if ( $isText ) {
							$previousSelectedValue = 'null';
							if ( isset( $data[ $value['fields']['id'] ]['metakey'] ) && 'null' != $data[ $value['fields']['id'] ]['metakey'] ) {
								$previousSelectedValue = $data[ $value['fields']['id'] ]['metakey'];
							}
							$updatedDropdownHTML = str_replace( '{{*fieldID}}', $value['fields']['id'], $selectDropdownHTML );
							$updatedDropdownHTML = str_replace( 'value="' . $previousSelectedValue . '"', 'value="' . $previousSelectedValue . '" selected="selected"', $updatedDropdownHTML );
							print_r($updatedDropdownHTML);
						}
						echo '</td>';
						echo '</tr>';
					}
					?>
					<?php wp_nonce_field( 'fruugo_marketplace', 'fruugo_marketplace_actions' ); ?>
				</tbody>
			</table>
		</div>
	</div>
	<?php
	echo '</div>';
	?>
	<div class="ced_fruugo_profile_all_wrapper">
		<div class="ced_fruugo_select_metakeys_wrapper" id="ced_fruugo_select_metakeys_wrapper">
			<div class="ced_fruugo_select_metakeys_heading">
				<?php esc_html_e( 'Select Product MetaKeys', 'ced-fruugo' ); ?>	
			</div>	
			<div class="ced_fruugo_select_metakeys_section">
				<input type="hidden" name="profileID" id="profileID" value="<?php esc_attr_e($profileID) ; ?>">
				<div class="ced_fruugo_pro_search_div">
					<div class="ced_fruugo_inline_box">
						<label for="ced_fruugo_pro_search_box"><?php esc_attr_e( 'Type Product Name Here', 'ced-fruugo' ); ?></label>
						<div class="ced_fruugo_wrap_div">
							<input type="hidden" name="selected_product_id" id="selected_product_id" value="<?php esc_attr_e($selectedProID) ; ?>">
							<input type="text" autocomplete="off" id="ced_fruugo_pro_search_box" name="ced_fruugo_pro_search_box" placeholder="Product Name" value="<?php esc_attr_e($selectedProName) ; ?>"/>
							<div id="ced_fruugo_suggesstion_box" style="display: none;"></div>
						</div>
						<img class="ced_fruugo_ajax_pro_search_loader" src="<?php esc_attr(CED_FRUUGO_URL) . 'admin/images/ajax-loader.gif'; ?>" style="display: none;">
					</div>	
				</div>
				<?php fruggorenderMetaKeysTableOnProfilePage( $selectedProID ); ?>
			</div>
		</div>
		<div class="ced_fruugo_profile_submit_button">
			<h2><?php esc_html_e( 'Other', 'ced-fruugo' ); ?></h2>
			<p class="ced_fruugo_button_right">
				<?php 
				
				$country=get_option('ced_fruugo_country_other');
				$vat=get_option('ced_fruugo_vat_rate');
				$currency=get_option('ced_fruugo_currency_other');
				$langauge=get_option('ced_fruugo_langauge_other');
				
				?>
				<?php wp_nonce_field( 'fruugo_profile', 'fruugo_profile_actions' ); ?>
				<label for="ced_fruugo_pro_search_box"><?php esc_attr_e( 'Fruugo Currency', 'ced-fruugo' ); ?></label>
				<input type="text" autocomplete="off" id="ced_fruugo_pro_search_box" name="ced_fruugo_currency_other" placeholder="Currency" value="<?php esc_attr_e($currency) ; ?>"/>
				<label for="ced_fruugo_pro_search_box"><?php esc_attr_e( 'Fruugo Country', 'ced-fruugo' ); ?></label>
				<input type="text" autocomplete="off" id="ced_fruugo_pro_search_box" name="ced_fruugo_country_other" placeholder="Country" value="<?php esc_attr_e($country) ; ?>"/>
				<label for="ced_fruugo_pro_search_box"><?php esc_attr_e( 'Fruugo Language', 'ced-fruugo' ); ?></label>
				<input type="text" autocomplete="off" id="ced_fruugo_pro_search_box" name="ced_fruugo_langauge_other" placeholder="Langauge" value="<?php esc_attr_e($langauge) ; ?>"/>
				<label for="ced_fruugo_pro_search_box"><?php esc_attr_e( 'Vat Rate', 'ced-fruugo' ); ?></label>
				<input type="text" autocomplete="off" id="ced_fruugo_pro_search_box" name="ced_fruugo_vat_rate" placeholder="VAT RATE" value="<?php esc_attr_e($vat) ; ?>"/>
			</p>
		</div>
		<div class="ced_fruugo_profile_submit_button">
			<h2><?php esc_html_e( 'SAVE PROFILE', 'ced-fruugo' ); ?></h2>
			<p class="ced_fruugo_button_right">
				<?php wp_nonce_field( 'fruugo_profile', 'fruugo_profile_actions' ); ?>
				<input class="button button-ced_fruggo ced_fruugo_save_profile_button" value="<?php esc_html_e( 'Save Profile', 'ced-fruugo' ); ?>" name="saveProfile" type="submit">
			</p>
		</div>
	</div>
	<?php
	echo '</form>';
	?>
