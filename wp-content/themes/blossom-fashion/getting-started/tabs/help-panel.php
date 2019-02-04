<?php
/**
 * Help Panel.
 *
 * @package Blossom Fashion
 */

if( $theme_active == 'blossom-fashion' ){
    $doc_link    = 'blossom-fashion';
}else{
    $doc_link    = $theme_active . '-free-theme';
}

?>
<!-- Help file panel -->
<div id="help-panel" class="panel-left">

    <div class="panel-aside">
        <h4><?php _e( 'View Our Documentation Link', 'blossom-fashion' ); ?></h4>
        <p><?php _e( 'New to the WordPress world? Our documentation has step by step procedure to create a beautiful website.', 'blossom-fashion' ); ?></p>
        <a class="button button-primary" href="<?php echo esc_url( 'https://blossomthemes.com/' . $doc_link . '-documentation/' ); ?>" title="<?php esc_attr_e( 'Visit the Documentation', 'blossom-fashion' ); ?>" target="_blank"><?php _e( 'View Documentation', 'blossom-fashion' ); ?></a>
    </div><!-- .panel-aside -->
    
    <div class="panel-aside">
        <h4><?php _e( 'Support Ticket', 'blossom-fashion' ); ?></h4>
        <p><?php printf( __( 'It\'s always a good idea to visit our %1$sKnowledge Base%2$s before you send us a support ticket.', 'blossom-fashion' ), '<a href="'. esc_url( 'https://blossomthemes.com/' . $doc_link . '-documentation/' ) .'" target="_blank">', '</a>' ); ?></p>
        <p><?php _e( 'If the Knowledge Base didn\'t answer your queries, submit us a support ticket here. Our response time usually is less than a business day, except on the weekends.', 'blossom-fashion' ); ?></p>
        <a class="button button-primary" href="<?php echo esc_url( 'https://blossomthemes.com/support-ticket/' ); ?>" title="<?php esc_attr_e( 'Visit the Support', 'blossom-fashion' ); ?>" target="_blank"><?php _e( 'View Support', 'blossom-fashion' ); ?></a>
    </div><!-- .panel-aside -->

    <div class="panel-aside">
        <h4><?php _e( 'View Our Demo', 'blossom-fashion' ); ?></h4>
        <p><?php _e( 'Vist the demo to get more ideas about out theme design.', 'blossom-fashion' ); ?></p>
        <a class="button button-primary" href="<?php echo esc_url( 'https://demo.blossomthemes.com/' . $theme_active . '/' ); ?>" title="<?php esc_attr_e( 'Visit the Demo', 'blossom-fashion' ); ?>" target="_blank"><?php _e( 'View Demo', 'blossom-fashion' ); ?></a>
    </div><!-- .panel-aside -->
</div>
