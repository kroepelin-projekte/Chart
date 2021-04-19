<?php

include_once("./Services/COPage/classes/class.ilPageComponentPlugin.php");

/**
 * Class ilChartPlugin
 *
 * @author KPG <dev@kroepelin-projekte.de>
 *
 * @ilCtrl_isCalledBy ilChartPluginGUI: ilPCPluggedGUI
 */
class ilChartPlugin extends ilPageComponentPlugin {
    
    const PLUGIN_ID = "chrt";
    const PLUGIN_NAME = "Chart";
    const PLUGIN_CLASS_NAME = self::class;

    /**
     * @var ilChartConfig
     */
    protected $config;

    /**
     * @var null
     */
    protected static $instance = null;

    /**
     * ilChartPlugin constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->includePluginClasses();
        $this->config = new ilChartConfig($this->getSlotId().'_'.$this->getId());
    }

    /**
     * @return string
     */
    public function getPluginName() {
        return self::PLUGIN_NAME;
    }

    /**
     * @param string $a_type
     *
     * @return bool
     */
    public function isValidParentType($a_type) {
        // Allow in all parent types
        return true;
    }

    /**
     * Include classes in plugin
     */
    public function includePluginClasses() {
        $this->includeClass('class.ilChartConfig.php');
        $this->includeClass('class.ilChartPluginGUI.php');
    }

    /**
     * @return ilChartConfig
     */
    public function getConfig() {
        return $this->config;
    }

    /**
     * @param array  $properties
     * @param string $plugin_version
     *
     */
    public function onDelete($properties, $plugin_version) {
        global $ilCtrl;

        if ($ilCtrl->getCmd() !== "moveAfter") {

        }
    }

    /**
     * @param array  $properties
     * @param string $plugin_version
     */
    public function onClone(&$properties, $plugin_version) {
        
    }

    /**
     * @param $a_mode
     *
     * @return array
     */
    function getCssFiles($a_mode) {
        return ["css/chart.css"];
    }

    /**
     * 
     * @param type $a_mode
     * @return array
     */
    function getJavascriptFiles($a_mode):array {
        $js = ["js/Chart.min.js", "js/chartjs-plugin-datalabels.min.js"];
        return $js;
    }
}