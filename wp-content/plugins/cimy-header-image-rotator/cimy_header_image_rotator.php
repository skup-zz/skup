<?php
/*
Plugin Name: Cimy Header Image Rotator
Plugin URI: http://www.marcocimmino.net/cimy-wordpress-plugins/cimy-header-image-rotator/
Description: Displays an image that automatically rotate depending on setting. You can setup from one second up to one day and more. Intermediate settings are also possible.
Author: Marco Cimmino
Version: 4.0.3
Author URI: cimmino.marco@gmail.com

This plugin is based on the "Header Image Rotator - Basic" v2.1 plugin by Matthew Hough (http://www.wpimagerotator.com). Thanks!

Matthew's plugin was based on the "Header Randomizer" plugin by Lennart Groetzbach (http://www.lennartgroetzbach.de/blog/?p=1040). Thanks!

This plugin uses jquery.cross-slide.js by Tobia Conforto <tobia.conforto@gmail.com> (http://tobia.github.com/CrossSlide/) (GPL v2 license). Thanks!

Copyright (C) 2009-2011 Marco Cimmino (cimmino.marco@gmail.com)

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.


The full copy of the GNU General Public License is available here: http://www.gnu.org/licenses/gpl.txt
 */

$cimy_hir_options = "cimy_hir_options";
$cimy_hir_options_descr = "Cimy Header Image Rotator options are stored here and modified only by admin";

/* Check to see if options already exist in the database */
$cimy_hir_curr_options = get_option($cimy_hir_options);

// invalidate v1.x.x options so new ones are created
if (isset($cimy_hir_curr_options['span_id']))
	$cimy_hir_curr_options = "";

/* Add options if they does not already exist */
if ($cimy_hir_curr_options == '') {
	$cimy_hir_curr_options['swap_rate'] = 3;
	$cimy_hir_curr_options['swap_type'] = "s";
	$cimy_hir_curr_options['div_id'] = 'cimy_div_id';
	$cimy_hir_curr_options['div_width'] = 400;
	$cimy_hir_curr_options['div_height'] = 200;
	$cimy_hir_curr_options['border'] = 1;
	$cimy_hir_curr_options['fade'] = 0;
	$cimy_hir_curr_options['shuffle'] = false;

	update_option($cimy_hir_options, $cimy_hir_curr_options);
}

// updating from pre v4.0.0
if (!isset($cimy_hir_curr_options["move_effect"])) {
	$cimy_hir_curr_options['move_effect'] = "none";
	$cimy_hir_curr_options['speed'] = 10;
	$cimy_hir_curr_options['file_link'] = "";

	$cimy_hir_curr_options['left'] = false;
	$cimy_hir_curr_options['right'] = false;
	$cimy_hir_curr_options['down'] = false;
	$cimy_hir_curr_options['up'] = false;

	$cimy_hir_curr_options['bottomright'] = false;
	$cimy_hir_curr_options['from_topleft_zoom'] = "1.0";
	$cimy_hir_curr_options['to_bottomright_zoom'] = "1.5";

	$cimy_hir_curr_options['topright'] = false;
	$cimy_hir_curr_options['from_bottomleft_zoom'] = "1.0";
	$cimy_hir_curr_options['to_topright_zoom'] = "1.5";

	$cimy_hir_curr_options['bottomleft'] = false;
	$cimy_hir_curr_options['from_topright_zoom'] = "1.0";
	$cimy_hir_curr_options['to_bottomleft_zoom'] = "1.5";

	$cimy_hir_curr_options['topleft'] = false;
	$cimy_hir_curr_options['from_bottomright_zoom'] = "1.0";
	$cimy_hir_curr_options['to_topleft_zoom'] = "1.5";

	update_option($cimy_hir_options, $cimy_hir_curr_options);
}

// updating from v4.0.0
if (!isset($cimy_hir_curr_options['file_text'])) {
	$cimy_hir_curr_options['file_text'] = "";
	update_option($cimy_hir_options, $cimy_hir_curr_options);
}

// pre 2.6 compatibility or if not defined
if (!defined("WP_CONTENT_URL"))
	define("WP_CONTENT_URL", get_option("siteurl")."/wp_content");
	
if (!defined("WP_CONTENT_DIR"))
	define("WP_CONTENT_DIR", ABSPATH."/wp_content");

$chir_plugin_path = plugin_basename(dirname(__FILE__))."/";

/* Define Constants and variables*/
if (is_multisite()) {
	global $blog_id;

	$chir_plugins_dir = __FILE__;
	if (!stristr($chir_plugins_dir, "mu-plugins") === false)
		$chir_plugins_dir = "mu-plugins";
	else
		$chir_plugins_dir = "plugins";
	$blog_path = $blog_id."/";
}
else {
	$blog_path = "";
	$chir_plugins_dir = "plugins";
}

define('IMAGE_FOLDER', 'Cimy_Header_Images/');
define('IMAGE_PATH', WP_CONTENT_DIR."/".IMAGE_FOLDER.$blog_path);
define('IMAGE_URI', WP_CONTENT_URL."/".IMAGE_FOLDER.$blog_path);
define('PLUGIN_URI', WP_CONTENT_URL.'/'.$chir_plugins_dir.'/'.$chir_plugin_path);

if ((!is_dir(WP_CONTENT_DIR.'/'.IMAGE_FOLDER)) && (is_writable(WP_CONTENT_DIR))) {
	if (defined("FS_CHMOD_DIR"))
		@mkdir(WP_CONTENT_DIR.'/'.IMAGE_FOLDER, FS_CHMOD_DIR);
	else
		@mkdir(WP_CONTENT_DIR.'/'.IMAGE_FOLDER, 0777);
}

