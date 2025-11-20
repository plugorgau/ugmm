<?php

declare(strict_types=1);

$ACCESS_LEVEL = 'all';
$TOPLEVEL = 'home';

require_once('../lib/PLUG/session.inc.php');

$memberauthdata = $Auth->getAuthData();
$memberself = Person::load($ldap, $memberauthdata['dn']);

$smarty->assign('memberself', $memberself->userarray());

display_page('memberself.tpl');
