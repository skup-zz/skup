<?php
/*
Plugin Name: AnyFont
Plugin URI: http://2amlife.com/projects/anyfont
Description: AnyFont allows you to use any truetype or opentype font for post titles, menu items or anywhere else you want to use a custom font on your site..
Author: Ryan Peel
Version: 2.2
Author URI: http://fontserv.com/
Text Domain: anyfont

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

define("ANYFONT_VERSION", "2.2");

!defined('WP_ADMIN_URL') ? define('WP_ADMIN_URL', get_option('siteurl') . '/wp-admin') :0;
!defined('WP_CONTENT_URL') ? define('WP_CONTENT_URL', get_option('siteurl') . '/wp-content') :0;
!defined('WP_CONTENT_DIR') ? define('WP_CONTENT_DIR', ABSPATH . 'wp-content') : 0;
!defined('WP_PLUGIN_URL') ? define('WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins') : 0;
!defined('WP_PLUGIN_DIR') ? define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins') : 0;
define('ANYFONT_ROOT', trailingslashit(WP_PLUGIN_DIR).basename(dirname( __FILE__ )));
if(defined("DOMAIN_MAPPING") && DOMAIN_MAPPING == 1){
	$_anyfont_url = "http://".COOKIE_DOMAIN."/wp-content/plugins/".basename(dirname( __FILE__ ));
} else{
	$_anyfont_url = trailingslashit(WP_PLUGIN_URL).basename(dirname( __FILE__ ));
}
define('ANYFONT_URL', is_ssl() ? str_replace("http", "https", $_anyfont_url) : $_anyfont_url);
define('ANYFONT_FONTSERV_URL', 'http://api.fontserv.com/');

//load language
load_plugin_textdomain('anyfont', trailingslashit(basename(WP_CONTENT_DIR)).basename(dirname( __FILE__ )).'/i18n', basename(dirname( __FILE__ )).'/i18n');

//check for multisite and set cache dirs
if(anyfont_check_for_multsite()){
	$upload_dir = get_option('upload_path');
	if(substr($upload_dir,0,1) == '/'){
		$blog_dir = $upload_dir;
	} else {
		$blog_dir = ABSPATH.$upload_dir;
	}
	$cache_dir = $blog_dir;
	function_exists("get_space_allowed") ? define("ANYFONT_CACHE_MAX_SIZE", get_space_allowed()) : 0;
}else{
	$blog_dir = trailingslashit(WP_CONTENT_DIR)."uploads";
	$cache_dir = trailingslashit(WP_CONTENT_DIR)."cache";
}
!is_dir($blog_dir) ? wp_mkdir_p($blog_dir) :0 ;
!is_dir($cache_dir) ? wp_mkdir_p($cache_dir) :0;

$legacy_dir_c = trailingslashit(WP_CONTENT_DIR)."font-cache";
$legacy_dir = trailingslashit(WP_CONTENT_DIR)."fonts";
$dir_c = "";
$dir = "";
$default_dir_c = trailingslashit($cache_dir)."anyfont";
$dir_custom = get_option('anyfont-custom-cachedir');
$default_dir = trailingslashit($blog_dir)."fonts";
$dir_custom_f = get_option('anyfont-custom-fontdir');

// // set directories for font and cache
if(get_option('anyfont-enable-custom-cache')){
	$dir_c = $dir_custom;
	if(is_dir($default_dir_c)){
        is_writeable($default_dir_c)  && !is_dir($dir_c) ? rename($default_dir_c, $dir_c) : 0;
    } else if(is_dir($legacy_dir_c)){
        is_writeable($legacy_dir_c) && !is_dir($dir_c) ? rename($legacy_dir_c, $dir_c) : 0;
    }
} else {
	$dir_c = $default_dir_c;
	is_dir($legacy_dir_c) && !is_dir($dir_c) ? is_writeable($legacy_dir_c) ? rename($legacy_dir_c, $dir_c) : 0 : 0;
	is_dir($dir_custom) && !is_dir($dir_c) ? is_writeable($dir_custom) ?  rename($dir_custom, $dir_c) : 0 : 0;
}

if(get_option('anyfont-enable-custom-fontdir')){
	$dir = $dir_custom_f;
	if(is_dir($default_dir)){
       is_writeable($default_dir) && !is_dir($dir) ?  rename($default_dir, $dir) : 0;
    } else if(is_dir($legacy_dir)){
       is_writeable($legacy_dir) && !is_dir($dir) ? rename($legacy_dir, $dir) : 0;
    }
} else {
	$dir = $default_dir;
	is_dir($legacy_dir) && !is_dir($dir) ? is_writeable($default_dir) ? rename($legacy_dir, $dir) : 0 : 0;
    is_dir($dir_custom_f) && !is_dir($dir) ? is_writeable($dir_custom_f) ? rename($dir_custom_f, $dir): 0 : 0;
}
define( 'ANYFONT_CACHE_URL', trailingslashit(WP_CONTENT_URL)."uploads/fonts/" );
define( 'ANYFONT_CACHE', $dir_c );
define( 'ANYFONT_FONTDIR', $dir );
preg_match("/.*(\/wp-content\/.*)/", ANYFONT_FONTDIR, $font_path);
define('ANYFONT_FONT_URL', get_option('siteurl').$font_path[1]."/");


if(get_option('anyfont_image_module') && get_option('anyfont_image_module') != "auto"){
	$libver = get_option('anyfont_image_module');
	switch($libver){
		case 'php4':
			$libver = "php4";
			$engine = "gd";
			break;

		case 'php5':
			$libver = "php5";
			$engine = "imagick";
			break;

		case 'remote':
			$libver = "php4";
			$engine = "remote";
			break;
	}
} else {
	if(!function_exists('version_compare') || version_compare( phpversion(), '5', '<' )){
		$libver = "php4";
		$engine = "gd";
	}else if(!extension_loaded("imagick")) {
		$libver = "php4";
		$engine = "gd";
	}else{
		$libver = "php5";
		$engine = "imagick";
	}
}
if(!function_exists('version_compare') || version_compare(get_option("anyfont_current_version"), ANYFONT_VERSION, '<' )){
	add_action("init", "anyfont_install");
}
define('ANYFONT_LIBDIR', ANYFONT_ROOT."/lib");
define('ANYFONT_LIB_VERSION', $libver);
define('ANYFONT_IMAGE_ENGINE', $engine);

$dont_replace_title = false;
$anyfont_title_css = false;

//do health check
require_once(ANYFONT_LIBDIR."/class.admin.php");

$init = new anyfontAdmin(false);
if($init->checkAnyFontHealth() !== false){
	update_option("anyfont_init_error", false);
}


//check cache size limit
if( get_option( 'anyfont-limit-cache-size' ) && get_option( 'anyfont_cache_last_checked' ) < ( time()+( 24*60*60 ) ) ){
	if(is_readable(ANYFONT_CACHE)){
		$cachedir = opendir( ANYFONT_CACHE );
		$cachesize = 0;
		while ($curfile = readdir($cachedir)) {
			if(!is_dir($curfile)){
				$cachesize += filesize(ANYFONT_CACHE."/$curfile");
			}
		}
		$cache_limit = 1048576*(int)get_option('anyfont-cache-size-limit');
		if($cachesize > $cache_limit){
			$cachedir = opendir(ANYFONT_CACHE);
			while ($curfile = readdir($cachedir)) {
				if(!is_dir($curfile)){
					$split = explode("-", $curfile);
					if($split[1] == "admin.png"){
						unlink(ANYFONT_CACHE."/$curfile");
					}
				}
			}
		}
		update_option('anyfont_cache_last_checked', time());
	}
}

add_filter("default_contextual_help", "anyfont_help");


function anyfont_admin_menu(){
	$settings = __("Settings", 'anyfont');
	$fontmanager = __("Manage Fonts", 'anyfont');
	$stylemanager = __("Manage Styles", 'anyfont');
	add_menu_page('AnyFont', 'AnyFont', 'manage_anyfont', 'anyfont-settings', 'anyfont_settings_page', ANYFONT_URL."/img/anyfont-icon.png");
	add_submenu_page('anyfont-settings', $settings, $settings, 'manage_anyfont', 'anyfont-settings', 'anyfont_settings_page');
	add_submenu_page('anyfont-settings', $fontmanager, $fontmanager, 'manage_fonts', 'anyfont-fonts', 'anyfont_fonts_page');
	add_submenu_page('anyfont-settings', $stylemanager, $stylemanager, 'create_font_styles', 'anyfont-styles', 'anyfont_styles_page');
}

function anyfont_warning_msg($message=false){
	$type = "updated warn";
	if(!$message){
		$message = "<strong>ERROR:</strong> ".get_option("anyfont_init_error");
		$type = "error";
	}
	if($type == "warn"){
		echo "<style>
			#message.warn{
				background: rgb(255, 251, 204) url(../wp-content/plugins/anyfont/img/warning.png) no-repeat 5px 50%;
				border:2px solid #e6db55;
				border-radius:  10px;
				font-weight: bold;
				height: 50px;
				padding-left: 60px;
				padding-top: 5px;
				width:90%;
			}
			</style>";
	}
	echo '<div id="message" class="'.$type.'">'.$message.'</div>';
}

function anyfont_settings_page(){
	require_once(ANYFONT_LIBDIR."/class.admin.php");
	$config = array("page" => "settings", "title" => __("Settings", 'anyfont'));
	$page = new anyfontAdmin($config);
	$page->printPage();
}

function anyfont_help($default_help){
	$anyfont_contextual_help = "<h4>".__("Help Pages for FontServ & AnyFont", 'anyfont')."</h4><p><a href='http://fontserv.com/help' target='_blank'>".__("FontServ FAQ and Help Page", 'anyfont')."</a><br/>
	<a href='http://wordpress.org/extend/plugins/anyfont/faq/' target='_blank'>".__("AnyFont FAQ", 'anyfont')."</a><br/>
	<a href='http://2amlife.com/projects/anyfont/' target='_blank'>".__("AnyFont Project Page", 'anyfont')."</a></p>
	<p>".__("If you need to contact support please use the support form available on your <a href='http://fontserv.com/account' target='_blank'>account</a> page at FontServ.com.<br /> If you have not yet signed up at FontServ.com, signing up is both free and quick to do. <a href='http://fontserv.com/signup' target='_blank'>Sign Up Here</a>", 'anyfont')."</p>";
	if(isset($_REQUEST['page'])){
		switch($_REQUEST['page']){

			case "anyfont-settings":
				$default_help = $anyfont_contextual_help;
				break;

			case "anyfont-fonts":
				$default_help = $anyfont_contextual_help;
				break;

			case "anyfont-styles":
				$default_help = $anyfont_contextual_help;
				break;
		}
	}
	return $default_help;
}

function anyfont_fonts_page() {
	require_once(ANYFONT_LIBDIR."/class.admin.php");
	$config = array("page" => "fonts", "title" => __("Font Manager", 'anyfont'));
	$page = new anyfontAdmin($config);
	$page->printPage();
}

function anyfont_styles_page() {
	require_once(ANYFONT_LIBDIR."/class.admin.php");
	$config = array("page" => "styles", "title" => __("Style Manager", 'anyfont'));
	$page = new anyfontAdmin($config);
	$page->printPage();
}

function anyfont_insert_scripts() {
	$page_req = parse_url( $_SERVER['REQUEST_URI']);
	if(isset($page_req['query'])){
		$page = explode("=", $page_req['query']);
		$anyfont_pages = array('anyfont-settings','anyfont-fonts','anyfont-styles');
		if(in_array($page[1], $anyfont_pages)){
			wp_enqueue_script("anyfont", ANYFONT_URL."/anyfont.js", array('prototype', 'scriptaculous'));
		}
	}
}

/**
* Check if .htaccess is available and if it should be updated.
* @return mixed
*/
function anyfont_check_htaccess(){
	if(get_option('anyfont_use_htaccess') === 'on'){
		if(stristr($_SERVER['SERVER_SOFTWARE'], "Apache")){
			if(!anyfont_check_apache_module("mod_rewrite")){
				return "NM"; //mod_rewrite not found
			}
			$rules = anyfont_get_htaccess_rules();
			$htaccess_file = ABSPATH.".htaccess";
			if(file_exists($htaccess_file)){
				if(!is_writeable($htaccess_file)){
					return "NW"; //file not writable
				} else {
					$cur = file_get_contents($htaccess_file);
					if(!strstr($cur, $rules)){
						return "NR"; //rules not found
					} else {
						return "OK";
					}
				}
			}
			return "NC"; //file not found 
		}
		return "NA";  //not running on apache.
	}
	return "NU"; //.htaccess disabled
}

