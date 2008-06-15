<?php
ini_set('display_errors', 0);
define('DOING_AJAX', true);
define('WP_ADMIN', true);

$root = dirname( __FILE__ );

$path = realpath('./../../../')."/";

if ( file_exists( $path . 'wp-config.php') ) {
	/** The config file resides in ABSPATH */
	require_once( $path . 'wp-config.php' );
} elseif ( file_exists( dirname($path) . '/wp-config.php' ) ) {
	/** The config file resides one level below ABSPATH */
	require_once( dirname($path) . '/wp-config.php' );
} else {
	header("HTTP/1.0 500 Server Error");
	_e("We could not find your <code>wp-config.php</code> file.");
	exit(0);
}

require_once(ABSPATH.'wp-admin/includes/admin.php');

if(!is_user_logged_in()){
	die('-1');
}

if(!current_user_can('upload_fonts')){
	anyfont_return_upload(array(
		"success"	=>	false,
		"failure"	=>	__("You are not allowed to upload fonts. Please contact your Administrator for assistance.", 'anyfont')
	));
}elseif($_FILES){
	list($name,$result) = anyfont_upload('font', ANYFONT_FONTDIR, 'ttf,otf');
	if($name){
		$details = stat(ANYFONT_FONTDIR."/$name");
		$name_array = explode(".tt", $name);
		$name_array[1] != "f" ? $name_array = explode(".otf", $name) : 0;
		$fontdetails = anyfont_get_font_info( ANYFONT_FONTDIR ."/$name");
		require_once(ANYFONT_LIBDIR."/".ANYFONT_LIB_VERSION."/class.tpl.php");
		require_once(ANYFONT_LIBDIR."/class.admin.php");
		$list = "";
		$tpl = new fastTPL(ANYFONT_ROOT."/tpl");
		$tpl->define(array("font_block" => "fonts-block.html","font_preview" => "font-preview.html"));
		$admn = new anyfontAdmin();
		if(!is_array($admn->fontlist) || count($admn->fontlist) <= 0){
			$admn->readFontDir();
		}
// 		anyfont_return_json($admn->fontlist);
		foreach($admn->fontlist as $displayname => $fontdetail){
			if($displayname == $fontdetails[1]){
				$fontblk = $admn->getFontBlock($tpl, $displayname, $fontdetail);
			}
		}
		$return = array(
					"success"   =>	true,
					"file_name" =>	$name,
					"fontlist"  =>	urlencode($fontblk)//urldecode($fontblk)
				);
// 		echo $list;
		anyfont_return_upload($return);

// 				"font_id"	=>	preg_replace("/\s+/", "", $fontdetails[1]),
// 				"font_name" =>	$fontdetails[1],
// 				"copyright" =>	$fontdetails[10],
// 				"styletype"	=>	$fontdetails[2],
// 				"img_url"	=>	get_option('siteurl')."/images/admin/".$name_array[0]."/".urlencode("The quick brown fox jumps over the lazy dog").".png",
// 				"img_char"  =>  ANYFONT_URL."/img/icon-charmap.png",
// 				"img_del"	=>	ANYFONT_URL."/img/icon-delete.png"
	} else {
		anyfont_return_upload(array(
			"success"	=>	false,
			"failure"	=>	$result
		));
	}
}else{
	anyfont_return_upload(array(
		"success"	=>	false,
		"failure"	=>	__("File Upload Error", 'anyfont')
	));
}

function anyfont_upload($file_id, $folder=false, $types=false) {
    if(!$_FILES[$file_id]['name']) return array('','No file specified');

    $file_name = str_replace("&", "and", $_FILES[$file_id]['name']);

	$ext_arr = split("\.",basename($file_name));
    $ext = strtolower($ext_arr[count($ext_arr)-1]);
	$file_name = $ext_arr[0];

    if($types) {
		$all_types = explode(",",strtolower($types));
        if(in_array($ext,$all_types));
        else {
            $result = "'".$_FILES[$file_id]['name']."' is not a valid file.";
            return array('',$result);
        }
    }
	if(!file_exists($folder)) {
		if(!wp_mkdir_p($folder)){
			$result = sprintf(__("The folder '%s' does not exist and could not be created, please check that the webserver has permissions to write to the wp-content folder.", 'anyfont'), $folder);
			return array("$file_name.$ext", $result);
		}
	} elseif(!is_writable($folder)) {
		$result = sprintf(__("The folder '%s' is not writable, please check that the webserver has permissions to write to the folder. Only as a last resort, try setting the folder permissions to 0777.", 'anyfont'), $folder);
		return array("$file_name.$ext", $result);
	}

	if($folder){
		$uploadfile = $folder."/$file_name.$ext";
	} else {
		$result = "Server Error.";
		return array("$file_name.$ext", $result);
	}

    $result = '';

	if (!move_uploaded_file($_FILES[$file_id]['tmp_name'], $uploadfile)) {
        $result = "Cannot upload the file '".$_FILES[$file_id]['name']."'"; //Show error if any.
         return array("$file_name.$ext",$result);
    } else {
        if(!$_FILES[$file_id]['size']) {
            @unlink($uploadfile);
            $file_name = '';
            $result = "Empty file - please upload a valid font.";
        } else {
            chmod($uploadfile,0777);
        }
    }

    return array("$file_name.$ext", $result);
}

function anyfont_return_upload($result){
	if (function_exists('json_encode')) {
		echo json_encode($result);
		exit(0);
	} else {
		require_once(ANYFONT_ROOT.'/lib/class.json.php');
		$JSON = new serviceJSON();
		echo $JSON->encode($result);
		exit(0);
	}
}


?>