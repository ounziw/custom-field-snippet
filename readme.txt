=== Custom Field Snippet ===
Contributors: ounziw
Donate link: http://pledgie.com/campaigns/8706
Tags: custom field, snippet, theme
Requires at least: 3.4
Tested up to: 3.5.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Creates Snippets like "echo get_post_meta($post->ID,'FIELD NAME',true);

== Description ==

This plugin creates and shows the snippets which display your custom field data. You can display your custom field data, by pasting these codes to your theme.

This plugin saves the time for theme developers/designers writing codes.

If you buy a license key, you can enjoy this plugin in any post type.
You can by the key at <a href="http://wp.php-web.net/?p=275">http://wp.php-web.net/?p=275</a>

== Installation ==

1. Upload `custom-field-snippet.php` to the `/wp-content/plugins/` directory

== Frequently asked questions ==

= Do I need Advanced Custom Fields plugin? =
Not necessary. You can enjoy this plugin with WordPress default custom fields.

= Do I have to buy a license key? =
No.
Only when you use this plugin in post types (i.e. other than post or page), you need to buy a license key.

= Can I use a license key for multiple sites? =
Legally, yes.
But it is ethically recommended to buy a license key for each site.

== Screenshots ==

1. snippet for custom field
1. when you use Advanced Custom Field

== Changelog ==

3.3 bug fix. jquery ui tab support, when you do not use Advanced Custom Fields.

3.2 Refactoring. add get_metadata() method, which returns an array of postmeta key/values.

3.1 bug fix. Show only snippets that match the post/page.

3.0 upport for Advanced Custom Fields 4.0

2.1.1 support for Advanced Custom Fields 3.5.7

2.1 add filter: cfs_tabs_class

2.0 JQuery UI tab. Supports user defined tab.

1.2 internationalization ready

1.1 new feature: Advanced Custom Fields repeater field