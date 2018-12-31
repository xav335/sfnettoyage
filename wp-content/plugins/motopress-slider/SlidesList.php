<?php
require_once dirname(__FILE__) . '/List.php';

class MPSLSlidesList extends MPSLList {
    private $sliderId;

    public function __construct($id) {
        parent::__construct();
        $this->sliderId = $id;
    }

    public function getSliderId(){
        return $this->sliderId;
    }

    public function render() {
        global $mpsl_settings;
        $slides = self::getList($this->sliderId);
//        $slide = $this->getSlide(4, array('slider_id', 'slide_order', 'options', 'layers'));
//        $slide = $this->duplicateSlide(4);
//        var_dump($slide);

        if ($slides !== false) {
            include $this->pluginDir . 'views/slides.php';

        } else {
            // TODO: Throw error
            _e('Record not found', MPSL_TEXTDOMAIN);
        }
    }

    public function getOptions() {}
    public function getAttributes() {
        return array(
            'slider_id' => $this->getSliderId(),
        );
    }

    public static function getList($sliderId) {

        $db = new MPSliderDB();

        // TODO: Message
        if (!$db->isSliderExists($sliderId)) return false;

        $slider = $db->isSliderExists($sliderId);
        if (is_null($slider)) return false;

        $slides = $db->getSlidesBySlider($sliderId);

        foreach ($slides as &$slide) {
            $options = json_decode($slide['options'], true);
            if ($options) {
                $slide['title'] = (isset($options['title'])) ? $options['title'] : false;
            }
        }

        return $slides;
    }

}