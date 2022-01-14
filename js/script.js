var divClass = document.getElementsByClassName('chdiv');
var div = [];
var j = 1;

for (let i = 0; i < divClass.length; i++) {

    let title, type, chartId, chartMaxValue, chartDataFormat, chartCurrencySymbol, categoryDiv, datasetDiv,
        datasetCount, datasetValueDiv,
        colorCategoryDiv, colorDatasetDiv, percentDiv, chartDataSet, chartLabels, canVas, dataTable, thisChart,
        optionsPie, optionsHorizontalBar, optionsBar, optionsLine, symbol;

    div[i] = document.getElementById('chart_div_' + j).children;

    title = div[i].chart_title.value;
    type = div[i].chart_type.value;
    chartId = div[i].chart_id.value;
    chartMaxValue = div[i].chart_max_value.value;
    chartDataFormat = div[i].chart_data_format.value;
    chartCurrencySymbol = div[i].chart_currency_symbol.value;

    // Category title
    categoryDiv = div[i].div_title_category.children;
    // Dataset title
    datasetDiv = div[i].div_dataset.children;
    // Dataset value
    datasetValueDiv = div[i].div_value_dataset.children;
    // Category Color
    colorCategoryDiv = div[i].div_color_category.children;
    // Dataset color
    colorDatasetDiv = div[i].div_color_dataset.children;
    // Percent
    percentDiv = div[i].div_percent.children;

    chartDataSet = {label: [], data: [], backgroundColor: [], borderColor: []};
    chartLabels = {labels: [], datasetsTitle: []};

    // If data format is percent
    if (chartDataFormat === "2") {

        for (let k = 0; k < categoryDiv.length; k++) {

            let tmp = {};
            let index = 0;
            for (let n = 0; n < percentDiv.length; n++) {

                if (percentDiv[n].id.indexOf('category_' + k) > -1) {

                    tmp[index] = percentDiv[n].value;
                }
                index += 1;
            }
            chartDataSet.data[k] = tmp;
        }

        symbol = function (value) {
            return value + ' %';
        };

    } else {

        symbol = function (value) {
            let parseVal = parseFloat(value);
            return parseVal.toLocaleString() + ' ' + chartCurrencySymbol;
        };
    }

    if (type === 'pie' || type === 'line') {

        for (let k = 0; k < categoryDiv.length; k++) {
            chartLabels.labels[k] = categoryDiv[k].value;
        }

    } else {

        for (let k = 0; k < categoryDiv.length; k++) {
            chartLabels.labels[k] = categoryDiv[k].value;
            chartLabels.datasetsTitle[k] = categoryDiv[k].value; // TODO to delete
        }
    }

    let categoriesColors = [];
    for (let k = 0; k < colorCategoryDiv.length; k++) {
        categoriesColors.push('#' + colorCategoryDiv[k].value);
    }

    // Count of datasets
    datasetCount = getCountDatasets(datasetValueDiv);

    let datasetForChart = [];
    let dataDataset = [];
    for (let n = 0; n < datasetCount; n++) {

        let dataDatasetTmp = [];

        if (chartDataFormat === "1") {

            for (let m = 0; m < datasetValueDiv.length; m++) {
                if (datasetValueDiv[m].getAttribute('id').indexOf('value_dataset_' + (n + 1)) > -1) {
                    dataDatasetTmp.push(datasetValueDiv[m].value);
                }
            }

        } else {

            for (let m = 0; m < percentDiv.length; m++) {
                if (percentDiv[m].getAttribute('id').indexOf('dataset_' + (n + 1) + '_category') > -1) {
                    dataDatasetTmp.push(percentDiv[m].value);
                }
            }
        }
        dataDataset[n] = dataDatasetTmp;

        if (type === 'horizontalBar') {
            datasetForChart[n] = datasetHorizontalBarChart(datasetDiv[n].value, dataDataset[n], colorDatasetDiv[n].value);
        } else if (type === 'bar') {
            datasetForChart[n] = datasetVerticalBarChart(datasetDiv[n].value, dataDataset[n], colorDatasetDiv[n].value);
        } else if (type === 'pie') {
            datasetForChart[n] = datasetPieChart(datasetDiv[n].value, dataDataset[n], categoriesColors)
        } else if (type === 'line') {
            datasetForChart[n] = datasetLineChart(datasetDiv[n].value, dataDataset[n], colorDatasetDiv[n].value);
        }
    }

    canVas = document.getElementById(chartId).getContext('2d');
    if (type === 'pie') {

        optionsPie = getOptionsPie(symbol, title);
        dataTable = getDataTable(type, chartLabels.labels, datasetForChart, optionsPie);
        thisChart = new Chart(canVas, dataTable);

    } else if (type === 'line') {

        optionsLine = getOptionsLine(symbol, title, chartMaxValue);
        dataTable = getDataTable(type, chartLabels.labels, datasetForChart, optionsLine);
        thisChart = new Chart(canVas, dataTable);

    } else if (type === 'bar') {

        optionsBar = getOptionsVerticalBar(symbol, title, chartMaxValue);
        dataTable = getDataTable(type, chartLabels.labels, datasetForChart, optionsBar);
        thisChart = new Chart(canVas, dataTable);

    } else if (type === 'horizontalBar') {

        optionsHorizontalBar = getOptionsHorizontalBar(symbol, title, chartMaxValue);
        dataTable = getDataTable(type, chartLabels.labels, datasetForChart, optionsHorizontalBar);
        thisChart = new Chart(canVas, dataTable);

        // CSS
        let countBars = categoryDiv.length * datasetDiv.length;
        let heightChart = getHeightChart(countBars);
        document.getElementById('chart_div_' + j).querySelector('.chart-container').style.height = heightChart + 'px';
    }
    j++;
}


