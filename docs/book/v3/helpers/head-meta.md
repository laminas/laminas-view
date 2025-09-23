# HeadMeta

The HTML `<meta>` element is used to provide meta information about your HTML document, typically keywords, document character set, caching pragmas, etc.

Meta tags are typically be one of:

- `http-equiv`
- `name`
- `itemprop`
- `property`
- `charset`

Most of these types, must contain a `content` attribute, and can also have various additional attributes.

For detailed information about the `<meta>` tag and its various attributes, please consult [Mozilla's documentation](https://developer.mozilla.org/docs/Web/HTML/Reference/Elements/meta).

## Basic Usage

You can call the `headMeta()` view helper at any time from within any template to add items to the list of meta tags to render.

Here's an example for setting an SEO description and rendering the output immediately:

```php
echo $this->headMeta()
    ->appendName('description', 'This is a meta description');

// Will output: <meta content="This is a meta description" name="description">
```

You can control the order of meta tags by using either the `prepend` prefixed method names or `append` prefixed method names.
Method names prefixed with `set`, for example `setName()` or `setHttpEquiv` will first delete any meta tag with a matching name prior to appending the item to the list.

```php
echo $this->headMeta()
          ->appendName('author', 'Miss Piggy')
          ->prependName('generator', 'Laminas')
          ->setName('author', 'Kermit');

// <meta content="Laminas" name="generator">
// <meta content="Kermit" name="author">
```

Identical meta entries are ignored, so executing the following will yield only 1 entry.

```php
echo $this->headMeta()
          ->appendName('generator', 'Fozzy Bear')
          ->appendName('generator', 'Fozzy Bear');
// <meta content="Fozzy Bear" name="generator">
```

If you wished to set some client-side caching rules, you'd set `http-equiv` tags with the rules you wish to enforce:

```php
// disabling client-side cache
$this->headMeta()
    ->appendHttpEquiv('expires', 'Wed, 26 Feb 1997 08:21:57 GMT')
    ->appendHttpEquiv('pragma', 'no-cache')
    ->appendHttpEquiv('Cache-Control', 'no-cache');
```

You may wish to set the content type, character set, and language if this has not already been sent by HTTP headers.

```php
// setting content type and character set
$this->headMeta()
    ->appendHttpEquiv('Content-Type', 'text/html; charset=UTF-8')
    ->appendHttpEquiv('Content-Language', 'en-US');
```

If you are serving an HTML5 document, you should provide the character set like
this:

```php
// setting character set in HTML5
echo $this->headMeta()->setCharset('UTF-8');
// <meta charset="UTF-8">
```

As a final example, an easy way to display a transitional message before a
redirect is using a "meta refresh":

```php
// setting a meta refresh for 3 seconds to a new url:
$this->headMeta()
    ->appendHttpEquiv('Refresh', '3;URL=https://www.example.com/target.html');
```

When you're ready to place your meta tags in the layout, echo the helper:

```php
<?= $this->headMeta() ?>
```

## Usage with RDFa Compatible Doctypes

If you plan on emitting [Open Graph Protocol](http://opengraphprotocol.org/) meta tags for services such as Facebook or LinkedIn, then you _should_ be using an [RDFa](https://rdfa.info/) compatible doctype.

The `HeadMeta` helper will not prohibit you from adding `<meta property="og:whatever">` tags, but if you are _not_ serving HTML5 documents, check that your doctype is set to something that is compatible with RDFa so that your emitted markup is valid.

The methods `setProperty`, `appendProperty` and `prependProperty` provide convenient shortcuts for adding these tags:

```php
$this->headMeta()->prependProperty('og:title', 'my article title');
$this->headMeta()->appendProperty('og:type', 'article');
echo $this->headMeta();

// output is:
//   <meta property="og:title" content="my article title" />
//   <meta property="og:type" content="article" />
```

## Adding Schema.org Structured Data as Meta tags

With `setItemprop`, `appendItemprop` and `prependItemprop`, you can easily add structured data to your document as meta tags.

To learn more about structured data, start with the [documentation at schema.org](https://schema.org/docs/documents.html).

```php
$this->headMeta()->setItemprop('headline', 'My Article Headline');
$this->headMeta()->setItemprop('dateCreated', $date->format('c'));
echo $this->headMeta();

// output is:
//   <meta itemprop="headline" content="My Article Headline">
//   <meta itemprop="dateCreated" content="2018-07-12T22:19:06+00:00">
```

## Support for Arbitrary Custom Meta Tags

The `HeadMeta` helper does not validate the meta tags you configure - this is the task of a dedicated HTML validator such as [the one provided by the W3C](https://validator.w3.org/).

As such, it is possible to add meta tags with any attribute/value pairs that you desire using the `append` and `prepend` methods:

```php
$this->headMeta()->append([
    'muppet' => 'Gonzo',
    'colour' => 'Blue',
]);
$this->headMeta()->prepend([
    'muppet' => 'Kermit',
    'colour' => 'Green',
]);
echo $this->headMeta();

// <meta colour="Green" muppet="Kermit">
// <meta colour="Blue" muppet="Gonzo">
```

## Attribute Escaping & Normalisation

All attribute names are converted to lowercase and all values are escaped, it is therefore important not to provide already encoded values to the `HeadMeta` helper to avoid double encoding.

## Further Reading

- [The Doctype Helper](doctype.md) affects the output style of the meta tag and should be configured to match the meta types you intend to use
- [Mozilla Documentation on the Meta tag](https://developer.mozilla.org/docs/Web/HTML/Reference/Elements/meta)
- [Schema.org Documentation](https://schema.org/docs/documents.html) and the [`itemprop` global attribute](https://developer.mozilla.org/docs/Web/HTML/Reference/Global_attributes/itemprop)
- [Open Graph Protocol](http://opengraphprotocol.org/)
- [RDFa](https://rdfa.info/)
- [W3C Markup Validator](https://validator.w3.org/)
