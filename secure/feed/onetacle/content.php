<?php
/**
 * RSS2 Feed Template for displaying RSS2 Posts feed.
 *
 * @package WordPress
 */

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

if(!isset($_GET["thecsvcat"]) || !in_array($_GET["thecsvcat"], array_keys($theCatsMap))) exit;

define('WP_USE_THEMES', false);
require('../../../wp-load.php');
date_default_timezone_set('America/New_York');
// Get yesterday date to avoid taxing the db in custom query
//$ydate = explode("-", date("Y-m-d", time()-24*3600));
/*$date_query = array(
        'after' => array(
            'year'  => $ydate[0],
            'month' => $ydate[1],
            'day'   => $ydate[2]
        )
);*/


//extract post IDs from CSV of URLs provided by Hunter
$csvArr = array_map('str_getcsv', file("onetacleURLs_{$_GET["thecsvcat"]}.csv"));

array_shift($csvArr);//remove header row from csv

$postIDsArr = array();

$ch = curl_init();
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

foreach ($csvArr as $csvElement) {
    $postid = url_to_postid( $csvElement[0] );//first column of csv data holding URL portion
    if ($postid) {
        array_push($postIDsArr, intval($postid));
    } else {
        curl_setopt($ch, CURLOPT_URL, "https://www.cheatsheet.com".$csvElement[0]);
        curl_exec($ch);
        $last_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $redirect_url = explode("www.cheatsheet.com", $last_url);
        $postid = url_to_postid($redirect_url[1]);
        if ($postid) {
            array_push($postIDsArr, intval($postid));
        }
    }
    //array_push($postIDsArr, array($postid, $csvElement[0]));
    
}

curl_close($ch);

//echo"<pre>";print_r($postIDsArr);echo"</pre>";
//exit;


$args = array(
 'posts_per_page'=>'-1',//get all posts
 //'date_query' => $date_query,
 //'order_by' => 'post_date',
 //'order' => 'DESC',
 //'meta_key' => 'amsp_feed',
 //'meta_value' => 'on',
 'post_type' => array('post'),
 'post__in'      => $postIDsArr
 //'post_status' => array('publish', 'future')
 );

$theNewCatID = $theCatsMap[$_GET["thecsvcat"]];

$feedPosts = new WP_Query(); $feedPosts->query($args);//automatically filters any duplicate IDs in post__in

//header("Content-disposition: attachment; filename=onetacleURLs_{$_GET["thecsvcat"]}.xml");
//header ("Content-Type:text/xml");
ob_start();

include('feed-as-rss2.php');

//header("Expires: 0");
$htmlStr = ob_get_contents();
ob_end_clean();
file_put_contents("onetacleURLs_{$_GET["thecsvcat"]}.xml", $htmlStr);
?>
