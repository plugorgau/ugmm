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

    private Net_LDAP2 $ldap;

    public function __construct(Net_LDAP2 $ldap)
    {
        $this->ldap = $ldap;
    }

    private function load_ldapmembers_from_group(string $group): array
    {
        // Fetch entry for group and all member attributes
        $dn = "cn=$group,ou=Groups,".LDAP_BASE;
        $filter = Net_LDAP2_Filter::create('memberOf', 'equals', $dn);
        return $this->load_members_from_filter($filter);
    }

    public function load_members_from_filter(Net_LDAP2_Filter|string $filter): array
    {
        if (is_string($filter)) {
            $filter = Net_LDAP2_Filter::parse($filter);
        }
        $searchbase = "ou=Users,".LDAP_BASE;
        $options = array(
            'scope' => 'sub',
            'attributes' => Person::_ATTRS,
        );

        $search = $this->ldap->search($searchbase, $filter, $options);
        if (PEAR::isError($search)) {
            throw new Exception('LDAP Error: '.$search->getMessage());
        }

        $members = array();
        while ($entry = $search->popEntry()) {
            if ($entry->dn() == DEFAULT_MEMBER) {
                continue;
            }
            $member = new Person($this->ldap, $entry);
            $members[$member->uidNumber] = $member;
        }
        ksort($members);
        return $members;
    }

    public function get_current_members(): array
    {
        return $this->load_ldapmembers_from_group('currentmembers');
    }

    public function get_overdue_members(): array
    {
        return $this->load_ldapmembers_from_group('overduemembers');
    }

    public function get_expired_members(): array
    {
        return $this->load_ldapmembers_from_group('expiredmembers');
    }

    public function get_pending_members(): array
    {
        return $this->load_ldapmembers_from_group('pendingmembers');
    }

    public function get_member_object(int $uidNumber): Person
    {
        $uidNumber = intval($uidNumber); // Sanitise
        $dn = "uidNumber=$uidNumber,ou=Users,".LDAP_BASE;
        if ($this->ldap->dnExists($dn)) {
            $thismember = Person::load($this->ldap, $dn);
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
            'attributes' => Person::_ATTRS
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
        return new Person($this->ldap, $entry);
    }

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

    public function new_member(string $username, string $firstname, string $lastname, string $address, string $home, string $work, string $mobile, string $email, string $password, string $notes): Person
    {
        $pendingID = isset($_SESSION['pendingID']) ? $this->next_freeuidNumber($_SESSION['pendingID']) : $this->next_uidNumber();
        $newmember = Person::create($this->ldap, $pendingID, $username, $firstname, $lastname, $address, $home, $work, $mobile, $email, '', $password, $notes);
        if ($newmember->is_error()) {
            $_SESSION['pendingID'] = $pendingID;
        } else {
            unset($_SESSION['pendingID']);
        }
        return $newmember;
    }

    public function list_groups(): array
    {
        $searchbase = "ou=Groups,".LDAP_BASE;
        $filter = Net_LDAP2_Filter::create('objectClass', 'equals', 'groupOfNames');
        $options = array(
            'scope' => 'one',
            'attributes' => array('cn'),
        );

        $search = $this->ldap->search($searchbase, $filter, $options);

        if (PEAR::isError($search)) {
            throw new Exception('LDAP Error: '.$search->getMessage());
        }

        $groups = array();
        while ($entry = $search->popEntry()) {
            $groups[] = $entry->getValue('cn', 'single');
        }

        // Filter out membership management groups
        $filtergroups = array('expiredmembers', 'currentmembers', 'overduemembers', 'pendingmembers', 'shell');
        $groups = array_diff($groups, $filtergroups);

        sort($groups);
        return $groups;
    }

    public function delete_member(string $dn): void
    {
        if ($this->ldap->dnExists($dn)) {
            $this->ldap->delete($dn, true);
        }
    }

    public function payments_since(DateTimeImmutable $date): array
    {
        return Payment::load_since($this->ldap, $date);
    }
}

class Payment
{
    private readonly Net_LDAP2 $ldap;
    private readonly Net_LDAP2_Entry $entry;

