<?php
/**
 * Remoter Theme functions and customizations
 */

// Logo
function remoter_logo() {
    return '<a href="'. esc_url( home_url( '/' ) ) .'" class="custom-logo-link" rel="home" itemprop="url"><img  src="'. get_stylesheet_directory_uri() . '/images/logo.svg" class="remoter-logo-svg" alt="Remoter" itemprop="logo"></a>';
}

// Favicon
function remoter_favicon() { 
?>
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo get_stylesheet_directory_uri(); ?>/images/favicon.png">
<?php
}
add_action( 'blossom_fashion_before_wp_head', 'remoter_favicon', 15 );

// Datalayer
function remoter_datalayer() { 
?>
<script>
	dataLayer = [{'is_single': <?php echo is_single()? 1: 0; ?>}];
</script>
<?php	
}
add_action( 'wp_head', 'remoter_datalayer', 8 );