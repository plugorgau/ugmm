<?php

declare(strict_types=1);

$ACCESS_LEVEL = 'committee';
$TOPLEVEL = 'ctte';

require_once('../lib/PLUG/session.inc.php');

$OrgMembers = new Members($ldap);

// Validate $_GET better? intval should clean it to just a number
$memberid = intval($_GET['id']);
if ($memberid < 10000) {
    header("Location: ctte-members");
}

if (isset($_POST['personals_form']) && !verify_nonce($_POST['nonce'], 'editmember')) {
    $error[] = "Attempt to double submit form? No changes made.";
}

if (isset($_POST['personals_form']) && ! $error) {
    // process form
    // Ignore GET value and use POST value from form
    $memberid = intval($_POST['id']);

    $member = $OrgMembers->get_member_object($memberid);
    // Validate each item and update memberdetails object


    // TODO: Class validates objects and maintains errors/successs messages
    $member->change_username($_POST['uid']);
    $member->change_name($_POST['givenName'], $_POST['sn']);
    $member->change_email($_POST['mail']);
    $member->change_address($_POST['street']);
    $member->change_phone($_POST['homePhone'], $_POST['pager'], $_POST['mobile']);
    $member->change_description($_POST['notes']);


    if ($member->is_error()) {
        //$error[] = "Member details not updated";
        $error = array_merge($error, $member->get_errors());
    } else {
        $member->update_ldap();
        $success[] = "Member details updated";
        $success = array_merge($success, $member->get_messages());
    }
}

// Process payment form
if (isset($_POST['payment_form']) && !verify_nonce($_POST['nonce'], 'makepayment')) {
    $error[] = "Attempt to double submit form? No changes made.";
}

if (isset($_POST['payment_form']) && ! $error) {
    // process form
    // Ignore GET value and use POST value from form
    $memberid = intval($_POST['id']);

    $member = $OrgMembers->get_member_object($memberid);
    // Validate each item and update memberdetails object
    $payment_type = trim($_POST['membership_type']);
    $payment_years = trim($_POST['years']);
    $payment_date = trim($_POST['payment_date']);
    $payment_comment = trim($_POST['receipt_number']);
    $payment_ack = trim($_POST['payment_ack']);

    // Allow future dating here? TODO: Prevent future dating
    // Class will ensure date is "now" if empty, we need to ensure it's valid
    try {
        $payment_date = new DateTimeImmutable($payment_date);
    } catch (Exception) {
        $error[] = "Invalid payment date";
    }
    // TODO: validate
    if (!$error) {

        $member->makePayment(intval($payment_type), intval($payment_years), $payment_date, $payment_comment, boolval($payment_ack));
        if ($member->is_error()) {
            $success = $member->get_messages();
            $error = $member->get_errors();
        } else {
            $success = $member->get_messages();
        }
    }
}

// Process group membership form
if (isset($_POST['groups_form']) && !verify_nonce($_POST['nonce'], 'updategroups')) {
    $error[] = "Attempt to double submit form? No changes made.";
}

if (isset($_POST['groups_form']) && isset($_POST['go_go_button']) && ! $error) {
    // process form
    // Ignore GET value and use POST value from form
    $memberid = intval($_POST['id']);

    $member = $OrgMembers->get_member_object($memberid);

    // Update memberships as desired
    $memberships = isset($_POST['groups']) ? $_POST['groups'] : array();
    foreach ($OrgMembers->list_groups() as $group) {
        if (in_array($group, $memberships)) {
            $member->add_to_group($group);
        } else {
            $member->remove_from_group($group);
        }
    }
    $success[] = "Group membership updated";
    $success = array_merge($success, $member->get_messages());
    $error = array_merge($error, $member->get_errors());
}

// Process email forwarding format_ph
if (isset($_POST['email_form']) && !verify_nonce($_POST['nonce'], 'updateemailforwarding')) {
    $error[] = "Attempt to double submit form? No changes made.";
}

