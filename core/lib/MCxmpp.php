<?php

class MCxmpp
{
    use UserTrait;
    
    
    const VCARD_FULLNAME = 'FN';
    const VCARD_NICKNAME = 'NICKNAME';
    const VCARD_BIRTHDAY = 'BDAY';
    const VCARD_EMAIL = 'EMAIL USERID';
    const VCARD_COUNTRY = 'ADR CTRY';
    const VCARD_CITY = 'ADR LOCALITY';
    const VCARD_DESCRIPTION = 'DESC';
    const VCARD_AVATAR_URL = 'EXTRA PHOTOURL';
    
    const RESPONSE_MAX_LENGTH = 10000000;
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
        $this->password = 'f0sVSPrO';
        $this->timeout = 5;
        $this->debug = TRUE;
        $this->userAgent = 'XMLRPC::Client Mourjan site';
        
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
        if ($this->username && $this->password) {
            $params = [
                ['user' => $this->username, 'server' => $this->server, 'password' => $this->password], $params
            ];
        }
        
        $request = xmlrpc_encode_request($command, $params, ['encoding' => 'utf-8', 'escaping' => 'markup']);
        echo $request, "\n";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->server);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['User-Agent: ' . $this->userAgent, 'Content-Type: text/xml']);
        $response = curl_exec($ch);
        curl_close($ch);
        // INFO: We must use a custom parser instead xmlrpc_decode if the answer is longer than 10000000 bytes
        if (strlen($response) > self::RESPONSE_MAX_LENGTH) {
            $xml = (new XmlrpcDecoder)->decodeResponse($response);
        } else {
            $xml = \xmlrpc_decode($response);
        }
        //var_dump($xml);
        //if (!$xml || \xmlrpc_is_fault($xml)) {
        //    throw new \RuntimeException("Error execution command '$command'' with parameters " . var_export($params, true) . ". Response: ");
        //}
        if ($this->debug) {
            var_dump($command, $params, $response);
        }
        return $xml;
    }
}


/**
 * Class UserTrait
 *
 * @category  GGS
 * @package   GameNet\Jabber
 * @copyright Copyright (с) 2014, GameNet. All rights reserved.
 * @author    Vadim Sabirov <vadim.sabirov@syncopate.ru>
 * @version   1.0
 */
