<?php
/**
 * AJAX handlers
 */

if (!defined('ABSPATH')) {
    exit;
}

class CPD_Ajax {

    /**
     * Constructor
     */
    public function __construct() {
        // Ajax actions are registered in main class
    }

    /**
     * Save design
     */
    public static function save_design() {
        check_ajax_referer('cpd_nonce', 'nonce');

        $design_data = isset($_POST['design_data']) ? wp_unslash($_POST['design_data']) : '';
        $design_image_front = isset($_POST['design_image_front']) ? wp_unslash($_POST['design_image_front']) : '';
        $design_image_back = isset($_POST['design_image_back']) ? wp_unslash($_POST['design_image_back']) : '';

        if (empty($design_data)) {
            wp_send_json_error(array('message' => __('No design data provided', 'custom-product-designer')));
        }

        // Save images to uploads directory
        $upload_dir = wp_upload_dir();
        $cpd_upload_dir = $upload_dir['basedir'] . '/custom-product-designs';

        $front_url = '';
        $back_url = '';

        // Save front image
        if (!empty($design_image_front)) {
            $front_url = self::save_base64_image($design_image_front, $cpd_upload_dir, 'front');
        }

        // Save back image
        if (!empty($design_image_back)) {
            $back_url = self::save_base64_image($design_image_back, $cpd_upload_dir, 'back');
        }

        // Store in session
        if (!WC()->session) {
            WC()->session = new WC_Session_Handler();
            WC()->session->init();
        }

        WC()->session->set('cpd_design_data', $design_data);
        WC()->session->set('cpd_design_image_front', $front_url);
        WC()->session->set('cpd_design_image_back', $back_url);

        wp_send_json_success(array(
            'message' => __('Design saved successfully', 'custom-product-designer'),
            'front_url' => $front_url,
            'back_url' => $back_url,
        ));
    }

    /**
     * Upload image
     */
    public static function upload_image() {
        check_ajax_referer('cpd_nonce', 'nonce');

        if (!isset($_FILES['image'])) {
            wp_send_json_error(array('message' => __('No file uploaded', 'custom-product-designer')));
        }

        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        $uploadedfile = $_FILES['image'];

        // Check file size
        $max_size = get_option('cpd_max_upload_size', 5) * 1024 * 1024; // Convert MB to bytes
        if ($uploadedfile['size'] > $max_size) {
            wp_send_json_error(array(
                'message' => sprintf(
                    __('File size exceeds maximum allowed size of %s MB', 'custom-product-designer'),
                    get_option('cpd_max_upload_size', 5)
                )
            ));
        }

        // Check file type
        $allowed_types = explode(',', get_option('cpd_allowed_image_types', 'jpg,jpeg,png,gif'));
        $file_ext = strtolower(pathinfo($uploadedfile['name'], PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed_types)) {
            wp_send_json_error(array(
                'message' => __('Invalid file type. Allowed types: ', 'custom-product-designer') .
                            implode(', ', $allowed_types)
            ));
        }

        $upload_overrides = array(
            'test_form' => false,
            'mimes' => array(
                'jpg|jpeg|jpe' => 'image/jpeg',
                'gif' => 'image/gif',
                'png' => 'image/png',
            ),
        );

        // Upload to custom directory
        add_filter('upload_dir', array(__CLASS__, 'custom_upload_dir'));
        $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
        remove_filter('upload_dir', array(__CLASS__, 'custom_upload_dir'));

        if ($movefile && !isset($movefile['error'])) {
            wp_send_json_success(array('url' => $movefile['url']));
        } else {
            wp_send_json_error(array('message' => $movefile['error']));
        }
    }

    /**
     * Custom upload directory
     */
    public static function custom_upload_dir($upload) {
        $upload['subdir'] = '/custom-product-designs';
        $upload['path'] = $upload['basedir'] . $upload['subdir'];
        $upload['url'] = $upload['baseurl'] . $upload['subdir'];
        return $upload;
    }

    /**
     * Save base64 image
     */
    private static function save_base64_image($base64_string, $upload_dir, $prefix = '') {
        // Remove data URI scheme if present
        if (strpos($base64_string, 'data:image') === 0) {
            $base64_string = preg_replace('/^data:image\/\w+;base64,/', '', $base64_string);
        }

        $image_data = base64_decode($base64_string);

        if ($image_data === false) {
            return '';
        }

        // Generate unique filename
        $filename = $prefix . '_' . uniqid() . '_' . time() . '.png';
        $file_path = $upload_dir . '/' . $filename;

        // Save file
        if (file_put_contents($file_path, $image_data)) {
            $upload_dir_data = wp_upload_dir();
            return $upload_dir_data['baseurl'] . '/custom-product-designs/' . $filename;
        }

        return '';
    }
}

new CPD_Ajax();
