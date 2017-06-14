<?php
/**
 * Sample class for email data
 */
class WooYM_Test_Emails{

  function __construct(){
    add_action( 'json_api_wooym_callback', array($this, 'json_api_yandex_money_get_data_mail'), $priority = 10, $accepted_args = 3 );
  }

  function json_api_yandex_money_get_data_mail($body, $data_request, $gw){

    if( ! apply_filters('wooym_debug_email_enable', false) ){
      return;
    }

    $to = get_option( 'ym_http_mail_addresses', get_option('admin_email') );

    $message = 'Поступил платеж';
    $message .= '<hr/>';

    $message .= sprintf('
        <ul>
          <li>amount: %s</li>
          <li>sender: %s</li>
          <li>type: %s</li>
          <li>label: %s</li>
        </ul>',

      $body['amount'],
      $body['sender'],
      $body['notification_type'],
      $body['label']
    );

    $message .= sprintf('<hr><pre>%s</pre>', print_r($body, true));

    $data = print_r($data_request, true);
    $message .= sprintf('<hr><pre>%s</pre>', $data);

    $subject = apply_filters( 'ym_http_subject_mail', 'Поступил платеж через ЯДеньги' );

    add_filter( 'wp_mail_content_type', array($this, 'set_html_content_type') );
    $result = wp_mail( $to, $subject, $message);
    remove_filter( 'wp_mail_content_type', array($this, 'set_html_content_type') );


  }

  function set_html_content_type(){
    return 'text/html';
  }

}
new WooYM_Test_Emails;
