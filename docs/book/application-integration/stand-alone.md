# Stand-Alone

The view and all view-helpers of laminas-view can also be used stand-alone.

## The View

The examples uses the following directory structure:

* `public`
    * `index.php`
* `templates`
    * `index.phtml`
    * `layout.phtml`

### Basic Example

#### Setup

[Create a renderer, set a resolver for templates](../php-renderer.md#usage)
and initialize the view in `public/index.php`:

```php
// Create template resolver
$templateResolver = new Laminas\View\Resolver\TemplatePathStack([
    'script_paths' => [__DIR__ . '/../templates'],
]);

// Create the renderer
$renderer = new Laminas\View\Renderer\PhpRenderer();
$renderer->setResolver($templateResolver);

// Initialize the view
$view = new Laminas\View\View();
$view->getEventManager()->attach(
    Laminas\View\ViewEvent::EVENT_RENDERER,
    static function () use ($renderer) {
        return $renderer;
    }
);
```

#### Create View Script

[Create a view script](../view-scripts.md) in `templates/index.phtml`:

```php
<?php
/**
 * @var Laminas\View\Renderer\PhpRenderer $this
 * @var string                            $headline
 */
?>
<h1><?= $headline ?></h1>
```

#### Create View Model and render Output

Extend the script in `public/index.php` to add a [view model](../quick-start.md):

```php
$viewModel = new Laminas\View\Model\ViewModel(['headline' => 'Example']);
$viewModel->setTemplate('index');

// Set the return type to get the rendered content
$viewModel->setOption('has_parent', true);

echo $view->render($viewModel); // <h1>Example</h1>
```

<button class="btn btn-light" type="button" data-toggle="collapse"
        data-target="#full-code-basis-example"
        aria-expanded="false"
        aria-controls="full-code-basis-example">
Show full code example
</button>

<div class="collapse" id="full-code-basis-example">
```php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Create template resolver
$templateResolver = new Laminas\View\Resolver\TemplatePathStack([
    'script_paths' => [__DIR__ . '/../templates'],
]);

// Create the renderer
$renderer = new Laminas\View\Renderer\PhpRenderer();
$renderer->setResolver($templateResolver);

// Initialize the view
$view = new Laminas\View\View();
$view->getEventManager()->attach(
    Laminas\View\ViewEvent::EVENT_RENDERER,
    static function () use ($renderer) {
        return $renderer;
    }
);

// Create view model
$viewModel = new Laminas\View\Model\ViewModel(['headline' => 'Example']);
$viewModel->setTemplate('index');

// Set the return type to get the rendered content
$viewModel->setOption('has_parent', true);

// Render
echo $view->render($viewModel);
```
</div>

### Example with Layout

#### Add Layout Script

Create a new file `templates/layout.phtml` and add the following content:

```php
<?php
/**
 * @var Laminas\View\Renderer\PhpRenderer $this
 * @var string                            $content
 */
?>
<body>
<?= $content ?>
</body>
```

#### Create Layout Model and render Output

Update the script in `public/index.php` to add a view model for layout:

```php
// Create layout model
$layout = new Laminas\View\Model\ViewModel();
$layout->setTemplate('layout');

// Set the return type to get the rendered content
$layout->setOption('has_parent', true);

// Add previous view model as child
$layout->addChild($viewModel);

// Render
echo $view->render($layout);
```

<button class="btn btn-light" type="button" data-toggle="collapse"
        data-target="#full-code-example-with-layout"
        aria-expanded="false"
        aria-controls="full-code-example-with-layout">
Show full code example
</button>

<div class="collapse" id="full-code-example-with-layout">
```php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Create template resolver
$templateResolver = new Laminas\View\Resolver\TemplatePathStack([
    'script_paths' => [__DIR__ . '/../templates'],
]);

// Create the renderer
$renderer = new Laminas\View\Renderer\PhpRenderer();
$renderer->setResolver($templateResolver);

// Initialize the view
$view = new Laminas\View\View();
$view->getEventManager()->attach(
    Laminas\View\ViewEvent::EVENT_RENDERER,
    static function () use ($renderer) {
        return $renderer;
    }
);

// Create view model
$viewModel = new Laminas\View\Model\ViewModel(['headline' => 'Example']);
$viewModel->setTemplate('index');

// Create layout model
$layout = new Laminas\View\Model\ViewModel();
$layout->setTemplate('layout');

// Set the return type to get the rendered content
$layout->setOption('has_parent', true);

// Add previous view model as child
$layout->addChild($viewModel);

// Render
echo $view->render($layout);
```
</div>

## View Helpers

### Setup

Create the renderer:

```php
$renderer = new Laminas\View\Renderer\PhpRenderer();
```

### Using Helper

```php
echo $renderer->doctype(Laminas\View\Helper\Doctype::HTML5); // <!DOCTYPE html>
```
