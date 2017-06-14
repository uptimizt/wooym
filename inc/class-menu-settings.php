<?php

class WooYM_Settings {
  function __construct(){

    add_action('admin_menu', function () {
        add_options_page(
          $page_title = 'Яндекс Деньги',
          $menu_title = "Яндекс Деньги",
          $capability = 'manage_options',
          $menu_slug = 'http-ym-options',
          $function = array($this, 'options_page_callback')
        );
    });

    add_action( 'admin_init', array($this, 'init_ym_http_section_main'), $priority = 10, $accepted_args = 1 );
    add_action( 'admin_init', array($this, 'init_ym_http_section_mail'), $priority = 10, $accepted_args = 1 );
  }


  function options_page_callback(){
    ?>

    <form method="POST" action="options.php">
      <h1>Настройки интеграции Яндекс Деньги</h1>
      <?php
        do_settings_sections( 'http-ym-options' );
        settings_fields( 'http-ym-options' );
        submit_button();
      ?>
    </form>
    <?php
  }


  function init_ym_http_section_mail(){

    add_settings_section(
      'ym_http_section_mail',
      'Настройка почты',
      null,
      $menu_slug = 'http-ym-options'
    );

    add_settings_field(
      $id = 'ym_http_mail_addresses',
      $title = 'Почтовые адреса',
      $callback = [$this, 'ym_http_mail_addresses_display'],
      $page = 'http-ym-options',
      $section = 'ym_http_section_mail'
    );
    register_setting('http-ym-options', 'ym_http_mail_addresses');

    add_settings_field(
      $id = 'ym_http_mail_enable',
      $title = 'Включить отправку на почту',
      $callback = [$this, 'ym_http_mail_enable_display'],
      $page = 'http-ym-options',
      $section = 'ym_http_section_mail'
    );
    register_setting('http-ym-options', 'ym_http_mail_enable');



  }


  function ym_http_mail_enable_display(){
    $f = 'ym_http_mail_enable';
    printf('<input type="checkbox" name="%s" value="1" %s />', $f, checked( 1, get_option($f), false ));
  }

  function ym_http_mail_addresses_display(){
    $f = 'ym_http_mail_addresses';
    printf('<input type="text" name="%s" value="%s"/>', $f, get_option($f));
  }


  function init_ym_http_section_main(){

    add_settings_section(
    	'ym_http_section_main',
    	'Секретный ключ',
    	null,
    	'http-ym-options'
    );

    add_settings_field(
      $id = 'ym_http_key',
      $title = 'Ключ идентификации',
      $callback = array($this, 'ym_http_key_display'),
      $page = 'http-ym-options',
      $section = 'ym_http_section_main'
    );
    register_setting('http-ym-options', 'ym_http_key');

  }


  function ym_http_key_display(){
    $name = 'ym_http_key';
    printf('<input type="password" name="%s" value="%s"/>',$name, get_option('ym_http_key'));
  }




}
new WooYM_Settings;
