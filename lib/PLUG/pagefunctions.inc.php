<?php

require_once('config.inc.php');
require_once('accesscheck.inc.php');

if (!isset($pagestarttime)) // For pages that don't need auth
{
    /* Page load time */
       $mtime = microtime();
       $mtime = explode(" ",$mtime);
       $mtime = $mtime[1] + $mtime[0];
       $pagestarttime = $mtime;
    /**/

}

require_once('smarty4/Smarty.class.php');

// create object
$smarty = new Smarty;
$smarty->setTemplateDir(dirname(__FILE__) . "/templates");
$smarty->setCompileDir('/var/cache/plug-ugmm/templates_c');
$smarty->compile_check = true;

$smarty->registerPlugin('modifier', 'date', 'date');
$smarty->registerPlugin('modifier', 'sizeof', 'sizeof');

function page_gen_stats($params, $smarty) {
   global $pagestarttime;
   $mtime = microtime();
   $mtime = explode(" ",$mtime);
   $mtime = $mtime[1] + $mtime[0];
   $endtime = $mtime;
   $totaltime = round(($endtime - $pagestarttime), 2);
   $totalmem = memory_get_peak_usage(true) / 1024 / 1024;
   return 'Page generated in '.$totaltime.' seconds using ' . $totalmem . 'Mb mem';
}
$smarty->registerPlugin('function', 'page_gen_stats', 'page_gen_stats');

$toplevelmenu = array(
    'home' => array('label' => "Home", 'link' => 'memberself', 'level' => 'all'),
    //'admin' => array('label' => "Admin", 'link' => '', 'level' => 'admin'),
    'ctte' => array('label' => "Committee", 'link' => 'ctte-members', 'level' => 'committee'),
    'logout' => array('label' => "Logout", 'link' => 'logout', 'level' => 'all'),
    //'web' => array('label' => "Webmasters", 'link' => '', 'level' => array('admin', 'committee', 'webmaster')),
);

// Submenu's level is defined by parent level
$submenu = array(
    'ctte' => array(
        'members' => array('label' => "Membership List", 'link' => 'ctte-members'),
        'expiredmembers' => array('label' => "Expired Members", 'link' => 'ctte-members?expiredmembers=1'),
        'newmember' => array('label' => "New Member", 'link' => 'ctte-newmember'),
        'editmember' => array('label' => '', 'link' => 'ctte-editmember?id='),
        'resendack' => array('label' => '', 'link' => 'resendack?member_id='),
    ),

    'admin' => array(
        'usergroups' => array('label' => "Manage User Groups", 'link' => '/~tim/plugldap/'),
        'emailaliases' => array('label' => "Manage Email Aliases", 'link' => '/~tim/plugldap/'),
    ),

    'home' => array(
        'editselfdetails' => array('label' => "Change details", 'link' => 'member-editdetails'),
        'editselfforwarding' => array('label' => "Change forwarding", 'link' => 'member-editforwarding'),
        'editselfshell' => array('label' => "Change shell", 'link' => 'member-editshell'),
        'editselfpassword' => array('label' => "Change password", 'link' => 'member-editpassword'),
    ),
);

// Assign menu arrays so we can softcode links in templates
$smarty->assign('topmenuitems', $toplevelmenu);
$smarty->assign('submenuitems', $submenu);

// Membership amount
$smarty->assign('CONCESSION_AMOUNT', "$" . CONCESSION_AMOUNT / 100);
$smarty->assign('FULL_AMOUNT', "$" . FULL_AMOUNT / 100 );

// External links
$smarty->assign('external_links', EXTERNAL_LINKS);

// Email addresses
$smarty->assign('emails', array(
    'webmasters' => WEBMASTERS_EMAIL,
    'committee' => COMMITTEE_EMAIL,
    'admin' => ADMIN_EMAIL,
));

// Shells
$shells = array(
    'zsh' => '/usr/bin/zsh',
    'bash' => '/bin/bash',
    'csh' => '/bin/csh',
);


// Need them defined as arrays for array_merge
$error=array();
$success = array();

function display_page($template)
{
        global $smarty, $error, $success, $TOPLEVEL;

        //-- Needed by menu.tpl --
        list($topmenu, $menu) = generate_menus($TOPLEVEL);

        $smarty->assign('topmenu', $topmenu);
        $smarty->assign('submenu', $menu);
        //--------------------------

        //-- Needed by messages.tpl --
        // Bring in messages from session
        if (isset($_SESSION['errormessages']) || isset($_SESSION['successmessages']))
        {
            $error = array_merge($error, $_SESSION['errormessages']);
            $success = array_merge($success, $_SESSION['successmessages']);
            unset($_SESSION['errormessages']);
            unset($_SESSION['successmessages']);
        }

        $smarty->assign('errors', $error);
        $smarty->assign('success', $success);
        //---------------------------

        return $smarty->display($template);
}

function generate_menus($top = '')
{
    global $Auth, $toplevelmenu, $submenu;

    $top = $top ? $top : 'home';

    $smenu = array();
    $topmenu = array();

    // Check if we are authenticated
    foreach ($toplevelmenu as $key => $menu)
    {
        if (check_level($menu['level']))
        {
            $topmenu[$key]  = $menu;
            if ($key == $top)
                $smenu = $submenu[$key];
        }
    }
    return array($topmenu, $smenu);
}

function redirect_with_messages($url)
{
    global $error, $success;
    $_SESSION['errormessages'] = $error;
    $_SESSION['successmessages'] = $success;
    http_response_code(303);
    header('Location: ' . $url);
    exit();
}

// Clean functions from GRASE Hotspot
function cleantext($text)
{

	$text = strip_tags($text);
	$text = str_replace("<", "", $text);
	$text = str_replace(">", "", $text);

#	$text = htmlspecialchars($text, ENT_NOQUOTES);
#	$text = mysql_real_escape_string($text);

	return trim($text);
}

function cleanpassword($text)
{
    // TODO: Filter out prohibited chars?
    return $text;
}
/* Need locale code for clean_number
function clean_number($number)
{
    global $locale;
    $fmt = new NumberFormatter( $locale, NumberFormatter::DECIMAL );
    return $fmt->parse(ereg_replace("[^\.,0-9]", "", clean_text($number)));
}

*/
function clean_int($number)
{
    return intval($number);
    //return intval(clean_number($number));
    //ereg_replace("[^0-9]", "", clean_text($number));
}


# vim: set tabstop=4 shiftwidth=4 :
# Local Variables:
# tab-width: 4
# end:
