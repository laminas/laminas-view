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
<img src="//www.gravatar.com/avatar/5658ffccee7f0ebfda2b226238b1eb6e?s=80&d=mp&r=g" />
```

## Custom Settings

You can customize the request and HTML output for a gravatar.com image by passing additional arguments to the view helper:

### Set Image Size

Provide a positive integer value to the second argument to yield an image with the given dimensions:

```php
echo $this->gravatarImage('email@example.com', 120);
```

Output:

```html
<img src="..." width="120" height="120" />
```

### Set arbitrary image attributes

You can provide attributes for the resulting image tag as an associative array, but bear in mind that `src`, `width` and `height` attributes will be ignored.

```php
echo $this->gravatarImage('email@example.com', 120, [
    'alt' => 'Profile Picture for Someone',
    'data-something' => 'other-thing',
]);
```

Output:

```html
<img src="..." alt="Profile Picture for Someone" data-something="other-thing" />
```

### Change the fallback image

The Gravatar service will present a default image when a given email address does not correspond to a known profile picture. The possible values are listed in `GravatarImage::DEFAULT_IMAGE_VALUES` and [documented here](https://en.gravatar.com/site/implement/images/). Each possible value has a constant _(Prefixed `DEFAULT_*`)_ you can refer to when specifying the fallback image type. Provide the value as the 4th argument.

```php
use Laminas\View\Helper\GravatarImage;

// Set the default avatar image to use if gravatar.com does not find a match
echo $this->gravatarImage('email@example.com', 120, [], GravatarImage::DEFAULT_RETRO);
```

You can also supply your own fallback image as a fully qualified url:

```php
echo $this->gravatarImage('email@example.com', 120, [], 'https://example.com/default-image.png');
```

### Change the image rating allowed

The Gravatar service allows users to provide a rating for the images they upload to indicate the type of audience they should be acceptable to. By default, the rating is "G". You can allow potentially explicit profile images by changing the rating to a value as [documented by the Gravatar service](https://en.gravatar.com/site/implement/images/). Again, each of the possible ratings are available as constants defined in the helper _(Prefixed `RATING_*`)_ and can be provided as the 5th argument:

```php
use Laminas\View\Helper\GravatarImage;

// Set the avatar "rating" threshold (often used to omit NSFW avatars)
$this->gravatarImage(
    'email@example.com',
    120,
    [],
    GravatarImage::DEFAULT_MP,
    GravatarImage::RATING_PG
);

```
