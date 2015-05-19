<?php
/**
  @package    catalog::addons::payment
  @author     Loaded Commerce
  @copyright  Copyright 2003-2014 Loaded Commerce
  @copyright  Portions Copyright 2003 osCommerce
  @license    https://github.com/loadedcommerce/loaded7/blob/master/LICENSE.txt
  @version    $Id: transfer.php v1.0 2013-05-20 wa4u $
*/
class lC_Payment_transfer extends lC_Payment {  
 /**
  * The public title of the payment module
  *
  * @var string
  * @access protected
  */  
  protected $_title;
 /**
  * The class name of the payment module
  *
  * @var string
  * @access protected
  */  
  protected $_code = 'transfer';
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
  * Class Constructor
  */ 
  public function lC_Payment_transfer() {
    global $lC_Database, $lC_Language, $lC_ShoppingCart;
    
    $this->_title = $lC_Language->get('payment_bank_transfer_title');
    $this->_method_title = $lC_Language->get('payment_bank_transfer_method_title');
    $this->_status = (defined('ADDONS_PAYMENT_BANK_TRANSFER_STATUS') && (ADDONS_PAYMENT_BANK_TRANSFER_STATUS == '1') ? true : false);
    $this->_sort_order = (defined('ADDONS_PAYMENT_BANK_TRANSFER_SORT_ORDER') ? ADDONS_PAYMENT_BANK_TRANSFER_SORT_ORDER : null);    

    if ($this->_status === true) {
      if ((int)ADDONS_PAYMENT_BANK_TRANSFER_ORDER_STATUS_ID > 0) {
        $this->order_status = ADDONS_PAYMENT_BANK_TRANSFER_ORDER_STATUS_ID;
      }

      if ((int)ADDONS_PAYMENT_BANK_TRANSFER_ZONE > 0) {
        $check_flag = false;

        $Qcheck = $lC_Database->query('select zone_id from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id and zone_country_id = :zone_country_id order by zone_id');
        $Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
        $Qcheck->bindInt(':geo_zone_id', ADDONS_PAYMENT_BANK_TRANSFER_ZONE);
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

        if ($check_flag == false) {
          $this->_status = false;
        }
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
    return array('id' => $this->_code,
                 'module' => '<div class="paymentSelectionTitle">' . $this->_method_title . '</div>');
  }
 /**
  * Parse the response from the processor
  *
  * @access public
  * @return string
  */ 
  public function process() {
    global $lC_Language;
   
    $this->email_footer = sprintf($lC_Language->get('payment_bank_transfer_email_footer'), ADDONS_PAYMENT_BANK_TRANSFER_PAYTO, ADDONS_PAYMENT_BANK_TRANSFER_ACCOUNT, ADDONS_PAYMENT_BANK_TRANSFER_BANK, ADDONS_PAYMENT_BANK_TRANSFER_SWIFT);
    $this->_order_id = lC_Order::insert();
    lC_Order::process($this->_order_id, $this->order_status);
  }
}
?>