<?php


function check_level($ACCESS_LEVEL)
{
    global $Auth;
    // Require admin level unless defined. This prevents us from accidentally forgetting to set an access level and users getting access to things they aren't allowed
    $ACCESS_LEVEL = isset($ACCESS_LEVEL) ? $ACCESS_LEVEL : 'admin';
    // Check if level of access required is in the memberOf array

    $user_details = $Auth->getAuthData();
    
    if ($ACCESS_LEVEL == "all") return TRUE;

    if (!is_array($ACCESS_LEVEL)) $ACCESS_LEVEL = array($ACCESS_LEVEL);
    
    foreach($ACCESS_LEVEL as $level)
    {
        $groupname = "cn=$level,ou=Groups,".LDAP_BASE;
        
        if (is_array($user_details['memberOf']))
        {
            $groups = $user_details['memberOf'];
        }
        else
        {
            $groups = array($user_details['memberOf']);
        }

        if (in_array($groupname, $groups))
        {
            // User is in correct group!
            return TRUE;
        }
    }
    
    return FALSE;
}

if (! check_level($ACCESS_LEVEL))
{

    display_page('accessdenied.tpl');
    exit;
}


# vim: set tabstop=4 shiftwidth=4 :
# Local Variables:
# tab-width: 4
# end:
