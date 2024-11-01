<?php 
/*
	Plugin Name: Woocoomerce Paypal INR Support
	Description: This plugin allows you to make use of Paypal as payment gateway while selling products in Indian Rupee (INR) currency. 
	Version: 0.9
*/


define("WOOINR_FILE" , __FILE__);

require "admin.php";

add_filter( 'woocommerce_currencies', 'wooinr_currency' );

function wooinr_currency( $currencies ) {
    $currencies['INR'] = __( 'Indian Rupees', 'woocommerce' );
    return $currencies;
}

add_filter('woocommerce_currency_symbol', 'wooinr_currency_symbol', 10, 2);

function wooinr_currency_symbol( $currency_symbol, $currency ) {
    switch( $currency ) {
    case 'INR': $currency_symbol = 'Rs'; break;
	}	
	return $currency_symbol;
}


add_filter("woocommerce_paypal_supported_currencies" , "wooinr_add_inr_to_paypal_cur");

function wooinr_add_inr_to_paypal_cur($cur) {
	$cur[] = "INR";
	return $cur;
}

function wooinr_convert_inr_to_usd($paypal_args){

    if ( $paypal_args['currency_code'] == 'INR'){
        $convert_rate = wooinr_get_exchange_rate(); 
        if($convert_rate) {
	        $count = 1;
	        $paypal_args['currency_code'] = "USD";
	        while( isset($paypal_args['amount_' . $count]) ){
	            $paypal_args['amount_' . $count] = round( $paypal_args['amount_' . $count] / $convert_rate, 2);
	            $count++;
	        }
        }
    }
    return $paypal_args;
}
add_filter('woocommerce_paypal_args', 'wooinr_convert_inr_to_usd');


function wooinr_get_exchange_rate() {
    
    if($inrusd_rate = get_transient('wooinr_inr_rate')) {
    	return $inrusd_rate;
    }

    $file = 'latest.json';
	$appId = get_option( 'wooinr_ex_api' );
	
	if(!$appId) return false;

	$url = "http://openexchangerates.org/api/$file?app_id=".$appId;

	$json = file_get_contents($url);

	$exchangeRates = json_decode($json);


	if(is_object($exchangeRates) && isset($exchangeRates->rates) && isset($exchangeRates->rates->INR)) {
		set_transient( 'wooinr_inr_rate', $exchangeRates->rates->INR, 6*HOUR_IN_SECONDS );

		return $exchangeRates->rates->INR;
	}
	else {
		return false;
	}

}



add_filter( 'plugin_action_links', 'wooinr_add_custom_setting_link', 10, 5 );
function wooinr_add_custom_setting_link( $actions, $plugin_file ) 
{
	static $plugin;

	if (!isset($plugin))
		$plugin = plugin_basename(__FILE__);
	
	if ($plugin == $plugin_file) {

			$settings = array('settings' => '<a href="edit.php?post_type=product&page=wooinr">' . __('Settings', 'General') . '</a>');
		
    		$actions = array_merge($settings, $actions);
			
		}
		
		return $actions;
}


function wooinr_update_nag() {
	if ( class_exists( 'WooCommerce' ) ) {
		$wooinr_ex_api = get_option("wooinr_ex_api");

		if( empty($wooinr_ex_api) ) {
			$url = site_url()."/wp-admin/edit.php?post_type=product&page=wooinr";

		    ?>
		    <div class="error notice">
		        <p><?php echo "<b>Woo INR:</b> Enter Openexchangerates API to enable Woocommerce INR. <a href='$url'>Click here.</a>"; ?></p>
		    </div>
		    <?php
		}
	}
}
add_action( 'admin_notices', 'wooinr_update_nag' );
