<?php

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
    
    $plug = new PLUG($ldap);
    
    echo $plug->check_username_available('LinuxAlien2');
    
    //$testperson = new Person($ldap);
    
    //$testperson->load_ldap("uidNumber=10063,ou=Users,dc=plug,dc=org,dc=au");
    /*
    $testperson->change_address("PO Box 470 Floreat WA 6014");
    $testperson->update_ldap();*/
/*    $testperson->create_person('11111', 'blinkybill', 'blinky', 'bill', '', 'blinky@gmail.com', 'blah');*/
    //$testperson->print_ldif();
    
?>
