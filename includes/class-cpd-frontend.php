<?php
/**
 * Frontend functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class CPD_Frontend {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_footer', array($this, 'designer_modal'));
    }

    /**
     * Output designer modal
     */
    public function designer_modal() {
        if (!is_product()) {
            return;
        }

        global $product;
        if (!$product || get_post_meta($product->get_id(), '_enable_designer', true) !== 'yes') {
            return;
        }

        $enable_back = get_post_meta($product->get_id(), '_designer_enable_back', true) === 'yes';
        $canvas_width = get_post_meta($product->get_id(), '_designer_canvas_width', true) ?: 600;
        $canvas_height = get_post_meta($product->get_id(), '_designer_canvas_height', true) ?: 600;
        $product_image = get_post_meta($product->get_id(), '_designer_product_image', true);

        if (!$product_image) {
            $image_id = $product->get_image_id();
            if ($image_id) {
                $product_image = wp_get_attachment_image_url($image_id, 'full');
            }
        }
        ?>
        <div id="cpd-designer-modal" class="cpd-modal" style="display: none;">
            <div class="cpd-modal-content">
                <span class="cpd-close">&times;</span>

                <h2 id="cpd-designer-heading">
                    <?php echo esc_html(get_option('cpd_designer_heading', 'Design Your Product')); ?>
                </h2>

                <div class="cpd-designer-container">
                    <!-- Toolbar -->
                    <div class="cpd-toolbar">
                        <?php if (get_option('cpd_enable_text', 1)): ?>
                        <div class="cpd-tool-section">
                            <h3><?php echo esc_html__('Text', 'custom-product-designer'); ?></h3>
                            <button id="cpd-add-text" class="cpd-btn">
                                <?php echo esc_html__('Add Text', 'custom-product-designer'); ?>
                            </button>

                            <div id="cpd-text-options" class="cpd-options" style="display: none;">
                                <label>
                                    <?php echo esc_html__('Text:', 'custom-product-designer'); ?>
                                    <input type="text" id="cpd-text-input" placeholder="<?php echo esc_attr__('Enter text', 'custom-product-designer'); ?>" />
                                </label>

                                <label>
                                    <?php echo esc_html__('Font:', 'custom-product-designer'); ?>
                                    <select id="cpd-font-family"></select>
                                </label>

                                <label>
                                    <?php echo esc_html__('Size:', 'custom-product-designer'); ?>
                                    <input type="number" id="cpd-font-size" value="24" min="8" max="200" />
                                </label>

                                <label>
                                    <?php echo esc_html__('Color:', 'custom-product-designer'); ?>
                                    <input type="color" id="cpd-text-color" value="#000000" />
                                </label>

                                <div class="cpd-text-style">
                                    <button id="cpd-text-bold" class="cpd-btn-small" title="<?php echo esc_attr__('Bold', 'custom-product-designer'); ?>">
                                        <strong>B</strong>
                                    </button>
                                    <button id="cpd-text-italic" class="cpd-btn-small" title="<?php echo esc_attr__('Italic', 'custom-product-designer'); ?>">
                                        <em>I</em>
                                    </button>
                                    <button id="cpd-text-underline" class="cpd-btn-small" title="<?php echo esc_attr__('Underline', 'custom-product-designer'); ?>">
                                        <u>U</u>
                                    </button>
                                </div>

                                <div class="cpd-text-align">
                                    <button id="cpd-text-left" class="cpd-btn-small" title="<?php echo esc_attr__('Align Left', 'custom-product-designer'); ?>">
                                        ≡
                                    </button>
                                    <button id="cpd-text-center" class="cpd-btn-small" title="<?php echo esc_attr__('Center', 'custom-product-designer'); ?>">
                                        ≡
                                    </button>
                                    <button id="cpd-text-right" class="cpd-btn-small" title="<?php echo esc_attr__('Align Right', 'custom-product-designer'); ?>">
                                        ≡
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (get_option('cpd_enable_images', 1)): ?>
                        <div class="cpd-tool-section">
                            <h3><?php echo esc_html__('Images', 'custom-product-designer'); ?></h3>
                            <button id="cpd-upload-image" class="cpd-btn">
                                <?php echo esc_html__('Upload Image', 'custom-product-designer'); ?>
                            </button>
                            <input type="file" id="cpd-image-upload" accept="image/*" style="display: none;" />
                        </div>
                        <?php endif; ?>

                        <?php if (get_option('cpd_enable_shapes', 1)): ?>
                        <div class="cpd-tool-section">
                            <h3><?php echo esc_html__('Shapes', 'custom-product-designer'); ?></h3>
                            <button class="cpd-add-shape cpd-btn-small" data-shape="rect">
                                <?php echo esc_html__('Rectangle', 'custom-product-designer'); ?>
                            </button>
                            <button class="cpd-add-shape cpd-btn-small" data-shape="circle">
                                <?php echo esc_html__('Circle', 'custom-product-designer'); ?>
                            </button>
                            <button class="cpd-add-shape cpd-btn-small" data-shape="triangle">
                                <?php echo esc_html__('Triangle', 'custom-product-designer'); ?>
                            </button>

                            <div id="cpd-shape-options" class="cpd-options" style="display: none;">
                                <label>
                                    <?php echo esc_html__('Fill Color:', 'custom-product-designer'); ?>
                                    <input type="color" id="cpd-shape-color" value="#ff0000" />
                                </label>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (get_option('cpd_enable_clipart', 1)): ?>
                        <div class="cpd-tool-section">
                            <h3><?php echo esc_html__('Clipart', 'custom-product-designer'); ?></h3>
                            <div id="cpd-clipart-list" class="cpd-clipart-grid"></div>
                        </div>
                        <?php endif; ?>

                        <?php if (get_option('cpd_enable_effects', 1)): ?>
                        <div class="cpd-tool-section">
                            <h3><?php echo esc_html__('Effects', 'custom-product-designer'); ?></h3>
                            <button class="cpd-apply-filter cpd-btn-small" data-filter="grayscale">
                                <?php echo esc_html__('Grayscale', 'custom-product-designer'); ?>
                            </button>
                            <button class="cpd-apply-filter cpd-btn-small" data-filter="sepia">
                                <?php echo esc_html__('Sepia', 'custom-product-designer'); ?>
                            </button>
                            <button class="cpd-apply-filter cpd-btn-small" data-filter="blur">
                                <?php echo esc_html__('Blur', 'custom-product-designer'); ?>
                            </button>
                            <button class="cpd-apply-filter cpd-btn-small" data-filter="invert">
                                <?php echo esc_html__('Invert', 'custom-product-designer'); ?>
                            </button>
                            <button class="cpd-apply-filter cpd-btn-small" data-filter="emboss">
                                <?php echo esc_html__('Emboss', 'custom-product-designer'); ?>
                            </button>
                            <button class="cpd-apply-filter cpd-btn-small" data-filter="sharpen">
                                <?php echo esc_html__('Sharpen', 'custom-product-designer'); ?>
                            </button>
                            <button id="cpd-remove-filters" class="cpd-btn-small">
                                <?php echo esc_html__('Remove Filters', 'custom-product-designer'); ?>
                            </button>
                        </div>
                        <?php endif; ?>

                        <div class="cpd-tool-section">
                            <h3><?php echo esc_html__('Actions', 'custom-product-designer'); ?></h3>
                            <button id="cpd-delete-object" class="cpd-btn cpd-btn-danger">
                                <?php echo esc_html__('Delete Selected', 'custom-product-designer'); ?>
                            </button>
                            <button id="cpd-clear-canvas" class="cpd-btn cpd-btn-warning">
                                <?php echo esc_html__('Clear All', 'custom-product-designer'); ?>
                            </button>
                        </div>
                    </div>

                    <!-- Canvas Area -->
                    <div class="cpd-canvas-wrapper">
                        <?php if ($enable_back): ?>
                        <div class="cpd-side-toggle">
                            <button id="cpd-front-btn" class="cpd-side-btn active">
                                <?php echo esc_html__('Front', 'custom-product-designer'); ?>
                            </button>
                            <button id="cpd-back-btn" class="cpd-side-btn">
                                <?php echo esc_html__('Back', 'custom-product-designer'); ?>
                            </button>
                        </div>
                        <?php endif; ?>

                        <div class="cpd-canvas-container">
                            <canvas id="cpd-canvas-front" width="<?php echo esc_attr($canvas_width); ?>" height="<?php echo esc_attr($canvas_height); ?>"></canvas>
                            <?php if ($enable_back): ?>
                            <canvas id="cpd-canvas-back" width="<?php echo esc_attr($canvas_width); ?>" height="<?php echo esc_attr($canvas_height); ?>" style="display: none;"></canvas>
                            <?php endif; ?>
                        </div>

                        <div class="cpd-canvas-actions">
                            <button id="cpd-save-design" class="cpd-btn cpd-btn-primary cpd-btn-large">
                                <?php echo esc_html__('Save & Add to Cart', 'custom-product-designer'); ?>
                            </button>
                            <button id="cpd-cancel-design" class="cpd-btn cpd-btn-secondary">
                                <?php echo esc_html__('Cancel', 'custom-product-designer'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <input type="hidden" id="cpd-product-image" value="<?php echo esc_url($product_image); ?>" />
        <input type="hidden" id="cpd-enable-back" value="<?php echo $enable_back ? '1' : '0'; ?>" />
        <?php
    }
}

new CPD_Frontend();
