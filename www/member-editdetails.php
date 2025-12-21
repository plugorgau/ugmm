<?php

declare(strict_types=1);

$ACCESS_LEVEL = 'all';
$TOPLEVEL = 'home';

require_once('../lib/PLUG/session.inc.php');

$memberauthdata = $Auth->getAuthData();
$memberself = Person::load($ldap, $memberauthdata['dn']);

if (isset($_POST['edit_selfmember']) && !verify_nonce($_POST['nonce'], 'editselfdetails')) {
    $error[] = "Attempt to double submit form? No changes made.";
}

if (isset($_POST['edit_selfmember']) && isset($_POST['go_go_button']) && ! $error) {
    // TODO: Class validates objects and maintains errors/successs messages
    $memberself->change_email($_POST['mail']);
    $memberself->change_address($_POST['street']);
    $memberself->change_phone($_POST['homePhone'], $_POST['pager'], $_POST['mobile']);

    if ($memberself->is_error()) {
        //$error[] = "Member details not updated";
        $error = array_merge($error, $memberself->get_errors());
    } else {
        $memberself->update_ldap();
        $success[] = "Member details updated";
        $success = array_merge($success, $memberself->get_messages());

        redirect_with_messages($toplevelmenu['home']['link']);
    }
}

if (isset($_POST['edit_selfmember']) && isset($_POST['oops_button']) && ! $error) {
    $success[] = "Member details unchanged";
    redirect_with_messages($toplevelmenu['home']['link']);
}


$smarty->assign('member', $memberself);
display_page('editselfdetails.tpl');
