# PartialLoop

The `PartialLoop` helper is similar to the [`Partial` helper](partial.md), but makes it easy to render the same partial _(template)_ for each member of an iterable.
This eases display of large blocks of repeated content or complex display logic into a single location.
Bear in mind the performance implications of rendering partials for each iteration.
It will always be quicker to iterate over the result set directly.

Let's assume the following partial view script:

```php
<?php
// partial-loop.phtml
?>
    <dt><?= $this->key ?></dt>
    <dd><?= $this->value ?></dd>
```

And the following "model":

```php
$model = [
    ['key' => 'Mammal', 'value' => 'Camel'],
    ['key' => 'Bird', 'value' => 'Penguin'],
    ['key' => 'Reptile', 'value' => 'Asp'],
    ['key' => 'Fish', 'value' => 'Flounder'],
];
```

In your view script, you can then invoke the `PartialLoop` helper:

```php
<dl>
<?= $this->partialLoop('partial-loop.phtml', $model) ?>
</dl>
```

Resulting in the following:

```html
<dl>
    <dt>Mammal</dt>
    <dd>Camel</dd>

    <dt>Bird</dt>
    <dd>Penguin</dd>

    <dt>Reptile</dt>
    <dd>Asp</dd>

    <dt>Fish</dt>
    <dd>Flounder</dd>
</dl>
```

## The PartialLoop Counter

The `PartialLoop` view helper gives access to the current position of the iterable within the view script via `$this->partialLoop()->getPartialCounter()`.
This provides a way to have alternating colors on table rows, for example.

Continuing from our previous example:

```php
<?php
// partial-loop.phtml

use Laminas\View\TemplateInterface;
/** @var TemplateInterface $this */

$index = $this->partialLoop()->getPartialCounter();
$attribute = 1&$index === 1
    ? 'class="odd"'
    : 'class="even"';
?>
    <dt <?= $class ?>><?= $this->key ?></dt>
    <dd <?= $class ?>><?= $this->value ?></dd>
```

Resulting in the following:

```html
<dl>
    <dt class="even">Mammal</dt>
    <dd class="even">Camel</dd>

    <dt class="odd">Bird</dt>
    <dd class="odd">Penguin</dd>

    <dt class="even">Reptile</dt>
    <dd class="even">Asp</dd>

    <dt class="odd">Fish</dt>
    <dd class="odd">Flounder</dd>
</dl>
```