function anyfont_get_htaccess_rules(){
	$anyfont_url = parse_url(ANYFONT_URL);
	$anyfont_path = trailingslashit($anyfont_url['path']);
	$rules = "# BEGIN AnyFont\n";
	$rules .= "<IfModule mod_rewrite.c>\n";
	$rules .= "RewriteEngine On\n";
	$rules .= "RewriteBase /\n";
	$rules .= "RewriteCond %{REQUEST_FILENAME} !-f\n";
	$rules .= "RewriteRule ^images/(.*)\.png$ {$anyfont_path}img.php [L]\n";
	$rules .= "</IfModule>\n\n";
	get_option("anyfont_add_mimetype") ? $rules .= "AddType font/opentype  .otf .woff .ttf\nAddType application/vnd.ms-fontobject .eot\n\n" : 0;
	$rules .= "# END AnyFont\n\n";
	return $rules;
}

/**
* Update .htaccess file according to permalink status
* @param string
*/
function anyfont_update_permalink_status($action=false){
	$htaccess_file = ABSPATH.".htaccess";
	switch($action){
	
		case  "uninstall":
			if(file_exists($htaccess_file)){
				if(is_writeable($htaccess_file)){
					$contents = file_get_contents($htaccess_file);
					$start = mb_strpos($contents, "# BEGIN AnyFont");
					$end = mb_strpos($contents, "# END AnyFont") + mb_strlen("# END AnyFont");
					if(is_numeric($start) && is_numeric($end)){
						$new_contents = substr_replace($contents, "", $start, $end);
						anyfont_file_put_contents($htaccess_file, $new_contents);
					}
				}
			}
			break;

		case "install":
			if(get_option('anyfont_use_htaccess') === "on"){
				$rules = anyfont_get_htaccess_rules();
				if(!file_exists($htaccess_file)){
					if(is_writeable(ABSPATH)){
						touch($htaccess_file);
						anyfont_file_put_contents($htaccess_file, $rules);
					}
				} else {
					$orig = file_get_contents($htaccess_file);
					if(!strstr($orig, $rules)){
						anyfont_file_put_contents($htaccess_file, $rules.$orig);
					}
				}
			}
			break;

		default:
			$status = anyfont_check_htaccess();
			if($status == "NR" || $status == "NC"){
				anyfont_update_permalink_status("install");

			} else if($status == "NU"){
				anyfont_update_permalink_status("uninstall");
			}
			break;
	}
	return true;
}

function anyfont_install(){
	add_option('anyfont_image_module', 'auto');
	add_option('anyfont_disable_hotlinking', true);
	add_option('anyfont_enable_tinymce', true);
	add_option('anyfont_cache_last_checked', time());
	add_option('anyfont_init_error', 0);
	add_option('anyfont-fontserv-api-key', '');
	add_option("anyfont_styles", serialize(array()));
	delete_option('anyfont-use-htaccess');
	$anyfont_cap = array('manage_anyfont', 'manage_fonts', 'create_font_styles', 'assign_font_style', 'upload_fonts');
	$role = get_role('administrator');
	foreach($anyfont_cap as $cap){
		$role->add_cap($cap);
	}
	$add_role = add_role("anyfont_admin", "AnyFont Admin", array('read' => true));
	if (null !== $add_role) {
		$aa_role = get_role('anyfont_admin');
		foreach($anyfont_cap as $cap){
			$aa_role->add_cap($cap);
		}
	}
	if(isset($_SERVER['SERVER_SOFTWARE']) && strstr(strtolower($_SERVER['SERVER_SOFTWARE']), "iis")){
		update_option("anyfont_disable_prettylinks", "on");
	}
	anyfont_update_permalink_status();
	anyfont_migrate_styles();
	anyfont_fix_custom_css();
	require_once(ANYFONT_LIBDIR."/class.admin.php");
	$init = new anyfontAdmin(false);
	$init->checkAnyFontHealth();
	delete_option("anyfont_previous_version");
	update_option("anyfont_current_version", ANYFONT_VERSION);
	return true;
}

