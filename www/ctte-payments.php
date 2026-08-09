<?php

declare(strict_types=1);

$ACCESS_LEVEL = 'committee';
$TOPLEVEL = 'ctte';

require_once('../lib/PLUG/session.inc.php');

if (isset($_GET['since'])) {
    $since = new DateTimeImmutable($_GET['since']);
} else {
    $since = new DateTimeImmutable()->sub(new DateInterval('P2Y'));
}

$OrgMembers = new Members($ldap);
$payments = $OrgMembers->payments_since($since);

if (!(isset($_GET['format']) && $_GET['format'] == 'beancount')) {
    $smarty->assign('since', $since->format('Y-m-d'));
    $smarty->assign('payments', $payments);
    display_page('recentpayments.tpl');
    exit;
}

// Beancount output format
const CURRENCY = 'AUD';
const INCOME_ACCOUNT = 'Income:Membership';
const ACCOUNT_OPEN_DATE = '1998-09-14';
const RECEIVABLES_ACCOUNT_PREFIX = 'Assets:Receivables:Member:';

// Create a beancount account for the given member
function receivables_account(Person $member): string
{
    // Replace forbidden characters in the user name
    $uid = preg_replace('/[^A-Za-z0-9-]/', '-', $member->uid);
    // Make sure the first character is upper case, if it is a letter
    $uid = ucfirst($uid);
    return RECEIVABLES_ACCOUNT_PREFIX . $uid;
}

header('Content-Type: text/plain;charset=UTF-8');
echo ";; -*- mode: beancount -*-

;; Membership payments since {$since->format('Y-m-d')} imported from UGMM
";

$seen = array();
ksort($payments);
foreach ($payments as $payment) {
    $member = $payment->member;

    $memberName = addslashes($member->displayName);
    $receivables = receivables_account($member);

    // Open the receivables account if this is the first time we've
    // seen the member.
    if (!array_key_exists($receivables, $seen)) {
        $seen[$receivables] = true;
        echo "
".ACCOUNT_OPEN_DATE." open {$receivables} ".CURRENCY."
  member-name: \"{$memberName}\"
";
    }

    $description = addslashes($payment->description);
    $amount = sprintf("-%.2f", $payment->amount / 100);
    echo "
{$payment->date->format('Y-m-d')} * \"{$memberName}\" \"{$description}\"
  membership-type: \"{$payment->formattedtype}\"
  membership-years: \"{$payment->years}\"
  ".INCOME_ACCOUNT.":{$payment->formattedtype} {$amount} ".CURRENCY."
  {$receivables}
";
}
