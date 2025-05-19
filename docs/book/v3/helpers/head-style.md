# HeadStyle

The HTML `<style>` element is used to include inline CSS in the HTML `<head>` element.

<!-- markdownlint-disable-next-line heading-increment -->
> ### Use HeadLink to link CSS files
>
> [HeadLink](head-link.md) should be used to create `<link>` elements for
> including external stylesheets. `HeadStyle` is used when you wish to define
> your stylesheets inline.

The `HeadStyle` helper supports the following methods for setting and adding CSS declarations:

- `appendStyle(string $content, array $attributes = [])`
- `prependStyle(string $content, array $attributes = [])`
- `setStyle(string $content, array $attributes = [])`

In all cases, `$content` is the actual CSS declarations. `$attributes` are any
additional attributes you wish to provide to the `style` tag such as `lang`, `title`, `media`, etc.

`HeadStyle` also allows [capturing style declarations](#capturing-style-declarations) from the output buffer; this can be useful if you want to create the declarations programmatically, and then place them elsewhere.

## Basic Usage

You may specify a new style tag at any time:

```php
// adding styles
$styles = <<<'CSS'
a {
    color: pink;
    text-decoration: underline;
}
CSS;

$this->headStyle()->appendStyle($styles);
```

As you probably know, source order of CSS declarations is important with later declarations overriding earlier declarations.

```php
// Putting styles in order

// place at end:
$this->headStyle()->appendStyle($finalStyles);

// place at beginning
$this->headStyle()->prependStyle($firstStyles);
```

When you're finally ready to output all style declarations in your layout
script, echo the helper:

```php
<?= $this->headStyle() ?>
```

## Capturing Style Declarations

Sometimes you need to generate CSS style declarations programmatically. While
you could use string concatenation, heredocs, and the like, often it's easier
just to do so by creating the styles and sprinkling in PHP tags. `HeadStyle`
lets you do just that, capturing it to the stack:

```php
<?php $this->headStyle()->captureStart() ?>
body {
    background-color: <?= $this->bgColor ?>;
}
<?php $this->headStyle()->captureEnd() ?>
```

The `captureStart` method accepts additional arguments:

- `$position` which is an enum to indicate whether styles should be appended, prepended or will overwrite existing styles.
- `$attributes` which are arbitrary HTML attributes to apply to the `<style>` tag emitted in the head of the document.
