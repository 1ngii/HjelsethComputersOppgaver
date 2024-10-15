<?php
/*
Plugin Name: Products API
Author: Helgi Bjarnason
Description: Just a test for me to get used to shortcodes, plugins and using API
 */

function get_products_data($atts){
    $response = wp_remote_get('https://dummyjson.com/products');
    $data = json_decode($response["body"]);
    $filter = isset($_GET['filter']) ? intval($_GET['filter']) : 0;
    $search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
    $output = "
    <form method=\"get\">
    <input type=\"search\" name=\"search\" placeholder=\"Search products\" value=\"" . esc_attr(isset($_GET['search']) ? $_GET['search'] : '') . "\">

     <label for=\"filter\">Filter shown data : </label>
        <select name=\"filter\" id=\"cmbMake\">
        ";

        $options = [
            0 => "Any",
            1 => "Price over $500",
            2 => "Price under $500",
            3 => "Multiple images"
        ];
        
        foreach ($options as $value => $label){
            $selected = $filter == $value ? 'selected' : '';
            $output.= "<option value= \"$value\" $selected>$label</option>";
        }
        $output .= "
            </select>
            <button type=\"submit\" >Search</button>
    </form>
    ";

    

    
    
    
    $output .= '
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
        if ($search_query && stripos($product->title, $search_query) === false) {
            continue;
        }
        
        $images = '';
        foreach($product->images as $image)
        {
            $images .= "<img src='" . $image . "' alt='" . $product->title . "'/>";
        }

        if ($filter == 0 ||
            // ($filter == 1 && !empty($product->title))||
            ($filter == 1 && $product->price > 500) ||
            ($filter == 2 && $product->price < 500) ||
            ($filter == 3 && count($product->images) > 1)){

            $output .= "<tr>
            <td> $product->title </td>
            <td>" . number_format($product->price, 2) . "</td>
            <td> $images </td>
            </tr>";
        }
    }
    $output .= "</tbody></table>";

    
    return $output;
}

add_shortcode( 'products_api', 'get_products_data' );
?>
