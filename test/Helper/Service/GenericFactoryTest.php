<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\Service;

use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\Helper\InlineScript;
use Laminas\View\Helper\Service\GenericFactory;
use Laminas\View\HelperPluginManager;
use LaminasTest\View\TestAsset\InMemoryContainer;
use PHPUnit\Framework\TestCase;

final class GenericFactoryTest extends TestCase
{
    public function testExceptionThrownForInvalidServiceRequest(): void
    {
        $container = new InMemoryContainer();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Dependencies of type "Kermit" cannot be created by this factory');

        (new GenericFactory())->__invoke($container, 'Kermit');
    }

    public function testEscaperIsOptional(): void
    {
        $container = new InMemoryContainer();
        $container->set(HelperPluginManager::class, new HelperPluginManager(new ServiceManager()));
        $service = (new GenericFactory())->__invoke($container, InlineScript::class);
        self::assertInstanceOf(InlineScript::class, $service);
    }
}
