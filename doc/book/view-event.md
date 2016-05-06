# The ViewEvent

The view layer of Zend Framework 2 incorporates and utilizes a custom `Zend\EventManager\Event`
implementation -`Zend\View\ViewEvent`. This event is created during `Zend\View\View::getEvent()` and
is passed directly to all the events that method triggers.

The `ViewEvent` adds accessors and mutators for the following:

- `Model` object, typically representing the layout view model.
- `Renderer` object.
- `Request` object.
- `Response` object.
- `Result` object.

The methods it defines are:

- `setModel(Model $model)`
- `getModel()`
- `setRequest($request)`
- `getRequest()`
- `setResponse($response)`
- `getResponse()`
- `setRenderer($renderer)`
- `getRenderer()`
- `setResult($result)`
- `getResult()`

## Order of events

The following events are triggered, in the following order:

Those events are extensively describe in the following sections.

## ViewEvent::EVENT\_RENDERER

### Listeners

The following classes are listening to this event (they are sorted from higher priority to lower
priority):

#### For PhpStrategy

This listener is added when the strategy used for rendering is `PhpStrategy`:

#### For JsonStrategy

This listener is added when the strategy used for rendering is `JsonStrategy`:

#### For FeedStrategy

This listener is added when the strategy used for rendering is `FeedStrategy`:

### Triggerers

This event is triggered by the following classes:

## ViewEvent::EVENT\_RENDERER\_POST

### Listeners

There are currently no built-in listeners for this event.

### Triggerers

This event is triggered by the following classes:

## ViewEvent::EVENT\_RESPONSE

### Listeners

The following classes are listening to this event (they are sorted from higher priority to lower
priority):

#### For PhpStrategy

This listener is added when the strategy used for rendering is `PhpStrategy`:

#### For JsonStrategy

This listener is added when the strategy used for rendering is `JsonStrategy`:

#### For FeedStrategy

This listener is added when the strategy used for rendering is `FeedStrategy`:

### Triggerers

This event is triggered by the following classes:
