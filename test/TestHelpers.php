<?php

declare(strict_types=1);

namespace LaminasTest\View;

use Closure;
use PHPUnit\Framework\TestCase;
use stdClass;

use function restore_error_handler;
use function set_error_handler;

use const E_ALL;
use const E_USER_DEPRECATED;

final class TestHelpers
{
    /**
     * @param Closure(): T $operation
     * @return T
     * @template T
     */
    public static function expectDeprecationWithMessage(string $message, Closure $operation): mixed
    {
        $object          = new stdClass();
        $object->code    = null;
        $object->message = null;

        set_error_handler(static function (int $errorNo, string $message) use ($object): bool {
            $object->code    = $errorNo;
            $object->message = $message;

            return true;
        }, E_ALL);

        try {
            $returnValue = $operation();
        } finally {
            restore_error_handler();
        }

        /** @psalm-suppress RedundantCondition */
        if ($object->code !== E_USER_DEPRECATED) {
            TestCase::fail('A deprecation was not issued');
        }

        TestCase::assertStringContainsString($message, (string) $object->message);

        return $returnValue;
    }
}
