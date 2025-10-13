# HeadScript & InlineScript

These helpers are intended to make adding `<script>` tags to a document easy and convenient.

Both helpers are identical in behaviour and methods.
The reason for 2 helpers is so that scripts you wish to emit in the `<head>` are kept separate from scripts you wish to emit in the `<body>`.

The helpers support the following methods for setting and adding scripts:

- `appendFile(string $src, array $attributes = [])`
- `prependFile(string $src, array $attributes = [])`
- `setFile(string $src, array $attributes = [])`
- `appendScript(string $content, array $attributes = [])`
- `prependScript(string $content, array $attributes = [])`
- `setScript(string $content, array $attributes = [])`

In the case of the `*File()` methods, `$src` will be a relative file path or the URL of a remote script to load.
For the `*Script()` methods, `$content` is the client-side scripting directives you wish to emit in the script tag.

`HeadScript` and `InlineScript` also allow [capturing scripts](#capturing-scripts) from the [PHP output buffer](https://www.php.net/manual/outcontrol.output-buffering.php); this can be useful if you want to create the client-side script programmatically, and then place it elsewhere.

NOTE: **Not limited to Javascript**
You are not limited to only emitting Javascript with these helpers.
Any type of content that browsers are able to interpret can be added, it is up to you however to add the correct `type` attribute so that browsers know what the content represents, for example `$this->headScript()->appendFile('/some.json', ['type' => 'application/ld+json'])`

## Basic Usage

You may specify a new script tag at any time. As noted above, these may be links to locally hosted or remote files, or, inline javascript or JSON for example.

```php
// adding scripts
$this->headScript()
    ->appendFile('/js/main.js')
    ->appendScript('const someGlobalVariable = 42;');
```

Order is often important with client-side scripting; you may need to ensure that
libraries are loaded in a specific order due to dependencies each have; use the
various `append`, `prepend`, and `offsetSet` directives to aid in this task:

```php
// Putting scripts in order

$this->headScript()->appendFile('/js/additional.js');
$this->headScript()->prependFile('/js/primary.js');
```

When you're finally ready to output all scripts in your layout script, cast the helper to a string:

```php
<?= $this->headScript() ?>
```

## Capturing Scripts

Capturing output provides an alternative method of aggregating scripts to a string suitable for output via `HeadScript` or `InlineScript` as opposed to string concatenation, `sprintf`, or [Heredocs](https://www.php.net/manual/language.types.string.php#language.types.string.syntax.heredoc) etc.

```php
<?php $this->headScript()->captureStart() ?>
const url = '<?= $this->baseUrl ?>';
const form = document.getElementById('foo-form');
form.setAttribute('action', url);
<?php $this->headScript()->captureEnd() ?>
```

The `captureStart` method accepts additional arguments:

- `$position` which is an enum to indicate whether scripts should be appended, prepended or will overwrite existing scripts.
- `$attributes` which are arbitrary HTML attributes to apply to the `<script>` tag emitted in the head or body of the document depending on the helper in use.
