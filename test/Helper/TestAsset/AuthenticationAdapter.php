<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

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
