<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\Service;

use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Exception\RuntimeException;
use Laminas\View\Helper\Service\ServerUrlFactory;
use PHPUnit\Framework\TestCase;

class ServerUrlFactoryTest extends TestCase
{
    /** @var array<array-key, mixed> */
    private array $serverVariables;

    protected function setUp(): void
    {
        parent::setUp();
        $this->serverVariables = $_SERVER;
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->serverVariables;
        parent::tearDown();
    }

    public function testThatWhenThereIsNoConfigurationTheHostUriWillBeDetectedFromGlobals(): void
    {
        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['HTTPS']       = true;
        $_SERVER['SERVER_PORT'] = 443;

        $helper = (new ServerUrlFactory())(new ServiceManager());
        self::assertEquals('https://example.com', $helper->__invoke());
    }

    public function testThatWhenThereIsNoConfigurationDetectedPathQueryAndFragmentWillBeOmitted(): void
    {
        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['HTTPS']       = true;
        $_SERVER['SERVER_PORT'] = 443;
        $_SERVER['REQUEST_URI'] = '/some/#thing?foo=bar';

        $helper = (new ServerUrlFactory())(new ServiceManager());
        self::assertEquals('https://example.com', $helper->__invoke());
    }

    public function testThatConfiguredHostIsPreferred(): void
    {
        $container = new ServiceManager();
        $container->setService('config', [
            'view_helper_config' => [
                'server_url' => 'https://other.example.com',
            ],
        ]);

        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['HTTPS']       = true;
        $_SERVER['SERVER_PORT'] = 443;

        $helper = (new ServerUrlFactory())($container);
        self::assertEquals('https://other.example.com', $helper->__invoke());
    }

    public function testUndetectableEnvironmentAndZeroConfigurationYieldsException(): void
    {
        $_SERVER = [];

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The current host or scheme cannot be detected from the environment');

        (new ServerUrlFactory())(new ServiceManager());
    }

    public function testAnEmptyEnvironmentIsAcceptableWhenConfigurationIsFound(): void
    {
        $_SERVER   = [];
        $container = new ServiceManager();
        $container->setService('config', [
            'view_helper_config' => [
                'server_url' => 'https://other.example.com',
            ],
        ]);
        $helper = (new ServerUrlFactory())($container);
        self::assertEquals('https://other.example.com', $helper->__invoke());
    }
}
