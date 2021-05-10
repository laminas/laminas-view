<?php

namespace Laminas\View\Helper;

use Laminas\Authentication\AuthenticationServiceInterface;
use Laminas\View\Exception;

/**
 * View helper plugin to fetch the authenticated identity.
 */
class Identity extends AbstractHelper
{
    /**
     * AuthenticationService instance
     *
     * @var AuthenticationServiceInterface
     */
    protected $authenticationService;

    /**
     * Retrieve the current identity, if any.
     *
     * If none available, returns null.
     *
     * @throws Exception\RuntimeException
     * @return mixed|null
     */
    public function __invoke()
    {
        if (! $this->authenticationService instanceof AuthenticationServiceInterface) {
            throw new Exception\RuntimeException('No AuthenticationServiceInterface instance provided');
        }

        if (! $this->authenticationService->hasIdentity()) {
            return;
        }

        return $this->authenticationService->getIdentity();
    }

    /**
     * Set AuthenticationService instance
     *
     * @param AuthenticationServiceInterface $authenticationService
     * @return Identity
     */
    public function setAuthenticationService(AuthenticationServiceInterface $authenticationService)
    {
        $this->authenticationService = $authenticationService;
        return $this;
    }

    /**
     * Get AuthenticationService instance
     *
     * @return AuthenticationServiceInterface
     */
    public function getAuthenticationService()
    {
        return $this->authenticationService;
    }
}
