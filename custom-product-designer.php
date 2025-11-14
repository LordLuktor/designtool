<?php
/**
 * Plugin Name: Custom Product Designer for WooCommerce
 * Plugin URI: https://github.com/LordLuktor/designtool
 * Description: A powerful product customization tool that allows customers to design custom products like t-shirts, cups, caps, and more with text, images, shapes, and effects.
 * Version: 1.0.0
 * Author: Custom Design Tool
 * Author URI: https://github.com/LordLuktor
 * Text Domain: custom-product-designer
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define('CPD_VERSION', '1.0.0');
define('CPD_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CPD_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CPD_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Custom Product Designer Class
 */
class Custom_Product_Designer {

    /**
     * Single instance of the class
     */
    protected static $_instance = null;

    /**
     * Main Instance
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
        $this->includes();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Check if WooCommerce is active
        add_action('plugins_loaded', array($this, 'check_dependencies'));

        // Initialize plugin
        add_action('init', array($this, 'init'));

        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

        // Add custom tab to product page
        add_filter('woocommerce_product_data_tabs', array($this, 'add_product_data_tab'));
        add_action('woocommerce_product_data_panels', array($this, 'add_product_data_panel'));
        add_action('woocommerce_process_product_meta', array($this, 'save_product_data'));

        // Add designer button on product page
        add_action('woocommerce_before_add_to_cart_button', array($this, 'add_designer_button'));

        // Ajax handlers
        add_action('wp_ajax_cpd_save_design', array($this, 'ajax_save_design'));
        add_action('wp_ajax_nopriv_cpd_save_design', array($this, 'ajax_save_design'));
        add_action('wp_ajax_cpd_upload_image', array($this, 'ajax_upload_image'));
        add_action('wp_ajax_nopriv_cpd_upload_image', array($this, 'ajax_upload_image'));

        // Cart and order hooks
        add_filter('woocommerce_add_cart_item_data', array($this, 'add_cart_item_data'), 10, 2);
        add_filter('woocommerce_get_item_data', array($this, 'display_cart_item_data'), 10, 2);
        add_action('woocommerce_checkout_create_order_line_item', array($this, 'add_order_item_meta'), 10, 4);
        add_action('woocommerce_order_item_meta_end', array($this, 'display_order_item_meta'), 10, 3);
    }

    /**
     * Include required files
     */
    private function includes() {
        require_once CPD_PLUGIN_DIR . 'includes/class-cpd-admin.php';
        require_once CPD_PLUGIN_DIR . 'includes/class-cpd-frontend.php';
        require_once CPD_PLUGIN_DIR . 'includes/class-cpd-ajax.php';
        require_once CPD_PLUGIN_DIR . 'includes/class-cpd-cart.php';
    }

