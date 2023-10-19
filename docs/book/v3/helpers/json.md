# Json

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
