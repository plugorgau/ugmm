<?php

/**
 * Define all things here that are Organisation specific
 */

define('CONCESSION_AMOUNT', 2000);
define('FULL_AMOUNT', 5000);

define('CONCESSION_TYPE', 2);
define('FULL_TYPE', 1);

define('COMMITTEE_EMAIL', "committee@plug.org.au");
define('CONTACT_EMAIL', "committee@plug.org.au");
define('WEBMASTERS_EMAIL', "webmasters@plug.org.au");
define('ADMIN_EMAIL', "admin@plug.org.au");
define('SCRIPTS_FROM_EMAIL', "PLUG Membership Scripts <admin@plug.org.au>");
define('SCRIPTS_REPLYTO_EMAIL', "PLUG Committee <committee@plug.org.au>");

define('PAYMENT_OPTIONS',
    " (a) Head down to the next PLUG workshop or seminar to pay your dues to
     a committee member (e-mail ".COMMITTEE_EMAIL." beforehand to make
     sure there will be somebody there to renew your membership).

 (b) Direct deposit your dues into PLUG's bank account (see
     http://www.plug.org.au/membership for details), and email
     ".COMMITTEE_EMAIL." to let them know you have deposited it.
     Credit card facilities are available if no other method is
     available to you, just contact the committee to organise."
);

define('LDAP_BASE', 'dc=plug,dc=org,dc=au');
define('DEFAULT_MEMBER', 'cn=admin,dc=plug,dc=org,dc=au');
