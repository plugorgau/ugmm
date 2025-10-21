<?php

require_once '../lib/PLUG/Members.class.php';

    
    $OrgMembers = new Members($ldap);
    
    echo  $OrgMembers->next_freeuidNumber(10383);

    var_dump($ldap->dnExists("uidNumber=10390,ou=Users,dc=plug,dc=org,dc=au"));
    echo $ldap->getEntry("uidNumber=10390,ou=Users,dc=plug,dc=org,dc=au")->getMessage();
    
    //$testperson = new Person($ldap);
    
    //$testperson->load_ldap("uidNumber=10063,ou=Users,dc=plug,dc=org,dc=au");
    
    //$hash = createPasswordHash('password');
    //echo "$hash"; echo "</br>";
    //echo validate('password', $hash);
    
    /*
    $testperson->change_address("PO Box 470 Floreat WA 6014");
    $testperson->update_ldap();*/
/*    $testperson->create_person('11111', 'blinkybill', 'blinky', 'bill', '', 'blinky@gmail.com', 'blah');*/
    //$testperson->print_ldif();
