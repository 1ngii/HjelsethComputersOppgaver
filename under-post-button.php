<?php
    /*
    Plugin Name: Under Post Button
    Description: When this plugin is activated it adds a button on every post
    */
    
    add_action('the_content', 'myButton');

    function myButton ( $content ) {
        return $content .= '<a href="intro"><button class="btn">Intro Page</button>';
    }
?>