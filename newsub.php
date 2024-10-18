<?php
/*
Plugin Name: New Subscription WooCommerce
Author: Helgi Bjarnason
Version: 1.0
Description: Adds subscription as metadata in all product types. A reboot of the WooCommerce Subscription plugin, as the other one does not work as intended.
*/

add_action( 'woocommerce_product_options_general_product_data', 'ingii_option_group' );

function ingii_option_group(){
    echo'<div class="options_group">';
    woocommerce_wp_checkbox(
        array(
            'id'    => 'sub',
            'value' => get_post_meta( get_the_ID(), 'subscription_product', true ),
            'label' => 'Subscription product',
            'desc_tip' => true,
            'description' => 'This is a subscription type product with recurring payments'
        )
        );
    echo'</div>';
}

// function subscription_pricing_option_group(){
//     echo'<div class="">';
//     woocommerce_wp_text_input([
//         'id'  => 'sub_pricing',
//         ''  => '',
//         ''  => '',
//         ''  => '',
//         ''  => '',
//     ]);
//     echo'</div>';
// }

add_action( 'woocommerce_process_product_meta','ingii_save_field');

function ingii_save_field( $id ){
    $subscription = isset( $_POST[ 'sub' ] ) && 'yes' === $_POST[ 'sub' ] ? 'yes' : 'no';
    update_post_meta( $id, 'subscription_product', $subscription );

    if ($subscription == 'yes'){
        $sub_options = [
        '1' => '1 Month',
        '2' => '3 Months',
        '3' => 'Year'
    ];

    woocommerce_wp_select([
        'id'  =>  'subscription_options',
        'label'  =>  __( 'Select a subscription option', '' ),
        'options'  =>  $sub_options,
        'desc'  =>  __('Choose one option from the dropdown'),
    ]);
    }
}

?>