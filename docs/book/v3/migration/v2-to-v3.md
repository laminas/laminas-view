# Migration from Version 2 to 3

Version 3 is the first major release of `laminas-view` and includes a number of backwards incompatible changes.

## New Features

## New Dependencies

## Signature Changes and Behaviour Changes

### Legacy Zend-Related Service and Helper Names

All helper aliases that referred to the `Zend` equivalent of a helper or service have been removed.
Similarly, factories that previously searched for services in the container such as a Translator or Authentication Service for example, no longer check for the presence of the Zend equivalent.

### Helpers

#### Asset Helper

Previous versions of the asset helper permitted run-time modification and retrieval of the resource map with `Laminas\View\Helper\Asset::setResourceMap()` and `Laminas\View\Helper\Asset::getResourceMap()`.
Both of these methods have been removed.
Now, the only way to configure the resource map is via constructor injection.
The method of configuring the resource map remains unchanged.

#### Identity Helper

The deprecated runtime retrieval and modification of the underlying authentication service has been removed and the service must be injected into the helper constructor.
Specifically, the methods `Laminas\View\Helper\Identity::setAuthenticationService()` and `Laminas\View\Helper\Identity::getAuthenticationService()` have been removed.

#### `HeadTitle` Helper

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

## Removed Features

### Stream Wrapper Functionality

In previous versions of laminas-view, it was possible to enable stream wrapper functionality in order to work around an inability to enable PHP's `short_open_tag` ini setting.
This functionality has been removed in version 3.
If you had not explicitly enabled this feature, this change will not affect your code.

### Laminas Console Integration

`Laminas\View\RendererConsoleRenderer` and `Laminas\View\Model\ConsoleModel` have been removed effectively removing all support for the deprecated `laminas-console` component.

### Helpers

#### Escape Helpers: `escapeCss`, `escapeHtml`, `escapeHtmlAttr`, `escapeJs`, and `escapeUrl`

The methods `setEncoding()`, `getEncoding()`, `setView()`, `getView()`, `setEscaper()`, and `getEscaper()` have been removed from the escape helpers.
These helpers now have constructors that expect an [Escaper](https://docs.laminas.dev/laminas-escaper/) instance that has been configured with the encoding you expect to output in your view.

The encoding defaults to UTF-8 as it has always done but can be overridden in configuration by setting `view_manager.encoding` to your preferred value.

### Helper Plugin Manager Behaviour Changes

#### Translator "Initializers"

The plugin manager no longer attempts to automatically inject a translator into any plugins or helpers.
If you previously relied on this behaviour, you will need to instead register a factory for your custom helper that injects the translator manually.

## Removed Class and Traits

### `TranslatorAwareTrait`

The `TranslatorAwareTrait` has been removed.
It encouraged setter injection and runtime retrieval of a translator instance which is no longer supported.

If you have a custom helper that requires a translator instance, you should instead inject the translator at construction time.
This can be achieved by writing a custom factory for the helper.
[Further information on writing and registering helpers](../helpers/advanced-usage.md).

### Removed Helpers

#### Flash Messenger

The flash messenger view helper is no longer present in version 3 and has been migrated to a separate package: [laminas-mvc-plugin-flashmessenger](https://docs.laminas.dev/laminas-mvc-plugin-flashmessenger/).
In order to continue to use the flash messenger in your projects, you will need to explicitly require it in your composer dependencies.

#### Flash and Quicktime

The deprecated helpers `htmlFlash` and `htmlQuicktime` have been removed.
If your project requires these helpers, you can make use of the [HtmlObject](helpers/html-object.md) view helper to achieve the same output.

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
The replacement helper is called [GravatarImage](helpers/gravatar-image.md) and has the following signature when accessed via view scripts:

```php
function gravatarImage(
    string $email,
    int $imageSize = 80,
    array $imageAttributes = [],
    string $defaultImage = 'mm',
    string $rating = 'g'
);
```

#### Json

The deprecated Json view helper has been removed.
To encode data to Json for output in a view, you can call [`json_encode`](https://www.php.net/json_encode) directly.

If you were relying on behaviour that was previously available via `laminas-json`, for example, calling object methods `toArray` or `toJson` prior to encoding, you should make the relevant objects implement `JsonSerializable`.
You can find documentation on the `JsonSerializable` interface [on the PHP website](https://www.php.net/manual/class.jsonserializable.php).

#### Navigation

The deprecated navigation view helpers such as `Breadcrumbs`, and `Menu` etc have been removed and can now be found in [the `laminas-navigation-view` component](https://docs.laminas.dev/laminas-navigation/helpers/intro/).

As such, the namespace for these helpers has changed from `Laminas\View\Navigation` to `Laminas\Navigation\View\Helper`, so if you have referenced the FQCNs of these helpers in your code, you will need to update them accordingly.
