# HtmlObject

The [HTML `<object>` element](https://developer.mozilla.org/docs/Web/HTML/Element/object) is used for embedding external media in web pages.

The object view helper takes care of embedding media with minimum effort.

Additionally, you can specify attributes, parameters, and content that can be
rendered along with the `<object>`.

## Customizing the object by passing additional arguments

The first argument in the object helpers is always required.
It is the URI to the resource you want to embed.
The second argument is the mime type of the resource to be embedded.
The third argument is used for passing arbitrary attributes to the
object element which should be an associative array with string keys and scalar values.
The fourth argument accepts an array representing [object parameters](https://developer.mozilla.org/docs/Web/HTML/Reference/Elements/param).
Finally, there is the option of providing additional, often fallback, content to the object. The following example utilizes all arguments.

```php
echo $this->htmlObject(
    '/path/to/file.ext',
    'mime/type',
    [
        'attr1' => 'aval1',
        'attr2' => 'aval2',
    ],
    [
        'param1' => 'pval1',
        'param2' => 'pval2',
    ],
    'some content'
);
```

This would output:

```html
<object data="/path/to/file.ext" type="mime/type"
    attr1="aval1" attr2="aval2">
    <param name="param1" value="pval1" />
    <param name="param2" value="pval2" />
    some content
</object>
```
