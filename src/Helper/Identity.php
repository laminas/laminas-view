<?php

declare(strict_types=1);

namespace Laminas\View\Helper;

use function method_exists;

/**
 * View helper plugin to fetch the authenticated identity.
 */
class Identity extends AbstractHelper
{
    use DeprecatedAbstractHelperHierarchyTrait;

    /** @var object|null */
    protected $authenticationService;

    public function __construct(?object $authenticationService = null)
    {
        $this->authenticationService = $authenticationService;
    }

    /**
     * Retrieve the current identity, if any.
     *
     * If none available, returns null.
     *
     * @return mixed|null
     */
    public function __invoke()
    {
        $service = $this->authenticationService;
        if (! $service) {
            return null;
        }

        if (method_exists($service, 'hasIdentity') && $service->hasIdentity() === false) {
            return null;
        }

        if (method_exists($service, 'getIdentity')) {
            return $service->getIdentity();
        }

        return null;
    }

    /**
     * Set AuthenticationService instance
     *
     * @deprecated since >= 2.20.0. The authentication service should be provided to the constructor. This method will
     *             be removed in version 3.0 of this component
     *
     * @return $this
     */
    public function setAuthenticationService(object $authenticationService)
    {
        $this->authenticationService = $authenticationService;

        return $this;
    }

    /**
     * Get AuthenticationService instance
     *
     * @deprecated since >= 2.20.0. The authentication service should be provided to the constructor. This method will
     *             be removed in version 3.0 of this component
     *
     * @return null|object
     */
    public function getAuthenticationService()
    {
        return $this->authenticationService;
    }
}
