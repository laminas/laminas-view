<?php

declare(strict_types=1);

namespace Laminas\View\Helper;

use Laminas\Http\Response;

use function json_encode;
use function trigger_error;

use const E_USER_DEPRECATED;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

/**
 * Helper for simplifying JSON responses
 */
class Json extends AbstractHelper
{
    /** @var Response */
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

    private function optionsToFlags(array $options = []) : int
    {
        $prettyPrint = $options['prettyPrint'] ?? false;
        $flags = JSON_THROW_ON_ERROR;
        $flags |= $prettyPrint ? 0 : JSON_PRETTY_PRINT;
        $enableExpr = $options['enableJsonExprFinder'] ?? false;
        if ($enableExpr) {
            trigger_error('Json Expression Finder options are no longer available', E_USER_DEPRECATED);
        }

        return $flags;
    }

    /**
     * Set the response object
     *
     * @return Json
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
        return $this;
    }
}
