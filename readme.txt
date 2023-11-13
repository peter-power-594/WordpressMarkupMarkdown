=== Markup Markdown ===
Tags: Editor, Markdown
Stable Tag: 2.6.0
Version: 2.6.0
Requires at least: 4.9
Tested up to: 6.4.1
Requires PHP: 5.6.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Disable Wordpress's native Gutenberg or TinyMCE editor in favor of a Markdown editor.

== Description ==

This plugin replaces the Gutenberg block editor (or the classic TinyMCE) on the edit screen with [EasyMDE](https://easy-markdown-editor.tk), a markdown editor based on CodeMirror.

The content is saved with the markdown syntax in the database and is rendered on the frontend via wordpress native filters thanks to the [Parsedown](https://parsedown.org) PHP library.

This extension rocks:
- Sticky toolbar for the default editor since 2.6
- Possible to disable OP Cache since 2.5
- Audio & Video playlist support added since 2.4
- New beta interface since 2.3
- Possible to enable or disable specific addons since 2.2
- Gallery shortcode support since 2.1
- ACF markdown field since 2.0
- Multilingual spell checking since 1.9
- Disable markdown for specific custom post type since 1.7
- Extra markdown syntax support since 1.4
- Static cache files since 1.3
- Autoconvert Youtube & Vimeo links to iframes since 1.2
- Support with lightbox and masonry for the gallery layout since 1.1

That's pretty all you should know. It's under active development, keep in touch and feel free to drop a line on the forum, to let a rating or even support me by buying a coffee!

== Frequently Asked Questions ==

= How to install =

The same as usual:

1. Just download and upload the zip file to your wordpress instance. Or install directly by simply searching from the WP plugin panel.
2. Activate the extension.

All done! That's all you should do.

= Is it still compatible with Gutenberg or any other builder?

**Yes but you can't use both at the same time ;-)**  
Data are saved as pure markdown code in the database, for the other editors on the market data are saved as HTML or custom markups like shortcodes. Currently data are still saved but won't be converted or rendered correctly if you revert back or switch between editors.

= Can I switch between editors or allow the markdown editor for specific users ? =

**Yes, please keep in mind it's a global switch for every user. You need to stick to one editor with one post type.**  
Any block editor will be disabled for **all the users** of your Wordpress instance. _filters_ are available to disable the markdown editor for specific custom post types at a global level. For example you can do a setup to use _Divi_ or _Elementor_ to edit your pages and _Markdown_ to edit your blog'posts. Please refer to the forum to know how to do it.

= Can I use it with custom fields? (Or within my theme) =

Sure, developers & designers can access the public properties & methods of the instance inside their templates through the global _mmd_ function. For example let's say you want to use it with a custom field called 'foo_bar'. You can use something
like that:
``echo mmd()->markdown2html( get_post_meta( get_the_ID(), 'foo_bar' , true ) );``

= What's the deal with the beta interface? =

The default editor is based on EasyMDE so you can write in markdown and use Wordpress feature at the same time.  
The beta interface is based on SummerNote, a jQuery WYSIWYG Engine. It's a custom version so you can get a live rendering when typing your content or adding medias. It's working quiet well actually but if something's wrong, you may have to edit directly the code from the builder or from the database so for the production environment I would advise to stick with the default options with EasyMDE. To find you more check my article here: https://red.phutu.red/blog/wordpress-plugins/dynamic-input-method-markdown/

== Changelog ==

= 2.6.0 =

Improvement:
- Adding option to make the default EasyMDE toolbar sticky

= 2.5.1 =

Bug fixes:
- PHP: Addons config file not properly created with new installs & upgrades
- JS: Undefined variables when the spell checker was not activated

= 2.5.0 =

Improvement:
- Adding option to disable the static html cache

= 2.4.0 =

Improvements:
Possible to do the following actions from the media uploader
- Adding an audio file
- Adding a video file
- Creating an audio playlist
- Creating a video playlist

Bug fixes:
- Music or movies was not usable
- Inserting multiples images at once or creating a gallery should work properly
- Solve an issue to avoid duplicate ids with images when using custom fields
- Better performance with a unique media wizard per page instead of one media wizard per custom field

= 2.3.0 =

Improvements:
- New beta interface based on HTML markups for better accessibility
- A few dependencies are now loaded from the UNPKG CDN (https://unpkg.com/)

Bug fix:
- The parser has been patched to be compatible with PHP >= 8.X

= 2.2.2 =

Bug fix:
- Lightboxes working again with the gallery shortcode

= 2.2.1 =

Bug fixes:
- Adding missing 2 & 3 columns styles for the gallery in the preview rendered by EasyMDE
- Shortcodes now working in the preview page rendered by Wordpress (The one with /?preview=true in the url)

= 2.2.0 =

Improvement:
- Enabling "Screen options" on the top right area of the settings page so you can activate or disable addons one by one

= 2.1.2 =

Bug fixes:
- Forcing WP medias tools to be available to avoid errors with some specific hosting
- CodeMirror instances now available from wp.pluginMarkupMarkdown.instances array for developers

= 2.1.0 =
Improvements:
- Better media support with native Wordpress Modal UI for editor in the admin panel
- Tiny responsive features added for images uploaded via Wordpress on the frontend (srcset)
- *Alignment* and *caption* now works, converted as <figure> on the frontend
- Default [gallery] shortcodes should support *columns* & *size* attributes as well
- Syntax highlighting enabled in the preview

Bug fixes:
- 404 with one single dictionary activated
- Styles broken in the preview (conflict with default admin panel style)

= 2.0.2 =

Bug fix:
- Make the [my_gallery] shortcodes rendered as thumbnail galleries in the preview

= 2.0.1 =

Bug fixes:
- Patched the error when the config file was not found after upgrading

= 2.0.0 =
Code refactoring. Addon framework created !
Please setup and save again your settings again if need be. Sorry for the inconvenience.

New feature:
- Advanced Custom Field (ACF) support added with a "Markup Markdown" content field

= 1.9.3 =
Improvement:
- Add Polylang compatibility by switching dictionaries order when editing a post in an alternative language

= 1.9.2 =
Bug fixes:
- Undefined variable with fresh install
- Editor was blocked in case one of the dictionary was not found

= 1.9.1 =
New features:
- Spell checking (experimental)
- Fresh settings panel

Bug fixes:
- Update EasyMDE version to 2.18.0 (Wrong version in previous commit, my bad)

= 1.7.4 =
- Deleting experimental user interface
- Adding WP_MMD_RAW_DATA constant for developers to disable the HTML output filter
at a global level through the wp-config.php file or at a local level with hooks
inside your child theme

= 1.7.3 =

Bug Fixes :
- Masonry setup fixed for the gallery layout

= 1.7.2 =

Improvements:
- New settings page with default layout options

= 1.7.1 =

Improvements:
- Adding feature to disable the editor for custom post types

In your child theme just turn off the markdown editor by adding the following snippet :

``php
add_action('init', function() {
  remove_post_type_support('post_type_slug', 'markup_markdown');
});
``

Bug Fixes:
- Remove debug lines

= 1.6.0 =

Improvements:
- Removing curl straight dependencies in favor of wp_remote_get functions
- Youtube oembed support added
- Remove unused files

= 1.5.4 =

Bug Fix:
- Public method mmd()->markdown2html fixed

Since the cache support from v1.3, the method was broken when used in templates.
The cache is only restricted to the post / page *content*.

= 1.5.3 =

Improvement:
- Vimeo arguments support added.

Please refer to the link below for the complete list :
https://developer.vimeo.com/api/oembed/videos#embedding-a-video-with-oembed-step-1

Ex: https://vimeo.com/30198629/&quality=360p

= 1.5.2 =

Bug Fixes:
- Space at the beginning of the file and other typo
- Unknown unused callback

= 1.5.1 =

Bug Fixes:
- Multiple Vimeo url not working
- Non existent minified related map files removed

Experimental UI:
- Headlines input fixed
- HTML tags sidebar's prototype released

= 1.5.0 =

Experimental UI: Adding modern syntax via summernote wysiwyg
Find out more here: https://www.youtube.com/watch?v=cl2P5zUXAmU

= 1.4.0 =

Improvement: Parsedown extra plugins added so you use extra features

= 1.3.3 =

Bug Fixes:
- Site ID used for the static cache rules, now compatible with network sites
- Regexp updated for vimeo videos
- Espaced quotes fix and lower filter priorities so other shortcodes can be parsed properly

= 1.3.2 =

Bug Fix: adding rules if cache directory not available

= 1.3.1 =

Adding cache via static files in the mmd-cache directory.

= 1.2.5 =

Adding Vimeo support. Vimeo links will be converted to iframes.

= 1.2.4 =

Bug Fix: Enabling gallery with archive templates

= 1.2.3 =

Bug Fix: Editor styles adjusted with Wordpress

= 1.2.2 =

Bug Fix: Single image button not working properly

= 1.2.1 =

New Feature: Image Gallery using Lightbox and Masonry for the image gallery post format

= 1.1.2 =

Bug fix: modal preloading (Double click was required to add an image from the library)

= 1.1.1 =

Adding Youtube support. Youtube links will be converted to iframes.

= 1.0.1 =

Updates to match the Wordpress Plugin Directory requirements

= 1.0.0 =

First version ! ! !  Based on:
- Javascript easyMDE 2.15.0
- PHP Parsedown 1.7.4

Developers can access the instance instance through the mmd() global function
