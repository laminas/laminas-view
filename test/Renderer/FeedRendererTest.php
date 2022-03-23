<?php

declare(strict_types=1);

namespace LaminasTest\View\Renderer;

use Laminas\View\Exception;
use Laminas\View\Model\FeedModel;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\FeedRenderer;
use Laminas\View\Resolver\PrefixPathStackResolver;
use PHPUnit\Framework\TestCase;

use function date;
use function time;

class FeedRendererTest extends TestCase
{
    private FeedRenderer $renderer;

    protected function setUp(): void
    {
        $this->renderer = new FeedRenderer();
    }

    /**
     * @psalm-return array<string, mixed>
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

    public function testRendersFeedModelAccordingToTypeProvidedInModel(): void
    {
        $model = new FeedModel($this->getFeedData('atom'));
        $model->setOption('feed_type', 'atom');
        $xml = $this->renderer->render($model);
        $this->assertStringContainsString('<' . '?xml', $xml);
        $this->assertStringContainsString('atom', $xml);
    }

    public function testRendersFeedModelAccordingToRenderTypeIfNoTypeProvidedInModel(): void
    {
        $this->renderer->setFeedType('atom');
        $model = new FeedModel($this->getFeedData('atom'));
        $xml   = $this->renderer->render($model);
        $this->assertStringContainsString('<' . '?xml', $xml);
        $this->assertStringContainsString('atom', $xml);
    }

    public function testCastsViewModelToFeedModelUsingFeedTypeOptionProvided(): void
    {
        $model = new ViewModel($this->getFeedData('atom'));
        $model->setOption('feed_type', 'atom');
        $xml = $this->renderer->render($model);
        $this->assertStringContainsString('<' . '?xml', $xml);
        $this->assertStringContainsString('atom', $xml);
    }

    public function testCastsViewModelToFeedModelUsingRendererFeedTypeIfNoFeedTypeOptionInModel(): void
    {
        $this->renderer->setFeedType('atom');
        $model = new ViewModel($this->getFeedData('atom'));
        $xml   = $this->renderer->render($model);
        $this->assertStringContainsString('<' . '?xml', $xml);
        $this->assertStringContainsString('atom', $xml);
    }

    public function testStringModelWithValuesProvidedCastsToFeed(): void
    {
        $this->renderer->setFeedType('atom');
        $xml = $this->renderer->render('layout', $this->getFeedData('atom'));
        $this->assertStringContainsString('<' . '?xml', $xml);
        $this->assertStringContainsString('atom', $xml);
    }

    public function testNonStringNonModelArgumentRaisesException(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('expects');
        /** @psalm-suppress InvalidArgument */
        $this->renderer->render(['foo']);
    }

    public function testSettingUnacceptableFeedTypeRaisesException(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('expects a string of either "rss" or "atom"');
        $this->renderer->setFeedType('foobar');
    }

    public function testReturnsSameRendererInstanceWhenResolverIsSet(): void
    {
        $resolver    = new PrefixPathStackResolver();
        $returnValue = $this->renderer->setResolver($resolver);
        $this->assertSame($returnValue, $this->renderer);
    }

    public function testReturnsSameRendererInstanceWhenFieldTypeIsSet(): void
    {
        $returnValue = $this->renderer->setFeedType('rss');
        $this->assertSame($returnValue, $this->renderer);
    }
}
