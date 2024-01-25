<?php

declare(strict_types=1);

namespace Laminas\View\Helper;

use function json_encode;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

/**
 * Helper for simplifying JSON responses
 */
final class Json
{
    /**
     * Encode data as JSON
     *
     * @param array{prettyPrint?: bool} $jsonOptions
     * @return non-empty-string
     */
    public function __invoke(mixed $data, array $jsonOptions = []): string
    {
        /** @psalm-var non-empty-string */
        return json_encode($data, $this->optionsToFlags($jsonOptions));
    }

    /** @param array{prettyPrint?: bool} $options */
    private function optionsToFlags(array $options = []): int
    {
        $prettyPrint = $options['prettyPrint'] ?? false;
        $flags       = JSON_THROW_ON_ERROR;
        $flags      |= $prettyPrint === false ? 0 : JSON_PRETTY_PRINT;

        return $flags;
    }
}