    private const attrPaymentAmount = 'x-plug-paymentAmount';
    private const attrPaymentDate = 'x-plug-paymentDate';
    private const attrPaymentID = 'x-plug-paymentID';
    private const attrPaymentType = 'x-plug-paymentType';
    private const attrPaymentDescription = 'x-plug-paymentDescription';
    private const attrPaymentYears = 'x-plug-paymentYears';
    private const _ATTRS = array(
        self::attrPaymentAmount,
        self::attrPaymentDate,
        self::attrPaymentID,
        self::attrPaymentType,
        self::attrPaymentDescription,
        self::attrPaymentYears,
    );

    private function __construct(Net_LDAP2 $ldap, Net_LDAP2_Entry $entry)
    {
        $this->ldap = $ldap;
        $this->entry = $entry;
    }

    public string $dn {
        get => $this->entry->dn();
    }

    public int $amount {
        get => intval($this->entry->getValue(self::attrPaymentAmount, 'single'));
    }

    public DateTimeImmutable $date {
        get => new DateTimeImmutable($this->entry->getValue(self::attrPaymentDate, 'single'));
    }

    public int $id {
        get => intval($this->entry->getValue(self::attrPaymentID, 'single'));
    }

    public int $type {
        get => intval($this->entry->getValue(self::attrPaymentType, 'single'));
    }

    public string $description {
        get {
            $d = $this->entry->getValue(self::attrPaymentDescription, 'single');
            return $d ? $d : '';
        }
    }

    public int $years {
        get => intval($this->entry->getValue(self::attrPaymentYears, 'single'));
    }

    public string $formatteddate {
        get => $this->date->format('Y-m-d');
    }

    public string $formattedamount {
        get => sprintf("$%.2f", $this->amount / 100);
    }

    public string $formattedtype {
        get => $this->type == FULL_TYPE ? "Full" : "Concession";
    }

    public Person $member {
        get {
            // The member DN is our DN with the x-plug-paymentID bit stripped
            $dn = preg_replace('/^'.self::attrPaymentID.'=\d+,/', '', $this->entry->dn());
            return Person::load($this->ldap, $dn);
        }
    }

    public static function load_ldap(Net_LDAP2 $ldap, string $dn): Payment
    {
        $ldapentry = $ldap->getEntry($dn, self::_ATTRS);
        if (PEAR::isError($ldapentry)) {
            throw new Exception('LDAP Error: '.$ldapentry->getMessage());
        }

        return new self($ldap, $ldapentry);
    }

    public static function load_for(Net_LDAP2 $ldap, string $parentdn): array
    {
        $filter = Net_LDAP2_Filter::create('objectClass', 'equals', 'x-plug-payment');
        $options = array(
            'scope' => 'sub',
            'attributes' => self::_ATTRS,
        );

        $search = $ldap->search($parentdn, $filter, $options);

        if (PEAR::isError($search)) {
            throw new Exception($search->getMessage() . "\n");
        }

        $payments = array();
        foreach ($search->entries() as $entry) {
            $payment = new self($ldap, $entry);
            $payments[$payment->id] = $payment;
        }
        krsort($payments);
        return $payments;
    }

