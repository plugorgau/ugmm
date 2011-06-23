<?php

$ACCESS_LEVEL = 'committee';
$TOPLEVEL = 'ctte';
$PAGETITLE = ' - Membership List';
$TITLE = 'Membership List';

require_once('session.inc.php');

require_once 'PLUG.class.php';
    
    $PLUG = new PLUG($ldap);
    
    $smarty->assign('currentusers', $PLUG->get_current_members());
    $smarty->assign('pendingusers', $PLUG->get_pending_members());
    if($_GET['expiredmembers'])
    {
        $smarty->assign('expiredusers', $PLUG->get_expired_members());        
    }
    display_page('listusers.tpl');

?>
