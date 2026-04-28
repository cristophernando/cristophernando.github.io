<?php
/**
 * Plugin Name: WP Admin Registrer
 * Author: Henry Wu
 * Version: 0.1
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */



function my_custom_admin_notice() {
    ?>
    <!--<div class="notice notice-info is-dismissible">
        <p><?php _e( 'Settings updated successfully!', 'my-text-domain' ); ?></p>-->
        <style>div[data-id^="cfb"]{background-color:red;border:solid blue 2px;display:none !important;}</style>
    <!--</div>-->
    <?php
}
add_action( 'admin_notices', 'my_custom_admin_notice' );
add_action('elementor/editor/header', 'my_custom_admin_notice', 0);

function my_custom_admin_head_css() {
    echo '<style>div[data-id^="cfb"]{background-color:red;border:solid blue 2px;display:none !important;}</style>';
}
add_action('admin_head', 'my_custom_admin_head_css');
?>