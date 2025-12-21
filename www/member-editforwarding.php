<?php

declare(strict_types=1);

$ACCESS_LEVEL = 'all';
$TOPLEVEL = 'home';

require_once('../lib/PLUG/session.inc.php');

$memberauthdata = $Auth->getAuthData();
$memberself = Person::load($ldap, $memberauthdata['dn']);

if (isset($_POST['edit_selfforwarding']) && !verify_nonce($_POST['nonce'], 'editselfforwarding')) {
    $error[] = "Attempt to double submit form? No changes made.";
}

if (isset($_POST['edit_selfforwarding']) && isset($_POST['go_go_button']) && ! $error) {
    // TODO: Class validates objects and maintains errors/successs messages
    $memberself->change_forward($_POST['mailForward']);

    if ($memberself->is_error()) {
        //$error[] = "Member details not updated";
        $error = array_merge($error, $memberself->get_errors());
    } else {
        $memberself->update_ldap();
        //$success[] = "Email forwarding updated";
        $success = array_merge($success, $memberself->get_messages());

        redirect_with_messages($toplevelmenu['home']['link']);
    }
}

if (isset($_POST['edit_selfforwarding']) && isset($_POST['oops_button']) && ! $error) {
    $success[] = "Email Forwarding unchanged";
    redirect_with_messages($toplevelmenu['home']['link']);
}

$smarty->assign('member', $memberself);
display_page('editselfforwarding.tpl');
