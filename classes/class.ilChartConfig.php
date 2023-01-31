<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilChartConfig
 *
 * @author KPG <support@kroepelin-projekte.de>
 */
class ilChartConfig
{
    protected ilSetting $settings;
    public function __construct(string $settingsId)
    {
        $this->settings = new ilSetting($settingsId);
    }
    public function getOption(string $check): string
    {
        return $this->settings->get($check, 0);
    }
    public function setOption(string $check, string $checked): void
    {
        $this->settings->set($check, $checked);
    }
    public function getValue(string $value): string
    {
        return $this->settings->get($value, 0);
    }
    public function setValue(string $value, string $_value): void
    {
        $this->settings->set($value, $_value);
    }
}
