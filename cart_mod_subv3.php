<?php
/**
 * Plugin Name:WooCommerce Custom Cart Item Data
 * Description: How To Add Custom Cart Item Data in WooCommerce.
 * Plugin URI: https://webkul.com/
 * Version: 1.0.0
 * Author: Webkul
 * Author URI: https://webkul.com/
 * Text Domain: webkul
 */
/*
 * Add a custom input field to the product page.
 */
function wk_add_text_field() { ?>
    <div class="custom-field-wrap" style="margin: 10px;">
        <label for="custom-field"><?php esc_html_e( 'Quote', 'webkul' ); ?></label>
        <input type="text" name='custom-field' id='custom-field' value=''>
    </div>
    <?php
}
add_action( 'woocommerce_before_add_to_cart_button', 'wk_add_text_field' );

/**
 * Validate custom input field value
 */
function wk_add_to_cart_validation( $passed, $product_id, $quantity, $variation_id = null ) {
    if ( empty( $_POST['custom-field'] ) ) {
        $passed = false;
        wc_add_notice( __( 'Quote is a required field.', 'webkul' ), 'error' );
    }
    return $passed;
}
add_filter( 'woocommerce_add_to_cart_validation', 'wk_add_to_cart_validation', 10, 4 );

/**
 * Add custom cart item data
 */
function wk_add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {
    if ( isset( $_POST['custom-field'] ) ) {
        $cart_item_data['pr_field'] = sanitize_text_field( $_POST['custom-field'] );
    }
    return $cart_item_data;
}
add_filter( 'woocommerce_add_cart_item_data', 'wk_add_cart_item_data', 10, 3 );

/**
 * Display custom item data in the cart
 */
function wk_get_item_data( $item_data, $cart_item_data ) {
    if ( isset( $cart_item_data['pr_field'] ) ) {
        $item_data[] = array(
            'key'   => __( 'Quote', 'webkul' ),
            'value' => wc_clean( $cart_item_data['pr_field'] ),
        );
    }
    return $item_data;
}
add_filter( 'woocommerce_get_item_data', 'wk_get_item_data', 10, 2 );

/**
 * Add custom meta to order
 */
function wk_checkout_create_order_line_item( $item, $cart_item_key, $values, $order ) {
    if ( isset( $values['pr_field'] ) ) {
        $item->add_meta_data(
            __( 'Quote', 'webkul' ),
            $values['pr_field'],
            true
        );
    }
}
add_action( 'woocommerce_checkout_create_order_line_item', 'wk_checkout_create_order_line_item', 10, 4 );
?>
