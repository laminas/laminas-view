# RenderToPlaceholder

The `RenderToPlaceholder` helper allows you to _render_ a template to an arbitrary string placeholder for output "somewhere else".

```php
$this->renderToPlaceholder('some-template.phtml', 'myPlaceholder');
// Possibly in another template, perhaps a layout

echo $this->placeholder('myPlaceholder');
```

Bear in mind that if you wish to render a template within the current template, the [partial helper](./partial.md) is better choice.
