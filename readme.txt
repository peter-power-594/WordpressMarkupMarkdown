=== Markup Markdown ===
Tags: Editor, Markdown
Stable Tag: 3.13.0
Version: 3.13.0
Requires at least: 4.9
Tested up to: 6.7.1
Requires PHP: 5.6.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html#license-text
Donate Link: https://ko-fi.com/peterpower594

Disable Wordpress's native Gutenberg or TinyMCE editor in favor of a Markdown editor.

== Description ==

This plugin replaces the Gutenberg block editor (or the classic TinyMCE) on the edit screen with [EasyMDE](https://github.com/Ionaru/easy-markdown-editor), a markdown editor based on CodeMirror.

The content is saved with the markdown syntax in the database and is rendered on the frontend via wordpress native filters thanks to the [Parsedown](https://parsedown.org) PHP library.


This extension rocks:
- v3.13: Adding a new filter to toggle on/off Gutenberg in the admin screen and new autoplug for Code Snippets
- v3.12: Adding support for LaTeX via Katex or MathJax
- v3.11: Bug fix with code block fences, compatible with plugins like Prismatic and new autoplug for CodeMirror Blocks (Syntax Highlighter)
- v3.10: Adding compatibility with the BuddyPress and BuddyPress Docs plugins via an autoplug, possible to disable autoplugs from the settings panel
- v3.9: Adding compatibility with the bbPress plugin via an autoplug 
- v3.8: Adding compatibility with the O2 plugin via an autoplug, support for using # signs as ordered list
- v3.7: Adding support for selective heading levels
- v3.6: Performance improvements with spellchecker and suggestions
- v3.5: Adding support for right-to-left alphabets like Arabic, Hebrew, or Persian
- v3.4: Adding support for categories, tags and taxonomies description field (Woocommerce and REST API compatible)
- v3.3: Support for multiple html attributes, compatibility with acf_form added for the frontend, basic compatibility with block styles
- v3.2: Support to enable markdown only for custom fields
- v3.1: Side preview panel fixed
- v3.0: Choose and sort the default toolbar buttons
- v2.6: Sticky toolbar with the editor
- v2.6: Possible to disable OP Cache
- v2.5: Video playlist support added
- v2.3: New beta interface
- v2.2: Possible to enable or disable specific addons
- v2.1: Gallery shortcode support
- v2.0: ACF markdown field support
- v1.9: Multilingual spell checking support
- v1.7: Disable markdown for specific custom post type
- v1.4: Extra markdown syntax added
- v1.3: Static cache files with OP Cache enabled by default
- v1.2: Autoconvert Youtube & Vimeo links to iframes
- v1.1: Support with lightbox and masonry for the gallery layout

That's pretty all you should know. It's under active development, keep in touch and feel free [to drop a line on the forum](https://wordpress.org/support/plugin/markup-markdown/), [to let a rating](https://wordpress.org/support/plugin/markup-markdown/reviews/) or even support me by buying a coffee !

== Frequently Asked Questions ==

= How to install =

The same as usual:

1. Just download and upload the zip file to your wordpress instance. Or install directly by simply searching from the WP plugin panel.
2. Activate the extension.

All done! That's all you should do.

= Is it still compatible with Gutenberg or any other builder?

**Yes but you can't use both at the same time ;-)**
Data are saved as pure markdown code in the database, for the other builders on the market data are saved as HTML or custom markups like shortcodes. Currently data are still saved but won't be converted or rendered correctly if you revert back or switch between editors.

= Can I switch between editors or allow the markdown editor for specific users ? =

**Yes, please keep in mind it's a global switch for every user. You need to stick to one editor with one post type.**
Any block editor will be disabled for **all the users** of your Wordpress instance. _filters_ are available to disable the markdown editor for specific custom post types at a global level. For example you can do a setup to use _Divi_ or _Elementor_ to edit your pages and _Markdown_ to edit your blog'posts. Please refer to the forum to know how to do it.

= Can I use it with custom fields? (Or within my theme) =

Sure, developers & designers can access the public properties & methods of the instance inside their templates through the global _mmd_ function. For example let's say you want to use it with a custom field called 'foo_bar'. You can use something
like that:

`<?php echo mmd()->markdown2html( get_post_meta( get_the_ID(), 'foo_bar' , true ) ); ?>`

Disclaimer: with the plugin Advanced Custom Field (ACF), HTML content has been sanitized since v6.2.5.
If you need to render iframes or others elements, instead of using:

`<?php the_field( 'my_custom_field' ); ?>`

 use

`<?php echo mmd()->markdown2html( get_field( 'my_custom_field' ) ); ?>`

= What's the deal with the beta interface? =

The default editor is based on EasyMDE so you can write in markdown and use Wordpress feature at the same time. The side panel preview mode has been fixed since 3.0.
The beta interface is based on SummerNote, a jQuery WYSIWYG Engine. _The beta interface has been removed since 3.0 and will be available as a separate addon._ It's a custom version so you can get a live rendering when typing your content or adding medias. It's not perfect, it's still working well but for now you will have to modify the code on your own (builder or database) if something goes wrong. To find out more check my article here: https://www.markup-markdown.com/blog/wordpress-plugins/dynamic-input-method-markdown/

= Accessibility =

The current version is based on components that are not compatible with assistive devices like screen readers. Several available alternative plugins could cover the gap while I'm working on a new interface. Thank you for your patience and your understanding.


== Changelog ==

= 3.10.2 =

Bug fixes:
- Updating a few translations
- Various tiny layout features patched

= 3.10.1 =

Bug Fix:
- Adding missing files

= 3.10.0 =

Improvements:
- _AutoPlugs_ tab added to the settings panel
- _BuddyPress_ and _BuddyPress Docs_ plugs added


= 3.9.1 =

Bug fix:
- Solved heading levels upper than 2 rendered as bullet list when heading level 1 was disabled 

= 3.9.0 =

Improvement:
- _bbPress_ added as an autoplug

Various tiny bug fixes

= 3.8.2 =

Bug fix:
- Solved heading levels upper than 2 not working when heading level 1 was disabled 

= 3.8.1 =

Bug fix:
- Solved an issue with EasyMDE overlay modes when the spell checker styles were not applied

= 3.8.0 =

Improvement:
If H1 heading is disabled from the options panel, then inside the WYSIYWIG you can use the sharp sign # for ordered list

Bug fix:
- Editor wasn't loaded properly if the spell checker was enabled but no dictionary was selected
- A few javascript errors fixed with the spell checker's suggestions when space or special chars were involved

= 3.7.4 =

Bug fix:
Fixing javascript error with selective headings when Code Mirror's overlay mode was not active. (Related to enabled options)

= 3.7.3 =

Bug fix:
Cleaning SVN Trunk >< Sorry for the inconvenience m(__)m

= 3.7.2 =

Bug fix:
Quick patch to add missing stylesheets assets since 3.7.0

= 3.7.1 =

Bug fix:
Quick patch to add an option to preserve spaces at the beginning of line

= 3.7.0 =

New feature:
- Adding support for selective heading levels

You can now prevent the use of specific headings like H1 in the WYSIWYG for better SEO and friendly theme compatibility

= 3.6.6 =

Bug fix:
- Removing unwanted spaces (trim) with the headlines

= 3.6.5 =

Quick bug fix:
- Patch the PHP error with existing instances when the spell checker is enabled and an extra dictionary file is missing

= 3.6.4 =

Bug fix:
- Adding basic strict mode back with Parsedown

Following the markdown recommendations, #headlines text (without the space after the # sign) are not rendered as headlines anymore.

Improvement:
- Keeping current hooks but adding a few tweaks earlier in the frontend so markdown can be triggered with themes built for Gutenberg.

*get_header* action won't be fired on the frontend with most of the themes using the blocks editor, *wp_head* will be too late to setup filters as content related data are prepared earlier in the rendering process.

= 3.6.3 =

Improvement:
- Adding possibility to load an extra dictionary file that could be used to add your own custom words in the future

= 3.6.2 =

Improvement:
- The spellchecker based on Typojs is shared between multiple code mirror instances
(Performance boost as one unique checker is used even if you are working with several custom markdown fields)

= 3.6.1 =

Improvements:
- _Disable Emojis_ added as an autoplug
- Rules added to exclude emoticons from the spellchecker

= 3.6.1 =

Improvement:
- Custom code mirror spellchecker updated

One unique event *CodeMirrorDictionariesReady* will be dispatched once all the dictionaries are loaded
*CodeMirrorSpellCheckerReady* will be fired when the spellchecker is ready to be used and attached to a code mirror instance.

= 3.6.0 =

Improvement:
- Primary scripts now minified as a unique bundle file

By default _builder.min.js_ is loaded.  
If _WP_DEBUG_ is enabled, separate minified module scripts will be used.  
If _SCRIPT_DEBUG_ or _MMD_SCRIPT_DEBUG_ is turned on, the unminified version available of a module will be loaded when available

= 3.5.0 =

New feature:
- Adding buttons and support for RTL

Improvements:
- Better support with multisite
- Hooks more friendly with plugins like CPT UI (Custom Post Type) and ACF (Advanced Custom Field)

= 3.4.2 =

Improvements:
- Basic internationalization strings added
- French version released as local for now

Bug fix:
- Adding css namespace for the toolbar buttons to avoid collision with Bootstrap on the frontend

= 3.4.1 =

Improvements:
- Adding missing filters in the REST Api
- Adding a plug for the description field with Woocommerce templates

Bug fix:
- Undefined variable in array with WP Geshi

= 3.4.0 =

Improvement:
- Adding markdown support by default for category, tags, and term descriptions

= 3.3.8 =

Bug fix:
- Activate markdown filters on the front page / home page as well

= 3.3.7 =

Bug fix:
- Patch a PHP warning if the align attribute was missing

Improvement
- Adding a few missing styles inside the preview panel

= 3.3.6 =

Bug fix:
- Fixing PHP error with undefined array key

= 3.3.5 =

Bug fix:
- Adding a whitelist on the REST hook to allow the rendering of markdown with REST content

= 3.3.4 =

Bug fixes:
- Removing blank icon in the toolbar when the spell checker addon was disabled
- Fixing the case when a custom image size was overriden by wordress predefined size

= 3.3.3 =

Bug fix:
- Patch a cache issue with (my) shared webhosting with the WP Geshi AutoPlug

= 3.3.2 =

Bug fix:
- Patch a javascript error with an undefined variable

= 3.3.1 =

Improvements:
- Markdown extra patches added to support HTML multi-attributes
`## Headline {#h2 .short .great lang=en}` => `<h2 id="h2" lang="en" class="short great">Headline</h2>`
- Basic block classnames added to headlines and images to avoid broken layout with themes built for Gutenberg
`### H3 Tag` => `<h3 class="wp-block-heading">H3 Tag</h3>`
- ACF Markup Markdown custom fields can now be used in the frontend with acf_form_head & acf_form

Bug fix:
- Static Cache is disabled by default to avoid side effects with a few cache engine
- Filters fixed: when cache was turned on, excerpt was returning the content value

New feature: AUTO PLUGS
Unlike *Addons* designed to add extra features to the editing experience, the *Plugs* will be designed to smooth the behavior of the rendering with existing WP plugins.
The first plug with WP Geshi Highlight has been added to allow the rendering of snippets on the frontend

= 3.2.6 =

Readme.txt Updated

= 3.2.5 =

Improvement:
- Patch to refresh the editor when the spell checker is disabled

= 3.2.4 =

Bug fix:
- Patch to refresh the view with frozen loader icons

= 3.2.3 =

Bug fix:
- Patch for markdown contents not rendered on archive templates and REST calls

= 3.2 =

Bug fix:
- Bug introduced with version 3 for the custom post type support filter has been fixed

Improvement:
- "ACF Markup Markdown" custom field with custom post type !

= 3.1.0 =

Improvements:
- The preview panel has a tiny cache feature to avoid flickering issue when using the side panel view
- Better support in responsive mode for the sticky toolbar and fullscreen mode

= 3.0.1 =

Hotfix:
- Namespace Patch for the Extra Parsedown

= 3.0.0 =

Refactored !

New feature:
- Possible to select and sort the buttons displayed from the toolbars

= 2.6.1 =

Bug fix:
- Forget to bump assets version number to avoid cache issues when upgrading

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
