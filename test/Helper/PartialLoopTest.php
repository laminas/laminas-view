<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use ArrayObject;
use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\Helper\Partial;
use Laminas\View\Helper\PartialLoop;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\TemplatePathStack;
use LaminasTest\View\TestHelpers;
use PHPUnit\Framework\TestCase;
use stdClass;
use TypeError;

use function var_export;

final class PartialLoopTest extends TestCase
{
    private PartialLoop $helper;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $renderer = new PhpRenderer();
        $resolver = new TemplatePathStack([
            'script_paths' => [
                __DIR__ . '/_files/modules/application/views/scripts',
            ],
        ]);
        $renderer->setResolver($resolver);
        $partial      = new Partial($renderer);
        $this->helper = new PartialLoop($partial);
    }

    public function testPartialLoopIteratesOverArray(): void
    {
        $data = [
            ['message' => 'foo'],
            ['message' => 'bar'],
            ['message' => 'baz'],
            ['message' => 'bat'],
        ];

        $result = $this->helper->__invoke('partialLoop.phtml', $data);
        foreach ($data as $item) {
            $string = 'This is an iteration: ' . $item['message'];
            self::assertStringContainsString($string, $result);
        }
    }

    public function testPartialLoopIteratesOverIterator(): void
    {
        $data = [
            ['message' => 'foo'],
            ['message' => 'bar'],
            ['message' => 'baz'],
            ['message' => 'bat'],
        ];
        $o    = new TestAsset\PartialLoopIterator($data);

        $result = $this->helper->__invoke('partialLoop.phtml', $o);
        foreach ($data as $item) {
            $string = 'This is an iteration: ' . $item['message'];
            self::assertStringContainsString($string, $result);
        }
    }

    public function testPartialLoopIteratesOverRecursiveIterator(): void
    {
        $rIterator = new TestAsset\PartialLoopRecursiveIterator();
        for ($i = 0; $i < 5; ++$i) {
            $data = [
                'message' => 'foo' . $i,
            ];
            $rIterator->addItem(new TestAsset\PartialLoopIterator($data));
        }

        $result = $this->helper->__invoke('partialLoop.phtml', $rIterator);
        foreach ($rIterator as $item) {
            foreach ($item as $value) {
                self::assertStringContainsString($value, $result, var_export($value, true));
            }
        }
    }

    public function testPartialLoopThrowsExceptionWithBadIterator(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('PartialLoop helper requires iterable data');
        $this->helper->__invoke('partialLoop.phtml', new TestAsset\PartialLoopBogusIterator());
    }

    public function testPassingNullDataThrowsException(): void
    {
        $this->expectException(TypeError::class);
        /** @psalm-suppress NullArgument */
        $this->helper->__invoke('partialLoop.phtml', null);
    }

    public function testPassingNoArgsReturnsHelperInstance(): void
    {
        $test = $this->helper->__invoke();
        self::assertSame($this->helper, $test);
    }

    public function testShouldAllowIteratingOverTraversableObjects(): void
    {
        $data = [
            ['message' => 'foo'],
            ['message' => 'bar'],
            ['message' => 'baz'],
            ['message' => 'bat'],
        ];
        $o    = new ArrayObject($data);

        $result = $this->helper->__invoke('partialLoop.phtml', $o);
        foreach ($data as $item) {
            $string = 'This is an iteration: ' . $item['message'];
            self::assertStringContainsString($string, $result);
        }
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
            fn (): string => $this->helper->__invoke('partialLoop.phtml', $o),
        );

        foreach ($data as $item) {
            $string = 'This is an iteration: ' . $item['message'];
            self::assertStringContainsString($string, $result, $result);
        }
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

        $this->helper->setObjectKey('obj');

        $result = $this->helper->__invoke('partialLoopObject.phtml', $o);
        foreach ($data as $item) {
            $string = 'This is an iteration: ' . $item->message;
            self::assertStringContainsString($string, $result, $result);
        }
    }

    public function testEmptyArrayPassedToPartialLoopShouldNotThrowException(): void
    {
        self::assertSame('', $this->helper->__invoke('partialLoop.phtml', []));
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

        $this->helper->__invoke('partialLoopCouter.phtml', $data);
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

        $this->helper->__invoke('partialLoopCouter.phtml', $data);
        self::assertEquals(4, $this->helper->getPartialCounter());

        $this->helper->__invoke('partialLoopCouter.phtml', $data);
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

        $this->helper->setObjectKey('obj');

        $result = $this->helper->__invoke('partialLoopShouldNotConvertToArrayRecursively.phtml', $rIterator);

        foreach ($rIterator as $item) {
            foreach ($item as $key => $value) {
                self::assertStringContainsString('This is an iteration: ' . $value, $result, var_export($value, true));
            }
        }
    }

    public function testNestedCallsShouldNotOverrideObjectKey(): void
    {
        $data = [];
        for ($i = 0; $i < 3; $i++) {
            $obj            = new stdClass();
            $obj->helper    = $this->helper;
            $obj->objectKey = "foo" . $i;
            $obj->message   = "bar";
            $obj->data      = [
                $obj,
            ];
            $data[]         = $obj;
        }

        $this->helper->setObjectKey('obj');
        $result = $this->helper->__invoke('partialLoopParentObject.phtml', $data);

        foreach ($data as $item) {
            $string = 'This is an iteration with objectKey: ' . $item->objectKey;
            self::assertStringContainsString($string, $result, $result);
        }
    }

    public function testNestedPartialLoopsNestedArray(): void
    {
        $data = [
            [
                'obj' => [
                    'helper'  => $this->helper,
                    'message' => 'foo1',
                    'data'    => [
                        [
                            'message' => 'foo2',
                        ],
                    ],
                ],
            ],
        ];

        $result = $this->helper->__invoke('partialLoopParentObject.phtml', $data);
        self::assertStringContainsString('foo1', $result, $result);
        self::assertStringContainsString('foo2', $result, $result);
    }

    public function testPartialLoopWithInvalidValuesWillRaiseException(): void
    {
        $this->expectException(TypeError::class);
        /** @psalm-suppress InvalidArgument */
        $this->helper->__invoke('partialLoopParentObject.phtml', 'foo');
    }

    public function testPartialLoopWithInvalidObjectValuesWillRaiseException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('PartialLoop helper requires iterable data, stdClass given');

        $this->helper->__invoke('partialLoopParentObject.phtml', new stdClass());
    }

    public function testPartialLoopIteratesOverArrayInLoopMethod(): void
    {
        $data = [
            ['message' => 'foo'],
            ['message' => 'bar'],
            ['message' => 'baz'],
            ['message' => 'bat'],
        ];

        $result = $this->helper->__invoke('partialLoop.phtml', $data);
        foreach ($data as $item) {
            $string = 'This is an iteration: ' . $item['message'];
            self::assertStringContainsString($string, $result);
        }
    }

    public function testPartialLoopIteratesOverIteratorInLoopMethod(): void
    {
        $data = [
            ['message' => 'foo'],
            ['message' => 'bar'],
            ['message' => 'baz'],
            ['message' => 'bat'],
        ];
        $o    = new TestAsset\PartialLoopIterator($data);

        $result = $this->helper->__invoke('partialLoop.phtml', $o);
        foreach ($data as $item) {
            $string = 'This is an iteration: ' . $item['message'];
            self::assertStringContainsString($string, $result);
        }
    }

    public function testPartialLoopIteratesOverRecursiveIteratorInLoopMethod(): void
    {
        $rIterator = new TestAsset\PartialLoopRecursiveIterator();
        for ($i = 0; $i < 5; ++$i) {
            $data = [
                'message' => 'foo' . $i,
            ];
            $rIterator->addItem(new TestAsset\PartialLoopIterator($data));
        }

        $result = $this->helper->__invoke('partialLoop.phtml', $rIterator);
        foreach ($rIterator as $item) {
            foreach ($item as $key => $value) {
                self::assertStringContainsString($value, $result, var_export($value, true));
            }
        }
    }

    public function testPartialLoopThrowsExceptionWithBadIteratorInLoopMethod(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('PartialLoop helper requires iterable data');
        $this->helper->__invoke('partialLoop.phtml', new TestAsset\PartialLoopBogusIterator());
    }

    public function testShouldAllowIteratingOverTraversableObjectsInLoopMethod(): void
    {
        $data = [
            ['message' => 'foo'],
            ['message' => 'bar'],
            ['message' => 'baz'],
            ['message' => 'bat'],
        ];
        $o    = new ArrayObject($data);

        $result = $this->helper->__invoke('partialLoop.phtml', $o);
        foreach ($data as $item) {
            $string = 'This is an iteration: ' . $item['message'];
            self::assertStringContainsString($string, $result);
        }
    }

    public function testShouldAllowIteratingOverObjectsImplementingToArrayInLoopMethod(): void
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
            fn (): string => $this->helper->__invoke('partialLoop.phtml', $o),
        );

        foreach ($data as $item) {
            $string = 'This is an iteration: ' . $item['message'];
            self::assertStringContainsString($string, $result, $result);
        }
    }

    public function testShouldNotCastToArrayIfObjectIsTraversableInLoopMethod(): void
    {
        $data = [
            new TestAsset\IteratorWithToArrayTestContainer(['message' => 'foo']),
            new TestAsset\IteratorWithToArrayTestContainer(['message' => 'bar']),
            new TestAsset\IteratorWithToArrayTestContainer(['message' => 'baz']),
            new TestAsset\IteratorWithToArrayTestContainer(['message' => 'bat']),
        ];
        $o    = new TestAsset\PartialLoopIteratorWithToArray($data);

        $this->helper->setObjectKey('obj');

        $result = $this->helper->__invoke('partialLoopObject.phtml', $o);
        foreach ($data as $item) {
            $string = 'This is an iteration: ' . $item->message;
            self::assertStringContainsString($string, $result, $result);
        }
    }

    public function testEmptyArrayPassedToPartialLoopShouldNotThrowExceptionInLoopMethod(): void
    {
        $this->helper->__invoke('partialLoop.phtml', []);
        self::assertEquals(0, $this->helper->getPartialCounter());
    }

    public function testPartialLoopIncrementsPartialCounterInLoopMethod(): void
    {
        $data = [
            ['message' => 'foo'],
            ['message' => 'bar'],
            ['message' => 'baz'],
            ['message' => 'bat'],
        ];

        $this->helper->__invoke('partialLoopCouter.phtml', $data);
        self::assertEquals(4, $this->helper->getPartialCounter());
    }

    public function testPartialLoopPartialCounterResetsInLoopMethod(): void
    {
        $data = [
            ['message' => 'foo'],
            ['message' => 'bar'],
            ['message' => 'baz'],
            ['message' => 'bat'],
        ];

        $this->helper->__invoke('partialLoopCouter.phtml', $data);
        self::assertEquals(4, $this->helper->getPartialCounter());

        $this->helper->__invoke('partialLoopCouter.phtml', $data);
        self::assertEquals(4, $this->helper->getPartialCounter());
    }

    public function testShouldNotConvertToArrayRecursivelyIfModelIsTraversableInLoopMethod(): void
    {
        $rIterator = new TestAsset\PartialLoopRecursiveIterator();
        for ($i = 0; $i < 5; ++$i) {
            $data = [
                'message' => 'foo' . $i,
            ];
            $rIterator->addItem(new TestAsset\PartialLoopIterator($data));
        }

        $this->helper->setObjectKey('obj');

        $result = $this->helper->__invoke('partialLoopShouldNotConvertToArrayRecursively.phtml', $rIterator);

        foreach ($rIterator as $item) {
            foreach ($item as $value) {
                self::assertStringContainsString('This is an iteration: ' . $value, $result, var_export($value, true));
            }
        }
    }

    public function testNestedCallsShouldNotOverrideObjectKeyInLoopMethod(): void
    {
        $data = [];
        for ($i = 0; $i < 3; $i++) {
            $obj            = new stdClass();
            $obj->helper    = $this->helper;
            $obj->objectKey = "foo" . $i;
            $obj->message   = "bar";
            $obj->data      = [
                $obj,
            ];
            $data[]         = $obj;
        }

        $this->helper->setObjectKey('obj');
        $result = $this->helper->__invoke('partialLoopParentObject.phtml', $data);

        foreach ($data as $item) {
            $string = 'This is an iteration with objectKey: ' . $item->objectKey;
            self::assertStringContainsString($string, $result, $result);
        }
    }

    public function testNestedPartialLoopsNestedArrayInLoopMethod(): void
    {
        $data = [
            [
                'obj' => [
                    'helper'  => $this->helper,
                    'message' => 'foo1',
                    'data'    => [
                        [
                            'message' => 'foo2',
                        ],
                    ],
                ],
            ],
        ];

        $result = $this->helper->__invoke('partialLoopParentObject.phtml', $data);
        self::assertStringContainsString('foo1', $result, $result);
        self::assertStringContainsString('foo2', $result, $result);
    }

    public function testPartialLoopWithInvalidValuesWillRaiseExceptionInLoopMethod(): void
    {
        $this->expectException(TypeError::class);
        $this->helper->__invoke('partialLoopParentObject.phtml', 'foo');
    }

    public function testPartialLoopWithInvalidObjectValuesWillRaiseExceptionInLoopMethod(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('PartialLoop helper requires iterable data, stdClass given');

        $this->helper->__invoke('partialLoopParentObject.phtml', new stdClass());
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
        $this->helper->__invoke('partialLoopCouter.phtml', $data);

        $this->helper->resetState();

        self::assertSame(0, $this->helper->getPartialCounter());
        self::assertNull($this->helper->getObjectKey());
    }
}