trait UserTrait
{
    abstract protected function sendRequest($command, array $params);
    /**
     * Create an ejabberd user account.
     *
     * @param string $user
     * @param string $password
     *
     * @throws \RuntimeException
     */
    public function createUser($user, $password)
    {
        $response = $this->sendRequest(
            'register',
            [
                'host'     => $this->host,
                'user'     => $user,
                'password' => $password
            ]
        );
        if ($response['res'] != 0) {
            throw new \RuntimeException('Unable create user');
        }
    }
    /**
     * Check if ejabberd user account already exists.
     *
     * @param string $user
     *
     * @return bool
     */
    public function checkAccount($user)
    {
        $response = $this->sendRequest(
            'check_account',
            [
                'user' => $user,
                'host' => $this->host
            ]
        );
        return $response['res'] == 0;
    }
    /**
     * Change the password on behalf of the given user.
     *
     * @param string $user
     * @param string $password
     */
    public function changePassword($user, $password)
    {
        $this->sendRequest(
            'change_password',
            [
                'host'    => $this->host,
                'user'    => $user,
                'newpass' => $password
            ]
        );
    }
    /**
     * Define user nickname.
     *
     * The nickname is set/updated in the user Vcard. Other informations are unchanged.
     *
     * @param string $user
     * @param string $nickname
     */
    public function setNickname($user, $nickname)
    {
        $this->sendRequest(
            'set_nickname',
            [
                'host'     => $this->host,
                'user'     => $user,
                'nickname' => $nickname
            ]
        );
    }
    /**
     * Get last activity information
     *
     * Timestamp is the seconds since 1970-01-01 00:00:00 UTC, for example: date +%s
     *
     * @param string $user
     *
     * @return string
     */
    public function getLastActivity($user)
    {
        $response = $this->sendRequest(
            'get_last',
            [
                'host' => $this->host,
                'user' => $user,
            ]
        );
        return $response['last_activity'];
    }
    /**
     * Send a chat message to a local or remote bare of full JID
     *
     * @param string $fromJid
     * @param string $toJid
     * @param string $message
     */
    public function sendMessageChat($fromJid, $toJid, $message)
    {
        $this->sendRequest(
            'send_message_chat',
            [
                'from' => $fromJid,
                'to'   => $toJid,
                'body' => $message
            ]
        );
    }
    /**
     * Unregister an ejabberd user account. This mechanism should only be used for server administration or server
     * integration purpose.
     *
     * @param string $user
     */
    public function unregisterUser($user)
    {
        $this->sendRequest(
            'unregister',
            [
                'host' => $this->host,
                'user' => $user,
            ]
        );
    }
    /**
     * Set user status with message
     *
     * @param string $user
     * @param string $show Valid values are: away, chat, dnd, xa
     * @param string $status Text message
     * @param int $priority The value MUST be an integer between -128 and +127
     */
    public function setStatus($user, $show, $status, $priority)
    {
        $priority = (string) $priority;
        $stanza = "
            <presence>
                <show>$show</show>
                <status>$status</status>
                <priority>$priority</priority>
            </presence>";
        $this->sendStanzaC2S($user, $stanza);
    }
    /**
     * Get information about all sessions of a user
     *
     * @param string $user
     *
     * @return array [['connection', 'ip', 'port', 'priority', 'node', 'uptime', 'status', 'resource', 'statustext'], [], ...]
     */
    public function userSessionsInfo($user)
    {
        $response = $this->sendRequest(
            'user_sessions_info',
            [
                'host' => $this->host,
                'user' => $user,
            ]
        );
        if (!isset($response['sessions_info']) || empty($response['sessions_info'])) {
            return [];
        }
        $sessions = [];
        foreach ($response['sessions_info'] as $info) {
            $session = [];
            foreach ($info['session'] as $data) {
                foreach ($data as $key => $value) {
                    $session[$key] = $value;
                }
            }
            $sessions[] = $session;
        }
        return $sessions;
    }
    /**
     * Get content from a vCard field
     *
     * @param string $user
     * @param string $name
     *
     * @return string
     */
    public function getVCard($user, $name)
    {
        if (strstr($name, ' ')) {
            $command = 'get_vcard2';
            list($name, $subname) = explode(' ', $name);
            $params = [
                'host' => $this->host,
                'user' => $user,
                'name' => $name,
                'subname' => $subname,
            ];
        } else {
            $command = 'get_vcard';
            $params = [
                'host' => $this->host,
                'user' => $user,
                'name' => $name,
            ];
        }
        try {
            $response = $this->sendRequest($command, $params);
        } catch (\RuntimeException $e) {
            return '';
        }
        return $response['content'];
    }
    /**
     * Set content in a vCard field
     *
     * @param string $user
     * @param string $name
     * @param string $value
     */
    public function setVCard($user, $name, $value)
    {
        if (strstr($name, ' ')) {
            $command = 'set_vcard2';
            list($name, $subname) = explode(' ', $name);
            $params = [
                'host' => $this->host,
                'user' => $user,
                'name' => $name,
                'subname' => $subname,
                'content' => $value,
            ];
        } else {
            $command = 'set_vcard';
            $params = [
                'host' => $this->host,
                'user' => $user,
                'name' => $name,
                'content' => $value,
            ];
        }
        $this->sendRequest($command, $params);
    }
    /**
     * Ban an account: kick sessions and set random password
     *
     * @param string $user
     * @param string $reason
     */
    public function banAccount($user, $reason)
    {
        $this->sendRequest(
            'ban_account',
            [
                'host' => $this->host,
                'user' => $user,
                'reason' => $reason,
            ]
        );
    }
    /**
     * Send a stanza as if sent from a c2s session
     *
     * @param string $user
     * @param string $stanza XML string
     */
    public function sendStanzaC2S($user, $stanza)
    {
        $sessions = $this->userSessionsInfo($user);
        foreach ($sessions as $session) {
            $stanza = str_replace('{resource}', $session['resource'], $stanza);
            $this->sendRequest(
                'send_stanza_c2s',
                [
                    'host' => $this->host,
                    'user' => $user,
                    'resource' => $session['resource'],
                    'stanza' => $stanza,
                ]
            );
        }
    }
    /**
     * Set groups for contact in roster user owner
     *
     * @deprecated
     * @param string $user
     * @param string $contact
     * @param array $groups
     */
    public function setGroupForUserRoster($user, $contact, array $groups)
    {
        $id = uniqid();
        $userJid = "$user@$this->host";
        $contactJid = "$contact@$this->host";
        $groupList = '';
        foreach ($groups as $group) {
            $groupList .= "<group>$group</group>";
        }
        $stanza = "
            <iq from=\"$userJid/{resource}\" type=\"set\" id=\"$id\">
                <query xmlns=\"jabber:iq:roster\">
                    <item jid=\"$contactJid\">$groupList</item>
                </query>
            </iq>";
        $this->sendStanzaC2S($user, $stanza);
    }
    /**
     * Add jid to a group in a user's roster (supports ODBC)
     *
     * WARNING!
     * This method uses commands that are not available in the basic version eJabberd
     * These commands are available in the branch version https://github.com/gamenet/ejabberd
     * See request to add features - https://github.com/gamenet/ejabberd/pull/11
     *
     * @param $user
     * @param $contact
     * @param $group
     */
    public function addUserToGroup($user, $contact, $group)
    {
        $this->sendRequest(
            'add_jid_to_group',
            [
                'localserver' => $this->host,
                'localuser'   => $user,
                'jid'         => "$contact@$this->host",
                'group'       => $group,
            ]
        );
    }
    /**
     * Delete a jid from a user's roster group (supports ODBC)
     *
     * WARNING!
     * This method uses commands that are not available in the basic version eJabberd
     * These commands are available in the branch version https://github.com/gamenet/ejabberd
     * See request to add features - https://github.com/gamenet/ejabberd/pull/11
     *
     *
     * @param $user
     * @param $contact
     * @param $group
     */
    public function deleteUserFromGroup($user, $contact, $group)
    {
        $this->sendRequest(
            'delete_jid_from_group',
            [
                'localserver'  => $this->host,
                'localuser'    => $user,
                'jid'          => "$contact@$this->host",
                'group'        => $group,
                'deleteroster' => "false",
            ]
        );
    }
    /**
     * Get list of connected users
     *
     * @return string
     */
    public function getConnectedUsers()
    {
        $users = $this->sendRequest('connected_users', []);
        return isset($users['connected_users']) ? $users['connected_users'] : [];
    }
}

