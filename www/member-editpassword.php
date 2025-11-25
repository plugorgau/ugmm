<?php

declare(strict_types=1);

$ACCESS_LEVEL = 'all';
$TOPLEVEL = 'home';

require_once('../lib/PLUG/session.inc.php');

$memberauthdata = $Auth->getAuthData();
$memberself = Person::load($ldap, $memberauthdata['dn']);

if (isset($_POST['edit_selfpassword']) && !verify_nonce($_POST['nonce'], 'editselfpassword')) {
    $error[] = "Attempt to double submit form? No changes made.";
}

if (isset($_POST['edit_selfpassword']) && isset($_POST['oops_button']) && ! $error) {
    $success[] = "Password unchanged";
    redirect_with_messages($toplevelmenu['home']['link']);
}

$oldpasswordhash = $memberself->userPassword;
if (isset($_POST['edit_selfpassword']) && ! validatePassword($_POST['current_password'], $oldpasswordhash)) {
    $error[] = "Old password does not match";
}


if (isset($_POST['edit_selfpassword']) && isset($_POST['go_go_button']) && ! $error) {
    // Class validates objects and maintains errors/successs messages
    if ($_POST['newpasswordconfirm'] != $_POST['newpassword']) {
        $error[] = _("Passwords don't match");
    }

    if (!$error && $memberself->is_valid_password($_POST['newpassword'])) {
        $memberself->change_password(cleanpassword($_POST['newpassword']));

        if ($memberself->is_error()) {
            //$error[] = "Member details not updated";
            $error = array_merge($error, $memberself->get_errors());
        } else {
            $memberself->update_ldap();
            //$success[] = "Email forwarding updated";
            $success = array_merge($success, $memberself->get_messages());

            redirect_with_messages($toplevelmenu['home']['link']);
        }
    } else {
        $error = array_merge($error, $memberself->get_password_errors());
    }
}

$smarty->assign('member', $memberself);
display_page('editselfpassword.tpl');
