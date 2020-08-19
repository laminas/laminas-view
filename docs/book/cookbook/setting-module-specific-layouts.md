# Setting module-specific Layouts

The following example shows how to set a template for the layout based on a
module name in a laminas-mvc based application. The example uses a listener that
listens on the
[`Laminas\Mvc\MvcEvent::EVENT_RENDER` event](https://docs.laminas.dev/laminas-mvc/mvc-event/#mvceventevent_render-render)
and uses the
[`Laminas\Router\RouteMatch` object](https://docs.laminas.dev/laminas-mvc/routing/#routing)
to get the called controller from the current request.

## Create Listener

Create a listener as a separate class, e.g.
`module/Admin/src/Listener/LayoutListener.php`:

```php
namespace Admin\Listener;

use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Filter\FilterChain;
use Laminas\Filter\FilterInterface;
use Laminas\Filter\StringToLower;
use Laminas\Filter\Word\CamelCaseToDash;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Resolver\TemplateMapResolver;

class LayoutListener extends AbstractListenerAggregate
{
    /** @var TemplateMapResolver */
    private $templateMapResolver;

    /** @var FilterInterface */
    private $filter;

    public function __construct(TemplateMapResolver $templateMapResolver)
    {
        $this->templateMapResolver = $templateMapResolver;
        $this->filter              = (new FilterChain())
            ->attach(new CamelCaseToDash())
            ->attach(new StringToLower());
    }

    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(
            MvcEvent::EVENT_RENDER,
            [$this, 'setLayout']
        );
    }

    public function setLayout(MvcEvent $event) : void
    {
        // Get and check the route match object
        $routeMatch = $event->getRouteMatch();
        if (! $routeMatch) {
            return;
        }

        // Get and check the parameter for current controller
        $controller = $routeMatch->getParam('controller');
        if (! $controller) {
            return;
        }

        // Extract module name
        $module = substr($controller, 0, strpos($controller, '\\'));

        // Convert the module name from camel case to a lower string with dashes
        $name = 'layout/' . $this->filter->filter($module);

        // Has the resolver an entry / layout with the given name?
        if (! $this->templateMapResolver->has($name)) {
            return;
        }

        // Get root view model
        $layoutViewModel = $event->getViewModel();

        // Change template only if template should be set
		if(!$layoutViewModel->terminate()){
			$layoutViewModel->setTemplate($name);
		}
    }
}
```

## Register Listener

Extend the module class to register the listener, e.g.
`module/Admin/Module.php`:

```php
namespace Admin;

use Admin\Listener\LayoutListener;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Resolver\TemplateMapResolver;

class Module
{
    public function onBootstrap(MvcEvent $event) : void
    {
        $application = $event->getApplication();

        /** @var TemplateMapResolver $templateMapResolver */
        $templateMapResolver = $application->getServiceManager()->get(
            'ViewTemplateMapResolver'
        );

        // Create and register layout listener
        $listener = new LayoutListener($templateMapResolver);
        $listener->attach($application->getEventManager());
    }

    // …
}
```

> More information on registering module-specific listeners can be found in the
> [documentation of laminas-mvc](https://docs.laminas.dev/laminas-mvc/examples/#registering-module-specific-listeners).

## Add Template Scripts to the Configuration

Extend the configuration of a module to add the specific layout script, e.g.
`module/Admin/config/module.config.php`:

```php
return [
    // Add the following array
    'view_manager' => [
        'template_map' => [
            'layout/admin' => __DIR__ . '/../view/layout/admin.phtml',
        ],
    ],
    // …
];
```

And in another module, e.g. `module/Album/config/module.config.php`:

```php
return [
    // Add the following array
    'view_manager' => [
        'template_map' => [
            'layout/album' => __DIR__ . '/../view/layout/layout-of-album.phtml',
        ],
    ],
    // …
];
```

The name of the array key must follow the format `layout/{module-name}` and the
value must contain the path to the layout file. The path and the filename can be
freely chosen.
