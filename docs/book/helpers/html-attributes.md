# HtmlAttributes

The `HtmlAttributes` helper can be used to make code related to HTML attributes
easier to write and read. The helper initializes and returns
`Laminas\View\HtmlAttributesSet` objects, which can then be manipulated and
converted to strings.

## Basic Usage

```php
<?php
$attributes = $this->htmlAttributes(['class' => 'input-group']);
if ($form->hasValidated()) {
    $attributes->add('class', 'has-validation');
}
?>

<div<?= $attributes ?>></div>
```

Output:

```html
<div class="input-group&#x20;has-validation"></div>
```

## Getting an `HtmlAttributesSet` object

To get an empty `HtmlAttributesSet` object, call the helper without any
parameters.

```php
$attributes = $this->htmlAttributes();
```

You may also set one or more attributes at the same time.

```php
$attributes = $this->htmlAttributes([
    'id' => 'login-username',
    'class' => ['input-group', 'mb-3']
]);
```

Calling the helper always creates a new object. Several `HtmlAttributesSet`
objects can be used in the same template.

## Using `HtmlAttributesSet` as an array

`HtmlAttributeSet` extends PHP's
[`ArrayObject`](https://www.php.net/manual/en/class.arrayobject.php) which
allows it to be used like an array.

### Setting an attribute

```php
$attributes['id'] = 'login-username';

$attributes['class'] = ['input-group', 'mb-3'];
```

## Setting several attributes at once

Several attributes can be set at once using the
`HtmlAttributesSet::set(iterable $attributes)` method.

```php
$attributes->set([
    'id' => 'login-username',
    'class' => ['input-group', 'mb-3']
])
```

## Adding a value to an attribute

Attribute values can added using the
`HtmlAttributesSet::add(string $name, $value)` method.

The method will set the attribute if it does not exist.

```php
<?php $attributes = $this->htmlAttributes(['class' => 'input-group']); ?>

<div<?= $attributes ?>></div>

<?php $attributes->add('class', 'has-validation'); ?>

<div<?= $attributes ?>></div>
```

Output:

```html
<div class="input-group"></div>

<div class="input-group&#x20;has-validation"></div>
```

## Merging attributes with existing attributes

Attributes and their values can be merged with existing attributes and their
values using the `HtmlAttributesSet::merge(iterable $attributes)` method.

```php
<?php
$attributes = $this->htmlAttributes(['class' => 'input-group']);
$attributes->merge([
    'id' => 'login-username',
    'class' => 'mb-3'
]);
?>

<div<?= $attributes ?>></div>
```

Output:

```html
<div id="login-username" class="input-group&#x20;mb-3"></div>
```

## Checking if a specific attribute with a specific value exists

The existence of a specific attribute with a specific value can be checked using
the `HtmlAttributesSet::hasValue(string $name, string $value)` method.

The method handles cases where the attribute does not exist or has multiple values.

```php
if ($attributes->hasValue('class', 'has-validation')) {
    // ...
}
```

## Outputting attributes

`HtmlAttributesSet` implements PHP's
[`__toString()`](https://www.php.net/manual/en/language.oop5.magic.php#object.tostring)
magic method so its instances can be printed like a string.

When an `HtmlAttributesSet` instance is converted to a string, attribute names
and values are automatically escaped using escapers from the
[EscapeHtml](https://docs.laminas.dev/laminas-view/helpers/escape/#escapehtml)
and [EscapeHtmlAttr](https://docs.laminas.dev/laminas-view/helpers/escape/#escapehtmlattr)
view helpers.

```php
<?php
$attributes = $this->htmlAttributes([
    'title' = 'faketitle onmouseover=alert(/laminas-framework/);'
]);
?>

<a<?= $attributes ?>>click</a>
```

Output:

```html
<a title='faketitle&#x20;onmouseover&#x3D;alert&#x28;&#x2F;laminas-framework&#x2F;&#x29;&#x3B;'>click</a>
```