/**
 * Find count of datasets
 */
function getCountDatasets(datasetValueSel)  {

    let datasetIndexes = [];
    for (let n = 0; n < datasetValueSel.length; n++) {
        let id = datasetValueSel[n].getAttribute('id');
        datasetIndexes.push(id.substr(parseInt(id.indexOf('value_dataset_') + 14), parseInt(id.indexOf('_category')) - parseInt(id.indexOf('value_dataset_') + 14)));
    }
    return Math.max.apply(Math, datasetIndexes);
}

function datasetHorizontalBarChart(datasetTitle, datasetValue, datasetColor)  {

    return {
        label: datasetTitle,
        data: datasetValue,
        backgroundColor: "#" + datasetColor,
        borderColor: '#000000',
        borderWidth: 1,
        barPercentage: 0.8,
        minBarLength: 25
    }
}

function datasetVerticalBarChart(datasetTitle, datasetValue, datasetColor)  {

    return {
        label: datasetTitle,
        data: datasetValue,
        backgroundColor: "#" + datasetColor,
        borderColor: '#000000',
        borderWidth: 1,
        barPercentage: 0.8,
        minBarLength: 25
    }
}

function datasetPieChart(datasetTitle, datasetValue, categoriesColors)  {

    return {
        label: datasetTitle,
        data: datasetValue,
        backgroundColor: categoriesColors,
        borderColor: '#000000',
        borderWidth: 1
    }
}

function datasetLineChart(datasetTitle, datasetValue, datasetColors)  {

    return {
        label: datasetTitle,
        data: datasetValue,
        backgroundColor: "#" + datasetColors,
        borderColor: "#" + datasetColors,
        borderWidth: 2,
        fill: false,
        tension: 0.0,
    }
}

function getDataTable(typ, labels, datasets, options)  {

    return {
        type: typ,
        data: {
            labels: labels,
            datasets: datasets
        },
        options: options
    };
}

function getOptionsVerticalBar(formatter, title, maxValue)  {

    return {
        plugins: {
            datalabels: {
                align: 'start',
                anchor: 'end',
                backgroundColor: '#ffffff',
                borderColor: '#000000',
                borderRadius: 1,
                borderWidth: 0.5,
                color: '#000000',
                font: {
                    size: 12,
                    weight: 600
                },
                offset: 1,
                padding: 2,
                clamp: true,
                clip: true,
                display: 'auto',
                formatter: formatter
            }
        },
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true,
                    suggestedMax: parseInt(maxValue) ? maxValue : 0,
                    callback: function (value) {
                        return value.toLocaleString();
                    }
                }
            }],
            xAxes: [{
                ticks: {
                    beginAtZero: true
                }
            }]
        },
        responsive: true,
        maintainAspectRatio: false,
        legend: {
            display: true,
            labels: {
                boxWidth: 5,
                usePointStyle: true,
                boxHeight: 1
            }
        },
        title: {
            display: true,
            text: title
        },
        tooltip: true,
        tooltips: {
            mode: 'point',
            callbacks: {
                title: function(tooltipItem, data) {
                    return tooltipItem[0]['label'];
                },
                label: function(tooltipItem, data) {
                    return data['datasets'][tooltipItem['datasetIndex']]['label'] + ' ' + parseFloat(data['datasets'][tooltipItem['datasetIndex']]['data'][tooltipItem['index']]).toLocaleString();
                }
            },
        }
    };
}

