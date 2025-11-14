<?php
/**
 * Cart and checkout functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class CPD_Cart {

    /**
     * Constructor
     */
    public function __construct() {
        add_filter('woocommerce_add_to_cart_validation', array($this, 'validate_add_to_cart'), 10, 3);
        add_filter('woocommerce_add_cart_item_data', array($this, 'add_cart_item_data'), 10, 3);
        add_filter('woocommerce_get_cart_item_from_session', array($this, 'get_cart_item_from_session'), 10, 2);
        add_filter('woocommerce_get_item_data', array($this, 'display_cart_item_data'), 10, 2);
        add_action('woocommerce_before_calculate_totals', array($this, 'add_custom_price'), 10, 1);
        add_action('woocommerce_checkout_create_order_line_item', array($this, 'add_order_item_meta'), 10, 4);
        add_action('woocommerce_order_item_meta_end', array($this, 'display_order_item_meta'), 10, 3);
        add_action('woocommerce_admin_order_item_headers', array($this, 'admin_order_item_headers'));
        add_action('woocommerce_admin_order_item_values', array($this, 'admin_order_item_values'), 10, 3);
    }

    /**
     * Validate add to cart
     */
    public function validate_add_to_cart($passed, $product_id, $quantity) {
        $enable_designer = get_post_meta($product_id, '_enable_designer', true);

        if ($enable_designer === 'yes') {
            // Check if design data is present
            if (!isset($_POST['cpd_design_data']) || empty($_POST['cpd_design_data'])) {
                wc_add_notice(__('Please customize the product before adding to cart.', 'custom-product-designer'), 'error');
                return false;
            }
        }

        return $passed;
    }

    /**
     * Add cart item data
     */
    public function add_cart_item_data($cart_item_data, $product_id, $variation_id) {
        $enable_designer = get_post_meta($product_id, '_enable_designer', true);

        if ($enable_designer === 'yes') {
            // Get design data from session
            if (WC()->session) {
                $design_data = WC()->session->get('cpd_design_data');
                $design_image_front = WC()->session->get('cpd_design_image_front');
                $design_image_back = WC()->session->get('cpd_design_image_back');

                if ($design_data) {
                    $cart_item_data['cpd_design_data'] = $design_data;
                    $cart_item_data['cpd_design_image_front'] = $design_image_front;
                    $cart_item_data['cpd_design_image_back'] = $design_image_back;
                    $cart_item_data['unique_key'] = md5(microtime() . rand());

                    // Clear session data
                    WC()->session->set('cpd_design_data', null);
                    WC()->session->set('cpd_design_image_front', null);
                    WC()->session->set('cpd_design_image_back', null);
                }
            }
        }

        return $cart_item_data;
    }

    /**
     * Get cart item from session
     */
    public function get_cart_item_from_session($cart_item, $values) {
        if (isset($values['cpd_design_data'])) {
            $cart_item['cpd_design_data'] = $values['cpd_design_data'];
            $cart_item['cpd_design_image_front'] = $values['cpd_design_image_front'];
            $cart_item['cpd_design_image_back'] = $values['cpd_design_image_back'];
        }

        return $cart_item;
    }

    /**
     * Display cart item data
     */
    public function display_cart_item_data($item_data, $cart_item) {
        if (isset($cart_item['cpd_design_image_front'])) {
            $html = '<div class="cpd-cart-preview">';

            if (!empty($cart_item['cpd_design_image_front'])) {
                $html .= '<div class="cpd-preview-item">';
                $html .= '<strong>' . __('Front Design:', 'custom-product-designer') . '</strong><br>';
                $html .= '<img src="' . esc_url($cart_item['cpd_design_image_front']) . '" style="max-width: 150px; height: auto; margin-top: 5px;" />';
                $html .= '</div>';
            }

            if (!empty($cart_item['cpd_design_image_back'])) {
                $html .= '<div class="cpd-preview-item" style="margin-top: 10px;">';
                $html .= '<strong>' . __('Back Design:', 'custom-product-designer') . '</strong><br>';
                $html .= '<img src="' . esc_url($cart_item['cpd_design_image_back']) . '" style="max-width: 150px; height: auto; margin-top: 5px;" />';
                $html .= '</div>';
            }

            $html .= '</div>';

            $item_data[] = array(
                'key' => __('Custom Design', 'custom-product-designer'),
                'value' => $html,
                'display' => '',
            );
        }

        return $item_data;
    }

    /**
     * Add custom price
     */
    public function add_custom_price($cart) {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        foreach ($cart->get_cart() as $cart_item) {
            if (isset($cart_item['cpd_design_data'])) {
                $product_id = $cart_item['product_id'];
                $extra_cost = get_post_meta($product_id, '_designer_extra_cost', true);

                if ($extra_cost && $extra_cost > 0) {
                    $price = $cart_item['data']->get_price();
                    $new_price = $price + floatval($extra_cost);
                    $cart_item['data']->set_price($new_price);
                }
            }
        }
    }

    /**
     * Add order item meta
     */
    public function add_order_item_meta($item, $cart_item_key, $values, $order) {
        if (isset($values['cpd_design_data'])) {
            $item->add_meta_data('_cpd_design_data', $values['cpd_design_data'], true);

            if (!empty($values['cpd_design_image_front'])) {
                $item->add_meta_data('_cpd_design_image_front', $values['cpd_design_image_front'], true);
            }

            if (!empty($values['cpd_design_image_back'])) {
                $item->add_meta_data('_cpd_design_image_back', $values['cpd_design_image_back'], true);
            }
        }
    }

    /**
     * Display order item meta on order details
     */
    public function display_order_item_meta($item_id, $item, $order) {
        $design_image_front = $item->get_meta('_cpd_design_image_front');
        $design_image_back = $item->get_meta('_cpd_design_image_back');

        if ($design_image_front || $design_image_back) {
            echo '<div class="cpd-order-design" style="margin-top: 10px;">';

            if ($design_image_front) {
                echo '<div class="cpd-design-preview">';
                echo '<strong>' . esc_html__('Front Design:', 'custom-product-designer') . '</strong><br>';
                echo '<img src="' . esc_url($design_image_front) . '" style="max-width: 200px; height: auto; margin-top: 5px; border: 1px solid #ddd; padding: 5px;" />';
                echo '</div>';
            }

            if ($design_image_back) {
                echo '<div class="cpd-design-preview" style="margin-top: 10px;">';
                echo '<strong>' . esc_html__('Back Design:', 'custom-product-designer') . '</strong><br>';
                echo '<img src="' . esc_url($design_image_back) . '" style="max-width: 200px; height: auto; margin-top: 5px; border: 1px solid #ddd; padding: 5px;" />';
                echo '</div>';
            }

            echo '</div>';
        }
    }

    /**
     * Admin order item headers
     */
    public function admin_order_item_headers() {
        echo '<th class="cpd-design-preview">' . esc_html__('Custom Design', 'custom-product-designer') . '</th>';
    }

    /**
     * Admin order item values
     */
    public function admin_order_item_values($product, $item, $item_id) {
        $design_image_front = $item->get_meta('_cpd_design_image_front');
        $design_image_back = $item->get_meta('_cpd_design_image_back');

        echo '<td class="cpd-design-preview">';

        if ($design_image_front || $design_image_back) {
            if ($design_image_front) {
                echo '<div><strong>' . esc_html__('Front:', 'custom-product-designer') . '</strong><br>';
                echo '<a href="' . esc_url($design_image_front) . '" target="_blank">';
                echo '<img src="' . esc_url($design_image_front) . '" style="max-width: 80px; height: auto;" />';
                echo '</a></div>';
            }

            if ($design_image_back) {
                echo '<div style="margin-top: 5px;"><strong>' . esc_html__('Back:', 'custom-product-designer') . '</strong><br>';
                echo '<a href="' . esc_url($design_image_back) . '" target="_blank">';
                echo '<img src="' . esc_url($design_image_back) . '" style="max-width: 80px; height: auto;" />';
                echo '</a></div>';
            }

            // Download links
            echo '<div style="margin-top: 5px;">';
            if ($design_image_front) {
                echo '<a href="' . esc_url($design_image_front) . '" download class="button button-small">' .
                     esc_html__('Download Front', 'custom-product-designer') . '</a> ';
            }
            if ($design_image_back) {
                echo '<a href="' . esc_url($design_image_back) . '" download class="button button-small">' .
                     esc_html__('Download Back', 'custom-product-designer') . '</a>';
            }
            echo '</div>';
        } else {
            echo '-';
        }

        echo '</td>';
    }
}

new CPD_Cart();
