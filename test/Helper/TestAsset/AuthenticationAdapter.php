<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\TestAsset;

use Laminas\Authentication\Adapter\AdapterInterface;
use Laminas\Authentication\Result;

class AuthenticationAdapter implements AdapterInterface
{
    /** @var IdentityObject|null */
    protected $identity;

    public function setIdentity(IdentityObject $identity): void
    {
        $this->identity = $identity;
    }

    public function authenticate(): Result
    {
        return new Result(Result::SUCCESS, $this->identity);
    }
}
