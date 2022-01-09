<?php

declare(strict_types=1);

namespace LaminasTest\View;

use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\ViewEvent;
use PHPUnit\Framework\TestCase;

class ViewEventTest extends TestCase
{
    /** @var ViewEvent */
    protected $event;

    protected function setUp(): void
    {
        $this->event = new ViewEvent();
    }

    public function testModelIsNullByDefault(): void
    {
        $this->assertNull($this->event->getModel());
    }

    public function testRendererIsNullByDefault(): void
    {
        $this->assertNull($this->event->getRenderer());
    }

    public function testRequestIsNullByDefault(): void
    {
        $this->assertNull($this->event->getRequest());
    }

    public function testResponseIsNullByDefault(): void
    {
        $this->assertNull($this->event->getResponse());
    }

    public function testResultIsNullByDefault(): void
    {
        $this->assertNull($this->event->getResult());
    }

    public function testModelIsMutable(): void
    {
        $model = new ViewModel();
        $this->event->setModel($model);
        $this->assertSame($model, $this->event->getModel());
    }

    public function testRendererIsMutable(): void
    {
        $renderer = new PhpRenderer();
        $this->event->setRenderer($renderer);
        $this->assertSame($renderer, $this->event->getRenderer());
    }

    public function testRequestIsMutable(): void
    {
        $request = new Request();
        $this->event->setRequest($request);
        $this->assertSame($request, $this->event->getRequest());
    }

    public function testResponseIsMutable(): void
    {
        $response = new Response();
        $this->event->setResponse($response);
        $this->assertSame($response, $this->event->getResponse());
    }

    public function testResultIsMutable(): void
    {
        $result = 'some result';
        $this->event->setResult($result);
        $this->assertSame($result, $this->event->getResult());
    }

    public function testModelIsMutableViaSetParam(): void
    {
        $model = new ViewModel();
        $this->event->setParam('model', $model);
        $this->assertSame($model, $this->event->getModel());
        $this->assertSame($model, $this->event->getParam('model'));
    }

    public function testRendererIsMutableViaSetParam(): void
    {
        $renderer = new PhpRenderer();
        $this->event->setParam('renderer', $renderer);
        $this->assertSame($renderer, $this->event->getRenderer());
        $this->assertSame($renderer, $this->event->getParam('renderer'));
    }

    public function testRequestIsMutableViaSetParam(): void
    {
        $request = new Request();
        $this->event->setParam('request', $request);
        $this->assertSame($request, $this->event->getRequest());
        $this->assertSame($request, $this->event->getParam('request'));
    }

    public function testResponseIsMutableViaSetParam(): void
    {
        $response = new Response();
        $this->event->setParam('response', $response);
        $this->assertSame($response, $this->event->getResponse());
        $this->assertSame($response, $this->event->getParam('response'));
    }

    public function testResultIsMutableViaSetParam(): void
    {
        $result = 'some result';
        $this->event->setParam('result', $result);
        $this->assertSame($result, $this->event->getResult());
        $this->assertSame($result, $this->event->getParam('result'));
    }

    public function testSpecializedParametersMayBeSetViaSetParams(): void
    {
        $model    = new ViewModel();
        $renderer = new PhpRenderer();
        $request  = new Request();
        $response = new Response();
        $result   = 'some result';

        $params = [
            'model'    => $model,
            'renderer' => $renderer,
            'request'  => $request,
            'response' => $response,
            'result'   => $result,
            'otherkey' => 'other value',
        ];

        $this->event->setParams($params);
        $this->assertEquals($params, $this->event->getParams());

        $this->assertSame($params['model'], $this->event->getModel());
        $this->assertSame($params['model'], $this->event->getParam('model'));

        $this->assertSame($params['renderer'], $this->event->getRenderer());
        $this->assertSame($params['renderer'], $this->event->getParam('renderer'));

        $this->assertSame($params['request'], $this->event->getRequest());
        $this->assertSame($params['request'], $this->event->getParam('request'));

        $this->assertSame($params['response'], $this->event->getResponse());
        $this->assertSame($params['response'], $this->event->getParam('response'));

        $this->assertSame($params['result'], $this->event->getResult());
        $this->assertSame($params['result'], $this->event->getParam('result'));

        $this->assertEquals($params['otherkey'], $this->event->getParam('otherkey'));
    }
}
