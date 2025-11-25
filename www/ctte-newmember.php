<?php

declare(strict_types=1);

$ACCESS_LEVEL = 'committee';
$TOPLEVEL = 'ctte';

require_once('../lib/PLUG/session.inc.php');

$OrgMembers = new Members($ldap);

$smarty->assign("usercreated", false);

if (isset($_POST['newmember_form']) && !verify_nonce($_POST['nonce'], 'newmember')) {
    $error[] = "Attempt to double submit form? No changes made.";
}

// Check password matches
if (isset($_POST['newmember_form']) && $_POST['password'] != $_POST['verifypassword']) {
    $error[] = "Passwords don't match";
}

// TODO Password strength check? lock password if null?

// TODO check for email address already used

$memberdetails = array(
    'uid' => isset($_POST['uid']) ? trim($_POST['uid']) : '',
    'givenName' => isset($_POST['first_name']) ? trim($_POST['first_name']) : '',
    'sn' => isset($_POST['last_name']) ? trim($_POST['last_name']) : '',
    'mail' => isset($_POST['email_address']) ? trim($_POST['email_address']) : '',
    'street' => isset($_POST['street_address']) ? trim($_POST['street_address']) : '',
    'homePhone' => isset($_POST['home_phone']) ? trim($_POST['home_phone']) : '',
    'pager' => isset($_POST['work_phone']) ? trim($_POST['work_phone']) : '',
    'mobile' => isset($_POST['mobile_phone']) ? trim($_POST['mobile_phone']) : '',
);

if (isset($_POST['newmember_form']) && ! $error) {

    $member = $OrgMembers->new_member(
        $memberdetails['uid'],
        $memberdetails['givenName'],
        $memberdetails['sn'],
        $memberdetails['street'],
        $memberdetails['homePhone'],
        $memberdetails['pager'],
        $memberdetails['mobile'],
        $memberdetails['mail'],
        $_POST['password'],
        trim($_POST['notes'])
    );

    if ($member->is_error()) {
        $error = array_merge($error, $member->get_errors());
    } else {
        $success = array_merge($success, $member->get_messages());
        $smarty->assign("usercreated", true);
        $smarty->assign("newmember", $member);
    }
}

$smarty->assign('member', $memberdetails);
display_page('newmember.tpl');
