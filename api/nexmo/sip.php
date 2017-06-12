<?php

$method = filter_input(INPUT_SERVER, 'REQUEST_METHOD');
$request = array_merge($_GET, $_POST);

function handle_call_status()
{    
}

function handle_error($request)
{
}

switch ($method) 
{
    case 'POST':
        $ncco = handle_call_status();
        header("HTTP/1.1 200 OK");
        break;
    
    default:
        //Handle your errors
        handle_error($request);
        break;
}