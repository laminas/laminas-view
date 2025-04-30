# Escape

The following helpers can **escape output in view scripts and defend from XSS and related vulnerabilities**.

To escape different contexts of a HTML document, laminas-view provides the following helpers:

* [`EscapeCss`](#escapecss)
* [`EscapeHtml`](#escapehtml)
* [`EscapeHtmlAttr`](#escapehtmlattr)
* [`EscapeJs`](#escapejs)
* [`EscapeUrl`](#escapeurl)

More information to the operation and the background of security can be found
in the [documentation of laminas-escaper](https://docs.laminas.dev/laminas-escaper/configuration/).

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

If the object does not contain the methods `__toString()` or `toArray()` then the object is cast to an `array`:

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

## Using Custom Encoding

By default, the escape helpers will expect `utf-8` input and emit `utf-8` output.
You can change this by setting the global encoding used by the underlying escaper in your application-wide configuration.
The factory responsible for creating an escaper instance looks for array configuration under the `config` identifier within the DI container.
This config array should include the following values in order to set the desired encoding:

```php
$config = [
    'view_manager' => [
        'encoding' => 'iso-8859-1',
    ],
    // …other configuration…
];
```

A list of accepted encodings can be found in the [documentation of laminas-escaper](https://docs.laminas.dev/laminas-escaper/configuration/).
