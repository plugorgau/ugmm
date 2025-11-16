<?php

// This is a test script to find the names of all active members,
// i.e. those whose membership has not expired, and those whose payment
// is overdue by less than 3 months (still entitled to all benefits of
// membership according to our Constitution

declare(strict_types=1);

require_once('/etc/private/ldapconnection.inc.php');
require_once('/usr/share/plug-ugmm/lib/PLUG/config.inc.php');
require_once('/usr/share/plug-ugmm/lib/PLUG/Members.class.php');

$OrgMembers = new Members($ldap);

// Create days after epoch for now and find all accounts < this (grace period of 5 days?)
$today = new DateTimeImmutable();
$expired = date_to_shadow_expire($today->sub(new DateInterval(GRACE_PERIOD)));

$filter = "(shadowExpire>=$expired)";
$members = $OrgMembers->load_members_dn_from_filter($filter);

foreach ($members as $dn) {
    $member = new Person($ldap);
    $member->load_ldap($dn);

    $details = $member->userarray();

    echo $details['cn']." <".$details['mail'].">\n";
}
