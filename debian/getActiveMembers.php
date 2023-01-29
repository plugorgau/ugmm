<?php
// This is a test script to find the names of all active members,
// i.e. those whose membership has not expired, and those whose payment
// is overdue by less than 3 months (still entitled to all benefits of
// membership according to our Constitution


require_once('/etc/private/ldapconnection.inc.php');
require_once('/usr/share/plug-ugmm/www/PLUG/config.inc.php');
require_once('/usr/share/plug-ugmm/www/PLUG/Members.class.php');

$OrgMembers = new Members($ldap);

// Create days after epoch for now and find all accounts < this (grace period of 5 days?)
$expired = ceil(date("U", strtotime("-3 months"))/ 86400);

// It is done in three parts to simplify the filters
// Note that previous versions of ugmm classified members as "expired" when their
// payment was overdue by 5 days, so the "expired members" group may contain
// some active members until this is rectified - check these first

$filter = "(&(shadowExpire>=$expired)(memberOf=cn=expiredmembers,ou=Groups,".LDAP_BASE."))";

$members = $OrgMembers->load_members_dn_from_filter($filter);
echo "Overdue\n";
foreach($members as $dn)
{
    $member = new Person($ldap);
    $member->load_ldap($dn);

    $details = $member->userarray();
    
    echo $details['cn']. "\n";
}
// Now check those currently classified as overdue in case they have just rolled past
// the three months
$filter = "(&(shadowExpire>=$expired)(memberOf=cn=overduemembers,ou=Groups,".LDAP_BASE."))";
$members = $OrgMembers->load_members_dn_from_filter($filter);
foreach($members as $dn)
{
    $member = new Person($ldap);
    $member->load_ldap($dn);

    $details = $member->userarray();
    
    echo $details['cn']. "\n";
}
// Finally those classified as current - shouldn't normally need to filter on date, but
// might as well check in case the "daily" update script hasn't been run

echo "Current\n";
$filter = "(&(shadowExpire>=$expired)(memberOf=cn=currentmembers,ou=Groups,".LDAP_BASE."))";

$members = $OrgMembers->load_members_dn_from_filter($filter);

foreach($members as $dn)
{
    $member = new Person($ldap);
    $member->load_ldap($dn);

    $details = $member->userarray();
    
    echo $details['cn']. "\n";
}

