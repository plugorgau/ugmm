<?php

declare(strict_types=1);

$ACCESS_LEVEL = 'committee';
$TOPLEVEL = 'ctte';

require_once('../lib/PLUG/session.inc.php');

$OrgMembers = new Members($ldap);

$since = new DateTimeImmutable()->sub(new DateInterval('P2Y'));
$smarty->assign('payments', $OrgMembers->payments_since($since));

display_page('recentpayments.tpl');
