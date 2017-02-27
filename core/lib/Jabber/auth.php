<?php

require 'external_auth.php';

class MyAuth extends EjabberdExternalAuth {

    protected function authenticate($user, $server, $password) 
    {
        //$this->db()->prepare(...);
        // here be dragons
        error_log($user . ' ' . $server, 3, '/var/log/mourjan/myauth.log');
        $isValid = ($server==='mourjan.com' && ($user=='2' || $user=='1'));
        return $isValid;
    }

    protected function exists($user, $server) 
    {
        error_log(__FUNCTION__ . ' ' . $user);
        return true;
    }

}

new MyAuth(NULL, '/var/log/mourjan/myauth.log');