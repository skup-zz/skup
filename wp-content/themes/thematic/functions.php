<?php

// Getting Theme and Child Theme Data
// Credits: Joern Kretzschmar

$themeData = get_theme_data(TEMPLATEPATH . '/style.css');
$thm_version = trim($themeData['Version']);
if(!$thm_version)
    $thm_version = "unknown";

$ct=get_theme_data(STYLESHEETPATH . '/style.css');
$templateversion = trim($ct['Version']);
if(!$templateversion)
    $templateversion = "unknown";

// set theme constants
define('THEMENAME', $themeData['Title']);
define('THEMEAUTHOR', $themeData['Author']);
define('THEMEURI', $themeData['URI']);
define('THEMATICVERSION', $thm_version);

// set child theme constants
define('TEMPLATENAME', $ct['Title']);
define('TEMPLATEAUTHOR', $ct['Author']);
define('TEMPLATEURI', $ct['URI']);
define('TEMPLATEVERSION', $templateversion);


// set feed links handling
// If you set this to TRUE, thematic_show_rss() and thematic_show_commentsrss() are used instead of add_theme_support( 'automatic-feed-links' )
if (!defined('THEMATIC_COMPATIBLE_FEEDLINKS')) {	
	if (function_exists('comment_form')) {
		define('THEMATIC_COMPATIBLE_FEEDLINKS', false); // WordPress 3.0
	} else {
		define('THEMATIC_COMPATIBLE_FEEDLINKS', true); // below WordPress 3.0
	}
}

// set comments handling for pages, archives and links
// If you set this to TRUE, comments only show up on pages with a key/value of "comments"
if (!defined('THEMATIC_COMPATIBLE_COMMENT_HANDLING')) {
	define('THEMATIC_COMPATIBLE_COMMENT_HANDLING', false);
}

// set body class handling to WP body_class()
// If you set this to TRUE, Thematic will use thematic_body_class instead
if (!defined('THEMATIC_COMPATIBLE_BODY_CLASS')) {
	define('THEMATIC_COMPATIBLE_BODY_CLASS', false);
}

// set post class handling to WP post_class()
// If you set this to TRUE, Thematic will use thematic_post_class instead
if (!defined('THEMATIC_COMPATIBLE_POST_CLASS')) {
	define('THEMATIC_COMPATIBLE_POST_CLASS', false);
}
// which comment form should be used
if (!defined('THEMATIC_COMPATIBLE_COMMENT_FORM')) {
	if (function_exists('comment_form')) {
		define('THEMATIC_COMPATIBLE_COMMENT_FORM', false); // WordPress 3.0
	} else {
		define('THEMATIC_COMPATIBLE_COMMENT_FORM', true); // below WordPress 3.0
	}
}

// Check for WordPress mu or WordPress 3.0
define('THEMATIC_MB', function_exists('get_blog_option'));

// Create the feedlinks
if (!(THEMATIC_COMPATIBLE_FEEDLINKS)) {
	add_theme_support( 'automatic-feed-links' );
}

// Check for WordPress 2.9 add_theme_support()
if ( apply_filters( 'thematic_post_thumbs', TRUE) ) {
	if ( function_exists( 'add_theme_support' ) )
	add_theme_support( 'post-thumbnails' );
}

// Load jQuery
wp_enqueue_script('jquery');

// Path constants
define('THEMELIB', TEMPLATEPATH . '/library');

// Create Theme Options Page
require_once(THEMELIB . '/extensions/theme-options.php');

// Load legacy functions
require_once(THEMELIB . '/legacy/deprecated.php');

// Load widgets
require_once(THEMELIB . '/extensions/widgets.php');

// Load custom header extensions
require_once(THEMELIB . '/extensions/header-extensions.php');

// Load custom content filters
require_once(THEMELIB . '/extensions/content-extensions.php');

// Load custom Comments filters
require_once(THEMELIB . '/extensions/comments-extensions.php');
 
// Load custom discussion filters
require_once(THEMELIB . '/extensions/discussion-extensions.php');

// Load custom Widgets
require_once(THEMELIB . '/extensions/widgets-extensions.php');

// Load the Comments Template functions and callbacks
require_once(THEMELIB . '/extensions/discussion.php');

// Load custom sidebar hooks
require_once(THEMELIB . '/extensions/sidebar-extensions.php');

// Load custom footer hooks
require_once(THEMELIB . '/extensions/footer-extensions.php');

// Add Dynamic Contextual Semantic Classes
require_once(THEMELIB . '/extensions/dynamic-classes.php');

// Need a little help from our helper functions
require_once(THEMELIB . '/extensions/helpers.php');

// Load shortcodes
require_once(THEMELIB . '/extensions/shortcodes.php');

// Adds filters for the description/meta content in archives.php
add_filter( 'archive_meta', 'wptexturize' );
add_filter( 'archive_meta', 'convert_smilies' );
add_filter( 'archive_meta', 'convert_chars' );
add_filter( 'archive_meta', 'wpautop' );

// Remove the WordPress Generator - via http://blog.ftwr.co.uk/archives/2007/10/06/improving-the-wordpress-generator/
function thematic_remove_generators() { return ''; }
if (apply_filters('thematic_hide_generators', TRUE)) {  
    add_filter('the_generator','thematic_remove_generators');
}

// Translate, if applicable
load_theme_textdomain('thematic', THEMELIB . '/languages');

