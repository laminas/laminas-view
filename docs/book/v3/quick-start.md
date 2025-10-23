# Quick Start

`laminas-view` provides a "View" layer for PHP applications.

Its main purpose is to resolve and render template files for output, typically in a web application, but its use is not constrained to websites alone.

Templates are interpreted directly by PHP, therefore all the PHP language is available, however, a plugin system designed to provide "View Helpers" within templates can be used to keep domain logic out of templates; this makes laminas-view inherently flexible, but expects you to maintain some discipline with regard to what you should and shouldn't do in a template context.

The components of the view layer are as follows:

- **Template Resolvers** utilize Resolver Strategies to resolve a template name to a
  file on disk that a _Renderer_ may consume. As an example, a Resolver may take the name `'blog/entry'` and resolve it to the local file `./views/blog/entry.phtml`.
- **View Models** are objects that contain the necessary variables to use when rendering a template, and often keep a reference to the template that the model should be rendered with. View Models can be nested allowing for modular layouts and template rendering.
- **Renderers** are responsible for receiving variables and template names, or View Models, and rendering a single template into the desired output _(Typically HTML)_.
- **View Helpers** are plugins that provide useful functions within templates, for example, the `HeadTitle` view helper can be used to aggregate and output the HTML `<title>` element: `<?php echo $this->headTitle('Some Great Website') ?>`
- The **View** is similar to a _Renderer_ and is what most applications will interact with to perform rendering. It is capable of rendering nested templates and as such, it is how layouts are handled.

