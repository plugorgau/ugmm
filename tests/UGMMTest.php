<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once '/usr/share/php/Symfony/Component/CssSelector/autoload.php';
require_once '/usr/share/php/Symfony/Component/Mime/autoload.php';
require_once '/usr/share/php/Symfony/Component/HttpClient/autoload.php';
require_once '/usr/share/php/Symfony/Component/BrowserKit/autoload.php';
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\BrowserKit\HttpBrowser;

const BASE_URL = 'http://localhost:8000';

final class UGMMTest extends TestCase
{
    private function assertText(Crawler $page, string $selector, string $value)
    {
        $text = implode('\n', $page->filter($selector)->each(
            function (Crawler $crawler, $i): string {
                return $crawler->text();
            }
        ));
        $this->assertStringContainsString($value, $text);
    }

    public function login(HttpBrowser $client, string $username, string $password): Crawler
    {
        $page = $client->request('GET', BASE_URL);
        $this->assertText($page, 'title', ' - Login');
        return $client->submitForm('Log In', [
            'username' => $username,
            'password' => $password,
        ]);
    }

    public function testLoginSuccess()
    {
        $client = new HttpBrowser();
        $page = $this->login($client, 'bobtest', 'test432bob');
        $this->assertText($page, 'title', ' - Member Details');
    }

    public function testLoginFailure()
    {
        $client = new HttpBrowser();
        $page = $this->login($client, 'bobtest', 'wrong');
        $this->assertText($page, 'title', 'PLUG - Members Area - Login');
        $this->assertText($page, '#errormessages strong', 'Incorrect Login.');
    }

    public function testLogout()
    {
        $client = new HttpBrowser();
        $page = $this->login($client, 'bobtest', 'test432bob');
        $this->assertText($page, 'title', ' - Member Details');
        $page = $client->clickLink('Logout');
        $this->assertText($page, 'title', ' - Login');
    }

    public function testMemberselfInfo()
    {
        $client = new HttpBrowser();
        $page = $this->login($client, 'bobtest', 'test432bob');

        $labels = $page->filter('.grid')->eq(0)->filter('.label');
        $fields = $page->filter('.grid')->eq(0)->filter('.field');
        $this->assertText($labels->eq(0), '*', 'E-mail Address');
        $this->assertText($fields->eq(0), '*', 'bob@plug.org.au');
        $this->assertText($labels->eq(1), '*', 'Postal Address');
        $this->assertText($fields->eq(1), '*', '42 Test Bvd, Nowheresville 6969');
        $this->assertText($labels->eq(2), '*', 'Home Phone');
        $this->assertText($fields->eq(2), '*', 'N/A');
        $this->assertText($labels->eq(3), '*', 'Work Phone');
        $this->assertText($fields->eq(3), '*', 'N/A');
        $this->assertText($labels->eq(4), '*', 'Mobile Phone');
        $this->assertText($fields->eq(4), '*', '0469 000000');

        $labels = $page->filter('.grid')->eq(1)->filter('.label');
        $fields = $page->filter('.grid')->eq(1)->filter('.field');
        $this->assertText($labels->eq(0), '*', 'Username');
        $this->assertText($fields->eq(0), '*', 'bobtest');
        $this->assertText($labels->eq(1), '*', 'Unix User ID');
        $this->assertText($fields->eq(1), '*', '10001');
        $this->assertText($labels->eq(2), '*', 'Shell');
        $this->assertText($fields->eq(2), '*', '/bin/bash');
        $this->assertText($labels->eq(3), '*', 'Account expires');
        $this->assertText($fields->eq(3), '*', 'Wednesday, 31 December 1969');
    }

