<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see LICENSE */

require_once __DIR__ . "/../vendor/autoload.php";

use Kpg\Chart\Provider\ToolProvider;

include_once "./Services/COPage/classes/class.ilPageComponentPlugin.php";
//include_once "./Customizing/global/plugins/Services/COPage/PageComponent/Chart/classes/class.ilChartPluginGUI.php";

/**
 * Class ilChartPlugin
 *
 * @author KPG <dev@kroepelin-projekte.de>
 */
class ilChartPlugin extends ilPageComponentPlugin
{
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
     * Constructor ilChartPlugin
     */
    public function __construct()
    {
        global $DIC;
        parent::__construct();
        $this->includePluginClasses();
        $this->config = new ilChartConfig($this->getSlotId().'_'.$this->getId());
        $this->provider_collection->setToolProvider(new ToolProvider($DIC, $this));
    }

    /**
     * @return string
     */
    public function getPluginName()
    {
        return self::PLUGIN_NAME;
    }

    /**
     * @param string $a_type
     * @return bool
     */
    public function isValidParentType($a_type)
    {
        // Allow in all parent types
        return true;
    }

    /**
     * Include classes in plugin
     */
    public function includePluginClasses()
    {
        $this->includeClass('class.ilChartConfig.php');
    }

    /**
     * @return ilChartConfig
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param array  $properties
     * @param string $plugin_version
     */
    public function onDelete($properties, $plugin_version)
    {
        global $ilCtrl;
        
        if ($ilCtrl->getCmd() !== "moveAfter") {
        }
    }

    /**
     * @param array  $properties
     * @param string $plugin_version
     */
    public function onClone(&$properties, $plugin_version)
    {
    }

    /**
     * @param $a_mode
     * @return array
     */
    public function getCssFiles($a_mode): array
    {
        return ["css/chart.css"];
    }
    
    /**
     * @param  type  $a_mode
     * @return array
     */
    public function getJavascriptFiles($a_mode): array
    {
        $js = ["js/Chart.min.js", "js/chartjs-plugin-datalabels.min.js"];
        return $js;
    }
}
