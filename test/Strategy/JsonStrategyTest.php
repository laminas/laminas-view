<?php

namespace LaminasTest\View\Strategy;

use Laminas\EventManager\EventManager;
use Laminas\EventManager\Test\EventListenerIntrospectionTrait;
use Laminas\Http\Request as HttpRequest;
use Laminas\Http\Response as HttpResponse;
use Laminas\Stdlib\Parameters;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\JsonRenderer;
use Laminas\View\Strategy\JsonStrategy;
use Laminas\View\ViewEvent;
use PHPUnit\Framework\TestCase;

use function iterator_to_array;
use function json_encode;

class JsonStrategyTest extends TestCase
{
    use EventListenerIntrospectionTrait;

    /** @var JsonRenderer */
    private $renderer;
    /** @var JsonStrategy */
    private $strategy;
    /** @var ViewEvent */
    private $event;
    /** @var HttpResponse */
    private $response;

    protected function setUp(): void
    {
        $this->renderer = new JsonRenderer();
        $this->strategy = new JsonStrategy($this->renderer);
        $this->event    = new ViewEvent();
        $this->response = new HttpResponse();
    }

    public function testJsonModelSelectsJsonStrategy(): void
    {
        $this->event->setModel(new JsonModel());
        $result = $this->strategy->selectRenderer($this->event);
        $this->assertSame($this->renderer, $result);
    }

    /**
     * @group #2410
     */
    public function testJsonAcceptHeaderDoesNotSelectJsonStrategy(): void
    {
        $request = new HttpRequest();
        $request->getHeaders()->addHeaderLine('Accept', 'application/json');
        $this->event->setRequest($request);
        $result = $this->strategy->selectRenderer($this->event);
        $this->assertNotSame($this->renderer, $result);
    }

    /**
     * @group #2410
     */
    public function testJavascriptAcceptHeaderDoesNotSelectJsonStrategy(): void
    {
        $request = new HttpRequest();
        $request->getHeaders()->addHeaderLine('Accept', 'application/javascript');
        $this->event->setRequest($request);
        $result = $this->strategy->selectRenderer($this->event);
        $this->assertNotSame($this->renderer, $result);
    }

    /**
     * @group #2410
     */
    public function testJsonModelJavascriptAcceptHeaderDoesNotSetJsonpCallback(): void
    {
        $this->event->setModel(new JsonModel());
        $request = new HttpRequest();
        $request->getHeaders()->addHeaderLine('Accept', 'application/javascript');
        $request->setQuery(new Parameters(['callback' => 'foo']));
        $this->event->setRequest($request);
        $result = $this->strategy->selectRenderer($this->event);
        $this->assertSame($this->renderer, $result);
        $this->assertFalse($result->hasJsonpCallback());
    }

    public function testLackOfJsonModelDoesNotSelectJsonStrategy(): void
    {
        $result = $this->strategy->selectRenderer($this->event);
        $this->assertNotSame($this->renderer, $result);
        $this->assertNull($result);
    }

    protected function assertResponseNotInjected(): void
    {
        $content = $this->response->getContent();
        $headers = $this->response->getHeaders();
        $this->assertEmpty($content);
        $this->assertFalse($headers->has('content-type'));
    }

    public function testNonMatchingRendererDoesNotInjectResponse(): void
    {
        $this->event->setResponse($this->response);

        // test empty renderer
        $this->strategy->injectResponse($this->event);
        $this->assertResponseNotInjected();

        // test non-matching renderer
        $renderer = new JsonRenderer();
        $this->event->setRenderer($renderer);
        $this->strategy->injectResponse($this->event);
        $this->assertResponseNotInjected();
    }

    public function testNonStringResultDoesNotInjectResponse(): void
    {
        $this->event->setResponse($this->response);
        $this->event->setRenderer($this->renderer);
        $this->event->setResult($this->response);

        $this->strategy->injectResponse($this->event);
        $this->assertResponseNotInjected();
    }

    public function testMatchingRendererAndStringResultInjectsResponse(): void
    {
        $expected = json_encode(['foo' => 'bar']);
        $this->event->setResponse($this->response);
        $this->event->setRenderer($this->renderer);
        $this->event->setResult($expected);

        $this->strategy->injectResponse($this->event);
        $content = $this->response->getContent();
        $headers = $this->response->getHeaders();
        $this->assertEquals($expected, $content);
        $this->assertTrue($headers->has('content-type'));
        $this->assertStringContainsString('application/json', $headers->get('content-type')->getFieldValue());
    }

    public function testMatchingRendererAndStringResultInjectsResponseJsonp(): void
    {
        $expected = json_encode(['foo' => 'bar']);
        $this->renderer->setJsonpCallback('foo');
        $this->event->setResponse($this->response);
        $this->event->setRenderer($this->renderer);
        $this->event->setResult($expected);

        $this->strategy->injectResponse($this->event);
        $content = $this->response->getContent();
        $headers = $this->response->getHeaders();
        $this->assertEquals($expected, $content);
        $this->assertTrue($headers->has('content-type'));
        $this->assertStringContainsString('application/javascript', $headers->get('content-type')->getFieldValue());
    }

    public function testReturnsNullWhenCannotSelectRenderer(): void
    {
        $model   = new ViewModel();
        $request = new HttpRequest();
        $this->event->setModel($model);
        $this->event->setRequest($request);

        $this->assertNull($this->strategy->selectRenderer($this->event));
    }

