<?php
/*
Plugin Name: Fetch CS Posts Cron Job
Version: 1.0
Author: Xiangyu Fan
Author URI: http://www.cheatsheet.com/
Description: Used to run cron job of fetching editor-picked posts from the feed hosted in CS site. 
*/

set_time_limit(0);

/*register_activation_hook(__FILE__, 'cron_plugin_activation');
register_deactivation_hook(__FILE__, 'cron_plugin_deactivation');

add_filter('cron_schedules', 'new_interval');
// Add once 3 minute interval to wp schedules
function new_interval($interval) {
        $interval['minutes_3'] = array('interval' => 3*60, 'display' => 'Once 3 minutes');
        return $interval;
}

// We can assume that at this point, the cron hook is registered, and we can add a function action to the execution of its hook.
add_action('my_cron', 'do_my_cron');

function cron_plugin_activation () {
        // If our cron hook doesn't yet exist, create it.
        if (!wp_next_scheduled('my_cron')) {
                wp_schedule_event( time(), 'minutes_3', 'my_cron');
        }
}

function cron_plugin_deactivation () {
        // If our cron hook exists. remove it.
        if (wp_next_scheduled('my_cron')) {
                wp_clear_scheduled_hook('my_cron');
        }
}*/

date_default_timezone_set('America/New_York');
$curr_dir = dirname(__FILE__)."/";

$img_upper_dir = $curr_dir."../../../wp-content/uploads/".date("Y");
if(!file_exists($img_upper_dir)) mkdir($img_upper_dir, 0775);
$img_dir = $img_upper_dir.date("/m");
if(!file_exists($img_dir)) mkdir($img_dir, 0775);

define('WP_USE_THEMES', false);
require($curr_dir.'/../../../wp-load.php');
//require_once($curr_dir.'config.php');
$config = array(
        //'api_client_id' => 'amsp',
        //'api_client_secret' => '',
        //'domain_from' => 'https://www.cheatsheet.com',
        'domain_to' => 'http://allsportscasting.com',
        //'user_default_pass' => ''
);

//maps to category ID on new site
$theCatsMap = array(
    "NBA" => "2",
    "MLB" => "4",
    "NCAA" => "7",
    "NFL" => "3",
    "Soccer" => "5",
    "Other" => "10"
);


if(!isset($_GET["scode"]) || $_GET["scode"] != "dAr5jE3oK") exit;

if(!isset($_GET["thexmlcat"]) || !in_array($_GET["thexmlcat"], array_keys($theCatsMap))) exit;

//function do_my_cron() {
	
//global $curr_dir, $config;

// Avoid filtering out tags in inserting post content 
//mod_allowed_tags();

//$cs_last_build = get_option("cs_last_build");
//if(!$cs_last_build) $cs_last_build = "";

//$xml = file_get_contents("http://allsportscasting.com/secure/feed/onetacle/onetacleURLs_{$_GET["thexmlcat"]}.xml");

$doc = new DOMDocument();
$doc->load("onetacleURLs_{$_GET["thexmlcat"]}.xml");//load from manually built feed

if(!$doc) exit;

//$new_build_date = $doc->getElementsByTagName('lastBuildDate')->item(0)->nodeValue;
//if( (!$new_build_date) || $new_build_date <= $cs_last_build) return;

