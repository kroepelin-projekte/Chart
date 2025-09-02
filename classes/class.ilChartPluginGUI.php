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

use ILIAS\DI\Container;
use \ILIAS\UI\Component\Input\Container\Form\Standard;

/**
 * Class ilChartPluginGUI
 *
 * @author KPG <support@kroepelin-projekte.de>
 * @ilCtrl_isCalledBy ilChartPluginGUI: ilPCPluggedGUI
 */
class ilChartPluginGUI extends ilPageComponentPluginGUI
{
    private Container $dic;

    protected ilGlobalTemplateInterface $tpl;

    protected static int $id_counter = 0;

    /**
     * @var ilCtrl
     */
    protected ilCtrl $ctrl;

    /**
     * @var ilChartPlugin
     */
    protected ilPlugin $pl;
    public function __construct()
    {
        global $DIC;
        parent::__construct();
        $this->dic = $DIC;
        $this->tpl = $DIC["tpl"];
        $this->ctrl = $DIC->ctrl();
        $this->pl = ilChartPlugin::getInstance();
    }

    /**
     * @throws ilCtrlException
     */
    public function executeCommand(): void
    {
        $cmd = $this->dic->ctrl()->getCmd();
        if (in_array($cmd, [
            ilChartPluginConstant::CMD_CREATE,
            ilChartPluginConstant::CMD_SAVE,
            ilChartPluginConstant::CMD_EDIT,
            ilChartPluginConstant::CMD_EDIT_STYLE,
            ilChartPluginConstant::CMD_EDIT_DATASETS,
            ilChartPluginConstant::CMD_EDIT_CATEGORIES_DATASET_NAMES,
            ilChartPluginConstant::CMD_UPDATE,
            ilChartPluginConstant::CMD_UPDATE_CATEGORIES_DATASET_NAMES,
            ilChartPluginConstant::CMD_UPDATE_STYLE,
            ilChartPluginConstant::CMD_UPDATE_DATASETS,
            ilChartPluginConstant::CMD_CANCEL
        ])) {
            $this->$cmd();
        }
    }


    /**
     * @throws ilCtrlException
     */
    public function insert(): void
    {
        global $DIC;

        $renderer = $DIC->ui()->renderer();
        $this->setTabs(ilChartPluginConstant::LANG_CHART, false);
        $form = $this->initFormChart();
        $this->tpl->setContent($renderer->render($form));
    }

    /**
     * @throws ilCtrlException
     */
    public function create(): void
    {
        $form = $this->initFormChart();
        $form = $form->withRequest($this->dic->http()->request());
        $result = $form->getData();

        if (!$this->validate($result)) {
            $this->tpl->setOnScreenMessage("failure", $this->dic->language()->txt(ilChartPluginConstant::MESSAGE_FAILURE));
            $this->dic->ctrl()->redirectByClass(ilChartPluginConstant::PLUGIN_CLASS_NAME_GUI, ilChartPluginConstant::CMD_EDIT);
        }
        $this->updateChart($result);
    }

    /**
     * @return string[]
     */
    private function getShuffleExtendedColors(): array
    {
        $extendedColors = $this->getExtendedColors();
        shuffle($extendedColors);
        return $extendedColors;
    }

    /**
     * @throws ilCtrlException
     */
    public function edit(): void
    {
        global $DIC;

        $renderer = $DIC->ui()->renderer();
        $this->setTabs(ilChartPluginConstant::LANG_CHART, true, true);
        $form = $this->initFormChart(true);
        $this->tpl->setContent($renderer->render($form));
    }

    /**
     * @throws ilCtrlException
     */
    public function editStyle(): void
    {
        global $DIC;

        $renderer = $DIC->ui()->renderer();

        $this->setTabs(ilChartPluginConstant::TAB_STYLE, true, true);
        $form = $this->initFormStyleEdit();

        $this->tpl->setContent($renderer->render($form));
    }

    /**
     * @throws ilCtrlException
     */
    public function editDatasets(): void
    {
        global $DIC;

        $renderer = $DIC->ui()->renderer();

        $this->setTabs(ilChartPluginConstant::DATASETS, true, true);
        $form = $this->initFormDatasetsEdit();
        $this->tpl->setContent($renderer->render($form));
    }