    public function testMemberEditDetails()
    {
        $client = new HttpBrowser();
        $this->login($client, 'bobtest', 'test432bob');
        $page = $client->clickLink('Edit your personal details');
        $this->assertText($page, 'title', ' - Editing Member Details');

        $data = $page->selectButton('Update')->form()->getValues();
        $this->assertSame($data['mail'], 'bob@plug.org.au');
        $this->assertSame($data['street'], '42 Test Bvd, Nowheresville 6969');
        $this->assertSame($data['homePhone'], '');
        $this->assertSame($data['pager'], '');
        $this->assertSame($data['mobile'], '0469 000000');

        // Set home phone, and verify it has changed in the member details
        $page = $client->submitForm('Update', [
            'homePhone' => '08 5550 1234',
        ]);
        $this->assertText($page, 'title', ' - Member Details');
        $labels = $page->filter('.grid')->eq(0)->filter('.label');
        $fields = $page->filter('.grid')->eq(0)->filter('.field');
        $this->assertText($labels->eq(2), '*', 'Home Phone');
        $this->assertText($fields->eq(2), '*', '08 5550 1234');

        // And change it back
        $page = $client->clickLink('Edit your personal details');
        $data = $page->selectButton('Update')->form()->getValues();
        $this->assertSame($data['homePhone'], '08 5550 1234');
        $page = $client->submitForm('Update', [
            'homePhone' => '',
        ]);
    }

    public function testMemberEditDetailsCancel()
    {
        $client = new HttpBrowser();
        $this->login($client, 'bobtest', 'test432bob');
        $page = $client->clickLink('Edit your personal details');
        $page = $client->submitForm('Cancel', [
            'homePhone' => '08 5550 1234',
        ]);
        $labels = $page->filter('.grid')->eq(0)->filter('.label');
        $fields = $page->filter('.grid')->eq(0)->filter('.field');
        $this->assertText($labels->eq(2), '*', 'Home Phone');
        $this->assertText($fields->eq(2), '*', 'N/A');
    }

    public function testMemberEditForwarding()
    {
        $client = new HttpBrowser();
        $this->login($client, 'bobtest', 'test432bob');
        $page = $client->clickLink('Change your e-mail forwarding');
        $this->assertText($page, 'title', ' - Editing Member Email Forwarding');

        $data = $page->selectButton('Change')->form()->getValues();
        $this->assertSame($data['mailForward'], 'bob@example.com');

        // TODO: test posting the form
    }

    public function testMemberEditShell()
    {
        $client = new HttpBrowser();
        $this->login($client, 'bobtest', 'test432bob');
        $page = $client->clickLink('Change your shell account settings');
        $this->assertText($page, 'title', ' - Editing Member Shell');

        $data = $page->selectButton('Change Shell')->form()->getValues();
        $this->assertSame($data['account_shell'], 'bash');

        // TODO: test posting the form
    }

    public function testMemberEditPassword()
    {
        $client = new HttpBrowser();
        $this->login($client, 'bobtest', 'test432bob');
        $page = $client->clickLink('Change your PLUG password');
        $this->assertText($page, 'title', ' - Editing Member Password');

        $page = $client->submitForm('Change Password', [
            'current_password' => 'test432bob',
            'newpassword' => 'newpassword123',
            'newpasswordconfirm' => 'newpassword123',
        ]);
        $this->assertText($page, 'title', 'PLUG - Members Area - Member Details');
        $this->assertText($page, '#successmessages li', 'Password changed');

        // Try logging in with the new password, and change back
        $client->getCookieJar()->clear();
        $page = $this->login($client, 'bobtest', 'newpassword123');
        $this->assertText($page, 'title', ' - Member Details');
        $page = $client->clickLink('Change your PLUG password');
        $page = $client->submitForm('Change Password', [
            'current_password' => 'newpassword123',
            'newpassword' => 'test432bob',
            'newpasswordconfirm' => 'test432bob',
        ]);
    }

