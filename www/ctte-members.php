<?php

$ACCESS_LEVEL = 'committee';
$TOPLEVEL = 'ctte';
$PAGETITLE = ' - Membership List';
$TITLE = 'Membership List';

require_once('./PLUG/session.inc.php');
    
    $OrgMembers = new Members($ldap);
    
    $smarty->assign('currentusers', $OrgMembers->get_current_members());
    $smarty->assign('overdueusers', $OrgMembers->get_overdue_members());
    $smarty->assign('pendingusers', $OrgMembers->get_pending_members());
    $smarty->assign('expiredusers', FALSE);

    if(@$_GET['expiredmembers'])
    {
        $smarty->assign('expiredusers', $OrgMembers->get_expired_members());
    }
    display_page('listusers.tpl');
