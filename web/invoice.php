<?php
define ('MOURJAN_KEY', 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAo//5OB8WpXTlsD5TEA5S+JK/I4xuYNOCGpGen07GKUpNdHcIVxSejoKiTmszUjsRgR1NC5H6Xu+5YMxfsPzQWwqyGMaQbvdLYOW2xQ5gnK4HEqp1ZP74HkNrnBCpyaGEuap4XcHu+37xNxZNRZpTgtr34dPcMIsN2GGANMNTy5aWlAPsl1BTYkDOCMu2f+Tyq2eqIkOvlHS09717JwNrx6NyI+CI7y8AAuLLZOp8usXWA/Lx3H6COts9IXMXE/+eNiFkaGsaolxzvO/aBg9w/0iYWGTinInOyHqwjcxazmoNJxxYbS/iTAlcPMrXzjn3UUepcq2WZ/+HWI0bzf4mVQIDAQAB');
$downloadLinkPath = 'http://h8.mourjan.com:8080/v1/pdf/invoice/';

$TID = \filter_input(\INPUT_GET, 'tid', \FILTER_SANITIZE_STRING, ['options'=>['default'=>false]]);
$signature = \filter_input(\INPUT_GET, 'signature', \FILTER_SANITIZE_STRING, ['options'=>['default'=>'']]);
if ($TID && \base64_decode($signature)===\strtoupper(\hash_hmac('sha1', $TID, MOURJAN_KEY))) {
    $filename = $TID.'.pdf';
    $content = \file_get_contents($downloadLinkPath . $TID);
    \header("Content-type:application/pdf");
    \header('Content-Length: '.\strlen( $content ));
    \header('Content-disposition: inline; filename="' . $filename . '"');
    \header('Cache-Control: public, must-revalidate, max-age=0');
    \header('Pragma: public');
    echo $content;
}
else {
    \header("HTTP/1.1 401 Unauthorized");
    exit;
}
