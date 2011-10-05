<?php

// Start of code for emailing expired members and moving them into the expired group

require_once('/etc/private/ldapconnection.inc.php');
require_once 'PLUG/PLUG.class.php';

// Create days after epoch for now and find all accounts < this (grace period of 5 days?)
$today = ceil(time()/ 86400) - 5;

// Select all accounts not already in group expired
$filter = "(&(shadowExpire<=$today)(memberOf=cn=currentmembers,ou=Groups,dc=plug,dc=org,dc=au))";

$PLUG = new PLUG($ldap);

$members = $PLUG->load_members_dn_from_filter($filter);

foreach($members as $dn)
{
    $member = new Person($ldap);
    $member->load_ldap($dn);
// Email that their account has expired?
    $details = $member->userarray();
    
    echo "User ".$details['displayName']. " has expired\n";
// Remove from group current. Add to group expired
    $member->set_status_group();

}

?>
