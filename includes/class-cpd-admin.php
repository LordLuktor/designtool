<?php
/**
 * Admin functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class CPD_Admin {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Product Designer', 'custom-product-designer'),
            __('Product Designer', 'custom-product-designer'),
            'manage_options',
            'custom-product-designer',
            array($this, 'admin_page'),
            'dashicons-edit',
            56
        );

        add_submenu_page(
            'custom-product-designer',
            __('Settings', 'custom-product-designer'),
            __('Settings', 'custom-product-designer'),
            'manage_options',
            'custom-product-designer',
            array($this, 'admin_page')
        );

        add_submenu_page(
            'custom-product-designer',
            __('Clipart Manager', 'custom-product-designer'),
            __('Clipart Manager', 'custom-product-designer'),
            'manage_options',
            'cpd-clipart-manager',
            array($this, 'clipart_manager_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('cpd_settings', 'cpd_canvas_bg_color');
        register_setting('cpd_settings', 'cpd_canvas_text_color');
        register_setting('cpd_settings', 'cpd_button_color');
        register_setting('cpd_settings', 'cpd_enable_text');
        register_setting('cpd_settings', 'cpd_enable_images');
        register_setting('cpd_settings', 'cpd_enable_shapes');
        register_setting('cpd_settings', 'cpd_enable_clipart');
        register_setting('cpd_settings', 'cpd_enable_effects');
        register_setting('cpd_settings', 'cpd_max_upload_size');
        register_setting('cpd_settings', 'cpd_allowed_image_types');
        register_setting('cpd_settings', 'cpd_designer_heading');
    }

    /**
     * Admin page
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Custom Product Designer Settings', 'custom-product-designer'); ?></h1>

            <form method="post" action="options.php">
                <?php settings_fields('cpd_settings'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="cpd_designer_heading">
                                <?php echo esc_html__('Designer Heading', 'custom-product-designer'); ?>
                            </label>
                        </th>
                        <td>
                            <input type="text" id="cpd_designer_heading" name="cpd_designer_heading"
                                   value="<?php echo esc_attr(get_option('cpd_designer_heading', 'Design Your Product')); ?>"
                                   class="regular-text" />
                            <p class="description">
                                <?php echo esc_html__('The heading displayed in the designer interface', 'custom-product-designer'); ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="cpd_canvas_bg_color">
                                <?php echo esc_html__('Canvas Background Color', 'custom-product-designer'); ?>
                            </label>
                        </th>
                        <td>
                            <input type="color" id="cpd_canvas_bg_color" name="cpd_canvas_bg_color"
                                   value="<?php echo esc_attr(get_option('cpd_canvas_bg_color', '#ffffff')); ?>" />
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="cpd_canvas_text_color">
                                <?php echo esc_html__('Default Text Color', 'custom-product-designer'); ?>
                            </label>
                        </th>
                        <td>
                            <input type="color" id="cpd_canvas_text_color" name="cpd_canvas_text_color"
                                   value="<?php echo esc_attr(get_option('cpd_canvas_text_color', '#000000')); ?>" />
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="cpd_button_color">
                                <?php echo esc_html__('Button Color', 'custom-product-designer'); ?>
                            </label>
                        </th>
                        <td>
                            <input type="color" id="cpd_button_color" name="cpd_button_color"
                                   value="<?php echo esc_attr(get_option('cpd_button_color', '#0073aa')); ?>" />
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <?php echo esc_html__('Enable Features', 'custom-product-designer'); ?>
                        </th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" name="cpd_enable_text" value="1"
                                           <?php checked(get_option('cpd_enable_text', 1), 1); ?> />
                                    <?php echo esc_html__('Text', 'custom-product-designer'); ?>
                                </label><br>

                                <label>
                                    <input type="checkbox" name="cpd_enable_images" value="1"
                                           <?php checked(get_option('cpd_enable_images', 1), 1); ?> />
                                    <?php echo esc_html__('Image Upload', 'custom-product-designer'); ?>
                                </label><br>

                                <label>
                                    <input type="checkbox" name="cpd_enable_shapes" value="1"
                                           <?php checked(get_option('cpd_enable_shapes', 1), 1); ?> />
                                    <?php echo esc_html__('Shapes', 'custom-product-designer'); ?>
                                </label><br>

                                <label>
                                    <input type="checkbox" name="cpd_enable_clipart" value="1"
                                           <?php checked(get_option('cpd_enable_clipart', 1), 1); ?> />
                                    <?php echo esc_html__('Clipart', 'custom-product-designer'); ?>
                                </label><br>

                                <label>
                                    <input type="checkbox" name="cpd_enable_effects" value="1"
                                           <?php checked(get_option('cpd_enable_effects', 1), 1); ?> />
                                    <?php echo esc_html__('Visual Effects', 'custom-product-designer'); ?>
                                </label>
                            </fieldset>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="cpd_max_upload_size">
                                <?php echo esc_html__('Max Upload Size (MB)', 'custom-product-designer'); ?>
                            </label>
                        </th>
                        <td>
                            <input type="number" id="cpd_max_upload_size" name="cpd_max_upload_size"
                                   value="<?php echo esc_attr(get_option('cpd_max_upload_size', 5)); ?>"
                                   min="1" max="20" step="1" />
                            <p class="description">
                                <?php echo esc_html__('Maximum file size for image uploads', 'custom-product-designer'); ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="cpd_allowed_image_types">
                                <?php echo esc_html__('Allowed Image Types', 'custom-product-designer'); ?>
                            </label>
                        </th>
                        <td>
                            <input type="text" id="cpd_allowed_image_types" name="cpd_allowed_image_types"
                                   value="<?php echo esc_attr(get_option('cpd_allowed_image_types', 'jpg,jpeg,png,gif')); ?>"
                                   class="regular-text" />
                            <p class="description">
                                <?php echo esc_html__('Comma-separated list of allowed image extensions', 'custom-product-designer'); ?>
                            </p>
                        </td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Clipart manager page
     */
    public function clipart_manager_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Clipart Manager', 'custom-product-designer'); ?></h1>

            <div class="cpd-clipart-upload">
                <h2><?php echo esc_html__('Upload New Clipart', 'custom-product-designer'); ?></h2>

                <form method="post" enctype="multipart/form-data" action="">
                    <?php wp_nonce_field('cpd_upload_clipart', 'cpd_clipart_nonce'); ?>

                    <input type="file" name="cpd_clipart_file" accept="image/*" />
                    <input type="submit" name="cpd_upload_clipart" class="button button-primary"
                           value="<?php echo esc_attr__('Upload Clipart', 'custom-product-designer'); ?>" />
                </form>

                <?php
                if (isset($_POST['cpd_upload_clipart']) && check_admin_referer('cpd_upload_clipart', 'cpd_clipart_nonce')) {
                    $this->handle_clipart_upload();
                }
                ?>
            </div>

            <div class="cpd-clipart-list">
                <h2><?php echo esc_html__('Existing Clipart', 'custom-product-designer'); ?></h2>
                <?php $this->display_clipart_list(); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Handle clipart upload
     */
    private function handle_clipart_upload() {
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        $uploadedfile = $_FILES['cpd_clipart_file'];
        $upload_overrides = array('test_form' => false);

        // Create clipart directory if it doesn't exist
        $clipart_dir = CPD_PLUGIN_DIR . 'assets/clipart/';
        if (!file_exists($clipart_dir)) {
            wp_mkdir_p($clipart_dir);
        }

        $filename = sanitize_file_name($uploadedfile['name']);
        $destination = $clipart_dir . $filename;

        if (move_uploaded_file($uploadedfile['tmp_name'], $destination)) {
            echo '<div class="notice notice-success"><p>' .
                 esc_html__('Clipart uploaded successfully!', 'custom-product-designer') . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>' .
                 esc_html__('Failed to upload clipart.', 'custom-product-designer') . '</p></div>';
        }
    }

    /**
     * Display clipart list
     */
    private function display_clipart_list() {
        $clipart_dir = CPD_PLUGIN_DIR . 'assets/clipart/';
        $clipart_url = CPD_PLUGIN_URL . 'assets/clipart/';

        if (!is_dir($clipart_dir)) {
            echo '<p>' . esc_html__('No clipart found.', 'custom-product-designer') . '</p>';
            return;
        }

        $files = scandir($clipart_dir);
        $has_clipart = false;

        echo '<div class="cpd-clipart-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px; margin-top: 20px;">';

        foreach ($files as $file) {
            if (in_array(pathinfo($file, PATHINFO_EXTENSION), array('png', 'svg', 'jpg', 'jpeg', 'gif'))) {
                $has_clipart = true;
                $file_url = $clipart_url . $file;
                ?>
                <div class="cpd-clipart-item" style="border: 1px solid #ddd; padding: 10px; text-align: center;">
                    <img src="<?php echo esc_url($file_url); ?>" alt="<?php echo esc_attr($file); ?>"
                         style="max-width: 100%; height: auto; max-height: 100px;" />
                    <p style="margin: 10px 0 0; font-size: 12px; word-break: break-all;">
                        <?php echo esc_html($file); ?>
                    </p>
                    <form method="post" style="margin-top: 5px;">
                        <?php wp_nonce_field('cpd_delete_clipart', 'cpd_delete_nonce'); ?>
                        <input type="hidden" name="cpd_clipart_file" value="<?php echo esc_attr($file); ?>" />
                        <button type="submit" name="cpd_delete_clipart" class="button button-small button-link-delete"
                                onclick="return confirm('<?php echo esc_js(__('Are you sure?', 'custom-product-designer')); ?>')">
                            <?php echo esc_html__('Delete', 'custom-product-designer'); ?>
                        </button>
                    </form>
                </div>
                <?php
            }
        }

        echo '</div>';

        if (!$has_clipart) {
            echo '<p>' . esc_html__('No clipart found.', 'custom-product-designer') . '</p>';
        }

        // Handle deletion
        if (isset($_POST['cpd_delete_clipart']) && check_admin_referer('cpd_delete_clipart', 'cpd_delete_nonce')) {
            $file = sanitize_file_name($_POST['cpd_clipart_file']);
            $file_path = $clipart_dir . $file;
            if (file_exists($file_path)) {
                unlink($file_path);
                echo '<div class="notice notice-success"><p>' .
                     esc_html__('Clipart deleted successfully!', 'custom-product-designer') . '</p></div>';
                echo '<meta http-equiv="refresh" content="0">';
            }
        }
    }
}

new CPD_Admin();
