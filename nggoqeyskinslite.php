<?php
// NextGen Oqey Skins Lite
// Copyright (c) 2012 oqeysites.com
// This is an add-on for WordPress
// http://wordpress.org/
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
// *****************************************************************

/*
Plugin Name: NextGen Oqey Skins Lite
Version: 0.1
Description: NextGen Oqey Skins Lite is an add-on for oQey Gallery plugin that allow to use oQey Skins for NextGen gallery.
Author: oqeysites.com
Author URI: http://oqeysites.com/
*/
if (!empty($_SERVER['SCRIPT_FILENAME']) && 'nggoqeyskinslite.php' == basename($_SERVER['SCRIPT_FILENAME'])) die ('Please do not load this page directly. Thanks!');

global $oqeycounter;

define('OQEY_ABSPATH', str_replace('\\', '/', ABSPATH) ); //oqey path

require_once(OQEY_ABSPATH . 'wp-admin/includes/plugin.php');


function oqey_gallery_required(){
   
      echo '<div class="error fade" style="background-color:#E36464;">
            <p>'.__( 'NextGen Oqey Skins Lite requires oQey Gallery plugin activated. Please install and activate this plugin.', 'oqey-gallery' ).'
            </p></div>';
}

if(!is_plugin_active('oqey-gallery/oqeygallery.php')){
   add_action( 'admin_notices', 'oqey_gallery_required');
}

/*Functions*/
function oqey_getBFolder($id){ $folder=""; if($id==0 || $id==1){ $folder = "";}else{ $folder=$id."/"; } return $folder; }

function NgggetUserNow($userAgent) {
    $crawlers = 'Google|msnbot|Rambler|Yahoo|AbachoBOT|accoona|' .
    'AcioRobot|ASPSeek|CocoCrawler|Dumbot|FAST-WebCrawler|' .
    'GeonaBot|Gigabot|Lycos|MSRBOT|Scooter|AltaVista|IDBot|eStyle|Scrubby|yandex|facebook';
    $isCrawler = (preg_match("/$crawlers/i", $userAgent) > 0);
    return $isCrawler;
}
/**/

function read_next_tags_and_process_them($content) {
global $post;

            
$tag = 'slideshow';
remove_shortcode( $tag );

$tag = 'nggallery';
remove_shortcode( $tag );

add_shortcode( 'slideshow', 'NgAddoQeyGallery' );
add_shortcode( 'nggallery', 'NgAddoQeyGallery' );

return $content;

}

add_filter('the_content', 'read_next_tags_and_process_them', 1);

