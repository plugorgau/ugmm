<?php

declare(strict_types=1);

/**
 * Define all things here that are Organisation specific
 */

date_default_timezone_set('Australia/Perth');

const CONCESSION_AMOUNT = 2000;
const FULL_AMOUNT = 5000;

const CONCESSION_TYPE = 2;
const FULL_TYPE = 1;

const EXTERNAL_LINKS = array(
    "home" => "https://www.plug.org.au/",
    "contact" => "https://www.plug.org.au/contact/",
    "membership" => "https://www.plug.org.au/membership/",
    "lists" => "https://www.plug.org.au/resources/mailing-list/",
    "events" => "https://www.plug.org.au/events/",
);

const COMMITTEE_EMAIL = "committee@plug.org.au";
const CONTACT_EMAIL = "committee@plug.org.au";
const WEBMASTERS_EMAIL = "webmasters@plug.org.au";
const ADMIN_EMAIL = "admin@plug.org.au";
const SCRIPTS_FROM_EMAIL = "PLUG Membership Scripts <admin@plug.org.au>";
const SCRIPTS_REPLYTO_EMAIL = "PLUG Committee <committee@plug.org.au>";

const PAYMENT_OPTIONS =
    " (a) Head down to the next PLUG workshop or seminar to pay your dues to
     a committee member (e-mail ".COMMITTEE_EMAIL." beforehand to make
     sure there will be somebody there to renew your membership).

 (b) Direct deposit your dues into PLUG's bank account (see
     http://www.plug.org.au/membership for details), and email
     ".COMMITTEE_EMAIL." to let them know you have deposited it.
     Credit card facilities are available if no other method is
     available to you, just contact the committee to organise.";

const LDAP_BASE = 'dc=plug,dc=org,dc=au';
const DEFAULT_MEMBER = 'cn=admin,dc=plug,dc=org,dc=au';
