<?php

/**
 * YandexMoney Getway
 */
function wooym_gateway_class(){

class WooYM_Getway extends WC_Payment_Gateway {

  public function __construct(){
      $this -> id = 'yandex_wallet';
      $this -> method_title  = 'Яндекс.Кошелек';
      $this -> has_fields = false;

      $this -> init_form_fields();
      $this -> init_settings();

	    $this->title              = $this->get_option( 'title' );
	    $this->description        = $this->get_option( 'description' );
      $this -> liveurl = '';
      $this->wallet_number = $this->get_option( 'wallet_number' );

      $this -> msg['message'] = "";
      $this -> msg['class'] = "";

      if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) ) {
                add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( &$this, 'process_admin_options' ) );
             } else {
                add_action( 'woocommerce_update_options_payment_gateways', array( &$this, 'process_admin_options' ) );
            }
      add_action('woocommerce_receipt_yandex_wallet', array(&$this, 'receipt_page'));
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
     * Receipt Page
     **/
    function receipt_page($order){
        echo $this -> generate_payu_form($order);
    }
    /**
     * Generate payu button link
     **/
    public function generate_payu_form($order_id){

        $order = new WC_Order($order_id);
    		$sendurl='https://money.yandex.ru/quickpay/confirm.xml';

        $result ='';
    		$result .= '<form name=ShopForm method="POST" id="submit_Yandex_Wallet_payment_form" action="'.$sendurl.'">';
    			$result .= '<input type="hidden" name="receiver" value="'.$this->wallet_number.'">';
    			$result .= '<input type="hidden" name="formcomment" value="'.get_bloginfo('name').': '.$order_id.'">';
    			$result .= '<input type="hidden" name="short-dest" value="'.get_bloginfo('name').': '.$order_id.'">';
    			$result .= '<input type="hidden" name="label" value="'.$order_id.'">';
    			$result .= '<input type="hidden" name="quickpay-form" value="shop">';
    			$result .= '<input type="hidden" name="targets" value="Заказ {'.$order_id.'}">';
    			$result .= '<input type="hidden" name="sum" value="'.number_format( $order->order_total, 2, '.', '' ).'" data-type="number" >';
    			$result .= '<input type="hidden" name="comment" value="'.$order->customer_note.'" >';
    			$result .= '<input type="hidden" name="need-fio" value="false">';
    			$result .= '<input type="hidden" name="need-email" value="false" >';
    			$result .= '<input type="hidden" name="successURL" value="' . $order->get_checkout_order_received_url() . '" >';
    			$result .= '<input type="hidden" name="need-phone" value="false">';
    			$result .= '<input type="hidden" name="need-address" value="false">';
          $result .= '<input id="AC" type="radio" name="paymentType" value="AC"> <label for="AC">Оплата банковской картой</label><br/>';
    			$result .= '<input id="PC" type="radio" name="paymentType" value="PC"> <label for="PC">Оплата через кошелек Яндекс.Деньги.</label><br/>';
    			$result .= '<input type="submit" name="submit-button" value="Оплатить">';
    		$result .='</form>';

    		return $result;

    }
    /**
     * Process the payment and return the result
     **/
   function process_payment($order_id){
          $order = new WC_Order($order_id);
		      return array('result' => 'success', 'redirect' => $order->get_checkout_payment_url( true ));
    }


    function showMessage($content){
            return '<div class="box '.$this -> msg['class'].'-box">'.$this -> msg['message'].'</div>'.$content;
    }

     // get all pages
    function get_pages($title = false, $indent = true) {
        $wp_pages = get_pages('sort_column=menu_order');
        $page_list = array();
        if ($title) $page_list[] = $title;
        foreach ($wp_pages as $page) {
            $prefix = '';
            // show indented child pages?
            if ($indent) {
                $has_parent = $page->post_parent;
                while($has_parent) {
                    $prefix .=  ' - ';
                    $next_page = get_page($has_parent);
                    $has_parent = $next_page->post_parent;
                }
            }
            // add to page list array array
            $page_list[$page->ID] = $prefix . $page->post_title;
        }
        return $page_list;
    }
}

}

add_action( 'plugins_loaded', 'wooym_gateway_class' );
