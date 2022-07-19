<?php

declare(strict_types=1);

namespace Laminas\View\Model;

use JsonException;
use Laminas\Stdlib\ArrayUtils;
use Laminas\View\Exception\DomainException;
use Traversable;

use function json_encode;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

class JsonModel extends ViewModel
{
    /**
     * JSON probably won't need to be captured into a
     * a parent container by default.
     *
     * @var string|null
     */
    protected $captureTo;

    /**
     * JSONP callback (if set, wraps the return in a function call)
     *
     * @var string|null
     */
    protected $jsonpCallback;

    /**
     * JSON is usually terminal
     *
     * @var bool
     */
    protected $terminate = true;

    /**
     * Set the JSONP callback function name
     *
     * @param  string $callback
     * @return JsonModel
     */
    public function setJsonpCallback($callback)
    {
        $this->jsonpCallback = $callback;
        return $this;
    }

    /**
     * Serialize to JSON
     *
     * @return string
     */
    public function serialize()
    {
        $variables = $this->getVariables();
        if ($variables instanceof Traversable) {
            $variables = ArrayUtils::iteratorToArray($variables);
        }

        $options = (bool) $this->getOption('prettyPrint', false) ? JSON_PRETTY_PRINT : 0;

        if (null !== $this->jsonpCallback) {
            return $this->jsonpCallback . '(' . $this->jsonEncode($variables, $options) . ');';
        }
        return $this->jsonEncode($variables, $options);
    }

    /** @param mixed $data */
    private function jsonEncode($data, int $options): string
    {
        try {
            return json_encode($data, $options | JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new DomainException('Failed to encode Json', (int) $e->getCode(), $e);
        }
    }
}
