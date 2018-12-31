<?php
/*
Plugin Name: MotoPress Slider
Plugin URI: http://www.getmotopress.com/
Description: Responsive MotoPress Slider for your WordPress theme. This plugin is all you need for creating beautiful slideshows, smooth transitions, effects and animations. Easy navigation, intuitive interface and responsive layout.
Version: 1.1.4
Author: MotoPress
Author URI: http://www.getmotopress.com/
*/
if ( ! defined( 'ABSPATH' ) ) exit;

$mpsl_plugin_file = __FILE__;
if ( isset( $network_plugin ) ) {
    $mpsl_plugin_file = $network_plugin;
}
if ( isset( $plugin ) ) {
    $mpsl_plugin_file = $plugin;
}

require_once 'settings/settings.php';
require_once 'MPSLDB.php';
require_once 'includes/classes/SliderAPI.php';
require_once 'MPSLOptions.php';
require_once 'OptionsFactory.php';
require_once 'SliderOptions.php';
require_once 'SlideOptions.php';
require_once 'List.php';
require_once 'SlidersList.php';
require_once 'SlidesList.php';
require_once 'includes/classes/YoutubeDataApi.php';
require_once 'includes/classes/VimeoOEmbedApi.php';

if (is_admin()) {
    require_once 'functions.php';
    require_once 'license.php';
    require_once 'pluginOptions.php';
    require_once 'EDD_MPSL_Plugin_Updater.php';
}

class MPSLAdmin {
    const SLIDERS_TABLE = 'mpsl_sliders';
    const SLIDES_TABLE = 'mpsl_slides';
    public $pageController;

    private $mpsl_settings;
    private $menuHook;
    private $licenseHook = null;
    private $licenseController;
    private $pluginDir;

    public function __construct() {
        global $mpsl_settings;
        $this->pluginDir = $mpsl_settings['plugin_dir_path'] . '/';
        $this->mpsl_settings = &$mpsl_settings;
        $this->initLicenseController();
        $this->initPluginOptionsController();
        $this->addActions();
        $this->initPageController();
    }

    private function initPageController(){
        if (isset($_GET['page']) && $_GET['page'] === $this->mpsl_settings['plugin_name']) {
            $view = isset($_GET['view']) ? $_GET['view'] : null;
            switch($view) {
                case 'slider' :                     
                    require_once $this->pluginDir . 'SliderOptions.php';
                    $id = isset($_GET['id']) ? $_GET['id'] : null;
                    if (!is_null($id) && !MPSLSliderOptions::isIdExists($id)) {
                        wp_die(sprintf(__('Slider with id %s is not exists!', MPSL_TEXTDOMAIN), $id));
//                                    $id = null;
                    }
                    $slider = new MPSLSliderOptions($id);
//                                    $slider->override($id, false, false);
                    $this->pageController = $slider;
                    break;
                case 'slide' :                      
                    $id = isset($_GET['id']) ? $_GET['id'] : null;
                    $slider = new MPSLSlideOptions($id);
                    $this->pageController = $slider;
                    break;
                case 'slides' :                     
                    $id = isset($_GET['id']) ? $_GET['id'] : null;
                    $slides = new MPSLSlidesList($id);
                    $this->pageController = $slides;
                    break;
                case 'export' :
                    add_action('admin_init', array($this, 'exportSliders'));                    
                    break;      
                default: 
                    $sliders = new MPSLSlidersList();
                    $this->pageController = $sliders;
                    break;
            }
        }
    }

    private function initLicenseController(){
        $isDisableUpdater = apply_filters('mpsl_disable_updater', false);
        if (!$isDisableUpdater) {
            new EDD_MPSL_Plugin_Updater($this->mpsl_settings['edd_mpsl_store_url'], __FILE__, array(
                'version' => $this->mpsl_settings['plugin_version'], // current version number
                'license' => get_option('edd_mpsl_license_key'), // license key (used get_option above to retrieve from DB)
                'item_name' => $this->mpsl_settings['edd_mpsl_item_name'], // name of this plugin
                'author' => $this->mpsl_settings['plugin_author'] // author of this plugin
            ));
        }
        $this->licenseController = new MPSLLicense();
    }

    private function initPluginOptionsController(){
        $this->pluginOptionsController = new MPSLPluginOptions();
    }
    
    public static function install($network_wide){
        global $wpdb;
        
        $autoLicenseKey = apply_filters('mpsl_auto_license_key', false);        
        if ($autoLicenseKey) {  
            MPSLLicense::setAndActivateLicenseKey($autoLicenseKey);            
        }

        if (is_multisite() && $network_wide) {
            // store the current blog id
            $current_blog = $wpdb->blogid;

            // Get all blogs in the network and activate plugin on each one
            $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
            foreach ( $blog_ids as $blog_id ) {
                switch_to_blog( $blog_id );
                self::createTables();
                restore_current_blog();
            }
        } else {
            self::createTables();
        }
        
    }
    
    public static function onCreateBlog($blog_id, $user_id, $domain, $path, $site_id, $meta){
        if ( is_plugin_active_for_network( 'motopress-slider/motopress-slider.php' ) ) {
            switch_to_blog( $blog_id );
            self::createTables();
            restore_current_blog();            
        }
    }
    
