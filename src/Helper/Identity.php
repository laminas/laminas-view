<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\View\Helper;

use Laminas\Authentication\AuthenticationService;
use Laminas\View\Exception;

/**
 * View helper plugin to fetch the authenticated identity.
 */
class Identity extends AbstractHelper
{
    /**
     * @var AuthenticationService
     */
    protected $authenticationService;

    /**
     * @return AuthenticationService
     */
    public function getAuthenticationService()
    {
        return $this->authenticationService;
    }

    /**
     * @param AuthenticationService $authenticationService
     */
    public function setAuthenticationService(AuthenticationService $authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }

    /**
     * Retrieve the current identity, if any.
     *
     * If none available, returns null.
     *
     * @return mixed|null
     * @throws Exception\RuntimeException
     */
    public function __invoke()
    {
        if (!$this->authenticationService instanceof AuthenticationService){
            throw new Exception\RuntimeException('No AuthenticationService instance provided');
        }

        if (!$this->authenticationService->hasIdentity()) {
            return null;
        }
        return $this->authenticationService->getIdentity();
    }
}
