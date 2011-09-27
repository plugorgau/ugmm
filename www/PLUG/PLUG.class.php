<?php

require_once('ldapconnection.inc.php');

define('CONCESSION_AMOUNT', 1000);
define('FULL_AMOUNT', 2000);

define('CONCESSION_TYPE', 2);
define('FULL_TYPE', 1);

define('COMMITTEE_EMAIL', "committee@plug.org.au");
define('CONTACT_EMAIL', "committee@plug.org.au");
//define('ADMIN_EMAIL', "admin@plug.org.au");
define('SCRIPTS_FROM_EMAIL', "PLUG Membership Scripts <admin@plug.org.au>");
define('SCRIPTS_REPLYTO_EMAIL', "PLUG Committee <committee@plug.org.au>");

define('DEFAULT_MEMBER', 'cn=admin,dc=plug,dc=org,dc=au');

// For debugging, remove later TODO:
define('ADMIN_EMAIL', "linuxalien@plug.org.au");

if(!defined('FORCE'))
    define('FORCE', false);

class PLUG {

    // Class for plug, contains members of type Member/Person
   
    private $currentmembers;
    private $expiredmembers;
    private $pendingmembers;        
   
    private $ldap;
    
    function __construct($ldap)
    {
        $this->ldap = $ldap;
    }
    
    private function load_ldapmembers_from_group($group)
    {
        // Fetch entry for group and all member attributes
        $dn = "cn=$group,ou=Groups,dc=plug,dc=org,dc=au";
        $entry = $this->ldap->getEntry($dn, array('member'));
        
        if (PEAR::isError($entry)) {
            throw new Exception('LDAP Error: '.$entry->getMessage());
        }         

        // Load all members
        $members = $entry->getValue('member');
        asort($members);
        
        foreach($members as $member)
        {
            if($member == DEFAULT_MEMBER) continue;
            
            $thismember = new Person($this->ldap);
            $thismember->load_ldap($member);
            $memberdetails[] = $thismember->userarray();

        }       
        
        
        return $memberdetails;
    }   
    
    function load_current_members()
    {
        $this->currentmembers = $this->load_ldapmembers_from_group('currentmembers');
        // TODO load Payments
    }
    
    function load_expired_members()
    {
        $this->expiredmembers = $this->load_ldapmembers_from_group('expiredmembers');    
        // TODO load Payments        
    }
    
    function load_pending_members()
    {
        $this->pendingmembers = $this->load_ldapmembers_from_group('pendingmembers');    
    }    
    
    function get_current_members()
    {
        $this->load_current_members();
        return $this->currentmembers;
    }
    
    function get_expired_members()
    {
        $this->load_expired_members();
        return $this->expiredmembers;
    }    
    
    function get_pending_members()
    {
        $this->load_pending_members();
        return $this->pendingmembers;
    }
    
    function get_member_object($uidNumber)
    {
        $uidNumber = intval($uidNumber); // Sanitise 
        $dn = "uidNumber=$uidNumber,ou=Users,dc=plug,dc=org,dc=au";    
        if($this->ldap->dnExists($dn))
        {
            $thismember = new Person($this->ldap);
            $thismember->load_ldap($dn);
            $thismember->load_payments();
            return $thismember;    
        }
        return PEAR::raiseError (
            _('User not found. Invalid UID number'),
            -10,
            PEAR_ERROR_RETURN);
    }
    
    function get_member_by_email($email)
    {
        $filter = Net_LDAP2_Filter::create('mail', 'equals',  $email);
        $searchbase = "ou=Users,dc=plug,dc=org,dc=au";
        $options = array(
            'scope' => 'one',
            'attributes' => array(
                'uidNumber')
            );
            
        $search = $this->ldap->search($searchbase, $filter, $options);
        
        if (PEAR::isError($search)) {
           throw new Exception($search->getMessage() . "\n");
        }
        
        if($search->count() != 1)
        {
            // This can be caused by more than one user account being registered to the address
            // Incorrect number, return error
            return FALSE;
        }
        
        $entry = $search->shiftEntry();

        return $this->get_member_object($entry->getValue('uidNumber', 'single'));
    
    }
    

/*    function get_member($uidNumber)
    {
        $uidNumber = intval($uidNumber); // Sanitise 
        $dn = "uidNumber=$uidNumber,ou=Users,dc=plug,dc=org,dc=au";    
        $thismember = new Person($this->ldap);
        $thismember->load_ldap($dn);
        $thismember->load_payments();
        return $thismember->userarray();    
    }*/

    
    function check_username_available($username)
    {
        $filter = Net_LDAP2_Filter::create('uid', 'equals',  $username);
        $searchbase = 'ou=Users,dc=plug,dc=org,dc=au';
        $options = array(
            'scope' => 'one',
            'attributes' => array('dn'),
            'sizelimit' => 1
        );
        
        $search = $this->ldap->search($searchbase, $filter, $options);

        if (PEAR::isError($search)) {
            throw new Exception($search->getMessage() . "\n");
        }

        return $search->count();
    }
    
    function next_uidNumber()
    {
        $dn = "cn=maxUid,ou=Users,dc=plug,dc=org,dc=au";
        // Get next uidNumber from maxUid
        
        $entry = $this->ldap->getEntry($dn, array('uidNumber'));
        
        if (PEAR::isError($entry)) {
            throw new Exception('LDAP Error: '.$entry->getMessage());
        }         

        $uidNumber = $entry->getValue('uidNumber');
        
        // Check if it's actually free
        $uidNumber = $this->next_freeuidNumber($uidNumber);

        // Increment maxUid        
        $entry->replace(array(
            'uidNumber' => $uidNumber + 1));
        
        $result = $entry->update();
        
        if (PEAR::isError($result)) {
            throw new Exception('LDAP Error: '.$result->getMessage());
        }   
        
        return $uidNumber;        

    }
    
