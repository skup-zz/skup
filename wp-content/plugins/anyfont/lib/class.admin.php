<?php
/*
    This program is free software; you can redistribute it and/or
    modify it under the terms of the GNU General Public License
    as published by the Free Software Foundation; either version 2
    of the License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin Street, Fifth Floor,
    Boston, MA  02110-1301, USA.
    ---
    Copyright (C) 2010, Ryan Peel ryan@2amlife.com
 */

class anyfontAdmin {

	var $page = false;
	var $page_html = false;
	var $tplPath = false;
	var $tpl = false;
	var $fontlist = array();
	var $modules = array();
	var $fs_status = array();
	var $styles = false;
	var $fontdir = true;
	var $cachedir = true;
	var $styleconfig = true;
	var $url ="";

	function __construct($config=false){
		$this->helptext = array(
			"default" => __("Help for this setting is not yet available.", 'anyfont'),
			"settings_menu_text" => __("Enables replacement of the text in your page menus with the styles chosen below.", 'anyfont'),
			"image-padding" => __("Enable this setting if the text in your generated images is cut off.", 'anyfont'),
			"image-padding-top" => __("Amount of empty space at the top of the generated image.", 'anyfont'),
			"image-padding-bottom" => __("Amount of empty space at the bottom of the generated image.", 'anyfont'),
			"image-padding-right" => __("Amount of empty space on the right of the generated image.", 'anyfont'),
			"image-padding-left" => __("Amount of empty space on the left of the generated image.", 'anyfont'),
			"use-htaccess" => __("Unless you are using an alternative mod_rewrite method you should NEVER disable this setting.", 'anyfont'),
			"add_mimetype" => __("Enable this setting if you are using CSS3 styles to ensure the correct mimetypes are sent to the client browser when fonts are downloaded.", 'anyfont'),
			"enable-custom-fontdir" => __("Enabling this setting will allow you to set the folder for uploaded font files.  Note: Changing this setting will automatically move any fonts you have already uploaded to the new folder.", 'anyfont'),
			"enable-custom-cache" => __("Enabling this setting will allow you to set the folder which is used to store generated images.  Note: Changing this setting will automatically move any images already cached to the new folder.", 'anyfont'),
			"enable_tinymce" => __("When this setting is enabled, a button will be added to the editor for posts/pages which allows you to apply AnyFont styles to text in your post.", 'anyfont'),
			"enable_gzip" => __("Enable this setting if you would like to compress AnyFont generated images using gzip before they sent to the browser.  Note: In some cases this may actually increase the size of the image as all generated images are already optimized.  Use at your own discretion.", 'anyfont'),
			"disable_hotlinking" => __("This will prevent any unauthorized websites from using your generated images on their own pages. Disabling this setting is not recommended.", 'anyfont'),
			"cache_show_bytes" => __("When enabled, Disk usage will be shown in bytes. This was the default behaviour prior to version 1.0 so this setting is here just in case someone actually liked it that way! ;)", 'anyfont'),
			"cache_dashboard_widget" => __("Enabling this will add an admin widget to your dashboard showing disk usage information for AnyFont", 'anyfont'),
			"limit-cache-size" => __("If you want to make sure AnyFont doesn't use more disk space than what you're allowed, enabling this setting will allow you to set a size limit.  If enabled, the cache will be checked once every day and cleaned only if it is found to be larger than the set limit", 'anyfont'),
			"cache-size-limit" => __("Enter the maximum amount of disk space AnyFont is allowed to use in MB", 'anyfont'),
			"post-titles" => __("When this setting is enabled, the primary text heading of your blog posts will automatically be replaced with a dynamically generated image which is 100% SEO friendly. Dont forget to select a style from the dropdown list after enabling this setting.", 'anyfont'),
			"page-titles" => __("When this setting is enabled, the primary text heading of your blog pages will automatically be replaced with a dynamically generated image which is 100% SEO friendly. Dont forget to select a style from the dropdown list after enabling this setting.", 'anyfont'),
			"tag_title" => __("When this setting is enabled, the tag name, which is normally displayed at the top of the page when browsing a specific tag, will automatically be replaced with a dynamically generated image which is 100% SEO friendly. Dont forget to select a styles from the dropdown list after enabling this setting.", 'anyfont'),
			"cat_title" => __("When this setting is enabled, the category name, which is normally displayed at the top of the page when browsing a specific category, will automatically be replaced with a dynamically generated image which is 100% SEO friendly. Dont forget to select a styles from the dropdown list after enabling this setting.", 'anyfont'),
			"widget_title" => __("When this setting is enabled, widget titles will automatically be replaced with a dynamically generated image. NOTE: Widget title images are NOT SEO friendly. Dont forget to select a styles from the dropdown list after enabling this setting.", 'anyfont'),
			"blog_title" => __("When enabled, AnyFont will attempt to style your Blogs Name in the site header using the chosen style.  Note: Results may vary depending on the theme.", 'anyfont'),
			"blog_desc" => __("When enabled, AnyFont will attempt to style your Blogs Description in the site header using the chosen style.  Note: Results may vary depending on the theme.", 'anyfont'),
			"menu" => __("Enabling this setting will allow you to set styles for menus generated with the wp_list_pages or wp_page_menu WordPress functions and the new menu builder available in WordPress 3(Requires the 'Title Attribute' to be set for each entry on the WordPress menus admin page).  Note: Results may vary depending on the theme.", 'anyfont'),
			"name" => __("The name of the style. Acceptable characters to use include letters, numbers, spaces, underscores and dashes.", 'anyfont'),
			"color" => __("The main color of the font face, Select a color by clicking the color square and choosing a preset or type in your own color code in hex format", 'anyfont'),
			"font-name" => __("Select an uploaded font from the list, if you have not yet uploaded any fonts, you should do so before attempting to create a style.", 'anyfont'),
			"font-size" => __("Select a size from the dropdown or type in a custom size.", 'anyfont'),
			"limit-width" => __("Enabling this setting will allow you to set a character limit for the width of the image, after which the text will then continue on a new line.", 'anyfont'),
			"max-width" => __("Set the pixel width for the generated images", 'anyfont'),
			"text-align" => __("Align the text to display either centered or left aligned(default) in the generated image.", 'anyfont'),
			"line-height" => __("Adjust the height for each line of text on multi-line images. NOTE: When set to '0px', this setting will be ignored and the default line height will be used", 'anyfont'),
			"shadow" => __("Enabling this setting will allow you to add shadow effects to the text", 'anyfont'),
			"shadow-color" => __("Set the color for the text shadow by clicking the color square and choosing a preset or type in your own color code in hex format", 'anyfont'),
			"shadow-distance" => __("This controls how far the shadow is from the text. The higher the value, the further down and to the right the shadow will appear.", 'anyfont'),
			"shadow-spread" => __("Control the amount of blur/stretch that is applied to the shadow.", 'anyfont'),
			"soften-shadow" => __("Enabling this setting will blur the shadow slightly and smoothly merge it into the background.", 'anyfont'),
			"background-color" => __("This must be set to the background color that the image will be placed on when used on your site. ie: If your site has a white background, this color should then be set to white.", 'anyfont'),
			"api-key" => __("Enter your API key which can be found on your Account page at FontServ.com. Sign up now for free if you do not yet have an account.", 'anyfont'),
			"body_text" => __("This setting will apply the selected style to <strong><em>all</em></strong> the text on every page of your site.", 'anyfont'),
			"content_text" => __("This setting will apply the selected style to just the content element on every page of your site. WARNING: This setting may not work with all themes as it depends on there being a div element with the id 'content'.", 'anyfont'),
			"footer_text" => __("This setting will apply the selected style to just the footer element on every page of your site. WARNING: This setting may not work with all themes as it depends on there being a div element with the id 'footer'.", 'anyfont'),
			"header_text" => __("This setting will apply the selected style to just the header element on every page of your site. WARNING: This setting may not work with all themes as it depends on there being a div element with the id 'header'.", 'anyfont'),
			"custom_element" => __("Apply styles to any Element, Element ID or Element Classname.  For more details, please click help in the upper right hand corner of this page.", 'anyfont'),
			"fontserv-remote-images" => __("Enable this setting to have all images generated at FontServ instead of your own server.", 'anyfont'),
			"fontserv-run-backups" => __("Backup your font files to FontServ.com automatically whenever a change is detected.", 'anyfont'),
			"style-backups" => __("Backup all your styles to FontServ.com automatically whenever a change is detected.", 'anyfont'),
			"font-family" => __("Select one of your converted fonts from the list in the dropdown menu", 'anyfont'),
			"font-formatting" => __("Set whether your styles text should be bold, italic or underlined.  Multiple options may be selected.", 'anyfont'),
			"text-align-css" => __("Set the styles text to be either left, center or right aligned.", 'anyfont'),
			"max-width-text-align" => __("Set the styles text to be aligned to either the left or the center.", 'anyfont'),
			"post-type-titles" => __("Custom post type title replacement. Both image styles and css styles can be assigned.", 'anyfont'),
			"css3_new_custom_help" => __("Type in an element name, class name or ID to apply the selected style to matching elements. ID's should be prefixed with a '#' and class names with a '.', chaining elements, ID's and class names together is also supported. ie:'div#content h2.title'.", 'anyfont'),
			"custom_css" => __("This is a custom css rule you created.", 'anyfont'),
			"disable_prettylinks" => __("Enabling this option will force AnyFont to generate only variable based image links regardless of what the WordPress permalink setting is set to. This option does not affect your WordPress permalinks, only AnyFont's image links will change. <br/>URL Example: <em>'/?style=my+style&txt=Hello+World'</em>", 'anyfont')
		);
		$url = parse_url(get_option('siteurl'));
		$this->sitepath = isset($url['path']) ? trailingslashit($url['path']) : "/";
		$this->tplPath = ANYFONT_ROOT."/tpl";
		if(is_array($config) && isset($config['page'])){
			require_once(ANYFONT_LIBDIR."/".ANYFONT_LIB_VERSION."/class.tpl.php");
			$this->page = $config['page'];
			$this->page_title = $config['title'];
			$this->get_page();
		}
	}

	function printPage(){
	
		print($this->page_html);
	}

