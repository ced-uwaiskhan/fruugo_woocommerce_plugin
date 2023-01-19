<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Adds a global instance to call some core functionality on-the-fly.
 *
 * @class    CED_FRUUGO_Render_Attributes
 * @version  1.0.0
 * @package  Class
 * 
 */
class CED_FRUUGO_Render_Attributes {

	private static $_instance;

	public static function getInstance() {
		if ( ! self::$_instance instanceof self ) {
			self::$_instance = new self();
		}

			return self::$_instance;
	}

	/*
	* Function to render input text html
	*/
	public function renderInputTextHTML( $attribute_id, $attribute_name, $categoryID, $productID, $marketPlace, $attribute_description = null, $indexToUse, $additionalInfo = array( 'case' => 'product' ), $conditionally_required = false, $conditionally_required_text = '' ) {

		global $post,$product,$loop;
		$fieldName = $categoryID . '_' . $attribute_id;
		if ( 'product' == $additionalInfo['case'] ) {
			$previousValue = get_post_meta( $productID, $fieldName, true );
		} else {
			$previousValue = $additionalInfo['value'];
		}

		?>
		<p class="form-field _umb_brand_field ">
			<input type="hidden" name="<?php esc_attr_e($marketPlace . '[]'); ?>" value="<?php esc_attr_e($fieldName); ?>" />
			<label for=""><?php esc_attr_e($attribute_name); ?>
			</label>
						<input class="short" style="" name="<?php esc_attr_e($fieldName) . esc_attr_e('[' . $indexToUse . ']') ; ?>" id="" value="<?php esc_attr_e($previousValue); ?>" placeholder="" type="text" /> 
			<?php
			if ( ! is_null( $attribute_description ) && '' != $attribute_description ) {
				echo wc_help_tip( __( $attribute_description, 'ced-fruugo' ) );
			}
			if ( $conditionally_required ) {
				echo wc_help_tip( __( $conditionally_required_text, 'ced-fruugo' ) );
			}
			?>
		</p>
		<?php
	}
	/*
	* Function to render dropdown html
	*/
	public function renderDropdownHTML( $attribute_id, $attribute_name, $values, $categoryID, $productID, $marketPlace, $attribute_description = null, $indexToUse, $additionalInfo = array( 'case' => 'product' ) ) {
		$fieldName = $categoryID . '_' . $attribute_id;
		if ( 'product' == $additionalInfo['case'] ) {
			$previousValue = get_post_meta( $productID, $fieldName, true );
		} else {
			$previousValue = $additionalInfo['value'];
		}
		?>
		<p class="form-field _umb_id_type_field ">
			<input type="hidden" name="<?php esc_attr_e($marketPlace . '[]'); ?>" value="<?php esc_attr_e($fieldName); ?>" />
			<label for=""><?php esc_attr_e($attribute_name); ?></label>
			<select id="<?php esc_attr_e($fieldName); ?>" name="<?php esc_attr_e($fieldName) . esc_attr_e('[' . $indexToUse . ']') ; ?>" class="select short" style="">
				<?php
				// echo '<option value="">-- Select --</option>';
				foreach ( $values as $key => $value ) {
					$key           = preg_replace( '/\s+/', '', $key );
					$previousValue = preg_replace( '/\s+/', '', $previousValue );
					if ( $previousValue == $key ) {
						echo '<option value="' . esc_attr($key) . '" selected>' . esc_attr($value) . '</option>';
					} else {
						echo '<option value="' . esc_attr($key) . '">' . esc_attr($value) . '</option>';
					}
				}
				?>
			</select>
			<?php
			if ( ! is_null( $attribute_description ) && '' != $attribute_description ) {
				echo wc_help_tip( __( $attribute_description, 'ced-fruugo' ) );
			}
			?>
		</p>
		<?php
	}

}
global $global_CED_FRUUGO_Render_Attributes;
$global_CED_FRUUGO_Render_Attributes = CED_FRUUGO_Render_Attributes::getInstance();
?>
