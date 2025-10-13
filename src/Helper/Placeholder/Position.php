<?php

declare(strict_types=1);

namespace Laminas\View\Helper\Placeholder;

enum Position
{
    case Append;
    case Prepend;
    case Set;
}
