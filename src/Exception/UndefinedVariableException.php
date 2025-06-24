<?php

declare(strict_types=1);

namespace Laminas\View\Exception;

use function sprintf;

final class UndefinedVariableException extends RuntimeException
{
    public static function forVariableName(string $name): self
    {
        return new self(sprintf(
            'The variable "%s" has not been defined',
            $name,
        ));
    }
}
