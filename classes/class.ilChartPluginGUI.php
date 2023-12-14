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

/**
 * Class ilChartPluginGUI
 *
 * @author KPG <support@kroepelin-projekte.de>
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
    const CMD_UPDATE_STYLE = "updateStyle";
    const CMD_EDIT_DATASETS = "editDatasets";
    const CMD_UPDATE_DATASETS = "updateDatasets";
    const TAB_STYLE = "style";
    const LANG_CHART_STYLE = "chart_style";
    const LANG_DESCRIPTION = "description";
    const LANG_CHART_DATASETS = "chart_datasets";
    const LANG_DESCRIPTION_DATASETS = "description_datasets";
    const LANG_CHART = "chart";
    const LANG_CHART_HORIZONTAL_BAR = "horizontal_bar_chart";
    const LANG_CHART_VERTICAL_BAR = "vertical_bar_chart";
    const LANG_CHART_PIE_CHART = "pie_chart";
    const LANG_CHART_LINE_CHART = 'line_chart';
    const CANVAS_ID_PREFIX = "chart_page_component_";
    const DIV_CANVAS_ID_PREFIX = "div_canvas_";
    const DIV_ID_PREFIX = "chart_div_";
    const MESSAGE_SUCCESS = "msg_obj_modified";
    const MESSAGE_FAILURE = "form_input_not_valid";
    const CHART_TITLE = "chart_title";
    const CHART_TYPE = "chart_type";
    const DATA_FORMAT = "data_format";
    const CURRENCY_SYMBOL = "currency_symbol";
    const CHART_MAX_VALUE = "chart_max_value";
    const CATEGORIES = "categories";
    const DATASETS = "datasets";
    const DESCRIPTION_EDIT_STYLE = "description_edit_style";
    private Container $dic;
    protected ilGlobalTemplateInterface $tpl;
    protected static int $id_counter = 0;
    public function __construct()
    {
        global $DIC;
        $this->dic = $DIC;
        $this->tpl = $DIC['tpl'];
        parent::__construct();
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
                                 self::CMD_UPDATE,
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
        $this->setTabs(self::LANG_CHART, false);
        $form = $this->initFormChart(self::CMD_INSERT);
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * @throws ilFormException
     * @throws ilCtrlException
     */
    public function create(): void
    {
        $form = $this->initFormChart(self::CMD_INSERT);
        if (!$form->checkInput() || !$this->validate($form)) {
            $this->tpl->setOnScreenMessage("failure", $this->dic->language()->txt(self::MESSAGE_FAILURE));
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHtml());

        } else {
            $properties = [
                self::CHART_TITLE => $form->getInput(self::CHART_TITLE),
                self::CHART_TYPE => $form->getInput(self::CHART_TYPE),
                self::CHART_MAX_VALUE => $form->getInput(self::CHART_MAX_VALUE),
                self::DATA_FORMAT => $form->getInput(self::DATA_FORMAT),
                self::CURRENCY_SYMBOL => $form->getInput(self::CURRENCY_SYMBOL),
            ];
            foreach ($form->getInput(self::CATEGORIES) as $key => $value) {
                $properties["title_category_" . ($key + 1)] = $value;
            }
            foreach ($form->getInput(self::DATASETS) as $key => $value) {
                $properties["title_dataset_" . ($key + 1)] = $value;
            }
            foreach ($form->getInput(self::CATEGORIES) as $key => $value) {
                foreach ($form->getInput(self::DATASETS) as $k => $val) {
                      if(!array_key_exists("value_dataset_" . ($k + 1) . "_category_" . ($key + 1), $properties)) {
                        $properties["value_dataset_" . ($k + 1) . "_category_" . ($key + 1)] = "0";
                    }
                }
            }


            if (count($form->getInput(self::CATEGORIES)) !== count(array_unique($form->getInput(self::CATEGORIES)))) {
                $this->tpl->setOnScreenMessage("failure", $this->plugin->txt('category_names_unique'));
                $form->setValuesByPost();
                $this->tpl->setContent($form->getHtml());
                return;
            }
            if (count($form->getInput(self::DATASETS)) !== count(array_unique($form->getInput(self::DATASETS)))) {
                $this->tpl->setOnScreenMessage("failure", $this->plugin->txt('datasets_names_unique'));
                $form->setValuesByPost();
                $this->tpl->setContent($form->getHtml());
                return;
            }

            $shuffleExtendedColors = $this->getShuffleExtendedColors();
            // Set default colors for categories
            $j = 0; // Key in $extendedColors array
            for ($i = 0; $i < count($form->getInput(self::CATEGORIES)); $i ++) {
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
            for ($i = 0; $i < count($form->getInput(self::DATASETS)); $i ++) {
                $color = $shuffleExtendedColors[$j];

                if ($j === count($shuffleExtendedColors) - 1) {
                    $j = 0;
                } else {
                    $j += 1;
                }
                $properties["color_dataset_".($i + 1)] = $color;
            }
            if ($this->createElement($properties)) {
                $this->tpl->setOnScreenMessage("success", $this->dic->language()->txt(self::MESSAGE_SUCCESS), true);
                $this->returnToParent();
            }
        }
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
        $this->setTabs(self::LANG_CHART, true);
        $form = $this->initFormChart(self::CMD_EDIT);
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * @throws ilCtrlException
     */
    public function editStyle(): void
    {
        $this->setTabs(self::TAB_STYLE, true);
        $form = $this->initFormStyleEdit();
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * @throws ilCtrlException
     */
    public function editDatasets(): void
    {
        $this->setTabs(self::DATASETS, true);
        $form = $this->initFormDatasetsEdit();
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * @throws ilCtrlException
     * @throws ilFormException
     */
    private function update(): void
    {
        $form = $this->initFormChart(self::CMD_EDIT);

        $properties = $this->getProperties();
        if (!$form->checkInput() || !$this->validate($form)) {
            $this->tpl->setOnScreenMessage("failure", $this->dic->language()->txt(self::MESSAGE_FAILURE));
            $this->setTabs(self::LANG_CHART, true);
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHtml());
            return;
        }

        if($this->checkIfChartFromLastVersion($properties)) {
            $properties = $this->getTranformedProperties($properties);
        }

        $datasetValues = [];

        foreach ($form->getInput(self::CATEGORIES) as $key => $value) {
            foreach ($form->getInput(self::DATASETS) as $k => $val) {

                if(array_key_exists("value_dataset_" . ($k + 1) . "_category_" . ($key + 1), $properties)) {
                    $datasetValues["value_dataset_" . ($k + 1) . "_category_" . ($key + 1)] = $properties["value_dataset_" . ($k + 1) . "_category_" . ($key + 1)];
                }else{
                    $datasetValues["value_dataset_" . ($k + 1) . "_category_" . ($key + 1)] = "0";
                }
            }
        }

        if (count($form->getInput(self::CATEGORIES)) !== count(array_unique($form->getInput(self::CATEGORIES)))) {
            $this->tpl->setOnScreenMessage("failure", $this->plugin->txt('category_names_unique'));
            $this->setTabs(self::LANG_CHART, true);
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHtml());
            return;
        }
        if (count($form->getInput(self::DATASETS)) !== count(array_unique($form->getInput(self::DATASETS)))) {
            $this->tpl->setOnScreenMessage("failure", $this->plugin->txt('datasets_names_unique'));
            $this->setTabs(self::LANG_CHART, true);
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHtml());
            return;
        }

        $countColorsCategories = count($form->getInput(self::CATEGORIES));
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

        $countColorsDatasets = count($form->getInput(self::DATASETS));
        $propertiesDatasetsColorsTmp = [];

        for ($i = 1; $i <= $countColorsDatasets; $i++) {

            if(array_key_exists("color_dataset_".$i, $properties)) {
                $propertiesDatasetsColorsTmp["color_dataset_".$i] = $properties["color_dataset_".$i];
                $datasetValues["color_dataset_".$i] = $properties["color_dataset_".$i];
            }else{
                $extendedColors = $this->getExtendendColors();
                $color = $extendedColors[rand(0, count($extendedColors)-1)];

                $propertiesDatasetsColorsTmp["color_dataset_".$i] = $color;
                $datasetValues["color_dataset_".$i] = $color;
            }
        }

        $properties = [];
        $properties[self::CHART_TITLE] = $form->getInput(self::CHART_TITLE);
        $properties[self::CHART_TYPE] = $form->getInput(self::CHART_TYPE);
        $properties[self::CHART_MAX_VALUE] = $form->getInput(self::CHART_MAX_VALUE);
        $properties[self::DATA_FORMAT] = $form->getInput(self::DATA_FORMAT);
        $properties[self::CURRENCY_SYMBOL] = $form->getInput(self::CURRENCY_SYMBOL);
        $properties = array_merge($properties, $propertiesCategoriesColorsTmp);
        $properties = array_merge($properties, $propertiesDatasetsColorsTmp);
        $properties = array_merge($properties, $datasetValues);

        foreach ($form->getInput(self::CATEGORIES) as $key => $value) {
            $properties["title_category_".($key+1)] = $value;
        }

        $datasets = $form->getInput(self::DATASETS);
        foreach ($datasets as $key => $value) {
            $properties["title_dataset_".($key+1)] = $value;
        }

        if ($this->updateElement($properties)) {
            $this->tpl->setOnScreenMessage("success", $this->dic->language()->txt(self::MESSAGE_SUCCESS), true);
            $this->dic->ctrl()->redirectByClass(self::PLUGIN_CLASS_NAME, self::CMD_EDIT);
        }
    }

    /**
     * @throws ilCtrlException
     */
    private function updateStyle(): void
    {
        $form = $this->initFormStyleEdit();

        if (! $form->checkInput()) {
            $this->tpl->setOnScreenMessage("failure", $this->dic->language()->txt(self::MESSAGE_FAILURE));
            $this->setTabs(self::TAB_STYLE, true);
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHTML());
            return;
        }

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
            $this->tpl->setOnScreenMessage("success", $this->dic->language()->txt(self::MESSAGE_SUCCESS), true);
            $this->dic->ctrl()->redirect($this, self::CMD_EDIT_STYLE);
        }
    }

    /**
     * @throws ilCtrlException
     */
    private function updateDatasets(): void
    {
        $form = $this->initFormDatasetsEdit();

        if (! $form->checkInput()) {
            $this->tpl->setOnScreenMessage("failure", $this->dic->language()->txt(self::MESSAGE_FAILURE));
            $this->setTabs(self::DATASETS, true);
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHTML());
            return;
        }

        $properties = $this->getProperties();
        if($this->checkIfChartFromLastVersion($properties)) {
            $properties = $this->getTranformedProperties($properties);
        }
        $countDatasets = $this->getCountPropertiesByType($properties, "title_dataset");
        $countCategory = $this->getCountPropertiesByType($properties, "title_category");

        $err = 0;
        for ($i = 0; $i < $countCategory; $i++) {
            for ($j = 0; $j < $countDatasets; $j++) {

                $input = trim($form->getInput("dataset_" . ($j+1). "_category_".($i+1)));

                if($input === '') {
                    $input = '0';
                }

                if(! is_numeric($input)){
                    $err++;
                } else {
                    $properties["value_dataset_" . ($j+1). "_category_".($i+1)] = $input;
                }
            }
        }

        if ($err !== 0) {
            $this->tpl->setOnScreenMessage("failure", $this->dic->language()->txt(self::MESSAGE_FAILURE));
            $this->setTabs(self::DATASETS, true);
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHTML());
            return;
        }

        if ($this->updateElement($properties)) {
            $this->tpl->setOnScreenMessage("success", $this->dic->language()->txt(self::MESSAGE_SUCCESS), true);
            $this->dic->ctrl()->redirect($this, self::CMD_EDIT_DATASETS);
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

    private function validate(ilPropertyFormGUI $form): bool
    {
        if($form->getInput(self::CHART_TYPE) === ""){
            return false;
        }

        if(!is_numeric($form->getInput(self::CHART_MAX_VALUE)) && $form->getInput(self::CHART_MAX_VALUE) !== ''){
            return false;
        }

        $categories = $form->getInput(self::CATEGORIES);
        foreach($categories as $value){

            if($value === ""){
                return false;
            }
        }

        $datasets = $form->getInput(self::DATASETS);
        foreach($datasets as $value){

            if($value === ""){
                return false;
            }
        }
        return true;
    }

    private function getExtendendColors(): array
    {
        $parentType = $this->getPlugin()->getParentType();
        $parentId = $this->getPlugin()->getParentId();

        if ($parentType === "copa") {  // Case: parent is content page

            $styles_settings = new ilContentStyleSettings();
            $styles_settings->read();
            $styles = $styles_settings->getStyles();
            $styleId = array_pop($styles)['id'];

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

            foreach ($colors as $color) {
                if (strpos($color["name"], "extendedcolor") > -1) {
                    $extendedColorsCode[] = $color["code"];
                }
            }
        }
        return $extendedColorsCode;
    }

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
     * @throws ilCtrlException
     * @throws ilFormException
     */
    public function initFormChart(string $action): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->getPlugin()->txt(self::CMD_EDIT));
        $form->setDescription($this->getPlugin()->txt(self::LANG_DESCRIPTION));
        $prop = $this->getProperties();
        if($this->checkIfChartFromLastVersion($prop)) {
            $prop = $this->getTranformedProperties($this->getProperties());
        }
        $titleChart = new ilTextInputGUI($this->getPlugin()->txt(self::CHART_TITLE), self::CHART_TITLE);
        $titleChart->setRequired(false);
        $titleChart->setValue($prop[self::CHART_TITLE] ?? "");
        $form->addItem($titleChart);

        $selectChartType = new ilSelectInputGUI($this->getPlugin()->txt(self::CHART_TYPE), self::CHART_TYPE);
        $selectChartType->setRequired(true);
        $optionsChart = [
            "1" => $this->getPlugin()->txt(self::LANG_CHART_HORIZONTAL_BAR),
            "2" => $this->getPlugin()->txt(self::LANG_CHART_VERTICAL_BAR),
            "3" => $this->getPlugin()->txt(self::LANG_CHART_PIE_CHART),
            "4" => $this->getPlugin()->txt(self::LANG_CHART_LINE_CHART)
        ];
        $selectChartType->setOptions($optionsChart);
        $selectChartType->setValue($prop[self::CHART_TYPE] ?? "1");
        $form->addItem($selectChartType);

        $maxValueChart = new ilTextInputGUI($this->getPlugin()->txt(self::CHART_MAX_VALUE), self::CHART_MAX_VALUE);
        $maxValueChart->setRequired(false);
        $maxValueChart->setValue($prop[self::CHART_MAX_VALUE] ?? "");
        $form->addItem($maxValueChart);

        $radioGroup = new ilRadioGroupInputGUI("Format", self::DATA_FORMAT);
        $radioGroup->setValue($prop[self::DATA_FORMAT] ?? "2");
        $radioGroup->setRequired(true);

        $radioNumber = new ilRadioOption($this->getPlugin()->txt("number"), "1");
        $currencySymbol = new ilTextInputGUI("Symbol", self::CURRENCY_SYMBOL);
        $currencySymbol->setInfo($this->getPlugin()->txt('add_currency_symbol'));
        $currencySymbol->setValue($prop[self::CURRENCY_SYMBOL] ?? "");
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
        $header->setTitle($this->getPlugin()->txt('datasets_names'));
        $header->setInfo($this->getPlugin()->txt("datasets_info"));
        $form->addItem($header);

        $countDataset = $this->getCountPropertiesByType($prop, 'title_dataset');
        $datasetsTitle = [];
        for($i = 0; $i < $countDataset; $i++){
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
            $form->addCommandButton(self::CMD_CREATE, $this->dic->language()->txt(self::CMD_SAVE));
        } else {
            $form->addCommandButton(self::CMD_UPDATE, $this->dic->language()->txt(self::CMD_SAVE));
        }
        $form->addCommandButton(self::CMD_CANCEL, $this->dic->language()->txt(self::CMD_CANCEL));
        $form->setFormAction($this->dic->ctrl()->getFormAction($this));

        return $form;
    }

    /**
     * @throws ilCtrlException
     */
    public function initFormStyleEdit(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->getPlugin()->txt(self::CMD_EDIT));
        $form->setDescription($this->getPlugin()->txt(self::DESCRIPTION_EDIT_STYLE));
        $prop = $this->getProperties();
        if($this->checkIfChartFromLastVersion($prop)) {
            $prop = $this->getTranformedProperties($this->getProperties());
        }
        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->getPlugin()->txt(self::CATEGORIES));
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
        $header->setTitle($this->getPlugin()->txt(self::DATASETS));
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

        $form->addCommandButton(self::CMD_UPDATE_STYLE, $this->dic->language()->txt(self::CMD_SAVE));
        $form->addCommandButton(self::CMD_CANCEL, $this->dic->language()->txt(self::CMD_CANCEL));
        $form->setFormAction($this->dic->ctrl()->getFormAction($this));

        return $form;
    }

    /**
     * @throws ilCtrlException
     */
    public function initFormDatasetsEdit(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setDescription($this->getPlugin()->txt(self::LANG_DESCRIPTION_DATASETS));
        $form->setTitle($this->getPlugin()->txt(self::CMD_EDIT));
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

        $form->addCommandButton(self::CMD_UPDATE_DATASETS, $this->dic->language()->txt(self::CMD_SAVE));
        $form->addCommandButton(self::CMD_CANCEL, $this->dic->language()->txt(self::CMD_CANCEL));
        $form->setFormAction($this->dic->ctrl()->getFormAction($this));

        return $form;
    }


    function cancel(): void
    {
        $this->returnToParent();
    }


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


    private function percentDataFormat(array $a_properties): string
    {
        $percent = "";
        $datasets = [];
        $datasetsValueCategory = [];
        $countCategories = $this->getCountCategories($a_properties);
        if ($a_properties[self::DATA_FORMAT] === "2") {
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
     */
    public function getElementHTML(string $a_mode, array $a_properties, string $a_plugin_version): string
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
        $tpl->setVariable("CHART_TITLE", $properties[self::CHART_TITLE]);
        $tpl->setVariable("CHART_TYPE", $this->getChartType($properties[self::CHART_TYPE]));
        $tpl->setVariable("CHART_MAX_VALUE", $properties[self::CHART_MAX_VALUE]);
        $tpl->setVariable("CHART_DATA_FORMAT", $properties[self::DATA_FORMAT]);
        $tpl->setVariable("CHART_CURR_SYMBOL", $properties[self::CURRENCY_SYMBOL]);
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
    private function setTabs(string $a_active, bool $tabStyleVisible): void
    {
        $pl = $this->getPlugin();
        $this->dic->tabs()->addTab(self::LANG_CHART, $pl->txt(self::LANG_CHART),
            $this->dic->ctrl()->getLinkTarget($this, self::CMD_EDIT));
        if ($tabStyleVisible) {
            $this->dic->tabs()->addTab(self::DATASETS, $pl->txt(self::LANG_CHART_DATASETS),
                $this->dic->ctrl()->getLinkTarget($this, self::CMD_EDIT_DATASETS));
            $this->dic->tabs()->addTab(self::TAB_STYLE, $pl->txt(self::LANG_CHART_STYLE),
                $this->dic->ctrl()->getLinkTarget($this, self::CMD_EDIT_STYLE));
        }
        if ($a_active === "chart") {
            $this->dic->tabs()->activateTab(self::LANG_CHART);
        } elseif ($a_active === "style") {
            $this->dic->tabs()->activateTab(self::TAB_STYLE);
        } elseif ($a_active === self::DATASETS) {
            $this->dic->tabs()->activateTab(self::DATASETS);
        }
    }

    private function getTranformedProperties(array $a_properties): array
    {
        $tranformedProperties = [];
        $unchangeableKeys = [self::CHART_TITLE, self::CHART_TYPE, self::DATA_FORMAT, self::CURRENCY_SYMBOL];
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
        $tranformedProperties[self::CHART_MAX_VALUE] = '';

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
        foreach($properties as $key => $value){
            if(strpos($key, "key") > -1){
                return true;
            }
        }
        return false;
    }
}
