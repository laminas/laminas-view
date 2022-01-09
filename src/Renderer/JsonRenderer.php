<?php

declare(strict_types=1);

namespace Laminas\View\Renderer;

use ArrayAccess;
use JsonException;
use JsonSerializable;
use Laminas\Stdlib\ArrayUtils;
use Laminas\View\Exception;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ModelInterface as Model;
use Laminas\View\Renderer\RendererInterface as Renderer;
use Laminas\View\Resolver\ResolverInterface as Resolver;
use Traversable;

use function array_replace_recursive;
use function get_object_vars;
use function is_object;
use function json_encode;
use function sprintf;

use const JSON_THROW_ON_ERROR;

/**
 * JSON renderer
 */
class JsonRenderer implements Renderer, TreeRendererInterface
{
    /**
     * Whether to merge child models with no capture-to value set
     *
     * @var bool
     */
    protected $mergeUnnamedChildren = false;

    /** @var Resolver|null */
    protected $resolver;

    /**
     * JSONP callback (if set, wraps the return in a function call)
     *
     * @var string
     */
    protected $jsonpCallback;

    /**
     * Return the template engine object, if any
     *
     * If using a third-party template engine, such as Smarty, patTemplate,
     * phplib, etc, return the template engine object. Useful for calling
     * methods on these objects, such as for setting filters, modifiers, etc.
     *
     * @return $this
     */
    public function getEngine()
    {
        return $this;
    }

    /**
     * Set the resolver used to map a template name to a resource the renderer may consume.
     *
     * @todo Determine use case for resolvers when rendering JSON
     * @return void
     */
    public function setResolver(Resolver $resolver)
    {
        $this->resolver = $resolver;

        return $this;
    }

    /**
     * Set flag indicating whether to merge unnamed children
     *
     * @param  bool $mergeUnnamedChildren
     * @return JsonRenderer
     */
    public function setMergeUnnamedChildren($mergeUnnamedChildren)
    {
        $this->mergeUnnamedChildren = (bool) $mergeUnnamedChildren;
        return $this;
    }

    /**
     * Set the JSONP callback function name
     *
     * @param  string $callback
     * @return JsonRenderer
     */
    public function setJsonpCallback($callback)
    {
        $callback = (string) $callback;
        if (! empty($callback)) {
            $this->jsonpCallback = $callback;
        }
        return $this;
    }

    /**
     * Returns whether or not the jsonpCallback has been set
     *
     * @return bool
     */
    public function hasJsonpCallback()
    {
        return null !== $this->jsonpCallback;
    }

    /**
     * Should we merge unnamed children?
     *
     * @return bool
     */
    public function mergeUnnamedChildren()
    {
        return $this->mergeUnnamedChildren;
    }

    /**
     * Renders values as JSON
     *
     * @todo   Determine what use case exists for accepting both $nameOrModel and $values
     * @param  string|Model $nameOrModel The script/resource process, or a view model
     * @param  null|array|ArrayAccess $values Values to use during rendering
     * @throws Exception\DomainException
     * @return string The script output.
     */
    public function render($nameOrModel, $values = null)
    {
        // use case 1: View Models
        // Serialize variables in view model
        if ($nameOrModel instanceof Model) {
            if ($nameOrModel instanceof JsonModel) {
                $children = $this->recurseModel($nameOrModel, false);
                $this->injectChildren($nameOrModel, $children);
                $output = $nameOrModel->serialize();
            } else {
                $output = $this->recurseModel($nameOrModel);
                $output = $this->jsonEncode($output);
            }

            if ($this->hasJsonpCallback()) {
                $output = $this->jsonpCallback . '(' . $output . ');';
            }
            return $output;
        }

        // use case 2: $nameOrModel is populated, $values is not
        // Serialize $nameOrModel
        if (null === $values) {
            if (! is_object($nameOrModel) || $nameOrModel instanceof JsonSerializable) {
                $return = $this->jsonEncode($nameOrModel);
            } elseif ($nameOrModel instanceof Traversable) {
                $return = $this->jsonEncode(ArrayUtils::iteratorToArray($nameOrModel));
            } else {
                $return = $this->jsonEncode(get_object_vars($nameOrModel));
            }

            if ($this->hasJsonpCallback()) {
                $return = $this->jsonpCallback . '(' . $return . ');';
            }
            return $return;
        }

        // use case 3: Both $nameOrModel and $values are populated
        throw new Exception\DomainException(sprintf(
            '%s: Do not know how to handle operation when both $nameOrModel and $values are populated',
            __METHOD__
        ));
    }

    /**
     * Can this renderer render trees of view models?
     *
     * Yes.
     *
     * @return true
     */
    public function canRenderTrees()
    {
        return true;
    }

    /**
     * Retrieve values from a model and recurse its children to build a data structure
     *
     * @param bool $mergeWithVariables Whether to merge children with the variables of the $model
     * @return (array|mixed)[]|ArrayAccess
     * @psalm-return ArrayAccess|array<array|mixed>
     */
    protected function recurseModel(Model $model, $mergeWithVariables = true)
    {
        $values = [];
        if ($mergeWithVariables) {
            $values = $model->getVariables();
        }

        if ($values instanceof Traversable) {
            $values = ArrayUtils::iteratorToArray($values);
        }

        if (! $model->hasChildren()) {
            return $values;
        }

        $mergeChildren = $this->mergeUnnamedChildren();
        foreach ($model as $child) {
            $captureTo = $child->captureTo();
            if (! $captureTo && ! $mergeChildren) {
                // We don't want to do anything with this child
                continue;
            }

            $childValues = $this->recurseModel($child);
            if ($captureTo) {
                // Capturing to a specific key
                // TODO please complete if append is true. must change old
                // value to array and append to array?
                $values[$captureTo] = $childValues;
            } elseif ($mergeChildren) {
                // Merging values with parent
                $values = array_replace_recursive($values, $childValues);
            }
        }
        return $values;
    }

    /**
     * Inject discovered child model values into parent model
     *
     * @todo detect collisions and decide whether to append and/or aggregate?
     * @param array $children
     */
    protected function injectChildren(Model $model, array $children): void
    {
        foreach ($children as $child => $value) {
            // TODO detect collisions and decide whether to append and/or aggregate?
            $model->setVariable($child, $value);
        }
    }

    private function jsonEncode($data): string
    {
        try {
            return json_encode($data, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new Exception\DomainException('Json encoding failed', $e->getCode(), $e);
        }
    }
}
