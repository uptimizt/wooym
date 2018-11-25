<?php
/*
Plugin Name: WooYM - WooCommerce и ЯДеньги
Plugin URI: https://wpcraft.ru/product/wooym/
Description: Интеграция WooCommerce и кошелька Яндекс Деньги (wooym)
Author: WPCraft
Author URI: http://wpcraft.ru/
Version: 1.0
*/

require_once 'inc/class-wooym-getway.php';
require_once 'inc/class-wooym-callback-endpoint.php';

/**
* Add class to the autoload WooCommerce
*
* @param array $methods - methods
*
*/
function wooym_woocommerce_add_gateway($methods) {
        $methods[] = 'WooYM_Getway';
        return $methods;
}
add_filter('woocommerce_payment_gateways', 'wooym_woocommerce_add_gateway' );
