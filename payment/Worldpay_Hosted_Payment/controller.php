<?php
/**
  @package    catalog::addons::payment
  @author     Loaded Commerce
  @copyright  Copyright 2003-2014 Loaded Commerce, LLC
  @copyright  Portions Copyright 2003 osCommerce
  @license    https://github.com/loadedcommerce/loaded7/blob/master/LICENSE.txt
  @version    $Id: controller.php v1.0 2013-08-08 datazen $
*/
// your addon must extend lC_Addon
class Worldpay_Hosted_Payment extends lC_Addon {
  /*
  * Class constructor
  */
  public function Worldpay_Hosted_Payment() {    
    global $lC_Language;    
   /**
    * The addon type (category)
    * valid types; payment, shipping, themes, checkout, catalog, admin, reports, connectors, other 
    */    
    $this->_type = 'payment';
   /**
    * The addon class name
    */    
    $this->_code = 'Worldpay_Hosted_Payment';        
   /**
    * The addon title used in the addons store listing
    */     
    $this->_title = $lC_Language->get('addon_payment_worldpay_hosted_payment_title');
   /**
    * The addon description used in the addons store listing
    */     
    $this->_description = $lC_Language->get('addon_payment_worldpay_hosted_payment_description');
   /**
    * The developers name
    */    
    $this->_author = 'Loaded Commerce, LLC';
   /**
    * The developers web address
    */    
    $this->_authorWWW = 'http://www.loadedcommerce.com';    
   /**
    * The addon version
    */     
    $this->_version = '1.0.1';
   /**
    * The Loaded 7 core compatibility version
    */     
    $this->_compatibility = '7.002.0.0'; // the addon is compatible with this core version and later   
   /**
    * The base64 encoded addon image used in the addons store listing
    */     
    $this->_thumbnail = lc_image(DIR_WS_CATALOG . 'addons/' . $this->_code . '/images/worldpay.png');
   /**
    * The mobile capability of the addon
    */ 
    $this->_mobile_enabled = false;    
   /**
    * The addon enable/disable switch
    */    
    $this->_enabled = (defined('ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_STATUS') && @constant('ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_STATUS') == '1') ? true : false;  
  }
 /**
  * Checks to see if the addon has been installed
  *
  * @access public
  * @return boolean
  */
  public function isInstalled() {
    global $lC_Addon;

    return (bool)defined('ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_STATUS');
  }
 /**
  * Install the addon
  *
  * @access public
  * @return void
  */
  public function install() {
    global $lC_Database;

    $lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Enable AddOn', 'ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_STATUS', '-1', 'Do you want to enable this addon?', '6', '0', 'lc_cfg_use_get_boolean_value', 'lc_cfg_set_boolean_value(array(1, -1))', now())");
    $lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Installation ID', 'ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_INSTALLATION_ID', '', 'Installation ID used for the Worldpay payment service.', '6', '0', now())");
    $lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('MD5 Transaction Password', 'ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_MD5_PASSWORD', '', 'The MD5 secret encryption password used to validate transaction responses with (specified in the WorldPay Customer Management System)', '6', '0', now())");
    $lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Payment Response Password', 'ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_CALLBACK_PASSWORD', '', 'A password that is sent back in the payment response (specified in the WorldPay Customer Management System)', '6', '0', now())");
    $lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Sandbox Mode', 'ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_TEST_MODE', '-1', 'Set to \'Yes\' for sandbox test environment or set to \'No\' for production environment.', '6', '0', 'lc_cfg_use_get_boolean_value', 'lc_cfg_set_boolean_value(array(1, -1))', now())");
    $lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Transaction Method', 'ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_TRANSACTION_METHOD', 'Capture', 'The processing method to use for each transaction', '6', '0', 'lc_cfg_use_get_boolean_value', 'lc_cfg_set_boolean_value(array(\'Pre-Authorization\', \'Capture\'))', now())"); 
    $lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Cards Accepted', 'ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_ACCEPTED_TYPES', '', 'Accept these credit card types for this payment method.', '6', '0', 'lc_cfg_set_credit_cards_checkbox_field', now())");
    $lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'lc_cfg_use_get_zone_class_title', 'lc_cfg_set_zone_classes_pull_down_menu', now())");
    $lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Set Pending Status', 'ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_ORDER_STATUS_ID', '1', 'For Pending orders, set the status of orders made with this payment module to this value.', '6', '0', 'lc_cfg_use_get_order_status_title', 'lc_cfg_set_order_statuses_pull_down_menu', now())");
    $lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Order Completed Status', 'ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_ORDER_STATUS_COMPLETE_ID', '1', 'Set the status of orders made with this payment module when the order has been completed.', '6', '0', 'lc_cfg_use_get_order_status_title', 'lc_cfg_set_order_statuses_pull_down_menu', now())");
    $lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0' , now())");      
    $lC_Database->simpleQuery("ALTER TABLE " . TABLE_ORDERS . " CHANGE payment_method payment_method VARCHAR( 512 ) NOT NULL");
  }
 /**
  * Return the configuration parameter keys an an array
  *
  * @access public
  * @return array
  */
  public function getKeys() {
    global $lC_Addon;
    
    if (!isset($this->_keys)) {
      $this->_keys = array('ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_STATUS',
                           'ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_MD5_PASSWORD',
                           'ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_INSTALLATION_ID',
                           'ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_CALLBACK_PASSWORD',
                           'ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_TEST_MODE',
                           'ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_ACCEPTED_TYPES',
                           'ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_ZONE',
                           'ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_ORDER_STATUS_ID',
                           'ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_ORDER_STATUS_COMPLETE_ID',
                           'ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_SORT_ORDER',
                           'ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_TRANSACTION_METHOD');      
    }

    return $this->_keys;
  }    
}
?>