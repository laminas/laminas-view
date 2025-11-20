# Introduction

In your [view scripts](../view-scripts.md), you'll perform certain complex functions over and over:
e.g., formatting a date, generating form elements, or displaying action links.
You can use helpers, or plugins to perform these behaviors for you.

A helper is a class that will normally have an `__invoke()` method as the entry point to perform its function.
For further details on creating your own custom helpers, take a look at the [advanced usage chapter](advanced-usage.md).

Helpers are retrieved from the `HelperPluginManager` by their fully qualified class name or alias.
It is the registered alias that we use inside [templates](../view-scripts.md) to call view helpers.

## Fetching or Using Helpers from Within Templates

As a recap from the [templates and view scripts documentation](../view-scripts.md), we call helpers by using their alias like a method name on the template instance:

```php
// some-template.phtml
?>
<h1>Hi there <?= $this->escapeHtml($this->name) ?></h1>
```

In the above example, the [EscapeHtml helper](escape.md#escapehtml) is retrieved from the plugin manager, its `__invoke` method is called with the value to be escaped, and it returns the escaped string for output in the rendered markup.

## Accessing View Helpers Outside the Rendering Cycle

The `HelperPluginManager` should be available in your applications dependency injection container.
To retrieve plugin instances, you must retrieve the plugin manager and then retrieve the helper from the plugin manager:

```php
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\HelperPluginManager;
use Psr\Container\ContainerInterface;

/** @var ContainerInterface $container */
$plugins = $container->get(HelperPluginManager::class);
$escaper = $plugins->get(EscapeHtml::class);
$value = $escaper($someValueToEscape);
```

Typically, [Mezzio](https://docs.mezzio.dev/) applications, or those that largely make use of Laminas components will use [Laminas Service Manager](https://docs.laminas.dev/laminas-servicemanager/) for dependency injection.
However, your main DI container does not need to be a `ServiceManager` instance in order to use `laminas-view` and its plugin system.
Any PSR-11 container can be used, but it will be your responsibility to ensure services are wired up appropriately when Laminas Service Manager is not in use.
The `ConfigProvider` can be inspected to determine how the various services are identified and created.

<!-- markdownlint-disable-next-line no-inline-html -->
<details><summary>Example Manual ServiceManager Setup</summary>

This example shows how to retrieve plugin instances in isolation without the benefits of using "Config Providers" to illustrate the steps that might be taken when using Laminas View with another PSR-11 DI container.

```php
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Factory\HelperPluginManagerFactory;
use Laminas\View\Helper\HeadTitle;
use Laminas\View\HelperPluginManager;

// Our main application DI container
$container = new ServiceManager();
// Wire up the factory to create the plugin manager
$container->setFactory(HelperPluginManager::class, HelperPluginManagerFactory::class);
// Fetch a plugin manager instance
$pluginManager = $container->get(HelperPluginManager::class);
// Retrieve helpers
$headTitleHelper = $pluginManager->get(HeadTitle::class);
// Do things with the helper…
$headTitleHelper->setPrefix('Title: ');
```

<!-- markdownlint-disable-next-line no-inline-html -->
</details>

## Included Helpers

Laminas View comes with a number of helpers for easing the creation of markup in web applications.

The currently shipped helpers include:

- [Asset](asset.md)
- [BasePath](base-path.md)
- [Cycle](cycle.md)
- [Doctype](doctype.md)
- [Escape](escape.md)
- [GravatarImage](gravatar-image.md)
- [HeadLink](head-link.md)
- [HeadMeta](head-meta.md)
- [HeadScript](head-and-inline-script.md)
- [HeadStyle](head-style.md)
- [HeadTitle](head-title.md)
- [HtmlAttributes](html-attributes.md)
- [HtmlList](html-list.md)
- [HTML Object Plugins](html-object.md)
- [HtmlTag](html-tag.md)
- [InlineScript](head-and-inline-script.md)
- [Layout](layout.md)
- [Partial](partial.md)
- [PartialLoop](partial-loop.md)
- [Placeholder](placeholder.md)
- [RenderToPlaceholder](render-to-placeholder.md)

NOTE: **i18n Helpers**
View helpers related to **Internationalization** are documented in the [I18n View Helpers](https://docs.laminas.dev/laminas-i18n/view-helpers/) documentation.

NOTE: **Form Helpers**
View helpers related to **form** are documented in the [Form View Helpers](https://docs.laminas.dev/laminas-form/helper/intro/) documentation.

NOTE: **Navigation Helpers**
View helpers related to **navigation** are documented in the [Navigation View Helpers](https://docs.laminas.dev/laminas-navigation/helpers/intro/) documentation.

NOTE: **Pagination Helpers**
View helpers related to **paginator** are documented in the [Paginator Usage](https://docs.laminas.dev/laminas-paginator/usage/#rendering-pages-with-view-scripts) documentation.

NOTE: **FlashMessenger Helper**
View helper related to **Flash Messenger** is documented in the [FLash Messenger View Helper](https://docs.laminas.dev/laminas-mvc-plugin-flashmessenger/view-helper/) documentation.

NOTE: **Custom Helpers**
For documentation on writing **custom view helpers** see the [Advanced usage](advanced-usage.md) chapter.
