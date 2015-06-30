<?php
/*
  $Id: certitrade.php v1.0 2013-12-03 gulsarrays $

  LoadedSolutionHub.com, Loaded7 eCommerce Solution Providers
  http://www.LoadedSolutionHub.com

  Copyright (c) 2013 LoadedSolutionHub.com

  @author     LoadedSolutionHub.com Team
  @copyright  (c) 2013 LoadedSolutionHub.com
  @license    http://LoadedSolutionHub.com/license.html
*/
class lC_Payment_certitrade extends lC_Payment {     
 /**
  * The public title of the payment module (admin)
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
  protected $_code = 'certitrade';
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
  * The order id
  *
  * @var integer
  * @access protected
  */ 
  protected $_order_id;
 /**
  * The completed order status ID
  *
  * @var integer
  * @access protected
  */   
  protected $_order_status_complete;
  /**
  * Allowed Currency parameter array
  *
  * @var array
  * @access protected
  */  
  protected $_currency_array;
  /**
  * Allowed Language parameter array
  *
  * @var array
  * @access protected
  */  
  protected $_language_array;    
 /**
  * Constructor
  */      
  public function lC_Payment_certitrade() {
    global $lC_Language;

    $this->_title = $lC_Language->get('payment_certitrade_title'); // admin listing title
    $this->_method_title = $lC_Language->get('payment_certitrade_method_title'); // public sidebar title 
    $this->_status = (defined('ADDONS_PAYMENT_CERTITRADE_STATUS') && (ADDONS_PAYMENT_CERTITRADE_STATUS == '1') ? true : false);
    $this->_sort_order = (defined('ADDONS_PAYMENT_CERTITRADE_SORT_ORDER') ? ADDONS_PAYMENT_CERTITRADE_SORT_ORDER : null);
		$this->table_paytrans = DB_TABLE_PREFIX."paytrans";	

    if ($this->_status == true) {
      $this->initialize();
    }
  }
 /**
  * Initialize the payment module 
  *
  * @access public
  * @return void
  */
  public function initialize() {
    global $lC_Database, $lC_Language, $order;

    if ((int)ADDONS_PAYMENT_CERTITRADE_ORDER_STATUS_ID > 0) {
      $this->order_status = ADDONS_PAYMENT_CERTITRADE_ORDER_STATUS_ID;
    }    
    
    if ((int)ADDONS_PAYMENT_CERTITRADE_ORDER_STATUS_COMPLETE_ID > 0) {
      $this->_order_status_complete = ADDONS_PAYMENT_CERTITRADE_ORDER_STATUS_COMPLETE_ID;
    } 
    
    if (is_object($order)) $this->update_status();

    if (defined('ADDONS_PAYMENT_CERTITRADE_TEST_MODE') && ADDONS_PAYMENT_CERTITRADE_TEST_MODE == '1') {
      $this->form_action_url = 'https://www.certitrade.net/webshophtml/e/auth.php';  // sandbox url      
    } else {
      $this->form_action_url = 'https://payment.certitrade.net/webshophtml/e/auth.php';  // production url      
    }

    //Currency parameter to currency
    $this->_currency_array = array(
      'SEK' => '752', // Swedish krona 
      'EUR' => '978', // Euro 
      'USD' => '840', // US Dollar
      'DKK' => '208', // Dansish krona
      'GBP' => '826', // Brittish punds
      'ISK' => '352', // Islandic krona
      'NOK' => '578', // Norwegian crownes
      'AUD' => '036', // Australian Dollar
      'CAD' => '124', // Canadian Dollar
      'JPY' => '392', // Japanese Yen
      'NZD' => '554', // New Zealand Dollar
      'CHF' => '756', // Swiss Franc
      'TRY' => '949' // New Turkish Lira
    );

  //Language parameter to lang  
  $this->_language_array = array(
    'Danish' => 'da', // Danish
    'Swedish' => 'sv', // Swedish
    'Norwegian' => 'no', // Norwegian
    'English' => 'en', // English
    'Finnish' => 'fi', // Finnish
    'German' => 'de', // German
    'French' => 'fr', // French
    'Italian' => 'it', // Italian
    'Spanska' => 'es' // Spanska
    );
  }
 /**
  * Disable module if zone selected does not match billing zone  
  *
  * @access public
  * @return void
  */  
	