function getOptionsHorizontalBar(formatter, title, maxValue)  {

    return {
        plugins: {
            datalabels: {
                align: 'start',
                anchor: 'end',
                backgroundColor: '#ffffff',
                borderColor: '#000000',
                borderRadius: 1,
                borderWidth: 0.5,
                color: '#000000',
                font: {
                    size: 12,
                    weight: 600
                },
                offset: 1,
                padding: 2,
                clamp: true,
                clip: true,
                display: 'auto',
                formatter: formatter
            }
        },
        scales: {
            xAxes: [{
                ticks: {
                    beginAtZero: true,
                    suggestedMax: parseInt(maxValue) ? maxValue : 0,
                    callback: function (value) {
                        return value.toLocaleString();
                    }
                }
            }]
        },
        responsive: true,
        maintainAspectRatio: false,
        legend: {
            display: true,
            labels: {
                boxWidth: 5,
                usePointStyle: true,
                boxHeight: 1
            }
        },
        title: {
            display: true,
            text: title
        },
        tooltip: true,
        tooltips: {
            mode: 'point',
            callbacks: {
                title: function(tooltipItem, data) {
                    return tooltipItem[0]['label'];
                },
                label: function(tooltipItem, data) {
                    return data['datasets'][tooltipItem['datasetIndex']]['label'] + ' ' + parseFloat(data['datasets'][tooltipItem['datasetIndex']]['data'][tooltipItem['index']]).toLocaleString();
                }
            },
        },
    };
}

function getOptionsPie(formatter, title)  {

    return {
        plugins: {
            datalabels: {
                align: 'end',
                anchor: 'center',
                backgroundColor: '#ffffff',
                borderColor: '#000000',
                borderRadius: 1,
                borderWidth: 0.5,
                color: '#000000',
                font: {
                    size: 13,
                    weight: 600
                },
                padding: 2,
                display: 'auto',
                formatter: formatter,
            }
        },
        responsive: true,
        maintainAspectRatio: false,
        legend: {
            display: true,
            labels: {
                boxWidth: 5,
                usePointStyle: true,
                boxHeight: 1
            }
        },
        title: {
            display: true,
            text: title
        },
        tooltips: {
            callbacks: {
                title: function(tooltipItem, data) {
                    return data['datasets'][tooltipItem[0]['datasetIndex']]['label'];
                },
                label: function(tooltipItem, data) {
                    return data['labels'][tooltipItem['index']] + ' ' + parseFloat(data['datasets'][tooltipItem['datasetIndex']]['data'][tooltipItem['index']]).toLocaleString();
                }
            }
        },
    };
}

function getOptionsLine(formatter, title, maxValue)  {

    return {
        plugins: {
            datalabels: {
                align: 'start',
                anchor: 'end',
                backgroundColor: '#ffffff',
                borderColor: '#000000',
                borderRadius: 1,
                borderWidth: 0.5,
                color: '#000000',
                font: {
                    size: 12,
                    weight: 600
                },
                offset: 1,
                padding: 2,
                clamp: false,
                clip: false,
                display: 'auto',
                formatter: formatter
            }
        },
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true,
                    suggestedMax: parseInt(maxValue) ? maxValue : 0,
                    callback: function (value) {
                        return value.toLocaleString();
                    }
                }
            }]
        },
        responsive: true,
        maintainAspectRatio: false,
        legend: {
            display: true,
            labels:{
                boxWidth: 5,
                usePointStyle: true,
                boxHeight: 1
            }
        },
        title: {
            display: true,
            text: title
        },
        tooltip: true,
        tooltips: {
            callbacks: {
                title: function(tooltipItem, data) {
                    return tooltipItem[0]['label'];
                },
                label: function(tooltipItem, data) {
                    return data['datasets'][tooltipItem['datasetIndex']]['label'] + ' ' + parseFloat(data['datasets'][tooltipItem['datasetIndex']]['data'][tooltipItem['index']]).toLocaleString();
                }
            },
        },
    };
}

function getHeightChart(countBars)  {

    let heightChart = 0;

    if(countBars >= 12){
        heightChart = countBars * 45;
    }else if(countBars >= 6){
        heightChart = countBars * 65;
    }else if(countBars >= 3){
        heightChart = countBars * 80;
    }else{
        heightChart = countBars * 100;
    }
    return heightChart;
}