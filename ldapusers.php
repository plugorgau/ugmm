<?php

require_once('session.inc.php');

require_once 'PLUG.class.php';

require_once 'Net/LDAP2.php';
    // The configuration array:
    $config = array (
        'binddn'    => 'cn=admin,dc=plug,dc=org,dc=au',
        'bindpw'    => 'plug',
        'basedn'    => 'dc=plug,dc=org,dc=au',
        'host'      => 'localhost'
    );

    // Connecting using the configuration:
    $ldap = Net_LDAP2::connect($config);

    // Testing for connection error
    if (PEAR::isError($ldap)) {
        die('Could not connect to LDAP-server: '.$ldap->getMessage());
    } 
    
    $PLUG = new PLUG($ldap);
    
    $smarty->assign('currentusers', $PLUG->get_current_members());
    $smarty->assign('pendingusers', $PLUG->get_pending_members());
    $smarty->assign('expiredusers', $PLUG->get_expired_members());        
    display_page('listusers.tpl');

?>