    function next_freeuidNumber($uidNumber)
    {
        // Loop checking that it's actually available
        while($this->ldap->dnExists("uidNumber=$uidNumber,ou=Users,dc=plug,dc=org,dc=au"))
            $uidNumber++;
        return $uidNumber;
    }
    
    /*function next_paymentID()
    {
        $dn = "cn=maxUid,ou=Users,dc=plug,dc=org,dc=au";
        // Get next paymentID from maxUid
        
        $entry = $this->ldap->getEntry($dn, array('x-plug-paymentID'));
        
        if (PEAR::isError($entry)) {
            throw new Exception('LDAP Error: '.$entry->getMessage());
        }         

        $paymentID = $entry->getValue('x-plug-paymentID');

        // Increment maxUid        
        $entry->replace(array(
            'x-plug-paymentID' => $paymentID + 1));
        
        $result = $entry->update();
        
        if (PEAR::isError($result)) {
            throw new Exception('LDAP Error: '.$result->getMessage());
        }   
        
        return $paymentID;        

    }*/
    
    function new_member($username, $firstname, $lastname, $address, $home, $work, $mobile, $email, $password, $notes)
    {
        $newmember = new Person($this->ldap);
        $pendingID = isset($_SESSION['pendingID']) ? $this->next_freeuidNumber($_SESSION['pendingID']) : $this->next_uidNumber();
        $newmember->create_person($pendingID, $username, $firstname, $lastname, $address, $home, $work, $mobile, $email, '', $password, $notes);
        if($newmember->is_error())
        {
            $_SESSION['pendingID'] = $pendingID;
        }
        else
        {
            unset($_SESSION['pendingID']);
        }
        return $newmember;        
    }
    
    function delete_member($dn)
    {
        if($this->ldap->dnExists($dn))
            $this->ldap->delete($dn, TRUE);
    } 

}

class Payment
{
    private $dn;
    private $ldap;
    private $paymentarray = array(
        'objectClass' => array('top', 'x-plug-payment'),
        'x-plug-paymentAmount' => 0,
        'x-plug-paymentDate' => '',
        'x-plug-paymentID' => '',
        'x-plug-paymentType' => '',
        'x-plug-paymentDescription' => '',
        'x-plug-paymentYears' => 0); 
        
    function __construct($ldap)
    {
        $this->ldap = $ldap;
    }
    
    function load_ldap($dn)
    {
        $this->dn = $dn;
        $this->ldapentry = $this->ldap->getEntry($dn, array(
            'objectClass',
            'x-plug-paymentAmount',
            'x-plug-paymentDate',
            'x-plug-paymentID',
            'x-plug-paymentType',
            'x-plug-paymentDescription',
            'x-plug-paymentYears'
            ));
        if (PEAR::isError($this->ldapentry)) {
            throw new Exception('LDAP Error: '.$this->ldapentry->getMessage());
        }        
        
        $this->paymentarray = $this->ldapentry->getValues();
        //$this->userorigldaparray = $this->userldaparray;
        //$this->explode_user_ldap_array();

    }    
    
/*    function new_payment($parentdn, $id, $type, $amount, $date, $description)
    {
        $this->dn = "x-plug-paymentID=$id,$parentdn";
        $this->paymentarray['x-plug-paymentAmount'] = $amount;
        $this->paymentarray['x-plug-paymentDate'] = date('YmdHis',strtotime($date)). "+0800";
        $this->paymentarray['x-plug-paymentID'] = $id;
        $this->paymentarray['x-plug-paymentType'] = $type;
        $this->paymentarray['x-plug-paymentDescription'] = $description;
        if($type == CONCESSION_TYPE)
        {
            // Concession
            $this->paymentarray['x-plug-paymentYears'] = $amount / CONCESSION_AMOUNT;
        }else
        {
            // Assume full
            $this->paymentarray['x-plug-paymentYears'] = $amount / FULL_AMOUNT;        
        }
        
        $this->create_new_ldap_payment();
    }*/
    function new_payment($parentdn, $type, $years, $date, $description, $id = false)
    {
        global $payment_modifier_amount; //Hack for change in payment amounts
        if(!isset($payment_modifier_amount)) $payment_modifier_amount = 1;
        if(! $id)
            $id = $this->next_paymentID();
            
        $this->dn = "x-plug-paymentID=$id,$parentdn";
        $this->paymentarray['x-plug-paymentYears'] = $years;
        $this->paymentarray['x-plug-paymentDate'] = date('YmdHis',strtotime($date)). "+0800";
        $this->paymentarray['x-plug-paymentID'] = $id;
        $this->paymentarray['x-plug-paymentType'] = $type;
        $this->paymentarray['x-plug-paymentDescription'] = $description;
        if($type == CONCESSION_TYPE)
        {
            // Concession
            $this->paymentarray['x-plug-paymentAmount'] = $years * CONCESSION_AMOUNT * $payment_modifier_amount;
        }else
        {
            // Assume full
            $this->paymentarray['x-plug-paymentAmount'] = $years * FULL_AMOUNT * $payment_modifier_amount;        
        }
        
        $this->create_new_ldap_payment();
    }    
    
    function paymentarray()
    {
        return $this->paymentarray;
    }    
    
    private function create_new_ldap_payment()
    {
        // TODO: Check if exists first
        $entry = Net_LDAP2_Entry::createFresh($this->dn, array_filter($this->paymentarray));
        $ldapres = $this->ldap->add($entry);
        if (PEAR::isError($ldapres)) {
            throw new Exception('LDAP Error: '.$ldapres->getMessage()); //TODO: Better error handling
        }           
    }
    