function anyfont_uninstall(){
	update_option('anyfont_init_error', false);
	anyfont_update_permalink_status("uninstall");
}

function anyfont_fix_custom_css(){
	$css_rules = maybe_unserialize(get_option("anyfont_customcss_list"));
	if(!is_array($css_rules)){
		return true;
	}
	foreach($css_rules as $rule){
		$enc = anyfont_custom_encode($rule);
		if(!get_option("anyfont_$enc")){
			$option = get_option("anyfont_$rule");
			$styledata = get_option("anyfont_{$rule}_style");
			add_option("anyfont_$enc", $option);
			add_option("anyfont_{$enc}_style", $styledata);
			delete_option("anyfont_$rule");
			delete_option("anyfont_{$rule}_style");
		}
	}
}

function anyfont_migrate_styles(){
	if(file_exists(ANYFONT_FONTDIR."/styles.ini")){
		$styles = parse_ini_file(ANYFONT_FONTDIR."/styles.ini", true);
		if(isset($styles['admin']))
			unset($styles['admin']);

		$new_styles = array(
			"image" => $styles,
			"css" => array()
		);
// 		foreach($new_styles['image'] as $name => $style){
// 			$new_styles['image'][$name]['assigned'] = array();
// 		}
// 		$final_styles = anyfont_migrate_settings($new_styles);
		anyfont_write_styles($new_styles);
		unlink(ANYFONT_FONTDIR."/styles.ini");
	} else if(!get_option("anyfont_styles")){
		$final_styles = array(
			"image" => array(),
			"css" => array()
		);
		anyfont_write_styles($final_styles);
	}
}

/*function anyfont_migrate_settings($styles){
	$old_switches = array("anyfont_post_title", "anyfont_widget_title", "anyfont_page_title", "anyfont_blog_title", "anyfont_blog_desc", "anyfont_tag_title", "anyfont_cat_title", "anyfont_menu");
	$transfer = array();
	foreach($old_switches as $option){
		$val = get_option($option) == 1 ? "on" : "off";
		$style = get_option($option."_style");
		$newoption = str_replace("anyfont_", "", $option);
		if($val == "on"){
			$transfer[$newoption] = $style;
			$styles[$style]['assigned'] = array($newoption);
		}
		delete_option($option);
		delete_option($option."_style");
	}
	if(get_option("anyfont_menu") == 1){
		$transfer['menu_hover'] = get_option("anyfont_menu_hover");
		$transfer['menu_active'] = get_option("anyfont_menu_active");
	}
	add_option("anyfont_assigned", serialize($transfer));
	return $styles;
}*/

function anyfont_file_put_contents($filename, $data){
	if(is_writeable($filename)){
		if(!function_exists('version_compare') || version_compare( phpversion(), '5', '<' )){
			$fw = fopen($filename, 'w');
			fwrite($fw, $data);
			fclose($fw);
		} else {
			file_put_contents($filename, $data);
		}
		return true;
	}
	return false;
}

function anyfont_serialize_array($array, $prefix = ''){
	$ini = array();
	if( is_array($array)){
		ksort($array);
		foreach ($array as $key => $value){
			// parse data types
			if ($value === true || $value == '1')
				$value = 'true';
			else if ($value === false || $value === '')
				$value = 'false';
			else if (is_string($value) && $key != 'font-size')
				$value = '"' . addslashes($value) . '"';

			// serialize value
			if (!is_array($value) && !is_numeric($key))
				$ini[] = ($prefix ? $prefix . '.' : '') . $key . ' = ' . $value;
			else if (!is_array($value))
				$ini[] = $prefix . '[] = ' . $value;
			else
				$ini = array_merge($ini, anyfont_serialize_array($value, ($prefix ? $prefix . '.' : '') . $key));
		}
		return $ini;
	} else {
		return false;
	}
}

function anyfont_check_for_multsite() {
	global $wpmu_version;
	if (function_exists('is_multisite'))
		return is_multisite();
	if (!empty($wpmu_version))
		return true;
	return false;
}

function anyfont_edit_styles(){

	if(isset($_REQUEST['update_style']) && $_REQUEST['update_style'] != ''){
		$style = _anyfont_edit_styles(false);
		anyfont_write_styles($style);
		if($_REQUEST['style-type'] === "image" && !isset($_REQUEST['new-style'])){
			$result =anyfont_edit_style_return_img(false);
		} else if(isset($_REQUEST['new-style'])){
			require_once(ANYFONT_LIBDIR."/".ANYFONT_LIB_VERSION."/class.tpl.php");
			require_once(ANYFONT_LIBDIR."/class.admin.php");
			$admn = new anyfontAdmin();
			$style_block = $admn->getStyleBlock($_REQUEST['update_style'], $style[$_REQUEST['style-type']][$_REQUEST['update_style']], $_REQUEST['style-type']);
			$result = array("savestatus" => "savedNew", "type" => $_REQUEST['style-type'], "stylename" => $_REQUEST['update_style'], "styleblock" => $style_block, "msg" => $_REQUEST['update_style']." has been saved.");
		} else {
			$result = array("savestatus" => "saved");
		}
	} else {
		$result = array("savestatus" => "failed", "error" => _("Please enter the style name."));
	}
	anyfont_return_json($result);
}

function anyfont_preview_style(){

	$style = _anyfont_edit_styles(true);
	anyfont_write_styles($style);
	anyfont_return_json(anyfont_edit_style_return_img(true));
}

function anyfont_edit_style_return_img($preview=false){

	$style_name = !$preview ? urlencode($_REQUEST['update_style']) : "Preview";
	$url = anyfont_using_permalinks() ? ANYFONT_IMAGE_URL."$style_name/".urlencode($_REQUEST['update_style']).".png" : ANYFONT_IMAGE_URL."$style_name&txt=".urlencode($_REQUEST['update_style']);
	return array("savestatus" => "saved",
				"stylename" => str_replace(" ", "__", $_REQUEST['update_style']),
				// RW : Added an @ to the base64_encode to prevent AJAX errors on my prod server while saving an edited style.
				"img" => @base64_encode(file_get_contents($url))
				);
}

