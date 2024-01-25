# Json

This view helper simply encodes its argument to JSON.

## Basic Usage

```php
<?= $this->json(['example' => 'payload']) ?>
```

The helper accepts a second argument `$jsonOptions`, an associative array with one possible key: `prettyPrint`.
Providing the value `['prettyPrint' => true]` will pretty-print the encoded data.

```php
<?= $this->json($data, ['prettyPrint' => true]) ?>
```
