<?php
/*
Plugin Name: MotoPress Content Editor
Plugin URI: http://www.getmotopress.com/
Description: MotoPress content builder makes the process of post editing easy and fast. Thanks to drag and drop functionality it's possible to manage your article, add different content elements, replace, edit them and see the ready to be published result right in the editor area.
Version: 1.6.2
Author: MotoPress
Author URI: http://www.getmotopress.com/
*/

$motopress_plugin_file = __FILE__;
if ( isset( $network_plugin ) ) {
    $motopress_plugin_file = $network_plugin;
}
if ( isset( $plugin ) ) {
    $motopress_plugin_file = $plugin;
}
require_once 'includes/Requirements.php';
require_once 'includes/settings.php';
require_once 'includes/compatibility.php';

add_action('wp_head', 'motopressCEWpHead', 7);
//add_filter('the_content', 'motopressCEContentWrapper');

// Custom CSS [if exsists]
add_action('wp_head', 'motopressCECustomCSS', 999);
function motopressCECustomCSS(){
    global $motopressCESettings;
    if (!$motopressCESettings['wp_upload_dir_error']) {
        if ( file_exists($motopressCESettings['custom_css_file_path']) ) {
            echo "\n<!-- MotoPress Custom CSS Start -->\n<style type=\"text/css\">\n@import url('".$motopressCESettings['custom_css_file_url']."?".filemtime($motopressCESettings['custom_css_file_path'])."');\n</style>\n<!-- MotoPress Custom CSS End -->\n";
        }
    }
}
// Custom CSS END

if (isset($_GET['motopress-ce']) && $_GET['motopress-ce'] == 1) {
    add_filter('show_admin_bar', '__return_false');
}

//function motopressCEContentWrapper($content) {
//    return '<div class="motopress-content-wrapper">' . $content . '</div>';
//}

