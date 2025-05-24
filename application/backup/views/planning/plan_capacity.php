<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'plant',width:100,halign:'center'">Plant</th>
            <th rowspan="2" data-options="field:'line_no',width:80,align:'center'">Line No</th>
            <th rowspan="2" data-options="field:'line_name',width:150,halign:'center'">Line Name</th>
            <th rowspan="2" data-options="field:'item_no',width:200,halign:'center'">Product No</th>
            <th rowspan="2" data-options="field:'item_name',width:250,halign:'center'"> Product Name</th>
            <th rowspan="2" data-options="field:'capacity',width:100,halign:'center',align:'right'"> Capacity</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_name',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_name',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 35px;">
    <?= $button ?>
</div>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>

<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 800px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                <legend><b>Form Data</b></legend>
                <div style="width: 50%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Plant</span>
                        <input style="width:60%;" name="plant" id="plant" required class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Line No</span>
                        <input style="width:60%;" name="line_no" id="line_no" required class="easyui-combogrid">
                    </div>
                </div>
                <div style="width: 50%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Line Name</span>
                        <input style="width:60%;" name="line_name" id="line_name" readonly class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Total Capacity</span>
                        <input style="width:60%;" name="capacity_total" id="capacity_total" required class="easyui-numberbox">
                    </div>
                </div>
                <!-- <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" name="item_no" id="item_no" required class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Name</span>
                    <input style="width:60%;" name="item_name" id="item_name" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Capacity</span>
                    <input style="width:30%;" name="capacity" id="capacity" required class="easyui-numberbox">
                </div> -->
            </fieldset>
        </div>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Add Sales Order Product" toolbar="#toolbar2" data-options="singleSelect: true"></table>
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
<iframe id="printout" src="<?= base_url('planning/plan_capacity/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        $('#frm_insert').form('clear');

        $("#plant").combobox('enable');
        $("#line_no").combogrid('enable');
        $("#line_name").textbox('enable');
        $("#capacity_total").numberbox('enable');
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);

            $("#plant").combobox('disable');
            $("#line_no").combogrid('disable');
            $("#line_name").textbox('disable');
            $("#capacity_total").numberbox('disable');
            
            addTable(row.line_no, '<?= base_url('planning/plan_capacity/datatableUpdates?line_no=') ?>' + window.btoa(row.line_no));
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
                            url: '<?= base_url('planning/plan_capacity/delete') ?>',
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

    //INSERT ADD ROW
    function addTable(line_no, url = "") {
        var lastIndex;
        var dg = $('#dg2').datagrid({
            url: url,
            columns: [
                [{
                    field: 'item_id',
                    width: 200,
                    halign: 'center',
                    title: "Product No",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('planning/plan_capacity/readProducts?line_no=') ?>' + line_no,
                            required: true,
                            panelWidth: 400,
                            idField: 'item_id',
                            textField: 'item_id',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Product No',
                            columns: [
                                [{
                                    field: 'item_id',
                                    title: 'Product No',
                                    width: 200
                                }, {
                                    field: 'item_name',
                                    title: 'Product Name',
                                    width: 200
                                }]
                            ],
                            onSelect: function(value, rows) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);
                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_name'
                                });

                                $(ed.target).textbox('setValue', rows.item_name);
                            }
                        }
                    }
                }, {
                    field: 'item_name',
                    width: 200,
                    halign: 'center',
                    title: "Product Name",
                    editor: {
                        type: 'textbox',
                        options: {
                            required: true
                        }
                    }
                }, {
                    field: 'capacity',
                    width: 100,
                    halign: 'center',
                    title: "Capacity",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true
                        }
                    }
                },]
            ],
            onClickRow: function(rowIndex) {
                if (lastIndex != rowIndex) {
                    $(this).datagrid('endEdit', lastIndex);
                    $(this).datagrid('beginEdit', rowIndex);
                }
                lastIndex = rowIndex;
            },
            onBeginEdit: function(rowIndex, row) {
                var editors = $('#dg2').datagrid('getEditors', rowIndex);
                var trans_date = $("#trans_date").datebox('getValue');
                
                $(editors[2].target).numberbox({
                    onChange: function() {
                        var capacity = $(editors[2].target).numberbox('getValue');
                        var capacity_total = $("#capacity_total").numberbox('getValue');
                        alert(capacity + " | " + capacity_total);
                    }
                });
            }
        });
    }
    var editIndex = undefined;

    function endEditing() {
        if (editIndex == undefined) {
            return true
        }
        if ($('#dg2').datagrid('validateRow', editIndex)) {
            $('#dg2').datagrid('endEdit', editIndex);
            editIndex = undefined;
            return true;
        } else {
            return false;
        }
    }

    function append() {
        var line_no = $("#line_no").combogrid('getValue');
        var capacity_total = $("#capacity_total").numberbox('getValue');

        if (line_no != "" && capacity_total != "") {
            $("#capacity_total").numberbox('readonly', true);

            if (endEditing()) {
                $('#dg2').datagrid('appendRow', {
                    qty: '0'
                });
                editIndex = $('#dg2').datagrid('getRows').length - 1;
                $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
            }
        } else {
            toastr.error("Please Choose Line and Total Capacity first");
        }
    }

    function removeit() {
        if (editIndex == undefined) {
            return
        }
        $('#dg2').datagrid('cancelEdit', editIndex).datagrid('deleteRow', editIndex);
        editIndex = undefined;
    }

    //Upload Data
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_plan_capacity.xls') ?>');
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        window.location.assign('<?= base_url('planning/plan_capacity/print/excel') ?>');
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        $('#dg').datagrid({
            url: '<?= base_url('planning/plan_capacity/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        }).datagrid('enableFilter');

        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    // toastr.error("Still Maintenance");
                    var plant = $("#plant").combobox('getValue');
                    var line_no = $("#line_no").combogrid('getValue');
                    var line_name = $("#line_name").textbox('getValue');
                    var capacity_total = $("#capacity_total").numberbox('getValue');

                    if (line_no == "" || totalrows <= 0) {
                        toastr.error("please complete your input data");
                    } else {
                        
                        $('#dg2').datagrid('acceptChanges');
                        var rows = $('#dg2').datagrid('getRows');
                        var totalrows = rows.length;
                        endEditing();

                        for (let i = 0; i < totalrows; i++) {
                            if (rows[i].item_id) {
                                $.ajax({
                                    type: "post",
                                    url: '<?= base_url('planning/plan_capacity/create') ?>',
                                    data: {
                                        plant: plant,
                                        line_no: line_no,
                                        line_name: line_name,
                                        capacity_total: capacity_total,
                                        item_id: rows[i].item_id,
                                        item_name: rows[i].item_name,
                                        capacity: rows[i].capacity,
                                    },
                                    dataType: "json",
                                    success: function(result) {
                                        if (result.theme == "success") {
                                            toastr.success(result.message, result.title);
                                        } else {
                                            toastr.error(result.message, result.title);
                                        }
                                    }
                                });
                            }
                        }
                        $('#dg').datagrid('reload');
                        $('#dlg_insert').dialog('close');
                    }
                }
            }]
        });

        //Upload Data
        $('#dlg_upload').dialog({
            buttons: [{
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('planning/plan_capacity/upload') ?>',
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
                            $.messager.progress('close');
                            var json = eval('(' + result + ')');
                            requestData(json.total, json);

                            function requestData(total, json, number = 1, value = 0) {
                                if (value < 100) {
                                    value = Math.floor((number / total) * 100);
                                    $('#p_upload').progressbar('setValue', value);
                                    $('#p_start').html(number);
                                    $('#p_finish').html(total);

                                    $.post('<?= base_url('planning/plan_capacity/uploadCreate') ?>', {
                                        data: json[number - 1]
                                    }, function(note) {
                                        var result = eval('(' + note + ')');
                                        if (result.theme == "success") {
                                            var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                        } else {
                                            var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                                        }
                                        $("#p_remarks").append(title + "<br>");
                                        requestData(total, json, number + 1, value);
                                    });
                                }
                            }
                        }
                    });
                }
            }]
        });

        $('#plant').combobox({
            url: '<?php echo base_url('planning/plan_capacity/readPlant'); ?>',
            valueField: 'plant',
            textField: 'plant',
            prompt: 'Select Plant',
            onSelect: function(plant){
                $('#line_no').combogrid({
                    url: '<?php echo base_url('planning/plan_capacity/readLine/'); ?>' + plant.plant,
                    panelWidth: 300,
                    idField: 'line_no',
                    textField: 'line_no',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Select Line No",
                    columns: [
                        [{
                            field: 'line_no',
                            title: 'Line No',
                            width: 80
                        }, {
                            field: 'line_description',
                            title: 'Line Name',
                            width: 200
                        }]
                    ],
                    onSelect: function(val, line){
                        $("#line_name").textbox('setValue', line.line_description);
                        
                        addTable(line.line_no);
                        // $('#item_no').combogrid({
                        //     url: '<?= base_url('planning/plan_capacity/readProducts?line_no=') ?>' + line.line_no,
                        //     panelWidth: 400,
                        //     idField: 'item_id',
                        //     textField: 'item_id',
                        //     mode: 'remote',
                        //     fitColumns: true,
                        //     prompt: "Select Product No",
                        //     columns: [
                        //         [{
                        //             field: 'item_id',
                        //             title: 'Item ID',
                        //             width: 200
                        //         }, {
                        //             field: 'item_name',
                        //             title: 'Item Name',
                        //             width: 200
                        //         }]
                        //     ],
                        //     onSelect: function(val, row){
                        //         $("#item_name").textbox('setValue', row.item_name);
                        //     }
                        // });
                    }
                });
            }
        });
    });

    //Format Datepicker
    function myformatter(date) {
        var y = date.getFullYear();
        var m = date.getMonth() + 1;
        var d = date.getDate();
        return y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d);
    }

    //Format Datepicker
    function myparser(s) {
        if (!s) return new Date();
        var ss = (s.split('-'));
        var y = parseInt(ss[0], 10);
        var m = parseInt(ss[1], 10);
        var d = parseInt(ss[2], 10);
        if (!isNaN(y) && !isNaN(m) && !isNaN(d)) {
            return new Date(y, m - 1, d);
        } else {
            return new Date();
        }
    }
</script>