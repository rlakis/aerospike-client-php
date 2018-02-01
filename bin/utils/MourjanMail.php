<?php
require_once 'deps/phpmailer/phpmailer/PHPMailerAutoload.php';
use Core\Model\DB;

class MourjanMail extends PHPMailer 
{
    
    public 
            $user = null,$db=null,
            $templatePath   =  'include',
            $mLanguage,
            $debug=false,
            $dir='',
            $notifiers=array(),
            $notifierMailIndex=0,
            $notifierMailIndexDefault=0,
            $notifierMailIndexFile=null,
            $templates=array(),
            
            $emailHeader_en='
                <body style="padding:0;margin:0">
	<table width="100%" cellpadding="0" cellspacing="0">
    	<tr>
        	<td align="center">
            	<table width="600px" bgcolor="#FFFFFF" cellspacing="0" cellpadding="0">
                    <tr>
                    	<td width="100%" align="center" style="font-family:verdana,arial;font-size:13px;color:#999;padding:5px;">To ensure delivery to your inbox, add <b style="color:#666">noreply@mourjan.com</b> to your address book</td>
                    </tr>
                    <tr>
                    	<td width="100%">
                        	<table width="100%" background="{img_url}/fx.png" bgcolor="#143D55" cellspacing="0" cellpadding="5px" style="border:1px solid #CCC;border-collapse:collapse">
                            	<tr>
                                    <td align="left" style="font-family:verdana,arial;color:#FFF;font-size:20px;">{title}</td>
                                    <td height="50px" width="140px"><a href="{base_url}"><img src="{img_url}/eico_en.png" width="140" height="50" /></a></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
            	</table>
          	</td>
      	</tr>
            ',
            $emailHeader_ar='
                <body style="padding:0;margin:0">
	<table width="100%" cellpadding="0" cellspacing="0">
    	<tr>
        	<td align="center">
            	<table dir="rtl" style="direction:rtl" width="600px" bgcolor="#FFFFFF" cellspacing="0" cellpadding="0">
                    <tr>
                    	<td  width="100%" align="center" style="font-family:tahoma,arial;direction:rtl;font-size:13px;color:#999;padding:5px;">
                        حرصاً على وصول الرسائل على صندوق بريدك، عليك إضافة <b color="#666">noreply@mourjan.com</b> إلى دفتر عناوينك
                        </td>
                    </tr>
                    <tr>
                    	<td  width="100%">
                        	<table width="100%" dir="rtl" background="{img_url}/fx.png" bgcolor="#143D55" cellspacing="0" cellpadding="5px" style="direction:rtl;border:1px solid #CCC;border-collapse:collapse">
                            	<tr>
                                    <td align="right" style="padding-right:20px;font-family:tahoma,arial;direction:rtl;color:#FFF;font-size:21px;">{title}</td>
                                    <td height="50px" width="140px"><a href="{base_url}"><img src="{img_url}/eico.png" width="140" height="50" /></a></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
            	</table>
          	</td>
      	</tr>
            ',
            
            $plainFooter_en='
                <tr>
        	<td align="center">
                <table width="600px" bgcolor="#FFF" cellpadding="0" cellspacing="0"> 
                    <tr>
                    	<td align="center">
                        	<table bgcolor="#f5f5f5" width="100%" cellpadding="5px" cellspacing="0" style="color:#999;border:1px solid #CCC;border-collapse:collapse">
                            	<tr>
                                	<td width="50%" align="center" style="font-family:verdana,arial;font-size:13px;border-right:1px solid #CCC;line-height:25px">
                                    	<b style="color:#FF9000">Support</b><br />
                                        <a href="mailto:support@mourjan.com">support@mourjan.com</a>
				                    </td>
                                    <td align="center" style="font-family:verdana,arial;font-size:13px;line-height:25px">
                                    	<b style="color:#FF9000">Follow us</b><br />
                                        <a href="https://www.facebook.com/mourjan.ads"><img src="{img_url}/fb.png" /></a> 
                                        &nbsp;&nbsp;
                                        <a href="https://twitter.com/MourjanWeb"><img src="{img_url}/tw.png" /></a>
				                    </td>
                              	</tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                    	<td align="center">
                        	<table width="520px" cellpadding="5px" cellspacing="5px" style="color:#999">
                            	<tr>
                                	<td align="center" style="font-family:verdana,arial;font-size:13px;">
				                        © 2014 Mourjan.com Classifieds Aggregator - Le Point Center, Fouad Shehab Street, Dekwaneh, Lebanon
                                	</td>
                              	</tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
            ',
            