//$api_posts = array();
//$last_cron_log = "";
foreach ($doc->getElementsByTagName('item') as $node) {
        $guidMeta = explode("?p=", $node->getElementsByTagName('guid')->item(0)->nodeValue);
        $cs_post_id = $guidMeta[1];
        $pub_date = $node->getElementsByTagName('pubDate')->item(0)->nodeValue;    
//        if( $pub_date <= $cs_last_build )  break;
		
        $title = $node->getElementsByTagName('title')->item(0)->nodeValue;
        $newCategoryID = intval($node->getElementsByTagName('categoryID')->item(0)->nodeValue);
        $desc = (string) $node->getElementsByTagName('desc')->item(0)->nodeValue;
        $content = (string) $node->getElementsByTagName('encoded')->item(0)->nodeValue;
        $content = preg_replace_callback("/src\=\".*?\"/", "remove_cache_tag", $content);
//        $tags = (string) $node->getElementsByTagName('tags')->item(0)->nodeValue;
        $yoast_title = (string) $node->getElementsByTagName('yoastTitle')->item(0)->nodeValue;
        $yoast_kws = (string) $node->getElementsByTagName('yoastKws')->item(0)->nodeValue;
        $yoast_kwarr = explode("|", $yoast_kws);
        if(empty($title) || empty($content)) continue;
        
        // Remove the links on images
        $regexp = "/\<a.*?\>(\<img.*?\>)\<\/a\>/i";
        $content = preg_replace($regexp, "$1", $content);
        
        //remove page breaks
        $content = preg_replace("/<!--nextpage-->/", "", $content);
        
        // Get post fields
//        $author = $node->getElementsByTagName('creator')->item(0)->nodeValue;
        $post_slug = $node->getElementsByTagName('slug')->item(0)->nodeValue;
        $thumbnail = $node->getElementsByTagName('thumbnail')->item(0);
		$feature_img = $thumbnail ? $thumbnail->getAttribute('url') : '';
		
		// Update post data
        $data = update_content_imgs($content);
        $content = $data[0];
        $images = $data[1];
        fetch_general_images($images, $feature_img);
        if($feature_img){
        	fetch_feature_images($feature_img);
        	$feature_img = update_img_link($feature_img);
        }
        // Build the post to be inserted
        
//        $as_post_id = $node->getElementsByTagName('asPid')->item(0)->nodeValue;  

        $new_post = array(); // initialize new post each time to avoid interaction
        $new_post['post_title'] = $title;
        $new_post['post_category'] = array($newCategoryID); // new Category id mapped from content feed rss
        $new_post['post_excerpt'] = $desc;
        $new_post['post_content'] = $content;
        $new_post['post_name'] = $post_slug;
        $new_post['post_status'] = 'publish';
        $publish_time = strtotime($pub_date) - 300; // keep 5 minutes earlier in publish time
        $new_post['post_date'] = date('Y-m-d H:i:s', $publish_time);
		$new_post['post_date_gmt'] = gmdate('Y-m-d H:i:s', $publish_time);       
//        $new_post['post_author'] = get_author_id($author);      
        $new_post['meta_input'] = array(
              'image' => remove_appendix($feature_img), // when save to db, remove appendix in img url
              'cs_post_id' => $cs_post_id,
        	  '_yoast_wpseo_focuskw' => $yoast_kwarr[0],  
              '_yoast_wpseo_focuskeywords' => $yoast_kwarr[1]       				
        );
        // check whether as post id exist
//        if($as_post_id) {
//        	$new_post['ID'] = (int) $as_post_id;
//        }else{
        	//check whether cs post id exist
                //This will cause post to UPDATE if already exists by tracking cs post id in post meta table
        	$pid = get_cs_post_id_by_meta('cs_post_id', $cs_post_id);
        	if($pid) $new_post['ID'] = $pid;
//        }
        
        $as_post_id = wp_insert_post( $new_post );
//        wp_set_post_tags( $as_post_id, $tags, false );
//        $as_post_slug = get_post_field( 'post_name', $as_post_id);
//        $as_post_link = get_post_link($as_post_slug);
//        array_push($api_posts, array($cs_post_id, $as_post_id, $as_post_link));
//        $last_cron_log .= $cs_post_id."|".$as_post_id."|".$new_post['post_date']."|".$as_post_link;
}

/*if( count($api_posts) >0 ){
	update_option('cs_last_build', $new_build_date);
	@file_put_contents($curr_dir."last_cron_log.txt", $last_cron_log);
	$access_token = get_access_token();	 
	foreach($api_posts as $api_post){
		call_cs_api($access_token, $api_post);
	}
}*/

//}
/**
 * Get post slug from post url
 * */
/*function get_post_slug($post_link){
	$pos1 = strrpos($post_link, ".");
	$pos2 = strrpos(substr($post_link, 0, $pos1), "/");
	if($pos1 && $pos2){
		$pos2++;
		$post_slug = substr($post_link, $pos2, $pos1-$pos2);
	}
	else $post_slug = "";
	return $post_slug;
}*/

