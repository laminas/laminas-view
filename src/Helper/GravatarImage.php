<?php

declare(strict_types=1);

namespace Laminas\View\Helper;

use Laminas\Escaper\Escaper;
use Laminas\View\HtmlAttributesSet;

use function md5;
use function sprintf;
use function strtolower;
use function trim;

/**
 * @psalm-import-type AttributeSet from HtmlAttributesSet
 */
final class GravatarImage
{
    private const GRAVATAR_URL = '//www.gravatar.com/avatar';

    public const RATINGS = [
        'g',
        'pg',
        'r',
        'x',
    ];

    public const DEFAULT_IMAGE_VALUES = [
        '404',
        'mm',
        'identicon',
        'monsterid',
        'wavatar',
    ];

    private Escaper $escaper;

    public function __construct(?Escaper $escaper = null)
    {
        $this->escaper = $escaper ?: new Escaper();
    }

    /**
     * @param non-empty-string                                  $emailAddress
     * @param positive-int                                      $imageSize
     * @param AttributeSet                                      $imageAttributes
     * @psalm-param value-of<self::DEFAULT_IMAGE_VALUES>|string $defaultImage
     * @psalm-param value-of<self::RATINGS>                     $rating
     */
    public function __invoke(
        string $emailAddress,
        int $imageSize = 80,
        array $imageAttributes = [],
        string $defaultImage = 'mm',
        string $rating = 'g'
    ): string {
        $imageAttributes['width'] = $imageAttributes['height'] = $imageSize;
        $imageAttributes['alt']   = $imageAttributes['alt'] ?? '';
        $imageAttributes['src']   = sprintf(
            '%s/%s?s=%d&r=%s&d=%s',
            self::GRAVATAR_URL,
            md5(strtolower(trim($emailAddress))),
            $imageSize,
            $rating,
            $this->escaper->escapeUrl($defaultImage)
        );

        return sprintf(
            '<img%s />',
            (string) new HtmlAttributesSet($this->escaper, $imageAttributes)
        );
    }
}
