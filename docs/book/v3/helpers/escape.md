# Escape

The following helpers can **escape output in view scripts and defend from XSS
and related vulnerabilities**. To escape different contexts of a HTML document,
laminas-view provides the following helpers:

* [`EscapeCss`](#escapecss)
* [`EscapeHtml`](#escapehtml)
* [`EscapeHtmlAttr`](#escapehtmlattr)
* [`EscapeJs`](#escapejs)
* [`EscapeUrl`](#escapeurl)

More information to the operation and the background of security can be found
in the
[documentation of laminas-escaper](https://docs.laminas.dev/laminas-escaper/configuration/).

<!-- markdownlint-disable-next-line header-increment -->
> ### Installation Requirements
>
> The escape helpers depends on the laminas-escaper component, so be sure to have
> it installed before getting started:
>
> ```bash
> $ composer require laminas/laminas-escaper
> ```

## EscapeCss

```php
$css = <<<CSS
body {
    background-image: url('http://example.com/foo.jpg?</style><script>alert(1)</script>');
}
CSS;

echo $this->escapeCss($css);
```

Output:

```css
body\20 \7B \D \A \20 \20 \20 \20 background\2D image\3A \20 url\28 \27 http\3A \2F \2F example\2E com\2F foo\2E jpg\3F \3C \2F style\3E \3C script\3E alert\28 1\29 \3C \2F script\3E \27 \29 \3B \D \A \7D
```

## EscapeHtml

```php
$html = "<script>alert('laminas-framework')</script>";

echo $this->escapeHtml($html);
```

Output:

```html
&lt;script&gt;alert(&#039;laminas-framework&#039;)&lt;/script&gt;
```

## EscapeHtmlAttr

```php+html
<?php $html = 'faketitle onmouseover=alert(/laminas-framework/);'; ?>

<a title=<?= $this->escapeHtmlAttr($html) ?>>click</a>
```

Output:

```html
<a title=faketitle&#x20;onmouseover&#x3D;alert&#x28;&#x2F;laminas-framework&#x2F;&#x29;&#x3B;>click</a>
```

## EscapeJs

```php
$js = "window.location = 'https://getlaminas.org/?cookie=' + document.cookie";

echo $this->escapeJs($js);
```

Output:

```js
window.location\x20\x3D\x20\x27https\x3A\x2F\x2Fgetlaminas.org\x2F\x3Fcookie\x3D\x27\x20\x2B\x20document.cookie
```

## EscapeUrl

```php
<?php
$url = <<<JS
" onmouseover="alert('laminas')
JS;
?>

<a href="http://example.com/?name=<?= $this->escapeUrl($url) ?>">click</a>
```

Output:

```html
<a href="http://example.com/?name=%22%20onmouseover%3D%22alert%28%27laminas%27%29">click</a>
```

## Using Encoding

```php
$this->escapeHtml()->setEncoding('iso-8859-15');
```

All allowed encodings can be found in the
[documentation of laminas-escaper](https://docs.laminas.dev/laminas-escaper/configuration/).

### Get Current Value

To get the current value of this option, use the `getEncoding()` method.

```php
$this->escapeHtml()->setEncoding('iso-8859-15');

echo $this->escapeHtml()->getEncoding(); // iso-8859-15
```

### Default Value

The default value for all escape helpers is `utf-8`.

## Using Recursion

All escape helpers can use recursion for the given values during the escape
operation. This allows the escaping of the datatypes `array` and `object`.

### Escape an Array

To escape an `array`, the second parameter `$recurse` must be use the constant
`RECURSE_ARRAY` of an escape helper:

```php
$html = [
    'headline' => '<h1>Foo</h1>',
    'content'  => [
        'paragraph' => '<p>Bar</p>',
    ],
];

var_dump($this->escapeHtml($html, Laminas\View\Helper\EscapeHtml::RECURSE_ARRAY));
```

Output:

```php
array(2) {
  'headline' =>
  string(24) "&lt;h1&gt;Foo&lt;/h1&gt;"
  'content' =>
  array(1) {
    'paragraph' =>
    string(22) "&lt;p&gt;Bar&lt;/p&gt;"
  }
}
```

### Escape an Object

An escape helper can use the `__toString()` method of an object. No additional
parameter is needed:

```php
$object = new class {
    public function __toString() : string
    {
        return '<h1>Foo</h1>';
    }
};

echo $this->escapeHtml($object); // "&lt;h1&gt;Foo&lt;/h1&gt;"
```

An escape helper can also use the `toArray()` method of an object. The second
parameter `$recurse` must be use the constant `RECURSE_OBJECT` of an escape
helper:

```php
$object = new class {
    public function toArray() : array
    {
        return ['headline' => '<h1>Foo</h1>'];
    }
};

var_dump($this->escapeHtml($object, Laminas\View\Helper\EscapeHtml::RECURSE_OBJECT));
```

Output:

```php
array(1) {
  'headline' =>
  string(3) "&lt;h1&gt;Foo&lt;/h1&gt;"
}
```

If the object does not contains the methods `__toString()` or `toArray()` then
the object is casted to an `array`:

```php
$object = new class {
    public $headline = '<h1>Foo</h1>';
};

var_dump($this->escapeHtml($object, Laminas\View\Helper\EscapeHtml::RECURSE_OBJECT));
```

Output:

```php
array(1) {
  'headline' =>
  string(3) "&lt;h1&gt;Foo&lt;/h1&gt;"
}
```

## Using Custom Escaper

Create an own instance of `Laminas\Escaper\Escaper` and set to any of the escape
helpers:

```php
$escaper = new Laminas\Escaper\Escaper('utf-8');

$this->escapeHtml()->setEscaper($escaper);
```

### Get Current Value

To get the current value, use the `getEscaper()` method.

```php
<?php
$escaper = new Laminas\Escaper\Escaper('utf-8');
$this->escapeHtml()->setEscaper($escaper);

var_dump($this->escapeHtml()->getEscaper()); // instance of Laminas\Escaper\Escaper
```

### Default Value

The default value is an instance of `Laminas\Escaper\Escaper`, created by the
helper.
