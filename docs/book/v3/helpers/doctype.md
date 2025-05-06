# Doctype

Valid HTML and XHTML documents should include a `DOCTYPE` declaration.
Besides being difficult to remember, these can also affect how certain elements in your document should be rendered (for instance, `CDATA` escaping in `<script>` and `<style>` elements.

The `Doctype` helper allows you to specify one of the following types:

- `XHTML11`
- `XHTML1_STRICT`
- `XHTML1_TRANSITIONAL`
- `XHTML1_FRAMESET`
- `XHTML1_RDFA`
- `XHTML1_RDFA11`
- `XHTML_BASIC1`
- `XHTML5`
- `HTML4_STRICT`
- `HTML4_LOOSE`
- `HTML4_FRAMESET`
- `HTML5`

## Basic Usage

The `Doctype` helper requires a constant indicating the desired doctype to its constructor.
Given no arguments, the default doctype of HTML 5 is used.
It can then be cast to a string in order to emit the doctype declaration:

```php
use Laminas\View\Helper\Doctype;

$helper = new Doctype();
echo (string) $helper; // <!DOCTYPE html>
```

An example of configuring the helper with a specific doctype:

```php
use Laminas\View\Helper\Doctype;

$helper = new Doctype(Doctype::HTML4_FRAMESET);
echo (string) $helper; // <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset…
```

In templates, you will typically use the helper in a layout template, for example:

```php
<?php echo $this->doctype() ?>
```

In normal usage, the desired doctype is _configured_, and other helpers query the configured doctype to customise their output.
For example, in a XHTML document, meta tags have a self-closing tag `<meta />` whereas in HTML 5, tags omit the closing slash, i.e. `<meta>`

## Usage in a Mezzio Application

The factory `Laminas\View\Helper\Service\DoctypeFactory` checks the application configuration, making it possible to define the doctype through your configuration, e.g. `config/autoload/mezzio.global.php` or a `ConfigProvider.php` in a module.

For example, add the following lines to your `config/autoload/mezzio.global.php` file to set the `Doctype` to HTML5:

```php
return [
    /* ... */
    'view_helper_config' => [
        'doctype' => \Laminas\View\Helper\Doctype::HTML5,
    ],
];
```

## Usage in a laminas-mvc Application

If you're running a [laminas-mvc](https://docs.laminas.dev/laminas-mvc/) application, you should specify doctype via the
[ViewManager](https://docs.laminas.dev/laminas-mvc/services/#viewmanager) service.

Add the following lines to your `config/autoload/global.php` file to set the `Doctype` to HTML5:

```php
return [
    /* ... */
    'view_manager' => [
        'doctype' => \Laminas\View\Helper\Doctype::HTML5,
        /* ... */
    ],
];
```

NOTE: The default doctype is HTML 5 when no configuration is specified.

## Retrieving the Doctype

Inside templates, you can retrieve the doctype declaration by either casting the doctype helper to a string, or calling `doctypeDeclaration()`

```php
$doctype = $this->doctype()->doctypeDeclaration();
```

Typically, you'll want to know if the doctype is XHTML or not; for this, the
`isXhtml()` method will suffice:

```php
if ($this->doctype()->isXhtml()) {
    // do something differently
}
```

You can also check if the doctype represents an HTML5 document.

```php
if ($this->doctype()->isHtml5()) {
    // do something differently
}
```

## Choosing a Doctype to Use with the Open Graph Protocol

To implement the [Open Graph Protocol](http://opengraphprotocol.org/), you may
specify a doctype compatible with RDFa. These doctypes allows a developer to use the
[Resource Description Framework](http://www.w3.org/TR/xhtml-rdfa-primer/) within
an HTML document.

The constants to use to indicate RDFa support are:

```php
use Laminas\View\Helper\Doctype;

$supportsRdfa = [
    Doctype::HTML5,
    Doctype::XHTML1_RDFA,
    Doctype::XHTML1_RDFA11,
    Doctype::XHTML5,
];
```

The RDFa doctype allows XHTML to validate when the 'property' meta tag attribute
is used per the Open Graph Protocol spec. Example within a view script:

```php
<?= $this->doctype('XHTML1_RDFA'); ?>
<html xmlns="http://www.w3.org/1999/xhtml"
      xmlns:og="http://opengraphprotocol.org/schema/">
<head>
   <meta property="og:type" content="musician" />
```

In the previous example, we set the property to `og:type`. The `og` references
the Open Graph namespace we specified in the html tag. The content identifies
the page as being about a musician. See the [Open Graph Protocol
documentation](http://opengraphprotocol.org/) for supported properties. The
[HeadMeta helper](head-meta.md) may be used to programmatically set these Open
Graph Protocol meta tags.

Here is how you check if the doctype is set to `XHTML1_RDFA`:

```php
<?= $this->doctype() ?>
<html xmlns="http://www.w3.org/1999/xhtml"
    <?php if ($this->doctype()->isRdfa()): ?>
      xmlns:og="http://opengraphprotocol.org/schema/"
      xmlns:fb="http://www.facebook.com/2008/fbml"
    <?php endif; ?>
>
```
