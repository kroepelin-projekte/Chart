window.onload = function() {


    /*let inputs = document.getElementsByTagName("INPUT");

    let title = "";
    for(let i = 0; i < inputs.length; i++){

        //alert(i);
        //alert(inputs[i].getAttribute("class"));

        if(inputs[i].id === "chart_title_slate"){

            title = inputs[i].value;
        }
    }
    document.getElementById("chart_title").value = title;

    alert(document.getElementById("chart_title").value);*/

    let containers = ['il_prop_cont_chart_title_slate', 'il_prop_cont_chart_type', 'il_prop_cont_data_format', 'il_prop_cont_currency_symbol'];

    for(let i = 0; i < containers.length; i++){

        let containerInputGroup = document.getElementById(containers[i]);
        let label = containerInputGroup.getElementsByTagName("label").item(0);
        textAlign(label, 'left');
        changeClassName(label, 'col-sm-3', 'col-sm-12');
    }

    changeClassName(document.getElementById('il_prop_cont_currency_symbol').querySelector('div:nth-child(2)'), 'col-sm-9', 'col-sm-12');


    let form = document.getElementById('il_prop_cont_chart_title_slate').parentNode;
    let submitButton = form.getElementsByClassName('btn').item(0);

    submitButton.addEventListener("click", function(e) {

        e.preventDefault;
    });


    /*
    let label = containerTitleSlate.getElementsByTagName("label").item(0);
    alignLeft(label);
    changeClassName(label, 'col-sm-3', 'col-sm-12');
    for(let i = 0; i < labels.length; i++){

        alert(labels[i].getAttribute("class"));
    }*/


};





function textAlign(sel, position) {
    sel.style.textAlign = position;
}

function changeClassName(sel, oldClassName, newClassName) {
    sel.classList.remove(oldClassName);
    sel.classList.add(newClassName);
}

/*
(function () {

    function insertJSAlPlaceholder()  {

        clickcmdid = cmd_id;
        var pl = document.getElementById('CONTENT' + cmd_id);
        pl.style.display = 'none';
        doActionForm('cmd[exec]', 'command', 'insert_par', '', 'PageContent', '');
    }

    insertJSAlPlaceholder();


})();*/
