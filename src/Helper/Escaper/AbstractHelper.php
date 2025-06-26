<?php

declare(strict_types=1);

namespace Laminas\View\Helper\Escaper;

use Laminas\Escaper\EscaperInterface;
use Laminas\View\Exception;

use function is_array;
use function is_object;
use function is_string;
use function method_exists;
use function trigger_error;

use const E_USER_DEPRECATED;

/**
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 */
abstract class AbstractHelper
{
    public const RECURSE_NONE   = 0x00;
    public const RECURSE_ARRAY  = 0x01;
    public const RECURSE_OBJECT = 0x02;

    public function __construct(protected readonly EscaperInterface $escaper)
    {
    }

    /**
     * Escape a value for current escaping strategy
     */
    abstract protected function escape(string $value): string;

    /**
     * Invoke this helper: escape a value
     *
     * @param int-mask-of<self::RECURSE_*> $recurse Expects one of the recursion constants;
     *                                              used to decide whether to recurse the given value when escaping
     * @throws Exception\InvalidArgumentException
     * @return mixed Given a scalar, a scalar value is returned. Given an object, with the $recurse flag not
     *               allowing object recursion, returns a string. Otherwise, returns an array.
     */
    public function __invoke(mixed $value, int $recurse = self::RECURSE_NONE): mixed
    {
        if (is_string($value)) {
            return $this->escape($value);
        }

        if (is_array($value)) {
            if (! ($recurse & self::RECURSE_ARRAY)) {
                throw new Exception\InvalidArgumentException(
                    'Array provided to Escape helper, but flags do not allow recursion',
                );
            }
            /** @psalm-var mixed $v */
            foreach ($value as $k => $v) {
                $value[$k] = $this->__invoke($v, $recurse);
            }

            return $value;
        }

        if (is_object($value)) {
            if (! ($recurse & self::RECURSE_OBJECT)) {
                // Attempt to cast it to a string
                if (method_exists($value, '__toString')) {
                    return $this->escape((string) $value);
                }
                throw new Exception\InvalidArgumentException(
                    'Object provided to Escape helper, but flags do not allow recursion',
                );
            }

            if (method_exists($value, 'toArray')) {
                trigger_error(
                    'Non-iterable objects implementing a `toArray` method will be rejected in version 4.0 '
                    . 'of laminas-view ',
                    E_USER_DEPRECATED,
                );
                return $this->__invoke($value->toArray(), $recurse | self::RECURSE_ARRAY);
            }

            return $this->__invoke((array) $value, $recurse | self::RECURSE_ARRAY);
        }

        return $value;
    }
}