	function get_page(){

		$jslang_array = array(
			"del_style_note" => __("Please note that once a style is deleted, any generated images that are assosiated with the style will no longer load.", 'anyfont'),
			"chk_del_style" => __("Are you sure you want to delete this style?", 'anyfont'),
			"chk_del_styles" => __("Are you sure you want to delete the selected styles?", 'anyfont'),
			"chk_del_fonts" => __("Are you sure you want to delete the selected font(s)?", 'anyfont'),
			"del_font_note" => __("Please note that once a font is deleted, any styles which are using the font will break.", 'anyfont'),
			"msg_del_fonts" => __("Deleting Selected Fonts...", 'anyfont'),
			"msg_del_styles" => __("Deleting Selected Styles...", 'anyfont'),
			"msg_del" => __("Deleting", 'anyfont'),
			"err_select_style" => __("No Styles Selected!", 'anyfont'),
			"chk_clear_cache" => __("Are you sure you want to clear the cache?\nNote: this action will not delete any uploaded font files.", 'anyfont'),
			"msg_clear_cache" => __("Clearing the cache...", 'anyfont'),
			"msg_no_images" => __("no images", 'anyfont'),
			"msg_upload_success" => __("was uploaded successfully", 'anyfont'),
			"err_upload_failed" => __("Upload Failed:", 'anyfont'),
			"err_saving_style" => __("Save Failed! Please ensure that the font folder and all files inside are writable by the webserver.", 'anyfont'),
			"msg_saved_style" => __("Style has been saved.", 'anyfont'),
			"msg_saving_style" => __("Saving Style...", 'anyfont'),
			"msg_preview_style" => __("Loading Preview...", 'anyfont'),
			"msg_upload_start" => __("Uploading Font...", 'anyfont'),
			"msg_saving_settings" => __("Saving Settings...", 'anyfont'),
			"css3_new_custom_help" => $this->helptext["css3_new_custom_help"],
			"msg_del_css" => __("Please wait while custom CSS rule is deleted.", 'anyfont')

		);
		if (function_exists('json_encode')) {
			$jslang = json_encode($jslang_array);
		} else {
			require_once(ANYFONT_LIBDIR.'/class.json.php');
			$JSON = new serviceJSON();
			$jslang = $JSON->encode($jslang_array);
		}
		$this->tpl = new fastTPL($this->tplPath);
		$this->tpl->define(array($this->page => "{$this->page}.html",
							"header" => "header.html",
                                                        "footer" => "footer.html",
                                                ));
                $this->tpl->assign("STYLE", file_get_contents(ANYFONT_ROOT."/styles.css"));
                $this->tpl->assign("URL", $this->sitepath);
                $this->tpl->assign("JSLANG", $jslang);
                $styles = $this->readStyles('css');
                $style_array = array();
		!is_array($styles) ? $styles = array() : 0;
		foreach($styles as $stylename => $styledata){
			array_push($style_array, $stylename);
		}
		if (function_exists('json_encode')) {
			$style_list = json_encode($style_array);
		} else {
			require_once(ANYFONT_LIBDIR.'/class.json.php');
			$JSON = new serviceJSON();
			$style_list = $JSON->encode($style_array);
		}
		$this->checkAnyFontHealth();
		$this->checkKey();
		
		if($this->fs_status['result'] == "success"){
			$this->tpl->assign("FONTSERV_KEY",  "<span class='green'>Valid</span>");
			$this->tpl->assign("FONTSERV_PACKAGE", "<li><div class='status_heading'>Package: </div><span class='package'>".$this->fs_status['package']."</span></li>");
		} else{
			$this->tpl->assign("FONTSERV_KEY", !get_option("anyfont-fontserv-api-key") ? "<a href='http://fontserv.com/' target='_blank'>Sign up here</a>" : "<span class='red'>Invalid</span>");
		}
		$this->tpl->assign("CSSSTYLES", $style_list);
		$this->tpl->assign("IMAGEURL", ANYFONT_IMAGE_URL);
		$this->tpl->assign("PERMALINKS", anyfont_using_permalinks() ? 1 : 0);
		$this->tpl->assign("ICON_CLASS", $this->page);
		$this->tpl->assign("SETTINGS_PAGE_TITLE", __("AnyFont Settings", 'anyfont'));
		$this->tpl->assign("FONTS_PAGE_TITLE", __("Install Fonts", 'anyfont'));
		$this->tpl->assign("STYLES_PAGE_TITLE", __("Create Styles", 'anyfont'));

		switch($this->page){
			case 'settings':
				$this->tpl->assign("SETTINGS_ACTIVE_NAV", " nav-tab-active");
				$this->getSettings();
				break;

			case 'fonts':
				$this->tpl->assign("FONTS_ACTIVE_NAV", " nav-tab-active");
				$this->readFontDir() ? $this->getFonts() : 0;
				break;

			case 'styles':
				$this->tpl->assign("STYLES_ACTIVE_NAV", " nav-tab-active");
				if($this->readFontDir()){
					$this->getStyles('image');
					$this->getStyles('css');
				}
				break;
		}
		isset($this->fs_status['upgrade']) ? $this->tpl->assign("INSERT_TEXT", $this->fs_status['upgrade']) : 0;
		$this->tpl->assign("HEADER", $this->tpl->fetchParsed("header"));
		$this->tpl->assign("VERSION", ANYFONT_VERSION);
		$this->tpl->assign("FOOTER_TXT", __("Please click the help button in the top right corner of this page for links to the documentation and support options.", 'anyfont'));
		$this->tpl->assign("FOOTER", $this->tpl->fetchParsed("footer"));
		$this->page_html =  $this->tpl->fetchParsed($this->page);
	}

	function getSettings(){

		$tpl = new fastTPL($this->tplPath);
        $tpl->define(array("autoreplace" => "inserts.html",
                    "cache" => "cache.html",
                    "disk_cache_settings" => "disk_cache_settings.html",
                    "fontserv" => "fontserv.html",
                    "fontserv_settings" => "fontserv_settings.html",
                    "advanced" => "advanced.html",
                    "gen_settings" => "gen_settings.html",
                    "post_block" => "image_single_title_replace.html",
                    "css_custom" => "css.html"
                ));

		$opt = array("auto(Default)" => "auto", "gd" => "php4", "imagick" => "php5");
		$imageopt = "";
		$mi = 0;
		foreach($opt as $mod => $val){
			$selected = ($val == get_option('anyfont_image_module')) ? "selected=\"selected\"" : "";
			if($val == "auto"){
				$imageopt .= "<option value=\"$val\" $selected>$mod</option>";
			}else if(extension_loaded($mod)){
				$imageopt .= "<option value=\"$val\" $selected>$mod module</option>";
				$mi++;
			}
		}
		if($mi < 2){
			$tpl->assign("HIDE_MODULE_SELECT", 'style="display:none"');
		}
        $this->isReplaceEnabled($tpl, 'anyfont_cat_title', "CAT");
        $this->isReplaceEnabled($tpl, 'anyfont_header_text', "HEAD");
        $this->isReplaceEnabled($tpl, 'anyfont_body_text', "BODY");
        $this->isReplaceEnabled($tpl, 'anyfont_content_text', "CONTENT");
        $this->isReplaceEnabled($tpl, 'anyfont_footer_text', "FOOTER");
        $this->isReplaceEnabled($tpl, 'anyfont_tag_title', "TAG");
        $this->isReplaceEnabled($tpl, 'anyfont_widget_title', "WIDGET");
        $this->isReplaceEnabled($tpl, 'anyfont_blog_title', "BLOG_TITLE");
        $this->isReplaceEnabled($tpl, 'anyfont_blog_desc', "BLOG_DESC");
        $this->isReplaceEnabled($tpl, 'anyfont_menu', "MENU");
        $this->isReplaceEnabled($tpl, 'anyfont_disable_hotlinking', "DISABLE_HOTLINKING");
		$this->isReplaceEnabled($tpl, 'anyfont-limit-cache-size', 'CACHE_LIMIT');
		$this->isReplaceEnabled($tpl, 'anyfont_cache_show_bytes', 'CACHE_SHOW_BYTES');
		$this->isReplaceEnabled($tpl, 'anyfont_cache_dashboard_widget', 'CACHE_DASHBOARD');
		$this->isReplaceEnabled($tpl, 'anyfont_enable_gzip', 'ENABLE_GZIP');
		$this->isReplaceEnabled($tpl, 'anyfont_enable_tinymce', 'ENABLE_TINYMCE');
		$this->isReplaceEnabled($tpl, 'anyfont-enable-custom-fontdir', 'ENABLE_CUSTOM_FONTDIR');
		$this->isReplaceEnabled($tpl, 'anyfont-enable-custom-cache', 'ENABLE_CUSTOM_CACHEDIR');
		$this->isReplaceEnabled($tpl, 'anyfont_fav_links', 'ENABLE_FAV_LINKS');
		$this->isReplaceEnabled($tpl, 'anyfont_use_htaccess', 'ENABLE_HTACCESS');
		$this->isReplaceEnabled($tpl, 'anyfont_disable_prettylinks', 'DISABLE_PRETTYLINKS');
		$this->isReplaceEnabled($tpl, 'anyfont_add_mimetype', 'ENABLE_MIMETYPE');
		$this->isReplaceEnabled($tpl, 'anyfont-fontserv-remote-images', 'REMOTE_IMAGES');
		$this->isReplaceEnabled($tpl, 'anyfont-fontserv-run-backup', 'BACKUP');
		if(get_option('anyfont_disable_prettylinks') == 'on'){
			$tpl->assign("HIDE_HTACCESS", 'style="display:none"');
		}else if(get_option('anyfont_use_htaccess') == 'on'){
			$tpl->assign("HIDE_PRETTYLINKS", 'style="display:none"');
		}
		if(isset($_SERVER['SERVER_SOFTWARE']) && strstr(strtolower($_SERVER['SERVER_SOFTWARE']), "iis")){
			$tpl->assign("HTACCESS_CLASS", 'class="hide-htaccess"');
		}
        $tpl->assign("REPLACE_H2", __("AutoReplace Titles", 'anyfont'));
        $tpl->assign("HELP_MSG", __("To replace certain plain text titles with images in your wordpress blog, enable the appropriate section below and then assign it one of your <a href=\"{STYLE_URL}\">styles</a>.", 'anyfont'));
        $tpl->assign("SEO_NOTE", __("The post title, page title, blog name and blog description image replacements are SEO compatible.", 'anyfont'));
        $tpl->assign("DISCLAIMER", __("<span>PLEASE NOTE:</span><br /> The above options may not be compatible with ALL themes and/or custom widgets.", 'anyfont'));
        $tpl->assign("DISCLAIMER_CONT", __("If you encounter any problems:<br/><br /><ul><li>Please check the <a href=\"http://wordpress.org/extend/plugins/anyfont/faq\" target=\"_blank\">FAQ</a> for known issues with certain themes.</li><li>Check with the theme developer</li><li>See message below to contact me for support.</li></ul>", 'anyfont'));
		if(function_exists('get_post_types')){
			$post_types = get_post_types();
			$image_post_titles = "";
			$o = 10;
			foreach($post_types as $type){
				if($type !== 'attachment' && $type !== 'revision' && $type !== 'nav_menu_item'){
					$tpl->assign("OPTION_NUMBER", $o);
					$tpl->assign("POST_TYPE", $type);
					$o++;
					!get_option("anyfont_{$type}_title_style") ? add_option("anyfont_{$type}_title_style") : 0;
					!get_option("anyfont_{$type}_title") ? add_option("anyfont_{$type}_title") : 0;
					$this->isReplaceEnabled($tpl, "anyfont_{$type}_title", "POST");
					$tpl->assign("POST_TITLE_STYLES", $this->titleStyleConfig( get_option("anyfont_{$type}_title_style"), "both"));
					$help_section = ($type == "post" || $type == "page") ? "{$type}-titles" : "post-type-titles";
					$tpl->assign("POST_HELP", $this->helptext[$help_section]);
					$image_post_titles .= $tpl->fetchParsed("post_block");
				}
			}
			$tpl->assign("POST_BLOCK", $image_post_titles);
		}
		$tpl->assign("HEAD_TEXT_STYLES", $this->titleStyleConfig(get_option('anyfont_header_text_style'), "css"));
		$tpl->assign("BODY_TEXT_STYLES", $this->titleStyleConfig(get_option('anyfont_body_text_style'), "css"));
		$tpl->assign("CONTENT_TEXT_STYLES", $this->titleStyleConfig(get_option('anyfont_content_text_style'), "css"));
		$tpl->assign("FOOTER_TEXT_STYLES", $this->titleStyleConfig(get_option('anyfont_footer_text_style'), "css"));
		$tpl->assign("TAG_TITLE_STYLES", $this->titleStyleConfig(get_option('anyfont_tag_title_style'), "image"));
		$tpl->assign("CAT_TITLE_STYLES", $this->titleStyleConfig(get_option('anyfont_cat_title_style'), "image"));
		$tpl->assign("WIDGET_TITLE_STYLES", $this->titleStyleConfig(get_option('anyfont_widget_title_style'), "image"));
		$tpl->assign("BLOG_TITLE_STYLES", $this->titleStyleConfig(get_option('anyfont_blog_title_style'), "image"));
		$tpl->assign("BLOG_DESC_STYLES", $this->titleStyleConfig(get_option('anyfont_blog_desc_style'), "image"));
		$tpl->assign("MENU_STYLES", $this->titleStyleConfig(get_option('anyfont_menu_style'), "both"));
		$tpl->assign("HOVER_STYLES", $this->titleStyleConfig(get_option('anyfont_menu_hover'), "both"));
		$tpl->assign("ACTIVE_STYLES", $this->titleStyleConfig(get_option('anyfont_menu_active'), "both"));
		$tpl->assign("CSS_RULE_BLOCK", $this->getCSSRuleBlock());
		$tpl->assign("STYLE_URL", WP_ADMIN_URL."/admin.php?page=anyfont-styles");
		$tpl->assign("ANYFONT_CACHE_DIR", ANYFONT_CACHE);
		$tpl->assign("ANYFONT_FONTDIR", ANYFONT_FONTDIR);
		$tpl->assign("API_KEY", get_option('anyfont-fontserv-api-key'));
		$tpl->assign("API_KEY_HELP", $this->helptext['api-key']);
		$tpl->assign("KEY_STATUS", $this->fs_status['msg']);
		$tpl->assign("FONTSERV_EXTRA_SETTINGS", $this->fs_status['extraSettings'] ? $tpl->fetchParsed("fontserv_settings") : "");
		$tpl->assign("CACHE_SETTINGS_H2", __("Cache Settings", 'anyfont'));
		$tpl->assign("ADVANCED_H2", __("Advanced Settings", 'anyfont'));
		$tpl->assign("ADVANCED_MSG", __("It is recommended that the following options are left on their default settings.", 'anyfont'));
		$tpl->assign("IMAGE_MODULE_OPTIONS", $imageopt);
		$tpl->assign("CACHE_MAX_SIZE", get_option('anyfont-cache-size-limit'));
		$tpl->assign("CACHE_MAX_SIZE_HELP", $this->helptext['anyfont-cache-size-limit']);
		$tpl->assign("DISK_CACHE_BLOCK", $this->getDiskCache(true));
		$tpl->assign("DISK_CACHE_SETTINGS", $tpl->fetchParsed("disk_cache_settings"));
		$tpl->assign("IMPORTANT_MESSAGE", __("<strong>Please Note: </strong>As of version 2.1.1, AnyFont does not require any changes to be made the .htaccess file in order for it to work correctly. Ultimately this means AnyFont should work on almost any WordPress install as long as one of the required php image modules are available. However, should you want to override this change and force AnyFont to add rewrite rules to the .htaccess file as it did in older versions, simply enable the option below."));
		if(function_exists('is_multisite')){
			if(is_multisite() && is_super_admin()){
				$tpl->assign("ADV_SETTINGS", $tpl->fetchParsed("advanced"));
			} else if(!is_multisite()){
				$tpl->assign("ADV_SETTINGS", $tpl->fetchParsed("advanced"));
			}
		} else {
			$tpl->assign("ADV_SETTINGS", $tpl->fetchParsed("advanced"));
		}
		$this->tpl->assign("AUTOTEXT_BLOCK", $tpl->fetchParsed("autoreplace"));
		$this->tpl->assign("OVERVIEW_BLOCK", $tpl->fetchParsed("cache"));
		$this->tpl->assign("FONTSERV_BLOCK", $tpl->fetchParsed("fontserv"));
		$this->tpl->assign("SETTINGS_BLOCK", $tpl->fetchParsed("gen_settings"));
	}