    public static function load_since(Net_LDAP2 $ldap, DateTimeImmutable $date): array
    {
        $searchbase = 'ou=Users,'.LDAP_BASE;
        $filter = Net_LDAP2_Filter::combine('and', array(
            Net_LDAP2_Filter::create('objectClass', 'equals', 'x-plug-payment'),
            Net_LDAP2_Filter::create(self::attrPaymentDate, 'greaterOrEqual', $date->format('YmdHisO')),
        ));
        $options = array(
            'scope' => 'sub',
            'attributes' => self::_ATTRS,
        );

        $search = $ldap->search($searchbase, $filter, $options);
        if (PEAR::isError($search)) {
            throw new Exception('LDAP Error: '.$search->getMessage());
        }

        $payments = array();
        while ($entry = $search->popEntry()) {
            $payment = new self($ldap, $entry);
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
        if ($type == CONCESSION_TYPE) {
            // Concession
            $amount = $years * CONCESSION_AMOUNT * $payment_modifier_amount;
        } else {
            // Assume full
            $amount = $years * FULL_AMOUNT * $payment_modifier_amount;
        }

        $dn = self::attrPaymentID."=$id,$parentdn";
        $entry = Net_LDAP2_Entry::createFresh($dn, array(
            'objectClass' => array('top', 'x-plug-payment'),
        ));
        $entry->replace(array(
            self::attrPaymentID => $id,
            self::attrPaymentType => $type,
            self::attrPaymentYears => $years,
            self::attrPaymentDate => $date->format('YmdHisO'),
            self::attrPaymentAmount => $amount,
            self::attrPaymentDescription => $description,
        ));
        $ldapres = $ldap->add($entry);
        if (PEAR::isError($ldapres)) {
            throw new Exception('LDAP Error: '.$ldapres->getMessage()); //TODO: Better error handling
        }

        return new self($ldap, $entry);
    }

    private static function next_paymentID(Net_LDAP2 $ldap): int
    {
        $dn = "cn=maxUid,ou=Users,".LDAP_BASE;
        // Get next paymentID from maxUid

        $entry = $ldap->getEntry($dn, array(self::attrPaymentID));

        if (PEAR::isError($entry)) {
            throw new Exception('LDAP Error: '.$entry->getMessage());
        }

        $paymentID = $entry->getValue(self::attrPaymentID);

        // Search and ensure not already exists
        $filter2 = Net_LDAP2_Filter::combine('not', Net_LDAP2_Filter::create('cn', 'equals', 'maxUid'));
        do {
            $filter1 = Net_LDAP2_Filter::create(self::attrPaymentID, 'equals', $paymentID);
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
            self::attrPaymentID => $paymentID));

        $result = $entry->update();

        if (PEAR::isError($result)) {
            throw new Exception('LDAP Error: '.$result->getMessage());
        }

        return $paymentID - 1;

    }

}

class Person
{
    private Net_LDAP2 $ldap;
    private Net_LDAP2_Entry $ldapentry;

    private array $errors = array();
    private array $messages = array();
    private bool $errorstate = false;
    private array $passworderrors = array();

    private const attrUid = 'uid';
    private const attrDisplayName = 'displayName';
    private const attrUidNumber = 'uidNumber';
    private const attrGidNumber = 'gidNumber';
    private const attrHomeDirectory = 'homeDirectory';
    private const attrUserPassword = 'userPassword';
    private const attrLoginShell = 'loginShell';
    private const attrMail = 'mail';
    private const attrMailForward = 'mailForward';
    private const attrGivenName = 'givenName';
    private const attrSn = 'sn';
    private const attrCn = 'cn';
    private const attrStreet = 'street';
    private const attrHomePhone = 'homePhone';
    private const attrMobile = 'mobile';
    private const attrPager = 'pager';
    private const attrDescription = 'description';
    private const attrShadowExpire = 'shadowExpire';
    private const attrMemberOf = 'memberOf';
    private const attrCreateTimestamp = 'createTimestamp';
    private const attrModifyTimestamp = 'modifyTimestamp';

    public const _ATTRS = array(
        self::attrUid,
        self::attrDisplayName,
        self::attrUidNumber,
        self::attrGidNumber,
        self::attrHomeDirectory,
        self::attrUserPassword,
        self::attrLoginShell,
        self::attrMail,
        self::attrMailForward,
        self::attrGivenName,
        self::attrSn,
        self::attrCn,
        self::attrStreet,
        self::attrHomePhone,
        self::attrMobile,
        self::attrPager,
        self::attrDescription,
        self::attrShadowExpire,
        self::attrMemberOf,
        self::attrCreateTimestamp,
        self::attrModifyTimestamp,
    );

    public function __construct(Net_LDAP2 $ldap, Net_LDAP2_Entry $ldapentry)
    {
        $this->ldap = $ldap;
        $this->ldapentry = $ldapentry;
    }

    private function reload_ldap(): void
    {
        $ldapentry = $this->ldap->getEntry($this->dn, self::_ATTRS);
        if (PEAR::isError($ldapentry)) {
            throw new Exception('LDAP Error: reload_ldap: '.$ldapentry->getMessage());
        }

        $this->ldapentry = $ldapentry;
    }

    public static function load(Net_LDAP2 $ldap, string $dn): self
    {
        $ldapentry = $ldap->getEntry($dn, self::_ATTRS);
        if (PEAR::isError($ldapentry)) {
            throw new Exception('LDAP Error: Person::load: '.$ldapentry->getMessage());
        }

        return new self($ldap, $ldapentry);
    }

