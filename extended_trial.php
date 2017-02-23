<?php
/**
 * @package Woocommerce_Extended_Trial_coupons
 * @version 1.6
 */
/*
Plugin Name: WooCommerce Extended Trial Coupons
Plugin URI: http://towerhousestudio.com
Description: Allows to have extended trials for woocommerce subscriptions.
Author: TowerHouseStudio
Version: 1.0
Author URI: http://towerhousestudio.com
*/


// Add custom coupon types
add_filter( 'woocommerce_coupon_discount_types', 'extended_trial_custom_coupons');

function extended_trial_custom_coupons($discount_types){
	return array_merge(
			$discount_types,
			array(
				'extended_trial'         => __( 'Extended trial', 'extended_trial' ),
			)
		);
}

//New validation for the new coupon type, without this woocommerce will 
//show an error saying the coupon is not valid
add_filter('woocommerce_subscriptions_validate_coupon_type', 'extended_trial_coupon_validation', 10, 3);
function extended_trial_coupon_validation($arg1, $coupon, $valid){
	
	if($coupon->is_type('extended_trial')){
		$month_variation = $coupon->__get('coupon_custom_fields')['month_variation'];
		$year_variation = $coupon->__get('coupon_custom_fields')['yearly_variation'];

		if(!empty($month_variation) && !empty($year_variation)){
			return false;
		}
	}

	return true;
}

//This is the custom logic for the new coupon type, after is validated we bring the 
//product indicated by the id stored in the custom field, and replace the selected one by the product
//with the longer trial
add_action('woocommerce_applied_coupon', 'extended_trial_after_coupon');
function extended_trial_after_coupon($coupon_code){
	// Get the coupon
	$the_coupon = new WC_Coupon( $coupon_code );
	if($the_coupon->is_type('extended_trial')){
		
		$cart_item = WC()->cart->get_cart();
		if(is_array($cart_item) && count($cart_item) > 0){
			$id = key($cart_item);
			// error_log("ID: ". var_export($id),1);
			$cart_item = WC()->cart->get_cart_item($id);
			$period = $cart_item['data']->subscription_period;
			$product_id = $cart_item['data']->id;
			$coupon_variation = $the_coupon->__get('coupon_custom_fields')['month_variation'];
			if($period != 'month'){
				$coupon_variation = $the_coupon->__get('coupon_custom_fields')['yearly_variation'];
			}
			WC()->cart->empty_cart();
			WC()->cart->add_to_cart($product_id, 1, $coupon_variation[0]);
		}
	}
}

?>
