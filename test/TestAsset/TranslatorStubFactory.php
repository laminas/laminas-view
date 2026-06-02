<?php

declare(strict_types=1);

namespace LaminasTest\View\TestAsset;

use Closure;
use Laminas\Translator\TranslatorInterface;
use ReflectionClass;
use ReflectionParameter;

use function assert;

final readonly class TranslatorStubFactory
{
    /** @param (Closure(string):string)|null $messageMatcher */
    public function getTranslator(Closure|null $messageMatcher = null): TranslatorInterface
    {
        $messageMatcher ??= fn (string $message): string => $message;
        $reflection       = new ReflectionClass(TranslatorInterface::class);
        $method           = $reflection->getMethod('translate');
        $parameter        = $method->getParameters()[0] ?? null;
        assert($parameter instanceof ReflectionParameter);

        if (! $parameter->hasType()) {
            return $this->v1Stub($messageMatcher);
        }

        return $this->v2Stub($messageMatcher);
    }

    /** @param Closure(string):string $messageMatcher */
    private function v1Stub(Closure $messageMatcher): TranslatorInterface
    {
        /** @psalm-suppress MissingParamType,MixedArgument,MethodSignatureMismatch,UnusedPsalmSuppress */
        return new class ($messageMatcher) implements TranslatorInterface
        {
            /** @param Closure(string):string $messageMatcher */
            public function __construct(private readonly Closure $messageMatcher)
            {
            }

            /**
             * @inheritDoc
             * @psalm-suppress MethodSignatureMismatch
             */
            public function translate($message, $textDomain = 'default', $locale = null)
            {
                return ($this->messageMatcher)($message);
            }

            /**
             * @inheritDoc
             * @psalm-suppress MethodSignatureMismatch
             */
            public function translatePlural($singular, $plural, $number, $textDomain = 'default', $locale = null)
            {
                return ($this->messageMatcher)($singular);
            }
        };
    }

    /** @param Closure(string):string $messageMatcher */
    private function v2Stub(Closure $messageMatcher): TranslatorInterface
    {
        return new class ($messageMatcher) implements TranslatorInterface
        {
            /** @param Closure(string):string $messageMatcher */
            public function __construct(private readonly Closure $messageMatcher)
            {
            }

            /** @inheritDoc */
            public function translate(
                string $message,
                string $textDomain = 'default',
                string|null $locale = null,
            ): string {
                return ($this->messageMatcher)($message);
            }

            /** @inheritDoc */
            public function translatePlural(
                string $singular,
                string $plural,
                int $number,
                string $textDomain = 'default',
                string|null $locale = null,
            ): string {
                return ($this->messageMatcher)($singular);
            }
        };
    }
}
