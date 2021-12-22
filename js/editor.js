window.onload = function() {

    let containers = ['il_prop_cont_chart_title_slate', 'il_prop_cont_chart_type_slate', 'il_prop_cont_data_format_slate', 'il_prop_cont_currency_symbol_slate'];

    for(let i = 0; i < containers.length; i++){

        let containerInputGroup = document.getElementById(containers[i]);
        let label = containerInputGroup.getElementsByTagName("label").item(0);
        textAlign(label, 'left');
        changeClassName(label, 'col-sm-3', 'col-sm-12');
    }

    changeClassName(document.getElementById('il_prop_cont_currency_symbol_slate').querySelector('div:nth-child(2)'), 'col-sm-9', 'col-sm-12');
    setValue(document.getElementById('chart_title_slate'), document.getElementById('chart_title'));

    if(document.getElementById('chart_type').value === ''){
        document.getElementById('chart_type').value = '1';
    }
    setValue(document.getElementById('chart_type_slate'), document.getElementById('chart_type'));
    setValue(document.getElementById('currency_symbol_slate'), document.getElementById('currency_symbol'));

    if(document.getElementById('data_format').value === ''){
        document.getElementById('data_format').value = '1';
    }

    if(document.getElementById('data_format').value === '1') {
        setChecked(document.getElementById('data_format_slate_1'), true);
    }else if(document.getElementById('data_format').value === '2') {
        setChecked(document.getElementById('data_format_slate_2'), true);
    }

    let form = document.getElementById('il_prop_cont_chart_title_slate').parentNode.parentNode;

    document.getElementById("chart_title_slate").addEventListener("keyup", function(){
        document.getElementById('chart_title').value = getValue(document.getElementById("chart_title_slate"));
    });

    document.getElementById("chart_type_slate").addEventListener("change", function(){
        document.getElementById('chart_type').value = getValue(document.getElementById("chart_type_slate"));
    });

    document.getElementById('data_format_slate_1').addEventListener("click", function(){
        document.getElementById('data_format').value = getValue(document.getElementById('data_format_slate_1'));
    });

    document.getElementById('data_format_slate_2').addEventListener("click", function(){
        document.getElementById('data_format').value = getValue(document.getElementById('data_format_slate_2'));
    });

    document.getElementById('currency_symbol_slate').addEventListener("keyup", function(){
        document.getElementById('currency_symbol').value = getValue(document.getElementById('currency_symbol_slate'));
    });
};

function textAlign(sel, position) {
    sel.style.textAlign = position;
}

function changeClassName(sel, oldClassName, newClassName) {
    sel.classList.remove(oldClassName);
    sel.classList.add(newClassName);
}

function getValue(sel)  {
    return sel.value;
}

function setValue(selSlate, selGUI)  {
    selSlate.value = selGUI.value;
}

function setChecked(selSlate, checked)  {
    selSlate.setAttribute('checked', checked);
}
