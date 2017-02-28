#!/opt/php.7.0.16/bin/php
<?php
require 'external_auth.php';
require 'firebase/php-jwt/src/JWT.php';
use Firebase\JWT\JWT;


class MyAuth extends EjabberdExternalAuth 
{

    protected function authenticate($user, $server, $password) 
    {
        //$this->db()->prepare(...);
        // here be dragons
        $this->log($user . ' ' . $server, LOG_INFO);
        $isValid = ($server==='mourjan.com' && ($user=='2' || $user=='1'));
        return $isValid;
    }


    protected function exists($user, $server) 
    {
        $this->log(__FUNCTION__ . ' ' . $user, LOG_INFO);
        return true;
    }

}


//new MyAuth(NULL, '/var/log/mourjan/myauth.log');

$key = "9613287168";
$token = ["mobile"=>9613287168, "date_created"=>"01.02.2012", "identifier"=>"1727582100", "provider"=>"facebook"];
$jwt = JWT::encode($token, $key);

echo $jwt, "\n\n";

/**
 * You can add a leeway to account for when there is a clock skew times between
 * the signing and verifying servers. It is recommended that this leeway should
 * not be bigger than a few minutes.
 *
 * Source: http://self-issued.info/docs/draft-ietf-oauth-json-web-token.html#nbfDef
 */
JWT::$leeway = 60; // $leeway in seconds
$decoded = JWT::decode($jwt, $key, array('HS256'));


print_r( $decoded );