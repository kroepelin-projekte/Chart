window.onload = function() {

    let containers = ['il_prop_cont_chart_title_slate', 'il_prop_cont_chart_type_slate', 'il_prop_cont_data_format_slate', 'il_prop_cont_currency_symbol_slate'];
    let inputsSlate =  ['chart_title_slate', 'chart_type_slate', 'data_format_slate_1', 'data_format_slate_2', 'currency_symbol_slate'];

    for(let i = 0; i < containers.length; i++){

        let containerInputGroup = document.getElementById(containers[i]);
        let label = containerInputGroup.getElementsByTagName("label").item(0);
        textAlign(label, 'left');
        changeClassName(label, 'col-sm-3', 'col-sm-12');
    }

    changeClassName(document.getElementById('il_prop_cont_currency_symbol_slate').querySelector('div:nth-child(2)'), 'col-sm-9', 'col-sm-12');

    // TODO eventually to be deleted
    //document.getElementById('il_prop_cont_chart_title_slate').parentNode.querySelector('input[type="submit"]').setAttribute('name', '');



    setValue(document.getElementById('chart_title_slate'), document.getElementById('chart_title'));
    setValue(document.getElementById('chart_type_slate'), document.getElementById('chart_type'));
    setValue(document.getElementById('currency_symbol_slate'), document.getElementById('currency_symbol'));

    if(document.getElementById('data_format').value === '1') {
        setChecked(document.getElementById('data_format_slate_1'), true);
    }else if(document.getElementById('data_format').value === '2') {
        setChecked(document.getElementById('data_format_slate_2'), true);
    }

    let form = document.getElementById('il_prop_cont_chart_title_slate').parentNode.parentNode;
    //let btnSave = form.getElementsByClassName('ilFormCmds').item(1).getElementsByClassName('btn').item(1);

    /*console.log('CLASSNAME' + form.getElementsByClassName('form-horizontal')[0].getElementsByClassName('ilFormCmds')[0].getElementsByClassName('btn')[0]);
*/
    let btnSave = form.getElementsByClassName('form-horizontal')[0].getElementsByClassName('ilFormCmds')[0].getElementsByClassName('btn')[0]

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

   /* console.log(document.getElementById('il_center_col'));

    console.log(document.getElementById('il_center_col').length);

    document.getElementById('il_center_col').getElementById('form_').style.border = '2px solid red';
    document.getElementById('form_')[5].addEventListener("click", function(){

        alert("OK");

    });
*/

    //btnSave.addEventListener("click", function(){

      //  alert("OK");

        /*$.ajax({
            type: 'POST',
            url: 'ilias.php?ref_id=84&type=copa&item_ref_id=84&hier_id=2&pc_id=0e6117c054458266b8abc2fb5c756ce1&cmd=testAjax&cmdClass=ilchartplugingui&cmdNode=vv:l4:80:s7:tf:5l&baseClass=ilrepositorygui',
            success: function(data) {
                alert(data);
            }
        });*/

       /* var cb =
            {
                success: this.asynchSuccess,
                failure: this.asynchFailure,
                argument: {}
            };

        if (this.callback_url != null)
        {
            var request = YAHOO.util.Connect.asyncRequest('POST', this.callback_url, cb,
                "id=" + id + "&type=TestType&answer=TestAnswer");
        }

        return false;*/

        //e.preventDefault();

        //let btnSave = document.querySelector('input[name="cmd[update]"]');

        /*let hiddenTitle = document.getElementById('chart_title');

        //hiddenTitle.parentNode.parentNode.parentNode.parentNode.submit();

        let form = hiddenTitle.parentNode.parentNode.parentNode.parentNode;

        //form.submit();
        let cmds = form.getElementsByClassName('ilFormCmds');

        var ilCOPageCallback =
            {
                success: 'test',
                failure: 'testFailure',
                argument: { mode: mode}
            };
        var form_str = YAHOO.util.Connect.setForm("ajaxform");
        var request = YAHOO.util.Connect.asyncRequest('POST', 'testUrl', ilCOPageCallback);

        return false;*/

        /*alert(cmds.getAttribute('class'));
        alert(cmds.getElementsByClassName('btn')[0].getAttribute('class'));//value);*/

        /*alert(cmds.length);
        console.log(cmds[0]);*/
        /*cmds[0].style.border = '2px solid red';
        cmds[0].getElementsByClassName('btn')[0].style.color = 'yellow';

        //cmds[0].getElementsByClassName('btn')[0].click();


        let evt = document.createEvent('MouseEvents')
        evt.initMouseEvent('mousedown', true, false, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
        cmds[0].getElementsByClassName('btn')[0].dispatchEvent(evt);

        cmds[0].getElementsByClassName('btn')[0].fireEvent(event)*/

        /*var event; // The custom event that will be created
        if(document.createEvent){
            event = document.createEvent("HTMLEvents");
            event.initEvent("dataavailable", true, true);
            event.eventName = "dataavailable";
            element.dispatchEvent(event);
        } else {
            event = document.createEventObject();
            event.eventName = "dataavailable";
            event.eventType = "dataavailable";
            element.fireEvent("on" + event.eventType, event);
        }*/
        //click(cmds[0].getElementsByClassName('btn')[0]);

        //form.getElementsByClassName('ilFormCmds').item(1).querySelector('input[name="cmd[update]"]').click();
    //});


    /*
    let label = containerTitleSlate.getElementsByTagName("label").item(0);
    alignLeft(label);
    changeClassName(label, 'col-sm-3', 'col-sm-12');
    for(let i = 0; i < labels.length; i++){

        alert(labels[i].getAttribute("class"));
    }*/


};


// handle asynchronous request (success)
function asynchSuccess(o){
    if (ilCOPageQuestionHandler.success_handler != null) {
        ilCOPageQuestionHandler.success_handler();
    }
}

// Success Handler
function asynchFailure(o)
{
}


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

function click(node) {
    /*let evt = document.createEvent('MouseEvents');
    evt.initMouseEvent('mousedown', true, false, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
    node.dispatchEvent(evt);

    node.click();*/

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
