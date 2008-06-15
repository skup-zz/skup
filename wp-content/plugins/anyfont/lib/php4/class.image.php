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

class ttfImage {

	var $text;
	var $style;
	var $etag;

	public function __construct($text, $style, $displaytext, $gzip){
		isset($text) ? $this->text = $text : 0;
		isset($style) ? $this->style = $style : 0;
		isset($displaytext) ? $this->displaytext = $displaytext : 0;
		isset($gzip) ? $this->gzip = $gzip : 0;
		if($this->style == "admin"){
			$this->fontsettings = array();
			$this->fontsettings['font-name'] = $this->text;
			$this->text = $this->displaytext;
			if($this->displaytext == "charactermap"){
				$this->text =  $this->charMap();
                $this->fontsettings["limit-width"] = true;
                $this->fontsettings["max-width"] = 960;
                $this->fontsettings['max-width-text-align'] = "center";
                $this->fontsettings["line-height"] = 40;
			}
			$this->fontsettings['color'] = "333333";
			$this->fontsettings['font-size'] = 18;
			$this->fontsettings['shadow'] = false;
			$this->fontsettings['background-color'] = "FFFFFF";
			$this->fontsettings['is_admin'] = true;
		} else if($this->style == "admin-small"){
			$this->fontsettings = array();
			$this->fontsettings['font-name'] = $this->text;
			$this->text = $this->displaytext;
			$this->fontsettings['color'] = "333333";
			$this->fontsettings['font-size'] = 16;
			$this->fontsettings['shadow'] = false;
			$this->fontsettings['background-color'] = "FFFFFF";
			$this->fontsettings['is_admin'] = true;
		}else {
			$styles = unserialize(get_option('anyfont_styles'));
			if(isset($styles['image'][$this->style])){
				$this->fontsettings = $styles['image'][$this->style];
				!isset($this->fontsettings['background-color']) ? $this->fontsettings['background-color'] = "FFFFFF" : 0;
				!isset($this->fontsettings['shadow-distance']) ? $this->fontsettings['shadow-distance'] = 6 : 0;
				!isset($this->fontsettings['image-padding']) ? $this->fontsettings['image-padding'] = 0 : 0;
				!isset($this->fontsettings['image-padding-top']) ? $this->fontsettings['image-padding-top'] = 0 : 0;
				!isset($this->fontsettings['image-padding-bottom']) ? $this->fontsettings['image-padding-bottom'] = 0 : 0;
				!isset($this->fontsettings['image-padding-left']) ? $this->fontsettings['image-padding-left'] = 0 : 0;
				!isset($this->fontsettings['image-padding-right']) ? $this->fontsettings['image-padding-right'] = 0 : 0;
				!isset($this->fontsettings['line-height']) ? $this->fontsettings['line-height'] = 0 : 0;
				!isset($this->fontsettings['max-width-text-align']) ? $this->fontsettings['max-width-text-align'] = !$this->fontsettings['text-align-center'] ? "left" : "center" : 0;
			} else {
				wp_redirect(get_option('siteurl'));
				exit(0);
			}
		}
		if(is_array($this->fontsettings)){
			$this->fonttype = !file_exists(ANYFONT_FONTDIR."/".$this->fontsettings['font-name'].".ttf") ? ".otf" : ".ttf";
			$pre_hash = "";
			foreach($this->fontsettings as $value){
				$pre_hash.=$value;
			}
			$hash = md5($pre_hash.$this->text."gd");
			$this->etag = $hash;
			if(!isset($this->fontsettings['is_admin'])){
				$this->cache_file = trailingslashit(ANYFONT_CACHE).$hash."-site.png";
			} else {
				$this->cache_file = trailingslashit(ANYFONT_CACHE).$hash."-admin.png";
			}
			$this->tmp_file = trailingslashit(ANYFONT_CACHE).$hash."_bg.png";
			if(file_exists($this->cache_file)){
				if(isset($_SERVER["HTTP_IF_NONE_MATCH"]) || isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])){
					if($_SERVER["HTTP_IF_NONE_MATCH"] == $this->etag || $_SERVER['HTTP_IF_MODIFIED_SINCE'] == gmdate('D, d M Y H:i:s', filemtime($this->cache_file)).' GMT'){
						header("HTTP/1.0 304 Not Modified");
						exit(304);
					} else {
						$this->text2Image();
					}
				} else {
					$this->fetchImage();
				}
			} else {
				$this->text2Image();
			}
		}else{
			wp_redirect(get_option('siteurl'));
		}
	}

	function text2Image(){

		$style = $this->style;

		$this->font = trailingslashit(ANYFONT_FONTDIR).$this->fontsettings['font-name'].$this->fonttype;
		$this->size = $this->fontsettings['font-size'];
		if($this->fontsettings["limit-width"]){
			$str = explode(" ", $this->text);
			$count = count($str);
			$n = 0;
			$l = 0;
			$lines = array();
			$max_width = (int)$this->fontsettings["max-width"];
			$max = $max_width;
			while($n < $count){
				!isset($lines[$l]) ? $lines[$l] = "" : 0;
				$test_str = $lines[$l] !== "" ? $lines[$l]." ".$str[$n] : $str[$n];
				$metrics =  $this->imageSize($test_str);
				if ($metrics['width'] < $max) {
					$lines[$l] != "" ? $lines[$l].=" ".$str[$n] : $lines[$l] = $str[$n];
					$n++;
				} else if($test_str !== $str[$n]){
					$max = $max_width;
					$l++;
				} else {
					$max+=10;
				}
			}
			$this->text = implode("\n", $lines);
		}
		$this->linecount = substr_count($this->text, "\n")+1;
		$metrics = $this->imageSize($this->text);

		$padding_x = !$this->fontsettings['image-padding'] ? 0 : (int)$this->fontsettings['image-padding-left']+(int)$this->fontsettings['image-padding-right'];
		$padding_y = !$this->fontsettings['image-padding'] ? 0 : (int)$this->fontsettings['image-padding-top']+(int)$this->fontsettings['image-padding-bottom'];
		$h_metrics = $this->imageSize($this->getChars(33, 255));
		$l_metrics = $this->imageSize(substr($this->text, 0, 1));
		$this->top = $metrics['top']+(!$this->fontsettings['image-padding'] ? 0 : ((int)$this->fontsettings['image-padding-top']));
		$this->left = $l_metrics['left']+(!$this->fontsettings['image-padding'] ? 0 : ((int)$this->fontsettings['image-padding-left']));
		$this->width = $metrics['width']+$padding_x;
		$this->height = $h_metrics['height']+$padding_y;

		if($this->fontsettings['shadow']){
			$this->width += $this->fontsettings['shadow-distance'];
			$this->height += $this->fontsettings['shadow-distance'];
		}
		if($this->linecount > 1){
			$this->fontsettings["line-height"] > 0 ? $this->height = $this->fontsettings["line-height"] < $this->height ? $this->height-(($this->height-$this->fontsettings["line-height"])/2) : $this->fontsettings["line-height"] : 0;
			$this->height*=$this->linecount;
		}
		if($this->fontsettings["limit-width"] &&  $this->fontsettings['max-width-text-align'] == "center"){
			$this->width = $this->fontsettings['max-width'];
		}

		$this->bg = @imagecreatetruecolor($this->width, $this->height);
		$background = $this->hex_to_rgb($this->fontsettings['background-color']);
		$this->bg_color = @imagecolorallocate($this->bg, $background[0], $background[1], $background[2]);
		imagefill($this->bg, 0, 0, $this->bg_color);
		@imagepng($this->bg, $this->tmp_file);

		$this->img = @imagecreatefrompng($this->tmp_file);

		$this->bg_color = imagecolorat($this->img, 1, 1);

		$color = $this->hex_to_rgb($this->fontsettings['color']);
		$this->font_color = @imagecolorallocate($this->img, $color[0], $color[1], $color[2]);

		if($this->fontsettings["shadow"]){
			if($this->linecount > 1 && $this->fontsettings["line-height"] > 0){
				$l = 0;
				$top = $this->top;
				$lines = explode("\n", $this->text);
				while($l < $this->linecount){
					if($this->fontsettings['max-width-text-align'] == "center"){
						$m = $this->imageSize($lines[$l]);
						$this->left = (($this->width-$m['width'])/2)+(!$this->fontsettings['image-padding'] ? 0 : ((int)$this->fontsettings['image-padding-left']));
					}
					$l > 0 ? $top += $this->fontsettings['line-height'] : 0;
					$this->shadowImage($lines[$l], $top);
					$l++;
				}
			}else{
				if($this->fontsettings['max-width-text-align'] == "center"){
					$m = $this->imageSize($this->text);
					$this->left = (($this->width-$m['width'])/2)+(!$this->fontsettings['image-padding'] ? 0 : ((int)$this->fontsettings['image-padding-left']));;
				}
				$this->shadowImage($this->text, $this->top);
			}
		}

		if($this->linecount > 1 && $this->fontsettings['line-height'] > 0){
			$l = 0;
			$lines = explode("\n", $this->text);
			while($l < $this->linecount){
				if($this->fontsettings['max-width-text-align'] == "center"){
					$m = $this->imageSize($lines[$l]);
					$this->left = (($this->width-$m['width'])/2)+(!$this->fontsettings['image-padding'] ? 0 : ((int)$this->fontsettings['image-padding-left']));;
				}
				$l > 0 ? $this->top += $this->fontsettings["line-height"] : 0;
				imagettftext($this->img, $this->size, 0, $this->left, $this->top, $this->font_color, $this->font, $lines[$l]);
				$l++;
			}
		}else{
			if($this->fontsettings['max-width-text-align'] == "center"){
				$m = $this->imageSize($this->text);
				$this->left = (($this->width-$m['width'])/2)+(!$this->fontsettings['image-padding'] ? 0 : ((int)$this->fontsettings['image-padding-left']));;
			}
			imagettftext($this->img, $this->size, 0, $this->left, $this->top, $this->font_color, $this->font, $this->text);
		}
		$this->fbg_color = @imagecolorallocate($this->img, $background[0], $background[1], $background[2]);
		imagecolortransparent($this->img, $this->fbg_color);
		@imagepng($this->img, $this->cache_file);
		imagedestroy($this->bg);
		imagedestroy($this->img);
		unlink($this->tmp_file);
		$this->fetchImage();
	}

	function imageSize($text){
		$this->box = imagettfbbox($this->size, 0, $this->font, $text);

		$min_x = min(array($this->box[0], $this->box[2], $this->box[4], $this->box[6]));
		$max_x = max(array($this->box[0], $this->box[2], $this->box[4], $this->box[6]));
		$min_y = min(array($this->box[1], $this->box[3], $this->box[5], $this->box[7]));
		$max_y = max(array($this->box[1], $this->box[3], $this->box[5], $this->box[7]));

		return array(
			"left" => abs($min_x),
			"top" => abs($min_y),
			"width" => ($max_x - $min_x),
			"height" => ($max_y + abs($min_y))
		);
	}

	function shadowImage($text, $top){
		$shadow = @imagecreatefrompng($this->tmp_file);
		$shadow_rgb = $this->hex_to_rgb($this->fontsettings['shadow-color']);
		$shadow_color = @imagecolorallocate($shadow, $shadow_rgb[0], $shadow_rgb[1], $shadow_rgb[2]);
		imagettftext($shadow, $this->size, 0, $this->left+$this->fontsettings['shadow-distance'], $top+$this->fontsettings['shadow-distance'], $shadow_color, $this->font, $text);
		$this->fontsettings['soften-shadow'] ? $this->blur($shadow) : 0;
		imagecopymerge($this->img, $shadow, 0, 0, 0, 0, $this->width, $this->height, 50);
		imagedestroy($shadow);
	}

	function blur($image){
		$imagex = imagesx($image);
		$imagey = imagesy($image);
		$dist = 4;

		for($x = 0; $x < $imagex; ++$x){
			for($y = 0; $y < $imagey; ++$y){
				$newr = 0;
				$newg = 0;
				$newb = 0;

				$colours = array();
				$thiscol = imagecolorat($image, $x, $y);
				if($thiscol != $this->bg_color){
					for($k = $x - $dist; $k <= $x + $dist; ++$k){
						for($l = $y - $dist; $l <= $y + $dist; ++$l){
							if($k < 0){ $colours[] = $thiscol; continue; }
							if($k >= $imagex){ $colours[] = $thiscol; continue; }
							if($l < 0){ $colours[] = $thiscol; continue; }
							if($l >= $imagey){ $colours[] = $thiscol; continue; }
							$colours[] = imagecolorat($image, $k, $l);
						}
					}

					foreach($colours as $colour) {
						$newr += ($colour >> 16) & 0xFF;
						$newg += ($colour >> 8) & 0xFF;
						$newb += $colour & 0xFF;
					}

					$numelements = count($colours);
					$newr /= $numelements;
					$newg /= $numelements;
					$newb /= $numelements;

					$newcol = imagecolorallocate($image, $newr, $newg, $newb);
					imagesetpixel($image, $x, $y, $newcol);
				}
			}
		}
	}

	function fetchImage(){
		$img = file_get_contents($this->cache_file);
		$encoding = false;
		if($this->gzip && !ini_get('zlib.output_compression') && 'ob_gzhandler' != ini_get('output_handler') && isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
			header('Vary: Accept-Encoding'); // Handle proxies
			if ( false !== strpos( strtolower($_SERVER['HTTP_ACCEPT_ENCODING']), 'deflate') && function_exists('gzdeflate') && $this->gzip) {
				$encoding = "deflate";
				$img = gzdeflate( $img, 3 );
			} elseif ( false !== strpos( strtolower($_SERVER['HTTP_ACCEPT_ENCODING']), 'gzip') && function_exists('gzencode') && $this->gzip) {
				$encoding = "gzip";
				$img = gzencode( $img, 3 );
			}
		}
		ob_clean();
		header('X-Generated-By: AnyFont v'.ANYFONT_VERSION.' for WordPress');
		header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($this->cache_file)).' GMT');
		header('Etag: '.$this->etag);
		$encoding !== false ? header('Content-Encoding: '.$encoding) : 0;
		header('Content-Length: '.strlen($img));
		header('Content-Type: image/png');
		echo $img;
		ob_end_flush();
		die;
	}


	/** Description: Method to convert colours from the hex format to RGB
	* @param var hex colour definition
	* @return var RBG Colour Definition
	*/
	function hex_to_rgb($hex){
		substr($hex,0,1) == '#' ? $hex = substr($hex,1) : 0;

		if(strlen($hex) == 3){
			$hex = substr($hex,0,1) . substr($hex,0,1).
			substr($hex,1,1) . substr($hex,1,1).
			substr($hex,2,1) . substr($hex,2,1);
		}

		strlen($hex) != 6 ? $error = 'Error: Invalid color "'.$hex.'"' : 0 ;

		$rgb[] = hexdec(substr($hex,0,2));
		$rgb[] = hexdec(substr($hex,2,2));
		$rgb[] = hexdec(substr($hex,4,2));

		return $rgb;
	}

	function charMap(){
		$txt = $this->getChars(65, 90)."\n";
		$txt .= $this->getChars(97, 122)."\n";
		$txt .= $this->getChars(48, 57)."\n";
		$txt .= $this->getChars(33, 47).$this->getChars(58, 64).$this->getChars(91, 96).$this->getChars(123, 126)."\n";
		$txt .= $this->getChars(192, 223)."\n";
		$txt .= $this->getChars(224, 255);
		return html_entity_decode(urldecode($txt), ENT_QUOTES);
	}

	function getChars($start, $end){
		$txt = "";
		while($start <= $end){
			$txt .= "&#$start;&#32;";
			$start++;
		}
		return $txt;
	}
}

?>