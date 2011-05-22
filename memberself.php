<?php

require_once('session.inc.php');

require_once 'PLUG.class.php';
    
    $memberself = new Person($ldap);
    $memberauthdata = $Auth->getAuthData();
    $memberself->load_ldap($memberauthdata['dn']);
    
    print_r($memberself->userarray());
    $smarty->assign('memberself', $memberself->userarray());
    
    display_page('memberself.tpl');
    
?>