    /**
     * @return void
     * @throws ilCtrlException
     * @throws ilFormException
     */
    public function editCategoriesDatasetNames(): void
    {
        $this->setTabs(ilChartPluginConstant::LANG_CATEGORIES_DATASETNAMES, true, true);
        $form = $this->initFormCategoriesDatasetNames(ilChartPluginConstant::CMD_UPDATE);
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * @param      $data
     * @param bool $idUpdate
     * @return void
     * @throws ilCtrlException
     */
    private function updateChart($data, bool $idUpdate = false): void
    {
        $properties = $this->getProperties();
        $properties[ilChartPluginConstant::CHART_TITLE] = $data["chart"][ilChartPluginConstant::CHART_TITLE];
        $properties[ilChartPluginConstant::CHART_TYPE] = $data["chart"][ilChartPluginConstant::CHART_TYPE];
        $properties[ilChartPluginConstant::CHART_MAX_VALUE] = $data["chart"][ilChartPluginConstant::CHART_MAX_VALUE];

        $dataFormat = $data["chart"][ilChartPluginConstant::DATA_FORMAT];
        $format = $dataFormat[0];
        if ($dataFormat[0] === "1") {
            $properties[ilChartPluginConstant::CURRENCY_SYMBOL] = $dataFormat[1]["symbol"];
        }
        $properties[ilChartPluginConstant::DATA_FORMAT] = $format;

        if (!$this->checkIfCategoriesDatasetNamesExist($properties)) {
            // Set default values
            $properties["title_category_1"] = "Kategorie";
            $properties["title_dataset_1"] = "Dataset";
            $properties["value_dataset_1_category_1"] = 0;

            $colors = $this->getShuffleExtendedColors();

            $properties["color_dataset_1"] = $colors[0];
            $properties["color_category_1"] = $colors[1];
        }

        $success = false;
        if ($idUpdate) {
            if ($this->updateElement($properties)) {
                $success = true;
            }
        } else {
            if ($this->createElement($properties)) {
                $success = true;
            }
        }

        if ($success) {
            $this->tpl->setOnScreenMessage("success", $this->dic->language()->txt(ilChartPluginConstant::MESSAGE_SUCCESS), true);

            if($idUpdate) {
                $this->dic->ctrl()->redirectByClass(ilChartPluginConstant::PLUGIN_CLASS_NAME_GUI, ilChartPluginConstant::CMD_EDIT);
            }
            $this->returnToParent();
        }
        $this->tpl->setOnScreenMessage("failure", $this->dic->language()->txt(ilChartPluginConstant::MESSAGE_FAILURE), true);
        $this->returnToParent();
    }

    /**
     * @param array $a_properties
     * @return string
     */
    private function checkIfCategoriesDatasetNamesExist(array $a_properties): string
    {
        $exist = false;
        foreach ($a_properties as $key => $value) {
            if (str_starts_with($key, "title_category") || str_starts_with($key, "title_dataset")) {
                $exist = true;
                break;
            }
        }
        return $exist;
    }

    /**
     * @throws ilCtrlException
     */
    private function update(): void
    {
        $request = $this->dic->http()->request();
        $form = $this->initFormChart(true);
        $form = $form->withRequest($request);
        $result = $form->getData();

        if ($request->getMethod() == "POST") {
            if (!$this->validate($result)) {
                $this->tpl->setOnScreenMessage("failure", $this->dic->language()->txt(ilChartPluginConstant::MESSAGE_FAILURE));
                $this->dic->ctrl()->redirectByClass(ilChartPluginConstant::PLUGIN_CLASS_NAME_GUI, ilChartPluginConstant::CMD_EDIT);
            }

            $this->updateChart($result, true);
        }
        $this->tpl->setOnScreenMessage("failure", $this->dic->language()->txt(ilChartPluginConstant::MESSAGE_FAILURE));
        $this->dic->ctrl()->redirect($this, ilChartPluginConstant::CMD_EDIT);
    }

    /**
     * @return void
     * @throws ilCtrlException|ilFormException
     */
    public function updateCategoriesDatasetNames(): void
    {
        $form = $this->initFormCategoriesDatasetNames("update");

        $properties = $this->getProperties();

        if (!$form->checkInput()) {
            $this->tpl->setOnScreenMessage("failure", $this->dic->language()->txt(ilChartPluginConstant::MESSAGE_FAILURE));
            $this->setTabs(ilChartPluginConstant::LANG_CATEGORIES_DATASETNAMES, true, true);
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHtml());
            return;
        }

        $datasetValues = [];
        foreach ($form->getInput(ilChartPluginConstant::CATEGORIES) as $key => $value) {
            foreach ($form->getInput(ilChartPluginConstant::DATASETS) as $k => $val) {

                if(array_key_exists("value_dataset_" . ($k + 1) . "_category_" . ($key + 1), $properties)) {
                    $datasetValues["value_dataset_" . ($k + 1) . "_category_" . ($key + 1)] = $properties["value_dataset_" . ($k + 1) . "_category_" . ($key + 1)];
                } else {
                    $datasetValues["value_dataset_" . ($k + 1) . "_category_" . ($key + 1)] = "0";
                }
            }
        }

        if (count($form->getInput(ilChartPluginConstant::CATEGORIES)) !== count(array_unique($form->getInput(ilChartPluginConstant::CATEGORIES)))) {
            $this->tpl->setOnScreenMessage("failure", $this->plugin->txt("category_names_unique"));
            $this->setTabs(ilChartPluginConstant::LANG_CHART, true);
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHtml());
            return;
        }