/**
 * Get post link from post slug
 * */
/*function get_post_link($slug){
	global $config;
	return $config['domain_to']."/entertainment/$slug.html/";
}*/
/**
 * Get author id based on username, if not existed, create the user 
 * */
/*function get_author_id($author){
	global $config;
	$authorMeta = explode("|", $author);
    $the_user = get_user_by('login', $authorMeta[1]);
    if($the_user){
		$user_id = $the_user->ID;
    }else{
    	$userdata = array(
    	'user_login'  =>  $authorMeta[1],
    	'user_pass'   =>  $config["user_default_pass"],
    	'display_name' => $authorMeta[0],
    	'role' => 'author' 
		);
		$user_id = wp_insert_user( $userdata );	
	}
	return $user_id;
}*/

/**
 * Get general images from CS site
* */
function update_img_link($image){
	global $config;
	$image = preg_replace("/http.*?\/wp\-content\/uploads\/.*?\/.*?\//", $config['domain_to']."/wp-content/uploads/".date("Y/m/"), $image);
	return $image;
}

/**
 * Update the date and domain of image link
* */
function update_content_imgs($content){
	global $config;	
	preg_match_all("/src\=\"(http.*?\/wp\-content\/uploads\/.*?)\"/", $content, $matches);
	$content = preg_replace("/http.*?\/wp\-content\/uploads\/.*?\/.*?\//", $config['domain_to']."/wp-content/uploads/".date("Y/m/"), $content);
	
	return array($content, $matches[1]);
}

/**
 * Get general images from CS site
* */
function fetch_general_images($images, $feature_img){
	global $img_dir;
	foreach($images as $image){
		if($image == $feature_img) continue;
		$imgfile = file_get_contents($image, false, stream_context_create($arrContextOptions));
		$imgname = get_img_name($image);
		file_put_contents($img_dir."/".$imgname, $imgfile);
	}
}
/**
 * Get feature image and its resized versions from CS site
* */
function fetch_feature_images($feature_img){
	global $img_dir;
	$feature_img = rm_img_size($feature_img);
	$sizes = array("", "150x100", "300x200");
	foreach($sizes as $size){
		$resized_img = add_img_size($feature_img, $size);
		$imgname = remove_appendix(get_img_name($resized_img));
		$imgfile = file_get_contents($resized_img, false, stream_context_create($arrContextOptions));
		if($imgfile){
			file_put_contents($img_dir."/".$imgname, $imgfile);
		}else if(strpos($resized_img, "-e1") !== false){ // if img fetch fails and img url has appendix, remove it and re-fetch
				$resized_img = remove_appendix($resized_img);
				$imgfile = file_get_contents($resized_img, false, stream_context_create($arrContextOptions));
				file_put_contents($img_dir."/".$imgname, $imgfile);
		}else;
	}
}
/**
 * Add image size from image src
* */
function add_img_size($image, $size){
	if(!$size) return $image;
	$pos = strrpos($image, ".");
	$end_part = substr($image, $pos);
	return str_replace($end_part, "-".$size.$end_part, $image);
}

/**
 * Remove image size from image src
* */
function rm_img_size($image){
	return preg_replace("/\-(640|300|150)x.*?\./", ".", $image);
}

/**
 * Get image name from image src
 */
function get_img_name($img_src){
	$pos = strrpos($img_src, "/");
	return substr($img_src, $pos);
}

/**
 * Remove appendix from image name
 */
function remove_appendix($imgname){
	// for image name like this: -e1492797565451.jpg
	$imgname = preg_replace("/\-e1.*?\./", ".", $imgname);
	// for image name like this: -e1492797565451-150x100.jpg
	$imgname = preg_replace("/\-e1.*?\-/", "-", $imgname);
	return $imgname;
}
/**
 * Update CS post data accordingly
* */
/*function call_cs_api($access_token, $api_post){	    
	$request_parameters = array(
		'action' => 'update_after_transfer',
		'access_token' => $access_token,
		'post_csid' => $api_post[0], 
		'post_asid' => $api_post[1],
		'post_aslink' => $api_post[2]
	);
	$res = makeRequest('post', 'post_as.php', $request_parameters);
}*/

