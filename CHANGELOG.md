# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.6.4 - 2016-03-02

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-view#52](https://github.com/zendframework/zend-view/pull/52) fixes several issues
  detected after re-enabling tests skipped when executed against
  laminas-servicemanager/laminas-eventmanager v3:
  - `HelperPluginManager` now implements an `EventManagerAware` initializer.
  - `Laminas\View\Helper\Navigation\AbstractHelper` now contains logic to ensure
    that when an `EventManager` instance is lazy-loaded, it composes a
    `SharedEventManager`.
  - The `FlashMessenger` factory now correctly pulls the `config` service, not
    the `Config` service (former is both backwards- and forwards compatible).

## 2.6.3 - 2016-02-22

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-view#50](https://github.com/zendframework/zend-view/pull/50) fixes
  the initializer defined and registered in
  `Navigation\PluginManager::__construct()` to ensure it properly pulls and
  injects the application container into navigation helpers, under both
  laminas-servicemanager v2 and v3. Additionally, when lazy-instantiating the
  `Navigation\PluginManager`, the `Navigation` helper now passes the composed
  service manager instance to its constructor.

## 2.6.2 - 2016-02-18

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-view#47](https://github.com/zendframework/zend-view/pull/47) fixes
  `Navigation\PluginManager` to ensure it is backwards compatible
  with laminas-servicemanager v2, including:
  - fixing the constructor to be BC with v2 and forwards-compatible with v3.
  - adding additional, normalized alias/factory pairs.
- [zendframework/zend-view#47](https://github.com/zendframework/zend-view/pull/47) fixes
  the behavior of `HelperPluginManager::injectTranslator()` to return
  early if no container is provided (fixing an issue with navigation
  helpers introduced in 2.6.0).

## 2.6.1 - 2016-02-18

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-view#44](https://github.com/zendframework/zend-view/pull/44) fixes the
  constructor of `HelperPluginManager` to ensure it is backwards compatible
  with laminas-servicemanager v2.

## 2.6.0 - 2016-02-17

### Added

- [zendframework/zend-view#8](https://github.com/zendframework/zend-view/pull/8) adds a new method to
  each of the `Breadcrumbs` and `Menu` navigation helpers, 
  `renderPartialWithParams(array $params = [], $container = null, $partial = null)`.
  This method allows passing parameters to the navigation partial to render,
  just as you would when using the `partial()` view helper.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-view#15](https://github.com/zendframework/zend-view/pull/15),
  [zendframework/zend-view#17](https://github.com/zendframework/zend-view/pull/17),
  [zendframework/zend-view#35](https://github.com/zendframework/zend-view/pull/35), and
  [zendframework/zend-view#42](https://github.com/zendframework/zend-view/pull/42) update the component
  to be forwards-compatible with the v3 releases of laminas-eventmanager,
  laminas-servicemanager, and laminas-stdlib. The changes include:
  - changes to how events are triggered to ensure they continue working correctly.
  - updates to the plugin manager to be forwards-compatible.
  - updates to helper factories to be forwards-compatible.

## 2.5.3 - 2016-01-19

### Added

- [zendframework/zend-view#5](https://github.com/zendframework/zend-view/pull/5) adds support for the
  `itemprop` attribute in the `headLink()` view helper.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-view#25](https://github.com/zendframework/zend-view/pull/25) updates
  `PhpRenderer::render()` to no longer lazy-instantiate a `FilterChain`;
  content filtering is now only done if a `FitlerChain` is already
  injected in the renderer.

## 2.5.2 - 2015-06-16

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-view#4](https://github.com/zendframework/zend-view/pull/4) fixes an issue with
  how the `ServerUrl` detects and emits the port when port-forwarding is in
  effect.

## 2.4.3 - 2015-06-16

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-view#4](https://github.com/zendframework/zend-view/pull/4) fixes an issue with
  how the `ServerUrl` detects and emits the port when port-forwarding is in
  effect.