if ((is_multisite()) && (!is_dir(IMAGE_PATH)) && (is_writable(WP_CONTENT_DIR.'/'.IMAGE_FOLDER))) {
	if (defined("FS_CHMOD_DIR"))
		@mkdir(IMAGE_PATH, FS_CHMOD_DIR);
	else
		@mkdir(IMAGE_PATH, 0777);
}

$cimy_hir_domain = 'cimy_hir';
$cimy_hir_i18n_is_setup = false;
cimy_hir_i18n_setup();

function cimy_hir_my_init() {
	wp_register_script("cimy_hir_cross-slide", PLUGIN_URI."js/jquery.cross-slide-minified.js", array("jquery"), false);
}

function cimy_hir_my_admin_init() {
	$role = & get_role('administrator');
	$role->add_cap('manage_cimy_image_rotator');

	wp_register_script("cimy_hir_upload_pic", PLUGIN_URI."js/upload_pic.js", false, false);
}

function cimy_hir_js_enqueue() {
	wp_enqueue_script("cimy_hir_cross-slide");
}

function cimy_hir_admin_js_enqueue() {
	wp_enqueue_script("cimy_hir_upload_pic");
}

/* Add actions */
add_action('admin_menu', 'rotator_admin_menu');
add_action('wp_head', 'hir_add_css');
add_action('init', 'cimy_hir_my_init');
add_action('admin_init', 'cimy_hir_my_admin_init');
add_action('wp_enqueue_scripts', 'cimy_hir_js_enqueue');

function cimy_hir_i18n_setup() {
	global $cimy_hir_domain, $cimy_hir_i18n_is_setup, $chir_plugin_path, $chir_plugins_dir;

	if ($cimy_hir_i18n_is_setup)
		return;

	load_plugin_textdomain($cimy_hir_domain, false, '../'.$chir_plugins_dir.'/'.$chir_plugin_path.'langs');
}

function rotator_admin_menu() {
	$page = add_submenu_page('options-general.php', 'Cimy Header Image Rotator', 'Cimy Header Image Rotator', 'manage_cimy_image_rotator', 'cimy_header_image_rotator', 'rotator_options');
	add_action('admin_print_scripts-'.$page, 'cimy_hir_admin_js_enqueue');
	add_action('admin_print_scripts-'.$page, 'hir_add_js');
}

function get_files($path, $type) {
	$files = glob($path.$type);

	if ($files === FALSE)
		return array();

	$i = 0;
	$tot_files = count($files);
	while ($i < $tot_files) {
		if (!is_file($files[$i]))
			unset($files[$i]);
		$i++;
	}

	return array_values($files);
}

function get_image_key($img_array) {
	global $cimy_hir_curr_options;

	$type = $cimy_hir_curr_options["swap_type"];
	$rate = $cimy_hir_curr_options["swap_rate"];

	if ($img_array === false)
		return false;

	$count = count($img_array);

	if (empty($img_array))
		return false;

	$date = date($type);

	$swap_types = array("s", "i", "G", "d", "W", "n");
	$swap_types_keys = array_keys($swap_types, $type);
	$type_key = $swap_types_keys[0];
	$type_key++;
	$offset = date($swap_types[$type_key]);

	// divide per actual time per $rate so you change every $rate then % # images
	// $offset is needed to not depend on the custom user input and so loads all images
	$key = ($offset + intval($date / $rate)) % $count;

	return $key;
}

function switch_image($img_array) {
	return IMAGE_URI.basename($img_array[get_image_key($img_array)]);
}

function header_img_check() {
	global $cimy_hir_curr_options;

	if ($cimy_hir_curr_options["div_id"] != "") {
		echo PLUGIN_URI.'img/ok.gif';
	} else {
		echo PLUGIN_URI.'img/error.gif';
	}
}

function image_path_check($image_array) {
	$pics = count($image_array);

	if ((!($image_array === false)) && ($pics != 0)) {
		echo PLUGIN_URI.'img/ok.gif';
	} else {
		echo PLUGIN_URI.'img/error.gif';
	}
}

function image_path_message($image_array) {
	global $cimy_hir_domain;

	//$dir = is_dir(IMAGE_PATH);
	$pics = count($image_array);

	if ($image_array === false) {
		echo "<font color=\"red\">".__("The plugin could not find or open the above image directory. Please make sure you have created a folder called 'Cimy_Header_Images' (without the quote marks) in your wp-content folder.", $cimy_hir_domain)."</font>";
	} else if ($pics == 0) {
		echo "<font color=\"red\">".__("There are no pictures in your image folder. Please add some images for the plugin to work.", $cimy_hir_domain)."</font>";
	}
}

