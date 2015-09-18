<?php
/*
  $Id: authorizenet_cc.php v1.0 2013-04-20 datazen $

  Loaded Commerce, Innovative eCommerce Solutions
  http://www.loadedcommerce.com

  Copyright (c) 2013 Loaded Commerce, LLC

  @author     Loaded Commerce Team
  @copyright  (c) 2013 LoadedCommerce Team
  @license    http://loadedcommerce.com/license.html
*/
class lC_Payment_authorizenet_cc extends lC_Payment {
                                   
  protected $_title,
            $_code = 'authorizenet_cc',
            $_status = false,
            $_sort_order,
            $_transaction_response;

  public function lC_Payment_authorizenet_cc() {
    global $lC_Database, $lC_Language, $lC_ShoppingCart;

    $this->_title = $lC_Language->get('payment_authnet_title');
    $this->_method_title = $lC_Language->get('payment_authnet_method_title');
    $this->_status = (ADDONS_PAYMENT_AUTHNET_SIM_PAYMENTS_STATUS == '1') ? true : false;
    $this->_sort_order = ADDONS_PAYMENT_AUTHNET_SIM_PAYMENTS_SORT_ORDER;

    switch (ADDONS_PAYMENT_AUTHNET_SIM_PAYMENTS_TRANSACTION_SERVER) {
      case 'Production':
      case 'production':
        $this->iframe_relay_url = 'https://secure.authorize.net/gateway/transact.dll';
        break;

      case 'Certify':
      case 'certification':
        $this->iframe_relay_url = 'https://certification.authorize.net/gateway/transact.dll';
        break;

      default:
        $this->iframe_relay_url = 'https://test.authorize.net/gateway/transact.dll';
    }

    $Qcredit_cards = $lC_Database->query('select credit_card_name from :table_credit_cards where credit_card_status = :credit_card_status');
    $Qcredit_cards->bindRaw(':table_credit_cards', TABLE_CREDIT_CARDS);
    $Qcredit_cards->bindInt(':credit_card_status', '1');
    $Qcredit_cards->setCache('credit-cards');
    $Qcredit_cards->execute();

    while ($Qcredit_cards->next()) {
      $this->_card_images .= lc_image('images/cards/cc_' . strtolower(str_replace(" ", "_", $Qcredit_cards->value('credit_card_name'))) . '.png', null, null, null, 'style="vertical-align:middle; margin:0 2px;"');
    }

    $Qcredit_cards->freeResult();

    $this->form_action_url = lc_href_link(FILENAME_CHECKOUT, 'payment_template', 'SSL', true, true, true) ;  

    if ($this->_status === true) {
      if ((int)ADDONS_PAYMENT_AUTHNET_SIM_PAYMENTS_ORDER_STATUS_ID > 0) {
        $this->order_status = ADDONS_PAYMENT_AUTHNET_SIM_PAYMENTS_ORDER_STATUS_ID;
      }

      if ((int)ADDONS_PAYMENT_AUTHNET_SIM_PAYMENTS_ZONE > 0) {
        $check_flag = false;

        $Qcheck = $lC_Database->query('select zone_id from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id and zone_country_id = :zone_country_id order by zone_id');
        $Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
        $Qcheck->bindInt(':geo_zone_id', ADDONS_PAYMENT_AUTHNET_SIM_PAYMENTS_ZONE);
        $Qcheck->bindInt(':zone_country_id', $lC_ShoppingCart->getBillingAddress('country_id'));
        $Qcheck->execute();

        while ($Qcheck->next()) {
          if ($Qcheck->valueInt('zone_id') < 1) {
            $check_flag = true;
            break;
          } elseif ($Qcheck->valueInt('zone_id') == $lC_ShoppingCart->getBillingAddress('zone_id')) {
            $check_flag = true;
            break;
          }
        }

        if ($check_flag === false) {
          $this->_status = false;
        }
      }
    }
  }

  public function getJavascriptBlock() {
    return false;
  }