    public static function create(Net_LDAP2 $ldap, string $uid, string $username, string $firstname, string $lastname, string $address, string $home, string $work, string $mobile, string $email, string $forward, string $password, string $notes): self
    {
        $dn = "uidNumber=$uid,ou=Users,".LDAP_BASE;
        $entry = Net_LDAP2_Entry::createFresh($dn, array(
            'objectClass' => array('top', 'person', 'posixAccount', 'inetOrgPerson', 'shadowAccount', 'mailForwardingAccount'),
            self::attrShadowExpire => '1', // Start all users off as expired
        ));
        $person = new self($ldap, $entry);
        $person->change_uid($uid, $uid);
        $person->change_username($username);
        $person->change_name($firstname, $lastname);
        $person->change_address($address);
        $person->change_phone($home, $work, $mobile);
        $person->change_shell("/bin/bash");
        $person->change_homedir("/home/$username");
        $person->change_email($email);
        $person->change_forward($forward);
        $person->change_password($password);
        $person->change_description($notes);
        if (! $person->is_error()) {
            $ldapres = $person->ldap->add($person->ldapentry);
            if (PEAR::isError($ldapres)) {
                throw new Exception('LDAP Error: '.$ldapres->getMessage()); //TODO: Better error handling
            }
            // Overwrite other messages in messages array
            $person->messages = array("New member created with id " . $person->uidNumber);

            $person->create_new_ldap_group();
            // Extra call, but allows us to continue working with a new object.
            $person->reload_ldap();

            $person->set_status_group();
        }
        return $person;
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

    public string $dn {
        get => $this->ldapentry->dn();
    }

    public string $uid {
        get => (string)$this->ldapentry->getValue(self::attrUid, 'single');
    }

    public string $displayName {
        get => (string)$this->ldapentry->getValue(self::attrDisplayName, 'single');
    }

    public int $uidNumber {
        get => (int)$this->ldapentry->getValue(self::attrUidNumber, 'single');
    }

    public int $gidNumber {
        get => (int)$this->ldapentry->getValue(self::attrGidNumber, 'single');
    }

    public array $memberOf {
        get => $this->ldapentry->getValue(self::attrMemberOf, 'all');
    }

    public string $homeDirectory {
        get => (string)$this->ldapentry->getValue(self::attrHomeDirectory, 'single');
    }

    public string $userPassword {
        get => (string)$this->ldapentry->getValue(self::attrUserPassword, 'single');
    }

    public string $loginShell {
        get => (string)$this->ldapentry->getValue(self::attrLoginShell, 'single');
    }

    public string $mail {
        get => (string)$this->ldapentry->getValue(self::attrMail, 'single');
    }

    public string $mailForward {
        get => (string)$this->ldapentry->getValue(self::attrMailForward, 'single');
    }

    public string $givenName {
        get => (string)$this->ldapentry->getValue(self::attrGivenName, 'single');
    }

    public string $sn {
        get => (string)$this->ldapentry->getValue(self::attrSn, 'single');
    }

    public string $cn {
        get => (string)$this->ldapentry->getValue(self::attrCn, 'single');
    }

    public string $street {
        get => (string)$this->ldapentry->getValue(self::attrStreet, 'single');
    }

    public string $homePhone {
        get => (string)$this->ldapentry->getValue(self::attrHomePhone, 'single');
    }

    public string $mobile {
        get => (string)$this->ldapentry->getValue(self::attrMobile, 'single');
    }

    public string $pager {
        get => (string)$this->ldapentry->getValue(self::attrPager, 'single');
    }

    public string $description {
        get => (string)$this->ldapentry->getValue(self::attrDescription, 'single');
    }

    public int $shadowExpire {
        get => (int)$this->ldapentry->getValue(self::attrShadowExpire, 'single');
    }

    public string $expiry {
        get {
            $expiry = shadow_expire_to_date($this->shadowExpire);
            return $expiry->format("d M y");
        }
    }

    public string $formattedexpiry {
        get {
            $expiry = shadow_expire_to_date($this->shadowExpire);
            return $expiry->format("l, d F Y");
        }
    }

    public function change_expiry(DateTimeImmutable $date): void
    {
        $this->ldapentry->replace(array(
            self::attrShadowExpire => date_to_shadow_expire($date),
        ));
    }

    public function change_name(string $firstname, string $lastname): void
    {
        if ($firstname == '') {
            $this->errors[] = "Firstname is required";
        }
        $lastname = $lastname ? $lastname : "_";
        if ($firstname != $this->givenName || $lastname != $this->sn) {
            $this->ldapentry->replace(array(
                self::attrSn => $lastname,
                self::attrGivenName => $firstname,
                self::attrDisplayName => "$firstname $lastname",
                self::attrCn => "$firstname $lastname",
            ));
            $this->messages[] = "Name changed";
        }

    }

    public function change_address(string $address): void
    {
        if ($address == '') {
            $this->errors[] = "Address is required";
        }
        if ($address != $this->street) {
            $this->ldapentry->replace(array(
                self::attrStreet => $address,
            ));
            $this->messages[] = "Address changed";
        }
    }

    public function change_username(string $username): void
    {
        if ($this->uid == '' || $username != $this->uid) {
            if ($username == '') {
                $this->errors[] = "Username required";
            } elseif (strlen($username) < 3 && ! FORCE) {
                $this->errors[] = "Username must be at least 3 characters long";
            } elseif ($this->check_username_available($username)) {
                $this->ldapentry->replace(array(
                    self::attrUid => $username,
                ));
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
            $this->ldapentry->replace(array(
                self::attrUidNumber => $uid,
                self::attrGidNumber => $gid,
            ));
        }
    }

    public function change_shell(string $loginShell): void
    {
        if ($loginShell != $this->loginShell) {
            $this->ldapentry->replace(array(
                self::attrLoginShell => $loginShell,
            ));
            $this->messages[] = "Shell details changed";
        }
    }

    private function change_homedir(string $homedir): void
    {
        if ($homedir != $this->homeDirectory) {
            $this->ldapentry->replace(array(
                self::attrHomeDirectory => $homedir,
            ));
            $this->messages[] = "Home directory changed";
        }

    }

    public function change_email(string $email): void
    {
        if ($email == '' && ! FORCE) {
            $this->errors[] = 'Email address required';
        }
        if ($email != $this->mail) {
            if ($this->check_email_available($email) != 0 && ! FORCE) {
                // Check that email isn't already registered
                $this->errors[] = "Email address($email) is already registered. Please use another email address for password recovery purposes";
            } elseif (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->ldapentry->replace(array(
                    self::attrMail => $email,
                ));
                $this->messages[] = "Email changed";
            } else {
                $this->errors[] = "Invalid email address '$email'";
            }
        }
    }

    public function change_forward(string $forward): void
    {
        if ($forward != $this->mailForward) {
            if ($forward == "" || filter_var($forward, FILTER_VALIDATE_EMAIL)) {
                $this->ldapentry->replace(array(
                    self::attrMailForward => $forward,
                ));
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
            $this->ldapentry->replace(array(
                self::attrUserPassword => $password,
            ));
        } else {
            $this->ldapentry->replace(array(
                self::attrUserPassword => '{crypt}'.createPasswordHash($password),
            ));
        }
        $this->messages[] = "Password changed";

        // Just update ldap?
        // $this->update_ldap();
    }

    public function change_phone(string $home, string $work, string $mobile): void
    {
        $this->ldapentry->replace(array(
            self::attrHomePhone => $home,
            self::attrMobile => $mobile,
            self::attrPager => $work,
        ));
    }

    public function change_description(string $description): void
    {
        $this->ldapentry->replace(array(
            self::attrDescription => $description,
        ));
    }

    public function disable_shell(): bool
    {
        return $this->remove_from_group('shell');
    }

    public function enable_shell(): bool
    {
        return $this->add_to_group('shell');
    }

    private function create_new_ldap_group(): void
    {
        $gid = $this->gidNumber;
        $group = array(
            'gidNumber' => $gid,
            'cn' => $this->uid,
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
        $result = $this->ldapentry->update();

        if (PEAR::isError($result)) {
            throw new Exception('LDAP Error: '.$result->getMessage());
        }

        // Extra call, but allows us to continue working with a new object.
        $this->reload_ldap();
    }

    public array $allgroups {
        get {
            $allgroups = array();
            foreach ($this->memberOf as $group) {
                if (strpos($group, 'cn=') !== false) {
                    // This is a group with name, process it
                    $dnparts = explode(',', $group);
                    $cn = explode('=', $dnparts[0]);
                    $allgroups[] = $cn[1];
                }
            }
            return $allgroups;
        }
    }

    public array $groups {
        get {
            $filtergroups = array('expiredmembers', 'currentmembers', 'overduemembers', 'pendingmembers', 'shell');
            return array_diff($this->allgroups, $filtergroups);
        }
    }

    public bool $shellEnabled {
        get => in_array('shell', $this->allgroups);
    }

    public bool $membershipCurrent {
        get => in_array('currentmembers', $this->allgroups);
    }

    private function is_membership_overdue(): bool
    {
        return in_array('overduemembers', $this->allgroups);
    }

    public function set_status_group(): void
    {
        // Due to memberOf being out of sync, we need to sync the ldap object
        $this->update_ldap();

        $groups = array('pendingmembers', 'expiredmembers', 'overduemembers', 'currentmembers');

        // Grace period of 3 months
        $expiry_raw = $this->shadowExpire;
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
        if (!in_array($groupdn, $this->memberOf)) {
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

                // Reload entry so memberOf is correct
                $this->reload_ldap();
                $this->messages[] = "Added to ".$group." group";
            }

        }

        return true; // TODO: return error if error?
    }

    public function remove_from_group(string $group): bool
    {
        $groupdn = "cn=$group,ou=Groups,".LDAP_BASE;
        if (in_array($groupdn, $this->memberOf)) {
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

                // Reload entry so memberOf is correct
                $this->reload_ldap();
                $this->messages[] = "Removed from ".$group." group";
            }

        }

        return true; // TODO: return error if error?
    }

    /* Display and Get functions */
    public function send_user_email(string $body, string $subject): bool
    {
        $headers = "Reply-To: ".SCRIPTS_REPLYTO_EMAIL."\r\n";
        $headers .= "Return-Path: ".SCRIPTS_REPLYTO_EMAIL."\r\n";
        $headers .= "From: ".SCRIPTS_FROM_EMAIL."\r\n";
        $headers .= "Bcc: ".ADMIN_EMAIL."\r\n";
        $headers .= "Content-type: text/plain; charset=iso-8859-1\r\n";

        if (mail($this->mail, $subject, $body, $headers)) {
            $this->messages[] = "Email sent ($subject)";
            return true;
        }

        $this->errors[] = "Error sending email ($subject)";
        return false;
    }

    public array $payments {
        get => Payment::load_for($this->ldap, $this->dn);
    }

    public function makePayment(int $type, int $years, DateTimeImmutable $date, string $description, bool $ack, int|bool $id = false): int
    {
        $payment = Payment::create($this->ldap, $this->dn, $type, $years, $date, $description, $id);

        // If the payment date is before the expiry date, increase the expiry date.
        // If the payment date is after the expiry date, set the expiry date to the payment date + x years.

        $expiry = shadow_expire_to_date($this->shadowExpire);
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

        $body = sprintf(
            $body,
            $this->displayName,
            $payment->formattedamount,
            $payment->years,
            $payment->years > 1 ? 's' : '',
            $payment->formattedtype,
            $payment->formatteddate,
            $this->formattedexpiry,
            COMMITTEE_EMAIL
        );

        // TODO: call send_user_email instead of doing it here
        $headers = "Reply-To: ".SCRIPTS_REPLYTO_EMAIL."\r\n";
        $headers .= "Return-Path: ".SCRIPTS_REPLYTO_EMAIL."\r\n";
        $headers .= "From: ".SCRIPTS_FROM_EMAIL."\r\n";
        $headers .= "Bcc: ".ADMIN_EMAIL."\r\n";
        $headers .= "Content-type: text/plain; charset=iso-8859-1\r\n";

        $subject = "PLUG Payment confirmation ".$paymentid;

        if (mail($this->mail, $subject, $body, $headers)) {
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
        $hash = sha1(sha1($tick . $this->userPassword));

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
