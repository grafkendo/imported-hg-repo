<?php
/**
 * Remoter WP Admin functions and customizations
 */

// Replace login logo 
function remoter_login_logo() { ?>
    <style>
        #login h1 a, .login h1 a {
            background-image: url(<?php echo get_stylesheet_directory_uri(); ?>/images/remoter-login-icon.svg);
        }
    </style>
<?php }
add_action( 'login_enqueue_scripts', 'remoter_login_logo' );

// Replace login link
function remoter_login_logo_url() {
    return home_url();
}
add_filter( 'login_headerurl', 'remoter_login_logo_url' );

// Replace title
function remoter_login_logo_url_title() {
    return 'Remoter';
}
add_filter( 'login_headertitle', 'remoter_login_logo_url_title' );