    /**
     * Check plugin dependencies
     */
    public function check_dependencies() {
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            deactivate_plugins(CPD_PLUGIN_BASENAME);
            return false;
        }
        return true;
    }

    /**
     * WooCommerce missing notice
     */
    public function woocommerce_missing_notice() {
        echo '<div class="error"><p><strong>' .
             esc_html__('Custom Product Designer requires WooCommerce to be installed and active.', 'custom-product-designer') .
             '</strong></p></div>';
    }

    /**
     * Initialize plugin
     */
    public function init() {
        // Load text domain
        load_plugin_textdomain('custom-product-designer', false, dirname(CPD_PLUGIN_BASENAME) . '/languages');

        // Create uploads directory
        $this->create_uploads_directory();
    }

    /**
     * Create uploads directory
     */
    private function create_uploads_directory() {
        $upload_dir = wp_upload_dir();
        $cpd_upload_dir = $upload_dir['basedir'] . '/custom-product-designs';

        if (!file_exists($cpd_upload_dir)) {
            wp_mkdir_p($cpd_upload_dir);

            // Add .htaccess for security
            $htaccess_file = $cpd_upload_dir . '/.htaccess';
            if (!file_exists($htaccess_file)) {
                file_put_contents($htaccess_file, 'Options -Indexes');
            }
        }
    }

    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_frontend_scripts() {
        if (is_product()) {
            // Fabric.js for canvas manipulation
            wp_enqueue_script('fabricjs', 'https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js', array(), '5.3.0', true);

            // Google Fonts
            wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Open+Sans:wght@400;700&family=Lato:wght@400;700&family=Montserrat:wght@400;700&family=Oswald:wght@400;700&family=Raleway:wght@400;700&family=Poppins:wght@400;700&family=Playfair+Display:wght@400;700&display=swap', array(), null);

            // Plugin styles
            wp_enqueue_style('cpd-frontend', CPD_PLUGIN_URL . 'assets/css/frontend.css', array(), CPD_VERSION);

            // Plugin scripts
            wp_enqueue_script('cpd-frontend', CPD_PLUGIN_URL . 'assets/js/frontend.js', array('jquery', 'fabricjs'), CPD_VERSION, true);

            // Localize script
            wp_localize_script('cpd-frontend', 'cpdData', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('cpd_nonce'),
                'plugin_url' => CPD_PLUGIN_URL,
                'fonts' => $this->get_available_fonts(),
                'clipart' => $this->get_clipart_list(),
                'i18n' => array(
                    'add_text' => __('Add Text', 'custom-product-designer'),
                    'add_image' => __('Add Image', 'custom-product-designer'),
                    'add_shape' => __('Add Shape', 'custom-product-designer'),
                    'upload_image' => __('Upload Image', 'custom-product-designer'),
                    'delete' => __('Delete', 'custom-product-designer'),
                    'save_design' => __('Save Design', 'custom-product-designer'),
                    'design_saved' => __('Design saved successfully!', 'custom-product-designer'),
                    'error' => __('An error occurred. Please try again.', 'custom-product-designer'),
                )
            ));
        }
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if ('post.php' === $hook || 'post-new.php' === $hook) {
            wp_enqueue_style('cpd-admin', CPD_PLUGIN_URL . 'assets/css/admin.css', array(), CPD_VERSION);
            wp_enqueue_script('cpd-admin', CPD_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), CPD_VERSION, true);
        }
    }

    /**
     * Add custom product data tab
     */
    public function add_product_data_tab($tabs) {
        $tabs['custom_designer'] = array(
            'label' => __('Product Designer', 'custom-product-designer'),
            'target' => 'custom_designer_options',
            'class' => array('show_if_simple', 'show_if_variable'),
            'priority' => 25,
        );
        return $tabs;
    }

    /**
     * Add custom product data panel
     */
    public function add_product_data_panel() {
        global $post;
        ?>
        <div id="custom_designer_options" class="panel woocommerce_options_panel">
            <div class="options_group">
                <?php
                woocommerce_wp_checkbox(array(
                    'id' => '_enable_designer',
                    'label' => __('Enable Designer', 'custom-product-designer'),
                    'description' => __('Enable product customization for this product', 'custom-product-designer'),
                ));

                woocommerce_wp_checkbox(array(
                    'id' => '_designer_enable_back',
                    'label' => __('Enable Back Design', 'custom-product-designer'),
                    'description' => __('Allow customers to design both front and back', 'custom-product-designer'),
                ));

                woocommerce_wp_text_input(array(
                    'id' => '_designer_extra_cost',
                    'label' => __('Extra Cost ($)', 'custom-product-designer'),
                    'description' => __('Additional cost for custom design', 'custom-product-designer'),
                    'type' => 'number',
                    'custom_attributes' => array(
                        'step' => '0.01',
                        'min' => '0',
                    ),
                ));

                woocommerce_wp_text_input(array(
                    'id' => '_designer_canvas_width',
                    'label' => __('Canvas Width (px)', 'custom-product-designer'),
                    'description' => __('Width of the design canvas', 'custom-product-designer'),
                    'type' => 'number',
                    'custom_attributes' => array(
                        'step' => '1',
                        'min' => '100',
                    ),
                    'value' => get_post_meta($post->ID, '_designer_canvas_width', true) ?: '600',
                ));

                woocommerce_wp_text_input(array(
                    'id' => '_designer_canvas_height',
                    'label' => __('Canvas Height (px)', 'custom-product-designer'),
                    'description' => __('Height of the design canvas', 'custom-product-designer'),
                    'type' => 'number',
                    'custom_attributes' => array(
                        'step' => '1',
                        'min' => '100',
                    ),
                    'value' => get_post_meta($post->ID, '_designer_canvas_height', true) ?: '600',
                ));

                woocommerce_wp_text_input(array(
                    'id' => '_designer_product_image',
                    'label' => __('Product Background Image URL', 'custom-product-designer'),
                    'description' => __('URL of the product image to use as background', 'custom-product-designer'),
                    'type' => 'url',
                ));
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Save custom product data
     */
    public function save_product_data($post_id) {
        $enable_designer = isset($_POST['_enable_designer']) ? 'yes' : 'no';
        update_post_meta($post_id, '_enable_designer', $enable_designer);

        $enable_back = isset($_POST['_designer_enable_back']) ? 'yes' : 'no';
        update_post_meta($post_id, '_designer_enable_back', $enable_back);

        if (isset($_POST['_designer_extra_cost'])) {
            update_post_meta($post_id, '_designer_extra_cost', sanitize_text_field($_POST['_designer_extra_cost']));
        }

        if (isset($_POST['_designer_canvas_width'])) {
            update_post_meta($post_id, '_designer_canvas_width', absint($_POST['_designer_canvas_width']));
        }

        if (isset($_POST['_designer_canvas_height'])) {
            update_post_meta($post_id, '_designer_canvas_height', absint($_POST['_designer_canvas_height']));
        }

        if (isset($_POST['_designer_product_image'])) {
            update_post_meta($post_id, '_designer_product_image', esc_url_raw($_POST['_designer_product_image']));
        }
    }

    /**
     * Add designer button on product page
     */
    public function add_designer_button() {
        global $product;

        if (get_post_meta($product->get_id(), '_enable_designer', true) === 'yes') {
            echo '<button type="button" id="cpd-open-designer" class="button cpd-designer-btn">' .
                 esc_html__('Customize Product', 'custom-product-designer') . '</button>';
        }
    }

    /**
     * Get available fonts
     */
    private function get_available_fonts() {
        return array(
            'Roboto',
            'Open Sans',
            'Lato',
            'Montserrat',
            'Oswald',
            'Raleway',
            'Poppins',
            'Playfair Display',
            'Arial',
            'Helvetica',
            'Times New Roman',
            'Georgia',
            'Verdana',
        );
    }

    /**
     * Get clipart list
     */
    private function get_clipart_list() {
        $clipart_dir = CPD_PLUGIN_DIR . 'assets/clipart/';
        $clipart_url = CPD_PLUGIN_URL . 'assets/clipart/';
        $clipart = array();

        if (is_dir($clipart_dir)) {
            $files = scandir($clipart_dir);
            foreach ($files as $file) {
                if (in_array(pathinfo($file, PATHINFO_EXTENSION), array('png', 'svg', 'jpg'))) {
                    $clipart[] = $clipart_url . $file;
                }
            }
        }

        return $clipart;
    }

    /**
     * Ajax save design
     */
    public function ajax_save_design() {
        check_ajax_referer('cpd_nonce', 'nonce');

        $design_data = isset($_POST['design_data']) ? json_decode(stripslashes($_POST['design_data']), true) : array();
        $design_image = isset($_POST['design_image']) ? $_POST['design_image'] : '';

        // Save design data to session or temporary storage
        WC()->session->set('cpd_design_data', $design_data);
        WC()->session->set('cpd_design_image', $design_image);

        wp_send_json_success(array('message' => __('Design saved successfully', 'custom-product-designer')));
    }

    /**
     * Ajax upload image
     */
    public function ajax_upload_image() {
        check_ajax_referer('cpd_nonce', 'nonce');

        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        $uploadedfile = $_FILES['image'];
        $upload_overrides = array('test_form' => false);

        // Upload to custom directory
        add_filter('upload_dir', array($this, 'custom_upload_dir'));
        $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
        remove_filter('upload_dir', array($this, 'custom_upload_dir'));

        if ($movefile && !isset($movefile['error'])) {
            wp_send_json_success(array('url' => $movefile['url']));
        } else {
            wp_send_json_error(array('message' => $movefile['error']));
        }
    }

    /**
     * Custom upload directory
     */
    public function custom_upload_dir($upload) {
        $upload['subdir'] = '/custom-product-designs';
        $upload['path'] = $upload['basedir'] . $upload['subdir'];
        $upload['url'] = $upload['baseurl'] . $upload['subdir'];
        return $upload;
    }

    /**
     * Add cart item data
     */
    public function add_cart_item_data($cart_item_data, $product_id) {
        if (isset($_POST['cpd_design_data'])) {
            $cart_item_data['cpd_design_data'] = sanitize_text_field($_POST['cpd_design_data']);
            $cart_item_data['cpd_design_image'] = sanitize_text_field($_POST['cpd_design_image']);
            $cart_item_data['unique_key'] = md5(microtime() . rand());
        }
        return $cart_item_data;
    }

    /**
     * Display cart item data
     */
    public function display_cart_item_data($item_data, $cart_item) {
        if (isset($cart_item['cpd_design_image'])) {
            $item_data[] = array(
                'key' => __('Custom Design', 'custom-product-designer'),
                'value' => '<img src="' . esc_url($cart_item['cpd_design_image']) . '" style="max-width: 100px;" />',
                'display' => '',
            );
        }
        return $item_data;
    }

    /**
     * Add order item meta
     */
    public function add_order_item_meta($item, $cart_item_key, $values, $order) {
        if (isset($values['cpd_design_data'])) {
            $item->add_meta_data('_cpd_design_data', $values['cpd_design_data'], true);
            $item->add_meta_data('_cpd_design_image', $values['cpd_design_image'], true);
        }
    }

    /**
     * Display order item meta
     */
    public function display_order_item_meta($item_id, $item, $order) {
        $design_image = $item->get_meta('_cpd_design_image');
        if ($design_image) {
            echo '<div class="cpd-order-design"><strong>' .
                 esc_html__('Custom Design:', 'custom-product-designer') .
                 '</strong><br><img src="' . esc_url($design_image) . '" style="max-width: 200px;" /></div>';
        }
    }
}

/**
 * Main instance of Custom Product Designer
 */
function CPD() {
    return Custom_Product_Designer::instance();
}

// Initialize the plugin
CPD();