            $emailFooter_en='
                <tr>
        	<td align="center">
                <table width="600px" bgcolor="#FFF" cellpadding="0" cellspacing="0"> 
                    <tr>
                    	<td align="center">
                        	<table width="520px" cellspacing="10px" cellpadding="0">
                            	<tr valign="top">
                                	<td width="50%" bgcolor="#f5f5f5" style="border:1px solid #ccc">
                                            <table cellpading="0" cellspacing="0" width="100%">
                                            <tr>
                                                <td>
                                                    <table width="100%" height="170" style="height:170px" cellpadding="5px" cellspacing="0">
                                                    <tr valign="top">
                                                    <td style="font-family:verdana,arial;font-size:13px;color:#777;line-height:25px">
                                                                    <img width="50px" height="50px" src="{img_url}/ehe.png" style="float:left" />
                                                        <b style="color:#FF9000">Mourjan News</b><br />
                                                        <b>27/8/2013</b> Mourjan got smarter by displaying phone numbers formatted according to the user\'s location, making it easier for users to pick up the phone and call.
                                                    </td>
                                                    </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                            <td>
                                                <table width="100%" cellpadding="5px" cellspacing="0">
                                                    <tr>
                                                    <td align="center">
                                                	<table width="160px" bgcolor="#369" background="{img_url}/fx.png" style="border:1px solid #afafaf;background-color:#369" cellpadding="5px" cellspacing="0">
                                                    	<tr>
                                                          	<td align="center">
                                                              	<a href="{link_mourjan}" style="font-family:verdana,arial;line-height:normal;text-align:center;font-size:13px;display:block;padding:5px 0;color:#fff;text-decoration:none;">Mourjan.com</a>
                                                            </td>
                                                        </tr>
                                                 	</table>
                                                    </td>
                                                    </tr>
                                                </table>
                                            </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td bgcolor="#f5f5f5" style="border:1px solid #ccc">
                                        <table cellpading="0" cellspacing="0" width="100%">
                                            <tr>
                                            <td>
                                                <table width="100%" height="170" style="height:170px" cellpadding="5px" cellspacing="0">
                                                        <tr valign="top">
                                                        <td style="font-family:verdana,arial;font-size:13px;color:#777;line-height:25px">
                                                                        <img width="50px" height="50px" src="{img_url}/epe.png" style="float:left" />
                                                            <b style="color:#FF9000">Mourjan Plans</b><br />
                                                            Currently Mourjan team is planning and working on providing statistics for ad publishers based on the number of views their ads are receiving.
                                                        </td>
                                                        </tr>
                                                 </table>
                                            </td>
                                            </tr>
                                            <tr>
                                            <td>
                                                <table width="100%" cellpadding="5px" cellspacing="0">
                                                    <tr>
                                                        <td align="center">
                                                                <table width="160px" bgcolor="#369" background="{img_url}/fx.png" style="border:1px solid #afafaf;background-color:#369" cellpadding="5px" cellspacing="0">
                                                                <tr>
                                                                        <td align="center">
                                                                        <a href="{link_contact}" style="font-family:verdana,arial;line-height:normal;text-align:center;font-size:13px;display:block;padding:5px 0;color:#fff;text-decoration:none;">Make a Suggestion</a>
                                                                    </td>
                                                                </tr>
                                                                </table>
                                                        </td>
                                                        </tr>
                                                </table>
                                            </td>
                                            </tr>
                                            </table>
                                        

                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                    	<td>
                        	<br />
                        </td>
                    </tr>
                    <tr>
                    	<td align="center">
                        	<table bgcolor="#f5f5f5" width="100%" cellpadding="5px" cellspacing="0" style="color:#999;border:1px solid #CCC;border-collapse:collapse">
                            	<tr>
                                	<td width="50%" align="center" style="font-family:verdana,arial;font-size:13px;border-right:1px solid #CCC;line-height:25px">
                                    	<b style="color:#FF9000">Support</b><br />
                                        <a href="mailto:support@mourjan.com">support@mourjan.com</a>
				                    </td>
                                    <td align="center" style="font-family:verdana,arial;font-size:13px;line-height:25px">
                                    	<b style="color:#FF9000">Follow us</b><br />
                                        <a href="https://www.facebook.com/mourjan.ads"><img src="{img_url}/fb.png" /></a> 
                                        &nbsp;&nbsp;
                                        <a href="https://twitter.com/MourjanWeb"><img src="{img_url}/tw.png" /></a>
				                    </td>
                              	</tr>
                            </table>
                        </td>
                    </tr>
                	<tr>
                    	<td align="center">
                        	<table width="520px" cellpadding="5px" cellspacing="5px" style="color:#999">
                            	<tr>
                                	<td align="center" style="font-family:verdana,arial;font-size:13px;">
				                        © 2014 Mourjan.com Classifieds Aggregator - Le Point Center, Fouad Shehab Street, Dekwaneh, Lebanon
                                	</td>
                              	</tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
            ',
            
            $emailFooter_ar='
                <tr>
        	<td align="center">
                <table width="600px" bgcolor="#FFF" cellpadding="0" cellspacing="0"> 
                    <tr>
                    	<td align="center">
                        	<table width="520px" dir="rtl" style="direction:rtl" cellspacing="10px" cellpadding="0">
                            	<tr valign="top">
                                	<td width="50%" bgcolor="#f5f5f5" style="border:1px solid #ccc">
                                            <table cellpading="0" cellspacing="0" width="100%">
                                            <tr>
                                                <td>
                                                    <table width="100%" height="190" style="height:190px" cellpadding="5px" cellspacing="0">
                                                    <tr valign="top">
                                                    <td style="font-family:tahoma,arial;font-size:14px;color:#777;line-height:30px">
                                                                    <img width="50px" height="50px" src="{img_url}/eh.png" style="float:right" />
                                                        <b style="color:#FF9000">جديد مرجان</b><br />
                                                        <b>27/8/2013</b> أصبح مرجان أذكى من خلال عرض أرقام الهاتف بصيغة تناسب مكان تواجد المستخدم مما يسهل ويشجع القارئ على الإتصال.
                                                    </td>
                                                    </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                            <td>
                                                <table width="100%" cellpadding="5px" cellspacing="0">
                                                    <tr>
                                                    <td align="center">
                                                	<table width="160px" bgcolor="#369" background="{img_url}/fx.png" style="border:1px solid #afafaf;background-color:#369" cellpadding="5px" cellspacing="0">
                                                    	<tr>
                                                          	<td align="center">
                                                              	<a href="{link_mourjan}" style="font-family:tahoma,arial;line-height:normal;text-align:center;direction:rtl;font-size:14px;display:block;padding:5px 0;color:#fff;text-decoration:none;">موقع مرجان</a>
                                                            </td>
                                                        </tr>
                                                 	</table>
                                                    </td>
                                                    </tr>
                                                </table>
                                            </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td bgcolor="#f5f5f5" style="border:1px solid #ccc">
                                        <table dir="rtl" style="direction:rtl" cellpading="0" cellspacing="0" width="100%">
                                            <tr>
                                            <td>
                                                <table width="100%" height="190" style="height:190px" cellpadding="5px" cellspacing="0">
                                                        <tr valign="top">
                                                        <td style="font-family:tahoma,arial;font-size:14px;color:#777;line-height:30px">
                                                                        <img width="50px" height="50px" src="{img_url}/ep.png" style="float:right" />
                                                            <b style="color:#FF9000">مشاريع مرجان</b><br />
                                                            حالياً فريق عمل مرجان يعمل على توفير إحصائيات للمعلنين بعدد المشاهدات والتفاعلات الخاصة باعلاناتهم المنشورة.
                                                        </td>
                                                        </tr>
                                                 </table>
                                            </td>
                                            </tr>
                                            <tr>
                                            <td>
                                                <table width="100%" cellpadding="5px" cellspacing="0">
                                                    <tr>
                                                        <td align="center">
                                                                <table width="160px" bgcolor="#369" background="{img_url}/fx.png" style="border:1px solid #afafaf;background-color:#369" cellpadding="5px" cellspacing="0">
                                                                <tr>
                                                                        <td align="center">
                                                                        <a href="{link_contact}" style="font-family:tahoma,arial;line-height:normal;text-align:center;font-size:14px;display:block;padding:5px 0;color:#fff;text-decoration:none;">تقديم إقتراح</a>
                                                                    </td>
                                                                </tr>
                                                                </table>
                                                        </td>
                                                        </tr>
                                                </table>
                                            </td>
                                            </tr>
                                            </table>
                                        

                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                    	<td>
                        	<br />
                        </td>
                    </tr>
                    <tr>
                    	<td align="center">
                        	<table dir="rtl" bgcolor="#f5f5f5" width="100%" cellpadding="5px" cellspacing="0" style="direction:rtl;color:#999;border:1px solid #CCC;border-collapse:collapse">
                            	<tr>
                                	<td width="50%" align="center" style="font-family:tahoma,arial;direction:rtl;font-size:14px;border-left:1px solid #CCC;line-height:30px">
                                    	<b style="color:#FF9000">للتواصل</b><br />
                                        <a href="mailto:support@mourjan.com">support@mourjan.com</a>
				                    </td>
                                    <td align="center" style="font-family:tahoma,arial;direction:rtl;font-size:14px;line-height:30px">
                                    	<b style="color:#FF9000">تابعنا</b><br />
                                        <a href="https://www.facebook.com/mourjan.ads"><img src="{img_url}/fb.png" /></a> 
                                        &nbsp;&nbsp;
                                        <a href="https://twitter.com/MourjanWeb"><img src="{img_url}/tw.png" /></a>
				                    </td>
                              	</tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                    	<td align="center">
                        	<table width="520px" cellpadding="5px" cellspacing="5px" style="color:#999">
                            	<tr>
                                	<td align="center" style="font-family:verdana,arial;font-size:13px;">
				                        © 2014 Mourjan.com Classifieds Aggregator - Le Point Center, Fouad Shehab Street, Dekwaneh, Lebanon
                                	</td>
                              	</tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
            ',
            