    private function next_paymentID()
    {
        $dn = "cn=maxUid,ou=Users,dc=plug,dc=org,dc=au";
        // Get next paymentID from maxUid
        
        $entry = $this->ldap->getEntry($dn, array('x-plug-paymentID'));
        
        if (PEAR::isError($entry)) {
            throw new Exception('LDAP Error: '.$entry->getMessage());
        }         

        $paymentID = $entry->getValue('x-plug-paymentID');
        
        // Search and ensure not already exists
        $filter2 = Net_LDAP2_Filter::combine('not', Net_LDAP2_Filter::create('cn', 'equals',  'maxUid'));        
        do{
            $filter1 = Net_LDAP2_Filter::create('x-plug-paymentID', 'equals',  $paymentID);
            $filter = Net_LDAP2_Filter::combine('and', array($filter1, $filter2));
            $searchbase = "ou=Users,dc=plug,dc=org,dc=au";
            $options = array(
                'scope' => 'sub',
                'attributes' => array(
                    'uidNumber')
                );
                
            $search = $this->ldap->search($searchbase, $filter, $options);
            
            if (PEAR::isError($search)) {
               throw new Exception($search->getMessage() . "\n");
            }
            
            $paymentID ++;
        
        }while($search->count() != 0);


        // Increment maxUid        
        $entry->replace(array(
            'x-plug-paymentID' => $paymentID));
        
        $result = $entry->update();
        
        if (PEAR::isError($result)) {
            throw new Exception('LDAP Error: '.$result->getMessage());
        }   
        
        return $paymentID - 1;        

    }                
}

class Person {
    private $dn;
/*    private $uid;
    private $displayName;
    private $uidNumber;
    private $gidNumber;
    private $homeDirectory;
    private $userPassword;
    private $loginShell;
    private $mail = array();
    private $givenName;
    private $sn;
    private $cn;
    private $street;
    private $homeTelephoneNumber;
    private $mobileTelephoneNumber;
    private $pagerTelephoneNumber;
    private $description;
    
    private $objectClass = array('top',  'person', 'posixAccount', 'inetOrgPerson');*/
    
    private $userldaparray;
    private $userorigldaparray;   
    private $ldapentry; 
    
    private $payments = array();
    
    private $ldap;
    
    private $errors = array();
    private $messages = array();
    private $errorstate = FALSE;
    private $passworderrors = array();
    
    function __construct($ldap)
    {
        $this->ldap = $ldap;
        
        $this->userldaparray = array(
            'uid' => '',
            'displayName' => '',
            'uidNumber' => '',
            'gidNumber' => '',
            'homeDirectory' => '',
            'userPassword' => '',
            'loginShell' => '',
            'mail' => '',
            'mailForward' => '',
            'givenName' => '',
            'sn' => '',
            'cn' => '',
            'street' => '',
            'homePhone' => '',
            'mobile' => '',
            'pager' => '',
            'description' => '',
            'shadowExpire' => '1', // Start all users off as expired
            'objectClass' => array('top', 'person', 'posixAccount', 'inetOrgPerson', 'shadowAccount', 'mailForwardingAccount'),            
            );
    }
    
    function load_ldap($dn)
    {
        $this->dn = $dn;
        $this->ldapentry = $this->ldap->getEntry($dn, array(
            'objectClass',
            'uid',
            'displayName',
            'uidNumber',
            'gidNumber',
            'homeDirectory',
            'userPassword',
            'loginShell',
            'mail',
            'mailForward',
            'givenName',
            'sn',
            'cn',
            'street',
            'homePhone',
            'mobile',
            'pager',
            'description',
            'shadowExpire',
            'memberOf'));
        if (PEAR::isError($this->ldapentry)) {
            throw new Exception('LDAP Error: load_ldap: '.$this->ldapentry->getMessage());
        }        
        
        $this->userldaparray = $this->ldapentry->getValues();
        $this->userorigldaparray = $this->userldaparray;
        //$this->explode_user_ldap_array();
        //$this->load_payments();

    }
    
    function create_person($uid, $username, $firstname, $lastname, $address, $home, $work, $mobile, $email, $forward, $password, $notes)
    {
        $this->dn = "uidNumber=$uid,ou=Users,dc=plug,dc=org,dc=au";
        $this->change_uid($uid, $uid);
        $this->change_username($username);
        $this->change_name($firstname, $lastname);
        $this->change_address($address);
        $this->change_phone($home, $work, $mobile);
        $this->change_shell("/bin/bash");
        $this->change_homedir("/home/$username");
        $this->change_email($email);
        $this->change_forward($forward);        
        $this->change_password($password);
        $this->change_description($notes);
        if(! $this->is_error())
        {
            $this->create_new_ldap_person();
            $this->create_new_ldap_group();
            // Extra call, but allows us to continue working with a new object.
            $this->load_ldap($this->dn);

            $this->set_status_group();

        }
        
    }
    
    function is_error()
    {
        $this->errors = array_filter($this->errors);
        if(sizeof($this->errors)) $this->errorstate = TRUE;
        return $this->errorstate;
    }
    
    function get_errors()
    {
        $this->errors = array_filter($this->errors);
        return $this->errors;
    }
    
    function get_messages()
    {
        $this->messages = array_filter($this->messages);
        return $this->messages;
    }    
    
    function change_expiry($date) // $date as string
    {
        // TODO: UTC issues?
        // $date needs to be converted to DAYS since epoch
        // We strtotime the date, divide by 86400, round down
        // Take the ABS so that -1 becomes 1 as -1 is never expire
        $this->userldaparray['shadowExpire'] = abs(floor(strtotime($date)/ 86400));
    }
    