    public function testSignup()
    {
        $client = new HttpBrowser();
        $page = $client->request('GET', BASE_URL);
        $this->assertText($page, 'title', ' - Login');
        $page = $client->clickLink('Signup Form');
        $this->assertText($page, 'title', ' - Signup');

        // TODO: make sure we have a unique user ID
        $uid = sprintf('test%05d', rand(0, 99999));
        $page = $client->submitForm('Signup', [
            'givenName' => 'Test',
            'sn' => 'Last-name',
            'mail' => $uid . '@example.com',
            'street' => '123 Fake St',
            'homePhone' => '08 5550 1111',
            'pager' => '08 5550 2222',
            'mobile' => '08 5550 3333',
            'uid' => $uid,
            'password' => 'pass1234',
            'vpassword' => 'pass1234',
            'notes' => 'Sign up for testing',
        ]);
        $this->assertText($page, 'title', ' - Signup Complete');

        // Verify that we can log in as the new user
        $this->login($client, $uid, 'pass1234');
    }

    public function testSignupBadData()
    {
        $client = new HttpBrowser();
        $page = $client->request('GET', BASE_URL);
        $this->assertText($page, 'title', ' - Login');
        $page = $client->clickLink('Signup Form');
        $this->assertText($page, 'title', ' - Signup');

        // Try signing up with an existing user's details
        $page = $client->submitForm('Signup', [
            'givenName' => ' Test',
            'sn' => 'Last-name ',
            'mail' => 'bob@example.com',
            'street' => '123 Fake St',
            'homePhone' => '08 5550 1111',
            'pager' => '08 5550 2222',
            'mobile' => '08 5550 3333',
            'uid' => 'bobtest',
            'password' => 'pass1234',
            'vpassword' => 'pass1234',
            'notes' => 'Sign up for testing',
        ]);
        $this->assertText($page, 'title', ' - Signup');
        $this->assertText($page, '#errormessages strong', 'Username not available');
        // The previously entered information appears in the form
        $form = $page->selectButton('Signup')->form();
        $this->assertSame($form['givenName']->getValue(), 'Test');
        $this->assertSame($form['sn']->getValue(), 'Last-name');
        $this->assertSame($form['mail']->getValue(), 'bob@example.com');
        $this->assertSame($form['street']->getValue(), '123 Fake St');
        $this->assertSame($form['homePhone']->getValue(), '08 5550 1111');
        $this->assertSame($form['pager']->getValue(), '08 5550 2222');
        $this->assertSame($form['mobile']->getValue(), '08 5550 3333');
        $this->assertSame($form['uid']->getValue(), 'bobtest');
        $this->assertSame($form['notes']->getValue(), 'Sign up for testing');
    }

    public function testCommitteeMembers()
    {
        $client = new HttpBrowser();
        $this->login($client, 'chair', 'chairpass');
        $page = $client->clickLink('Committee');
        $this->assertText($page, 'title', ' - Membership List');
        // TODO: Check the page content
    }

    public function testCommitteeMembersAccessDenied()
    {
        $client = new HttpBrowser();
        $this->login($client, 'bobtest', 'test432bob');
        $page = $client->request('GET', BASE_URL . '/ctte-members');
        // Regular users cannot see pages requiring committee access
        $this->assertText($page, 'h1', 'WARNING');
        $this->assertSame($client->getResponse()->getStatusCode(), 403);
    }

