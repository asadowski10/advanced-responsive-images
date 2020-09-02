# Changelog

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
