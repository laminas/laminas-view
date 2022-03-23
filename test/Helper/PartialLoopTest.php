<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use ArrayObject;
use Laminas\View\Exception;
use Laminas\View\Helper\PartialLoop;
use Laminas\View\Renderer\PhpRenderer as View;
use PHPUnit\Framework\TestCase;
use stdClass;

use function var_export;

class PartialLoopTest extends TestCase
{
    /** @var PartialLoop */
    public $helper;

    /** @var string */
    public $basePath;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->basePath = __DIR__ . '/_files/modules';
        $this->helper   = new PartialLoop();
    }

    public function testPartialLoopIteratesOverArray(): void
    {
        $data = [
            ['message' => 'foo'],
            ['message' => 'bar'],
            ['message' => 'baz'],
            ['message' => 'bat'],
        ];

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);

        $result = $this->helper->__invoke('partialLoop.phtml', $data);
        foreach ($data as $item) {
            $string = 'This is an iteration: ' . $item['message'];
            $this->assertStringContainsString($string, $result);
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
        $o    = new TestAsset\IteratorTest($data);

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);

        $result = $this->helper->__invoke('partialLoop.phtml', $o);
        foreach ($data as $item) {
            $string = 'This is an iteration: ' . $item['message'];
            $this->assertStringContainsString($string, $result);
        }
    }

    public function testPartialLoopIteratesOverRecursiveIterator(): void
    {
        $rIterator = new TestAsset\RecursiveIteratorTest();
        for ($i = 0; $i < 5; ++$i) {
            $data = [
                'message' => 'foo' . $i,
            ];
            $rIterator->addItem(new TestAsset\IteratorTest($data));
        }

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);

        $result = $this->helper->__invoke('partialLoop.phtml', $rIterator);
        foreach ($rIterator as $item) {
            foreach ($item as $key => $value) {
                $this->assertStringContainsString($value, $result, var_export($value, true));
            }
        }
    }

    public function testPartialLoopThrowsExceptionWithBadIterator(): void
    {
        $data = [
            ['message' => 'foo'],
            ['message' => 'bar'],
            ['message' => 'baz'],
            ['message' => 'bat'],
        ];
        $o    = new TestAsset\BogusIteratorTest($data);

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);

        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('PartialLoop helper requires iterable data');
        $this->helper->__invoke('partialLoop.phtml', $o);
    }

    public function testPassingNullDataThrowsException(): void
    {
        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);

        $this->expectException(Exception\InvalidArgumentException::class);
        $this->helper->__invoke('partialLoop.phtml', null);
    }

    public function testPassingNoArgsReturnsHelperInstance(): void
    {
        $test = $this->helper->__invoke();
        $this->assertSame($this->helper, $test);
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

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);

        $result = $this->helper->__invoke('partialLoop.phtml', $o);
        foreach ($data as $item) {
            $string = 'This is an iteration: ' . $item['message'];
            $this->assertStringContainsString($string, $result);
        }
    }

    public function testShouldAllowIteratingOverObjectsImplementingToArray(): void
    {
        $data = [
            ['message' => 'foo'],
            ['message' => 'bar'],
            ['message' => 'baz'],
            ['message' => 'bat'],
        ];
        $o    = new TestAsset\ToArrayTest($data);

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);

        $result = $this->helper->__invoke('partialLoop.phtml', $o);
        foreach ($data as $item) {
            $string = 'This is an iteration: ' . $item['message'];
            $this->assertStringContainsString($string, $result, $result);
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
        $o    = new TestAsset\IteratorWithToArrayTest($data);

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);
        $this->helper->setObjectKey('obj');

        $result = $this->helper->__invoke('partialLoopObject.phtml', $o);
        foreach ($data as $item) {
            $string = 'This is an iteration: ' . $item->message;
            $this->assertStringContainsString($string, $result, $result);
        }
    }

    public function testEmptyArrayPassedToPartialLoopShouldNotThrowException(): void
    {
        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);

        ($this->helper)('partialLoop.phtml', []);
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

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);

        $this->helper->__invoke('partialLoopCouter.phtml', $data);
        $this->assertEquals(4, $this->helper->getPartialCounter());
    }

    public function testPartialLoopPartialCounterResets(): void
    {
        $data = [
            ['message' => 'foo'],
            ['message' => 'bar'],
            ['message' => 'baz'],
            ['message' => 'bat'],
        ];

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);

        $this->helper->__invoke('partialLoopCouter.phtml', $data);
        $this->assertEquals(4, $this->helper->getPartialCounter());

        $this->helper->__invoke('partialLoopCouter.phtml', $data);
        $this->assertEquals(4, $this->helper->getPartialCounter());
    }

    public function testShouldNotConvertToArrayRecursivelyIfModelIsTraversable(): void
    {
        $rIterator = new TestAsset\RecursiveIteratorTest();
        for ($i = 0; $i < 5; ++$i) {
            $data = [
                'message' => 'foo' . $i,
            ];
            $rIterator->addItem(new TestAsset\IteratorTest($data));
        }

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);
        $this->helper->setObjectKey('obj');

        $result = $this->helper->__invoke('partialLoopShouldNotConvertToArrayRecursively.phtml', $rIterator);

        foreach ($rIterator as $item) {
            foreach ($item as $key => $value) {
                $this->assertStringContainsString('This is an iteration: ' . $value, $result, var_export($value, true));
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

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);

        $this->helper->setObjectKey('obj');
        $result = $this->helper->__invoke('partialLoopParentObject.phtml', $data);

        foreach ($data as $item) {
            $string = 'This is an iteration with objectKey: ' . $item->objectKey;
            $this->assertStringContainsString($string, $result, $result);
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

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);

        $result = $this->helper->__invoke('partialLoopParentObject.phtml', $data);
        $this->assertStringContainsString('foo1', $result, $result);
        $this->assertStringContainsString('foo2', $result, $result);
    }

    public function testPartialLoopWithInvalidValuesWillRaiseException(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('PartialLoop helper requires iterable data, string given');

        $this->helper->__invoke('partialLoopParentObject.phtml', 'foo');
    }

    public function testPartialLoopWithInvalidObjectValuesWillRaiseException(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
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

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);

        $result = $this->helper->loop('partialLoop.phtml', $data);
        foreach ($data as $item) {
            $string = 'This is an iteration: ' . $item['message'];
            $this->assertStringContainsString($string, $result);
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
        $o    = new TestAsset\IteratorTest($data);

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);

        $result = $this->helper->Loop('partialLoop.phtml', $o);
        foreach ($data as $item) {
            $string = 'This is an iteration: ' . $item['message'];
            $this->assertStringContainsString($string, $result);
        }
    }

    public function testPartialLoopIteratesOverRecursiveIteratorInLoopMethod(): void
    {
        $rIterator = new TestAsset\RecursiveIteratorTest();
        for ($i = 0; $i < 5; ++$i) {
            $data = [
                'message' => 'foo' . $i,
            ];
            $rIterator->addItem(new TestAsset\IteratorTest($data));
        }

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);

        $result = $this->helper->Loop('partialLoop.phtml', $rIterator);
        foreach ($rIterator as $item) {
            foreach ($item as $key => $value) {
                $this->assertStringContainsString($value, $result, var_export($value, true));
            }
        }
    }

    public function testPartialLoopThrowsExceptionWithBadIteratorInLoopMethod(): void
    {
        $data = [
            ['message' => 'foo'],
            ['message' => 'bar'],
            ['message' => 'baz'],
            ['message' => 'bat'],
        ];
        $o    = new TestAsset\BogusIteratorTest($data);

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('PartialLoop helper requires iterable data');
        $this->helper->Loop('partialLoop.phtml', $o);
    }

    public function testPassingNullDataThrowsExceptionInLoopMethod(): void
    {
        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);

        $this->expectException(Exception\InvalidArgumentException::class);
        $this->helper->loop('partialLoop.phtml', null);
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

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);

        $result = $this->helper->loop('partialLoop.phtml', $o);
        foreach ($data as $item) {
            $string = 'This is an iteration: ' . $item['message'];
            $this->assertStringContainsString($string, $result);
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
        $o    = new TestAsset\ToArrayTest($data);

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);

        $result = $this->helper->loop('partialLoop.phtml', $o);
        foreach ($data as $item) {
            $string = 'This is an iteration: ' . $item['message'];
            $this->assertStringContainsString($string, $result, $result);
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
        $o    = new TestAsset\IteratorWithToArrayTest($data);

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);
        $this->helper->setObjectKey('obj');

        $result = $this->helper->loop('partialLoopObject.phtml', $o);
        foreach ($data as $item) {
            $string = 'This is an iteration: ' . $item->message;
            $this->assertStringContainsString($string, $result, $result);
        }
    }

    public function testEmptyArrayPassedToPartialLoopShouldNotThrowExceptionInLoopMethod(): void
    {
        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);

        $this->helper->loop('partialLoop.phtml', []);
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

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);

        $this->helper->loop('partialLoopCouter.phtml', $data);
        $this->assertEquals(4, $this->helper->getPartialCounter());
    }

    public function testPartialLoopPartialCounterResetsInLoopMethod(): void
    {
        $data = [
            ['message' => 'foo'],
            ['message' => 'bar'],
            ['message' => 'baz'],
            ['message' => 'bat'],
        ];

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);

        $this->helper->loop('partialLoopCouter.phtml', $data);
        $this->assertEquals(4, $this->helper->getPartialCounter());

        $this->helper->loop('partialLoopCouter.phtml', $data);
        $this->assertEquals(4, $this->helper->getPartialCounter());
    }

    public function testShouldNotConvertToArrayRecursivelyIfModelIsTraversableInLoopMethod(): void
    {
        $rIterator = new TestAsset\RecursiveIteratorTest();
        for ($i = 0; $i < 5; ++$i) {
            $data = [
                'message' => 'foo' . $i,
            ];
            $rIterator->addItem(new TestAsset\IteratorTest($data));
        }

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);
        $this->helper->setObjectKey('obj');

        $result = $this->helper->loop('partialLoopShouldNotConvertToArrayRecursively.phtml', $rIterator);

        foreach ($rIterator as $item) {
            foreach ($item as $key => $value) {
                $this->assertStringContainsString('This is an iteration: ' . $value, $result, var_export($value, true));
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

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);

        $this->helper->setObjectKey('obj');
        $result = $this->helper->loop('partialLoopParentObject.phtml', $data);

        foreach ($data as $item) {
            $string = 'This is an iteration with objectKey: ' . $item->objectKey;
            $this->assertStringContainsString($string, $result, $result);
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

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);

        $result = $this->helper->loop('partialLoopParentObject.phtml', $data);
        $this->assertStringContainsString('foo1', $result, $result);
        $this->assertStringContainsString('foo2', $result, $result);
    }

    public function testPartialLoopWithInvalidValuesWillRaiseExceptionInLoopMethod(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('PartialLoop helper requires iterable data, string given');

        $this->helper->loop('partialLoopParentObject.phtml', 'foo');
    }

    public function testPartialLoopWithInvalidObjectValuesWillRaiseExceptionInLoopMethod(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('PartialLoop helper requires iterable data, stdClass given');

        $this->helper->loop('partialLoopParentObject.phtml', new stdClass());
    }
}