function hir_add_css() {
	global $cimy_hir_curr_options;

	$img_array = get_files(IMAGE_PATH, "*");

	if ($img_array === false)
		return;

	$tot_images = count($img_array);

	if (empty($img_array))
		return;

	$div_id = esc_js($cimy_hir_curr_options['div_id']);
	$swap_type = $cimy_hir_curr_options['swap_type'];
	$swap_rate = $cimy_hir_curr_options['swap_rate'];

	switch ($swap_type) {
		// seconds
		case "s":
			$seconds = $swap_rate;
			break;
		// minutes
		case "i":
			$seconds = 60*$swap_rate;
			break;
		// hours
		case "G":
			$seconds = 60*60*$swap_rate;
			break;
		// days
		case "d":
			$seconds = 60*60*24*$swap_rate;
			break;
		// weeks
		case "W":
			$seconds = 60*60*24*7*$swap_rate;
			break;
		// months
		case "n":
			$seconds = 60*60*24*7*30*$swap_rate;
			break;
	}

	$speed = $cimy_hir_curr_options["speed"];
	$img_key = get_image_key($img_array);
	$move_options = array();
	if ($cimy_hir_curr_options["move_effect"] == "slide") {
		if ($cimy_hir_curr_options["down"])
			$move_options[] = ", dir: 'down'";

		if ($cimy_hir_curr_options["up"])
			$move_options[] = ", dir: 'up'";

		if ($cimy_hir_curr_options["left"])
			$move_options[] = ", dir: 'left'";

		if ($cimy_hir_curr_options["right"])
			$move_options[] = ", dir: 'right'";
	}
	else if ($cimy_hir_curr_options["move_effect"] == "kenburns") {
		if ($cimy_hir_curr_options["bottomright"]) {
			$str = ", from: 'top left ".$cimy_hir_curr_options["from_topleft_zoom"]."x'";
			$str.= ", to: 'bottom right ".$cimy_hir_curr_options["to_bottomright_zoom"]."x'";
			$move_options[] = $str;
		}

		if ($cimy_hir_curr_options["topright"]) {
			$str = ", from: 'bottom left ".$cimy_hir_curr_options["from_bottomleft_zoom"]."x'";
			$str.= ", to: 'top right ".$cimy_hir_curr_options["to_topright_zoom"]."x'";
			$move_options[] = $str;
		}

		if ($cimy_hir_curr_options["bottomleft"]) {
			$str = ", from: 'top right ".$cimy_hir_curr_options["from_topright_zoom"]."x'";
			$str.= ", to: 'bottom left ".$cimy_hir_curr_options["to_bottomleft_zoom"]."x'";
			$move_options[] = $str;
		}

		if ($cimy_hir_curr_options["topleft"]) {
			$str = ", from: 'bottom right ".$cimy_hir_curr_options["from_bottomright_zoom"]."x'";
			$str.= ", to: 'top left ".$cimy_hir_curr_options["to_topleft_zoom"]."x'";
			$move_options[] = $str;
		}
	}

	echo "
<script type=\"text/javascript\" language=\"javascript\">
jQuery(document).ready(function($) {
  $(function() {
   var myid = $('#$div_id');
   if (myid[0]) {
    $('#$div_id').crossSlide({\n";
	if ($cimy_hir_curr_options['move_effect'] == "none")
		echo "      sleep: $seconds,\n";
	else if ($cimy_hir_curr_options['move_effect'] == "slide")
		echo "      speed: $speed,\n";
	echo "      fade: ".$cimy_hir_curr_options['fade'];
	echo $cimy_hir_curr_options['shuffle'] ? ",\n      shuffle: true" : "";
	echo "\n    }, [";
	// super-silly Internet Explorer can't handle comma at the end of last parameter
	$flag = false;
	$i = 0;
	$has_captions = false;

	while ($i != $tot_images) {
		if ($flag)
			echo ",";
		else
			$flag = true;

		$filename = basename($img_array[$img_key]);
		echo "\n\t{ src: '".esc_js(IMAGE_URI.$filename)."'";

		if (($cimy_hir_curr_options['move_effect'] == "slide") && (!empty($move_options)))
			echo $move_options[$i % count($move_options)];

		if ($cimy_hir_curr_options['move_effect'] == "kenburns") {
			echo $move_options[$i % count($move_options)];
			echo ", time: $seconds";
		}

		if (!empty($cimy_hir_curr_options[$filename]["link"]))
			echo "\n\t, href: '".esc_js($cimy_hir_curr_options[$filename]["link"])."', target: '_blank'";
		else if (!empty($cimy_hir_curr_options["file_link"]))
			echo "\n\t, href: '".esc_js($cimy_hir_curr_options["file_link"])."', target: '_blank'";

		if (!empty($cimy_hir_curr_options[$filename]["text"])) {
			echo "\n\t, alt: '".esc_js($cimy_hir_curr_options[$filename]["text"])."'";
			$has_captions = true;
		}
		else if (!empty($cimy_hir_curr_options["file_text"])) {
			echo "\n\t, alt: '".esc_js($cimy_hir_curr_options["file_text"])."'";
			$has_captions = true;
		}

		echo "}";
		$img_key = ($img_key + 1) % $tot_images;
		$i++;
	}
	echo "
       ]";
	if ($has_captions)
		echo ", function(idx, img, idxOut, imgOut) {
		var caption = $('div.".$div_id."_caption');
		caption.show().css({ opacity: 0 })
		if (idxOut == undefined)
		{
			// starting single image phase, put up caption
			caption.text(img.alt).animate({ opacity: .7 })
		}
		else
		{
			// starting cross-fade phase, take out caption
			caption.hide()
		}
		var img_alt = $(img).attr('alt');
		if (img_alt === undefined || img_alt === false || img_alt == '')
			caption.hide()
	}";
	echo ");
   }
  });
});
</script>";
}

function hir_add_js() {
	print <<<EOT
	<script type="text/javascript">
		<!--
		function open_win(url, name, args) {
			newWindow = window.open(url, name, args);
			newWindow.screenX = window.screenX;
			newWindow.screenY = window.screenY;
			newWindow.focus();
		}
		//-->
	</script>
EOT;
}

