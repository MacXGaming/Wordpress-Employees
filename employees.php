<?php
/**
* @package Employees
* @version 1.1
*/
/*
Plugin Name: Employees
Plugin URI: http://renedyhr.dk
Description: A plugin that handles Employees
Author: René Dyhr
Version: 1.0
Author URI: http://renedyhr.dk
*/




function register_employees() {
    $labels = array(
        'name' => _x( 'Employees', 'employee' ),
        'singular_name' => _x( 'Employees', 'employee' ),
        'add_new' => _x( 'Add New', 'employee' ),
        'add_new_item' => _x( 'Add New Employee', 'employee' ),
        'edit_item' => _x( 'Edit Employee', 'employee' ),
        'new_item' => _x( 'New Employee', 'employee' ),
        'view_item' => _x( 'View Employee', 'employee' ),
        'search_items' => _x( 'Search Employees', 'employee' ),
        'not_found' => _x( 'No employee found', 'employee' ),
        'not_found_in_trash' => _x( 'No employee found in Trash', 'employee' ),
        'parent_item_colon' => _x( 'Parent Employees:', 'employee' ),
        'menu_name' => _x( 'Employees', 'employee' ),
    );
    $args = array(
        'labels' => $labels,
        'hierarchical' => true,
        'description' => 'Employees filterable by category',
        'supports' => array( 'title', 'thumbnail', 'page-attributes' ),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 6,
        'menu_icon' => 'dashicons-groups',
        'show_in_nav_menus' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'has_archive' => true,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => true,
        'capability_type' => 'post'
    );
    register_post_type( 'employee', $args );
}
add_action( 'init', 'register_employees' );

add_action("admin_init", "admin_init");

function admin_init(){
    add_meta_box("information", "Information", "information", "employee", "normal", "low");
}


function information() {
    global $post;
    $custom = get_post_custom($post->ID);
    $email = $custom["email"][0];
    $position = $custom["position"][0];
    $telephone = $custom["telephone"][0];
    $mobile = $custom["mobile"][0];
    ?>
    <p>
        <label>Email:</label><br />
        <input type="text" name="email" value="<?php echo $email; ?>">
    </p>
    <p>
        <label>Stilling:</label><br />
        <input type="text" name="position" value="<?php echo $position; ?>">
    </p>
    <p>
        <label>Telefon Nr.:</label><br />
        <input type="tel" name="telephone" value="<?php echo $telephone; ?>">
    </p>
    <p>
        <label>Mobil Nr.:</label><br />
        <input type="tel" name="mobile" value="<?php echo $mobile; ?>">
    </p>
    <?php
}

add_action('save_post', 'save_details');


function save_details(){
    global $post;

    // update_post_meta($post->ID, "year_completed", $_POST["year_completed"]);
    update_post_meta($post->ID, "email", $_POST["email"]);
    update_post_meta($post->ID, "position", $_POST["position"]);
    update_post_meta($post->ID, "telephone", $_POST["telephone"]);
    update_post_meta($post->ID, "mobile", $_POST["mobile"]);
}

add_action( 'admin_menu', 'employee_add_admin_menu' );
add_action( 'admin_init', 'employee_settings_init' );

function employee_add_admin_menu() {
    add_options_page( 'Employees', 'Employees', 'manage_options', 'employees', 'employee_options_page' );
}

function employee_settings_init() {
    register_setting( 'pluginPage', 'employee_settings' );
    add_settings_section(
    'employee_pluginPage_section',
    __( 'Your section description', 'wordpress' ),
    'employee_settings_section_callback',
    'pluginPage'
);

add_settings_field(
'employee_select_field_0',
__( 'Settings field description', 'wordpress' ),
'employee_select_field_0_render',
'pluginPage',
'employee_pluginPage_section'
);


}