  public function selection() {
    global $lC_Language;
    $selection = array('id' => $this->_code,
                       'module' => '<div class="payment-selection">' . $this->_method_title . '<span>' . $this->_card_images . '</span></div><div class="payment-selection-title">' . $lC_Language->get('payment_authnet_method_blurb') . '</div>');

    return $selection;
  }

  public function pre_confirmation_check() {
    return false;
  }

  public function confirmation() {
    return false;
  }

  public function process_button() {
    global $lC_Database, $lC_Session, $lC_MessageStack, $lC_Customer, $lC_Language, $lC_Currencies, $lC_ShoppingCart, $lC_CreditCard;
    
    $order_id = lC_Order::insert();
    
    $type = (defined('ADDONS_PAYMENT_AUTHNET_SIM_PAYMENTS_TRANSACTION_TYPE') && ADDONS_PAYMENT_AUTHNET_SIM_PAYMENTS_TRANSACTION_TYPE == 'Auth Only') ? 'AUTH_ONLY' : 'AUTH_CAPTURE';

    $params = array('x_login' => ADDONS_PAYMENT_AUTHNET_SIM_PAYMENTS_LOGIN_ID,
                    'x_version' => '3.1',
                    'x_show_form' => 'PAYMENT_FORM',
                    'x_delim_data' => 'FALSE',
                    'x_relay_response' => 'TRUE',
                    'x_relay_url' => lc_href_link(FILENAME_IREDIRECT, '', 'SSL', true, true, true),
                    'x_first_name' => $lC_ShoppingCart->getBillingAddress('firstname'),
                    'x_last_name' => $lC_ShoppingCart->getBillingAddress('lastname'),
                    'x_company' => $lC_ShoppingCart->getBillingAddress('company'),
                    'x_address' => $lC_ShoppingCart->getBillingAddress('street_address'),
                    'x_city' => $lC_ShoppingCart->getBillingAddress('city'),
                    'x_state' => $lC_ShoppingCart->getBillingAddress('state'),
                    'x_zip' => $lC_ShoppingCart->getBillingAddress('postcode'),
                    'x_country' => $lC_ShoppingCart->getBillingAddress('country_iso_code_2'),
                    'x_phone' => $lC_ShoppingCart->getBillingAddress('telephone_number'),
                    'x_cust_id' => $lC_Customer->getID(),
                    'x_customer_ip' => lc_get_ip_address(),
                    'x_email' => $lC_Customer->getEmailAddress(),
                    'x_description' => substr(STORE_NAME, 0, 255),
                    'x_amount' => $lC_Currencies->formatRaw($lC_ShoppingCart->getTotal(), $lC_Currencies->getCode()),
                    'x_currency_code' => $lC_Currencies->getCode(),
                    'x_method' => 'CC',
                    'x_type' => $type,
                    'x_freight' => $lC_Currencies->formatRaw($lC_ShoppingCart->getShippingMethod('cost'), $lC_Currencies->getCode()),
                    'x_cancel_url' => lc_href_link(FILENAME_CHECKOUT, '', 'NONSSL', true, true, true),
                    'x_ship_to_first_name' => $lC_ShoppingCart->getShippingAddress('firstname'),
                    'x_ship_to_last_name' => $lC_ShoppingCart->getShippingAddress('lastname'),
                    'x_ship_to_company'  => $lC_ShoppingCart->getShippingAddress('company'),   
                    'x_ship_to_address'  => $lC_ShoppingCart->getShippingAddress('street_address'),
                    'x_ship_to_city'  => $lC_ShoppingCart->getShippingAddress('city'),      
                    'x_ship_to_state' => $lC_ShoppingCart->getShippingAddress('state'),
                    'x_ship_to_zip' => $lC_ShoppingCart->getShippingAddress('postcode'),        
                    'x_ship_to_country' => $lC_ShoppingCart->getShippingAddress('country_iso_code_2'));

    $process_button_string = $this->_InsertFP(ADDONS_PAYMENT_AUTHNET_SIM_PAYMENTS_LOGIN_ID, ADDONS_PAYMENT_AUTHNET_SIM_PAYMENTS_TRANSACTION_KEY, $lC_Currencies->formatRaw($lC_ShoppingCart->getTotal()),rand(1, 1000), $lC_Currencies->getCode());
   
    $i = 0;
    foreach ($lC_ShoppingCart->getProducts() as $product) {
      $process_button_string .= lc_draw_hidden_field('x_line_item', ($i+1) . '<|>' . substr($product['name'], 0, 31) . '<|>' . substr($product['name'], 0, 255) . '<|>' . $product['quantity'] . '<|>' . $product['price'] . '<|>' . ($product['tax_class_id'] > 0 ? 'YES' : 'NO')) . "\n";
      $i++;
    }

    if (ADDONS_PAYMENT_AUTHNET_SIM_PAYMENTS_TRANSACTION_TEST_MODE == '1') {
      $process_button_string .= lc_draw_hidden_field('x_test_request', 'TRUE');
    }
      foreach ( $params as $key => $value ) {
        $process_button_string .= lc_draw_hidden_field($key, $value) . "\n";
      }

    return $process_button_string;
  }

