<?php

/**
 * YandexMoney Getway
 */
function wooym_gateway_class(){

class WooYM_Getway extends WC_Payment_Gateway {

  /**
   * The Constructor
   */
  public function __construct(){
      $this->id = 'yandex_wallet';
      $this->method_title  = 'Яндекс.Кошелек';
      $this->has_fields = false;

      $this->init_form_fields();
      $this->init_settings();

	    $this->title              = $this->get_option( 'title' );
	    $this->description        = $this->get_option( 'description' );
      $this->liveurl = '';
      $this->wallet_number = $this->get_option( 'wallet_number' );

      $this -> msg['message'] = "";
      $this -> msg['class'] = "";

      add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

      add_action('woocommerce_receipt_yandex_wallet', array(&$this, 'display_form'));
   }

  /**
   * Определяем поля
   */
  function init_form_fields(){

       $this->form_fields = array(
         'enabled'         => array(
           'title'   => __( 'Enable/Disable', 'woocommerce' ),
           'type'    => 'checkbox',
           'label'   => __( 'Enable bank transfer', 'woocommerce' ),
           'default' => 'no',
         ),
         'title'           => array(
           'title'       => __( 'Title', 'woocommerce' ),
           'type'        => 'text',
           'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
           'default'     => __( 'Direct bank transfer', 'woocommerce' ),
           'desc_tip'    => true,
         ),
         'description'     => array(
           'title'       => __( 'Description', 'woocommerce' ),
           'type'        => 'textarea',
           'description' => __( 'Payment method description that the customer will see on your checkout.', 'woocommerce' ),
           'default'     => __( 'Make your payment directly into our bank account. Please use your Order ID as the payment reference. Your order will not be shipped until the funds have cleared in our account.', 'woocommerce' ),
           'desc_tip'    => true,
         ),
        'instructions'    => array(
          'title'       => __( 'Instructions', 'woocommerce' ),
          'type'        => 'textarea',
          'description' => __( 'Instructions that will be added to the thank you page and emails.', 'woocommerce' ),
          'default'     => '',
          'desc_tip'    => true,
        ),
        'wallet_number' => array(
            'title' => __('Номер кошелька','yandex_wallet'),
            'type' => 'number',
            'description' => __('Номер кошелька на который нужно перечислять платежи','yandex_wallet'),
            'default' => __('0','yandex_wallet')),
        'ym_api_callback_check' => array(
            'title' => __('Установлен обратный адрес в Яндекс Кошельке','yandex_wallet'),
            'type' => 'checkbox',
            'description' => __(sprintf('Поставьте тут галочку, после того как укажете адрес %s в <a href="%s" target="_blank">настройках кошелька</a> на стороне Яндекса', get_rest_url( 0, '/yandex-money/v1/notify/' ), 'https://money.yandex.ru/myservices/online.xml') ,'yandex_wallet'),
            'default' => __('0','yandex_wallet')),
        'wallet_secret' => array(
            'title' => __('Секрет кошелька','yandex_wallet'),
            'type' => 'password',
            'description' => __(sprintf('Секретный ключ из <a href="%s" target="_blank">настроек кошелька</a> для синхронизации', 'https://money.yandex.ru/myservices/online.xml'),'yandex_wallet'),
            'default' => __('0','yandex_wallet')),
        'debug_email' => array(
            'title' => __('Отладочные письма','yandex_wallet'),
            'type' => 'checkbox',
            'label' => __('Включить отладочные письма','yandex_wallet'),
            'description' => __('Отправлять служебные письма с отладочной информацией о платежах на адрес админа сайта о всех поступающих платежах' ,'yandex_wallet'),
            'default' => __('0','yandex_wallet')),

        );
    }

    public function admin_options(){
        echo '<h3>'.__('Оплата через Яндекс.Кассу','yandex_wallet').'</h3>';
        echo '<table class="form-table">';
        $this->generate_settings_html();
        echo '</table>';

    }

    /**
     *  There are no payment fields for payu, but we want to show the description if set.
     **/
    function payment_fields(){
        if($this -> description) echo wpautop(wptexturize($this -> description));
    }


    /**
     * Generate payu button link
     **/
    public function display_form($order_id)
    {
      $order = wc_get_order($order_id);

      $payment_gateway = wc_get_payment_gateway_by_order( $order );

      if( ! empty($payment_gateway->settings['instructions']) ){
        $instructions = $payment_gateway->settings['instructions'];
        echo '<h1>Инструкции</h1>';
        echo $instructions;
      }

      $btn_classes = apply_filters('wooym_btn_classes', '');

      ?>
      <h1>Выберите способ оплаты</h1>
      <form name=ShopForm method="POST" id="submit_Yandex_Wallet_payment_form" action="https://money.yandex.ru/quickpay/confirm.xml">
  			<input type="hidden" name="receiver" value="<?php echo $this->wallet_number ?>">
  			<input type="hidden" name="formcomment" value="<?php echo get_bloginfo('name') . ': ' . $order_id; ?>">
  			<input type="hidden" name="short-dest" value="<?php echo get_bloginfo('name').': '.$order_id; ?>">
  			<input type="hidden" name="label" value="<?php echo $order_id; ?>">
  			<input type="hidden" name="quickpay-form" value="shop">
  			<input type="hidden" name="targets" value="Заказ {<?php echo $order_id ?>}">
  			<input type="hidden" name="sum" value="<?php echo number_format( $order->get_total(), 2, '.', '' )?>" data-type="number" >
  			<input type="hidden" name="comment" value="<?php echo $order->get_customer_note() ?>" >
  			<input type="hidden" name="need-fio" value="false">
  			<input type="hidden" name="need-email" value="false" >
  			<input type="hidden" name="successURL" value="<?php echo $order->get_checkout_order_received_url() ?>" >
  			<input type="hidden" name="need-phone" value="false">
  			<input type="hidden" name="need-address" value="false">
        <input id="AC" type="radio" name="paymentType" value="AC"> <label for="AC">Оплата банковской картой</label><br/>
  			<input id="PC" type="radio" name="paymentType" value="PC"> <label for="PC">Оплата через кошелек Яндекс.Деньги.</label><br/>
  			<input type="submit" name="submit-button" <?= $btn_classes ?> value="Оплатить">
  		</form>
      <?php
    }

    /**
     * Process the payment and return the result
     **/
   function process_payment($order_id)
   {
      $order = wc_get_order( $order_id );

      // $order->update_status( 'on-hold', "Ожидание оплаты от Яндекс Кошелька" );

      return array(
        'result' => 'success',
        'redirect' => $order->get_checkout_payment_url( true )
      );
   }


    function showMessage($content)
    {
      return '<div class="box '.$this -> msg['class'].'-box">'.$this -> msg['message'].'</div>'.$content;
    }
}

}

add_action( 'plugins_loaded', 'wooym_gateway_class' );