function employee_select_field_0_render() {

    $options = get_option( 'employee_settings' );
    ?>
    <label>Layout</label>
    <select name='employee_settings[style]'>
        <option value='' <?php selected( $options['style'] ); ?>>Grid</option>
        <option value='1' <?php selected( $options['style'], 1 ); ?>>List</option>
        <option value='3' <?php selected( $options['style'], 3 ); ?>>Custom</option>
    </select>

    <label>Per Line</label>
    <select name='employee_settings[per-line]'>
        <option value='1' <?php selected( $options['per-line'], 1 ); ?>>1</option>
        <option value='2' <?php selected( $options['per-line'], 2 ); ?>>2</option>
        <option value='3' <?php selected( $options['per-line'], 3 ); ?>>3</option>
        <option value='4' <?php selected( $options['per-line'], 4 ); ?>>4</option>
    </select>

    <?php

}

function employee_settings_section_callback() {

    echo __( 'This is the simple setting page for employees', 'wordpress' );

}

function employee_options_page() {
    ?>
    <form action='options.php' method='post'>
        <h2>Employees</h2>
        <?php
        settings_fields( 'pluginPage' );
        do_settings_sections( 'pluginPage' );
        submit_button();
        ?>
    </form>
    <?php
}

add_shortcode( 'ansatte', 'display_views' );
add_shortcode( 'employee', 'display_views' );
function display_views() {

    $employee = get_option('employee_settings');


    // query
    $args = array(
        'post_type' => 'employee',
        'posts_per_page' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC',
    );

    $the_query = new WP_Query($args);

    $output='<link rel="stylesheet" type="text/css" href="'.plugins_url('style.css',__FILE__).'">';
    switch($employee['style']) {
        case 1: // List
        $output .= '<table class="employee-table">';
        $output .= '<tr><td colspan="2" style="border: none;"><hr></td></tr>';
        if($the_query->have_posts()){
            foreach ($the_query->posts as $post) {
                $thumbnail="";
                $metadata = get_post_meta($post->ID);
                $thumbnail = get_the_post_thumbnail_url($post->ID, 'large');
                if(!empty($thumbnail)){
                    $thumbnail = get_the_post_thumbnail_url($post->ID, 'large');
                }else{
                    $thumbnail = plugins_url('images/default_picture.png',__FILE__);
                }

                $email = "<a href=\"mailto:".$metadata['email'][0]."\">Send mig en mail</a>";

                $output .= '<tr><td style="border: none;">';
                $output .= '<div>';
                $output .= '<div class="employee-person-name">' . $post->post_title . '</div>';
                $output .= '<div class="employee-person-function">' . $metadata['position'][0] . '</div>';
                if ( $metadata['telephone'][0] != NULL ) {
                    $output .= '<br />' . __('Telefon:', 'employee') . ' ' . $metadata['telephone'][0];
                }
                if ( $metadata['mobile'][0] != NULL ) {
                    $output .= '<br />' . __('Mobil:', 'employee') . ' ' . $metadata['mobile'][0];
                }
                if ( $metadata['email'][0] != NULL ) {
                    $output .= '<br />' . $email;
                }
                $output .= '</div>';
                $output .= '</td><td style="border: none;">';
                $output .= '<img src="' . $thumbnail . '">';
                $output .= '</td></tr>';
                $output .= '<tr><td colspan="2" style="border: none;"><hr></td></tr>';
            }
        }
        $output .= '</table>';
        break;

        default: //grid
        $i = 1;
        $output .= '<style type="text/css">.employee-cellule {text-align:center;color:#000;}</style>';
        $output .= '<table class="employee-table" width="100%">';
        $output .= '<tr>';
        if($the_query->have_posts()){
            foreach ($the_query->posts as $post) {
                $thumbnail="";
                $metadata = get_post_meta($post->ID);
                $thumbnail = get_the_post_thumbnail($post->ID, 'thumbnail');
                if(!empty($thumbnail)){
                    $thumbnail = get_the_post_thumbnail($post->ID, 'thumbnail');
                }else{
                    $thumbnail = plugins_url('images/default_picture.png',__FILE__);
                }

                $email = "<a href=\"mailto:".$metadata['email'][0]."\">Send mig en mail</a>";

                $output .= '<td class="employee-cellule"><div class="employee-vignette">';
                $output .= '<div style="padding-top:20px;padding-bottom:5px;"><img src="' . $thumbnail . '"></div>';
                $output .= '<div>';
                $output .= '<div class="employee-person-name" style="font-weight:bold;font-size:16px;padding-bottom:5px;">' . $post->post_title . '</div>';
                $output .= '<div class="employee-person-function" style="padding-bottom:5px;">' . $metadata['position'][0] . '</div>';
                if ( $metadata['telephone'][0] != NULL ) {
                    $output .= '<div style="padding-bottom:5px;">' . __('Telefon:', 'employee') . ' ' . $metadata['telephone'][0].'</div>';
                }else{
                    $output .= '<div style="padding-bottom:5px;">&nbsp;</div>';
                }
                if ( $metadata['mobile'][0] != NULL ) {
                    $output .= '<div style="padding-bottom:5px;">' . __('Mobil:', 'employee') . ' ' . $metadata['mobile'][0].'</div>';
                }else{
                    $output .= '<div style="padding-bottom:5px;">&nbsp;</div>';
                }
                if ( $metadata['email'][0] != NULL ) {
                    $output .= '<div style="padding-bottom:20px;">' . $email.'</div>';
                }else{
                    $output .= '<div style="padding-bottom:20px;">&nbsp;</div>';
                }
                $output .= '</div>';
                $output .= '</div></td>';
                if ( $i % $employee['per-line'] == 0) {
                    $output .= '</tr><tr>';
                }
                $i++;
            }
        }
        $output .= '</table>';
        break;
        case 3: //custom

        $cnt1 = 0;
        if($the_query->have_posts()){
            foreach ($the_query->posts as $post) {
                $thumbnail="";
                $metadata = get_post_meta($post->ID);
                $thumbnail = get_the_post_thumbnail_url($post->ID, 'large');
                if(!empty($thumbnail)){
                    $thumbnail = get_the_post_thumbnail_url($post->ID, 'large');
                }else{
                    $thumbnail = plugins_url('images/default_picture.png',__FILE__);
                }

                $email = "<a href=\"mailto:".$metadata['email'][0]."\">Send mig en mail</a>";
                $cnt1++;
                if ($cnt1==1) {
                    $output .= '<div style="border:0px solid #f00;">';
                }
                $output .= '<div style="width:230px;display:inline-block;border:0px solid #000;text-align:center;">';
                $output .= '<div style="padding-top:20px;padding-bottom:5px;"><img src="' . $thumbnail . '"></div>';
                $output .= '<div class="employee-person-name" style="font-weight:bold;font-size:16px;padding-bottom:5px;">' . $post->post_title . '</div>';
                $output .= '<div class="employee-person-function" style="padding-bottom:5px;">' . $metadata['position'][0] . '</div>';
                if ( $metadata['telephone'][0] != NULL ) {
                    $output .= '<div style="padding-bottom:5px;">' . __('Telefon:', 'employee') . ' ' . $metadata['telephone'][0].'</div>';
                }else{
                    $output .= '<div style="padding-bottom:5px;">&nbsp;</div>';
                }
                if ( $metadata['mobile'][0] != NULL ) {
                    $output .= '<div style="padding-bottom:5px;">' . __('Mobil:', 'employee') . ' ' . $metadata['mobile'][0].'</div>';
                }else{
                    $output .= '<div style="padding-bottom:5px;">&nbsp;</div>';
                }
                if ( $metadata['email'][0] != NULL ) {
                    $output .= '<div style="padding-bottom:20px;">' . $email.'</div>';
                }
                $output .= '</div>';
                if($cnt1== $employee['per-line']) {
                    $output .= "</div>";
                    $output .= "<div style=\"clear:both;\"></div>";
                    $cnt1=0;
                }
            }
        }
        break;
    }
    wp_reset_postdata();
    return $output;
}
