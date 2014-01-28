<?php
/**
  @package    catalog::addons::payment
  @author     Loaded Commerce
  @copyright  Copyright 2003-2014 Loaded Commerce, LLC
  @copyright  Portions Copyright 2003 osCommerce
  @license    https://github.com/loadedcommerce/loaded7/blob/master/LICENSE.txt
  @version    $Id: worldpay_hosted_payment.php v1.0 2013-08-08 datazen $
*/
include_once(DIR_FS_CATALOG . 'includes/classes/transport.php');

class lC_Payment_worldpay_hosted_payment extends lC_Payment {     
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
  protected $_code = 'worldpay_hosted_payment';
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
  * The allowed credit card types (pipe separated)
  *
  * @var string
  * @access protected
  */ 
  protected $_allowed_types;  
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
  * The credit card image string
  *
  * @var string
  * @access protected
  */   
  protected $_card_images;   
 /**
  * Constructor
  */      
  public function lC_Payment_worldpay_hosted_payment() {
    global $lC_Language;

    $this->_title = $lC_Language->get('payment_worldpay_hosted_payment_title');
    $this->_method_title = $lC_Language->get('payment_worldpay_hosted_payment_method_title');
    $this->_status = true;
    $this->_sort_order = (defined('ADDONS_PAYMENT_WORLDPAY_SORT_ORDER') ? ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_SORT_ORDER : null);

    if (defined('ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_STATUS')) {
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

    if ((int)ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_ORDER_STATUS_ID > 0) {
      $this->order_status = ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_ORDER_STATUS_ID;
    }
    
    if ((int)ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_ORDER_STATUS_COMPLETE_ID > 0) {
      $this->_order_status_complete = ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_ORDER_STATUS_COMPLETE_ID;
    }    

    if (is_object($order)) $this->update_status();
    
     if (defined('ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_TEST_MODE') && ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_TEST_MODE == '1') {
        
        $this->form_action_url = 'https://secure-test.worldpay.com/wcc/purchase'; 
      }else{

        $this->form_action_url = 'https://secure.worldpay.com/wcc/purchase';
      }

    
    $Qcredit_cards = $lC_Database->query('select credit_card_name from :table_credit_cards where credit_card_status = :credit_card_status');
    $Qcredit_cards->bindRaw(':table_credit_cards', TABLE_CREDIT_CARDS);
    $Qcredit_cards->bindInt(':credit_card_status', '1');
    $Qcredit_cards->setCache('credit-cards');
    $Qcredit_cards->execute();

    while ($Qcredit_cards->next()) {
      $this->_card_images .= lc_image('images/cards/cc_' . strtolower(str_replace(" ", "_", $Qcredit_cards->value('credit_card_name'))) . '.png', null, null, null, 'style="vertical-align:middle; margin:0 2px;"');
      $name = strtolower($Qcredit_cards->value('credit_card_name'));
      if (stristr($Qcredit_cards->value('credit_card_name'), 'discover')) $name = 'Discover';
      if (stristr($Qcredit_cards->value('credit_card_name'), 'jcb')) $name = 'JCB';
      $this->_allowed_types .= ucwords($name) . '|';
    }
    if (substr($this->_allowed_types, -1) == '|') $this->_allowed_types = substr($this->_allowed_types, 0, strlen($this->_allowed_types) - 1);
    
    $Qcredit_cards->freeResult();      
  }
 /**
  * Disable module if zone selected does not match billing zone  
  *
  * @access public
  * @return void
  */  
  public function update_status() {
    global $lC_Database, $order;

    if ( ($this->_status === true) && ((int)ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_ZONE > 0) ) {
      $check_flag = false;

      $Qcheck = $lC_Database->query('select zone_id from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id and zone_country_id = :zone_country_id order by zone_id');
      $Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
      $Qcheck->bindInt(':geo_zone_id', ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_ZONE);
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
                       'module' => '<div class="payment-selection">' . $this->_method_title . '<span>' . $this->_card_images . '</span></div><div class="payment-selection-title">' . $lC_Language->get('payment_worldpay_hosted_payment_method_blurb') . '</div>');    
    
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
    
    return false;    
  }
 /**
  * Return the confirmation button logic
  *
  * @access public
  * @return string
  */ 
  public function process_button() {
   global $lC_Currencies, $lC_Customer, $order, $lC_Currencies, $lC_ShoppingCart, $lC_Language;

      $order_id = lC_Order::insert();
      $process_button_string = lc_draw_hidden_field('instId', ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_INSTALLATION_ID) .
                               lc_draw_hidden_field('amount', $lC_Currencies->formatRaw($lC_ShoppingCart->getTotal(), $lC_Currencies->getCode())) .
                               lc_draw_hidden_field('currency', $_SESSION['currency']) .
                               lc_draw_hidden_field('hideCurrency', 'true') .
                               lc_draw_hidden_field('cartId', $order_id) .
                               lc_draw_hidden_field('desc', STORE_NAME) .
                               lc_draw_hidden_field('name', $lC_ShoppingCart->getBillingAddress('firstname') . ' ' . $lC_ShoppingCart->getBillingAddress('lastname')) .
                               lc_draw_hidden_field('address', $lC_ShoppingCart->getBillingAddress('street_address')) .
                               lc_draw_hidden_field('postcode', $lC_ShoppingCart->getBillingAddress('postcode')) .
                               lc_draw_hidden_field('country', $lC_ShoppingCart->getBillingAddress('country_iso_code_2')) .
                               lc_draw_hidden_field('tel', $lC_Customer->getTelephone()) .
                               lc_draw_hidden_field('email', $lC_Customer->getEmailAddress()) .
                               lc_draw_hidden_field('fixContact', 'Y') .
                               lc_draw_hidden_field('lang', $lC_Language->getCode()) .
                               lc_draw_hidden_field('signatureFields', 'amount:currency:cartId') .
                               lc_draw_hidden_field('signature', md5(ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_MD5_PASSWORD . ':' . $lC_Currencies->formatRaw($lC_ShoppingCart->getTotal(), $lC_Currencies->getCode()) . ':' . $_SESSION['currency'] . ':' . $order_id));

      if (defined('ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_TRANSACTION_METHOD') && ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_TRANSACTION_METHOD == '1') {

        $process_button_string .= lc_draw_hidden_field('authMode', 'E');
      }else{

        $process_button_string .= lc_draw_hidden_field('authMode', 'A');
      }

      if (defined('ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_TEST_MODE') && ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_TEST_MODE == '1') {

        $process_button_string .= lc_draw_hidden_field('testMode', '100');
      }

      $process_button_string .= lc_draw_hidden_field('M_sid', session_id()) .
                                lc_draw_hidden_field('M_cid', $lC_Customer->getID()) .
                                lc_draw_hidden_field('M_lang', $lC_Language->getCode()) .
                                lc_draw_hidden_field('M_hash', md5(session_id() . $lC_Customer->getID() . $order_id . $lC_Language->getCode() . number_format($lC_ShoppingCart->getTotal(), 2) . ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_MD5_PASSWORD));

      return $process_button_string;
  }
 /**
  * Parse the response from the processor
  *
  * @access public
  * @return string
  */ 
  public function process() {
    
    return false;
  } 
 /**
  * Check the status of the pasyment module
  *
  * @access public
  * @return boolean
  */ 
  public function check() {
    if (!isset($this->_check)) {
      $this->_check = defined('ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_STATUS');
    }

    return $this->_check;
  } 
}
?>