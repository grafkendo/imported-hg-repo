<?php

/* 
 * uses 'image' postmeta field injected by other posts transfer script to set new theme's featured image
 */

require_once('../../../wp-load.php');
require_once('../../../wp-admin/includes/image.php');

if(!isset($_GET["scode"]) || $_GET["scode"] != "dAr5jE3oK") exit;

$args = array(
 'posts_per_page'=>'-1',//get all posts
 'post_type' => array('post')
 );

$allPosts = new WP_Query();
$allPosts->query($args);

while ($allPosts->have_posts()){
    $allPosts->the_post();
    global $post;
    
    $postID = get_the_ID();
    
    if (has_post_thumbnail($postID)) {
        continue;//skip if featured image exists
    }

    $url = get_post_meta( $postID, 'image', true );
    $filename = basename($url);

    $uploadDir = wp_upload_dir();

    $file = $uploadDir['path'] . '/' . $filename;

    $wp_filetype = wp_check_filetype( $filename, null );

    $args = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title'     => sanitize_file_name( $filename ),
        'post_content'   => '',
        'post_status'    => 'inherit'
    );

    // Create the attachment
    $attachmentID = wp_insert_attachment( $args, $file, $postID );

    // Define attachment metadata
    $attachmentMetadata = wp_generate_attachment_metadata( $attachmentID, $file );

    // Assign metadata to attachment
    wp_update_attachment_metadata( $attachmentID, $attachmentMetadata );

    // And finally assign featured image to post
    set_post_thumbnail( $postID, $attachmentID );

}