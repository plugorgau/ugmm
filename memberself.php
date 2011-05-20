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
    
    $memberself = new Person($ldap);
    $memberauthdata = $Auth->getAuthData();
    $memberself->load_ldap($memberauthdata['dn']);
    
    print_r($memberself->userarray());
    $smarty->assign('memberself', $memberself->userarray());
    
    display_page('memberself.tpl');
    
?>
