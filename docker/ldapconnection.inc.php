<?php

// Copy this to /etc/private/ldapconnection.inc.php and modify it to suit your ldap database

require_once 'Net/LDAP2.php';
    // The configuration array:
    $config = array (
        'binddn'    => 'cn=admin,dc=plug,dc=org,dc=au',
        'bindpw'    => 'plug',
        'basedn'    => 'dc=plug,dc=org,dc=au',
        'host'      => 'openldap'
    );

    // Connecting using the configuration:
    $ldap = Net_LDAP2::connect($config);

    // Testing for connection error
    if (PEAR::isError($ldap)) {
        die('Could not connect to LDAP-server: '.$ldap->getMessage());
    }
