<?php

declare(strict_types=1);

namespace LaminasTest\View\Strategy;

use Laminas\EventManager\EventManager;
use Laminas\EventManager\Test\EventListenerIntrospectionTrait;
use Laminas\Feed\Writer\FeedFactory;
use Laminas\Http\Header\HeaderInterface;
use Laminas\Http\Request as HttpRequest;
use Laminas\Http\Response as HttpResponse;
use Laminas\View\Model\FeedModel;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\FeedRenderer;
use Laminas\View\Strategy\FeedStrategy;
use Laminas\View\ViewEvent;
use PHPUnit\Framework\TestCase;

use function assert;
use function date;
use function iterator_to_array;
use function time;

class FeedStrategyTest extends TestCase
{
    use EventListenerIntrospectionTrait;

    private FeedRenderer $renderer;
    private FeedStrategy $strategy;
    private ViewEvent $event;
    private HttpResponse $response;

    protected function setUp(): void
    {
        $this->renderer = new FeedRenderer();
        $this->strategy = new FeedStrategy($this->renderer);
        $this->event    = new ViewEvent();
        $this->response = new HttpResponse();
    }

    public function testFeedModelSelectsFeedStrategy(): void
    {
        $this->event->setModel(new FeedModel());
        $result = $this->strategy->selectRenderer($this->event);
        $this->assertSame($this->renderer, $result);
    }

    /**
     * @group #2410
     */
    public function testRssAcceptHeaderDoesNotSelectFeedStrategy(): void
    {
        $request = new HttpRequest();
        $request->getHeaders()->addHeaderLine('Accept', 'application/rss+xml');
        $this->event->setRequest($request);
        $result = $this->strategy->selectRenderer($this->event);
        $this->assertNotSame($this->renderer, $result);
    }

    /**
     * @group #2410
     */
    public function testAtomAcceptHeaderDoesNotSelectFeedStrategy(): void
    {
        $request = new HttpRequest();
        $request->getHeaders()->addHeaderLine('Accept', 'application/atom+xml');
        $this->event->setRequest($request);
        $result = $this->strategy->selectRenderer($this->event);
        $this->assertNotSame($this->renderer, $result);
    }

    public function testAcceptHeaderDoesNotSetFeedType(): void
    {
        $this->event->setModel(new FeedModel());
        $request = new HttpRequest();
        $request->getHeaders()->addHeaderLine('Accept', 'application/atom+xml');
        $this->event->setRequest($request);
        $result = $this->strategy->selectRenderer($this->event);
        $this->assertSame($this->renderer, $result);
        $this->assertNotSame('atom', $result->getFeedType());
    }

    public function testLackOfFeedModelOrAcceptHeaderDoesNotSelectFeedStrategy(): void
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
        $renderer = new FeedRenderer();
        $this->event->setRenderer($renderer);
        $this->strategy->injectResponse($this->event);
        $this->assertResponseNotInjected();
    }

    public function testNonStringOrFeedResultDoesNotInjectResponse(): void
    {
        $this->event->setResponse($this->response);
        $this->event->setRenderer($this->renderer);
        $this->event->setResult($this->response);

        $this->strategy->injectResponse($this->event);
        $this->assertResponseNotInjected();
    }

    private function assertResponseHasHeader(string $type, string $value): void
    {
        $headers = $this->response->getHeaders();
        $this->assertTrue($headers->has($type));
        $header = $headers->get($type);
        assert($header instanceof HeaderInterface);
        $this->assertEquals($value, $header->getFieldValue());
    }

    public function testMatchingRendererAndStringResultInjectsResponse(): void
    {
        $this->renderer->setFeedType('atom');
        $expected = '<?xml version="1.0"><root><content>content</content></root>';
        $this->event->setResponse($this->response);
        $this->event->setRenderer($this->renderer);
        $this->event->setResult($expected);

        $this->strategy->injectResponse($this->event);

        $this->assertEquals($expected, $this->response->getContent());
        $this->assertResponseHasHeader('content-type', 'application/atom+xml');
    }

    /**
     * @return array<string, mixed>
     */
    protected function getFeedData(string $type): array
    {
        return [
            'copyright'       => date('Y'),
            'date_created'    => time(),
            'date_modified'   => time(),
            'last_build_date' => time(),
            'description'     => self::class,
            'id'              => 'https://getlaminas.org/',
            'language'        => 'en_US',
            'feed_link'       => [
                'link' => 'https://getlaminas.org/feed.xml',
                'type' => $type,
            ],
            'link'            => 'https://getlaminas.org/feed.xml',
            'title'           => 'Testing',
            'encoding'        => 'UTF-8',
            'base_url'        => 'https://getlaminas.org/',
            'entries'         => [
                [
                    'content'       => 'test content',
                    'date_created'  => time(),
                    'date_modified' => time(),
                    'description'   => self::class,
                    'id'            => 'https://getlaminas.org/1',
                    'link'          => 'https://getlaminas.org/1',
                    'title'         => 'Test 1',
                ],
                [
                    'content'       => 'test content',
                    'date_created'  => time(),
                    'date_modified' => time(),
                    'description'   => self::class,
                    'id'            => 'https://getlaminas.org/2',
                    'link'          => 'https://getlaminas.org/2',
                    'title'         => 'Test 2',
                ],
            ],
        ];
    }

    public function testMatchingRendererAndFeedResultInjectsResponse(): void
    {
        $this->renderer->setFeedType('atom');
        $expected = FeedFactory::factory($this->getFeedData('atom'));
        $this->event->setResponse($this->response);
        $this->event->setRenderer($this->renderer);
        $this->event->setResult($expected);

        $this->strategy->injectResponse($this->event);

        $this->assertEquals($expected->export('atom'), $this->response->getContent());
        $this->assertResponseHasHeader('content-type', 'application/atom+xml');
    }

    public function testResponseContentTypeIsBasedOnFeedType(): void
    {
        $this->renderer->setFeedType('rss');
        $expected = FeedFactory::factory($this->getFeedData('rss'));
        $this->event->setResponse($this->response);
        $this->event->setRenderer($this->renderer);
        $this->event->setResult($expected);

        $this->strategy->injectResponse($this->event);

        $this->assertEquals($expected->export('rss'), $this->response->getContent());
        $this->assertResponseHasHeader('content-type', 'application/rss+xml');
    }

    public function testReturnsNullWhenUnableToSelectRenderer(): void
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
                self::assertIsInt($priority);
                self::assertIsArray($listener);
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
        $this->strategy->attach($events, 100);

        foreach (['renderer' => 'selectRenderer', 'response' => 'injectResponse'] as $event => $method) {
            $listeners        = $this->getListenersForEvent($event, $events, true);
            $expectedListener = [$this->strategy, $method];
            $expectedPriority = 100;
            $found            = false;
            foreach ($listeners as $priority => $listener) {
                self::assertIsInt($priority);
                self::assertIsArray($listener);
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

        $this->strategy->detach($events);
        $listeners = iterator_to_array($this->getListenersForEvent('renderer', $events));
        $this->assertCount(0, $listeners);
        $listeners = iterator_to_array($this->getListenersForEvent('response', $events));
        $this->assertCount(0, $listeners);
    }
}
