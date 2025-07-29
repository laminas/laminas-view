# Placeholder

The `Placeholder` view helper is used to persist content between view scripts and offers useful methods for aggregating multiple string values for combined output elsewhere.

## Basic Usage

```php
// some-view-partial.phtml
if ($someCondition) {
    $this->placeholder('links')->append('<a href="#something">Something</a>');
}
$this->placeholder()->append('<a href="#always">Always</a>', 'links');
```

```php
// layout.phtml
?>
<nav>
    <a href="#otherthing">Other thing</a>
    <?= $this->placeholder('links') ?>
</nav>
```

Results in:

```html
<nav>
    <a href="#otherthing">Other thing</a>
    <a href="#something">Something</a>
    <a href="#always">Always</a>
</nav>
```

## Aggregating and Ordering Content

The placeholder view helper can aggregate content in several ways:

- `append(string $content, string|null $placeholder = null)` Adds content to the end of the list
- `prepend(string $content, string|null $placeholder = null)` Adds content at the beginning of the list
- `set(string $content, string|null $placeholder = null)` Replaces existing content with the value given
- `captureStart()` and `captureEnd` provide a way of adding content using [PHPs output buffer](https://www.php.net/manual/outcontrol.output-buffering.php). See more in [Capturing Content](#capture-content)

By default, when content is emitted, by casting the helper to a string with `<?php echo $this->placeholder('some-placeholder') ?>`, each item will be separated with a new line, or more accurately, [`PHP_EOL`](https://www.php.net/manual/reserved.constants.php#constant.php-eol).

The `setSeparator()` method can be used to modify the separator value:

```php
echo $this->placeholder('foo')
    ->append('One')
    ->prepend('Two')
    ->setSeparator('<br>');

// Two<br>One
```

## Capture Content

Occasionally you may have content for a placeholder in a view script that is easier to template; the `Placeholder` view helper allows you to capture arbitrary content for later rendering using the following API.

- `captureStart(string $name, $position)` begins capturing content.
    - `$name` is the name of the placeholder you wish to capture to. It can be omitted if you have already called the helper with the placeholder name.
    - `$position` should be an enum case of `Laminas\View\Helper\Placeholder\Position`. This value can be omitted and defaults to append.
    - `captureStart()` locks capturing until `captureEnd()` is called; you cannot
      nest capturing with the same placeholder container. Doing so will raise an
      exception.
- `captureEnd()` stops capturing content, and places it in the container object
  according to how `captureStart()` was called.

As an example:

```php
/** Default capture: append */
$this->placeholder('foo')->captureStart();
foreach ($this->data as $datum): ?>
<div class="foo">
    <h2><?= $datum->title ?></h2>
    <p><?= $datum->content ?></p>
</div>
<?php endforeach; ?>
<?php $this->placeholder('foo')->captureEnd() ?>

<?= $this->placeholder('foo') ?>
```

## Stateful Tracking of the In-Use Placeholder

The placeholder tracks the name of the most recently used placeholder name.

In this example, the calls to `append` and `prepend` do not specify the name of the placeholder because the helper is first invoked with the placeholder name:

```php
echo $this->placeholder('myName')
    ->append('Foo')
    ->prepend('Bar');
```

In most situations, it is better to be explicit about the placeholder you wish to modify, particularly if you are using several:

```php
$this->placeholder()->append('Foo', 'ph1')
    ->append('Bar', 'ph1');
    
$this->placeholder()->append('Baz', 'other');

echo $this->placeholder('ph1'); // Foo\nBar
echo $this->placeholder('other'); // Baz
```

If at any point, the placeholder name cannot be determined, an exception will be thrown, so using the second argument to explicitly state the placeholder you wish to update or emit is a good habit to get into.