if (isset($_POST['email_form']) && isset($_POST['go_go_button']) && ! $error) {
    // process form
    // Ignore GET value and use POST value from form
    $memberid = intval($_POST['id']);

    $member = $OrgMembers->get_member_object($memberid);

    // TODO: Class validates objects and maintains errors/successs messages
    $member->change_forward($_POST['mailForward']);

    if ($member->is_error()) {
        $error = array_merge($error, $member->get_errors());
    } else {
        $member->update_ldap();
        $success = array_merge($success, $member->get_messages());
    }
}

// Process shell lock/unlock_form
if (isset($_POST['shell_form']) && !verify_nonce($_POST['nonce'], 'updateshelllock')) {
    $error[] = "Attempt to double submit form? No changes made.";
}

if (isset($_POST['shell_form']) && isset($_POST['lock_button']) && ! $error) {
    // process form
    // Ignore GET value and use POST value from form
    $memberid = intval($_POST['id']);

    $member = $OrgMembers->get_member_object($memberid);
    if ($member->disable_shell()) {
        $success[] = "Shell disabled";
    } else {
        $error[] = "Error disabling shell";
    }
}

if (isset($_POST['shell_form']) && isset($_POST['unlock_button']) && ! $error) {
    // process form
    // Ignore GET value and use POST value from form
    $memberid = intval($_POST['id']);

    $member = $OrgMembers->get_member_object($memberid);
    if ($member->enable_shell()) {
        $success[] = "Shell enabled";
    } else {
        $error[] = "Error enabling shell";
    }
}

// TODO:
// TODO: Lock accounts so user can't unlock them?
// Password
if (isset($_POST['passwordlock_form']) && !verify_nonce($_POST['nonce'], 'lockpassword')) {
    $error[] = "Attempt to double submit form? No changes made.";
}

if (isset($_POST['passwordlock_form']) && isset($_POST['force_pw_change']) && ! $error) {
    // Force password change and disable shell?

    // Ignore GET value and use POST value from form
    $memberid = intval($_POST['id']);

    $member = $OrgMembers->get_member_object($memberid);

    $member->disable_shell();
    $member->change_password('{crypt}accountlocked'.time());
    if ($member->is_error()) {
        $error = array_merge($error, $member->get_errors());
        $error = array_merge($error, $member->get_password_errors());
    } else {
        $success[] = "User account is now locked. Please direct user to <a href='resetpassword'>Password Reset</a> to renable access.";
        $member->update_ldap();
    }
    // Send reset email?
}

if (isset($_POST['password_form']) && !verify_nonce($_POST['nonce'], 'updatepassword')) {
    $error[] = "Attempt to double submit form? No changes made.";
}

if (isset($_POST['password_form']) && isset($_POST['go_go_button']) && ! $error) {
    // Ignore GET value and use POST value from form
    $memberid = intval($_POST['id']);

    $member = $OrgMembers->get_member_object($memberid);

    if ($_POST['new_password'] != $_POST['verify_password']) {
        $error[] = _("Passwords don't match");
    }

    if (! $error && $member->is_valid_password($_POST['new_password'])) {
        $member->change_password(cleanpassword($_POST['new_password']));

        if ($member->is_error()) {
            $error = array_merge($error, $member->get_errors());
        } else {
            $member->update_ldap();
            $success = array_merge($success, $member->get_messages());
        }
    } else {
        $error = array_merge($error, $member->get_password_errors());
    }

    // Send reset email?
}
// Delete member

// If we've processed a POST request, redirect back to ourself
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    redirect_with_messages('ctte-editmember?id=' . $memberid);
}

// Finished processing all the forms
if (!isset($member)) {
    $member = $OrgMembers->get_member_object($memberid);
}


$smarty->assign('member', $member);
$smarty->assign('all_groups', $OrgMembers->list_groups());
display_page('editmember.tpl');
