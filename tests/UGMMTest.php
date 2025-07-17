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

    public function testLoginSuccess() {
        $client = new HttpBrowser();
        $page = $client->request('GET', 'http://localhost:8000');
        $this->assertText($page, 'title', 'PLUG - Members Area - Login');

        $page = $client->submitForm('Log In', [
            'username' => 'bobtest',
            'password' => 'test432bob',
        ]);
        $this->assertText($page, 'title', 'PLUG - Members Area - Member Details');
    }

    public function testLoginFailure() {
        $client = new HttpBrowser();
        $page = $client->request('GET', 'http://localhost:8000');
        $this->assertText($page, 'title', 'PLUG - Members Area - Login');

        $page = $client->submitForm('Log In', [
            'username' => 'bobtest',
            'password' => 'wrong',
        ]);
        $this->assertText($page, 'title', 'PLUG - Members Area - Login');
        $this->assertText($page, '#errormessages strong', 'Incorrect Login.');
    }
}
