<?php
die();
include_once '/var/www/dev.mourjan/config/cfg.php';
include_once $config['dir'].'/core/model/Db.php';
require_once ('lib/phpmailer.class.php');

echo "\n",'Starting mailing process...',"\n";
$db=new DB($config);

$mail = new PHPMailer(true);
$mail->IsSMTP();
$res=0;
$mail->Host       = $config['smtp_server'];
$mail->SMTPAuth   = true;
$mail->Port       = $config['smtp_port'];
$mail->Username   = $config['smtp_user'];
$mail->Password   = $config['smtp_pass'];
$mail->SMTPSecure = 'ssl';
$mail->SetFrom($config['admin_email'][0], 'Mourjan.com');
$mail->CharSet='UTF-8';
$mail->Subject = 'Post your Ad With Mourjan.com';
$mail->Body    = '
<div class="main" style="padding:10px;font-family:"lucida grande","tahoma","arial";font-size:10pt"><div class="pane" style="border:1px solid #e9e9e9;overflow:hidden">
    <h1 style="background-color:#000;color:#fff;text-align:center;padding:5px;font-size:15pt">Mourjan.com</h1>
    <ul style="margin:0 10px;padding:10px 0;overflow:hidden;list-style:none">
        <li style="width:50%;float:left;direction:ltr;text-align:left;margin:0px;background-color:#e9e9e9" class="en">
            <h2 style="color:#900;font-size:13pt;margin-bottom:10px;padding: 5px">Post Your Ad For Free!</h2>
            <p style="line-height:1.7em;padding:5px">Mourjan.com\'s team is happy to announce the launch of its much anticipated <a href="'.$config['host'].'/post/en/">ad posting service</a> for its growing online community,</p>
            <p style="line-height:1.7em;padding:5px">if you have any feedback on how to improve our services, <a href="'.$config['host'].'/contact/en/">send us your opinion</a></p>
            <p style="line-height:1.7em;padding:5px">Thank you for your patience and best regards,</p>
            <p style="line-height:1.7em;padding:5px">
                Mourjan\'s Team<br />
                <a href="'.$config['host'].'/">www.mourjan.com</a><br />
                Twitter: @MourjanWeb
            </p>
        </li>
        <li style="width:50%;float:left;direction:rtl;text-align:right;margin:0px;font-size:11pt" class="ar">
            <h2 style="color:#900;font-size:13pt;margin-bottom:10px;padding: 5px">إنشر إعلانك مجانا!</h2>
            <p style="line-height:1.7em;padding:5px">يسر فريق عمل موقع مرجان‏ الإعلان عن تفعيل خدمة <a href="'.$config['host'].'/post/">إدراج الإعلان المجاني</a> لمستخدمي الموقع،</p>
            <p style="line-height:1.7em;padding:5px">إذا كان لديك أي ملاحظات حول كيفية تحسين خدماتنا، <a href="'.$config['host'].'/contact/">أرسل لنا رأيك</a></p>
            <p style="line-height:1.7em;padding:5px">شكراً على صبركم مع أطيب التمنيات،</p>
            <p style="line-height:1.7em;padding:5px">
                فريق عمل مرجان<br />
                <a style="direction:ltr;unicode-bidi:bidi-override" class="pn" href="'.$config['host'].'/">www.mourjan.com</a><br />
                Twitter: @MourjanWeb
            </p>
        </li>
    </ul>
    <div class="nb" style="color:#666;font-size:9pt">
        <ul style="margin:0 10px;padding:10px 0;overflow:hidden;list-style:none">
            <li style="width:50%;float:left;direction:ltr;text-align:left;margin:0px;background-color:#e9e9e9" class="en"><p style="padding:5px">Email Disclaimer: This email is sent to you as a registered user with mourjan.com</p></li>
            <li style="width:50%;float:left;font-size:11pt;direction:rtl;text-align:right;margin:0px" class="ar"><p style="padding:5px" >إخلاء المسؤولية: لقد تم إرسال هذه الرسالة لك بناءً على كونك مستخدم مسجل على موقع مرجان</p></li>
        <ul>
    </div>
</div></div>';
$mail->IsHTML(true);

$users=$db->queryResultArray('select * from web_users where id>27');
$usersCount=count($users);
$i=0;
foreach ($users as $user){
    if (preg_match('/@/', $user['EMAIL'])) {
        echo $user['ID'],"\t",$user['FULL_NAME'],"\t",$user['EMAIL'],"\n";
        if($i==0)$mail->AddAddress('mourjan@berysoft.com','info');
        $mail->AddBcc($user['EMAIL'],$user['FULL_NAME']);
        $i++;
    }
    if ($i>9) {
        $i=0;
        try {
            if ($mail->Send()) $res= 1;
        } catch (phpmailerException $e) {
            $res= 0;
        } catch (Exception $e) {
            $res= 0;
        }
        $mail->ClearAllRecipients();
    }
}
$db->close();
echo 'Mailing process Done!',"\n\n";
?>
