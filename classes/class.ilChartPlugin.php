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
 * If this is not the case, or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilChartPlugin
 * @author KPG <support@kroepelin-projekte.de>
 */
class ilChartPlugin extends ilPageComponentPlugin
{
    const PLUGIN_NAME = "Chart";
    public function getPluginName(): string
    {
        return self::PLUGIN_NAME;
    }
    public function isValidParentType(string $a_type): bool
    {
        return true;
    }
//    public function onDelete(array $a_properties, string $a_plugin_version, bool $move_operation = false): void
//    {
//    }
//    public function onClone(array &$a_properties, string $a_plugin_version): void
//    {
//    }
//    public function afterRepositoryCopy(
//        array &$a_properties,
//        array $mapping,
//        int $source_ref_id,
//        string $a_plugin_version
//    ): void {
//    }

    public function getCssFiles(string $a_mode): array
    {
        return ["/css/chart.css"];
    }

    public function getJavascriptFiles(string $a_mode) : array
    {
        return ["/js/Chart.min.js", "/js/chartjs-plugin-datalabels.min.js"];
    }
}
