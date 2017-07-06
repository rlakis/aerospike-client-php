<?php

include_once get_cfg_var('mourjan.path').'/core/model/NoSQL.php';
include_once get_cfg_var('mourjan.path').'/core/model/MobileValidation.php';

use \Core\Model\NoSQL;
use Core\Model\MobileValidation;


$method = filter_input(INPUT_SERVER, 'REQUEST_METHOD');
$request = array_merge($_GET, $_POST);

function handle_call_status()
{
    $decoded_request = json_decode(file_get_contents('php://input'), true);
    
    error_log(json_encode($decoded_request, JSON_PRETTY_PRINT).PHP_EOL, 3, "/var/log/mourjan/sms.log");
    
    $direction = $decoded_request['direction'] ?? 'outbound';
    
    if (isset($decoded_request['status'])) 
    {
        if ($direction=='outbound')
        {
            switch ($decoded_request['status']) 
            {
                case 'ringing':
                    NoSQL::getInstance()->outboundCall($decoded_request);
                    //MobileValidation::modifyNexmoCall($decoded_request['uuid']);
                    break;

                case 'answered':
                    NoSQL::getInstance()->outboundCall($decoded_request);
                    break;

                case 'complete':
                case 'completed':
                    NoSQL::getInstance()->outboundCall($decoded_request);
                    $key = NoSQL::getInstance()->getConnection()->initKey(Core\Model\ASD\NS_USER, 'did', intval($decoded_request['from']));
                    $operations = [
                        ["op" => \Aerospike::OPERATOR_INCR, "bin" => "outbound", "val" => 1],
                        ["op" => \Aerospike::OPERATOR_WRITE, "bin" => "locked", "val" => 0],
                    ];
                    NoSQL::getInstance()->getConnection()->operate($key, $operations, $record);                
                    break;

                default:
                    NoSQL::getInstance()->outboundCall($decoded_request);
                    break;
            }
        }
        else 
        {
            //error_log("Inbound status: ".$decoded_request['status']);
            switch ($decoded_request['status']) 
            {
                case 'started':
                case 'ringing':
                    //NoSQL::getInstance()->getValidNumberCallRequests(MobileValidation::CLI_TYPE, $number, $did, $result)
                    //MobileValidation::getInstance(MobileValidation::NEXMO)->modifyNexmoCall($decoded_request['uuid']);
                    //NoSQL::getInstance()->outboundCall($decoded_request);
                    //error_log("Handle conversation_uuid, this return parameter identifies the Conversation");
                    break;

                case 'answered':
                    //NoSQL::getInstance()->outboundCall($decoded_request);
                    //error_log("You use the uuid returned here for all API requests on individual calls");
                    break;

                case 'complete':
                case 'completed':
                    $to = intval($decoded_request['to']);
                    $from = intval($decoded_request['from']);
                    
                    if (NoSQL::getInstance()->getValidNumberCallRequests(MobileValidation::CLI_TYPE, $from, $to, $result)==\Aerospike::OK)
                    {                                      
                        if ($result)
                        {
                            error_log(var_export($result, TRUE));
                            
                            NoSQL::getInstance()->inboundCall($decoded_request, $result[0]);
                            $key = NoSQL::getInstance()->getConnection()->initKey(Core\Model\ASD\NS_USER, 'did', $to);
                            $operations = 
                                [
                                    ["op" => \Aerospike::OPERATOR_INCR, "bin" => "inbound", "val" => 1],
                                    ["op" => \Aerospike::OPERATOR_WRITE, "bin" => "locked", "val" => 0],
                                ];
                            NoSQL::getInstance()->getConnection()->operate($key, $operations, $record);
                        }
                        else 
                        {
                            error_log(" type cli {$from}/{$to}");
                        }
                    }
                    
                    $ch = curl_init("https://h9.mourjan.com/v1/nexmo/event");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json', 'Content-Type: application/json', 'Authorization: D38D5D58-572B-49EC-BAB5-63B6081A55E6']);
                    curl_setopt($ch, CURLOPT_HEADER, 1);
                    curl_setopt($ch, CURLOPT_NOBODY, 1);
                    $response = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch); // Don't forget to close the connection
                    error_log("h9 nexmo status: {$response}");
                    
                    
                    break;

                default:
                    //NoSQL::getInstance()->outboundCall($decoded_request);
                    break;
            }
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