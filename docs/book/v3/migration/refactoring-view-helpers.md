# Refactoring View Helpers for View 3

In version 2 of Laminas View, it was typical behaviour to retrieve plugins by alias from the view instance composed in all plugins that inherited from the now removed `AbstractHelper`.

Here's an example of a plugin that would need refactoring for version 3:

```php
use Laminas\View\AbstractHelper;

class PluginWithHiddenDependencies extends AbstractHelper
{
    public function __invoke(string $routeName, string $text): string
    {
        $urlPlugin = $this->getView()->plugin('url');
        $escapePlugin = $this->getView()->plugin('escapeHtml');
        
        return sprintf(
            '<a href="%s">%s</a>',
            $urlPlugin($routeName),
            $escapePlugin($text),
        );
    }
}
```

There are a number of problems with the class above that relied on hidden knowledge of how `AbstractHelper` worked and in turn how the plugin manager automatically injected the primary view renderer into all helpers, and also that the thing labelled 'url' or 'escapeHtml' was actually a specific type of plugin _(That may also be unexpectedly aliased to a different type)_.

That's a _lot_ of hidden complexity and indirection, and furthermore it is very difficult to test.

With the removal of automatic, runtime view renderer injection, and the removal of `AbstractHelper`, you are now forced to practice dependency injection in view helpers.

Whilst this is inconvenient now, it will pay future dividends with more maintainable, testable and readable code.

To refactor the above plugin, we would now write the following to achieve the same outcome:

```php
use Laminas\Escaper\EscaperInterface;
use Laminas\View\Helper\HelperInterface;
use Mezzio\Helper\UrlHelperInterface;

final readonly class PluginWithDependencies implements HelperInterface
{
    public function __construct(
        private UrlHelperInterface $urlHelper,
        private EscaperInterface $escaper,
    ) {
    }
    
    public function __invoke(string $routeName, string $text): string
    {
        return sprintf(
            '<a href="%s">%s</a>',
            $this->urlHelper->generate($routeName),
            $this->escaper->escapeHtml($text),
        );
    }
}
```

The above will need a factory too:

```php
use Laminas\Escaper\EscaperInterface;
use Laminas\View\HelperPluginManagerInterface;
use Mezzio\Helper\UrlHelperInterface;
use Psr\Container\ContainerInterface;

final readonly class PluginFactory
{    
    public function __invoke(ContainerInterface $container): PluginWithDependencies
    {
        $helpers = $container->get(HelperPluginManagerInterface::class);
        
        return new PluginWithDependencies(
            $helpers->get(UrlHelperInterface::class),
            $container->get(EscaperInterface::class),
        );
    }
}
```

You will already be registering custom plugins in configuration, but this configuration will need updating to reference the new factory:

```php
return [
    'view_helpers' => [
        'factories' => [
            PluginWithDependencies::class => PluginFactory::class,
        ],
        'aliases' => [
            'myPlugin' => PluginWithDependencies::class,
        ],
    ],
];
```

The refactored plugin is actually compatible with both `laminas-view` version 2 _and_ version 3.
It has always been possible to use dependency injection with `laminas-view`, therefore refactoring of plugins can happen prior to upgrading.

Hopefully this guide has shown that

- Inheriting from `AbstractHelper` is unnecessary
- Forcing the use of DI leads to more maintainable and testable code
- Static Analysers can infer more easily the types in use

## About `HelperInterface`

`HelperInterface` was changed for version 3 and no longer contains _any_ methods at all.

It is **not** _required_ that plugins implement this interface in order to be compatible with the `HelperPluginManager`, but it's worth implementing in custom plugins anyway, and the reason for that centers around discovery.

Writing factories can be a bore; An abstract factory would need some way of identifying helpers, so that they could be retrieved from the correct plugin manager:

```php
/**
 * This example is from a fictional Reflection-Based Abstract Factory
 *
 * The code might be part of a method that constructs view helpers by analysing constructor parameters 
 */
use Laminas\View\Helper\HelperInterface;use Laminas\View\HelperPluginManagerInterface;

assert($parameter instanceof ReflectionParameter);
$type = $parameter->getType();
assert($type instanceof ReflectionNamedType);

if (is_a($type->getName(), HelperInterface::class, true)) {
    // So, the parameter is a HelperInterface, fetch it from the helper plugin manager:
    $helperPlugins = $container->get(HelperPluginManagerInterface::class);
    $constructorParam = $helperPlugins->get($type->getName());
}
```
