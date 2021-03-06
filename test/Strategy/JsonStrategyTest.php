<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

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

class JsonStrategyTest extends TestCase
{
    use EventListenerIntrospectionTrait;

    protected function setUp(): void
    {
        $this->renderer = new JsonRenderer;
        $this->strategy = new JsonStrategy($this->renderer);
        $this->event    = new ViewEvent();
        $this->response = new HttpResponse();
    }

    public function testJsonModelSelectsJsonStrategy()
    {
        $this->event->setModel(new JsonModel());
        $result = $this->strategy->selectRenderer($this->event);
        $this->assertSame($this->renderer, $result);
    }

    /**
     * @group #2410
     */
    public function testJsonAcceptHeaderDoesNotSelectJsonStrategy()
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
    public function testJavascriptAcceptHeaderDoesNotSelectJsonStrategy()
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
    public function testJsonModelJavascriptAcceptHeaderDoesNotSetJsonpCallback()
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

    public function testLackOfJsonModelDoesNotSelectJsonStrategy()
    {
        $result = $this->strategy->selectRenderer($this->event);
        $this->assertNotSame($this->renderer, $result);
        $this->assertNull($result);
    }

    protected function assertResponseNotInjected()
    {
        $content = $this->response->getContent();
        $headers = $this->response->getHeaders();
        $this->assertEmpty($content);
        $this->assertFalse($headers->has('content-type'));
    }

    public function testNonMatchingRendererDoesNotInjectResponse()
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

    public function testNonStringResultDoesNotInjectResponse()
    {
        $this->event->setResponse($this->response);
        $this->event->setRenderer($this->renderer);
        $this->event->setResult($this->response);

        $this->strategy->injectResponse($this->event);
        $this->assertResponseNotInjected();
    }

    public function testMatchingRendererAndStringResultInjectsResponse()
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

    public function testMatchingRendererAndStringResultInjectsResponseJsonp()
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

    public function testReturnsNullWhenCannotSelectRenderer()
    {
        $model   = new ViewModel();
        $request = new HttpRequest();
        $this->event->setModel($model);
        $this->event->setRequest($request);

        $this->assertNull($this->strategy->selectRenderer($this->event));
    }

    public function testAttachesListenersAtExpectedPriorities()
    {
        $events = new EventManager();
        $this->strategy->attach($events);

        foreach (['renderer' => 'selectRenderer', 'response' => 'injectResponse'] as $event => $method) {
            $listeners        = $this->getListenersForEvent($event, $events, true);
            $expectedListener = [$this->strategy, $method];
            $expectedPriority = 1;
            $found            = false;
            foreach ($listeners as $priority => $listener) {
                if ($listener === $expectedListener
                    && $priority === $expectedPriority
                ) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, 'Listener not found');
        }
    }

    public function testCanAttachListenersAtSpecifiedPriority()
    {
        $events = new EventManager();
        $this->strategy->attach($events, 1000);

        foreach (['renderer' => 'selectRenderer', 'response' => 'injectResponse'] as $event => $method) {
            $listeners        = $this->getListenersForEvent($event, $events, true);
            $expectedListener = [$this->strategy, $method];
            $expectedPriority = 1000;
            $found            = false;
            foreach ($listeners as $priority => $listener) {
                if ($listener === $expectedListener
                    && $priority === $expectedPriority
                ) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, 'Listener not found');
        }
    }

    public function testDetachesListeners()
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

    public function testDefaultsToUtf8CharsetWhenCreatingJavascriptHeader()
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

    public function testDefaultsToUtf8CharsetWhenCreatingJsonHeader()
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

    public function testUsesProvidedCharsetWhenCreatingJavascriptHeader()
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

    public function testUsesProvidedCharsetWhenCreatingJsonHeader()
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

    public function testCharsetIsUtf8ByDefault()
    {
        $this->assertEquals('utf-8', $this->strategy->getCharset());
    }

    public function testCharsetIsMutable()
    {
        $this->strategy->setCharset('iso-8859-1');
        $this->assertEquals('iso-8859-1', $this->strategy->getCharset());
    }

    public function multibyteCharsets()
    {
        return [
            'utf-16' => ['utf-16'],
            'utf-32' => ['utf-32'],
        ];
    }

    /**
     * @dataProvider multibyteCharsets
     */
    public function testContentTransferEncodingHeaderSetToBinaryForSpecificMultibyteCharsets($charset)
    {
        $this->strategy->setCharset($charset);

        $this->event->setResponse($this->response);
        $this->event->setRenderer($this->renderer);
        $this->event->setResult(json_encode(['foo' => 'bar']));

        $this->strategy->injectResponse($this->event);
        $content = $this->response->getContent();
        $headers = $this->response->getHeaders();
        $this->assertTrue($headers->has('content-transfer-encoding'));
        $this->assertEquals('binary', $headers->get('content-transfer-encoding')->getFieldValue());
    }
}
