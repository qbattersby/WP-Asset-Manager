<?php
/* 
Plugin Name: WP Asset Manager
Plugin URI: http://www.piercecommunications.co.uk
Description: Remove plugin styles and scripts for pages that dont need them.
Author: J Burns 
Version: 1.0
Author URI: http://www.piercecommunications.co.uk 
*/

define('PLUGIN_NAME','WP Asset Manager');
define('PLUGIN_PATH',plugin_dir_url(__FILE__));
define('CURRENT_USER_PATH','http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'/');

if (CURRENT_USER_PATH != PLUGIN_PATH){

    ################################################
    #
    #  Add Menu Links to Sidebar in CMS
    #
    ################################################

    add_action("admin_menu", "wp_plugin_status");

    function wp_plugin_status() {

        // Menu name
        add_menu_page("WP Asset Manager", "Asset Manager", 0, "asset-manager", "asset_manager", PLUGIN_PATH."menu_icon.png");

        // Custom Scripts
        add_submenu_page("asset-manager", "Custom Scripts", "Custom Scripts", 0, "custom-scripts", "custom_scripts");

        // Custom Styles
        add_submenu_page("asset-manager", "Custom Styles", "Custom Styles", 0, "custom-styles", "custom_styles");

        // Hide the first Subpage
        remove_submenu_page('asset-manager','asset-manager');

    }


    function custom_scripts(){
      
        include('views/custom_scripts.php');

    }

    function custom_styles(){
        
        include('views/custom_styles.php');

    }

    add_action( 'wp_print_scripts', 'my_deregister_javascript', 100 );
    add_action( 'wp_print_styles', 'my_deregister_styles', 100 );

    function my_deregister_javascript() {

        global $post;

        $active_plugins = get_option('active_plugins');

        foreach ($active_plugins as $plugins):

            $plugin = explode("/", $plugins);

            $plugin_status = get_post_meta($post->ID, str_replace('-','_',strtolower($plugin[0])), TRUE);

            if ($plugin_status == 0){

                wp_deregister_script($plugin[0]);

            }

        endforeach;

    }

    function my_deregister_styles() {

        global $post;

        $active_plugins = get_option('active_plugins');

        foreach ($active_plugins as $plugins):

            $plugin = explode("/", $plugins);

            $plugin_status = get_post_meta($post->ID, str_replace('-','_',strtolower($plugin[0])), TRUE);

            if ($plugin_status == 0){

                wp_deregister_style($plugin[0]);

            }

        endforeach;

    }

    add_action( 'add_meta_boxes', 'wp_plugin_status_widget' );

    // backwards compatible (before WP 3.0)
    // add_action( 'admin_init', 'wp_plugin_status_widget', 1 );

    /* Do something with the data entered */
    add_action( 'save_post', 'wp_plugin_status_save_postdata' );

    /* Adds a box to the main column on the Post and Page edit screens */
    function wp_plugin_status_widget() {
        $screens = array( 'articles', 'page' );
        foreach ($screens as $screen) {
            add_meta_box(
                'myplugin_sectionid',
                __( 'Asset Manager', 'myplugin_textdomain' ),
                'wp_plugin_status_content',
                $screen
            );
        }
    }

    /* Prints the box content */
    function wp_plugin_status_content( $post ) {

        // Use nonce for verification
        wp_nonce_field( plugin_basename( __FILE__ ), 'myplugin_noncename' );

        // The actual fields for data entry
        
        // Use get_post_meta to retrieve an existing value from the database and use the value for the form
        $value = get_post_meta( $post->ID, '_my_meta_value_key', true );

        echo '<table width="100%" cellpadding="10" cellspacing="0">';

        $custom_scripts = get_option('_wp_custom_script');
        $custom_scripts = unserialize($custom_scripts);

        $custom_styles = get_option('_wp_custom_style');
        $custom_styles = unserialize($custom_styles);

        $wp_plugins = get_option( 'active_plugins' );

        //print_r($active_plugins); exit;

        $i = 1;
        $ii = 0;
        $iii = 0;

        echo '<style>select.green_me { background:#336600; color:#fff; } select.red_me { background:#990000; color:#fff; }</style>';

        echo '<thead>';
        echo '<tr>';
        echo '<td><strong>Type</strong></td>';
        echo '<td><strong>Name</strong></td>';
        echo '<td><strong>Status</strong></td>';
        echo '</tr>';
        echo '</thead>';

        echo '<tbody>';

        foreach ($wp_plugins as $plugins):

            $plugin = explode("/", $plugins);
            $checked = get_post_meta($post->ID, str_replace('-','_',strtolower($plugin[0])), TRUE);
            $is_active = isset($checked) && $checked == 1 ? 'selected="selected"' : '';
            $is_deactive = isset($checked) && $checked == 0 ? 'selected="selected"' : '';
            $color = isset($checked) && $checked == 1 ? 'class="green_me"' : 'class="red_me"';

            $hide_me = isset($plugin[0]) && $plugin[0] == 'wp-plugin-status' ? 'style="display:none;"' : '';

            //print_r($plugin[0]); exit;

            echo '<tr valign="top" '.$hide_me.'>';

            echo '<td width="20%"><label>Plugin</label></br></td>';
            echo '<td width="40%"><label><strong>'.str_replace('-',' ',ucfirst($plugin[0])).'</strong></label></br></td>';

            echo '<td width="40%">';
            echo '<select name="'.$plugin[0].'_'.$i.'" id="'.$plugin[0].'_'.$i.'" '.$color.'>';
            echo '<option value="1" '.$is_active.'>Active</option>';
            echo '<option value="0" '.$is_deactive.'>Inactive</option>';
            echo '</select>';
            echo '</td>';

            echo '</tr>';

        $i++; endforeach;

        if (!empty($custom_scripts)){ foreach ($custom_scripts as $script):

            $active_data = get_post_meta( $post->ID, '_active_scripts', true );
            $active_array = unserialize($active_data);

            $is_active = isset($active_array[$ii]) && $active_array[$ii] == $script ? 'selected="selected"' : '';
            $is_deactive = isset($active_array[$ii]) && $active_array[$ii] == $script ? '' : 'selected="selected"';
            $color = isset($active_array[$ii]) && $active_array[$ii] == $script ? 'class="green_me"' : 'class="red_me"';

            echo '<tr valign="top">';

            echo '<td width="20%"><label>Javascript</label></br></td>';
            echo '<td width="40%"><label><strong>'.$script.'</strong></label></br></td>';

            echo '<td width="40%">';
            echo '<select name="script_'.$ii.'" id="script_'.$ii.'" '.$color.'>';
            echo '<option value="1" '.$is_active.'>Active</option>';
            echo '<option value="0" '.$is_deactive.'>Inactive</option>';
            echo '</select>';
            echo '</td>';

            echo '</tr>';

        $ii++; endforeach; }

        if (!empty($custom_styles)){ foreach ($custom_styles as $style):

            $active_data = get_post_meta( $post->ID, '_active_styles', true );
            $inactive_data = get_post_meta( $post->ID, '_inactive_styles', true );

            $active_array = unserialize($active_data);
            $deactive_array = unserialize($inactive_data);

            $is_active = isset($active_array[$iii]) && $active_array[$iii] == $style ? 'selected="selected"' : '';
            $is_deactive = isset($active_array[$iii]) && $active_array[$iii] == $style ? '' : 'selected="selected"';
            $color = isset($active_array[$iii]) && $active_array[$iii] == $style ? 'class="green_me"' : 'class="red_me"';

            echo '<tr valign="top">';

            echo '<td width="20%"><label>CSS</label></br></td>';
            echo '<td width="40%"><label><strong>'.$style.'</strong></label></br></td>';

            echo '<td width="40%">';
            echo '<select name="css_'.$iii.'" id="css_'.$iii.'" '.$color.'>';
            echo '<option value="1" '.$is_active.'>Active</option>';
            echo '<option value="0" '.$is_deactive.'>Inactive</option>';
            echo '</select>';
            echo '</td>';

            echo '</tr>';

        $iii++; endforeach; }           

        echo '</tbody>';

        echo '</table>';

    }

    /* When the post is saved, saves our custom data */
    function wp_plugin_status_save_postdata( $post_id ) {

        // First we need to check if the current user is authorised to do this action. 
        if ( 'page' == $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_page', $post_id ) )
            return;
        } else {
        
        if ( ! current_user_can( 'edit_post', $post_id ) )
            return;
        }

        // Secondly we need to check if the user intended to change this value.
        if ( ! isset( $_POST['myplugin_noncename'] ) || ! wp_verify_nonce( $_POST['myplugin_noncename'], plugin_basename( __FILE__ ) ) )
          return;


        // Thirdly we can save the value to the database
        $post_ID = $_POST['post_ID'];
        $active_plugins = get_option( 'active_plugins' );

        //Scripts Postdata
        $custom_scripts = get_option('_wp_custom_script');
        $custom_scripts = unserialize($custom_scripts);
        $active_scripts = array();
        $clean_active_scripts = array();

        //Styles Postdata
        $custom_styles = get_option('_wp_custom_style');
        $custom_styles = unserialize($custom_styles);
        $active_styles = array();
        $clean_active_styles = array();  

        $i = 1;
        $ii = 0;
        $iii = 0;


        //////////////////////////////////////////
        //
        // ADD/UPDATE PLUGINS TO DATABASE 
        //
        //////////////////////////////////////////          

        foreach ($active_plugins as $plugins):

            $plugin = explode("/", $plugins);

            $post_data = isset($_POST[$plugin[0].'_'.$i]) && !empty($_POST[$plugin[0].'_'.$i]) ? $_POST[$plugin[0].'_'.$i] : 0;

            add_post_meta($post_ID, str_replace('-','_',strtolower($plugin[0])), $post_data, true) or
            update_post_meta($post_ID, str_replace('-','_',strtolower($plugin[0])), $post_data);

        $i++; endforeach;



        //////////////////////////////////////////
        //
        // ADD/UPDATE SCRIPTS TO DATABASE 
        //
        //////////////////////////////////////////        

        if (!empty($custom_scripts)){  

            foreach ($custom_scripts as $script):

                $post_data = isset($_POST['script_'.$ii]) && !empty($_POST['script_'.$ii]) ? $_POST['script_'.$ii] : 0;

                if ($post_data == 1){

                    $active_scripts[] = $script;
            
                }

            $ii++; endforeach; 

            $clean_active_scripts = serialize($active_scripts);

            add_post_meta($post_ID, '_active_scripts', $clean_active_scripts, true) or update_post_meta($post_ID, '_active_scripts', $clean_active_scripts);

        }


        //////////////////////////////////////////
        //
        // ADD/UPDATE STYLES TO DATABASE 
        //
        //////////////////////////////////////////

        if (!empty($custom_styles)){

            foreach ($custom_styles as $style):

                $post_data = isset($_POST['css_'.$iii]) && !empty($_POST['css_'.$iii]) ? $_POST['css_'.$iii] : 0;

                if ($post_data == 1){

                    $active_styles[] = $style;
            
                }

            $iii++; endforeach;        

            $clean_active_styles = serialize($active_styles);

            add_post_meta($post_ID, '_active_styles', $clean_active_styles, true) or update_post_meta($post_ID, '_active_styles', $clean_active_styles);

        }

    }

} else {

    die();

}
