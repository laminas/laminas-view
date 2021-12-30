<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\TestAsset;

class IdentityObject
{
    /** @var string|null */
    protected $username;

    /** @var string|null */
    protected $password;

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }
}