    function increase_expiry($years)
    {
        /*// To allow for leap years, do this fancy instead of just $years * 365
        $date = date("YmdHis",$this->userldaparray['shadowExpire'] * 86400) . " + $years years";
        print_r(array($date));
        if(!strtotime($date))
            throw new Exception("Invalid date");
        $this->userldaparray['shadowExpire'] = abs(floor(strtotime($date)/ 86400));*/
        
        // Misses a day for leap years, oh well.
        $this->userldaparray['shadowExpire'] += $years * 365;
    }    
    
    function change_name($firstname, $lastname)
    {
        if($firstname == '')
        {
            $this->errors[] = "Firstname is required";
        }
        $lastname = $lastname ? $lastname : "_";
        if($firstname != $this->userldaparray['givenName'] ||
            $lastname != $this->userldaparray['sn'])
        {
            $this->userldaparray['sn'] = $lastname;
            $this->userldaparray['givenName'] = $firstname;
            $this->userldaparray['displayName'] = "$firstname $lastname";
            $this->userldaparray['cn'] = "$firstname $lastname";
            
            $this->messages[] = "Name changed";
        }
        
    }
    
    function change_address($address)
    {
        if($address == '')
        {
            $this->errors[] = "Address is required";
        }
        if($address != $this->userldaparray['street'])
        {
            $this->userldaparray['street'] = $address;
            
            $this->messages[] = "Address changed";
        }
    }
    
    function change_username($username)
    {
        if($this->userldaparray['uid'] == '' || $username != $this->userldaparray['uid'])
        {
            if($username == '')
            {
                $this->errors[] = "Username required";
            }elseif(strlen($username) < 3 && ! FORCE)
            {
                $this->errors[] = "Username must be at least 3 characters long";
            }elseif($this->check_username_available($username))
            {
                $this->userldaparray['uid'] = $username;
                $this->messages[] = "Username changed";
            }else{
                $this->errors[] = "Username not available";
            }
        }
    }
    
    private function change_uid($uid, $gid)
    {
        if($uid < 10000 || $gid < 10000 || $uid == '' || $gid == '')
        {
            $this->errors[] = "UID or GID out of Range";
        }
        else
        {
            $this->userldaparray['uidNumber'] = $uid;
            $this->userldaparray['gidNumber'] = $gid;
        }
    }
    
    function change_shell($loginShell)
    {
        if($loginShell != $this->userldaparray['loginShell'])
        {
            $this->userldaparray['loginShell'] = $loginShell;
            $this->messages[] = "Shell details changed";
        }
    }
    
    private function change_homedir($homedir)
    {
        if($homedir != $this->userldaparray['homeDirectory'])
        {
            $this->userldaparray['homeDirectory'] = $homedir;
            $this->messages[] = "Home directory changed";
        }    
    
    }
    
    function change_email($email)
    {
        if($email == '' && ! FORCE)
        {
            $this->errors[] = 'Email address required';
        }
        if($email != $this->userldaparray['mail'])
        {
            if($this->check_email_available($email) != 0 && ! FORCE)
            {
                // Check that email isn't already registered
                $this->errors[] = "Email address($email) is already registered. Please use another email address for password recovery purposes";
            }elseif(filter_var($email, FILTER_VALIDATE_EMAIL))
            {
                $this->userldaparray['mail'] = $email;
                $this->messages[] = "Email changed";
            }else
            {
                $this->errors[] = "Invalid email address '$email'";
            }
        }
    }
    
    function change_forward($forward)
    {
        if($forward != $this->userldaparray['mailForward'])
        {
            if($forward == "" || filter_var($forward, FILTER_VALIDATE_EMAIL))
            {
                $this->userldaparray['mailForward'] = $forward;
                $this->messages[] = "Email forwarding changed";
            }else
            {
                $this->errors[] = "Invalid email address for forwarding '$forward'";
            }
        }
        

    }    
    
    function change_password($password)
    {
        // Can't check if password hasn't changed, so always do this
        // Check if we are passing an already crypted password
        if(substr($password, 0, 7) == '{crypt}')
        {
            $this->userldaparray['userPassword'] = $password;
        }else
        {
            $this->userldaparray['userPassword'] = '{crypt}'.createPasswordHash($password);
        }
        $this->messages[] = "Password changed";
        
        // Just update ldap?
        // $this->update_ldap();
    }
    
    function change_phone($home, $work, $mobile)
    {
        $this->userldaparray['homePhone'] = $home;
        $this->userldaparray['mobile'] = $mobile;
        $this->userldaparray['pager'] = $work;                
    }
    
    function change_description($description)
    {
        $this->userldaparray['description'] = $description;
    }
    
    function disable_shell()
    {
        return $this->remove_from_group('shell');
    }
    
    function enable_shell()
    {
        return $this->add_to_group('shell');
    }
    
    private function create_user_ldap_array()
    {
/*        $this->userldaparray['objectClass'] = $this->objectClass;
        $this->userldaparray['uid'] = $this->uid;
        $this->userldaparray['displayName'] = $this->displayName;
        $this->userldaparray['uidNumber'] = $this->uidNumber;
        $this->userldaparray['gidNumber'] = $this->gidNumber;
        $this->userldaparray['homeDirectory'] = $this->homeDirectory;
        $this->userldaparray['userPassword'] = $this->userPassword;
        $this->userldaparray['loginShell'] = $this->loginShell;
        $this->userldaparray['mail'] = $this->mail;
        $this->userldaparray['givenName'] = $this->givenName;
        $this->userldaparray['sn'] = $this->sn;
        $this->userldaparray['cn'] = $this->cn;
        $this->userldaparray['street'] = $this->street;
        $this->userldaparray['homeTelephoneNumber'] = $this->homeTelephoneNumber;
        $this->userldaparray['mobileTelephoneNumber'] = $this->mobileTelephoneNumber;
        $this->userldaparray['pagerTelephoneNumber'] = $this->pagerTelephoneNumber;
        $this->userldaparray['description'] = $this->description;*/
        
        //$this->userldaparray = array_filter($this->userldaparray);
    }
    