        if (count($form->getInput(ilChartPluginConstant::DATASETS)) !== count(array_unique($form->getInput(ilChartPluginConstant::DATASETS)))) {
            $this->tpl->setOnScreenMessage("failure", $this->plugin->txt("datasets_names_unique"));
            $this->setTabs(ilChartPluginConstant::LANG_CHART, true);
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHtml());
            return;
        }

        $shuffleExtendedColors = $this->getShuffleExtendedColors();
        // Set default colors for categories
        $j = 0; // Key in $extendedColors array
        for ($i = 0; $i < count($form->getInput(ilChartPluginConstant::CATEGORIES)); $i++) {
            $color = $shuffleExtendedColors[$j];

            if ($j === count($shuffleExtendedColors) - 1) {
                $j = 0;
            } else {
                $j += 1;
            }
            $properties["color_category_".($i + 1)] = $color;
        }

        $shuffleExtendedColors = $this->getShuffleExtendedColors();
        // Set default colors for datasets
        $j = 0; // Key in $extendedColors array
        for ($i = 0; $i < count($form->getInput(ilChartPluginConstant::DATASETS)); $i++) {
            $color = $shuffleExtendedColors[$j];

            if ($j === count($shuffleExtendedColors) - 1) {
                $j = 0;
            } else {
                $j += 1;
            }
            $properties["color_dataset_".($i + 1)] = $color;
        }
        $properties = array_merge($properties, $datasetValues);

        $categories = $form->getInput(ilChartPluginConstant::CATEGORIES);

        foreach ($properties as $key => $value) {
            if (str_starts_with($key,'title_category_')) {
                unset($properties[$key]);
            } else if (str_starts_with($key,'title_dataset_')) {
                unset($properties[$key]);
            }
        }

        foreach ($categories as $key => $value) {
            $properties["title_category_".($key + 1)] = $value;
        }

        $datasets = $form->getInput(ilChartPluginConstant::DATASETS);
        foreach ($datasets as $key => $value) {
            $properties["title_dataset_".($key + 1)] = $value;
        }

        if ($this->updateElement($properties)) {
            $this->tpl->setOnScreenMessage("success", $this->dic->language()->txt(ilChartPluginConstant::MESSAGE_SUCCESS), true);
            $this->dic->ctrl()->redirectByClass(ilChartPluginConstant::PLUGIN_CLASS_NAME_GUI, ilChartPluginConstant::CMD_EDIT_CATEGORIES_DATASET_NAMES);
        }
    }

    /**
     * @throws ilCtrlException
     */
    private function updateStyle(): void
    {
        global $DIC;

        $request = $DIC->http()->request();
        $form  = $this->initFormStyleEdit();

        if ($request->getMethod() == "POST") {
            $form  = $form->withRequest($request);
            $formData = $form->getData();
            $properties = $this->getProperties();

            $this->convertColorPickerFieldsToString(
                $properties,
                $formData
            );

            if ($this->updateElement($properties)) {
                $this->tpl->setOnScreenMessage("success", $this->dic->language()->txt(ilChartPluginConstant::MESSAGE_SUCCESS), true);
                $this->dic->ctrl()->redirect($this, ilChartPluginConstant::CMD_EDIT_STYLE);
            }
        }
        $this->tpl->setOnScreenMessage("failure", $this->dic->language()->txt(ilChartPluginConstant::MESSAGE_FAILURE));
        $this->dic->ctrl()->redirect($this, ilChartPluginConstant::CMD_EDIT_STYLE);
    }

    /**
     * @throws ilCtrlException
     */
    public function updateDatasets(): void
    {
        $request = $this->dic->http()->request();
        $form = $this->initFormDatasetsEdit();
        $form = $form->withRequest($request);
        $result = $form->getData();

        if ($request->getMethod() == "POST") {
            $properties = $this->getProperties();
            $countDatasets = $this->getCountPropertiesByType($properties, "title_dataset");
            $countCategories = $this->getCountPropertiesByType($properties, "title_category");

            for ($i = 0; $i < $countCategories; $i++) {
                if (empty($result["group_category_" . ($i + 1)][1])) {
                    for ($j = 0; $j < $countDatasets; $j++) {
                        $value = trim($result["hidden_dataset_" . ($j + 1) . "_category_" . ($i + 1)]);

                        if (!is_numeric($value) || (str_starts_with($value, "0") && strlen((string) abs($value)) > 1)) {
                            $this->tpl->setOnScreenMessage(
                                "failure", $this->dic->language()->txt(ilChartPluginConstant::MESSAGE_FAILURE)
                            );
                            $this->dic->ctrl()->redirect($this, ilChartPluginConstant::CMD_EDIT_DATASETS);
                        }

                        if ($value === "") {
                            $value = 0;
                        }
                        $properties["value_dataset_" . ($j + 1) . "_category_" . ($i + 1)] = $value;
                    }
                } else {
                    for ($j = 0; $j < $countDatasets; $j++) {
                        $value = trim(
                            $result["group_category_" . ($i + 1)][1]["dataset_" . ($j + 1) . "_category_" . ($i + 1)]
                        );

                        if (!is_numeric($value) || (str_starts_with($value, "0") && strlen((string) abs($value)) > 1)) {
                            $this->tpl->setOnScreenMessage(
                                "failure",
                                $this->dic->language()->txt(ilChartPluginConstant::MESSAGE_FAILURE),
                                true
                            );
                            $this->dic->ctrl()->redirect($this, ilChartPluginConstant::CMD_EDIT_DATASETS);
                        }

                        if ($value === "") {
                            $value = 0;
                        }
                        $properties["value_dataset_" . ($j + 1) . "_category_" . ($i + 1)] = $value;
                    }
                }
            }

            if ($this->updateElement($properties)) {
                $this->tpl->setOnScreenMessage("success", $this->dic->language()->txt(ilChartPluginConstant::MESSAGE_SUCCESS), true);
                $this->dic->ctrl()->redirect($this, ilChartPluginConstant::CMD_EDIT_DATASETS);
            }
        }
        $this->tpl->setOnScreenMessage("failure", $this->dic->language()->txt(ilChartPluginConstant::MESSAGE_SUCCESS), true);
        $this->dic->ctrl()->redirect($this, ilChartPluginConstant::CMD_EDIT_DATASETS);
    }

    /**
     * @param array  $properties
     * @param string $searchString
     * @return int
     */
    private function getCountPropertiesByType(array $properties, string $searchString): int
    {
        $count = 0;
        foreach($properties as $key => $value) {

            if (strpos($key, $searchString) > -1) {
                $count += 1;
            }
        }
        return $count;
    }

    /**
     * @param        $data
     * @return bool
     */
    private function validate($data): bool
    {
        if (array_key_exists("chart_type", $data["chart"])
            && $data["chart"]["chart_type"] === ""
        ) {
            return false;
        }

        if (array_key_exists("chart_type", $data["chart"])
            && !is_numeric($data["chart"]["chart_max_value"])
            && $data["chart"]["chart_max_value"] !== ""
        ) {
            return false;
        }
        return true;
    }

    /**
     * @return array|string[]
     */
    private function getExtendedColors(): array
    {
        $parentId = $this->getPlugin()->getParentId();
        $objStylesheet = new ilObjStyleSheet();
        $styleId = $objStylesheet->lookupObjectStyle($parentId);

        $extendedColorsCode = [];
        if ($styleId === 0) {
            $extendedColorsCode = $this->getExtendedColorsDefaultILIAS();
        } else {
            $objStyle = new ilObjStyleSheet($styleId);
            $colors = $objStyle->getColors();

            foreach ($colors as $color) {
                if (strpos($color["name"], "extendedcolor") > -1) {
                    $extendedColorsCode[] = $color["code"];
                }
            }

            // If the extended colors in the selected custom content style don't exist
            if (empty($extendedColorsCode)) {
                $extendedColorsCode = $this->getExtendedColorsDefaultILIAS();
            }
        }
        return $extendedColorsCode;
    }

    /**
     * @return string[]
     */
    private function getExtendedColorsDefaultILIAS(): array
    {
        return [
            "f3de2c",
            "cddc39",
            "59a0a5",
            "86cb92",
            "ce73a8",
            "82639e",
            "9e7c7d",
            "f75e82",
            "ea4d54",
        ];
    }

    /**
     * @throws ilCtrlException
     */
    public function initFormChart(bool $isUpdate = false): Standard
    {
        global $DIC;

        $ui = $DIC->ui()->factory();
        $prop = $this->getProperties();

        $inputFields[ilChartPluginConstant::CHART_TITLE] = $ui->input()->field()->text(
            $this->getPlugin()->txt(ilChartPluginConstant::CHART_TITLE),
            ""
        )->withValue($prop[ilChartPluginConstant::CHART_TITLE] ?? "")->withRequired(true);

        $optionsChart = [
            "1" => $this->getPlugin()->txt(ilChartPluginConstant::LANG_CHART_HORIZONTAL_BAR),
            "2" => $this->getPlugin()->txt(ilChartPluginConstant::LANG_CHART_VERTICAL_BAR),
            "3" => $this->getPlugin()->txt(ilChartPluginConstant::LANG_CHART_PIE_CHART),
            "4" => $this->getPlugin()->txt(ilChartPluginConstant::LANG_CHART_LINE_CHART)
        ];

        $inputFields[ilChartPluginConstant::CHART_TYPE] = $ui->input()->field()->select(
            $this->getPlugin()->txt(ilChartPluginConstant::CHART_TYPE),
            $optionsChart
        )->withValue($prop[ilChartPluginConstant::CHART_TYPE] ?? "")->withRequired(true);

        $inputFields[ilChartPluginConstant::CHART_MAX_VALUE] = $ui->input()->field()->text(
            $this->getPlugin()->txt(ilChartPluginConstant::CHART_MAX_VALUE),
            ""
        )->withValue($prop[ilChartPluginConstant::CHART_MAX_VALUE] ?? "");

        $group1 = $ui->input()->field()->group(
            [
                "symbol" => $ui->input()->field()->text($this->getPlugin()->txt(ilChartPluginConstant::SYMBOL), $this->getPlugin()->txt("add_currency_symbol"))
                                                 ->withValue($prop[ilChartPluginConstant::CURRENCY_SYMBOL] ?? "")
            ],
            $this->getPlugin()->txt("number")
        );

        $group2 = $ui->input()->field()->group(
            [],
            $this->getPlugin()->txt("percent")
        );

        $inputFields[ilChartPluginConstant::DATA_FORMAT] = $ui->input()->field()->switchableGroup(
            [
                "1" => $group1,
                "2" => $group2
            ],
            $this->getPlugin()->txt("format")
        )->withValue($prop[ilChartPluginConstant::DATA_FORMAT] ?? "1");

        $sectionChart = $ui->input()->field()->section(
            $inputFields,
            $this->getPlugin()->txt(ilChartPluginConstant::CMD_EDIT),
            $this->getPlugin()->txt(ilChartPluginConstant::LANG_DESCRIPTION),
        );

        $fallbackCmd = ilChartPluginConstant::CMD_CREATE;
        if ($isUpdate) {
            $fallbackCmd = ilChartPluginConstant::CMD_UPDATE;
        }

        return $ui->input()->container()->form()->standard(
            $this->dic->ctrl()->getFormAction($this, $fallbackCmd),
            [
                "chart" => $sectionChart
            ],
        );
    }

    /**
     * @return Standard
     * @throws ilCtrlException
     */
    public function initFormStyleEdit(): Standard
    {
        global $DIC;

        $ui = $DIC->ui()->factory();
        $prop = $this->getProperties();

        $inputFieldsCategoriesColors = [];
        $countColorsCategory = 0;
        foreach ($prop as $k => $val) {
            if (strpos($k, "title_category") > -1) {
                $i = substr($k, strpos($k, "title_category") + 15, strlen($k));

                $inputFieldsCategoriesColors["color_category_" . $i] = $ui->input()->field()->colorPicker(
                    $val,
                    ""
                )->withValue("#" . $prop["color_category_" . $i])->withRequired(true);

                $countColorsCategory = $countColorsCategory + 1;
            }
        }

        $inputFieldsCategoriesColors["count_colors_categories"] = $ui->input()->field()->hidden()
                                                                       ->withValue($countColorsCategory)->withRequired(true);
        $inputFieldsDatasetsColors = [];
        $countColorsDataset = 0;
        foreach ($prop as $k => $val) {

            if (strpos($k, "title_dataset") > -1) {
                $i = substr($k, strpos($k, "title_dataset") + 14, strlen($k));

                $inputFieldsDatasetsColors["color_dataset_" . $i] = $ui->input()->field()->colorPicker(
                    $val,
                    ""
                )->withValue("#" . $prop["color_dataset_" . $i])->withRequired(true);

                $countColorsDataset = $countColorsDataset + 1;
            }
        }

        $inputFieldsDatasetsColors["count_colors_datasets"] = $ui->input()->field()->hidden()
                                                     ->withValue($countColorsDataset)->withRequired(true);

        $sectionCategories = $ui->input()->field()->section(
            $inputFieldsCategoriesColors,
            $this->getPlugin()->txt(ilChartPluginConstant::CATEGORIES),
            $this->getPlugin()->txt("description_style_categories"),
        );

        $sectionDatasets = $ui->input()->field()->section(
            $inputFieldsDatasetsColors,
            $this->getPlugin()->txt(ilChartPluginConstant::DATASETS),
            $this->getPlugin()->txt("description_style_datasets"),
        );

        $formAction = $DIC->ctrl()->getFormActionByClass(
            ilChartPluginConstant::PLUGIN_CLASS_NAME_GUI,
            ilChartPluginConstant::CMD_UPDATE_STYLE
        );

        return $ui->input()->container()->form()->standard(
            $formAction,
            [
                "categories" => $sectionCategories,
                "datasets" => $sectionDatasets
            ],
        );
    }

    /**
     * @throws ilCtrlException
     */
    public function initFormDatasetsEdit(): Standard
    {
        global $DIC;

        $ui = $DIC->ui()->factory();
        $prop = $this->getProperties();

        $countCategories = 0;
        $countDatasets = 0;
        foreach($prop as $key => $value) {
            if(strpos($key, "title_category_") > -1) {
                $countCategories += 1;
            }
            if(strpos($key, "title_dataset_") > -1) {
                $countDatasets += 1;
            }
        }

        $hiddenInputs = [];
        $inputs = [];
        for($i = 0; $i < $countCategories; $i++) {
            $groupCategories = [];
            $inputDatasets = [];
            for($j = 0; $j < $countDatasets; $j++) {
                $inputDatasets["dataset_" . ($j + 1) . "_category_" . ($i + 1)] = $ui->input()->field()->text($prop["title_dataset_" . ($j + 1)])->withValue($prop["value_dataset_" .($j + 1) . "_category_" . ($i + 1)]);
                $hiddenInputs["dataset_" . ($j + 1) . "_category_" . ($i + 1)] = $ui->input()->field()->hidden()->withValue($prop["value_dataset_" .($j + 1) . "_category_" . ($i + 1)]);
            }

            $groupCategories["category_" . ($i + 1)] = $ui->input()->field()->group(
                $inputDatasets,
                $prop["title_category_" . ($i + 1)]
            );

            $switchableGroup = $ui->input()->field()->switchableGroup(
                $groupCategories,
                ""
            );

            $inputs["group_category_" . ($i + 1)] = $switchableGroup;
        }

        foreach ($hiddenInputs as $key => $input) {
            $inputs["hidden_" . $key] = $input;
        }

        return $ui->input()->container()->form()->standard(
            $this->dic->ctrl()->getFormAction($this, ilChartPluginConstant::CMD_UPDATE_DATASETS),
            $inputs
        );
    }

    /**
     * @throws ilFormException
     * @throws ilCtrlException
     */
    public function initFormCategoriesDatasetNames(string $action): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->getPlugin()->txt(ilChartPluginConstant::CMD_EDIT));
        $form->setDescription($this->getPlugin()->txt(ilChartPluginConstant::LANG_DESCRIPTION));
        $prop = $this->getProperties();

        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->getPlugin()->txt("categories_names"));
        $header->setInfo($this->getPlugin()->txt("categories_info"));
        $form->addItem($header);

        $countCategory = $this->getCountPropertiesByType($prop, "title_category");

        $categoriesTitle = [];
        for($i = 0; $i < $countCategory; $i++) {
            $categoriesTitle[] = $prop["title_category_".($i + 1)];
        }

        $category = new ilTextInputGUI($this->lng->txt("title"), ilChartPluginConstant::CATEGORIES);
        $category->setRequired(true);
        $category->setMulti(true, true);

        $multiCategories = [];
        foreach ($categoriesTitle as $key => $title) {
            if (!$key) {
                $category->setValue($title);
            }
            $multiCategories[] = $title;
        }
        $category->setMultiValues($multiCategories);
        $form->addItem($category);

        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->getPlugin()->txt("datasets_names"));
        $header->setInfo($this->getPlugin()->txt("datasets_info"));
        $form->addItem($header);

        $countDataset = $this->getCountPropertiesByType($prop, "title_dataset");
        $datasetsTitle = [];
        for($i = 0; $i < $countDataset; $i++) {
            $datasetsTitle[] = $prop["title_dataset_".($i + 1)];
        }

        $dataset = new ilTextInputGUI($this->getPlugin()->txt(ilChartPluginConstant::DATASETS), ilChartPluginConstant::DATASETS);
        $dataset->setRequired(true);
        $dataset->setMulti(true, true);

        $multiDatasets = [];
        foreach ($datasetsTitle as $key => $title) {
            if (!$key) {
                $dataset->setValue($title);
            }
            $multiDatasets[] = $title;
        }
        $dataset->setMultiValues($multiDatasets);
        $form->addItem($dataset);

        if ($action === ilChartPluginConstant::CMD_INSERT) {
            $form->addCommandButton(ilChartPluginConstant::CMD_CREATE_CATEGORIES_DATASET_NAMES, $this->dic->language()->txt(ilChartPluginConstant::CMD_SAVE));
        } else {
            $form->addCommandButton(ilChartPluginConstant::CMD_UPDATE_CATEGORIES_DATASET_NAMES, $this->dic->language()->txt(ilChartPluginConstant::CMD_SAVE));
        }
        $form->addCommandButton(ilChartPluginConstant::CMD_CANCEL, $this->dic->language()->txt(ilChartPluginConstant::CMD_CANCEL));
        $form->setFormAction($this->dic->ctrl()->getFormAction($this));

        return $form;
    }

    /**
     * @return void
     */
    public function cancel(): void
    {
        $this->returnToParent();
    }

    /**
     * @param array $properties
     * @return int
     */
    private function getCountCategories(array $properties): int
    {
        $count = 0;
        foreach($properties as $key => $value) {
            if(strpos($key, "title_category") > -1) {
                $count += 1;
            }
        }
        return $count;
    }

    /**
     * @param string $chart_type
     * @return string
     */
    private function getChartType(string $chart_type): string
    {
        if ($chart_type == "1") {
            return "horizontalBar";
        } elseif ($chart_type == "2") {
            return "bar";
        } elseif ($chart_type == "3") {
            return "pie";
        } else {
            return "line";
        }
    }

    /**
     * @param array $a_properties
     * @return string
     */
    private function percentDataFormat(array $a_properties): string
    {
        $percent = "";
        $indexDataset = "";
        $datasets = [];
        $datasetsValueCategory = [];
        $countCategories = $this->getCountCategories($a_properties);
        if ($a_properties[ilChartPluginConstant::DATA_FORMAT] === "2") {
            for($i = 0; $i < $countCategories; $i++) {
                foreach ($a_properties as $key => $value) {
                    if (strpos($key, "value_dataset") > -1 && strpos($key, "_category_" . ($i + 1)) > -1) {
                        $indexDataset = substr($key, 14, strpos($key, "_category_") - 14);
                    }
                    if (strpos($key, "_category_" . ($i + 1)) > -1 && ($key !== "title_category_" . ($i + 1)) && ($key !== "color_category_" . ($i + 1))) {
                        $value = $a_properties["value_dataset_" . $indexDataset ."_category_" .($i + 1)];
                        if (strpos($value, ",") > -1) {
                            $value = str_replace(",", ".", $value);
                        }
                        $datasetsValueCategory["category_" . ($i + 1)]["dataset_". $indexDataset] = $value;
                    }
                }
            }

            foreach($datasetsValueCategory as $key => $value) {
                $indexCategory = substr($key, strpos($key, "category_") + 9);
                foreach($value as $k => $val) {
                    $indexDataset = substr($k, strpos($k, "dataset_") + 8);
                    $datasets["dataset_" . $indexDataset]["category_" . $indexCategory] = $datasetsValueCategory["category_" . $indexCategory]["dataset_" . $indexDataset];
                }
            }
        }

        $sumDatasetValues = [];
        foreach($datasets as $key => $value) {
            $indexDataset = substr($key, strpos($key, "dataset_") + 8);
            $sumDataset = 0;
            foreach($value as $k => $val) {
                $indexCategory = substr($k, strpos($k, "category_") + 9);
                if(strpos($datasets["dataset_" . $indexDataset]["category_". $indexCategory], ",") > -1) {
                    $datasets["dataset_" . $indexDataset]["category_". $indexCategory] = str_replace(",", ".", $value);
                }
                $tmpVal = (float) $datasets["dataset_" . $indexDataset]["category_". $indexCategory];
                $sumDataset += $tmpVal;
            }
            $sumDatasetValues["sum_dataset_" . $indexDataset] = $sumDataset;
        }
        foreach($datasets as $key => $value) {
            $indexDataset = substr($key, strpos($key, "dataset_") + 8);
            if($sumDatasetValues["sum_dataset_" . $indexDataset] > 0) {
                foreach ($value as $k => $val) {
                    $indexCategory = substr($k, strpos($k, "category_") + 9);
                    $tmpVal = (float)$datasets["dataset_" . $indexDataset]["category_" . $indexCategory];
                    $percentValue = round(($tmpVal * 100 / $sumDatasetValues["sum_dataset_" . $indexDataset]), 2);
                    $percent .= '<input type="hidden" id="' . $key . "_" . $k . '_percent" value="' . $percentValue . '">';
                }
            }
        }
        return $percent;
    }

    /**
     * @param array $a_properties
     * @return string
     */
    private function titleCategoryInputFields(array $a_properties): string
    {
        $categoryFields = "";
        foreach ($a_properties as $key => $value) {
            if (strpos($key, "title_category") > -1) {
                $categoryFields .= '<input type="hidden" id="'.$key.'" value="'.$value.'">';
            }
        }
        return $categoryFields;
    }

    /**
     * @param array $a_properties
     * @return string
     */
    private function titleDatasetInputFields(array $a_properties): string
    {
        $datasetFields = "";
        foreach ($a_properties as $key => $value) {
            if (strpos($key, "title_dataset") > -1) {
                $datasetFields .= '<input type="hidden" id="'.$key.'" value="'.$value.'">';
            }
        }
        return $datasetFields;
    }

    /**
     * @param array $a_properties
     * @return string
     */
    private function valueDatasetInputFields(array $a_properties): string
    {
        $valueFields = "";
        foreach ($a_properties as $key => $value) {
            if (strpos($key, "value_dataset") > -1) {
                $value = str_replace(',', '.', $value);
                $valueFields .= '<input type="hidden" id="'.$key.'" value="' . $value . '">';
            }
        }
        return $valueFields;
    }

    /**
     * @param array $a_properties
     * @return string
     */
    private function colorCategoryInputField(array $a_properties): string
    {
        $colorFields = "";
        foreach ($a_properties as $key => $value) {
            if (strpos($key, "color_category") > -1) {
                $colorFields .= '<input type="hidden" id="'.$key.'" value="'.$value.'">';
            }
        }
        return $colorFields;
    }

    /**
     * Get color dataset in input fields
     *
     * @param array $a_properties
     * @return string
     */
    private function colorDatasetInputField(array $a_properties): string
    {
        $colorFields = "";
        foreach ($a_properties as $key => $value) {
            if (strpos($key, "color_dataset") > -1) {
                $colorFields .= '<input type="hidden" id="'.$key.'" value="'.$value.'">';
            }
        }
        return $colorFields;
    }

    /**
     * @throws ilTemplateException
     * @throws ilSystemStyleException
     */
    public function getElementHTML(string $a_mode, array $a_properties, string $plugin_version): string
    {
        global $DIC;

        self::$id_counter += 1;
        $divCanvasId = ilChartPluginConstant::DIV_CANVAS_ID_PREFIX . self::$id_counter;
        $divId = ilChartPluginConstant::DIV_ID_PREFIX . self::$id_counter;
        $id = ilChartPluginConstant::CANVAS_ID_PREFIX . self::$id_counter;

        $template = $DIC->ui()->mainTemplate();
        $template->addCss(ilChartPluginConstant::PLUGIN_DIRECTORY . "/css/chart.css");
        $template->addJavaScript(ilChartPluginConstant::PLUGIN_DIRECTORY . "/js/Chart.min.js");
        $template->addJavaScript(ilChartPluginConstant::PLUGIN_DIRECTORY . "/js/chartjs-plugin-datalabels.min.js");
        $template->addJavaScript(ilChartPluginConstant::PLUGIN_DIRECTORY . "/js/script.js");

        $properties = $a_properties;

        $tpl = new ilTemplate(
            "tpl.content.html",
            true,
            true,
            "public/" . ilChartPluginConstant::PLUGIN_DIRECTORY,
            ilGlobalTemplateInterface::DEFAULT_BLOCK,
            true
        );
        $tpl->setVariable("DIV", $divId);

        $tpl->setVariable("DIV_CANVAS_ID", $divCanvasId);
        $tpl->setVariable("CHART_ID", $id);
        $tpl->setVariable("CHART_TITLE", $properties[ilChartPluginConstant::CHART_TITLE]);
        $tpl->setVariable("CHART_TYPE", $this->getChartType($properties[ilChartPluginConstant::CHART_TYPE]));
        $tpl->setVariable("CHART_MAX_VALUE", $properties[ilChartPluginConstant::CHART_MAX_VALUE]);
        $tpl->setVariable("CHART_DATA_FORMAT", $properties[ilChartPluginConstant::DATA_FORMAT]);

        if (!empty($properties[ilChartPluginConstant::CURRENCY_SYMBOL])) {
            $tpl->setVariable("CHART_CURR_SYMBOL", $properties[ilChartPluginConstant::CURRENCY_SYMBOL]);
        }

        $tpl->setVariable("TITLE_CATEGORIES", $this->titleCategoryInputFields($properties));
        $tpl->setVariable("TITLE_DATASETS", $this->titleDatasetInputFields($properties));
        $tpl->setVariable("VALUE_DATASETS", $this->valueDatasetInputFields($properties));
        $tpl->setVariable("COLOR_CATEGORY", $this->colorCategoryInputField($properties));
        $tpl->setVariable("COLOR_DATASET", $this->colorDatasetInputField($properties));
        $tpl->setVariable("PERC", $this->percentDataFormat($properties));

        $tpl->parseCurrentBlock();

        return $tpl->get();
    }

    /**
     * @throws ilCtrlException
     */
    private function setTabs(
        string $activeTab,
        bool $tabStyleVisible,
        bool $tabCategoriesDatasetNamesIsVisible = false
    ): void {
        $pl = $this->getPlugin();
        $this->dic->tabs()->addTab(
            ilChartPluginConstant::TAB_CHART,
            $pl->txt(ilChartPluginConstant::LANG_CHART),
            $this->dic->ctrl()->getLinkTarget($this, ilChartPluginConstant::CMD_EDIT)
        );

        if ($tabCategoriesDatasetNamesIsVisible) {
            $this->dic->tabs()->addTab(
                ilChartPluginConstant::TAB_CATEGORIES_DATASETNAMES,
                $pl->txt(ilChartPluginConstant::LANG_CATEGORIES_DATASETNAMES),
                $this->dic->ctrl()->getLinkTarget($this, ilChartPluginConstant::CMD_EDIT_CATEGORIES_DATASET_NAMES)
            );
        }

        if ($tabStyleVisible) {
            $this->dic->tabs()->addTab(
                ilChartPluginConstant::TAB_DATASETS,
                $pl->txt(ilChartPluginConstant::LANG_CHART_DATASETS),
                $this->dic->ctrl()->getLinkTarget($this, ilChartPluginConstant::CMD_EDIT_DATASETS)
            );
            $this->dic->tabs()->addTab(
                ilChartPluginConstant::TAB_STYLE,
                $pl->txt(ilChartPluginConstant::LANG_CHART_STYLE),
                $this->dic->ctrl()->getLinkTarget($this, ilChartPluginConstant::CMD_EDIT_STYLE)
            );
        }

        if ($activeTab === "chart") {
            $this->dic->tabs()->activateTab(ilChartPluginConstant::TAB_CHART);
        } elseif ($activeTab === "style") {
            $this->dic->tabs()->activateTab(ilChartPluginConstant::TAB_STYLE);
        } elseif ($activeTab === "datasets") {
            $this->dic->tabs()->activateTab(ilChartPluginConstant::TAB_DATASETS);
        } elseif($activeTab === "categories_datasetnames") {
            $this->dic->tabs()->activateTab(ilChartPluginConstant::TAB_CATEGORIES_DATASETNAMES);
        }
    }

    /**
     * Convert rgb color to hex
     *
     * @param $r
     * @param $g
     * @param $b
     * @return string
     */
    private function rgbToHex($r, $g, $b): string
    {
        return sprintf("#%02x%02x%02x", $r, $g, $b);
    }

    /**
     * @param array $properties
     * @param array $formData
     * @return void
     */
    private function convertColorPickerFieldsToString(
        array &$properties,
        array $formData
    ): void {
        $countColorsCategories = $formData["categories"]["count_colors_categories"];
        $countColorsDatasets = $formData["datasets"]["count_colors_datasets"];

        for ($i = 0; $i < $countColorsCategories; $i++) {
            $rgbColor = $formData["categories"]["color_category_" . ($i + 1)];
            $hexColor = $this->rgbToHex($rgbColor->r(), $rgbColor->g(), $rgbColor->b());
            $hexColor = str_replace("#", "", $hexColor);

            $properties["color_category_" . ($i + 1)] = $hexColor;
        }

        for ($i = 0; $i < $countColorsDatasets; $i++) {
            $rgbColor = $formData["datasets"]["color_dataset_" . ($i + 1)];
            $hexColor = $this->rgbToHex($rgbColor->r(), $rgbColor->g(), $rgbColor->b());
            $hexColor = str_replace("#", "", $hexColor);

            $properties["color_dataset_" . ($i + 1)] = $hexColor;
        }
    }
}
