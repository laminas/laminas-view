<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use ArrayObject;
use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\Helper\PartialLoop;
use Laminas\View\HelperPluginManager;
use LaminasTest\View\GenerateServiceManager;
use LaminasTest\View\TestHelpers;
use PHPUnit\Framework\TestCase;
use stdClass;
use TypeError;

use function var_export;

final class PartialLoopTest extends TestCase
{
    private PartialLoop $helper;

    protected function setUp(): void
    {
        $container = GenerateServiceManager::withConfig([
            'view_manager' => [
                'template_path_stack' => [
                    __DIR__ . '/partial-loop-templates',
                ],
            ],
        ]);

        $helpers      = $container->get(HelperPluginManager::class);
        $this->helper = $helpers->get(PartialLoop::class);
    }

    public function testPartialLoopIteratesOverArray(): void
    {
        $data = [
            ['message' => 'foo'],
            ['message' => 'bar'],
            ['message' => 'baz'],
            ['message' => 'bat'],
        ];

        $result = $this->helper->__invoke('basic-loop.phtml', $data);

        self::assertSame('foobarbazbat', $result);
        self::assertSame(4, $this->helper->getPartialCounter());
    }

    public function testPartialLoopIteratesOverTraversable(): void
    {
        $data = [
            ['message' => 'foo'],
            ['message' => 'bar'],
            ['message' => 'baz'],
            ['message' => 'bat'],
        ];

        $result = $this->helper->__invoke('basic-loop.phtml', new ArrayObject($data));
        self::assertSame('foobarbazbat', $result);
        self::assertSame(4, $this->helper->getPartialCounter());
    }

    public function testPartialLoopIteratesOverNestedIterable(): void
    {
        $rIterator = new TestAsset\PartialLoopRecursiveIterator();
        for ($i = 0; $i < 5; ++$i) {
            $data = [
                'message' => 'foo' . $i,
            ];
            $rIterator->addItem(new TestAsset\PartialLoopIterator($data));
        }

        $result = $this->helper->__invoke('basic-loop.phtml', $rIterator);
        foreach ($rIterator as $item) {
            foreach ($item as $value) {
                self::assertIsString($value);
                self::assertStringContainsString($value, $result, var_export($value, true));
            }
        }
    }

    public function testPartialLoopThrowsExceptionWithBadIterator(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('PartialLoop helper requires iterable data');
        $this->helper->__invoke('basic-loop.phtml', new stdClass());
    }

    public function testPassingNullDataThrowsException(): void
    {
        $this->expectException(TypeError::class);
        /** @psalm-suppress NullArgument */
        $this->helper->__invoke('basic-loop.phtml', null);
    }

    public function testPassingNoArgsReturnsHelperInstance(): void
    {
        $test = $this->helper->__invoke();
        self::assertSame($this->helper, $test);
    }

    public function testShouldAllowIteratingOverObjectsImplementingToArrayWithDeprecation(): void
    {
        $data = [
            ['message' => 'foo'],
            ['message' => 'bar'],
            ['message' => 'baz'],
            ['message' => 'bat'],
        ];
        $o    = new TestAsset\PartialLoopToArrayImplementor($data);

        $result = TestHelpers::expectDeprecationWithMessage(
            'Non-iterable objects implementing a `toArray`',
            fn (): string => $this->helper->__invoke('basic-loop.phtml', $o),
        );

        self::assertSame('foobarbazbat', $result);
    }

    public function testShouldNotCastToArrayIfObjectIsTraversable(): void
    {
        $data = [
            new TestAsset\IteratorWithToArrayTestContainer(['message' => 'foo']),
            new TestAsset\IteratorWithToArrayTestContainer(['message' => 'bar']),
            new TestAsset\IteratorWithToArrayTestContainer(['message' => 'baz']),
            new TestAsset\IteratorWithToArrayTestContainer(['message' => 'bat']),
        ];
        $o    = new TestAsset\PartialLoopIteratorWithToArray($data);

        $this->helper->setObjectKey('object');

        $result = $this->helper->__invoke('object-values.phtml', $o);

        self::assertSame('foobarbazbat', $result);
    }

