<?php

namespace LaminasTest\View\Helper\TestAsset;

use Laminas\Authentication\Adapter\AdapterInterface;
use Laminas\Authentication\Result;

class AuthenticationAdapter implements AdapterInterface
{
    protected $identity;

    public function setIdentity($identity): void
    {
        $this->identity = $identity;
    }

    public function authenticate()
    {
        return new Result(Result::SUCCESS, $this->identity);
    }
}
