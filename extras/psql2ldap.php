<?php

//define('DEBUGLEVEL', 10);
define('DEBUGLEVEL', 50);

define('DEBUG', 100);
define('INFO', 50);
define('WARNING', 20);
define('ERROR', 10);

define('FORCE', true);

define('CONCESSION_AMOUNT', 500);
define('FULL_AMOUNT', 1000);

/* Page load time */
   $mtime = microtime();
   $mtime = explode(" ",$mtime);
   $mtime = $mtime[1] + $mtime[0];
   $pagestarttime = $mtime; 

// nextuid is now gotten from DB!
//$nextuid = 10325;

$value = 0;
$expired = 0;
$valid = 0;

require_once "MDB2.php";

    $plugdsn = array(
        "phptype" => "pgsql",
        "username" => 'tim',
        "password" => 'timplug',
        "hostspec" => 'localhost',
        "port"     => '5434',
        "database" => 'plug',
        'portability' => MDB2_PORTABILITY_ALL ^ MDB2_PORTABILITY_FIX_CASE,
        "new_link" => true
        );


    $plugpgsql =& MDB2::connect($plugdsn);
    
    if (PEAR::isError($plugpgsql)) {
        die('Could not connect to SQL-server: '.$plugpgsql->getMessage());
    }        
    $plugpgsql->setFetchMode(MDB2_FETCHMODE_ASSOC);
    
require_once('/usr/share/plug-ugmm/www/PLUG/Members.class.php');
require_once('/etc/private/ldapconnection.inc.php');

$OrgMembers = new Members($ldap);
    
/* Delete a few groups first */

        $deletegroup = array('currentmembers', 'pendingmembers', 'expiredmembers');        
        foreach($deletegroup as $group)
        {
            $groupdn = "cn=$group,ou=Groups,dc=plug,dc=org,dc=au";
            $ldapres = $ldap->delete($groupdn);
            if (PEAR::isError($ldapres)) {
                eho(DEBUG, 'LDAP Error: '.$ldapres->getMessage() . "\n");
            }
        }
        
// Get next uid from db

    $results = $plugpgsql->queryOne('select MAX(uid) from account');
    if (PEAR::isError($results)) {
        eho(ERROR, 'DB Error '.$results->getMessage());
        die('error');
    }
        
    $nextuid = $results + 1;

// Load all members            
    $results = $plugpgsql->queryAll('SELECT * FROM member');            

    if (PEAR::isError($results)) {
        eho(ERROR, 'DB Error '.$results->getMessage());
        die('error');
    }
    

    foreach($results as $result){
        $account = $plugpgsql->queryRow('SELECT * FROM account WHERE member_id = '. $result['id']);
        
        $payments = $plugpgsql->queryAll('SELECT * FROM payment WHERE member_id = '. $result['id']);


        $account['locked'] = TRUE;
        if(@$account['enabled'] == "t")
            $account['locked'] = FALSE;
        if(@$account['uid'] == '')
        {
            $account['uid'] = $nextuid;
            $nextuid ++;
            
        }
        
        if(@$account['username'] == '')
        {
            $account['username'] = ereg_replace(" ", "", $result['first_name']) . '.' . $result['last_name'];
        }
        

        
        $alias = $plugpgsql->queryAll('SELECT destination FROM alias WHERE alias = \''.$account['username'].'\'');
        
        $user = array();

        $dn = "uidNumber=${account['uid']},ou=Users,dc=plug,dc=org,dc=au";
        
        $user['objectClass'] = array('top',  'person', 'posixAccount', 'inetOrgPerson', 'shadowAccount', 'mailForwardingAccount');
        $user['uid'] = $account['username'];
        $user['displayName'] = "${result['first_name']} ${result['last_name']}";
        $user['uidNumber'] = $account['uid'];
        $user['gidNumber'] = $account['uid'];        
        $user['homeDirectory'] = @$account['homedir'] ? $account['homedir'] : '/home/'.$account['username'];
        $user['userPassword'] = "{crypt}".@$account['password']; // TODO if empty
        $user['loginShell'] = @$account['shell'] ? $account['shell'] : '/usr/bin/zsh';
        
        $user['mail'] = strtolower($result['email_address']);
        $user['mailForward'] = "";
        foreach($alias as $email)
        {
            // If statement was to filter out before mailForward. mailFoward can == mail
            //if(strtolower($email['destination']) != strtolower($result['email_address']))
            
            // Only last alias is used, aliases should be stored as "one, two, three"
                $user['mailForward'] = $email['destination'];
        }
        
        $user['givenName'] = $result['first_name'];
        $user['sn'] = @$result['last_name'] ? $result['last_name'] : '_';
        $user['cn'] = "${result['first_name']} ${result['last_name']}";
        $user['street'] = @$result['street_address'] ? $result['street_address'] : "No Address on file";
        $user['homePhone'] = format_ph($result['home_phone']);
        $user['mobile'] = format_ph($result['mobile_phone']);
        $user['pager'] = format_ph($result['work_phone']);
        $user['description'] = $result['notes'];
        // NB: shadowExpire is number of DAYS not seconds since epoch
        // -1 is taken as no expiry, so need to set to 1
        $user['shadowExpire'] = ceil(strtotime($result['expiry'])/ 86400);
        if($user['shadowExpire'] <= 0) $user['shadowExpire'] = 1;
        
        $user = array_filter($user);
        
        eho(INFO, "<p><h3>$dn</h3>");
        // Delete user before adding (to ensure sync for now)
        $OrgMembers->delete_member($dn);
        $OrgMembers->delete_member("gidNumber=${user['gidNumber']},ou=UPG, ou=Groups, dc=plug, dc=org, dc=au");

        // Add the entry        
        $person = new Person($ldap);
        eho(INFO, "Creating person ". $user['uid']);
        $person->create_person($user['uidNumber'], $user['uid'], $user['givenName'], $user['sn'], $user['street'], @$user['homePhone'], @$user['pager'], @$user['mobile'], $user['mail'], @$user['mailForward'], $user['userPassword'], @$user['description']);
        
        print_r($person->get_messages());
        
        if($person->is_error())
        {
            print_r($person->get_errors());
            die('Error in creation of person');
        }        

        // Lock /unlock account
        if($account['locked'])
        {
            eho(INFO, "Shell locked");
            $person->disable_shell();

        }else{
            eho(INFO, "Unlocking shell");
            $person->enable_shell();
            
        }
        
        // Some members don't have payments but had active accounts, we don't want them in the pending payment stage
        if(sizeof($payments) == 0 && $user['shadowExpire'] != 1)
        {
            $person->change_expiry($result['expiry']);
        }

        foreach($payments as $payment)
        {
            
            if($payment['type_id'] == 2)
            {
                eho(INFO, "Concession payment: ");
                $payment['years'] = $payment['amount'] / CONCESSION_AMOUNT;
            }else
            {
                eho(INFO, "Normal payment: ");
                $payment['years'] = $payment['amount'] / FULL_AMOUNT;                
            }

            $payment_modifier_amount = 1;
            
            // Added to deal with price increase of members
            if($payment['payment_date'] > "2011-07-01 00:00:00")
            {
                if($payment['amount'] == 2000 && $payment['type_id'] == 1)
                    $payment_modifier_amount = 2;
                if($payment['amount'] == 1000 && $payment['type_id'] == 2)
                    $payment_modifier_amount = 2;
            }
                
            $person->makePayment($payment['type_id'], $payment['years'], $payment['payment_date'], $payment['receipt_number'], false, $payment['id']);
            
            if($payment['id'] >= $next_paymentID)
                $next_paymentID = $payment['id'] + 1;
        
        }
        
            $person->set_status_group();
            $person->update_ldap();
        

        // Get all the userid's to dn for adding to groups later
        $userids[$user['uid']] = $dn;
                   
        flush();
    }