function motopressCEWpHead() {
//    global $post;
    global $motopressCESettings;

    wp_register_style('mpce-bootstrap-grid', $motopressCESettings['plugin_dir_url'] . 'bootstrap/bootstrap-grid.min.css', array(), $motopressCESettings['plugin_version']);

//    wp_register_style('mpce-bootstrap-responsive-utility', $motopressCESettings['plugin_dir_url'] . 'bootstrap-responsive-utility.min.css', array(), $motopressCESettings['plugin_version']);
//    wp_enqueue_style('mpce-bootstrap-responsive-utility');

    wp_register_style('mpce-theme', $motopressCESettings['plugin_dir_url'] . 'includes/css/theme.css', array(), $motopressCESettings['plugin_version']);

    /*
    if (
        ($post && !empty($post->post_content) && has_shortcode($post->post_content, 'mp_row')) ||
        MPCEShortcode::isContentEditor()
    ) {
        wp_enqueue_style('mpce-bootstrap-grid');
        wp_enqueue_style('mpce-theme');
    }
    */

    if (!wp_script_is('jquery')) {
        wp_enqueue_script('jquery');
    }

    wp_register_style('mpce-flexslider', $motopressCESettings['plugin_dir_url'] . 'flexslider/flexslider.min.css', array(), $motopressCESettings['plugin_version']);
    wp_register_script('mpce-flexslider', $motopressCESettings['plugin_dir_url'] . 'flexslider/jquery.flexslider-min.js', array('jquery'), $motopressCESettings['plugin_version']);
    wp_register_style('mpce-font-awesome', $motopressCESettings['plugin_dir_url'] . 'fonts/font-awesome/css/font-awesome.min.css', array(), '4.3.0');
    /*wp_register_script('mpce-theme', $motopressCESettings['plugin_dir_url'] . 'includes/js/theme.js', array('jquery'), $motopressCESettings['plugin_version']);
    wp_enqueue_script('mpce-theme');*/

    wp_register_script('google-charts-api', 'https://www.google.com/jsapi');
    wp_register_script('mp-google-charts', $motopressCESettings['plugin_dir_url'] . 'includes/js/mp-google-charts.js', array('jquery','google-charts-api'), $motopressCESettings['plugin_version']);

    wp_register_script('mp-social-share', $motopressCESettings['plugin_dir_url'] . 'includes/js/mp-social-share.js' , array('jquery'), $motopressCESettings['plugin_version']);

    wp_register_script('mp-row-fullwidth', $motopressCESettings['plugin_dir_url'] . 'includes/js/mp-row-fullwidth.js', array('jquery'), $motopressCESettings['plugin_version']);
    wp_register_script('mp-video-background', $motopressCESettings['plugin_dir_url'] . 'includes/js/mp-video-background.js', array('jquery'), $motopressCESettings['plugin_version']);
    wp_register_script('mp-youtube-api', '//www.youtube.com/player_api');
    wp_register_script('stellar', $motopressCESettings['plugin_dir_url'] . 'stellar/jquery.stellar.min.js', array('jquery'), $motopressCESettings['plugin_version']);
    wp_register_script('mp-row-parallax', $motopressCESettings['plugin_dir_url'] . 'includes/js/mp-row-parallax.js', array('jquery', 'stellar'), $motopressCESettings['plugin_version']);
    wp_register_script('magnific-popup', $motopressCESettings['plugin_dir_url'] . 'magnific-popup/magnific-popup.min.js', array('jquery'), $motopressCESettings['plugin_version']);
    wp_register_style('magnific-popup', $motopressCESettings['plugin_dir_url'] . 'magnific-popup/magnific-popup.min.css', null, $motopressCESettings['plugin_version']);
    wp_register_script('mp-lightbox', $motopressCESettings['plugin_dir_url'] . 'includes/js/mp-lightbox.js', array('jquery', 'magnific-popup'), $motopressCESettings['plugin_version']);
    wp_register_script('mp-grid-gallery', $motopressCESettings['plugin_dir_url'] . 'includes/js/mp-grid-gallery.js', array('jquery'), $motopressCESettings['plugin_version']);
    $mpGoogleChartsSwitch = array('motopressCE' => '0');
    wp_enqueue_style('mpce-theme');
    wp_enqueue_style('mpce-bootstrap-grid');
    wp_enqueue_style('mpce-font-awesome');

    if (isset($_GET['motopress-ce']) && $_GET['motopress-ce'] == 1) {
//        wp_enqueue_style('mpce-bootstrap-grid');
//        wp_enqueue_style('mpce-theme');
//        wp_deregister_style('mpce-bootstrap-responsive-utility');

        global $wp_scripts;
        $migrate = false;        
        if (version_compare($wp_scripts->registered['jquery']->ver, MPCERequirements::MIN_JQUERY_VER, '<')) {
            $wpjQueryVer = motopressCEGetWPScriptVer('jQuery');
            wp_deregister_script('jquery');
            wp_register_script('jquery', includes_url() . 'js/jquery/jquery.js', array(), $wpjQueryVer);
            wp_enqueue_script('jquery');            

            if (version_compare($wpjQueryVer, '1.9.0', '>')) {
                if (wp_script_is('jquery-migrate', 'registered')) {                    
                    wp_enqueue_script('jquery-migrate', array('jquery'));
                    $migrate = true;
                }
            }
        }

        wp_register_script('mpce-no-conflict', $motopressCESettings['plugin_dir_url'] . 'mp/core/noConflict/noConflict.js', array('jquery'), $motopressCESettings['plugin_version']);
        wp_enqueue_script('mpce-no-conflict');
        $jQueryOffset = array_search('jquery', $wp_scripts->queue) + 1;
        $index = ($migrate) ? array_search('jquery-migrate', $wp_scripts->queue) : array_search('mpce-no-conflict', $wp_scripts->queue);
        $length = $index - $jQueryOffset;
        $slice = array_splice($wp_scripts->queue, $jQueryOffset, $length);
        $wp_scripts->queue = array_merge($wp_scripts->queue, $slice);

/*
        $wpjQueryUIVer = motopressCEGetWPScriptVer('jQueryUI');
        foreach (MPCERequirements::$jQueryUIComponents as $component) {
            if (wp_script_is($component)) {
                if (version_compare($wp_scripts->registered[$component]->ver, MPCERequirements::MIN_JQUERYUI_VER, '<')) {
                    wp_deregister_script($component);
                }
            }
        }
        wp_register_script('mpce-jquery-ui', $motopressCESettings['admin_url'].'load-scripts.php?c=0&load='.implode(',', MPCERequirements::$jQueryUIComponents), array('mpce-no-conflict'), $wpjQueryUIVer);
        wp_enqueue_script('mpce-jquery-ui');
*/

        if (wp_script_is('jquery-ui.min')) wp_dequeue_script('jquery-ui.min'); //fix for theme1530

        wp_register_script('mpce-tinymce', $motopressCESettings['plugin_dir_url'] . 'tinymce/tinymce.min.js', array(), $motopressCESettings['plugin_version']);
        wp_enqueue_script('mpce-tinymce');

        wp_enqueue_style('mpce-flexslider');
        wp_enqueue_script('mpce-flexslider');

        wp_enqueue_script('google-charts-api');
        wp_enqueue_script('mp-google-charts');

        wp_enqueue_style( 'wp-mediaelement' );
        wp_enqueue_script( 'wp-mediaelement' );

        wp_enqueue_script('stellar');
        wp_enqueue_script('mp-row-parallax');
        wp_enqueue_script('mp-youtube-api');
        wp_enqueue_script('mp-video-background');
        wp_enqueue_script('mp-grid-gallery');
        
//        wp_enqueue_style('mpce-font-awesome');

        if (is_plugin_active('motopress-slider/motopress-slider.php')) {
            global $mpsl_settings;
            if (version_compare($mpsl_settings['plugin_version'], '1.1.2', '>=')) {
                global $mpSlider;
                $mpSlider->enqueueScriptsStyles();
            }
        }

        $mpGoogleChartsSwitch = array('motopressCE' => '1');
        
        do_action('mpce_add_custom_scripts');        
        do_action('mpce_add_custom_styles');
    }

    wp_localize_script( 'mp-google-charts', 'motopressGoogleChartsPHPData', $mpGoogleChartsSwitch );
}

require_once 'includes/ce/Shortcode.php';
$shortcode = new MPCEShortcode();
$shortcode->register();

add_action('admin_bar_menu', 'motopressCEAdminBarMenu', 81);

function motopressCEExcerptShortcode() {
    $excerptShortcode = get_option('motopress-ce-excerpt-shortcode', '1');
    if ($excerptShortcode) {
        remove_filter('the_excerpt', 'wpautop');
        add_filter('the_excerpt', 'do_shortcode');
        add_filter('get_the_excerpt', 'do_shortcode');
    }
}

motopressCEExcerptShortcode();

require_once 'includes/ce/Library.php';
require_once 'includes/getLanguageDict.php';

function motopressCEWPInit() {
    if (!is_admin()) {
        if (!isset($motopressCERequirements)) {
            global $motopressCERequirements;
            $motopressCERequirements = new MPCERequirements();}
        if (!isset($motopressCELang)) {
            global $motopressCELang;
            $motopressCELang = motopressCEGetLanguageDict();
        }                
        $motopressCELibrary = getMotopressCELibrary();        
    }
}
add_action('init', 'motopressCEWPInit');

function motopressSetBrandName(){
    global $motopressCESettings;
    $motopressCESettings['brand_name'] = apply_filters('mpce_brand_name', 'MotoPress');    
}
add_action('after_setup_theme', 'motopressSetBrandName');

if (!is_admin()) {
    add_action('wp', array('MPCEShortcode', 'setCurPostData'));
    return;
}

require_once 'contentEditor.php';
require_once 'motopressOptions.php';
//require_once 'includes/settings.php';
require_once 'includes/Flash.php';
//require_once 'includes/AutoUpdate.php';
require_once 'includes/ce/Tutorials.php';
if (!class_exists('EDD_MPCE_Plugin_Updater')) {
    require_once 'includes/EDD_MPCE_Plugin_Updater.php';
}

