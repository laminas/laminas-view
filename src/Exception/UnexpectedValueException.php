<?php

declare(strict_types=1);

namespace Laminas\View\Exception;

use UnexpectedValueException as UnexpectedValue;

/**
 * @deprecated Since 2.40.0
 *
 * @final
 */
class UnexpectedValueException extends UnexpectedValue implements ExceptionInterface
{
}
