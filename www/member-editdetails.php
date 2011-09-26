<?php

$ACCESS_LEVEL = 'all';
$TOPLEVEL = 'home';
$PAGETITLE = ' - Editing Member Details';
$TITLE = 'Edit Member Details';

require_once('./PLUG/session.inc.php');

    $memberself = new Person($ldap);
    $memberauthdata = $Auth->getAuthData();
    $memberself->load_ldap($memberauthdata['dn']);
    
     if(isset($_POST['edit_selfmember']) && !verify_nonce($_POST['nonce'],'editselfdetails'))
        $error[] = "Attempt to double submit form? No changes made.";

    if(isset($_POST['edit_selfmember']) && isset($_POST['go_go_button']) && ! $error)
    {

        // TODO: Class validates objects and maintains errors/successs messages
        $memberself->change_email($_POST['email_address']);
        $memberself->change_address($_POST['street_address']);
        $memberself->change_phone($_POST['home_phone'], $_POST['work_phone'], $_POST['mobile_phone']);
        
        if($memberself->is_error())
        {
            //$error[] = "Member details not updated";
            $error = array_merge($error, $memberself->get_errors());
        }else{
            $memberself->update_ldap();
            $success[] = "Member details updated";
            $success = array_merge($success, $memberself->get_messages());

            redirect_with_messages($toplevelmenu['home']['link']);
        }
        
        $memberdetails = $memberself->userarray();

    }
    
    if(isset($_POST['edit_selfmember']) && isset($_POST['oops_button']) && ! $error)
    {
        $success[] = "Member details unchanged";
        redirect_with_messages($toplevelmenu['home']['link']);        
    }



// Finished processing all the forms
    if(!isset($memberdetails))
    {
        // Validate $_GET better? intval should clean it to just a number
        $memberdetails = $memberself->userarray();        
    }


    $smarty->assign('member', $memberdetails);
    //print_r($memberdetails);
    //print_r($error);
    //print_r($success);
    display_page('editselfdetails.tpl')
    
?>