add_action('admin_init', 'motopressCEInit');
add_action('admin_menu', 'motopressCEMenu', 11);
add_action('save_post', 'motopressCESave', 10, 2);

function motopressCEInit() {
    global $motopressCESettings;

    wp_register_style('mpce-style', $motopressCESettings['plugin_dir_url'] . 'includes/css/style.css', array(), $motopressCESettings['plugin_version']);
    wp_register_script('mpce-detect-browser', $motopressCESettings['plugin_dir_url'].'mp/core/detectBrowser/detectBrowser.js', array(), $motopressCESettings['plugin_version']);

    wp_enqueue_script('mpce-detect-browser');

    //new MPCEAutoUpdate($motopressCESettings['plugin_version'], $motopressCESettings['update_url'], $motopressCESettings['plugin_name'].'/'.$motopressCESettings['plugin_name'].'.php');

    //add_action('in_plugin_update_message-'.$motopressCESettings['plugin_name'].'/'.$motopressCESettings['plugin_name'].'.php', 'motopressCEAddUpgradeMessageLink', 20, 2);
    $isDisableUpdater = apply_filters('mpce_disable_updater', false);
    if (!$isDisableUpdater) {
        new EDD_MPCE_Plugin_Updater($motopressCESettings['edd_mpce_store_url'], __FILE__, array(
            'version' => $motopressCESettings['plugin_version'], // current version number
            'license' => get_option('edd_mpce_license_key'), // license key (used get_option above to retrieve from DB)
            'item_name' => $motopressCESettings['edd_mpce_item_name'], // name of this plugin
            'author' => $motopressCESettings['plugin_author'] // author of this plugin
        ));
    }    

    motopressCERegisterHtmlAttributes();

    if (!is_array(get_option('motopress_google_font_classes'))){
        add_action('admin_notices', 'motopress_google_font_not_writable_notice');
        $fontClasses = array(
            'opensans' => array(
                'family' => 'Open Sans',
                'variants' => array('300', 'regular', '700')
            )
        );
        saveGoogleFontClasses($fontClasses);
    }
}

function motopress_google_font_not_writable_notice(){
    global $motopressCELang;
    $error = motopress_check_google_font_dir_permissions();
    if (isset($error['error'])) {
        echo '<div class="error"><p>' . $motopressCELang->CENoticeDefaultGoogleFontError . '</p><p>' . $error['error'] . '</p></div>';
    }
}

/**
 * Check permissions for writing Google Font's style files.
 *
 * @param boolean $mkdir creates the necessary directories
 * @return array $error
 */
function motopress_check_google_font_dir_permissions($mkdir = false){
    global $motopressCESettings;
    global $motopressCELang;
    $error = array();
    if ( !is_dir($motopressCESettings['google_font_classes_dir'])) {
        if (!is_dir($motopressCESettings['motopress_upload_dir'])) {
            if (is_writable($motopressCESettings['wp_upload_dir'])){
                if ($mkdir) {
                    mkdir($motopressCESettings['motopress_upload_dir'], 0777);
                    mkdir($motopressCESettings['google_font_classes_dir'], 0777);
                }
            } else {
                $error['error'] = str_replace( '%dir%', $motopressCESettings['wp_upload_dir'], $motopressCELang->CEOptMsgGoogleFontNotWritable );
            }
        } elseif(is_writable($motopressCESettings['motopress_upload_dir'])){
            if ($mkdir) {
                mkdir($motopressCESettings['google_font_classes_dir'], 0777);
            }
        } else {
            $error['error'] =  str_replace( '%dir%', $motopressCESettings['motopress_upload_dir'], $motopressCELang->CEOptMsgGoogleFontNotWritable );
        }
    }
    if (!isset($error['error']) && !is_writable($motopressCESettings['google_font_classes_dir'])){
        $error['error'] = str_replace( '%dir%', $motopressCESettings['google_font_classes_dir'], $motopressCELang->CEOptMsgGoogleFontNotWritable );
    }

    return $error;
}

