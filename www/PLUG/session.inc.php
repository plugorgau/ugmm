<?php
/* Based off session.inc.php which is also written by me (Timothy White) and GPL'ed */

/* Page load time */
   $mtime = microtime();
   $mtime = explode(" ",$mtime);
   $mtime = $mtime[1] + $mtime[0];
   $pagestarttime = $mtime;

/**/
// It is safe to assume that lib/ is present next to www/ during dev and after install
define('AUTH_DIR', dirname(dirname(dirname(__FILE__))) . "/lib/pear-Auth");
//# define('AUTH_DIR', "/usr/share/plug-ugmm/lib/pear-Auth");
set_include_path(get_include_path() . PATH_SEPARATOR . AUTH_DIR);

require_once('/etc/private/ldapconnection.inc.php');
require_once('config.inc.php');
require_once('pagefunctions.inc.php');
require_once('accesscheck.inc.php');
require_once('Auth.php');

require_once 'Members.class.php';

function loginForm($username = null, $status = null, &$auth = null)
{
    global $smarty;
    $smarty->clearAssign('MenuItems');
    $smarty->clearAssign("LoggedInUsername");
    $smarty->assign('username', $username);

    switch ($status)
    {
        case '':
        case 0:
            break;
        case AUTH_IDLED:
        case AUTH_EXPIRED:
            $error = "Your session has expired. Please login again";
            //AdminLog::getInstance()->log("Expired Session");
            break;
        case AUTH_WRONG_LOGIN:
            $error = "Incorrect Login.";
            //AdminLog::getInstance()->log("Invalid Login");
            break;
        case AUTH_SECURITY_BREACH:
            $error = "Security Issue. Please login again";
            //AdminLog::getInstance()->log("Security Issue With Login");
            break;
        default:
            $error = "Authentication Issue. Please report to Admin";
            //AdminLog::getInstance()->log("Auth Issues: $status");
    }

    if (isset($error)) $smarty->assign("error", $error);
    display_page('loginform.tpl');
    exit();
}

$options = array(
    'host' => 'ldapi:///',
    'basedn' => "ou=Users,dc=plug,dc=org,dc=au",
    'attributes' => array('dn', 'uid', 'memberOf'),
    'groupfilter' => '(objectClass=groupOfNames)',
    'memberattr' => 'member',
    'version' => 3,
    //'group' => 'admin'
    );


$Auth = new Auth("LDAP", $options, "loginForm");

$Auth->setAdvancedSecurity(array(
    AUTH_ADV_USERAGENT => true,
    AUTH_ADV_IPCHECK   => true,
    AUTH_ADV_CHALLENGE => false
));
$Auth->setIdle(600);
$Auth->setSessionName("secureplug");


/* *
 * If we wanted to support logging in via email address, here we need to detect
 * an email address as username ($_POST['username']) and then do a ldapsearch
 * (mail=$_POST['username']) and replace $_POST['username' with the uid from
 * the search. The do $Auth->start(); with the new details
$_POST['username'] = uid;
*/

$Auth->start();

if (!$Auth->checkAuth())
{
 // THIS CODE SHOULD NEVER RUN as we display the login form and exit if not authenticated
    echo "Should never get here";
    exit();
}
elseif (isset($_GET['logout']))
{
//    AdminLog::getInstance()->log("Log out");
    $Auth->logout();
    $Auth->start(); // restarts login process, so shows form
    exit; // This should never run
}
else
{
    $smarty->assign("LoggedInUsername", $Auth->getUsername());
}

//print_r($Auth->getAuthData());

$_SESSION['loggedinusername'] = $Auth->getUsername();


if (! check_level($ACCESS_LEVEL))
{
    http_response_code(403);
    display_page('accessdenied.tpl');
    exit;
}


// Nonce code based on Wordpress nonce code but added storing in session to make real nonce (instead of wordpress nonce which is valid for 6-12 hours (or even 24) and can be reused as many times in that time.

function nonce_tick() {
    $nonce_life = 86400 / 2;

    return ceil(time() / ( $nonce_life / 2 ));
}

function verify_nonce($nonce, $action = -1) {

    // Check if nonce exists
    if (!isset($_SESSION['nonce'][$nonce]))
        return false;
    $valid = false;

    // Check if nonce is still valid
    $randnum = $_SESSION['nonce'][$nonce];
    // Nonce generated 0-6 hours ago
    if ( create_nonce($action, 0, $randnum) == $nonce )
        $valid = true;
    // Nonce generated 6-12 hours ago
    if ( create_nonce($action, 1, $randnum) == $nonce )
        $valid = true;

    if ($valid)
        unset($_SESSION['nonce'][$nonce]); // Unlike WP, we only use it once
    // $valid will be false if it's expired
    return $valid;
}

function create_nonce($action = -1, $tick = 0, $randnum = 0) {
    $user = $_SESSION['loggedinusername'];
    $i = nonce_tick() - $tick;

    $randnum = $randnum ? $randnum : rand(); //

    $nonce =  substr(sha1($i . $action . $user . $randnum . 'nonce'), -12, 10);
    $_SESSION['nonce'][$nonce] = $randnum;
    return $nonce;
}

$smarty->registerPlugin('modifier', 'nonce', 'create_nonce');


# vim: set tabstop=4 shiftwidth=4 :
# Local Variables:
# tab-width: 4
# end:
