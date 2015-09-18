<?php
    /*
    $Id: specialshipping.php v1.0 2013-07-10 datazen $

    LoadedCommerce, Innovative eCommerce Solutions
    http://www.loadedcommerce.com

    @author     Loaded Commerce, osCommerce
    @copyright  (c) 2013 Loaded Commerce, osCommerce
    @license    http://loadedcommerce.com/license.html
    */
    include_once(DIR_FS_CATALOG . 'includes/classes/transport.php');

    class lC_Shipping_specialshipping extends lC_Shipping {
        public $icon;

        protected $_title,
        $_code = 'specialshipping',
        $_status = false;  

        // class constructor
        public function lC_Shipping_specialshipping() {
            global $lC_Language,$lC_Database;

            $this->icon = DIR_WS_CATALOG . 'addons/special_shipping/images/special_shipping.png';
            $this->_title = $lC_Language->get('shipping_up_title');
            $this->_status = (defined('ADDONS_SHIPPING_SPECIAL_SHIPPING_STATUS') && (ADDONS_SHIPPING_SPECIAL_SHIPPING_STATUS == '1') ? true : false);
            $this->_sort_order = (defined('ADDONS_SHIPPING_SPECIAL_SHIPPING_SORT_ORDER') ? ADDONS_SHIPPING_SPECIAL_SHIPPING_SORT_ORDER : null);    

            $valuess = "'ADDONS_SHIPPING_SPECIAL_SHIPPING_TYPE%'"; 
            $values_specials = $lC_Database->query('select * from :table_configuration where configuration_key LIKE '.$valuess.'');
            $values_specials->bindTable(':table_configuration',TABLE_CONFIGURATION);
            $values_specials->execute();
            $result = array();

            while($values_specials->next()){
                $result[] = array('title' => $values_specials->value('configuration_title'),'key' => $values_specials->value('configuration_key'), 'value' => $values_specials->value('configuration_value'));
            }

            $variable = array();
            foreach($result as $allow){
                $variable[] = array(''.$allow['title'].'' => ''.$allow['title'].'');
            }

            $this->_types = $variable;

        }

        // class methods
        public function initialize() {
            if ($this->_status === true) {
                $this->_status = true;
            }


        } 


        // class methods
        public function quote($method = '') {
            global $lC_Database, $lC_Language, $lC_ShoppingCart, $lC_Currencies, $lC_Tax;
            $shipping_weight = ($lC_ShoppingCart->getShippingBoxesWeight() < 0.1 ? 0.1 : $lC_ShoppingCart->getShippingBoxesWeight());
            $shipping_num_boxes = ceil((float)$shipping_weight / (float)SHIPPING_MAX_WEIGHT);
            if ($shipping_num_boxes <= 0) $shipping_num_boxes = 1;    

            $allowed_methods = $this->_getAllowedMethods();

            if(defined(ADDONS_SHIPPING_SPECIAL_SHIPPING_DEFAULT)){
                $valuess = "'ADDONS_SHIPPING_SPECIAL_SHIPPING_DEFAULT'"; 
                $values_specials = $lC_Database->query('select * from :table_configuration where configuration_key LIKE '.$valuess.'');
                $values_specials->bindTable(':table_configuration',TABLE_CONFIGURATION);
                $values_specials->execute();

                $value = explode('_',ADDONS_SHIPPING_SPECIAL_SHIPPING_DEFAULT);

                $value_array = $values_specials->value('configuration_value');
                $value_arrayy = "'".$value_array."'";
                $values_specialss = $lC_Database->query('select * from :table_configuration where configuration_key LIKE '.$value_arrayy.'');
                $values_specialss->bindTable(':table_configuration',TABLE_CONFIGURATION);
                $values_specialss->execute();
                $value_final = $values_specialss->value('configuration_title');

                foreach($allowed_methods as $key => $value){
                    if($key == '0'){
                        $value_value = $value;
                    }
                    if($value == $value_final){
                        $matchvalue = $key;
                        $allowed_methods[0] = $value_final;
                        $allowed_methods[$key] = $value_value;
                    }

                }
            }
                                   
            if ( (is_array($allowed_methods)) && (sizeof($allowed_methods) > 0) ) {

                $this->quotes = array('id' => $this->_code,
                                      'module' => $this->_title);

                $methods = array();

                foreach($allowed_methods as $waste){
                    $result_mu[] =  array($waste);
                }

                $std_rcd = false;
                $qsize = sizeof($result_mu);

                for ($i=0; $i<$qsize; $i++) {
                    list($no, $type) = each($result_mu[$i]);
                    if (!in_array($type, $allowed_methods)) continue;
                    foreach($this->_types as $allow){
                        if($type == $allow[$type]){
                            $methods[] = array('id' => $type,
                                'title' => $allow[$type],
                                'cost' => 0
                            );
                    }}

                }
                // begin sort order control - low to high is set, comment out for high to low sort
                $this->quotes['methods'] = $methods;
            } else {
                $this->quotes = array('module' => $this->_title,
                                      'error' => 'We are unable to obtain a rate quote for  shipping.<br>Please contact the store if no other alternative is shown.');
            }

            if (!empty($this->icon)) $this->quotes['icon'] = lc_image($this->icon, $this->_title, null, null, 'style="vertical-align:-35%;"');

            return $this->quotes;
        }

        private function _getAllowedMethods() {
            $allowed = array();
            global $lC_Database;
            $valuess = "'ADDONS_SHIPPING_SPECIAL_SHIPPING_TYPE%'"; 
            $values_specials = $lC_Database->query('select * from :table_configuration where configuration_key LIKE '.$valuess.'');
            $values_specials->bindTable(':table_configuration',TABLE_CONFIGURATION);
            $values_specials->execute();

            $result = array();

            while($values_specials->next()){
                $result[] = array('title' => $values_specials->value('configuration_title'),'key' => $values_specials->value('configuration_key'), 'value' => $values_specials->value('configuration_value'));
            }

            foreach($result as $allow){
                $key = $allow['value'];
                if ($key == '1') {
                    $allowed[] = $allow['title'];
                }
            }

            return $allowed;
        }
    }
?>