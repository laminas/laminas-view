# The PhpRenderer for Direct Template Rendering

`Laminas\View\Renderer\PhpRenderer` is responsible for rendering individual templates.
It composes a **View Helper Plugin Manager**, a **Template Resolver**, and optionally an arbitrary filter with which to perform [post-rendering mutations](#filter-and-mutate-content-post-render) on the output.

You will normally use `Laminas\View\View` for rendering as detailed in the [quick start](quick-start.md#rendering-a-template).
The `PhpRenderer` is 'lower level', for example, the [Partial view helper](helpers/partial.md) is effectively a simple wrapper around the `PhpRenderer`.

## Usage

Basic usage consists of obtaining an instance of the `PhpRenderer` and then calling its `render()` method.

The `PhpRenderer` has constructor dependencies that, in an application using [`laminas-servicemanager`](https://docs.laminas.dev/laminas-servicemanager/) will already be resolved.

### Fetch an Instance from `Laminas\ServiceManager`

```php
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Renderer\PhpRenderer;

assert($container instanceof ServiceManager);

$renderer = $container->get(PhpRenderer::class);

$markup = $renderer->render('some-template-name', ['greeting' => 'Hi There']);
```

### Instantiate a Renderer

```php
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\TemplateMapResolver;

// Instantiate a View Helper Plugin Manager
$pluginManager = new HelperPluginManager(new ServiceManager());

// Create a Template Resolver
$resolver = new TemplateMapResolver([
    'some-template' => __DIR__ . '/path/to/a/template.phtml',
]);

// Decide whether to use "Strict Variables" or not
$strictVariables = true;

// Instantiate the Renderer
$renderer = new PhpRenderer(
    $pluginManager,
    $resolver,
    $strictVariables,
);

// Render a template
$markup = $renderer->render('some-template', ['greeting' => 'Hi There']);
```

`laminas-view` ships with several types of "template resolvers", which are used to resolve a template name to a resource a renderer can consume.
[Template resolvers are described in depth here](./template-resolvers.md).

> WARNING: **PhpRenderer Does Not Render Nested Models**
> The PhpRenderer's sole responsibility is to render a _single_ template.
> It cannot render nested templates in the way that the main `\Laminas\View\View` class does in order to produce complex layouts and view hierarchies.

## Filter and Mutate Content Post-Render

Sometimes it is necessary to post-process rendered markup as a string.
PhpRenderer allows this by accepting a 'Filter' via its `setFilter()` method.
The filter can be any callable that satisfies the signature `callable(string): string`.
By way of a trivial example:

```php
use Laminas\View\Renderer\PhpRenderer;use Psr\Container\ContainerInterface;

assert($container instanceof ContainerInterface);

// Retrieve the PhpRenderer instance
$renderer = $container->get(PhpRenderer::class);

// Attach a filter
$renderer->setFilter(static function (string $markup): string {
    return strrev($markup);
});

// Render Some content:
$markup = $renderer->render('template-name', ['name' => 'Fred']);

// Assuming the template content is '<p>Hello <?= $this->name ?\></p>'
echo $markup;
// Outputs '>p/<derF olleH>p<'

```
