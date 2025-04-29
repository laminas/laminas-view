# HeadTitle

The HTML `<title>` element is used to **provide a title for an HTML document**.
The `HeadTitle` helper allows you to aggregate, append and prepend strings at runtime for output to the document head.

It can be configured with strings to prefix or postfix the title site-wide, and can also be configured with a translator for internationalised website and applications.

## Basic Usage

Specify a title tag in a view script, e.g.
`module/Album/view/album/album/index.phtml`:

```php
$this->headTitle('My albums');
```

Render the title in the layout script, e.g.
`module/Application/view/layout/layout.phtml`

```php
<?= $this->headTitle() ?>
```

Output:

```html
<title>My albums</title>
```

### Add the Website Name

A typical usage includes the website name in the title.
Add the name and [set a
separator](#using-a-separator) in the layout script, e.g.
`module/Application/view/layout/layout.phtml`

```php
<?= $this->headTitle('Music')->setSeparator(' - ') ?>
```

Output:

```html
<title>My albums - Music</title>
```

## Set Content

The normal behaviour is to append the content to the title (container).

```php
$this->headTitle('My albums')
$this->headTitle('Music');

echo $this->headTitle(); // <title>My albumsMusic</title>
```

### Append Content

To explicitly append content, call the `append()` method of the helper:

```php
$this->headTitle()->setSeparator(' - ');
$this->headTitle()->append('My albums');
$this->headTitle()->append('Music');

echo $this->headTitle(); // <title>My albums - Music</title>
```

### Prepend Content

To prepend content, call the `prepend()` method of the helper:

```php
$this->headTitle()->setSeparator(' - ');
$this->headTitle('My albums');
$this->headTitle()->prepend('Music');

echo $this->headTitle(); // <title>Music - My albums</title>
```

### Overwrite existing Content

To overwrite all aggregated content stored inside the helper, call the `set()` method:

```php
$this->headTitle('My albums');
$this->headTitle()->set('Music');

echo $this->headTitle(); // <title>Music</title>
```

## Automatic Content Escaping

By default, all content passed to `headTitle()` is escaped during output:

```php
echo $this->headTitle('"1 & 2"'); // <title>&amp;quot;1 &amp;amp; 2&amp;quot;</title>
```

This behaviour can turned off in [configuration](#configuring-options-outside-the-view-layer) which would require you to manually escape all input passed to the `headTitle()` helper, for example:

```php
echo $this->headTitle($this->escapeHtml('"1 & 2"'));
```

… or be confident that all content provided to the helper is already correctly escaped.

Failing to escape user-supplied strings for output introduces security risks in your application.

## Using a Separator

```php
$this->headTitle()->setSeparator(' | ');
$this->headTitle('My albums');
$this->headTitle('Music');

echo $this->headTitle(); // <title>My albums | Music</title>
```

### Default Value

The default value is an empty `string` that means no extra content is added
between the titles on rendering.

## Add Prefix and Postfix

The content of the helper can be formatted with a prefix and a postfix.

```php
$this->headTitle('My albums')
    ->setPrefix('Music: ')
    ->setPostfix(' 𝄞');

echo $this->headTitle(); // <title>Music: My albums 𝄞</title>
```

Note that no separator or any other content is added between the aggregated title and the prefix or postfix;
You will need to add the correct whitespace yourself.

## Render without Tags

In case the title is needed without the `<title>` and `</title>` tags the
`renderTitle()` method can be used.

```php
echo $this->headTitle('My albums')->renderTitle(); // My albums
```

## Configuring Options Outside the View Layer

The `HeadTitle` helper has a number of options that can be configured as defaults to negate the need to call setter methods prior to output.

The following configuration snippet displays the available options with the default value for each:

```php
return [
    'view_helper_config' => [
        'head_title' => [
            'indent' => '',
            'separator' => '',
            'prefix' => '',
            'postfix' => '',
            'auto_escape' => true,
            'text_domain' => 'default',
        ],
        // …Other Helper Options…
    ],
    // …Other application configuration…
];
```

Providing the configuration is available in your DI container under the `config` identifier, the factory registered for the `HeadTitle` helper will inject those values into the constructor during initialisation.

### Option Descriptions

- `indent` - A string, typically whitespace, applied prior to the opening `<title>` tag
- `separator` - A string applied between aggregated title values
- `prefix` - A string applied prior to the desired title value
- `postfix` - A string applied after the desired title value
- `auto_escape` - A boolean value to enable or disable automatic escaping of output.
- `text_domain` - The translator text domain to use during translation of the title.

## Translation

By default, the registered factory will attempt to retrieve a `Laminas\Translator\TranslatorInterface` instance from the DI container and inject this translator into the `HeadTitle` helper during construction.

When a translator is found, each title value will be automatically translated by your configured translator.

Please consult the [`laminas-i18n` documentation](https://docs.laminas.dev/laminas-i18n/translation/) for further details.