            $plainFooter_ar='
            <tr>
        	<td align="center">
                <table width="600px" bgcolor="#FFF" cellpadding="0" cellspacing="0"> 
                    <tr>
                    	<td align="center">
                        	<table dir="rtl" bgcolor="#f5f5f5" width="100%" cellpadding="5px" cellspacing="0" style="direction:rtl;color:#999;border:1px solid #CCC;border-collapse:collapse">
                            	<tr>
                                	<td width="50%" align="center" style="font-family:tahoma,arial;direction:rtl;font-size:14px;border-left:1px solid #CCC;line-height:30px">
                                    	<b style="color:#FF9000">للتواصل</b><br />
                                        <a href="mailto:support@mourjan.com">support@mourjan.com</a>
				                    </td>
                                    <td align="center" style="font-family:tahoma,arial;direction:rtl;font-size:14px;line-height:30px">
                                    	<b style="color:#FF9000">تابعنا</b><br />
                                        <a href="https://www.facebook.com/mourjan.ads"><img src="{img_url}/fb.png" /></a> 
                                        &nbsp;&nbsp;
                                        <a href="https://twitter.com/MourjanWeb"><img src="{img_url}/tw.png" /></a>
				                    </td>
                              	</tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                    	<td align="center">
                        	<table width="520px" cellpadding="5px" cellspacing="5px" style="color:#999">
                            	<tr>
                                	<td align="center" style="font-family:verdana,arial;font-size:13px;">
				                        © 2014 Mourjan.com Classifieds Aggregator - Le Point Center, Fouad Shehab Street, Dekwaneh, Lebanon
                                	</td>
                              	</tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
            '
            
            ;


    function __construct($config, $lang='en', $debug=false){
        parent::__construct();
        $this->dir = $config['dir'];
        $this->notifiers    = $config['notifier_mail'];
        
        $this->IsSMTP();
        $this->debug=false;
        $this->SMTPAuth     = true;
        $this->SMTPSecure   = 'ssl';
        $this->Host         = $config['smtp_server'];
        $this->Port         = $config['smtp_port'];
        $this->Username     = $config['smtp_user'];
        $this->Password     = $config['smtp_pass'];
        $this->CharSet      = 'UTF-8';
        $this->SetFrom($config['smtp_user'], 'Mourjan.com');
        //$this->setLanguage($lan);
        $this->mLanguage=$lang;
        require_once $config['dir'].'/core/model/Db.php';
        require_once $config['dir'].'/core/model/User.php';
        $this->db = new DB($config);
        $this->db->setWriteMode(true);
        $this->user = new User($this->db,$config,null,0);
        $this->templatePath = $config['dir'].'/bin/utils/include';
        //$this->templatePath = '/var/www/dev.mourjan/bin/utils/include';
    }
    
    function doClearAll(){        
        $this->ClearAddresses();
        $this->ClearBCCs();
        $this->ClearCCs();
        $this->ClearAttachments();
        $this->ClearCustomHeaders();
        $this->ClearReplyTos();
    }    
    
    function MsgTemplate($template, $params=array(), $genParams=array(), $basedir = '', $plainFooter=0) {
        //echo __DIR__, "\n";
        global $config;
        $templateUri=$this->templatePath.'/'.($this->mLanguage!='en'?$this->mLanguage.'/':'').$template.'.html';
        if (!isset($this->templates[$templateUri])) {
            $this->templates[$templateUri]=file_get_contents($templateUri, true);
        }
        if (count($params)) {
            foreach ($params as $key => $value) {
                $$key = $value;
            }
        }
        $message=addcslashes($this->templates[$templateUri],'"');
        eval("\$message= \"$message\";");
        if (count($genParams)){
            if($this->mLanguage=='ar'){
                if($plainFooter)
                    $message = $this->emailHeader_ar.$message.$this->plainFooter_ar;
                else 
                    $message = $this->emailHeader_ar.$message.$this->emailFooter_ar;
            }else{
                if($plainFooter)
                    $message = $this->emailHeader_en.$message.$this->plainFooter_en;
                else 
                    $message = $this->emailHeader_en.$message.$this->emailFooter_en;
            }
            $genParams['base_url']=$config['host'].'/';
            foreach ($genParams as $key => $value){
                $message=preg_replace('/{'.$key.'}/', $value, $message);
            }
        }
        if ($this->debug) echo $message;
        parent::MsgHTML($message, $basedir);
    }
    