function cimy_hir_manage_upload($input_name, $rules) {
	$file_name = $_FILES[$input_name]['name'];

	// protect from site traversing
	$file_name = str_replace('../', '', $file_name);
	$file_name = str_replace('/', '', $file_name);

	// if there is no file to upload
	//	or dest dir is not writable
	// then everything else is useless
	if ((!isset($_FILES[$input_name]['name'])) || (!is_writable(IMAGE_PATH)))
		return "";
	
	// picture filesystem path
	$file_full_path = IMAGE_PATH.$file_name;
	
	// filesize in Byte transformed in KiloByte
	$file_size = $_FILES[$input_name]['size'] / 1024;
	$file_type = $_FILES[$input_name]['type'];
	$file_tmp_name = $_FILES[$input_name]['tmp_name'];
	$file_error = $_FILES[$input_name]['error'];

	// CHECK IF IT IS A REAL PICTURE
	if (stristr($file_type, "image/") === false)
		$file_error = 1;

	// CHECK THAT FILE NAME WILL NOT SCREW THE PLUG-IN
	if ($file_name != esc_js($file_name))
		$file_error = 2;
	
	// MIN LENGTH
	if (isset($rules['min_length']))
		if ($file_size < (intval($rules['min_length'])))
			$file_error = 1;
	
	// EXACT LENGTH
	if (isset($rules['exact_length']))
		if ($file_size != (intval($rules['exact_length'])))
			$file_error = 1;

	// MAX LENGTH
	if (isset($rules['max_length']))
		if ($file_size > (intval($rules['max_length'])))
			$file_error = 1;

	// if there are no errors and filename is empty
	if (($file_error == 0) && (!empty($file_name))) {
		if (move_uploaded_file($file_tmp_name, $file_full_path)) {
			// change file permissions for broken servers
			if (defined("FS_CHMOD_FILE"))
				@chmod($file_full_path, FS_CHMOD_FILE);
			else
				@chmod($file_full_path, 0644);
		}
	}

	return $file_error;
}

