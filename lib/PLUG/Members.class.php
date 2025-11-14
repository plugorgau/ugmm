<?php

declare(strict_types=1);

require_once('/etc/private/ldapconnection.inc.php');
require_once('config.inc.php');

if (!defined('FORCE')) {
    define('FORCE', false);
}

class Members
{
    // Class for plug, contains members of type Member/Person

    private ?array $currentmembers = null;
    private ?array $overduemembers = null;
    private ?array $expiredmembers = null;
    private ?array $pendingmembers = null;

    private Net_LDAP2 $ldap;

    public function __construct(Net_LDAP2 $ldap)
    {
        $this->ldap = $ldap;
    }

    private function load_ldapmembers_from_group(string $group): array
    {
        // Fetch entry for group and all member attributes
        $dn = "cn=$group,ou=Groups,".LDAP_BASE;
        $entry = $this->ldap->getEntry($dn, array('member'));

        if (PEAR::isError($entry)) {
            throw new Exception('LDAP Error: '.$entry->getMessage());
        }

        // Load all members
        $members = $entry->getValue('member', 'all');
        asort($members);

        $memberdetails = array();
        foreach ($members as $member) {
            if ($member == DEFAULT_MEMBER) {
                continue;
            }

            $thismember = new Person($this->ldap);
            $thismember->load_ldap($member);
            $memberdetails[] = $thismember->userarray();

        }


        return $memberdetails;
    }

    public function load_members_dn_from_filter(string $filter): array
    {
        $filter = Net_LDAP2_Filter::parse($filter);
        $searchbase = "ou=Users,".LDAP_BASE;
        $options = array(
            'scope' => 'sub',
            'attributes' => array(
                'dn')
        );

        $search = $this->ldap->search($searchbase, $filter, $options);

        if (PEAR::isError($search)) {
            throw new Exception('LDAP Error: '.$search->getMessage());
        }

        $dns = array();

        while ($entry = $search->popEntry()) {
            if ($entry->dn() == DEFAULT_MEMBER) {
                continue;
            }
            $dns[] = $entry->dn();

        }


        return $dns;

    }

    public function get_current_members(): array
    {
        if ($this->currentmembers === null) {
            $this->currentmembers = $this->load_ldapmembers_from_group('currentmembers');
        }
        return $this->currentmembers;
    }

    public function get_overdue_members(): array
    {
        if ($this->overduemembers === null) {
            $this->overduemembers = $this->load_ldapmembers_from_group('overduemembers');
        }
        return $this->overduemembers;
    }

    public function get_expired_members(): array
    {
        if ($this->expiredmembers === null) {
            $this->expiredmembers = $this->load_ldapmembers_from_group('expiredmembers');
        }
        return $this->expiredmembers;
    }

    public function get_pending_members(): array
    {
        if ($this->pendingmembers === null) {
            $this->pendingmembers = $this->load_ldapmembers_from_group('pendingmembers');
        }
        return $this->pendingmembers;
    }

    public function get_member_object(int $uidNumber): Person
    {
        $uidNumber = intval($uidNumber); // Sanitise
        $dn = "uidNumber=$uidNumber,ou=Users,".LDAP_BASE;
        if ($this->ldap->dnExists($dn)) {
            $thismember = new Person($this->ldap);
            $thismember->load_ldap($dn);
            return $thismember;
        }
        return PEAR::raiseError(
            _('User not found. Invalid UID number'),
            -10,
            PEAR_ERROR_RETURN
        );
    }

    public function get_member_by_email(string $email): Person|bool
    {
        $filter = Net_LDAP2_Filter::create('mail', 'equals', $email);
        $searchbase = "ou=Users,".LDAP_BASE;
        $options = array(
            'scope' => 'one',
            'attributes' => array(
                'uidNumber')
        );

        $search = $this->ldap->search($searchbase, $filter, $options);

        if (PEAR::isError($search)) {
            throw new Exception($search->getMessage() . "\n");
        }

        if ($search->count() != 1) {
            // This can be caused by more than one user account being registered to the address
            // Incorrect number, return error
            return false;
        }

        $entry = $search->shiftEntry();

        return $this->get_member_object(intval($entry->getValue('uidNumber', 'single')));

    }


    /*    function get_member($uidNumber)
          {
          $uidNumber = intval($uidNumber); // Sanitise
          $dn = "uidNumber=$uidNumber,ou=Users,dc=plug,dc=org,dc=au";
          $thismember = new Person($this->ldap);
          $thismember->load_ldap($dn);
          return $thismember->userarray();
          }*/


