# Migration from Version 2 to 3

Version 3 is the first major release of `laminas-view` and includes a number of backwards incompatible changes.

## Helper Removals

### Flash Messenger

The flash messenger view helper is no longer present in version 3 and has been migrated to a separate package: [laminas-mvc-plugin-flashmessenger](https://github.com/laminas/laminas-mvc-plugin-flashmessenger). In order to continue to use the flash messenger in your projects, you will need to explicitly require it in your composer dependencies.

### Obsolete View Helpers: Flash and Quicktime

The deprecated helpers `htmlFlash` and `htmlQuicktime` have been removed. If your project requires these helpers, you can make use of the [HtmlObject](helpers/html-object.md) view helper to achieve the same output.

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

## Backwards Incompatible Changes to Helpers

### Json View Helper

In previous versions of laminas-view the [Json View Helper](helpers/json.md) made use of the [laminas-json](https://github.com/laminas/laminas-json) library which enabled encoding of [JSON Expressions](https://github.com/laminas/laminas-json/blob/3.4.x/docs/book/advanced.md#json-expressions). Support for this library and the expression finder feature has been removed.
