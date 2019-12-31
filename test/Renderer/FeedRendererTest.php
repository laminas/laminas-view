<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View\Renderer;

use Laminas\View\Exception;
use Laminas\View\Model\FeedModel;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\FeedRenderer;
use Laminas\View\Resolver\PrefixPathStackResolver;
use PHPUnit\Framework\TestCase;

class FeedRendererTest extends TestCase
{
    public function setUp()
    {
        $this->renderer = new FeedRenderer();
    }

    protected function getFeedData($type)
    {
        return [
            'copyright' => date('Y'),
            'date_created' => time(),
            'date_modified' => time(),
            'last_build_date' => time(),
            'description' => __CLASS__,
            'id' => 'https://getlaminas.org/',
            'language' => 'en_US',
            'feed_link' => [
                'link' => 'https://getlaminas.org/feed.xml',
                'type' => $type,
            ],
            'link' => 'https://getlaminas.org/feed.xml',
            'title' => 'Testing',
            'encoding' => 'UTF-8',
            'base_url' => 'https://getlaminas.org/',
            'entries' => [
                [
                    'content' => 'test content',
                    'date_created' => time(),
                    'date_modified' => time(),
                    'description' => __CLASS__,
                    'id' => 'https://getlaminas.org/1',
                    'link' => 'https://getlaminas.org/1',
                    'title' => 'Test 1',
                ],
                [
                    'content' => 'test content',
                    'date_created' => time(),
                    'date_modified' => time(),
                    'description' => __CLASS__,
                    'id' => 'https://getlaminas.org/2',
                    'link' => 'https://getlaminas.org/2',
                    'title' => 'Test 2',
                ],
            ],
        ];
    }

    public function testRendersFeedModelAccordingToTypeProvidedInModel()
    {
        $model = new FeedModel($this->getFeedData('atom'));
        $model->setOption('feed_type', 'atom');
        $xml = $this->renderer->render($model);
        $this->assertContains('<' . '?xml', $xml);
        $this->assertContains('atom', $xml);
    }

    public function testRendersFeedModelAccordingToRenderTypeIfNoTypeProvidedInModel()
    {
        $this->renderer->setFeedType('atom');
        $model = new FeedModel($this->getFeedData('atom'));
        $xml = $this->renderer->render($model);
        $this->assertContains('<' . '?xml', $xml);
        $this->assertContains('atom', $xml);
    }

    public function testCastsViewModelToFeedModelUsingFeedTypeOptionProvided()
    {
        $model = new ViewModel($this->getFeedData('atom'));
        $model->setOption('feed_type', 'atom');
        $xml = $this->renderer->render($model);
        $this->assertContains('<' . '?xml', $xml);
        $this->assertContains('atom', $xml);
    }

    public function testCastsViewModelToFeedModelUsingRendererFeedTypeIfNoFeedTypeOptionInModel()
    {
        $this->renderer->setFeedType('atom');
        $model = new ViewModel($this->getFeedData('atom'));
        $xml = $this->renderer->render($model);
        $this->assertContains('<' . '?xml', $xml);
        $this->assertContains('atom', $xml);
    }

    public function testStringModelWithValuesProvidedCastsToFeed()
    {
        $this->renderer->setFeedType('atom');
        $xml = $this->renderer->render('layout', $this->getFeedData('atom'));
        $this->assertContains('<' . '?xml', $xml);
        $this->assertContains('atom', $xml);
    }

    public function testNonStringNonModelArgumentRaisesException()
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('expects');
        $this->renderer->render(['foo']);
    }

    public function testSettingUnacceptableFeedTypeRaisesException()
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('expects a string of either "rss" or "atom"');
        $this->renderer->setFeedType('foobar');
    }

    public function testReturnsSameRendererInstanceWhenResolverIsSet()
    {
        $resolver = new PrefixPathStackResolver();
        $returnValue = $this->renderer->setResolver($resolver);
        $this->assertSame($returnValue, $this->renderer);
    }

    public function testReturnsSameRendererInstanceWhenFieldTypeIsSet()
    {
        $returnValue = $this->renderer->setFeedType('rss');
        $this->assertSame($returnValue, $this->renderer);
    }
}
