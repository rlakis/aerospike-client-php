<?php

include_once get_cfg_var('mourjan.path').'/core/model/NoSQL.php';

use \Core\Model\NoSQL;


$method = $_SERVER['REQUEST_METHOD'];
$request = array_merge($_GET, $_POST);

function handle_call_status()
{
    $decoded_request = json_decode(file_get_contents('php://input'), true);
    error_log(json_encode($decoded_request, JSON_PRETTY_PRINT).PHP_EOL, 3, "/var/log/mourjan/sms.log");
    // Work with the call status
  
    if (isset($decoded_request['status'])) 
    {
        switch ($decoded_request['status']) 
        {
            case 'ringing':
                NoSQL::getInstance()->outboundCall($decoded_request);
                //error_log("Handle conversation_uuid, this return parameter identifies the Conversation");
                break;
        
            case 'answered':
                NoSQL::getInstance()->outboundCall($decoded_request);
                //error_log("You use the uuid returned here for all API requests on individual calls");
                break;
      
            case 'complete':
                NoSQL::getInstance()->outboundCall($decoded_request);
                //if you set eventUrl in your NCCO. The recording download URL
                //is returned in recording_url. It has the following format
                //https://api.nexmo.com/media/download?id=52343cf0-342c-45b3-a23b-ca6ccfe234b0
                //Make a GET request to this URL using a JWT as authentication to download
                //the Recording. For more information, see Recordings.
                break;
            
            default:
                NoSQL::getInstance()->outboundCall($decoded_request);
                break;
        }
        return;
    }
}


/*
 *  Handle errors
*/
function handle_error($request)
{
     //code to handle your errors
}

/*
  Send the 200 OK to Nexmo and handle changes to the call
*/
switch ($method) 
{
    case 'POST':
        //Retrieve your dynamically generated NCCO.
        $ncco = handle_call_status();
        header("HTTP/1.1 200 OK");
        break;
    
    default:
        //Handle your errors
        handle_error($request);
        break;
}