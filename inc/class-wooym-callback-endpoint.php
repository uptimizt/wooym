<?php
/*
* Yandex Money Reciver via HTTP
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly



class WooYM_Callback_Endpoint {

  var $secret = '';

  function __construct(){
    add_action( 'rest_api_init', array($this, 'rest_api_init_callback') );

    add_action('json_api_wooym_callback', [$this, 'check_data'], 10, 3);

    add_action('json_api_wooym_callback', [$this, 'update_order'], 10, 3);

    add_filter('wooym_debug_email_enable', [$this, 'check_debug_email']);

  }

  /**
  * Проверяем опцию отладочных писем и если есть то отправляем
  */
  function check_debug_email($bool){
    $ym_gateway = $this->get_object_wooym();
    if( ! empty($ym_gateway->settings['debug_email'])){
      $bool = true;
    }

    return $bool;
  }

  function update_order($body, $data_request, $ym_gateway){

    if(empty($body['label'])){
      wp_mail(get_option('admin_email'), 'Ошибка обработки платежа от Яндекса', "Пришло уведомление от Яндекс Кошелька с пустым полем label");

      return false;
    }

    $order_id = (int)$body['label'];



    $order = wc_get_order($order_id);

    if(empty($order)){
      return false;
    }

    $check_result = $order->set_status('processing', 'Поступила оплата через Яндекс Деньги');
    $order->save();

    if(empty($check_result)){
      wp_mail(get_option('admin_email'), 'Ошибка обработки платежа от Яндекса', "Не удалос изменить статус заказа: " . $order_id);
    }

    return;

  }

  //Нужно сделать проверку уведомления по sha1 и секретному слову https://tech.yandex.ru/money/doc/dg/reference/notification-p2p-incoming-docpage/
  function check_data($body, $data_request, $ym_gateway) {

    $check = sprintf(
      "%s&%s&%s&%s&%s&%s&%s&%s&%s",
      $body['notification_type'],
      $body['operation_id'],
      $body['amount'],
      $body['currency'],
      $body['datetime'],
      $body['sender'],
      $body['codepro'],
      $ym_gateway->settings['wallet_secret'],
      $body['label']
    );

    $sha1_hash_check = sha1($check);

    if($sha1_hash_check == $body['sha1_hash']){
//
    } else {
//
    }
  }

  function rest_api_init_callback(){

    // Add deep-thoughts/v1/get-all-post-ids route
    register_rest_route( 'yandex-money/v1', '/notify/', array(
      'methods' => WP_REST_Server::CREATABLE,
      'callback' => array($this, 'save_data'),
    ));

  }

  function save_data($data_request){

    try {

      $body = print_r($data_request->get_body(), true);
      $body = $this->conver_body_in_array($body);

      $ym_gateway = $this->get_object_wooym();

      do_action( 'json_api_wooym_callback', $body, $data_request, $ym_gateway );

      $response = new WP_REST_Response( array('success', 'Data received successfully') );
      $response->set_status( 200 );

    } catch (WP_REST_Exception $e) {
        $response = new WP_REST_Response( array('fail', 'Data not received') );
        $response->set_status( 500 );
    }

    return $response;

  }

  function get_object_wooym(){
    $gateway_controller = WC_Payment_Gateways::instance();
    //далее попробовать получить ключ шлюза


    if(empty($gateway_controller->payment_gateways))
      return;

    if( ! is_array($gateway_controller->payment_gateways))
      return;

    foreach ($gateway_controller->payment_gateways as $key => $value) {
      if('yandex_wallet' == $value->id){
        return $value;
      }

    }
    return false;

  }

  //Converted string from Money to array
  function conver_body_in_array($body){

    if( strpos($body, 'notification_type') !== false ){
      // $message .= sprintf('<hr><pre>%s</pre>', $body);
      $data_array_source = explode('&', $body);

      $data_array = array();
      if(is_array($data_array_source)){
        foreach ($data_array_source as $value) {
          $value_array = explode('=', $value);
          $data_array[$value_array[0]] = $value_array[1];
        }
        $body = $data_array;
      }
    }

    return $body;
  }

}

new WooYM_Callback_Endpoint;
