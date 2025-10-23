# Migration from Version 2 to 3

Version 3 is the first major release of `laminas-view` and includes a number of backwards incompatible changes.

## New Features

### Config Provider

A config provider is now shipped with the library that wires up the following services to factories:

- `HelperPluginManager`
- `Laminas\Escaper\Escaper`

The `ConfigProvider` also supports documentation on configuration options.

### Native Parameter & Return Types Throughout

The entire codebase has been updated with native parameter and return types, improving type safety and type inference.

## Signature Changes and Behaviour Changes

### HelperInterface no Longer Specifies any Methods

Both `getView` and `setView` have been removed from `Laminas\View\Helper\HelperInterface` leaving it with no interface methods at all.
It is only necessary to implement this interface in a custom view helper, if your view helper lacks an `__invoke` method.

### Legacy Zend-Related Service and Helper Names

All helper aliases that referred to the `Zend` equivalent of a helper or service have been removed.
Similarly, factories that previously searched for services in the container such as a Translator or Authentication Service for example, no longer check for the presence of the Zend equivalent.

### Placeholder "Container"

The [Placeholder](#placeholder) view helper has been refactored to hide its container implementation, and the [inheritance hierarchy of the container has been removed](#other-placeholder-related-classes).

Because of this, `laminas-view` no longer ships a 'Container' implementation for end users and the `Laminas\View\Helper\Placeholder\Container` class is no longer suitable for public use.
It has been made final, and, marked as `@internal` and should not be used in consumer code.

### `RendererInterface`

The method `setResolver` has been removed from `Laminas\View\Renderer\RendererInterface`.
It was irrelevant to all but the `PhpRenderer` implementation which now requires a template resolver in its constructor instead.

### `PhpRenderer`

The PhpRenderer has been refactored and a number of methods have been removed:

- `init` - Because `PhpRenderer` is now final, the `init` method has no use-case
- `setHelperPluginManager` and `getHelperPluginManager` - Now that the helper plugin manager is a required constructor dependency, the setter and getter are unnecessary
- `setResolver` and `resolver` - The template resolver is a constructor dependency and can no longer be changed at runtime.
  Retrieving the template resolver is no longer necessary as it can be found via the main DI container.
- `setCanRenderTrees(bool $flag)` and `canRenderTrees(): bool` - The `PhpRenderer` never rendered trees of view models directly so these methods were superfluous. This also means that `PhpRenderer` no longer implements [the now removed `TreeRendererInterface`](#treerendererinterface).
- All variable related setters and getters have been removed because the renderer no longer aggregates view variables at all.
  Users should provide all variables to the view either as arguments to the `render` method, or by passing a `ViewModel` object containing them.
  The removed variable related methods are:
    - `setVars`
    - `vars`
    - `get`
    - `__get`
    - `__isset`
    - `__set`
    - `__unset`
- It is no longer possible to retrieve plugins _(View Helpers)_ from the renderer instance. The methods `plugin` and `__call` have been removed.
- The `addTemplate` method has been removed. In order to render nested templates, users should render via the new `View` class.

Effectively, `PhpRenderer` can now only be used to render a single template to a string via its `render` method.
With the exception of the `setFilter` method, all other methods have been removed.

### Template Resolvers

All template resolver implementations now have parameter, property and return types.
Additionally, all implementations are now `final`.

#### `ResolverInterface`

The signature of `ResolverInterface`s only method `resolve` has changed from: `public function resolve($name, ?Renderer $renderer = null)` to `public function resolve(string $name): string|false`.

It is no longer possible or necessary to pass a renderer as the second argument and the method guarantees to return either the on-disk path to a template or `false`.

#### `AggregateResolver`

The following methods have been removed:

- `getLastSuccessfulResolver`
- `getLastLookupFailure`

The following constants have been removed:

- `AggregateResolver::FAILURE_NO_RESOLVERS`
- `AggregateResolver::FAILURE_NOT_FOUND`

The aggregate resolver can now be constructed with a list of `ResolverInterface` implementors.

#### `TemplatePathStack`

All options should now be specified via constructor arguments.

The following methods have been removed:

- `setOptions`
- `setDefaultSuffix`
- `getDefaultSuffix`
- `normalizePath`
- `setLfiProtection`
- `isLfiProtectionOn`
- `getLastLookupFailure`

The following constants have been removed:

- `TemplatePathStack::FAILURE_NO_PATHS`
- `TemplatePathStack::FAILURE_NOT_FOUND`

### `ViewModel` and `ViewModelInterface`

#### Removal of Options

Conceptually, "options" have been removed from the view model interface and its concrete implementation meaning removal of the following methods:

- `Laminas\View\Model\ViewModel::setOption()`
- `Laminas\View\Model\ViewModel::getOption()`
- `Laminas\View\Model\ViewModel::setOptions()`
- `Laminas\View\Model\ViewModel::getOptions()`
- `Laminas\View\Model\ViewModel::clearOptions()`
- `Laminas\View\Model\ModelInterface::setOption()`
- `Laminas\View\Model\ModelInterface::setOptions()`
- `Laminas\View\Model\ModelInterface::getOptions()`
- `Laminas\View\Model\ClearableModelInterface::clearOptions()`

### Helpers

#### `Asset`

Previous versions of the asset helper permitted run-time modification and retrieval of the resource map with `Laminas\View\Helper\Asset::setResourceMap()` and `Laminas\View\Helper\Asset::getResourceMap()`.
Both of these methods have been removed.
Now, the only way to configure the resource map is via constructor injection.
The method of configuring the resource map remains unchanged.

#### `Cycle`

The `Cycle` helper no longer inherits from `AbstractHelper` meaning that the `getView` and `setView` methods no longer exist.

This helper no longer implements `Iterator` which is unlikely to cause any issues because it was never intended to be iterated over, rather the methods it exposed have similarities to those in `Iterator`.

The following additional methods have been removed:

- `getName`
- `valid`

Along with adding native parameter and return types, the return type of `rewind` has changed from `self` to `void`.
The helper is now also `final`.

#### `Doctype`

The `Doctype` view helper no longer extends from `AbstractHelper` therefore the `getView` and `setView` methods no longer exist on the class.

Additionally, runtime mutation of the configured doctype has been removed, i.e. it is no longer possible to call `setDoctype` on the helper and change the doctype declaration emitted.

The only supported method of operation is to configure the doctype once in application configuration and use the helper to emit the doctype declaration in a template.

The following methods have been removed:

- `getView`
- `setView`
- `unsetDoctypeRegistry`
- `setDoctype`
- `getDoctype`
- `getDoctypes`

A new method `doctypeDeclaration` has been added that can be used to either retrieve the configured declaration, or by passing one of the doctype constants as an argument, it will return the corresponding declaration.

The default doctype has now been changed to HTML 5.

#### Escape Helpers: `escapeCss`, `escapeHtml`, `escapeHtmlAttr`, `escapeJs`, and `escapeUrl`

The methods `setEncoding()`, `getEncoding()`, `setView()`, `getView()`, `setEscaper()`, and `getEscaper()` have been removed from the escape helpers.
These helpers now have constructors that expect an [Escaper](https://docs.laminas.dev/laminas-escaper/) instance that has been configured with the encoding you expect to output in your view.

The encoding defaults to UTF-8 as it has always done but can be overridden in configuration by setting `view_manager.encoding` to your preferred value.

#### `Identity`

The deprecated runtime retrieval and modification of the underlying authentication service has been removed and the service must be injected into the helper constructor.
Specifically, the methods `Laminas\View\Helper\Identity::setAuthenticationService()` and `Laminas\View\Helper\Identity::getAuthenticationService()` have been removed.

#### `HeadTitle`

The `HeadTitle` helper's inheritance hierarchy has changed and it no longer extends from anything.
This means that a number of methods no longer exist, including, but not limited to:

- `getContainer`
- `setContainer`
- `getContainerClass`
- `setContainerClass`
- `getEscaper`
- `setEscaper`
- `getIndent`
- `getSeparator`
- `getPrefix`
- `getPostfix`
- `getView`
- `setView`
- `offsetGet|Set|Unset|Exists`
- `count` and more…

Please consult the [updated documentation](../helpers/head-title.md) for further information.

#### `HeadLink`

The `HeadLink` helper's inheritance hierarchy has changed and it no longer extends from anything.
This means that a number of methods no longer exist, including, but not limited to:

- `getContainer`
- `setContainer`
- `getContainerClass`
- `setContainerClass`
- `getEscaper`
- `setEscaper`
- `getIndent`
- `getSeparator`
- `getPrefix`
- `getPostfix`
- `getView`
- `setView`
- `offsetGet|Set|Unset|Exists`
- `count` and many more…

Please consult the [updated documentation](../helpers/head-link.md) for further information.

#### `HeadMeta`

The `HeadMeta` helper's inheritance hierarchy has changed and it no longer extends from anything.
This means that a number of methods no longer exist, including, but not limited to:

- `getContainer`
- `setContainer`
- `getContainerClass`
- `setContainerClass`
- `getEscaper`
- `setEscaper`
- `getIndent`
- `getSeparator`
- `getPrefix`
- `getPostfix`
- `getView`
- `setView`
- `offsetGet|Set|Unset|Exists`
- `count` and many more…

Please consult the [updated documentation](../helpers/head-meta.md) for further information.

#### `HeadScript` and `InlineScript`

`HeadScript` and `InlineScript` no longer inherit from another class, they have become final, and the following methods have been removed:

- `offsetSetScript`
- `offsetSetFile`
- `offsetSet`
- `createData`
- `itemToString`
- `append`
- `prepend`
- `set`
- `setView`
- `getView`
- `setAllowArbitraryAttributes`
- `arbitraryAttributesAllowed`
- Any previously inherited methods from `Placeholder\AbstractStandalone`, `IteratorAggregate`, `Countable` and `ArrayAccess`.

The helpers have been vastly simplified, allowing users to simply append, prepend or overwrite scripts via concrete methods and capture output as per the previous implementation.

#### `HeadStyle`

`HeadStyle` no longer inherit from another class, it has become final, and the following methods have been removed:

- `offsetSetStyle`
- `offsetSet`
- `createData`
- `itemToString`
- `append`
- `prepend`
- `set`
- `setView`
- `getView`
- Any previously inherited methods from `Placeholder\AbstractStandalone`, `IteratorAggregate`, `Countable` and `ArrayAccess`.

The helper has been vastly simplified, allowing users to simply append, prepend or overwrite styles via concrete methods and capture output as per the previous implementation.

#### `HtmlAttributes`

This helper no longer inherits from a base class, therefore the following methods have been removed

- `getView`
- `setView`

#### `HtmlList`

This helper no longer inherits from a base class, therefore the following methods have been removed

- `getView`
- `setView`
- `getClosingBracket`

#### `HtmlObject`

This helper no longer inherits from a base class, therefore the following methods have been removed

- `getView`
- `setView`
- `getClosingBracket`

#### `HtmlTag`

This helper no longer inherits from a base class, therefore the following methods have been removed

- `getView`
- `setView`
- `getClosingBracket`

Additionally, methods to introspect the internal attributes and flags have been removed:

- `getAttributes`
- `getUseNamespaces`

The method to enable the addition of the `xmlns` attribute for XHTML doctypes has been renamed from `setUseNamespaces` to `addXhtmlNamespace` to better describe its behaviour.

#### `Layout`

The inheritance hierarchy has been removed from this helper and the following methods have been removed:

- `getView`
- `setView`
- `getLayout`
- `setTemplate`

The layout model accessor and layout template setter were infeasible to use because retrieving the instance from a view template context, required setting the layout template with `$this->layout('some-template')`, therefore, the `getLayout` and `setTemplate` methods were inaccessible in normal usage.

#### `Partial`

This helper no longer inherits from a base class, therefore the following methods have been removed:

- `getView`
- `setView`

Additionally, the usage of non-iterable objects implementing a `toArray` method to represent the variables passed to the template is now deprecated.

#### `PartialLoop`

This helper no longer inherits from a base class, therefore the following methods have been removed:

- `getView`
- `setView`
- `loop`

Additionally, the usage of non-iterable objects implementing a `toArray` method to represent the variables passed to the template is now deprecated.

#### `Placeholder`

The inheritance hierarchy has been removed from this helper and the following methods have been removed:

- `createContainer`
- `getContainer`
- `containerExists`
- `deleteContainer`
- `clearContainers`
- `getView`
- `setView`
- Any previously inherited methods from `Placeholder\AbstractStandalone`, `IteratorAggregate`, `Countable` and `ArrayAccess`.

You can now only interact with the view helper rather than the underlying 'Container' implementation.

#### `RenderToPlaceholder`

The inheritance hierarchy has been removed from this helper and the following methods have been removed:

- `getView`
- `setView`

#### `ViewModel`

This helper no longer inherits from `AbstractHelper` so the following methods have been removed:

- `getView`
- `setView`

It can now be invoked, and as such it can be used in view scripts directly with `$this->viewModel()->getCurrent()` for example.

## Removed Features

### Stream Wrapper Functionality

In previous versions of laminas-view, it was possible to enable stream wrapper functionality in order to work around an inability to enable PHP's `short_open_tag` ini setting.
This functionality has been removed in version 3.
If you had not explicitly enabled this feature, this change will not affect your code.

### Laminas Console Integration

`Laminas\View\RendererConsoleRenderer` and `Laminas\View\Model\ConsoleModel` have been removed effectively removing all support for the deprecated `laminas-console` component.

### Laminas Feed Integration

`Laminas\View\Strategy\FeedStrategy`, `Laminas\View\Renderer\FeedRenderer` and `Laminas\View\Model\FeedModel` have been removed to be integrated into a separate library for integration between `laminas-feed` and `laminas-view`.

If you depend on the removed `laminas-feed` related classes, you will not be able to upgrade to version 3.0 until a replacement library has been published.

### Helper Plugin Manager Behaviour Changes

#### Translator "Initializers"

The plugin manager no longer attempts to automatically inject a translator into any plugins or helpers.
If you previously relied on this behaviour, you will need to instead register a factory for your custom helper that injects the translator manually.

#### Event Manager "Initializers"

The plugin manager no longer attempts to retrieve a `Laminas\EventManager` instance from the container, nor inject an event manager, shared or otherwise into helper instances.
If you have custom helpers that require access to an event manager, you should inject one by creating a custom factory for your helper.

#### View Renderer "Initializers"

The plugin manager no longer attempts to inject a `Laminas\View\Renderer\PhpRenderer` into helper instances.
If you have custom helpers that require access to the view renderer, you should inject one by creating a custom factory for your helper.

## Removed Classes and Traits

### `AbstractHelper`

The removal of `AbstractHelper` will affect anyone who has created custom view helpers by extending from this class.

It's removal means that the `getView` and `setView` methods are gone, in line with the [removal of initializers](#view-renderer-initializers) from the helper plugin manager.

If your custom view helper needs an instance of the `PhpRenderer`, you should refactor your helper to use dependency injection and inject the `PhpRenderer` into the constructor of your class, and, remove the inheritance from `AbstractHelper`.

### `AbstractHtmlElement`

This abstract base class is no longer used internally.

If you have written custom view helpers that extend from this now removed class, you will need to refactor your helpers to use composition and/or dependency injection.

### `TranslatorAwareTrait`

The `TranslatorAwareTrait` has been removed.
It encouraged setter injection and runtime retrieval of a translator instance which is no longer supported.

If you have a custom helper that requires a translator instance, you should instead inject the translator at construction time.
This can be achieved by writing a custom factory for the helper.
[Further information on writing and registering helpers](../helpers/advanced-usage.md).

### `TreeRendererInterface`

`Laminas\View\Renderer\TreeRendererInterface` has been removed.
The only implementor of this interface, `PhpRenderer`, has never rendered "trees" directly and as such this interface was superfluous.
Requirements for rendering nested view models can be extracted from the models themselves.

### Placeholder Registry

A very old and deprecated singleton registry `Laminas\View\Helper\Placeholder\Registry` has been removed.
This registry was historically used to aggregate placeholder containers and had not been used internally for some time.
Hopefully no one will notice that it's gone because there is no replacement for it.

### Other Placeholder-Related Classes

The following classes represented 'containers' used for aggregating data and had a deep inheritance hierarchy yielding a huge api surface.
Containers, where used, are no longer exposed by the helper apis so the following classes have been removed:

- `Laminas\View\Helper\Placeholder\Container\AbstractContainer`
- `Laminas\View\Helper\Placeholder\Container\AbstractStandalone`

### `Variables`

`Laminas\View\Variables` acted as an intermediary container for assigned variables and is no longer necessary.
Variables can be assigned to a `ViewModel` and passed to the View, or, an associative array can be used.
`ViewModel` can also be used to add and remove variables in the same way that `Variables` allowed.

### Removed Helpers

#### `DeclareVars`

The "DeclareVars" helper has been removed without replacement. This helper was un-documented and allowed mutation/initialisation of view variables from the view layer, which is an ill-advised strategy.

#### Flash Messenger

The flash messenger view helper is no longer present in version 3 and has been migrated to a separate package: [laminas-mvc-plugin-flashmessenger](https://docs.laminas.dev/laminas-mvc-plugin-flashmessenger/).
In order to continue to use the flash messenger in your projects, you will need to explicitly require it in your composer dependencies.

#### Flash and Quicktime

The deprecated helpers `htmlFlash` and `htmlQuicktime` have been removed.
If your project requires these helpers, you can make use of the [HtmlObject](../helpers/html-object.md) view helper to achieve the same output.

```php
echo $this->htmlObject(
    'path/to/flash.swf',
    'application/x-shockwave-flash',
    [
        'width' => 640,
        'height' => 480,
        'id' => 'long-live-flash'
    ],
    [
        'movie'   => 'path/to/flash.swf',
        'quality' => 'high'
    ],
    'Fallback Text Content'
);
```

#### Gravatar

The deprecated Gravatar view helper has been removed and replaced with a simplified version that doesn't store any state.
The replacement helper is called [GravatarImage](../helpers/gravatar-image.md) and has the following signature when accessed via view scripts:

```php
function gravatarImage(
    string $email,
    int $imageSize = 80,
    array $imageAttributes = [],
    string $defaultImage = 'mm',
    string $rating = 'g'
);
```

#### `HtmlPage`

This helper was a simple shortcut for adding HTML documents to the output as HTML `<object>` elements.
Most of the features this helper assisted with are deprecated or obsolete.

#### `Identity`

The `Identity` view helper has been removed to ensure that Laminas\View can be used standalone with minimal un-related dependencies.

Displaying the user identity in the view is specific to the authentication framework you are using, and we can't assume that `Laminas\Authentication` is in use everywhere `Laminas\View` is used.

#### Json

The deprecated Json view helper has been removed.
To encode data to Json for output in a view, you can call [`json_encode`](https://www.php.net/json_encode) directly.

If you were relying on behaviour that was previously available via `laminas-json`, for example, calling object methods `toArray` or `toJson` prior to encoding, you should make the relevant objects implement `JsonSerializable`.
You can find documentation on the `JsonSerializable` interface [on the PHP website](https://www.php.net/manual/class.jsonserializable.php).

#### Navigation

The deprecated navigation view helpers such as `Breadcrumbs`, and `Menu` etc have been removed and can now be found in [the `laminas-navigation-view` component](https://docs.laminas.dev/laminas-navigation/helpers/intro/).

As such, the namespace for these helpers has changed from `Laminas\View\Navigation` to `Laminas\Navigation\View\Helper`, so if you have referenced the FQCNs of these helpers in your code, you will need to update them accordingly.

#### RenderChildModel

The `RenderChildModel` helper never adequately rendered nested views, or took into account appending views.
It was also un-documented, and not likely used very much.
Possible use-cases were dubious at best and rendering view models can more easily be achieved by using the `Partial` view helper.

#### ServerUrl

The `ServerUrl` helper has been removed. Because this helper needs to be seeded with the current HTTP environment, its functionality is coupled to the framework you are using `Laminas\View` with, therefore, expect re-implementations of this helper in bridging libraries such as `mezzio-laminasviewrenderer`