    public static function onDeleteBlog($tables){
        global $wpdb;
        $tables[] = $wpdb->prefix . self::SLIDERS_TABLE;
        $tables[] = $wpdb->prefix . self::SLIDES_TABLE;
        return $tables;
    }
    
    public static function createTables(){
        global $wpdb;
        $slidersTableRes = $wpdb->query(sprintf(
            'CREATE TABLE IF NOT EXISTS %s (
                id int(9) NOT NULL AUTO_INCREMENT,
                title tinytext NOT NULL,
                alias tinytext NULL,
                options text NOT NULL,
                PRIMARY KEY (id)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;',
            $wpdb->prefix . self::SLIDERS_TABLE
        ));
        if (!$slidersTableRes) {
            //@todo show error message
//            MPSLMessages::error(printf(__('Table %1$s', MPSL_TEXTDOMAIN), $this->mpsl_settings['sliders_table']));
        }

        $slidesTableRes = $wpdb->query(sprintf(
            'CREATE TABLE IF NOT EXISTS %s (
                id int(9) NOT NULL AUTO_INCREMENT,
                slider_id int(9) NOT NULL,
                slide_order int(11) NOT NULL,
                options text NOT NULL,
                layers text NOT NULL,
                PRIMARY KEY (id)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;',
            $wpdb->prefix . self::SLIDES_TABLE
        ));
        if (!$slidesTableRes) {
            //@todo show error message
        }
    }

    public function fixAfterUpdate() {
        include_once('includes/fix-after-update.php');
        mpslFixAfterUpdate();
    }

    public function adminPrintStyles() {
        /*if (get_option('_mpsl_needs_update') == 1) {
            //wp_enqueue_style('mpsl-update', plugins_url($this->pluginDir . '/css/activation.css', dirname(__FILE__)));
            add_action('admin_notices', array($this, 'adminUpdateNotices'));
        }*/
        add_action('admin_notices', array($this, 'adminNotices'));
    }

    public function adminNotices() {
        if (get_option('_mpsl_needs_update') == 1) {
            include($this->pluginDir . 'includes/notices/update.php');

        } elseif (!empty($_GET['mpsl-updated'])) {
            include($this->pluginDir . 'includes/notices/updated.php');
        }
    }

    /*public function adminUpdateNotices() {
        if (get_option('_mpsl_needs_update') == 1) {
            include($this->pluginDir . 'includes/notices/update.php');
        }
    }*/

    public function adminInit() {
        global $mpsl_settings;
        register_importer( 'mpsl-importer', $mpsl_settings['product_name'], sprintf(__( 'Import sliders and images from a %s export file.', MPSL_TEXTDOMAIN ), $mpsl_settings['product_name']), array( $this, 'importPageRender' ) );
        if (!empty($_GET['mpsl_do_update'])) {
            include_once($this->pluginDir . 'includes/update.php');
            mpslDoUpdate();

            // Update complete
            delete_option('_mpsl_needs_update');

            wp_safe_redirect(admin_url("admin.php?page={$this->mpsl_settings['plugin_name']}&mpsl-updated=true"));
            exit;
        }
    }

    private function addActions(){
        global $mpsl_settings;
        
        add_action('after_setup_theme', array($this, 'setProductName'));
        add_action('plugins_loaded', array($this, 'loadTextdomain'));
        add_action('admin_menu', array($this, 'mpslMenu'), 11);
        if (get_option('mpsl_version') != $mpsl_settings['plugin_version']) add_action('init', array($this, 'fixAfterUpdate'), 1);
        add_action('admin_init', array($this, 'adminInit'));
        add_action('admin_print_styles', array($this, 'adminPrintStyles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueueAdminStylesAndScripts'), 10);

        //AJAX
        add_action('wp_ajax_mpsl_update_slider', array($this, 'updateSliderCallback'));
        add_action('wp_ajax_mpsl_create_slider', array($this, 'createSliderCallback'));
        add_action('wp_ajax_mpsl_delete_slider', array($this, 'deleteSliderCallback'));
        add_action('wp_ajax_mpsl_duplicate_slider', array($this, 'duplicateSliderCallback'));
        add_action('wp_ajax_mpsl_update_slide', array($this, 'updateSlideCallback'));
        add_action('wp_ajax_mpsl_create_slide', array($this, 'createSlideCallback'));
        add_action('wp_ajax_mpsl_delete_slide', array($this, 'deleteSlideCallback'));
        add_action('wp_ajax_mpsl_duplicate_slide', array($this, 'duplicateSlideCallback'));
        add_action('wp_ajax_mpsl_update_slides_order', array($this, 'updateSlidesOrderCallback'));
        add_action('wp_ajax_mpsl_check_alias_exists', array($this, 'checkAliasExistsCallback'));
        add_action('wp_ajax_mpsl_get_youtube_thumbnail', array($this, 'getYoutubeThumbnailCallback'));
        add_action('wp_ajax_mpsl_get_vimeo_thumbnail', array($this, 'getVimeoThumbnailCallback'));        
    }
    
    public function setProductName(){
        global $mpsl_settings;
        $mpsl_settings['product_name'] = apply_filters('mpsl_product_name', 'MotoPress Slider');    
    }

    public function enqueueAdminStylesAndScripts($hook) {        
        if (isset($this->menuHook) && $hook === $this->menuHook) {
            global $mpsl_settings;
            wp_register_script('jquery-ui-touch', $mpsl_settings['plugin_dir_url'] . 'jqueryui/jquery-ui-touch/jquery.ui.touch-punch.min.js', array('jquery-ui-widget', 'jquery-ui-mouse'), '0.2.3');
            $page = isset($_GET['view']) ? $_GET['view'] : 'sliders';
            $prefix = (is_ssl()) ? 'https://' : 'http://';

            $deps = array('jquery');
            
            if ($page === 'sliders') {
                wp_enqueue_script('jquery-ui-dialog');                
            }

            if ($page === 'slides') {
                wp_enqueue_script('jquery-ui-sortable');
                if (wp_is_mobile()) {
                    wp_enqueue_script('jquery-ui-touch');
                    $deps[] = 'jquery-ui-touch';
                }       
            }

            if ($page === 'slider') {
                wp_enqueue_script('jquery-ui-core');
                wp_enqueue_script('jquery-ui-widget');
                wp_enqueue_script('jquery-ui-tabs');
                $codeMirrorVer = '3.12';
                wp_enqueue_script('codemirror', $mpsl_settings['plugin_dir_url'] . '/codemirror/lib/codemirror.js', array('jquery'), $codeMirrorVer);
                wp_enqueue_style('codemirror', $mpsl_settings['plugin_dir_url'] . '/codemirror/lib/codemirror.css', array(), $codeMirrorVer);
                wp_enqueue_script('codemirror-css', $mpsl_settings['plugin_dir_url'] . '/codemirror/mode/css/css.js', array('codemirror'), $codeMirrorVer);
//                wp_enqueue_script('codemirror-js', $mpsl_settings['plugin_dir_url'] . '/codemirror/mode/javascript/javascript.js', array('codemirror'), $codeMirrorVer);
            }

            if ($page === 'slide') {
                wp_enqueue_script('jquery-ui-core');
                wp_enqueue_script('jquery-ui-widget');
                wp_enqueue_script('jquery-ui-mouse');
                wp_enqueue_script('jquery-ui-draggable');
                wp_enqueue_script('jquery-ui-droppable');
                wp_enqueue_script('jquery-ui-resizable');
                wp_enqueue_script('jquery-ui-sortable');
                wp_enqueue_script('jquery-ui-tabs');
                wp_enqueue_script('jquery-ui-datepicker');
                if (wp_is_mobile()) {
                    wp_enqueue_script('jquery-ui-touch', $mpsl_settings['plugin_dir_url'] . 'jqueryui/jquery-ui-touch/jquery.ui.touch-punch.min.js', array(), $mpsl_settings['plugin_version']);
                    $deps[] = 'jquery-ui-touch';
                }       
                // Test
//                wp_enqueue_script('mpsl-jquery-ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js');
            }
            wp_enqueue_script('jquery-ui-button');                           
            $deps[] = 'jquery-ui-button';
            if($mpsl_settings['is_new_wp_version']) {
                if ($page === 'slide') wp_enqueue_media();

//                wp_enqueue_style('mpsl-jquery-ui-theme', esc_url_raw($prefix.'ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/ui-lightness/jquery-ui.css'), false, '1.10.3');
                wp_enqueue_style('mpsl-jquery-ui-theme', $mpsl_settings['plugin_dir_url'] . 'jqueryui/ui-lightness/jquery-ui.css', false, $mpsl_settings['plugin_version']);

            } else { // TODO: Test
                if ($page === 'slide') {
//                wp_enqueue_style('thickbox');
//                wp_enqueue_script('thickbox');
//                wp_enqueue_script('media-upload');

//                wp_enqueue_style('wp-mediaelement');
//                wp_enqueue_script('wp-mediaelement');
                }

                wp_enqueue_style('mpsl-jquery-ui-theme', esc_url_raw($prefix.'ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/themes/ui-lightness/jquery-ui.css'), false, '1.8.18');
            }

            wp_enqueue_style('mpsl-admin', $mpsl_settings['plugin_dir_url'] . 'css/admin.css', array(), $mpsl_settings['plugin_version']);

            if ($page === 'slide') {
                wp_enqueue_style('mpsl-slide', $mpsl_settings['plugin_dir_url'] . 'css/slide.css', array(), $mpsl_settings['plugin_version']);
                $customPreloaderImageSrc = apply_filters('mpsl_preloader_src', false);                
                if ($customPreloaderImageSrc) {
                    echo '<style type="text/css">.mpsl-preloader, .mpsl-global-preloader{background-image: url("' . esc_url($customPreloaderImageSrc) . '") !important;}</style>';
                }
            }                                       
            wp_register_script('mpsl-canjs', $mpsl_settings['plugin_dir_url'] . 'js/vendors/can.custom.min.js', $deps, $mpsl_settings['canjs_version'], true);
            $deps[] = 'mpsl-canjs';

            wp_register_script('mpsl-functions', $mpsl_settings['plugin_dir_url'] . 'js/Functions.js', $deps, $mpsl_settings['plugin_version'], true);
            $deps[] = 'mpsl-functions';

            if (in_array($page, array('slider', 'slide'))) {
                wp_enqueue_script('mpsl-controllers', $mpsl_settings['plugin_dir_url'] . "js/controls.js", $deps, $mpsl_settings['plugin_version'], true);
                $deps[] = 'mpsl-controllers';

//                if ($page === 'slide') {
//                    wp_register_script('mpsl-layers-manager', $mpsl_settings['plugin_dir_url'] . 'js/LayersManager.js', $deps, $mpsl_settings['plugin_version'], true);
//                    $deps[] = 'mpsl-layers-manager';
//                }
            }

            if (file_exists($mpsl_settings['plugin_dir_path'] . "/js/$page.js")) {
                wp_register_script("mpsl-$page", $mpsl_settings['plugin_dir_url'] . "js/$page.js", $deps, $mpsl_settings['plugin_version'], true);
                wp_enqueue_script("mpsl-$page");


//                if (in_array($page, array('slider', 'slide'))) {
//                    $deps[] = "mpsl-$page";
//                    wp_register_script("mpsl-$page-init", $mpsl_settings['plugin_dir_url'] . "js/$page.init.js", $deps, $mpsl_settings['plugin_version'], true);
//                    wp_enqueue_script("mpsl-$page-init");
//                }
            }

            $jsVars = array();
            $jsVars['Vars'] = array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'settings' => $mpsl_settings,
                'options' => $this->pageController->getOptions(),
                'menu_url' => menu_page_url($mpsl_settings['plugin_name'], false),
                'lang' => $this->getLangStrings(),
                'nonces' => array(
                    'update_slider' => wp_create_nonce('wp_ajax_mpsl_update_slider'),
                    'create_slider' => wp_create_nonce('wp_ajax_mpsl_create_slider'),
                    'delete_slider' => wp_create_nonce('wp_ajax_mpsl_delete_slider'),
                    'duplicate_slider' => wp_create_nonce('wp_ajax_mpsl_duplicate_slider'),
                    'create_slide' => wp_create_nonce('wp_ajax_mpsl_create_slide'),
                    'update_slide' => wp_create_nonce('wp_ajax_mpsl_update_slide'),
                    'delete_slide' => wp_create_nonce('wp_ajax_mpsl_delete_slide'),
                    'duplicate_slide' => wp_create_nonce('wp_ajax_mpsl_duplicate_slide'),
                    'update_slides_order' => wp_create_nonce('wp_ajax_mpsl_update_slides_order'),
                    'check_alias_exists' => wp_create_nonce('wp_ajax_mpsl_check_alias_exists'),
                    'get_youtube_thumbnail' => wp_create_nonce('wp_ajax_mpsl_get_youtube_thumbnail'),
                    'get_vimeo_thumbnail' => wp_create_nonce('wp_ajax_mpsl_get_vimeo_thumbnail'),
                    'export_sliders' => wp_create_nonce('wp_ajax_mpsl_export_sliders'),
                    'import_sliders' => wp_create_nonce('wp_ajax_mpsl_import_sliders')
                )
            );
            if (in_array($page, array('slider', 'slide', 'slides'))) {
                $jsVars['Vars']['attrs'] = $this->pageController->getAttributes();
            }
            if ($page === 'slide') {
                $jsVars['Vars']['layers'] = $this->pageController->getLayers();
                $jsVars['Vars']['layer_options'] = $this->pageController->getLayerOptions();
                $jsVars['Vars']['layer_defaults'] = $this->pageController->getLayerOptionsDefaults();
                $jsVars['Vars']['slider_attrs'] = $this->pageController->getSliderAttrs();
            }

            wp_localize_script("mpsl-$page", 'MPSL', $jsVars);
        }
    }

    private function getLangStrings(){
        global $mpsl_settings;
        return array(
            'test' => __('test', MPSL_TEXTDOMAIN),
            'emptyInputError' => __('%s require non empty value.', MPSL_TEXTDOMAIN),
            'ajax_result_not_found' => __('In the AJAX response undisclosed result field.', MPSL_TEXTDOMAIN),
            'validate_digitals_only' => __('%s must content digitals only.', MPSL_TEXTDOMAIN),
            'validate_less_min' => __('%s could not be less then %d', MPSL_TEXTDOMAIN),
            'validate_greater_max' => __('%s could not be greater then %d', MPSL_TEXTDOMAIN),
            'aliasNotValidPattern' => __('Alias not valid. Alias could contents latin symbols, numbers, underscore and hyphen only.', MPSL_TEXTDOMAIN),
            'aliasAlreadyExists' => __('This alias already exists. Alias must be unique.'),
            'validate_invalid_date_format' => __('"%s" invalid date format. Use datepicker.', MPSL_TEXTDOMAIN),
            'validate_invalid_day' => __('"%s" invalid value for day: %day.', MPSL_TEXTDOMAIN),
            'validate_invalid_month' => __('"%s" invalid value for month: %month.', MPSL_TEXTDOMAIN),
            'validate_invalid_year' => __('"%s" Invalid value for year: %year - must be between %minYear and %maxYear.', MPSL_TEXTDOMAIN),
            'validate_invalid_hour' => __('"%s" invalid value for hour: %hour.', MPSL_TEXTDOMAIN),
            'validate_invalid_minute' => __('"%s" invalid value for minute: %minute.', MPSL_TEXTDOMAIN),

            'slider_updated' => __('Slider updated.', MPSL_TEXTDOMAIN),
            'slider_update_error' =>  __('Slider update error:', MPSL_TEXTDOMAIN),
            'slider_created' => __('Slider is created', MPSL_TEXTDOMAIN),
            'slider_deleted' => __('Slider is deleted.', MPSL_TEXTDOMAIN),
            'slider_deleted_id' => __('Slider %d deleted.', MPSL_TEXTDOMAIN),
            'slider_duplicated' => __('Slider duplicated.', MPSL_TEXTDOMAIN),
            'slider_want_delete_single' => __('Do you really want to delete \'%d\' ?', MPSL_TEXTDOMAIN),

            'slide_created' => __('Slide created.', MPSL_TEXTDOMAIN),
            'slide_created_error' => __('Slide is not created.', MPSL_TEXTDOMAIN),
            'slide_deleted' => __('Slide %d deleted.', MPSL_TEXTDOMAIN),
            'slide_updated' => __('Slide updated.', MPSL_TEXTDOMAIN),
            'slide_update_error' => __('Slide update error: ', MPSL_TEXTDOMAIN),
            'slide_deleted' => __('Slide deleted.', MPSL_TEXTDOMAIN),
            'slide_duplicated' => __('Slide duplicated.', MPSL_TEXTDOMAIN),
            'slide_want_delete_single' => __('Do you really want to delete \'%d\' ?', MPSL_TEXTDOMAIN),

            'slides_sorted' => __('Slides sorted', MPSL_TEXTDOMAIN),
            'slides_sorted_error' => __('Slides error when sorting', MPSL_TEXTDOMAIN),

            'layer_want_delete_all' => __('Do you really want to delete all the layers?', MPSL_TEXTDOMAIN),
            'import_export_dialog_title' => sprintf(__('%s Import and Export', MPSL_TEXTDOMAIN), $mpsl_settings['product_name']),
            'no_sliders_selected_to_export' => __('No sliders selected to export.')
        );
    }

    public function loadTextdomain(){
        global $mpsl_settings;
        load_plugin_textdomain('mpsl', FALSE, $mpsl_settings['plugin_name'] .'/lang/');
    }

    public function mpslMenu() {        
        global $mpsl_settings;        
        $isHideMenu = apply_filters('mpsl_hide_menu', false);        
        if (!isMPSLDisabledForCurRole() && !$isHideMenu) {
            $this->menuHook = add_menu_page($mpsl_settings['product_name'], $mpsl_settings['product_name'], 'read', $this->mpsl_settings['plugin_name'], array($this, 'renderPage'), "dashicons-slides");
            $isHideOptionsMenu = apply_filters('mpsl_hide_options_page', false);
            if (!$isHideOptionsMenu) {
                $this->pluginOptionsController->addMenu();
            }                
            $isHideLicensePage = apply_filters('mpsl_hide_license_page', false);
            if (!$isHideLicensePage && is_main_site()) {
                $this->licenseController->addMenu();
            }
        }
    }

    public function renderPage(){
        echo '<div class="mpsl-wrapper wrap">';
        $this->pageController->render();
        echo '<div class="mpsl-global-preloader"></div>';
        echo '<div id="mpsl-info-box"></div>';
        echo '</div>';
    }
    
    public function importPageRender(){
        require_once 'includes/classes/SliderImporter.php';
        $importer = new MPSLSliderImporter();
        $importer->renderImportPage();        
    }
    
    public function autoImport($path, $isVerbose = false){
        require_once 'includes/classes/SliderImporter.php';
        $importer = new MPSLSliderImporter();
        return $importer->importFromFile($path, $isVerbose); 
    }

    // AJAX Callbacks
    public function updateSliderCallback() {
        mpslVerifyNonce();
        // Prepare data
        $id = isset($_POST['id']) ? $_POST['id'] : null;
        if (isset($_POST['options'])) {
            $options = stripslashes($_POST['options']);
            $options = json_decode($options, true);
        } else {
            $options = array();
        }
        $title = isset($options['main']['title']) ? $options['main']['title'] : null;
        $alias = isset($options['main']['alias']) ? $options['main']['alias'] : null;

        // TODO: Flash messages
        if (is_null($title)) return false;
        if (is_null($alias)) return false;
        if (!count($options)) return false;

        if (isset($_POST['id'])) {
            require_once $this->pluginDir . 'SliderOptions.php';
            $slider = new MPSLSliderOptions($id);
            $slider->setTitle($title);
            $oldAlias = $slider->getAlias();
            if (($oldAlias === $alias) || !$slider->isAliasExists($alias)) {
                $slider->setAlias($alias);
                $slider->overrideOptions($options, true);
                $updated = $slider->update();
                if (false !== $updated) {
                    wp_send_json(array('result' => true, 'id' => $slider->getId()));
                } else {
                    global $wpdb;
                    mpslSetError(__('Slider is not updated. Error: ', MPSL_TEXTDOMAIN) . $wpdb->last_error);
                }
            } else {
                mpslSetError(__('This alias already exists. Alias must be unique.', MPSL_TEXTDOMAIN));
            }
        } else {
            mpslSetError(__('Id is not set.', MPSL_TEXTDOMAIN));
        }
}

    public function createSliderCallback(){
        mpslVerifyNonce();        
        if (isset($_POST['options'])) {
            $options = stripslashes($_POST['options']);
            $options = json_decode($options, true);
        } else {
            $options = array();
        }
        require_once $this->pluginDir . 'SliderOptions.php';
        $slider = new MPSLSliderOptions();
        $slider->overrideOptions($options, true);
        if (!$slider->isAliasExists($slider->getAlias())) {
            if(!$slider->isNotValidOptions()){
                $id = $slider->create();
                if (false !== $id) {
                    wp_send_json(array('result' => true, 'id' => $slider->getId()));
                } else {
                    global $wpdb;
                    mpslSetError(__('Slider is not updated. Error: ', MPSL_TEXTDOMAIN) . $wpdb->last_error);
                }
            } else {
                mpslSetError(__('Slider parameters are not valid.', MPSL_TEXTDOMAIN));
            }
        } else {
            mpslSetError(__('This alias already exists. Alias must be unique.', MPSL_TEXTDOMAIN));
        }

    }

    public function deleteSliderCallback(){
        mpslVerifyNonce();
        if (isset($_POST['id'])) {
            require_once $this->pluginDir . 'SliderOptions.php';
            $slider = new MPSLSliderOptions($_POST['id']);
            $error = null;
            $result = $slider->delete();
            if (false !== $result) {
                wp_send_json(array('result' => true));
            } else {
                global $wpdb;
                mpslSetError(__('Slider is not deleted. Error: ', MPSL_TEXTDOMAIN) . $wpdb->last_error);
            }
        } else {
            mpslSetError(__('Slider is not deleted. ID is not set.', MPSL_TEXTDOMAIN));
        }
    }

    public function duplicateSliderCallback(){
        mpslVerifyNonce();
        if (isset($_POST['id'])) {
            require_once $this->pluginDir . 'SliderOptions.php';
            $slider = new MPSLSliderOptions($_POST['id']);
            $error = null;
            $id = $slider->duplicate();
            if (false !== $id) {
                require_once $this->pluginDir . 'SlidersList.php';
                $slidersList = new MPSLSlidersList();
                $html = $slidersList->getRowHtml($id);
                wp_send_json(array('result' => true, 'id' => $id, 'html' => $html ));
            } else {
                global $wpdb;
                mpslSetError(__('Slider is not duplicated. Error: ', MPSL_TEXTDOMAIN) . $wpdb->last_error);
            }
        } else {
            mpslSetError(__('Slider is not duplicated. Slider ID is not set.', MPSL_TEXTDOMAIN));
        }
    }

    public function checkAliasExistsCallback(){
        mpslVerifyNonce();
        require_once $this->pluginDir . 'SliderOptions.php';
        $alias = $_POST['alias'];
        $result = array(
            'result' => MPSLSliderOptions::isAliasExists($alias)
        );
        wp_send_json($result);
    }

    function updateSlideCallback() {
        mpslVerifyNonce();
        $id = isset($_POST['id']) ? (int) $_POST['id'] : null;
        if (isset($_POST['options'])) {
            $options = stripslashes($_POST['options']);
            $options = json_decode($options, true);
        } else {
            $options = array();
        }
        if (isset($_POST['layers']) ){
            $layers = stripslashes($_POST['layers']);
            $layers = json_decode($layers, true);
        } else {
            $layers = array();
        }

        // TODO: Flash messages
        if (!count($options)) return false;

        if (!is_null($id)) {
            require_once $this->pluginDir . 'SlideOptions.php';
            $slide = new MPSLSlideOptions($id);
//            $slide->setSlideOrder($order);
            $slide->overrideOptions($options, true);
//            $slide->overrideLayers($layers, true, true);
            $slide->setLayers($layers);
            $result = $slide->update();
            if (false !== $result) {
                wp_send_json(array('result' => $result, 'id' => $slide->getId()));
            } else {
                global $wpdb;
                mpslSetError(__('Slide is not updated. Error: ', MPSL_TEXTDOMAIN) . $wpdb->last_error);
            }

        } else {
            mpslSetError(__('Slide ID is not set.', MPSL_TEXTDOMAIN));
        }
        die();
    }

    function deleteSlideCallback() {
        mpslVerifyNonce();
        require_once $this->pluginDir . 'SlideOptions.php';
        $slide = new MPSLSlideOptions($_POST['id']);
        $result = $slide->delete();
        if (false !== $result) {
            wp_send_json(array('result' => $result));
        } else{
            global $wpdb;
            mpslSetError(__('Slide is not deleted. Error: ', MPSL_TEXTDOMAIN) . $wpdb->last_error);
        }
        die();
    }

    function createSlideCallback() {
        mpslVerifyNonce();
        require_once $this->pluginDir . 'SlideOptions.php';
        if (isset($_POST['slider_id'])) {
            $sliderId = (int) $_POST['slider_id'];
            $mpsl_options = new MPSLSlideOptions();
            $mpsl_options->create($sliderId);
        } else {
            mpslSetError(__('Slider ID is not set.', MPSL_TEXTDOMAIN));
        }
        die();
    }

    function duplicateSlideCallback() {
        mpslVerifyNonce();
        if (isset($_POST['id'])) {
            $id = (int) $_POST['id'];
            require_once $this->pluginDir . 'SlideOptions.php';
            $slide = new MPSLSlideOptions($id);
            $slide->duplicateSlide($id);
        } else {
            mpslSetError(__('Slide ID is not set.', MPSL_TEXTDOMAIN));
        }
        die();
    }

    function updateSlidesOrderCallback(){
        mpslVerifyNonce();
        if (isset($_POST['order'])) {
            $order = (array) $_POST['order'];
            $db = new MPSliderDB();
            $result = $db->updateSlidesOrder($order);
            if ( false !== $result ) {
                wp_send_json(array('result' => true));
            } else {
                global $wpdb;
                mpslSetError(__('Slides order update error: ' . $wpdb->last_error, MPSL_TEXTDOMAIN));
            }
        } else {
            mpslSetError(__('Order is not set.', MPSL_TEXTDOMAIN));
        }
        die();
    }
    
    function getYoutubeThumbnailCallback(){
        mpslVerifyNonce();
        if (isset($_GET['src'])) {
            $youtubeDataApi = MPSLYoutubeDataApi::getInstance();
            $thumbnail = $youtubeDataApi->getThumbnail($_GET['src']);
            if (false === $thumbnail) {
                $thumbnail = '';
            }
            wp_send_json(array('result' => $thumbnail));
        } else {
            mpslSetError(__('YouTube video source not setted.', MPSL_TEXTDOMAIN));
        }
    }
    
    function getVimeoThumbnailCallback(){
        mpslVerifyNonce();
        if (isset($_GET['src'])) {
            $vimeoOEmbedApi = MPSLVimeoOEmbedApi::getInstance();
            $thumbnail = $vimeoOEmbedApi->getThumbnail($_GET['src']);
            if (false === $thumbnail) {
                $thumbnail = '';
            }
            wp_send_json(array('result' => $thumbnail));
        } else {
            mpslSetError(__('Vimeo video source not setted.', MPSL_TEXTDOMAIN));
        }
    }
    
    function exportSliders(){ 
        global $mpsl_settings;    
        if (isset($_POST['ids']) && !empty($_POST['ids'])) {
            if (check_admin_referer('export-mpsl-sliders')) {                        
                $uploads = wp_upload_dir();
                $exportData = array(
                    'info' => array(
                        'mpsl-ver' => $mpsl_settings['plugin_version'],
                        'base-upload' => $uploads['baseurl']
                    )
                );
                $internalResources = array();
                foreach($_POST['ids'] as $id) {
                    require_once $this->pluginDir . 'SliderOptions.php';
                    $slider = new MPSLSliderOptions($id);                
                    $error = null;
                    $slider_data = $slider->getExportSliderData($internalResources);
                    $exportData['sliders'][$id] = $slider_data;       
                }
                $exportData['uploads']  = $internalResources;                                          
                $siteTitle = str_replace( ' ', '', get_bloginfo('name') );                
                $date = date("m-d-Y");      
                
                if (count($exportData['sliders']) > 1) {                    
                    $sliderAlias = 'sliders';
                } else {
                    $firstSlider = reset($exportData['sliders']);
                    $sliderAlias = $firstSlider['options']['alias'];
                }
                                
                $exportFileName = $siteTitle . "-" . $sliderAlias . "-data-" . $date;
                $exportData = json_encode($exportData);
                
                header( "Content-Type: application/force-download; charset=" . get_bloginfo('charset')  );
                header( "Content-Disposition: attachment; filename=$exportFileName.json" );
                exit( $exportData );
            }            
        } 
        exit();
    }

}
if (is_admin()) {
    global $mpslAdmin;
    $mpslAdmin = new MPSLAdmin();        
    register_activation_hook($mpsl_plugin_file, array('MPSLAdmin', 'install'));    
    add_action( 'wpmu_new_blog', array('MPSLAdmin','onCreateBlog'), 10, 6 );
    add_filter( 'wpmu_drop_tables', array('MPSLAdmin', 'onDeleteBlog'));
}

