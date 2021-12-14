var divClass = document.getElementsByClassName('chdiv');
var div = [];
var j = 1;

for (let i = 0; i< divClass.length; i++) {

    let title, type, chId, chDataFormat, chCurrencySymbol, categoryDiv, datasetDiv, datasetValueDiv, colorCategoryDiv, colorDatasetDiv, percDiv, chartDataSet, chartLabels, canVas, chDataTable, thisChart, optionsPie, optionsBar, optionsLine, symbol,
    object = {};

    div[i] = document.getElementById('chart_div_'+j).children;


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

    // category1
    chartLabels = {labels:[]};

    // Percent(2)/Currency
    if (chDataFormat === "2") {
        for (let k = 0; k < categoryDiv.length; k++) {
            chartDataSet.data[k] = percDiv[k].value;
        }
        symbol = function(value) {
            return value + ' %';
        };
    } else {
        for (let k = 0; k < categoryDiv.length; k++) {
            let parseVal = [];
            parseVal[k] = parseFloat(datasetValueDiv[k].value);
            chartDataSet.data[k] = parseVal[k];
        }

        symbol = function (value) {
            let parseVal = parseFloat(value);
            return parseVal.toLocaleString() + ' ' + chCurrencySymbol;
        };
    }

    for (let k = 0; k < categoryDiv.length; k++) {
        chartLabels.labels[k] = categoryDiv[k].value;
    }




    /* START IOANNA */
    let allCharts = {};
    let chart = {category:[], dataset:[], datasetValues: [], backgroundColorDataset: [], borderColorDataset: []};

    let datasetValues = {};
    for (let k = 0; k < categoryDiv.length; k++) {
        chart.category[k] = categoryDiv[k].value;

        datasetValues[k] = {
            'category' : k
        };
    }

    for (let k = 0; k < datasetDiv.length; k++) {
        chart.dataset[k] = datasetDiv[k].value;
    }


    // Set dataset values pro category
    let catDatasetValues = [];
    /*for (let n = 0; n < categoryDiv.length; n++) {

        let tmpDatasetValues = [];

        for (let k = 0; k < datasetValueDiv.length; k++) {
            if(datasetValueDiv[k].id.indexOf('category_' + (n+1)) > -1){

                tmpDatasetValues.push(datasetValueDiv[k].value);
            }
        }
        catDatasetValues[n] = tmpDatasetValues;
    }*/

    for (let n = 0; n < datasetValueDiv.length; n++) {

        console.log(datasetValueDiv[n].id);

        let tmpDatasetValues = [];

        for (let k = 0; k < categoryDiv.length; k++) {

            if(datasetValueDiv[n].id.indexOf('category_' + (k+1)) > -1){

                tmpDatasetValues[k] = datasetValueDiv[n].value;


            }
        }
        catDatasetValues[n] = tmpDatasetValues;
    }

    /*console.log('CAT');
    console.log(catDatasetValues);*/
    chart.datasetValues = catDatasetValues;

    for (let k = 0; k < categoryDiv.length; k++) {

    }



    /* END IOANNA */






    for (let k = 0; k < colorCategoryDiv.length; k++) {
        chartDataSet.backgroundColor[k] = "#" + colorCategoryDiv[k].value;
        chartDataSet.borderColor[k] = "#" + colorCategoryDiv[k].value;
    }




    /* START IOANNA */
    for (let k = 0; k < colorDatasetDiv.length; k++) {
        chart.backgroundColorDataset[k] = "#" + colorDatasetDiv[k].value;
        chart.borderColorDataset[k] = "#" + colorDatasetDiv[k].value;
    }

  /*  console.log("CHART");
    console.log(chart);*/



    let datasetForChart = [];
    /*for (let k = 0; k < chart.category.length; k++) {

        object[k] = {
            label : chart.category[k],
            data : chart.datasetValues[k],
            backgroundColor : chart.backgroundColorDataset[k],
            borderColor : chart.borderColorDataset,
            borderWidth: 2,
            barPercentage: 0.8,
            //barThickness: 24,
            minBarLength: 25
        }
        datasetForChart.push(object[k]);
    }*/

  /*  alert(chart.datasetValues.length);
    alert(chart.dataset.length);*/

    //let j = 0;
    for (let k = 0; k < chart.dataset.length; k++) {

        object[k] = {
            label : chart.dataset[k],
            data : chart.datasetValues[k],
            backgroundColor : chart.backgroundColorDataset[k],
            borderColor : chart.borderColorDataset,
            borderWidth: 2,
            barPercentage: 0.8,
            //barThickness: 24,
            minBarLength: 25
        }
        datasetForChart.push(object[k]);
        //j = j + 1;
    }

    console.log(datasetForChart);
    //console.log(object);

    /* END IOANNA */


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
                stacked: false
            }],
            yAxes: [{
                stacked: false
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
                clamp: true,
                clip: true,
                display: 'auto',
                formatter: symbol
            }
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

    /**
     * @todo create diff datasets
     * @todo only pie backgroundColor: chartDataSet.backgroundColor !!!!
     *
     */
    if (type === 'pie') {
        chDataTable = {
            type: type,
            data: {
                labels: chartLabels.labels,
                datasets: [{
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
                }]
            },
            options: optionsPie
        };

        thisChart = new Chart(canVas, chDataTable);
    }else if (type === 'line'){
        chDataTable = {
            type: type,
            data: {
                labels: chartLabels.labels,
                datasets: [{
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
                    }]
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


/*var divClass = document.getElementsByClassName('chdiv');
var div = [];
var j = 1;

for (let i = 0; i< divClass.length; i++) {
    let title, type, chId, chDataFormat, chCurrencySymbol, keyDiv, valueDiv, colorDiv, percDiv, chartDataSet, chartLabels, canVas, chDataTable, thisChart, optionsPie, optionsBar, optionsLine, symbol;

    div[i] = document.getElementById('chart_div_'+j).children;
    
    title = div[i].chart_title.value;
    type = div[i].chart_type.value;
    chId = div[i].chart_id.value;
    chDataFormat = div[i].chart_data_format.value;
    chCurrencySymbol = div[i].chart_currency_symbol.value;
    keyDiv = div[i].div_title_category.children;
    valueDiv = div[i].div_value_dataset.children;
    colorDiv = div[i].div_color.children;
    percDiv = div[i].div_percent.children;
    chartDataSet = {label:[],data:[],backgroundColor: [],borderColor: [],borderWidth: 1};
    chartLabels = {labels:[]};

    if (chDataFormat === "2") {
        for (let k = 0; k<keyDiv.length; k++) {
            chartDataSet.data[k] = percDiv[k].value;
        }
        symbol = function (value) {
                    return value + ' %';
                };
    } else {
        for (let k = 0; k<keyDiv.length; k++) {
            chartDataSet.data[k] = valueDiv[k].value;
        }
        symbol = function (value) {
                    return chCurrencySymbol+ ' ' + value;
                };
    }
    
    for (let k = 0; k<keyDiv.length; k++) {
        chartLabels.labels[k] = keyDiv[k].value;
    }
    
    for (let k = 0; k < colorDiv.length; k++) {
        chartDataSet.backgroundColor[k] = "#" + colorDiv[k].value;
        chartDataSet.borderColor[k] = "#" + colorDiv[k].value;
    }

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
        maintainAspectRatio: true,
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
                  size: 13,
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
            stacked: true
            }],
            yAxes: [{
            stacked: true
            }]
        },
        responsive: true,
        maintainAspectRatio: true,
        legend: {
            display: false
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
        maintainAspectRatio: true,
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

    };

    canVas = document.getElementById(chId).getContext('2d');

    if (type === 'pie') {
    chDataTable = {
            type: type,
            data: {
                labels: chartLabels.labels,
                datasets: [{
                    label: chartDataSet.label,
                    data: chartDataSet.data,
                    backgroundColor: chartDataSet.backgroundColor,
                    borderColor: '#000000',
                    borderWidth: 1
                }]
            },
            options: optionsPie
        };

        thisChart = new Chart(canVas, chDataTable);

    }else if (type === 'line') {
        chDataTable = {
            type: type,
            data: {
                labels: chartLabels.labels,
                datasets: [{
                    label: chartDataSet.label,
                    data: chartDataSet.data,
                    backgroundColor: chartDataSet.backgroundColor,
                    borderColor: '#000000',
                    borderWidth: 1
                }]
            },
            options: optionsLine
        };

        thisChart = new Chart(canVas, chDataTable);
    } else {
        chDataTable = {
            type: type,
            data: {
                labels: chartLabels.labels,
                datasets: [{
                    label: chartDataSet.label,
                    data: chartDataSet.data,
                    backgroundColor: chartDataSet.backgroundColor,
                    borderColor: '#000000',
                    borderWidth: 1,
                    categoryPercentage: 0.5
                }]
            },
            options: optionsBar
        };

        thisChart = new Chart(canVas, chDataTable);
    }
    j++;
}*/

