<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see LICENSE */

/**
 * Class ilChartPluginGUI
 *
 * @author KPG <dev@kroepelin-projekte.de>
 * @ilCtrl_isCalledBy ilChartPluginGUI: ilPCPluggedGUI
 */
class ilChartPluginGUI extends ilPageComponentPluginGUI
{
    const PLUGIN_CLASS_NAME = self::class;
    const CMD_CANCEL = "cancel";
    const CMD_CREATE = "create";
    const CMD_SAVE = "save";
    const CMD_INSERT = "insert";
    const CMD_UPDATE = "update";
    const CMD_EDIT = "edit";
    const CMD_EDIT_STYLE = "editStyle";
    const CMD_EDIT_DATASETS = "editDatasets";
    const CMD_UPDATE_STYLE = "updateStyle";
    const CMD_UPDATE_DATASETS = "updateDatasets";
    const TAB_CHART = "chart";
    const TAB_STYLE = "style";
    const TAB_STYLE_DATASETS = "style-datasets";
    const TAB_DATASETS = "datasets";
    const LANG_DESCRIPTION = "description";
    const LANG_DESCRIPTION_DATASETS = "description_datasets";
    const LANG_DESCRIPTION_STYLE = "description_style";
    const LANG_CHART = "chart";
    const LANG_CHART_TITLE = "chart_title";
    const LANG_CHART_TYPE = "chart_type";
    const LANG_CHART_STYLE = "chart_style";
    const LANG_CHART_DATASETS = "chart_datasets";
    const LANG_CHART_HORIZONTAL_BAR = "horizontal_bar_chart";
    const LANG_CHART_VERTICAL_BAR = "vertical_bar_chart";
    const LANG_CHART_PIE_CHART = "pie_chart";
    const LANG_CHART_LINE_CHART = 'line_chart';
    const LANG_OBJ_MODIFIED = "msg_obj_modified";
    const ACTION_INSERT = "insert";
    const ACTION_EDIT = "edit";
    const CANVAS_ID_PREFIX = "chart_page_component_";
    const DIV_CANVAS_ID_PREFIX = "div_canvas_";
    const DIV_ID_PREFIX = "chart_div_";
    const MAX_VALUE_CHART = "max_value_chart";

    /**
     * @var ilChartPlugin
     */
    protected $pl;

    /**
     * @var int
     */
    protected static $id_counter = 0;

    /**
     * Constructor ilChartPluginGUI
     */
    public function __construct()
    {
        parent::__construct();
        $this->pl = new ilChartPlugin();
    }

    /**
     * Execute command
     *
     * @param
     * @return
     */
    public function executeCommand()
    {
        global $DIC;

        $next_class = $DIC->ctrl()->getNextClass();

        switch ($next_class) {
            default:
                // Perform valid commands
                $cmd = $DIC->ctrl()->getCmd();
                if (in_array($cmd, array(self::CMD_CREATE, self::CMD_SAVE, self::CMD_EDIT, self::CMD_EDIT_STYLE, self::CMD_EDIT_DATASETS, self::CMD_UPDATE, self::CMD_UPDATE_STYLE, self::CMD_UPDATE_DATASETS, self::CMD_CANCEL))) {
                    $this->$cmd();
                }
                break;
        }
    }

    /**
     * Form for new elements
     */
    public function insert()
    {
        global $tpl;

        $this->setTabs(self::TAB_CHART, false);
        $form = $this->initFormChart(self::ACTION_INSERT);
        $tpl->setContent($form->getHTML());
    }