	function getCSSRuleBlock(){
		$tpl = new fastTPL($this->tplPath);
        $tpl->define(array(
					"css_block" => "css_rule_block.html",
                    "css_single" => "css.html"
                ));
		$custom_css = "";
		$css_list = maybe_unserialize(get_option('anyfont_customcss_list'));
		$c = 0;
		!is_array($css_list) ? $css_list = array() : 0;
		if(count($css_list) > 0){
			foreach($css_list as $target){
				$id = anyfont_custom_encode($target);
				$tpl->assign("CSS_RULE_NUM", $c);
				$c++;
				$tpl->assign("CSS_RULE_LABEL", $target);
				$tpl->assign("CSS_RULE_NAME", $id);
				$tpl->assign("CSS_RULE_ID", $id);
				$style = get_option("anyfont_{$id}_style");
				$this->isReplaceEnabled($tpl, "anyfont_{$id}", "CSS_RULE");
				$tpl->assign("CSS_RULE_STYLES", $this->titleStyleConfig($style, "css"));
				$tpl->assign("CSS_RULE_HELP", $this->helptext["custom_css"]);
				$tpl->assign("CSS_RULE_DELETE", "<span onclick=\"AnyFont.deleteCustomCSS('".anyfont_custom_encode($target)."');\" class=\"anyfont_add_del_btn\"><img src=\"".ANYFONT_URL."/img/delete.png\" alt=\"delete\" title=\"Delete Custom Rule\" /></span>");
				$custom_css .= $tpl->fetchParsed("css_single");
			}
		}
		$tpl->assign("CSS3_CUSTOM_FIELDS", $custom_css);
		$tpl->assign("CSS3_CUSTOM_STYLES", $this->titleStyleConfig("", "css"));
		$tpl->assign("CSS3_NEW_CUSTOM_HELP", $this->helptext["css3_new_custom_help"]);
		$tpl->assign("ANYFONT_URL", ANYFONT_URL);
		return  $tpl->fetchParsed("css_block");
	}

	function checkKey($echo=false){
		require_once(ANYFONT_LIBDIR.'/class.fontserv-client.php');
		$key = get_option('anyfont-fontserv-api-key') !== '' ? get_option('anyfont-fontserv-api-key') : "nokey";
		$server = new FontServ(ANYFONT_FONTSERV_URL, $key, get_option("siteurl"));
		$this->fs_status =  $server->checkKey($key, get_option("siteurl"));
		if($echo === "return"){
			return $this->fs_status;
		} else if($echo === true){
			echo $this->fs_status;
		}
	}

	function getDiskCache($button=false){
		if(!class_exists("fastTPL")){
			require_once(ANYFONT_LIBDIR."/".ANYFONT_LIB_VERSION."/class.tpl.php");
		}
		$tpl = new fastTPL($this->tplPath);
        $tpl->define(array("disk_cache" => "disk_cache.html"));
		$totalsize = 0;
		$imagecount = 0;
		$admincount = 0;
		$adminsize = 0;
		if($this->cachedir){
			$dir = opendir(ANYFONT_CACHE);
			while ($filename = readdir($dir)) {
				$fileinfo = explode(".", $filename);
				if ($fileinfo[1] == "png") {
					$split = explode("-", $fileinfo[0]);
					if($split[1] == "admin"){
						$admincount++;
						$adminsize += filesize(ANYFONT_CACHE."/".$filename);
					}else{
						$imagecount++;
						$totalsize += filesize(ANYFONT_CACHE."/".$filename);
					}
				}
			}
		}
		$totalfontcount = 0;
		$totalfontsize = 0;
		if($this->fontdir){
			$dir = opendir(ANYFONT_FONTDIR);
			$fontspace = array();
			while ($filename = readdir($dir)) {
				$fileinfo = explode(".", $filename);

				if ($fileinfo[1] == "ttf" || $fileinfo[1] == "otf" || $fileinfo[1] == "eot" || $fileinfo[1] == "js" || $fileinfo[1] == "woff" || $fileinfo[1] == "svg") {
					$size = filesize(ANYFONT_FONTDIR."/".$filename);
					!is_array($fontspace[$fileinfo[1]]) ? $fontspace[$fileinfo[1]] = array() : 0;
					!isset($fontspace[$fileinfo[1]]['size']) ? $fontspace[$fileinfo[1]]['size'] = $size :  $fontspace[$fileinfo[1]]['size'] += $size;
					!isset($fontspace[$fileinfo[1]]['count']) ? $fontspace[$fileinfo[1]]['count'] = 0 : $fontspace[$fileinfo[1]]['count'] += 1;
					$totalfontcount++;
					$totalfontsize += $size;
				}
			}
		}
		$imagecount == 0 ? $imagecount = "no" : 0;
		$admincount == 0 ? $admincount = "no" : 0;
		$total = $totalsize+$adminsize;
		$tpl->assign("CACHE_DISK_H2", __("Disk Usage", 'anyfont'));
// 		$tpl->assign("SITE_IMAGES_HELP", __("All images which are generated for your sites public pages and posts"));
// 		$tpl->assign("ADMIN_IMAGES_HELP", __("All images generated while using the AnyFont Style Manager or Font Manager"));
		$tpl->assign("IMAGE_COUNT", $imagecount);
		$tpl->assign("TOTAL_SIZE", $this->bytecalc($totalsize));
		$tpl->assign("IMAGE_COUNT_ADMIN", $admincount);
		$tpl->assign("TTF_COUNT", $fontspace['ttf']['count']);
		$tpl->assign("OTF_COUNT", $fontspace['otf']['count']);
		$tpl->assign("WOFF_COUNT", $fontspace['woff']['count']);
		$tpl->assign("EOT_COUNT", $fontspace['eot']['count']);
		$tpl->assign("SVG_COUNT", $fontspace['svg']['count']);
		$tpl->assign("CUFON_COUNT", $fontspace['js']['count']);
		$tpl->assign("TTF_SIZE", $this->bytecalc($fontspace['ttf']['size']));
		$tpl->assign("OTF_SIZE", $this->bytecalc($fontspace['otf']['size']));
		$tpl->assign("WOFF_SIZE", $this->bytecalc($fontspace['woff']['size']));
		$tpl->assign("EOT_SIZE", $this->bytecalc($fontspace['eot']['size']));
		$tpl->assign("SVG_SIZE", $this->bytecalc($fontspace['svg']['size']));
		$tpl->assign("CUFON_SIZE", $this->bytecalc($fontspace['js']['size']));
		$tpl->assign("FONT_COUNT", $totalfontcount);
		$tpl->assign("FONT_SIZE", $this->bytecalc($totalfontsize));
		$tpl->assign("TOTAL_SIZE_ALL", $this->bytecalc($total));
		$tpl->assign("TOTAL_SIZE_ADMIN", $this->bytecalc($adminsize));
		$tpl->assign("CACHE_LIMIT", get_option('anyfont-limit-cache-size') == "on" ? $this->bytecalc(1048576*(int)get_option('anyfont-cache-size-limit')) : "unlimited");
		get_option('anyfont-limit-cache-size') == "on" ? $tpl->assign("CACHE_PERCENTAGE", "(".round((($total)*100)/(1048576*(int)get_option('anyfont-cache-size-limit')))."% full)") : 0;
		!$button ? 0 : $tpl->assign("CLEAR_CACHE_BUTTON", "<input type=\"button\" class=\"button-primary\" onclick=\"AnyFont.clearCache();\" value=\"Clear the Cache\" />");
		return $tpl->fetchParsed("disk_cache");
	}

