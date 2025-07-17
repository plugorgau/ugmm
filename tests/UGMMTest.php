<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once '/usr/share/php/Symfony/Component/CssSelector/autoload.php';
require_once '/usr/share/php/Symfony/Component/Mime/autoload.php';
require_once '/usr/share/php/Symfony/Component/HttpClient/autoload.php';
require_once '/usr/share/php/Symfony/Component/BrowserKit/autoload.php';
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\BrowserKit\HttpBrowser;


final class UGMMTest extends TestCase {

    private function assertText(Crawler $page, string $selector, string $value) {
        $text = $page->filter($selector)->text();
        $this->assertSame($text, $value);
    }

    public function login(HttpBrowser $client, string $username, string $password): Crawler {
        $page = $client->request('GET', 'http://localhost:8000');
        $this->assertText($page, 'title', 'PLUG - Members Area - Login');
        return $client->submitForm('Log In', [
            'username' => $username,
            'password' => $password,
        ]);
    }

    public function testLoginSuccess() {
        $client = new HttpBrowser();
        $page = $this->login($client, 'bobtest', 'test432bob');
        $this->assertText($page, 'title', 'PLUG - Members Area - Member Details');
    }

    public function testLoginFailure() {
        $client = new HttpBrowser();
        $page = $this->login($client, 'bobtest', 'wrong');
        $this->assertText($page, 'title', 'PLUG - Members Area - Login');
        $this->assertText($page, '#errormessages strong', 'Incorrect Login.');
    }

    public function testMemberselfInfo() {
        $client = new HttpBrowser();
        $page = $this->login($client, 'bobtest', 'test432bob');

        $rows = $page->filter('table')->eq(0)->children();
        $this->assertText($rows->eq(0), 'th', 'E-mail Address');
        $this->assertText($rows->eq(0), 'td', 'bob@plug.org.au');
        $this->assertText($rows->eq(1), 'th', 'Postal Address');
        $this->assertText($rows->eq(1), 'td', '42 Test Bvd, Nowheresville 6969');
        $this->assertText($rows->eq(2), 'th', 'Home Phone');
        $this->assertText($rows->eq(2), 'td', 'N/A');
        $this->assertText($rows->eq(3), 'th', 'Work Phone');
        $this->assertText($rows->eq(3), 'td', 'N/A');
        $this->assertText($rows->eq(4), 'th', 'Mobile Phone');
        $this->assertText($rows->eq(4), 'td', '0469 000000');

        $rows = $page->filter('table')->eq(1)->children();
        $this->assertText($rows->eq(0), 'th', 'Username');
        $this->assertText($rows->eq(0), 'td', 'bobtest');
        $this->assertText($rows->eq(1), 'th', 'Unix User ID');
        $this->assertText($rows->eq(1), 'td', '6969');
        $this->assertText($rows->eq(2), 'th', 'Shell');
        $this->assertText($rows->eq(2), 'td', '/bin/sh');
        $this->assertText($rows->eq(3), 'th', 'Account expires');
        $this->assertText($rows->eq(3), 'td', 'Wednesday, 31 December 1969');
    }

    public function testMemberEditDetails() {
        $client = new HttpBrowser();
        $this->login($client, 'bobtest', 'test432bob');
        $page = $client->clickLink('Edit your personal details');
        $this->assertText($page, 'title', 'PLUG - Members Area - Editing Member Details');

        $data = $page->selectButton('Update')->form()->getValues();
        $this->assertSame($data['email_address'], 'bob@plug.org.au');
        $this->assertSame($data['street_address'], '42 Test Bvd, Nowheresville 6969');
        $this->assertSame($data['home_phone'], '');
        $this->assertSame($data['work_phone'], '');
        $this->assertSame($data['mobile_phone'], '0469 000000');

        // Set home phone, and verify it has changed in the member details
        $page = $client->submitForm('Update', [
            'home_phone' => '08 5550 1234',
        ]);
        $this->assertText($page, 'title', 'PLUG - Members Area - Member Details');
        $rows = $page->filter('table')->eq(0)->children();
        $this->assertText($rows->eq(2), 'th', 'Home Phone');
        $this->assertText($rows->eq(2), 'td', '08 5550 1234');

        // And change it back
        $page = $client->clickLink('Edit your personal details');
        $data = $page->selectButton('Update')->form()->getValues();
        $this->assertSame($data['home_phone'], '08 5550 1234');
        $page = $client->submitForm('Update', [
            'home_phone' => '',
        ]);
    }

    public function testMemberEditDetailsCancel() {
        $client = new HttpBrowser();
        $this->login($client, 'bobtest', 'test432bob');
        $page = $client->clickLink('Edit your personal details');
        $page = $client->submitForm('Cancel', [
            'home_phone' => '08 5550 1234',
        ]);
        $rows = $page->filter('table')->eq(0)->children();
        $this->assertText($rows->eq(2), 'th', 'Home Phone');
        $this->assertText($rows->eq(2), 'td', 'N/A');
    }
}