    public function testEmptyArrayPassedToPartialLoopShouldNotThrowException(): void
    {
        self::assertSame('', $this->helper->__invoke('basic-loop.phtml', []));
        self::assertEquals(0, $this->helper->getPartialCounter());
    }

    public function testPartialLoopIncrementsPartialCounter(): void
    {
        $data = [
            ['message' => 'foo'],
            ['message' => 'bar'],
            ['message' => 'baz'],
            ['message' => 'bat'],
        ];

        $this->helper->__invoke('basic-loop.phtml', $data);
        self::assertEquals(4, $this->helper->getPartialCounter());
    }

    public function testPartialLoopPartialCounterResets(): void
    {
        $data = [
            ['message' => 'foo'],
            ['message' => 'bar'],
            ['message' => 'baz'],
            ['message' => 'bat'],
        ];

        $this->helper->__invoke('basic-loop.phtml', $data);
        self::assertEquals(4, $this->helper->getPartialCounter());

        $this->helper->__invoke('basic-loop.phtml', $data);
        self::assertEquals(4, $this->helper->getPartialCounter());
    }

    public function testShouldNotConvertToArrayRecursivelyIfModelIsTraversable(): void
    {
        $rIterator = new TestAsset\PartialLoopRecursiveIterator();
        for ($i = 0; $i < 5; ++$i) {
            $data = [
                'message' => 'foo' . $i,
            ];
            $rIterator->addItem(new TestAsset\PartialLoopIterator($data));
        }

        $this->helper->setObjectKey('object');

        $result = $this->helper->__invoke('nested-iterable.phtml', $rIterator);

        self::assertSame('foo0foo1foo2foo3foo4', $result);
    }

    public function testNestedCallsShouldNotOverrideObjectKey(): void
    {
        $data = [];
        for ($i = 0; $i < 3; $i++) {
            $obj            = new stdClass();
            $obj->objectKey = 'newKey';
            $obj->message   = 'bar' . $i;
            $obj->data      = [
                $obj,
            ];
            $data[]         = $obj;
        }

        $this->helper->setObjectKey('object');
        $result = $this->helper->__invoke('outer-loop.phtml', $data);

        self::assertSame(
            'bar0bar0bar1bar1bar2bar2',
            $result,
        );
    }

    public function testNestedPartialLoopsNestedArray(): void
    {
        $object            = new stdClass();
        $object->objectKey = 'newKey';
        $object->message   = 'bar';
        $object->data      = [
            (object) ['message' => 'baz'],
            (object) ['message' => 'bat'],
        ];

        $this->helper->setObjectKey('object');
        $result = $this->helper->__invoke('outer-loop.phtml', [$object]);

        self::assertSame('barbazbat', $result);
    }

    public function testPartialLoopWithInvalidValuesWillRaiseException(): void
    {
        $this->expectException(TypeError::class);
        /** @psalm-suppress InvalidArgument */
        $this->helper->__invoke('basic-loop.phtml', 'foo');
    }

    public function testPartialLoopWithInvalidObjectValuesWillRaiseException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('PartialLoop helper requires iterable data, stdClass given');

        $this->helper->__invoke('basic-loop.phtml', new stdClass());
    }

    public function testObservableStateIsResetWhenRequired(): void
    {
        $data = [
            ['message' => 'foo'],
            ['message' => 'bar'],
            ['message' => 'baz'],
            ['message' => 'bat'],
        ];

        $this->helper->setObjectKey('foo');
        $this->helper->__invoke('basic-loop.phtml', $data);

        $this->helper->resetState();

        self::assertSame(0, $this->helper->getPartialCounter());
        self::assertNull($this->helper->getObjectKey());
    }
}
