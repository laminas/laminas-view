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

In the above example, the [EscapeHtml helper](escape.md#escapehtml) is retrieved from the plugin manager, its `__invoke` method is called with the value to be escaped and it returns the escaped string for output in the rendered markup.

## Accessing View Helpers Outside the Rendering Cycle

The `HelperPluginManager` is available in the main ServiceManager.
To retrieve plugin instances, you must retrieve the plugin manager and then retrieve the helper from the plugin manager:

```php
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\HelperPluginManager;

/** @var ServiceManager $container */
$plugins = $container->get(HelperPluginManager::class);
$escaper = $plugins->get(EscapeHtml::class);
$value = $escaper($someValueToEscape);
```

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
