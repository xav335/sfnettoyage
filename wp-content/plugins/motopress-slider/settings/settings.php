<?php
require_once ABSPATH . 'wp-admin/includes/plugin.php';

$mpsl_settings = array();
global $wpdb;

$mpsl_settings['debug'] = false;
$mpsl_settings['prefix'] = 'mpsl_';
$mpsl_settings['admin_url'] = get_admin_url();
$mpsl_settings['plugin_root'] = WP_PLUGIN_DIR;
$mpsl_settings['plugin_root_url'] = plugins_url();
$mpsl_settings['plugin_name'] = 'motopress-slider';
$mpsl_settings['plugin_dir_path'] = $mpsl_settings['plugin_root'].'/'.$mpsl_settings['plugin_name'];
$pluginData = get_plugin_data($mpsl_settings['plugin_dir_path'].'/'.$mpsl_settings['plugin_name'].'.php', false, false);
$mpsl_settings['plugin_version'] = $pluginData['Version'];
$mpsl_settings['plugin_author'] = $pluginData['Author'];
$mpsl_settings['plugin_dir_url'] = plugin_dir_url($mpsl_plugin_file);
$mpsl_settings['sliders_table'] = $wpdb->prefix . 'mpsl_sliders';
$mpsl_settings['slides_table'] = $wpdb->prefix . 'mpsl_slides';
$mpsl_settings['canjs_version'] = '2.1.4';
$mpsl_settings['shortcode_name'] = 'mpsl';

$wpVersion = get_bloginfo('version');
$wpVersion = (double) $wpVersion;
$mpsl_settings['is_new_wp_version'] = ($wpVersion >= 3.5) ? true : false;

$mpsl_settings['license_type'] = "Personal";
$mpsl_settings['edd_mpsl_store_url'] = $pluginData['PluginURI'];
$mpsl_settings['edd_mpsl_item_name'] = $pluginData['Name'] . ' ' . $mpsl_settings['license_type'];
$mpsl_settings['renew_url'] = $pluginData['PluginURI'] . 'buy/';

$GLOBALS['mpsl_settings'] = $mpsl_settings;

define('MPSL_TEXTDOMAIN', 'mpsl');