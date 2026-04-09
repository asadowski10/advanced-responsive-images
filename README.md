# Advanced Responsive Images

## Description

WordPress plugin that outputs custom HTML for responsive images (for example `<picture>` with lazy loading), driven by your theme configuration and pluggable rendering modes.

## Features

- Choose how images are rendered on the front end (modes such as picture + lazy load).
- Register custom providers and templates for your own markup.
- Integrates with `wp_get_attachment_image()` using a `data-location` attribute and JSON location definitions under your theme’s `assets/conf-img/` path (or `ARI_JSON_DIR`).

> I cannot guarantee free ad hoc support. Please be patient; I maintain this project alone.

## Installation

### Requirements

- **WordPress** 5.6 or higher (uses APIs aligned with modern responsive image handling).
- **PHP** 8.0 or higher (see `ARI_MIN_PHP_VERSION` in the main plugin file).

### From the WordPress admin

Install and activate the plugin from **Plugins → Add New** if you use a distribution package, or upload the ZIP under **Plugins → Add New → Upload Plugin**.

### Manual upload

1. Unpack the download package.
2. Upload the folder to `/wp-content/plugins/`.
3. Activate **Advanced Responsive Images** under **Plugins**.

### With Composer

From your project root (for example a Composer-managed WordPress setup):

```bash
composer require asadowski10/advanced-responsive-images
```

Optional packages (see `composer.json`):

- [WP Thumb](https://wordpress.org/plugins/wp-thumb/) — image resizing, cropping, and caching.
- [BEA WP Thumb](https://github.com/BeAPI/bea-wp-thumb) — reduce default WordPress image generation on upload.

## Configuration

- Default mode is `picture_lazyload` (override with the `ARI_MODE` constant).
- Default JSON config directory is `get_template_directory() . '/assets/conf-img/'` (override with `ARI_JSON_DIR`).

See the [changelog](CHANGELOG.md) for release notes and breaking changes.

## Repository

- Source: [github.com/asadowski10/advanced-responsive-images](https://github.com/asadowski10/advanced-responsive-images)
- Issues: [github.com/asadowski10/advanced-responsive-images/issues](https://github.com/asadowski10/advanced-responsive-images/issues)

## Translate Advanced Responsive Images

If you want to contribute a translation, you can [open a pull request](https://github.com/asadowski10/advanced-responsive-images/compare) with the translation files. Please read the [contributing guidelines](.github/CONTRIBUTING.md) first.

## License

Copyright (c) 2017–2026 Alexandre Sadowski

This code is licensed under the [MIT License](LICENSE).