/*
function motopressCEAddUpgradeMessageLink($plugin_data, $r) {
    global $motopressCELang;
    echo ' ' . strtr($motopressCELang->CEDownloadMessage, array('%link%' => $r->url));
}
*/

function motopressCERegisterHtmlAttributes() {
    global $allowedposttags;

    if (isset($allowedposttags['div']) && is_array($allowedposttags['div'])) {
        $attributes = array_fill_keys(array_values(MPCEShortcode::$attributes), true);
        $allowedposttags['div'] = array_merge($allowedposttags['div'], $attributes);
    }
}

//add_filter('tiny_mce_before_init', 'motopressCERegisterTinyMCEHtmlAttributes', 10, 1);
// this func override valid_elements of tinyMCE.
// If you need to use this function you will set all html5 attrs in addition to motopress-attributes
//function motopressCERegisterTinyMCEHtmlAttributes($options) {
//    global $motopressCESettings;
//
//    if (!isset($options['extended_valid_elements'])) {
//        $options['extended_valid_elements'] = '';
//    }
//
//    $attributes = array_values(MPCEShortcode::$attributes);
//    //html5attrs must contain all valid html5 attributes
//    $html5attrs = array('class', 'id', 'align', 'style');
//    if (strpos($options['extended_valid_elements'], 'div[')) {
//        $attributesStr = implode('|', $attributes);
//        $options['extended_valid_elements'] .= preg_replace('/div\[([^\]]*)\]/', 'div[$1|' . $attributesStr . ']', $options['extended_valid_elements']);
//    } else {
//        array_push($attributes, $html5attrs);
//        $attributesStr = implode('|', $attributes);
//        $options['extended_valid_elements'] .= ',div[' . $attributesStr . ']';
//    }
//
//    return $options;
//}

function motopressCEMenu() {
    require_once 'includes/ce/Access.php';
    $ceAccess = new MPCEAccess();    

    if ( !$ceAccess->isCEDisabledForCurRole() ) {        
        global $motopressCESettings;
        global $motopressCELang;
        $motopressCELang = motopressCEGetLanguageDict();
        global $motopressCERequirements;
        $motopressCERequirements = new MPCERequirements();
        global $motopressCEIsjQueryVer;
        $motopressCEIsjQueryVer = motopressCECheckjQueryVer();
        
        $isHideMenu = apply_filters( 'mpce_hide_menu_page', false );
        if (!$isHideMenu) {
            $mainMenuSlug = 'motopress';
        
            $mainMenuExists = has_action('admin_menu', 'motopressMenu');            
            if (!$mainMenuExists) {
                $iconSrc = apply_filters('mpce_menu_icon_src', $motopressCESettings['plugin_dir_url'] . 'images/menu-icon.png');                                           
                $mainPage = add_menu_page($motopressCESettings['brand_name'], $motopressCESettings['brand_name'], 'read', $mainMenuSlug, 'motopressCE', $iconSrc);
            } else {
                $optionsHookname = get_plugin_page_hookname('motopress_options', $mainMenuSlug);
                remove_action($optionsHookname, 'motopressOptions');
                remove_submenu_page('motopress', 'motopress_options');
            }
            $menuTitle = apply_filters('mpce_submenu_title', $motopressCELang->CE);
            $mainPage = add_submenu_page($mainMenuSlug, $menuTitle, $menuTitle, 'read', $mainMenuExists ? 'motopress_content_editor' : 'motopress', 'motopressCE');
            $hideOptions = get_site_option('motopress-ce-hide-options-on-subsites', '0');
            if ($hideOptions === '0' || (is_multisite() && is_main_site()) ) {
                $optionsPage = add_submenu_page($mainMenuSlug, $motopressCELang->motopressOptions, $motopressCELang->motopressOptions, 'manage_options', 'motopress_options', 'motopressCEOptions');
                add_action('load-' . $optionsPage, 'motopressCESettingsSave');
                add_action('admin_print_styles-' . $optionsPage, 'motopressCEAdminStylesAndScripts');
	            do_action('admin_mpce_settings_init', $optionsPage);
            }
            $isHideLicensePage = apply_filters( 'mpce_hide_license_page', false );         
            if (!$isHideLicensePage && is_main_site()) {
                $licensePage = add_submenu_page($mainMenuSlug, $motopressCELang->CELicense, $motopressCELang->CELicense, 'manage_options', 'motopress_license', 'motopressCELicense');
                add_action('load-' . $licensePage, 'motopressCELicenseSave');
                add_action('admin_print_styles-' . $licensePage, 'motopressCEAdminStylesAndScripts');
                do_action('admin_mpce_license_init', $optionsPage);
            }            
            add_action('admin_print_styles-' . $mainPage, 'motopressCEAdminStylesAndScripts');              
        }
        
        add_action('admin_print_scripts-post.php', 'motopressCEAddTools');
        add_action('admin_print_scripts-post-new.php', 'motopressCEAddTools');             
    }
}

