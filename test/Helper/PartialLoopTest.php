<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View\Helper;

use ArrayObject;
use Laminas\View\Exception;
use Laminas\View\Helper\PartialLoop;
use Laminas\View\Renderer\PhpRenderer as View;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Test class for Laminas\View\Helper\PartialLoop.
 *
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
class PartialLoopTest extends TestCase
{
    /**
     * @var PartialLoop
     */
    public $helper;

    /**
     * @var string
     */
    public $basePath;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->basePath = __DIR__ . '/_files/modules';
        $this->helper   = new PartialLoop();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->helper);
    }

    public function testPartialLoopIteratesOverArray()
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

    public function testPartialLoopIteratesOverIterator()
    {
        $data = [
            ['message' => 'foo'],
            ['message' => 'bar'],
            ['message' => 'baz'],
            ['message' => 'bat']
        ];
        $o = new TestAsset\IteratorTest($data);

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);

        $result = $this->helper->__invoke('partialLoop.phtml', $o);
        foreach ($data as $item) {
            $string = 'This is an iteration: ' . $item['message'];
            $this->assertStringContainsString($string, $result);
        }
    }

    public function testPartialLoopIteratesOverRecursiveIterator()
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
                $this->assertStringContainsString($value, $result, var_export($value, 1));
            }
        }
    }

    public function testPartialLoopThrowsExceptionWithBadIterator(): void
    {
        $data = [
            ['message' => 'foo'],
            ['message' => 'bar'],
            ['message' => 'baz'],
            ['message' => 'bat']
        ];
        $o = new TestAsset\BogusIteratorTest($data);

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);

        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('PartialLoop helper requires iterable data');
        $this->helper->__invoke('partialLoop.phtml', $o);
        $this->fail('PartialLoop should only work with arrays and iterators');
    }

    public function testPassingNullDataThrowsException()
    {
        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);

        $this->expectException(Exception\InvalidArgumentException::class);
        $result = $this->helper->__invoke('partialLoop.phtml', null);
    }

    public function testPassingNoArgsReturnsHelperInstance()
    {
        $test = $this->helper->__invoke();
        $this->assertSame($this->helper, $test);
    }

    public function testShouldAllowIteratingOverTraversableObjects()
    {
        $data = [
            ['message' => 'foo'],
            ['message' => 'bar'],
            ['message' => 'baz'],
            ['message' => 'bat']
        ];
        $o = new ArrayObject($data);

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);

        $result = $this->helper->__invoke('partialLoop.phtml', $o);
        foreach ($data as $item) {
            $string = 'This is an iteration: ' . $item['message'];
            $this->assertStringContainsString($string, $result);
        }
    }

    public function testShouldAllowIteratingOverObjectsImplementingToArray()
    {
        $data = [
            ['message' => 'foo'],
            ['message' => 'bar'],
            ['message' => 'baz'],
            ['message' => 'bat']
        ];
        $o = new TestAsset\ToArrayTest($data);

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);

        $result = $this->helper->__invoke('partialLoop.phtml', $o);
        foreach ($data as $item) {
            $string = 'This is an iteration: ' . $item['message'];
            $this->assertStringContainsString($string, $result, $result);
        }
    }

    /**
     * @group Laminas-3350
     * @group Laminas-3352
     */
    public function testShouldNotCastToArrayIfObjectIsTraversable()
    {
        $data = [
            new TestAsset\IteratorWithToArrayTestContainer(['message' => 'foo']),
            new TestAsset\IteratorWithToArrayTestContainer(['message' => 'bar']),
            new TestAsset\IteratorWithToArrayTestContainer(['message' => 'baz']),
            new TestAsset\IteratorWithToArrayTestContainer(['message' => 'bat']),
        ];
        $o = new TestAsset\IteratorWithToArrayTest($data);

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

    /**
     * @group Laminas-3083
     */
    public function testEmptyArrayPassedToPartialLoopShouldNotThrowException(): void
    {
        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);

        ($this->helper)('partialLoop.phtml', []);
        self::assertEquals(0, $this->helper->getPartialCounter());
    }

    /**
     * @group Laminas-2737
     */
    public function testPartialLoopIncrementsPartialCounter()
    {
        $data = [
            ['message' => 'foo'],
            ['message' => 'bar'],
            ['message' => 'baz'],
            ['message' => 'bat']
        ];

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);

        $this->helper->__invoke('partialLoopCouter.phtml', $data);
        $this->assertEquals(4, $this->helper->getPartialCounter());
    }

    /**
     * @group Laminas-5174
     */
    public function testPartialLoopPartialCounterResets()
    {
        $data = [
            ['message' => 'foo'],
            ['message' => 'bar'],
            ['message' => 'baz'],
            ['message' => 'bat']
        ];

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);

        $this->helper->__invoke('partialLoopCouter.phtml', $data);
        $this->assertEquals(4, $this->helper->getPartialCounter());

        $this->helper->__invoke('partialLoopCouter.phtml', $data);
        $this->assertEquals(4, $this->helper->getPartialCounter());
    }

    public function testShouldNotConvertToArrayRecursivelyIfModelIsTraversable()
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
                $this->assertStringContainsString('This is an iteration: ' . $value, $result, var_export($value, 1));
            }
        }
    }

    /**
     * @group 7093
     */
    public function testNestedCallsShouldNotOverrideObjectKey()
    {
        $data = [];
        for ($i = 0; $i < 3; $i++) {
            $obj = new stdClass();
            $obj->helper = $this->helper;
            $obj->objectKey = "foo" . $i;
            $obj->message = "bar";
            $obj->data = [
                $obj
            ];
            $data[] = $obj;
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

    /**
     * @group 7450
     */
    public function testNestedPartialLoopsNestedArray()
    {
        $data = [[
            'obj' => [
                'helper' => $this->helper,
                'message' => 'foo1',
                'data' => [[
                    'message' => 'foo2'
                ]]
            ]
        ]];

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);

        $result = $this->helper->__invoke('partialLoopParentObject.phtml', $data);
        $this->assertStringContainsString('foo1', $result, $result);
        $this->assertStringContainsString('foo2', $result, $result);
    }

    public function testPartialLoopWithInvalidValuesWillRaiseException()
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('PartialLoop helper requires iterable data, string given');

        $this->helper->__invoke('partialLoopParentObject.phtml', 'foo');
    }

    public function testPartialLoopWithInvalidObjectValuesWillRaiseException()
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('PartialLoop helper requires iterable data, stdClass given');

        $this->helper->__invoke('partialLoopParentObject.phtml', new stdClass());
    }

    public function testPartialLoopIteratesOverArrayInLoopMethod()
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

    public function testPartialLoopIteratesOverIteratorInLoopMethod()
    {
        $data = [
            ['message' => 'foo'],
            ['message' => 'bar'],
            ['message' => 'baz'],
            ['message' => 'bat']
        ];
        $o = new TestAsset\IteratorTest($data);

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);

        $result = $this->helper->Loop('partialLoop.phtml', $o);
        foreach ($data as $item) {
            $string = 'This is an iteration: ' . $item['message'];
            $this->assertStringContainsString($string, $result);
        }
    }

    public function testPartialLoopIteratesOverRecursiveIteratorInLoopMethod()
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
                $this->assertStringContainsString($value, $result, var_export($value, 1));
            }
        }
    }

    public function testPartialLoopThrowsExceptionWithBadIteratorInLoopMethod(): void
    {
        $data = [
            ['message' => 'foo'],
            ['message' => 'bar'],
            ['message' => 'baz'],
            ['message' => 'bat']
        ];
        $o = new TestAsset\BogusIteratorTest($data);

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('PartialLoop helper requires iterable data');
        $this->helper->Loop('partialLoop.phtml', $o);
        $this->fail('PartialLoop should only work with arrays and iterators');
    }

    public function testPassingNullDataThrowsExceptionInLoopMethod()
    {
        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);

        $this->expectException(Exception\InvalidArgumentException::class);
        $result = $this->helper->loop('partialLoop.phtml', null);
    }

    public function testShouldAllowIteratingOverTraversableObjectsInLoopMethod()
    {
        $data = [
            ['message' => 'foo'],
            ['message' => 'bar'],
            ['message' => 'baz'],
            ['message' => 'bat']
        ];
        $o = new ArrayObject($data);

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);

        $result = $this->helper->loop('partialLoop.phtml', $o);
        foreach ($data as $item) {
            $string = 'This is an iteration: ' . $item['message'];
            $this->assertStringContainsString($string, $result);
        }
    }

    public function testShouldAllowIteratingOverObjectsImplementingToArrayInLoopMethod()
    {
        $data = [
            ['message' => 'foo'],
            ['message' => 'bar'],
            ['message' => 'baz'],
            ['message' => 'bat']
        ];
        $o = new TestAsset\ToArrayTest($data);

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);

        $result = $this->helper->loop('partialLoop.phtml', $o);
        foreach ($data as $item) {
            $string = 'This is an iteration: ' . $item['message'];
            $this->assertStringContainsString($string, $result, $result);
        }
    }

    /**
     * @group Laminas-3350
     * @group Laminas-3352
     */
    public function testShouldNotCastToArrayIfObjectIsTraversableInLoopMethod()
    {
        $data = [
            new TestAsset\IteratorWithToArrayTestContainer(['message' => 'foo']),
            new TestAsset\IteratorWithToArrayTestContainer(['message' => 'bar']),
            new TestAsset\IteratorWithToArrayTestContainer(['message' => 'baz']),
            new TestAsset\IteratorWithToArrayTestContainer(['message' => 'bat']),
        ];
        $o = new TestAsset\IteratorWithToArrayTest($data);

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

    /**
     * @group Laminas-3083
     */
    public function testEmptyArrayPassedToPartialLoopShouldNotThrowExceptionInLoopMethod(): void
    {
        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);

        $this->helper->loop('partialLoop.phtml', []);
        $this->addToAssertionCount(1);
    }

    /**
     * @group Laminas-2737
     */
    public function testPartialLoopIncrementsPartialCounterInLoopMethod()
    {
        $data = [
            ['message' => 'foo'],
            ['message' => 'bar'],
            ['message' => 'baz'],
            ['message' => 'bat']
        ];

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);

        $this->helper->loop('partialLoopCouter.phtml', $data);
        $this->assertEquals(4, $this->helper->getPartialCounter());
    }

    /**
     * @group Laminas-5174
     */
    public function testPartialLoopPartialCounterResetsInLoopMethod()
    {
        $data = [
            ['message' => 'foo'],
            ['message' => 'bar'],
            ['message' => 'baz'],
            ['message' => 'bat']
        ];

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);

        $this->helper->loop('partialLoopCouter.phtml', $data);
        $this->assertEquals(4, $this->helper->getPartialCounter());

        $this->helper->loop('partialLoopCouter.phtml', $data);
        $this->assertEquals(4, $this->helper->getPartialCounter());
    }

    public function testShouldNotConvertToArrayRecursivelyIfModelIsTraversableInLoopMethod()
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
                $this->assertStringContainsString('This is an iteration: ' . $value, $result, var_export($value, 1));
            }
        }
    }

    /**
     * @group 7093
     */
    public function testNestedCallsShouldNotOverrideObjectKeyInLoopMethod()
    {
        $data = [];
        for ($i = 0; $i < 3; $i++) {
            $obj = new stdClass();
            $obj->helper = $this->helper;
            $obj->objectKey = "foo" . $i;
            $obj->message = "bar";
            $obj->data = [
                $obj
            ];
            $data[] = $obj;
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

    /**
     * @group 7450
     */
    public function testNestedPartialLoopsNestedArrayInLoopMethod()
    {
        $data = [[
            'obj' => [
                'helper' => $this->helper,
                'message' => 'foo1',
                'data' => [[
                    'message' => 'foo2'
                ]]
            ]
        ]];

        $view = new View();
        $view->resolver()->addPath($this->basePath . '/application/views/scripts');
        $this->helper->setView($view);

        $result = $this->helper->loop('partialLoopParentObject.phtml', $data);
        $this->assertStringContainsString('foo1', $result, $result);
        $this->assertStringContainsString('foo2', $result, $result);
    }

    public function testPartialLoopWithInvalidValuesWillRaiseExceptionInLoopMethod()
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('PartialLoop helper requires iterable data, string given');

        $this->helper->loop('partialLoopParentObject.phtml', 'foo');
    }

    public function testPartialLoopWithInvalidObjectValuesWillRaiseExceptionInLoopMethod()
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('PartialLoop helper requires iterable data, stdClass given');

        $this->helper->loop('partialLoopParentObject.phtml', new stdClass());
    }
}