function _anyfont_edit_styles($preview=false){
	$type = $_REQUEST['style-type'];
	$update_style = !$preview ? $_REQUEST['update_style'] : "Preview";
	$style_id = str_replace(" ", "__", $_REQUEST['update_style']);
	$style = unserialize(get_option('anyfont_styles'));
	switch($type){

		case "image":
			$style[$type][$update_style]['font-name'] = $_REQUEST['font-name'];
			$style[$type][$update_style]['font-size'] = str_replace("pt", "", $_REQUEST['font-size']);
			$style[$type][$update_style]['color'] = str_replace("#", "", $_REQUEST['color']);
			$style[$type][$update_style]['background-color'] =  str_replace("#", "", $_REQUEST['background-color']);
			$style[$type][$update_style]['shadow'] = $_REQUEST['shadow'] == 'on' ? true : false;
			$style[$type][$update_style]['shadow-color'] =  str_replace("#", "", $_REQUEST['shadow-color']);
			$style[$type][$update_style]['shadow-spread'] = $_REQUEST['shadow-spread']."";
			$style[$type][$update_style]['shadow-distance'] = str_replace("px", "", $_REQUEST['shadow-distance']);
			$style[$type][$update_style]['soften-shadow'] = $_REQUEST['soften-shadow'] == 'on' ? true : false;
			$style[$type][$update_style]['image-padding'] = $_REQUEST['image-padding'] == 'on' ? true : false;
			$style[$type][$update_style]['image-padding-top'] = str_replace("px", "", $_REQUEST['image-padding-top']);
			$style[$type][$update_style]['image-padding-bottom'] = str_replace("px", "", $_REQUEST['image-padding-bottom']);
			$style[$type][$update_style]['image-padding-left'] = str_replace("px", "", $_REQUEST['image-padding-left']);
			$style[$type][$update_style]['image-padding-right'] = str_replace("px", "", $_REQUEST['image-padding-right']);
			$style[$type][$update_style]['line-height'] = str_replace("px", "", $_REQUEST['line-height']);
			$style[$type][$update_style]['max-width-text-align'] = isset($_REQUEST[$style_id.'_max-width-text-align']) ? $_REQUEST[$style_id.'_max-width-text-align'] : $_REQUEST['max-width-text-align'];
			// RW : New attributes
			$style[$type][$update_style]['limit-width'] = $_REQUEST['limit-width'] == 'on' ? true : false;
			$style[$type][$update_style]['max-width'] = str_replace("px", "", $_REQUEST['max-width']);
			break;

		case "css":
			$style[$type][$update_style]['color'] = $_REQUEST['color'];
			$style[$type][$update_style]['font-family'] = $_REQUEST['_font-family'];
			$style[$type][$update_style]['font-file'] = $_REQUEST['font-family'];
			$style[$type][$update_style]['font-size'] = $_REQUEST['font-size'];
			$style[$type][$update_style]['font-weight'] = $_REQUEST['font-weight'] == "bold" ? "bold" : "normal";
			$style[$type][$update_style]['font-style'] = $_REQUEST['font-style'] == "oblique" ? "oblique" : "";
			$style[$type][$update_style]['text-decoration'] = $_REQUEST['text-decoration'] == "underline" ? "underline" : "";
			$style[$type][$update_style]['text-align'] = !$_REQUEST['text-align'] ? "" : $_REQUEST['text-align'];
			$style[$type][$update_style]['line-height'] = $_REQUEST['line-height'] == "0px" ? "1" : $_REQUEST['line-height'];
			$style[$type][$update_style]['text-shadow'] = $_REQUEST['shadow'] == "on" ? $_REQUEST['shadow-distance']." ".$_REQUEST['shadow-distance']." ".$_REQUEST['shadow-spread']."px ".$_REQUEST['shadow-color'] : "";
			break;
	}
	return $style;
}

function anyfont_write_styles($styles){

	ksort($styles);
	update_option("anyfont_styles", serialize($styles));
	if(get_option("anyfont-fontserv-run-backup") == "on"){
		require_once(ANYFONT_LIBDIR.'/class.fontserv-client.php');
		$key = get_option('anyfont-fontserv-api-key');
		$server = new FontServ(ANYFONT_FONTSERV_URL, $key, get_option('siteurl'));
		$server->update_styles(serialize($styles), $key, get_option('siteurl'));
	}
	return true;
}

function anyfont_delete_font(){

	if(isset($_REQUEST['font-name'])){
		$font = urldecode($_REQUEST['font-name']);
		$css3 = _anyfont_delete_font($font);
// 		$css3 !== false ? anyfont_deletefont_fontserv($font) : 0;
		printf(__("%s has been deleted.", 'anyfont'), $font);
		exit(0);
	}else if(isset($_REQUEST['fonts'])){
		$fonts = explode(",", $_REQUEST['fonts']);
		foreach($fonts as $font){
			$font = urldecode($font);
			$css3 = _anyfont_delete_font($font);
// 			$css3 !== false ? anyfont_deletefont_fontserv($font) : 0;
		}
		_e("The selected fonts have been deleted.", 'anyfont');
		exit(0);
	}
}

function _anyfont_delete_font($font){
	$types = array(".ttf", ".otf", ".svg", ".eot", ".woff");
	$i=0;
	while($i<count($types)){
		file_exists(ANYFONT_FONTDIR."/".$font.$types[$i]) ? unlink(ANYFONT_FONTDIR."/".$font.$types[$i]) : 0;
		$i++;
	}
	if($i > 1){
		return true;
	}
	return false;
}

// function anyfont_deletefont_fontserv($font){
// 	
// 	require_once(ANYFONT_LIBDIR.'/class.fontserv-client.php');
// 	$server = new FontServ(ANYFONT_FONTSERV_URL, get_option('anyfont-fontserv-api-key'), get_option("siteurl"));
// 	$server->removeFont($font);
// 
// }

function anyfont_delete_style(){

	$style = unserialize(get_option('anyfont_styles'));
	if(isset($_REQUEST['styles'])){
		$styles = explode(",", $_REQUEST['styles']);
		foreach($styles as $stylename){
			unset($style[$_REQUEST['type']][urldecode($stylename)]);
			$msg = __("The selected styles have been deleted.", 'anyfont');
		}
	}else if(isset($_REQUEST['style-name'])){
		unset($style[$_REQUEST['type']][urldecode($_REQUEST['style-name'])]);
		$msg = sprintf(__("%s has been deleted.", 'anyfont'), $_REQUEST['style-name']);
	}
	if(anyfont_write_styles($style)){
		echo $msg;
		exit(0);
	} else {
		echo "Something broke...";
		exit(0);
	}
}

function anyfont_clear_cache(){

	$cachedir = ANYFONT_CACHE;
	$dir = opendir($cachedir);
	while ($filename = readdir($dir)) {
		if(!is_dir($filename)){
			unlink($cachedir."/".$filename);
		}
	}
	rmdir($cachedir);
	wp_mkdir_p(ANYFONT_CACHE);
	require_once(ANYFONT_LIBDIR."/class.admin.php");
	$html = new anyfontAdmin(false);
	$html->checkAnyFontHealth();
	$return = array("block"=>"anyfont_disk_usage",
					"content"=>$html->getDiskCache(true),
					"message"=>__("Cache Cleared", 'anyfont')
					);
	anyfont_return_json($return);
}

function anyfont_replace_title($title){

	global $post,$dont_replace_title, $anyfont_title_css;
	if(!in_the_loop() || is_feed()){
		return $title;
	}else{
		$uri = explode("/", $_SERVER['REQUEST_URI']);
		$option_bool = 'anyfont_'.$post->post_type.'_title';
		$option_style = 'anyfont_'.$post->post_type.'_title_style';
		$title_replace = get_option($option_bool);
		if(!$dont_replace_title && $title_replace && !in_array("wp-admin", $uri)){
			 $all_styles = unserialize(get_option('anyfont_styles'));
			 $style = get_option($option_style);
			 if(!$all_styles['image'][$style]){
				$anyfont_title_css = anyfont_css_title($post->post_type, $all_styles['css'][$style]);
				add_action("loop_end", "anyfont_insert_css_title", 1, 1);
				return $title;
			 } else{
				$urltitle = urlencode($title);
				$style =  urlencode($style);
				$url = anyfont_using_permalinks() ? ANYFONT_IMAGE_URL."$style/$urltitle.png" : ANYFONT_IMAGE_URL."$style&txt=$urltitle";
				return "<img src=\"$url\" title=\"$title\" alt=\"$title\" style=\"border: 0 none ;\"/>";
			 }
		}else{
			return $title;
		}
	}
}

function anyfont_replace_tag_title($title){

	$uri = explode("/", $_SERVER['REQUEST_URI']);

	if(get_option('anyfont_tag_title') && !in_array("wp-admin", $uri)){
		remove_filter("single_tag_title", "anyfont_replace_tag_title", 10, 2);
		 $style = get_option('anyfont_tag_title_style');
		 $urltitle = urlencode($title);
		 $style =  urlencode($style);
		 $url = anyfont_using_permalinks() ? ANYFONT_IMAGE_URL."$style/$urltitle.png" : ANYFONT_IMAGE_URL."$style&txt=$urltitle";
		 return "<img src=\"$url\" title=\"$title\" alt=\"$title\" style=\"border: 0 none ;\"/>";
	}else{
		return $title;
	}
}

