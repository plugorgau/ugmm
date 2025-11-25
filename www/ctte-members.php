<?php

declare(strict_types=1);

$ACCESS_LEVEL = 'committee';
$TOPLEVEL = 'ctte';

require_once('../lib/PLUG/session.inc.php');

$OrgMembers = new Members($ldap);

$smarty->assign('currentusers', $OrgMembers->get_current_members());
$smarty->assign('overdueusers', $OrgMembers->get_overdue_members());
$smarty->assign('pendingusers', $OrgMembers->get_pending_members());
$smarty->assign('expiredusers', array());

if (isset($_GET['expiredmembers']) && $_GET['expiredmembers']) {
    $smarty->assign('expiredusers', $OrgMembers->get_expired_members());
}
display_page('listusers.tpl');
