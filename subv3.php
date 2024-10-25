<?php
/*
Plugin Name: SUBV3
Author: Helgi Bjarnason
Version: 1.3
Description: Adds subscription data tab to all product types. A reboot of the WooCommerce Subscription plugin, as the other one does not work as intended.
*/

add_filter('woocommerce_get_price_html', '__return_empty_string', 10, 2);

add_filter( 'woocommerce_product_data_tabs', 'ingii_product_settings_tabs' );

function ingii_product_settings_tabs( $tabs ){
    $tabs [ 'subscription' ] = [
        'label'  =>  'Subscription',
        'target'  =>  'subscription_product_data',
        'priority'  =>  '21',
    ];
    return $tabs;
}

add_action( 'woocommerce_product_data_panels', 'subscription_product_panels' );

function subscription_product_panels() {
    echo '<div id="subscription_product_data" class="panel woocommerce_options_panel">';
    woocommerce_wp_checkbox([
            'id'    => 'sub',
            'value' => get_post_meta( get_the_ID(), 'subscription_product', true ),
            'label' => 'Subscription product',
            'desc_tip' => true,
            'description' => 'This is a subscription type product with recurring payments'
        ]);

    $existing_subscription_plans = get_post_meta( get_the_ID(), 'subscription_plans', true ) ?: [];
    $subscription_value = get_post_meta( get_the_ID(), 'subscription_product_data', true );
    $is_subscription_enabled = get_post_meta( get_the_ID(), 'subscription_product', true) === 'yes';
    
        echo '<div id="subscription_product_data_extend" style="display: ' . ($is_subscription_enabled ? 'block' : 'none') . ';">';

        $options = [];
        foreach ($existing_subscription_plans as $index => $plan){
            $options[$index] = $plan['name'] . ': ' . $plan['value'] . ' kr';
        }

        woocommerce_wp_select([
                'id' => 'subscription_options',
                'label' => __( 'List of possible subscription options', 'New Subscription WooCommerce' ),
                'options' => $options,
                'desc' => __('Choose one option from the dropdown', 'New Subscription WooCommerce'),
                'value' => $subscription_value,
            ]);
            
       
    echo '<form method="POST">';
        echo'<h3>New Subscription option</h3>';
        echo '<div id="new_subscription_plan">';
        woocommerce_wp_text_input([
            'id'  =>  'new_subscription_name',
            'label'  =>  'Name',
            'value'  => get_post_meta( get_the_ID(), 'new_subscription_name', true ),
        ]);
        woocommerce_wp_text_input([
            'id'  =>  'new_subscription_value',
            'label'  =>  'Value (kr)',
            'value'  => get_post_meta( get_the_ID(), 'new_subscription_value', true ),
        ]);
        echo '</div>';
        
        echo '<button type="submit" name="add_subscription_plan">Submit new plan</button>';
    echo '</form>';

    echo '</div>'; //subscription_product_data_extend

    echo '</div>'; //subscription_product_data

    // Enqueue JavaScript to show/hide the select box based on checkbox 
    wc_enqueue_js("
        jQuery('#sub').change(function(){
            if(jQuery(this).is(':checked')) {
                $('#subscription_product_data_extend').show();
            } else {
                $('#subscription_product_data_extend').hide();
            }
        }).change(); // Trigger change to set initial state
    ");
}

add_action('woocommerce_single_product_summary', 'display_and_change_billing_option', 25);

function display_and_change_billing_option() {
    global $product;

    $is_subscription_product = get_post_meta($product->get_id(), 'subscription_product', true);
    $regular_price = $product->get_regular_price(); 
    $custom_price = 0;
    $current_price = $regular_price;
    $subscription_plans = get_post_meta($product->get_id(), 'subscription_plans', true) ?: [];

    foreach ($subscription_plans as $plan) {
        if (isset($plan['value'])) {
            $custom_price = floatval($plan['value']); 
            break; 
        }
    }

    if ($is_subscription_product === 'yes') {
        echo '<div class="billing-option">';
        echo '<label for="billing_method">Choose your preferred billing method:</label>';
        echo '<select id="billing_method" name="billing_method">';
        echo '<option value="0"' . selected($current_price, $regular_price, false) . '>Regular price</option>';
        echo '<option value="1"' . selected($current_price, $custom_price, false) . '>Subscription</option>';
        echo '</select>';
        echo '</div>';

        if (!empty($subscription_plans)) {
            echo '<div id="subscription_options_container" style="display: none;">';
            echo '<form method="POST" id="sub_plans"> ';
            echo '<label for="subscription_options">Select Subscription Plan:</label>';
            echo '<select id="subscription_options" name="subscription_options">';
            foreach ($subscription_plans as $plan) {
                echo '<option value="' . esc_attr($plan['value']) . '">' . esc_html($plan['name']) . '</option>';
            }
            echo '</select>';
            echo '<input type="hidden" name="selected_subscription" id="selected_subscription" />';

            echo '</form>';
            echo '</div>';
        }
    }

    echo '<div id="price_display">' . wc_price($current_price) . '</div>'; 

    // Enqueue JavaScript
    wc_enqueue_js("
        var currentPriceRegular = " . floatval($regular_price) . ";
        var currentPriceSubscription = " . floatval($custom_price) . ";

        function updatePriceDisplay(price) {
            jQuery('#price_display').html(price.toFixed(2) + ' kr');
        }

        jQuery('#billing_method').change(function() {
            var selectedValue = jQuery(this).val();
            var newPrice;

            

            if (selectedValue === '0') {
                newPrice = currentPriceRegular;
                jQuery('#subscription_options_container').hide(); 
            } else {
                newPrice = currentPriceSubscription;
                jQuery('#subscription_options_container').show(); 
            }

            updatePriceDisplay(newPrice);
        }).change(); 

        jQuery('#subscription_options').change(function() {
            var selectedPlanValue = jQuery(this).val();
            updatePriceDisplay(parseFloat(selectedPlanValue));

            console.log('Selected Subscription Plan Value: ' + selectedPlanValue);
        });
    ");
}


add_action('wp_enqueue_scripts', 'enqueue_ajax_script');

function enqueue_ajax_script() {
    wp_enqueue_script('ajax-script', plugin_dir_url(__FILE__) . 'ajax-script.js', array('jquery'), null, true);

    // Localize script to make AJAX URL available in JavaScript
    wp_localize_script('ajax-script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
}


add_action('wp_ajax_check_subscription_selection', 'check_subscription_selection');
add_action('wp_ajax_nopriv_check_subscription_selection', 'check_subscription_selection');


function check_subscription_selection() {
    $product_id = intval($_POST['product_id']);
    $selected_subscription = sanitize_text_field($_POST['selected_subscription']);

    $is_subscription_product = get_post_meta($product_id, 'subscription_product', true);

    if ($is_subscription_product === 'yes' && empty($selected_subscription)) {
        wp_send_json_error('Please select a subscription option.');
    } else {
        $price = floatval($selected_subscription); 
        wp_send_json_success(['message' => 'Valid selection.', 'new_price' => $price]);
    }
    wp_die();
}

// add_filter('woocommerce_add_to_cart_validation', 'wk_add_to_cart_validation', 10, 4 );

// function wk_add_to_cart_validation($passed, $product_id, $quantity, $variation_id = null){
//     $selected_subscription = sanitize_text_field($_POST['selected_subscription']);

//     $is_subscription_product = get_post_meta($product_id, 'subscription_product', true);

//     if($is_subscription_product === 'yes' && empty($selected_subscription)){
//         $passed=false;
//         wp_add_notice('Choose a sub type', 'error');
//     } 
//     return $passed;
// }


add_filter( 'woocommerce_add_cart_item_data', 'wk_add_cart_items', 10, 3 );

function wk_add_cart_items($cart_item_data, $product_id, $variation_id) {
    if (isset($_POST['billing_method'])) {
        $cart_item_data['sub_field'] = sanitize_text_field($_POST['billing_method']); // Sanitize input
    }
    return $cart_item_data;
}

add_action( 'woocommerce_process_product_meta', 'ingii_save_field' );

function ingii_save_field( $id ) {
    $subscription = isset( $_POST['sub'] ) && 'yes' === $_POST['sub'] ? 'yes' : 'no';
    update_post_meta( $id, 'subscription_product', $subscription );

    if ($subscription == 'yes') {
        // Check if the button was pressed and input fields are not empty
        $new_subscription_name = isset($_POST['new_subscription_name']) ? sanitize_text_field($_POST['new_subscription_name']) : '';
        $new_subscription_value = isset($_POST['new_subscription_value']) ? sanitize_text_field($_POST['new_subscription_value']) : '';

        if (!empty($new_subscription_name) && !empty($new_subscription_value)) {
            // Retrieve existing subscription plans
            $existing_plans = get_post_meta($id, 'subscription_plans', true) ?: [];

            $existing_plans[] = [
                'name' => $new_subscription_name,
                'value' => $new_subscription_value,
            ];

            update_post_meta($id, 'subscription_plans', $existing_plans);
        }
    } else {
        delete_post_meta($id, 'subscription_plans');
    
    
}};

?>