    private function explode_user_ldap_array()
    {
/*        $this->objectClass = $this->userldaparray['objectClass'];
        $this->uid = $this->userldaparray['uid'];
        $this->displayName = $this->userldaparray['displayName'];
        $this->uidNumber = $this->userldaparray['uidNumber'];
        $this->gidNumber = $this->userldaparray['gidNumber'];
        $this->homeDirectory = $this->userldaparray['homeDirectory'];
        $this->userPassword = $this->userldaparray['userPassword'];
        $this->loginShell = $this->userldaparray['loginShell'];
        $this->mail = $this->userldaparray['mail'];
        $this->givenName = $this->userldaparray['givenName'];
        $this->sn = $this->userldaparray['sn'];
        $this->cn = $this->userldaparray['cn'];
        $this->street = $this->userldaparray['street'];
        $this->homeTelephoneNumber = $this->userldaparray['homeTelephoneNumber'];
        $this->mobileTelephoneNumber = $this->userldaparray['mobileTelephoneNumber'];
        $this->pagerTelephoneNumber = $this->userldaparray['pagerTelephoneNumber'];
        $this->description = $this->userldaparray['description'];*/
    }
    
    private function create_new_ldap_person()
    {
        // TODO: Check if exists first
        $entry = Net_LDAP2_Entry::createFresh($this->dn, array_filter($this->userldaparray));
        $ldapres = $this->ldap->add($entry);
        if (PEAR::isError($ldapres)) {
            throw new Exception('LDAP Error: '.$ldapres->getMessage()); //TODO: Better error handling
        }
        // Overwrite other messages in messages array
        $this->messages = array("New member created with id " . $this->userldaparray['uidNumber']);
        // Also create group           
        return TRUE;        
    }
    
    private function create_new_ldap_group()
    {
        $gid = $this->userldaparray['gidNumber'];
        $group = array(
            'gidNumber' => $gid,
            'cn' => $this->userldaparray['uid'],
            'member' => $this->dn,
            'objectClass' => array('groupOfNames', 'posixGroup')
        );
        $dn = "gidNumber=$gid,ou=UPG,ou=Groups,dc=plug,dc=org,dc=au";
        
        $entry = Net_LDAP2_Entry::createFresh($dn, $group);
        
        $ldapres = $this->ldap->add($entry);
        
        if (PEAR::isError($ldapres)) {
            throw new Exception('LDAP Error: '.$ldapres->getMessage()); //TODO: Better error handling
        }
        //$this->messages[] = "Group Created";

    }
    
    private function create_new_ldap_system_group($dn, $cn)
    {
        $gids = array(
            'admin' => 1001,
            'committee' => 1006,
            'webslave' => 1003,
            'librarian' => 1007,
            'shell' => 1009
        );
        
        if(isset($gids[$cn])){
            $gid = $gids[$cn];
            $group = array(
                'gidNumber' => $gid,
                'cn' => $cn,
                'member' => DEFAULT_MEMBER,
                'objectClass' => array('groupOfNames', 'posixGroup')
            );
        }else{
            $group = array(
                'cn' => $cn,
                'member' => DEFAULT_MEMBER,
                'objectClass' => array('groupOfNames')
            );
        }
        
        $entry = Net_LDAP2_Entry::createFresh($dn, $group);
        
        $ldapres = $this->ldap->add($entry);
        
        if (PEAR::isError($ldapres)) {
            throw new Exception('LDAP Error: '.$ldapres->getMessage()); //TODO: Better error handling
        }
        //$this->messages[] = "Group Created";

    }    
    
    public function update_ldap()
    {
        //$this->create_user_ldap_array();
        /*print_r(array_diff_assoc(
                $this->userldaparray,
                $this->userorigldaparray
            ));*/


        $this->ldapentry->replace(
            array_diff_assoc(
                $this->userldaparray,
                $this->userorigldaparray
            )
        );
        $result = $this->ldapentry->update();
        
        if (PEAR::isError($result)) {
            throw new Exception('LDAP Error: '.$result->getMessage());
        }          

        // Extra call, but allows us to continue working with a new object.
        $this->load_ldap($this->dn);
    }
    
    private function memberOf_filter()
    {
        $filtergroups = array('expiredmembers', 'currentmembers', 'pendingmembers', 'shell');
        // Filters memberOf to just give system group names
        $groups = $this->userldaparray['memberOf'];
        if(!is_array($groups)) $groups = array($groups);
        $validgroups = array();
        foreach($groups as $group)
        {
            if(strpos($group, 'cn=') !== FALSE)
            {
                // This is a group with name, process it
                $dnparts = explode(',',$group);
                $cn = explode('=', $dnparts[0]);
                if(! in_array($cn[1], $filtergroups))
                {
                    $validgroups[] = $cn[1];
                }
                $allgroups[] = $cn[1];
            }
        }
        
        // Don't store this is userldaparray as it's not a valid attribute
        return array($validgroups, $allgroups);
    }
    
    private function is_shell_enabled()
    {
        list($validgroups, $allgroups) = $this->memberOf_filter();
        if(in_array('shell', $allgroups)) return true;
        return false;
    }
    
