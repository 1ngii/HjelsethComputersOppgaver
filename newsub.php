<?php
/*
Plugin Name: New Subscription WooCommerce
Author: Helgi Bjarnason
Version: 1.0
Description: Adds subscription as metadata in all product types. A reboot of the WooCommerce Subscription plugin, as the other one does not work as intended.
*/

add_action( 'woocommerce_product_options_general_product_data', 'ingii_option_group' );

function ingii_option_group() {
    echo '<div class="options_group">';
    woocommerce_wp_checkbox([
            'id'    => 'sub',
            'value' => get_post_meta( get_the_ID(), 'subscription_product', true ),
            'label' => 'Subscription product',
            'desc_tip' => true,
            'description' => 'This is a subscription type product with recurring payments'
        ]);

    $subscription_value = get_post_meta( get_the_ID(), 'subscription_options', true );
    echo '<div class="subscription_options" style="display: ' . (get_post_meta( get_the_ID(), 'subscription_product', true ) === 'yes' ? 'block' : 'none') . ';">';
    woocommerce_wp_select([
        'id' => 'subscription_options',
        'label' => __( 'Select a subscription option', 'New Subscription WooCommerce' ),
        'options' => [
            '0' => '1 Month',
            '1' => '3 Months',
            '2' => 'Year'
        ],
        'desc' => __('Choose one option from the dropdown', 'New Subscription WooCommerce'),
        'value' => $subscription_value,
    ]);

    $subscription_price_value = get_post_meta(get_the_ID(), 'subscription_price', true);
    woocommerce_wp_text_input([
        'id'  =>  'subscription_price',
        'label'  =>  'Price (kr)',
        'desc_tip'  =>  true,
        'description'  =>  'Price per month of subscription',
        'value'  =>  $subscription_price_value,
    ]);

    echo '</div>'; //subscription_options

    echo '</div>'; //options_group

    // Enqueue JavaScript to show/hide the select box based on checkbox 
    wc_enqueue_js("
        jQuery('#sub').change(function(){
            if(jQuery(this).is(':checked')) {
                jQuery('.subscription_options').show();
            } else {
                jQuery('.subscription_options').hide();
            }
        }).change(); // Trigger change to set initial state
    ");
}






add_action( 'woocommerce_single_product_summary', 'display_and_change_billing_option', 25 );

function display_and_change_billing_option() {
    global $product;

    $billing_option = get_post_meta( $product->get_id(), 'subscription_product', true );
    $custom_price = get_post_meta( $product->get_id(), 'subscription_price', true );
    $regular_price = $product->get_regular_price(); // Get the regular price

    if ($billing_option === 'yes') {
        echo '<div class="billing-option">';
        echo '<label for="billing_method">Choose your preferred billing method:</label>';
        echo '<select id="billing_method" name="billing_method">';
        echo '<option value="0"' . selected($billing_option, false == "1") . '>Regular price</option>';
        echo '<option value="1"' . selected($custom_price, false < 0) . '>Subscription</option>';
        echo '</select>';
        echo '</div>';

        // Display the price
        echo '<div id="price_display">' . wc_price($regular_price) . '</div>'; // Initial display

        // Enqueue JavaScript for updating the price display
        wc_enqueue_js("
            var regularPrice = " . floatval($regular_price) . ";
            var subscriptionPrice = " . floatval($custom_price) . ";

            jQuery('#billing_method').change(function() {
                var selectedValue = jQuery(this).val();

                if (selectedValue === '0') {
                    jQuery('#price_display').html(regularPrice.toFixed(2) + ' kr');
                } else {
                    jQuery('#price_display').html(subscriptionPrice.toFixed(2) + ' kr');
                }
            });
        ");
    }
}


function replace_default_price_with_custom_price( $price, $product ) {
    $custom_price = get_post_meta( $product->get_id(), 'subscription_price', true );

    if ( ! empty( $custom_price ) && is_numeric( $custom_price ) ) {
        $price = wc_price( $custom_price );
    }

    return $price;
}
function relax(){
    ;
}

add_filter('woocommerce_get_price_html', '__return_empty_string', 10, 2);


add_action( 'woocommerce_process_product_meta', 'ingii_save_field' );

function ingii_save_field( $id ) {
    $subscription = isset( $_POST['sub'] ) && 'yes' === $_POST['sub'] ? 'yes' : 'no';
    update_post_meta( $id, 'subscription_product', $subscription );

    if ($subscription == 'yes') {
        $subscription_option = isset($_POST['subscription_options']) ? $_POST['subscription_options'] : '';
        update_post_meta($id, 'subscription_options', $subscription_option);
    } 
    else {
        delete_post_meta($id, 'subscription_options');
    }

    if ($subscription == 'yes') {
        $subscription_price = isset($_POST['subscription_price']) ? $_POST['subscription_price'] : '';
        update_post_meta($id, '_price', $subscription_price);  
    } 
    else {
        delete_post_meta($id, 'subscription_price');
    }
}
?>