    public function check_username_available(string $username): int
    {
        $filter = Net_LDAP2_Filter::create('uid', 'equals', $username);
        $searchbase = 'ou=Users,'.LDAP_BASE;
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

    public function next_uidNumber(): string
    {
        $dn = "cn=maxUid,ou=Users,".LDAP_BASE;
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

    public function next_freeuidNumber(string $uidNumber): string
    {
        // Loop checking that it's actually available
        /* This code should work but dnExists is broken
           while($this->ldap->dnExists("uidNumber=$uidNumber,ou=Users,dc=plug,dc=org,dc=au"))
           $uidNumber++;
        */
        while ($this->our_dnExists("uidNumber=$uidNumber,ou=Users,".LDAP_BASE)) {
            $uidNumber++;
        }
        return $uidNumber;
    }

    public function our_dnExists(string $dn): bool
    {
        $entry = $this->ldap->getEntry($dn, array('dn'));
        if (PEAR::isError($entry)) {
            // Can't get correct error (just returns 1000 in code) so assume any error is dn doesn't exist;
            return false;
        }
        return true;
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

    public function new_member(string $username, string $firstname, string $lastname, string $address, string $home, string $work, string $mobile, string $email, string $password, string $notes): Person
    {
        $newmember = new Person($this->ldap);
        $pendingID = isset($_SESSION['pendingID']) ? $this->next_freeuidNumber($_SESSION['pendingID']) : $this->next_uidNumber();
        $newmember->create_person($pendingID, $username, $firstname, $lastname, $address, $home, $work, $mobile, $email, '', $password, $notes);
        if ($newmember->is_error()) {
            $_SESSION['pendingID'] = $pendingID;
        } else {
            unset($_SESSION['pendingID']);
        }
        return $newmember;
    }

    public function delete_member(string $dn): void
    {
        if ($this->ldap->dnExists($dn)) {
            $this->ldap->delete($dn, true);
        }
    }

}

class Payment
{
    private readonly Net_LDAP2_Entry $entry;

    private const _DEFAULTS = array(
        'objectClass' => array('top', 'x-plug-payment'),
        'x-plug-paymentAmount' => 0,
        'x-plug-paymentDate' => '',
        'x-plug-paymentID' => '',
        'x-plug-paymentType' => FULL_TYPE,
        'x-plug-paymentDescription' => '',
        'x-plug-paymentYears' => 0);

    public function __construct(Net_LDAP2_Entry $entry)
    {
        $this->entry = $entry;
    }

    public string $dn {
        get => $this->entry->dn();
    }

    public int $amount {
        get => intval($this->entry->getValue('x-plug-paymentAmount', 'single'));
    }

    public string $date {
        get => $this->entry->getValue('x-plug-paymentDate', 'single');
    }

    public int $id {
        get => intval($this->entry->getValue('x-plug-paymentID', 'single'));
    }

    public int $type {
        get => intval($this->entry->getValue('x-plug-paymentType', 'single'));
    }

    public string $description {
        get {
            $d = $this->entry->getValue('x-plug-paymentDescription', 'single');
            return $d ? $d : '';
        }
    }

    public int $years {
        get => intval($this->entry->getValue('x-plug-paymentYears', 'single'));
    }

    public string $formatteddate {
        get => new DateTimeImmutable($this->date)->format('Y-m-d');
    }

    public string $formattedamount {
        get => sprintf("$%.2f", $this->amount / 100);
    }

    public string $formattedtype {
        get => $this->type == FULL_TYPE ? "Full" : "Concession";
    }

    public static function load_ldap(Net_LDAP2 $ldap, string $dn): Payment
    {
        $ldapentry = $ldap->getEntry($dn, array_keys(self::_DEFAULTS));
        if (PEAR::isError($ldapentry)) {
            throw new Exception('LDAP Error: '.$ldapentry->getMessage());
        }

        return new self($ldapentry);
    }

    public static function load_for(Net_LDAP2 $ldap, string $parentdn): array
    {
        $filter = Net_LDAP2_Filter::create('objectClass', 'equals', 'x-plug-payment');
        $options = array(
            'scope' => 'sub',
            'attributes' => array_keys(self::_DEFAULTS),
        );

        $search = $ldap->search($parentdn, $filter, $options);

        if (PEAR::isError($search)) {
            throw new Exception($search->getMessage() . "\n");
        }

        $payments = array();
        foreach ($search->entries() as $entry) {
            $payment = new self($entry);
            $payments[$payment->id] = $payment;
        }
        krsort($payments);
        return $payments;
    }

    public static function create(Net_LDAP2 $ldap, string $parentdn, int $type, int $years, DateTimeImmutable $date, string $description, int|bool $id = false): Payment
    {
        global $payment_modifier_amount; //Hack for change in payment amounts
        if (!isset($payment_modifier_amount)) {
            $payment_modifier_amount = 1;
        }
        if (! $id) {
            $id = self::next_paymentID($ldap);
        }

        $dn = "x-plug-paymentID=$id,$parentdn";
        $attrs = self::_DEFAULTS;
        $attrs['x-plug-paymentYears'] = $years;
        $attrs['x-plug-paymentDate'] = $date->format('YmdHisO');
        $attrs['x-plug-paymentID'] = $id;
        $attrs['x-plug-paymentType'] = $type;
        $attrs['x-plug-paymentDescription'] = $description;
        if ($type == CONCESSION_TYPE) {
            // Concession
            $attrs['x-plug-paymentAmount'] = $years * CONCESSION_AMOUNT * $payment_modifier_amount;
        } else {
            // Assume full
            $attrs['x-plug-paymentAmount'] = $years * FULL_AMOUNT * $payment_modifier_amount;
        }

        // TODO: Check if exists first
        $entry = Net_LDAP2_Entry::createFresh($dn, array_filter($attrs));
        $ldapres = $ldap->add($entry);
        if (PEAR::isError($ldapres)) {
            throw new Exception('LDAP Error: '.$ldapres->getMessage()); //TODO: Better error handling
        }

        return new self($entry);
    }

    private static function next_paymentID(Net_LDAP2 $ldap): int
    {
        $dn = "cn=maxUid,ou=Users,".LDAP_BASE;
        // Get next paymentID from maxUid

        $entry = $ldap->getEntry($dn, array('x-plug-paymentID'));

        if (PEAR::isError($entry)) {
            throw new Exception('LDAP Error: '.$entry->getMessage());
        }

        $paymentID = $entry->getValue('x-plug-paymentID');

        // Search and ensure not already exists
        $filter2 = Net_LDAP2_Filter::combine('not', Net_LDAP2_Filter::create('cn', 'equals', 'maxUid'));
        do {
            $filter1 = Net_LDAP2_Filter::create('x-plug-paymentID', 'equals', $paymentID);
            $filter = Net_LDAP2_Filter::combine('and', array($filter1, $filter2));
            $searchbase = "ou=Users,".LDAP_BASE;
            $options = array(
                'scope' => 'sub',
                'attributes' => array(
                    'uidNumber')
            );

            $search = $ldap->search($searchbase, $filter, $options);

            if (PEAR::isError($search)) {
                throw new Exception($search->getMessage() . "\n");
            }

            $paymentID++;

        } while ($search->count() != 0);


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

class Person
{
    private string $dn;
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

    private array $userldaparray;
    private array $userorigldaparray;
    private Net_LDAP2_Entry|PEAR_Error $ldapentry;

    private ?array $_payments = null;

    private Net_LDAP2 $ldap;

    private array $errors = array();
    private array $messages = array();
    private bool $errorstate = false;
    private array $passworderrors = array();

    private const _DEFAULTS = array(
        'objectClass' => array('top', 'person', 'posixAccount', 'inetOrgPerson', 'shadowAccount', 'mailForwardingAccount'),
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
        'memberOf' => array(),
        'createTimestamp' => '',
        'modifyTimestamp' => '',
    );

    public function __construct(Net_LDAP2 $ldap)
    {
        $this->ldap = $ldap;

        $this->userldaparray = self::_DEFAULTS;
    }

    public function load_ldap(string $dn): void
    {
        $this->dn = $dn;
        $this->ldapentry = $this->ldap->getEntry($dn, array_keys(self::_DEFAULTS));
        if (PEAR::isError($this->ldapentry)) {
            throw new Exception('LDAP Error: load_ldap: '.$this->ldapentry->getMessage());
        }

        $this->userldaparray = array_merge(self::_DEFAULTS, $this->ldapentry->getValues());
        $this->userorigldaparray = $this->userldaparray;
        //$this->explode_user_ldap_array();
    }

    public function create_person(string $uid, string $username, string $firstname, string $lastname, string $address, string $home, string $work, string $mobile, string $email, string $forward, string $password, string $notes): void
    {
        $this->dn = "uidNumber=$uid,ou=Users,".LDAP_BASE;
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
        if (! $this->is_error()) {
            $this->create_new_ldap_person();
            $this->create_new_ldap_group();
            // Extra call, but allows us to continue working with a new object.
            $this->load_ldap($this->dn);

            $this->set_status_group();

        }

    }

    public function is_error(): bool
    {
        $this->errors = array_filter($this->errors);
        if (sizeof($this->errors)) {
            $this->errorstate = true;
        }
        return $this->errorstate;
    }

    public function get_errors(): array
    {
        $this->errors = array_filter($this->errors);
        return $this->errors;
    }

    public function get_messages(): array
    {
        $this->messages = array_filter($this->messages);
        return $this->messages;
    }

    public function change_expiry(DateTimeImmutable $date): void
    {
        $this->userldaparray['shadowExpire'] = date_to_shadow_expire($date);
    }

    public function change_name(string $firstname, string $lastname): void
    {
        if ($firstname == '') {
            $this->errors[] = "Firstname is required";
        }
        $lastname = $lastname ? $lastname : "_";
        if ($firstname != $this->userldaparray['givenName'] ||
           $lastname != $this->userldaparray['sn']) {
            $this->userldaparray['sn'] = $lastname;
            $this->userldaparray['givenName'] = $firstname;
            $this->userldaparray['displayName'] = "$firstname $lastname";
            $this->userldaparray['cn'] = "$firstname $lastname";

            $this->messages[] = "Name changed";
        }

    }

    public function change_address(string $address): void
    {
        if ($address == '') {
            $this->errors[] = "Address is required";
        }
        if ($address != $this->userldaparray['street']) {
            $this->userldaparray['street'] = $address;

            $this->messages[] = "Address changed";
        }
    }

    public function change_username(string $username): void
    {
        if ($this->userldaparray['uid'] == '' || $username != $this->userldaparray['uid']) {
            if ($username == '') {
                $this->errors[] = "Username required";
            } elseif (strlen($username) < 3 && ! FORCE) {
                $this->errors[] = "Username must be at least 3 characters long";
            } elseif ($this->check_username_available($username)) {
                $this->userldaparray['uid'] = $username;
                $this->messages[] = "Username changed";
            } else {
                $this->errors[] = "Username not available";
            }
        }
    }

    private function change_uid(int|string $uid, int|string $gid): void
    {
        if ($uid < 10000 || $gid < 10000 || $uid == '' || $gid == '') {
            $this->errors[] = "UID or GID out of Range";
        } else {
            $this->userldaparray['uidNumber'] = $uid;
            $this->userldaparray['gidNumber'] = $gid;
        }
    }

    public function change_shell(string $loginShell): void
    {
        if ($loginShell != $this->userldaparray['loginShell']) {
            $this->userldaparray['loginShell'] = $loginShell;
            $this->messages[] = "Shell details changed";
        }
    }

    private function change_homedir(string $homedir): void
    {
        if ($homedir != $this->userldaparray['homeDirectory']) {
            $this->userldaparray['homeDirectory'] = $homedir;
            $this->messages[] = "Home directory changed";
        }

    }

    public function change_email(string $email): void
    {
        if ($email == '' && ! FORCE) {
            $this->errors[] = 'Email address required';
        }
        if ($email != $this->userldaparray['mail']) {
            if ($this->check_email_available($email) != 0 && ! FORCE) {
                // Check that email isn't already registered
                $this->errors[] = "Email address($email) is already registered. Please use another email address for password recovery purposes";
            } elseif (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->userldaparray['mail'] = $email;
                $this->messages[] = "Email changed";
            } else {
                $this->errors[] = "Invalid email address '$email'";
            }
        }
    }

    public function change_forward(string $forward): void
    {
        if (isset($this->userldaparray['mailForward'])) {
            $existing_mailForward = $this->userldaparray['mailForward'];
        } else {
            $existing_mailForward = "";
        }
        if ($forward != $existing_mailForward) {
            if ($forward == "" || filter_var($forward, FILTER_VALIDATE_EMAIL)) {
                $this->userldaparray['mailForward'] = $forward;
                $this->messages[] = "Email forwarding changed";
            } else {
                $this->errors[] = "Invalid email address for forwarding '$forward'";
            }
        }


    }

    public function change_password(string $password): void
    {
        // Can't check if password hasn't changed, so always do this
        // Check if we are passing an already crypted password
        if (substr($password, 0, 7) == '{crypt}') {
            $this->userldaparray['userPassword'] = $password;
        } else {
            $this->userldaparray['userPassword'] = '{crypt}'.createPasswordHash($password);
        }
        $this->messages[] = "Password changed";

        // Just update ldap?
        // $this->update_ldap();
    }

    public function change_phone(string $home, string $work, string $mobile): void
    {
        $this->userldaparray['homePhone'] = $home;
        $this->userldaparray['mobile'] = $mobile;
        $this->userldaparray['pager'] = $work;
    }

    public function change_description(string $description): void
    {
        $this->userldaparray['description'] = $description;
    }

    public function disable_shell(): bool
    {
        return $this->remove_from_group('shell');
    }

    public function enable_shell(): bool
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

    private function create_new_ldap_person(): bool
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
        return true;
    }

    private function create_new_ldap_group(): void
    {
        $gid = $this->userldaparray['gidNumber'];
        $group = array(
            'gidNumber' => $gid,
            'cn' => $this->userldaparray['uid'],
            'member' => $this->dn,
            'objectClass' => array('groupOfNames', 'posixGroup')
        );
        $dn = "gidNumber=$gid,ou=UPG,ou=Groups,".LDAP_BASE;

        $entry = Net_LDAP2_Entry::createFresh($dn, $group);

        $ldapres = $this->ldap->add($entry);

        if (PEAR::isError($ldapres)) {
            throw new Exception('LDAP Error: '.$ldapres->getMessage()); //TODO: Better error handling
        }
        //$this->messages[] = "Group Created";

    }

    private function create_new_ldap_system_group(string $dn, string $cn): void
    {
        $gids = array(
            'admin' => 1001,
            'committee' => 1006,
            'webslave' => 1003,
            'librarian' => 1007,
            'shell' => 1009
        );

        if (isset($gids[$cn])) {
            $gid = $gids[$cn];
            $group = array(
                'gidNumber' => $gid,
                'cn' => $cn,
                'member' => DEFAULT_MEMBER,
                'objectClass' => array('groupOfNames', 'posixGroup')
            );
        } else {
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

    public function update_ldap(): void
    {
        //$this->create_user_ldap_array();
        /*print_r(@array_diff_assoc(
          $this->userldaparray,
          $this->userorigldaparray
          ));*/

        // Nested arrays aren't supported by array_diff_assoc(), so ignore
        // warnings that arise from array members such as 'objectClass', 'memberOf', etc.
        //# $userldap = prune_array($this->userldaparray);
        $this->ldapentry->replace(
            @array_diff_assoc(
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

    private function memberOf_filter(): array
    {
        $filtergroups = array('expiredmembers', 'currentmembers', 'overduemembers', 'pendingmembers', 'shell');
        // Filters memberOf to just give system group names
        $groups = $this->userldaparray['memberOf'];
        if (!is_array($groups)) {
            $groups = array($groups);
        }
        $validgroups = array();
        $allgroups = array();
        foreach ($groups as $group) {
            if (strpos($group, 'cn=') !== false) {
                // This is a group with name, process it
                $dnparts = explode(',', $group);
                $cn = explode('=', $dnparts[0]);
                if (! in_array($cn[1], $filtergroups)) {
                    $validgroups[] = $cn[1];
                }
                $allgroups[] = $cn[1];
            }
        }
        // Don't store this is userldaparray as it's not a valid attribute
        return array($validgroups, $allgroups);
    }

    private function is_shell_enabled(): bool
    {
        list($validgroups, $allgroups) = $this->memberOf_filter();
        if (in_array('shell', $allgroups)) {
            return true;
        }
        return false;
    }

    private function is_membership_current(): bool
    {
        list($validgroups, $allgroups) = $this->memberOf_filter();
        if (in_array('currentmembers', $allgroups)) {
            return true;
        }
        return false;
    }

    private function is_membership_overdue(): bool
    {
        list($validgroups, $allgroups) = $this->memberOf_filter();
        if (in_array('overduemembers', $allgroups)) {
            return true;
        }
        return false;
    }

    public function expiry(): array
    {
        $expiry_raw = (int)$this->userldaparray['shadowExpire'];
        $expiry = shadow_expire_to_date($expiry_raw);
        return array(
            'expiry_raw' => $expiry_raw,
            'expiry' => $expiry->format("d M y"),
            'formattedexpiry' => $expiry->format("l, d F Y"),
        );
    }

    public function set_status_group(): void
    {
        // Due to memberOf being out of sync, we need to sync the ldap object
        $this->update_ldap();

        $groups = array('pendingmembers', 'expiredmembers', 'overduemembers', 'currentmembers');

        // Grace period of 3 months
        $expiry_raw = (int)$this->userldaparray['shadowExpire'];
        $expiry = shadow_expire_to_date($expiry_raw);
        $today = new DateTimeImmutable();
        $grace = $today->sub(new DateInterval(GRACE_PERIOD));
        if ($expiry_raw <= 1) {
            // Pending group
            $validgroup = 'pendingmembers';
        } elseif ($expiry < $grace) {
            // Expired
            $validgroup = 'expiredmembers';
        } elseif ($expiry < $today) {
            // Overdue
            $validgroup = 'overduemembers';
        } else {
            // Current member
            $validgroup = 'currentmembers';
        }

        foreach ($groups as $group) {
            if ($validgroup == $group) {
                $this->add_to_group($group);
            } else {
                $this->remove_from_group($group);
            }
        }

    }

    public function add_to_group(string $group): bool
    {
        $groupdn = "cn=$group,ou=Groups,".LDAP_BASE;
        $groups = is_array(@$this->userldaparray['memberOf']) ? @$this->userldaparray['memberOf'] : array(@$this->userldaparray['memberOf']);
        if (!in_array($groupdn, $groups)) {
            //echo "Adding to group $groupdn";
            //print_r($this->userldaparray['memberOf']);
            // Fetch entry for group and all member attributes
            if (! $this->ldap->dnExists($groupdn)) {
                $this->create_new_ldap_system_group($groupdn, $group);
            }

            $entry = $this->ldap->getEntry($groupdn, array('member'));

            if (PEAR::isError($entry)) {
                throw new Exception($entry->getMessage() . "\n");
            }


            $members = $entry->getValue('member', 'all');

            // Double check before attempt add. memberOf can be out of sync and it's cheap to check
            if (! in_array($this->dn, $members)) {
                $entry->add(
                    array(
                        'member' => $this->dn
                    )
                );

                $res = $entry->update();

                if (PEAR::isError($res)) {
                    throw new Exception($res->getMessage() . "\n");
                }

                // TODO: add to array so object is correct as well? Or reload from ldap

            }

        }

        return true; // TODO: return error if error?
    }

    public function remove_from_group(string $group): bool
    {
        $groupdn = "cn=$group,ou=Groups,".LDAP_BASE;
        $groups = is_array(@$this->userldaparray['memberOf']) ? @$this->userldaparray['memberOf'] : array(@$this->userldaparray['memberOf']);

        if (in_array($groupdn, $groups)) {
            // Fetch entry for group and all member attributes
            $entry = $this->ldap->getEntry($groupdn, array('member'));

            if (PEAR::isError($entry)) {
                throw new Exception($entry->getMessage() . "\n");
            }

            $members = $entry->getValue('member', 'all');

            // Double check before attempt remove. memberOf can be out of sync and it's cheap to check
            if (in_array($this->dn, $members)) {

                $entry->delete(
                    array(
                        'member' => $this->dn
                    )
                );

                $res = $entry->update();

                if (PEAR::isError($res)) {
                    throw new Exception($res->getMessage() . "\n");
                }

                // TODO: remove from array so object is correct as well? Or reload from ldap
            }

        }

        return true; // TODO: return error if error?
    }

    /* Display and Get functions */

    public function print_ldif(): void
    {
        echo "<p><h3>".$this->dn."</h3>\n";

        foreach ($this->userldaparray as $attribute => $value) {

            if (is_array($value)) {
                foreach ($value as $val) {
                    echo "$attribute: $val<br/>\n";
                }
            } else {
                echo "$attribute: $value<br/>\n";
            }
        }

        echo "</p>\n";
    }

    public function userarray(): array
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

    public function send_user_email(string $body, string $subject): bool
    {
        $headers = "Reply-To: ".SCRIPTS_REPLYTO_EMAIL."\r\n";
        $headers .= "Return-Path: ".SCRIPTS_REPLYTO_EMAIL."\r\n";
        $headers .= "From: ".SCRIPTS_FROM_EMAIL."\r\n";
        $headers .= "Bcc: ".ADMIN_EMAIL."\r\n";
        $headers .= "Content-type: text/plain; charset=iso-8859-1\r\n";

        if (mail($this->userldaparray['mail'], $subject, $body, $headers)) {
            $this->messages[] = "Email sent ($subject)";
            return true;
        }

        $this->errors[] = "Error sending email ($subject)";
        return false;
    }

    private function load_payments(): void
    {
        if ($this->_payments === null && !$this->is_error()) {
            $this->_payments = Payment::load_for($this->ldap, $this->dn);
        }
    }

    public array $payments {
        get {
            $this->load_payments();
            return $this->_payments ? $this->_payments : array();
        }
    }

    public function makePayment(int $type, int $years, DateTimeImmutable $date, string $description, bool $ack, int|bool $id = false): int
    {
        $this->load_payments();
        $payment = Payment::create($this->ldap, $this->dn, $type, $years, $date, $description, $id);
        $this->_payments[$payment->id] = $payment;
        krsort($this->_payments);

        // If the payment date is before the expiry date, increase the expiry date.
        // If the payment date is after the expiry date, set the expiry date to the payment date + x years.

        $expiry = shadow_expire_to_date((int)$this->userldaparray['shadowExpire']);
        $period = new DateInterval('P'.$years.'Y');
        if ($expiry->add(new DateInterval(GRACE_PERIOD)) > $date) {
            // Account is current, or within the overdue grace period:
            // use the old expiry as the start point.
            $this->change_expiry($expiry->add($period));
        } else {
            // Account has expired, change expiry from payment date/now
            $this->change_expiry($date->add($period));
        }

        $this->update_ldap();
        // TODO: Forward date payments if some time remaingin on membership?
        $this->set_status_group();


        if ($ack) {
            $this->sendPaymentReceipt($payment->id);
        }

        $this->messages[] = "Payment processed";
        return $payment->id;
    }

    public function sendPaymentReceipt(int $paymentid): bool
    {

        if (!isset($this->payments[$paymentid])) {
            $this->errors[] = "Invalid payment id";
            return false;
        }

        $body = "Dear %s,

Your payment of %s for %s year%s of %s membership was received on %s. Your membership is due to expire on %s. You will be notified by email a month before your membership expires.

If you require a receipt please contact %s and we'll arrange for one to be posted to you.

Thank you for your payment.

--
PLUG Membership Scripts";


        $payment = $this->payments[$paymentid];
        $expiry = $this->expiry();

        $body = sprintf(
            $body,
            $this->userldaparray['displayName'],
            $payment->formattedamount,
            $payment->years,
            $payment->years > 1 ? 's' : '',
            $payment->formattedtype,
            $payment->formatteddate,
            $expiry['formattedexpiry'],
            COMMITTEE_EMAIL
        );

        // TODO: call send_user_email instead of doing it here
        $headers = "Reply-To: ".SCRIPTS_REPLYTO_EMAIL."\r\n";
        $headers .= "Return-Path: ".SCRIPTS_REPLYTO_EMAIL."\r\n";
        $headers .= "From: ".SCRIPTS_FROM_EMAIL."\r\n";
        $headers .= "Bcc: ".ADMIN_EMAIL."\r\n";
        $headers .= "Content-type: text/plain; charset=iso-8859-1\r\n";

        $subject = "PLUG Payment confirmation ".$paymentid;

        if (mail($this->userldaparray['mail'], $subject, $body, $headers)) {
            $this->messages[] = "Payment confirmation sent";
            return true;
        }

        $this->errors[] = "Error sending payment confirmation";
        return false;

    }

    // Password reset hashing

    public function create_hash(int $tick = 0): string
    {
        // Derived from WP nonce code
        $tick = ceil(time() / 21600) - $tick; // To check we check current and previous tick
        $hash = sha1(sha1($tick . $this->userldaparray['userPassword']));

        $lasttickval = $tick - intval($tick / 10) * 10;
        for ($i = 0; $i < $lasttickval ; $i++) {
            $hash = sha1($hash);
        }
        return $hash;
    }

    public function check_hash(string $hash): bool
    {
        if ($hash == $this->create_hash()) {
            return true;
        }
        if ($hash == $this->create_hash(1)) {
            return true;
        }
        return false;
    }

    // Validation function available globally
    public function is_valid_password(string $password): bool
    {
        list($valid, $error) = PLUGFunction::is_valid_password($password);
        /*        $error = array();
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

                  return true;*/
        $this->passworderrors = $error;
        return $valid;

    }

    /*function check_password_strength($password)
      {
      $error = array();
      if(strlen($password) < 7)
      $error[] = _("Password too short");

      if( !preg_match("#[0-9]+#", $password) )
      $error[] = _("Password must include at least one number");
      if( !preg_match("#[a-zA-Z]+#", $password) )
      $error[] = _("Password must include at least one letter");

      return $error;
      }*/

    public function get_password_errors(): array
    {
        return $this->passworderrors;
    }



    //Duplicate functions from PLUG class. Is there a better way of doing this as this class doesn't have access to the PLUG object?
    private function check_username_available(string $username): bool
    {
        $filter = Net_LDAP2_Filter::create('uid', 'equals', $username);
        $searchbase = 'ou=Users,'.LDAP_BASE;
        $options = array(
            'scope' => 'one',
            'attributes' => array('dn'),
            'sizelimit' => 1
        );

        $search = $this->ldap->search($searchbase, $filter, $options);

        if (PEAR::isError($search)) {
            throw new Exception($search->getMessage() . "\n");
        }
        if ($search->count() == 0) {
            return true;
        }
        return false;
    }

    private function check_email_available(string $email): int
    {
        $filter = Net_LDAP2_Filter::create('mail', 'equals', $email);
        $searchbase = 'ou=Users,'.LDAP_BASE;
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
    public function uid(): int|string
    {
        return $this->userldaparray['uidNumber'];
    }

    public function username(): string
    {
        return $this->userldaparray['uid'];
    }

    public function givenName(): string
    {
        return $this->userldaparray['givenName'];
    }

    public function mail(): string
    {
        return $this->userldaparray['mail'];
    }
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

class PLUGFunction
{
    // Validation function available globally
    public static function is_valid_password(string $password): array
    {
        $error = array();
        $newpassword = cleanpassword($password);
        if ($newpassword == '') {
            $error[] = _('Blank password not allowed');
        }

        if ($newpassword != $password) {
            $error[] = _('Invalid characters used in password');
        }

        $error = array_merge($error, PLUGFunction::check_password_strength($newpassword));

        if (sizeof($error) != 0) {
            return array(false, $error);
        }

        return array(true, $error);

    }

    public static function check_password_strength(string $password): array
    {
        $error = array();
        if (strlen($password) < 7) {
            $error[] = _("Password too short");
        }

        if (!preg_match("#[0-9]+#", $password)) {
            $error[] = _("Password must include at least one number");
        }
        if (!preg_match("#[a-zA-Z]+#", $password)) {
            $error[] = _("Password must include at least one letter");
        }

        return $error;
    }

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
function createPasswordHash(string $strPlainText): string
{

    if (CRYPT_SHA512 != 1) {
        throw new Exception('Hashing mechanism not supported.');
    }

    // create a salt that ensures crypt creates an sha512 hash
    $base64_alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
                    .'abcdefghijklmnopqrstuvwxyz0123456789+/';
    $salt = '$6$';
    for ($i = 0; $i < 16; $i++) {
        $salt .= $base64_alphabet[rand(0, 63)];
    }

    return crypt($strPlainText, $salt.'$');

}

function validatePassword(string $strPlainText, string $strHash): bool
{

    if (CRYPT_SHA512 != 1) {
        throw new Exception('Hashing mechanism not supported.');
    }
    if (strtolower(substr($strHash, 0, 7)) == '{crypt}') {
        $strHash = substr($strHash, 7);
        return crypt($strPlainText, $strHash) == $strHash;
    } else {
        return $strPlainText == $strHash;
    }
}

function shadow_expire_to_date(int $shadow_expire): DateTimeInterface
{
    return new DateTimeImmutable()->setTimestamp($shadow_expire * 86400);
}

function date_to_shadow_expire(DateTimeInterface $date): int
{
    // $date needs to be converted to DAYS since epoch.
    // We convert to a timestamp, divide by 86400, round up (so it
    // happens after the chosen date).
    // Clamp to a minimum of 1, as -1 is never expire
    return max((int)ceil($date->getTimestamp() / 86400), 1);
}


# vim: set tabstop=4 shiftwidth=4 :
# Local Variables:
# tab-width: 4
# end:
