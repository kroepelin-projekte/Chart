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
    private const PLUGIN_DIRECTORY = "Customizing/global/plugins/Services/COPage/PageComponent/Chart";

    private const PLUGIN_CLASS_NAME = self::class;

    private const CMD_CANCEL = "cancel";

    private const CMD_CREATE = "create";

    private const CMD_CREATE_CATEGORIES_DATASET_NAMES = "createCategoriesDatasetNames";

    private const CMD_UPDATE_CATEGORIES_DATASET_NAMES = "updateCategoriesDatasetNames";

    private const CMD_SAVE = "save";

    private const CMD_INSERT = "insert";

    private const CMD_UPDATE = "update";

    private const CMD_EDIT = "edit";

    private const CMD_EDIT_STYLE = "editStyle";

    private const CMD_UPDATE_STYLE = "updateStyle";

    private const CMD_EDIT_DATASETS = "editDatasets";

    private const CMD_EDIT_CATEGORIES_DATASET_NAMES = "editCategoriesDatasetNames";

    private const CMD_UPDATE_DATASETS = "updateDatasets";

    private const TAB_STYLE = "style";

    private const LANG_CHART_STYLE = "chart_style";

    private const LANG_DESCRIPTION = "description";

    private const LANG_CHART_DATASETS = "chart_datasets";

    private const LANG_DESCRIPTION_DATASETS = "description_datasets";

    private const LANG_CHART = "chart";

    private const LANG_CATEGORIES_DATASETNAMES = "categories_datasetnames";

    private const LANG_CHART_HORIZONTAL_BAR = "horizontal_bar_chart";

    private const LANG_CHART_VERTICAL_BAR = "vertical_bar_chart";

    private const LANG_CHART_PIE_CHART = "pie_chart";

    private const LANG_CHART_LINE_CHART = "line_chart";

    private const CANVAS_ID_PREFIX = "chart_page_component_";

    private const DIV_CANVAS_ID_PREFIX = "div_canvas_";

    private const DIV_ID_PREFIX = "chart_div_";

    private const MESSAGE_SUCCESS = "msg_obj_modified";

    private const MESSAGE_FAILURE = "form_input_not_valid";

    private const CHART_TITLE = "chart_title";

    private const CHART_TYPE = "chart_type";

    private const DATA_FORMAT = "data_format";

    private const SYMBOL = "symbol";

    private const PERCENT = "percent";

    private const CURRENCY_SYMBOL = "currency_symbol";

    private const CHART_MAX_VALUE = "chart_max_value";

    private const CATEGORIES = "categories";

    private const DATASETS = "datasets";

    private const DESCRIPTION_EDIT_STYLE = "description_edit_style";

    private const FORM_CHART = "chart";

    private const FORM_CATEGORIES_DATASETS = "categories-datasets";

    private const FORM_DATASETS = "datasets";

    private const FORM_STYLE = "style";

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
        if (in_array($cmd, array(self::CMD_CREATE,
                                 self::CMD_SAVE,
                                 self::CMD_EDIT,
                                 self::CMD_EDIT_STYLE,
                                 self::CMD_EDIT_DATASETS,
                                 self::CMD_EDIT_CATEGORIES_DATASET_NAMES,
                                 self::CMD_UPDATE,
                                 self::CMD_UPDATE_CATEGORIES_DATASET_NAMES,
                                 self::CMD_UPDATE_STYLE,
                                 self::CMD_UPDATE_DATASETS,
                                 self::CMD_CANCEL
        ))) {
            $this->$cmd();
        }
    }


    /**
     * @throws ilCtrlException
     * @throws ilFormException
     */
    public function insert(): void
    {
        global $DIC;

        $renderer = $DIC->ui()->renderer();
        $this->setTabs(self::LANG_CATEGORIES_DATASETNAMES, false, true /* TODO remove it */);
        $form = $this->initFormChart();
        $this->tpl->setContent($renderer->render($form));


    }

    /**
     * @throws ilFormException
     * @throws ilCtrlException
     */
    public function create(): void
    {

        $form = $this->initFormChart();
        $form = $form->withRequest($this->dic->http()->request());
        $result = $form->getData();

        // TODO Test it
        if (!$this->validate($result, self::FORM_CHART)) {
            $this->tpl->setOnScreenMessage("failure", $this->dic->language()->txt(self::MESSAGE_FAILURE));
            $this->dic->ctrl()->redirectByClass(self::PLUGIN_CLASS_NAME, self::CMD_EDIT);
        }

        $this->updateChart($result);
    }
    private function getShuffleExtendedColors(): array
    {
        $extendedColors = $this->getExtendendColors();
        shuffle($extendedColors);
        return $extendedColors;
    }

    /**
     * @throws ilFormException
     * @throws ilCtrlException
     */
    public function edit(): void
    {
        global $DIC;

        $renderer = $DIC->ui()->renderer();
        $this->setTabs(self::LANG_CHART, true, true);
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

        $this->setTabs(self::TAB_STYLE, true, true);
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

        $this->setTabs(self::DATASETS, true, true);
        $form = $this->initFormDatasetsEdit();
        $this->tpl->setContent($renderer->render($form));
    }

    /**
     * @return void
     * @throws ilCtrlException
     */
    public function editCategoriesDatasetNames()
    {
        $prop = $this->getProperties();
        $styleTabIsVisible = false;
        if ($this->checkIfCategoriesDatasetNamesExist($prop)) {
            $styleTabIsVisible = true;
        }
        $this->setTabs(self::DATASETS, $styleTabIsVisible, true);
        $form = $this->initFormCategoriesDatasetNames(self::CMD_UPDATE);
        $this->tpl->setContent($form->getHTML());
    }


    /**
     * @throws ilCtrlException
     */
    private function updateChart($data, bool $idUpdate = false): void
    {
        $properties = [];
        $properties[self::CHART_TITLE] = $data["chart"][self::CHART_TITLE];
        $properties[self::CHART_TYPE] = $data["chart"][self::CHART_TYPE];
        $properties[self::CHART_MAX_VALUE] = $data["chart"][self::CHART_MAX_VALUE];


        $dataFormat = $data["chart"][self::DATA_FORMAT];
        $format = $dataFormat[0];
        if ($dataFormat[0] === "1") {
            $properties[self::CURRENCY_SYMBOL] = $dataFormat[1]["symbol"];
        }
        $properties[self::DATA_FORMAT] = $format;

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
            $this->tpl->setOnScreenMessage("success", $this->dic->language()->txt(self::MESSAGE_SUCCESS), true);
            $this->returnToParent();
        }
        $this->tpl->setOnScreenMessage("failure", $this->dic->language()->txt(self::MESSAGE_FAILURE), true);
        $this->returnToParent();
    }

    /**
     * @throws ilCtrlException
     * @throws ilFormException
     */
    private function update(): void
    {
        $request = $this->dic->http()->request();
        $form = $this->initFormChart(true);
        $form = $form->withRequest($request);
        $result = $form->getData();

        if ($request->getMethod() == "POST") {
            // TODO Test it
            if (!$this->validate($result, self::FORM_CHART)) {
                $this->tpl->setOnScreenMessage("failure", $this->dic->language()->txt(self::MESSAGE_FAILURE));
                $this->dic->ctrl()->redirectByClass(self::PLUGIN_CLASS_NAME, self::CMD_EDIT);
            }

            $this->updateChart($result, true);
        }
        $this->tpl->setOnScreenMessage("failure", $this->dic->language()->txt(self::MESSAGE_FAILURE));
        $this->dic->ctrl()->redirect($this, self::CMD_EDIT);
    }

    /**
     * @return void
     * @throws ilCtrlException
     */
    public function updateCategoriesDatasetNames(): void
    {
        $form = $this->initFormCategoriesDatasetNames("update");

        $properties = $this->getProperties();

        // TODO
        if (!$form->checkInput()) {
            $this->tpl->setOnScreenMessage("failure", $this->dic->language()->txt(self::MESSAGE_FAILURE));
            $this->setTabs(self::LANG_CATEGORIES_DATASETNAMES, true, true);
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHtml());
            return;
        }

        $datasetValues = [];
        foreach ($form->getInput(self::CATEGORIES) as $key => $value) {
            foreach ($form->getInput(self::DATASETS) as $k => $val) {

                if(array_key_exists("value_dataset_" . ($k + 1) . "_category_" . ($key + 1), $properties)) {
                    $datasetValues["value_dataset_" . ($k + 1) . "_category_" . ($key + 1)] = $properties["value_dataset_" . ($k + 1) . "_category_" . ($key + 1)];
                } else {
                    $datasetValues["value_dataset_" . ($k + 1) . "_category_" . ($key + 1)] = "0";
                }
            }
        }

        if (count($form->getInput(self::CATEGORIES)) !== count(array_unique($form->getInput(self::CATEGORIES)))) {
            $this->tpl->setOnScreenMessage("failure", $this->plugin->txt("category_names_unique"));
            $this->setTabs(self::LANG_CHART, true);
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHtml());
            return;
        }

        if (count($form->getInput(self::DATASETS)) !== count(array_unique($form->getInput(self::DATASETS)))) {
            $this->tpl->setOnScreenMessage("failure", $this->plugin->txt("datasets_names_unique"));
            $this->setTabs(self::LANG_CHART, true);
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHtml());
            return;
        }




        $shuffleExtendedColors = $this->getShuffleExtendedColors();
        // Set default colors for categories
        $j = 0; // Key in $extendedColors array
        for ($i = 0; $i < count($form->getInput(self::CATEGORIES)); $i++) {
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
        for ($i = 0; $i < count($form->getInput(self::DATASETS)); $i++) {
            $color = $shuffleExtendedColors[$j];

            if ($j === count($shuffleExtendedColors) - 1) {
                $j = 0;
            } else {
                $j += 1;
            }
            $properties["color_dataset_".($i + 1)] = $color;
        }

        $properties = array_merge($properties, $datasetValues);

        foreach ($form->getInput(self::CATEGORIES) as $key => $value) {
            $properties["title_category_".($key + 1)] = $value;
        }

        $datasets = $form->getInput(self::DATASETS);
        foreach ($datasets as $key => $value) {
            $properties["title_dataset_".($key + 1)] = $value;
        }

        if ($this->updateElement($properties)) {
            $this->tpl->setOnScreenMessage("success", $this->dic->language()->txt(self::MESSAGE_SUCCESS), true);
            $this->dic->ctrl()->redirectByClass(self::PLUGIN_CLASS_NAME, self::CMD_EDIT_CATEGORIES_DATASET_NAMES);
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
                $this->tpl->setOnScreenMessage("success", $this->dic->language()->txt(self::MESSAGE_SUCCESS), true);
                $this->dic->ctrl()->redirect($this, self::CMD_EDIT_STYLE);
            }
        }
        $this->tpl->setOnScreenMessage("failure", $this->dic->language()->txt(self::MESSAGE_FAILURE));
        $this->dic->ctrl()->redirect($this, self::CMD_EDIT_STYLE);
    }

    public function updateDatasets()
    {
        $request = $this->dic->http()->request();

        $form = $this->initFormDatasetsEdit();
        $form = $form->withRequest($request);
        $result = $form->getData();

        if ($request->getMethod() == "POST") {
            // TODO Test it
            if (!$this->validate($result, self::DATASETS)) {
                $this->tpl->setOnScreenMessage("failure", $this->dic->language()->txt(self::MESSAGE_FAILURE));
                $this->returnToParent();
            }

            $properties = $this->getProperties();
            $countDatasets = $this->getCountPropertiesByType($properties, "title_dataset");
            $countCategories = $this->getCountPropertiesByType($properties, "title_category");

            for ($i = 0; $i < $countCategories; $i++) {
                if (empty($result["group_category_" . ($i + 1)][1])) {
                    for ($j = 0; $j < $countDatasets; $j++) {
                        $value = trim($result["hidden_dataset_" . ($j + 1) . "_category_" . ($i + 1)]);

                        // TODO Fix display screen message
                        if (!is_numeric($value) || str_starts_with($value, "0")) {
                            $this->tpl->setOnScreenMessage(
                                "failure", $this->dic->language()->txt(self::MESSAGE_FAILURE)
                            );
                            $this->dic->ctrl()->redirect($this, self::CMD_EDIT_DATASETS);
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

                        // TODO Fix display screen message
                        if (!is_numeric($value) || str_starts_with($value, "0")) {
                            $this->tpl->setOnScreenMessage(
                                "failure", $this->dic->language()->txt(self::MESSAGE_FAILURE)
                            );
                            $this->dic->ctrl()->redirect($this, self::CMD_EDIT_DATASETS);
                        }

                        if ($value === "") {
                            $value = 0;
                        }
                        $properties["value_dataset_" . ($j + 1) . "_category_" . ($i + 1)] = $value;
                    }
                }
            }

            if ($this->updateElement($properties)) {
                $this->tpl->setOnScreenMessage("success", $this->dic->language()->txt(self::MESSAGE_SUCCESS), true);
                $this->dic->ctrl()->redirect($this, self::CMD_EDIT_DATASETS);
            }
        }
        $this->tpl->setOnScreenMessage("failure", $this->dic->language()->txt(self::MESSAGE_SUCCESS), true);
        $this->dic->ctrl()->redirect($this, self::CMD_EDIT_DATASETS);

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

    private function validate($data, string $form): bool
    {
        if ($form === self::FORM_CHART) {
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
        } elseif ($form === self::FORM_CATEGORIES_DATASETS) {

            dd($data);


        } elseif ($form === self::FORM_DATASETS) {
            foreach($data as $key => $value) {
                if (str_starts_with($key, "hidden_dataset_")) {
                    if(!is_numeric($value) || $value === "") {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * @return array|string[]
     */
    private function getExtendendColors(): array
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
    public function initFormChart(bool $isUpdate = false)
    {
        global $DIC;

        $ui = $DIC->ui()->factory();
        $prop = $this->getProperties();

        $inputFields[self::CHART_TITLE] = $ui->input()->field()->text(
            $this->getPlugin()->txt(self::CHART_TITLE),
            ""
        )->withValue($prop[self::CHART_TITLE] ?? "")->withRequired(true);

        $optionsChart = [
            "1" => $this->getPlugin()->txt(self::LANG_CHART_HORIZONTAL_BAR),
            "2" => $this->getPlugin()->txt(self::LANG_CHART_VERTICAL_BAR),
            "3" => $this->getPlugin()->txt(self::LANG_CHART_PIE_CHART),
            "4" => $this->getPlugin()->txt(self::LANG_CHART_LINE_CHART)
        ];

        $inputFields[self::CHART_TYPE] = $ui->input()->field()->select(
            $this->getPlugin()->txt(self::CHART_TYPE),
            $optionsChart
        )->withValue($prop[self::CHART_TYPE] ?? "")->withRequired(true);

        $inputFields[self::CHART_MAX_VALUE] = $ui->input()->field()->text(
            $this->getPlugin()->txt(self::CHART_MAX_VALUE),
            ""
        )->withValue($prop[self::CHART_MAX_VALUE] ?? "")->withRequired(true);

        $group1 = $ui->input()->field()->group(
            [
                "symbol" => $ui->input()->field()->text($this->getPlugin()->txt(self::SYMBOL), $this->getPlugin()->txt("add_currency_symbol"))
                                                 ->withValue($prop[self::CURRENCY_SYMBOL] ?? "")
            ],
            $this->getPlugin()->txt("number")
        );

        $group2 = $ui->input()->field()->group(
            [],
            $this->getPlugin()->txt("percent")
        );


        $inputFields[self::DATA_FORMAT] = $ui->input()->field()->switchableGroup(
            [
                "1" => $group1,
                "2" => $group2
            ],
            $this->getPlugin()->txt("format")
        )->withValue($prop[self::DATA_FORMAT] ?? "1");

        $sectionChart = $ui->input()->field()->section(
            $inputFields,
            $this->getPlugin()->txt(self::CMD_EDIT),
            $this->getPlugin()->txt(self::LANG_DESCRIPTION),
        );

        $fallbackCmd = self::CMD_CREATE;
        if ($isUpdate) {
            $fallbackCmd = self::CMD_UPDATE;
        }

        $form = $ui->input()->container()->form()->standard(
            $this->dic->ctrl()->getFormAction($this, $fallbackCmd),
            [
                "chart" => $sectionChart
            ],
        );

        return $form;
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
            $this->getPlugin()->txt(self::CATEGORIES),
            $this->getPlugin()->txt("description_style_categories"),
        );

        $sectionDatasets = $ui->input()->field()->section(
            $inputFieldsDatasetsColors,
            $this->getPlugin()->txt(self::DATASETS),
            $this->getPlugin()->txt("description_style_datasets"),
        );

        $formAction = $DIC->ctrl()->getFormActionByClass(
            self::class,
            self::CMD_UPDATE_STYLE
        );

        $form = $ui->input()->container()->form()->standard(
            $formAction,
            [
                "categories" => $sectionCategories,
                "datasets" => $sectionDatasets
            ],
        );

        return $form;
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

        $form = $ui->input()->container()->form()->standard(
            $this->dic->ctrl()->getFormAction($this, self::CMD_UPDATE_DATASETS),
            $inputs
        );

        return $form;
    }

    /**
     * @throws ilFormException
     * @throws ilCtrlException
     */
    public function initFormCategoriesDatasetNames(string $action)
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->getPlugin()->txt(self::CMD_EDIT));
        $form->setDescription($this->getPlugin()->txt(self::LANG_DESCRIPTION));
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

        $category = new ilTextInputGUI($this->lng->txt("title"), self::CATEGORIES);
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

        $dataset = new ilTextInputGUI($this->getPlugin()->txt(self::DATASETS), self::DATASETS);
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

        if ($action === self::CMD_INSERT) {
            $form->addCommandButton(self::CMD_CREATE_CATEGORIES_DATASET_NAMES, $this->dic->language()->txt(self::CMD_SAVE));
        } else {
            $form->addCommandButton(self::CMD_UPDATE_CATEGORIES_DATASET_NAMES, $this->dic->language()->txt(self::CMD_SAVE));
        }
        $form->addCommandButton(self::CMD_CANCEL, $this->dic->language()->txt(self::CMD_CANCEL));
        $form->setFormAction($this->dic->ctrl()->getFormAction($this));

        return $form;
    }


    public function cancel(): void
    {
        $this->returnToParent();
    }


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
        if ($a_properties[self::DATA_FORMAT] === "2") {
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
    private function checkIfCategoriesDatasetNamesExist(array $a_properties): string
    {
        $exist = false;
        foreach ($a_properties as $key => $value) {
            if (strpos($key, "title_category") > -1 || strpos($key, "title_dataset") > -1) { // TODO replace it with str_replace
                $exist = true;
                break;
            }
        }
        return $exist;
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
        $divcanid = self::DIV_CANVAS_ID_PREFIX . self::$id_counter;
        $divid = self::DIV_ID_PREFIX . self::$id_counter;
        $id = self::CANVAS_ID_PREFIX . self::$id_counter;

        $template = $DIC->ui()->mainTemplate();
        $template->addCss(self::PLUGIN_DIRECTORY . "/css/chart.css");
        $template->addJavaScript(self::PLUGIN_DIRECTORY . "/js/Chart.min.js");
        $template->addJavaScript(self::PLUGIN_DIRECTORY . "/js/chartjs-plugin-datalabels.min.js");
        $template->addJavaScript(self::PLUGIN_DIRECTORY . "/js/script.js");

        $properties = $a_properties;
        $categoriesDatasetNamesExist = $this->checkIfCategoriesDatasetNamesExist($properties);
        $tplName = "tpl.content.html";
        if (!$categoriesDatasetNamesExist) {
            $tplName = "tpl.content_data_not_exist.html";
        }

        $tpl = new ilTemplate(
            $tplName,
            true,
            true,
            "public/" . self::PLUGIN_DIRECTORY,
            ilGlobalTemplateInterface::DEFAULT_BLOCK,
            true
        );
        $tpl->setVariable("DIV", $divid);

        if ($categoriesDatasetNamesExist) {
            $tpl->setVariable("DIV_CANVAS_ID", $divcanid);
            $tpl->setVariable("CHART_ID", $id);
            $tpl->setVariable("CHART_TITLE", $properties[self::CHART_TITLE]);
            $tpl->setVariable("CHART_TYPE", $this->getChartType($properties[self::CHART_TYPE]));
            $tpl->setVariable("CHART_MAX_VALUE", $properties[self::CHART_MAX_VALUE]);
            $tpl->setVariable("CHART_DATA_FORMAT", $properties[self::DATA_FORMAT]);

            if (!empty($properties[self::CURRENCY_SYMBOL])) {
                $tpl->setVariable("CHART_CURR_SYMBOL", $properties[self::CURRENCY_SYMBOL]);
            }

            $tpl->setVariable("TITLE_CATEGORIES", $this->titleCategoryInputFields($properties));
            $tpl->setVariable("TITLE_DATASETS", $this->titleDatasetInputFields($properties));
            $tpl->setVariable("VALUE_DATASETS", $this->valueDatasetInputFields($properties));
            $tpl->setVariable("COLOR_CATEGORY", $this->colorCategoryInputField($properties));
            $tpl->setVariable("COLOR_DATASET", $this->colorDatasetInputField($properties));
            $tpl->setVariable("PERC", $this->percentDataFormat($properties));
        }

        $tpl->parseCurrentBlock();

        return $tpl->get();
    }

    /**
     * @throws ilCtrlException
     */
    private function setTabs(string $a_active, bool $tabStyleVisible, bool $tabCategoriesDatasetnamesIsVisible = false): void
    {
        $pl = $this->getPlugin();
        $this->dic->tabs()->addTab(
            self::LANG_CHART,
            $pl->txt(self::LANG_CHART),
            $this->dic->ctrl()->getLinkTarget($this, self::CMD_EDIT)
        );

        if ($tabCategoriesDatasetnamesIsVisible) {
            $this->dic->tabs()->addTab(
                "categories-datasets",
                $pl->txt(self::LANG_CATEGORIES_DATASETNAMES),
                $this->dic->ctrl()->getLinkTarget($this, self::CMD_EDIT_CATEGORIES_DATASET_NAMES)
            );
        }


        if ($tabStyleVisible) {
            $this->dic->tabs()->addTab(
                self::DATASETS,
                $pl->txt(self::LANG_CHART_DATASETS),
                $this->dic->ctrl()->getLinkTarget($this, self::CMD_EDIT_DATASETS)
            );
            $this->dic->tabs()->addTab(
                self::TAB_STYLE,
                $pl->txt(self::LANG_CHART_STYLE),
                $this->dic->ctrl()->getLinkTarget($this, self::CMD_EDIT_STYLE)
            );
        }
        if ($a_active === "chart") {
            $this->dic->tabs()->activateTab(self::LANG_CHART);
        } elseif ($a_active === "style") {
            $this->dic->tabs()->activateTab(self::TAB_STYLE);
        } elseif ($a_active === self::DATASETS) {
            $this->dic->tabs()->activateTab(self::DATASETS);
        }
    }

    /**
     * @param array $a_properties
     * @return array
     */
    private function getTranformedProperties(array $a_properties): array
    {
        $tranformedProperties = [];
        $unchangeableKeys = [self::CHART_TITLE, self::CHART_TYPE, self::DATA_FORMAT, self::CURRENCY_SYMBOL];
        foreach($a_properties as $key => $value) {
            if (in_array($key, $unchangeableKeys)) {
                $tranformedProperties[$key] = $value;
            } elseif (strpos($key, "key") > -1) {
                $indexCategory = substr($key, 3);
                $tranformedProperties["title_category_" . $indexCategory] = $value;
                $tranformedProperties["value_dataset_1_category_" . $indexCategory] = $a_properties["value" . $indexCategory];
            } elseif (strpos($key, "color") > -1) {
                $indexCategory = substr($key, 5);
                $tranformedProperties["color_category_" . $indexCategory] = $a_properties["color" . $indexCategory];
            }
        }

        $tranformedProperties["title_dataset_1"] = "Dataset";
        $tranformedProperties["color_dataset_1"] = $tranformedProperties["color_category_1"];
        $tranformedProperties[self::CHART_MAX_VALUE] = "";

        unset($a_properties["color1"]);
        unset($a_properties["color2"]);
        unset($a_properties["key1"]);
        unset($a_properties["key2"]);
        unset($a_properties["value1"]);
        unset($a_properties["value2"]);

        return $tranformedProperties;
    }

    private function checkIfChartFromLastVersion(array $properties): bool
    {
        foreach($properties as $key => $value) {
            if(strpos($key, "key") > -1) {
                return true;
            }
        }
        return false;
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