  public function process() {
    global $lC_Database, $lC_MessageStack;

    $error = false;
    $code = (isset($_POST['x_response_code']) && $_POST['x_response_code'] != '') ? preg_replace('/[^0-9]/', '', $_POST['x_response_code']) : NULL;
    $msg = (isset($_POST['error']) && $_POST['error'] != NULL) ? preg_replace('/[^a-zA-Z0-9]\:\|\[\]/', '', $_POST['error']) : NULL;
    $order_id = (isset($_POST['x_invoice_num']) && $_POST['x_invoice_num'] != NULL) ? preg_replace('/[^0-9]\:\|\[\]/', '', $_POST['x_invoice_num']) : 0;

    if ($code == '1') { // success    
      lC_Order::process($order_id, $this->order_status);
    } else {
      $error = true;
      $lC_MessageStack->add('checkout_payment', $code . ' - ' . $msg);
      lC_Order::remove($order_id);
    } 
    
    // insert into transaction history
    $this->_transaction_response = $code;

    $response_array = array('root' => $_POST);
    $response_array['root']['transaction_response'] = trim($this->_transaction_response);
    $lC_XML = new lC_XML($response_array);
    
    $Qtransaction = $lC_Database->query('insert into :table_orders_transactions_history (orders_id, transaction_code, transaction_return_value, transaction_return_status, date_added) values (:orders_id, :transaction_code, :transaction_return_value, :transaction_return_status, now())');
    $Qtransaction->bindTable(':table_orders_transactions_history', TABLE_ORDERS_TRANSACTIONS_HISTORY);
    $Qtransaction->bindInt(':orders_id', $order_id);
    $Qtransaction->bindInt(':transaction_code', 1);
    $Qtransaction->bindValue(':transaction_return_value', $lC_XML->toXML());
    $Qtransaction->bindInt(':transaction_return_status', (strtoupper(trim($this->_transaction_response)) == '1') ? 1 : 0);
    $Qtransaction->execute();   
    
    if ($error) lc_redirect(lc_href_link(FILENAME_CHECKOUT, 'payment', 'SSL'));
  }

  private function _InsertFP($loginid, $x_tran_key, $amount, $sequence, $currency = '') {
    $tstamp = time();

    $fingerprint = hash_hmac("md5", $loginid . '^' . $sequence . '^' . $tstamp . '^' . $amount . '^' . $currency, $x_tran_key); 

    return lc_draw_hidden_field('x_fp_sequence', $sequence) .
    lc_draw_hidden_field('x_fp_timestamp', $tstamp) .
    lc_draw_hidden_field('x_fp_hash', $fingerprint);
  }
}
?>