    function commentNotify($user,$comment,$adLink,$adTitle,$email,$pic,$rtl,$userId,$username=''){
        global $config;
        $imgUrlLink=$config['url_resources'].$config['url_img'];
        $this->doClearAll();
        $this->Subject='New comment on your ad';
        $this->AddAddress($email,$username);
        //$this->AddBCC('admin@berysoft.com');
        //$this->AddBCC('bassel@mourjan.com');
        
        $content = $this->getAdTextTable($adTitle, $rtl, $pic);
        if (preg_match('/[\x{0621}-\x{0669}]/u', $comment)){
            $comment='<p style="direction:rtl;text-align:center;color:#666;font-size:19px;font-style:italic;font-family:tahoma,arial;line-height:30px">"'.$comment.'"</p>';
        }else{
            $comment='<p style="text-align:center;direction:ltr;color:#666;font-size:18px;font-style:italic;font-family:verdana,arial;line-height:25px">"'.$comment.'"</p>';
        }
        
        $addLang= ($this->mLanguage =='ar' ? '' :$this->mLanguage.'/');
        
        $myAccountKey = $this->user->encodeRequest('my_account',array($userId));
        $myAdsKey = $this->user->encodeRequest('my_ads',array($userId));
        
        $link_acc= $config['host'].'/a/'.$addLang.'?k='.$myAccountKey;
        $link_ads= $config['host'].'/a/'.$addLang.'?k='.$myAdsKey;
        
        $params=array(
            'username'  =>  $username,
            'user'      =>  $user,
            'comment'   =>  $comment,
            'adLink'    =>  $adLink,
            'content'   =>  $content,
            'link_account'  =>  $link_acc,
            'link_ads'  =>  $link_ads,
            'img_url'   =>  $imgUrlLink
        );
        $genParams=array(
            'img_url'   =>  $imgUrlLink,
            'title'     =>  ($this->mLanguage == 'ar' ? 'إشعار بتعليق جديد على اعلانك' :'New Comment Notification')
        );
        $this->MsgTemplate('ad-comment',$params, $genParams, '', 1);
        if($this->debug) return 1;
        else return $this->Send();
    }

    function sendEmailValidation($userEmail,$verifyLink,$userName=''){
        global $config;
        $imgUrlLink=$config['url_resources'].$config['url_img'];
        $this->doClearAll();
        $this->Username = 'account@mourjan.com';
        $this->SetFrom('account@mourjan.com', 'Mourjan.com');
        $this->Subject='Please verify your email';
        $this->AddAddress($userEmail,$userName);
        $params=array(
            'username'  =>  $userName,
            'useremail' =>  $userEmail,
            'verifyLink'=>  $verifyLink,
            'img_url'   =>  $imgUrlLink
        );        
        $genParams=array(
            'img_url'   =>  $imgUrlLink,
            'title'     =>  ($this->mLanguage == 'ar' ? 'تأكيد ملكية عنوان البريد الإلكتروني' :'Email Verification')
        );
        $this->MsgTemplate('email-verification',$params,$genParams,'',1);
        if($this->debug) return 1;
        else return $this->Send();
    }
    
    function sendEmailCode($userEmail,$verifyLink){
        global $config;
        $imgUrlLink=$config['url_resources'].$config['url_img'];
        $this->doClearAll();
        $this->Username = 'account@mourjan.com';
        $this->SetFrom('account@mourjan.com', 'mourjan.com');
        $this->Subject='Please verify your email';
        $this->AddAddress($userEmail);
        $params=array(
            'useremail' =>  $userEmail,
            'verifyLink'=>  $verifyLink,
            'img_url'   =>  $imgUrlLink
        );        
        $genParams=array(
            'img_url'   =>  $imgUrlLink,
            'title'     =>  ($this->mLanguage == 'ar' ? 'تأكيد ملكية عنوان البريد الإلكتروني' :'Email Verification')
        );
        $this->MsgTemplate('email-code-verification',$params,$genParams,'',1);
        if($this->debug) return 1;
        else return $this->Send();
    }
    
    function sendNewAccount($userEmail,$verifyLink){
        global $config;
        $imgUrlLink=$config['url_resources'].$config['url_img'];
        $this->doClearAll();
        $this->Username = 'account@mourjan.com';
        $this->SetFrom('account@mourjan.com', 'Mourjan.com');
        $this->Subject='Welcome to Mourjan';
        $this->AddAddress($userEmail,'');
        $params=array(
            'useremail' =>  $userEmail,
            'verifyLink'=>  $verifyLink,
            'img_url'   =>  $imgUrlLink
        );        
        $genParams=array(
            'img_url'   =>  $imgUrlLink,
            'title'     =>  ($this->mLanguage == 'ar' ? 'تفعيل الحساب' :'Account Activation')
        );
        $this->MsgTemplate('account-verification',$params,$genParams,'',1);
        if($this->debug) return 1;
        else return $this->Send();
    }
    
    function sendResetPass($userEmail,$verifyLink){
        global $config;
        $imgUrlLink=$config['url_resources'].$config['url_img'];
        $this->doClearAll();
        $this->Username = 'account@mourjan.com';
        $this->SetFrom('account@mourjan.com', 'Mourjan.com');
        $this->Subject='Password Reset Request';
        $this->AddAddress($userEmail,'');
        $params=array(
            'useremail' =>  $userEmail,
            'verifyLink'=>  $verifyLink,
            'img_url'   =>  $imgUrlLink
        );        
        $genParams=array(
            'img_url'   =>  $imgUrlLink,
            'title'     =>  ($this->mLanguage == 'ar' ? 'إعادة تعيين كلمة السر' :'Password Reset')
        );
        $this->MsgTemplate('password-reset',$params,$genParams,'',1);
        if($this->debug) return 1;
        else return $this->Send();
    }
    
    function sendPageEmailValidation($userEmail,$verifyLink,$userName=''){
        $this->doClearAll();
        $this->Username = 'account@mourjan.com';
        $this->SetFrom('account@mourjan.com', 'Mourjan.com');
        $this->Subject='Please verify the contact email address for your Mourjan page';
        $this->AddAddress($userEmail,$userName);
        $params=array(
            'username'  =>  $userName,
            'useremail' =>  $userEmail,
            'verifyLink'=>  $verifyLink
        );
        $this->MsgTemplate('bmail-verification',$params);
//        if($this->debug) return 1;
//        else return $this->Send();
    }
    
