var divClass = document.getElementsByClassName('chdiv');
var div = [];
var j = 1;

for (let i = 0; i < divClass.length; i++) {

    let title, type, chId, chDataFormat, chCurrencySymbol, categoryDiv, datasetDiv, datasetCount, datasetValueDiv,
        colorCategoryDiv, colorDatasetDiv, percDiv, chartDataSet, chartLabels, canVas, dataTable, thisChart,
        optionsPie, optionsHorizontalBar, optionsBar, optionsLine, symbol, countVerticalBars, countHorizontalBarsGroup,
        object = {};

    div[i] = document.getElementById('chart_div_' + j).children;


    title = div[i].chart_title.value;
    type = div[i].chart_type.value;
    chId = div[i].chart_id.value;
    chDataFormat = div[i].chart_data_format.value;
    chCurrencySymbol = div[i].chart_currency_symbol.value;

    // Category (1)
    categoryDiv = div[i].div_title_category.children;
    // Dataset
    datasetDiv = div[i].div_dataset.children;
    // Dataset value (n<5)
    datasetValueDiv = div[i].div_value_dataset.children;
    colorCategoryDiv = div[i].div_color_category.children;
    colorDatasetDiv = div[i].div_color_dataset.children;
    percDiv = div[i].div_percent.children;


    /*console.log("OK");
    for (let i = 0; i < percDiv.length; i++) {

        console.log(percDiv[i].value);
    }
    console.log("OK2");*/


    /**
     * @todo multi Datasets
     */
    chartDataSet = {label: [], data: [], backgroundColor: [], borderColor: []};

    // category
    chartLabels = {labels: [], datasetsTitle: []};

    // Percent(2)/Currency
    if (chDataFormat === "2") {

        for (let k = 0; k < categoryDiv.length; k++) {

            let tmp = {};
            let index = 0;
            for (let n = 0; n < percDiv.length; n++) {

                if (percDiv[n].id.indexOf('category_' + k) > -1) {

                    tmp[index] = percDiv[n].value;
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
            return parseVal.toLocaleString() + ' ' + chCurrencySymbol;
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

    /*let datasetColors = [];
    for (let k = 0; k < colorDatasetDiv.length; k++) {
        datasetColors.push('#' + colorDatasetDiv[k].value);
    }*/

    let categoriesColors = [];
    for (let k = 0; k < colorCategoryDiv.length; k++) {
        categoriesColors.push('#' + colorCategoryDiv[k].value);
    }

    // Find count of datasets
    /*let datasetIndexes = [];
    for (let n = 0; n < datasetValueDiv.length; n++) {
        let id = datasetValueDiv[n].getAttribute('id');
        datasetIndexes.push(id.substr(parseInt(id.indexOf('value_dataset_') + 14), parseInt(id.indexOf('_category')) - parseInt(id.indexOf('value_dataset_') + 14)));
    }

    datasetCount = Math.max.apply(Math, datasetIndexes);*/

    datasetCount = getCountDatasets(datasetValueDiv);


    /* S T A R T  I O A N N A */
    let datasetForChart = [];
    let dataDataset = [];
    for (let n = 0; n < datasetCount; n++) {

        let dataDatasetTmp = [];

        if (chDataFormat === "1") {

            for (let m = 0; m < datasetValueDiv.length; m++) {

                if (datasetValueDiv[m].getAttribute('id').indexOf('value_dataset_' + (n + 1)) > -1) {

                    dataDatasetTmp.push(datasetValueDiv[m].value);
                }
            }

        } else {

            for (let m = 0; m < percDiv.length; m++) {

                if (percDiv[m].getAttribute('id').indexOf('dataset_' + (n + 1) + '_category') > -1) {
                    dataDatasetTmp.push(percDiv[m].value);
                }
            }

        }

        dataDataset[n] = dataDatasetTmp;

        if (type === 'horizontalBar') {

            datasetForChart[n] = {

                label: datasetDiv[n].value,
                data: dataDataset[n],
                backgroundColor: "#" + colorDatasetDiv[n].value,
                borderColor: '#000000',
                borderWidth: 1,
                barPercentage: 0.8,
                minBarLength: 25
            }

        } else if (type === 'bar') {

            datasetForChart[n] = {

                label: datasetDiv[n].value,
                data: dataDataset[n],
                backgroundColor: "#" + colorDatasetDiv[n].value,
                borderColor: '#000000',
                borderWidth: 1,
                barPercentage: 0.8,
                minBarLength: 25
            }

        } else if (type === 'pie') {

            datasetForChart[n] = {

                label: datasetDiv[n].value,
                data: dataDataset[n],
                backgroundColor: categoriesColors,
                borderColor: '#000000',
                borderWidth: 1
            }

        } else if (type === 'line') {

            datasetForChart[n] = {

                label: datasetDiv[n].value,
                data: dataDataset[n],
                backgroundColor: "#" + colorDatasetDiv[n].value,
                borderColor: "#" + colorDatasetDiv[n].value,
                borderWidth: 2,
                fill: false,
                tension: 0.0,
            }
        }

    }

    /* E N D  I O A N N A */
    optionsPie = getOptionsPie(symbol, title);
    /*optionsPie = {
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
                formatter: symbol,
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
                    return parseFloat(data['datasets'][tooltipItem['datasetIndex']]['data'][tooltipItem['index']]).toLocaleString();
                }
            },
        },
    };*/

    optionsHorizontalBar = getOptionsHorizontalBar(symbol, title)
    /*optionsHorizontalBar = {
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
                formatter: symbol
            }
        },
        scales: {
            xAxes: [{
                ticks: {
                    beginAtZero: true,
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
            mode: 'point'
        }
    };*/

    optionsBar = getOptionsVerticalBar(symbol, title);
    /*optionsBar = {
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
                formatter: symbol
            }
        },
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true,
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
            mode: 'point'
        }
    };*/

    optionsLine = getOptionsLine(symbol, title);

    /*optionsLine = {
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
                formatter: symbol
            }
        },
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true,
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
        tooltip: true
    };*/

    canVas = document.getElementById(chId).getContext('2d');

    if (type === 'pie') {

        /*chDataTable = {
            type: type,
            data: {
                labels: chartLabels.labels,
                datasets: datasetForChart
            },
            options: optionsPie
        };*/

        dataTable = getDataTable(type, chartLabels.labels, datasetForChart, optionsPie);

        thisChart = new Chart(canVas, dataTable);

    }else if (type === 'line'){

        /*chDataTable = {
            type: type,
            data: {
                labels: chartLabels.labels,
                datasets: datasetForChart
            },
            options: optionsLine
        };*/

        dataTable = getDataTable(type, chartLabels.labels, datasetForChart, optionsLine);

        thisChart = new Chart(canVas, dataTable);

    } else if (type === 'bar') {

        /*chDataTable = {
            type: type,
            data: {
                labels: chartLabels.labels,
                datasets: datasetForChart
            },
            options: optionsBar
        };*/

        dataTable = getDataTable(type, chartLabels.labels, datasetForChart, optionsBar);

        thisChart = new Chart(canVas, dataTable);

    } else if (type === 'horizontalBar') {

        /*chDataTable = {
            type: type,
            data: {
                labels: chartLabels.labels,
                datasets: datasetForChart
            },
            options: optionsHorizontalBar
        };*/

        dataTable = getDataTable(type, chartLabels.labels, datasetForChart, optionsHorizontalBar);

        thisChart = new Chart(canVas, dataTable);

        // CSS
        let countBars = categoryDiv.length * datasetDiv.length;
        let heightChart = getHeightChart(countBars);
        document.getElementById('chart_div_' + j).querySelector('.chart-container').style.height = heightChart + 'px';
    }
    j++;
}

function getCountDatasets(datasetValueSel)  {

    let datasetIndexes = [];
    for (let n = 0; n < datasetValueSel.length; n++) {
        let id = datasetValueSel[n].getAttribute('id');
        datasetIndexes.push(id.substr(parseInt(id.indexOf('value_dataset_') + 14), parseInt(id.indexOf('_category')) - parseInt(id.indexOf('value_dataset_') + 14)));
    }

    return Math.max.apply(Math, datasetIndexes);
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

function getOptionsVerticalBar(formatter, title)  {

    let options = {};
    options = {
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
            mode: 'point'
        }
    };
    return options;
}
function getOptionsHorizontalBar(formatter, title)  {

    let options = {};
    options = {
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
            mode: 'point'
        }
    };
    return options;
}

function getOptionsPie(formatter, title)  {

    let options = {};
    options = {
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
                    return parseFloat(data['datasets'][tooltipItem['datasetIndex']]['data'][tooltipItem['index']]).toLocaleString();
                }
            },
        },
    };
    return options;
}
function getOptionsLine(formatter, title)  {

    let options = {};
    options = {
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
        tooltip: true
    };
    return options;
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