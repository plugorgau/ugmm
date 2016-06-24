<?php

$ACCESS_LEVEL = 'all';
$TOPLEVEL = 'home';
$PAGETITLE = 'Logout';
$TITLE = 'Logout';

require_once('./PLUG/session.inc.php');

// Logout
$Auth->logout();

// Redirect to main page.
header('Location: '. $toplevelmenu['home']['link']);
?>
Redirecting to login page
<?
exit();