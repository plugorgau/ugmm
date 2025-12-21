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
if (isset($_POST['newmember_form']) && $_POST['password'] != $_POST['vpassword']) {
    $error[] = "Passwords don't match";
}

// TODO Password strength check? lock password if null?

// TODO check for email address already used

$memberdetails = array(
    'uid' => isset($_POST['uid']) ? trim($_POST['uid']) : '',
    'givenName' => isset($_POST['givenName']) ? trim($_POST['givenName']) : '',
    'sn' => isset($_POST['sn']) ? trim($_POST['sn']) : '',
    'mail' => isset($_POST['mail']) ? trim($_POST['mail']) : '',
    'street' => isset($_POST['street']) ? trim($_POST['street']) : '',
    'homePhone' => isset($_POST['homePhone']) ? trim($_POST['homePhone']) : '',
    'pager' => isset($_POST['pager']) ? trim($_POST['pager']) : '',
    'mobile' => isset($_POST['mobile']) ? trim($_POST['mobile']) : '',
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