    private function is_membership_current()
    {
        list($validgroups, $allgroups) = $this->memberOf_filter();
        if(in_array('currentmembers', $allgroups)) return true;
        return false;
    }    
    
    function expiry()
    {
        // * 86400 to get from days to seconds
        return array(
            'expiry' => date("d M y", $this->userldaparray['shadowExpire'] * 86400),
            'formattedexpiry' => date("l, d F Y", $this->userldaparray['shadowExpire'] * 86400)
            );
    }
    
    function set_status_group()
    {
        // Due to memberOf being out of sync, we need to sync the ldap object 
        $this->update_ldap();
        
        $groups = array('pendingmembers', 'expiredmembers', 'currentmembers');
        // Grace period of 5 days
        $today = ceil(time()/ 86400) - 5;
        if($this->userldaparray['shadowExpire'] <= 1)
        {
            // Pending group
            $validgroup = 'pendingmembers';
        }elseif($this->userldaparray['shadowExpire'] < $today)
        {
            // Expired
            $validgroup = 'expiredmembers';
        }else
        {
            // Current member
            $validgroup = 'currentmembers';
        }
        
        foreach($groups as $group)
        {
            if($validgroup == $group)
            {
                $this->add_to_group($group);
            }else
            {
                $this->remove_from_group($group);
            }
        }
        
    }
    
    function add_to_group($group)
    {
        $groupdn = "cn=$group,ou=Groups,dc=plug,dc=org,dc=au";
        $groups = is_array(@$this->userldaparray['memberOf']) ? @$this->userldaparray['memberOf'] : array(@$this->userldaparray['memberOf']);
        if(!in_array($groupdn, $groups))
        {
            //echo "Adding to group $groupdn";
            //print_r($this->userldaparray['memberOf']);
            // Fetch entry for group and all member attributes
            if(! $this->ldap->dnExists($groupdn))
                $this->create_new_ldap_system_group($groupdn, $group);
                
            $entry = $this->ldap->getEntry($groupdn, array('member'));
            
            if (PEAR::isError($entry)) {
               throw new Exception($entry->getMessage() . "\n");
            }
            

            $members = $entry->getValue('member', 'all');
       
            // Double check before attempt add. memberOf can be out of sync and it's cheap to check
            if(! in_array($this->dn, $members))
            {
                $entry->add(
                    array(
                        'member' => $this->dn
                    )
                );
                
                $res = $entry->update();
                
                if (PEAR::isError($res)) {
                   throw new Exception($res->getMessage() . "\n");
                }
                
            }
            
        }
        
        return TRUE; // TODO: return error if error?
    }
    
    function remove_from_group($group)
    {
        $groupdn = "cn=$group,ou=Groups,dc=plug,dc=org,dc=au";    
        $groups = is_array(@$this->userldaparray['memberOf']) ? @$this->userldaparray['memberOf'] : array(@$this->userldaparray['memberOf']);
        
        if(in_array($groupdn, $groups))
        {
            // Fetch entry for group and all member attributes
            $entry = $this->ldap->getEntry($groupdn, array('member'));
            
            if (PEAR::isError($entry)) {
               throw new Exception($entry->getMessage() . "\n");
            }
            
            $members = $entry->getValue('member', 'all');
            
            // Double check before attempt remove. memberOf can be out of sync and it's cheap to check
            if(in_array($this->dn, $members))
            {
              
                $entry->delete(
                    array(
                        'member' => $this->dn
                    )
                );
                
                $res = $entry->update();
                
                if (PEAR::isError($res)) {
                   throw new Exception($res->getMessage() . "\n");
                }
            }
            
        }
        
        return TRUE; // TODO: return error if error?
    }
    
    /* Display and Get functions */
    
    public function print_ldif()
    {
        echo "<p><h3>".$this->dn."</h3>\n";
        
        foreach($this->userldaparray as $attribute => $value){

            if(is_array($value))
            {  
                foreach($value as $val)
                    echo "$attribute: $val<br/>\n";
            }else
            {
                echo "$attribute: $value<br/>\n";
            }
        }
        
        echo "</p>\n";
    }
    
    function userarray()
    {
        list($sysgroups, $allgroups) = $this->memberOf_filter();
        return array_merge(
            $this->userldaparray,
            array( 'groups' => $sysgroups ),
            array( 'allgroups' => $allgroups ), 
            array( 'shellEnabled' => $this->is_shell_enabled()),
            array( 'membershipCurrent' => $this->is_membership_current()),
            $this->expiry(),
            array( 'payments' => $this->payments)
            );
    }
    
    function load_payments()
    {
        $filter = Net_LDAP2_Filter::create('objectClass', 'equals',  'x-plug-payment');
        $searchbase = $this->dn;
        $options = array(
            'scope' => 'sub',
            'attributes' => array(
                'dn',
                'x-plug-paymentAmount',
                'x-plug-paymentDate',
                'x-plug-paymentID',
                'x-plug-paymentType',
                'x-plug-paymentDescription',
                'x-plug-paymentYears')
            );
            
        $search = $this->ldap->search($searchbase, $filter, $options);
        
        if (PEAR::isError($search)) {
           throw new Exception($search->getMessage() . "\n");
        }
        
        $payments = $search->sorted_as_struct(array('x-plug-paymentID'));
        
        

        foreach($payments as $payment)
        {
            $this->clean_payment_struct($payment);
            // smarty tempalte doesn't like - in var names        
            /*$cleanpayment = array();
            $cleanpayment['amount'] = $payment['x-plug-paymentAmount'][0];
            $cleanpayment['date'] = $payment['x-plug-paymentDate'][0];
            $cleanpayment['id'] = $payment['x-plug-paymentID'][0];
            $cleanpayment['type'] = $payment['x-plug-paymentType'][0];
            $cleanpayment['years'] = $payment['x-plug-paymentYears'][0];
            $cleanpayment['dn'] = $payment['dn'];
            $cleanpayment['description'] = $payment['x-plug-paymentDescription'][0];
            $cleanpayment['formatteddate'] = date('Y-m-d', strtotime($cleanpayment['date']));
            $cleanpayment['formattedamount'] = sprintf("$%.2f",$cleanpayment['amount']/100);
            $cleanpayment['formattedtype'] = $cleanpayment['type'] == FULL_TYPE ? "Full" : "Concession";
            $this->payments[$cleanpayment['id']] = $cleanpayment;*/

        }
        
        arsort($this->payments);
/*        echo "<pre>";
        print_r($this->payments);
        echo "</pre>"; */       
    
    }
    
