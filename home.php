<?php
/*
Plugin Name: Home
Author: Helgi Bjarnason
Description: Just a test for me to get used to shortcodes
 */

// function get_products_data($atts, $content = null){
//     $response = wp_remote_get('https://dummyjson.com/products');
//     $data = json_decode($response["body"]);
//     // $output = '
//     // <style>
//     //     .products-table {
//     //         border-collapse: collapse;
//     //         width: 100%;
//     //         max-width: 800px;
//     //         margin: 0 auto;
//     //     }
//     //     .products-table th, .products-table td {
//     //         border: 1px solid #ddd;
//     //         padding: 8px;
//     //         text-align: left;
//     //     }
//     //     .products-table th {
//     //         background-color: #f2f2f2;
//     //         font-weight: bold;
//     //     }
//     //     .products-table img {
//     //         max-width: 100px;
//     //         height: auto;
//     //         margin: 2px;
//     //     }
//     // </style>';

//     echo "<table><thead><th>Product title<th><th>Images</th></thead><tbody>";
//     foreach($data->products as $product){
//         $images = '';
//         foreach($product->images as $image)
//         {
//             $images .= "<img src='" .$image ."'/>"; 
//         }
//          echo'<tr><td>' .  $product->title ."</td> <td>". $images."</td> </tr>";
//      }
//     echo "</tbody></table>";

// }
// add_shortcode('products_api', 'get_products_data');


/*
Plugin Name: Home
Author: Helgi Bjarnason
Description: Just a test for me to get used to shortcodes
 */

function get_products_data($atts, $content = null){
    $response = wp_remote_get('https://dummyjson.com/products');
    $data = json_decode($response["body"]);
    
    // Add CSS styles
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
    
    $output .= "<table class='products-table'><thead><tr><th>Product title</th><th>Images</th></tr></thead><tbody>";
    foreach($data->products as $product){
        $images = '';
        foreach($product->images as $image)
        {
            $images .= "<img src='" . esc_url($image) . "' alt='" . esc_attr($product->title) . "'/>";
        }
        $output .= '<tr><td>' . esc_html($product->title) . "</td><td>" . $images . "</td></tr>";
    }
    $output .= "</tbody></table>";

    return $output;
}
add_shortcode('products_api', 'get_products_data');
?>
