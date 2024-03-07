# Preparing for Version 3

Version 3 will introduce a number of backwards incompatible changes. This document is intended to help you prepare for these changes.

## Signature Changes

### Template Resolvers

In version 2, template resolvers, which all implement the method `ResolverInterface::resolve()` have varying return types along with some undocumented or inconsistent behaviour, specifically concerned with error handling, for example, when a template cannot be found, or no templates have been configured.

Version 3 will solve these issues by guaranteeing a string return type from `ResolverInterface::resolve()` or throw a `\Laminas\View\Exception\ExceptionInterface`.

#### Before

Before version 3 the return type can `null`, `false` or `string`:

```php
return $this->resolver->resolve($name, $this->renderer);
```

#### After

If a template resolver is used as standalone, use a `try`-`catch` block to create a custom signal for a missing template in an application:

```php
try {
    return $this->resolver->resolve($name, $this->renderer);
} catch (\Laminas\View\Exception\ExceptionInterface $error) {
    return null; // custom return type
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
