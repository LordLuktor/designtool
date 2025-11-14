<?php
/**
 * Uninstall Custom Product Designer
 *
 * Removes all plugin data when plugin is deleted
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Remove plugin options
delete_option('cpd_canvas_bg_color');
delete_option('cpd_canvas_text_color');
delete_option('cpd_button_color');
delete_option('cpd_enable_text');
delete_option('cpd_enable_images');
delete_option('cpd_enable_shapes');
delete_option('cpd_enable_clipart');
delete_option('cpd_enable_effects');
delete_option('cpd_max_upload_size');
delete_option('cpd_allowed_image_types');
delete_option('cpd_designer_heading');

// Remove product meta
global $wpdb;

// Use prepared statements to prevent SQL injection
$wpdb->query($wpdb->prepare(
    "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s OR meta_key LIKE %s",
    '_enable\_designer%',
    '\_designer\_%'
));

// Remove order meta - use proper escaping
$wpdb->query($wpdb->prepare(
    "DELETE FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE meta_key LIKE %s",
    '\_cpd\_%'
));

// Optionally remove uploaded designs
// Uncomment the following lines if you want to delete all design files on uninstall
/*
$upload_dir = wp_upload_dir();
$cpd_upload_dir = $upload_dir['basedir'] . '/custom-product-designs';

if (is_dir($cpd_upload_dir)) {
    // Delete all files in directory
    $files = glob($cpd_upload_dir . '/*');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }

    // Delete .htaccess
    if (file_exists($cpd_upload_dir . '/.htaccess')) {
        unlink($cpd_upload_dir . '/.htaccess');
    }

    // Remove directory
    rmdir($cpd_upload_dir);
}
*/
