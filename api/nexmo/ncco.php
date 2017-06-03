<?php

$method = filter_input(INPUT_SERVER, 'REQUEST_METHOD');
error_log($method);

switch ($method)
{
    case 'GET':
        //Retrieve with the parameters in this request
        $to = filter_input(INPUT_GET, 'to', FILTER_VALIDATE_INT); //The endpoint being called
        $from = filter_input(INPUT_GET, 'from', FILTER_VALIDATE_INT); //The endpoint you are calling from
        $uuid = filter_input(INPUT_GET, 'conversation_uuid', FILTER_SANITIZE_STRING); //The unique ID for this Call

        //For more advanced Conversations you use the paramaters to personalize the NCCO
        //Dynamically create the NCCO to run a conversation from your virtual number
        $ncco='[
            {
            "action": "talk",
            "text": "Welcome to a mourjan classifieds"
            }
            ]';

        header('Content-Type: application/json');
        echo $ncco;
        break;

    default:
        //Handle your errors
        handle_error($request);
        break;
}