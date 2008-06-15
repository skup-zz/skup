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
    Copyright (C) 2009, Ryan Peel ryan@2amlife.com
 */
// This file is only used when the htaccess option is enabled.
// The purpose of this file is to "bootstrap" the wordpress enviroment without loading any templates.
define('WP_USE_THEMES', false);
$path = realpath('./../../../')."/";

if ( file_exists( $path.'wp-config.php') ) {
	/** The config file resides in ABSPATH */
	require_once( $path.'wp-config.php' );
} elseif ( file_exists( dirname($path) . '/wp-config.php' ) ) {
	/** The config file resides one level below ABSPATH */
	require_once( dirname($path) . '/wp-config.php' );
} else {
	header("HTTP/1.0 404 Not Found");
	exit(404);
}

?>