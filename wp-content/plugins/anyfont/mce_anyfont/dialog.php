<?php
ini_set('display_errors', 0);
$path = realpath('./../../../../')."/";

if ( file_exists( $path.'wp-config.php') ) {
    /** The config file resides in ABSPATH */
    require_once( $path.'wp-config.php' );
} elseif ( file_exists( dirname($path) . '/wp-config.php' ) ) {
    /** The config file resides one level below ABSPATH */
    require_once( dirname($path) . '/wp-config.php' );
} else {
    header("HTTP/1.0 500 Server Error");
    exit(500);
}

define('WP_USE_THEMES', true);

$url = get_option('siteurl');


	$all_styles = anyfont_encode_json(unserialize(get_option("anyfont_styles")));

function anyfont_encode_json($array){
	if (function_exists('json_encode')) {
		return json_encode($array);
	} else {
		require_once(ANYFONT_ROOT.'/lib/class.json.php');
		$JSON = new serviceJSON();
		return $JSON->encode($array);
	}
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-US">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<script type="text/javascript" src="<?=$url?>/wp-includes/js/tinymce/tiny_mce.js?ver=3241-1141"></script>
<script type="text/javascript" src="<?=$url?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
<script type="text/javascript" src="<?=$url?>/wp-includes/js/prototype.js"></script>
<title><?php _e('Insert Text using AnyFont Styles', 'anyfont'); ?></title>

<base target="_self" />


<script type="text/javascript">

/* Source:http://cass-hacks.com/articles/code/js_url_encode_decode/ */
function URLEncode(clearString) {
    var output = '';
    var x = 0;
    clearString = clearString.toString();
    var regex = /(^[a-zA-Z0-9_.]*)/;
    while (x < clearString.length) {
        var match = regex.exec(clearString.substr(x));
        if (match != null && match.length > 1 && match[1] != '') {
            output += match[1];
        x += match[1].length;
        } else {
        if (clearString[x] == ' ')
            output += '+';
        else {
            var charCode = clearString.charCodeAt(x);
            var hexVal = charCode.toString(16);
            output += '%' + ( hexVal.length < 2 ? '0' : '' ) + hexVal.toUpperCase();
        }
        x++;
        }
    }
    return output;
}

function submitForm(e) {
	var randomid = 'anyfont_random_'+Math.floor(Math.random()*23144);
	var style_list = <?php echo $all_styles;?>;
    var style = document.getElementById('style').value;
    var txt = document.getElementById('text').value;
//     if(style == '' || txt == '')
//         return false;
	var style_array = style.split("__");
	var type = style_array[0];
	var style = style_array[1];
	if(type == "image"){
		var image = '<img src="<? echo $url; ?>/images/'+style+'/'+URLEncode(txt)+'.png" alt="'+txt+'" />';
		tinyMCE.execCommand('mceInsertContent', false, image);
    } else if(type == "css"){
		var css = 'font-family:\''+style_list.css[style]['font-family']+'\';'
		var span = '<span style="font-family:\''+style_list.css[style]['font-family']+'\';">'+txt+'</span>';
		tinyMCE.execCommand('mceInsertContent', false, span);
    }
    tinyMCEPopup.close();
    return true;
}

function cancelForm() {

    tinyMCEPopup.close();
    return true;
}

</script>
<style>
.panel_wrapper label{
    width:100px;
    float:left;
}
.panel_wrapper input{
    width:200px;
}
</style>
</head>
<body>
    <form onsubmit="submitForm(this);">
	<div class="panel_wrapper" style="border-top:1px solid #919B9C;">
<!--        <fieldset>
        <legend>General</legend>-->
        <p>Please select one of your <i>image styles</i> to insert.</p>
        <label for="Style">Choose a style:</label>
        <select name="style" id="style" class="mceRequired">
            <option value="admin">Select Style...</option>
        <?
		$styles = readStyles('image');
		echo "<option class=\"select_option_heading\" disabled=\"disabled\">Image Styles</option>";
		if(is_array($styles)){
			foreach($styles as $style => $option){
				if($style!=="Preview"){
					echo  "<option value=\"image__$style\">$style</option>";
				}
			}
		}
// 		$styles = readStyles('css');
// 		echo "<option class=\"select_option_heading\" disabled=\"disabled\">CSS Styles</option>" ;
// 		if(is_array($styles)){
// 			foreach($styles as $style => $option){
// 				if($style!=="Preview"){
// 					echo "<option value=\"css__$style\">$style</option>";
// 				}
// 			}
// 		}

        ?>
        </select>
        <br /><br />
        <label for="text">Text to insert:</label>
        <input type="text" name="text" id="text" class="mceFocus mceRequired" value="<?php echo stripslashes($_REQUEST['text']); ?>">
<!--         </fieldset> -->
        </div>
        <div class="mceActionPanel">
            <div style="float: left">
                <input type="button" id="cancel" name="cancel" value="Cancel" onclick="cancelForm();">
            </div>
            <div style="float: right">
                <input type="submit" id="insert" name="insert" value="Insert Text">
            </div>
        </div>
	</form>
</body>
</html>
<?php

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
?>