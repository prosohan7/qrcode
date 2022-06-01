<?php
/**
 * Plugin Name:       Posts To QR Code
 * Plugin URI:        https://example.com/plugins/qrcode/
 * Description:       This is practise plugin.
 * Version:           1.0
 * Author:            Sohan
 * Author URI:        https://author.example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/my-plugin/
 * Text Domain:       posts-to-qrcode
 * Domain Path:       /languages
 */

// Load Text Domain
// function wordcount_load_textdomain(){
//     load_plugin_textdomain( 'posts-to-qrcode', false, dirname(__FILE__)."/languages" );
// }
// add_action( 'plugins_loaded', 'wordcount_load_textdomain');

function pqrc_display_qr_code($content){
    $current_post_id    = get_the_ID();
    $current_post_title = get_the_title( $current_post_id );
    $current_post_url   = urlencode( get_the_permalink ( $current_post_id ) );
    $current_post_type  = get_post_type( $current_post_id );

    //Post Type Check
    $excluded_post_types = apply_filters( 'pqrc_excluded_post_types', array() );
    if ( in_array( $current_post_type, $excluded_post_types ) ) {
        return $content;
    }

    // Dimension Hook
    $height     = get_option( 'pqrc_height' );
    $width      = get_option( 'pqrc_width' );
    $height     = $height ? $height : 180;
    $width      = $width ? $width : 180;
    $dimension  = apply_filters( 'pqrc_qrcode_dimension', "{$width}x{$height}" );

    // Image attributes
    $image_attributes = apply_filters( 'pqrc_image_attributes', '' );

    $image_src = sprintf( 'https://api.qrserver.com/v1/create-qr-code/?size=%s&ecc=L&qzone=1&data=%s', $dimension ,$current_post_url );
    $content .=  sprintf( "<div class='qrcode'><img %s src='%s' alt='%s' /></div>", $image_attributes, $image_src, $current_post_title );
    return $content;
}
add_filter( 'the_content', 'pqrc_display_qr_code' );

function pqrc_settings_init(){
    //add_settings_section( $id:string, $title:string, $callback:callable, $page:string );
    add_settings_section( 'pqrc_section', __( 'Posts to QR Code', 'posts-to-qrcode'), "pqrc_section_callback", 'general' );
//add_settings_field( $id:string, $title:string, $callback:callable, $page:string, $section:string, $args:array );
    add_settings_field( 'pqrc_height', __( 'QR Code Height', 'posts-to-qrcode'), 'pqrc_display_field', 'general', 'pqrc_section', array('pqrc_height') );
    add_settings_field( 'pqrc_width', __( 'QR Code Width', 'posts-to-qrcode' ), 'pqrc_display_field', 'general', 'pqrc_section', array('pqrc_width') );
    // add_settings_field( 'extra_option', __( 'QR Code Extra', 'posts-to-qrcode' ), 'pqrc_display_field', 'general', 'pqrc_section', array('extra_option') );
    add_settings_field( 'pqrc_select', __( 'Dropdown', 'posts-to-qrcode' ), 'pqrc_display_select_field', 'general', 'pqrc_section' );
    add_settings_field( 'pqrc_checkbox', __( 'Select Countries', 'posts-to-qrcode' ), 'pqrc_display_checkbocgroup_field', 'general', 'pqrc_section' );

    //register_setting( $option_group:string, $option_name:string, $args:array )
    register_setting( 'general', 'pqrc_height', array( 'sanitize_callback' => 'esc_attr' ) );
    register_setting( 'general', 'pqrc_width', array( 'sanitize_callback' => 'esc_attr' ) );
    // register_setting( 'general', 'extra_option', array( 'sanitize_callback' => 'esc_attr' ) );
    register_setting( 'general', 'pqrc_select', array( 'sanitize_callback' => 'esc_attr' ) );
    register_setting( 'general', 'pqrc_checkbox' );
}

function pqrc_display_checkbocgroup_field(){
    $option = get_option('pqrc_checkbox');
    $countries = array(
        'None',
        'Afganistan',
        'India',
        'Maldives',
        'Nepal',
        'Pakistan',
        'Sri Lanka'
    );
    
    foreach( $countries as $country ) {
        $selected = '';

        if( is_array($option) && in_array( $country, $option ) ) {
            $selected = 'checked';
        }
        printf('<input type="checkbox" name="pqrc_checkbox[]" value="%s" %s /> %s <br>', $country, $selected, $country);
    }
}

function pqrc_display_select_field(){
    $option = get_option('pqrc_select');
    $countries = array(
        'None',
        'Afganistan',
        'India',
        'Maldives',
        'Nepal',
        'Pakistan',
        'Sri Lanka'
    );
    printf('<select id="%s" name="%s">', 'pqrc_select', 'pqrc_select');
    foreach( $countries as $country ) {
        $selected = '';
        if( $option == $country ) {
            $selected = 'selected';
        }
        printf('<option value="%s" %s>%s</option>', $country, $selected, $country);
    }
    echo "</select>";
}

function pqrc_section_callback(){
    echo "<p>". __( 'Settings for posts to QR Plugin', 'posts-to-qrcode') ."</p>";
}

function pqrc_display_field($args){
    $option = get_option( $args[0] );
    printf( "<input type='text' id='%s' name='%s' value='%s'/>", $args[0], $args[0], $option );
}

// function pqrc_display_width(){
//     $width = get_option( 'pqrc_width' );
//     printf( "<input type='text' id='%s' name='%s' value='%s'/>", 'pqrc_width', 'pqrc_width', $width );
// }
// function pqrc_display_height(){
//     $height = get_option( 'pqrc_height' );
//     printf( "<input type='text' id='%s' name='%s' value='%s'/>", 'pqrc_height', 'pqrc_height', $height );
// }

add_action( "admin_init", "pqrc_settings_init" );
