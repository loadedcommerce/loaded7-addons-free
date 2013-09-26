<?php
/*
  $Id: controller.php v1.0 2013-04-20 datazen $

  Loaded Commerce, Innovative eCommerce Solutions
  http://www.loadedcommerce.com

  Copyright (c) 2013 Loaded Commerce, LLC

  @author     Loaded Commerce Team
  @copyright  (c) 2013 LoadedCommerce Team
  @license    http://loadedcommerce.com/license.html
  @modified Inventory Source, Inc. Team
*/

class InventoryUpdater extends lC_Addon { // your addon must extend lC_Addon
  /*
  * Class constructor
  */
  public function InventoryUpdater() {    
    global $lC_Language;    
   /**
    * The addon type (category)
    * valid types; payment, shipping, themes, checkout, catalog, admin, reports, connectors, inventory, other 
    */    
    $this->_type = 'inventory';
   /**
    * The addon class name
    */    
    $this->_code = 'InventoryUpdater';       
   /**
    * The addon title used in the addons store listing
    */     
    $this->_title = $lC_Language->get('addon_catalog_inventoryupdater_title');
   /**
    * The addon description used in the addons store listing
    */     
    $this->_description = $lC_Language->get('addon_catalog_inventoryupdater_description');
   /**
    * The addon blurb used in the addons store listing
    */     
    $this->_blurb = $lC_Language->get('addon_catalog_inventoryupdater_blurb');    
   /**
    * The developers name
    */    
    $this->_author = 'Inventory Source, Inc.';
   /**
    * The developers web address
    */    
    $this->_authorWWW = 'http://www.inventorysource.com';    
   /**
    * The addon version
    */     
    $this->_version = '1.0.0'; 
   /**
    * The addon image used in the addons store listing
    */     
    $this->_thumbnail = lc_image(DIR_WS_CATALOG . 'addons/' . $this->_code . '/images/logo.png');
   /**
    * The addon enable/disable switch
    */    
    $this->_enabled = (defined('ADDONS_CATALOG_' . strtoupper($this->_code) . '_STATUS') && @constant('ADDONS_CATALOG_' . strtoupper($this->_code) . '_STATUS') == '1') ? true : false;
  }
 /**
  * Checks to see if the addon has been installed
  *
  * @access public
  * @return boolean
  */
  public function isInstalled() {
    return (bool)defined('ADDONS_CATALOG_' . strtoupper($this->_code) . '_STATUS');
  }
 /**
  * Install the addon
  *
  * @access public
  * @return void
  */
  public function install() {
    global $lC_Database;

    $lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Enable AddOn', 'ADDONS_CATALOG_" . strtoupper($this->_code) . "_STATUS', '1', 'Do you want to enable this addon?', '6', '0', 'lc_cfg_use_get_boolean_value', 'lc_cfg_set_boolean_value(array(1, -1))', now())");
  }
 /**
  * Return the configuration parameter keys an an array
  *
  * @access public
  * @return array
  */
  public function getKeys() {
    if (!isset($this->_keys)) {
      $this->_keys = array('ADDONS_CATALOG_' . strtoupper($this->_code) . '_STATUS');
    }

    return $this->_keys;
  }  
}
?>