<?php

require_once dirname(__FILE__) . '/MPSLOptions.php';

class MPSLSlideOptions extends MPSLOptions {
    private $sliderId = null;
    private $sliderAlias = null;
    private $slideOrder = null;
    private $layers = null;
    private $layerOptions = null;

    function __construct($id = null) {        
        parent::__construct();

        include $this->pluginDir . 'settings/slide.php';
        $this->options = $slideOptions;
        $this->prepareOptions();

        include $this->pluginDir . 'settings/layer.php';
        $this->layerOptions = $layerOptions;
        $this->prepareLayers();

        if (is_null($id)) {
            $this->overrideOptions(null, false);
        } else {
            $loaded = $this->load($id);

            if (!$loaded) {
                // TODO: Throw error
//                _e('Record not found', MPSL_TEXTDOMAIN);
            }
        }

    }

    protected function load($id) {
        global $wpdb;

        $result = $wpdb->get_row(sprintf(
            'SELECT * FROM %s WHERE id = %d',
            $wpdb->prefix . parent::SLIDES_TABLE,
            (int) $id
        ), ARRAY_A);

        if (is_null($result)) return false;

        $this->id = (int) $id;
        $this->sliderId = (int) $result['slider_id'];
        $this->sliderAlias = MPSLSliderOptions::getAliasById($this->sliderId);
        $this->slideOrder = (int) $result['slide_order'];
//        $this->options = json_decode($result['options'], true);
        $this->overrideOptions(json_decode($result['options'], true), false);
        $this->overrideLayers(json_decode($result['layers'], true));

        return true;
    }

    public function overrideLayers($layers = null){
        $defaults = $this->getLayerDefaults();
        if (!empty($layers)) {
            foreach($layers as $layerKey => $layer) {
                $layers[$layerKey] = array_merge($defaults, $layer);
                // update attached image url
                if (isset($layers[$layerKey]['image_id']) && !empty($layers[$layerKey]['image_id'])) {
                    $image_url = wp_get_attachment_url($layers[$layerKey]['image_id']);
                    if (false === $image_url) {
                        $image_url = '?';
                    }
                    $layers[$layerKey]['image_url'] = $image_url;
                }
            }
        }
        $this->layers = $layers;
    }
    
    public function overrideOptions($options = null, $isGrouped = true) {
        if (isset($options['bg_image_id']) && !empty($options['bg_image_id'])) {
            $image_url = wp_get_attachment_url($options['bg_image_id']);
            if (false === $image_url) {
                $image_url = '?';
            }
            $options['bg_internal_image_url'] = $image_url;
        }
        parent::overrideOptions($options, $isGrouped);        
    }

    public function getLayerDefaults(){
        $defaults = array();
        foreach($this->layerOptions as $grp){
            foreach($grp['options'] as $optName => $opt) {
                $defaults[$optName] = $opt['default'];
            }
        }
        return $defaults;
    }

    private function prepareLayers() {
//        foreach ($this->layerOptions as $optName => $opt) {
//            $this->layerOptions[$optName]['name'] = $optName;
//            $this->layerOptions[$optName]['value'] = $opt['default'];
//        }
        foreach ($this->layerOptions as $grpName => $grp) {
            foreach ($grp['options'] as $optName => $opt) {
                $this->layerOptions[$grpName]['options'][$optName]['group'] = $grpName;
                $this->layerOptions[$grpName]['options'][$optName]['name'] = $optName;
                $this->layerOptions[$grpName]['options'][$optName]['value'] = $opt['default'];

                if (array_key_exists('options', $opt)) {
                    foreach ($this->layerOptions[$grpName]['options'][$optName]['options'] as $childOptName => $childOpt) {
                        $this->layerOptions[$grpName]['options'][$optName]['options'][$childOptName]['group'] = $grpName;
                        $this->layerOptions[$grpName]['options'][$optName]['options'][$childOptName]['name'] = $childOptName;
                        $this->layerOptions[$grpName]['options'][$optName]['options'][$childOptName]['value'] = $childOpt['default'];
                    }
                }
            }
        }
    }

    public function create($sliderId = null) {
        global $wpdb;

        // Update options with new data
        $this->overrideOptions();

        // Define query data
        $qTable = $wpdb->prefix . self::SLIDES_TABLE;
        
        $order = $this->getNextOrder($sliderId);       

        $qData = array(
            'slider_id' => $sliderId,
            'slide_order' => $order,
            'options' => json_encode($this->getOptionValues()),
            'layers' => json_encode(array())
        );
        $qFormats = array('%d', '%d', '%s', '%s');

        // Exec query
        $wpdb->hide_errors();
        $result = $wpdb->insert($qTable, $qData, $qFormats);
        if ($result === false) {
            mpslSetError(__('Slide is not created. Error: ', MPSL_TEXTDOMAIN) . $wpdb->last_error);
        }
        $id = ($result) ? $wpdb->insert_id : null;
        $this->id = (int) $id;
        $this->setGeneratedByIdTitle();
        $this->update();

        wp_send_json(array('result' => $result, 'id' => $id));
    }
    
    public function import($sliderId) {
        global $wpdb;
        $qTable = $wpdb->prefix . self::SLIDES_TABLE;
        $order = $this->getNextOrder($sliderId); 
        $qData = array(
            'slider_id' => $sliderId,
            'slide_order' => $order,
            'options' => json_encode($this->getOptionValues()),
            'layers' => json_encode($this->layers)
        );
        $qFormats = array('%d', '%d', '%s', '%s');
        $wpdb->hide_errors();
        $this->setId(null);
        $result = $wpdb->insert($qTable, $qData, $qFormats);
        $id = ($result) ? $wpdb->insert_id : null;
        $this->id = (int) $id;
        return $id;
    }
    