function anyfont_replace_cat_title($title){

	$uri = explode("/", $_SERVER['REQUEST_URI']);

	if(is_category() && get_option('anyfont_cat_title') && !in_array("wp-admin", $uri)){
		remove_filter("single_cat_title", "anyfont_replace_cat_title", 10, 2);
		 $style = get_option('anyfont_cat_title_style');
		 $urltitle = urlencode($title);
		 $style =  urlencode($style);
		 $url = anyfont_using_permalinks() ? ANYFONT_IMAGE_URL."$style/$urltitle.png" : ANYFONT_IMAGE_URL."$style&txt=$urltitle";
		 echo "<img src=\"$url\" title=\"$title\" alt=\"$title\" style=\"border: 0 none ;\"/>";
	}else{
		return $title;
	}
}

function anyfont_replace_widget_title($params){

	if(get_option('anyfont_widget_title')){
		$style = urlencode(get_option('anyfont_widget_title_style'));
		if(anyfont_using_permalinks()){
			$before_title = "<img src=\"".ANYFONT_IMAGE_URL."$style/";
			$after_title = ".png\" alt=\" \" title=\" \" />";
		} else {
			$before_title = "<img src=\"".ANYFONT_IMAGE_URL."$style&txt=";
			$after_title = "\" alt=\" \" title=\" \" />";
		}
		if(is_array($params)){
			foreach($params as $key => $param){
				if(!preg_match("/<a\s.*>/", $params[$key]['before_title'], $match) && !preg_match("/<a\s.*>/", $params[$key]['after_title'], $match)){
					$params[$key]['before_title'] = $params[$key]['before_title'].$before_title;
					$params[$key]['after_title'] = $after_title.$params[$key]['after_title'];
				}
			}
		}
	}
	return $params;
}

function anyfont_update_option(){

	$return = array("type"=>"message",
					"message"=>__("Your settings have been saved!", 'anyfont')
					);
	if(is_array($_REQUEST['option'])){
		foreach($_REQUEST['option'] as $option){
			if(!is_array($_REQUEST[$option])){
				$option === 'anyfont_add_mimetype' ? anyfont_update_permalink_status("uninstall") : 0;
				update_option($option , $_REQUEST[$option]);
				$option === 'anyfont_add_mimetype' ? anyfont_update_permalink_status('') : 0;
				$option === 'anyfont_use_htaccess' ? $_REQUEST[$option] == "on" ? anyfont_update_permalink_status('install') : anyfont_update_permalink_status("uninstall") : 0;
				if($option == 'anyfont-limit-cache-size' || $option == 'anyfont-cache-size-limit'){
					require_once(ANYFONT_LIBDIR."/class.admin.php");
					$html = new anyfontAdmin(false);
					$html->checkAnyFontHealth(false);
					$return = array("type"=>"replace",
									"block"=>"anyfont_disk_usage",
									"content"=>$html->getDiskCache(true),
									"message"=>__("Your settings have been saved!", 'anyfont')
									);
				} else if($option == 'anyfont-fontserv-api-key') {
					require_once(ANYFONT_LIBDIR."/class.admin.php");
					$html = new anyfontAdmin(false);
					$response = $html->checkKey("return");
					$return = array("type"=>"update",
									"block"=>"anyfont-fontserv-key-status",
									"content" => $response['msg'],
									"message"=>__("Your API key has been saved!", 'anyfont')
									);
				}
			}else {
				$result = $_REQUEST[$option]['result'] == "on" ? true : false;
				update_option($option , $result);
                if($result){
                    if($option['style'] !== "false"){
                        update_option($option."_style", $_REQUEST[$option]['style']);
                        if($option == "anyfont_menu"){
							update_option($option."_hover", $_REQUEST[$option]['hover']);
							update_option($option."_active", $_REQUEST[$option]['active']);
                        }
                    } else {
						$return = array("type"=>"message",
										"message"=>__("Error: No Style Selected! If you have not created any styles yet, <a href='admin.php?page=anyfont-styles'>click here</a> to go to the style manager now.", 'anyfont')
										);
                    }
                 }
			 }
		}
	}
	if(is_array($_REQUEST['css_options'])){
		foreach($_REQUEST['css_options'] as $option){

			$result = $_REQUEST[$option]['result'] == "on" ? true : false;
			update_option($option , $result);
			if($result){
				if($option['style'] !== "false"){
					 update_option($option."_style", $_REQUEST[$option]['style']);
				} else {
					$return = array("type"=>"message",
									"message"=>__("Error: No Style Selected! If you have not created any styles yet, <a href='admin.php?page=anyfont-styles'>click here</a> to go to the style manager now.", 'anyfont')
									);
				}
			}
		}
// 		$return["debug"] =$_REQUEST;
	}
	if(is_array($_REQUEST['new_rule']) && $_REQUEST[$_REQUEST['new_rule'][0]]['result'] != "Add a custom CSS Rule" && $_REQUEST[$_REQUEST['new_rule'][0]]['result'] != ""){
		$css_custom = maybe_unserialize(get_option('anyfont_customcss_list'));
		!is_array($css_custom) ? $css_custom = array() : 0;
		$errors = false;
		foreach($_REQUEST['new_rule'] as $option){
			if($_REQUEST[$option]['result'] != "" && $_REQUEST[$option]['style'] != "false"){
				$name = urldecode($_REQUEST[$option]['result']);
				if(!in_array($name, $css_custom)){
					$css_custom[] = $name;
					$name = anyfont_custom_encode($name);
					update_option("anyfont_".$name , true);
					update_option("anyfont_".$name."_style", $_REQUEST[$option]['style']);
				}
				update_option("anyfont_customcss_list", serialize($css_custom));
			} else {
				$errors = true;
			}
		}
		if(!$errors){
			require_once(ANYFONT_LIBDIR."/class.admin.php");
			$html = new anyfontAdmin(false);
			$return = array("type"=>"update",
							"block"=>"css_custom_rule_block",
							"message"=>__("Your settings have been saved!", 'anyfont'),
							"create_cb"=>'css_custom_rule_block',
							"content"=>$html->getCSSRuleBlock()
							);
		} else {
			$return = array("type"=>"message",
							"message"=>__("Error: There was an error while saving, Please check if all the enabled options have a style selected.", 'anyfont')
							);
		}
	}
	anyfont_return_json($return);
}

function anyfont_delete_custom_css(){
	$rule = $_REQUEST['css_rule'];
	delete_option("anyfont_".$rule);
	delete_option("anyfont_".$rule."_style");
	$css_rule = base64_decode($rule);
	$css_custom = maybe_unserialize(get_option('anyfont_customcss_list'));
	if(is_array($css_custom)){
		foreach($css_custom as $key => $val){
			if($css_rule == $val){
				unset($css_custom[$key]);
			}
		}
		update_option("anyfont_customcss_list", serialize($css_custom));
	}
	$return = array("result"=>"success",
					"delid"=> $rule,
					"message"=>__("Custom CSS rule deleted.", 'anyfont')
					);
	anyfont_return_json($return);
}

function anyfont_return_json($return){

	header("Content-type: application/json");
	
	if (function_exists('json_encode')) {
		$out = json_encode($return);
	} else {
		require_once(ANYFONT_ROOT.'/lib/class.json.php');
		$JSON = new serviceJSON();
		$out = $JSON->encode($return);
	}
	if(!ini_get('zlib.output_compression') && 'ob_gzhandler' != ini_get('output_handler') && isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
		header('Vary: Accept-Encoding'); // Handle proxies
		if ( false !== strpos( strtolower($_SERVER['HTTP_ACCEPT_ENCODING']), 'deflate') && function_exists('gzdeflate')) {
			header('Content-Encoding: deflate');
			$out = gzdeflate( $out, 3 );
		} elseif ( false !== strpos( strtolower($_SERVER['HTTP_ACCEPT_ENCODING']), 'gzip') && function_exists('gzencode') ) {
			header('Content-Encoding: gzip');
			$out = gzencode( $out, 3 );
		}
	}
	echo $out;
	exit();
}

