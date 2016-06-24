<?php

$ACCESS_LEVEL = 'committee';
$TOPLEVEL = 'ctte';
$PAGETITLE = ' - Add Member';
$TITLE = 'Add New Member';

require_once('./PLUG/session.inc.php');

    $OrgMembers = new Members($ldap);

     if(isset($_POST['newmember_form']) && !verify_nonce($_POST['nonce'],'newmember'))
        $error[] = "Attempt to double submit form? No changes made.";

        // Check password matches
    if(isset($_POST['newmember_form']) && $_POST['password'] != $_POST['verifypassword'])
        $error[] = "Passwords don't match";
        
    // TODO Password strength check? lock password if null?
    
    // TODO check for email address already used
        
    if(isset($_POST['newmember_form']) && ! $error)
    {

        $member = $OrgMembers->new_member(
            trim($_POST['uid']),
            trim($_POST['first_name']),
            trim($_POST['last_name']),
            trim($_POST['street_address']),
            trim($_POST['home_phone']),
            trim($_POST['work_phone']), 
            trim($_POST['mobile_phone']),
            trim($_POST['email_address']),
            $_POST['password'],
            trim($_POST['notes'])
        );
        
        if($member->is_error())
        {
            $error = array_merge($error, $member->get_errors());
            
            // Member details so can edit and correct
            $memberdetails = $member->userarray();            
        }else
        {
            $success = array_merge($success, $member->get_messages());
            $smarty->assign("usercreated", TRUE);
            $smarty->assign("newmember", $member->userarray());
        }

    }
    
    
    $smarty->assign('member', @$memberdetails);
    //print_r($memberdetails);
    //print_r($error);
    //print_r($success);
    display_page('newmember.tpl')
      
              
?>
