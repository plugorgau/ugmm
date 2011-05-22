<?php
/* Based off session.inc.php which is also written by me (Timothy White) and GPL'ed */

/* Page load time */
   $mtime = microtime();
   $mtime = explode(" ",$mtime);
   $mtime = $mtime[1] + $mtime[0];
   $pagestarttime = $mtime; 

/**/
require_once('ldapconnection.inc.php');
require_once('pagefunctions.inc.php');
require_once('Auth.php');

function loginForm($username = null, $status = null, &$auth = null)
{
    global $smarty;
    $smarty->clear_assign('MenuItems');
    $smarty->clear_assign("LoggedInUsername");
    $smarty->assign('username', $username);
    
    switch($status)
    {
        case 0:
            break;
        case -1:
        case -2:
            $error = "Your session has expired. Please login again";
            //AdminLog::getInstance()->log("Expired Session");
            break; 
        case -3:
            $error = "Incorrect Login.";
            //AdminLog::getInstance()->log("Invalid Login");
            break;
        case -5:
            $errro = "Security Issue. Please login again";
            //AdminLog::getInstance()->log("Security Issue With Login");
            break;
        default:
            $error = "Authentication Issue. Please report to Admin";
            //AdminLog::getInstance()->log("Auth Issues: $status");
    }
    
    if(isset($error)) $smarty->assign("error", $error);
    display_page('loginform.tpl');
    exit();
}

$options = array(
    'host' => 'localhost',
    'attributes' => array('dn', 'uid', 'memberOf'),
    'groupfilter' => '(objectClass=groupOfNames)',
    'memberattr' => 'member',
    'version' => 3,
    'group' => 'admin'
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
}elseif(isset($_GET['logoff']))
{
//    AdminLog::getInstance()->log("Log out");
    $Auth->logout();
    $Auth->start(); // restarts login process, so shows form
}else
{
    $smarty->assign("LoggedInUsername", $Auth->getUsername());
}

print_r($Auth->getAuthData());

?>