function anyfont_bloginfo_replace($output, $show){

	switch($show){
		case 'name':
			if(get_option('anyfont_blog_title')){
				$style = get_option('anyfont_blog_title_style');
				$urltitle = urlencode($output);
				$style =  urlencode($style);
				$url = anyfont_using_permalinks() ? ANYFONT_IMAGE_URL."$style/$urltitle.png" : ANYFONT_IMAGE_URL."$style&txt=$urltitle";
				!get_option('anyfont_blog_desc') ? remove_filter("bloginfo", "anyfont_bloginfo_replace", 10, 2) : 0;
				return "<img src=\"$url\" title=\"$output\" alt=\"$output\" style=\"border: 0 none ;\"/>";
			}else{
				!get_option('anyfont_blog_desc') ? remove_filter("bloginfo", "anyfont_bloginfo_replace", 10, 2) : 0;
				return $output;
			}

		case 'description':
			if(get_option('anyfont_blog_desc')){
				$style = get_option('anyfont_blog_desc_style');
				$urltitle = urlencode($output);
				$style =  urlencode($style);
				$url = anyfont_using_permalinks() ? ANYFONT_IMAGE_URL."$style/$urltitle.png" : ANYFONT_IMAGE_URL."$style&txt=$urltitle";
				remove_filter("bloginfo", "anyfont_bloginfo_replace", 10, 2);
				return  "<img src=\"$url\" title=\"$output\" alt=\"$output\" style=\"border: 0 none ;\"/>";
			}else{
				remove_filter("bloginfo", "anyfont_bloginfo_replace", 10, 2);
				return $output;
			}

		default:
			return $output;

	}
}

