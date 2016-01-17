# Advanced usage of helpers

## Registering Helpers

`Zend\View\Renderer\PhpRenderer` composes a *plugin manager* for managing helpers, specifically an
instance of `Zend\View\HelperPluginManager`, which extends
`Zend\ServiceManager\AbstractPluginManager`, and this extends `Zend\ServiceManager\ServiceManager`.
As you can see, the *HelperPluginManager* is a specialized service manager, so you can register a
helper/plugin like any other service (see the Service Manager documentation
&lt;zend.service-manager.intro&gt; for more information).

Programmatically, this is done as follows:

```php
// $view is an instance of PhpRenderer
$pluginManager = $view->getHelperPluginManager();

// Register as a invokable class:
$pluginManager->setInvokableClass('lowercase', 'MyModule\View\Helper\LowerCase');

// Register as a factory:
$pluginManager->setFactory('lowercase', function ($pluginManager) {
   $lowercaseHelper = new MyModule\View\Helper\LowerCase;

   // ...do some configuration or dependency injection...

   return $lowercaseHelper;
});
```

Within an MVC application, you will typically simply pass a map of plugins to the class via your
configuration.

```php
// From within a configuration file
return array(
   'view_helpers' => array(
      'invokables' => array(
         'lowercase' => 'MyModule\View\Helper\LowerCase',
         'uppercase' => 'MyModule\View\Helper\UpperCase',
      ),
   ),
);
```

If your module class implements `Zend\ModuleManager\Feature\ViewHelperProviderInterface`, or just
the method `getViewHelperConfig()`, you could do the following (it's the same as the previous
example).

```php
namespace MyModule;

class Module
{
    public function getAutoloaderConfig(){ /*common code*/ }
    public function getConfig(){ /*common code*/ }

    public function getViewHelperConfig()
    {
        return array(
           'invokables' => array(
              'lowercase' => 'MyModule\View\Helper\LowerCase',
              'uppercase' => 'MyModule\View\Helper\UpperCase',
           ),
        );
   }
}
```

The two latter examples can be done in each module that needs to register helpers with the
`PhpRenderer`; however, be aware that another module can register helpers with the same name, so
order of modules can impact which helper class will actually be registered!

## Writing Custom Helpers

Writing custom helpers is easy. We recommend extending `Zend\View\Helper\AbstractHelper`, but at the
minimum, you need only implement the `Zend\View\Helper\HelperInterface` interface:

```php
namespace Zend\View\Helper;

use Zend\View\Renderer\RendererInterface as Renderer;

interface HelperInterface
{
    /**
     * Set the View object
     *
     * @param  Renderer $view
     * @return HelperInterface
     */
    public function setView(Renderer $view);

    /**
     * Get the View object
     *
     * @return Renderer
     */
    public function getView();
}
```

If you want your helper to be capable of being invoked as if it were a method call of the
`PhpRenderer`, you should also implement an `__invoke()` method within your helper.

As previously noted, we recommend extending `Zend\View\Helper\AbstractHelper`, as it implements the
methods defined in `HelperInterface`, giving you a headstart in your development.

Once you have defined your helper class, make sure you can autoload it, and then register it with
the plugin
manager &lt;zend.view.helpers.register&gt;.

Here is an example helper, which we're titling "SpecialPurpose"

```php
// /module/src/MyModule/View/Helper/SpecialPurpose.php
namespace MyModule\View\Helper;

use Zend\View\Helper\AbstractHelper;

class SpecialPurpose extends AbstractHelper
{
    protected $count = 0;

    public function __invoke()
    {
        $this->count++;
        $output = sprintf("I have seen 'The Jerk' %d time(s).", $this->count);
        return htmlspecialchars($output, ENT_QUOTES, 'UTF-8');
    }
}
```

Then assume that we \[register it with the plugin manager\](zend.view.helpers.register), by the name
"specialpurpose".

Within a view script, you can call the `SpecialPurpose` helper as many times as you like; it will be
instantiated once, and then it persists for the life of that `PhpRenderer` instance.

```php
// remember, in a view script, $this refers to the Zend\View\Renderer\PhpRenderer instance.
echo $this->specialPurpose();
echo $this->specialPurpose();
echo $this->specialPurpose();
```

The output would look something like this:

```php
I have seen 'The Jerk' 1 time(s).
I have seen 'The Jerk' 2 time(s).
I have seen 'The Jerk' 3 time(s).
```

Sometimes you will need access to the calling `PhpRenderer` object -- for instance, if you need to
use the registered encoding, or want to render another view script as part of your helper. This is
why we define the `setView()` and `getView()` methods. As an example, we could rewrite the
`SpecialPurpose` helper as follows to take advantage of the `EscapeHtml` helper:

```php
namespace MyModule\View\Helper;

use Zend\View\Helper\AbstractHelper;

class SpecialPurpose extends AbstractHelper
{
    protected $count = 0;

    public function __invoke()
    {
        $this->count++;
        $output  = sprintf("I have seen 'The Jerk' %d time(s).", $this->count);
        $escaper = $this->getView()->plugin('escapehtml');
        return $escaper($output);
    }
}
```

## Registering Concrete Helpers

Sometimes it is convenient to instantiate a view helper, and then register it with the renderer.
This can be done by injecting it directly into the plugin manager.

```php
// $view is a PhpRenderer instance

$helper = new MyModule\View\Helper\LowerCase;
// ...do some configuration or dependency injection...

$view->getHelperPluginManager()->setService('lowercase', $helper);
```

The plugin manager will validate the helper/plugin, and if the validation passes, the helper/plugin
will be registered.
