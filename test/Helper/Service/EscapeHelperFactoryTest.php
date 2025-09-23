<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\Service;

use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\Helper\EscapeCss;
use Laminas\View\Helper\Service\EscapeHelperFactory;
use LaminasTest\View\TestAsset\InMemoryContainer;
use PHPUnit\Framework\TestCase;

final class EscapeHelperFactoryTest extends TestCase
{
    public function testExceptionThrownForInvalidServiceRequest(): void
    {
        $container = new InMemoryContainer();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Dependencies of type "Kermit" cannot be created by this factory');

        (new EscapeHelperFactory())->__invoke($container, 'Kermit');
    }

    public function testEscaperIsOptional(): void
    {
        $container = new InMemoryContainer();
        $service   = (new EscapeHelperFactory())->__invoke($container, EscapeCss::class);
        self::assertInstanceOf(EscapeCss::class, $service);
    }
}
