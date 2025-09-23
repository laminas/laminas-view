<?php

declare(strict_types=1);

namespace Laminas\View\Resolver;

use Laminas\View\Exception\RuntimeException;

use function sprintf;

/**
 * phpcs:disable WebimpressCodingStandard.NamingConventions.Exception
 */
final class TemplateCannotBeFound extends RuntimeException
{
    public static function byName(string $name): self
    {
        return new self(sprintf(
            'The template "%s" cannot be resolved',
            $name,
        ));
    }
}
