# View Helpers

## Introduction

In your view scripts, often it is necessary to perform certain complex functions over and over:
e.g., formatting a date, generating form elements, or displaying action links. You can use helper,
or plugin, classes to perform these behaviors for you.

A helper is simply a class that implements `Laminas\View\Helper\HelperInterface` and it simply defines
two methods, `setView()`, which accepts a `Laminas\View\Renderer\RendererInterface`
instance/implementation, and `getView()`, used to retrieve that instance.
`Laminas\View\Renderer\PhpRenderer` composes a *plugin manager*, allowing you to retrieve helpers, and
also provides some method overloading capabilities that allow proxying method calls to helpers.

As an example, let's say we have a helper class named `MyModule\View\Helper\LowerCase`, which we
register in our plugin manager with the name "lowercase". We can retrieve it in one of the following
ways:

```php
// $view is a PhpRenderer instance

// Via the plugin manager:
$pluginManager = $view->getHelperPluginManager();
$helper        = $pluginManager->get('lowercase');

// Retrieve the helper instance, via the method "plugin",
// which proxies to the plugin manager:
$helper = $view->plugin('lowercase');

// If the helper does not define __invoke(), the following also retrieves it:
$helper = $view->lowercase();

// If the helper DOES define __invoke, you can call the helper
// as if it is a method:
$filtered = $view->lowercase('some value');
```

The last two examples demonstrate how the `PhpRenderer` uses method overloading to retrieve and/or
invoke helpers directly, offering a convenience API for end users.

A large number of helpers are provided in the standard distribution of Laminas. You can also
register helpers by adding them to the *plugin manager*.

## Included Helpers

Laminas comes with an initial set of helper classes. In particular, there are helpers for
creating route-based *URL*s and *HTML* lists, as well as declaring variables. Additionally, there
are a rich set of helpers for providing values for, and rendering, the various HTML *&lt;head&gt;*
tags, such as `HeadTitle`, `HeadLink`, and `HeadScript`. The currently shipped helpers include:

- \[BasePath\](laminas.view.helpers.initial.basepath)
- \[Cycle\](laminas.view.helpers.initial.cycle)
- \[Doctype\](laminas.view.helpers.initial.doctype)
- \[FlashMessenger\](laminas.view.helpers.initial.flashmessenger)
- \[Gravatar\](laminas.view.helpers.initial.gravatar)
- \[HeadLink\](laminas.view.helpers.initial.headlink)
- \[HeadMeta\](laminas.view.helpers.initial.headmeta)
- \[HeadScript\](laminas.view.helpers.initial.headscript)
- \[HeadStyle\](laminas.view.helpers.initial.headstyle)
- \[HeadTitle\](laminas.view.helpers.initial.headtitle)
- \[HtmlList\](laminas.view.helpers.initial.htmllist)
- \[HTML Object Plugins\](laminas.view.helpers.initial.object)
- \[Identity\](laminas.view.helpers.initial.identity)
- \[InlineScript\](laminas.view.helpers.initial.inlinescript)
- \[JSON\](laminas.view.helpers.initial.json)
- \[Partial\](laminas.view.helpers.initial.partial)
- \[Placeholder\](laminas.view.helpers.initial.placeholder)
- \[Url\](laminas.view.helpers.initial.url)

> ## Note
View helpers related to **Internationalization** are documented in the \[I18n View
Helpers\](laminas.i18n.view.helpers) chapter.

> ## Note
View helpers related to **form** are documented in the \[Form View Helpers\](laminas.form.view.helpers)
chapter.

> ## Note
View helpers related to **navigation** are documented in the \[Navigation View
Helpers\](laminas.navigation.view.helpers) chapter.

> ## Note
View helpers related to **paginator** are documented in the \[Paginator
Usage\](laminas.paginator.rendering) chapter.

> ## Note
For documentation on writing **custom view helpers** see the \[Advanced
usage\](laminas.view.helpers.advanced-usage) chapter.
