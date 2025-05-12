<?php

declare(strict_types=1);

namespace LaminasTest\View\HTML;

use Laminas\View\HTML\Tag;
use PHPUnit\Framework\TestCase;

final class TagTest extends TestCase
{
    public function testEqualityWithSameAttributeOrder(): void
    {
        self::assertTrue(
            (new Tag('foo', ['bar' => 'baz', 'bing' => 'bong']))->equals(
                new Tag('foo', ['bar' => 'baz', 'bing' => 'bong']),
            ),
        );
    }

    public function testEqualityWithDifferentAttributeOrder(): void
    {
        self::assertTrue(
            (new Tag('foo', ['bar' => 'baz', 'bing' => 'bong']))->equals(
                new Tag('foo', ['bing' => 'bong', 'bar' => 'baz']),
            ),
        );
    }

    public function testEqualityWithDifferentTag(): void
    {
        self::assertFalse(
            (new Tag('foo', ['bar' => 'baz', 'bing' => 'bong']))->equals(
                new Tag('oof', ['bar' => 'baz', 'bing' => 'bong']),
            ),
        );
    }

    public function testEqualityWithDifferentAttributes(): void
    {
        self::assertFalse(
            (new Tag('foo', ['bar' => 'baz', 'bing' => 'bong']))->equals(
                new Tag('foo', ['bar' => 'baz']),
            ),
        );
    }
}
