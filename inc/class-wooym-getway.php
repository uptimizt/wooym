<?php

/**
 * YandexMoney Getway
 */
function wooym_gateway_class(){

class WooYM_Getway extends WC_Payment_Gateway {

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


    function init_form_fields(){

       $this->form_fields = array(
            'enabled' => array(
                'title' => __('Включить/Выключить','yandex_wallet'),
                'type' => 'checkbox',
                'label' => __('Включить модуль оплаты через Яндекс.Кассу','yandex_wallet'),
                'default' => 'no'),
            'title' => array(
                'title' => __('Заголовок','yandex_wallet'),
                'type'=> 'text',
                'description' => __('Название, которое пользователь видит во время оплаты','yandex_wallet'),
                'default' => __('Яндекс.Кошелек','yandex_wallet')),
            'description' => array(
                'title' => __('Описание','yandex_wallet'),
                'type' => 'textarea',
                'description' => __('Описание, которое пользователь видит во время оплаты','yandex_wallet'),
                'default' => __('Оплата через Яндекс.Кошелек','yandex_wallet')),
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
        $this -> generate_settings_html();
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
      ?>
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
  			<input type="submit" name="submit-button" value="Оплатить">
  		</form>
      <?php
    }

    /**
     * Process the payment and return the result
     **/
   function process_payment($order_id)
   {
      $order = wc_get_order( $order_id );

      return array('result' => 'success', 'redirect' => $order->get_checkout_payment_url( true ));
   }


    function showMessage($content)
    {
      return '<div class="box '.$this -> msg['class'].'-box">'.$this -> msg['message'].'</div>'.$content;
    }
}

}

add_action( 'plugins_loaded', 'wooym_gateway_class' );
