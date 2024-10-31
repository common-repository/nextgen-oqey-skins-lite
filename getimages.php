<?php
include ("../../../wp-load.php");
global $wpdb;  

if(isset($_REQUEST['gal_id'])){

   $oqey_galls = $wpdb->prefix . "ngg_gallery";
   $oqey_images = $wpdb->prefix . "ngg_pictures";

   $data = explode("-", $_REQUEST['gal_id']);
   $id = esc_sql( $data[0] );
   $pid = $data[1];//post id
   $s = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $oqey_galls WHERE gid = %d ", $id ) );
   $nggpath = $s->path;

   $gthmb = get_option('siteurl').'/wp-content/oqey_gallery/galleries/'.oqey_getBFolder($wpdb->blogid).$s->folder.'/galthmb/';
   $gimg = get_option('siteurl').'/wp-content/oqey_gallery/galleries/'.oqey_getBFolder($wpdb->blogid).$s->folder.'/galimg/';


   if(!empty($m[0])){
    
     $gthmb2 = "";
     $gimg2  = "";
     
   }else{
    
     $gthmb2   = $gthmb;
     $gimg2    = $gimg;
     $gthmbnew = "";
     $gimgnew  = "";
     
  }

     $bg   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $oqey_images WHERE galleryid = %d ORDER BY sortorder ASC LIMIT 0,1 ", $id ) );
     $imgs = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $oqey_images WHERE galleryid = %d ORDER BY sortorder ASC", $id ) );         

     $bg_image = get_option('siteurl').'/'.trim($nggpath).'/'.trim($bg->filename);   

   
   header('Content-Type: ' . feed_content_type('rss-http') . '; charset=' . get_option('blog_charset'), true);
   $r .= '<?xml version="1.0" encoding="UTF-8"?>';
   $r .= '<oqeygallery bgpath="'.$bg_image.'" galtitle="'.urlencode($s->title).'" path="" imgPath="">'; 
    
   foreach($imgs as $i) { 
        
       $gthmbnew = get_option('siteurl').'/'.trim($nggpath).'/thumbs/thumbs_';
       $gimgnew  = get_option('siteurl').'/'.trim($nggpath).'/';   

      
       $r .= '<item>';
       $r .= '<thumb file="'.$gthmbnew.trim($i->filename).'" alt="'.urlencode(trim($i->alttext)).'" comments="'.urlencode(trim($i->description)).'" link=""/>';
       $r .= '<image file="'.$gimgnew.trim($i->filename).'" alt="'.urlencode(trim($i->alttext)).'" comments="'.urlencode(trim($i->description)).'" link="">';
       
       /*if($_REQUEST['withexif']=="true"){
        
        $r .= '<exif>';
                $exif = json_decode($i->meta_data);
             
             if(!empty($exif->Make)){ 
                
                $r .='<parametru name="Make" value="'.urlencode($exif->Make).'" />';
             
             }
             if(!empty($exif->Model)){
                
               $r .='<parametru name="Model" value="'.urlencode($exif->Model).'" />';
             
             }
             if(!empty($exif->DateTime)){
                
               $r .='<parametru name="DateTime" value="'.urlencode($exif->DateTime).'" />';
             
             }
             if(!empty($exif->Software)){
                
               $r .='<parametru name="Software" value="'.urlencode($exif->Software).'" />';
             
             }
             if(!empty($exif->Artist)){
                
               $r .='<parametru name="Artist" value="'.urlencode($exif->Artist).'" />';
             
             }
             if(!empty($exif->ExposureTime)){
                
               $r .='<parametru name="ExposureTime" value="'.urlencode($exif->ExposureTime).'" />';
             
             }
             if(!empty($exif->FNumber)){ 
                
                $r .='<parametru name="FNumber" value="'.urlencode($exif->FNumber).'" />';
             
             }
             if(!empty($exif->ExposureProgram)){ 
                
                $r .='<parametru name="ExposureProgram" value="'.urlencode($exif->ExposureProgram).'" />';
             
             }
             if(!empty($exif->ISOSpeedRatings)){ 
                
                $r .='<parametru name="ISOSpeedRatings" value="'.urlencode($exif->ISOSpeedRatings).'" />';
             
             }
             if(!empty($exif->COMPUTED->CCDWidth)){ 
                
                $r .='<parametru name="CCDWidth" value="'.urlencode($exif->COMPUTED->CCDWidth).'" />';
             
             }
             $exif = '';
        
        $r .= '</exif>';       
                
       }*/
       $r .= '</image>';
       $r .= '</item>';
       
   
    
 }

   $r .= '</oqeygallery>';
   
   echo $r;
  
  }else{ 
    
    die();
    
}
?>