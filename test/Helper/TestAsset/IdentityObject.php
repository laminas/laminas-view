<?php

namespace LaminasTest\View\Helper\TestAsset;

class IdentityObject
{
    /** @var string|null */
    protected $username;

    /** @var string|null */
    protected $password;

    /**
     * @param string $password
     */
    public function setPassword($password): void
    {
        $this->password = (string) $password;
    }

    /**
     * @return string|null
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $username
     */
    public function setUsername($username): void
    {
        $this->username = (string) $username;
    }

    /**
     * @return string|null
     */
    public function getUsername()
    {
        return $this->username;
    }
}
