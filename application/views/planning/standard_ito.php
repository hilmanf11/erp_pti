<table id="dg" class="easyui-datagrid" style="width:100%;"></table>
<div id="toolbar">
    <fieldset style="width: 100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius: 4px;">
        <legend><b>Form Filter Data</b></legend>
        <div style="width: 30%; float: left;">
            <div class="fitem">
                <span style="width: 20%; display: inline-block;">Product Family</span>
                <input style="width: 60%;" id="f_prodfam" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width: 20%; display: inline-block;">Maker Type</span>
                <input style="width: 60%;" id="f_maker" class="easyui-combobox">
                <a style="margin-left: 10px;" href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter</a>
            </div>
        </div>
    </fieldset>
    <?= $button ?>
</div>

<div id="dlg_form" class="easyui-dialog" style="padding: 5px;">
    <table>
        <tr>
            <td>Product Family</td>
            <td rowspan="4">&nbsp;&nbsp;</td>
            <td><input id="prodfam" class="easyui-combobox" required></td>
        </tr>
        <tr>
            <td>Maker Type</td>
            <td><input id="maker" class="easyui-combobox" required></td>
        </tr>
        <tr>
            <td>MIN</td>
            <td><input id="std_min" class="easyui-numberbox" required></td>
        </tr>
        <tr>
            <td>MAX</td>
            <td><input id="std_max" class="easyui-numberbox" required></td>
        </tr>
    </table>
</div>

<!-- Upload -->
<div id="dlg_upload" class="easyui-dialog" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_upload" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">File Upload</span>
                <input name="file_upload" style="width: 60%;" required="" accept=".xlsx" id="file_excel" class="easyui-filebox">
            </div>
        </fieldset>
    </form>
    <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
    <div id="p_remarks" class="easyui-panel" style="width:100%; height:200px; padding:10px; margin-top: 10px;">
        <p>History Upload</p>
        <ul id="remarks"></ul>
    </div>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= $base_url.'datatables/print' ?>" style="width: 100%;" hidden></iframe>

