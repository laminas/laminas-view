# Templates or "View Scripts"

As described in the [Template Resolvers documentation](template-resolvers.md), templates are typically PHP files stored in local directories.

During rendering, these template files are processed by calling PHP's built-in [`include()`](https://www.php.net/manual/function.include.php) expression from the context of an object that is prepared with only the variables you wish to make available to the template.

## Variables

Variables are assigned to the view, either by passing an associative array or a `ViewModel` to the `Laminas\View\View::render()` method.

Here's an example of how you would render the same template in each of these ways:

```php
use Laminas\View\Model\ViewModel;
use Laminas\View\View;

/** @var View $view */
$html = $view->renderTemplate('my-template', [
    'greeting' => 'Hello Fred 👋',
]);

// The above is equivalent to:

$viewModel = new ViewModel([
    'greeting' => 'Hello Fred 👋',
], 'my-template');

$html = $view->render($viewModel);
```

Inside your template, you can access variables from the local scope, or as instance properties:

```php
// my-template.phtml

echo $greeting;

// is equivalent to:

echo $this->greeting;
```

As a general rule, we recommend you use the instance property form because it is easier to differentiate between variables created within the view script scope and those assigned to it via the view model.
Additionally, if you run a static analyser such as Psalm on your templates, the instance property form can be used to improve type inference, particularly if you are creating custom view model objects.

### Strict Variables Configuration

By default, accessing an undefined variable from a template context will cause an exception.
You can disable this behaviour by explicitly setting `strict_variables` to `false`.

```php
// view-manager.global.php

return [
    'view_manager' => [
        'strict_variables' => false,
        // ... more configuration
    ],
];
```

## View Helpers

The [quick start guide](quick-start.md#making-use-of-view-helpers) should provide sufficient information on how to _use_ view helpers from within template files.
It's a good idea to look at the [list of shipped helpers](helpers/intro.md) and the individual documentation for those, and, the [chapter on writing and registering your own helpers](helpers/advanced-usage.md).

## Escaping Output

One of the most important tasks to perform in a view script is to make sure that output is escaped properly;
among other things, this helps to avoid cross-site scripting attacks.
Unless you are using a function, method, or helper that does escaping on its own, you should always escape variables when you output them and pay careful attention to applying the correct escaping strategy to each HTML context you use.

There are a [number of helpers available](helpers/escape.md) that you can use for this purpose:

- `$this->escapeHtml($value)`
- `$this->escapeHtmlAttr($value)`
- `$this->escapeJs($value)`
- `$this->escapeCss($value)`
- `$this->escapeUrl($value)`

Matching the correct helper (or combination of helpers) to the context into which you are injecting untrusted variables will ensure that you are protected against Cross-Site Scripting (XSS) vulnerabilities.

```html
<!-- bad view-script practice: -->
<h1><?= $this->variable ?></h1>

<!-- good view-script practice: -->
<h1><?= $this->escapeHtml($this->variable) ?></h1>

<!-- and remember context is always relevant! -->
<script type="text/javascript">
    const foo = '<?= $this->escapeJs($variable) ?>';
</script>
```

## IDE Auto-Completion in View Scripts

We ship a helper interface that can be used and extended to aid auto-completion in most modern IDEs and editors.
By documenting `$this` as a instance of `Laminas\View\TemplateInterface`, all the shipped view helper methods will be available as auto-completion targets:

```php
<?php
declare(strict_types=1);

use Laminas\View\TemplateInterface;

/** @var TemplateInterface $this */
?>
<?= $this->doctype() ?>
<html>
    <head>
        <?= $this->headTitle()->setPrefix('My App: ') ?>
        <?= $this->headLink() ?>
        <?= $this->headScript() ?>
        <?= $this->headStyle() ?>
    </head>
    <body>
        <?= $this->content ?>
    </body>
</html>
```

> DANGER: **Do not implement TemplateInterface**
> The shipped `TemplateInterface` is purely for documentation of view helpers, it is not intended to be used beyond static analysis and will not keep BC over time.

In your projects, it is very likely you will [write custom view helpers](helpers/advanced-usage.md), therefore you'll want these available to you too.
This can be achieved by declaring your own interface along the lines of:

```php
namespace App;

use Laminas\View\TemplateInterface;

interface AppTemplate extends TemplateInterface
{
    /**
     * Format N muppet names
     * 
     * @param positive-int $number
     * @param non-empty-string $muppetName
     * @return non-empty-string
     */
    public function customHelper(int $number, string $muppetName): string;
}
```

You can then use this interface in your view scripts to gain auto-completion for your custom helpers:

```php
<?php
declare(strict_types=1);

use App\AppTemplate;

/** @var AppTemplate $this */
?>

<h1><?= $this->customHelper(10, 'Miss Piggy') ?></h1>
```

Because interfaces can make use of multiple inheritance, this can be a good way of aggregating custom helpers registered across different modules in your app.