// Shortcode
add_shortcode($mpsl_settings['shortcode_name'], 'mpsl_shortcode');
function mpsl_shortcode($atts){
    global $mpsl_settings;
    $mp_plugin_active = is_plugin_active('motopress-content-editor/motopress-content-editor.php');

    $defaultAtts = array(
        'alias' => '',
        'edit_mode' => false
    );
    if ($mp_plugin_active) $defaultAtts = MPCEShortcode::addStyleAtts($defaultAtts);
    extract(shortcode_atts($defaultAtts, $atts, 'mpsl'));

    if ($alias === '') {
        $alias = isset($atts[0]) ? $atts[0] : '';
    }
    $edit_mode = filter_var($edit_mode, FILTER_VALIDATE_BOOLEAN);

    $mpAtts = array();
    if ($mp_plugin_active) {
        if (!empty($mp_style_classes)) $mp_style_classes = ' ' . $mp_style_classes;
        $mpAtts = array(
            'mp_style_classes' => $mp_style_classes,
            'margin' => $margin
        );
    }

    return mpsl_slider($alias, $edit_mode, null, $mpAtts);
}

function mpsl_slider($alias = '', $edit_mode = false, $slideId = null, $mpAtts = array()){
    if ($alias) {
        $slider = new MPSLSliderOptions();
        $slider->loadByAlias($alias);
        $sliderOptions = $slider->getFullSliderData($slideId);
        if (isset($sliderOptions['slides']) && !empty($sliderOptions['slides'])) {
            mpsl_enqueue_core_scripts_styles();
            $hasVisibleSlides = false; // will change to true if slider has at least one visible slide .
            ob_start();
            include dirname(__FILE__) . '/views/shortcode.php';
            $result = ob_get_clean();
            return $hasVisibleSlides ? $result : null;
        }
    }
}