// TODO: create maxUid with maxUid and maxPaymentID values
//
$dn = 'cn=maxUid,ou=Users,dc=plug,dc=org,dc=au';
$maxUidArray = array(
    'objectClass' => array('namedObject', 'extensibleObject', 'top'),
    'uidNumber' => $nextuid,
    'cn' => 'maxUid',
    'x-plug-paymentID' => $next_paymentID
);
// delete old maxUid object
$ldapres = $ldap->delete($dn);
if (PEAR::isError($ldapres)) {
    eho(DEBUG, 'LDAP Error: '.$ldapres->getMessage() . "\n");
}      

$entry = Net_LDAP2_Entry::createFresh($dn, $maxUidArray);
$ldapres = $ldap->add($entry);
if (PEAR::isError($ldapres)) {
    eho(DEBUG, 'LDAP Error: '.$ldapres->getMessage() . "\n");
}
eho(INFO, "Adding maxUid object with nextuid as $nextuid and next_paymentID as $next_paymentID");

// System groups
$groups = $plugpgsql->queryAll("SELECT * from public.group");
foreach($groups as $group)
{
        $lgroup = array();
        $groupdn = "cn=${group['name']},ou=Groups,dc=plug,dc=org,dc=au";
        $lgroup['objectClass'] = array('top',  'posixGroup');
        $lgroup['gidNumber'] = $group['gid'];
        $lgroup['cn'] = $group['name'];
        
        $members = $plugpgsql->queryAll("select account.username from usergroup,account where usergroup.uid = account.uid and usergroup.gid=".$group['gid']);
        
        eho(INFO, "<p><h3>Group ${group['name']}</h3>");
        
        $ldapres = $ldap->delete($groupdn);
        if (PEAR::isError($ldapres)) {
            eho(DEBUG, 'LDAP Error: '.$ldapres->getMessage() . "\n");
        }        
        
        foreach($members as $member)
        {
            eho(INFO, $member['username'] . "\n");
            $person = new Person($ldap);
            $person->load_ldap($userids[$member['username']]);
            $person->add_to_group($group['name']);
            
        }
        
        eho(INFO, "</p>");
            

      
}

// Aliases


/* TODO: Aliases that aren't members
select alias from alias where alias not in (select username from account)
*/

eho(INFO, "'$valid' '$expired'");

