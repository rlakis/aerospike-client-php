<?php
include_once get_cfg_var('mourjan.path').'/core/model/MobileValidation.php';
include_once get_cfg_var('mourjan.path').'/core/model/NoSQL.php';

$method = filter_input(INPUT_SERVER, 'REQUEST_METHOD');

switch ($method)
{
    case 'GET':
        //Retrieve with the parameters in this request
        $to = filter_input(INPUT_GET, 'to', FILTER_VALIDATE_INT); //The endpoint being called
        $from = filter_input(INPUT_GET, 'from', FILTER_VALIDATE_INT); //The endpoint you are calling from
        $uuid = filter_input(INPUT_GET, 'conversation_uuid', FILTER_SANITIZE_STRING); //The unique ID for this Call

        
        
        if (key_exists(intval($to), \Core\Model\MobileValidation::NUMBERS))
        {
            $ncco='[
            {
            "action": "connect",            
            "from":"'.$from.'",
            "timeout": "6",
            "limit": "10",
            "machineDetection": "hangup",
            "eventUrl": [
            "https://dv.mourjan.com/api/nexmo/sip.php"
            ],
            "endpoint": [
                {
                "type": "sip",
                "uri": "sip:1000@51.255.175.173"
                }
            ]
            }            
            ]';               
        }
        else
        {
            error_log(var_export($_GET, TRUE));
            //For more advanced Conversations you use the paramaters to personalize the NCCO
            //Dynamically create the NCCO to run a conversation from your virtual number
            $pin = substr($from, -4);
            $speech = "";
            for ($i=0; $i<4; $i++)
            {
                $speech.=$pin[$i]." . ";
            }
        
            $ncco='[
            {
            "action": "talk",
            "text": "Your Mourjan code is  ' . $speech .'"
            }
            ]';

            \Core\Model\NoSQL::getInstance()->textCall(['conversation_uuid'=>$uuid, 'from'=>$from, 'to'=>$to]);
            
        }
        
        header('Content-Type: application/json');
        echo $ncco;
        break;

    default:
        //Handle your errors
        handle_error($request);
        break;
}