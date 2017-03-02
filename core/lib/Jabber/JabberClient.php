<?php
namespace lib\Jabber;
require_once 'MixIns/UserTrait.php';
require_once 'MixIns/RoomTrait.php';
/*
 * @package lib\Jabber
 * @copyright 2017, Mourjan
 * @author Robert Lakis <rlakis@berysoft.com>
 * @license MIT http://opensource.org/licenses/MIT
 */
class JabberClient
{
    use MixIns\UserTrait;
    use \lib\Jabber\MixIns\RoomTrait;
        
    const VCARD_FULLNAME = 'FN';
    const VCARD_NICKNAME = 'NICKNAME';
    const VCARD_BIRTHDAY = 'BDAY';
    const VCARD_EMAIL = 'EMAIL USERID';
    const VCARD_COUNTRY = 'ADR CTRY';
    const VCARD_CITY = 'ADR LOCALITY';
    const VCARD_DESCRIPTION = 'DESC';
    const VCARD_AVATAR_URL = 'EXTRA PHOTOURL';
    
    /**
     * @var string
     */
    protected $server;
    /**
     * @var string
     */
    protected $host;
    /**
     * @var bool
     */
    protected $debug;
    /**
     * @var int
     */
    protected $timeout;
    /**
     * @var string
     */
    protected $username;
    /**
     * @var string
     */
    protected $password;
    /**
     * @var string
     */
    protected $userAgent;
    
    public function __construct(array $options)
    {
        $this->server = $options['server'];
        $this->host = 'mourjan.com';
        $this->username = '9613287168@mourjan.com';
        $this->password = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJtb3VyamFuIiwic3ViIjoiYW55IiwibmJmIjoxNDg4MzgzNTEwLCJleHAiOjE0ODg0Njk5MTAsImlhdCI6MTQ4ODM4MzUxMCwidHlwIjoiamFiYmVyIiwicGlkIjo5MTcxLCJtb2IiOiI5NjEzMjg3MTY4IiwidXJkIjoxMzI4MDU0NDAwLCJ1aWQiOiIxNzI3NTgyMTAwIiwicHZkIjoiZmFjZWJvb2sifQ.pSCk8AdrRPBWy6OdkGkNPFzaZJTDjdk_ZG0o8Y-__TA';
        $this->timeout = 5;
        $this->debug = TRUE;
        $this->userAgent = 'JSON API:Client Mourjan site';
        
    }
    
    /**
     * @param int $timeout
     * @return $this
    */
    public function setTimeout($timeout)
    {
        if (!is_int($timeout) || $timeout < 0) {
            throw new \InvalidArgumentException('Timeout value must be integer');
        }
        $this->timeout = $timeout;
        return $this;
    }
    
    
    
    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }
    
    
    /**
     * @param string $userAgent
     * @return $this
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
        return $this;
    }
    
    
    
    protected function sendRequest($command, array $params)
    {
        $data_string = json_encode($params);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "{$this->server}/{$command}");
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "{$this->username}:{$this->password}");
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_SSL_OPTIONS, CURL_SSLVERSION_TLSv1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                            'Content-Type: application/json',
                            'Content-Length: ' . strlen($data_string),
                            'User-Agent: ' . $this->userAgent,
                            'X-Admin: true'
                    ]);

        $response = curl_exec($ch);
        $error= curl_error($ch);
        curl_close($ch);

        echo "----------------------------------------------------------------", "\n";
        echo $response,"\n";
        echo $error, "\n";                                
        ECHO "----------------------------------------------------------------","\n";


        if ($this->debug)
        {
            var_dump($command, $params, $response);
        }
        return $response;
    }
}





$jc = new JabberClient(['server'=>'https://dv.mourjan.com:5280/api']);
//$jc->createUser('1', 'GQ71BUT2');
//$jc->createUser('4', 'GQ71but244');

var_dump( $jc->checkAccount(['1']) );
//$jc->createRoom("96171750413-1400-rlakis");

//$jc->deleteRoom("96171750413-1400-rlakis");

//$jc->inviteToRoom("96171750413-1400-rlakis", "", "test chat", ["rlakis@mourjan.com"]);
//$jc->getConnectedUsers();

$jc->addRosterItem("2", "4");
//$jc->addRosterItem("3", "2");

$jc->sendMessageChat("2", "4", "Hello there");
//$jc->subscribeRoom("96171750413-1400-rlakis", "rlakis@mourjan.com", "Advertiser");