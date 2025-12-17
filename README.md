# Kirby Media Library Plugin

A simple plugin that adds a centralized media library for Kirby sites. <br>
It provides tabs for Images, Videos, Documents and other files, displays thumbnails, file info, and panel links. Perfect for managing all assets in one place.

## Installation

### Download
1. Run ``composer require josehoudini/media-library``

or

1. Download the .zip
2. Copy the folder into `/site/plugins/media-library`

## Setup
Add ``media-library`` to your panel menu in

```php
// site/config/config.php

return [
    'panel' => [
        'menu => [
            ...
            'media-library',
        ]
    ]
];
```

## Disclaimer

This plugin is provided "as is" with no guarantee. Use it at your own risk and always test it yourself before using it in a production environment. If you find any issues, please create a new issue.

## Roadmap

- [x] Caching and pagination
- [ ] Add sorting functionality
- [ ] Add search functionality
- [ ] Target and highlight "danger zone" files

## License
<a href="https://opensource.org/license/MIT" target="_blank">MIT</a>

It is discouraged to use this plugin in any project that promotes racism, sexism, homophobia, animal abuse, violence or any other form of hate speech.
