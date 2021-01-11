# HtmlAttributes

The `HtmlAttributes` helper is used to create `HtmlAttributesSet` objects.

## Basic Usage

```php
$exampleAttributes = $this->htmlAttributes();
$exampleAttributes->add('class', 'example');
<div<?= $exampleAttributes ?>>
```

Output:

```html
<div class="example">
```