$locale = get_locale();
$locale_file = THEMELIB . "/languages/$locale.php";
if ( is_readable($locale_file) )
	require_once($locale_file);


//--------------------------------------------------------------------------------------------------------------

//Replace Blog Title with Your Logo
 
function remove_thematic_blogtitle() {
     remove_action('thematic_header','thematic_blogtitle', 3);
}
add_action('init','remove_thematic_blogtitle');
 
function child_logo_image() {
     //Add your own logo image code
     //Here's an example
 
 //--------------------------------------------------------------------------------------------------------------
 ?>
	

    <div id="logo"><a href="index.php" title=""><img src="<?php bloginfo('template_url'); ?>/images/logo3.png" alt=""/></a></div>



	<style type="text/css">

	
    #logo {
	width:60%;
	height:60%;
	position: fixed;
	z-index:1;
	padding-top: 4px;
	padding-left:880px;
	}
	</style>

   <?php
   // End Example
}
add_action('thematic_aboveheader','child_logo_image', 3);

  
//--------------------------------------------------------------------------------------------------

 //--------------------------------------------------------------------------------------------------
// First we make our function
function childtheme_welcome_blurb() {
 
// We'll show it only on the HOME page IF it's NOT paged
// (so, not page 2,3,etc.)
if (is_home() & !is_paged()) { ?>
 
<?php aio_slideshow(); ?>

<!-- our welcome blurb ends here -->
<?php }
 
} // end of our new function childtheme_welcome_blurb
 
// Now we add our new function to our Thematic Action Hook
add_action('thematic_aboveheader','childtheme_welcome_blurb');
			  ?>

<?php 
//--------------------------------------------------------------------------------------------------


// Generate footer code
 
function childtheme_footer($thm_footertext) {
     $date = date('Y');
     $blog_name = get_bloginfo('name');
     $admin_url = get_bloginfo('wpurl') . '/wp-admin';
     $entries_rss = get_bloginfo('rss2_url');
 
     $thm_footertext = sprintf(
     '<p>&copy; %s %s | <a href="http://wptheming.com/2009/10/useful-thematic-filters/">Site Admin</a> | <a href="http://wptheming.com/2009/10/useful-thematic-filters/">Entries RSS</a></p>',
     $date, $blog_name, $admin_url, $entries_rss);
 
     return $thm_footertext;
     }
 
add_filter('thematic_footertext', 'childtheme_footer');


//--------------------------------------------------------------------------------------------------

/* Defining Widget Areas */
 
function remove_widget_areas($content) {
    unset($content['Secondary Aside']);
    unset($content['1st Subsidiary Aside']);
    unset($content['2nd Subsidiary Aside']);
    unset($content['3rd Subsidiary Aside']);
    unset($content['Index Top']);
    unset($content['Index Insert']);
    unset($content['Single Top']);
    unset($content['Single Insert']);
    unset($content['Single Bottom']);
    unset($content['Page Top']);
    unset($content['Page Bottom']);
    return $content;
}
add_filter('thematic_widgetized_areas', 'remove_widget_areas');
 
function rename_widgetized_areas($content) {
    $content['Primary Aside']['args']['name'] = 'Posts Sidebar';
    $content['Index Bottom']['args']['name'] = 'Home Content';
    return $content;
}
add_filter('thematic_widgetized_areas', 'rename_widgetized_areas');
 
//  Make the Primary Aside Widget Area Only Appear on Pages
 
function childtheme_primary_aside() {
    if (!is_front_page() && !is_home() && !is_page('view-art')) {
        if (is_sidebar_active('primary-aside')) {
            echo thematic_before_widget_area('primary-aside');
    if ( has_post_thumbnail()) { ?>
<div class="page-thumbnail">
  <?php the_post_thumbnail( 'medium' ); ?>
</div>
<?php }
    else {
        dynamic_sidebar('primary-aside');
        }
            echo thematic_after_widget_area('primary-aside');
        }
    }
}
 
// Change Primary Aside Function
 
function change_primary_aside($content) {
    $content['Primary Aside']['function'] = 'childtheme_primary_aside';
    return $content;
}
add_filter('thematic_widgetized_areas','change_primary_aside');

//---------------------------------------------------------------------------------------------




	function child_interior_mod() {
	// remove all of thematic's header content
	
	remove_action('thematic_header','thematic_blogdescription',5);
	remove_action('thematic_header','thematic_brandingclose',7);
	remove_action('thematic_header','thematic_access',9);

	// hook all of it into init inside this function
	add_action('init','child_firstpage_home_mod');
}

function child_firstpage_home_mod() {
	 // remove nav from it's default location

	// hook all of it into init inside this function
	add_action('init','child_interior_mod');
}

function childtheme_structural_mods() {
	if (is_home() & !is_paged()) {
		child_firstpage_home_mod();
	} else {
		child_interior_mod();
	}
}
add_action('template_redirect','childtheme_structural_mods');


//--------------------------------------------------------------------------------------------

function fix() {
    if (is_home() & !is_paged()) { 
 
 // Add Header Image // Add Header Image

	 echo '<a href="'.get_bloginfo('url').'" title="'.get_bloginfo('name').'" ><span id="header-image"></span></a>';
	
?>
	<body id="body1"></div>
      
<?php        
      } else { ?>
    
    <body id="body2"></div>
    <?php }
    }

add_action('thematic_aboveheader','fix');


//--------------------------------------------------------------------------------------------

