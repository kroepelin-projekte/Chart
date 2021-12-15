var divClass = document.getElementsByClassName('chdiv');
var div = [];
var j = 1;

for (let i = 0; i < divClass.length; i++) {

    let title, type, chId, chDataFormat, chCurrencySymbol, categoryDiv, datasetDiv, datasetCount, datasetValueDiv, colorCategoryDiv, colorDatasetDiv, percDiv, chartDataSet, chartLabels, canVas, chDataTable, thisChart, optionsPie, optionsBar, optionsLine, symbol,
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


    console.log(datasetDiv);


    /**
     * @todo multi Datasets
     */
    chartDataSet = {label:[], data:[], backgroundColor: [], borderColor: []};

    // category
    chartLabels = {labels:[], datasetsTitle:[]};

    // Percent(2)/Currency
    if (chDataFormat === "2") {
        for (let k = 0; k < categoryDiv.length; k++) {
            chartDataSet.data[k] = percDiv[k].value;
        }
        symbol = function(value) {
            return value + ' %';
        };
    } else {
        /*for (let k = 0; k < categoryDiv.length; k++) {
            let parseVal = [];
            parseVal[k] = parseFloat(datasetValueDiv[k].value);
            chartDataSet.data[k] = parseVal[k];
        }*/

        /*let datasetIndexes = [];
        for(let n = 0; n < datasetValueDiv.length; n++){
            let id = datasetValueDiv[n].getAttribute('id');
            datasetIndexes.push(id.substr(parseInt(id.indexOf('value_dataset_') + 14), parseInt(id.indexOf('_category')) - parseInt(id.indexOf('value_dataset_') + 14)));
        }

        datasetLength = Math.max.apply(Math, datasetIndexes);*/



        symbol = function (value) {
            let parseVal = parseFloat(value);
            return parseVal.toLocaleString() + ' ' + chCurrencySymbol;
        };
    }

    for (let k = 0; k < categoryDiv.length; k++) {
        chartLabels.labels[k] = categoryDiv[k].value;
    }

    for (let k = 0; k < datasetDiv.length; k++) {
        chartLabels.datasetsTitle[k] = datasetDiv[k].value;
    }

    let datasetColors = [];
    for (let k = 0; k < colorDatasetDiv.length; k++) {
        datasetColors.push('#' + colorDatasetDiv[k].value);
    }

    // Find count of datasets
    let datasetIndexes = [];
    for(let n = 0; n < datasetValueDiv.length; n++){
        let id = datasetValueDiv[n].getAttribute('id');
        datasetIndexes.push(id.substr(parseInt(id.indexOf('value_dataset_') + 14), parseInt(id.indexOf('_category')) - parseInt(id.indexOf('value_dataset_') + 14)));
    }
    datasetCount = Math.max.apply(Math, datasetIndexes);



    /* S T A R T  I O A N N A */

    let datasetForChart = [];
    let dataDataset = [];
    for(let n = 0; n < datasetCount; n++){

        let dataDatasetTmp = [];
        for(let m = 0; m < datasetValueDiv.length; m++) {

            if (datasetValueDiv[m].getAttribute('id').indexOf('value_dataset_' + (n + 1)) > -1) {
                dataDatasetTmp.push(datasetValueDiv[m].value);
            }
        }

        console.log("DATASETTMP");
        console.log(dataDatasetTmp);
        dataDataset[n] = dataDatasetTmp;

        alert(datasetDiv[n].value);

        if(type === 'horizontalBar' || type === 'bar'){

            datasetForChart[n] = {

                label: datasetDiv[n].value,
                data: dataDataset[n],
                backgroundColor: "#" + colorDatasetDiv[n].value,
                borderColor: '#000000',
                borderWidth: 1,
                barPercentage: 0.8,
                //barThickness: 24,
                minBarLength: 25
            }

        }else if(type === 'pie'){

            datasetForChart[n] = {

                label: datasetDiv[n].value,
                data: dataDataset[n],
                backgroundColor: datasetColors,//'#' + colorDatasetDiv[n].value,
                borderColor: '#000000',
                borderWidth: 1
            }

        }else if(type === 'line'){

            // fill: true,
            //     borderColor: 'rgba(75, 192, 192, 0.2)',
            //alert(datasetDiv[n].value);
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

    /*[{
        label: "Dataset 1",
        data: chartDataSet.data,
        backgroundColor: "blue",
        borderColor: 'blue',
        fill: false,
        tension: 0.0,
    },
        {
            label: "Dataset 2",
            data: chartDataSet.data,
            backgroundColor: "red",
            borderColor: 'red',
            fill: false,
            tension: 0.4,
        },
        {
            label: "Dataset 3",
            data: chartDataSet.data,
            backgroundColor: "green",
            borderColor: 'green',
            fill: false,
            tension: 0.0,
        }]*/

    /*[{
                    label: chartDataSet.label,
                    data: chartDataSet.data,
                    backgroundColor: chartDataSet.backgroundColor,
                    borderColor: '#000000',
                    borderWidth: 2
                },
                {
                    label: chartDataSet.label,
                    data: chartDataSet.data,
                    backgroundColor: chartDataSet.backgroundColor,
                    borderColor: '#000000',
                    borderWidth: 2
                }]*/

    /*datasetForChart = [{
        label: "Dataset 1",
        data: [22, 23],
        backgroundColor: "blue",
        borderColor: '#000000',
        borderWidth: 1,
        barPercentage: 0.8,
        //barThickness: 24,
        minBarLength: 25
    },
        {
            label: "Dataset 2",
            data: [23, 21],
            backgroundColor: "red",
            borderColor: '#000000',
            borderWidth: 1,
            barPercentage: 0.8,
            //barThickness: 24,
            minBarLength: 25
        },
        {
            label: "Dataset 3",
            data: [20, 22],
            backgroundColor: "green",
            borderColor: '#000000',
            borderWidth: 1,
            barPercentage: 0.8,
            //barThickness: 24,
            minBarLength: 25
        }];*/

    console.log(datasetForChart);

    /* E N D  I O A N N A */

    optionsPie = {
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
                formatter: symbol
            }
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
        }
    };
    optionsBar = {
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
                    beginAtZero: true
                }
            }],
            yAxes: [{
                ticks: {
                    beginAtZero: true
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
        tooltip: false
    };
    optionsLine = {
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
                    beginAtZero: true
                }
            }]
        },
        // scales: {
        //     xAxes: [{
        //         stacked: false
        //     }],
        //     yAxes: [{
        //         stacked: false
        //     }]
        // },
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
        tooltip: false
    };

    canVas = document.getElementById(chId).getContext('2d');

    console.log(chartLabels.labels);
    /**
     * @todo create diff datasets
     * @todo only pie backgroundColor: chartDataSet.backgroundColor !!!!
     *
     */
    if (type === 'pie') {

        chDataTable = {
            type: type,
            data: {
                labels: chartLabels.datasetsTitle/*chartLabels.labels*/,
                datasets: datasetForChart/*[{
                    label: chartDataSet.label,
                    data: chartDataSet.data,
                    backgroundColor: chartDataSet.backgroundColor,
                    borderColor: '#000000',
                    borderWidth: 2
                },
                {
                    label: chartDataSet.label,
                    data: chartDataSet.data,
                    backgroundColor: chartDataSet.backgroundColor,
                    borderColor: '#000000',
                    borderWidth: 2
                }]*/
            },
            options: optionsPie
        };

        thisChart = new Chart(canVas, chDataTable);

    }else if (type === 'line'){

        chDataTable = {
            type: type,
            data: {
                labels: chartLabels.labels,
                datasets: datasetForChart/*[{
                    label: "Dataset 1",
                    data: chartDataSet.data,
                    backgroundColor: "blue",
                    borderColor: 'blue',
                    fill: false,
                    tension: 0.0,
                },
                    {
                        label: "Dataset 2",
                        data: chartDataSet.data,
                        backgroundColor: "red",
                        borderColor: 'red',
                        fill: false,
                        tension: 0.4,
                    },
                    {
                        label: "Dataset 3",
                        data: chartDataSet.data,
                        backgroundColor: "green",
                        borderColor: 'green',
                        fill: false,
                        tension: 0.0,
                    }]*/
            },
            options: optionsLine
        };

        thisChart = new Chart(canVas, chDataTable);

    }else {
        chDataTable = {
            type: type,
            data: {
                labels: chartLabels.labels,
                datasets: datasetForChart/*[{
                    label: "Dataset 1",
                    data: chartDataSet.data,
                    backgroundColor: "blue",
                    borderColor: '#000000',
                    borderWidth: 1,
                    barPercentage: 0.8,
                    //barThickness: 24,
                    minBarLength: 25
                },
                    {
                        label: "Dataset 2",
                        data: chartDataSet.data,
                        backgroundColor: "red",
                        borderColor: '#000000',
                        borderWidth: 1,
                        barPercentage: 0.8,
                        //barThickness: 24,
                        minBarLength: 25
                    },
                    {
                        label: "Dataset 3",
                        data: chartDataSet.data,
                        backgroundColor: "green",
                        borderColor: '#000000',
                        borderWidth: 1,
                        barPercentage: 0.8,
                        //barThickness: 24,
                        minBarLength: 25
                    }]*/
            },
            options: optionsBar
        };

        thisChart = new Chart(canVas, chDataTable);
    }
    j++;
}

