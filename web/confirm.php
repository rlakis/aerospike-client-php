<?php
/* =====================================
 * 	 PayPal Express Checkout Call
 * =====================================
 */
include_once dirname(__FILE__) . '/../config/cfg.php';
include_once ($config['dir'] . "/core/lib/paypalfunctions.php");
include_once ($config['dir'] . "/core/model/Db.php");
include_once ($config['dir'] . "/core/model/User.php");
include_once $config['dir'] . '/core/lib/MCSessionHandler.php';

$PaymentOption = "PayPal";
if ($PaymentOption == "PayPal") {
    /*
      '------------------------------------
      ' this  step is required to get parameters to make DoExpressCheckout API call,
      ' this step is required only if you are not storing the SetExpressCheckout API call's request values in you database.
      ' ------------------------------------
     */
    $res = GetExpressCheckoutDetails($_REQUEST['token']);

    /*
      '------------------------------------
      ' The paymentAmount is the total value of
      ' the purchase.
      '------------------------------------
     */

    $finalPaymentAmount = $res["PAYMENTREQUEST_0_AMT"];

    /*
      '------------------------------------
      ' Calls the DoExpressCheckoutPayment API call
      '
      ' The ConfirmPayment function is defined in the file PayPalFunctions.php,
      ' that is included at the top of this file.
      '-------------------------------------------------
     */

    //Format the  parameters that were stored or received from GetExperessCheckout call.
    $token = $_REQUEST['token'];
    $payerID = $_REQUEST['PayerID'];
    $paymentType = 'Sale';
    $currencyCodeType = $res['CURRENCYCODE'];
    $items = array();
    $i = 0;
    // adding item details those set in setExpressCheckout
    while (isset($res["L_PAYMENTREQUEST_0_NAME$i"])) {
        $items[] = array('name' => $res["L_PAYMENTREQUEST_0_NAME$i"], 'amt' => $res["L_PAYMENTREQUEST_0_AMT$i"], 'qty' => $res["L_PAYMENTREQUEST_0_QTY$i"]);
        $i++;
    }

    $resArray = ConfirmPayment($token, $paymentType, $currencyCodeType, $payerID, $finalPaymentAmount, $items);
    $ack = strtoupper($resArray["ACK"]);
    if ($ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING") {
        
        /*
         * TODO: Proceed with desired action after the payment 
         * (ex: start download, start streaming, Add coins to the game.. etc)
          '********************************************************************************************************************
          '
          ' THE PARTNER SHOULD SAVE THE KEY TRANSACTION RELATED INFORMATION LIKE
          '                    transactionId & orderTime
          '  IN THEIR OWN  DATABASE
          ' AND THE REST OF THE INFORMATION CAN BE USED TO UNDERSTAND THE STATUS OF THE PAYMENT
          '
          '********************************************************************************************************************
         */

        $transactionId = $resArray["PAYMENTINFO_0_TRANSACTIONID"]; // Unique transaction ID of the payment.
        $transactionType = $resArray["PAYMENTINFO_0_TRANSACTIONTYPE"]; // The type of transaction Possible values: l  cart l  express-checkout
        $paymentType = $resArray["PAYMENTINFO_0_PAYMENTTYPE"];  // Indicates whether the payment is instant or delayed. Possible values: l  none l  echeck l  instant
        $orderTime = $resArray["PAYMENTINFO_0_ORDERTIME"];  // Time/date stamp of payment
        $amt = $resArray["PAYMENTINFO_0_AMT"];  // The final amount charged, including any  taxes from your Merchant Profile.
        $currencyCode = $resArray["PAYMENTINFO_0_CURRENCYCODE"];  // A three-character currency code for one of the currencies listed in PayPay-Supported Transactional Currencies. Default: USD.
        $feeAmt = $resArray["PAYMENTINFO_0_FEEAMT"];  // PayPal fee amount charged for the transaction
        //	$settleAmt			= $resArray["PAYMENTINFO_0_SETTLEAMT"];  // Amount deposited in your PayPal account after a currency conversion.
        $taxAmt = $resArray["PAYMENTINFO_0_TAXAMT"];  // Tax charged on the transaction.
        //	$exchangeRate		= $resArray["PAYMENTINFO_0_EXCHANGERATE"];  // Exchange rate if a currency conversion occurred. Relevant only if your are billing in their non-primary currency. If the customer chooses to pay with a currency other than the non-primary currency, the conversion occurs in the customer's account.

        /*
          ' Status of the payment:
          'Completed: The payment has been completed, and the funds have been added successfully to your account balance.
          'Pending: The payment is pending. See the PendingReason element for more information.
         */

        $paymentStatus = $resArray["PAYMENTINFO_0_PAYMENTSTATUS"];

        /*
          'The reason the payment is pending:
          '  none: No pending reason
          '  address: The payment is pending because your customer did not include a confirmed shipping address and your Payment Receiving Preferences is set such that you want to manually accept or deny each of these payments. To change your preference, go to the Preferences section of your Profile.
          '  echeck: The payment is pending because it was made by an eCheck that has not yet cleared.
          '  intl: The payment is pending because you hold a non-U.S. account and do not have a withdrawal mechanism. You must manually accept or deny this payment from your Account Overview.
          '  multi-currency: You do not have a balance in the currency sent, and you do not have your Payment Receiving Preferences set to automatically convert and accept this payment. You must manually accept or deny this payment.
          '  verify: The payment is pending because you are not yet verified. You must verify your account before you can accept this payment.
          '  other: The payment is pending for a reason other than those listed above. For more information, contact PayPal customer service.
         */

        $pendingReason = $resArray["PAYMENTINFO_0_PENDINGREASON"];

        /*
          'The reason for a reversal if TransactionType is reversal:
          '  none: No reason code
          '  chargeback: A reversal has occurred on this transaction due to a chargeback by your customer.
          '  guarantee: A reversal has occurred on this transaction due to your customer triggering a money-back guarantee.
          '  buyer-complaint: A reversal has occurred on this transaction due to a complaint about the transaction from your customer.
          '  refund: A reversal has occurred on this transaction because you have given the customer a refund.
          '  other: A reversal has occurred on this transaction due to a reason not listed above.
         */

        $reasonCode = $resArray["PAYMENTINFO_0_REASONCODE"];
        
        $amt = number_format($amt, 2);
        
        $handler = new MCSessionHandler();
        session_set_save_handler($handler, true);
        session_start();
        $db = new DB($config);
        $user = new User($db, $config, null, 0);
        $user->populate();
        $id = $user->info['id'];
        
        error_log(sprintf("PAYPAL %s\t%s\t%d\t%s\t%s\t%s", date("Y-m-d H:i:s"), 'paypal', $id, $amt.$currencyCode, $transactionId, $orderTime).PHP_EOL, 3, "/var/log/mourjan/purchase.log");
        
        $products = $db->queryCacheResultSimpleArray("products", "select product_id, name_ar, name_en, usd_price, mcu  
                                        from product 
                                        where iphone=0 
                                        and blocked=0 
                                        and usd_price > 3 
                                        order by mcu asc", null, 0, $config['ttl_long']);

        $pp = null;
        foreach ($products as $product) {
            if ($amt == $product[3]) {
                $pp = $product;
                break;
            }
        }

        if ($pp) {
            
            $transactionId = 'PP_' . $transactionId;
            $old_transaction = $db->queryResultArray("select id from t_tran where TRANSACTION_ID=?", [$transactionId]);
            if ($old_transaction && count($old_transaction) > 0) {
                //do nothing
                $user->pending['PAYPAL_OLD'] = true;
            } else {
                $orderTime = date("Y-m-d H:i:s",strtotime($orderTime));                
                
                $coins = $db->queryResultArray(
                        "INSERT INTO T_TRAN (UID, DATED, CURRENCY_ID, AMOUNT, DEBIT, CREDIT, XREF_ID, TRANSACTION_ID, TRANSACTION_DATE, PRODUCT_ID) VALUES " .
                        "(?, current_timestamp, ?, ?, 0, ?, 0, ?, ?, ?) RETURNING ID", [$id,$currencyCode, $amt + 0.0, $pp[4] + 0.0, $transactionId, $orderTime, $pp[0]], TRUE, PDO::FETCH_NUM);
                if ($coins && count($coins)>0) {
                    $user->pending['PAYPAL_OK'] = (int) $pp[4];
                    sendSMS('9613287168', "PAYPAL purchase UID {$id}\nServer: {$config['server_id']}\nProduct: {$pp[0]}\nTransaction: {$transactionId}\nDate: {$orderTime}");
                } else {
                    $user->pending['PAYPAL_FAIL'] = true;
                    sendSMS('9613287168', "PAYPAL INSERT FAILURE UID {$id}\nServer: {$config['server_id']}\nProduct: {$pp[0]}\nTransaction: {$transactionId}\nDate: {$orderTime}");
                }
            }
        } else {
            //no product match
            $user->pending['PAYPAL_FAIL'] = true;
            sendSMS('9613287168', "PAYPAL PRODUCT FAILURE UID {$id}\nServer: {$config['server_id']}\nAmount: {$amt}\nTransaction: {$transactionId}\nDate: {$orderTime}");
        }
        $user->update();

        // Add javascript to close Digital Goods frame. You may want to add more javascript code to
        // display some info message indicating status of purchase in the parent window
        ?>
        <html>
            <script>
                window.onload = function() {
                    if (window.opener) {
                        window.close();
                        window.opener.location.reload();
                    }
                    else {
                        if (typeof top.dg !== 'undefined' && top.dg.isOpen() == true) {
                            top.dg.closeFlow();
                            top.window.location.reload();
                            return true;
                        }
                    }
                };
            </script>
        </html>
        <?php
    } else {
        //Display a user friendly Error on the page using any of the following error information returned by PayPal
        $ErrorCode = urldecode($resArray["L_ERRORCODE0"]);
        $ErrorShortMsg = urldecode($resArray["L_SHORTMESSAGE0"]);
        $ErrorLongMsg = urldecode($resArray["L_LONGMESSAGE0"]);
        $ErrorSeverityCode = urldecode($resArray["L_SEVERITYCODE0"]);

//		echo "DoExpressCheckoutDetails API call failed. ";
//		echo "Detailed Error Message: " . $ErrorLongMsg;
//		echo "Short Error Message: " . $ErrorShortMsg;
//		echo "Error Code: " . $ErrorCode;
//		echo "Error Severity Code: " . $ErrorSeverityCode;
        ?>
        <html>
            <script>
                window.onload = function() {
                    if (window.opener) {
                        window.close();
                        window.opener.location.reload();
                    }
                    else {
                        if (typeof top.dg !== 'undefined' && top.dg.isOpen() == true) {
                            top.dg.closeFlow();
                            top.window.location.reload();
                            return true;
                        }
                    }
                };
            </script>
        </html>
        <?php
    }
}

function sendSMS($phone_number, $text, $callback_reference=0) {
    global $config;
    try {
        include_once $config['dir'].'/core/lib/nexmo/NexmoMessage.php';
        $sms = new NexmoMessage('8984ddf8', 'ee02b1df');
        $response = $sender = (strval($phone_number)[0]=='1') ? '12165044111' : 'Mourjan';
        $sms->sendText( "+{$phone_number}", $sender, $text, $callback_reference);
        return $response;    
    } catch (Exception $e) {

    }
    return FALSE;
}
?>