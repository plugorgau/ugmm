<?php

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

        // Load all members
        $members = $entry->getValue('member');
        asort($members);
        
        foreach($members as $member)
        {
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
    
    private $ldap;
    
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
            'mail' => array(),
            'givenName' => '',
            'sn' => '',
            'cn' => '',
            'street' => '',
            'homeTelephoneNumber' => '',
            'mobileTelephoneNumber' => '',
            'pagerTelephoneNumber' => '',
            'description' => '',
            'objectClass' => array('top', 'person', 'posixAccount', 'inetOrgPerson'),            
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
            'givenName',
            'sn',
            'cn',
            'street',
            'homeTelephoneNumber',
            'mobileTelephoneNumber',
            'pagerTelephoneNumber',
            'description',
            'memberOf'));
        if (PEAR::isError($this->ldapentry)) {
            die('LDAP Error: '.$this->ldapentry->getMessage());
        }        
        
        $this->userldaparray = $this->ldapentry->getValues();
        $this->userorigldaparray = $this->userldaparray;
        //$this->explode_user_ldap_array();

    }
    
    function create_person($uid, $username, $firstname, $lastname, $address, $email, $password)
    {
        $this->dn = "uidNumber=$uid,ou=Users,dc=plug,dc=org,dc=au";
        $this->change_uid($uid, $uid);
        $this->change_username($username);
        $this->change_name($firstname, $lastname);
        $this->change_address($address);
        $this->change_shell("/usr/bin/zsh", "/home/$username");
        $this->change_email($email);
        $this->change_password($password);
        $this->create_user_ldap_array();
        $this->create_new_ldap_person();
    }
    
    function change_name($firstname, $lastname)
    {
        $lastname = $lastname ? $lastname : "_";
        $this->userldaparray['sn'] = $lastname;
        $this->userldaparray['givenName'] = $firstname;
        $this->userldaparray['displayName'] = "$firstname $lastname";
        $this->userldaparray['cn'] = "$firstname $lastname";
    }
    
    function change_address($address)
    {
        $this->userldaparray['street'] = $address;
    }
    
    function change_username($username)
    {
        $this->userldaparray['uid'] = $username;
    }
    
    private function change_uid($uid, $gid)
    {
        $this->userldaparray['uidNumber'] = $uid;
        $this->userldaparray['gidNumber'] = $gid;
    }
    
    function change_shell($loginShell, $homedir)
    {
        $this->userldaparray['loginShell'] = $loginShell;
        $this->userldaparray['homeDirectory'] = $homedir;
    }
    
    function change_email($email)
    {
        $this->userldaparray['mail'] = $email;
    }
    
    function change_password($password)
    {
        $this->userldaparray['userPassword'] = $password;
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
        $entry = Net_LDAP2_Entry::createFresh($this->dn, array_filter($this->userldaparray));
        $ldapres = $this->ldap->add($entry);
        if (PEAR::isError($ldapres)) {
            die('LDAP Error: '.$ldapres->getMessage()); //TODO: Better error handling
        }           
    }
    
    public function update_ldap()
    {
        //$this->create_user_ldap_array();
        /*print_r(array_diff_assoc(
                $this->userldaparray,
                $this->userorigldaparray
            ));    */
        $this->ldapentry->replace(
            array_diff_assoc(
                $this->userldaparray,
                $this->userorigldaparray
            )
        );
        $this->ldapentry->update();

    }
    
    private function memberOf_filter()
    {
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
                $validgroups[] = $cn[1];
            }
        }
        
        // Don't store this is userldaparray as it's not a valid attribute
        return $validgroups;
    }
    
    /* Display and Get functions */
    
    public function print_ldif()
    {
        $this->create_user_ldap_array();
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
        //$this->create_user_ldap_array();
        return array_merge($this->userldaparray, array('groups' => $this->memberOf_filter()));
    }
    
/*    function dn() { return  $dn;}
    function username() { return $uid;}
    function displayname() { return $displayName;}
    function uid() { return $uidNumber;}
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

class Member extends Person {
    private $expiry;
}


?>
