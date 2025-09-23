# Template Resolvers

Before we can render templates, we need a way of converting arbitrary template names into file paths on disk that we can load and execute.
Template Resolvers perform this function.
All resolvers implement the interface `Laminas\View\Resolver\ResolverInterface`.

A number of resolvers are shipped by default

## Available Resolvers

### `TemplateMapResolver`

The Template Map resolver is the most straight-forward to understand, and, likely the most performant.
It expects an array with template names as keys and file paths as values:

```php
use Laminas\View\Resolver\TemplateMapResolver;

$resolver = new TemplateMapResolver([
    'template1' => __DIR__ . '/../templates/one.phtml',
    'template2' => __DIR__ . '/../templates/two.phtml',
]);

$path = $resolver->resolve('template1');
// $path === __DIR__ . '/../templates/one.phtml'
```

### `TemplatePathStack`

The template path stack resolver composes a list of directories to search for a template.
The template name is expected to match the relative filename of a template to one of the configured directories.

```php
use Laminas\View\Resolver\TemplatePathStack;

$resolver = new TemplatePathStack([
    'script_paths' => [
        __DIR__ . '/../templates/a',
        __DIR__ . '/../templates/b',
    ],
    'default_suffix' => 'phtml',
]);

$path = $resolver->resolve('some-name');
// $path is either:
// - __DIR__ . '/../templates/a/some-name.phtml
// - __DIR__ . '/../templates/b/some-name.phtml
// - or false if it cannot be found.
```

As a stack, the last path added to the stack is searched first.

Because the resolver has a 'default suffix', this can be omitted when resolving templates, so `example` can be used in place of `example.phtml` when `phtml` is the default file name suffix.

In order to prevent undesirable directory traversal, the resolver has an option, **enabled by default** that will refuse to resolve templates that traverse upwards through the configured directories by using `/../`.
An attempt to resolve `/../template` will cause an exception, unless the option `lfi_protection` is set to `false`.

#### Options

```php
use Laminas\View\Resolver\TemplatePathStack;
$resolver = new TemplatePathStack([
    // A list of directories to search
    'script_paths' => [
        __DIR__ . '/../templates/a',
        __DIR__ . '/../templates/b',
    ],
    // The default filename suffix
    'default_suffix' => 'phtml',
    // Prevent directory traversal during template resolution
    'lfi_protection' => true,
]);
```

### `PrefixPathStackResolver`

The prefix path stack resolver composes multiple [`TemplatePathStack`](#templatepathstack) resolvers, each with a unique prefix.

```php
use Laminas\View\Resolver\PrefixPathStackResolver;

$resolver = new PrefixPathStackResolver([
    'kermit' => [
        __DIR__ . '/templates/kermit',
        __DIR__ . '/templates/more-green-stuff',
    ],
    'gonzo' => [
        __DIR__ . '/templates/gonzo',
        __DIR__ . '/templates/purple-things',
    ],
], 'phtml');

$path = $resolver->resolve('gonzo/example');

// $path is now one of
// - './templates/gonzo/example.html',
// - './templates/purple-things/example.html',
// - false,
```

### `RelativeFallbackResolver`

This resolver consumes an existing resolver and consults the runtime template being rendered.

Assuming the current template being rendered is `./templates/example/one.phtml` and from the context of this template, you are attempting to render the partial `partials/partial`, it will resolve that request to `./templates/example/partials/partial` before passing it to the composed resolver to resolve.

### `AggregateResolver`

The aggregate resolver accepts a list of resolver instances and queries each one in turn until a resolver is capable of resolving the requested template.

```php

use Laminas\View\Resolver\AggregateResolver;
use Laminas\View\Resolver\TemplateMapResolver;
use Laminas\View\Resolver\TemplatePathStack;

$resolver = new AggregateResolver([
    new TemplateMapResolver([
        'layout/default' => __DIR__ '/templates/layout/default.phtml',
    ]),
    new TemplatePathStack([
        'script_paths' => [
            __DIR__ . '/templates/example1',
            __DIR__ . '/templates/example2',
        ],
    ]),
]);
```

## Defaults for `Laminas\ServiceManager` Based Applications

By default, an `AggregateResolver` is used when `laminas-view` is used in an application making use of `laminas-servicemanager`.
It will be composed with, in order:

- `TemplateMapResolver`
- `TemplatePathStack`
- `PrefixPathStack`
- `RelativeFallbackResolver` with the map resolver as the 'parent'
- `RelativeFallbackResolver` with the stack resolver as the 'parent'
- `RelativeFallbackResolver` with the prefix resolver as the 'parent'

It is therefore possible to _configure_ your templates using standard configuration keys and then retrieve it from the DI container if you need to access the instance:

```php
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Resolver\ResolverInterface;

assert($container instanceof ServiceManager);

$resolver = $container->get(ResolverInterface::class);
$resolver->resolve('some/template');
```

### Example Configuration for Template Paths and Files

```php
// config/autoload/templates.global.php

return [
    'view_manager' => [
        /**
         * Explicitly mapped template files:
         */
        'template_map' => [
            'layout/default' => __DIR__ . '/../templates/layouts/default-layout.phtml',
            'pages/something' => __DIR__ . '/../templates/pages/something.phtml',
        ],
        
        /**
         * Directories for the template path stack resolver to search 
         */
        'template_path_stack' => [
            __DIR__ . '/templates/emails',
            __DIR__ . '/templates/pages',
        ],
        
        /**
         * Directories for the `PrefixPathStackResolver` to search
         */
        'prefix_template_path_stack' => [
            'email' => __DIR__ . '/email-templates/',
            'layout' => [
                __DIR__ . '/templates/layouts',
                __DIR__ . '/templates/more-layouts',
            ],
        ],
        
        /**
         * Default suffix applies to all relevant resolvers 
         */
        'default_template_suffix' => 'phtml',
    ],
];
```

## Writing Custom Resolvers

Custom resolvers need only implement `ResolverInterface`:

```php
namespace Laminas\View\Resolver;

interface ResolverInterface
{
    /**
     * Resolve a template/pattern name to a resource the renderer can consume
     *
     * @param non-empty-string $name
     * @return non-empty-string|false
     */
    public function resolve(string $name): string|false;
}
```

The `resolve` method _must_ return an on-disk file path that can be used with PHP's built-in `include` function, therefore any non-disk based template solution would need to fetch template contents and write them to a temporary file, likely with some kind of caching strategy.
