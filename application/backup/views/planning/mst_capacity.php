<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'process_main_name',width:150,halign:'center'">Main Process</th>
            <th rowspan="2" data-options="field:'process_sub_name',width:150,halign:'center'">Sub Main Process</th>
            <th rowspan="2" data-options="field:'manpower',width:150,align:'center'">Man Power Exist</th>
            <th rowspan="2" data-options="field:'efficiency',width:150,align:'center'">% Effieciency</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 195px; padding: 10px;">
    <fieldset style="width:40%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
        <legend><b>Form Filter Data</b></legend>
        <div class="fitem">
            <span style="width:35%; display:inline-block;">Main Process</span>
            <input style="width:60%;" name="filter_process_main" id="filter_process_main" class="easyui-combobox">
        </div>
        <div class="fitem">
            <span style="width:35%; display:inline-block;">Sub Main Process</span>
            <input style="width:60%;" name="filter_process_sub" id="filter_process_sub" class="easyui-combobox">
        </div>
        <div class="fitem">
            <span style="width:35%; display:inline-block;"></span>
            <a href="javascript:300px;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
        </div>
    </fieldset>
    <?= $button ?>
</div>

<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Main Process</span>
                <input style="width:60%;" name="process_main_number" id="process_main_number" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Sub Main Process</span>
                <input style="width:60%;" name="process_sub_number" id="process_sub_number" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Man Power Exist</span>
                <input style="width:30%;" name="manpower" required="" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Effieciency (%)</span>
                <input style="width:30%;" name="efficiency" required="" class="easyui-numberbox">
            </div>
        </fieldset>
    </form>
</div>

<!-- Upload -->
<div id="dlg_upload" class="easyui-dialog" title="Upload Data" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_upload" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">File Upload</span>
                <input name="file_upload" style="width: 60%;" required="" accept=".xls" id="file_excel" class="easyui-filebox">
            </div>
        </fieldset>
    </form>
    <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
    <div id="p_remarks" class="easyui-panel" style="width:100%; height:200px; padding:10px; margin-top: 10px;">
        <p>History Upload</p>
        <ul id="remarks">

        </ul>
    </div>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('planning/mst_capacity/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('planning/mst_capacity/create') ?>';
        $('#frm_insert').form('clear');
    }

    //Edit Data
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);

            $('#process_main_number').combobox({
                url: '<?php echo base_url('planning/mst_process_main/reads'); ?>',
                valueField: 'number',
                textField: 'name',
                prompt: 'Select Main Process',
                onLoadSuccess: function() {
                    $("#process_main_number").combobox('setValue', row.process_main_number);
                },
                onSelect: function(pm) {
                    $('#process_sub_number').combobox({
                        url: '<?php echo base_url('planning/mst_process_sub/reads?number='); ?>' + pm.number,
                        valueField: 'number',
                        textField: 'name',
                        prompt: 'Select Sub Process',
                        onLoadSuccess: function() {
                            $("#process_sub_number").combobox('setValue', row.process_sub_number);
                        }
                    });
                }
            });

            url_save = '<?= base_url('planning/mst_capacity/update') ?>?id=' + btoa(row.id);
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //Delete Data
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('planning/mst_capacity/delete') ?>',
                            data: {
                                id: row.id
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                toastr.error(jqXHR.statusText);
                                $.messager.alert("Error", jqXHR.statusText, 'error');
                            },
                            complete: function(data) {
                                $('#dg').datagrid('reload');
                            }
                        });
                    }
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function filter() {
        var filter_process_main = $("#filter_process_main").combobox('getValue');
        var filter_process_sub = $("#filter_process_sub").combobox('getValue');

        var url = "?filter_process_main=" + window.btoa(filter_process_main) +
            "&filter_process_sub=" + window.btoa(filter_process_sub);

        if (filter_process_main == "") {
            toastr.warning("Please select Process!", "Information");
        } else {
            $('#dg').datagrid({
                url: '<?= base_url('planning/mst_capacity/datatables') ?>' + url
            });

            $("#printout").attr('src', '<?= base_url('planning/mst_capacity/print') ?>' + url);
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_process_main = $("#filter_process_main").combobox('getValue');
        var filter_process_sub = $("#filter_process_sub").combobox('getValue');

        if (filter_process_main == "") {
            toastr.warning("Please select Process!", "Information");
        } else {
            var url = "?filter_process_main=" + window.btoa(filter_process_main) +
                "&filter_process_sub=" + window.btoa(filter_process_sub);

            window.location.assign('<?= base_url('planning/mst_capacity/print/excel') ?>' + url);
        }
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        $('#dg').datagrid({
            url: '<?= base_url('planning/mst_capacity/datatables') ?>',
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        });

        $('#filter_process_main').combobox({
            url: '<?php echo base_url('planning/mst_process_main/reads'); ?>',
            valueField: 'number',
            textField: 'name',
            prompt: 'Select Main Process',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(pm) {
                $('#filter_process_sub').combobox({
                    url: '<?php echo base_url('planning/mst_process_sub/reads?number='); ?>' + pm.number,
                    valueField: 'number',
                    textField: 'name',
                    prompt: 'Select Sub Process',
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });
            }
        });

        $('#process_main_number').combobox({
            url: '<?php echo base_url('planning/mst_process_main/reads'); ?>',
            valueField: 'number',
            textField: 'name',
            prompt: 'Select Main Process',
            onSelect: function(pm) {
                $('#process_sub_number').combobox({
                    url: '<?php echo base_url('planning/mst_process_sub/reads?number='); ?>' + pm.number,
                    valueField: 'number',
                    textField: 'name',
                    prompt: 'Select Sub Process'
                });
            }
        });

        //Save Data
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_insert').form('submit', {
                        url: url_save,
                        onSubmit: function() {
                            return $(this).form('validate');
                        },
                        success: function(result) {
                            var result = eval('(' + result + ')');

                            if (result.theme == "success") {
                                toastr.success(result.message, result.title);
                            } else {
                                toastr.error(result.message, result.title);
                            }

                            // $('#dlg_insert').dialog('close');
                            $('#dg').datagrid('reload');
                        }
                    });
                }
            }]
        });
    });
</script>