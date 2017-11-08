<?php
/*
Plugin Name: WooYM - WooCommerce и ЯДеньги
Version: 0.8
Plugin URI: https://wpcraft.ru/product/wooym/
Description: Интеграция WooCommerce и кошелька Яндекс Деньги (wooym)
Author: WPCraft
Author URI: http://wpcraft.ru/
*/

require_once 'inc/class-wooym-getway.php';
require_once 'inc/class-wooym-callback-endpoint.php';

// require_once 'inc/class-email-sample.php';



function wooym_woocommerce_add_gateway($methods) {
        $methods[] = 'WooYM_Getway';
        return $methods;
}

add_filter('woocommerce_payment_gateways', 'wooym_woocommerce_add_gateway' );
