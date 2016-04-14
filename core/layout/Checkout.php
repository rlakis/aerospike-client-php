<?php
require_once 'Page.php';
include_once ($config['dir']."/core/lib/paypalfunctions.php");

class Checkout extends Page{

    function __construct($router){
        parent::__construct($router);
        if($this->urlRouter->cfg['active_maintenance']){
            $this->user->redirectTo('/maintenance/'.($this->urlRouter->siteLanguage=='ar'?'':$this->urlRouter->siteLanguage.'/'));
        }
        $this->checkBlockedAccount();
        $title = $this->lang['account_balance'];
        $this->forceNoIndex=true;
        $this->title=$title;
        $this->urlRouter->cfg['enabled_ads']=0;
        
        $this->inlineCss.='
        ';
        
        $this->render();
    }
    
    function render(){
        $country_code="";
        if ($this->urlRouter->countryId && array_key_exists($this->urlRouter->countryId, $this->urlRouter->countries)) {
            $country_code = '-'.$this->urlRouter->countries[$this->urlRouter->countryId]['uri'];
        }
        ?><!doctype html><html lang="<?= $this->urlRouter->siteLanguage . $country_code ?>"><head><meta charset="UTF-8"><?php 
        echo '<title>', $this->title, '</title>';
        echo '<meta name="robots" content="noindex,nofollow,noarchive" />';
        ?><link rel="icon" href="<?= $this->urlRouter->cfg['url_img'] ?>/favicon.ico" type="image/x-icon" /><?php 
        /* if (!$this->isMobile) {?><meta http-equiv="X-UA-Compatible" content="IE=8" /><?php } */
            $this->set_analytics_header();
            /*$valentine_day = date('j');
             *  <script async src="<?= $this->urlRouter->cfg['url_jquery_mobile'] ?>/jq.min.js"></script>
            ?></head><?php flush() ?><body class="<?= ($this->urlRouter->userId ? 'partner':'').
                    ( ($valentine_day==14 && in_array($this->urlRouter->countryId,array(1,2,5,6,10,11,15,122,145))) ? ' valentine':'' ) 
                    ?>" <?= $this->pageItemScope ?>><meta itemprop="isFamilyFriendly" content="true" /><?php
             * 
             */
        ?></head><?php flush() ?><body><meta itemprop="isFamilyFriendly" content="true" /><?php
        
        $product = isset($_POST['product']) ? $_POST['product'] : '';
        
        $products = $this->urlRouter->db->queryCacheResultSimpleArray("products",
                                        "select product_id, name_ar, name_en, usd_price, mcu  
                                        from product 
                                        where iphone=0 
                                        and blocked=0 
                                        and usd_price > 3 
                                        order by mcu asc",
                                        null, 0, $this->urlRouter->cfg['ttl_long']);
        if(isset($products[$product])){
            $product = $products[$product];            
            $paymentAmount = number_format($product[3],2);
        }else{
            $product = 0;
        }

$PaymentOption = "PayPal";
if ( $PaymentOption == "PayPal" && $product)
{
        // ==================================
        // PayPal Express Checkout Module
        // ==================================

	
	        
        //'------------------------------------
        //' The paymentAmount is the total value of 
        //' the purchase.
        //'
        //' TODO: Enter the total Payment Amount within the quotes.
        //' example : $paymentAmount = "15.00";
        //'------------------------------------

        
        
        //'------------------------------------
        //' The currencyCodeType  
        //' is set to the selections made on the Integration Assistant 
        //'------------------------------------
        $currencyCodeType = "USD";
        $paymentType = "Sale";

        //'------------------------------------
        //' The returnURL is the location where buyers return to when a
        //' payment has been succesfully authorized.
        //'
        //' This is set to the value entered on the Integration Assistant 
        //'------------------------------------
        $returnURL = "https://www.mourjan.com/web/confirm.php";

        //'------------------------------------
        //' The cancelURL is the location buyers are sent to when they hit the
        //' cancel button during authorization of payment during the PayPal flow
        //'
        //' This is set to the value entered on the Integration Assistant 
        //'------------------------------------
        $cancelURL = "https://www.mourjan.com/web/cancel.php";

        //'------------------------------------
        //' Calls the SetExpressCheckout API call
        //'
        //' The CallSetExpressCheckout function is defined in the file PayPalFunctions.php,
        //' it is included at the top of this file.
        //'-------------------------------------------------

		$items = array();
		$items[] = array(
                    'name' => $product[2].' package', 
                    //'amt' => number_format($paymentAmount / $product[4], 3), 
                    'amt' => $paymentAmount, 
                    'qty' => 1);
	
		//::ITEMS::
		
		// to add anothe item, uncomment the lines below and comment the line above 
		// $items[] = array('name' => 'Item Name1', 'amt' => $itemAmount1, 'qty' => 1);
		// $items[] = array('name' => 'Item Name2', 'amt' => $itemAmount2, 'qty' => 1);
		// $paymentAmount = $itemAmount1 + $itemAmount2;
		
		// assign corresponding item amounts to "$itemAmount1" and "$itemAmount2"
		// NOTE : sum of all the item amounts should be equal to payment  amount 

		$resArray = SetExpressCheckoutDG( $paymentAmount, $currencyCodeType, $paymentType, 
												$returnURL, $cancelURL, $items );

        $ack = strtoupper($resArray["ACK"]);
        if($ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING")
        {
                $token = urldecode($resArray["TOKEN"]);
                 RedirectToPayPalDG( $token );
        } 
        else  
        {
                //Display a user friendly Error on the page using any of the following error information returned by PayPal
                $ErrorCode = urldecode($resArray["L_ERRORCODE0"]);
                $ErrorShortMsg = urldecode($resArray["L_SHORTMESSAGE0"]);
                $ErrorLongMsg = urldecode($resArray["L_LONGMESSAGE0"]);
                $ErrorSeverityCode = urldecode($resArray["L_SEVERITYCODE0"]);
                
                echo "SetExpressCheckout API call failed. ";
                echo "Detailed Error Message: " . $ErrorLongMsg;
                echo "Short Error Message: " . $ErrorShortMsg;
                echo "Error Code: " . $ErrorCode;
                echo "Error Severity Code: " . $ErrorSeverityCode;
              /*  ?><script>window.onload = function(){if(window.opener){window.close();}else{if(top.dg.isOpen() == true){top.dg.closeFlow();return true;}}};</script><?php */
        }
}else{
    ?><script>window.onload = function(){if(window.opener){window.opener.location.reload();window.close();}else{if(top.dg.isOpen() == true){top.dg.closeFlow();top.window.location.reload();return true;}}};</script><?php 
}


    
        
        
        
        ?></body></html><?php
    }
    
}
?>
