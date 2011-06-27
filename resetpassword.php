<?php

require_once('ldapconnection.inc.php');
require_once('pagefunctions.inc.php');

require_once 'PLUG.class.php';

$PLUG = new PLUG($ldap);

if(isset($_POST['resetpassword_form']))
{

    $memberemail = $_POST['email']; // TODO cleanup
    
    // search for member by email
    
    // if member by email then get object
   
    $member = $PLUG->get_member_by_email($memberemail);
    
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
    $member = $PLUG->get_member_object(intval($_GET['uid']));
    
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
            // Hash matched and we have a new password
            $newpassword = cleanpassword($_POST['newpassword']);
            if($newpassword == '')
                $error[] = _('Blank password not allowed');
                
            if($newpassword != $_POST['newpassword'])
                $error[] = _('Invalid characters used in password');
            if(cleanpassword($_POST['newpasswordconfirm']) != $newpassword)
                $error[] = _("Passwords don't match");
                
            if(strlen($newpassword) < 7)
                $error[] = _("Password too short");
                
            if( !preg_match("#[0-9]+#", $newpassword) )
            	$error[] = _("Password must include at least one number");
            if( !preg_match("#[a-zA-Z]+#", $newpassword) )
            	$error[] = _("Password must include at least one letter");            	
                
            if(sizeof($error) == 0)
            {
                // Change password
                $member->change_password($newpassword);
                if($member->is_error())
                {
                    $error = "Error changing password";
                    $error = array_merge($error, $member->get_errors());
                }else{
                    $success = array_merge($success, $member->get_messages());
                    $smarty->assign('resetform', FALSE);
                    $smarty->assign('successform', TRUE);
                    
                }

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

    /*$smarty->assign('errors', $error);
    $smarty->assign('success', $success);*/
    //print_r($memberdetails);
    display_page('resetpasswordform.tpl');


?>
