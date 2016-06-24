<?php

session_start();

require_once('PLUG/pagefunctions.inc.php');

require_once 'PLUG/Members.class.php';

$OrgMembers = new Members($ldap);

if(isset($_POST['membersignup_form'])) {
    // Check password matches
    if($_POST['password'] != $_POST['vpassword'])
    {
        $error[] = "Passwords don't match";      
    }
    
    $password = $_POST['password'];

    // Password strength check? Assign random password if null?    
    if(strlen(trim($_POST['password'])) == 0)
        $password = '{crypt}accountlocked'.time();    
    
    list($valid, $perrors) = PLUGFunction::is_valid_password($password);
    if(!$error && !$valid)
    {
        $error = array_merge($error, $perrors);
    }
        

    
    // TODO check for email address already used
        
    if(! $error)
    {
        $notes = "";
        if(strlen(trim($_POST['notes'])) > 0)
            $notes = "Signup Notes\n".trim($_POST['notes']);
            
        $member = $OrgMembers->new_member(
            trim($_POST['uid']),
            trim($_POST['givenName']),
            trim($_POST['sn']),
            trim($_POST['street']),
            trim($_POST['homePhone']),
            trim($_POST['pager']), 
            trim($_POST['mobile']),
            trim($_POST['mail']),
            $password,
            $notes
        );
        
        if($member->is_error())
        {
            $error = array_merge($error, $member->get_errors());
            
            // Member details so can edit and correct
            $memberdetails = $member->userarray();            
            $smarty->assign('newmember', @$memberdetails);
            $smarty->assign('newmembernotes', $_POST['notes']);
        }else
        {
            //$success = array_merge($success, $member->get_messages());
            $success[] = "Your membership is pending payment";
            $smarty->assign("usercreated", TRUE);
            $details = $member->userarray();
            $smarty->assign("newmember", $details);
            send_waitingpayment_email($member, $details);
            // TODO: Email user with instructions
            // TODO: Take them to a different page with payment details
            display_page('signupcompleted.tpl');
            exit();
            
        }

    }else{
        $memberdetails = $_POST;
        $smarty->assign('newmember', @$memberdetails);
        $smarty->assign('newmembernotes', $_POST['notes']);     
    }
}

display_page('signup.tpl');


function send_waitingpayment_email($member, $details)
{
    $body = "Dear %s,
    
Your PLUG membership is awaiting payment before it is activated.

If you have already paid, please email ".COMMITTEE_EMAIL." to sort out your
account activation. Otherwise you have several options for payment:

".PAYMENT_OPTIONS."
     
Membership fees are \$%s per year, or \$%s per year for holders of a
current student or concession card.

You may choose not to pay membership, in which case your PLUG membership and
shell account will not be actived. However, the mailing list is still freely
accessible to non-members.

If you have any queries, please do not hesitate to contact the PLUG
committee via email at ".COMMITTEE_EMAIL.".

Regards,

PLUG Membership Scripts";

    $body = sprintf($body,
        $details['displayName'],
        FULL_AMOUNT / 100,
        CONCESSION_AMOUNT / 100
    );
        
    $subject = "Your PLUG Membership is awaiting payment";
    
    $member->send_user_email($body, $subject);
}