	function isReplaceEnabled($tpl, $section, $assign){
		$tpl->assign("{$assign}_HELP", $this->helptext[preg_replace("/^anyfont\_?\-?/", "", $section)]);
		if(get_option($section)){
			$tpl->assign("{$assign}_CHECKED", "checked=\"checked\"");
			$tpl->assign("{$assign}_ON_CLASS", " anyfont_checkbox_on");
			$tpl->assign("{$assign}_DISABLED", "");
		} else {
			$tpl->assign("{$assign}_ON_CLASS", "");
			$tpl->assign("{$assign}_CHECKED", "");
			$tpl->assign("{$assign}_DISABLED", "disabled=\"disabled\"");
		}
	}

	function titleStyleConfig($section, $type){
        $styleoptions = '';
        if($type == "image" || $type == "both"){
			$styles = $this->readStyles('image');
			$type == "both" ? $styleoptions .= "<option class=\"select_option_heading\" disabled=\"disabled\">Image Styles</option>" : 0;
			if(is_array($styles)){
				foreach($styles as $style => $option){
					if($style!=="Preview"){
						$selected = $style == $section ? "selected=\"selected\"" : '';
						$styleoptions .= "<option value=\"$style\" $selected>$style</option>";
					}
				}
			}
        }
        if($type == "css" || $type == "both"){
			$styles = $this->readStyles('css');
			$type == "both" ? $styleoptions .= "<option class=\"select_option_heading\" disabled=\"disabled\">CSS Styles</option>" : 0;
			if(is_array($styles)){
				foreach($styles as $style => $option){
					$selected = $style == $section ? "selected=\"selected\"" : '';
					$styleoptions .= "<option value=\"$style\" $selected>$style</option>";
				}
			}
		}
		return $styleoptions;
	}

	function checkAnyFontHealth($showerror=true){
// 		switch(anyfont_check_htaccess()){
// 
// 			case "NA":
// 				$showerror ? $this->showError("AnyFont detected this site is not running on the Apache Webserver. AnyFont depends on the Apache Module \"mod_rewrite\" or equivalent to run corrrectly.<br /><strong>NOTE:</strong> If you are using ngix, you can try adding <code>rewrite images/(.*)/(.*)\.png$ /wp-content/plugins/anyfont/img.php last;</code> to your rewrite rules. <br />To disable this message, please uncheck <i>\"enable htaccess rules\"</i> in AnyFont's Advanced Settings.") : 0;
// 				return false;
// 
// 			case "NM":
// 				$showerror ? $this->showError("The Apache Module \"mod_rewrite\" could not be found. AnyFont requires this module to operate correctly.  Please ask your webhost to enable mod_rewrite and ensure that \"AllowOverride FileInfo\" is set for your domain.") : 0;
// 				return false;
// 
// 			case "NW":
// 				$showerror ? $this->showError("AnyFont can't write to your .htaccess file. Please ensure that ".ABSPATH.".htaccess is writable by the server and reload this page.<br /><br/><br/> <strong>Tip for advanced users:</strong> If you prefer to update the .htaccess file yourself please add the following rules to the beginning of the file:<br/><br/><code>".nl2br(anyfont_get_htaccess_rules())."</code><br /><i><strong>NOTE:</strong>To disable this message, please uncheck <i>\"enable htaccess rules\"</i> in AnyFont's Advanced Settings.</i>") : 0;
// 				return false;
// 
// 			case "NR":
// 			case "NU":
// 				anyfont_update_permalink_status("install");
// 				break;
// 
// 		}
		if(!is_dir(ANYFONT_CACHE)){
			if(!wp_mkdir_p(ANYFONT_CACHE)){
				$this->cachedir = false;
				$showerror ? $this->showError("The AnyFont cache directory could not be found and attempts to create it failed! Please create the folder \"".ANYFONT_CACHE."\" and ensure that it is writable by the server.") : 0;
				return false;
			}
		}
		if(is_dir(ANYFONT_CACHE) && !is_writable(ANYFONT_CACHE)){
			$this->cachedir = false;
			$showerror ? $this->showError("AnyFont is currently unable to save any files to the cache folder! Please check the permissions for the folder \"".ANYFONT_CACHE."\" and ensure that it is writable by the server.") : 0;
			return false;
		}
		if(!is_dir(ANYFONT_FONTDIR)){
            if(!wp_mkdir_p(ANYFONT_FONTDIR)){
				$this->fontdir = false;
            	$showerror ? $this->showError("AnyFont was unable to create a font folder to store uploaded font files! Please create the folder \"".ANYFONT_FONTDIR."\" and ensure that is writable by the server.") : 0;
            	return false;
            }
		} else if(!is_writable(ANYFONT_FONTDIR)){
			$this->fontdir = false;
			$showerror ? $this->showError("AnyFont is currently unable to save any files to the font folder! Please check the permissions for the folder \"".ANYFONT_FONTDIR."\" and ensure that is writable by the server.") : 0;
			return false;
		}
		return true;
	}

	function getFonts(){
		$list = "";
		$tpl = new fastTPL($this->tplPath);
		$tpl->define(array("font_block" => "fonts-block.html","font_preview" => "font-preview.html"));
		foreach($this->fontlist as $displayname => $fontdetail){
			$list .= $this->getFontBlock($tpl, $displayname, $fontdetail);
		}
		$this->tpl->assign("FONTS", $list);
		$this->tpl->assign("UPLOAD_URL", ANYFONT_URL."/upload.php");
	}

	function getFontBlock($tpl, $displayname, $fontdetail){
		$fontname = urlencode($fontdetail["type"][0]['filename']);
		$fonttypes = "";
		$extrapreviews = "";
		$i = 1;
		$t = count($fontdetail["type"]);
		foreach($fontdetail["type"] as $type){
			$i != 1 && $i <= $t ? $fonttypes .= ", " : 0;
			$fonttypes .= "{$type['ftype']}";
			$tpl->assign('FONTID', preg_replace('/\n/', "", str_replace("'","",preg_replace('/\s+/', "", $displayname).$type['ftype'])));
			$tpl->assign('FONT_NAME', urlencode($type['filename']));
			$tpl->assign('FONT_DISPLAY_NAME', $displayname);
			$tpl->assign('FONT_TYPE', $type['ftype']);
			$text = urlencode("The quick brown fox jumps over the lazy dog");
			$filename = urlencode($type['filename']);
			$url = anyfont_using_permalinks() ? ANYFONT_IMAGE_URL."admin/$filename/$text.png" : ANYFONT_IMAGE_URL."admin&txt=$filename&displaytext=$text";
			$tpl->assign('FONT_URL', $url);
			$tpl->assign('CHARMAP_ICON', ANYFONT_URL."/img/icon-charmap.png");
			$tpl->assign('DELETE_ICON', ANYFONT_URL."/img/icon-delete.png");
			$extrapreviews .= $tpl->fetchParsed("font_preview");
			$i++;
		}
		$font_url = $fontdetail['url'] != "" ? $fontdetail['url'] : false;
		$fontid = str_replace("'","",preg_replace('/\s+/', "", $displayname));
		$tpl->assign('FONTID', $fontid);
		$tpl->assign('FONT_NAME', $fontname);
		$tpl->assign('FONT_DISPLAY_NAME', $displayname);
		$tpl->assign('FONT_TYPES', $fonttypes);
		$tpl->assign('DISPLAY_NAME', $displayname);
		$tpl->assign('FORMAT_TXT', __("Formats: "));
		$tpl->assign('SUPP_STYLES_TXT', __("Supported Styles: "));
		$tpl->assign('IMAGES_TXT', __("images"));
		$tpl->assign('CSS3_TXT', __("CSS3"));
		$tpl->assign('EXTRA_PREVIEWS', "<ul id=\"extrapreviews\">".$extrapreviews."</ul>");
		$tpl->assign('FILE_NAME', "<strong>".__("File: ", 'anyfont')."</strong>".$fontdetail["file"]."<br /><br />");
		$tpl->assign('AUTHOR', $fontdetail['author'] != "" ? "<strong>".__("Author: ", 'anyfont')."</strong>".(!$font_url ? $fontdetail['author'] : "<a href=\"$font_url\" target=\"_blank\">".$fontdetail['author']."</a>")."<br />" : "");
		$tpl->assign('LICENSE', $fontdetail['license'] != "" ? "<strong>".__("License: ", 'anyfont')."</strong>".$fontdetail['license']."<br />" : "");
		$tpl->assign('TRADEMARK', $fontdetail['trademark'] != "" ? "<strong>".__("Trademark: ", 'anyfont')."</strong>".$fontdetail['trademark']."<br />" : "");
		$tpl->assign('DESCRIPTION', $fontdetail['description'] != "" ? "<strong>".__("Description: ", 'anyfont')."</strong>".$fontdetail['description']. "<br />" : "");
		$tpl->assign('WEBFONT_CLASS', $fontdetail['webfont']);
		$tpl->assign('WEBFONT_STATUS',$fontdetail['webfont']);
		$tpl->assign('WEBFONT_BUTTON', /*$fontdetail['webfont'] === "No" ? */"<input id='{$fontid}_css3_convert_button' type='button' class='button-secondary anyfont_css_button'  onclick='AnyFont.convertFont(\"{$fontname}\", \"{$fontid}\", \"".str_replace("'","",$displayname)."\", \"css3\");' value='Convert for CSS3 Styles' />" /*: ""*/);
		$text = urlencode("The quick brown fox jumps over the lazy dog");
// 		$filename = urlencode($fontname);
		$url = anyfont_using_permalinks() ? ANYFONT_IMAGE_URL."admin/$fontname/$text.png" : ANYFONT_IMAGE_URL."admin&txt=$fontname&displaytext=$text";
		$tpl->assign('FONT_URL', $url);
		$tpl->assign('FONT_INFO_ICON', ANYFONT_URL."/img/icon-fontinfo.png");
		$tpl->assign('WEBFONT_ICON', ANYFONT_URL."/img/icon-fontinfo.png");
		$tpl->assign('CHARMAP_ICON', ANYFONT_URL."/img/icon-charmap.png");
		$tpl->assign('DELETE_ICON', ANYFONT_URL."/img/icon-delete.png");
		return $tpl->fetchParsed("font_block");
	}

