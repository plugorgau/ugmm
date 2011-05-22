<?php

require_once('session.inc.php');

require_once 'PLUG.class.php';
    
    $PLUG = new PLUG($ldap);
    
    // Validate $_GET better? intval should clean it to just a number
    $memberdetails = $PLUG->get_member(intval($_GET['memberid']));
    $smarty->assign('member', $memberdetails);
    //print_r($memberdetails);
    display_page('editmember.tpl')

?>
