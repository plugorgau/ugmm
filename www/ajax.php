<?php

declare(strict_types=1);

require_once '../lib/PLUG/Members.class.php';

$OrgMembers = new Members($ldap);

switch ($_POST['ajax']) {
    case "checkusername":
        $username = trim(htmlentities($_POST['uid']));
        if ($username) {
            if (strlen($username) < 3) {
                echo "<span style='color:#f00'>Username too short</span>";
                break;
            }
            if (! $OrgMembers->check_username_available($username)) {
                echo "<span style='color:#0c0'>Username $username is available</span>";
            } else {
                echo "<span style='color:#f00'>Username $username is not available</span>";
            }
        }
        break;
}
