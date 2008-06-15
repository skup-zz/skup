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

	private $text;
	private $style;
	private $gzip;
	private $fontsettings;
	private $etag;

	/**
	* @param $style style to be used (must be defined in styles.ini)
	* @param $text string to be converted to an image
	* @param $gzip boolean to control if the response should be gzipped or not.
	*/
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
				$this->text = self::charMap();
				$this->fontsettings['max-width-text-align'] = "center";
			} else {
				$this->fontsettings['max-width-text-align'] = "left";
			}
			$this->fontsettings["max-width"] = 900;
			$this->fontsettings['color'] = "333333";
			$this->fontsettings['font-size'] = 27;
			$this->fontsettings['shadow'] = false;
			$this->fontsettings['shadow-color'] = "FFFFFF";
			$this->fontsettings["limit-width"] = true;
			$this->fontsettings["line-height"] = 50;
			$this->fontsettings['is_admin'] = true;
			$this->fontsettings['image-padding'] = true;
			$this->fontsettings['image-padding-bottom'] = 10;
			$this->fontsettings['image-padding-left'] = 10;
		} else if($this->style == "admin-small"){
			$this->fontsettings = array();
			$this->fontsettings['font-name'] = $this->text;
			$this->text = $this->displaytext;
			$this->fontsettings['color'] = "333333";
			$this->fontsettings['font-size'] = 20;
			$this->fontsettings['shadow'] = false;
			$this->fontsettings['shadow-color'] = "FFFFFF";
			$this->fontsettings['shadow-spread'] = 0;
			$this->fontsettings['is_admin'] = true;
		}else {
			$styles = unserialize(get_option('anyfont_styles'));
			if(isset($styles['image'][$this->style])){
				$this->fontsettings = $styles['image'][$this->style];
			} else {
				wp_redirect(get_option('siteurl'));
				exit(0);
			}
		}
		if(is_array($this->fontsettings)){
			$this->linecount = 1;

			!isset($this->fontsettings['shadow-distance']) ? $this->fontsettings['shadow-distance'] = 1 : 0;
			!isset($this->fontsettings['shadow-spread']) ? $this->fontsettings['shadow-spread'] = 1 : 0;
			!isset($this->fontsettings['image-padding']) ? $this->fontsettings['image-padding'] = 0 : 0;
			!isset($this->fontsettings['image-padding-top']) ? $this->fontsettings['image-padding-top'] = 0 : 0;
			!isset($this->fontsettings['image-padding-bottom']) ? $this->fontsettings['image-padding-bottom'] = 0 : 0;
			!isset($this->fontsettings['image-padding-left']) ? $this->fontsettings['image-padding-left'] = 0 : 0;
			!isset($this->fontsettings['image-padding-right']) ? $this->fontsettings['image-padding-right'] = 0 : 0;
			!isset($this->fontsettings['line-height']) ? $this->fontsettings['line-height'] = 0 : 0;
			!isset($this->fontsettings['max-width-text-align']) ? $this->fontsettings['max-width-text-align'] = !$this->fontsettings['text-align-center'] ? "left" : "center" : 0;
			$this->fonttype = !file_exists(ANYFONT_FONTDIR."/".$this->fontsettings['font-name'].".ttf") ? ".otf" : ".ttf";
			$pre_hash = "";
			foreach($this->fontsettings as $value){
				$pre_hash.=$value;
			}
			$hash = md5($pre_hash.$this->text."imagick");
			$this->etag = $hash;

			if(!isset($this->fontsettings['is_admin'])){
				$this->cache_file = ANYFONT_CACHE."/$hash-site.png";
			} else {
				$this->cache_file = ANYFONT_CACHE."/$hash-admin.png";
			}
			if(file_exists($this->cache_file)){
				if(isset($_SERVER["HTTP_IF_NONE_MATCH"]) || isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])){
					if($_SERVER["HTTP_IF_NONE_MATCH"] == $this->etag || $_SERVER['HTTP_IF_MODIFIED_SINCE'] == gmdate('D, d M Y H:i:s', filemtime($this->cache_file)).' GMT'){
						header("HTTP/1.0 304 Not Modified");
						exit(304);
					} else {
						self::text2Image();
					}
				} else {
					self::printImage();
				}
			} else {
				self::text2Image();
			}
		}
	}

	/** Description
	* Creates a png image from a text string using truetype fonts and writes the file to cache
	*/
	private function text2Image(){
		$image = new Imagick();
		$draw = new ImagickDraw();
		$draw->setTextEncoding("UTF-8");
		$draw->setFillColor(new ImagickPixel("#".$this->fontsettings['color']));
		if(file_exists(ANYFONT_FONTDIR."/".$this->fontsettings['font-name'].$this->fonttype)){
			$draw->setFont(ANYFONT_FONTDIR."/".$this->fontsettings['font-name'].$this->fonttype);
		}
		$draw->setFontSize($this->fontsettings['font-size']);
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
				$metrics =  $image->queryFontMetrics($draw, $test_str);
				if ($metrics['textWidth'] < $max) {
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
		//get metrics for width
		$fm = $image->queryFontMetrics($draw, $this->text);
		//get metrics for first letter
		$fcm = $image->queryFontMetrics($draw, substr($this->text, 0, 1));
		$min_x = min(array($fcm["boundingBox"]["x2"],$fcm["boundingBox"]["x1"]));
		if($min_x < 0){
			$min_x = -($min_x);
		}
		//get metrics for font height and baseline
		$fym = $image->queryFontMetrics($draw, self::charMap());
		$min_y = min(array($fym["boundingBox"]["y2"],$fym["boundingBox"]["y1"]));
		$max_y = max(array($fym["boundingBox"]["y2"],$fym["boundingBox"]["y1"]));
		$padding_x = !$this->fontsettings['image-padding'] ? 0 : (int)$this->fontsettings['image-padding-left']+(int)$this->fontsettings['image-padding-right'];
		$padding_y = !$this->fontsettings['image-padding'] ? 0 : (int)$this->fontsettings['image-padding-top']+(int)$this->fontsettings['image-padding-bottom'];
		$shadowspread = !$this->fontsettings['shadow'] ? 0 : $this->fontsettings['shadow-spread'];
		$width = $fm["textWidth"]+$padding_x;
		$height = $max_y+(-($min_y))+$padding_y;
		if($this->fontsettings['shadow']){
			$height += ((int)$this->fontsettings['shadow-distance']+((int)$this->fontsettings['shadow-spread']*2.8));
	        $width += ((int)$this->fontsettings['shadow-distance']+((int)$this->fontsettings['shadow-spread']*2.8));
		}
		if($this->linecount > 1){
			$this->fontsettings["line-height"] > 0 ? $height = $this->fontsettings["line-height"] < $height ? $height-(($height-$this->fontsettings["line-height"])/2) : $this->fontsettings["line-height"] : 0;
			$height*=$this->linecount;
		}
		if($this->fontsettings["limit-width"] && $this->fontsettings['max-width-text-align'] == "center"){
			$width = $this->fontsettings['max-width'];
		}
		$x = $min_x+$shadowspread+(!$this->fontsettings['image-padding'] ? 0 : ((int)$this->fontsettings['image-padding-left']));
		$y = $max_y+$shadowspread+(!$this->fontsettings['image-padding'] ? 0 : ((int)$this->fontsettings['image-padding-top']));
		$image->newImage($width, $height, "transparent", "png");
		if($this->fontsettings['shadow']){
			if($this->linecount > 1 && $this->fontsettings["line-height"] > 0){

				$l = 0;
				$ly = $y;
				$lines = explode("\n", $this->text);
				while($l < $this->linecount){
					if($this->fontsettings["limit-width"] && $this->fontsettings['max-width-text-align'] == "center"){
						$m = $image->queryFontMetrics($draw, $lines[$l]);
						$x = (($width-$m['textWidth'])/2)+(!$this->fontsettings['image-padding'] ? 0 : ((int)$this->fontsettings['image-padding-left']));;
					}
					$l > 0 ? $ly += $this->fontsettings["line-height"] : 0;
					$image = $this->shadowImage($image, $x, $ly, $lines[$l]);
					$l++;
				}
			}else{
				if($this->fontsettings["limit-width"] && $this->fontsettings['max-width-text-align'] == "center"){
					$m = $image->queryFontMetrics($draw, $this->text);
					$x = (($width-$m['textWidth'])/2)+(!$this->fontsettings['image-padding'] ? 0 : ((int)$this->fontsettings['image-padding-left']));;
				}
				$image = $this->shadowImage($image, $x, $y, $this->text);
			}
			$this->fontsettings['shadow-spread'] > 0 ? $image->blurImage(0, (int)$this->fontsettings['shadow-spread'], Imagick::CHANNEL_ALL) : 0;
		}
		if($this->linecount > 1 && $this->fontsettings["line-height"] > 0){
			$l = 0;
			$lines = explode("\n", $this->text);
			while($l < $this->linecount){
				if($this->fontsettings["limit-width"] && $this->fontsettings['max-width-text-align'] == "center"){
					$m = $image->queryFontMetrics($draw, $lines[$l]);
					$x = (($width-$m['textWidth'])/2)+(!$this->fontsettings['image-padding'] ? 0 : ((int)$this->fontsettings['image-padding-left']));;
				}
				$l > 0 ? $y += $this->fontsettings["line-height"] : 0;
				$image->annotateImage($draw, $x, $y, 0, $lines[$l]);
				$l++;
			}
		}else{
			if($this->fontsettings["limit-width"] && $this->fontsettings['max-width-text-align'] == "center"){
				$m = $image->queryFontMetrics($draw, $this->text);
				$x = (($width-$m['textWidth'])/2)+(!$this->fontsettings['image-padding'] ? 0 : ((int)$this->fontsettings['image-padding-left']));;
			}
			$image->annotateImage($draw, $x, $y, 0, $this->text);
		}
		$image->writeImage($this->cache_file);
		self::printImage($image);
	}

	private function shadowImage($image, $x, $y, $text){
		$shadow = new ImagickDraw();
		$shadow->setFillColor(new ImagickPixel("#".$this->fontsettings['shadow-color']));
		$shadow->setFont(ANYFONT_FONTDIR."/".$this->fontsettings['font-name'].$this->fonttype);
		$shadow->setFontSize($this->fontsettings['font-size']);
		$image->annotateImage($shadow, $x+$this->fontsettings['shadow-distance'], $y+$this->fontsettings['shadow-distance'], 0, $text);
		return $image;
	}

	/** Description
	* Fetches the image from cache and delivers it to the browser along with the required headers.
	* @prints PNG image
	*/
	private function printImage($img=false){
		!$img ?  $img = file_get_contents($this->cache_file) : 0;
		$encoding = false;
		if($this->gzip && !ini_get('zlib.output_compression') && 'ob_gzhandler' != ini_get('output_handler') && isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
			header('Vary: Accept-Encoding'); // Handle proxies
			if ( false !== strpos( strtolower($_SERVER['HTTP_ACCEPT_ENCODING']), 'deflate') && function_exists('gzdeflate') && $this->gzip) {
				$encoding = 'deflate';
				$img = gzdeflate( $img, 3 );
			} elseif ( false !== strpos( strtolower($_SERVER['HTTP_ACCEPT_ENCODING']), 'gzip') && function_exists('gzencode') && $this->gzip) {
				$encoding = 'gzip';
				$img = gzencode( $img, 3 );
			}
		}
		ob_clean();
		header('X-Generated-By: AnyFont v'.ANYFONT_VERSION.' for WordPress');
		header('Last-modified: '.gmdate('D, d M Y H:i:s', filemtime($this->cache_file)).' GMT');
		header('Expires: '.gmdate("D, d M Y H:i:s", mktime(0, 0, 0, date("m")  , date("d")-1, date("Y") )).' GMT');   //FORMAT: Wed, 11 Jan 1984 05:00:00 GMT
		header('Etag: "'.$this->etag.'"');
		$encoding !== false ? header('Content-Encoding: '.$encoding) : 0;
		header('Content-type: image/png');
		header('Content-length: '.strlen($img));
		echo $img;
		ob_end_flush();
		die;
	}

	private function hexToRgb($hex){
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

	private function charMap(){
		$i = 65;
		$txt = self::getChars(65, 90)."\n";
		$txt .= self::getChars(97, 122)."\n";
		$txt .= self::getChars(48, 57)."\n";
		$txt .= self::getChars(33, 47).self::getChars(58, 64).self::getChars(91, 96).self::getChars(123, 126)."\n";
		$txt .= self::getChars(192, 223)."\n";
		$txt .= self::getChars(224, 255);
		return html_entity_decode(urldecode($txt), ENT_QUOTES);
	}

	private function getChars($start, $end){
		$txt = "";
		while($start <= $end){
			$txt .= "&#$start;&#32;";
			$start++;
		}
		return $txt;
	}
}

?>