    function clean_payment_struct($payment)
    {
            // smarty tempalte doesn't like - in var names        
            $cleanpayment = array();
            $cleanpayment['amount'] = $payment['x-plug-paymentAmount'][0];
            $cleanpayment['date'] = $payment['x-plug-paymentDate'][0];
            $cleanpayment['id'] = $payment['x-plug-paymentID'][0];
            $cleanpayment['type'] = $payment['x-plug-paymentType'][0];
            $cleanpayment['years'] = $payment['x-plug-paymentYears'][0];
            $cleanpayment['dn'] = $payment['dn'];
            $cleanpayment['description'] = $payment['x-plug-paymentDescription'][0];
            $cleanpayment['formatteddate'] = date('Y-m-d', strtotime($cleanpayment['date']));
            $cleanpayment['formattedamount'] = sprintf("$%.2f",$cleanpayment['amount']/100);
            $cleanpayment['formattedtype'] = $cleanpayment['type'] == FULL_TYPE ? "Full" : "Concession";
            $this->payments[$cleanpayment['id']] = $cleanpayment;    
    }
    
    function clean_payment($payment)
    {
            // smarty tempalte doesn't like - in var names        
            $cleanpayment = array();
            $cleanpayment['amount'] = $payment['x-plug-paymentAmount'];
            $cleanpayment['date'] = $payment['x-plug-paymentDate'];
            $cleanpayment['id'] = $payment['x-plug-paymentID'];
            $cleanpayment['type'] = $payment['x-plug-paymentType'];
            $cleanpayment['years'] = $payment['x-plug-paymentYears'];
            $cleanpayment['dn'] = @$payment['dn'];
            $cleanpayment['description'] = $payment['x-plug-paymentDescription'];
            $cleanpayment['formatteddate'] = date('Y-m-d', strtotime($cleanpayment['date']));
            $cleanpayment['formattedamount'] = sprintf("$%.2f",$cleanpayment['amount']/100);
            $cleanpayment['formattedtype'] = $cleanpayment['type'] == FULL_TYPE ? "Full" : "Concession";
            $this->payments[$cleanpayment['id']] = $cleanpayment;    
            krsort($this->payments);
            //print_r($this->payments);
            //print_r($payment);
    }    
    
    function paymentsarray()
    {
        return $this->payments;
    }
    
    function makePayment($type, $years, $date, $description, $ack, $id = false)
    {
        if($date == '') $date = date("YmdHis",time());
        
        $payment = new Payment($this->ldap); 
        $payment->new_payment($this->dn, $type, $years, $date, $description, $id);
        $paymentarray = $payment->paymentarray();
        $this->clean_payment($paymentarray);
        
        /* | Account Expired | Back Dated | New Expiry
           | Y               | N          | now + x years
           | Y               | Y          | backdate + x years as long as we don't decrease
           | N               | N          | expiry + x years
           | N               | Y          | if backdate is 3 years ago? just append? expiry + x years */
        if($this->userldaparray['shadowExpire'] > abs(floor(time()/ 86400)))
        {
            // Account has not yet expired, increase expiry
            $this->increase_expiry($years);            
        }else
        {
            // Account has expired, change expiry from payment date/now
            $this->change_expiry($date . "+ $years years");
        }

        $this->update_ldap();
        // TODO: Forward date payments if some time remaingin on membership
        $this->set_status_group();
        

        if($ack)
        {
            $this->sendPaymentReceipt($paymentarray['x-plug-paymentID']);
        }
            
        $this->messages[] = "Payment processed";
        return $paymentarray['x-plug-paymentID'];
    }
        
    function sendPaymentReceipt($paymentid)
    {

        if(!isset($this->payments[$paymentid]))
        {
            $this->errors[] = "Invalid payment id";
            return FALSE;
        }

        $body = "Dear %s,

Your payment of %s for %s year%s of %s membership was received on %s. Your membership is due to expire on %s. You will be notified by email a month before your membership expires.

If you require a receipt please contact %s and we'll arrange for one to be posted to you.

Thank you for your payment.

--
PLUG Membership Scripts";


        $expiry = $this->expiry();

        $body = sprintf($body,
            $this->userldaparray['displayName'],
            $this->payments[$paymentid]['formattedamount'],
            $this->payments[$paymentid]['years'],
            $this->payments[$paymentid]['years'] > 1 ? 's' : '',
            $this->payments[$paymentid]['formattedtype'],
            $this->payments[$paymentid]['formatteddate'],
            $expiry['formattedexpiry'],
            COMMITTEE_EMAIL);
            
        $headers .= "Reply-To: ".SCRIPTS_REPLYTO_EMAIL."\r\n";
        $headers .= "Return-Path: ".SCRIPTS_REPLYTO_EMAIL."\r\n";
        $headers .= "From: ".SCRIPTS_FROM_EMAIL."\r\n";                
        $headers .= "Bcc: ".ADMIN_EMAIL."\r\n";
        $headers .= "Content-type: text/plain; charset=iso-8859-1\r\n";
        
        $subject = "PLUG Payment confirmation ".$paymentid;
        
        if(mail($this->userldaparray['mail'], $subject, $body, $headers))
        {
            $this->messages[] = "Payment confirmation sent";
            return TRUE;
        }

        $this->errors[] = "Error sending payment confirmation";
        return FALSE;
    
    }
    