function anyfont_img_output(){


	$server_addr = !isset($_SERVER['SERVER_ADDR']) ? $_SERVER['LOCAL_ADDR'] : $_SERVER['SERVER_ADDR'];
	if($_SERVER['REMOTE_ADDR'] != $server_addr){
		$site_url = parse_url(get_option('siteurl'));
		$referer = isset($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER']) : false;
		if(is_array($referer) && (!strstr($site_url['host'], $referer['host']) && get_option('anyfont_disable_hotlinking'))){
			wp_redirect(get_option('siteurl'));
			exit(0);
		}
	}

	if(isset($_REQUEST['imgstyle']) && isset($_REQUEST['txt'])){
		$style = urldecode($_REQUEST['imgstyle']);
		$text = html_entity_decode(urldecode($_REQUEST['txt']), ENT_QUOTES, 'UTF-8');
		$displaytext = isset($_REQUEST['displaytext']) ? html_entity_decode(urldecode($_REQUEST['displaytext']), ENT_QUOTES, 'UTF-8') : false;
	} else if( preg_match("/\/images\/(.*)\.png/", $_SERVER['REQUEST_URI'])){
		$req_vars = explode( "/",  (!defined("ANYFONT_IMG_VAR") ? $_SERVER['REQUEST_URI'] : ANYFONT_IMG_VAR));

		foreach($req_vars as $n => $v){
			if($v == "images"){
				$sn = $n;
				continue;
			}
		}
		$style = urldecode($req_vars[($sn+1)]);
		$text = str_replace(".png", "", html_entity_decode(urldecode($req_vars[($sn+2)]), ENT_QUOTES, 'UTF-8'));
		$displaytext = isset($req_vars[($sn+3)]) ? str_replace(".png", "", html_entity_decode(urldecode($req_vars[($sn+3)]), ENT_QUOTES, 'UTF-8')) : false;
	}
	
	if(!isset($text) || !isset($style)){
		wp_redirect(get_option('siteurl'));
		exit(0);
	} else {
		$gzip = isset($_SERVER['HTTP_ACCEPT_ENCODING']) ? strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') ? get_option('anyfont_enable_gzip') == "on" ? 1 : 0 : 0 : 0;
		require_once(ANYFONT_LIBDIR."/".ANYFONT_LIB_VERSION."/class.image.php");
		new ttfImage($text, $style, $displaytext, $gzip);
	}
}

function anyfont_img_headers($headers){

	$headers = false;
	return $headers;
}

function anyfont_add_header_filter(){

	add_filter("bloginfo", "anyfont_bloginfo_replace", 10, 2);
	add_filter("single_cat_title", "anyfont_replace_cat_title", 10, 2);
	add_filter("single_tag_title", "anyfont_replace_tag_title", 10, 2);
}

function anyfont_page_menu_filter($menu){
	if(get_option('anyfont_menu')){
		$menu_types = array();
		$menu_types["style"] = anyfont_check_style_type(get_option('anyfont_menu_style'));
// 		$menu_types["hover"] = anyfont_check_style_type(get_option('anyfont_menu_hover'));
// 		$menu_types["active"] = anyfont_check_style_type(get_option('anyfont_menu_active'));
		if($menu_types["style"] == "css"){
			$menu = anyfont_get_generic_css("anyfont_menu", "li.menu-item", true).anyfont_get_generic_css("anyfont_menu_hover", "li.menu-item:hover", true).anyfont_get_generic_css("anyfont_menu_active", "li.current-menu-item", true).$menu;
		} else if($menu_types["style"] == "image"){
			$menu = anyfont_get_menu_images($menu);
		}
	}
    return $menu;
}

function anyfont_get_menu_images($menu){

	$url = get_option('siteurl');
	$urltitle = urlencode($url);
	$standard =  ANYFONT_IMAGE_URL.urlencode(get_option('anyfont_menu_style'));
	$hover =  ANYFONT_IMAGE_URL.urlencode(get_option('anyfont_menu_hover'));
	$active = ANYFONT_IMAGE_URL.urlencode(get_option('anyfont_menu_active'));
	$splitmenu = explode("</li>\n", $menu);
	foreach($splitmenu as $listitem){
		if(preg_match_all('/title="([\w*\.*\?*\!*\s*]+)/', $listitem, $titles) > 0){
			$n=0;
			foreach($titles[0] as $key){
				$imgurl = preg_match("/current_page_item/i", $listitem) || preg_match("/current_menu_item/i", $listitem) ? $active : $standard;
				$encoded_title = urlencode($titles[1][$n]);
				$imgurl = anyfont_using_permalinks() ? $imgurl.'/'.$encoded_title.'.png' : $imgurl.'&txt='.$encoded_title;
				$hover_url = anyfont_using_permalinks() ? $hover.'/'.$encoded_title.'.png' : $hover.'&txt='.$encoded_title;
				$menu = preg_replace("/($key\W.*>?<?s?p?a?n?>?)({$titles[1][$n]})(<?\/?s?p?a?n?>?<\/a>)/", '$1<img class="anyfont_menu_image" src="'.$imgurl.'" alt="'.$titles[1][$n].'" style="border:0 none;" onmouseover="this.src = \''.$hover_url.'\'; return false;" onmouseout="this.src = \''.$imgurl.'\'; return false;" />$3', $menu);
				$n++;
			}
		}
	}
	return $menu;
}

function anyfont_ini_get_bool($a){

	$b = ini_get($a);

	switch (strtolower($b)){
		case 'on':
		case 'yes':
		case 'true':
			return 'assert.active' !== $a;

		case 'stdout':
		case 'stderr':
			return 'display_errors' === $a;

		default:
			return (bool)(int)$b;
    }
}

function anyfont_register_button($buttons) {
    array_push($buttons, "separator", "anyfont");
    return $buttons;
}

function anyfont_add_tinymce_plugin($plugin_array) {
    $plugin_array['anyfont'] = WP_PLUGIN_URL.'/anyfont/mce_anyfont/editor_plugin.js';
    return $plugin_array;
}

function anyfont_settings_link($links, $filename) {
	$filename == plugin_basename(__FILE__) ? array_unshift($links, '<a href="admin.php?page=anyfont-settings">Settings</a>') : 0;
	return $links;
}

function anyfont_dashboard_widget_function() {
	require_once(ANYFONT_LIBDIR."/class.admin.php");
	$html = new anyfontAdmin(false);
	$html->checkAnyFontHealth();
	echo $html->getDiskCache();
}

function anyfont_dashboard_widget() {
	wp_add_dashboard_widget('anyfont_dashboard_widget', 'AnyFont Disk Usage Summary', 'anyfont_dashboard_widget_function');
}

function anyfont_dec2ord($dec){
	return anyfont_dec2hex(ord($dec));
}

function anyfont_dec2hex($dec){
	return str_repeat('0', 2-strlen(($hex=strtoupper(dechex($dec))))) . $hex;
}

/**
* @original author Unknown
* found at http://www.phpclasses.org/browse/package/2144.html
*/
function anyfont_get_font_info($filename){
	$fd = fopen ($filename, "r");
	$text = fread ($fd, filesize($filename));
	fclose ($fd);

	$number_of_tables = hexdec(anyfont_dec2ord($text[4]).anyfont_dec2ord($text[5]));

	for($i=0;$i<$number_of_tables;$i++){
		$tag = $text[12+$i*16].$text[12+$i*16+1].$text[12+$i*16+2].$text[12+$i*16+3];

		if($tag == 'name'){
			$ntOffset = hexdec(
				anyfont_dec2ord($text[12+$i*16+8]).anyfont_dec2ord($text[12+$i*16+8+1]).
				anyfont_dec2ord($text[12+$i*16+8+2]).anyfont_dec2ord($text[12+$i*16+8+3])
			);

			$offset_storage_dec = hexdec(anyfont_dec2ord($text[$ntOffset+4]).anyfont_dec2ord($text[$ntOffset+5]));
			$number_name_records_dec = hexdec(anyfont_dec2ord($text[$ntOffset+2]).anyfont_dec2ord($text[$ntOffset+3]));
		}
	}

	$storage_dec = $offset_storage_dec + $ntOffset;
	$storage_hex = strtoupper(dechex($storage_dec));

	for($j=0;$j<$number_name_records_dec;$j++){
		$platform_id_dec	= hexdec(anyfont_dec2ord($text[$ntOffset+6+$j*12+0]).anyfont_dec2ord($text[$ntOffset+6+$j*12+1]));
		$name_id_dec		= hexdec(anyfont_dec2ord($text[$ntOffset+6+$j*12+6]).anyfont_dec2ord($text[$ntOffset+6+$j*12+7]));
		$string_length_dec	= hexdec(anyfont_dec2ord($text[$ntOffset+6+$j*12+8]).anyfont_dec2ord($text[$ntOffset+6+$j*12+9]));
		$string_offset_dec	= hexdec(anyfont_dec2ord($text[$ntOffset+6+$j*12+10]).anyfont_dec2ord($text[$ntOffset+6+$j*12+11]));

		if(!empty($name_id_dec) and empty($font_tags[$name_id_dec])){
			for($l=0;$l<$string_length_dec;$l++){
				if(ord($text[$storage_dec+$string_offset_dec+$l]) == '0'){
					continue;
				} else {
					$font_tags[$name_id_dec] .= ($text[$storage_dec+$string_offset_dec+$l]);
				}
			}
		}
	}
	return $font_tags;
}

function anyfont_check_apache_module($mod_name){

	if(function_exists("apache_get_modules")){
		$modules = apache_get_modules();
		return in_array($mod_name, $modules);
	} else {
		return "result unknown";
	}
}

function anyfont_convert_font(){
	if(isset($_REQUEST['fontname'])){
		$fontname = urldecode($_REQUEST['fontname']);
		$font = !file_exists(ANYFONT_FONTDIR."/$fontname.ttf") ? "$fontname.otf" : "$fontname.ttf";
		$fontinfo = anyfont_get_font_info(ANYFONT_FONTDIR."/".$font);
		require_once(ANYFONT_LIBDIR."/class.admin.php");
		$admin = new anyfontAdmin(false);
		$allfonts = $admin->readFontDir(true);
		require_once(ANYFONT_LIBDIR.'/class.fontserv-client.php');
		$server = new FontServ(ANYFONT_FONTSERV_URL, get_option('anyfont-fontserv-api-key'), get_option("siteurl"));
		if(count($allfonts[$fontinfo[1]]['type']) > 1){
			foreach($allfonts[$fontinfo[1]]['type'] as $type){
				$filename = $type['filename'];
				$font = !file_exists(ANYFONT_FONTDIR."/$filename.ttf") ? "$filename.otf" : "$filename.ttf";
				$files = $server->convertFont(file_get_contents(ANYFONT_FONTDIR."/".$font));
				if(isset($files['error'])){
					$response = array("success" => false,"msg" => $files['error']);
					continue;
				} else {
					$response = anyfont_save_webfonts($files, $filename, $fontinfo[1]);
				}
			}
		} else {
			$files = $server->convertFont(file_get_contents(ANYFONT_FONTDIR."/".$font));
			if(isset($files['error'])){
				$response = array("success" => false,"msg" => $files['error']);
			} else {
				$response = anyfont_save_webfonts($files, $fontname, $fontinfo[1]);
			}
		}
		anyfont_return_json($response);
	}
}

function anyfont_save_webfonts($files, $filename, $fontname){
	$response = array(
		"success" => false,
		"msg" => __("An error occured during the conversion process, please check if the fonts license allows web embedding.", 'anyfont')
	);
	if(is_array($files)){
		$e = 0;
		anyfont_check_fontserv_file($files["eot"]) ? file_put_contents(ANYFONT_FONTDIR."/{$filename}.eot", $files["eot"]) : $e++;
		anyfont_check_fontserv_file($files["woff"]) ? file_put_contents(ANYFONT_FONTDIR."/{$filename}.woff", $files["woff"]) : $e++;
		anyfont_check_fontserv_file($files["svg"]) ? file_put_contents(ANYFONT_FONTDIR."/{$filename}.svg", $files["svg"]) : 0;
		if($e === 0){
			$response = array(
				"success" => true,
				"msg" => sprintf(__("%s was successfully processed and can now be used with '@font-face' CSS styles.", 'anyfont'), $fontname)
			);
		}
	}
	return $response;
}

function anyfont_check_fontserv_file($filedata){
	if(strlen($filedata) > 0){
		return true;
	}
	return false;
}

function anyfont_css_webfonts($fontfamily, $filename){
	$return = "";
	$font = !file_exists(ANYFONT_FONTDIR."/$fontname.ttf") ? "$fontname.otf" : "$fontname.ttf";
	require_once(ANYFONT_LIBDIR."/class.admin.php");
	$admin = new anyfontAdmin(false);
	$allfonts = $admin->readFontDir(true);
	if(count($allfonts[$fontfamily]['type']) > 0){
		foreach($allfonts[$fontfamily]['type'] as $type){
			$filename = $type['filename'];
			$url = ANYFONT_FONT_URL.$filename;
			$font = !file_exists(ANYFONT_FONTDIR."/$filename.ttf") ? "$filename.otf" : "$filename.ttf";
			$return .= anyfont_css_fontface($fontfamily, $filename, $type['ftype']);
		}
	} /*else {
		$return = anyfont_css_fontface($filename, $type['ftype']);
	}*/
	return $return;
}

function anyfont_css_fontface($fontfamily, $filename, $type){
	$style="font-style:";
	$weight="font-weight:";
	switch($type){
		default:
		case "Regular":
			$style="";
			$weight.="normal";
			break;
		case "Medium":
			$style="";
			$weight.="medium";
			break;
		case "Bold":
			$style="";
			$weight.="bold";
			break;
		case "Oblique":
			$style.="oblique";
			$weight.="normal";
			break;
		case "BoldOblique":
		case "Bold Oblique":
			$style.="oblique";
			$weight.="bold";
			break;
		case "Italic":
			$style.="italic";
			$weight.="normal";
			break;
		case "BoldItalic":
		case "Bold Italic":
			$style.="italic";
			$weight.="bold";
			break;
	}
	$url = ANYFONT_FONT_URL.$filename;
	is_ssl() ? $url = str_replace("http", "https", $url) : 0;
	$truetype = file_exists(ANYFONT_FONTDIR."/$filename.ttf") ? "url('$url.ttf') format('truetype')" : "url('$url.otf') format('opentype')";
	$svg = file_exists(ANYFONT_FONTDIR."/$filename.svg") ? ", url('$url.svg#$filename') format('svg');" : ";";
	return "@font-face {
		font-family: '$fontfamily';
		src: url('$url.eot');
		src: local('☺'), url('$url.woff') format('woff'), {$truetype}{$svg}
		$weight;
		$style
	}";
}

