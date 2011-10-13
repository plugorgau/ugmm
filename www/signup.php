<?php

require_once('PLUG/pagefunctions.inc.php');

require_once 'PLUG/PLUG.class.php';

$PLUG = new PLUG($ldap);

display_page('signup.tpl');

?>