    /**
     * Save element
     */
    public function create()
    {
        global $DIC, $tpl;

        $form = $this->initFormChart(self::ACTION_INSERT);

        if (!$form->checkInput() || !$this->validate($form)) {
            ilUtil::sendFailure($DIC->language()->txt("form_input_not_valid"), true);
            $form->setValuesByPost();
            $tpl->setContent($form->getHtml());

        } else {

            $properties = [
                "chart_title" => $form->getInput("chart_title"),
                "chart_type" => $form->getInput("chart_type"),
                "chart_max_value" => $form->getInput("chart_max_value"),
                "data_format" => $form->getInput("data_format"),
                "currency_symbol" => $form->getInput("currency_symbol"),
            ];

            foreach ($form->getInput("categories") as $key => $value) {
                $properties["title_category_" . ($key + 1)] = $value;
            }

            foreach ($form->getInput("datasets") as $key => $value) {
                $properties["title_dataset_" . ($key + 1)] = $value;
            }

            foreach ($form->getInput("categories") as $key => $value) {
                foreach ($form->getInput("datasets") as $k => $val) {
                    if(!array_key_exists("value_dataset_" . ($k + 1) . "_category_" . ($key + 1), $properties)) {
                        $properties["value_dataset_" . ($k + 1) . "_category_" . ($key + 1)] = "0";
                    }
                }
            }

            $shuffleExtendedColors = $this->getShuffleExtendedColors();

            // Set default colors for categories
            $j = 0; // Key in $extendedColors array
            for ($i = 0; $i < count($form->getInput("categories")); $i ++) {
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
            for ($i = 0; $i < count($form->getInput("datasets")); $i ++) {
                $color = $shuffleExtendedColors[$j];

                if ($j === count($shuffleExtendedColors) - 1) {
                    $j = 0;
                } else {
                    $j += 1;
                }
                $properties["color_dataset_".($i + 1)] = $color;
            }

            if ($this->createElement($properties)) {
                ilUtil::sendSuccess($DIC->language()->txt(self::LANG_OBJ_MODIFIED), true);
                $this->returnToParent();
            }
        }
    }

    private function getShuffleExtendedColors()
    {
        $extendedColors = $this->getExtendendColors();
        shuffle($extendedColors);
        return $extendedColors;
    }

    /**
     * Edit
     *
     * @param
     * @return
     */
    public function edit()
    {
        global $tpl;

        $this->setTabs(self::TAB_CHART, true);
        $form = $this->initFormChart(self::ACTION_EDIT);
        $tpl->setContent($form->getHTML());
    }

    /**
     * Edit Style
     */
    public function editStyle()
    {
        global $tpl;

        $this->setTabs(self::TAB_STYLE, true);
        $form = $this->initFormStyleEdit();
        $tpl->setContent($form->getHTML());
    }

    /**
     * Edit Datasets
     */
    public function editDatasets()
    {
        global $tpl;

        $this->setTabs(self::TAB_DATASETS, true);
        $form = $this->initFormDatasetsEdit();
        $tpl->setContent($form->getHTML());
    }

    /**
     * Update Chart Form
     *
     * @throws ilCtrlException
     */
    private function update()
    {
        global $DIC;

        $form = $this->initFormChart(self::ACTION_EDIT);

        if (!$form->checkInput() || !$this->validate($form)) {

            ilUtil::sendFailure($DIC->language()->txt("form_input_not_valid"), true);
            $DIC->ctrl()->redirectByClass(self::PLUGIN_CLASS_NAME, self::CMD_EDIT);
        } else {

            $properties = $this->getProperties();

            if($this->checkIfChartFromLastVersion($properties)) {
                $properties = $this->getTranformedProperties($properties);
            }

            $datasetValues = [];

            foreach ($form->getInput("categories") as $key => $value) {
                foreach ($form->getInput("datasets") as $k => $val) {

                    if(array_key_exists("value_dataset_" . ($k + 1) . "_category_" . ($key + 1), $properties)) {
                        $datasetValues["value_dataset_" . ($k + 1) . "_category_" . ($key + 1)] = $properties["value_dataset_" . ($k + 1) . "_category_" . ($key + 1)];
                    }else{
                        $datasetValues["value_dataset_" . ($k + 1) . "_category_" . ($key + 1)] = "0";
                    }
                }
            }

            $countColorsCategories = count($form->getInput("categories"));
            $propertiesCategoriesColorsTmp = [];

            for ($i = 1; $i <= $countColorsCategories; $i++) {

                if(array_key_exists("color_category_" . $i, $properties)) {
                    $propertiesCategoriesColorsTmp["color_category_" . $i] = $properties["color_category_".$i];
                }else{

                    $extendedColors = $this->getExtendendColors();
                    $color = $extendedColors[rand(0, count($extendedColors)-1)];
                    $propertiesCategoriesColorsTmp["color_category_".$i] = $color;
                }
            }

            $countColorsDatasets = count($form->getInput("datasets"));
            $propertiesDatasetsColorsTmp = [];

            for ($i = 1; $i <= $countColorsDatasets; $i++) {
                $propertiesDatasetsColorsTmp["color_dataset_".$i] = $properties["color_dataset_".$i];

                if(array_key_exists("color_dataset_".$i, $properties)) {
                    $datasetValues["color_dataset_".$i] = $properties["color_dataset_".$i];
                }else{

                    $extendedColors = $this->getExtendendColors();
                    $color = $extendedColors[rand(0, count($extendedColors)-1)];

                    $datasetValues["color_dataset_".$i] = $color;
                }
            }

            $properties = [];
            $properties["chart_title"] = $form->getInput("chart_title");
            $properties["chart_type"] = $form->getInput("chart_type");
            $properties["chart_max_value"] = $form->getInput("chart_max_value");
            $properties["data_format"] = $form->getInput("data_format");
            $properties["currency_symbol"] = $form->getInput("currency_symbol");
            $properties = array_merge($properties, $propertiesCategoriesColorsTmp);
            $properties = array_merge($properties, $propertiesDatasetsColorsTmp);
            $properties = array_merge($properties, $datasetValues);

            foreach ($form->getInput("categories") as $key => $value) {
                $properties["title_category_".($key+1)] = $value;
            }

            $datasets = $form->getInput("datasets");
            foreach ($datasets as $key => $value) {
                $properties["title_dataset_".($key+1)] = $value;
            }

            if ($this->updateElement($properties)) {
                ilUtil::sendSuccess($DIC->language()->txt(self::LANG_OBJ_MODIFIED), true);
                $DIC->ctrl()->redirectByClass(self::PLUGIN_CLASS_NAME, self::CMD_EDIT);
            }
        }
    }

    /**
     * Update Style Form
     */
    private function updateStyle()
    {
        global $DIC;

        $form = $this->initFormStyleEdit();
        if ($form->checkInput()) {

            $properties = $this->getProperties();

            if($this->checkIfChartFromLastVersion($properties)) {
                $properties = $this->getTranformedProperties($properties);
            }
            $countColorsCategories = $form->getInput("count_colors_categories");
            $countColorsDatasets = $form->getInput("count_colors_datasets");

            for ($i = 0; $i < $countColorsCategories; $i++) {
                $properties["color_category_".($i+1)] = $form->getInput("color_category_".($i+1));
            }

            for ($i = 0; $i < $countColorsDatasets; $i++) {
                $properties["color_dataset_".($i+1)] = $form->getInput("color_dataset_".($i+1));
            }

            if ($this->updateElement($properties)) {
                ilUtil::sendSuccess($DIC->language()->txt(self::LANG_OBJ_MODIFIED), true);
                $DIC->ctrl()->redirect($this, self::CMD_EDIT_STYLE);
            }
        }
    }

    private function updateDatasets()
    {
        global $DIC;

        $form = $this->initFormDatasetsEdit();

        if ($form->checkInput()) {

            $properties = $this->getProperties();

            if($this->checkIfChartFromLastVersion($properties)) {
                $properties = $this->getTranformedProperties($properties);
            }
            $countDatasets = $this->getCountPropertiesByType($properties, "title_dataset");
            $countCategory = $this->getCountPropertiesByType($properties, "title_category");

            for ($i = 0; $i < $countCategory; $i++) {
                for ($j = 0; $j < $countDatasets; $j++) {

                    if($form->getInput("dataset_" . ($j+1). "_category_".($i+1)) === '' || !is_numeric($form->getInput("dataset_" . ($j+1). "_category_".($i+1)))){
                        ilUtil::sendFailure($DIC->language()->txt("form_input_not_valid"), true);
                        $DIC->ctrl()->redirect($this, self::CMD_EDIT_DATASETS);
                    }
                    $properties["value_dataset_" . ($j+1). "_category_".($i+1)] = $form->getInput("dataset_" . ($j+1). "_category_".($i+1));
                }
            }

            if ($this->updateElement($properties)) {
                ilUtil::sendSuccess($DIC->language()->txt(self::LANG_OBJ_MODIFIED), true);
                $DIC->ctrl()->redirect($this, self::CMD_EDIT_DATASETS);
            }
        }
    }

    /**
     * Get count properties by type
     *
     * @param array $properties
     * @param string $searchString
     * @return int
     */
    private function getCountPropertiesByType(array $properties, string $searchString): int
    {
        $count = 0;
        foreach($properties as $key => $value){

            if (strpos($key, $searchString) > -1) {
                $count += 1;
            }
        }
        return $count;
    }

    /**
     * Validate input values in configuration of page component
     *
     * @param $form
     * @return bool
     */
    private function validate($form): bool
    {
        if($form->getInput("chart_type") === ""){
            return false;
        }

        if(!is_numeric($form->getInput("chart_max_value")) && $form->getInput("chart_max_value") !== ''){
            return false;
        }

        $categories = $form->getInput("categories");
        foreach($categories as $key => $value){

            if($value === ""){
                return false;
            }
        }

        $datasets = $form->getInput("datasets");
        foreach($datasets as $key => $value){

            if($value === ""){
                return false;
            }
        }
        return true;
    }

    /**
     * Get extended colors from active content style
     *If the default content style active ist, then will be used the extended colors from Delos Skin
     *
     * @return array
     */
    private function getExtendendColors(): array
    {
        $parentType = $this->getPlugin()->getParentType();
        $parentId = $this->getPlugin()->getParentId();

        if ($parentType === "copa") {  // Case: parent is content page
            $parentRefId = $_GET["ref_id"];
            $objStylesheet = new ilObjContentPage($parentRefId);
            $styleId = $objStylesheet->getStyleSheetId();
        } else {
            $objStylesheet = new ilObjStyleSheet();
            $styleId = $objStylesheet->lookupObjectStyle($parentId);
        }

        $extendedColorsCode = [];
        if ($styleId === 0) {
            $extendedColorsCode = $this->getExtendedColorsDefaultILIAS();
        } else {
            $objStyle = new ilObjStyleSheet($styleId);
            $colors = $objStyle->getColors();

            foreach ($colors as $key => $color) {
                if (strpos($color["name"], "extendedcolor") > -1) {
                    $extendedColorsCode[] = $color["code"];
                }
            }
        }
        return $extendedColorsCode;
    }

    /**
     * Get extended colors from Delos Skin
     *
     * @return array
     */
    private function getExtendedColorsDefaultILIAS(): array
    {
        return [
            'f3de2c',
            'cddc39',
            '59a0a5',
            '86cb92',
            'ce73a8',
            '82639e',
            '9e7c7d',
            'f75e82',
            'ea4d54',
        ];
    }

    /**
     * Chart form
     *
     * @param $action
     * @return ilPropertyFormGUI
     */
    public function initFormChart($action)
    {
        global $DIC;

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");

        $form = new ilPropertyFormGUI();
        // Add Title
        $form->setTitle($this->getPlugin()->txt("edit"));
        // Add Description
        $form->setDescription($this->getPlugin()->txt(self::LANG_DESCRIPTION));
        // Get Properties
        $prop = $this->getProperties();

        if($this->checkIfChartFromLastVersion($prop)) {
            $prop = $this->getTranformedProperties($this->getProperties());
        }
        $titleChart = new ilTextInputGUI($this->getPlugin()->txt(self::LANG_CHART_TITLE), "chart_title");
        $titleChart->setRequired(false);
        $titleChart->setValue($prop["chart_title"]);
        $form->addItem($titleChart);

        $selectChartType = new ilSelectInputGUI($this->getPlugin()->txt(self::LANG_CHART_TYPE), "chart_type");
        $selectChartType->setRequired(true);
        $optionsChart = [
            "1" => $this->getPlugin()->txt(self::LANG_CHART_HORIZONTAL_BAR),
            "2" => $this->getPlugin()->txt(self::LANG_CHART_VERTICAL_BAR),
            "3" => $this->getPlugin()->txt(self::LANG_CHART_PIE_CHART),
            "4" => $this->getPlugin()->txt(self::LANG_CHART_LINE_CHART)
        ];
        $selectChartType->setOptions($optionsChart);
        $selectChartType->setValue($prop["chart_type"]);
        $form->addItem($selectChartType);

        $maxValueChart = new ilTextInputGUI($this->getPlugin()->txt(self::MAX_VALUE_CHART), "chart_max_value");
        $maxValueChart->setRequired(false);
        $maxValueChart->setValue($prop["chart_max_value"]);
        $form->addItem($maxValueChart);

        $radioGroup = new ilRadioGroupInputGUI("Format", "data_format");
        $radioGroup->setValue($prop["data_format"]);
        $radioGroup->setRequired(true);

        // Radio button for data format number with suditem for currency symbol
        $radioNumber = new ilRadioOption($this->getPlugin()->txt("number"), "1");
        $currencySymbol = new ilTextInputGUI("Symbol", "currency_symbol");
        $currencySymbol->setInfo($this->getPlugin()->txt('add_currency_symbol'));
        $currencySymbol->setValue($prop["currency_symbol"]);
        $radioNumber->addSubItem($currencySymbol);
        $radioGroup->addOption($radioNumber);

        $radioPercent = new ilRadioOption($this->getPlugin()->txt("percent"), "2");
        $radioGroup->addOption($radioPercent);
        $form->addItem($radioGroup);

        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->getPlugin()->txt('categories_names'));
        $header->setInfo($this->getPlugin()->txt("categories_info"));
        $form->addItem($header);

        $countCategory = $this->getCountPropertiesByType($prop, 'title_category');

        $categoriesTitle = [];
        for($i = 0; $i < $countCategory; $i++){
            $categoriesTitle[] = $prop["title_category_".($i + 1)];
        }

        $category = new ilTextInputGUI($this->lng->txt("title"), "categories");
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
        $header->setTitle($this->getPlugin()->txt('datasets_names'));
        $header->setInfo($this->getPlugin()->txt("datasets_info"));
        $form->addItem($header);

        $countDataset = $this->getCountPropertiesByType($prop, 'title_dataset');
        $datasetsTitle = [];
        for($i = 0; $i < $countDataset; $i++){
            $datasetsTitle[] = $prop["title_dataset_".($i + 1)];
        }

        $dataset = new ilTextInputGUI($this->getPlugin()->txt("dataset"), "datasets");
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

        if ($action === self::ACTION_INSERT) {
            $form->addCommandButton(self::CMD_CREATE, $DIC->language()->txt(self::CMD_SAVE));
        } else {
            $form->addCommandButton(self::CMD_UPDATE, $DIC->language()->txt(self::CMD_SAVE));
        }
        $form->addCommandButton(self::CMD_CANCEL, $DIC->language()->txt(self::CMD_CANCEL));
        $form->setFormAction($DIC->ctrl()->getFormAction($this));

        return $form;
    }

    /**
     * Style Form
     *
     * @return ilPropertyFormGUI
     * @throws ilCtrlException
     */
    public function initFormStyleEdit()
    {
        global $DIC;

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");

        $form = new ilPropertyFormGUI();
        // Add Title
        $form->setTitle($this->getPlugin()->txt("edit"));
        $form->setDescription($this->getPlugin()->txt("edit_style"));
        // Get Properties
        $prop = $this->getProperties();

        if($this->checkIfChartFromLastVersion($prop)) {
            $prop = $this->getTranformedProperties($this->getProperties());
        }
        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->getPlugin()->txt("categories"));
        $header->setInfo($this->getPlugin()->txt("description_style_categories"));
        $form->addItem($header);

        $countColorsCategory = 0;
        foreach ($prop as $k => $val) {
            if (strpos($k, "title_category") > -1) {
                $i = substr($k, strpos($k, "title_category")+15, strlen($k));
                $colorInputCategory = new ilColorPickerInputGUI($val, "color_category_".$i);

                if (!array_key_exists("color_category_" . $i, $prop)) {
                    $colorInputCategory->setDefaultColor("");
                }

                $colorInputCategory->setValue($prop["color_category_" . $i]);
                $form->addItem($colorInputCategory);
                $countColorsCategory = $countColorsCategory + 1;
            }
        }