function anyfont_css_title($post_type, $style_param){
	if(!is_array($style_param)){
		return "";
	}else{
		$style = "<style>";
		$style .= anyfont_css_webfonts($style_param['font-family'], $style_param['font-file']);
		$style .= ".{$post_type} h2, .{$post_type} h1, .{$post_type} h2.title a{";
		foreach($style_param as $key => $val){
			if($key !== "font-file" && $val !== ""){
				$style .= "$key:".($key == "font-family" ? '"'.$val.'"' : $val)." !important;";
			}
		}
		$style .= "}</style>";
		return  $style;
	}
}

function anyfont_insert_css_title($param){
	foreach($param->posts as $post){
		$option_style = 'anyfont_'.$post->post_type.'_title_style';
		$all_styles = unserialize(get_option('anyfont_styles'));
		$style = get_option($option_style);
		echo anyfont_css_title($post->post_type, $all_styles['css'][$style]);
	}
}

function anyfont_fav_actions($actions){

	if(is_array($actions) && get_option('anyfont_fav_links')){
		$actions["admin.php?page=anyfont-styles"] = array(__('New Font Style', 'anyfont'), 'manage_options');
		$actions["admin.php?page=anyfont-fonts"] = array(__('Upload New Font', 'anyfont'), 'manage_options');
	}
	return $actions;
}

function anyfont_get_generic_css($style_target, $css_target, $return = false){
	if($style_target == "anyfont_menu_hover" || $style_target == "anyfont_menu_active"){
		$style = get_option($style_target);
	} else {
		$style = get_option($style_target."_style");
	}
	$all_styles = unserialize(get_option('anyfont_styles'));
	$style_param = $all_styles["css"][$style];
	if(!is_array($style_param)){
		$style = "";
	} else {
		$style = "<style>";
		$style .= anyfont_css_webfonts($style_param['font-family'], $style_param['font-file']);
		$style .= "{$css_target}{";
		foreach($style_param as $key => $val){
			if($key !== "font-file" && $val !== ""){
				$style .= "$key:".($key == "font-family" ? '"'.$val.'"' : $val)." !important;";
			}
		}
		$style .= "}</style>";
	}
	if(!$return){
		echo $style;
	} else {
		return $style;
	}
}

function anyfont_body_text_css(){
	anyfont_get_generic_css('anyfont_body_text', "body");
}

function anyfont_header_text_css(){
	anyfont_get_generic_css('anyfont_header_text', "#header");
}

function anyfont_content_text_css(){
	anyfont_get_generic_css('anyfont_content_text', "#content");
}

function anyfont_footer_text_css(){
	anyfont_get_generic_css('anyfont_footer_text', "#footer");
}

function anyfont_insert_custom_css(){
	$css_rules = unserialize(get_option("anyfont_customcss_list"));
	if(is_array($css_rules)){
		foreach($css_rules as $rule){
			$enc_rule = anyfont_custom_encode($rule);
			if(get_option("anyfont_$enc_rule") != false){
				anyfont_get_generic_css("anyfont_$enc_rule", $rule);
			}
		}
	}
}

function anyfont_custom_encode($custom_rule){
	$rule = base64_encode($custom_rule);
	return str_replace("=", "", $rule);
}

function anyfont_check_style_type($style){

	$all_styles = unserialize(get_option('anyfont_styles'));
	if(!$all_styles['image'][$style]){
		return "css";
	} else if(!$all_styles['css'][$style]){
		return "image";
	} else {
		return false;
	}
}

function anyfont_using_permalinks(){

	global $wp_rewrite;
	
	if(get_option('anyfont_use_htaccess') !== ''){
		return true;
	} else if(get_option('anyfont_disable_prettylinks') == 'on'){
		return false;
	} else if(is_object($wp_rewrite) && method_exists($wp_rewrite, 'using_mod_rewrite_permalinks')){
		return $wp_rewrite->using_mod_rewrite_permalinks();
	} else {
		return false;
	}
}

function anyfont_define_url(){

	if(anyfont_using_permalinks()){
		$image_url = get_option('siteurl')."/images/";
	} else {
		$image_url = get_option('siteurl')."/?imgstyle=";
	}
	define('ANYFONT_IMAGE_URL',(is_ssl() ? str_replace("http", "https", $image_url) : $image_url));
}

add_action("setup_theme", "anyfont_define_url");

if((isset($_REQUEST['imgstyle']) && isset($_REQUEST['txt'])) || preg_match("/\/images\/(.*)\.png/", $_SERVER['REQUEST_URI'])){
	if(anyfont_ini_get_bool('display_errors')){
		ini_set('display_errors', 0);
	}
	ob_start();
	remove_all_actions("send_headers");
	remove_all_actions("get_header");
	add_action('plugins_loaded', 'anyfont_img_output');
} else {
	if(get_option('anyfont_menu') != false){
		add_filter("wp_page_menu", 'anyfont_page_menu_filter', 2, 1);
		add_filter("wp_nav_menu_items", "anyfont_page_menu_filter", 1, 1);
		add_filter("wp_list_pages", 'anyfont_page_menu_filter', 10, 1);
	}
	add_filter("dynamic_sidebar_params", "anyfont_replace_widget_title");
	add_filter("the_title", "anyfont_replace_title");
	add_filter('plugin_action_links', 'anyfont_settings_link', 10, 2 );
	add_action("wp_head", "anyfont_add_header_filter", 10, 0);
	if(get_option('anyfont_cache_dashboard_widget') != false){
		add_action('wp_dashboard_setup', 'anyfont_dashboard_widget' );
	}
	if(defined('WP_ADMIN') && get_option('anyfont_enable_tinymce') != false){
	    add_filter("mce_external_plugins", "anyfont_add_tinymce_plugin");
		add_filter('mce_buttons', 'anyfont_register_button');
	}
	if(defined('WP_ADMIN') && get_option("anyfont_init_error") != false){
		add_action( 'admin_notices', 'anyfont_warning_msg');
	}
	add_action('wp_ajax_anyfont_edit_styles', 'anyfont_edit_styles');
	add_action('wp_ajax_anyfont_delete_font', 'anyfont_delete_font');
	add_action('wp_ajax_anyfont_delete_style', 'anyfont_delete_style');
	add_action('wp_ajax_anyfont_clear_cache', 'anyfont_clear_cache');
	add_action('wp_ajax_anyfont_update_option', 'anyfont_update_option');
	add_action('wp_ajax_anyfont_preview_style', 'anyfont_preview_style');
	add_action('wp_ajax_anyfont_convert_font', 'anyfont_convert_font');
	add_action('wp_ajax_anyfont_delete_custom_css', 'anyfont_delete_custom_css');
	add_action('admin_menu', 'anyfont_admin_menu');
	add_action("admin_print_scripts", 'anyfont_insert_scripts');
	if(get_option('anyfont_body_text') != false){
		 add_action("wp_head", "anyfont_body_text_css");
	}
	if(get_option('anyfont_header_text') != false){
		 add_action("wp_head", "anyfont_header_text_css");
	}
	if(get_option('anyfont_content_text') != false){
		 add_action("wp_head", "anyfont_content_text_css");
	}
	if(get_option('anyfont_footer_text') != false){
		 add_action("wp_head", "anyfont_footer_text_css");
	}
	add_action("wp_head", "anyfont_insert_custom_css");
	add_filter("favorite_actions", "anyfont_fav_actions");
	register_activation_hook(__FILE__, 'anyfont_install');
	register_deactivation_hook(__FILE__, 'anyfont_uninstall');
}
