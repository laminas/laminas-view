# HtmlTag

The `HtmlTag` helper is used to **create the root of an HTML document**, the
open and close tags for the `<html>` element.

## Basic Usage

```php
<?= $this->htmlTag(['lang' => 'en'])->openTag() ?>
<!-- Some HTML -->
<?= $this->htmlTag()->closeTag() ?>
```

Output:

```html
<html lang="en">
<!-- Some HTML -->
</html>
```

## Using Attributes

It is possible to set any attributes you require on the tag, either during the initial invocation, or via the `setAttributes` method:

```php
echo $this->htmlTag()->setAttributes([
    'class' => 'no-js',
    'lang' => 'de',
])->openTag();

// <html class="no-js" lang="de">
```

You can also add further individual attributes with `setAttribute` like so:

```php
echo $this->htmlTag()->setAttribute('frog', 'kermit')->openTag();

// <html frog="kermit">
```

## Adding the XML Namespace for XHTML Documents

If you have configured the [doctype view helper](doctype.md) with an XHTML doctype, you can enable the relevant XML namespace on the HTML tag with `addXhtmlNamespace`:

```php
echo $this->htmlTag()->addXhtmlNamespace(true)->openTag();

// <html xmlns="http://www.w3.org/1999/xhtml">
```

By default, the namespace will not be added, even if the current doctype is an XHTML doctype. You must explicitly enable it with `addXhtmlNamespace(true)`.
