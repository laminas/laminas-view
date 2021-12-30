<?php

namespace LaminasTest\View\Helper;

use Laminas\View\Helper;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Tests Laminas_View_Helper_ServerUrl
 *
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
class ServerUrlTest extends TestCase
{
    /**
     * Back up of $_SERVER
     *
     * @var array
     */
    protected $serverBackup;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp(): void
    {
        $this->serverBackup = $_SERVER;
        unset($_SERVER['HTTPS']);
        unset($_SERVER['SERVER_PORT']);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown(): void
    {
        $_SERVER = $this->serverBackup;
    }

    public function testConstructorWithOnlyHost(): void
    {
        $_SERVER['HTTP_HOST'] = 'example.com';

        $url = new Helper\ServerUrl();
        $this->assertEquals('http://example.com', $url->__invoke());
    }

    public function testConstructorWithOnlyHostIncludingPort(): void
    {
        $_SERVER['HTTP_HOST'] = 'example.com:8000';

        $url = new Helper\ServerUrl();
        $this->assertEquals('http://example.com:8000', $url->__invoke());
    }

    public function testConstructorWithHostAndHttpsOn(): void
    {
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['HTTPS']     = 'on';

        $url = new Helper\ServerUrl();
        $this->assertEquals('https://example.com', $url->__invoke());
    }

    public function testConstructorWithHostAndHttpsTrue(): void
    {
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['HTTPS'] = true;

        $url = new Helper\ServerUrl();
        $this->assertEquals('https://example.com', $url->__invoke());
    }

    public function testConstructorWithHostIncludingPortAndHttpsTrue(): void
    {
        $_SERVER['HTTP_HOST'] = 'example.com:8181';
        $_SERVER['HTTPS'] = true;

        $url = new Helper\ServerUrl();
        $this->assertEquals('https://example.com:8181', $url->__invoke());
    }

    public function testConstructorWithHostReversedProxyHttpsTrue(): void
    {
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
        $_SERVER['SERVER_PORT'] = 80;

        $url = new Helper\ServerUrl();
        $this->assertEquals('https://example.com', $url->__invoke());
    }

    public function testConstructorWithHttpHostIncludingPortAndPortSet(): void
    {
        $_SERVER['HTTP_HOST'] = 'example.com:8181';
        $_SERVER['SERVER_PORT'] = 8181;

        $url = new Helper\ServerUrl();
        $this->assertEquals('http://example.com:8181', $url->__invoke());
    }

    public function testConstructorWithHttpHostAndServerNameAndPortSet(): void
    {
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = 8080;

        $url = new Helper\ServerUrl();
        $this->assertEquals('http://example.com:8080', $url->__invoke());
    }

    public function testConstructorWithNoHttpHostButServerNameAndPortSet(): void
    {
        unset($_SERVER['HTTP_HOST']);
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = 8080;

        $url = new Helper\ServerUrl();
        $this->assertEquals('http://example.org:8080', $url->__invoke());
    }

    public function testServerUrlWithTrueParam(): void
    {
        $_SERVER['HTTPS']       = 'off';
        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['REQUEST_URI'] = '/foo.html';

        $url = new Helper\ServerUrl();
        $this->assertEquals('http://example.com/foo.html', $url->__invoke(true));
    }

    public function testServerUrlWithInteger(): void
    {
        $_SERVER['HTTPS']     = 'off';
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['REQUEST_URI'] = '/foo.html';

        $url = new Helper\ServerUrl();
        $this->assertEquals('http://example.com', $url->__invoke(1337));
    }

    public function testServerUrlWithObject(): void
    {
        $_SERVER['HTTPS']     = 'off';
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['REQUEST_URI'] = '/foo.html';

        $url = new Helper\ServerUrl();
        $this->assertEquals('http://example.com', $url->__invoke(new stdClass()));
    }

    /**
     * @group Laminas-9919
     */
    public function testServerUrlWithScheme(): void
    {
        $_SERVER['HTTP_SCHEME'] = 'https';
        $_SERVER['HTTP_HOST'] = 'example.com';
        $url = new Helper\ServerUrl();
        $this->assertEquals('https://example.com', $url->__invoke());
    }

    /**
     * @group Laminas-9919
     */
    public function testServerUrlWithPort(): void
    {
        $_SERVER['SERVER_PORT'] = 443;
        $_SERVER['HTTP_HOST'] = 'example.com';
        $url = new Helper\ServerUrl();
        $this->assertEquals('https://example.com', $url->__invoke());
    }

    /**
     * @group Laminas-508
     */
    public function testServerUrlWithProxy(): void
    {
        $_SERVER['HTTP_HOST'] = 'proxyserver.com';
        $_SERVER['HTTP_X_FORWARDED_HOST'] = 'www.firsthost.org';
        $url = new Helper\ServerUrl();
        $url->setUseProxy(true);
        $this->assertEquals('http://www.firsthost.org', $url->__invoke());
    }

    /**
     * @group Laminas-508
     */
    public function testServerUrlWithMultipleProxies(): void
    {
        $_SERVER['HTTP_HOST'] = 'proxyserver.com';
        $_SERVER['HTTP_X_FORWARDED_HOST'] = 'www.firsthost.org, www.secondhost.org';
        $url = new Helper\ServerUrl();
        $url->setUseProxy(true);
        $this->assertEquals('http://www.secondhost.org', $url->__invoke());
    }

    public function testDoesNotUseProxyByDefault(): void
    {
        $_SERVER['HTTP_HOST'] = 'proxyserver.com';
        $_SERVER['HTTP_X_FORWARDED_HOST'] = 'www.firsthost.org, www.secondhost.org';
        $url = new Helper\ServerUrl();
        $this->assertEquals('http://proxyserver.com', $url->__invoke());
    }

    public function testCanUseXForwardedPortIfProvided(): void
    {
        $_SERVER['HTTP_HOST'] = 'proxyserver.com';
        $_SERVER['HTTP_X_FORWARDED_HOST'] = 'www.firsthost.org, www.secondhost.org';
        $_SERVER['HTTP_X_FORWARDED_PORT'] = '8888';
        $url = new Helper\ServerUrl();
        $url->setUseProxy(true);
        $this->assertEquals('http://www.secondhost.org:8888', $url->__invoke());
    }

    public function testUsesHostHeaderWhenPortForwardingDetected(): void
    {
        $_SERVER['HTTP_HOST'] = 'localhost:10088';
        $_SERVER['SERVER_PORT'] = 10081;
        $url = new Helper\ServerUrl();
        $this->assertEquals('http://localhost:10088', $url->__invoke());
    }
}
