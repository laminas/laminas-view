<?php

declare(strict_types=1);

namespace Laminas\View\Exception;

use BadMethodCallException as BadMethodCall;

/** @final */
class BadMethodCallException extends BadMethodCall implements ExceptionInterface
{
}
