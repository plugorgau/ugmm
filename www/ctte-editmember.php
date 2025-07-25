<?php

$ACCESS_LEVEL = 'committee';
$TOPLEVEL = 'ctte';

require_once('./PLUG/session.inc.php');

    $OrgMembers = new Members($ldap);
    
    if(intval($_GET['id']) < 10000)
    {
        header("Location: ldapusers.php");
    }
 
     if(isset($_POST['personals_form']) && !verify_nonce($_POST['nonce'],'editmember'))
        $error[] = "Attempt to double submit form? No changes made.";

    if(isset($_POST['personals_form']) && ! $error)
    {
        // process form
        // Ignore GET value and use POST value from form
        $memberid = intval($_POST['id']);
        

        $member = $OrgMembers->get_member_object($memberid);
        // Validate each item and update memberdetails object
        

        // TODO: Class validates objects and maintains errors/successs messages
        $member->change_username($_POST['uid']);            
        $member->change_name($_POST['first_name'], $_POST['last_name']);
        $member->change_email($_POST['email_address']);
        $member->change_address($_POST['street_address']);
        $member->change_phone($_POST['home_phone'], $_POST['work_phone'], $_POST['mobile_phone']);
        $member->change_description($_POST['notes']);
        
        
        if($member->is_error())
        {
            //$error[] = "Member details not updated";
            $error = array_merge($error, $member->get_errors());
        }else{
            $member->update_ldap();
            $success[] = "Member details updated";
            $success = array_merge($success, $member->get_messages());
        }
        
        $memberdetails = $member->userarray();

    }

// Process payment form
    if(isset($_POST['payment_form']) && !verify_nonce($_POST['nonce'],'makepayment'))
        $error[] = "Attempt to double submit form? No changes made.";

    if(isset($_POST['payment_form']) && ! $error)
    {
        // process form
        // Ignore GET value and use POST value from form
        $memberid = intval($_POST['id']);

        $member = $OrgMembers->get_member_object($memberid);
        // Validate each item and update memberdetails object
        $payment_type = trim($_POST['membership_type']);
        $payment_years = trim($_POST['years']);
        $payment_date = trim($_POST['payment_date']);
        $payment_comment = trim($_POST['receipt_number']);
        $payment_ack = trim($_POST['payment_ack']);
        
        // Allow future dating here? TODO: Prevent future dating
        // Class will ensure date is "now" if empty, we need to ensure it's valid
        if($payment_date != '' && !strtotime($payment_date))
            $error[] = "Invalid payment date";
        // TODO: validate
        if(!$error)
        {
            
            $member->makePayment($payment_type, $payment_years, $payment_date, $payment_comment, $payment_ack);
            if($member->is_error())
            {
                $success = $member->get_messages();
                $error = $member->get_errors();
            }
            else
            {
                $success = $member->get_messages();
            }
        }
        
        $memberdetails = $member->userarray();
    }

// Process email forwarding format_ph
     if(isset($_POST['email_form']) && !verify_nonce($_POST['nonce'],'updateemailforwarding'))
        $error[] = "Attempt to double submit form? No changes made.";

    if(isset($_POST['email_form']) && isset($_POST['go_go_button']) && ! $error)
    {
    
        // process form
        // Ignore GET value and use POST value from form
        $memberid = intval($_POST['id']);

        $member = $OrgMembers->get_member_object($memberid);

        // TODO: Class validates objects and maintains errors/successs messages
        $member->change_forward($_POST['email_forward']);
        
        if($member->is_error())
        {
            $error = array_merge($error, $member->get_errors());
        }else{
            $member->update_ldap();
            $success = array_merge($success, $member->get_messages());
        }
        
        $memberdetails = $member->userarray();

    }
    
// Process shell lock/unlock_form
    if(isset($_POST['shell_form']) && !verify_nonce($_POST['nonce'],'updateshelllock'))
        $error[] = "Attempt to double submit form? No changes made.";    
        
    if(isset($_POST['shell_form']) && isset($_POST['lock_button']) && ! $error)
    {
        // process form
        // Ignore GET value and use POST value from form
        $memberid = intval($_POST['id']);

        $member = $OrgMembers->get_member_object($memberid);
        if($member->disable_shell())
        {
            $success[] = "Shell disabled";
        }else
        {
            $error[] = "Error disabling shell";
        }
    }    

    if(isset($_POST['shell_form']) && isset($_POST['unlock_button']) && ! $error)
    {
        // process form
        // Ignore GET value and use POST value from form
        $memberid = intval($_POST['id']);

        $member = $OrgMembers->get_member_object($memberid);
        if($member->enable_shell())
        {
            $success[] = "Shell enabled";
        }else
        {
            $error[] = "Error enabling shell";
        }
    }            

// TODO:
// TODO: Lock accounts so user can't unlock them?
// Password
    if(isset($_POST['passwordlock_form']) && !verify_nonce($_POST['nonce'],'lockpassword'))
        $error[] = "Attempt to double submit form? No changes made.";    
        
    if(isset($_POST['passwordlock_form']) && isset($_POST['force_pw_change']) && ! $error)
    {
        // Force password change and disable shell?
        
        // Ignore GET value and use POST value from form
        $memberid = intval($_POST['id']);

        $member = $OrgMembers->get_member_object($memberid);
        
        $member->disable_shell();
        $member->change_password('{crypt}accountlocked'.time());
        if($member->is_error())
        {
                $error = array_merge($error, $member->get_errors());
                $error = array_merge($error, $member->get_password_errors());                
        }else
        {
            $success[] = "User account is now locked. Please direct user to <a href='resetpassword'>Password Reset</a> to renable access.";
            $member->update_ldap();
        }
        // Send reset email?
    }
    
    if(isset($_POST['password_form']) && !verify_nonce($_POST['nonce'],'updatepassword'))
        $error[] = "Attempt to double submit form? No changes made.";      
    
    if(isset($_POST['password_form']) && isset($_POST['go_go_button']) && ! $error)
    {
        
        // Ignore GET value and use POST value from form
        $memberid = intval($_POST['id']);

        $member = $OrgMembers->get_member_object($memberid);
        
        if($_POST['new_password'] != $_POST['verify_password'])
            $error[] = _("Passwords don't match");
            
        if(! $error && $member->is_valid_password($_POST['new_password']))        
        {
            $member->change_password(cleanpassword($_POST['new_password']));
            
            if($member->is_error())
            {
                $error = array_merge($error, $member->get_errors());
            }else{
                $member->update_ldap();
                $success = array_merge($success, $member->get_messages());

            }
        }else
        {
            $error = array_merge($error, $member->get_password_errors());
        
        }
        
        // Send reset email?
    }    
// Delete member

// Finished processing all the forms
    if(!isset($memberdetails))
    {
        // Validate $_GET better? intval should clean it to just a number
        $memberid = intval($_GET['id']);
        $member = $OrgMembers->get_member_object($memberid);
        $memberdetails = $member->userarray();        
    }


    $smarty->assign('member', $memberdetails);
    //print_r($memberdetails);
    //print_r($error);
    //print_r($success);
    display_page('editmember.tpl');