    public function getNextOrder($sliderId){  
        global $wpdb;
        $qTable = $wpdb->prefix . self::SLIDES_TABLE;
        $order = $wpdb->get_var(sprintf(
            "SELECT MAX(slide_order) FROM %s WHERE slider_id=%d",
            $qTable, $sliderId
        ));
        return is_null($order) ? 1 : $order + 1;
    }

    public function update() {
        global $wpdb;

//        print(json_encode(array($this->getOptionValues())));exit;

        // Define query data
        $qTable = $wpdb->prefix . self::SLIDES_TABLE;
        $qData = array(
            'options' => json_encode($this->getOptionValues()),
            'layers' => json_encode($this->layers)
        );
        $qFormats = array('%s', '%s');

        // Exec query
//        if (is_null($id)) {
//            $result = $wpdb->insert($qTable, $qData, $qFormats);
//            $id = ($result) ? $wpdb->insert_id : null;
//        } else {
            $wpdb->hide_errors();
            return $wpdb->update($qTable, $qData, array('id' => $this->id), $qFormats);
//        }


    }

    public function delete() {
        global $wpdb;
        $wpdb->hide_errors();
        return $wpdb->delete($wpdb->prefix . self::SLIDES_TABLE, array('id' => $this->id));
    }

    public function duplicateSlide(/*$sliderId, */$slideId) {
        global $wpdb;
        $wpdb->hide_errors();
        $db = new MPSliderDB();

        $slide = $db->getSlide($slideId, array('slider_id', 'slide_order', 'options', 'layers'));
        if (is_null($slide)) {
            mpslSetError(__('Slide ID is not set.', MPSL_TEXTDOMAIN));
        }
        $order = $wpdb->get_var(sprintf(
            "SELECT MAX(slide_order) FROM %s WHERE slider_id=%d",
            $wpdb->prefix . parent::SLIDES_TABLE, $this->sliderId
        ));
        $order = is_null($order) ? 0 : $order + 1;

        $slide['slide_order'] = $order;
        $options = json_decode($slide['options'], true);
        if ($options !== false && isset($options['title'])) {
            $options['title'] = __('Duplicate of ', MPSL_TEXTDOMAIN) . $options['title'];
            $slide['options'] = json_encode($options);
        }

        $result = $wpdb->insert($wpdb->prefix . parent::SLIDES_TABLE, $slide);
        if ($result === 'false') {
            mpslSetError(__('Slide is not duplicated. Error: ', MPSL_TEXTDOMAIN) . $wpdb->last_error);
        }

        wp_send_json(array('result' => $result, 'id' => $wpdb->insert_id));
    }

//    protected function getExistingOptions($id) {
//        return $this->options;
//    }

    public function getSliderAttrs() {
        $db = new MPSliderDB();
        $slider = $db->getSlider($this->sliderId);
        $slider['options'] = json_decode($slider['options']);
        return $slider;
    }

    public function getAttributes() {
        return array(
            'id' => $this->id,
            'slider_id' => $this->sliderId,
            'slide_order' => $this->slideOrder,
        );
    }

    public function render() {
        global $mpsl_settings;
//        $slider = new MPSLSliderOptions($this->sliderId);
//        $sliderOptions = $slider->getOptions();
        $options = $this->getOptions();
        include $this->pluginDir . 'views/slide.php';
    }

    public function renderLayer() {
        global $mpsl_settings;
        include $this->pluginDir . 'views/layer.php';
    }

    public function getSliderId() {
        return $this->sliderId;
    }

    public function getSlideOrder() {
        return $this->slideOrder;
    }

    public function getLayers() {
        return $this->layers;
    }
    
    public function getLayersForExport(&$internalResources){
        $options = array();
        $layers = $this->layers;
        foreach ($layers as &$layer) {
            foreach ($layer as $optionName => $optionValue) {
                switch ($optionName) {
                    case 'image_id' :
                        if (!empty($optionValue)) {
                            if (!isset($internalResources[$optionValue])) {                                
                                $internalResources[$optionValue] = array();
                                $internalResources[$optionValue]['value'] = wp_get_attachment_url($optionValue);
                            }
                            $layer[$optionName] = array(
                                'need_update' => true,
                                'old_value' => $optionValue                                
                            );
                        }
                        break;                    
                }
            }
        }
        return $layers;
    }

    public function setLayers($layers) {
        $this->layers = $layers;
    }

    public function getLayerOptions() {
            return $this->layerOptions;
    }

    public function getLayerOptionsDefaults(){
        include $this->pluginDir . 'settings/layer.php';
        $layerDefaults = array();
        foreach($layerOptions as $grp) {
            if (isset($grp['options'])) {
                foreach ($grp['options'] as $optName => $opt){
                    $layerDefaults[$optName] = $opt['default'];
                    if (array_key_exists('options', $opt)) {
                        foreach ($opt['options'] as $childOptName => $childOpt) {
                            $layerDefaults[$childOptName] = $childOpt['default'];
                        }
                    }
                }
            }
        }
        return $layerDefaults;
    }

    public function setGeneratedByIdTitle(){
        $newTitle = $this->getTitle() . '-' . $this->id;
        $this->setTitle($newTitle); 
    }
    
    public function getTitle(){
        return $this->options['main']['options']['title']['value'];
    }
    
    public function setTitle($title){
        $this->options['main']['options']['title']['value'] = $title;
    }
}
