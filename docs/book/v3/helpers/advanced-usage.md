# Advanced Usage of Helpers

## Writing & Registering Custom Helpers

`laminas-view` provides a "Plugin Manager" implementation that provides access to what we call "View Helpers", typically, from within a template.

"View Helpers", or "View Plugins" must be invokable objects or closures.

To begin with, here is a trivial example of a view helper that reverses any string input it receives:

```php
<?php

namespace MyApp\ViewHelpers;

use function strrev;

final class StrRev
{
    public function __invoke(string $value): string
    {
        return strrev($value);
    }
}
```

In order to use this helper from within a template, it must be _registered_ with the plugin manger.
This is achieved by altering your configuration to include the following:

```php
<?php
// config/autoload/view-helpers.global.php

use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'view_helpers' => [
        'factories' => [
            MyApp\ViewHelpers\StrRev::class => InvokableFactory::class,
        ],
        'aliases' => [
            'reverse' => MyApp\ViewHelpers\StrRev::class,
        ],
    ],
];
```

The top-level `view_helpers` configuration key follows the same format as any other [service manager configuration set](https://docs.laminas.dev/laminas-servicemanager/v4/configuring-the-service-manager/).

Notice that we added an alias of "reverse" - this is the effective method name you will call from within your template files:

```php
<?php
// templates/example.phtml

?>

<h1>This is an <?= $this->reverse('Example') ?></h1>
```

## Helpers with Service Dependencies

The trivial `strrev` example is not much use.
Typically, you will need to use other services to perform more complex formatting or data retrieval.
Using dependency injection, your view helpers can consume any service from within your application:

Here is a fictional helper that retrieves a user from a user repository service in order to personalise certain data types to their imaginary preferences.

Notice how `__invoke` just returns `$this` which enables you to chain method calls on the helper for greater utility from within templates.

```php
namespace MyApp\ViewHelpers;

use DateTimeInterface;
use MyApp\UserRepository;
use MyApp\User;

final class UserInformation {

    public function __construct(
        private readonly UserRepository $repository,
    ) {
    }
    
    public function __invoke(): self
    {
        return $this;
    }
    
    public function preferredName(string $username): string
    {
        $user = $this->repository->findByUsername($username);
        
        if ($user instanceof User) {
            return $user->preferredName();
        }
        
        return '';
    }
    
    public function formatDateFor(string $username, DateTimeInterface $date): string
    {
        $user = $this->repository->findByUsername($username);
        $format = DateTimeInterface::W3C;
        
        if ($user instanceof User) {
            $format = $user->preferredDateFormat();
        }
        
        return sprintf(
            '<time datetime="%s">%s</time>',
            $date->format(DateTimeInterface::ATOM),
            $format,
        );
    }
}
```

You would write a factory for this helper along the lines of

```php
namespace MyApp\ViewHelpers;

use Psr\Container\ContainerInterface;
use MyApp\UserRepository;

final class UserInformationFactory
{
    public function __invoke(ContainerInterface $container): UserInformation
    {
        return new UserInformation($container->get(UserRepository::class));
    }
}
```

and register the helper with configuration such as:

```php
return [
    'view_helpers' => [
        'factories' => [
            MyApp\ViewHelpers\UserInformation::class => MyApp\ViewHelpers\UserInformationFactory::class,
        ],
        'aliases' => [
            'userInfo' => MyApp\ViewHelpers\UserInformation::class,
        ],
    ],
];
```

Finally, inside your template, and assuming there are certain variables available to you in that template:

```php
<?php // some-template.phtml ?>
<h1>Good Morning, <?= $this->userInfo()->preferredName($this->username) ?></h1>

<p>The current date is <?= $this->userInfo()->formatDateFor($this->username, $this->currentTime) ?></p>
```

## Dealing with State Buildup

Sometimes it is necessary to aggregate state inside view helpers to achieve certain functionality.
The shipped [`Placeholder` view helper](../helpers/placeholder.md) is a good example of this: Aggregating values in templates for output in a parent template, or layout for example.

This state can become problematic when your app is running on long-lived processes such as [RoadRunner](https://roadrunner.dev/), [Swoole](https://www.php.net/manual/book.swoole.php), or [FrankenPHP](https://frankenphp.dev/) amongst others, because the helper stays in memory for multiple requests rather than being destroyed after each successful request.

In the case of the placeholder view helper, if this state was not reset for each request, the data stored inside that helper would keep growing and growing affecting the data output on consecutive renders for different users of your website or application.

`laminas-view` ships an interface for view helpers to implement so that state can be automatically reset at the end of each rendering cycle: `Laminas\View\Helper\StatefulHelperInterface`.

Here's a trivial example implementation:

```php
namespace MyApp;

use Laminas\View\Helper\StatefulHelperInterface;

final class TrivialHelper implements StatefulHelperInterface
{
    private int $count = 0;
    
    public function __invoke(): string
    {
        $this->count++;
        
        return '<p>Some HTML</p>';
    }
    
    public function resetState() : void{
        $this->count = 0;
    }
}
```

## Using Closures as View Helpers

It is perfectly possible to use closures as view helpers for trivial tasks.
In order to register these, you'd need to either return them from a factory, or register them as services in configuration.

```php
// config/autoload/helpers.global.php

return [
    'view_helpers' => [
        'services' => [
            'someHelper' => static fn(string $value): string => strrev($value),
        ],
    ],
];
```

The main drawback to registering closures via configuration in this way is that configuration caching (serialisation) is unlikely to work leading to slower startup times for your application.
