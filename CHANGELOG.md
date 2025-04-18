# Changelog

## 4.2.1 - 17 April 2025
- Improve performance on check tpl : thanks to [@petitphp](https://github.com/petitphp)

## 4.2.0 - 27 May 2024
- Allow caption in Picture mode : need te create default-picture-nolazyload-caption.tpl
- Remove old modes for HTML frontend compat (not used)

## 4.1.2 - 9 Nov 2023
- Improve width, height to 'auto' on picture mode when crop is false

## 4.1.1 - 25 Oct 2023
- Fix bug on Singleton Mode

## 4.1.0 - 23 Oct 2023
- Refactoring code
- Add new mode Picture to no lazyload images
- Minimum require PHP version to 7.4

## 4.0.2 - 16 Oct 2023
- Improve cache with group key
- Flush attachment group on update

## 4.0.1 - 7 June 2023
- Change default value ari_responsive_image_default_img_path to /dist/images

## 4.0.0 - 7 June 2023
- Change default path for ARI_JSON_DIR to /assets/

## 3.1.10 - 19 August 2022
- Fix PHP Warning on alt image attribute

## 3.1.9 - 17 August 2022
- Add new filter `ari_responsive_image_alt` to modify alt image attribute

## 3.1.8 - 03 August 2022
- Add compat for composer 2

## 3.1.7 - 31 Janv 2022
- Add possibility to disable placeholder for a location

## 3.1.6 - 14 Oct 2021
- Fix error in XML Feed

## 3.1.5 - 13 Oct 2021
- Fix object cache key with different $args for the same image and location

## 3.1.4 - 24 Sept 2021
- Update `ari_responsive_image_caption` filter to add attachment id and attachment attributes params
- Fix depecrated_function with check WordPress version

## 3.1.3 - 1 Sept 2021
- Add support of display caption
- Add new filter `ari_responsive_image_caption` to modify caption

## 3.1.2 - 31 August 2021
- PHP 8.0 Compatibility : update Singleton, fix warning

## 3.1.1 - 25 Fev 2021
- Add some object cache on image construct

## 3.1.0 - 22 Fev 2021
- Use new filter `wp_get_attachment_image` added `wp_get_attachment_image` in WordPress 5.6.0
- Deprecated function `bea_get_attachment_image()`, use `wp_get_attachment_image()`

## 3.0.19 - 24 Nov 2020
- Allow no height or width for TimThumb ( value = 0) in Picture Lazyload Front

## 3.0.18 - 2 Sept 2020
- Fix compat wpthumb

## 3.0.17 - 5 August 2020
- Fix error on image-sizes called image-location.json
- Replace some file_get_contents
- Fix some condition
- Delete class not used

## 3.0.16 - 4 August 2020
- Use fopen/fread, not file_get_contents to parse json files

## 3.0.15 - 3 August 2020
- Support alt => "none" param to display an empty alt for a11y

## 3.0.14 - 3 August 2020
- Deactive "big image size threshold"
- Add compatibily with native image generation ( without WP Thumb )

## 3.0.13 - 31 July 2020
- Load image size generation on plugins_loaded thanks to @Rahe for this pull request

## 3.0.12 - 15 May 2020
- Fix $attr in bea_get_attachment_image, update error message

## 3.0.11 - 2 May 2019
- Fix "alt" in picture-lazyload-front mode

## 3.0.10 - 4 Dec 2018
- Fix CSS class for default img

## 3.0.9 - 23 Oct 2018
- Update ari_responsive_image_default_img_name filter to allow modify default img
- Fix notice on main class

## 3.0.8 - 27 Sep 2018
- Update ari_responsive_image_default_img_path filter to compat with BFF

## 3.0.7 - 30 Aug 2018
- Error with urlencode for empty space on BFF 

## 3.0.6 - 29 Aug 2018
- Fix url with empty space on front BFF 

## 3.0.2 - 22 Fev 2018
- Improve helpers for debug

## 3.0.1 - 7 Jan 2018
- Remove add_filter() method in interface
- Fix error on main.php file using filter post_thumbnail_html

## 3.0.0 - 3 Jan 2018
- Breaking changes : use post_thumbnail_html filter to render HTML not get_attributes
- Fix empty alt for W3C
- Remove src on source element, use srcset

## 2.0.5 - 18 Dec 2017
- Check data-location exists on post_thumbnail_html filter

## 2.0.4 - 13 Dec 2017
- Add no script image in Picture Lazyload Mode / Lazysize for SEO
- Load default image if no post_thumbnail

## 2.0.3 - 27 Nov 2017
- Add bea_get_attachment_image() function
- Add lazyload classes in picture lazyload mode

## 2.0.2 - 20 Nov 2017
- Remove composer require plugins, use suggest

## 2.0.1 - 20 Nov 2017
- Add some composer require plugins

## 2.0.0
- New version with multiple implementations

## 1.0.0
- Initial draft plugin ( not working )
