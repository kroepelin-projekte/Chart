
/*insertJSAtPlaceholder: function(cmd_id)
{
    clickcmdid = cmd_id;
    var pl = document.getElementById('CONTENT' + cmd_id);
    pl.style.display = 'none';
    doActionForm('cmd[exec]', 'command', 'insert_par', '', 'PageContent', '');
},*/


(function () {

    function insertJSAlPlaceholder()  {

        clickcmdid = cmd_id;
        var pl = document.getElementById('CONTENT' + cmd_id);
        pl.style.display = 'none';
        doActionForm('cmd[exec]', 'command', 'insert_par', '', 'PageContent', '');
    }

    insertJSAlPlaceholder();
})();