        $countColorCategory = new ilHiddenInputGUI("count_colors_categories");
        $countColorCategory->setValue($countColorsCategory);
        $form->addItem($countColorCategory);

        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->getPlugin()->txt("datasets"));
        $header->setInfo($this->getPlugin()->txt("description_style_datasets"));
        $form->addItem($header);

        $countColorsDataset = 0;
        foreach ($prop as $k => $val) {

            if (strpos($k, "title_dataset") > -1) {
                $i = substr($k, strpos($k, "title_dataset")+14, strlen($k));
                $colorInputDataset = new ilColorPickerInputGUI($val, "color_dataset_".$i);

                if (!array_key_exists("color_dataset_" . $i, $prop)) {
                    $colorInputDataset->setDefaultColor("");
                }

                $colorInputDataset->setValue($prop["color_dataset_" . $i]);
                $form->addItem($colorInputDataset);
                $countColorsDataset = $countColorsDataset + 1;
            }
        }

        $countColorDataset = new ilHiddenInputGUI("count_colors_datasets");
        $countColorDataset->setValue($countColorsDataset);
        $form->addItem($countColorDataset);

        $form->addCommandButton(self::CMD_UPDATE_STYLE, $DIC->language()->txt(self::CMD_SAVE));
        $form->addCommandButton(self::CMD_CANCEL, $DIC->language()->txt(self::CMD_CANCEL));
        $form->setFormAction($DIC->ctrl()->getFormAction($this));

        return $form;
    }

    public function initFormDatasetsEdit()
    {
        global $DIC;

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");

        $form = new ilPropertyFormGUI();

        $form->setDescription($this->getPlugin()->txt(self::LANG_DESCRIPTION_DATASETS));

        // Add Title
        $form->setTitle($this->getPlugin()->txt("edit"));
        // Get Properties
        $prop = $this->getProperties();

        if($this->checkIfChartFromLastVersion($prop)) {
            $prop = $this->getTranformedProperties($this->getProperties());
        }

        $countCategories = 0;
        $countDatasets = 0;
        foreach($prop as $key => $value){

            if(strpos($key, "title_category_") > -1){
                $countCategories += 1;
            }

            if(strpos($key, "title_dataset_") > -1){
                $countDatasets += 1;
            }
        }

        $radioGroup = new ilRadioGroupInputGUI("", "dataset_values");
        for($i = 0; $i < $countCategories; $i++){

            $radioNumber = new ilRadioOption($prop["title_category_".($i+1)], "dataset". ($i+1));
            $radioGroup->addOption($radioNumber);
            $radioGroup->setValue("dataset". ($i+1));

            for($j = 0; $j < $countDatasets; $j++) {
                $dataset = new ilTextInputGUI($prop["title_dataset_".($j+1)], "dataset_".($j+1)."_category_".($i+1));
                $dataset->setValue($prop["value_dataset_" .($j+1)."_category_".($i+1)]);
                $radioNumber->addSubItem($dataset);
            }
        }
        $form->addItem($radioGroup);

        $form->addCommandButton(self::CMD_UPDATE_DATASETS, $DIC->language()->txt(self::CMD_SAVE));
        $form->addCommandButton(self::CMD_CANCEL, $DIC->language()->txt(self::CMD_CANCEL));
        $form->setFormAction($DIC->ctrl()->getFormAction($this));

        return $form;
    }

    /**
     * Cancel
     */
    function cancel()
    {
        $this->returnToParent();
    }

    /**
     * Get count of categories
     */
    private function getCountCategories(array $properties): int
    {
        $count = 0;
        foreach($properties as $key => $value){

            if(strpos($key, 'title_category') > -1){
                $count += 1;
            }
        }
        return $count;
    }

    /**
     * Get Chart Type
     */
    private function getChartType(string $chart_type): string
    {
        if ($chart_type == '1') {
            return 'horizontalBar';
        } elseif ($chart_type == '2') {
            return 'bar';
        } elseif ($chart_type == '3') {
            return 'pie';
        } else {
            return 'line';
        }
    }

    /**
     * Get percent Data Format
     *
     * @param array $a_properties
     * @return string
     */
    private function percentDataFormat(array $a_properties): string
    {
        $percent = "";
        $datasets = [];
        $countCategories = $this->getCountCategories($a_properties);

        $datasetsValueCategory = [];

        if ($a_properties["data_format"] === "2") {

            for($i = 0; $i < $countCategories; $i++) {

                foreach ($a_properties as $key => $value) {

                    if (strpos($key, "value_dataset") > -1 && strpos($key, "_category_" . ($i+1)) > -1) {
                        $indexDataset = substr($key, 14, strpos($key, "_category_") - 14);
                    }

                    if (strpos($key, "_category_" . ($i+1)) > -1 && ($key !== "title_category_" . ($i+1)) && ($key !== "color_category_" . ($i+1))) {

                        $value = $a_properties["value_dataset_" . $indexDataset ."_category_" .($i+1)];
                        if (strpos($value, ",") > -1) {
                            $value = str_replace(',', '.', $value);
                        }
                        $datasetsValueCategory["category_" . ($i + 1)]["dataset_". $indexDataset] = $value;
                    }
                }
            }

            foreach($datasetsValueCategory as $key => $value){

                $indexCategory = substr($key, strpos($key, "category_") + 9);
                foreach($value as $k => $val){
                    $indexDataset = substr($k, strpos($k, "dataset_") + 8);
                    $datasets["dataset_" . $indexDataset]["category_" . $indexCategory] = $datasetsValueCategory["category_" . $indexCategory]["dataset_" . $indexDataset];
                }
            }
        }

        $sumDatasetValues = [];
        foreach($datasets as $key => $value){

            $indexDataset = substr($key, strpos($key, "dataset_") + 8);
            $sumDataset = 0;
            foreach($value as $k => $val){

                $indexCategory = substr($k, strpos($k, "category_") + 9);
                if(strpos($datasets["dataset_" . $indexDataset]["category_". $indexCategory], ",") > -1){
                    $datasets["dataset_" . $indexDataset]["category_". $indexCategory] = str_replace(',', '.', $value);
                }
                $tmpVal = (float) $datasets["dataset_" . $indexDataset]["category_". $indexCategory];
                $sumDataset += $tmpVal;
            }
            $sumDatasetValues["sum_dataset_" . $indexDataset] = $sumDataset;
        }

        foreach($datasets as $key => $value){

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
     * Get key Input Fields
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
     * Get title dataset Input Fields
     *
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
     * Get value Input Fields
     *
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
     * Get color Input Fields
     *
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
     * Get Element HTML
     *
     * @param $a_mode
     * @param $a_plugin_version
     * @return mixed
     */
    public function getElementHTML($a_mode, array $a_properties, $a_plugin_version)
    {
        $pl = $this->getPlugin();
        self::$id_counter += 1;
        $divcanid = self::DIV_CANVAS_ID_PREFIX . self::$id_counter;
        $divid = self::DIV_ID_PREFIX . self::$id_counter;
        $id = self::CANVAS_ID_PREFIX . self::$id_counter;

        $tpl = $pl->getTemplate("tpl.content.html");

        $properties = $a_properties;
        if($this->checkIfChartFromLastVersion($a_properties)) {
            $properties = $this->getTranformedProperties($a_properties);
        }
        $tpl->setVariable("DIV", $divid);
        $tpl->setVariable("DIV_CANVAS_ID", $divcanid);
        $tpl->setVariable("CHART_ID", $id);
        $tpl->setVariable("CHART_TITLE", $properties['chart_title']);
        $tpl->setVariable("CHART_TYPE", $this->getChartType($properties['chart_type']));
        $tpl->setVariable("CHART_MAX_VALUE", $properties['chart_max_value']);
        $tpl->setVariable("CHART_DATA_FORMAT", $properties['data_format']);
        $tpl->setVariable("CHART_CURR_SYMBOL", $properties['currency_symbol']);
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
     * Set tabs in forms
     *
     * @param $a_active
     * @param $tabStyleVisible
     * @throws ilCtrlException
     */
    private function setTabs($a_active, $tabStyleVisible)
    {
        global $DIC;

        $pl = $this->getPlugin();

        $DIC->tabs()->addTab(self::TAB_CHART, $pl->txt(self::LANG_CHART),
            $DIC->ctrl()->getLinkTarget($this, self::CMD_EDIT));

        if ($tabStyleVisible) {
            $DIC->tabs()->addTab(self::TAB_DATASETS, $pl->txt(self::LANG_CHART_DATASETS),
                $DIC->ctrl()->getLinkTarget($this, self::CMD_EDIT_DATASETS));

            $DIC->tabs()->addTab(self::TAB_STYLE, $pl->txt(self::LANG_CHART_STYLE),
                $DIC->ctrl()->getLinkTarget($this, self::CMD_EDIT_STYLE));
        }

        if ($a_active === "chart") {
            $DIC->tabs()->activateTab(self::TAB_CHART);
        } elseif ($a_active === "style") {
            $DIC->tabs()->activateTab(self::TAB_STYLE);
        } elseif ($a_active === "datasets") {
            $DIC->tabs()->activateTab(self::TAB_DATASETS);
        }
    }

    /**
     * Get properties, after tranformation (this is only for charts, which existed since earlier version)
     *
     * @param $a_properties
     * @return array
     */
    private function getTranformedProperties($a_properties): array
    {
        $tranformedProperties = [];
        $unchangeableKeys = ["chart_title", "chart_type", "data_format", "currency_symbol"];

        foreach($a_properties as $key => $value){

            if(in_array($key, $unchangeableKeys)){
                $tranformedProperties[$key] = $value;
            }else{

                if(strpos($key, "key") > -1){

                    $indexCategory = substr($key, 3);
                    $tranformedProperties["title_category_" . $indexCategory] = $value;
                    $tranformedProperties["value_dataset_1_category_" . $indexCategory] = $a_properties["value" . $indexCategory];

                }else if(strpos($key, "color") > -1){

                    $indexCategory = substr($key, 5);
                    $tranformedProperties["color_category_" . $indexCategory] = $a_properties["color" . $indexCategory];
                }
            }

        }

        $tranformedProperties["title_dataset_1"] = "Dataset";
        $tranformedProperties["color_dataset_1"] = $tranformedProperties["color_category_1"];
        $tranformedProperties["chart_max_value"] = '';

        unset($a_properties["color1"]);
        unset($a_properties["color2"]);
        unset($a_properties["key1"]);
        unset($a_properties["key2"]);
        unset($a_properties["value1"]);
        unset($a_properties["value2"]);

        return $tranformedProperties;
    }

    /**
     * Check if chart is from last version of chart plugin
     *
     * @param $properties
     * @return bool
     */
    private function checkIfChartFromLastVersion($properties): bool
    {
        foreach($properties as $key => $value){
            if(strpos($key, "key") > -1){
                return true;
            }
        }
        return false;
    }
}
