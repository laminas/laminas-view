<?php

declare(strict_types=1);

namespace Laminas\View\Exception;

use RuntimeException as RuntimeError;

/** @final */
class RuntimeException extends RuntimeError implements ExceptionInterface
{
}
