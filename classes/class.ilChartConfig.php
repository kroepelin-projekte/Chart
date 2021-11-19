<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see LICENSE */

/**
* Class ilChartConfig
*
* @author KPG <dev@kroepelin-projekte.de>
*/
class ilChartConfig
{
    /**
    * @var ilSetting
    */
    protected $settings;

    /**
    * ilChartConfig constructor.
    * @param string $settingsId
    */
    public function __construct($settingsId)
    {
        $this->settings = new ilSetting($settingsId);
    }

    /**
     * @param $check
     * @return string
     */
    public function getOption($check)
    {
        return $this->settings->get($check, 0);
    }

    /**
     * @param $check
     * @param $checked
     */
    public function setOption($check, $checked)
    {
        $this->settings->set($check, $checked);
    }

    /**
     * @param $value
     * @return string
     */
    public function getValue($value)
    {
        return $this->settings->get($value, 0);
    }

    /**
     * @param $value
     * @param $_value
     */
    public function setValue($value, $_value)
    {
        $this->settings->set($value, $_value);
    }
}
