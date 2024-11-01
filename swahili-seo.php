<?php
/*
Plugin Name: Swahili Seo
Plugin URI: https://wordpress.org/plugins/swahili-seo
Description: Set metadata required for Search Engine Optimizations
Version: 1.0.0
Author: Prosper Mtabo
Author URI: 
License: GPLv2
*/
/* Copyright 2022 Prosper Mtabo (email : prospermtabo@gmail.com)
 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

defined('ABSPATH') || die('Access Denied');

register_activation_hook( __FILE__, 'swahiliseo_install' );
function swahiliseo_install() {
 global $wp_version;
 if ( version_compare( $wp_version, '3.5', '<' ) ) {
 wp_die( 'This plugin requires WordPress version 3.5 or higher.' );
 }
}
add_action( 'wp_head', 'swahiliseo_metadata_install' );
function swahiliseo_metadata_install() {
	
	if( is_single() ) {
		global $post;
 $swahiliseo_keywords = get_post_meta( $post->ID, '_swahiliseo_keywords', true );
 $swahiliseo_description = get_post_meta( $post->ID, '_swahiliseo_description', true );
	if (!empty($swahiliseo_keywords)){
	echo '<meta name="keywords" content="'.esc_attr( $swahiliseo_keywords ).'"/>'."\n";	
	}	
	echo '<meta name="Description" content="'.esc_attr( $swahiliseo_description ).'"/>'."\n";
	}
}
//create matabox
add_action( 'add_meta_boxes', 'swahiliseo_meta_box_init' );
// meta box functions for adding the meta box and saving the data
function swahiliseo_meta_box_init() {
 // create our custom meta box
 
 $post_array = ["post","page","product"];
 
 foreach ($post_array as $post_type) {
	  add_meta_box( 'swahiliseo-meta', 'Swahili SEO Metadata',
 'swahiliseo_meta_box',$post_type, 'normal', 'default' );
 }
 //add_meta_box( 'swahiliseo-meta', 'Swahili SEO Metadata',
 //'swahiliseo_meta_box', 'post', 'normal', 'default' );
 //add_meta_box( 'swahiliseo-meta', 'Swahili SEO Metadata',
 //'swahiliseo_meta_box', 'post', 'normal', 'default' );
 //add_meta_box( 'swahiliseo-meta', 'Swahili SEO Metadata',
 //'swahiliseo_meta_box', 'post', 'normal', 'default' );
}
function swahiliseo_meta_box( $post, $box ) {

 // retrieve the custom meta box values
 $swahiliseo_keywords = get_post_meta( $post->ID, '_swahiliseo_keywords', true );
 $swahiliseo_description = get_post_meta( $post->ID, '_swahiliseo_description', true );
 $post_title = $post->post_title;
 $post_permalink = substr(get_permalink($post->ID),0,-1);
 //nonce for security
 wp_nonce_field( plugin_basename( __FILE__ ), 'swa_save_meta_box' );
 
 //Preparing permalink
 $post_permalink_f = str_replace("://",":",$post_permalink);
 $post_permalink_f = str_replace("/",">",$post_permalink_f);
 $post_permalink_f = str_replace(":","://",$post_permalink_f);
 if (strlen($post_permalink_f)>33){$post_permalink_f = substr("$post_permalink_f",0,32).'...';}
 
 //prepairing title
 $post_title = trim($post_title);
 $post_title = substr($post_title,0,60);
 $post_title = str_replace ("  "," ",$post_title);//original title
 $with_space_count_title = strlen($post_title);
 $post_title_trim = str_replace (" ","",$post_title);//space removed title
 $without_space_count_title = strlen($post_title_trim);
 $number_title = 60 + $with_space_count_title - $without_space_count_title;
 $post_title = substr($post_title,0,$number_title);
 
  
 //prepairing description
 $swahiliseo_description = trim($swahiliseo_description);
 $swahiliseo_description = substr ($swahiliseo_description,0,(165 ));
 $swahiliseo_description = str_replace ("  "," ",$swahiliseo_description);//original description
 $with_space_count_description = strlen ($swahiliseo_description);
 $swahiliseo_description_trim = trim ($swahiliseo_description);//modified description
 $without_space_count_description = strlen ( $swahiliseo_description_trim);
 $number_description = 165 + $with_space_count_description - $without_space_count_description;
 $swahiliseo_description = substr ($swahiliseo_description,0,($number_description));  
 // custom meta box form elements
 echo '<p>Description:</p> <textarea id="swahiliseo_description" type="text" name="swahiliseo_description">'.esc_textarea( $swahiliseo_description ).'</textarea>
       <p>Keywords:</p><span id = "red"> This metatag is not used by Google, but other search engines can use it also avoid to use unrelated
keywords in your site </span> <textarea placeholder="keyword 1,keyword 2, keyword 3..." id = "swahiliseo_keywords"  type="text" name="swahiliseo_keywords">'.esc_textarea( $swahiliseo_keywords ).'</textarea>';
 echo '<p>Search Engines Preview:</p><span id = "green">Sample Preview in Desktop used by Google </span>
       <div id = "swahiliseo_preview" ><a disabled href="'.$post_permalink.'" id = "sw_post_permalink">'.$post_permalink_f.'</a>&nbsp&nbsp<img id = "dot" src = "'.plugin_dir_url( __FILE__ ) . 'img/dot.png'.'"> <div id = "sw_post_title"> <a id = "sw_post_title" href="'.$post_permalink.'">'.$post_title.'</a></div><div id = "swahiliseo_description">'. $swahiliseo_description.'</div></div>';
}
 // hook to save our meta box data when the post is saved
add_action( 'save_post', 'swahiliseo_save_meta_box' );
function swahiliseo_save_meta_box( $post_id ) {

 // process form data if $_POST is set
 if( isset( $_POST['swahiliseo_keywords'] ) ) {
 // if auto saving skip saving our meta box data
 if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
 return;
 //check nonce for security
 check_admin_referer( plugin_basename( __FILE__ ), 'swa_save_meta_box' );
 //trim spaces in meta keywords if empty
 $_POST['swahiliseo_keywords'] = trim ($_POST['swahiliseo_keywords']);
 // save the meta box data as post meta using the post ID as a unique prefix
 update_post_meta( $post_id, '_swahiliseo_keywords',sanitize_text_field( $_POST['swahiliseo_keywords'] ) );
 update_post_meta( $post_id, '_swahiliseo_description',sanitize_text_field ( $_POST['swahiliseo_description'] ) );

 }
}

register_uninstall_hook( __FILE__, 'swahiliseo_uninstall_hook' );
function swahiliseo_uninstall_hook() {
global $wpdb;
$wpdb->query( $wpdb->prepare( " DELETE FROM $wpdb->postmeta WHERE meta_key = '_swahiliseo_keywords' " ) );
$wpdb->query( $wpdb->prepare( " DELETE FROM $wpdb->postmeta WHERE meta_key = '_swahiliseo_description' " ) );
}

function swahiliseo_load_scripts() {
 
	wp_enqueue_style('swahiliseo-css', plugin_dir_url( __FILE__ ) . 'css/styles.css');
 
}
add_action ('admin_head','swahiliseo_load_scripts');
