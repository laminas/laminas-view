<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\View\Helper\ServerUrl;
use PHPUnit\Framework\TestCase;
use stdClass;

class ServerUrlTest extends TestCase
{
    /**
     * Back up of $_SERVER
     *
     * @var array
     */
    protected $serverBackup;

    protected function setUp(): void
    {
        $this->serverBackup = $_SERVER;
        unset($_SERVER['HTTPS']);
        unset($_SERVER['SERVER_PORT']);
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->serverBackup;
    }

    public function testConfiguredServerUrlWillBeUsed(): void
    {
        $helper = new ServerUrl('https://example.com');
        self::assertEquals('https://example.com', $helper());
    }

    public function testPathWillBeAppendedToConfiguredServerUrl(): void
    {
        $helper = new ServerUrl('https://example.com');
        self::assertEquals('https://example.com/foo', $helper('/foo'));
    }

    public function testRequestUriWillBeAppendedWhenArgumentIsTrue(): void
    {
        $helper                 = new ServerUrl('https://example.com');
        $_SERVER['REQUEST_URI'] = '/baz';
        self::assertEquals('https://example.com/baz', $helper(true));
    }

    /** @deprecated  */
    public function testConstructorWithOnlyHost(): void
    {
        $_SERVER['HTTP_HOST'] = 'example.com';

        $url = new ServerUrl();
        $this->assertEquals('http://example.com', $url->__invoke());
    }

    /** @deprecated  */
    public function testConstructorWithOnlyHostIncludingPort(): void
    {
        $_SERVER['HTTP_HOST'] = 'example.com:8000';

        $url = new ServerUrl();
        $this->assertEquals('http://example.com:8000', $url->__invoke());
    }

    /** @deprecated  */
    public function testConstructorWithHostAndHttpsOn(): void
    {
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['HTTPS']     = 'on';

        $url = new ServerUrl();
        $this->assertEquals('https://example.com', $url->__invoke());
    }

    /** @deprecated  */
    public function testConstructorWithHostAndHttpsTrue(): void
    {
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['HTTPS']     = true;

        $url = new ServerUrl();
        $this->assertEquals('https://example.com', $url->__invoke());
    }

    /** @deprecated  */
    public function testConstructorWithHostIncludingPortAndHttpsTrue(): void
    {
        $_SERVER['HTTP_HOST'] = 'example.com:8181';
        $_SERVER['HTTPS']     = true;

        $url = new ServerUrl();
        $this->assertEquals('https://example.com:8181', $url->__invoke());
    }

    /** @deprecated  */
    public function testConstructorWithHostReversedProxyHttpsTrue(): void
    {
        $_SERVER['HTTP_HOST']              = 'example.com';
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
        $_SERVER['SERVER_PORT']            = 80;

        $url = new ServerUrl();
        $this->assertEquals('https://example.com', $url->__invoke());
    }

    /** @deprecated  */
    public function testConstructorWithHttpHostIncludingPortAndPortSet(): void
    {
        $_SERVER['HTTP_HOST']   = 'example.com:8181';
        $_SERVER['SERVER_PORT'] = 8181;

        $url = new ServerUrl();
        $this->assertEquals('http://example.com:8181', $url->__invoke());
    }

    /** @deprecated  */
    public function testConstructorWithHttpHostAndServerNameAndPortSet(): void
    {
        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = 8080;

        $url = new ServerUrl();
        $this->assertEquals('http://example.com:8080', $url->__invoke());
    }

    /** @deprecated  */
    public function testConstructorWithNoHttpHostButServerNameAndPortSet(): void
    {
        unset($_SERVER['HTTP_HOST']);
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = 8080;

        $url = new ServerUrl();
        $this->assertEquals('http://example.org:8080', $url->__invoke());
    }

    /** @deprecated  */
    public function testServerUrlWithTrueParam(): void
    {
        $_SERVER['HTTPS']       = 'off';
        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['REQUEST_URI'] = '/foo.html';

        $url = new ServerUrl();
        $this->assertEquals('http://example.com/foo.html', $url->__invoke(true));
    }

    /** @deprecated  */
    public function testServerUrlWithInteger(): void
    {
        $_SERVER['HTTPS']       = 'off';
        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['REQUEST_URI'] = '/foo.html';

        $url = new ServerUrl();
        $this->assertEquals('http://example.com', $url->__invoke(1337));
    }

    /** @deprecated  */
    public function testServerUrlWithObject(): void
    {
        $_SERVER['HTTPS']       = 'off';
        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['REQUEST_URI'] = '/foo.html';

        $url = new ServerUrl();
        $this->assertEquals('http://example.com', $url->__invoke(new stdClass()));
    }

    /** @deprecated  */
    public function testServerUrlWithScheme(): void
    {
        $_SERVER['HTTP_SCHEME'] = 'https';
        $_SERVER['HTTP_HOST']   = 'example.com';
        $url                    = new ServerUrl();
        $this->assertEquals('https://example.com', $url->__invoke());
    }

    /** @deprecated  */
    public function testServerUrlWithPort(): void
    {
        $_SERVER['SERVER_PORT'] = 443;
        $_SERVER['HTTP_HOST']   = 'example.com';
        $url                    = new ServerUrl();
        $this->assertEquals('https://example.com', $url->__invoke());
    }

    /** @deprecated  */
    public function testServerUrlWithProxy(): void
    {
        $_SERVER['HTTP_HOST']             = 'proxyserver.com';
        $_SERVER['HTTP_X_FORWARDED_HOST'] = 'www.firsthost.org';
        $url                              = new ServerUrl();
        $url->setUseProxy(true);
        $this->assertEquals('http://www.firsthost.org', $url->__invoke());
    }

    /** @deprecated  */
    public function testServerUrlWithMultipleProxies(): void
    {
        $_SERVER['HTTP_HOST']             = 'proxyserver.com';
        $_SERVER['HTTP_X_FORWARDED_HOST'] = 'www.firsthost.org, www.secondhost.org';
        $url                              = new ServerUrl();
        $url->setUseProxy(true);
        $this->assertEquals('http://www.secondhost.org', $url->__invoke());
    }

    /** @deprecated  */
    public function testDoesNotUseProxyByDefault(): void
    {
        $_SERVER['HTTP_HOST']             = 'proxyserver.com';
        $_SERVER['HTTP_X_FORWARDED_HOST'] = 'www.firsthost.org, www.secondhost.org';
        $url                              = new ServerUrl();
        $this->assertEquals('http://proxyserver.com', $url->__invoke());
    }

    /** @deprecated  */
    public function testCanUseXForwardedPortIfProvided(): void
    {
        $_SERVER['HTTP_HOST']             = 'proxyserver.com';
        $_SERVER['HTTP_X_FORWARDED_HOST'] = 'www.firsthost.org, www.secondhost.org';
        $_SERVER['HTTP_X_FORWARDED_PORT'] = '8888';
        $url                              = new ServerUrl();
        $url->setUseProxy(true);
        $this->assertEquals('http://www.secondhost.org:8888', $url->__invoke());
    }

    /** @deprecated  */
    public function testUsesHostHeaderWhenPortForwardingDetected(): void
    {
        $_SERVER['HTTP_HOST']   = 'localhost:10088';
        $_SERVER['SERVER_PORT'] = 10081;
        $url                    = new ServerUrl();
        $this->assertEquals('http://localhost:10088', $url->__invoke());
    }
}
