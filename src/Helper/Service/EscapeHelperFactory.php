<?php

declare(strict_types=1);

namespace Laminas\View\Helper\Service;

use Laminas\Escaper\Escaper;
use Laminas\Escaper\EscaperInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\Helper\EscapeCss;
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\Helper\EscapeHtmlAttr;
use Laminas\View\Helper\EscapeJs;
use Laminas\View\Helper\EscapeUrl;
use Laminas\View\Helper\GravatarImage;
use Laminas\View\Helper\HtmlAttributes;
use Laminas\View\Helper\HtmlList;
use Psr\Container\ContainerInterface;

use function implode;
use function sprintf;

/**
 * This factory is used to generate helpers that have a single constructor argument on an Escaper instance
 *
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 */
final readonly class EscapeHelperFactory implements FactoryInterface
{
    private const CAN_CREATE = [
        EscapeCss::class      => EscapeCss::class,
        EscapeHtml::class     => EscapeHtml::class,
        EscapeHtmlAttr::class => EscapeHtmlAttr::class,
        EscapeJs::class       => EscapeJs::class,
        EscapeUrl::class      => EscapeUrl::class,
        HtmlAttributes::class => HtmlAttributes::class,
        HtmlList::class       => HtmlList::class,
        GravatarImage::class  => GravatarImage::class,
    ];

    /** @inheritDoc */
    public function __invoke(ContainerInterface $container, string $requestedName, ?array $options = null): mixed
    {
        $type = self::CAN_CREATE[$requestedName] ?? null;

        if ($type === null) {
            throw new InvalidArgumentException(sprintf(
                'Dependencies of type "%s" cannot be created by this factory. '
                . 'Only the following types are supported: %s',
                $requestedName,
                implode(', ', self::CAN_CREATE),
            ));
        }

        $escaper = $container->has(EscaperInterface::class)
            ? $container->get(EscaperInterface::class)
            : new Escaper();

        return new $type($escaper);
    }
}
