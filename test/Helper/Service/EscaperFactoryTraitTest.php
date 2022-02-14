<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\Service;

use Laminas\Escaper\Escaper;
use Laminas\View\Exception\RuntimeException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class EscaperFactoryTraitTest extends TestCase
{
    /** @var MockObject&ContainerInterface */
    private ContainerInterface $container;
    private StubEscaperFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = $this->createMock(ContainerInterface::class);
        $this->factory   = new StubEscaperFactory();
    }

    public function testTheEscaperWillBeCreatedWithTheDefaultEncodingWhenNoConfigurationIsFound(): void
    {
        $this->container->expects(self::exactly(2))
            ->method('has')
            ->willReturn(false);

        $escaper = $this->factory->getEscaper($this->container);
        self::assertEquals('utf-8', $escaper->getEncoding());
    }

    public function testThatEncodingWillNotBeConsultedWhenTheEscaperIsAlreadyAvailableInTheContainer(): void
    {
        $encoding = 'iso-8859-1';
        $escaper  = new Escaper($encoding);

        $this->container->expects(self::once())
            ->method('has')
            ->with(Escaper::class)
            ->willReturn(true);

        $this->container->expects(self::once())
            ->method('get')
            ->with(Escaper::class)
            ->willReturn($escaper);

        $retrieved = $this->factory->getEscaper($this->container);

        self::assertSame($escaper, $retrieved);
        self::assertEquals($encoding, $retrieved->getEncoding());
    }

    /** @param array<array-key, mixed> $config */
    private function containerWillHaveConfig(array $config): void
    {
        $this->container->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive([Escaper::class], ['config'])
            ->willReturnOnConsecutiveCalls(false, true);

        $this->container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn($config);
    }

    public function testAnExceptionIsThrownWhenEncodingIsSetToANonString(): void
    {
        $this->containerWillHaveConfig([
            'view_manager' => [
                'encoding' => ['not a string'],
            ],
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('View encoding should be a string. Received "array"');
        $this->factory->getEscaper($this->container);
    }

    public function testThatAnExceptionWillBeThrownWhenViewConfigurationIsNotAnArray(): void
    {
        $this->containerWillHaveConfig([
            'view_manager' => 'Invalid - not an array',
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid view configuration.');
        $this->factory->getEscaper($this->container);
    }

    public function testThatTheEscaperWillBeConfiguredWithTheDesiredEncoding(): void
    {
        $this->containerWillHaveConfig([
            'view_manager' => [
                'encoding' => 'iso-8859-1',
            ],
        ]);

        $escaper = $this->factory->getEscaper($this->container);
        self::assertEquals('iso-8859-1', $escaper->getEncoding());
    }
}
