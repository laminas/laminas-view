# Asset

The `Asset` helper is used to translate asset names.
It could be used to prevent browser caching for assets.

## Configuration and Basic Usage

`Zend\View\Helper\Service\AssetFactory` checks the application
configuration, making it possible to set up the resource map through
your `module.config.php`. The next example will set up `Asset` helper:

```php
'view_helper_config' => [
    'asset' => [
        'resource_map' => [
            'css/style.css' => 'css/style-3a97ff4ee3.css',
            'js/vendor.js' => 'js/vendor-a507086eba.js',
        ],
    ],
],
```

Then in your view you can use:

```php
// Usable in any of your .phtml files:
echo $this->asset('css/style.css');
```

and you would receive following output:

```html
css/style-3a97ff4ee3.css
```

The first argument of the `asset` helper is the regular asset name,
which will be replaced by versioned asset name defined in `resource_map`
of the configuration.

> ### Note
>
> When `asset` key is defined but `resource_map` is not provided or is not
> an array exception `Zend\View\Exception\RuntimeException` will be
thrown.
>
> When you call `asset` helper with parameter which is not defined on your
> `resource_map` exception `Zend\View\Exception\InvalidArgumentException`
> will be thrown.

## Resource map in JSON file

If you have JSON file with resource map, for example
`rev-manifest.json`:

```javascript
{
    "css/style.css": "css/style-3a97ff4ee3.css",
    "js/vendor.js": "js/vendor-a507086eba.js"
}
```

then you can have in your configuration:

```php
'view_helper_config' => [
    'asset' => [
        'resource_map' => json_decode(file_get_contents('/path/to/rev-manifest.json'), true),
    ],
],
```

and when you have enabled cache config this file will be also cached in
compiled configuration cache, so it prevents reading the file on each
page load.
