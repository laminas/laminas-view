<?php

declare(strict_types=1);

namespace Laminas\View\Helper\Service;

use Laminas\Escaper\Escaper;
use Laminas\Escaper\EscaperInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\Helper\Doctype;
use Laminas\View\Helper\HeadLink;
use Laminas\View\Helper\HeadMeta;
use Laminas\View\Helper\HeadScript;
use Laminas\View\Helper\HeadStyle;
use Laminas\View\Helper\HtmlObject;
use Laminas\View\Helper\HtmlTag;
use Laminas\View\Helper\InlineScript;
use Laminas\View\HelperPluginManager;
use Psr\Container\ContainerInterface;

use function implode;
use function sprintf;

/**
 * This factory is used to initialise helpers that have common constructor dependencies
 *
 * @internal
 *
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 */
final readonly class GenericFactory implements FactoryInterface
{
    private const CAN_CREATE = [
        HeadLink::class     => HeadLink::class,
        HeadMeta::class     => HeadMeta::class,
        HeadScript::class   => HeadScript::class,
        HeadStyle::class    => HeadStyle::class,
        HtmlObject::class   => HtmlObject::class,
        HtmlTag::class      => HtmlTag::class,
        InlineScript::class => InlineScript::class,
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

        $helpers = $container->get(HelperPluginManager::class);

        return new $type($escaper, $helpers->get(Doctype::class));
    }
}
