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

#### Json View Helper

In previous versions of laminas-view the [Json View Helper](helpers/json.md) made use of the [laminas-json](https://docs.laminas.dev/laminas-json/) library which enabled encoding of [JSON Expressions](https://docs.laminas.dev/laminas-json/advanced/#json-expressions).
Support for this library and the expression finder feature has been removed.

## Removed Class and Traits

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