function motopressCESave($postId, $post) {
    global $motopressCESettings;

    if (
        isset($_POST['motopress-ce-edited-post']) &&
        !empty($_POST['motopress-ce-edited-post']) &&
        $postId === (int) $_POST['motopress-ce-edited-post']
    ) {
        update_post_meta($postId, 'motopress-ce-save-in-version', $motopressCESettings['plugin_version']);
    }
}

function motopressCEAdminBarMenu($wp_admin_bar) {
    if (is_admin_bar_showing() && !is_admin() && !is_preview()) {
        global $wp_the_query, $motopressCESettings;
        $current_object = $wp_the_query->get_queried_object();
        if (!empty($current_object) &&
            !empty($current_object->post_type) &&
            ($post_type_object = get_post_type_object($current_object->post_type)) &&
            $post_type_object->show_ui && $post_type_object->show_in_admin_bar
        ) {
            require_once 'includes/ce/Access.php';
            $ceAccess = new MPCEAccess();

            $postType = get_post_type();
            $postTypes = get_option('motopress-ce-options');
            if (!$postTypes) $postTypes = array();

            if (in_array($postType, $postTypes) && post_type_supports($postType, 'editor') && $ceAccess->hasAccess($current_object->ID)) {
                require_once 'includes/getLanguageDict.php';
                $motopressCELang = motopressCEGetLanguageDict();
                
                $isHideLinkEditWith = apply_filters('mpce_hide_link_edit_with', false);
                if (!$isHideLinkEditWith) {                    
                    $wp_admin_bar->add_menu(array(
                        'href' => get_edit_post_link($current_object->ID) . '&motopress-ce-auto-open=true',
                        'parent' => false,
                        'id' => 'motopress-edit',
                        'title' => strtr($motopressCELang->CEAdminBarMenu, array('%BrandName%' => $motopressCESettings['brand_name'])),
                        'meta' => array(
                            'title' => strtr($motopressCELang->CEAdminBarMenu, array('%BrandName%' => $motopressCESettings['brand_name'])),
                            'onclick' => 'sessionStorage.setItem("motopressPluginAutoOpen", true);'
                        )
                    ));
                }
            }
        }
    }
}

function motopressCEAdminStylesAndScripts() {
	global $motopressCESettings;
	$pluginId = isset($_GET['plugin']) ? $_GET['plugin'] : $motopressCESettings['plugin_short_name'];

    wp_enqueue_style('mpce-style');
	do_action('admin_mpce_settings_print_styles-' . $pluginId);
}

function motopressCE() {
    motopressCEShowWelcomeScreen();
}

