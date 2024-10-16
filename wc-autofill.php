<?php
/*
Plugin Name: WooCommerce Autofill Products
Description: Automatically fill WooCommerce products from an external API.
Author: Helgi Bjarnason
Version: 1.0
*/

// Hook to add admin menu
add_action('admin_menu', 'wcap_add_admin_menu', 10);

function wcap_add_admin_menu() {
    add_menu_page('Autofill Products', 'Autofill Products', 'manage_options', 'wcap_autofill', 'wcap_autofill_page');
}

// Admin page to trigger autofill
function wcap_autofill_page() {
    if (isset($_POST['wcap_autofill'])) {
        wcap_fetch_and_create_products();
    }
    ?>
    <div class="wrap">
        <h1>Autofill Products from API</h1>
        <form method="post">
            <input type="submit" name="wcap_autofill" class="button button-primary" value="Autofill Products">
        </form>
    </div>
    <?php
}

// Function to fetch and create products
function wcap_fetch_and_create_products() {
    $response = wp_remote_get('https://dummyjson.com/products'); 

    if (is_wp_error($response)) {
        echo '<div class="error"><p>Error fetching products.</p></div>';
        return;
    }

    $data = json_decode($response['body']);

    if (!empty($data->products)) {
        foreach ($data->products as $product_data) {
            // Check if the product already exists
            $existing_product_id = wc_get_product_id_by_sku($product_data->id);

            if (!$existing_product_id) {
                // Create a new product
                $product = new WC_Product_Simple();
                $product->set_name($product_data->title);
                $product->set_regular_price($product_data->price);
                $product->set_description($product_data->description);
                $product->set_sku($product_data->id); // Use the API ID as SKU
                $product->set_stock_status('instock');

                // Handle images
                if (!empty($product_data->images)) {
                    $image_ids = [];
                    foreach ($product_data->images as $image_url) {
                        $image_id = wcap_upload_image($image_url);
                        if ($image_id) {
                            $image_ids[] = $image_id;
                        }
                    }
                    $product->set_image_id($image_ids[0]); // Set the first image as the product image
                    $product->set_gallery_image_ids($image_ids); // Set the rest as gallery images
                }

                $product->save();
            }
        }
        echo '<div class="updated"><p>Products have been autofilled successfully!</p></div>';
    } else {
        echo '<div class="error"><p>No products found in API response.</p></div>';
    }
}

// Helper function to upload images to the media library
function wcap_upload_image($image_url) {
    $response = wp_remote_get($image_url);
    if (is_wp_error($response)) {
        return false;
    }

    $upload = wp_upload_bits(basename($image_url), null, $response['body']);
    if (isset($upload['error']) && $upload['error'] !== false) {
        return false;
    }

    $attachment = [
        'post_mime_type' => $upload['type'],
        'post_title'     => sanitize_file_name(basename($image_url)),
        'post_content'   => '',
        'post_status'    => 'inherit'
    ];

    $attach_id = wp_insert_attachment($attachment, $upload['file']);
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata($attach_id, $upload['file']);
    wp_update_attachment_metadata($attach_id, $attach_data);

    return $attach_id;
}
?>