/**
 * Get valid access token. if not expired, retrieve it from db, otherwise get new token and save to db.
 * */
/*function get_access_token(){
	$token_expire = 3600; //set token expire in 1 hour	
	$access_token = trim(get_option("api_access_token"));
	if($access_token){
		$token_meta = explode("|", $access_token);
		if($token_meta[1] && (time() - $token_meta[0]) < $token_expire) return $token_meta[1];
	}	
	$new_api_token = makeRequest('post', 'token.php', array(
        'grant_type' => 'client_credentials'
    	)); 
    $new_api_token_data = json_decode($new_api_token);
	update_option('api_access_token', time()."|".$new_api_token_data->access_token);
    return $new_api_token_data->access_token;
}*/
/**
* Submits a request to Wordpress, checks for errors, and returns the parsed response.
*     
* @param string $request_type The request type. Can either be 'get' or 'post'.
* @param string $request_page The PHP file to make the request to.
* @param array $request_parameters The request parameters whose values will be url encoded.
* @return mixed The response from Wordpress.
*/
/*function makeRequest($request_type, $request_page, array $request_parameters) { 
    	global $config;  
        $request = curl_init();
        curl_setopt($request, CURLOPT_RETURNTRANSFER, TRUE);
        
        //Add the USERPWD attribute specificaly for authentication token requests
        if($request_page == 'token.php') {
            curl_setopt($request, CURLOPT_USERPWD, $config['api_client_id'].':'.$config['api_client_secret']);  
            //print $config['api_client_id'].':'.$config['api_client_secret'];
        	//print "result";
        }
             
        $request_url = $config['domain_from']."/csapi/".$request_page;        
		if($request_type == 'post') {
            $request_body = '';            
            if(!empty($request_parameters)) {
                $request_body_segments = array();
                
                foreach($request_parameters as $parameter_name => $parameter_value) {
                    $request_body_segments[] = "{$parameter_name}={$parameter_value}";
                }
                
                $request_body = implode('&', $request_body_segments);
                //print $request_body;
            }            
            curl_setopt($request, CURLOPT_POST, true);
            curl_setopt($request, CURLOPT_POSTFIELDS, $request_body);             
        }
        else {
            throw new Exception("Request type '{$request_type}' is not supported. Can either be 'get' or 'post'.");
        }
        
        curl_setopt($request, CURLOPT_URL, $request_url);        
        $response = curl_exec($request);        
        curl_close($request);
        return $response;
}*/

/**
 * Get post id by post meta
* */
function get_cs_post_id_by_meta($key, $value) {
		global $wpdb;
		$meta = $wpdb->get_results("SELECT * FROM `".$wpdb->postmeta."` WHERE meta_key='".$wpdb->escape($key)."' AND meta_value='".$wpdb->escape($value)."'");
		if (is_array($meta) && !empty($meta) && isset($meta[0])) {
			$meta = $meta[0];
		}		
		if (is_object($meta)) {
			return $meta->post_id;
		}
		else {
			return false;
		}
}

/**
 * Set allowed tags to avoid removing tags when inserting post
* */
/*function mod_allowed_tags(){
	global $allowedposttags;
	$allowedposttags['div'] = array('align' => array (), 'class' => array (), 'id' => array (), 'dir' => array (), 'lang' => array(), 'style' => array (), 'xml:lang' => array() );
	$allowedposttags['iframe'] = array('src' => array (), 'style' => array (), 'width' => array (), 'height' => array (), 'frameborder' => array(), 'scrolling' => array());
	$allowedposttags['script'] = array('src' => array (), 'charset' => array (), 'async' => array () );
	//$allowedposttags['img'] = array('class' => array (), 'src' => array (), 'data-src' => array (), 'srcset' => array (), 'sizes' => array(), 'width' => array (), 'height' => array (), 'title' => array (), 'alt' => array () );
}*/

/**
 * Remove cache tag appended to the end of the links
 */
function remove_cache_tag($matches){
	return preg_replace("/\?.*?\"/", '"', $matches[0]);
}
