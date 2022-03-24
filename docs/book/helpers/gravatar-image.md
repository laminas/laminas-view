# GravatarImage

The `GravatarImage` helper is useful for rendering image HTML markup returned from
the [gravatar.com](https://gravatar.com) service.

## Basic Usage

You can use the `GravatarImage` helper anywhere in view scripts per the following example:

```php
echo $this->gravatarImage('email@example.com');
```

The first argument passed to the helper should be an e-mail address for which you want grab an avatar from gravatar.com. For convenience, this e-mail will be automatically hashed via the md5 algorithm.

This will render an HTML img tag similar to the following:

```html
<img src="//www.gravatar.com/avatar/5658ffccee7f0ebfda2b226238b1eb6e?s=80&d=mm&r=g" />
```

## Custom Settings

You can customize the request and HTML output for a gravatar.com image by passing additional arguments to the view helper:

```php

// Basic usage provides a 80px square image, falling back to the "mm" default image provided by the Gravatar service
$this->gravatarImage('email@example.com');

// Set the image size you want gravatar.com to return, in pixels
$this->gravatarImage('email@example.com', 120);

// Provide custom HTML attributes for the generated img tag:
$this->gravatarImage('email@example.com', 120, [
    'alt' => 'Profile Picture for Someone',
    'data-something' => 'other-thing',
]);

// Set the default avatar image to use if gravatar.com does not find a match
$this->gravatarImage('email@example.com', 120, [], 'identicon');

// Set the avatar "rating" threshold (often used to omit NSFW avatars)
$this->gravatarImage('email@example.com', 120, [], 'mm', 'g');

```