	function readFontDir($returndata=false) {
		$this->fontlist = array();
		$filelist  = array();
		$sortlist  = array();
		$buildlist = array();
		if(!$this->fontdir){
			return false;
		}else{
			$dir = dir(ANYFONT_FONTDIR);
			while (false !== ($e = $dir->read())){
				if($this->is_font($e)){
					$fontdetails = $this->getFontInfo( ANYFONT_FONTDIR ."/". $e);
					if(!is_array($buildlist[$fontdetails[1]])){
						$buildlist[$fontdetails[1]] = array();
					}
					$buildlist[$fontdetails[1]]["name"] = $fontdetails[1];
					if(!is_array($buildlist[$fontdetails[1]]["type"])){
						$buildlist[$fontdetails[1]]["type"] = array();
					}
					array_push($buildlist[$fontdetails[1]]["type"], array("ftype" => $fontdetails[2], "filename" => $this->getFontName($e)));
					$buildlist[$fontdetails[1]]["trademark"] = $fontdetails[7];
					$buildlist[$fontdetails[1]]["description"] = nl2br($fontdetails[10]);
					$buildlist[$fontdetails[1]]["version"] = $fontdetails[5];
					$buildlist[$fontdetails[1]]["author"] = !isset($fontdetails[8]) ? $fontdetails[9] : $fontdetails[8];
					$buildlist[$fontdetails[1]]["url"] = $fontdetails[12];
					$buildlist[$fontdetails[1]]["license"] = nl2br($fontdetails[13]);
					$buildlist[$fontdetails[1]]["filename"] = $this->getFontName($e);
					$buildlist[$fontdetails[1]]["file"] = $e;
					$buildlist[$fontdetails[1]]["webfont"] = file_exists(ANYFONT_FONTDIR ."/".substr($e, 0, -4).".eot") ? "Yes" : "No";
// 					$buildlist[$fontdetails[1]]["cufon"] = file_exists(ANYFONT_FONTDIR ."/".substr($e, 0, -4).".js") ? "Yes" : "No";
					(count($buildlist[$fontdetails[1]]["type"]) <= 1) ?  array_push($sortlist, $fontdetails[1]) : 0;
				}
			}

			natcasesort($sortlist);
			foreach($sortlist as $fontname){
				$this->fontlist[$fontname] = $buildlist[$fontname];
			}
			if(!$returndata){
				return true;
			} else {
				return $this->fontlist;
			}
		}
	}

	function readStyles($type){
		if(file_exists(ANYFONT_FONTDIR."/styles.ini")){
			$styles = parse_ini_file(ANYFONT_FONTDIR."/styles.ini", true);
			if(isset($styles['admin']))
				unset($styles['admin']);
			$new_styles = array(
				"image" => $styles,
				"css" => array()
			);
			anyfont_write_styles($new_styles);
			unlink(ANYFONT_FONTDIR."/styles.ini");
		}
		$styles = unserialize(get_option('anyfont_styles'));
		return $styles[$type];
	}

	function getHelpText($section){
		$helptext =  isset($this->helptext[$section]) ? $this->helptext[$section] : $this->helptext['default'];
		return "<span class=\"help-txt\" title=\"$helptext\"></span>";
	}

	function bytecalc($bytes, $base10=false, $round=0){
        $labels=array('bytes', 'kB', 'MB', 'GB');

        if (($bytes <= 0) || (! is_array($labels)) || (count($labels) <= 0))
            return "0 bytes";

        $step = $base10 ? 3 : 10 ;
        $base = $base10 ? 10 : 2;

        $log = (int)(log10($bytes)/log10($base));

        krsort($labels);

        foreach ($labels as $p=>$lab) {
            $pow = $p * $step;
            if ($log < $pow) continue;
            $text = round($bytes/pow($base,$pow),$round).$lab;
            get_option("anyfont_cache_show_bytes") == "on" ? $text = $bytes." bytes (".$text.")" : 0;
            break;
        }
        return $text;
    }

	function dec2ord($dec){
		return $this->dec2hex(ord($dec));
	}

	function dec2hex($dec){
		return str_repeat('0', 2-strlen(($hex=strtoupper(dechex($dec))))) . $hex;
	}

	/**
	* @original author Unknown
	* found at http://www.phpclasses.org/browse/package/2144.html
	*/
	function getFontInfo($filename){
		$font_tags = array();
		$fd = fopen ($filename, "r");
		$this->text = fread ($fd, filesize($filename));
		fclose ($fd);

		$number_of_tables = hexdec($this->dec2ord($this->text[4]).$this->dec2ord($this->text[5]));

		for ($i=0;$i<$number_of_tables;$i++)
		{
			$tag = $this->text[12+$i*16].$this->text[12+$i*16+1].$this->text[12+$i*16+2].$this->text[12+$i*16+3];
			if ($tag == 'name')
			{
				$this->ntOffset = hexdec(
					$this->dec2ord($this->text[12+$i*16+8]).$this->dec2ord($this->text[12+$i*16+8+1]).
					$this->dec2ord($this->text[12+$i*16+8+2]).$this->dec2ord($this->text[12+$i*16+8+3]));

				$offset_storage_dec = hexdec($this->dec2ord($this->text[$this->ntOffset+4]).$this->dec2ord($this->text[$this->ntOffset+5]));
				$number_name_records_dec = hexdec($this->dec2ord($this->text[$this->ntOffset+2]).$this->dec2ord($this->text[$this->ntOffset+3]));
			}
		}

		$storage_dec = $offset_storage_dec + $this->ntOffset;
		$storage_hex = strtoupper(dechex($storage_dec));

		for ($j=0;$j<$number_name_records_dec;$j++)
		{
			$platform_id_dec	= hexdec($this->dec2ord($this->text[$this->ntOffset+6+$j*12+0]).$this->dec2ord($this->text[$this->ntOffset+6+$j*12+1]));
			$name_id_dec		= hexdec($this->dec2ord($this->text[$this->ntOffset+6+$j*12+6]).$this->dec2ord($this->text[$this->ntOffset+6+$j*12+7]));
			$string_length_dec	= hexdec($this->dec2ord($this->text[$this->ntOffset+6+$j*12+8]).$this->dec2ord($this->text[$this->ntOffset+6+$j*12+9]));
			$string_offset_dec	= hexdec($this->dec2ord($this->text[$this->ntOffset+6+$j*12+10]).$this->dec2ord($this->text[$this->ntOffset+6+$j*12+11]));

			if (!empty($name_id_dec) and empty($font_tags[$name_id_dec]))
			{
				for($l=0;$l<$string_length_dec;$l++)
				{
					if (ord($this->text[$storage_dec+$string_offset_dec+$l]) == '0') { continue; }
					else { $font_tags[$name_id_dec] .= ($this->text[$storage_dec+$string_offset_dec+$l]); }
				}
			}
		}
		return $font_tags;
	}

	function is_font($filename){
		$ext = explode('.', $filename);
		$ext = $ext[count($ext)-1];
		if( preg_match("/ttf$/i",$ext) ){
			return true;
		} elseif(preg_match("/otf$/i",$ext)){
			return true;
		}
		return false;
	}

	function getFontName($filename){
		$fontfile = explode(".", $filename);
		$fontname = "";
		for($i = 0; $i < (count($fontfile)-1);$i++){
			$i > 0 ? $fontname .= "." : 0;
			$fontname .= $fontfile[$i];
		}
		return $fontname;
	}

