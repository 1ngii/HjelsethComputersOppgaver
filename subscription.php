<?php
/*
Plugin Name: Subscription WooCommerce (didn't work as intended)
Description: Adds a subscription payment option to the WooCommerce "product" page
Author: Helgi Bjarnason
Version: 1.0
*/

add_filter( 'product_type_selector', 'add_subscription_product_type' );

function add_subscription_product_type( $types ){
    $types[ 'sub' ] = 'Subscription product';
    return $types;
}

add_action( 'init', 'create_subscription_product_type' ); 

function create_subscription_product_type(){
    class WC_Product_Subscription extends WC_Product {
        public function get_type() {
            return 'sub';
        }
    }
}

add_filter( 'woocommerce_product_class', 'my_woo_commerce_product_class', 10, 2 );

function my_woo_commerce_product_class($classname, $product_type){
    if ( $product_type == 'sub' ) {
        $classname = 'WC_Product_Subscription';
    }
    return $classname;
}

add_action( 'woocommerce_product_options_general_product_data', 'subscription_product_type_show_price' );

function subscription_product_type_show_price(){
    global $product_object;
    if ( $product_object && 'sub' === $product_object->get_type() ){
        wc_enqueue_js( "
            $('.product_data_tabs .general_tab').addClass('show_if_sub').show();
            $('.pricing').addClass('show_if_sub').show();
        ");
    }
}

add_action( "woocommerce_subscription_add_to_cart", function() {
    do_action( 'woocommerce_simple_add_to_cart' );
});
?>
