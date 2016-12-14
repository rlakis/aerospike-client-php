<?php
/* connect to gmail */
$hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
$username = 'noreply@mourjan.com';
$password = 'GQ71BUT2';
echo "Opening Imap connection\n";
/* try to connect */
$inbox = imap_open($hostname,$username,$password) or die('Cannot connect to Mourjan Mail: ' . imap_last_error());

$emailProviders = array(
    'gmail',
    'hotmail',
    'yahoo'
);

$provider = 'gmail';
foreach($emailProviders as $provider){
$regex='';
$regex2='';
echo "Fetching emails\n";
$emails = null;
switch($provider){
    case 'gmail':
        $regex = '/failed permanently:((?:.|[\n])*)Technical details/';        
        $emails = imap_search($inbox,'FROM "mailer-daemon@googlemail.com" ALL');
        break;
    case 'hotmail':
        $regex = '/(?:\s|\n)(.*@.*)(?:\s|\n|$)/';
        $emails = imap_search($inbox,'FROM "postmaster@hotmail.com" ALL');
        break;
    case 'yahoo':
        $regex = '/\<(.*@.*)\>/';
        $regex2 = '/Sorry your message to (.*) cannot be delivered\./';
        $emails = imap_search($inbox,'FROM "mailer-daemon@yahoo.com" ALL');
        break;
}
echo "\n",'Counted ',count($emails), "\n\n";
//imap_close($inbox);
//exit(0);
echo "Processing emails\n";
$processed = array();
$counter = 0;
/* if emails are returned, cycle through each... */
if($emails) {
	
	/* begin output var */
	$output = '';
	
	/* put the newest emails on top */
	rsort($emails);
        $i=0;
	foreach($emails as $email_number) {
		$matches=null;
		/* get information specific to this email */
		$overview = imap_fetch_overview($inbox,$email_number,0);
                try{
                    $message = imap_fetchbody($inbox,$email_number,1);
                    //$body = utf8_encode(quoted_printable_decode($message));
                    preg_match($regex, $message, $matches);
                    //echo $message;
                    if($matches && count($matches)){
                        $email = trim($matches[1]);
                        $processed[$email]=$email_number;
                        $counter++;
                    }elseif($regex2){
                        preg_match($regex2, $message, $matches);
                        if($matches && count($matches)){
                            $email = trim($matches[1]);
                            $processed[$email]=$email_number;
                            $counter++;
                        }
                    }
                    $status = imap_setflag_full($inbox,$email_number, "\\Deleted \\Flagged");
                }catch(Exception $e){}
	}
       echo "\n",'Counted ',$counter,' fetched ',count($processed),' out of ',count($emails), "\n\n";


    if(count($processed)){
        $root_path = dirname(dirname(dirname(__FILE__)));
        include_once $root_path.'/config/cfg.php';
        include_once $config['dir'].'/core/model/Db.php';
        $db=new DB($config);
        //echo 'query';
        
        $st_usr_opt=$db->prepareQuery("update web_users set lvl = 6,email='',user_email='', opts=:options where id=:id");
        $st_usr_lvl=$db->prepareQuery("update web_users set lvl = 6,email='',user_email='' where id=?");
        
        $st_sub=$db->prepareQuery("delete from subscription where web_user_id = ?");
        $st_wmail=$db->prepareQuery("delete from mail_watchlist where web_user_id = ?");
        
        $q='select id,email,user_email,opts from web_users where (email=?) or (user_email=?) or (cast(opts as varchar(8000)) containing ?)';
        $stmt=$db->prepareQuery($q);
        

        $updated = 0;
        foreach($processed as $key => $email){
            echo $key,"\n";
            $stmt->bindValue(1, $key, PDO::PARAM_STR);
            $stmt->bindValue(2, $key, PDO::PARAM_STR);
            $stmt->bindValue(3, $key, PDO::PARAM_STR);
            $user = $stmt->execute();
            if ($stmt->execute()) {
                
                $user=$stmt->fetchAll(PDO::FETCH_NUM);
                $stmt->closeCursor();
                
                if($user && count($user)){
                    $user = $user[0];
                    $opt = json_decode($user[3],true);
                    
                    //echo $user[0],"\n";
                    
                    if(isset($opt['email'])){
                        unset($opt['email']);
                        if(isset($opt['emailKey'])){
                            unset($opt['emailKey']);
                        }
                        $opt = json_encode($opt);
                        
                        $st_usr_opt->bindParam(':options', $opt, PDO::PARAM_LOB);
                        $st_usr_opt->bindParam(':id', $user[0], PDO::PARAM_INT);                        
                        $st_usr_opt->execute();
                     //   $st_usr_opt->closeCursor();
                        
                    }else {
                        
                        $st_usr_lvl->bindValue(1, $user[0]);
                        $st_usr_lvl->execute();
                      //  $st_usr_lvl->closeCursor();
                        
                    }
                    
                    $st_sub->bindValue(1, $user[0]);
                    $st_sub->execute();
//                    $st_sub->closeCursor();
                    
                    $st_wmail->bindValue(1, $user[0]);
                    $st_wmail->execute();
//                    $st_wmail->closeCursor();
                    
                    $updated++;
                }
                
            }
        }
        $db->getInstance()->commit();
        echo "Updated ",$updated, "\n\n";
    }
} 
}

/* close the connection */
imap_close($inbox);
?>