    function getAdTextTable($text,$rtl,$pic){
        global $config;
        $imgUrlLink=$config['url_resources'].$config['url_img'];
        return '
            <tr>
                <td>
                    <table width="100%" '.($rtl ? 'dir="rtl"':'dir="ltr"').' cellpadding="5px" style="'.($rtl ? 'direction:rtl;':'').'border-collapse:collapse;border:1px solid #ccc" background="'.$imgUrlLink.'/abg.jpg">
                    	<tr valign="top">
                            <td width="120px" align="center">'.$pic.'</td>
                            <td style="'.($rtl ? 'color:#333;line-height:30px;direction:rtl;text-align:right;font-family:tahoma,arial;font-size:14px;':'color:#333;line-height:25px;direction:ltr;text-align:left;font-family:verdana,tahoma;font-size:13px;').'">'.$text.'</td>
                        </tr>
                    </table>
                </td>
            </tr>
        ';
    }
    
    
    function newAdsNotices($ads, $userId, $email, $username, $lang, $total){
        global $config;
        $imgUrlLink=$config['url_resources'].$config['url_img'];
        $this->doClearAll();
        $this->mLanguage=$lang;        
        $this->Subject='New Ads in your watchlist';
        $this->AddAddress($email,$username);
        //$this->AddBCC('admin@berysoft.com');
        //$this->AddBCC('mourjan@gmail.com');
        
        $adCount = $total;
        if($lang=='ar'){
            if($total==1){
                $adCount='اعلان واحد جديد';
            }elseif($total==2){
                $adCount='اعلانين جدد';
            }elseif($total>2 && $total<11){
                $adCount.=' اعلانات جديدة';
            }else{
                $adCount.=' اعلان جديد';
            }
            $fontSize=14;
            $fontFamily='tahoma,arial';
        }else{
            if($total==1) $adCount.=' new ad';
            else $adCount.=' new ads';
            $fontSize=13;
            $fontFamily='verdana,arial';
        }
        
        $addLang= ($this->mLanguage =='ar' ? '' :$this->mLanguage.'/');
        $content='';
        
        foreach ($ads as $ad){
            $pic='';
            if (isset($ad[Classifieds::VIDEO]) && $ad[Classifieds::VIDEO] && count($ad[Classifieds::VIDEO])) {
                $pic = '<span style="height:110px;overflow:hidden;"><img width="110" src="'.$ad[Classifieds::VIDEO][2].'" /></span>';
            }elseif ($ad[Classifieds::PICTURES] && count($ad[Classifieds::PICTURES])) {
                $pic = $ad[Classifieds::PICTURES][0];
                $pic = '<span style="height:110px;overflow:hidden;"><img width="110" src="'.$config['url_ad_img'].'/repos/d/' . $pic.'" /></span>';
            }else{
                $pic='<img width="90" height="90" src="'.$imgUrlLink.'/90/'.$ad[Classifieds::SECTION_ID].'.png" />';
            }
            $content.=$this->getAdTextTable($ad[Classifieds::CONTENT], $ad[Classifieds::RTL], $pic);
            $sectionTitle=preg_replace('/\s-\s(?:page|صفحة)\s[0-9]*$/','',$ad['channel_title']);
            $cnt=$ad['channel_count']-1;
            $sectionTitle=preg_replace('/<.*?>/', '', $sectionTitle);
            $channelKey = $this->user->encodeRequest('channel',array($userId,$ad['channel_id']));
            $channelLink = $config['host'].'/a/'.$addLang.'?k='.$channelKey;
            if (preg_match('/[\x{0621}-\x{0669}]/u', $sectionTitle)){
                $sectionTitle='<a href="'.$channelLink.'" style="font-weight:bold;font-family:tahoma,arial;font-size:14px;direction:rtl;">'.$sectionTitle.'</a>';
            }else {
                $sectionTitle='<a href="'.$channelLink.'" style="font-weight:bold;font-family:verdana,arial;font-size:13px;direction:rtl;">'.$sectionTitle.'</a>';
            }
            $ad['channel_title']=$sectionTitle;
            if($cnt){
                if($this->mLanguage=='ar'){                                        
                    $sectionTitle='بالاضافة الى';
                    if($cnt==1){
                        $sectionTitle.=' اعلان آخر';
                    }elseif($cnt==2){
                        $sectionTitle.=' اعلانين آخرين';
                    }elseif($cnt>2 && $cnt<11){
                        $sectionTitle.=' '.$cnt.' اعلانات اخرى';
                    }else{
                        $sectionTitle.=' '.$cnt.' اعلان آخر';
                    }
                    $sectionTitle.=' في '.$ad['channel_title'];
                }else{                    
                    $sectionTitle='and';
                    if($cnt==1){
                        $sectionTitle.=' 1 more ad';
                    }else{
                        $sectionTitle.=' '.$cnt.' more ads';
                    }
                    $sectionTitle.=' in '.$ad['channel_title'];
                }
            }
            $content.='
                    <tr>
                        <td bgcolor="#f5f5f5" background="'.$imgUrlLink.'/fx.png">
                            <table width="100%" '.($this->mLanguage=='ar' ? 'dir="rtl"':'dir="ltr"').' cellpadding="5px" cellspacing="0" style="'.($this->mLanguage=='ar' ? 'direction:rtl;':'').'border-collapse:collapse;border:1px solid #ccc">
                                <tr>
                                    <td style="color:#333;font-family:'.$fontFamily.';font-size:'.$fontSize.'px;">'.$sectionTitle.'</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr><td>&nbsp;<br /></td></tr>
                    ';
        }
        
        $contactKey = $this->user->encodeRequest('contact',array($userId));
        $myWatchKey = $this->user->encodeRequest('my_watch',array($userId));
        
        $link_contact= $config['host'].'/a/'.$addLang.'?k='.$contactKey;
        $link_watch= $config['host'].'/a/'.$addLang.'?k='.$myWatchKey;
        
        $params=array(
            'ad_count'  =>  $adCount,
            'username'  =>  $username,
            'content'   =>  $content,
            'link_contact'  =>  $link_contact,
            'link_watch'  =>  $link_watch,
            'img_url'   =>  $imgUrlLink
        );
        $genParams=array(
            'img_url'   =>  $imgUrlLink,
            'title'     =>  ($this->mLanguage == 'ar' ? 'إعلانات جديدة في لائحة المراقبة' :'New Ads in Watchlist')
        );
        $this->MsgTemplate('ad-notification',$params, $genParams, '', 1);
        if($this->debug) return 1;
        else return $this->Send();
    }
    
