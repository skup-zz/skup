=== AnyFont ===
Contributors: choon
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=NRKYAMNJMBNAN
Tags: images, styles, fonts, admin, theme, enhancement, truetype, opentype, typography, ttf, font, plugin, css3, @font-face, webfont, titles, posts, pages, wpmu
Requires at least: 2.7
Tested up to: 3.1
Stable tag: 2.2

Use any TrueType or OpenType font to replace standard fonts anywhere on your site using images or webfonts! New in 2.2: IIS is now fully supported!


== Description ==

AnyFont allows you to automatically set any custom TrueType or OpenType font absolutely anywhere you want on your WordPress site.

Easily embed your custom fonts directly into your web pages using the new "@font-face" CSS rule. The new and improved Font Manager now includes the option to convert fonts to all the different webfont formats with a single click(Requires free sign up at [FontServ.com](http://fontserv.com)).

CSS3 "@font-face" support means you can now embed fonts into web pages and enable everyone to see your custom fonts without using any images, Flash or JavaScript.

**Upgrade your FontServ account to Pro for as little as $18 for 12 months**

To take advantage of this limited offer, browse to [FontServ.com](http://fontserv.com/).

**Features:**

* WPMU/WordPress 3 compatible with full support for multiple sites.
* Font Manager to easily upload truetype or opentype fonts to WordPress
* Easily convert your fonts to webfont formats. (Requires free sign up at [FontServ.com](http://fontserv.com/))
* FontServ webfonts [support all the major browsers](http://fontserv.com/help/) including Internet Explorer.
* Character Map to quickly check which characters are available for each font.
* Style Management which allows an unlimited number of different styles to be created.
* Apply font shadows easily using the Style Manager.
* TinyMCE Button for quick and easy insertion of AnyFont styled text into your posts or pages.
* Image Cache for generated images plus browser caching is enabled for images to reduce page load times.
* Cache overview and management tool.
* Easy text replacement options for menus, post titles, page titles, widget titles, blog name and blog description.
* Advanced option which allows you to apply styles to any element or css selector(class name or ID).
* Image replacements are SEO compatible.
* Help icon for every single option to guide you when setting things up for the first time.
* Image Styles support either PHP4+GD or PHP5+Imagick/GD.
* Officially tested on Apache and Microsoft IIS(Windows 2008 R2 Enterprise running IIS 7), but is also known to work with nginx and various other web servers.

**Translations:**

* Belarusian - [FatCow](http://www.fatcow.com)
* Turkish
* Dutch - [WP Webshop](http://wpwebshop.com)
* German - [Maco](http://www.macozoll.de)
* Russian - [Sasha](http://forex-trader.pp.ua/)
* Ukranian - [Pavel](http://wlstyling.crimea.ua/)

== Changelog ==

= 2.2 =

* Feature: Added new option to disable image permalinks when WordPress permalinks are enabled.
* Feature: Added various custom capabilities which can be assigned to user roles.
* Bugfix: Fixed numerous issues relating to Microsoft IIS Server.

= 2.1.4 =

* Bugfix: Incorrect post title in previous/next post links. (big thanks 2 [vxoxo](http://wordpress.org/support/profile/vxoxo) for pointing this one out.)
* Enhancement: Added version check to ensure the update functions get called after updating AnyFont files to the latest version, without needing to deactivate and then reactivate the plugin.

= 2.1.3 =

* Bugfix: Fixed custom css rules getting disabled when changing style or adding new rules.
* Bugfix: Deleting a single font file in a font-family no longer removes the whole family.
* Bugfix: Fixed incorrect URL in a WPMU child site when domain mapping is enabled for the site.

= 2.1.2 =

* Bugfix: Fixed dashboard widget error.
* Bugfix: Added ssl detection for css3 font urls.

= 2.1.1 =

* Feature: Added a compatibility mode which generates usable image links for WordPress sites which do not have permalinks enabled.
* Enhancement: Removed mod_rewrite dependency by default. Pretty links will now fall back on WordPress functionality. NOTE: Your image links will NOT change as long as permalinks are enabled.
* Enhancement: Advanced settings are now only accessible to the Super User in WPMU installations.
* Enhancement: Improved CSS3 font embedding to include all available font styles in any given font-family.
* Bugfix: Fixed conversion problem where only a single style in the font-family got converted for CSS3 styles.
* Bugfix: Fixed bug where only one font style was available for any given font-family.

= 2.1 =

* Feature: Added a new option which adds the various font mime types to Apache.(option is disabled by default)
* Enhancement: Added method to use deflate if its available on the server and is supported by the client.
* Enhancement: Improved detection process of  Apache, mod_rewrite and .htaccess.
* Enhancement: Added message which prints out the rewrite rules for Apache or ngix allowing users to manually edit the .htaccess file/rewrite rules if they wish.
* Bugfix: Fixed a bug with the uninstall process to address problem with htaccess rules not getting removed.

= 2.0.14 =

* Bugfix: Fixed a couple bugs which caused AnyFont to incorrectly determine the WordPress install path.

= 2.0.12 =

* Bugfix: Minor changes to improve compatibility with older versions of WordPress.

= 2.0.11 =

* Bugfix: FontServ.com API key status was not updating when saved.
* Enhancement: Switched the FontServ API class to use WP_Http instead of PEAR for better compatibility.

= 2.0.10 =

* Bugfix: text distortion when enabling text shadows (Only affected Imagick module)
* Bugfix: JavaScript errors while editing or creating styles
* Bugfix: IE specific JavaScript errors on admin pages.
* Enhancement: Tweaked some admin page styles for better cross browser compatibility.

= 2.0.9 =

* Bugfix: Parse error: syntax error, when using PHP4.

= 2.0.8 =

* Bugfix: Font reset issue which affected existing CSS3 styles when changes to the style were saved.

= 2.0.7 =

* Bugfix: Return HTML on font upload.
* Bugfix: Deleting multiple styles.
* Bugfix: Minor JavaScript errors.

= 2.0.6 =

* Enhancement: Added translation: Russian (ru_RU) - Thanks [Sasha](http://forex-trader.pp.ua/).
* Enhancement: Added translation: Ukrainian (uk_UA) - Thanks [Pavel](http://wlstyling.crimea.ua/).
* Feature: Added ability to delete custom css style assignments.
* Bugfix: Fixed various errors which were occurring when adding/disabling custom css style assignments.

= 2.0.5 =

* Feature: Added line-height option to css styles.
* Bugfix: Added sanity checks to prevent PHP warnings on line 1066 of anyfont.php when css text replacements are enabled but no style exists.
* Bugfix: Corrected PHP5 detection to fix occasional false positives when trying to convert a font.
* Bugfix: Error resulting in AnyFont not functioning at all when using PHP4.

= 2.0.4 =

* Updated German translations (Thanks Anton)
* Bugfix: Added sanity checks to prevent PHP warnings on line 149 of class.admin.php (Thanks Frank)

= 2.0.3 =

* Bugfix: Fixed PHP Warning on line 304 of class.admin.php (Thanks [Andrew](https://twitter.com/A_n_d_y_P))

= 2.0.2 =

* Feature: Added support to assign a style to any element or css selector(class name or ID).

= 2.0.1 =

* Bugfix: PEAR include errors.

= 2.0 =

* Feature: Added support for '@font-face' CSS styles.
* Feature: Added new auto replacement options to assign '@font-face' styles.
* Feature: Integrated with [FontServ.com](http://fontserv.com/) to automatically convert fonts to web formats. (WOFF, EOT, SVG)
* Feature: Added option to backup and restore styles and/or fonts using [FontServ.com](http://fontserv.com/) (Premium accounts only).
* Enhancement: Added support for custom post types title replacements.

= 1.1.3 =

* Enhancement: Added translation: German (de_DE). (Thanks Anton. [Maco](http://www.macozoll.de))
* Enhancement: Improved the menu title replacement code which determines the active page.
* Enhancement: Added https support for image urls.
* Bugfix: Fixed incorrect path for cache and font directorys when using multisite features.
* Bugfix: Fixed page title replacements not working correctly with newer versions of PHP5.

= 1.1.2 =

* Bugfix: Form inputs for max-width and text alignment did not work correctly after creating a new style.
* Bugfix: Parse Error in the admin class (Thanks for letting me know about this one Tim!)

= 1.1.1 =

* Feature: Added new setting to control text alignment when a maximum width is set.(feature sponsored by [writershouses.com](http://writershouses.com))
* Enhancement: Extended help with text which is included in the WordPress contextual help area.(help button located top right in the admin pages)
* Bugfix: Fixed minor bug with the menu replacements

= 1.1 =

* Feature: Added new setting to control line height on multi-line images.(feature sponsored by [writershouses.com](http://writershouses.com))
* Enhancement: Added translation: Dutch (nl_NL).
* Enhancement: Extended menu replacement support to include the new WordPress 3 Menus.
* Enhancement: Changed the value of the "max-width" setting from characters to pixels.
* Enhancement: Extended the image-padding option to allow different padding settings for top, bottom, left and right.
* Bugfix: Various minor bugs fixed.

= 1.0.3 =

* Bugfix: All saved styles not displaying correctly after upgrade. (only GD users affected)

= 1.0.2 =

* Enhancement: Added translation: Turkish (tr_TR).
* Bugfix: Removed PHP Warning message which displayed if the destination folder already existed when trying to rename folders.

= 1.0.1 =

* Bugfix: Added styles check on activation to remove depreciated admin style.
* Bugfix: Plugin was reported to be affecting WordPress core as per [this bug](http://core.trac.wordpress.org/ticket/11974). Unable to reproduce error but changes have been made with AnyFont which should solve the problem.  (Thanks Jim!)

= 1.0 =

* Feature: Option to automatically change menu text for any menu generated using the wp_list_pages or wp_page_menu WordPress functions.  Included in this feature is the ability to assign styles for hover(mouseover) and the active page.
* Feature: Automatic plugin “health” checks with alerts and tips on how to solve the problem that was detected.
* Feature: Advanced controls for power users that want to set custom locations for saving fonts or cache files.
* Feature: New Style setting which allows you to add extra space around an image should you find your text is getting cut off.
* Feature: Preview any changes you make to a style before saving.
* Feature: Copy an existing style to a new style.
* Feature: A help icon has been added to every single option in AnyFont to help guide you when setting things up.
* Enhancement: Extended Cache Management settings to limit the amount of disk space used.
* Bugfix: Images slow to load or occasionally timed out.
* Bugfix: Issue with certain characters being double encoded when inserting AnyFont styled text into a post/page.

= 0.9.1 =

* Enhancement: Included Belarusian translation.
* Bugfix: blank space below generated images.
* Bugfix: IE JavaScript error which prevented the Style Manager page from loading correctly.

= 0.9.0 =

* Feature: Added TinyMCE button to easily insert AnyFont styled text into posts/pages.
* Feature: Added an option to view a character map for any font in the Font Manager.
* Enhancement: Added support for WPMU environments.
* Enhancement: Uploaded font files are now stored correctly in the WordPress uploads folder.
* Enhancement: Added confirmation step when deleting fonts.
* Bugfix: Various issues with image cache.

= 0.8.7 =

* Bugfix: Corrected a caching bug which sometimes caused images to be generated with every request.

= 0.8.6 =

* Bugfix: Font select dropdown not selecting font when creating a new style.

= 0.8.5 =

* Bugfix: Fixed reference to "self" in PHP4 class. (Thanks Jose).

= 0.8.4 =

* Bugfix: Image shadow sometimes got cut off when shadow spread was set higher than 2. (Imagick Only).
* Bugfix: Unable to read multiple styles(Bold, Oblique, etc) when a font family had multiple styles uploaded. (reported by [@icithis](http://is.gd/5aVOC). :)
* Enhancement: Improved Font Manager page, now shows font styles available and the fonts copyright notice, if any.

= 0.8.3 =

* Bugfix: Found a solution to the issue where text was getting cut off and so far its been confirmed as working on 2 servers which previously gave different results.
* Bugfix: Fixed an error in the PHP4 admin class where PHP5 specific naming conventions were being used.

= 0.8.2 =

* Bugfix: Font list is correctly sorted into alphabetical order.
* Bugfix: Displays the correct font name instead of simply the filename.
* Bugfix: Minor CSS and other visual improvements.
* Enhancement: The AnyFont rewrite rules in .htaccess are now only required if your blog does NOT have permalinks enabled.
* Feature:  New automatic process to determine the rewrite requirements.

= 0.8.1 =

* Bugfix: Imagick issue where images would not display after 0.8.0 upgrade.
* Feature: Option to set the shadow distance.
* Feature: Option to soften the shadow. (GD module only)

= 0.8.0 =

* Bugfix: Text getting cut off in generated images.
* Bugfix: Browser not caching images on some WordPress installations.
* Feature: Shadows are now available for the GD image module in the style manager.
* Enhancement: Font previews are displayed in the select box dropdown when selecting a font in the style manager.
* Enhancement: Font sizes are no longer limited to the selection in the dropdown, the size can be now overridden by just typing a custom size.
* Enhancement: Shadows have been enhanced when using Imagick with a new option called shadow spread which softens the shadow.

= 0.7.4 =

* Bugfix: Internet Explorer font uploads.
* Bugfix: Missing colour selector on some shadow-colour input boxes.
* Bugfix: Improved compatibility with custom WordPress installations.
* Enhancement: AnyFont will now automatically detect and repair broken configurations and if the problem cant be fixed automatically, a message will be displayed giving a possible solution.

= 0.7.3 =

* Bugfix: Widget Title replacements will retain the original $before_title and $after_title variables, which really improves compatibility with highly customised themes. (thanks Gavin!)
* Bugfix: Text should now be left aligned if the style has a character limit

= 0.7.2 =

* Bugfix: Correctly escaped some unescaped characters in a regular expression in the template class (Thanks Joe!)
* Bugfix: Checkboxes for newly created styles should function correctly without needing to refresh the page
* Bugfix: Fonts are listed correctly in the dropdown after saving a new style

= 0.7.1 =

* Feature: New options to replace plain text version for tag titles and category titles.
* Enhancements: Style interface has been cleaned up by hiding any unused options.
* Bugfix: width limit option displaying as off even when enabled.
* Bugfix: correct font was not selected for created styles in style manager (PHP4 & GD version)
* Bugfix: width limit options are now available for styles created before version 0.7.0

Full changelog is available [here](http://2amlife.com/projects/anyfont/changelog)

== Upgrade Notice ==

= 2.2 =
RECOMMENDED UPDATE: Fixed all issues relating to Microsoft IIS7 Server, added new features and improved stability in various other areas. see changelog for details.

= 2.1.4 =
Fixed bug which caused the previous/next post link titles to have the same post title as the current post.

= 2.1.3 =
Fixed problem with custom css rules getting disabled on update and improved compatibility with domain mapping in wpmu environments.

= 2.1.2 =
Minor update to fix a loading error with the dashboard widget and added ssl detection when loading css3 fonts. If you updating from version 2.1 or below, please read the changelog for important updates.

= 2.1.1 =
Removed dependency on .htaccess and mod_rewrite.  Please note your image links WILL NOT CHANGE as long as you have permalinks enabled in WordPress, although users who have permalinks disabled or depend on IIS permalinks, will now be able to use AnyFont as normal. Read the changlelog for more changes.

= 2.0.14 =
Fixed some problems which caused AnyFont to incorrectly determine the WordPress install path.

= 2.0.11 =
Fixed the problems some people were experiencing with the FontServ API key not saving correctly. see changelog for full details.

= 2.0.10 =
Bugfix release which fixes numerous issues that were reported recently. see changelog for details.

= 2.0.9 =
Bugfix release, see changelog for details.

= 2.0.8 =
Fixed font reset issue which affected existing CSS3 styles when changes to the style were saved.

= 2.0.7 =
Bugfix release, see changelog for details.

= 2.0.6 =
Fixed all bugs relating to custom css style assignments and added the ability to delete a custom css style assignment.

= 2.0.5 =
Added line-height feature to CSS styles and fixed various bugs, see changelog for details.

= 2.0.4 =
German translations were updated and fixed a minor php warning output.

= 2.0.3 =
Fixed PHP Warning on line 304 of class.admin.php (Thanks [Andrew](https://twitter.com/A_n_d_y_P))

= 2.0.2 =
Included new feature which allows you to assign a style using an element or css selector.

= 2.0.1 =
Minor update which fixes PEAR include errors. My apologies to all those affected by this bug.

= 2.0 =
Major Update: Now has support for CSS3 @font-face which means you are able to embed fonts into your site enabling everyone to see your custom fonts without using any images, Flash or JavaScript.

= 1.1.3 =
Fixed minor bug with page titles plus some minor enhancements made to existing features, see changelog for details.

= 1.1.2 =
Fixes error caused by last update. If you were affected by the bug, please accept my apologies for any inconvenience it may have caused you.

= 1.1.1 =
Bug in menu replacements fixed. Added new feature to control text alignment.

= 1.1 =
WordPress 3 support added. NOTE: The value for the style option 'max-width' has been changed from characters to pixels, if you are using this option in your styles you should check and update your styles as soon as the update is complete.

= 1.0.3 =
Fixed bug which prevented all styles from displaying correctly in the Style Manager. (Only affected those using the GD image library)

= 1.0.2 =
Fixed PHP warning message about file rename and added Turkish translation.

= 1.0.1 =
Minor update with fix for those affected by the admin style bug.

= 1.0 =
Fixed bug which was causing slow image loading and occasional timeouts plus loads of other new features and improvements. See changelog for details.

= 0.9.1 =
IE JavaScript bug fix and extra space below generated images has been removed.

= 0.9.0 =
This release has some major improvements to the serverside image generation and cache which should speed up page load times. See changelog for list of new features.


== Screenshots ==

1. New Style Manager with CSS3 Styles.
2. New Improved Font Manager.
3. General Settings.
4. Notepad Theme showing the top menu and sidebar menu after enabling menu replacements in AnyFont.
5. TinyMCE Integration.

== Frequently Asked Questions ==

= After enabling the post title replace option on the settings page, all my post titles start with a "&#62; why?? =

This is quite a common error in themes, but quick and easy to fix, open up your theme's index.php file, look for a line that looks similar to the following:

>&#60;h2  class="posttitle"&#62;&#60;a href="&#60;?php the\_permalink() ?&#62;" rel="bookmark" title="Permanent Link to &#60;?php the\_title(); ?&#62;"&#62;&#60;?php the\_title(); ?&#62;&#60;/a&#62;&#60;/h2&#62;

The problem lies in the title attribute, it should read:

>"Permanent Link to &#60;?php the\_title\_*attribute*(); ?&#62;"

= My Hosting Provider says ImageMagick is already installed, but AnyFont doesn't see it? =

If you want to use ImageMagick over GD you need to ensure that the Imagick PHP extension to ImageMagick is also installed. [Click here for more info on Imagick](http://www.php.net/manual/en/book.imagick.php)

= I am using the new menus available with WordPress 3.0 and I can't get AnyFont to automatically change the menus. Are the new WordPress menus supported by AnyFont? =

Yes, the new menus in WordPress 3 are supported, but for them to work automatically with AnyFont, you will need to make sure that the 'Title Attribute' is set for each entry in your menu.

= Its just not working for me! HELP! =

The best way to get support is via your [account](http://fontserv.com/account/) page at [FontServ.com](http://fontserv.com/). If don't have an account yet, [Sign Up Here](http://fontserv.com/packages/)


== Installation ==

*Server Requirements:* PHP4 or PHP5 and either the ImageMagick(imagick module 2.1.1-rc1 and up) or GD image module installed.

Upload the AnyFont plugin to your WordPress site or install via the Plugins Page and then activate it!

If you don’t already have an account at [FontServ.com](http://fontserv.com/). [Sign Up Here](http://fontserv.com/packages/) to get your API key.