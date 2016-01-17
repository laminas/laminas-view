# View Helper - InlineScript

## Introduction

The *HTML* **&lt;script&gt;** element is used to either provide inline client-side scripting
elements or link to a remote resource containing client-side scripting code. The `InlineScript`
helper allows you to manage both. It is derived from
\[HeadScript\](zend.view.helpers.initial.headscript), and any method of that helper is available;
however, use the `inlineScript()` method in place of `headScript()`.

> ## Note
#### Use InlineScript for HTML Body Scripts
`InlineScript`, should be used when you wish to include scripts inline in the *HTML* **body**.
Placing scripts at the end of your document is a good practice for speeding up delivery of your
page, particularly when using 3rd party analytics scripts.
Some JS libraries need to be included in the *HTML* **head**; use HeadScript
&lt;zend.view.helpers.initial.headscript&gt; for those scripts.

## Basic Usage

Add to the layout script:

```php
<body>
    <!-- Content -->

    <?php
    echo $this->inlineScript()->prependFile($this->basePath('js/vendor/foundation.min.js'))
                              ->prependFile($this->basePath('js/vendor/jquery.js'));
    ?>
</body>
```

Output:

```php
<body>
    <!-- Content -->

    <script type="text/javascript" src="/js/vendor/jquery.js"></script>
    <script type="text/javascript" src="/js/vendor/foundation.min.js"></script>
</body>
```

## Capturing Scripts

Add in your view scripts:

```php
$this->inlineScript()->captureStart();
echo <<<JS
    $('select').change(function(){
        location.href = $(this).val();
    });
JS;
$this->inlineScript()->captureEnd();
```

Output:

```php
<body>
    <!-- Content -->

    <script type="text/javascript" src="/js/vendor/jquery.js"></script>
    <script type="text/javascript" src="/js/vendor/foundation.min.js"></script>
    <script type="text/javascript">
        //<!--
        $('select').change(function(){
            location.href = $(this).val();
        });
        //-->
    </script>
</body>
```