    function getAdTabloids($ad,$expired=false){
        global $config;
        $imgUrlLink=$config['url_resources'].$config['url_img'];
        
        $adContent=json_decode($ad['EXT_CONTENT'],true); 
        
        if (isset($adContent['video']) && is_array($adContent['video']) && count($adContent['video'])) {
            $pic = '<span style="height:110px;overflow:hidden;"><img width="110" src="'.$adContent['video'][2].'" /></span>';
        }elseif (isset($adContent['pics']) && is_array($adContent['pics']) && count($adContent['pics'])
                && isset($adContent['pic_def']) && $adContent['pic_def']) {
                    $pic = $adContent['pic_def'];
                    $pic = '<span style="height:110px;overflow:hidden;"><img width="110" src="'.$config['url_ad_img'].'/repos/d/' . $pic.'" /></span>';
        }else{
            $pic='<img width="90" height="90" src="'.$imgUrlLink.'/90/'.$ad['SECTION_ID'].'.png" />';
        }
        
                
        $content='
        <tr>
            <td align="center">
                <table width="520px" cellpadding="0" cellspacing="0"> 
        ';
        //render main text
        $content.=$this->getAdTextTable($ad['CONTENT'], $ad['RTL'], $pic);
        //render translation if any
        if(!empty($ad['T_CONTENT']))$content.=$this->getAdTextTable($ad['T_CONTENT'], 0, $pic);
        //render summery        
        
        $fieldIndex=2;
        $comma=', ';
        
        
        $fontSize=13;
        $fontFamily='verdana,arial';
        
        if($this->mLanguage=='ar'){
            $fieldIndex=1;
            $comma='، ';
            $fontSize=14;
            $fontFamily='tahoma,arial';            
            $expiry_date=date('d/m/Y',strtotime($ad['EXPIRY_DATE']));//to set
            $section = $this->parsePageLabel($ad['SECTION_NAME_AR'],$ad['PURPOSE_ID'],$ad['PURPOSE_NAME_AR']);
        }else {
            $expiry_date=date('l jS \of F Y',strtotime($ad['EXPIRY_DATE']));//to set
            $section = $this->parsePageLabel($ad['SECTION_NAME_EN'],$ad['PURPOSE_ID'],$ad['PURPOSE_NAME_EN']);
        }
        
        $addLang= ($this->mLanguage =='ar' ? '' :$this->mLanguage.'/');
        if ($expired){
            $sKey=$this->user->encodeRequest('my_archive',array($ad['WEB_USER_ID']));
            $link_1= $config['host'].'/a/'.$addLang.'?k='.$sKey;
            $link_1='<a href="'.$link_1.'" style="text-align:center;font-family:'.$fontFamily.';font-size:'.$fontSize.'px;display:block;padding:5px 0;color:#fff;text-decoration:none;">'.($this->mLanguage =='ar' ? 'أرشيف الإعلانات' :'My Archive').'</a>';
            
            $sKey=$this->user->encodeRequest('ad_renew',array($ad['WEB_USER_ID'],$ad['REFERENCE']));
            $link_2=$config['host'].'/a/'.$addLang.'?k='.$sKey;
            $link_2='<a href="'.$link_2.'" style="text-align:center;font-family:'.$fontFamily.';font-size:'.$fontSize.'px;display:block;padding:5px 0;color:#fff;text-decoration:none;">'.($this->mLanguage =='ar' ? 'تجديد النشر' :'Renew Ad').'</a>';
        }else{
            $sKey=$this->user->encodeRequest('ad_page',array($ad['WEB_USER_ID'],$ad['REFERENCE']));
            $link_1=$config['host'].'/a/'.$addLang.'?k='.$sKey;
            $link_1='<a href="'.$link_1.'" style="text-align:center;font-family:'.$fontFamily.';font-size:'.$fontSize.'px;display:block;padding:5px 0;color:#fff;text-decoration:none;">'.($this->mLanguage =='ar' ? 'صفحة الإعلان' :'Visit Ad Page').'</a>';
            
            $sKey=$this->user->encodeRequest('ad_stop',array($ad['WEB_USER_ID'],$ad['REFERENCE']));
            $link_2=$config['host'].'/a/'.$addLang.'?k='.$sKey;
            $link_2='<a href="'.$link_2.'" style="text-align:center;font-family:'.$fontFamily.';font-size:'.$fontSize.'px;display:block;padding:5px 0;color:#fff;text-decoration:none;">'.($this->mLanguage =='ar' ? 'إيقاف العرض' :'Stop Ad').'</a>';
        }
        
        
        $countries = $this->db->getCountriesDictionary();
        if (isset($adContent['pubTo'])) 
        {
            $countriesArray=array();
            $cities=$this->db->getCitiesDictionary();
            
            foreach ($adContent['pubTo'] as $city => $value)
            {
                if (isset($cities[$city])) 
                {
                    if (!isset($countriesArray[ $cities[$city][5] ]))
                    {
                        $ccs = $countries[ $cities[$city][5] ][6];   // $this->db->getCountryCities($cities[$city][5], $this->mLanguage);
                        if ($ccs && count($ccs)>1)
                        {
                            $countriesArray[$cities[$city][5]] = [$countries[ $cities[$city][5] ][$fieldIndex], []];
                        }
                        else 
                        {
                            $countriesArray[$cities[$city][5]] = [$countries[ $cities[$city][5] ][$fieldIndex], false];
                        }
                    }
                    
                    if ($countriesArray[$cities[$city][5]][1]!==false) $countriesArray[$cities[$city][5]][1][]=$cities[$city][$fieldIndex];
                }
            }
            
            $i=0;
            foreach ($countriesArray as $key => $value) 
            {
                if ($i)$section.=' - '.$value[0];
                else $section.=' '.($this->mLanguage=='ar'?'في':'in').' '.$value[0];
                if ($value[1]!==false) $section.='('.implode ($comma, $value[1]).')';
                $i++;
            }
        } 
        else 
        {
            $ccs = $countries[$ad['COUNTRY_ID']][6]; // $this->db->getCountryCities($ad['COUNTRY_ID'], $this->mLanguage);
            if ($ccs && count($ccs)>1)
            {
                $section.=' '.($this->mLanguage=='ar'?'في '.$ad['CITY_NAME_AR']:'in '.$ad['CITY_NAME_EN']).$comma;
            }
            $section.=' '.($this->mLanguage=='ar'?'في '.$ad['COUNTRY_NAME_AR']:'in '.$ad['COUNTRY_NAME_EN']);
        }
        
        
        
        $content.='
                    <tr>
                        <td bgcolor="#f5f5f5" background="'.$imgUrlLink.'/fx.png">
                            <table width="100%" '.($this->mLanguage=='ar' ? 'dir="rtl"':'dir="ltr"').' cellpadding="5px" cellspacing="0" style="'.($this->mLanguage=='ar' ? 'direction:rtl;':'').'border-collapse:collapse;border:1px solid #ccc">
                                <tr valign="top">
                                    <td width="100px" style="color:#333;font-weight:bold;font-family:'.$fontFamily.';font-size:'.$fontSize.'px;">'.($this->mLanguage=='ar' ? 'نشر في':'Published in').':</td>
                                    <td style="color:#333;font-family:'.$fontFamily.';font-size:'.$fontSize.'px;">'.$section.'</td>
                                </tr>
                                <tr>
                                    <td width="100px" style="color:#333;font-family:'.$fontFamily.';font-size:'.$fontSize.'px;"><b>'.($this->mLanguage=='ar' ? 'صالح لغاية':'Valid Until').':</b></td>
                                    <td style="color:#333;font-weight:bold;font-family:'.$fontFamily.';font-size:'.$fontSize.'px;">'.($expired ? '<b style="color:red" style="font-family:'.$fontFamily.';font-size:'.$fontSize.'px;">'.($this->mLanguage == 'ar' ? 'منتهي الصلاحية':'Expired').'</b>' : $expiry_date).'</td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <table width="100%" cellpadding="5px" cellspacing="0">
                                            <tr>
                                             	<td width="50%" align="center">
                                                    <table width="160px" bgcolor="#369" background="'.$imgUrlLink.'/fx.png" style="border:1px solid #afafaf;background-color:#369" cellpadding="5px" cellspacing="0">
                                                    	<tr>
                                                            <td align="center">
                                                                '.$link_1.'
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td width="50%" align="center">
                                                    <table width="160px" bgcolor="#'.($expired ? '41B419': 'a00').'" background="'.$imgUrlLink.'/fx.png" style="border:1px solid #afafaf;background-color:#'.($expired ? '41B419': 'a00').'" cellpadding="5px" cellspacing="0">
                                                      	<tr>
                                                            <td align="center">
                                                                '.$link_2.'                                                               	
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>                                                	
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        ';
            return $content;
    }
    
