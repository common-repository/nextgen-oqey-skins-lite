<?php
include ("../../../wp-load.php");
global $wpdb;

if(isset($_REQUEST['galleryid'])){
$id = mysql_real_escape_string($_REQUEST['galleryid']);

$oqey_music = $wpdb->prefix . "oqey_music";

header('Content-Type: ' . feed_content_type('rss-http') . '; charset=' . get_option('blog_charset'), true);
$r = get_option('siteurl').'/wp-content/oqey_gallery/music/'.oqey_getBlogFolder($wpdb->blogid);

$dax .= '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>';
$dax .= '<songs>'; 
    
   $mus = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $oqey_music WHERE status !=2 ORDER BY id ASC LIMIT 0,1 ", $id ) );
   
   $dax .= '<song path="'.urlencode(trim($mus->link)).'" artist="" title="'.urlencode(trim($mus->title)).'"></song>';

   
   $dax .= '</songs>';
   echo $dax;
}
?>