function NgAddoQeyGallery($atts){
   global $oqeycounter, $post_ID, $wpdb, $post, $wp_query;
   
   if (is_feed()) {

    // return AddoQeyGalleryToFeed($atts);

   }else{

   if($atts['width']!=""){ $oqey_width = $atts['width']; }else{ $oqey_width = get_option('oqey_width'); $oqey_width_n = get_option('oqey_width'); }
   if($atts['height']!=""){ $oqey_height = $atts['height']; }else{ $oqey_height = get_option('oqey_height'); }
   if($atts['autoplay']!=""){ $oqey_autoplay = $atts['autoplay']; }else{ $oqey_autoplay = "false"; }
   
   $id = $atts['id'];
   $id = esc_sql( $id );
   
   $oqey_galls = $wpdb->prefix . "ngg_gallery";
   $oqey_images = $wpdb->prefix . "ngg_pictures";
   $oqey_skins = $wpdb->prefix . "oqey_skins";
   
   $oqey_BorderSize = get_option('oqey_BorderSize');
   $oqey_bgcolor = get_option('oqey_bgcolor');
   $plugin_url_qu = site_url() . '/wp-content/plugins/nextgen-oqey-skins-lite';
   $plugin_repo_url = site_url() . '/wp-content/oqey_gallery';
   $oqey_gallery_url = site_url() . '/wp-content/plugins/oqey-gallery';
   
   $skinoptionsrecorded = "false";

   $gal = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $oqey_galls WHERE gid = %d ", $id ) );
   
   //print_r($gal);  
   
   $nggpath = $gal->path;
   
   if($gal){
            
      $folder = $gal->folder;
      $gal_title = urlencode($gal->title);

      /*get default skin*/         
      $skin = $wpdb->get_row("SELECT * FROM $oqey_skins WHERE status = '1'"); 
      $options = "oqey_skin_options_".$skin->folder; 
      $all = json_decode(get_option($options));
         
      if(!empty($all)){
            
            $skinoptionsrecorded = "true";
        
      }      
      
      $link =  OQEY_ABSPATH . 'wp-content/oqey_gallery/skins/'.oqey_getBFolder($wpdb->blogid).$skin->folder.'/'.$skin->folder.'.swf';
      
      if(!is_file($link)){
        
         $skin = $wpdb->get_row("SELECT * FROM $oqey_skins WHERE status != '2' LIMIT 0,1"); 
         $options = "oqey_skin_options_".$skin->folder; 
         $all = json_decode(get_option($options));
         
         if(!empty($all)){
            
            $skinoptionsrecorded = "true";
         
         }         
      }

      
      $all = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $oqey_images WHERE galleryid = %d ORDER BY sortorder ASC", $id  ));

      define('IBROWSER', preg_match('~(iPad|iPod|iPhone)~si', $_SERVER['HTTP_USER_AGENT']));
      define('OQEYBROWSER', preg_match('~(WebKit)~si', $_SERVER['HTTP_USER_AGENT']));      
      $gimg = get_option('siteurl').'/wp-content/oqey_gallery/galleries/'.oqey_getBFolder($wpdb->blogid).$gal->folder.'/galimg/';	

      $isCrawler = NgggetUserNow($_SERVER['HTTP_USER_AGENT']); // check if is a crawler

      if ($isCrawler || (is_plugin_active('wptouch/wptouch.php') && IBROWSER)){
    
        if ($isCrawler){
            
           $imgs = "<p align='center'>".urldecode($gal_title)."</p>";
        
        }else{ 
            
            if(is_plugin_active('wptouch/wptouch.php') && IBROWSER){
                
               if(get_option('oqey_gall_title_no')=="on"){
   	           
                  $imgs = '<div style="margin-left:auto; margin-right:auto; width:100%; text-align:center;">'.urldecode($gal_title).'</div>';
	           
               } 
            }
        }
    
        foreach($all as $i){ 

        
           $gimg = get_option('siteurl').'/'.trim($nggpath).'/';
         
           if($i->img_type!="video"){
          
             $imgs .= '<p style="margin-left:auto; margin-right:auto;display:block;text-align:center;">
	                     <img src="'.$gimg.trim($i->filename).'" alt="Photo '.urldecode(trim($i->alttext)).'" style="margin-top:1px;height:auto;max-width:100%;"/></p>'; 
             
             if(get_option('oqey_show_captions_under_photos')=="on"){
                
		$comments = '';
		
		if(!empty($i->description)){
		   $comments = ' | '.trim(urldecode($i->description));
	        }
			       
                $imgs .= '<p class="oqey_p_comments">'.trim(urldecode($i->alttext)).$comments."</p>";
                
             }
          
           }
           
        } 
        
        if ($isCrawler){ 
            
            $imgs .= '<div style="font-size:11px;margin-left:auto;margin-right:auto;width:100%;text-align:right;"><a href="http://oqeysites.com" target="_blank"><img src="'.$oqey_gallery_url.'/images/oqey-logo.png" alt="WordPress Photo Gallery Plugin by oQeySites"/></a></div>';
            return $imgs; 
        
        }else{ 
            
            return $imgs; 
        
        }
        
    }else{	
	
	if(get_option('oqey_gall_title_no')=="on"){
	   
   	   $galtitle = '<div style="margin-left:auto; margin-right:auto; width:100%; text-align:center;">'.urldecode($gal_title).'</div>';
	
    }else{ 
        
        $galtitle =""; 
    
    }
	
    $allimgs = array();
    
    if( get_option('oqey_noflash_options')=="incolums" ){
        
        $top_margin ='margin-top:3px;';
        
    }else{
        
        $top_margin = '';
        
    }
    
    $bgimages = array();
	
	foreach($all as $i){ 
	  
         $ipath      = OQEY_ABSPATH.'/'.trim($nggpath).'/';
         $img_type   = "nextgen";
         $img_f_path = urlencode(trim($nggpath));
         $img_path   = $ipath.trim($i->filename);   
         $size       = @getimagesize( $img_path );
    
	if ( $size ){
	
       list($iwidth, $iheight, $itype, $iattr)= $size;
    
    }else{
	
       $iwidth = 900;
       $iheight = 600;
	
    }
    
       $img_holder_h = $oqey_width/1.5; ///??????????????????
       
       if(!empty($atts['height'])){ $img_holder_h = $atts['height']; }else{ $img_holder_h = $oqey_width/1.5; }
       $customlink = "";
       $div_custom_margin = "";
       $custom_bg_img = "background:transparent;";       
       

       $c_width = $oqey_width;
       $oqey_width_n = $oqey_width;
       
       $d = wp_expand_dimensions($iwidth, $iheight, $oqey_width, $img_holder_h);
       if(!empty($atts['process'])){ $process = '&amp;process='.$atts['process']; }else{ $process = '&amp;process=on'; }
 	    $img_full_root = get_option('siteurl').'/wp-content/plugins/nextgen-oqey-skins-lite/oqeyimgresize.php?width='.$d[0].'&amp;new_height='.$d[1].'&amp;folder='.$gal->folder.'&amp;img='.trim($i->filename).'&amp;img_type='.$img_type.'&amp;img_f_path='.$img_f_path.$process;
       $imgs .= '[div class="oqeyimgdiv" style="background: url('.$img_full_root.') center top no-repeat;width:'.$oqey_width_n.'px;height:'.$img_holder_h.'px;'.$top_margin.'"]'.$customlink.'[/div]';
       

       if(get_option('oqey_show_captions_under_photos')=="on" && get_option('oqey_noflash_options')=="incolums" ){
                
                $imgs .= '[p class="oqey_p_comments"]'.trim($i->comments)."[/p]";
                
        }
      	
    }	
    
	if(get_option("oqey_backlinks")=="on"){ 
	
       $oqeybacklink = '<div style="font-size:11px;margin-left:auto;margin-right:auto;width:100%;text-align:center;font-family:Arial,sans-serif">powered by <a href="http://oqeysites.com" target="_blank">oQeySites</a></div>'; 
	
    }
    
    $custom_height = "";    
    $margin_top = $img_holder_h/2-50;	
	
	if( get_option('oqey_noflash_options')=="incolums" ){  
	   
	    $incolums        = "on";
	    $optouch         = "off"; 
       $custom_height   = "auto";
       $custom_height_n = "auto";
       $img_holder_h    = "auto";
	
    }
	
    if( get_option('oqey_noflash_options')=="injsarr" ){ 
	
       $incolums = "off"; 
	    $optouch  = "off"; 	
       
       if(get_option("oqey_backlinks")=="on"){       
           $custom_height = $custom_height + 25; 
       }
       
       if(get_option('oqey_gall_title_no')=="on"){
           
           $custom_height = $custom_height + 25;  
           $margin_top    = $margin_top + 25;         

       }
       
       $custom_height   = $custom_height + $img_holder_h."px";
       $custom_height_n = $custom_height;
	
    }
	
    if( get_option('oqey_noflash_options')=="injsarrtouch" ){ 
	
       $incolums = "off"; 
	    $optouch  = "on";  
	               
       if(get_option("oqey_backlinks")=="on"){  
            
           $custom_height = $custom_height + 25;    

       }
       
       if(get_option('oqey_gall_title_no')=="on"){

           $custom_height = $custom_height + 25;
           $margin_top    = $margin_top + 25;        

       }  
            
       $custom_height   = $custom_height + $img_holder_h."px";
       $custom_height_n = $custom_height;
    
    }
    
	    $margleft = $oqey_width - 44;
        
    if(get_option('oqey_flash_gallery_true')){ $pfv = "on"; }else{ $pfv = "off"; }
    
    $custom_margin_top = "";
    
    if( !empty($atts['custombgwidth']) && !empty($atts['custombgheight']) ){
        
        $oqey_width_n      = (int)$atts['custombgwidth']; 
        $custom_height_n   = (int)$atts['custombgheight'];  
        $margleft          = $oqey_width_n-44;      
        $custom_margin_top = "margin-top:35px;";
        
    }
    
   /*Custom words - set arrows*/ 
   $arrows=="on";
   $arrows = $atts['arrows'];
   
   if($arrows=="off"){
    
    $arrowleft  = "";
    $arrowtight = "";    
    
   }else{
    
    $arrowleft = '<div style="position:absolute;left:0px;top:'.$margin_top.'px;z-index:99999;" class="gall_links">
                   <a id="prev'.$oqeycounter.'" href="#back" style="text-decoration:none;" onclick="pausePlayer();">
                    <img alt="" class="larrowjs" src="'.$oqey_gallery_url.'/images/arrow-left.png" style="width:44px;height:94px;border:none;cursor:pointer;cursor:hand"/>
                   </a>
                  </div>';
    
    $arrowtight = '<div style="position:absolute;left:'.$margleft.'px;top:'.$margin_top.'px;z-index:99999;" class="gall_links">
                    <a id="next'.$oqeycounter.'" href="#next" style="text-decoration:none;" onclick="pausePlayer();">
                     <img alt="" class="rarrowjs" src="'.$oqey_gallery_url.'/images/arrow-right.png" style="width:44px; height:94px; border:none;cursor:pointer;cursor:hand"/>
                    </a>
                   </div>';
    
   }
   
   if($custom_height_n!="auto"){
  
     $img_holder_h = $img_holder_h."px";

   }   
   
   $oqeyblogid = oqey_getBFolder($wpdb->blogid);
 
ob_start();	
print <<< SWF
<div class="responsive_oqey" id="oqey_image_div{$oqeycounter}" style="position:relative;width:{$oqey_width_n}px;height:{$custom_height_n};display:none;margin: 0 auto;{$div_custom_margin}{$custom_bg_img}">
{$arrowleft}{$arrowtight}{$galtitle}
<div id="image{$oqeycounter}" style="width:{$c_width}px;height:{$img_holder_h};display:none;background:transparent;margin: 0 auto;{$custom_margin_top}" class="oqey_images"></div>
{$oqeybacklink}
</div>
<script type="text/javascript">
      var flashvars{$oqeycounter} = {
                          autoplay:"{$oqey_autoplay}",
                           flashId:"{$oqeycounter}",
		                      FKey:"{$skin->comkey}",
	                   GalleryPath:"{$plugin_url_qu}",	
                         GalleryID:"{$id}-{$post->ID}",
					      FirstRun:"{$skin->firstrun}"
					 };
	var params{$oqeycounter} = {bgcolor:"{$oqey_bgcolor}", allowFullScreen:"true", wMode:"transparent"};
	var attributes{$oqeycounter} = {id: "oqeygallery{$oqeycounter}"};
	swfobject.embedSWF("{$plugin_repo_url}/skins/{$oqeyblogid}{$skin->folder}/{$skin->folder}.swf", "flash_gal_{$oqeycounter}", "{$oqey_width}", "{$oqey_height}", "8.0.0", "", flashvars{$oqeycounter}, params{$oqeycounter}, attributes{$oqeycounter});
</script> 
<div id="flash_gal_{$oqeycounter}" style="width:{$oqey_width}px; min-width:{$oqey_width}px; min-height:{$oqey_height}px; height:{$oqey_height}px; margin: 0 auto;">
<script type="text/javascript">
  jQuery(document).ready(function($){ 
    var pv = swfobject.getFlashPlayerVersion();
    oqey_e(pv, {$oqeycounter}, '{$imgs}', '{$optouch}', '{$incolums}', '{$pfv}', '{$allimages}');
    });
    var htmlPlayer = document.getElementsByTagName('video');
    function pausePlayer(){ for(var i = 0; i < htmlPlayer.length; i++){htmlPlayer[i].pause();} }</script></div>
SWF;
$output = ob_get_contents();
ob_end_clean();
$oqeycounter ++;
return $output;
}
}//end crawler check
}
}

?>