function motopressCEShowWelcomeScreen() {
    global $motopressCESettings;
    global $motopressCELang;
    echo '<div class="motopress-title-page">';
    $logoLargeSrc = apply_filters('mpce_large_logo_src', $motopressCESettings['plugin_dir_url'].'images/logo-large.png?ver='.$motopressCESettings['plugin_version']);
    echo '<img id="motopress-logo" src="' . esc_url($logoLargeSrc) . '" />';
    $siteUrl = apply_filters('mpce_wl_site_url', 'http://www.getmotopress.com');
    $siteName = apply_filters('mpce_wl_site_name', 'getmotopress.com');    
    echo '<p class="motopress-description">' . strtr($motopressCELang->motopressDescription, array('%BrandName%' => $motopressCESettings['brand_name'], '%link%' => $siteUrl, '%siteName%' => $siteName)) . '</p>';

    global $motopressCEIsjQueryVer;
    if (!$motopressCEIsjQueryVer) {
        MPCEFlash::setFlash(strtr($motopressCELang->jQueryVerNotSupported, array('%minjQueryVer%' => MPCERequirements::MIN_JQUERY_VER, '%minjQueryUIVer%' => MPCERequirements::MIN_JQUERYUI_VER)), 'error');
    }

    echo '<p><div class="alert alert-error" id="motopress-browser-support-msg" style="display:none;">'.$motopressCELang->browserNotSupported.'</div></p>';

    $foundCEButtonDesc = apply_filters('mpce_found_button_description', $motopressCELang->CEDescription);
    echo '<div class="motopress-block"><p class="motopress-title">' . $foundCEButtonDesc . '</p>';
    $foundButtonImageSrc = apply_filters('mpce_found_button_img_src', $motopressCESettings['plugin_dir_url'].'images/ce/ce.png?ver='.$motopressCESettings['plugin_version']);
    echo '<a href="'.admin_url('post-new.php?post_type=page').'" target="_self" id="motopress-ce-link"><img id="motopress-ce" src="' . esc_url($foundButtonImageSrc) . '" /></a></div>';

	?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            if (Browser.IE || Browser.Opera) {
//                $('#motopress-layout-editor-btn').hide();
                $('.motopress-block #motopress-ce-link')
                    .attr('href', 'javascript:void(0);')
                    .css({ cursor: 'default' });
                $('#motopress-browser-support-msg').show();
            }
        });
    </script>
    <?php
}

// Plugin Activation
function motopressCEInstall($network_wide) {
    global $wpdb;
    if ( is_multisite() && $network_wide ) {
        $blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
        foreach ($blogids as $blog_id) {
            switch_to_blog($blog_id);
            motopressActivationDefaults();
        }
    }else {
        motopressActivationDefaults();
    }
    $autoLicenseKey = apply_filters('mpce_auto_license_key', false);        
    if ($autoLicenseKey) {          
        motopressCESetAndActivateLicense($autoLicenseKey);
    }
}

function motopressActivationDefaults() {
    add_option('motopress-language', 'en.json');
    add_option('motopress-ce-options', array('post', 'page'));
}

register_activation_hook(__FILE__, 'motopressCEInstall');
// Plugin Activation END

function motopressCECheckjQueryVer() {
    $jQueryVer = motopressCEGetWPScriptVer('jQuery');
    $jQueryUIVer = motopressCEGetWPScriptVer('jQueryUI');
    
    return (version_compare($jQueryVer, MPCERequirements::MIN_JQUERY_VER, '>=') && version_compare($jQueryUIVer, MPCERequirements::MIN_JQUERYUI_VER, '>=')) ? true : false;
}

function motopressCEGetWPScriptVer($script) {
    global $wp_version;    
    $ver = false;
    $path = ABSPATH . WPINC;
    $versionPattern = '/v((\d+\.{1}){1}(\d+){1}(\.{1}\d+)?)/is';
    switch ($script) {
        case 'jQuery':
            $path .= '/js/jquery/jquery.js';
            break;
     case 'jQueryUI':
        if (version_compare($wp_version, '4.1', '<')) {
            $path .= '/js/jquery/ui/jquery.ui.core.min.js';
        } else {            
            $path .= '/js/jquery/ui/core.min.js';            
            $versionPattern = '/jQuery UI Core ((\d+\.{1}){1}(\d+){1}(\.{1}\d+)?)/is';
        }        
        break;
    }
    
    if (is_file($path)) {        
        if (file_exists($path)) {
            $content = file_get_contents($path);            
            if ($content) {                                
                preg_match($versionPattern, $content, $matches);                
                if (!empty($matches[1])) {
                    $ver = $matches[1];
                }
            }
        }
    }
    return $ver;
}

