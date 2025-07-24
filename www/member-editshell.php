<?php

$ACCESS_LEVEL = 'all';
$TOPLEVEL = 'home';

require_once('./PLUG/session.inc.php');

    $memberself = new Person($ldap);
    $memberauthdata = $Auth->getAuthData();
    $memberself->load_ldap($memberauthdata['dn']);
    
     if(isset($_POST['edit_selfshell']) && !verify_nonce($_POST['nonce'],'editselfshell'))
        $error[] = "Attempt to double submit form? No changes made.";

    if(isset($_POST['edit_selfshell']) && isset($_POST['go_go_button']) && ! $error)
    {

        // TODO: Class validates objects and maintains errors/successs messages
        if(!isset($shells[$_POST['account_shell']]))
            $error[] = "Invalid shell";
        
        if(!$error)
        {
            $memberself->change_shell($shells[$_POST['account_shell']]);
            
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
        }
        
        $memberdetails = $memberself->userarray();

    }
    
    if(isset($_POST['edit_selfshell']) && isset($_POST['oops_button']) && ! $error)
    {
        $success[] = "Shell unchanged";
        redirect_with_messages($toplevelmenu['home']['link']);        
    }
    
    if(isset($_POST['edit_selfshell']) && isset($_POST['disable_shell']) && ! $error)
    {
        if($memberself->disable_shell())
        {
            $success[] = "Shell disabled";
            redirect_with_messages($toplevelmenu['home']['link']);        
        }else
        {
            $error[] = "Error disabling shell";
        }
    }    

    if(isset($_POST['edit_selfshell']) && isset($_POST['enable_shell']) && ! $error)
    {
        if($memberself->enable_shell())
        {
            $success[] = "Shell enabled";
            redirect_with_messages($toplevelmenu['home']['link']);        
        }else
        {
            $error[] = "Error enabling shell";
        }
    }    


// Finished processing all the forms
    if(!isset($memberdetails))
    {
        // Validate $_GET better? intval should clean it to just a number
        $memberdetails = $memberself->userarray();        
    }


    $smarty->assign('shells', $shells);
    $smarty->assign('member', $memberdetails);
    display_page('editselfshell.tpl');