/**
 * Class RoomTrait
 *
 * @package   GameNet\Jabber
 * @copyright Copyright (с) 2014, GameNet. All rights reserved.
 * @author    Vadim Sabirov <vadim.sabirov@syncopate.ru>
 * @version   1.0
 */
trait RoomTrait
{
    abstract protected function sendRequest($command, array $params);
    /**
     * Create a MUC room name@service in host.
     *
     * @param string $name
     */
    public function createRoom($name)
    {
        $this->sendRequest(
            'create_room',
            [
                'name'    => $name,
                'service' => 'conference.' . $this->host,
                'host'    => $this->host,
            ]
        );
    }
    /**
     * Send a direct invitation to several destinations.
     *
     * Password and Message can also be: none. Users JIDs are separated with `:`
     *
     * @param string $name The room name.
     * @param string $password The room password.
     * @param string $reason The invitation text which be sent to users.
     * @param array  $users The users JIDs invited to room.
     */
    public function inviteToRoom($name, $password, $reason, array $users)
    {
        $this->sendRequest(
            'send_direct_invitation',
            [
                'room'     => $name . '@conference.' . $this->host,
                'password' => $password,
                'reason'   => $reason,
                'users'    => join(':', $users),
            ]
        );
    }
    /**
     * Delete a MUC room.
     *
     * @param string $name The room name.
     */
    public function deleteRoom($name)
    {
        $this->sendRequest(
            'destroy_room',
            [
                'name'    => $name,
                'service' => 'conference.' . $this->host,
                'host'    => $this->host,
            ]
        );
    }
    /**
     * List existing rooms.
     *
     * @return array Return array like ['room1@conference.j.test.dev', 'room2@conference.j.test.dev', ...]
     */
    public function getOnlineRooms()
    {
        $rooms = $this->sendRequest(
            'muc_online_rooms',
            ['host' => $this->host]
        );
        if (!isset($rooms['rooms']) || empty($rooms['rooms'])) {
            return [];
        }
        $roomList = [];
        foreach ($rooms['rooms'] as $item) {
            $roomList[] = $item['room'];
        }
        return $roomList;
    }
    /**
     * Change an option in a MUC room.
     *
     * @param string $name The room name
     * @param string $option Valid values:
     *                       title (string)
     *                       password (string)
     *                       password_protected (bool)
     *                       anonymous (bool)
     *                       max_users (int)
     *                       allow_change_subj (bool)
     *                       allow_query_users (bool)
     *                       allow_private_messages (bool)
     *                       public (bool)
     *                       public_list (bool)
     *                       persistent (bool)
     *                       moderated (bool)
     *                       members_by_default (bool)
     *                       members_only (bool)
     *                       allow_user_invites (bool)
     *                       logging (bool)
     * @param string $value
     */
    public function setRoomOption($name, $option, $value)
    {
        $value = !is_bool($value) ? $value : ($value ? 'true' : 'false');
        $this->sendRequest(
            'change_room_option',
            [
                'name'    => $name,
                'service' => 'conference.' . $this->host,
                'option'  => $option,
                'value'   => (string) $value,
            ]
        );
    }
    /**
     * Change an affiliation in a MUC room.
     *
     * @param string $name The room name.
     * @param string $userJid
     * @param string $affiliation Valid values: outcast, none, member, admin, owner
     *                            If the affiliation is 'none', the action is to remove
     */
    public function setRoomAffiliation($name, $userJid, $affiliation)
    {
        $this->sendRequest(
            'set_room_affiliation',
            [
                'name'        => $name,
                'service'     => 'conference.' . $this->host,
                'jid'         => $userJid,
                'affiliation' => $affiliation,
            ]
        );
    }
}


$rpc = new MCxmpp(['server'=>'http://localhost:4560/RPC2']);
//    $rpc->createUser('Ivan', 'someStrongPassword');
var_dump($rpc->getConnectedUsers());//checkAccount('96171750413'));