function motoPressSlider($alias){
    echo mpsl_slider($alias);
}

//function mpsl_enqueue() {
//    global $mpsl_settings;
//
//    $isSlidePage = false;
//    if (is_admin()) {
//        $page = isset($_GET['page']) ? ($_GET['page'] === $mpsl_settings['plugin_name']) : false;
//        $view = isset($_GET['view']) ? ($_GET['view'] === 'slide') : false;
//        $isSlidePage = ($page and $view);
//    }
//add_action('wp_enqueue_scripts', 'mpsl_enqueue');
//add_action('admin_enqueue_scripts', 'mpsl_enqueue', 11);

function mpsl_enqueue_core_scripts_styles(){
    global $mpsl_settings;
    wp_enqueue_style('mpsl-core', $mpsl_settings['plugin_dir_url'] . 'motoslider_core/styles/motoslider.css', array(), $mpsl_settings['plugin_version']);
//    wp_enqueue_style('mpsl-custom', $mpsl_settings['plugin_dir_url'] . 'motoslider_core/styles/custom.css', array(), $mpsl_settings['plugin_version']);
    wp_enqueue_style('mpsl-object-style', $mpsl_settings['plugin_dir_url'] . 'css/objects.css', array('mpsl-core'), $mpsl_settings['plugin_version']);
    do_action('mpsl_slider_enqueue_style');

    wp_enqueue_script('mpsl-vendor', $mpsl_settings['plugin_dir_url'] . 'motoslider_core/scripts/vendor.js', array('jquery'), $mpsl_settings['plugin_version'], true);    
//    wp_enqueue_script('jquery.parallax', $mpsl_settings['plugin_dir_url'] . 'motoslider_core/scripts/jquery.jkit.custom.1.2.16.min.js', array('jquery'), $mpsl_settings['plugin_version'], true);
    wp_enqueue_script('mpsl-core', $mpsl_settings['plugin_dir_url'] . 'motoslider_core/scripts/motoslider.js', array(), $mpsl_settings['plugin_version'], true);
    wp_localize_script('mpsl-core', 'MPSLCorePath', $mpsl_settings['plugin_dir_url'] . 'motoslider_core/');
    wp_localize_script('mpsl-core', 'MPSLVersion', $mpsl_settings['plugin_version']);
}

/*
require_once 'OptionsFactory.php';
    require_once 'SliderOptions.php';

    $mainGroup = new OptionsGroup('[id]', '[title]', '[order]', '[icon]', '[description]', '[disabled]');
    $mainGroup->addOption('name', array(
        'type' => '',
        'label' => '',
        'pre_label' => '',
        'post_label' => '',
        'description' => '',
        'required' => false,
        'default' => '', // int | string | assoc array
        'order' => 0, // ?
        'dependency_groups' => array(),
        'dependency_options' => array(), // assoc nested array (group => [option names])

        // select | checkbox | radio
        'list' => array(), // assoc array
        'dependency_personal_options' => array(), // assoc nested array (name => [args])
        'align' => '', // vertical | horizontal

        // select | checkbox
        'multiple' => false,

        // number | spinner
        'min' => 1,
        'max' => 10,
        'step' => 1,

        'disabled' => false,
        'controls' => array(
            '[id]' => array(
                'title' => '',
                'icon' => '',
                'style' => ''
            )
        )
    ));


    $sliderOptions = new SliderOptions();
    $sliderOptions->addGroup($mainGroup, '[float]');
    $sliderOptions->render();
 * */