    function adActivated($ads){
        global $config;
        $imgUrlLink=$config['url_resources'].$config['url_img'];
        $this->doClearAll();
        $content='';
        $i=0;
        $email='';
        $name='';
        $count = count($ads);
        foreach($ads as $key => $ad){
            if (!$i){
                $email=trim($ad['USER_EMAIL']);
                if(!$email)$email=trim($ad['EMAIL']);
                if(!$email)return 1;
                $name=$ad['USER_NAME']; 
                $opts=json_decode($ad['OPTS'],true);
                if (isset($opts['lang'])) {
                    $this->mLanguage=$opts['lang'];
                }
            }
            if($count > 1) $content.='<tr><td align="center" style="font-family:verdana,arial;color:#999;font-size:22px;padding:5px 0">- '.($i+1).' -</td></tr>';
            $content.=$this->getAdTabloids($ad);
            $i++;
        }

        $this->AddAddress($email,$name);
        //$this->AddBCC('admin@berysoft.com');
        //$this->AddBCC('bassel@mourjan.com');
        
        $addLang= ($this->mLanguage =='ar' ? '' :$this->mLanguage.'/');
        
        $myAccountKey = $this->user->encodeRequest('my_account',array($ad['WEB_USER_ID']));
        $myAdsKey = $this->user->encodeRequest('my_ads',array($ad['WEB_USER_ID']));
        $mourjanKey = $this->user->encodeRequest('home',array($ad['WEB_USER_ID']));
        $contactKey = $this->user->encodeRequest('contact',array($ad['WEB_USER_ID']));
        
        $link_acc= $config['host'].'/a/'.$addLang.'?k='.$myAccountKey;
        $link_ads= $config['host'].'/a/'.$addLang.'?k='.$myAdsKey;
        $link_mourjan=$config['host'].'/a/'.$addLang.'?k='.$mourjanKey;
        $link_contact= $config['host'].'/a/'.$addLang.'?k='.$contactKey;
        
        $params=array(
            'content'       =>  $content,
            'link_account'  =>  $link_acc,
            'link_ads'  =>  $link_ads,
            'img_url'   =>  $imgUrlLink
        );
        $genParams=array(
            'link_contact'  =>  $link_contact,
            'link_mourjan'  =>  $link_mourjan,
            'img_url'   =>  $imgUrlLink
        );
        if (count($ads)>1) {
            $genParams['title']=  ($this->mLanguage == 'ar' ? 'إشعار بنشر الإعلانات' :'Published Ads Notification');
            $this->Subject='Your ads are approved and published';
            $this->MsgTemplate('ads-published',$params, $genParams,'',1);
        }else {      
            $genParams['title']=  ($this->mLanguage == 'ar' ? 'إشعار بنشر الإعلان' :'Published Ad Notification');
            $this->Subject='Your ad is approved and published';
            $this->MsgTemplate('ad-published',$params, $genParams,'',1);
        }
        if($this->debug) return 1;
        else return $this->Send();
    }


