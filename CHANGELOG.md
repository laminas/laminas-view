# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.6.0 - TBD

### Added

- [#8](https://github.com/zendframework/zend-view/pull/8) adds a new method to
  each of the `Breadcrumbs` and `Menu` navigation helpers, 
  `renderPartialWithParams(array $params = [], $container = null, $partial = null)`.
  This method allows passing parameters to the navigation partial to render,
  just as you would when using the `partial()` view helper.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.5.3 - TBD

### Added

- [#5](https://github.com/zendframework/zend-view/pull/5) adds support for the
  `itemprop` attribute in the `headLink()` view helper.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.5.2 - 2015-06-16

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#4](https://github.com/zendframework/zend-view/pull/4) fixes an issue with
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

- [#4](https://github.com/zendframework/zend-view/pull/4) fixes an issue with
  how the `ServerUrl` detects and emits the port when port-forwarding is in
  effect.
