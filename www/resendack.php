<?php

require_once('./PLUG/session.inc.php');
    
    $PLUG = new PLUG($ldap);
    
    
    if(intval($_GET['member_id']) < 10000)
    {
        header("Location: ldapusers.php");
    }
    
    $memberid = intval($_GET['member_id']);
    $member = $PLUG->get_member_object($memberid);        
    $memberdetails = $member->userarray(); 
    $memberpayments = $member->paymentsarray();       
    
    $paymentid = intval($_GET['payment_id']);
    
    if(! isset($memberpayments[$paymentid]))
    {
        $error[] = "Invalid payment ID.";
        display_page('returnerror.tpl');
        exit;
    }

    if(isset($_POST['resend_ack_form']) && !verify_nonce($_POST['nonce'],'resendack'))
        $error[] = "Attempt to double submit form? No changes made.";
    
   
    if(isset($_POST['resend_ack_form']) && ! $error)
    {

        if($_POST['member_id'] != $_GET['member_id'] ||  $_POST['payment_id'] != $_GET['payment_id'])
        {
            // Error with submission or trying to modify something?
            $error[] = "Problem with form submission. Possible attempt to modify another user";
        }
        else
        {
            if(isset($_POST['go_go_button']))
            {
                // Call function to resend ack
                //resend_payment_ack($memberid, $paymentid);
                if($member->sendPaymentReceipt($paymentid))
                {
                    $success[] = "Payment Acknowledgement resent";
                }else
                {
                    $error[] = "Error sending Payment Acknowledgement.";
                }
            }else
            {
                $success[] = "Payment Acknowledgement has not been resent";
            }
        }
    }

    //print_r($memberpayments);
    $smarty->assign('errors', $error);
    $smarty->assign('success', $success);    
    $smarty->assign('member', $memberdetails);
    $smarty->assign('payment', $memberpayments[intval($_GET['payment_id'])]);

    
    display_page('resend-ack.tpl');
        
?>
