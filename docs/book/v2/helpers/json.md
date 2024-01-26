# Json

WARNING: **Deprecated**
The JSON view helper has been deprecated and will be removed in version 3.0.
There is no replacement; however, it is trivial to encode data by using PHP's built-in [`json_encode`](https://www.php.net/json_encode) function.  

When creating views that return JSON, it's important to also set the appropriate
response header.  The JSON view helper does exactly that. In addition, by
default, it disables layouts (if currently enabled), as layouts generally aren't
used with JSON responses.

The JSON helper sets the following header:

```http
Content-Type: application/json
```

Most XmlHttpRequest libraries look for this header when parsing responses to
determine how to handle the content.

## Basic Usage

```php
<?= $this->json($this->data) ?>
```

### Enabling encoding using Laminas\Json\Expr

The JSON helper accepts an array of options that will be passed to `Laminas\Json\Json::encode()` and used internally to encode data.
`Laminas\Json\Json::encode` allows the encoding of native JSON expressions using `Laminas\Json\Expr` objects.
This option is disabled by default.
To enable this option, pass a boolean `true` to the `enableJsonExprFinder` key of the options array:

```php
<?= $this->json($this->data, ['enableJsonExprFinder' => true]) ?>
```
