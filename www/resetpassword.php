<?php

require_once('PLUG/pagefunctions.inc.php');

require_once 'PLUG/Members.class.php';

$OrgMembers = new Members($ldap);

if(isset($_POST['resetpassword_form']))
{

    $memberemail = $_POST['email']; // TODO cleanup
    
    // search for member by email
    
    // if member by email then get object
   
    $member = $OrgMembers->get_member_by_email($memberemail);
    
    if($member)    
    {
        $reseturl  = 'http';
        if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") $reseturl .= "s";
        
        $reseturl .= "://";
        $reseturl .= $_SERVER['HTTP_HOST']. $_SERVER['PHP_SELF']."?";
        $reseturl .= "uid=".$member->uid();
        $reseturl .= "&reset=".$member->create_hash();
        
        $name = $member->givenName();
        
       //TODO move this into class as well?
        $resetemail ="
Hi $name,
You recently asked to reset your PLUG password. To complete your request, please follow this link:

$reseturl

If you did not request a new password please contact the PLUG admins at admin@plug.org.au

Thanks,
The PLUG Password Reset facility";


        if(mail($member->mail(), 'PLUG Password Reset', $resetemail, "From: admin@plug.org.au"))
        {
            $success[] = _('An email has been sent to your address with a reset link');            
        }
        else
        {
            $error[] = _('There was an error sending the reset email. Please report this to admin@plug.org.au');
        }


    }
    else
    {
        $error[] = _('Incorrect email address. Please check the address. If you are still having trouble please contact the admins at admin @ plug.org.au');
    }
    
}
else if(isset($_GET['uid']) && isset($_GET['reset']))
{
    $member = $OrgMembers->get_member_object(intval($_GET['uid']));
    
    if(PEAR::isError($member))
    {
        $error[] = $member->getMessage();
    }
    elseif($member->check_hash($_GET['reset']))
    {
        // Hash matches
        
        $smarty->assign('resetform', TRUE);
        $smarty->assign('username', $member->username());        
    
        if(isset($_POST['newpasswordreset_form']))
        {
            if($_POST['newpasswordconfirm'] != $_POST['newpassword'])
                $error[] = _("Passwords don't match");
                
            if(! $error && $member->is_valid_password($_POST['newpassword']))
            {
                // Change password
                $member->change_password(cleanpassword($_POST['newpassword']));
                if($member->is_error())
                {
                    $error = "Error changing password";
                    $error = array_merge($error, $member->get_errors());
                }else{
                    $success = array_merge($success, $member->get_messages());
                    $smarty->assign('resetform', FALSE);
                    $smarty->assign('successform', TRUE);
                    
                    $member->update_ldap();
                    
                }

            }else
            {
                $error = array_merge($error, $member->get_password_errors());
            
            }
                

        }
        else
        {
            // Display password reset form
            $success[] = _('Please enter a new password for your account.');
            $smarty->assign('resetform', TRUE);
        }    

    }
    else
    {
        $error[] = _('This reset link is invalid or has expired. Please get a new link');
    }

}
    display_page('resetpasswordform.tpl');