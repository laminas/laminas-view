# Setting module-specific Layouts

The following example shows how to set a template for the layout based on a
module name in a zend-mvc based application. The example uses a listener that
listens on the
[`Zend\Mvc\MvcEvent::EVENT_RENDER` event](https://docs.zendframework.com/zend-mvc/mvc-event/#mvceventevent_render-render)
and uses the
[`Zend\Router\RouteMatch` object](https://docs.zendframework.com/zend-mvc/routing/#routing)
to get the called module from the current request.

## Create Listener

Create a listener as a separate class, e.g.
`module/Admin/src/Listener/LayoutListener.php`:

```php
namespace Admin\Listener;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;

class LayoutListener extends AbstractListenerAggregate
{
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(
            MvcEvent::EVENT_RENDER,
            [$this, 'setLayout']
        );
    }

    public function setLayout(MvcEvent $event) : void
    {
        // Get route match object
        $routeMatch = $event->getRouteMatch();

        // Check route match and parameter for current module
        if ($routeMatch
            && $routeMatch->getParam('module') === 'Admin'
        ) {
            // Get root view model
            $layoutViewModel = $event->getViewModel();

            // Change template
            $layoutViewModel->setTemplate('layout/backend');
        }
    }
}
```

## Register Listener

Extend the module class to register the listener, e.g.
`module/Admin/Module.php`:

```php
namespace Admin;

use Application\Listener\LayoutListener;
use Zend\Mvc\MvcEvent;

class Module
{
    public function onBootstrap(MvcEvent $e) : void
    {
        $application = $e->getApplication();
        
        // Create and register layout listener
        $layoutAggregate = new LayoutListener();
        $layoutAggregate->attach($application->getEventManager());
    }

    // …
}
```

> More informations on registering module-specific listeners can be found in the 
> [documentation of zend-mvc](https://docs.zendframework.com/zend-mvc/examples/#registering-module-specific-listeners).