<script>
    var act_form = '';
    $(function() {
        $("#f_prodfam").combobox({
            url: "<?= $base_url.'filterProdfam' ?>",
            prompt: "ALL",
            textField: "name",
            valueField: "number",
            icons:[{
                iconCls:'icon-clear',
                handler: function(e){ $(e.data.target).combobox('clear'); }
            }]
        });
        $("#f_maker").combobox({
            url: "<?= $base_url.'filterMaker' ?>",
            prompt: "ALL",
            textField: "name",
            valueField: "number",
            icons:[{
                iconCls:'icon-clear',
                handler: function(e){ $(e.data.target).combobox('clear'); }
            }]
        });

        $("#dg").datagrid({
            url: "<?= $base_url.'datatables' ?>",
            toolbar: '#toolbar',
            remoteFilter: true,
            pagination: true,
            rownumbers: true,
            fit: true,
            columns: [[
                {field: 'prodfam', title: 'Product Family', width: '20%', halign: 'center', rowspan: 2},
                {field: 'maker', title: 'Maker Type', width: '20%', halign: 'center', rowspan: 2},
                {title: 'Standard ITO', halign: 'center', colspan: 2},
            ],[
                {field: 'std_min', title: 'MIN', width: '10%', halign: 'center', align: 'right'},
                {field: 'std_max', title: 'MAX', width: '10%', halign: 'center', align: 'right'},
            ]],
            onLoadSuccess: function (data) {
                var prodfam = btoa($("#f_prodfam").combobox("getValue"));
                var maker = btoa($("#f_maker").combobox("getValue"));
                $("#printout").attr('src', "<?= $base_url.'datatables/print' ?>"
                    +"?prodfam="+prodfam
                    +"&maker="+maker
                );
            }
        }).datagrid("enableFilter");

        $("#dlg_form").dialog({
            modal: true,
            closed: true,
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    var prodfam = btoa($("#prodfam").combobox("getValue"));
                    var maker = btoa($("#maker").combobox("getValue"));
                    var min = parseFloat($("#std_min").numberbox("getValue"));
                    var max = parseFloat($("#std_max").numberbox("getValue"));
                    var std_min = btoa(min);
                    var std_max = btoa(max);

                    if ([prodfam, maker, std_min, std_max].includes(''))
                        { toastr.warning("Please fill in all fields!","WARNING"); }
                    else if (max < min) { toastr.warning("MAX is more than MIN!","WARNING"); }
                    else {
                        var act = 'create';
                        var data = {
                            prodfam: prodfam,
                            maker: maker,
                            std_min: std_min,
                            std_max: std_max
                        };

                        if (act_form != '') {
                            act = 'update';
                            data.id = act_form;
                        }

                        getprocess(act, data);
                    }
                }
            }]
        });

        $("#prodfam").combobox({
            url: "<?= $base_url.'prodfam' ?>",
            textField: "name",
            valueField: "number"
        });
        $("#maker").combobox({
            url: "<?= $base_url.'maker' ?>",
            textField: "name",
            valueField: "number"
        });
        $("#std_min").numberbox({precision:0});
        $("#std_max").numberbox({precision:0});

        $("#dlg_upload").dialog({
            title: 'Upload Data',
            closed: true,
            modal: true,
            buttons: [{
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $("#frm_upload").form('submit', {
                        url: "<?= $base_url.'uploadTemplate' ?>",
                        onSubmit: function() {
                            if ($(this).form('validate') == false) {
                                return $(this).form('validate');
                            } else {
                                $.messager.progress({
                                    title: 'Please Wait',
                                    msg: 'Importing Excel to Database'
                                });
                            }
                        },
                        success: function(result) {
                            var json = JSON.parse(result);

                            $.messager.progress('close');
                            if (json.stat != 'ok') { toastr.warning(json.stat, 'Warning'); }
                            else {
                                dataUpload = json.data;
                                $("#p_remarks").html('');
                                uploadProcess();
                            }
                        }
                    });
                }
            }]
        });
    });

    function filter() {
        var f_prodfam = btoa($("#f_prodfam").combobox("getValue"));
        var f_maker = btoa($("#f_maker").combobox("getValue"));

        $("#dg").datagrid("reload", {prodfam: f_prodfam, maker: f_maker});
    }

    function add() {
        act_form = '';
        $("#prodfam").combobox("clear").combobox("enable");
        $("#maker").combobox("clear").combobox("enable");
        $("#std_min").numberbox("clear");
        $("#std_max").numberbox("clear");
        $("#dlg_form").dialog('setTitle', 'Add New').dialog('open');
    }

    function update() {
        var dg = $("#dg").datagrid('getSelections');
        if (dg.length == 0 || dg.length > 1)
            { toastr.warning("Please select one of the data in the table first!", "Information"); }
        else {
            var row = dg[0];
            act_form = btoa(row.id);
            $("#prodfam").combobox("setValue", row.pfm_id).combobox("disable");
            $("#maker").combobox("setValue", row.maker_number).combobox("disable");
            $("#std_min").numberbox("setValue", row.std_min);
            $("#std_max").numberbox("setValue", row.std_max);
            $("#dlg_form").dialog('setTitle', 'Update Data').dialog('open');
        }
    }

    function deleted() {
        var rows = $("#dg").datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    $("#dg").datagrid('loading');
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        $.ajax({
                            method: 'post',
                            url: "<?= $base_url.'delete' ?>",
                            data: {id: row.id},
                            success: function(result) { var result = eval('(' + result + ')'); },
                            error: function(jqXHR, textStatus, errorThrown) {
                                toastr.error(jqXHR.statusText);
                                $.messager.alert("Error", jqXHR.statusText, 'error');
                            },
                            complete: function(data) { $("#dg").datagrid('reload').datagrid('loaded'); }
                        });
                    }
                }
            });
        } else { toastr.warning("Please select one of the data in the table first!", "Information"); }
    }

    function upload() { $("#dlg_upload").dialog('open'); }

    function uploadProcess(index = 0) { getprocess('templateProcess', dataUpload[index]); }

    function download_excel() { window.location.assign("<?= $base_url.'template' ?>"); }

    function pdf() { $("#printout").get(0).contentWindow.print(); }

    function excel() {
        var prodfam = btoa($("#f_prodfam").combobox("getValue"));
        var maker = btoa($("#f_maker").combobox("getValue"));
        window.location.assign("<?= $base_url.'excel' ?>"
            +"?prodfam="+prodfam
            +"&maker="+maker
        );
    }

    function reload() { window.location.reload(); }

    function getprocess(act, data) {
        $("#dg").datagrid('loading');
        $.ajax({
            url: "<?= $base_url ?>"+act,
            data: data,
            async: false,
            method: 'POST',
            dataType: 'json',
            success: function (result) {
                if (['create', 'update', 'delete'].includes(act)) {
                    if (result.stat != '') { toastr.error(result.stat,"FAILED"); }
                    else {
                        if (['create', 'update'].includes(act))
                            { $("#dlg_form").dialog("close"); }
                        filter();
                    }
                }
                $("#dg").datagrid('loaded');
            }
        });
    }
</script>