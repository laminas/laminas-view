# Partial

The `Partial` view helper is used to render a specified template within its own
variable scope. The primary use is for reusable template fragments with which
you do not need to worry about variable name clashes.

A sibling to the `Partial`, the [`PartialLoop` view helper](partial-loop.md) allows you to pass
iterable data, and render a partial for each item.

## Basic Usage

Basic usage of partials is to render a template fragment in its own view scope.
Consider the following partial script:

```php
<?php
// partial.phtml
?>
<ul>
    <li>From: <?= $this->escapeHtml($this->from) ?></li>
    <li>Subject: <?= $this->escapeHtml($this->subject) ?></li>
</ul>
```

You would then call it from your view script using the following:

```php
<?php
// main-template.phtml
?>

<h1>The main template</h1>

<?= $this->partial('partial.phtml', [
    'from' => 'Team Framework',
    'subject' => 'view partials',
]); ?>
```

Which would then render as:

```html
<h1>The main template</h1>

<ul>
    <li>From: Team Framework</li>
    <li>Subject: view partials</li>
</ul>
```

> NOTE: **What Is a Model?**
>
> A model used with the `Partial` view helper can be one of the following:
>
> - **`ModelInterface`**: If a model implementing the `ModelInterface` is passed,
>   the view variables are set using the result of `ModelInterface::getVariables()`.
> - **`array`**: If an array is passed, it should be associative, as its key/value
>   pairs are assigned to the view with keys as view variables.
> - **`Traversable`**: In case a `Traversable` object is passed (providing it is
>   Traversable<string, mixed>), it will be converted to an array using
>   `iterator_to_array()`, and then treated as an associative array.
> - **Standard object**. Any other object will assign the results of
>   `get_object_vars()` (essentially all public properties of the object) to the
>   view object.
>
> If your model is an object, you may want to have it passed **as an object** to
> the partial script, instead of serializing it to an array of variables. You
> can do this by setting the `objectKey` property of the appropriate helper:
>
> ```php
> // Tell partial to pass objects as 'model' variable
> $view->partial()->setObjectKey('model');
>
> // Tell partial to pass objects from partialLoop as 'model' variable
> // in final partial view script:
> $view->partialLoop()->setObjectKey('model');
> ```
