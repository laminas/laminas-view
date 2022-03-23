<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\TestAsset;

use Laminas\Authentication\AuthenticationServiceInterface;
use Laminas\Authentication\Result;

final class AuthenticationServiceStub implements AuthenticationServiceInterface
{
    private ?string $id;

    public function __construct(?string $id)
    {
        $this->id = $id;
    }

    public function hasIdentity(): bool
    {
        return $this->id !== null;
    }

    public function getIdentity(): ?string
    {
        return $this->id;
    }

    public function authenticate(): Result
    {
        return new Result(0, $this->id);
    }

    public function clearIdentity(): void
    {
        $this->id = null;
    }
}
