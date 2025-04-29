<?php

declare(strict_types=1);

namespace Laminas\View\Helper;

use Laminas\Authentication\AuthenticationServiceInterface;
use Laminas\View\Exception\RuntimeException;

/**
 * View helper plugin to fetch the authenticated identity.
 */
final class Identity
{
    private ?AuthenticationServiceInterface $authenticationService;

    public function __construct(?AuthenticationServiceInterface $authenticationService = null)
    {
        $this->authenticationService = $authenticationService;
    }

    /**
     * Retrieve the current identity, if any.
     *
     * If none available, returns null.
     *
     * @return mixed|null
     * @throws RuntimeException If the helper was not configured with an Authentication Service.
     */
    public function __invoke()
    {
        $service = $this->authenticationService;
        if (! $service instanceof AuthenticationServiceInterface) {
            throw new RuntimeException('No AuthenticationServiceInterface instance provided');
        }

        return $service->hasIdentity()
            ? $service->getIdentity()
            : null;
    }
}