function rotator_options() {
	global $cimy_hir_options, $cimy_hir_curr_options, $cimy_hir_domain;

	if (!current_user_can("manage_cimy_image_rotator"))
		return;

	$upload_res = 0;
	if (isset($_POST["upload"])) {
		$upload_res = cimy_hir_manage_upload("userfile", false);
	}

	if (isset($_POST["cimy_hir_post"]))
	{
		$cimy_hir_curr_options['move_effect'] = $_POST['hir_move_effect'];

		$cimy_hir_curr_options['from_topleft_zoom'] = floatval($_POST['hir_from_topleft_zoom']);
		$cimy_hir_curr_options['to_topleft_zoom'] = floatval($_POST['hir_to_topleft_zoom']);

		$cimy_hir_curr_options['from_bottomleft_zoom'] = floatval($_POST['hir_from_bottomleft_zoom']);
		$cimy_hir_curr_options['to_bottomleft_zoom'] = floatval($_POST['hir_to_bottomleft_zoom']);



		$cimy_hir_curr_options['from_topright_zoom'] = floatval($_POST['hir_from_topright_zoom']);
		$cimy_hir_curr_options['to_topright_zoom'] = floatval($_POST['hir_to_topright_zoom']);


		$cimy_hir_curr_options['from_bottomright_zoom'] = floatval($_POST['hir_from_bottomright_zoom']);
		$cimy_hir_curr_options['to_bottomright_zoom'] = floatval($_POST['hir_to_bottomright_zoom']);

		$cimy_hir_curr_options['speed'] = intval($_POST['hir_speed']);


		if (isset($_POST['hir_down']))
			$cimy_hir_curr_options['down'] = true;
		else
			$cimy_hir_curr_options['down'] = false;

		if (isset($_POST['hir_up']))
			$cimy_hir_curr_options['up'] = true;
		else
			$cimy_hir_curr_options['up'] = false;

		if (isset($_POST['hir_left']))
			$cimy_hir_curr_options['left'] = true;
		else
			$cimy_hir_curr_options['left'] = false;

		if (isset($_POST['hir_right']))
			$cimy_hir_curr_options['right'] = true;
		else
			$cimy_hir_curr_options['right'] = false;

		if (isset($_POST['hir_bottomright']))
			$cimy_hir_curr_options['bottomright'] = true;
		else
			$cimy_hir_curr_options['bottomright'] = false;

		if (isset($_POST['hir_topright']))
			$cimy_hir_curr_options['topright'] = true;
		else
			$cimy_hir_curr_options['topright'] = false;

		if (isset($_POST['hir_bottomleft']))
			$cimy_hir_curr_options['bottomleft'] = true;
		else
			$cimy_hir_curr_options['bottomleft'] = false;

		if (isset($_POST['hir_topleft']))
			$cimy_hir_curr_options['topleft'] = true;
		else
			$cimy_hir_curr_options['topleft'] = false;

		if (isset($_POST['hir_swap_rate'])) {
			$rate = intval($_POST["hir_swap_rate"]);

			if ($rate == 0)
				$rate = 1;

			$cimy_hir_curr_options['swap_rate'] = $rate;
		}

		if (isset($_POST['hir_swap_type'])) {
			$cimy_hir_curr_options['swap_type'] = $_POST["hir_swap_type"];
		}

		if (isset($_POST['hir_div_id'])) {
			$cimy_hir_curr_options['div_id'] = stripslashes($_POST["hir_div_id"]);
		}

		if (isset($_POST['hir_div_width'])) {
			$value = intval($_POST["hir_div_width"]);
			if ($value < 0)
				$value = 0;
			$cimy_hir_curr_options['div_width'] = $value;
		}

		if (isset($_POST['hir_div_height'])) {
			$value = intval($_POST["hir_div_height"]);
			if ($value < 0)
				$value = 0;
			$cimy_hir_curr_options['div_height'] = $value;
		}

		if (isset($_POST['hir_border'])) {
			$value = intval($_POST["hir_border"]);
			if ($value < 0)
				$value = 0;
			$cimy_hir_curr_options['border'] = $value;
		}

		if (isset($_POST['hir_fade'])) {
			$value = intval($_POST["hir_fade"]);
			if ($value < 0)
				$value = 0;
			$cimy_hir_curr_options['fade'] = $value;
		}

		if (isset($_POST['hir_shuffle']))
			$cimy_hir_curr_options['shuffle'] = true;
		else
			$cimy_hir_curr_options['shuffle'] = false;

		if (isset($_POST['hir_file_link'])) {
			$value = stripslashes($_POST["hir_file_link"]);
			$cimy_hir_curr_options['file_link'] = $value;
		}

		if (isset($_POST['hir_file_text'])) {
			$value = stripslashes($_POST["hir_file_text"]);
			$cimy_hir_curr_options['file_text'] = $value;
		}

		update_option($cimy_hir_options, $cimy_hir_curr_options);
	}

	if (isset($_GET['to_del'])) {
		@unlink(IMAGE_PATH. $_GET['to_del']);
		unset($cimy_hir_curr_options[$_GET['to_del']]);
		update_option($cimy_hir_options, $cimy_hir_curr_options);
	}

	if (isset($_POST['filelinks'])) {
		foreach ($_POST['filelinks'] as $key=>$link)
			$cimy_hir_curr_options[$key]["link"] = stripslashes($link);

		foreach ($_POST['filetext'] as $key=>$text)
			$cimy_hir_curr_options[$key]["text"] = stripslashes($text);


		update_option($cimy_hir_options, $cimy_hir_curr_options);
	}

	$image_array = get_files(IMAGE_PATH, "*");
	$pics = count($image_array);
?>
<div class="wrap">
<?php
	if (function_exists("screen_icon"))
		screen_icon("options-general");
?>
<form method="post" id="cimy_hir_admin" name="cimy_hir_admin" action="">
<input type="hidden" name="cimy_hir_post" value="1" />
<h2>Cimy Header Image Rotator</h2>
<table border="0" cellspacing="2" cellpadding="0">
	<tr>
		<td width="20"><img src="<?php echo PLUGIN_URI.'img/ok.gif'; ?>" alt="" /></td>
		<td><b><?php _e("Swap rate:", $cimy_hir_domain); ?></b></td>
		<td><input type="text" name="hir_swap_rate" value="<?php echo $cimy_hir_curr_options['swap_rate']; ?>" size="6" />
		<select name="hir_swap_type">
			<option 
			<?php if ($cimy_hir_curr_options['swap_type'] == "s") { echo "selected=\"selected\""; } ?>
				value="s"><?php _e("Seconds", $cimy_hir_domain); ?></option>
			<option 
			<?php if ($cimy_hir_curr_options['swap_type'] == "i") { echo "selected=\"selected\""; } ?>
				value="i"><?php _e("Minutes", $cimy_hir_domain); ?></option>
			<option 
			<?php if ($cimy_hir_curr_options['swap_type'] == "G") { echo "selected=\"selected\""; } ?>
				value="G"><?php _e("Hours", $cimy_hir_domain); ?></option>
			<option 
			<?php if ($cimy_hir_curr_options['swap_type'] == "d") { echo "selected=\"selected\""; } ?>
				value="d"><?php _e("Days", $cimy_hir_domain); ?></option>
			<option 
			<?php if ($cimy_hir_curr_options['swap_type'] == "W") { echo "selected=\"selected\""; } ?>
				value="W"><?php _e("Weeks", $cimy_hir_domain); ?></option>
			<option 
			<?php if ($cimy_hir_curr_options['swap_type'] == "n") { echo "selected=\"selected\""; } ?>
				value="n"><?php _e("Months", $cimy_hir_domain); ?></option>
		</select></td>
	</tr>
</table>
<table border="0" cellspacing="2" cellpadding="0">
	<tr>
		<td width="20"></td>
		<td><?php _e("This setting determines the interval at which your header images rotate.", $cimy_hir_domain); ?></td>
	</tr>
</table>
<p></p>
<table border="0" cellspacing="2" cellpadding="0">
	<tr>
		<td width="20"><img src="<?php header_img_check(); ?>" alt="" /></td>
		<td><b><?php _e("DIV ID:", $cimy_hir_domain); ?></b></td>
		<td><input type="text" name="hir_div_id" size="24" value="<?php echo esc_attr($cimy_hir_curr_options['div_id']); ?>" /></td>
		<td><b><?php _e("Border:", $cimy_hir_domain); ?></b></td>
		<td><input type="text" name="hir_border" size="24" value="<?php echo $cimy_hir_curr_options['border']; ?>" /></td>
	</tr>
	<tr>
		<td></td>
		<td><b><?php _e("Width:", $cimy_hir_domain); ?></b></td>
		<td><input type="text" name="hir_div_width" size="24" value="<?php echo $cimy_hir_curr_options['div_width']; ?>" /></td>
		<td><b><?php _e("Height:", $cimy_hir_domain); ?></b></td>
		<td><input type="text" name="hir_div_height" size="24" value="<?php echo $cimy_hir_curr_options['div_height']; ?>" /></td>
	</tr>
	<tr>
		<td></td>
		<td><b><?php _e("Fade effect:", $cimy_hir_domain); ?></b></td>
		<td><input type="text" name="hir_fade" size="24" value="<?php echo $cimy_hir_curr_options['fade']; ?>" /></td>
		<td colspan="2"><?php _e("smaller value means faster fade (0=disabled)", $cimy_hir_domain); ?></td>
	</tr>
	<tr><td><br /></td></tr>
	<tr>
		<td><input name="hir_move_effect" type="radio" value="none"<?php echo ($cimy_hir_curr_options['move_effect'] == "none") ? " checked=\"checked\"":""; ?> /></td>
		<td><b><?php _e("Static", $cimy_hir_domain); ?></b></td>
	</tr>
	<tr><td><br /></td></tr>
	<tr>
		<td><input name="hir_move_effect" type="radio" value="slide"<?php echo ($cimy_hir_curr_options['move_effect'] == "slide") ? " checked=\"checked\"":""; ?> /></td>
		<td colspan="5"><b><?php _e("Slide effect:", $cimy_hir_domain); ?></b></td>
	</tr>
	<tr>
		<td></td>
		<td colspan="5"><?php _e("Note that swap rate is ignored in this case and if you get no images then you have to reduce the size of the div and/or lower the speed value", $cimy_hir_domain); ?></td>
	</tr>
	<tr>
		<td></td>
		<td><b><?php _e("Speed:", $cimy_hir_domain); ?></b></td>
		<td><input type="text" name="hir_speed" size="24" value="<?php echo $cimy_hir_curr_options['speed']; ?>" /></td>
		<td colspan="2"><?php _e("pixels/second", $cimy_hir_domain); ?></td>
	</tr>
	<tr>
		<td></td>
		<td colspan="3"><input type="checkbox" name="hir_down" size="24" value="1"<?php echo $cimy_hir_curr_options['down'] ? " checked=\"checked\"":""; ?> /> <?php _e("From UP to DOWN", $cimy_hir_domain); ?></td>
	</tr>
	<tr>
		<td></td>
		<td colspan="3"><input type="checkbox" name="hir_up" size="24" value="1"<?php echo $cimy_hir_curr_options['up'] ? " checked=\"checked\"":""; ?> /> <?php _e("From DOWN to UP", $cimy_hir_domain); ?></td>
	</tr>

	<tr>
		<td></td>
		<td colspan="3"><input type="checkbox" name="hir_right" size="24" value="1"<?php echo $cimy_hir_curr_options['right'] ? " checked=\"checked\"":""; ?> /> <?php _e("From LEFT to RIGHT", $cimy_hir_domain); ?></td>
	</tr>

	<tr>
		<td></td>
		<td colspan="3"><input type="checkbox" name="hir_left" size="24" value="1"<?php echo $cimy_hir_curr_options['left'] ? " checked=\"checked\"":""; ?> /> <?php _e("From RIGHT to LEFT", $cimy_hir_domain); ?></td>
	</tr>
	<tr><td><br /></td></tr>
	<tr>
		<td><input name="hir_move_effect" type="radio" value="kenburns"<?php echo ($cimy_hir_curr_options['move_effect'] == "kenburns") ? " checked=\"checked\"":""; ?> /></td>
		<td><b><?php _e("Ken Burns effect:", $cimy_hir_domain); ?></b></td>
	</tr>
	<tr>
		<td></td>
		<td><input type="checkbox" name="hir_bottomright" size="24" value="1"<?php echo $cimy_hir_curr_options['bottomright'] ? " checked=\"checked\"":""; ?> /> <?php _e("From TOP LEFT", $cimy_hir_domain); ?></td>
		<td><b><?php _e("Zoom:", $cimy_hir_domain); ?></b> <input type="text" name="hir_from_topleft_zoom" value="<?php echo $cimy_hir_curr_options['from_topleft_zoom']; ?>" size="3" />x</td>
		<td><?php _e("to BOTTOM RIGHT", $cimy_hir_domain); ?></td>
		<td><b><?php _e("Zoom:", $cimy_hir_domain); ?></b> <input type="text" name="hir_to_bottomright_zoom" value="<?php echo $cimy_hir_curr_options['to_bottomright_zoom']; ?>" size="3" />x</td>
	</tr>
	<tr>
		<td></td>
		<td><input type="checkbox" name="hir_topleft" size="24" value="1"<?php echo $cimy_hir_curr_options['topleft'] ? " checked=\"checked\"":""; ?> /> <?php _e("From BOTTOM RIGHT", $cimy_hir_domain); ?></td>
		<td><b><?php _e("Zoom:", $cimy_hir_domain); ?></b> <input type="text" name="hir_from_bottomright_zoom" value="<?php echo $cimy_hir_curr_options['from_bottomright_zoom']; ?>" size="3" />x</td>
		<td><?php _e("to TOP LEFT", $cimy_hir_domain); ?></td>
		<td><b><?php _e("Zoom:", $cimy_hir_domain); ?></b> <input type="text" name="hir_to_topleft_zoom" value="<?php echo $cimy_hir_curr_options['to_topleft_zoom']; ?>" size="3" />x</td>
	</tr>
	<tr>
		<td></td>
		<td><input type="checkbox" name="hir_bottomleft" size="24" value="1"<?php echo $cimy_hir_curr_options['bottomleft'] ? " checked=\"checked\"":""; ?> /> <?php _e("From TOP RIGHT", $cimy_hir_domain); ?></td>
		<td><b><?php _e("Zoom:", $cimy_hir_domain); ?></b> <input type="text" name="hir_from_topright_zoom" value="<?php echo $cimy_hir_curr_options['from_topright_zoom']; ?>" size="3" />x</td>
		<td><?php _e("to BOTTOM LEFT", $cimy_hir_domain); ?></td>
		<td><b><?php _e("Zoom:", $cimy_hir_domain); ?></b> <input type="text" name="hir_to_bottomleft_zoom" value="<?php echo $cimy_hir_curr_options['to_bottomleft_zoom']; ?>" size="3" />x</td>
	</tr>
	<tr>
		<td></td>
		<td><input type="checkbox" name="hir_topright" size="24" value="1"<?php echo $cimy_hir_curr_options['topright'] ? " checked=\"checked\"":""; ?> /> <?php _e("From BOTTOM LEFT", $cimy_hir_domain); ?></td>
		<td><b><?php _e("Zoom:", $cimy_hir_domain); ?></b> <input type="text" name="hir_from_bottomleft_zoom" value="<?php echo $cimy_hir_curr_options['from_bottomleft_zoom']; ?>" size="3" />x</td>
		<td><?php _e("to TOP RIGHT", $cimy_hir_domain); ?></td>
		<td><b><?php _e("Zoom:", $cimy_hir_domain); ?></b> <input type="text" name="hir_to_topright_zoom" value="<?php echo $cimy_hir_curr_options['to_topright_zoom']; ?>" size="3" />x</td>
	</tr>
	<tr><td><br /></td></tr>
	<tr>
		<td></td>
		<td colspan="2"><input type="checkbox" name="hir_shuffle" size="24" value="1"<?php echo $cimy_hir_curr_options['shuffle'] ? " checked=\"checked\"":""; ?> /> <?php _e("Shuffle images", $cimy_hir_domain); ?></td>
	</tr>
	<tr>
		<td></td>
		<td><b><?php _e("Images link:", $cimy_hir_domain); ?></b></td>
		<td colspan="3"><input type="text" name="hir_file_link" size="79" value="<?php echo esc_attr($cimy_hir_curr_options['file_link']); ?>" /></td>
	</tr>
	<tr>
		<td></td>
		<td><b><?php _e("Images caption:", $cimy_hir_domain); ?></b></td>
		<td colspan="3"><input type="text" name="hir_file_text" size="79" value="<?php echo esc_attr($cimy_hir_curr_options['file_text']); ?>" /></td>
	</tr>
</table>
<p><input class="button-primary" type="submit" name="submitButtonName" value="<?php _e('Save Changes') ?>" /></p>
</form>
<?php
if (isset($image_array[0]))
	if (is_file($image_array[0]))
		$filename = IMAGE_URI.basename($image_array[0]);
?>
<table border="0" cellspacing="2" cellpadding="0">
	<tr>
		<td width="20"></td>
		<td width="700">
		<?php
			echo "<br />"."<strong>".__("Copy following code in your theme where you want image rotation:", $cimy_hir_domain)."</strong><br />";
			echo "<p><textarea rows=\"15\" cols=\"200\" class=\"large-text readonly\" name=\"rules\" id=\"rules\" readonly=\"readonly\">";
			echo esc_attr("<div id=\"".$cimy_hir_curr_options['div_id']."\">".__("Loading images...", $cimy_hir_domain)."</div>")."\n";
			echo esc_attr("<div class=\"".$cimy_hir_curr_options['div_id']."_caption\"></div>\n");
			echo esc_attr("<style type=\"text/css\">\n");
			echo esc_attr("\t#".$cimy_hir_curr_options['div_id']." {\n");
			echo esc_attr("\t\tfloat: left;\n");
			echo esc_attr("\t\tmargin: 1em auto;\n");
			echo esc_attr("\t\tborder: ".$cimy_hir_curr_options['border']."px solid #000000;\n");
			echo esc_attr("\t\twidth: ".$cimy_hir_curr_options['div_width']."px;\n");
			echo esc_attr("\t\theight: ".$cimy_hir_curr_options['div_height']."px;\n");
			echo esc_attr("\t}\n");
			echo esc_attr("\tdiv.".$cimy_hir_curr_options['div_id']."_caption {\n");
			echo esc_attr("\t\tposition: absolute;\n");
			echo esc_attr("\t\tmargin-top: 175px;\n");
			echo esc_attr("\t\tmargin-left: -75px;\n");
			echo esc_attr("\t\twidth: 150px;\n");
			echo esc_attr("\t\ttext-align: center;\n");
			echo esc_attr("\t\tleft: 50%;\n");
			echo esc_attr("\t\tpadding: 5px 10px;\n");
			echo esc_attr("\t\tbackground: black;\n");
			echo esc_attr("\t\tcolor: white;\n");
			echo esc_attr("\t\tfont-family: sans-serif;\n");
			echo esc_attr("\t\tborder-radius: 10px;\n");
			echo esc_attr("\t\tdisplay: none;\n");
			echo esc_attr("\t\tz-index: 2;\n");
			echo esc_attr("\t}\n");
			echo esc_attr("</style>\n");
			echo esc_attr("<noscript>")."\n";
			echo "\t".esc_attr("<div id=\"".$cimy_hir_curr_options['div_id']."_nojs\">")."\n";
			echo "\t\t".esc_attr("<img id=\"cimy_img_id\" src=\"".$filename."\" alt=\"\" />")."\n";
			echo "\t".esc_attr("</div>")."\n";
			echo esc_attr("</noscript>");
			echo "</textarea></p>";
			echo "<br /><br />";

			_e("<strong>Note 1:</strong> image rotation will be available only for users with JavaScript enabled on their browsers", $cimy_hir_domain);
// 			echo "<br />";
// 			_e("<strong>Note 2:</strong> you can easily avoid real-time rotation just passing <strong>false</strong> to the JavaScript call", $cimy_hir_domain);
		?>
		</td>
	</tr>
</table>
<p></p>
<table border="0" cellspacing="2" cellpadding="0">
	<tr>
		<td width="20"><img src="<?php image_path_check($image_array); ?>" alt="" /></td>
		<td><b><?php _e("Images path:", $cimy_hir_domain); ?>&nbsp;</b></td>
		<td><?php echo IMAGE_PATH; ?></td>
	</tr>
</table>
<table border="0" cellspacing="2" cellpadding="0">
	<tr>
		<td width="20"></td>
		<td><?php image_path_message($image_array); ?></td>
	</tr>
</table>
<p></p>
<h2><?php _e("Images"); ?></h2>
<form method="post" id="cimy_hir_upload" name="cimy_hir_upload" action="#cimy_hir_upload">
<table border="0" cellspacing="2" cellpadding="0">
	<tr>
		<td><input id="userfile" name="userfile" size="45" type="file" onchange="uploadPic('cimy_hir_upload', 'userfile', '<?php _e("Please upload an image with one of the following extensions", $cimy_hir_domain); ?>');" /></td>
		<td><input class="button" name="upload" type="submit" value="<?php _e("Upload image", $cimy_hir_domain); ?>" /></td>
	</tr>
	<?php if (isset($_POST["upload"])) {
		if ($upload_res == 1)
			echo '<tr><td colspan="2"><font color="red">'.__("There have been some problems uploading the image", $cimy_hir_domain).'</font></td></tr>';
		else if ($upload_res == 2)
			echo '<tr><td colspan="2"><font color="red">'.__("The file name contains some illegal characters, please rename it and upload it again", $cimy_hir_domain).'</font></td></tr>';
	} ?>
	<tr>
		<td colspan="2"><?php
			if (!is_writable(IMAGE_PATH)) {
				echo '<img src="'.PLUGIN_URI.'img/error.gif'.'" alt="" />&nbsp;';
				echo "<font color=\"red\">".__("Web server cannot write on images directory, check permissions", $cimy_hir_domain)."</font>";
			}
			else {
				echo '<img src="'.PLUGIN_URI.'img/ok.gif'.'" alt="" />&nbsp;';
				_e("Web server can write on images directory", $cimy_hir_domain);
			}
		?></td>
		<td></td>
	</tr>
</table>
</form>
<br />
<form method="post" id="cimy_hir_file_options" name="cimy_hir_file_options" action="#cimy_hir_file_options">
<table border="0" cellspacing="2" cellpadding="0">
<tr>
<td>
<?php

if ($image_array === false) {
	echo "<font color=\"red\">".__("Cannot open images directory. Please make sure that you create it and web server has read permission over it.", $cimy_hir_domain)."</font>";
} else {
	if ($pics == 0) {
		echo "<font color=\"red\">".__("Could not find any pictures.", $cimy_hir_domain)."</font>";
	}
	else {
		_e("Click on pictures to view...", $cimy_hir_domain);
		?>
		<table class="widefat" cellpadding="10">
		<?php
		$thead_tfoot = '<tr>
			<th style="text-align: center;"><h3>'.__("Delete").'</h3></th>
			<th style="text-align: center;"><h3>'.__("File name", $cimy_hir_domain).'</h3></th>
			<th style="text-align: center;"><h3>'.__("Link").'</h3></th>
			<th style="text-align: center;"><h3>'.__("Caption").'</h3></th>
		</tr>';
		?>
		<thead align="center">
			<?php echo $thead_tfoot; ?>
		</thead>
		<tfoot align="center">
			<?php echo $thead_tfoot; ?>
		</tfoot>
		<tbody>
		<?php
		$style = "";
		foreach ($image_array as $entry) {
			if (is_file($entry)) {
				$style = (' class="alternate"' == $style) ? '' : ' class="alternate"';
				$filename = basename($entry);
				echo '<tr'.$style.'>';
				echo '<td><a href="'.esc_attr($_SERVER["REQUEST_URI"]).'&amp;to_del='.esc_attr($filename).'" title="'.__("Delete file", $cimy_hir_domain).'" >[X]</a></td>';
				echo '<td><a href="javascript:void(0)" onclick="open_win(\''.esc_attr(IMAGE_URI.$filename).'\', \'header\', \'width=500, height=250, status=no, toolbar=no, menubar=no, scrollbars=yes, resizable=yes\')">'.esc_html($filename).'</a>';
				if ($filename != esc_js($filename))
					echo '<br /><font color="red">'.__("The file name contains some illegal characters, please rename it and upload it again", $cimy_hir_domain).'</font>';

				echo '</td>';
				echo "\n<td><input name=\"filelinks[".esc_attr($filename)."]\" type=\"text\" size=\"60\" value=\"".esc_attr($cimy_hir_curr_options[$filename]["link"])."\" /></td>\n";
				echo "\n<td><input name=\"filetext[".esc_attr($filename)."]\" type=\"text\" size=\"60\" value=\"".esc_attr($cimy_hir_curr_options[$filename]["text"])."\" /></td>\n";
				echo '</tr>';
			}
		}

		echo "</tbody></table>";
	}
}
?>
<p><input class="button-primary" type="submit" name="submitButtonName" value="<?php _e('Save Changes') ?>" /></p>
</td>
</tr>
</table>
</form>
</div>
<?php
}

?>