    function adRetired($ads){
        global $config;
        $imgUrlLink=$config['url_resources'].$config['url_img'];
        $this->doClearAll();
        $content='';
        $i=0;
        $email='';
        $name='';
        $count = count($ads);
        foreach($ads as $key => $ad){
            if (!$i){
                $email=trim($ad['USER_EMAIL']);
                if(!$email)$email=trim($ad['EMAIL']);
                if(!$email)return 1;
                $name=$ad['USER_NAME'];
                $opts=json_decode($ad['OPTS'],true);
                if (isset($opts['lang'])) {
                    $this->mLanguage=$opts['lang'];
                }
            }
            if($count > 1) $content.='<tr><td align="center" style="font-family:verdana,arial;color:#999;font-size:22px;padding:5px 0">- '.($i+1).' -</td></tr>';
            $content.=$this->getAdTabloids($ad,true);
            $i++;
        }
        
        $this->AddAddress($email, $name);
        //$this->AddBCC('bassel@mourjan.com');
        //$this->AddBCC('admin@berysoft.com');
        //$this->AddBCC('mourjan@gmail.com');
        
        $addLang= ($this->mLanguage =='ar' ? '' :$this->mLanguage.'/');
        
        $myAccountKey = $this->user->encodeRequest('my_account',array($ad['WEB_USER_ID']));
        $myArchiveKey = $this->user->encodeRequest('my_archive',array($ad['WEB_USER_ID']));
        $mourjanKey = $this->user->encodeRequest('home',array($ad['WEB_USER_ID']));
        $contactKey = $this->user->encodeRequest('contact',array($ad['WEB_USER_ID']));
        
        $link_acc= $config['host'].'/a/'.$addLang.'?k='.$myAccountKey;
        $link_archive= $config['host'].'/a/'.$addLang.'?k='.$myArchiveKey;
        $link_mourjan=$config['host'].'/a/'.$addLang.'?k='.$mourjanKey;
        $link_contact= $config['host'].'/a/'.$addLang.'?k='.$contactKey;
        
        $params=array(
            'content'       =>  $content,
            'link_account'  =>  $link_acc,
            'link_archive'  =>  $link_archive,
            'img_url'   =>  $imgUrlLink
        );
        $genParams=array(
            'link_contact'  =>  $link_contact,
            'link_mourjan'  =>  $link_mourjan,
            'img_url'   =>  $imgUrlLink
        );
        if (count($ads)>1) {
            $genParams['title']=  ($this->mLanguage == 'ar' ? 'إشعار بإنتهاء مدة عرض الإعلانات' :'Expired Ads Notification');
            $this->Subject='Your ads have expired';
            $this->MsgTemplate('ads-expired',$params, $genParams,'',1);
        }else {      
            $genParams['title']=  ($this->mLanguage == 'ar' ? 'إشعار بإنتهاء مدة عرض الإعلان' :'Expired Ad Notification');
            $this->Subject='Your ad has expired';
            $this->MsgTemplate('ad-expired',$params, $genParams,'',1);
        }
        if($this->debug) return 1;
        else return $this->Send();
    }
    
    function notifyPageUpgrade($userEmail, $pageLink, $userName='', $lang='ar'){
        $this->doClearAll();
        $this->mLanguage=$lang;
        $this->Username = 'account@mourjan.com';
        $this->SetFrom('account@mourjan.com', 'Mourjan.com');
        $this->Subject='Your Account is Special with Mourjan';
        $this->AddAddress($userEmail,$userName);
        //$this->AddBCC('mourjan@gmail.com');
        $params=array(
            'username'  =>  $userName,
            'pageLink'  =>  $pageLink.($this->mLanguage=='en' ? 'en/':'')
        );
        $this->MsgTemplate('page-upgrade',$params);
//        if($this->debug) return 1;
//        else return $this->Send();
    }
    
    
    function parsePageLabel($sname, $purposeId, $pname){
                switch($purposeId){
                    case 1://for sale
                    case 2://for rent
                    case 8://for trade
                        $sname = $sname.' '.$pname;
                        break;
                    case 999://various
                        if($this->mLanguage=='ar'){                    
                            $sname = ($sname == 'متفرقات' ? $sname :  $sname.' متفرقة');
                        }else{
                            $sname = ( (strpos($sname,'Misc.')===false || $sname == 'Miscellaneous') ? $sname : 'Misc. '.$sname);
                        }
                        break;
                    case 6://to rent
                    case 7://to buy
                        if($this->mLanguage=='ar'){                    
                            $sname = $pname.' '.$sname ;
                        }else{
                            $sname = 'Looking '.$pname.' '.$sname;
                        }
                        break;
                    case 3://vacancies
                        if($this->mLanguage=='ar'){                    
                            $sname = $pname.' '.$sname;
                        }else{
                            $sname = $sname.' '.$pname;
                        }
                        break;
                    case 4://seeking work
                        $in='';
                        if ($this->mLanguage=="en")$in=" {$this->lang['in']}";
                        $sname= $pname.$in.' '.$sname;
                        break;
                    case 5://services
                        if($this->mLanguage=='ar'){                    
                            $sname = ( strpos($sname,$pname)===false ? $pname .' '.$sname : $sname);
                        }else{
                            $sname = ( strpos($sname,$pname)===false ? $sname.' '.$pname : $sname);
                        }                        
                        break;
                }
        return $sname;
    }
    
    function Send() {
        if($this->Username == 'account@mourjan.com'){
            $sent = parent::Send();
            return $sent;
        }else{
            if ($this->debug) {
                echo "\n------------------------------------------------------\n";
                echo 'sending mail',"\n";
            }
            if(!$this->notifierMailIndexFile){                
                $this->getNotifierMailIndex();
                $this->notifierMailIndexDefault=$this->notifierMailIndex;
                $this->Username     = $this->notifiers[$this->notifierMailIndex];
                $this->SetFrom($this->Username, 'Mourjan.com');
            }
            $sent = parent::Send();
            
            if ($this->debug) {
                echo 'sending by ',"{$this->Username}\n";
            }

            if($sent){
                if ($this->debug) {
                    echo 'sending ok',"\n";
                    echo "\n------------------------------------------------------\n";
                }
                if($this->notifierMailIndex!=$this->notifierMailIndexDefault){
                    $this->setNotifierMailIndex();
                }
                try{
                    if($this->notifierMailIndexFile)
                        @fclose($this->notifierMailIndexFile);
                }catch(Exception $ex){
                    syslog(LOG_NOTICE, var_export($ex));
                }
                return 1;
            }else{
                $this->notifierMailIndex++;
                $notifiersCount = count($this->notifiers);
                $this->notifierMailIndex = $this->notifierMailIndex % $notifiersCount;
                if($this->notifierMailIndex == $this->notifierMailIndexDefault){
                    if($this->notifierMailIndexFile)
                        @fclose($this->notifierMailIndexFile);
                    if ($this->debug) {
                        echo 'sending failed',"\n";
                        echo "\n------------------------------------------------------\n";
                    }
                    return 0;
                }else{
                    $this->Username     = $this->notifiers[$this->notifierMailIndex];
                    $this->SetFrom($this->Username, 'Mourjan.com');
                    $this->Send();
                }
            }
        }
    }
    
    function getNotifierMailIndex(){
        $filename=$this->dir.'/config/notifier_mail_index';
        $this->notifierMailIndexFile=fopen($filename, 'r+');
        $stream=fread($this->notifierMailIndexFile, 1024);
        $this->notifierMailIndex=(int)$stream;
        $this->notifierMailIndex= $this->notifierMailIndex ? $this->notifierMailIndex : 0;
        return $this->notifierMailIndex;
    }
    
    function setNotifierMailIndex(){
        fseek($this->notifierMailIndexFile, 0);
        fwrite($this->notifierMailIndexFile, $this->notifierMailIndex);
    }
}
?>