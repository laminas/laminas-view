# Preparing for Version 3

Version 3 will introduce a number of backwards incompatible changes. This document is intended to help you prepare for these changes.

## Signature Changes

### Template Resolvers

In version 2, template resolvers, which all implement the method `ResolverInterface::resolve()` have varying return types along with some undocumented or inconsistent behaviour, specifically concerned with error handling, for example, when a template cannot be found, or no templates have been configured.

Version 3 will solve these issues by guaranteeing a return type of `string` or `false`.

#### Before

Before version 3 the return type can `null`, `false` or `string`:

```php
return $this->resolver->resolve($name, $this->renderer);
```

#### After

```php
$filePath = $this->resolver->resolve($name, $this->renderer);
if ($filePath === false) {
    // handle the failure to resolve
}
```

## Deprecations

### Undocumented Behaviour

`\Laminas\View\Resolver\TemplateMapResolver` allows runtime mutation of the template map with the `add($name, $path)` method.
This method has an undocumented feature where passing `null` to the `$path` parameter allows removal of an existing template providing that `$name` is a string. This feature is deprecated and will now issue an `E_USER_DEPRECATED` runtime error if used in this way.

This deprecation can be safely ignored but in order to prepare for its removal in v3, you should ensure that you provide the complete map to the `TemplateMapResolver`'s constructor rather than changing it at runtime.

### Deprecated Stream Wrappers for Short Open Tags

In version 2, the `TemplatePathStack` template resolver automatically registers a stream wrapper for templates when the php.ini setting `short_open_tag` was turned off. The purpose of the stream wrapper was to convert template files using the short open tag `<?= $variable ?>` to `<?php echo $variable ?>` so that templates would continue to be processed in environments where short_open_tag was turned off. Since PHP 5.4.0, `<?=` is always available, therefore the wrapper became mostly unnecessary.

The impact of this future removal will affect templates that use a regular short open tag for general PHP code, i.e. `<? $i = 1; echo $i ?>` in environments where `short_open_tag` is **off**. To mitigate the impact of this removal, you should ensure that, where relevant, all of your templates use the full `<?php` open tag. Use of the short echo tag `<?=` is unaffected.

## View Helper Changes

### Deprecated View Helpers Scheduled for Removal in 3.0

The following view helpers are deprecated and will be removed in version 3.0 of `laminas-view`.

- The [Json View Helper](../helpers/json.md)
- The Navigation View Helper and all associated helpers, `Links`, `Menu`, `Sitemap`, `Breadcrumbs` along with its dedicated plugin manager will be removed in 3.0. These view helpers have been copied to the `laminas-navigation` component, and you can prepare for their removal by altering the namespace in your code from, for example `Laminas\View\Helper\Navigation\Menu` to `Laminas\Navigation\View\Helper\Menu`
- The _(Un-documented)_ `DeclareVars` helper
- The `FlashMessenger` helper, available in its own package [laminas-mvc-plugin-flashmessenger](https://docs.laminas.dev/laminas-mvc-plugin-flashmessenger/)
- `HtmlFlash`, `HtmlPage` and `HtmlQuicktime` helpers
- The `Identity` helper
- `Url` and `ServerUrl` are specific to `laminas-mvc` and will be removed
- `Gravatar` will be replaced with `GravatarImage`

### Fundamental Changes to Custom View Helpers

The `AbstractHelper` and `AbstractHtmnlElement` classes are being removed in 3.0 along with automatic injection of the `PhpRenderer`.
This change forces custom helpers to use dependency injection for other plugin or service dependencies.

There is a [chapter on refactoring view helpers](../../v3/migration/refactoring-view-helpers.md) available to aid with this transition.