    public function testCommitteeNewMember()
    {
        $client = new HttpBrowser();
        $this->login($client, 'chair', 'chairpass');
        $page = $client->clickLink('Committee');
        $this->assertText($page, 'title', ' - Membership List');
        $page = $client->clickLink('New Member');
        $this->assertText($page, 'title', ' - Add Member');

        // TODO: make sure we have a unique user ID
        $uid = sprintf('test%05d', rand(0, 99999));
        // First try signing up with an existing user ID
        $page = $client->submitForm('Add New Member', [
            'givenName' => 'Test',
            'sn' => 'Last-name',
            'mail' => $uid . '@example.com',
            'street' => '123 Fake St',
            'homePhone' => '08 5550 1111',
            'pager' => '08 5550 2222',
            'mobile' => '08 5550 3333',
            'uid' => 'bobtest',
            'password' => 'pass1234',
            'vpassword' => 'pass1234',
            'notes' => 'Sign up for testing',
        ]);
        $this->assertText($page, 'title', ' - Add Member');
        $this->assertText($page, '#errormessages strong', 'Username not available');

        // Submit with unique user ID
        $page = $client->submitForm('Add New Member', [
            'uid' => $uid,
            'password' => 'pass1234',
            'vpassword' => 'pass1234',
        ]);
        $this->assertText($page, 'title', ' - Add Member');
        $this->assertText($page, '#successmessages li', 'New member created with id ');
        // click 'Edit member NNN to make payment' link
        $link = $page->filter('a')->reduce(
            function (Crawler $node, $i): bool {
                return str_contains($node->text(), ' to make payment');
            }
        )->link();
        $page = $client->click($link);
        $this->assertText($page, 'title', ' - Edit Member');

        // Make a payment
        $payment_date = new DateTimeImmutable()->format('Y-m-d');
        $page = $client->submitForm('Make Payment', [
            'payment_date' => $payment_date,
            'receipt_number' => 'test payment',
            'payment_ack' => '1',
        ]);
        $this->assertText($page, 'title', ' - Edit Member');
        $this->assertText($page, '#successmessages li', 'Payment confirmation sent');
        $this->assertText($page, '#successmessages li', 'Payment processed');

        $rows = $page->filter('#past-payments')->filter('tbody')->eq(0)->children();
        $payment = $rows->eq(0)->children();
        $this->assertText($payment->eq(0), 'td', $payment_date);
        $this->assertText($payment->eq(1), 'td', '$50.00');
        $this->assertText($payment->eq(2), 'td', 'Full');
        $this->assertText($payment->eq(3), 'td', '1');
        $this->assertText($payment->eq(4), 'td', 'test payment');
    }

    public function testCommitteeRecentPayments()
    {
        $client = new HttpBrowser();
        $this->login($client, 'chair', 'chairpass');
        $page = $client->clickLink('Committee');
        $this->assertText($page, 'title', ' - Membership List');
        $page = $client->clickLink('Recent Payments');
        $this->assertText($page, 'title', ' - Recent Payments');
    }

    public function testResetPassword()
    {
        $client = new HttpBrowser();
        $page = $client->request('GET', BASE_URL);
        $this->assertText($page, 'title', ' - Login');
        $page = $client->clickLink('Forgotten your password?');
        $this->assertText($page, 'title', ' - Reset Password');

        $page = $client->submitForm('Send Reset Email', [
            'email' => 'bob@plug.org.au',
        ]);
        $this->assertText($page, 'title', ' - Reset Password');
        $this->assertText($page, '#successmessages li', 'An email has been sent');

        // Get password reset URL from email
        $fp = fopen('/tmp/ugmm-mbox', 'r');
        $reset_url = '';
        while (!feof($fp)) {
            $line = fgets($fp);
            if ($line !== false && str_contains($line, '/resetpassword?')) {
                $reset_url = trim($line);
            }
        }
        fclose($fp);
        $this->assertNotSame($reset_url, '');

        // Change the password
        $page = $client->request('GET', $reset_url);
        $this->assertText($page, 'title', ' - Reset Password');
        $page = $client->submitForm('Change Password', [
            'newpassword' => 'newpass123',
            'newpasswordconfirm' => 'newpass123',
        ]);
        $this->assertText($page, 'title', ' - Reset Password');
        $this->assertText($page, '#successmessages li', 'Password changed');

        // Try logging in with the new password, and change back
        $client->getCookieJar()->clear();
        $page = $this->login($client, 'bobtest', 'newpass123');
        $this->assertText($page, 'title', ' - Member Details');
        $page = $client->clickLink('Change your PLUG password');
        $page = $client->submitForm('Change Password', [
            'current_password' => 'newpass123',
            'newpassword' => 'test432bob',
            'newpasswordconfirm' => 'test432bob',
        ]);
    }
}
