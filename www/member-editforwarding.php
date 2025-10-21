<?php

$ACCESS_LEVEL = 'all';
$TOPLEVEL = 'home';

require_once('../lib/PLUG/session.inc.php');

    $memberself = new Person($ldap);
    $memberauthdata = $Auth->getAuthData();
    $memberself->load_ldap($memberauthdata['dn']);
    
     if(isset($_POST['edit_selfforwarding']) && !verify_nonce($_POST['nonce'],'editselfforwarding'))
        $error[] = "Attempt to double submit form? No changes made.";

    if(isset($_POST['edit_selfforwarding']) && isset($_POST['go_go_button']) && ! $error)
    {

        // TODO: Class validates objects and maintains errors/successs messages
        $memberself->change_forward($_POST['email_forward']);
        
        if($memberself->is_error())
        {
            //$error[] = "Member details not updated";
            $error = array_merge($error, $memberself->get_errors());
        }else{
            $memberself->update_ldap();
            //$success[] = "Email forwarding updated";
            $success = array_merge($success, $memberself->get_messages());

            redirect_with_messages($toplevelmenu['home']['link']);
        }
        
        $memberdetails = $memberself->userarray();

    }
    
    if(isset($_POST['edit_selfforwarding']) && isset($_POST['oops_button']) && ! $error)
    {
        $success[] = "Email Forwarding unchanged";
        redirect_with_messages($toplevelmenu['home']['link']);        
    }



// Finished processing all the forms
    if(!isset($memberdetails))
    {
        // Validate $_GET better? intval should clean it to just a number
        $memberdetails = $memberself->userarray();        
    }


    $smarty->assign('member', $memberdetails);
    display_page('editselfforwarding.tpl');
