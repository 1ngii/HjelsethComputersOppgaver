<?php
/*
Plugin Name: New Subscription WooCommerce
Author: Helgi Bjarnason
Version: 1.0
Description: Adds subscription as metadata in all product types. A reboot of the WooCommerce Subscription plugin, as the other one does not work as intended.
*/

add_action( 'woocommerce_product_options_general_product_data', 'ingii_option_group' );

function ingii_option_group() {
    // Render the checkbox
    echo '<div class="options_group">';
    woocommerce_wp_checkbox(
        array(
            'id'    => 'sub',
            'value' => get_post_meta( get_the_ID(), 'subscription_product', true ),
            'label' => 'Subscription product',
            'desc_tip' => true,
            'description' => 'This is a subscription type product with recurring payments'
        )
    );

    // Render the select box for subscription options
    $subscription_value = get_post_meta( get_the_ID(), 'subscription_options', true );
    echo '<div class="subscription_options" style="display: ' . (get_post_meta( get_the_ID(), 'subscription_product', true ) === 'yes' ? 'block' : 'none') . ';">';
    woocommerce_wp_select([
        'id' => 'subscription_options',
        'label' => __( 'Select a subscription option', '' ),
        'options' => [
            '1' => '1 Month',
            '2' => '3 Months',
            '3' => 'Year'
        ],
        'desc' => __('Choose one option from the dropdown'),
        'value' => $subscription_value,
    ]);
    echo '</div>';

    echo '</div>'; // Close options_group

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

add_action( 'woocommerce_process_product_meta', 'ingii_save_field' );

function ingii_save_field( $id ) {
    // Save subscription checkbox value
    $subscription = isset( $_POST['sub'] ) && 'yes' === $_POST['sub'] ? 'yes' : 'no';
    update_post_meta( $id, 'subscription_product', $subscription );

    // Save subscription option selected
    if ($subscription == 'yes') {
        $subscription_option = isset($_POST['subscription_options']) ? $_POST['subscription_options'] : '';
        update_post_meta($id, 'subscription_options', $subscription_option);
    } else {
        // Optionally delete the subscription option if it's not a subscription
        delete_post_meta($id, 'subscription_options');
    }
}
?>