	function getStyles($type){
		if(!is_array($this->fontlist) || count($this->fontlist) <= 0){
			$this->readFontDir();
		}
		if($type == "css"){
			$fonts = $this->fontlist;
			foreach($fonts as $displayname => $fontdetail){
				if($fontdetail['webfont'] != "Yes"){
					unset($fonts[$displayname]);
				}
			}
			if(count($fonts) == 0){
				$this->tpl->assign(strtoupper($type)."_STYLES", "<div id=\"anyfont-note\" class=\"warning\">".__("CSS styles cannot be created before any uploaded fonts have been converted into all the required formats which make embedding fonts inside web pages compatible in all browsers. Please ensure you have signed up at FontServ.com and then convert some fonts in the Font Manager.")."</div><br />");
				return;
			}
		}
		$this->styles = $this->readStyles($type);
		$this->styletpl = new fastTPL($this->tplPath);
		$this->styletpl->define(array("styles_list" => "styles_list.html"));
		$styles = "";
		if(is_array($this->styles)){
            foreach($this->styles as $style => $option){
                if($style != "Preview"){
                    $styles.=$this->getStyleBlock($style, $option, $type);
                }
            }
		}
		$textpreview = $type != "css" ? "" : "onkeyup=\"AnyFont.updatePreview(this);\" onchange=\"AnyFont.updatePreview(this);\"";
		$changepreview = $type != "css" ? "" : "onchange=\"AnyFont.updatePreview(this, 'css');\"";
        $new_style = "<input type=\"hidden\" name=\"new-style\" id=\"new-style\" value=\"true\" /><input type=\"hidden\" name=\"style-type\" id=\"style-type\" value=\"$type\" />
		<label for=\"update_style\">name</label><input type=\"text\" name=\"update_style\" id=\"update_style\" $textpreview />".$this->getHelpText('name')."<br /><br />";
		$new_style .= ANYFONT_LIB_VERSION == "php4" && $type != "css" ? "<label for=\"background-color-$type\">background-color</label><input type=\"text\" class=\"colorinput\" name=\"background-color\" id=\"background-color-$type\" value=\"#FFFFFF\" />".$this->getHelpText('background-color')."<br /><br />" : "<input type=\"hidden\" name=\"background-color\" id=\"background-color-$type\" value=\"#FFFFFF\" />";
		$new_style .= "<label for=\"color-$type\">color</label><input type='text' class=\"colorinput\" name='color' id='color-$type' value='#000000' />".$this->getHelpText('color')."<br /><br />";
		$fontnameID = $type == "css" ? "font-family" : "font-name";
		$new_style .= "<label for=\"_$fontnameID-$type\">$fontnameID</label>
						<input readonly='readonly' id='_$fontnameID-$type' class=\"custom_select\" type=\"text\" name=\"_$fontnameID\" onclick=\"AnyFont.toggleDropMenu(this)\" value=\"Select Font...\" />".$this->getHelpText($fontnameID)."
						<input  id='$fontnameID-$type'  type=\"hidden\" name=\"$fontnameID\"/>
						<ul id=\"{$fontnameID}_menu\" class=\"menu\" style=\"display:none;\">";
		if($type === "css"){
			foreach($this->fontlist as $displayname => $fontdetail){
				if($fontdetail['webfont'] != "Yes"){
					unset($this->fontlist[$displayname]);
				}
			}
		}
		foreach($this->fontlist as $displayname => $fontdetail){
			$fontname = $fontdetail['filename'];
			$fontfilename = $fontdetail['filename'];
			if($type == "css"){
				if(count($fontdetail["type"]) > 1){
					$fontname=array();
					foreach($fontdetail["type"] as $ftype){
						$fontname[$ftype["ftype"]] = $ftype["filename"];
					}
					$fontname = str_replace("\"", "'", json_encode($fontname));
				} else {
					$fontname = "'".$fontname."'";
				}
				$url = anyfont_using_permalinks() ? ANYFONT_IMAGE_URL."admin-small/".urlencode($fontfilename)."/".urlencode($displayname).".png" : ANYFONT_IMAGE_URL."admin-small&txt=".urlencode($fontfilename)."&displaytext=".urlencode($displayname);
				$new_style .= "<li  onclick=\"AnyFont.selectOption('$fontnameID', '".str_replace("'", "", $displayname)."', $fontname, '$type');\" class=\"imglist\"><img src=\"$url\" alt=\"$displayname\" /></li>";
			} else {
				if(count($fontdetail["type"]) > 1){
					foreach($fontdetail["type"] as $fonttype){
						$displayname = $fontdetail['name']." ".$fonttype['ftype'];
						$filename = $fonttype['filename'];
						$url = anyfont_using_permalinks() ? ANYFONT_IMAGE_URL."admin-small/".urlencode($filename)."/".urlencode($displayname).".png" : ANYFONT_IMAGE_URL."admin-small&txt=".urlencode($filename)."&displaytext=".urlencode($displayname);
						$new_style .= "<li  onclick=\"AnyFont.selectOption('$fontnameID', '".str_replace("'", "", $displayname)."', '$filename', '$type');\" class=\"imglist\"><img src=\"".$url."\" alt=\"$displayname\" /></li>";
					}
				} else {
					$url = anyfont_using_permalinks() ? ANYFONT_IMAGE_URL."admin-small/".urlencode($fontname)."/".urlencode($displayname).".png" : ANYFONT_IMAGE_URL."admin-small&txt=".urlencode($fontname)."&displaytext=".urlencode($displayname);
					$new_style .= "<li  onclick=\"AnyFont.selectOption('$fontnameID', '".str_replace("'", "", $displayname)."', '$fontname', '$type');\" class=\"imglist\"><img src=\"".$url."\" alt=\"$displayname\" /></li>";
				}
			}
		}
		$new_style .= "</ul><br /><br />";
		$sizes = array("7", "8", "9", "10", "11", "12", "13", "14", "15", "16", "18", "22", "24", "28", "36", "40", "44", "48", "54", "60", "72");
		$new_style .= "<label for=\"font-size-$type\">font-size</label><input id='font-size-$type' class=\"custom_select font-size\" type=\"text\" name=\"font-size\" onclick=\"AnyFont.toggleDropMenu(this)\" value=\"18pt\" style=\"width:60px;\" $changepreview/>".$this->getHelpText('font-size');
		$new_style .= "<ul id=\"font-size_menu\" class=\"menu\" style=\"display:none;\">";
		foreach($sizes as $size){
			$new_style .= "<li onclick=\"AnyFont.selectOption('font-size', '{$size}pt', false, '$type');\">{$size}pt</li>";
		}
		$new_style .= "</ul><br /><br />";
		if($type !== "css"){
			$new_style .= "<div class=\"anyfont_checkbox\"><label for=\"#\">image-padding</label><input id=\"image-padding-$type\" type=\"checkbox\" name=\"image-padding\" onclick=\"AnyFont.toggleHidden(this)\" /></div>".$this->getHelpText('image-padding')."<br /><br />";
			$new_style .= "<div class=\"hidden_option\" style=\"display:none;\"><label for=\"image-padding-top\">top</label><input class=\"padding shadow-spin\" type='text' name='image-padding-top' id='image-padding-top-$type' value='0px' />".$this->getHelpText('image-padding-top')."<br /><br />
						<label for=\"image-padding-bottom-$type\">bottom</label><input class=\"padding shadow-spin\" type='text' name='image-padding-bottom' id='image-padding-bottom-$type' value='0px' />".$this->getHelpText('image-padding-bottom')."<br /><br />
						<label for=\"image-padding-left-$type\">left</label><input class=\"padding shadow-spin\" type='text' name='image-padding-left' id='image-padding-left-$type' value='0px' />".$this->getHelpText('image-padding-left')."<br /><br />
						<label for=\"image-padding-right-$type\">right</label><input class=\"padding shadow-spin\" type='text' name='image-padding-right' id='image-padding-right-$type' value='0px' />".$this->getHelpText('image-padding-right')."<br /></div>";
			$new_style .= "<div class=\"anyfont_checkbox\"><label for=\"#\">".__("limit-width")."</label><input id=\"limit-width-$type\" type=\"checkbox\" name=\"limit-width\" onclick=\"AnyFont.toggleHidden(this)\" /></div>".$this->getHelpText('limit-width')."<br /><br />";
			$new_style .= "<div class=\"hidden_option\" style=\"display:none;\"><label for=\"max-width-$type\">max-width</label><input class=\"max-width shadow-spin\" type='text' name='max-width' id='max-width-$type' value='400px' />".$this->getHelpText('max-width')."<br /><br />";
			$new_style .= "<label for=\"line-height-$type\">line-height</label><input class=\"line-height shadow-spin\" type='text' name='line-height' id='line-height-$type' value='0px' />".$this->getHelpText('line-height')."<br /><br />";
			$new_style .= "<label for=\"limit-width-text-align-$type\">text-align</label><div class=\"text-align\"><label class=\"left_radio\" title=\"Left Align\" for=\"max-width-text-align-left\"><input id=\"max-width-text-align-left-$type\" type=\"radio\" name=\"max-width-text-align-$type\" value=\"left\" checked=\"checked\" /></label><label title=\"Center Align\" class=\"center_radio last_btn\" for=\"max-width-text-align-center\"><input id=\"max-width-text-align-center\" type=\"radio\" name=\"max-width-text-align\" value=\"center\" /></label></div>".$this->getHelpText('text-align')."<br /></div>";
		} else {

			$new_style .= "<label for=\"font-style-$type\">font-formatting</label><div class=\"font-style\">
			<label class=\"bold_check\" title=\"Bold Text\" for=\"font-style-bold-$type\">
				<input id=\"font-style-bold-$type\" type=\"checkbox\" name=\"font-weight\" value=\"bold\" class=\"font-style-input\" $changepreview/>
			</label>
			<label title=\"Italics\" class=\"italic_check\" for=\"font-style-italic-$type\">
				<input id=\"font-style-italic-$type\" type=\"checkbox\" name=\"font-style\" value=\"oblique\" class=\"font-style-input\" $changepreview/>
			</label>
			<label title=\"Underline\" class=\"underline_check last_btn\" for=\"font-style-underline-$type\">
				<input id=\"font-style-underline-$type\" type=\"checkbox\" name=\"text-decoration\" value=\"underline\" class=\"font-style-input\" $changepreview/>
			</label></div>".$this->getHelpText('font-formatting')."<br/><br/>";
			$new_style .= "<label for=\"text-align\">text-align</label><div class=\"text-align\">
			<label class=\"left_radio\" title=\"Left Align\" for=\"text-align-left-$type\">
				<input id=\"text-align-left-$type\" type=\"radio\" name=\"text-align\" value=\"left\" checked=\"checked\" $changepreview/>
			</label>
			<label title=\"Center Align\" class=\"center_radio\" for=\"text-align-center-$type\">
				<input id=\"text-align-center-$type\" type=\"radio\" name=\"text-align\" value=\"center\" $changepreview/>
			</label>
			<label title=\"Right Align\" class=\"right_radio last_btn\" for=\"text-align-right-$type\">
				<input id=\"text-align-right-$type\" type=\"radio\" name=\"text-align\" value=\"right\" $changepreview/>
			</label></div>".$this->getHelpText('text-align-css')."<br/><br/>";
			$new_style .= "<label for=\"line-height-$type\">line-height</label><input class=\"line-height lhcss shadow-spin\" type='text' name='line-height' id='line-height-$type' value='0px'/>".$this->getHelpText('line-height')."<br /><br />";
		}
		$shadow_id = $type == "css" ? "text-shadow-css" : "shadow-image";
		$shadow_change = $type == "css" ? "onclick=\"AnyFont.setShadow(this.next('div.hidden_option').down('input.colorinput'));\"" : "";
		$shadow_css_class = $type == "css" ? "css-shadow-change " : "";
		$new_style .= "<div class=\"anyfont_checkbox\" $shadow_change ><label for=\"#\">shadow</label><input id=\"$shadow_id\" type=\"checkbox\" name=\"shadow\" onclick=\"AnyFont.toggleHidden(this);\" /></div>".$this->getHelpText('shadow')."<br /><br />";
		$new_style .= "<div class=\"hidden_option\" style=\"display:none;\"><label for=\"shadow-color-{$type}\">shadow-color</label><input type=\"text\" class=\"colorinput\" name=\"shadow-color\" id=\"shadow-color-{$type}\" value=\"#808080\" />".$this->getHelpText('shadow-color')."<br /><br />";
		$new_style .= "<label for=\"shadow-distance-{$type}\">shadow-distance</label><input class=\"{$shadow_css_class}shadow-distance shadow-spin\" type='text' name='shadow-distance' id='shadow-distance-{$type}' value='2px' />".$this->getHelpText('shadow-distance')."<br /><br />";
		$new_style .= ANYFONT_LIB_VERSION == "php5" || $type == "css" ? "<label for=\"shadow-spread-{$type}\">shadow-spread</label><input class=\"{$shadow_css_class}shadow-spread shadow-spin\" type='text' name='shadow-spread' id='shadow-spread-{$type}' value='1' />".$this->getHelpText('shadow-spread')."<br /></div>" : "<input type='hidden' name='shadow-spread' id='shadow-spread' value='1' />";
		$new_style .= ANYFONT_LIB_VERSION == "php4" && $type != "css" ? "<div class=\"anyfont_checkbox\"><label for=\"#\">soften-shadow</label><input id=\"soften-shadow-$type\" class=\"anyfont_chk_only\" type=\"checkbox\" name=\"soften-shadow\" /></div>".$this->getHelpText('soften-shadow')."<br /></div>": "<input id=\"soften-shadow-$type\" value=\"\" type=\"hidden\" name=\"soften-shadow\" />";
		$new_style .= "<p class=\"submit\"><input id=\"submit_style-$type\" class=\"button-primary button-save\" type=\"button\" value=\"create style\" onclick=\"AnyFont.updateStyle('anyfont-style-new-form-$type')\">
						<input type=\"button\" name=\"cancel\" value=\"cancel\" class=\"button-secondary\" style=\"float:right;margin-right:5px;\" onclick=\"AnyFont.toggleNew('anyfont-style-new-$type');AnyFont.toggleNew('anyfont-$type-preview');\"/></p></form>";
		$this->styletpl->assign("NEW_STYLE_FORM", $new_style);
		$this->styletpl->assign("STYLES", $styles);
		$this->styletpl->assign("TYPE", $type);
		$this->tpl->assign(strtoupper($type)."_STYLES", $this->styletpl->fetchParsed("styles_list"));
	}

