<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__FILE__).'/../lib/PLUG/config.inc.php';
require_once dirname(__FILE__).'/../lib/PLUG/Members.class.php';

final class MembersTest extends TestCase
{
    private ?Members $members;

    protected function setUp(): void
    {
        include '/etc/private/ldapconnection.inc.php';
        $this->members = new Members($ldap);
    }

    private function newMember(): Person
    {
        $uid = sprintf('unit%05d', rand(0, 99999));
        return $this->members->new_member($uid, "First", "Last", "123 Fake St", "08 9555 1111", "08 9555 2222", "08 9555 3333", $uid."@example.com", "password", "notes");
    }

    public function testNewMember(): void
    {
        $member = $this->newMember();
        $this->assertSame($member->givenName, "First");

        // We can load the newly created member from LDAP too
        $member2 = $this->members->get_member_object($member->uidNumber);
        $this->assertSame($member->mail, $member2->mail);
    }

    public function testMakePayment(): void
    {
        $member = $this->newMember();
        $this->assertSame($member->expiry()['expiry_raw'], 1);

        $id = $member->makePayment(FULL_TYPE, 1, new DateTimeImmutable('2025-11-15'), "message", false);

        // Payment object stores the appropriate info
        $payment = $member->payments[$id];
        $this->assertSame($payment->id, $id);
        $this->assertSame($payment->amount, 1 * FULL_AMOUNT);
        $this->assertSame($payment->formatteddate, '2025-11-15');
        $this->assertSame($payment->type, FULL_TYPE);
        $this->assertSame($payment->description, "message");
        $this->assertSame($payment->years, 1);

        // Expiry has been set to one year after
        $this->assertSame($member->expiry()['expiry'], '15 Nov 26');
    }

    public function testMakePaymentBeforeExpiry(): void
    {
        $member = $this->newMember();

        $member->makePayment(FULL_TYPE, 1, new DateTimeImmutable('2025-11-15'), "", false);
        $this->assertSame($member->expiry()['expiry'], '15 Nov 26');

        // Making a payment before the expiry date extends the old expiry
        $member->makePayment(FULL_TYPE, 1, new DateTimeImmutable('2026-09-01'), "", false);
        $this->assertSame($member->expiry()['expiry'], '15 Nov 27');
    }

    public function testMakePaymentDuringGracePeriod(): void
    {
        $member = $this->newMember();

        $member->makePayment(FULL_TYPE, 1, new DateTimeImmutable('2025-11-15'), "", false);
        $this->assertSame($member->expiry()['expiry'], '15 Nov 26');

        // Making a payment after expiry, but within grace period extends expiry
        $member->makePayment(FULL_TYPE, 1, new DateTimeImmutable('2026-12-25'), "", false);
        $this->assertSame($member->expiry()['expiry'], '15 Nov 27');
    }

    public function testMakePaymentAfterGracePeriod(): void
    {
        $member = $this->newMember();

        $member->makePayment(FULL_TYPE, 1, new DateTimeImmutable('2025-11-15'), "", false);
        $this->assertSame($member->expiry()['expiry'], '15 Nov 26');

        // Making a payment after grace period dates membership from payment date
        $member->makePayment(FULL_TYPE, 1, new DateTimeImmutable('2027-03-14'), "", false);
        $this->assertSame($member->expiry()['expiry'], '14 Mar 28');
    }

    public function testMakePaymentOverLeapYear(): void
    {
        $member = $this->newMember();

        // A payment will cover 366 days during a leap year
        $member->makePayment(FULL_TYPE, 1, new DateTimeImmutable('2023-06-01'), "", false);
        $this->assertSame($member->expiry()['expiry'], '01 Jun 24');
    }
}
