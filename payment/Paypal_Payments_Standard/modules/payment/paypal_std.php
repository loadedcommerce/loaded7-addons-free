<?php
<<<<<<< HEAD
/**  
  $Id: paypal_std.php v1.0 2013-01-01 datazen $
=======
  /**  
  $Id: cod.php v1.0 2013-01-01 datazen $
>>>>>>> e7a728f957e9d1758aa666b28512f4f60647c69c

  Loaded Commerce, Innovative eCommerce Solutions
  http://www.loadedcommerce.com

  Copyright (c) 2013 Loaded Commerce, LLC

  @author     Loaded Commerce Team
  @copyright  (c) 2013 Loaded Commerce Team
  @license    http://loadedcommerce.com/license.html
  */
  class lC_Payment_paypal_std extends lC_Payment {
    /**
    * The public title of the payment module
    *
    * @var string
    * @access protected
    */  
    protected $_title;
    /**
    * The code of the payment module
    *
    * @var string
    * @access protected
    */  
    protected $_code = 'paypal_std';
    /**
    * The status of the module
    *
    * @var boolean
    * @access protected
    */  
    protected $_status = false;
    /**
    * The sort order of the module
    *
    * @var integer
    * @access protected
    */  
    protected $_sort_order;   
    /**
    * Constructor
    */ 
    public function lC_Payment_paypal_std() {
      global $lC_Database, $lC_Language, $lC_ShoppingCart;

      $this->_title = $lC_Language->get('payment_paypal_std_title');
      $this->_method_title = $lC_Language->get('payment_paypal_std_method_title');
      $this->_status = (defined('ADDONS_PAYMENT_PAYPAL_PAYMENTS_STANDARD_STATUS') && (ADDONS_PAYMENT_PAYPAL_PAYMENTS_STANDARD_STATUS == '1') ? true : false);
      $this->_sort_order = (defined('ADDONS_PAYMENT_PAYPAL_PAYMENTS_STANDARD_SORT_ORDER') ? ADDONS_PAYMENT_PAYPAL_PAYMENTS_STANDARD_SORT_ORDER : null);    

      if (defined('ADDONS_PAYMENT_PAYPAL_PAYMENTS_STANDARD_STATUS')) {
        $this->initialize();
      }
    }

    public function initialize() {
      global $lC_Database, $lC_Language, $order;

      if ((int)ADDONS_PAYMENT_PAYPAL_PAYMENTS_STANDARD_ORDER_STATUS_ID > 0) {
        $this->order_status = ADDONS_PAYMENT_PAYPAL_PAYMENTS_STANDARD_ORDER_STATUS_ID;
      } else {
        $this->order_status = 0;
      } 

      if (is_object($order)) $this->update_status();    
      if (defined('ADDONS_PAYMENT_PAYPAL_PAYMENTS_STANDARD_TEST_MODE') && ADDONS_PAYMENT_PAYPAL_PAYMENTS_STANDARD_TEST_MODE == '1') {
        $this->form_action_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';  // sandbox url
      } else {
        $this->form_action_url = 'https://www.paypal.com/cgi-bin/webscr';  // production url
      }    

    }
    /**
    * Disable module if zone selected does not match billing zone  
    *
    * @access public
    * @return void
    */  
    public function update_status() {
      global $lC_Database, $order;

      if ( ($this->_status === true) && ((int)ADDONS_PAYMENT_PAYPAL_PAYMENTS_STANDARD_ZONE > 0) ) {
        $check_flag = false;

        $Qcheck = $lC_Database->query('select zone_id from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id and zone_country_id = :zone_country_id order by zone_id');
        $Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
        $Qcheck->bindInt(':geo_zone_id', ADDONS_PAYMENT_PAYPAL_PAYMENTS_STANDARD_ZONE);
        $Qcheck->bindInt(':zone_country_id', $order->billing['country']['id']);
        $Qcheck->execute();

        while ($Qcheck->next()) {
          if ($Qcheck->valueInt('zone_id') < 1) {
            $check_flag = true;
            break;
          } elseif ($Qcheck->valueInt('zone_id') == $order->billing['zone_id']) {
            $check_flag = true;
            break;
          }
        }

        if ($check_flag == false) {
          $this->_status = false;
        }
      }
    } 

    /**
    * Return the payment selections array
    *
    * @access public
    * @return array
    */ 
    public function selection() {
      global $lC_Language;

      $selection = array('id' => $this->_code,
        'module' => '<div class="payment-selection">' . $lC_Language->get('payment_paypal_std_method_title') . '<span style="margin-left:6px;">' . lc_image('addons/Paypal_Payments_Standard/images/paypal-cards.png', null, null, null, 'style="vertical-align:middle;"') . '</span></div><div class="payment-selection-title">' . $lC_Language->get('payment_paypal_std_method_blurb') . '</div>');    

      return $selection;
    }


<<<<<<< HEAD
   /**
  * Return the confirmation button logic
  *
  * @access public
  * @return string
  */ 
  private function _paypal_standard_params() {
    global $lC_Language, $lC_ShoppingCart, $lC_Currencies, $lC_Customer, $lC_Tax;  

    $upload         = 0;
    $no_shipping    = '1';
    $redirect_cmd   = '';
    $handling_cart  = '';
    $item_name      = '';
    $shipping       = '';

    // get the shipping amount
    $taxTotal       = 0;
    $shippingTotal  = 0;
  $discount_amount_cart = 0; 
    foreach ($lC_ShoppingCart->getOrderTotals() as $ot) {
      if ($ot['code'] == 'shipping') $shippingTotal = (float)$ot['value'];
      if ($ot['code'] == 'tax') $taxTotal = (float)$ot['value'];
    //if ($ot['code'] == 'coupon') $discount_amount_cart = (float)$ot['value'];
    if ($ot['code'] == 'coupon') $discount_amount_cart = lc_round($ot['value'],DECIMAL_PLACES);
    } 

    $shoppingcart_products = $lC_ShoppingCart->getProducts();
    $amount = $lC_Currencies->formatRaw($lC_ShoppingCart->getSubTotal(), $lC_Currencies->getCode());
  $shippingtax = $lC_ShoppingCart->getShippingCost();

  if(ADDONS_PAYMENT_PAYPAL_PAYMENTS_STANDARD_METHOD == 'Itemized') { 
      //$discount_amount_cart = 0;     

      $paypal_action_params = array(
        'upload' => sizeof($shoppingcart_products),
        'redirect_cmd' => '_cart',
        'handling_cart' => $shippingTotal
        //'discount_amount' => $discount_amount_cart
        );
     
=======
    /**
    * Perform any pre-confirmation logic
    *
    * @access public
    * @return boolean
    */ 
    public function pre_confirmation_check() {
      return false;
    }
    /**
    * Perform any post-confirmation logic
    *
    * @access public
    * @return integer
    */ 
    public function confirmation() {
      return false;
    }

    /**
    * Return the confirmation button logic
    *
    * @access public
    * @return string
    */ 
    public function process_button() {

      if(isset($_SESSION['cartSync']))  {
        lC_Order::remove($_SESSION['cartSync']['orderID']);
        unset($_SESSION['cartSync']['paymentMethod']);
        unset($_SESSION['cartSync']['prepOrderID']);
        unset($_SESSION['cartSync']['orderCreated']);
        unset($_SESSION['cartSync']['orderID']);
      }

      $order_id = lC_Order::insert($this->order_status);    
      $_SESSION['cartSync']['paymentMethod'] = $this->_code;
      // store the cartID info to match up on the return - to prevent multiple order IDs being created
      $_SESSION['cartSync']['cartID'] = $_SESSION['cartID'];
      $_SESSION['cartSync']['prepOrderID'] = $_SESSION['prepOrderID'];     
      $_SESSION['cartSync']['orderCreated'] = TRUE;
      $_SESSION['cartSync']['orderID'] = $order_id;

      echo $this->_paypal_standard_params();        
>>>>>>> e7a728f957e9d1758aa666b28512f4f60647c69c

      return false;
    }

<<<<<<< HEAD
      $i = 1;
    //BOF shipping tax classes
=======
    /**
    * Return the confirmation button logic
    *
    * @access public
    * @return string
    */ 
    private function _paypal_standard_params() {
      global $lC_Language, $lC_ShoppingCart, $lC_Currencies, $lC_Customer, $lC_Tax;  

      $upload         = 0;
      $no_shipping    = (isset($_SESSION['this_payment']) && $_SESSION['this_payment'] > 0) ? '0' : '1';
      $redirect_cmd   = '';
      $handling_cart  = '';
      $item_name      = '';
      $shipping       = '';

      // get the shipping amount
      $taxTotal       = 0;
      $shippingTotal  = 0;
      $discount_amount_cart = 0; 
      foreach ($lC_ShoppingCart->getOrderTotals() as $ot) {
        if ($ot['code'] == 'shipping') $shippingTotal = (float)$ot['value'];
        if ($ot['code'] == 'tax') $taxTotal = (float)$ot['value'];
        if ($ot['code'] == 'coupon') $discount_amount_cart = (float)$ot['value'];
      } 
      $shippingTotal = (isset($_SESSION['this_payment']) && $_SESSION['this_payment'] > 0) ? 0 : $shippingTotal;
      
      $shoppingcart_products = $lC_ShoppingCart->getProducts();
      $amount = (isset($_SESSION['this_payment']) && $_SESSION['this_payment'] > 0) ? number_format($_SESSION['this_payment'], DECIMAL_PLACES) : $lC_Currencies->formatRaw($lC_ShoppingCart->getSubTotal(), $lC_Currencies->getCode());
      $shippingtax = (isset($_SESSION['this_payment']) && $_SESSION['this_payment'] > 0) ? 0 : $lC_ShoppingCart->getShippingCost();

      if(ADDONS_PAYMENT_PAYPAL_PAYMENTS_STANDARD_METHOD == 'Itemized') { 
        //$discount_amount_cart = 0;     

        $paypal_action_params = array(
          'upload' => sizeof($shoppingcart_products),
          'redirect_cmd' => '_cart',
          'handling_cart' => $shippingTotal,
          'discount_amount' => $discount_amount_cart
        );
>>>>>>> e7a728f957e9d1758aa666b28512f4f60647c69c

        $i = 1;
        //BOF shipping tax classes

        $taxClassID = $lC_ShoppingCart->_shipping_method['tax_class_id']; 
        $countryID = ($lC_ShoppingCart->getShippingAddress('country_id') != NULL) ? $lC_ShoppingCart->getShippingAddress('country_id') : STORE_COUNTRY;
        $zoneID = ($lC_ShoppingCart->getShippingAddress('zone_id') != NULL) ? $lC_ShoppingCart->getShippingAddress('zone_id') : STORE_ZONE;
        $taxRate = $lC_Tax->getTaxRate($taxClassID, $countryID, $zoneID);
<<<<<<< HEAD
    
        $finalPrice = (sizeof($shoppingcart_products) === $i)? $products['price'] - $discount_amount_cart:$products['price'];
        $tax = $lC_Tax->calculate($finalPrice, $taxRate);

    $paypal_shoppingcart_params = array(
=======

        $tax_shipping = $lC_Tax->calculate($shippingTotal, $taxRate);
        //EOF shipping tax classes
        foreach($shoppingcart_products as $products) {
          $taxClassID = $products['tax_class_id']; 
          $countryID = ($lC_ShoppingCart->getShippingAddress('country_id') != NULL) ? $lC_ShoppingCart->getShippingAddress('country_id') : STORE_COUNTRY;
          $zoneID = ($lC_ShoppingCart->getShippingAddress('zone_id') != NULL) ? $lC_ShoppingCart->getShippingAddress('zone_id') : STORE_ZONE;
          $taxRate = $lC_Tax->getTaxRate($taxClassID, $countryID, $zoneID);
          $tax = $lC_Tax->calculate($products['price'], $taxRate);

          $paypal_shoppingcart_params = array(
>>>>>>> e7a728f957e9d1758aa666b28512f4f60647c69c
            'item_name_'.$i => $products['name'],
            'item_number_'.$i => $products['item_id'],
            'quantity_'.$i => $products['quantity'],
            'amount_'.$i => $lC_Currencies->formatRaw($products['price'], $lC_Currencies->getCode()),
      'discount_amount_'.$i => (sizeof($shoppingcart_products) === $i)? $discount_amount_cart:'0',
            'tax_'.$i => (sizeof($shoppingcart_products) === $i)? $tax + $tax_shipping:$tax          
          );

          //Customer Specified Product Options: PayPal Max = 2
          if($products['simple_options']) {
            for ($j=0, $n=sizeof($products['simple_options']); $j<2; $j++) {
              $paypal_shoppingcart_simple_options_params = array(
                'on'.$j.'_'.$i => $products['simple_options'][$j]['group_title'],
                'os'.$j.'_'.$i => $products['simple_options'][$j]['value_title']          
              ); 
              $paypal_shoppingcart_params =  array_merge($paypal_shoppingcart_params,$paypal_shoppingcart_simple_options_params);
            }
          }


          $paypal_action_params = array_merge($paypal_action_params,$paypal_shoppingcart_params);

          $i++;
        }

<<<<<<< HEAD
        $paypal_action_params = array_merge($paypal_action_params,$paypal_shoppingcart_params);
        
        $i++;
      }
      
    } else {
      $item_number = '';
      for ($i=1; $i<=sizeof($shoppingcart_products); $i++) {
        $item_number .= ' '.$shoppingcart_products[$i]['name'].' ,';
      }
      $item_number = substr_replace($item_number,'',-2);
      $paypal_action_params = array(
        'item_name' => STORE_NAME,
        'redirect_cmd' => '_xclick',
        'amount' => $amount,
        'shipping' => $shippingTotal,
    'discount_amount' => $discount_amount_cart,
    'tax' => '0.00',
        'item_number' => $item_number
=======
      } else {
        $item_number = '';
        for ($i=1; $i<=sizeof($shoppingcart_products); $i++) {
          $item_number .= ' '.$shoppingcart_products[$i]['name'].' ,';
        }
        $item_number = substr_replace($item_number,'',-2);
        $paypal_action_params = array(
          'item_name' => STORE_NAME,
          'redirect_cmd' => '_xclick',
          'amount' => $amount,
          'shipping' => $shippingTotal,
          'discount_amount' => $discount_amount_cart,
          'tax' => '0.00',
          'item_number' => $item_number
>>>>>>> e7a728f957e9d1758aa666b28512f4f60647c69c
        ); 
        $paypal_action_tax_params = array();
        foreach ($lC_ShoppingCart->getOrderTotals() as $module) {
          if($module['code'] == 'tax') {
            $paypal_action_tax_params = array(
              'tax' => lc_round($module['value'],DECIMAL_PLACES)
            ); 
          }
        }

        $paypal_action_params =  array_merge($paypal_action_params,$paypal_action_tax_params); 
      }

      $order_id = (isset($_SESSION['prepOrderID']) && $_SESSION['prepOrderID'] != NULL) ? end(explode('-', $_SESSION['prepOrderID'])) : 0;
      if ($order_id == 0) $order_id = (isset($_SESSION['cartSync']['orderID']) && $_SESSION['cartSync']['orderID'] != NULL) ? $_SESSION['cartSync']['orderID'] : 0;  

      $return_href_link = lc_href_link(FILENAME_CHECKOUT, 'process', 'AUTO', true, true, true);
      $cancel_href_link = lc_href_link(FILENAME_CHECKOUT, 'cart', 'AUTO', true, true, true);
      $notify_href_link = lc_href_link('addons/Paypal_Payments_Standard/ipn.php', 'ipn_order_id=' . $order_id, 'AUTO', true, true, true);
      $signature = $this->setTransactionID($amount);

      $paypal_standard_params = array(
        'cmd' => '_ext-enter', 
        'bn' => 'LoadedCommerce_Cart',
        'business' => ADDONS_PAYMENT_PAYPAL_PAYMENTS_STANDARD_BUSINESS_ID,       
        'currency_code' => $_SESSION['currency'],
        'return' => $return_href_link,
        'cancel_return' => $cancel_href_link,
        'notify_url' => $notify_href_link,
        'no_shipping' => $no_shipping,
        'rm' => ADDONS_PAYMENT_PAYPAL_PAYMENTS_STANDARD_RM,
        'custom' => $signature,
        'email' => $lC_Customer->getEmailAddress(),
        'first_name' => $lC_ShoppingCart->getBillingAddress('firstname'),
        'last_name' => $lC_ShoppingCart->getBillingAddress('lastname'),
        'address1' => $lC_ShoppingCart->getBillingAddress('street_address'),
        'address2' => '',
        'city' => $lC_ShoppingCart->getBillingAddress('city'), 
        'state' => $lC_ShoppingCart->getBillingAddress('state'), 
        'zip' => $lC_ShoppingCart->getBillingAddress('postcode'),
        'lc' => $lC_ShoppingCart->getBillingAddress('country_iso_code_3'),
        'no_note' => (ADDONS_PAYMENT_PAYPAL_PAYMENTS_STANDARD_NO_NOTE == 'Yes') ? '0': '1',
        'form' => 'mage');   

      $paypal_standard_action_params =  array_merge($paypal_standard_params,$paypal_action_params); 

      $paypal_params = '';
      foreach($paypal_standard_action_params as $name => $value) {
        $paypal_params .= lc_draw_hidden_field($name, $value);
      }

      return $paypal_params;    
    }
    /**
    * Parse the response from the processor
    *
    * @access public
    * @return string
    */ 
    public function process() { 
      // performed by ipn.php
    }

    public function setTransactionID($amount) {
      global $lC_Language, $lC_ShoppingCart, $lC_Currencies, $lC_Customer;
      $my_currency = $lC_Currencies->getCode();
      $trans_id = STORE_NAME . date('Ymdhis');
      $digest = md5($trans_id . number_format($amount * $lC_Currencies->value($my_currency), $lC_Currencies->decimalPlaces($my_currency), '.', '') . ADDONS_PAYMENT_PAYPAL_PAYMENTS_STANDARD_IPN_DIGEST_KEY);
      return $digest;
    }
  }
?>