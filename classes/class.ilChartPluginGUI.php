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

    private $editorIsActive = false;

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

        //$this->setCharacteristics(ilPCParagraphGUI::_getStandardCharacteristics());
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

    public function editorIsActive()
    {
        return $this->editorIsActive;
    }

    /**
     * Form for new elements
     */
    public function insert()
    {
        global $tpl;
        $this->editorIsActive = true;

        var_dump("OK");
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
                        $properties["value_dataset_" . ($k + 1) . "_category_" . ($key + 1)] = "";
                    }
                }
            }

            $extendedColors = $this->getExtendendColors();
            // Set default colors for categories
            $j = 0; // Key in $extendedColors array
            for ($i = 0; $i < count($form->getInput("categories")); $i ++) {
                $color = $extendedColors[$j];

                if ($j === count($extendedColors) - 1) {
                    $j = 0;
                } else {
                    $j += 1;
                }
                $properties["color_category_".($i + 1)] = $color;
            }

            // Set default colors for datasets
            $j = 0; // Key in $extendedColors array
            for ($i = 0; $i < count($form->getInput("datasets")); $i ++) {
                $color = $extendedColors[$j];

                if ($j === count($extendedColors) - 1) {
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
     * @param
     * @return
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

            $datasetValues = [];
            foreach($properties as $key => $value){
                if(strpos($key, "value_dataset_") > -1){
                    $datasetValues[$key] = $value;
                }
            }

            $countColorsCategories = count($form->getInput("categories"));
            $propertiesCategoriesColorsTmp = [];
            
            for ($i = 1; $i <= $countColorsCategories; $i++) {
                $propertiesCategoriesColorsTmp["color_category_".$i] = $properties["color_category_".$i];
            }

            $countColorsDatasets = count($form->getInput("datasets"));
            $propertiesDatasetsColorsTmp = [];

            for ($i = 1; $i <= $countColorsDatasets; $i++) {
                $propertiesDatasetsColorsTmp["color_dataset_".$i] = $properties["color_dataset_".$i];
            }

            $properties = [];
            $properties["chart_title"] = $form->getInput("chart_title");
            $properties["chart_type"] = $form->getInput("chart_type");
            $properties["data_format"] = $form->getInput("data_format");
            $properties["currency_symbol"] = $form->getInput("currency_symbol");
            $properties = array_merge($properties, $propertiesCategoriesColorsTmp);
            $properties = array_merge($properties, $propertiesDatasetsColorsTmp);
            $properties = array_merge($properties, $datasetValues);

            foreach ($form->getInput("categories") as $key => $value) {
                $properties["title_category_".($key+1)] = $value;
            }

            foreach ($form->getInput("datasets") as $key => $value) {
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

        $form = $this->initFormStyleEdit();
        if ($form->checkInput()) {
            $properties = $this->getProperties();

            $countDatasets = $this->getCountPropertiesByType($properties, "title_dataset");
            $countCategory = $this->getCountPropertiesByType($properties, "title_category");

            for ($i = 0; $i < $countCategory; $i++) {
                for ($j = 0; $j < $countDatasets; $j++) {
                    $properties["value_dataset_" . ($j+1). "_category_".($i+1)] = $form->getInput("dataset_" . ($j+1). "_category_".($i+1));
                }
            }

            if ($this->updateElement($properties)) {
                ilUtil::sendSuccess($DIC->language()->txt(self::LANG_OBJ_MODIFIED), true);
                $DIC->ctrl()->redirect($this, self::CMD_EDIT_DATASETS);
            }
        }
    }

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
        return true;
        /*$labels = $form->getInput("categories")["label"];
        $validate = true;
        foreach ($labels as $k => $label) {
            if ($label === "") {
                $validate = false;
                break;
            }
            if (preg_match('#[^0-9.,]#', $label)) {
                $validate = false;
                break;
            }
            $explodeDot = explode(".", $label);
            $explodeComma = explode(",", $label);
            if ((count($explodeDot) > 2 || count($explodeComma) > 2) || (count($explodeDot) > 1 && count($explodeComma) > 1) ||
                (strpos($label, '.') === strlen($label) - 1 || strpos($label, ',') === strlen($label) -1) ||
                (strpos($label, '.') === 0 || strpos($label, ',') === 0)) {
                $validate = false;
            }
            if (!strpos($label, ".") && substr($label, 0, 1) == "0" && strlen($label) > 1) {
                $validate = false;
            }
        }
        return $validate;*/
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
        $form->setTitle($this->getPlugin()->txt(self::LANG_CHART));
        // Add Description
        $form->setDescription($this->getPlugin()->txt(self::LANG_DESCRIPTION));
        // Get Properties
        $prop = $this->getProperties();

        // Title of chart
        $titleChart = new ilTextInputGUI($this->getPlugin()->txt(self::LANG_CHART_TITLE), "chart_title");
        $titleChart->setRequired(false);
        $titleChart->setValue($prop["chart_title"]);
        $form->addItem($titleChart);

        // Select kind of chart
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

        // Radio buttons for data format
        $radioGroup = new ilRadioGroupInputGUI($this->getPlugin()->txt("data_format"), "data_format");
        $radioGroup->setRequired(true);
        $radioGroup->setValue($prop["data_format"]);

        // Radio button for data format number with suditem for currency symbol
        $radioNumber = new ilRadioOption($this->getPlugin()->txt("number"), "1");
        $currencySymbol = new ilTextInputGUI($this->getPlugin()->txt("currency_symbol"), "currency_symbol");
        $currencySymbol->setInfo($this->getPlugin()->txt('add_currency_symbol'));
        $currencySymbol->setValue($prop["currency_symbol"]);
        $radioNumber->addSubItem($currencySymbol);

        $radioGroup->addOption($radioNumber);

        $radioPercent = new ilRadioOption($this->getPlugin()->txt("percent"), "2");
        $radioGroup->addOption($radioPercent);
        $form->addItem($radioGroup);

        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->lng->txt('categories'));
        //$header->setInfo($this->getPlugin()->txt("categories_info"));
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
        $header->setTitle($this->getPlugin()->txt('datasets'));
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

    public function buildOrderingTextsInputGui()
    {
        //$formDataConverter = $this->buildOrderingTextsFormDataConverter();
        require_once 'Modules/TestQuestionPool/classes/forms/class.ilAssOrderingFormValuesObjectsConverter.php';
        $converter = new ilAssOrderingFormValuesObjectsConverter();
        $converter->setPostVar('test20');

        require_once 'Modules/TestQuestionPool/classes/forms/class.ilAssOrderingTextsInputGUI.php';

        $orderingElementInput = new ilAssOrderingTextsInputGUI(
            $converter,
            'test10'
        );

        $orderingElementInput->setInfo($this->lng->txt('ordering_answer_sequence_info'));
        $orderingElementInput->setTitle($this->lng->txt('answers'));
        //$orderingElementInput->setEditElementOccuranceEnabled(true);

        /*require_once 'Modules/TestQuestionPool/classes/forms/class.ilAssOrderingTextsInputGUI.php';
        $x = new ilAssOrderingElementList();
        $orderingElementInput->setElementList($x);*/


        return $orderingElementInput;
    }

    /**
     * Style Form
     *
     * @return ilPropertyFormGUI
     */
    public function initFormStyleEdit()
    {
        global $DIC;

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");

        $form = new ilPropertyFormGUI();
        // Add Title
        $form->setTitle($this->getPlugin()->txt("edit_style"));
        // Add Description
        $form->setDescription($this->getPlugin()->txt(self::LANG_DESCRIPTION_STYLE));
        // Get Properties
        $prop = $this->getProperties();

        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->getPlugin()->txt("categories"));
        $header->setInfo($this->getPlugin()->txt(self::LANG_DESCRIPTION_STYLE));
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
        $header->setInfo($this->getPlugin()->txt(self::LANG_DESCRIPTION_STYLE));
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
        $form->addCommandButton(self::CMD_UPDATE_DATASETS, $DIC->language()->txt(self::CMD_SAVE));
        $form->addCommandButton(self::CMD_CANCEL, $DIC->language()->txt(self::CMD_CANCEL));
        // Get Properties
        $prop = $this->getProperties();

        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->getPlugin()->txt("categories"));
        $form->addItem($header);

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

        $radioGroup = new ilRadioGroupInputGUI($this->getPlugin()->txt("datasets"), "dataset_values");
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
     * Get Chart Type
     *
     * @param string $chart_type
     * @return string
     */
    private function getChartType(string $chart_type): string
    {
        if ($chart_type == '1') {
            return 'horizontalBar';
        } elseif ($chart_type == '2') {
            return 'bar';
        } else {
            return 'pie';
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
        $summ = 0;
        $valArray = [];
        $result = [];
        $percent = "";
        
        if ($a_properties['data_format'] === '2') {
            foreach ($a_properties as $key => $value) {
                if (strpos($key, "val") > -1) {
                    $value = str_replace(',', '.', $value);
                    $valArray[] = floatval($value);
                    $valInteger = floatval($value);
                    $summ += round($valInteger, 2);
                    
                    for ($index = 0; $index < count($valArray); $index++) {
                        $result[$index] = round(($valArray[$index]*100/$summ), 2);
                    }
                }
            }
        }
        foreach ($result as $key => $value) {
            $percent .= '<input type="hidden" id="'.$key.'" value="'.$value.'">';
        }
        return $percent;
    }
    
    /**
     * Get key Input Fields
     *
     * @param array $a_properties
     * @return string
     */
    private function keyInputField(array $a_properties): string
    {
        $keyFields = "";
        
        foreach ($a_properties as $key => $value) {
            if (strpos($key, "key") > -1) {
                $keyFields .= '<input type="hidden" id="'.$key.'" value="'.$value.'">';
            }
        }
        return $keyFields;
    }
    
    /**
     * Get value Input Fields
     *
     * @param array $a_properties
     * @return string
     */
    private function valueInputField(array $a_properties): string
    {
        $valueFields = "";
        
        foreach ($a_properties as $key => $value) {
            if (strpos($key, "val") > -1) {
                $value = str_replace(',', '.', $value);
                $valueFields .= '<input type="hidden" id="'.$key.'" value="'.round($value, 2).'">';
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
    private function colorInputField(array $a_properties): string
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
     * Get Element HTML
     *
     * @param $a_mode
     * @param array $a_properties
     * @param $a_plugin_version
     * @return mixed
     */
    public function getElementHTML($a_mode, array $a_properties, $a_plugin_version)
    {
        $pl = $this->getPlugin();
        $tpl = $pl->getTemplate("tpl.content.html");
        
        self::$id_counter += 1;
        $divcanid = self::DIV_CANVAS_ID_PREFIX . self::$id_counter;
        $divid = self::DIV_ID_PREFIX . self::$id_counter;
        $id = self::CANVAS_ID_PREFIX . self::$id_counter;

        $tpl->setVariable("DIV", $divid);
        $tpl->setVariable("DIV_CANVAS_ID", $divcanid);
        $tpl->setVariable("CHART_ID", $id);
        $tpl->setVariable("CHART_TITLE", $a_properties['chart_title']);
        $tpl->setVariable("CHART_TYPE", $this->getChartType($a_properties['chart_type']));
        $tpl->setVariable("CHART_DATA_FORMAT", $a_properties['data_format']);
        $tpl->setVariable("CHART_CURR_SYMBOL", $a_properties['currency_symbol']);
        $tpl->setVariable("KEYS", $this->keyInputField($a_properties));
        $tpl->setVariable("VALUES", $this->valueInputField($a_properties));
        $tpl->setVariable("COLOR", $this->colorInputField($a_properties));
        $tpl->setVariable("PERC", $this->percentDataFormat($a_properties));
        $tpl->parseCurrentBlock();
        
        return $tpl->get();
    }
    
    /**
     * Set tabs in forms
     *
     * @param $a_active
     * @param $tabStyleVisible
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
}
