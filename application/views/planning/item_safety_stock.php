<table id="dg" class="easyui-datagrid" style="width:100%;"></table>
<div id="toolbar"><?= $button ?></div>

<div id="dlg_form" class="easyui-dialog" style="width: 350px;">
    <div style="width: 98%; padding: 2px;">
        <div class="fitem">
            <span style="width: 35%; display: inline-block;">Item ID</span>
            <input style="width: 200px;" id="item" class="easyui-combobox" required>
        </div>
        <div class="fitem">
            <span style="width: 35%; display: inline-block;">Safety Stock</span>
            <input style="width: 200px;" id="safety" class="easyui-numberbox" required>
        </div>
    </div>
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
    var id_update = '';
    var dataUpload = [];
    $(function() {
        $('#dg').datagrid({
            url: "<?= $base_url.'datatables' ?>",
            toolbar: '#toolbar',
            remoteFilter: true,
            pagination: true,
            rownumbers: true,
            fit: true,
            columns: [[
                {field: 'item', title: 'Item ID', halign: 'center', width: '10%'},
                {
                    field: 'safety', title: 'Safety Stock', halign: 'center', align: 'center', width: '10%',
                    formatter: function(value, row, index) { return value+'%'; }
                },
            ]],
            onLoadSuccess: function () {
                $("#printout").attr('src', "<?= $base_url.'datatables/print' ?>");
            }
        });

        $('#dlg_form').dialog({
            modal: true,
            closed: true,
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    var item = $('#item').combobox('getValue');
                    var safety = $('#safety').numberbox('getValue');
                    var data_items = $('#item').combobox('getData');

                    if ([item, safety].includes('')) { toastr.warning('Please fill in all fields!', 'Information'); }
                    else if (!data_items.some(field => field.item == item) && id_update == '')
                        { toastr.warning('Item ID not found!', 'Information'); }
                    else {
                        var data = {item: btoa(item), safety: btoa(safety)};
                        if (id_update != '') { data.id = id_update; }
                        getprocess(((id_update == '') ? 'create' : 'update'), data);
                    }
                }
            }],
            onOpen: function () { $('#item').combobox('reload'); }
        });

        $('#item').combobox({
            url: "<?= $base_url.'getItems' ?>",
            valueField: 'item',
            textField: 'item',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) { $(e.data.target).combobox('clear').combobox('textbox').focus(); }
            }],
        });
        $('#safety').numberbox({
            min: 0,
            max: 100,
            precision: 0,
            suffix: '%',
            onFocus: function(){ $(this).numberbox('textbox').select(); },
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) { $(e.data.target).numberbox('clear').numberbox('textbox').focus(); }
            }],
        });

        $('#dlg_upload').dialog({
            title: 'Upload Data',
            closed: true,
            modal: true,
            buttons: [{
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
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

    function add() {
        id_update = '';
        $('#item').combobox('clear').combobox('enable');
        $('#safety').numberbox('clear');
        $('#dlg_form').dialog('setTitle', 'Add New').dialog('open');
    }

    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            id_update = btoa(row.id);
            $('#item').combobox('setValue', row.item)
                .combobox('setText', row.item)
                .combobox('disable');
            $('#safety').numberbox('setValue', row.safety);
            $('#dlg_form').dialog('setTitle', 'Update Data').dialog('open');
        } else { toastr.warning("Please select one of the data in the table first!", "Information"); }
    }

    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    $('#dg').datagrid('loading');
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        $.ajax({
                            method: 'post',
                            url: "<?= $base_url.'delete' ?>",
                            data: {id: btoa(row.id)},
                            success: function(result) { var result = eval('(' + result + ')'); },
                            error: function(jqXHR, textStatus, errorThrown) {
                                toastr.error(jqXHR.statusText);
                                $.messager.alert("Error", jqXHR.statusText, 'error');
                            },
                            complete: function(data) { $('#dg').datagrid('reload').datagrid('loaded'); }
                        });
                    }
                }
            });
        } else { toastr.warning("Please select one of the data in the table first!", "Information"); }
    }

    function upload() { $('#dlg_upload').dialog('open'); }

    function uploadProcess(index = 0) { getprocess('templateProcess', dataUpload[index]); }

    function download_excel() {
        $.messager.alert('Download Template','Please wait...','info');
        window.location.assign("<?= $base_url.'template' ?>");
    }

    function pdf() { $("#printout").get(0).contentWindow.print(); }

    function excel() { window.location.assign("<?= $base_url.'excel' ?>"); }

    function reload() { window.location.reload(); }

    function getprocess(act, data) {
        $('#dg').datagrid('loading');
        $.ajax({
            url: "<?= $base_url ?>"+act,
            data: data,
            async: false,
            method: 'POST',
            dataType: 'json',
            success: function (result) {
                if (['create','update'].includes(act)) {
                    if (result.stat != '') { toastr.error('Save failed!', 'Information'); }
                    else {
                        toastr.success('Saved successfully!', 'Information');
                        $('#dlg_form').dialog('close');
                        $('#dg').datagrid('reload');
                    }
                } else if (act == 'templateProcess') {
                    var next = dataUpload.indexOf(data) + 1;
                    var total = dataUpload.length;

                    $("#p_remarks").append(
                        ((next == 1) ? "" : "<br>") +
                        "<b style='color: " + ((result.theme == "success") ? 'green' : 'red') + ";'>" + result.title + "</b> | " + result.message
                    );
                    $('#p_upload').progressbar('setValue', Math.floor((next / total) * 100));
                    $('#p_finish').html(total);
                    $('#p_start').html(next);

                    if (next <= (total - 1)) { uploadProcess(next); }
                    else { $('#dg').datagrid('reload'); }
                }
                $('#dg').datagrid('loaded');
            }
        });
    }
</script>