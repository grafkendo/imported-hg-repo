<?php
/**
 * RSS2 Feed Template for displaying RSS2 Posts feed.
 *
 * @package WordPress
 */

$domain = "www.cheatsheet.com";

//function update_domain($content, $domain){
//$replace = $domain;
//$searches = array("wallstcheatsheet.com");
//return str_replace($searches, $replace, $content);
//}

/**
 * Add https to all cs link
 */
function add_secure($content){
	return str_replace("http://www.cheatsheet.com", "https://www.cheatsheet.com", $content);
}

/*
* get image type based on image src
*/

function get_img_type($imgsrc){
$imgtype = substr($imgsrc, strrpos($imgsrc, ".")+1);
if($imgtype == "jpg") $imgtype = "jpeg";
if($imgtype) return 'type="image/'.$imgtype.'"';
else return "";
}

/**
 * get the tag list of current post
 * */
//function get_cs_tag_list($posttags){
//$tag_names = array();
//if ($posttags) {
//  foreach($posttags as $tag) {
//    array_push($tag_names, $tag->name); 
//  }
//}
//return implode(",", $tag_names);
//}

remove_all_shortcodes();

echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>'; ?>

<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
	xmlns:media="http://search.yahoo.com/mrss/"
	<?php do_action('rss2_ns'); ?>
>
<channel>
	<title><?php bloginfo_rss('name'); ?></title>
	<atom:link href="https://<?php echo $domain; ?>/secure/feed/onetacle/" rel="self" type="application/rss+xml" />
	<link>https://<?php echo $domain; ?></link>
	<description><?php bloginfo_rss("description") ?></description>
	<lastBuildDate><?php echo $feedPosts->post_count > 0? $feedPosts->posts[0]->post_date_gmt.' +0000' : ''; ?></lastBuildDate>
	<language><?php bloginfo_rss( 'language' ); ?></language>
	<sy:updatePeriod><?php echo apply_filters( 'rss_update_period', 'hourly' ); ?></sy:updatePeriod>
	<sy:updateFrequency><?php echo apply_filters( 'rss_update_frequency', '1' ); ?></sy:updateFrequency>
	<?php do_action('rss2_head'); ?>
<?php
while ($feedPosts->have_posts()) : $feedPosts->the_post();
global $post;
$title = get_the_title();
if($title=="Auto Draft") continue;
//remove escape charactars from title
$title = stripcslashes($title);
$image = str_replace("http://wallstcheatsheet.com", "https://www.cheatsheet.com", get_post_meta( get_the_ID(), 'image', true ));
$imgtype = get_img_type($image);
$post_id = get_the_ID();

?>
	<item>
	<?php if ( strlen( $image ) > 0 ) : ?>
		<media:thumbnail url="<?php echo str_replace("http:", "https:", $image); ?>" <?php echo $imgtype ?> medium="image" />
	<?php endif; ?>
		<title><?php echo $title; ?></title>
		<slug><?php echo $post->post_name;  ?></slug>
		<pubDate><?php echo get_post_time('Y-m-d H:i:s', true); ?> +0000</pubDate>
		<!--<dc:creator><?php //echo get_the_author_meta("display_name")."|".get_the_author_meta("user_login") ?></dc:creator>-->
		<guid isPermaLink="false">https://<?php echo $domain."/?p=".$post_id; ?></guid>
<?php if (get_option('rss_use_excerpt')) : ?>
		<description><![CDATA[<?php the_excerpt_rss(); ?>]]></description>
<?php else : ?>
		<description><![CDATA[<?php the_excerpt_rss(); ?>]]></description>
	<?php $content = add_secure($post->post_content);?>
	<?php if ( strlen( $content ) > 0 ) : ?>
		<content:encoded><![CDATA[<?php echo $content; ?>]]></content:encoded>
	<?php else : ?>
		<content:encoded></content:encoded>
	<?php endif; ?>
<?php endif; ?>
		<!--<tags><?php //echo get_cs_tag_list( get_the_tags() ); ?></tags>-->
		<yoastTitle><![CDATA[<?php echo get_post_meta( $post_id, '_yoast_wpseo_title', true ); ?>]]></yoastTitle>
		<yoastKws><![CDATA[<?php echo get_post_meta( $post_id, '_yoast_wpseo_focuskw', true )."|".get_post_meta( $post_id, '_yoast_wpseo_focuskeywords', true ); ?>]]></yoastKws>
		<!--<asPid><?php //echo get_post_meta( $post_id, '_post_asid', true ); ?></asPid>-->
<!--		<wfw:commentRss><?php //echo $link; ?>feed</wfw:commentRss>
		<slash:comments>0</slash:comments>-->
                <categoryID><?php echo $theNewCatID; ?></categoryID>
<?php rss_enclosure(); ?>
	<?php //do_action('rss2_item'); ?>
	</item>
	<?php endwhile; wp_reset_postdata(); ?>
</channel>
</rss>