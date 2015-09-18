<?php
    /*
    $Id: controller.php v1.0 2013-04-20 datazen $

    Loaded Commerce, Innovative eCommerce Solutions
    http://www.loadedcommerce.com

    Copyright (c) 2013 Loaded Commerce, LLC

    @author     Loaded Commerce Team
    @copyright  (c) 2013 LoadedCommerce Team
    @license    http://loadedcommerce.com/license.html
    */
    class Special_Shipping extends lC_Addon { // your addon must extend lC_Addon
        /*
        * Class constructor
        */
        public function Special_Shipping() {    
            global $lC_Language;    
            /**
            * The addon type (category)
            * valid types; payment, shipping, themes, checkout, catalog, admin, reports, connectors, other 
            */    
            $this->_type = 'shipping';
            /**
            * The addon class name
            */    
            $this->_code = 'Special_Shipping';        
            /**
            * The addon title used in the addons store listing
            */     
            $this->_title = $lC_Language->get('addon_shipping_up_title');

            /* The addon description used in the addons store listing
            */     
            $this->_description = $lC_Language->get('addon_shipping_up_description');
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
            $this->_version = '1.0.0';
            /**
            * The Loaded 7 core compatibility version
            */     
            $this->_compatibility = '7.0.1.1'; // the addon is compatible with this core version and later   
            /**
            * The base64 encoded addon image used in the addons store listing
            */     
            $this->_thumbnail = lc_image(DIR_WS_CATALOG . 'addons/' . $this->_code . '/images/special_shipping.png', $this->_title);
            /**
            * The addon enable/disable switch
            */    
            $this->_enabled = (defined('ADDONS_SHIPPING_' . strtoupper($this->_code) . '_STATUS') && @constant('ADDONS_SHIPPING_' . strtoupper($this->_code) . '_STATUS') == '1') ? true : false;      
        }
        /**
        * Checks to see if the addon has been installed
        *
        * @access public
        * @return boolean
        */
        public function isInstalled() {
            global $lC_Database;

            $valuess = "'ADDONS_SHIPPING_SPECIAL_SHIPPING_ADDNEW'"; 
            $values_specials = $lC_Database->query('select configuration_value from :table_configuration where configuration_key LIKE '.$valuess.'');
            $values_specials->bindTable(':table_configuration',TABLE_CONFIGURATION);
            $values_specials->execute();

            $compare = $values_specials->value('configuration_value');
            if($compare != 'add new' && $compare != ''){  
                $value_main = ADDONS_SHIPPING_SPECIAL_SHIPPING_ADDNEW;
                $value = explode(' ',ADDONS_SHIPPING_SPECIAL_SHIPPING_ADDNEW); 
                $value = implode('_',$value); 
                $value_uppercase = strtoupper($value);
                $file_real = realpath('../addons/Special_Shipping/controller.php');
                $filee = file($file_real);
                $fileee ="'ADDONS_SHIPPING_' . strtoupper('$this->_code') . '_TYPE_".$value_uppercase."',";
                $values = "'ADDONS_SHIPPING_SPECIAL_SHIPPING_TYPE%'";
                $values_special = $lC_Database->query('select * from :table_configuration where configuration_key LIKE '.$values.'');
                $values_special->bindTable(':table_configuration',TABLE_CONFIGURATION);
                $values_special->execute();

                $array= array();
                while ($values_special->next()) {

                    $array[] = array('id' => $values_special->value('configuration_key'),
                        'text' => $values_special->value('configuration_title'));
                }
                
                $var = array();
                foreach($array as $allow){
                    if($allow['id'] != $fileee){
                }}	 


                $lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('".$value_main."', 'ADDONS_SHIPPING_" . strtoupper($this->_code) . "_TYPE_".$value_uppercase."', '1', 'Enable ".$value_main.".', '6', '0', 'lc_cfg_set_boolean_value(array(1, -1))', now())"); 
                $lC_Database->simpleQuery("update ".TABLE_CONFIGURATION . " set configuration_value = 'add new' where configuration_key = 'ADDONS_SHIPPING_SPECIAL_SHIPPING_ADDNEW'"); 

                array_splice($filee, 180, 0, $fileee);
                file_put_contents($file_real,$filee); 
            }

            return (bool)defined('ADDONS_SHIPPING_' . strtoupper($this->_code) . '_STATUS');
        }
        /**
        * Install the addon
        *
        * @access public
        * @return void
        */
        public function install() {
            global $lC_Database;

            $lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Enable AddOn', 'ADDONS_SHIPPING_" . strtoupper($this->_code) . "_STATUS', '-1', 'Do you want to enable this addon?', '6', '0', 'lc_cfg_use_get_boolean_value', 'lc_cfg_set_boolean_value(array(1, -1))', now())");
            $lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Set as default', 'ADDONS_SHIPPING_" . strtoupper($this->_code) . "_DEFAULT', '-1', 'select default one?', '6', '0', 'lc_cfg_set_special_pulldown_menu()', now())");
            $lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Ground', 'ADDONS_SHIPPING_" . strtoupper($this->_code) . "_TYPE_GND', '1', 'Enable Ground delivery.', '6', '0', 'lc_cfg_set_boolean_value(array(1, -1))', now())");
            $lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('2nd Day', 'ADDONS_SHIPPING_" . strtoupper($this->_code) . "_TYPE_2DA', '1', 'Enable 2nd Day delivery.', '6', '0', 'lc_cfg_set_boolean_value(array(1, -1))', now())");
            $lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('3 Day Select', 'ADDONS_SHIPPING_" . strtoupper($this->_code) . "_TYPE_3DS', '1', 'Enable 3 Day Select delivery.', '6', '0', 'lc_cfg_set_boolean_value(array(1, -1))', now())");
            $lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Overnight', 'ADDONS_SHIPPING_" . strtoupper($this->_code) . "_TYPE_ONG', '1', 'Enable overnight delivery.', '6', '0', 'lc_cfg_set_boolean_value(array(1, -1))', now())");
            $lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Add new one', 'ADDONS_SHIPPING_" . strtoupper($this->_code) . "_ADDNEW', 'add new', 'Add new one.', '6', '0', now())");
            $lC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Note', 'ADDONS_SHIPPING_" . strtoupper($this->_code) . "_NOTE', 'Shipping/Handling and any applicable taxes will be added to this order at the time of shipping. LTL Common Carrier will be used as the default shipping method for all items that are unable to ship in small packages with UPS. Common Carrier shipments will ship standard ground. For an estimated freight charge or an official quote on any expedited shipping, please contact us before placing your order. If you would like us to use another carrier for shipping not listed here or bill any account collect please put your request and account number in the comment section. ', 'note for user', '6', '0','lc_cfg_set_textarea_field', 'lc_cfg_set_textarea_field', now())");
        }
        /**
        * Return the configuration parameter keys an an array
        *
        * @access public
        * @return array
        */
        public function getKeys() {
            global $lC_Database;

            $lC_Database->simpleQuery("update ".TABLE_CONFIGURATION . " set configuration_value = 'add new' where configuration_key = 'ADDONS_SHIPPING_SPECIAL_SHIPPING_ADDNEW'"); 
            if (!isset($this->_keys)) {
                $this->_keys = array('ADDONS_SHIPPING_' . strtoupper($this->_code) . '_STATUS',
                    'ADDONS_SHIPPING_' . strtoupper($this->_code) . '_DEFAULT',	     'ADDONS_SHIPPING_' . strtoupper($this->_code) . '_TYPE_GND',
                    'ADDONS_SHIPPING_' . strtoupper($this->_code) . '_TYPE_2DA',
                    'ADDONS_SHIPPING_' . strtoupper($this->_code) . '_TYPE_3DS',
                    'ADDONS_SHIPPING_' . strtoupper($this->_code) . '_TYPE_ONG',
                    'ADDONS_SHIPPING_' . strtoupper($this->_code) . '_ADDNEW',
                    'ADDONS_SHIPPING_' . strtoupper($this->_code) . '_NOTE'
                );
            }

            return $this->_keys;
        }     
    }
?>