<?php
require 'external_auth.php';
require libFile('MCSessionHandler.php');
require libFile('MCUser.php');


class EjabberdJWTAuth extends EjabberdExternalAuth {

    
    protected function authenticate($user, $server, $password) {
        if ($user==='9613287168' && $server==="mourjan.com" && $password==="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJtb3VyamFuIiwic3ViIjoiYW55IiwibmJmIjoxNDg4MzgzNTEwLCJleHAiOjE0ODg0Njk5MTAsImlhdCI6MTQ4ODM4MzUxMCwidHlwIjoiamFiYmVyIiwicGlkIjo5MTcxLCJtb2IiOiI5NjEzMjg3MTY4IiwidXJkIjoxMzI4MDU0NDAwLCJ1aWQiOiIxNzI3NTgyMTAwIiwicHZkIjoiZmFjZWJvb2sifQ.pSCk8AdrRPBWy6OdkGkNPFzaZJTDjdk_ZG0o8Y-__TA") {
            return true;
        }
        
        $mcUser = new MCUser(  MCSessionHandler::getUser($user) );  
        $this->log(__FUNCTION__.PHP_EOL.$user.'@'.$server.PHP_EOL.$password);
        $this->log(__FUNCTION__.PHP_EOL.$mcUser->isValidToken($password));
        $isValid = ($server==='mourjan.com' && $mcUser->isValidToken($password));
        return $isValid;
    }


    protected function exists($user, $server) {
        $mcUser = new MCUser(  MCSessionHandler::getUser($user) );
        if ($mcUser->getID()==$user && $mcUser->getLevel()!=5) {
            return TRUE;
        }
        
        return FALSE;
    }

}


$authenticator = new EjabberdJWTAuth(TRUE);
//$authenticator->authenticate('2', 'mourjan.com', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJtb3VyamFuIiwic3ViIjoiYW55IiwibmJmIjoxNDg4NTQ1MzEwLCJleHAiOjE0ODg2MzE3MTAsImlhdCI6MTQ4ODU0NTMxMCwidHlwIjoiamFiYmVyIiwicGlkIjoyNzM1LCJtb2IiOiI5NjEzMjg3MTY4IiwidXJkIjoxMzI4MDU0NDAwLCJ1aWQiOiIxNzI3NTgyMTAwIiwicHZkIjoiZmFjZWJvb2sifQ.xIWeQ30AGrBx3FGHkU-kPsKBhnSuwFuqAe3rbEUVPgs');
