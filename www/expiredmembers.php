<?php

require_once('ldapconnection.inc.php');

// Create days after epoch for now and find all accounts < this (grace period of 5 days?)
$today = ceil(time()/ 86400) - 5;

// Select all accounts not already in group expired
$filter = "(&(shadowExpire<=$today)(memberOf=cn=currentmembers,ou=Groups,dc=plug,dc=org,dc=au))"

// Email that their account has expired?

// Remove from group current. Add to group expired
    // 2 ldap objects, add/delete from both, the update in one it(2 hits)