  public function update_status() {
    global $lC_Database, $order;

    if ( ($this->_status === true) && ((int)ADDONS_PAYMENT_CERTITRADE_ZONE > 0) ) {
      $check_flag = false;

      $Qcheck = $lC_Database->query('select zone_id from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id and zone_country_id = :zone_country_id order by zone_id');
      $Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
      $Qcheck->bindInt(':geo_zone_id', ADDONS_PAYMENT_CERTITRADE_ZONE);
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
                       'module' => '<div class="payment-selection">' . $lC_Language->get('payment_certitrade_method_title') . '</div><div class="payment-selection-title">' . $lC_Language->get('payment_certitrade_method_blurb') . '</div>'); 
    
    return $selection;
  }
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
    $lC_ShoppingCart->setBillingMethod(array('id' => 'certitrade', 'title' => $this->_method_title));
    return false;
  }
 /**
  * Return the confirmation button logic
  *
  * @access public
  * @return string
  */ 
 public function process_button() { 
    $this->_order_id = lC_Order::insert($this->order_status);
    echo $this->_getCertitradeParameter();
		return false;
  }
 /**
  * Set Certitrade Payment Parameter
  *
  * @access private
  * @return string
  */
  private function _getCertitradeParameter() {   
    global $lC_Language, $lC_ShoppingCart, $lC_Currencies, $lC_Customer;  

    $retururl   = str_replace('&amp;','&',lc_href_link(FILENAME_CHECKOUT, 'process', 'AUTO', true, true, true));
    //$retururl   = str_replace('&amp;','&', $retururl);
    $approveurl = str_replace('&amp;','&',lc_href_link(FILENAME_CHECKOUT, 'process&mode=approve', 'AUTO', true, true, true));
    //$approveurl = str_replace('&amp;','&', $approveurl);
    $declineurl = str_replace('&amp;','&',lc_href_link(FILENAME_CHECKOUT, 'payment&mode=decline', 'AUTO', true, true, true));
    //$declineurl = str_replace('&amp;','&', $declineurl);
    $cancelurl  = str_replace('&amp;','&',lc_href_link(FILENAME_CHECKOUT, 'cart&mode=cancel', 'AUTO', true, true, true));
    //$cancelurl  = str_replace('&amp;','&', $cancelurl);

    $data_array = array(      
      'md5key' => ADDONS_PAYMENT_CERTITRADE_MD5PROD,
      'merchantid' => ADDONS_PAYMENT_CERTITRADE_MERCHANTID,
      'rev' => 'E',
      'orderid' => $this->_order_id,
      'amount' => $lC_ShoppingCart->getTotal(),
      'currency' => array_key_exists($lC_Currencies->getCode(),$this->_currency_array) ? $this->_currency_array[$lC_Currencies->getCode()] : '840',
      'retururl' => $retururl,
      'approveurl' => $approveurl,
      'declineurl' => $declineurl,
      'cancelurl' => $cancelurl,
      'returwindow' => '',
      'lang' => array_key_exists($lC_Language->getData('name'),$this->_language_array) ? $this->_language_array[$lC_Language->getData('name')] : 'en',
      'cust_id' => $lC_Customer->getID(),
      'cust_name' => $lC_ShoppingCart->getBillingAddress('firstname').' '.$lC_ShoppingCart->getBillingAddress('lastname'),
      'cust_address1' => $lC_ShoppingCart->getBillingAddress('street_address'),
      'cust_address2' => '',
      'cust_address3' => '',
      'cust_zip' => $lC_ShoppingCart->getBillingAddress('postcode'),
      'cust_city' => $lC_ShoppingCart->getBillingAddress('city'),
      'cust_phone' => $lC_Customer->getTelephone(),
      'cust_email' => $lC_Customer->getEmailAddress(),
      'cust_country' => $lC_ShoppingCart->getBillingAddress('country_title'),
      'connection' => '',
      'acquirer' => '',
      'DEBUG' => '0',
      'HTTPDEBUG' => '0',
      'timeout' => '',
      'delayed_capture' => '',
      'max_delay_days' => '',
      'transp1' => '',
      'transp2' => '',
      'returmetod' => '',
    );

    $md5code = $this->generate_md5code($data_array);
    $data_array['md5code'] = $md5code ;
    $poststring = '';
    foreach($data_array AS $key => $val){
      if($key != 'md5key') {
        $poststring .= "<input type='hidden' name='$key' value='$val' />";
      }
    }
    return $poststring;
 }
 /**
  * Generate md5code
  *
  * @access public
  * @return string
  */
 private function generate_md5code($data_array) {
   $md5str = '';
   $md5code = '';

   $tmp_arr = array('md5key', 'merchantid', 'rev', 'orderid', 'amount', 'currency', 'retururl', 'approveurl', 'declineurl', 'cancelurl', 'returwindow', 'lang', 'cust_id', 'cust_name', 'cust_address1', 'cust_address2', 'cust_address3', 'cust_zip', 'cust_city', 'cust_phone', 'cust_email', 'connection', 'acquirer', 'DEBUG', 'HTTPDEBUG');
   if(is_array($data_array)) {     
     foreach($data_array as $k => $v) {
       if(in_array($k,$tmp_arr)) {
        $md5str .= $v;
       }
     }
     $md5code = md5($md5str);
   }
   return $md5code;
 }
 /**
  * Parse the response from the processor
  *
  * @access public
  * @return string
  */
  public function process() {
    global $lC_Language, $lC_Database, $lC_MessageStack, $lC_ShoppingCart;    

    $lC_ShoppingCart->setBillingMethod(array('id' => 'certitrade', 'title' => $this->_method_title));
    
    $error = false;
    $success = (isset($_POST['result']) && $_POST['result'] == 'OK') ? true : false;
    $code = (isset($_POST['result_code']) && $_POST['result_code'] != '') ? preg_replace('/[^0-9]/', '', $_POST['responseCode']) : NULL;

    switch ($success) {
      case true : // success
        // update order status       
        lC_Order::process($this->_order_id, $this->_order_status_complete);      
        // insert into transaction history
        $this->_transaction_response = $code;

        $response_array = array('root' => utility::cleanArr($_POST));
        $response_array['root']['transaction_response'] = $this->_transaction_response;
        $lC_XML = new lC_XML($response_array);

        $Qtransaction = $lC_Database->query('insert into :table_orders_transactions_history (orders_id, transaction_code, transaction_return_value, transaction_return_status, date_added) values (:orders_id, :transaction_code, :transaction_return_value, :transaction_return_status, now())');
        $Qtransaction->bindTable(':table_orders_transactions_history', TABLE_ORDERS_TRANSACTIONS_HISTORY);
        $Qtransaction->bindInt(':orders_id', $this->_order_id);
        $Qtransaction->bindInt(':transaction_code', 1);
        $Qtransaction->bindValue(':transaction_return_value', $lC_XML->toXML());
        $Qtransaction->bindInt(':transaction_return_status', (strtoupper(trim($this->_transaction_response)) == '000') ? 1 : 0);
        $Qtransaction->execute();        
        break;

      default : // error
        switch ($code) {
          case '4' :
            // cancelled
            lc_redirect(lc_href_link(FILENAME_CHECKOUT, 'cart', 'SSL'));
            break;

          default :
            /*
            Code for the result of the authorization.
            00=Approved.
            01=Declined by the bank.
            02=No contact with the bank.
            03=Other technical fault.
            04=Cancelled by the buyer.
            */
            if($code == 1) {
              $msg = $lC_Language->get('error_message_code_1');
            } else if($code == 2) {
              $msg = $lC_Language->get('error_message_code_2');
            } else if($code == 3) {
              $msg = $lC_Language->get('error_message_code_3');
            } else if($code == 4) {
              $msg = $lC_Language->get('error_message_code_4');
            }

            $msg = (isset($_POST['error']) && $_POST['error'] != NULL) ? preg_replace('/[^a-zA-Z0-9]\:\|\[\]/', '', $_POST['error']) : NULL;

            // there was an error
            $lC_MessageStack->add('checkout_payment', sprintf($lC_Language->get('error_payment_problem'), '(' . $code . ') : ' . $msg));
            $_SESSION['messageToStack'] = $lC_MessageStack->getAll(); 
            $error = true;
        }

        if ($error) lc_redirect(lc_href_link(FILENAME_CHECKOUT, 'payment', 'SSL'));
    }
  }
 /**
  * Check the status of the payment module
  *
  * @access public
  * @return boolean
  */ 
  public function check() {
    if (!isset($this->_check)) {
      $this->_check = defined('ADDONS_PAYMENT_CERTITRADE_STATUS');
    }

    return $this->_check;
  }
}
?>