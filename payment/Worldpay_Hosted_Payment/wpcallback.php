<?php
/**
  @package    catalog::addons::payment
  @author     Loaded Commerce
  @copyright  Copyright 2003-2014 Loaded Commerce, LLC
  @copyright  Portions Copyright 2003 osCommerce
  @license    https://github.com/loadedcommerce/loaded7/blob/master/LICENSE.txt
  @version    $Id: wpcallback.php v1.0 2013-08-08 datazen $
*/
require('../../includes/application_top.php');
require_once($lC_Vqmod->modCheck(DIR_FS_CATALOG . 'includes/classes/order.php'));

function meta_redirect($url){
  echo '<meta http-equiv="refresh" content="0;url='.$url.'">';
}

if (isset($_POST['M_hash']) && !empty($_POST['M_hash']) && ($_POST['M_hash'] == md5($_POST['M_sid'] . $_POST['M_cid'] . $_POST['cartId'] . $_POST['M_lang'] . number_format($_POST['amount'], 2) . ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_MD5_PASSWORD))) {
  $pass = true;
}

if (isset($_POST['callbackPW']) && ($_POST['callbackPW'] != ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_CALLBACK_PASSWORD)) {
  $pass = false;
}

if (defined('ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_CALLBACK_PASSWORD') && !isset($_POST['callbackPW'])) {
  $pass = false;
}

$status = isset($_POST['transStatus']) ? $_POST['transStatus'] : false;
$order_id = isset($_POST['cartId']) ? $_POST['cartId'] : false;

if ( $pass && $order_id && $status ) {

  if ( $status == 'Y' ) { // Transaction successfull

    lC_Order::process($order_id, ADDONS_PAYMENT_WORLDPAY_HOSTED_PAYMENT_ORDER_STATUS_COMPLETE_ID);

    $redirect_url = lc_href_link(FILENAME_CHECKOUT, 'process', 'AUTO');
  } elseif ( $status == 'C' ) { // Order canceled
    
    $redirect_url = lc_href_link(FILENAME_CHECKOUT, 'cart', 'SSL', true, true, true);
  } else { // Something else went wrong, send back to payment page

    $error_message = '&payment_error=' . $lC_Language->get('text_label_error') . ' ' . $_POST['rawAuthMessage'];
    $redirect_url = lc_href_link(FILENAME_CHECKOUT, 'payment'.$error_message, 'SSL', true, true, true);
  }

  // insert into transaction history
  $response_array = array('root' => $_POST);
  $response_array['root']['transaction_response'] = trim($status);
  $lC_XML = new lC_XML($response_array);

  $Qtransaction = $lC_Database->query('insert into :table_orders_transactions_history (orders_id, transaction_code, transaction_return_value, transaction_return_status, date_added) values (:orders_id, :transaction_code, :transaction_return_value, :transaction_return_status, now())');
  $Qtransaction->bindTable(':table_orders_transactions_history', TABLE_ORDERS_TRANSACTIONS_HISTORY);
  $Qtransaction->bindInt(':orders_id', $order_id);
  $Qtransaction->bindInt(':transaction_code', 1);
  $Qtransaction->bindValue(':transaction_return_value', $lC_XML->toXML());
  $Qtransaction->bindInt(':transaction_return_status', (strtoupper(trim($status)) == 'Y') ? 1 : 0);
  $Qtransaction->execute();
} else {
  $redirect_url = lc_href_link(FILENAME_CHECKOUT, 'cart', 'SSL', true, true, true); // Default redirect
}

meta_redirect($redirect_url);
?>