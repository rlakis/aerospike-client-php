<?php
include_once get_cfg_var('mourjan.path').'/deps/autoload.php';

use MaxMind\Db\Reader;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header("Access-Control-Allow-Origin: https://www.edigear.com", false);


function getIpLocation($ip=NULL) 
{
    if (empty($ip)) 
    {
        if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else 
        {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
    }

    $databaseFile = '/home/db/GeoLite2-City.mmdb';
    $reader = new \MaxMind\Db\Reader($databaseFile);
    $ips = explode(',', $ip);
    foreach ($ips as $addr) 
    {
        $geo = $reader->get(trim($addr));
        if (isset($geo['country'])) break;
    }
    $reader->close();

    //error_log(json_encode($geo));
    return $geo;
}

$res=0;

$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
$company = filter_input(INPUT_POST, 'company', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
$phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
$message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);

if($name && $email && $message){

$geo = getIpLocation();  
$geostr = "";
if (isset($geo['country']) && isset($geo['country']['names']) && isset($geo['country']['names']['en'])){
    $geostr.= $geo['country']['names']['en'];
}

if (isset($geo['location']) && isset($geo['location']['time_zone'])){
    $geostr.= " - {$geo['location']['time_zone']} [{$geo['location']['latitude']}, {$geo['location']['longitude']}]";
}

$msg = "<table style='width:100%'>";
$msg .= "<tr><td width='150px'><b>Name:</b></td><td>{$name}</td></tr>";
$msg .= "<tr><td width='150px'><b>Company:</b></td><td>{$company}</td></tr>";
$msg .= "<tr><td width='150px'><b>Email:</b></td><td>{$email}</td></tr>";
$msg .= "<tr><td width='150px'><b>Phone:</b></td><td>{$phone}</td></tr>";
$msg .= "<tr><td width='150px'><b>Geo:</b></td><td>{$geostr}</td></tr>";
$msg .= "<tr><td colspan='2'><br />{$message}</td></tr>";
$msg .= "</table>";

$mail = new \PHPMailer(true);
$mail->IsSMTP();

$smtp_server           = 'smtp.gmail.com';
$smtp_user             = 'account@mourjan.com';
$smtp_pass             = 'Y[\6c(?Qt\FZ^Y&r';
$smtp_port             = 465;

try {
    $mail->Host       = $smtp_server;
    $mail->SMTPAuth   = true;
    $mail->Port       = $smtp_port;
    $mail->Username   = $smtp_user;
    $mail->Password   = $smtp_pass;
    $mail->SMTPSecure = 'ssl';
    $mail->Sender = $smtp_user;
    $mail->SetFrom($smtp_user, 'Edigear');
    $mail->AddAddress("mourjan@gmail.com","Admin");
    $mail->addReplyTo($email, $name);
    
    $mail->IsHTML(true);
    $mail->CharSet='UTF-8';
    $mail->Subject = "Edigear Request";
    
    $mail->Body    = $msg;
    if ($mail->send())
    {
        $res = 1;
    }
 } catch (phpmailerException $e) {
        $res= 0;
        //trigger_error($mail->ErrorInfo);
        //error_log($e->getMessage());
 } catch (Exception $e) {
    $res= 0;
    //error_log($e->getMessage());
 }
 $mail->ClearAddresses();
 $mail->ClearAllRecipients();
 $mail->ClearAttachments();
 
}
 
 echo $res;