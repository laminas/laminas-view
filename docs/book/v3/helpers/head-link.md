# HeadLink

The [HTML `<link>` element](https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Elements/link) is used for linking a variety of resources for your site: stylesheets, preloads, feeds, favicons, trackbacks, and more.

The `HeadLink` helper provides a straight forward interface for creating and aggregating these elements for later retrieval and output in your layout script.

## Basic Usage

The `HeadLink` helper has special methods for adding stylesheet links to its
stack:

- `appendStylesheet(string $href, array $attributes = [])`
- `prependStylesheet(string $href, array $attributes = [])`
- `setStylesheet(string $href, array $attributes = [])`

Using any of these 3 methods will add a link with at least the attributes `rel="stylesheet"`, `type="text/css"` and of course the `href` set to the value of the first argument.

You can provide additional arguments to `$attributes` for any other relevant attributes such as `id`, `media` et al.

For example:

```php
$this->headLink()->appendStylesheet('mobile.css', [
    'media' => 'screen and (max-width: 500px)',
    'integrity' => 'some-hash',
]);

echo $this->headLink();
// <link rel="stylesheet" href="mobile.css" type="text/css" media="screen and (max-width: 500px)" integrity="some-hash">
```

The stylesheet convenience methods automatically add the arguably unnecessary `type` attribute.
Additionally, these methods prevent you from setting a custom `rel` attribute such as `rel="alternate stylesheet"`.
To have complete control over the attributes of the aggregated links, you can use the following methods:

- `append(array $attributes)`
- `prepend(array $attributes)`
- `set(array $attributes)`

WARNING: These methods **will not validate your attributes**, therefore, they will not complain if you omit the `rel` attribute for example.

```php
echo $this->headLink()->append([
    'rel' => 'preload',
    'as' => 'font',
    'href' => '/my-font.woff2',
])->append([
    'rel' => 'stylesheet',
    'href' => 'styles.css',
])->append([
    'rel' => 'icon',
    'href' => '/some-icon.png',
    'type' => 'image/png',
    'sizes' => '16x16',
]);
```

The expected output from the above PHP code would be:

```html
<link rel="preload" as="font" href="/my-font.woff2">
<link rel="stylesheet" href="styles.css">
<link rel="icon" href="/some-icon.png" type="image/png" sizes="16x16">
```

## Why Not Use Plain Markup?

In certain situations, it makes more sense to simply add icons _(for example)_ as plain markup in a layout template.
The primary use-case for this helper is to aggregate links in your layout template from unknown page-specific content.

For example, let assume that a specific page renders a large hero image that you wish to preload to improve your [LCP score](https://web.dev/articles/optimize-lcp).
The layout cannot possibly know which image to preload, therefore, inside your content template, adding `$this->headLink()->append(['rel' => 'preload', 'as' => 'image', 'href' => '/some-image.jpg')` will add the preload link to the list of links to output in the layout `<head>` section.

## Ordering Output

Internally, the aggregated links are a simple array.
Methods that _prepend_ links will insert the item as the first element, whereas _append_ methods will add the items to the end of the list.
Methods that _set_ items, **first clear the list** prior to appending the given item.

Because of this, you should consider the order that templates are rendered in. In a classic 2-step view, the page content is rendered first, and then assigned to a variable to be rendered in the layout second.
Therefore, it makes sense to adopt the convention of _appending_ links when adding page-specific head links and _prepending_ links that are "global" in nature.
The source order of links is not always important, but it is for stylesheets of course.

## Tweaking Markup

NOTE: Link attributes are always escaped so avoid adding already encoded attributes to prevent double escaping issues.

There are 2 more methods available to improve the whitespace presentation of links:

- `setIndent`
- `setSeparator`

By default, the indent is an empty string (`''`) and the separator in a newline (`PHP_EOL`).
Changing the values of these has a predictable outcome:

```php
<?php
echo $this->headLink()
    ->setSeparator("\n\n")
    ->setIndent("\t")
    ->appendStylesheet('foo.css')
    ->appendStylesheet('bar.css');
?>
    <link href="foo.css" rel="etc...">

    <link href="bar.css" rel="etc...">
```

## Doctype Aware

The `HeadLink` helper composes the [`Doctype` view helper](doctype.md) internally to alter the output for XML based doctypes.
When you have configured the doctype to `XHTML 1.1` for example, the closing tag of the rendered links will become a self-closing `<link />` as opposed to `<link>`.

## Further Reading

- [Mozilla's MDN Web Docs on the `<link>` element](https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Elements/link)
- [MDN Docs on the `rel` attribute](https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Attributes/rel)