    // Password reset hashing
    
    function create_hash($tick = 0)
    {
        // Derived from WP nonce code
        $tick =ceil(time() / 21600) - $tick; // To check we check current and previous tick
        $hash = sha1(sha1($tick . $this->userldaparray['userPassword']));
        
        $lasttickval = $tick - intval($tick/10)*10;
        for($i = 0; $i < $lasttickval ; $i++)
        {
            $hash = sha1($hash);
        }
        return $hash;
    }

    function check_hash($hash)
    {
        if($hash == $this->create_hash())
            return TRUE;
        if($hash == $this->create_hash(1))
            return TRUE;            
        return FALSE;
    }
    
    // Validation function available globally
    function is_valid_password($password)
    {
        $error = array();
        $newpassword = cleanpassword($password);
        if($newpassword == '')
            $error[] = _('Blank password not allowed');
            
        if($newpassword != $password)
            $error[] = _('Invalid characters used in password');
        
        $error = array_merge($error, $this->check_password_strength($newpassword));
        
        if(sizeof($error) != 0)
        {
            $this->passworderrors = $error;
            return false;
        }
        
        return true;
            
    }
    
    function check_password_strength($password)
    {
        $error = array();
        if(strlen($password) < 7)
            $error[] = _("Password too short");
            
        if( !preg_match("#[0-9]+#", $password) )
        	$error[] = _("Password must include at least one number");
        if( !preg_match("#[a-zA-Z]+#", $password) )
        	$error[] = _("Password must include at least one letter");    
        
        return $error;
    }
    
    function get_password_errors()
    {
        return $this->passworderrors;
    }
         
    
    
    //Duplicate functions from PLUG class. Is there a better way of doing this as this class doesn't have access to the PLUG object?
    private function check_username_available($username)
    {
        $filter = Net_LDAP2_Filter::create('uid', 'equals',  $username);
        $searchbase = 'ou=Users,dc=plug,dc=org,dc=au';
        $options = array(
            'scope' => 'one',
            'attributes' => array('dn'),
            'sizelimit' => 1
        );
        
        $search = $this->ldap->search($searchbase, $filter, $options);

        if (PEAR::isError($search)) {
            throw new Exception($search->getMessage() . "\n");
        }
        if($search->count() == 0) return TRUE;
        return FALSE;
    }
    
    private function check_email_available($email)
    {
        $filter = Net_LDAP2_Filter::create('mail', 'equals',  $email);
        $searchbase = 'ou=Users,dc=plug,dc=org,dc=au';
        $options = array(
            'scope' => 'one',
            'attributes' => array('dn'),
            'sizelimit' => 1
        );
        
        $search = $this->ldap->search($searchbase, $filter, $options);

        if (PEAR::isError($search)) {
            throw new Exception($search->getMessage() . "\n");
        }

        return $search->count();
    }    
    
    // OO Getters only enable those that are needed
    function uid() { return $this->userldaparray['uidNumber'];}
    
    function username() { return $this->userldaparray['uid'];}    
    
    function givenName() { return $this->userldaparray['givenName'];}
    
    function mail() { return $this->userldaparray['mail'];}
/*    function dn() { return  $dn;}
    function username() { return $uid;}
    function displayname() { return $displayName;}
    
    function gid() { return $gidNumber;}
    function homedir () { return  $homeDirectory;}*/
/*    private $userPassword;
    private $loginShell;
    private $mail = array();
    private $givenName;
    private $sn;
    private $cn;
    private $street;
    private $homeTelephoneNumber;
    private $mobileTelephoneNumber;
    private $pagerTelephoneNumber;
    private $description;*/
}


// md5scrypt function from http://www.php.net/manual/en/function.crypt.php#93171
/*function md5crypt($password){
    // create a salt that ensures crypt creates an md5 hash
    $base64_alphabet='ABCDEFGHIJKLMNOPQRSTUVWXYZ'
                    .'abcdefghijklmnopqrstuvwxyz0123456789+/';
    $salt='$1$';
    for($i=0; $i<9; $i++){
        $salt.=$base64_alphabet[rand(0,63)];
    }
    // return the crypt md5 password
    return crypt($password,$salt.'$');
}*/

// http://blog.ricky-stevens.com/php-sha512-password-hashing/
    function createPasswordHash($strPlainText) {

      if (CRYPT_SHA512 != 1) {
        throw new Exception('Hashing mechanism not supported.');
      }
      
    // create a salt that ensures crypt creates an sha512 hash
    $base64_alphabet='ABCDEFGHIJKLMNOPQRSTUVWXYZ'
                    .'abcdefghijklmnopqrstuvwxyz0123456789+/';
    $salt='$6$';
    for($i=0; $i<16; $i++){
        $salt.=$base64_alphabet[rand(0,63)];
    }      
     
      return crypt($strPlainText, $salt.'$');
     
    }
    
    function validatePassword($strPlainText, $strHash) {
     
      if (CRYPT_SHA512 != 1) {
        throw new Exception('Hashing mechanism not supported.');
      }
     
      return (crypt($strPlainText, $strHash) == $strHash) ? true : false;
     
    }
    


?>