function motopress_edit_link($actions, $post){
    global $motopressCELang, $motopressCESettings;
    require_once 'includes/ce/Access.php';
    $ceAccess = new MPCEAccess();
    $ceEnabledPostTypes = get_option('motopress-ce-options', array());    
    $isHideLinkEditWith = apply_filters('mpce_hide_link_edit_with', false);

    if (!$isHideLinkEditWith && $ceAccess->hasAccess($post->ID) && in_array( $post->post_type, $ceEnabledPostTypes ) ){

        $newActions = array();

        foreach ($actions as $action => $value) {
            $newActions[$action] = $value;
            if ($action === 'inline hide-if-no-js') {
                $newActions['motopress_edit_link'] = '<a href="' . get_edit_post_link( $post->ID, true ) . '" title="' . esc_attr(strtr($motopressCELang->CEAdminBarMenu, array('%BrandName%' => $motopressCESettings['brand_name']))) . '" onclick="sessionStorage.setItem(&quot;motopressPluginAutoOpen&quot;, true);">' . strtr($motopressCELang->CEAdminBarMenu, array('%BrandName%' => $motopressCESettings['brand_name'])) . '</a>';
            }
        }

        return $newActions;
    } else {
        return $actions;
    }

}
add_filter('page_row_actions', 'motopress_edit_link', 10, 2);
add_filter('post_row_actions', 'motopress_edit_link', 10, 2);

function motopressCELicenseNotice() {
    global $pagenow, $motopressCESettings, $motopressCELang;
    $isDisableUpdater = apply_filters('mpce_disable_updater', false);
    if ($pagenow === 'plugins.php' && is_main_site() && !$isDisableUpdater) {
        $isHideLicenseNotice = get_option('mpce_hide_license_notice', false);
        if (!$isHideLicenseNotice) {
            $license = get_option('edd_mpce_license_key');
            if ($license) {
                $licenseData = edd_mpce_check_license($license);
            }
            if (!$license || !isset($licenseData['data']->license) || $licenseData['data']->license !== 'valid' ) {                
                $dismissActionName = 'motopress_ce_dismiss_license_notice';                
                echo '<div class="error"><a id="mpce-dismiss-license-notice" href="javascript:void(0);" style="float: right;padding-top: 9px; text-decoration: none;">' . $motopressCELang->CELicenseNoticeDismiss . '<strong>X</strong></a><p>' . strtr($motopressCELang->CELicenseNotice, array('%link%' => admin_url('admin.php?page=motopress_license'), '%BrandName%' => $motopressCESettings['brand_name'])) . '</p></div>'; ?>
                <script type="text/javascript">
                    (function($){
                        var dismissBtn = $('#mpce-dismiss-license-notice');
                        dismissBtn.one('click', function(){
                            $.ajax({
                                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                                type: 'POST',                                
                                data: {
                                    action: '<?php echo $dismissActionName; ?>',
                                    nonce: '<?php echo wp_create_nonce('wp_ajax_' . $dismissActionName);?>',
                                }                            
                            });
                            dismissBtn.closest('div.error').remove();
                        });                                
                    })(jQuery);                                
                </script>
                <?php
            }
        }        
    }
}
add_action('admin_notices', 'motopressCELicenseNotice');
if (is_multisite()) add_action('network_admin_notices', 'motopressCELicenseNotice');

function motopressCELicenseNoticeDismiss(){
    require_once dirname(__FILE__).'/includes/verifyNonce.php';
    update_option('mpce_hide_license_notice', true);
}
add_action('wp_ajax_motopress_ce_dismiss_license_notice', 'motopressCELicenseNoticeDismiss');
