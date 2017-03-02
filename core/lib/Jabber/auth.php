<?php
require 'external_auth.php';
require get_cfg_var('mourjan.path') . '/core/lib/MCSessionHandler.php';
require get_cfg_var('mourjan.path') . '/core/lib/MCUser.php';


class EjabberdJWTAuth extends EjabberdExternalAuth 
{

    protected function authenticate($user, $server, $password) 
    {
        if ($user==='9613287168' && $server==="mourjan.com" && $password==="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJtb3VyamFuIiwic3ViIjoiYW55IiwibmJmIjoxNDg4MzgzNTEwLCJleHAiOjE0ODg0Njk5MTAsImlhdCI6MTQ4ODM4MzUxMCwidHlwIjoiamFiYmVyIiwicGlkIjo5MTcxLCJtb2IiOiI5NjEzMjg3MTY4IiwidXJkIjoxMzI4MDU0NDAwLCJ1aWQiOiIxNzI3NTgyMTAwIiwicHZkIjoiZmFjZWJvb2sifQ.pSCk8AdrRPBWy6OdkGkNPFzaZJTDjdk_ZG0o8Y-__TA")
        {
            return true;
        }
        
        $mcUser = new MCUser(  MCSessionHandler::getUser($user) );        
        $isValid = ($server==='mourjan.com' && $mcUser->isValidToken($password));
        return $isValid;
    }


    protected function exists($user, $server) 
    {
        $mcUser = new MCUser(  MCSessionHandler::getUser($user) );
        if ($mcUser->getID()==$user && $mcUser->getLevel()!=5)
        {
            return TRUE;
        }
        
        return FALSE;
    }

}


new EjabberdJWTAuth(NULL, '/var/log/mourjan/myauth.log');
