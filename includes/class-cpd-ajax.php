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

        // Check for upload errors
        if (isset($_FILES['image']['error']) && $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $error_messages = array(
                UPLOAD_ERR_INI_SIZE => __('The uploaded file exceeds the upload_max_filesize directive in php.ini', 'custom-product-designer'),
                UPLOAD_ERR_FORM_SIZE => __('The uploaded file exceeds the MAX_FILE_SIZE directive', 'custom-product-designer'),
                UPLOAD_ERR_PARTIAL => __('The uploaded file was only partially uploaded', 'custom-product-designer'),
                UPLOAD_ERR_NO_FILE => __('No file was uploaded', 'custom-product-designer'),
                UPLOAD_ERR_NO_TMP_DIR => __('Missing a temporary folder', 'custom-product-designer'),
                UPLOAD_ERR_CANT_WRITE => __('Failed to write file to disk', 'custom-product-designer'),
                UPLOAD_ERR_EXTENSION => __('A PHP extension stopped the file upload', 'custom-product-designer'),
            );
            $error_message = isset($error_messages[$_FILES['image']['error']])
                ? $error_messages[$_FILES['image']['error']]
                : __('Unknown upload error', 'custom-product-designer');
            wp_send_json_error(array('message' => $error_message));
        }

        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        $uploadedfile = $_FILES['image'];

        // Validate file exists
        if (empty($uploadedfile['tmp_name']) || !is_uploaded_file($uploadedfile['tmp_name'])) {
            wp_send_json_error(array('message' => __('Invalid file upload', 'custom-product-designer')));
        }

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

        // Check for empty file
        if ($uploadedfile['size'] == 0) {
            wp_send_json_error(array('message' => __('The uploaded file is empty', 'custom-product-designer')));
        }

        // Check file type
        $allowed_types = explode(',', get_option('cpd_allowed_image_types', 'jpg,jpeg,png,gif'));
        $allowed_types = array_map('trim', $allowed_types);
        $file_ext = strtolower(pathinfo($uploadedfile['name'], PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed_types)) {
            wp_send_json_error(array(
                'message' => __('Invalid file type. Allowed types: ', 'custom-product-designer') .
                            implode(', ', $allowed_types)
            ));
        }

        // Validate MIME type
        if (function_exists('mime_content_type')) {
            $mime_type = mime_content_type($uploadedfile['tmp_name']);
            $allowed_mimes = array('image/jpeg', 'image/jpg', 'image/png', 'image/gif');

            if (!in_array($mime_type, $allowed_mimes)) {
                wp_send_json_error(array('message' => __('Invalid file type detected', 'custom-product-designer')));
            }
        }

        // Ensure upload directory exists
        $upload_dir = wp_upload_dir();
        $cpd_upload_dir = $upload_dir['basedir'] . '/custom-product-designs';

        if (!file_exists($cpd_upload_dir)) {
            if (!wp_mkdir_p($cpd_upload_dir)) {
                wp_send_json_error(array('message' => __('Failed to create upload directory', 'custom-product-designer')));
            }
        }

        // Check if directory is writable
        if (!is_writable($cpd_upload_dir)) {
            wp_send_json_error(array('message' => __('Upload directory is not writable', 'custom-product-designer')));
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
            $error_msg = isset($movefile['error']) ? $movefile['error'] : __('Unknown error during file upload', 'custom-product-designer');
            wp_send_json_error(array('message' => $error_msg));
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

        // Ensure upload directory exists
        if (!file_exists($upload_dir)) {
            if (!wp_mkdir_p($upload_dir)) {
                error_log('CPD: Failed to create upload directory: ' . $upload_dir);
                return '';
            }
        }

        // Check if directory is writable
        if (!is_writable($upload_dir)) {
            error_log('CPD: Upload directory is not writable: ' . $upload_dir);
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

        error_log('CPD: Failed to save base64 image to: ' . $file_path);
        return '';
    }
}

new CPD_Ajax();