	function getStyleBlock($style, $option, $type){
		if(!is_array($this->fontlist) || count($this->fontlist) <= 0){
			$this->readFontDir();
		}
		$styleBlocktpl = new fastTPL($this->tplPath);
		$styleBlocktpl->define(array("styles_block" => "styles_block.html"));
		$url = get_bloginfo('wpurl');
		$styleBlocktpl->assign('TYPE', $type);
		$style_id = str_replace(" ", "__", $style);
		$styleBlocktpl->assign('STYLE_NAME', $style);
		$styleBlocktpl->assign('STYLE_NAME_ID', $style_id);
		$options = "<form id=\"$style_id\" class=\"anyfont_style_settings\">
						<input type=\"hidden\" value=\"$style\" name=\"update_style\" />
						<input type=\"hidden\" name=\"style-type\" id=\"style-type\" value=\"$type\" />";
		if($type == "image"){
			$url = anyfont_using_permalinks() ? ANYFONT_IMAGE_URL."$style/$style.png" : ANYFONT_IMAGE_URL."$style&txt=$style";
			$styleBlocktpl->assign('STYLE_PREVIEW', "<img id=\"preview_image_$style_id\" src=\"$url\" class=\"anyfont-style-preview\" />");
			ANYFONT_LIB_VERSION == "php4" ? !isset($option['background-color']) ? $option['background-color'] = "#FFFFFF" : 0 : 0;
			!isset($option['limit-width']) ? $option['limit-width'] = false : 0;
			!isset($option['max-width']) ? $option['max-width'] = 50 : 0;
			!isset($option['image-padding']) ? $option['image-padding'] = false : 0;
			!isset($option['image-padding-radius']) ? $option['image-padding-radius'] = 0 : 0;
			!isset($option['shadow-spread']) ? $option['shadow-spread'] = 2 : 0;
			!isset($option['shadow-distance']) ? $option['shadow-distance'] = 1 : 0;
			!isset($option['soften-shadow']) ? $option['soften-shadow'] = 0 : 0;
			!isset($option['line-height']) ? $option['line-height'] = 0 : 0;
			if(isset($option['image-padding-radius']) &&  $option['image-padding-radius'] > 0){
				$option['image-padding-top'] = $option['image-padding-radius']/2;
				$option['image-padding-bottom'] = $option['image-padding-radius']/2;
				$option['image-padding-left'] = $option['image-padding-radius']/2;
				$option['image-padding-right'] = $option['image-padding-radius']/2;

			}
			if(isset($option['image-padding-radius'])){
				unset($option['image-padding-radius']);
			}
			!isset($option['image-padding-top']) ? $option['image-padding-top'] = 0 : 0;
			!isset($option['image-padding-bottom']) ? $option['image-padding-bottom'] = 0 : 0;
			!isset($option['image-padding-left']) ? $option['image-padding-left'] = 0 : 0;
			!isset($option['image-padding-right']) ? $option['image-padding-right'] = 0 : 0;
			!isset($option['max-width-text-align']) ? $option['max-width-text-align'] = isset($option['text-align-center']) ? "center" : "left" : 0;
			if(isset($option['text-align-center'])){
				unset($option['text-align-center']);
			}
			ksort($option);
			foreach($option as $name => $value){
				$for = $style_id."_$name";
				$dolabel = true;
				switch($name){

					case 'assigned':
						$dolabel = false;
						break;

					case 'line-height':
					case 'shadow-color':
						$tfoption = $name == "shadow-color" ? $option['shadow'] : $option['limit-width'];
						$hide_box =  !$tfoption ? "style=\"display:none;\"" : "";
						$options .= "<br /><div class=\"hidden_option\" $hide_box >";
						break;

					case 'shadow':
						$option_on = !$option['shadow'] ? "" : " anyfont_checkbox_on";
						$options .= "<div class=\"anyfont_checkbox$option_on\">";
						$for = "#";
						break;

					case 'max-width-text-align':
						$options .= "<label for='$for'>text-align</label><div class=\"text-align\">";
						$dolabel = false;
						break;

					case 'soften-shadow':
						if(ANYFONT_LIB_VERSION == "php4"){
							$option_on = !$option['soften-shadow'] ? "" : " anyfont_checkbox_on";
							$options .= "<div class=\"anyfont_checkbox$option_on\">";
							$for = "#";
						}else if(ANYFONT_LIB_VERSION == "php5"){
							$options .= "<input id='{$style_id}_$name' type=\"hidden\" name=\"$name\" value=\"$value\" />";
							$dolabel = false;
						}
						break;

					case 'limit-width':
						$option_on = !$option['limit-width'] ? "" : " anyfont_checkbox_on";
						$options .= "<div class=\"anyfont_checkbox$option_on\">";
						$for = "#";
						break;

					case 'background-color':
						if(ANYFONT_LIB_VERSION == "php5"){
							$options .= "<input id='{$style_id}_$name' type=\"hidden\" name=\"$name\" value=\"$value\" />";
							$dolabel = false;
						}
						break;

					case 'shadow-spread':
						if(ANYFONT_LIB_VERSION == "php4"){
							$options .= "<input id='{$style_id}_$name' type=\"hidden\" name=\"$name\" value=\"$value\" />";
							$dolabel = false;
						}
						break;

					case 'image-padding':
						$option_on = !$option['image-padding'] ? "" : " anyfont_checkbox_on";
						$options .= "<div class=\"anyfont_checkbox$option_on\">";
						$for = "#";
						break;

					case "image-padding-right":
					case "image-padding-left":
					case "image-padding-bottom":
					case "image-padding-top":
						$label_txt = str_replace("image-padding-", "", $name);
						$options .= "<label for='$for'>$label_txt</label>";
						$dolabel = false;
						break;

				}
				$dolabel !== false ? $options .= "<label for='$for'>$name</label>" : 0;
				switch($name){
					case "background-color":
						ANYFONT_LIB_VERSION == "php4" ? $options .= "<input  type='text' class=\"colorinput\" name='$name' id='{$style_id}_$name' value='#$value' />".$this->getHelpText($name)."<br /><br />": 0;
						break;

					case "color":
						$options .= "<input type='text' class=\"colorinput\" name='$name' id='{$style_id}_$name' value='#$value' />".$this->getHelpText($name)."<br /><br />";
						break;

					case "font-name":
						$dropdown = "";
						foreach($this->fontlist as $displayname => $fontdetail){
							$fontname = $fontdetail['filename'];
							$fontname == $value ? $selectedfontname = $fontdetail['name'] : 0;
							
							if(count($fontdetail["type"]) > 1){
								foreach($fontdetail["type"] as $ftype){
									
									$displayname = $fontdetail['name']." ".$ftype['ftype'];
									$filename = $ftype['filename'];
									$filename == $value ? $selectedfontname = $fontdetail['name']." ".$ftype['ftype'] : 0;
									$url = anyfont_using_permalinks() ? ANYFONT_IMAGE_URL."admin-small/".urlencode($filename)."/".urlencode($displayname).".png" : ANYFONT_IMAGE_URL."admin-small&txt=".urlencode($filename)."&displaytext=".urlencode($displayname);
									$dropdown .= "<li  onclick=\"AnyFont.selectOption('{$style_id}_$name', '".str_replace("'", "", $displayname)."', '$filename');\" class=\"imglist\"><img src=\"$url\" alt=\"$displayname\" /></li>";
								}
							} else {
								$url = anyfont_using_permalinks() ? ANYFONT_IMAGE_URL."admin-small/".urlencode($fontname)."/".urlencode($displayname).".png" : ANYFONT_IMAGE_URL."admin-small&txt=".urlencode($fontname)."&displaytext=".urlencode($displayname);
								$dropdown .= "<li  onclick=\"AnyFont.selectOption('{$style_id}_$name', '".str_replace("'", "", $displayname)."', '$fontname');\" class=\"imglist\"><img src=\"$url\" alt=\"$displayname\" /></li>";
							}
						}
						$options .= "<input readonly='readonly' id='_{$style_id}_$name' class=\"custom_select\" type=\"text\" name=\"_$name\" onclick=\"AnyFont.toggleDropMenu(this)\" value=\"{$selectedfontname}\" />".$this->getHelpText($name);
						$options .= "<input  id='{$style_id}_$name'  type=\"hidden\" name=\"$name\" value=\"{$value}\" />";
						$options .= "<ul id=\"{$style_id}_{$name}_menu\" class=\"menu\" style=\"display:none;\">";
						$options .= $dropdown;
						$options .= "</ul><br /><br />";
						break;

					case "font-size":
						$sizes = array("7", "8", "9", "10", "11", "12", "13", "14", "15", "16", "18", "22", "24", "28", "36", "40", "44", "48", "54", "60", "72");
						$options .= "<input id='{$style_id}_$name' class=\"custom_select font-size\" type=\"text\" name=\"$name\" onclick=\"AnyFont.toggleDropMenu(this)\" value=\"{$value}pt\" style=\"width:60px;\"/>".$this->getHelpText($name);
						$options .= "<ul id=\"{$style_id}_{$name}_menu\" class=\"menu\" style=\"display:none;\">";
						foreach($sizes as $size){
							$options .= "<li onclick=\"AnyFont.selectOption('{$style_id}_$name', '{$size}pt');\">{$size}pt</li>";
						}
						$options .= "</ul><br /><br />";
						break;

					case "limit-width":
						$checked = !$value ? "" : "checked=\"checked\"";
						$options .= "<input id='{$style_id}_$name' type=\"checkbox\" name=\"$name\" $checked onclick=\"AnyFont.toggleHidden(this)\" /></div>".$this->getHelpText($name)."<br />";
						break;

					case "max-width":
						$options .= "<input class=\"max-width shadow-spin\" type='text' name='$name' id='{$style_id}_$name' value='{$value}px' />".$this->getHelpText($name)."<br /><br />";
						break;

					case "max-width-text-align":
						$left_checked = $value == "left" ? "checked=\"checked\"" : "";
						$center_checked = $value == "center" ? "checked=\"checked\"" : "";
						$options .= "<label class=\"left_radio\" title=\"Left Align\" for=\"{$style_id}_max-width-text-align-left\"><input id=\"{$style_id}_max-width-text-align-left\" type=\"radio\" name=\"{$style_id}_{$name}\" value=\"left\" $left_checked /></label><label class=\"center_radio last_btn\" title=\"Center Align\" for=\"{$style_id}_max-width-text-align-center\"><input id=\"{$style_id}_max-width-text-align-center\" type=\"radio\" name=\"{$style_id}_{$name}\" value=\"center\" $center_checked /></label></div>".$this->getHelpText("max-width-text-align")."<br /></div>";
						break;

					case "shadow":
						$checked = !$value ? "" : "checked=\"checked\"";
						$options .= "<input id='{$style_id}_$name' type=\"checkbox\" name=\"$name\" $checked onclick=\"AnyFont.toggleHidden(this)\" /></div>".$this->getHelpText($name)."<br />";
						break;

					case "shadow-color":
						$options .= "<input  type='text' class=\"colorinput shadow\" name='$name' id='{$style_id}_$name' value='#$value' />".$this->getHelpText($name)."<br /><br />";
						break;

					case "shadow-distance":
						$options .= "<input class=\"shadow-distance shadow-spin\" type='text' name='$name' id='{$style_id}_$name' value='{$value}px' />".$this->getHelpText($name)."<br /><br />";
						break;

					case "shadow-spread":
						ANYFONT_LIB_VERSION == "php5" ? $options .= "<input class=\"shadow-spread shadow-spin\" type='text' name='$name' id='{$style_id}_$name' value='$value' />".$this->getHelpText($name)."<br /></div>" : 0;
						break;

					case "soften-shadow":
						$checked = !$value ? "" : "checked=\"checked\"";
						ANYFONT_LIB_VERSION == "php4" ? $options .= "<input id='{$style_id}_$name' type=\"checkbox\" class=\"anyfont_chk_only\" name=\"$name\" $checked /></div>".$this->getHelpText($name)."<br /></div>" : 0;
						break;

					case "image-padding":
						$checked = !$value ? "" : "checked=\"checked\"";
						$display_style = !$value ? "style='display:none;'" : "";
						$options .= "<input id='{$style_id}_$name' type=\"checkbox\" name=\"$name\" $checked onclick=\"AnyFont.toggleHidden(this)\" /></div>".$this->getHelpText($name)."<br />";
						$options .= "<br /><div class=\"hidden_option\" $display_style >";
						break;

					case "image-padding-top":
					case "image-padding-right":
					case "image-padding-left":
					case "line-height":
					case "image-padding-bottom":
						$options .= "<input type='text' class=\"padding shadow-spin\" name='$name' id='{$style_id}_$name' value='{$value}px' />".$this->getHelpText($name)."<br />";
						$options .=($name == "image-padding-top" ? "</div>" : "<br />");
						break;

				}
			}
		} else if($type == "css"){
			$updateEl = "$('anyfont-{$style_id}-preview')";
			$style_css = "<style>".anyfont_css_webfonts($option['font-family'], $option['font-file'])."\n";
			$style_css .= "ul#{$style_id}_item span.font-preview, div#anyfont-options-{$style_id} span.font-preview{";
			!$option['line-height'] ? $option['line-height'] = "0px" : 0;
			foreach($option as $key => $rule){
				if($key!="font-file" && $rule != ""){
					$style_css .= $key.":".($key != 'font-family' ? $rule : "'".$rule."'").";";
				}
			}
			$style_css .= "margin:0px;padding: 0px 10px;</style>";
			$styleBlocktpl->assign("STYLE_CSS", $style_css);
			$styleBlocktpl->assign("STYLE_PREVIEW", $style);
			$styleBlocktpl->assign("STYLE_PREVIEW_ID", "id='anyfont-{$style_id}-preview'");
			$changepreview = "onchange=\"AnyFont.updatePreview(this, '$style_id');\"";
			foreach($option as $name => $value){
				$for = $style_id."_$name";
				$dolabel = true;
				switch($name){
					case "font-file":
					case "font-weight":
					case "text-decoration":
					case "line-height":
						$dolabel = false;
						break;

					case "font-style":
						$options .= "<label for='$for'>font-formatting</label><div class=\"font-style\">";
						$dolabel = false;
						break;

					case 'text-align':
						$options .= "<label for='$for'>text-align</label><div class=\"text-align\">";
						$dolabel = false;
						break;

					case "text-shadow":
						$option_on = !$option['text-shadow'] ? "" : " anyfont_checkbox_on";
						$options .= "<div class=\"anyfont_checkbox$option_on\" onclick=\"AnyFont.setShadow(this.next('div.hidden_option').down('input.colorinput'));\">";
						$options .= "<label for='#'>shadow</label>";
						$dolabel = false;
						break;
				}
				$dolabel !== false ? $options .= "<label for='$for'>$name</label>" : 0;
				switch($name){
					case "color":
						$options .= "<input type='text' class=\"colorinput\" name='$name' id='{$style_id}_$name' value='$value' />".$this->getHelpText($name)."<br /><br />";
						break;

					case "font-family":
						$dropdown = "";
						foreach($this->fontlist as $displayname => $fontdetail){
							if($fontdetail['webfont'] != "Yes"){
								unset($this->fontlist[$displayname]);
							}
						}
						foreach($this->fontlist as $displayname => $fontdetail){
							$fontfilename = $fontdetail['filename'];
							$fontname = "'".$fontdetail['filename']."'";
							if(count($fontdetail["type"]) > 1){
								$fontname=array();
								foreach($fontdetail["type"] as $ftype){
									$fontname[$ftype["ftype"]] = $ftype["filename"];
								}
								$fontname = str_replace("\"", "'", json_encode($fontname));
							}
							if($displayname == $value){
								$selectedfontname = $fontdetail['name'];
								$selectedfilename = $fontdetail['filename'];
							}
							$url = anyfont_using_permalinks() ? ANYFONT_IMAGE_URL."admin-small/".urlencode($fontfilename)."/".urlencode($displayname).".png" : ANYFONT_IMAGE_URL."admin-small&txt=".urlencode($fontfilename)."&displaytext=".urlencode($displayname);
							$dropdown .= "<li  onclick=\"AnyFont.selectOption('$name', '".str_replace("'", "", $displayname)."', $fontname, '$style_id');\" class=\"imglist\"><img src=\"$url\" alt=\"$displayname\" /></li>";
						}
						$options .= "<input readonly='readonly' id='_$name-{$style_id}' class=\"custom_select\" type=\"text\" name=\"_$name\" onclick=\"AnyFont.toggleDropMenu(this)\" value=\"{$selectedfontname}\" />".$this->getHelpText($name);
						$options .= "<input  id='$name-{$style_id}'  type=\"hidden\" name=\"$name\" value=\"{$selectedfilename}\" />";
						$options .= "<ul id=\"{$name}-{$style_id}_menu\" class=\"menu\" style=\"display:none;\">";
						$options .= $dropdown;
						$options .= "</ul><br /><br />";
						break;

					case "font-size":
						$sizes = array("7", "8", "9", "10", "11", "12", "13", "14", "15", "16", "18", "22", "24", "28", "36", "40", "44", "48", "54", "60", "72");
						$options .= "<input id='$name-{$style_id}' class=\"custom_select font-size\" type=\"text\" name=\"$name\" onclick=\"AnyFont.toggleDropMenu(this)\" value=\"{$value}\" style=\"width:60px;\"/>".$this->getHelpText($name);
						$options .= "<ul id=\"{$name}-{$style_id}_menu\" class=\"menu\" style=\"display:none;\">";
						foreach($sizes as $size){
							$options .= "<li onclick=\"AnyFont.selectOption('$name', '{$size}pt', false, '{$style_id}'); $updateEl.setStyle('font-size:{$size}pt')\">{$size}pt</li>";
						}
						$options .= "</ul><br /><br />";
						break;

					case "text-align":
						$left_checked = $value == "left" || $value == "" ? "checked=\"checked\"" : "";
						$center_checked = $value == "center" ? "checked=\"checked\"" : "";
						$right_checked = $value == "right" ? "checked=\"checked\"" : "";
						$options .= "<label class=\"left_radio\" title=\"Left Align\" for=\"{$style_id}_max-width-text-align-left\">
										<input id=\"{$style_id}_max-width-text-align-left\" type=\"radio\" name=\"{$name}\" value=\"left\" $left_checked $changepreview/>
									</label>
									<label class=\"center_radio\" title=\"Center Align\" for=\"{$style_id}_max-width-text-align-center\">
										<input id=\"{$style_id}_max-width-text-align-center\" type=\"radio\" name=\"{$name}\" value=\"center\" $center_checked $changepreview/>
									</label>
									<label class=\"right_radio last_btn\" title=\"Right Align\" for=\"{$style_id}_max-width-text-align-right\">
										<input id=\"{$style_id}_max-width-text-align-right\" type=\"radio\" name=\"{$name}\" value=\"right\" $right_checked $changepreview/>
									</label></div>".$this->getHelpText("text-align-css")."<br/><br/>";
						break;

					case "font-style":
						$bold_checked = $option['font-weight'] == "bold" ? "checked=\"checked\"" : "";
						$italic_checked = $value == "oblique" ? "checked=\"checked\"" : "";
						$underline_checked = $option['text-decoration'] == "underline" ? "checked=\"checked\"" : "";
						$options .= "<label class=\"bold_check\" title=\"Bold\" for=\"{$style_id}_font-style-bold\">
										<input id=\"{$style_id}_font-style-bold\" type=\"checkbox\" name=\"font-weight\" value=\"bold\" class=\"font-style-input\" $bold_checked $changepreview/>
									</label>
									<label class=\"italic_check\" title=\"Italics\" for=\"{$style_id}_font-style-italics\">
										<input id=\"{$style_id}_font-style-italics\" type=\"checkbox\" name=\"font-style\" value=\"oblique\" class=\"font-style-input\" $italic_checked $changepreview/>
									</label>
									<label class=\"underline_check last_btn\" title=\"Underline\" for=\"{$style_id}_font-style-underline\">
										<input id=\"{$style_id}_font-style-underline\" type=\"checkbox\" name=\"text-decoration\" value=\"underline\" class=\"font-style-input\" $underline_checked $changepreview/>
									</label></div>".$this->getHelpText("font-formatting")."<br/><br/>";
						break;


					case "line-height":
						$value == "1" ? $value = "0px" : 0;
						$options .= "<label for=\"line-height-{$style_id}\">line-height</label><input class=\"line-height lhcss shadow-spin\" type='text' name='line-height' id='line-height-{$style_id}' value='{$value}' />".$this->getHelpText('line-height')."<br /><br />";
						break;

					case "text-shadow":
						$checked = $option['text-shadow'] == "" ? "" : "checked=\"checked\"";
						$style = $option['text-shadow'] != "" ? "" : "style=\"display:none;\"";
						$shadow_values = $option['text-shadow'] == "" ? array("2px", "2px", "1px", "#808080") : explode(" ", $option['text-shadow']);
						$shadow_color = $shadow_values[3];
						$shadow_distance = $shadow_values[0];
						$shadow_spread = str_replace("px", "", $shadow_values[2]);
						$options .= "<input id='$name-{$style_id}' type=\"checkbox\" name=\"shadow\" $checked onclick=\"AnyFont.toggleHidden(this)\" /></div>".$this->getHelpText('shadow')."<br /><br />";
						$options .= "<div class=\"hidden_option\" $style><label for=\"shadow-color-{$style_id}\">shadow-color</label><input type=\"text\" class=\"colorinput\" name=\"shadow-color\" id=\"shadow-color-{$style_id}\" value=\"$shadow_color\" />".$this->getHelpText('shadow-color')."<br /><br />";
						$options .= "<label for=\"shadow-distance-{$style_id}\">shadow-distance</label><input class=\"css-shadow-change shadow-distance shadow-spin\" type='text' name='shadow-distance' id='shadow-distance-{$style_id}' value='$shadow_distance' />".$this->getHelpText('shadow-distance')."<br /><br />";
						$options .= "<label for=\"shadow-spread-{$style_id}\">shadow-spread</label><input class=\"css-shadow-change shadow-spread shadow-spin\" type='text' name='shadow-spread' id='shadow-spread-{$style_id}' value='$shadow_spread' />".$this->getHelpText('shadow-spread')."<br /></div>";
						break;
				}
			}
		}
		$options .= "<p class=\"submit\"><input id=\"submit_style\" class=\"button-primary button-save\" type=\"button\" value=\"save changes\" onclick=\"AnyFont.updateStyle('$style_id')\">";
		$options .= $type != "css" ? "<input id=\"preview_style\" class=\"button-primary button-save\" type=\"button\" value=\"preview changes\" onclick=\"AnyFont.previewStyle('$style_id')\">" : "";
		$options .= "<input id=\"copy_style\" class=\"button-secondary\" type=\"button\" value=\"copy to new style\" onclick=\"AnyFont.copyStyle('$style_id', '$type')\"></p></form>";
		$styleBlocktpl->assign('DELETE_ICON', ANYFONT_URL."/img/icon-delete.png");
		$styleBlocktpl->assign('EDIT_ICON', ANYFONT_URL."/img/icon-edit.png");
		$styleBlocktpl->assign('OPTIONS', $options);
		return $styleBlocktpl->fetchParsed("styles_block");
	}

	function showError($err){
		update_option('anyfont_init_error', $err);
	}

}
?>