function format_ph($number)
{
    $output = '';
    $number = ereg_replace("-", " ", $number);
    if(strlen($number) == 15)
        return $number; // Assume correct already
    $number = ereg_replace("[^+0-9]", "", $number);
    if(strlen($number) == 8)
    {   // assume WA
        $output = "+61 8 ".substr($number, 0, 4). " " . substr($number, 4, 4);
    }elseif(strlen($number) == 10)
    {
        //Assume mob
        $output = "+61 4 ".substr($number, 2, 4). " " . substr($number, 6, 4);        
    }
    elseif($number != ''){
        //echo "BLAH" . $number. "BLAH";
        $output = $number;
    }
    return $output;
}

/*function member_payment($memberdn, $paymentid, $paymenttype, $amount, $date, $description)
{
    global $ldap;
    $dn = "x-plug-paymentID=$paymentid,$memberdn";
    $payment['objectClass'] = array('top', 'x-plug-payment');
    $payment['x-plug-paymentAmount'] = $amount;
    $payment['x-plug-paymentDate'] = date('YmdHis',strtotime($date)). "+0800";
    $payment['x-plug-paymentID'] = $paymentid;
    $payment['x-plug-paymentType'] = $paymenttype;
    $payment['x-plug-paymentDescription'] = $description;
    if($paymenttype == 2)
    {
        // Concession
        $payment['x-plug-paymentYears'] = $amount / 500;
    }else
    {
        // Assume full
        $payment['x-plug-paymentYears'] = $amount / 1000;        
    }
    
    $payment = array_filter($payment);
    
    //echo "Adding user payment $paymentid</br>";
    //print_r($payment);
    $ldap->delete($dn);
    $entry = Net_LDAP2_Entry::createFresh($dn, $payment);
    
    $ldapres = $ldap->add($entry);
    if (PEAR::isError($ldapres)) {
        eho(ERROR, 'LDAP Error: '.$ldapres->getMessage());
    }
}*/

/*function valid_member($dn, $cn = "currentmembers", $gid = FALSE, $upg = FALSE)
{
    global $ldap;
    
//    if($gid)
//    {
//        $groupdn = "gidNumber=$gid,ou=Groups,dc=plug,dc=org,dc=au";    
    if($upg){
            $groupdn = "gidNumber=$gid,ou=UPG,ou=Groups,dc=plug,dc=org,dc=au";    
    }else
    {
        $groupdn = "cn=$cn,ou=Groups,dc=plug,dc=org,dc=au";
    }
    
    if($ldap->dnExists($groupdn))
    {
        eho(DEBUG, "Adding member $dn ($cn)");
        $entry = $ldap->getEntry($groupdn, array('member'));

        if (PEAR::isError($entry)) {
            die('LDAP Error: '.$entry->getMessage());
        }
        
        $members = $entry->getValue('member');
        
        //print_r($members);
        //echo gettype($members);
        $result = FALSE;
        if(is_array($members))
            $result = in_array($dn, $members);
        //print_r($result);
        //echo "<br/>'$members'<br/>";
        //echo "<br/>'$dn'<br/>";        
        
        if(!$result && $members != $dn)
        {
        
            $ldapres= $entry->add(array('member' => $dn));
            

            if (PEAR::isError($ldapres)) {
                die('LDAP Error: '.$ldapres->getMessage());
            }
            
            $ldapres = $entry->update();
            
            if (PEAR::isError($ldapres)) {
                die('LDAP Error: '.$ldapres->getMessage());
            }  
        }else{
            eho(DEBUG, "Already in group");   
                  
            



            $ldapres = $entry->update();

            if (PEAR::isError($ldapres)) {
                die('3LDAP Error: '.$ldapres->getMessage());
            }  
             
        }
    
    }else
    {
    
        eho(DEBUG, "Creating new group with member $dn ($cn)");
        $attrs = array('objectClass' => 'groupOfNames', 'cn' => $cn, 'member' => $dn);
        if($gid)
            $attrs = array('objectClass' => array('groupOfNames', 'posixGroup'), 'cn' => $cn, 'member' => $dn, 'gidNumber' => $gid);
        $entry = Net_LDAP2_Entry::createFresh($groupdn, $attrs);
        
        $ldapres = $ldap->add($entry);
        if (PEAR::isError($ldapres)) {
            eho(ERROR, 'LDAP Error: '.$ldapres->getMessage());
        }   

    }
    
    
}*/

   $mtime = microtime();
   $mtime = explode(" ",$mtime);
   $mtime = $mtime[1] + $mtime[0];
   $endtime = $mtime;
   $totaltime = round(($endtime - $pagestarttime), 2);
   eho(0, "Page generated in ".$totaltime." seconds using " .  memory_get_peak_usage(true)/1024/1024 . "Mb mem");
   
function eho ($debug, $text = 'x')
{
    if($text == 'x')
    {
         $text = $debug;
         $debug = DEBUG;
    }
    if($debug <= DEBUGLEVEL)
    {
        if(defined('STDIN'))
        {
            echo strip_tags("$text\n");
        }
        else
        {
            echo "$text<br/>";
        }
    }
}