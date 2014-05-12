<?php
/**
  @package    catalog::admin::applications
  @author     Loaded Commerce
  @copyright  Copyright 2003-2014 Loaded Commerce, LLC
  @copyright  Portions Copyright 2003 osCommerce
  @copyright  Portions Copyright Graith Internet 
  @license    https://github.com/loadedcommerce/loaded7/blob/master/LICENSE.txt
  @version    $Id: controller.php v1.0 2014-03-19 $
*/
class Google_Analytics_with_Demographics extends lC_Addon { // your addon must extend lC_Addon
  /*
  * Class constructor
  */
  public function Google_Analytics_with_Demographics() {    
    global $lC_Language;    
   /**
    * The addon type (category)
    * valid types; payment, shipping, themes, checkout, catalog, admin, reports, connectors, other 
    */    
    $this->_type = 'connectors';
   /**
    * The addon class name
    */    
    $this->_code = 'Google_Analytics_with_Demographics';       
   /**
    * The addon title used in the addons store listing
    */     
    $this->_title = $lC_Language->get('Google_Analytics_with_Demographics_title');
   /**
    * The addon description used in the addons store listing
    */     
    $this->_description = $lC_Language->get('Google_Analytics_with_Demographics_description');
   /**
    * The developers name
    */    
    $this->_author = 'Graith Internet';
   /**
    * The developers web address
    */    
    $this->_authorWWW = 'http://www.graith.co.uk';    
   /**
    * The addon version
    */     
    $this->_version = '1.0.3'; 
   /**
    * The Loaded 7 core compatibility version
    */     
    $this->_compatibility = '7.002.0.0'; // the addon is compatible with this core version and later   
   /**
    * The addon image used in the addons store listing
    */     
    $this->_thumbnail = lc_image(DIR_WS_CATALOG . 'addons/' . $this->_code . '/images/google_analytics.png');
   /**
    * The addon enable/disable switch
    */    
    $this->_enabled = (defined('ADDONS_CONNECTORS_' . strtoupper($this->_code) . '_STATUS') && @constant('ADDONS_CONNECTORS_' . strtoupper($this->_code) . '_STATUS') == '1') ? true : false;
  }

  /**
   * getAddonBlurb 
   * @return [type] [description]
   */
  public function getAddonBlurb() {
    if ($this->_blurb != '') return ($this->_blurb);
    $hook_directory = DIR_FS_CATALOG . 'addons/' . $this->_code . '/hooks/';
    
    // This method is called once when the Setup button is pressed. It give an opportunity to check for hooks
    
    $core_hook = file_get_contents($hook_directory.'templates_core.xml');
    
    // Check to see if there are any templates installed that don't have hook files

    $templates_installed = scandir(DIR_FS_CATALOG . 'templates');
    foreach ($templates_installed as $template_file) {
      if (preg_match('/([^\.]+)\.php$/',$template_file,$matches)) {
        $hook_file = 'templates_'.$matches[1].'.xml';
        if (!file_exists($hook_directory.$hook_file)) {
          // Work out the hook contents from the core version with a str_replace
          $new_hook = str_replace('core.php',$matches[1].'.php',$core_hook);
          file_put_contents($hook_directory.$hook_file, $new_hook);
          $this->_blurb .= '<h3>Added Support for template '.$matches[1].'</h3>';
        }
      }
    }

    return ($this->_blurb);
  }

 /**
  * Checks to see if the addon has been installed
  *
  * @access public
  * @return boolean
  */
  public function isInstalled() {
    return (bool)defined('ADDONS_CONNECTORS_' . strtoupper($this->_code) . '_STATUS');
  }
 /**
  * Install the addon
  *
  * @access public
  * @return void
  */
  public function install() {
    global $lC_Database;

    $lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Enable AddOn', 'ADDONS_CONNECTORS_" . strtoupper($this->_code) . "_STATUS', '1', 'Do you want to enable this addon?', '6', '0', 'lc_cfg_use_get_boolean_value', 'lc_cfg_set_boolean_value(array(1, -1))', now())");
    $lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Google Analytics Account Number', 'ADDONS_CONNECTORS_" . strtoupper($this->_code) . "_ACCOUNT', '', 'Enter your Google Analytics account number. This number should start with UA-', '6', '10',now())");
    $lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Enable Demographics', 'ADDONS_CONNECTORS_" . strtoupper($this->_code) . "_DEMOGRAPHICS', '-1', 'Do you want to enable demographics?<br><a href=\"https://support.google.com/analytics/answer/2819948\" target=\"_blank\">support.google.com&hellip;</a>', '6', '20', 'lc_cfg_use_get_boolean_value', 'lc_cfg_set_boolean_value(array(1, -1))', now())");
  }
 /**
  * Return the configuration parameter keys an an array
  *
  * @access public
  * @return array
  */
  public function getKeys() {
    if (!isset($this->_keys)) {
      $this->_keys = array(	'ADDONS_CONNECTORS_' . strtoupper($this->_code) . '_STATUS',
	  						'ADDONS_CONNECTORS_' . strtoupper($this->_code) . '_ACCOUNT',
                'ADDONS_CONNECTORS_' . strtoupper($this->_code) . '_DEMOGRAPHICS'
                );
    }

    return $this->_keys;
  }  
}
?>