<?php

// Start of code for emailing expired members and moving them into the expired group

require_once('/etc/private/ldapconnection.inc.php');
require_once '/usr/share/plug-ugmm/www/PLUG/PLUG.class.php';

$PLUG = new PLUG($ldap);

// ********* Expired members 

// Create days after epoch for now and find all accounts < this (grace period of 5 days?)
$today = ceil(time()/ 86400) - 5;

// Select all accounts not already in group expired
$filter = "(&(shadowExpire<=$today)(memberOf=cn=currentmembers,ou=Groups,dc=plug,dc=org,dc=au))";

$members = $PLUG->load_members_dn_from_filter($filter);

foreach($members as $dn)
{
    $member = new Person($ldap);
    $member->load_ldap($dn);

    $details = $member->userarray();
    
    echo "User ".$details['displayName']. " has expired\n";
    // Email that their account has expired?    
    send_expired_email($member, $details);
    // Remove from group current. Add to group expired    
    $member->set_status_group();

}

// ********* Expiring members 

// Create days after epoch for now + 30 days and find all accounts = this
$future = ceil(time()/ 86400) + 30;

// Select all accounts not already in group expired
$filter = "(&(shadowExpire=$future)(memberOf=cn=currentmembers,ou=Groups,dc=plug,dc=org,dc=au))";

$members = $PLUG->load_members_dn_from_filter($filter);

foreach($members as $dn)
{
    $member = new Person($ldap);
    $member->load_ldap($dn);
    $details = $member->userarray();
    
    echo "User ".$details['displayName']. " is expiring in 30 days\n";
    send_expiring_email($member, $details);

}

// TODO filter for members who are not yet paid, need to make sure we don't send out an email daily!

// TODO? Move the following into the class as well?

function send_expired_email($member, $details)
{
    $body = "Dear %s,
    
Your PLUG membership expired on %s.

If you wish to renew your PLUG membership, you have several options:

".PAYMENT_OPTIONS."
     
Membership fees are \$%s per year, or \$%s per year for holders of a
current student or concession card.

You may choose not to renew your membership, in which case your PLUG
shell account will expire 5 days after your membership lapsed. However,
the mailing list is still freely accessible to non-members.

If you have any queries, please do not hesitate to contact the PLUG
committee via email at ".COMMITTEE_EMAIL.".

Regards,

PLUG Membership Scripts";

    $body = sprintf($body,
        $details['displayName'],
        $details['formattedexpiry'],
        FULL_AMOUNT / 100,
        CONCESSION_AMOUNT / 100
    );
        
    $subject = "Your PLUG Membership has Expired";
    
    if($member->send_user_email($body, $subject))
    {
        foreach($member->get_messages() as $message) echo "$message\n";
    }else{
        foreach($member->get_errors() as $message) echo "$message\n";    
    }
}

function send_expiring_email($member, $details)
{
    $body = "Dear %s,
    
Your PLUG membership is due to expire on %s.

If you wish to renew your PLUG membership, you have several options:

".PAYMENT_OPTIONS."
     
Membership fees are \$%s per year, or \$%s per year for holders of a
current student or concession card.

You may choose not to renew your membership, in which case your PLUG
shell account will expire 5 days after your membership lapsed. However,
the mailing list is still freely accessible to non-members.

If you have any queries, please do not hesitate to contact the PLUG
committee via email at ".COMMITTEE_EMAIL.".

Regards,

PLUG Membership Scripts";

    $body = sprintf($body,
        $details['displayName'],
        $details['formattedexpiry'],
        FULL_AMOUNT / 100,
        CONCESSION_AMOUNT / 100
    );
        
    $subject = "Your PLUG Membership is Expiring";
    
    if($member->send_user_email($body, $subject))
    {
        foreach($member->get_messages() as $message) echo "$message\n";
    }else{
        foreach($member->get_errors() as $message) echo "$message\n";    
    }
}

function send_waitingpayment_email($member, $details)
{
    $body = "Dear %s,
    
Your PLUG membership is awaiting payment before it is activated.

If you have already paid, please email ".COMMITTEE_EMAIL." to sort out your
account activation. Otherwise you have several options for payment:

".PAYMENT_OPTIONS."
     
Membership fees are \$%s per year, or \$%s per year for holders of a
current student or concession card.

You may choose not to pay membership, in which case your PLUG membership and
shell account will not be actived. However, the mailing list is still freely
accessible to non-members.

If you have any queries, please do not hesitate to contact the PLUG
committee via email at ".COMMITTEE_EMAIL.".

Regards,

PLUG Membership Scripts";

    $body = sprintf($body,
        $details['displayName'],
        $details['formattedexpiry'],
        FULL_AMOUNT / 100,
        CONCESSION_AMOUNT / 100
    );
        
    $subject = "Your PLUG Membership is awaiting payment";
    
    if($member->send_user_email($body, $subject))
    {
        foreach($member->get_messages() as $message) echo "$message\n";
    }else{
        foreach($member->get_errors() as $message) echo "$message\n";    
    }
}

?>