    public function testAttachesListenersAtExpectedPriorities(): void
    {
        $events = new EventManager();
        $this->strategy->attach($events);

        foreach (['renderer' => 'selectRenderer', 'response' => 'injectResponse'] as $event => $method) {
            $listeners        = $this->getListenersForEvent($event, $events, true);
            $expectedListener = [$this->strategy, $method];
            $expectedPriority = 1;
            $found            = false;
            foreach ($listeners as $priority => $listener) {
                if (
                    $listener === $expectedListener
                    && $priority === $expectedPriority
                ) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, 'Listener not found');
        }
    }

    public function testCanAttachListenersAtSpecifiedPriority(): void
    {
        $events = new EventManager();
        $this->strategy->attach($events, 1000);

        foreach (['renderer' => 'selectRenderer', 'response' => 'injectResponse'] as $event => $method) {
            $listeners        = $this->getListenersForEvent($event, $events, true);
            $expectedListener = [$this->strategy, $method];
            $expectedPriority = 1000;
            $found            = false;
            foreach ($listeners as $priority => $listener) {
                if (
                    $listener === $expectedListener
                    && $priority === $expectedPriority
                ) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, 'Listener not found');
        }
    }

    public function testDetachesListeners(): void
    {
        $events = new EventManager();
        $this->strategy->attach($events, 100);

        $listeners = iterator_to_array($this->getListenersForEvent('renderer', $events));
        $this->assertCount(1, $listeners);
        $listeners = iterator_to_array($this->getListenersForEvent('response', $events));
        $this->assertCount(1, $listeners);

        $this->strategy->detach($events, 100);
        $listeners = iterator_to_array($this->getListenersForEvent('renderer', $events));
        $this->assertCount(0, $listeners);
        $listeners = iterator_to_array($this->getListenersForEvent('response', $events));
        $this->assertCount(0, $listeners);
    }

    public function testDefaultsToUtf8CharsetWhenCreatingJavascriptHeader(): void
    {
        $expected = json_encode(['foo' => 'bar']);
        $this->renderer->setJsonpCallback('foo');
        $this->event->setResponse($this->response);
        $this->event->setRenderer($this->renderer);
        $this->event->setResult($expected);

        $this->strategy->injectResponse($this->event);
        $content = $this->response->getContent();
        $headers = $this->response->getHeaders();
        $this->assertEquals($expected, $content);
        $this->assertTrue($headers->has('content-type'));
        $this->assertStringContainsString(
            'application/javascript; charset=utf-8',
            $headers->get('content-type')->getFieldValue()
        );
    }

    public function testDefaultsToUtf8CharsetWhenCreatingJsonHeader(): void
    {
        $expected = json_encode(['foo' => 'bar']);
        $this->event->setResponse($this->response);
        $this->event->setRenderer($this->renderer);
        $this->event->setResult($expected);

        $this->strategy->injectResponse($this->event);
        $content = $this->response->getContent();
        $headers = $this->response->getHeaders();
        $this->assertEquals($expected, $content);
        $this->assertTrue($headers->has('content-type'));
        $this->assertStringContainsString(
            'application/json; charset=utf-8',
            $headers->get('content-type')->getFieldValue()
        );
    }

    public function testUsesProvidedCharsetWhenCreatingJavascriptHeader(): void
    {
        $expected = json_encode(['foo' => 'bar']);
        $this->renderer->setJsonpCallback('foo');
        $this->event->setResponse($this->response);
        $this->event->setRenderer($this->renderer);
        $this->event->setResult($expected);

        $this->strategy->setCharset('utf-16');
        $this->strategy->injectResponse($this->event);
        $content = $this->response->getContent();
        $headers = $this->response->getHeaders();
        $this->assertEquals($expected, $content);
        $this->assertTrue($headers->has('content-type'));
        $this->assertStringContainsString(
            'application/javascript; charset=utf-16',
            $headers->get('content-type')->getFieldValue()
        );
    }

    public function testUsesProvidedCharsetWhenCreatingJsonHeader(): void
    {
        $expected = json_encode(['foo' => 'bar']);
        $this->event->setResponse($this->response);
        $this->event->setRenderer($this->renderer);
        $this->event->setResult($expected);

        $this->strategy->setCharset('utf-16');
        $this->strategy->injectResponse($this->event);
        $content = $this->response->getContent();
        $headers = $this->response->getHeaders();
        $this->assertEquals($expected, $content);
        $this->assertTrue($headers->has('content-type'));
        $this->assertStringContainsString(
            'application/json; charset=utf-16',
            $headers->get('content-type')->getFieldValue()
        );
    }

    public function testCharsetIsUtf8ByDefault(): void
    {
        $this->assertEquals('utf-8', $this->strategy->getCharset());
    }

    public function testCharsetIsMutable(): void
    {
        $this->strategy->setCharset('iso-8859-1');
        $this->assertEquals('iso-8859-1', $this->strategy->getCharset());
    }

    /**
     * @return string[][]
     * @psalm-return array{utf-16: array{0: 'utf-16'}, utf-32: array{0: 'utf-32'}}
     */
    public function multibyteCharsets(): array
    {
        return [
            'utf-16' => ['utf-16'],
            'utf-32' => ['utf-32'],
        ];
    }

    /**
     * @dataProvider multibyteCharsets
     * @param string $charset
     */
    public function testContentTransferEncodingHeaderSetToBinaryForSpecificMultibyteCharsets($charset): void
    {
        $this->strategy->setCharset($charset);

        $this->event->setResponse($this->response);
        $this->event->setRenderer($this->renderer);
        $this->event->setResult(json_encode(['foo' => 'bar']));

        $this->strategy->injectResponse($this->event);
        $this->response->getContent();
        $headers = $this->response->getHeaders();
        $this->assertTrue($headers->has('content-transfer-encoding'));
        $this->assertEquals('binary', $headers->get('content-transfer-encoding')->getFieldValue());
    }
}
