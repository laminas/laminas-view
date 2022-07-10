<?php

declare(strict_types=1);

namespace Laminas\View\Helper;

use Laminas\Http\Response;

use function json_encode;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

/**
 * Helper for simplifying JSON responses
 *
 * @psalm-suppress DeprecatedProperty
 * @final
 */
class Json extends AbstractHelper
{
    use DeprecatedAbstractHelperHierarchyTrait;

    /**
     * @deprecated since >= 2.20.0
     *
     * @var Response|null
     */
    protected $response;

    /**
     * Encode data as JSON and set response header
     *
     * @param  mixed $data
     * @param  array $jsonOptions Options to pass to JsonFormatter::encode()
     * @return string|void
     */
    public function __invoke($data, array $jsonOptions = [])
    {
        $data = json_encode($data, $this->optionsToFlags($jsonOptions));

        if ($this->response instanceof Response) {
            $headers = $this->response->getHeaders();
            $headers->addHeaderLine('Content-Type', 'application/json');
        }

        return $data;
    }

    /** @param array{prettyPrint?: bool} $options */
    private function optionsToFlags(array $options = []): int
    {
        $prettyPrint = $options['prettyPrint'] ?? false;
        $flags       = JSON_THROW_ON_ERROR;
        $flags      |= $prettyPrint ? 0 : JSON_PRETTY_PRINT;

        return $flags;
    }

    /**
     * Set the response object
     *
     * @deprecated since >= 2.20.0. If you need to set response headers, use the methods available in
     *             the framework. For example in Laminas MVC this can be achieved in the controller or in
     *             Mezzio, you can change response headers in Middleware. This method will be removed in 3.0
     *             without replacement functionality.
     *
     * @return Json
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
        return $this;
    }
}
