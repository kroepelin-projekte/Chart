<?php

namespace Kpg\Chart\Provider;

use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolPluginProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;

include_once "./Customizing/global/plugins/Services/COPage/PageComponent/Chart/classes/class.ilChartPluginGUI.php";

/**
 * Class ilChartPluginGUI
 *
 * @author KPG <dev@kroepelin-projekte.de>
 * @ilCtrl_isCalledBy ToolProvider: ilPCPluggedGUI
 */
class ToolProvider extends AbstractDynamicToolPluginProvider
{
    const LANG_CHART_TITLE = "chart_title";
    const LANG_CHART_DATASETS = "chart_datasets";
    const LANG_CHART_HORIZONTAL_BAR = "horizontal_bar_chart";
    const LANG_CHART_VERTICAL_BAR = "vertical_bar_chart";
    const LANG_CHART_PIE_CHART = "pie_chart";
    const LANG_CHART_LINE_CHART = 'line_chart';
    const LANG_CHART_TYPE = "chart_type";
    const LANG_DESCRIPTION = "description";
    const LANG_CHART = "chart";
    const SHOW_EDITOR = "copg_show_editor";
    const TPL_FILE = "tpl.editor_slate.html";
    const PLUGIN_ID = "chrt";
    const CMD_SAVE = "save";
    const CMD_UPDATE = "update";


    public function getToolsForContextStack(CalledContexts $called_contexts) : array
    {
        global $DIC;
        $tools = [];
        $additional_data = $called_contexts->current()->getAdditionalData();

        //$pageObjectGUI = new \ilPageObjectGUI('copa', 327);
        //var_dump($pageObjectGUI->getOutputMode());

       $plugin = new \ilChartPlugin();
        var_dump($plugin->getId());
        /* $plugin_gui = new \ilChartPluginGUI();
        $editorIsActive = $plugin_gui->editorIsActive();*/

        /*var_dump($DIC->ctrl()->getCmd());
        var_dump($DIC->ctrl()->getCmdClass());
        var_dump($DIC->ctrl()->getCmdNode());*/

        /*if ($additional_data->is(self::SHOW_EDITOR, true)) {*/

        if($plugin->getId() === 'chrt' && $DIC->ctrl()->getCmd() === 'edit' && $DIC->ctrl()->getCmdClass() === 'ilpcpluggedgui') {


            //var_dump($plugin->getId());
            $title = $this->dic->language()->txt('editor');
            $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("outlined/icon_edtr.svg"), $title);
            $iff = function () {
                return $this->identification_provider->contextAwareIdentifier('chrt');
            };
            $l = function (string $content) {
                return $this->dic->ui()->factory()->legacy($content);
            };
            $tools[] = $this->factory->tool($iff("copg_editor"))
                ->withSymbol($icon)
                ->withTitle($title)
                ->withContent($l($this->getContent()));

            return $tools;
            /*$title = $this->dic->language()->txt('editor');
            $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("outlined/icon_edtr.svg"), $title);

            $iff = function ($id) {
                //var_dump($this->identification_provider->contextAwareIdentifier($id));
                return $this->identification_provider->contextAwareIdentifier('test');
            };
            $l = function (string $content) {
                return $this->dic->ui()->factory()->legacy($content);
            };
            $tools[] = $this->factory->tool($iff("copg_editor"))
                ->withSymbol($icon)
                ->withTitle($title)
                ->withContent($l($this->getContent()));

            return $tools;*/
        }
        return [];
        /*if ($additional_data->is(self::SHOW_EDITOR, true)) {

        }*/


        //return [];
    }

    public function isInterestedInContexts() : ContextCollection
    {
        return $this->context_collection->main()->repository();
    }

    private function getContent()
    {
        global $DIC;
        $lng = $DIC->language();
        $lng->loadLanguageModule("content");

        $pl = new \ilChartPlugin();

        /*$gui = new \ilChartPluginGUI();
        $properties = $gui->getProperties();*/
       /* $gui = new \ilChartPluginGUI();*/
        //$prop = $gui->getProperties();

        //var_dump($properties);
        $form = new \ilPropertyFormGUI();
        /*$form->setTitle($pl->txt(self::LANG_CHART));*/

        //$form = new \ilAsyncPropertyFormGUI();

        $titleChart = new \ilTextInputGUI($pl->txt(self::LANG_CHART_TITLE), "chart_title_slate");
        $titleChart->setRequired(false);
        $form->addItem($titleChart);


        $selectChartType = new \ilSelectInputGUI($pl->txt(self::LANG_CHART_TYPE), "chart_type_slate");
        $selectChartType->setRequired(true);
        $optionsChart = [
            "1" => $pl->txt(self::LANG_CHART_HORIZONTAL_BAR),
            "2" => $pl->txt(self::LANG_CHART_VERTICAL_BAR),
            "3" => $pl->txt(self::LANG_CHART_PIE_CHART),
            "4" => $pl->txt(self::LANG_CHART_LINE_CHART)
        ];
        $selectChartType->setOptions($optionsChart);
        $form->addItem($selectChartType);

        // Radio buttons for data format
        $radioGroup = new \ilRadioGroupInputGUI("Format", "data_format_slate");
        $radioGroup->setRequired(true);

        // Radio button for data format number with suditem for currency symbol
        $radioNumber = new \ilRadioOption($pl->txt("number"), "1");
        $currencySymbol = new \ilTextInputGUI("Symbol", "currency_symbol_slate");
        $currencySymbol->setInfo($pl->txt('add_currency_symbol'));
        $radioNumber->addSubItem($currencySymbol);


        $radioGroup->addOption($radioNumber);

        $radioPercent = new \ilRadioOption($pl->txt("percent"), "2");
        $radioGroup->addOption($radioPercent);
        $form->addItem($radioGroup);

        return $form->getHTML();
    }
}