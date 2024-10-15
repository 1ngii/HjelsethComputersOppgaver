<?php
/*
Plugin Name: Home
Author: Helgi Bjarnason
Description: Just a test for me to get used to shortcodes, plugins and using API
 */

function get_products_data($atts){
    $response = wp_remote_get('https://dummyjson.com/products');
    $data = json_decode($response["body"]);
    
    $output = '
    <style>
        .products-table {
            border-collapse: collapse;
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }
        .products-table th, .products-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .products-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .products-table img {
            max-width: 100px;
            height: auto;
            margin: 2px;
        }
    </style>';
    
    $output .= "<table class='products-table'>
    <thead><tr>
    <th>Product title</th>
    <th>Price</th>
    <th>Images</th>
    </tr></thead>
    <tbody>";
    foreach($data->products as $product){
        $images = '';
        foreach($product->images as $image)
        {
            $images .= "<img src='" . $image . "' alt='" . $product->title . "'/>";
        }
            $output .= "<tr>
            <td> $product->title </td>
            <td>" . number_format($product->price, 2) . "</td>
            <td> $images </td>
            </tr>";
    }
    $output .= "</tbody></table>";

    return $output;
}
function filter_api_data($content){
    $output = "
    <label for=\"filter\">Filter shown data : </label>
    <select name=\"Make\" id=\"cmbMake\">
        <option value=\"any\">Any</option>
        <option value=\"products\">Products</option>
        <option value=\"price\">Price</option>
        <option value=\"images\">Images</option>
    </select>
    <button>Submit</button>";

    return $output;
}
// function search_data(){

// }
add_shortcode('products_api', 'get_products_data');
add_shortcode( 'api_filter', 'filter_api_data' );
// add_filter( 'search', 'search_data');
?>