`laminas-view` makes use of dependency injection and ships configuration for  [laminas-servicemanager](https://docs.laminas.dev/laminas-servicemanager/).
Using this library with other dependency injection containers is perfectly possible but out of scope for a quick start, so the rest of this manual makes the assumption that [laminas-servicemanager](https://docs.laminas.dev/laminas-servicemanager/) is in use.

## Configuration

The shipped `ConfigProvider` _(for use with [laminas-config-aggregator](https://docs.laminas.dev/laminas-config-aggregator/))_ will wire up all the services required for operation, but it is necessary to configure template paths at a bare minimum.

The following example configuration details the critical options for configuring templates:

```php
return [
    'view_manager' => [
        /**
         * The Strict Variables option defines whether access to an undefined
         * variable inside a template causes an exception, or simply resolves
         * to `null`. The default is `true`  
         */
        'strict_variables' => true,
        
        /**
         * It is common for a single layout file to be used across the vast
         * majority of an application. You can define this layout template here.
         * By default, this value is null 
         */
        'default_layout' => 'layout/default',
        
        /**
         * Escapers, and a number of other view helpers need to know the encoding
         * used for output. The default is 'utf-8' which is correct for many
         * situations, however you can change the value here 
         */
        'encoding' => 'utf-8',
        
        /**
         * The template map is the fastest way to resolve templates and
         * is a key-value map of template name to file name.
         * The "Map Resolver" does not attempt to guess the filename extension,
         * so values must point exactly to on-disk files. 
         */
        'template_map' => [
            'layout/default' => __DIR__ . '/../templates/layouts/default-layout.phtml',
            'pages/something' => __DIR__ . '/../templates/pages/something.phtml',
        ],
        
        /**
         * The TemplatePathStack takes a list of directories. Directories
         * are then searched in LIFO order (it's a stack) for the requested
         * template. This is a nice solution for rapid application
         * development, but potentially introduces performance expense in
         * production due to the number of static calls necessary.
         *
         * The following defines 2 directories to search, and when asked for
         * 'my-view', first we'd look for `templates/pages/my-view.phtml` and
         * if that is not found, we'd look for `templates/emails/my-view.phtml`. 
         */
        'template_path_stack' => [
            __DIR__ . '/templates/emails',
            __DIR__ . '/templates/pages',
        ],
        
        /**
         * The `PrefixPathStackResolver` allows you to define directories to
         * search by prefix. This is helpful for establishing naming conventions
         * without explicitly mapping the files.
         * In the example below 'layout/default', will search the 'layout' prefixed
         * directories for `default.phtml`.
         * Values can be either a single directory, or a list of directories. 
         */
        'prefix_template_path_stack' => [
            'email' => __DIR__ . '/email-templates/',
            'layout' => [
                __DIR__ . '/templates/layouts',
                __DIR__ . '/templates/more-layouts',
            ],
        ],
        
        /**
         * When templates are resolved to files, this is the filename extension
         * that will be used to match the correct file on disk.
         * The default is `phtml`, but you can configure anything you wish.
         */
        'default_template_suffix' => 'phtml',
    ],
];
```

## Rendering a Template

Once you have some templates configured, you will be able to render them by using the `Laminas\View\View` service.

The following example shows a [PSR-15](https://www.php-fig.org/psr/psr-15/) request handler returning an HTML response after rendering a template.

```php
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\View\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ExamplePageHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly View $view,
    ) {
    }
    
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $variables = [
            'greeting' => 'Hello',
            'name' => 'World',
        ];
        $markup = $this->view->renderTemplate('pages/greeting', $variables);
        
        return new HtmlResponse($markup);
    }
}
```

You'll see that the example provides an array of variables to use in the template scope.
This could also be a view model, in which case, the relevant part would be:

```php
use Laminas\View\Model\ViewModel;

$viewModel = new ViewModel([
    'greeting' => 'Hello',
    'name' => 'World',
], 'pages/greeting');
$markup = $this->view->render($viewModel);
```

### The Template Contents

Assuming you have defined a default layout template, the previous example would have rendered the `pages/greeting` template within your layout template.

The layout template itself would need to output the `content` variable.

Here are example templates that would be used to satisfy the previous example

```php
<?php
// templates/layout/default.phtml
?>
<html>
<head>
    <title>Example Layout</title>
</head>
<body>
    <main>
    <?= $this->content ?>
    </main>
</body>
</html>
```

```php
<?php
// templates/pages/greeting.phtml
?>
<p>
    <?= $this->escapeHtml($this->greeting) ?>,
    <?= $this->escapeHtml($this->name) ?>!!
</p>
```

## Nesting View Models

Using `ViewModel` objects to compose the view provides opportunities for more complex use cases.

Imagine defining a view model to represent a sidebar, the main content of the page and some perhaps dynamically generated navigation component, all within a layout template.

Whilst this can be achieved _within_ templates via "partials", it may make more sense to compile complex views via application services.

```php
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\View\Model\ViewModel;
use Laminas\View\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ComplexPageHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly View $view,
        private readonly SidebarService $sidebar,
        private readonly NavigationService $navigation,
    ) {
    }
    
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // The main page content with its page specific variables
        $model = new ViewModel(['greeting' => 'hello'], 'pages/greeting');
        
        // The imaginary sidebar service examines the request and generates the
        // markup for a sidebar specific to the current URL/section etc.
        $model->addChild(
            $this->sidebar->createSidebarModelForRequest($request),
            'sidebar',
        );
        
        // The imaginary navigation service examines the request and generates the
        // markup for sub-navigation for the current section.
        $model->addChild(
            $this->navigation->createSubNavigationModelForRequest($request),
            'navigation',
        );
        
        // The view automatically wraps the content in the default layout
        $markup = $this->view->render($model);
        
        return new HtmlResponse($markup);
    }
}
```

In a real world application, it is more likely that other middleware might be responsible for preparing the sidebar or navigation view model, and these would be retrieved from request attributes, perhaps.

You can achieve very complex markup using nested views, while simultaneously
keeping the details of rendering isolated from the Request/Response lifecycle of
the controller or handler.

### Output of Nested Views in Template Files

In the example above, you'll notice the second argument to `ViewModel::addChild()` is a string.
This string represents the variable name the view model will be rendered to, therefore, to output the "sidebar" and "navigation" markup in the main page's content, the template must contain something like the following:

```php
<?php
// templates/greeting.phtml
?>
<div class="page-layout">
    <?= $this->navigation ?>
    <div class="left-column">
        <?= $this->sidebar ?>
    </div>
    <div class="page-content">
        <p><?= $this->escapeHtml($this->greeting) ?></p>
    </div>
</div>
```

This variable name is referred to as "Capture To" in the view model and can be defined when adding a child model as per the previous example, or can be set on the model itself:

```php
use Laminas\View\Model\ViewModel;

$child = new ViewModel(['some' => 'variable'], 'some-template');
$child->setCaptureTo('childName');

$main = new ViewModel(['more' => 'variables'], 'main-template');
$main->addChild($child);
```

When no "Capture To" variable name has been set, the default value is `'content'`. This is why you'll often see `<?= $this->content ?>` in examples of layout templates.

## Dealing with Layouts

### Preventing the Default Layout from Rendering

There are 2 ways of preventing the default layout from rendering:

#### 1: Mark the Model as Terminal

View Models that are marked as terminal are skipped for layout during render, so, if you are in the habit of passing `ViewModel` instances to `Laminas\View\View::render()`, the following will suffice:

```php
use Laminas\View\Model\ViewModel;
use Laminas\View\View;

$model = new ViewModel(['some' => 'data'], 'template-name');
$model->setTerminal(true);

/** @var View $view */
$view->render($model); // Only `template-name` is rendered with any child models configured
```

#### 2: Disable Layout When Calling `render`

The third argument to `Laminas\View\View::render()` is a boolean that you can set to `false` to prevent layout rendering:

```php
use Laminas\View\View;

/** @var View $view */
$view->renderTemplate('template-name', ['some' => 'data'], false);
```

### Changing the Layout

There are 2 ways of changing the layout template:

#### 1: Calling `renderLayout` instead of `render`

The `View` service has a method that accepts a fully formed layout view model, expecting all child content to be attached and ready to render.

This option provides complete control, but only accepts `ViewModel` instances.

```php
use Laminas\View\Model\ViewModel;
use Laminas\View\View;

$viewModel = new ViewModel(['greeting' => 'Hey!'], 'some-template');
$layoutModel = new ViewModel([], 'some-layout');
$layoutModel->addChild($viewModel);

/** @var View $view */
$html = $view->renderLayout($layoutModel);
```

#### 2: Using the Layout View Helper from a Template Context

The [shipped layout view helper](./helpers/layout.md) can be used to change the layout from within a template context, or anywhere else you decide to interact with the layout view helper.

The following code block depicts the contents of a template file that calls the layout view helper to change the layout template for the current rendering cycle:

```php
<?php
// some-template.phtml
$this->layout('alternative-layout');
?>

<div class="some-page-content">
    <p>Template Contents</p>
</div>
```

## Making use of View Helpers

`laminas-view` ships [a number of ready-to-use "View Helpers"](./helpers/intro.md) designed to make building markup and working with templates easier.

It is expected that users will get into the habit of writing custom view helpers to suit the needs of their project so it's a good idea to read the [documentation about writing custom view helpers](./helpers/advanced-usage.md).

View helpers are called on `$this` from within template files.
Here is an example of escaping a variable for output:

```php
<?php
// example-template.phtml
?>

<h1>Hi there <?= $this->escapeHtml($this->name) ?></h1>
<?php
// Expected Output
// <h1>Hi there Fred &amp; Wilma</h1> 
```

In order for auto-completion to be available in your IDE, we ship an interface that provides all the methods you can call by default inside a template file.

By forcing `$this` to be `Laminas\View\TemplateInterface`, your text editor should offer completions for all the documented helpers.

```php
<?php
// example-layout.phtml
use Laminas\View\TemplateInterface;

/** @var TemplateInterface $this */
?>
<?= $this->doctype() ?>
<html lang="en">
    <head>
        <?= $this->headTitle() ?>
        <?= $this->headLink()->appendStylesheet('/css/main.css') ?>
        <?= $this->headScript()->appendScript('/js/some.js') ?>
        <?= $this->headMeta() ?>
        <?= $this->headTitle()->prepend('My Website')->setSeparator(' - ') ?>
    </head>
    <body>
        <?= $this->partial('header-template');
        <main>
            <?= $this->content ?>
        </main>
        <?= $this->partial('footer-template'); ?>
        <?= $this->inlineScript() ?>
    </body>
</html>
```

### Escaping Output

`laminas-view` does not automatically escape output, therefore it is your responsibility to perform escaping when appropriate using the correct helper for the context.

> DANGER: **Security Considerations**
> It is critical that you correctly escape all variables for output.
> Failing to escape output can leave your application vulnerable to XSS and related vulnerabilities.

5 different escapers are available as view helpers via [`laminas-escaper`](https://docs.laminas.dev/laminas-escaper/):

```php
<?php
// example-template.phtml
use Laminas\View\TemplateInterface;

/** @var TemplateInterface $this */
?>
<!-- General HTML -->
<p>Escaping for HTML: <?= $this->escapeHtml('<some content that needs escaping>') ?></p>

<!-- HTML Attributes -->
<img src="/assets/cat.jpg" alt="<?= $this->escapeHtmlAttr('Checkout my <Cat>') ?>">

<!-- CSS -->
<style>
    .special::after {
        content: "<?= $this->escapeCss($userContent) ?>";
    }
</style>

<!-- JS -->
<script>
    document.getElementById('whatever').textContent = '<?= $this->escapeJs($userContent) ?>';
</script>

<!-- URLs -->
<a href="https://example.com?parameter=<?= $this->escapeUrl('some value') ?>">Link</a>
```
