<?php

declare(strict_types=1);

namespace Laminas\View\Helper\Placeholder;

use Laminas\View\Helper\Placeholder\Container\AbstractContainer;

/**
 * Container for placeholder values
 *
 * @deprecated Since 2.40.0 This class will be removed in version 3.0 without replacement. The container is an
 *             implementation detail that should not be part of the public API
 *
 * @template TKey
 * @template TValue
 * @extends AbstractContainer<TKey, TValue>
 * @final
 */
class Container extends AbstractContainer
{
}
