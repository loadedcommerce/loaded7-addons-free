<?php
/**
  @package    catalog::modules::content
  @author     Loaded Commerce
  @copyright  Copyright 2003-2014 Loaded Commerce, LLC
  @copyright  Portions Copyright 2003 osCommerce
  @copyright  Portions Copyright Graith Internet 
  @license    https://github.com/loadedcommerce/loaded7/blob/master/LICENSE.txt
  @version    $Id: google_analytics_ecommerce_tracking.php v1.0 2014-03-19 $
*/
global $oID, $lC_Customer;

$name = (defined('STORE_NAME')) ? STORE_NAME : $lC_Customer->getName();

if ((defined('ADDONS_CONNECTORS_GOOGLE_ANALYTICS_WITH_DEMOGRAPHICS_STATUS') && ADDONS_CONNECTORS_GOOGLE_ANALYTICS_WITH_DEMOGRAPHICS_STATUS == 1) ||
  (defined('ADDONS_CONNECTORS_GOOGLE_ANALYTICS_WITH_DEMOGRAPHICS_ECOMMERCE_TRACKING') && ADDONS_CONNECTORS_GOOGLE_ANALYTICS_WITH_DEMOGRAPHICS_ECOMMERCE_TRACKING == 1)) {
    
  $total = 0;
  $tax = 0;
  $shipping = 0;
  foreach (lC_Success::getOrderTotals($oID) as $ot) {
    switch ($ot['class']) {
      case 'shipping':
        $shipping = (float)$ot['value'];
        break;
      case 'tax':
        $tax = (float)$ot['value'];
        break;     
      case 'total':
        $total = (float)$ot['value'];
        break;       
    }
  }     
  $shipData = lC_Success::getShippingAddress($oID, $lC_Customer->getID());
  $city = $shipData['city'];
  $state = $shipData['zone_code'];
  $country = lC_Address::getCountryIsoCode3($lC_Customer->getCountryID());
  $products = lC_Success::getOrderProducts($oID);

  echo '<!-- Google Analytics Ecommerce Tracking -->' . "\n";
  echo '  var _gaq = _gaq || [];' . "\n";
  echo "  _gaq.push(['_setAccount', '" . ADDONS_CONNECTORS_GOOGLE_ANALYTICS_WITH_DEMOGRAPHICS_ACCOUNT . "']);" . "\n";
  echo "  _gaq.push(['_trackPageview']);" . "\n";
  echo "  _gaq.push(['_addTrans'," . "\n";
  echo "    '$oID',           // transaction ID - required" . "\n";
  echo "    '$name',          // affiliation or store name" . "\n";
  echo "    '$total',          // total - required" . "\n";
  echo "    '$tax',           // tax" . "\n";
  echo "    '$shipping',              // shipping" . "\n";
  echo "    '$city',       // city" . "\n";
  echo "    '$state',     // state or province" . "\n";
  echo "    '$country'             // country" . "\n";
  echo "  ]);" . "\n";

  foreach($products as $product) {
    $pID = $product['id'];
    $name = $product['name'];
    $options = $product['options'];
    $price = $product['price'];
    $qty = $product['quantity'];
    echo "  _gaq.push(['_addItem'," . "\n";
    echo "    '$oID',           // transaction ID - required" . "\n";
    echo "    '$pID',           // SKU/code - required" . "\n";
    echo "    '$name',        // product name" . "\n";
    echo "    '$options',   // category or variation" . "\n";
    echo "    '$price',          // unit price - required" . "\n";
    echo "    '$qty'               // quantity - required" . "\n";
    echo "  ]);" . "\n";
  }
  echo "  _gaq.push(['_trackTrans']); //submits transaction to the Analytics servers" . "\n";
  echo "  (function() {" . "\n";
  echo "    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;" . "\n";
  echo "    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';" . "\n";
  echo "    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);" . "\n";
  echo "  })();" . "\n";
  echo "<!-- End Google Analytics Ecommerce Tracking-->" . "\n";
} 
?>