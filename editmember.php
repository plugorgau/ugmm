<?php

require_once('session.inc.php');

require_once 'PLUG.class.php';
    
    $PLUG = new PLUG($ldap);
    
    
    if(intval($_GET['id']) < 10000)
    {
        header("Location: ldapusers.php");
    }
 

    if(isset($_POST['personals_form']))
    {
        // process form
        
        // Ignore GET value and use POST value from form
        $memberid = intval($_POST['id']);
        

        $member = $PLUG->get_member_object($memberid);        
        // Validate each item and update memberdetails object
        
        if((string)$_POST['mobile_phone'] === (string)$memberdetails['mobile'])
        {
            echo "matches";
        }
        
        // TODO: Class validates objects and maintains errors/successs messages
        $member->change_name($_POST['first_name'], $_POST['last_name']);
        $member->change_email($_POST['email_address']);
        $member->change_address($_POST['street_address']);
        $member->change_phone($_POST['home_phone'], $_POST['work_phone'], $_POST['mobile_phone']);
        $member->change_description($_POST['notes']);
        $member->update_ldap();
        
        $memberdetails = $member->userarray();
    }
    else
    {
        // Validate $_GET better? intval should clean it to just a number
        $memberid = intval($_GET['id']);
        $memberdetails = $PLUG->get_member($memberid);        
    }

    $smarty->assign('member', $memberdetails);
    //print_r($memberdetails);
    display_page('editmember.tpl')

?>
