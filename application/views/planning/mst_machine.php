<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'process_main_name',width:150,halign:'center'"> Main Process</th>
            <th rowspan="2" data-options="field:'process_sub_name',width:150,halign:'center'"> Sub Process</th>
            <th rowspan="2" data-options="field:'machine_code',width:150,halign:'center'">Machine Code</th>
            <th rowspan="2" data-options="field:'machine_serial',width:150,halign:'center'">Serial No</th>
            <th rowspan="2" data-options="field:'bei_code_new',width:150,halign:'center'">BEI Code (New)</th>
            <th rowspan="2" data-options="field:'bei_code_old',width:150,halign:'center'"> BEI Code (Old)</th>
            <th rowspan="2" data-options="field:'location',width:100,halign:'center'"> Location</th>
            <th rowspan="2" data-options="field:'room',width:100,halign:'center'"> Room</th>
            <th rowspan="2" data-options="field:'machine_name',width:200,halign:'center'"> Machine Model</th>
            <th rowspan="2" data-options="field:'machine_type_number',width:150,halign:'center'"> Machine ID</th>
            <th rowspan="2" data-options="field:'category',width:150,halign:'center'"> Machine Specification</th>
            <th rowspan="2" data-options="field:'maker',width:120,halign:'center'"> Maker</th>
            <th rowspan="2" data-options="field:'year',width:100,halign:'center'"> Year</th>
            <th rowspan="2" data-options="field:'power',width:80,halign:'center'"> Power</th>
            <th rowspan="2" data-options="field:'uom',width:50,halign:'center'"> Uom</th>
            <th rowspan="2" data-options="field:'operator_qty',width:100,halign:'center'"> Operator</th>
            <th rowspan="2" data-options="field:'voltage',width:100,halign:'center'"> Voltage</th>
            <th rowspan="2" data-options="field:'air_consumption',width:100,halign:'center'"> Air Pressure</th>
            <th rowspan="2" data-options="field:'purchase_leadtime',width:100,halign:'center'"> Purchase Leadtime</th>
            <th rowspan="2" data-options="field:'space',width:100,halign:'center'"> Space (M2)</th>
            <th rowspan="2" data-options="field:'picture_1',width:100,halign:'center'"> Picture</th>
            <th rowspan="2" data-options="field:'status_is',width:80, styler:cellStyler, formatter:cellFormatter">Status</th>
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

<div id="toolbar" style="height: 195px; padding: 10px;">
    <fieldset style="width:100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <legend><b>Form Filter Data</b></legend>
        <div style="width: 50%;">
            <div class="fitem">
                <span style="width:200px; display:inline-block;">Main Process</span>
                <input style="width:300px;" id="filter_process_main_number" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:200px; display:inline-block;">Process</span>
                <input style="width:300px;" id="filter_process_sub_number" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:200px; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </div>
        <div style="width: 45%;">
            <div class="fitem">
                <span style="width:200px; display:inline-block;">Machine Specification</span>
                <input style="width:300px;" name="filter_machine_type" id="filter_machine_type" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:200px; display:inline-block;">Machine Model</span>
                <input style="width:300px;" name="filter_machine_name" id="filter_machine_name" class="easyui-textbox">
            </div>
        </div>
    </fieldset>
    <?= $button ?>
</div>

<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1000px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px;">
            <legend><b>Form Data</b></legend>
            <div style="float: left; width: 50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Machine Code</span>
                    <input style="width:60%;" name="machine_code" id="machine_code" readonly="true" required="true" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Machine Serial No</span>
                    <input style="width:60%;" name="machine_serial" id="machine_serial" required="true" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">BEI Code</span>
                    <input style="width:30%;" name="bei_code_new" id="bei_code_new" data-options="prompt:'New'" class="easyui-textbox">
                    <input style="width:30%;" name="bei_code_old" id="bei_code_old" data-options="prompt:'Old'" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Location</span>
                    <input style="width:30%;" name="location" id="location" data-options="prompt:'Location'" class="easyui-textbox">
                    <input style="width:30%;" name="room" id="room" data-options="prompt:'Room'" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Machine Model</span>
                    <input style="width:60%;" name="machine_name" id="machine_name" required="true" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Machine Specification</span>
                    <input style="width:60%;" name="machine_type_number" id="machine_type_number" required="true" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Main Process</span>
                    <input style="width:60%;" name="process_main_number" id="process_main_number" required="true" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Process</span>
                    <input style="width:60%;" name="process_sub_number" id="process_sub_number" required="true" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Maker</span>
                    <input style="width:60%;" name="maker" id="maker" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Year</span>
                    <input style="width:30%;" name="year" id="year" class="easyui-numberbox">
                </div>
            </div>
            <div style="float: left; width: 50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Power</span>
                    <input style="width:30%;" name="power" id="power" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Unit Of Meassure</span>
                    <input style="width:30%;" name="uom" id="uom" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Operator Qty</span>
                    <input style="width:40%;" name="operator_qty" id="operator_qty" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Voltage</span>
                    <input style="width:40%;" name="voltage" id="voltage" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Air Pressure</span>
                    <input style="width:40%;" name="air_consumption" id="air_consumption" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Purchase Leadtime</span>
                    <input style="width:40%;" name="purchase_leadtime" id="purchase_leadtime" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Space (M2)</span>
                    <input style="width:40%;" name="space" id="space" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Picture</span>
                    <input style="width:60%;" name="picture" id="picture" class="easyui-filebox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Status</span>
                    <select style="width:60%;" name="status_is" id="status_is" class="easyui-combobox">
                        <option value="0">ACTIVE</option>
                        <option value="1">NOT ACTIVE</option>
                    </select>
                </div>
            </div>
        </fieldset>
    </form>
</div>

<!-- Upload -->
<div id="dlg_upload" class="easyui-dialog" title="Upload Data" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <form id="frm_upload" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">File Upload</span>
                <input name="file_upload" style="width: 60%;" required="" accept=".xls" id="file_excel" class="easyui-filebox">
            </div>
        </fieldset>
    </form>
    <span style="float: left; color:green;">SUCCESS : <b id="p_success">0</b></span><span style="float: right; color:red;"> FAILED : <b id="p_failed">0</b></span>
    <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
    <div id="p_remarks" title="History Upload" class="easyui-panel" style="width:100%; height:200px; padding:10px; margin-top: 10px;">
        <ul id="remarks">

        </ul>
    </div>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('planning/mst_machine/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('planning/mst_machine/create') ?>';
        $('#frm_insert').form('clear');
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

        autonumber();
        $("#year").numberbox('setValue', '2022');
    }

    function autonumber() {
        //Get Autonumber
        $.ajax({
            type: "post",
            url: "<?= base_url('planning/mst_machine/autonumber') ?>",
            dataType: "text",
            success: function(machine_code) {
                $('#machine_code').textbox('setValue', machine_code);
            }
        });
    }

    //Edit Data
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('planning/mst_machine/update') ?>?id=' + btoa(row.id);

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
                            url: '<?= base_url('planning/mst_machine/delete') ?>',
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
        var filter_machine_type = $("#filter_machine_type").combobox('getValue');
        var filter_machine_name = $("#filter_machine_name").textbox('getValue');
        var filter_process_main_number = $("#filter_process_main_number").combobox('getValue');
        var filter_process_sub_number = $("#filter_process_sub_number").combobox('getValue');
        var url = "?filter_machine_type=" + filter_machine_type + "&filter_machine_name=" + filter_machine_name + "&filter_process_main_number=" + filter_process_main_number + "&filter_process_sub_number=" + filter_process_sub_number;

        $('#dg').datagrid({
            url: '<?= base_url('planning/mst_machine/datatables') ?>' + url
        });

        $("#printout").attr('src', '<?= base_url('planning/mst_machine/print') ?>' + url);
    }

    //Upload Data
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_machine.xls') ?>');
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_machine_type = $("#filter_machine_type").combobox('getValue');
        var filter_machine_name = $("#filter_machine_name").textbox('getValue');
        var filter_process_main_number = $("#filter_process_main_number").combobox('getValue');
        var filter_process_sub_number = $("#filter_process_sub_number").combobox('getValue');
        var url = "?filter_machine_type=" + filter_machine_type + "&filter_machine_name=" + filter_machine_name + "&filter_process_main_number=" + filter_process_main_number + "&filter_process_sub_number=" + filter_process_sub_number;


        window.location.assign('<?= base_url('planning/mst_machine/print/excel') ?>' + url);
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        $('#dg').datagrid({
            url: '<?= base_url('planning/mst_machine/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
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
                            autonumber();
                            $('#dg').datagrid('reload');
                        }
                    });
                }
            }]
        });

        //Upload Data
        $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('planning/mst_machine/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('planning/mst_machine/upload') ?>',
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

                            //Clear File
                            $.ajax({
                                url: "<?= base_url('planning/mst_machine/uploadclearFailed') ?>"
                            });

                            var json = eval('(' + result + ')');
                            requestData(json.total, json);

                            function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                                if (value < 100) {
                                    value = Math.floor((number / total) * 100);
                                    $('#p_upload').progressbar('setValue', value);
                                    $('#p_start').html(number);
                                    $('#p_finish').html(total);

                                    $.ajax({
                                        type: "POST",
                                        async: true,
                                        url: "<?= base_url('planning/mst_machine/uploadCreate') ?>",
                                        data: {
                                            "data": json[number - 1]
                                        },
                                        cache: false,
                                        dataType: "json",
                                        success: function(result) {
                                            if (result.theme == "success") {
                                                $('#p_success').html(success);
                                                var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                                requestData(total, json, number + 1, value, success + 1, failed + 0);
                                            } else {
                                                $('#p_failed').html(failed);
                                                var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;

                                                //Json Failed
                                                $.ajax({
                                                    type: "POST",
                                                    async: true,
                                                    url: "<?= base_url('planning/mst_machine/uploadcreateFailed') ?>",
                                                    data: {
                                                        data: json[number - 1],
                                                        message: result.message
                                                    },
                                                    cache: false
                                                });

                                                requestData(total, json, number + 1, value, success + 0, failed + 1);
                                            }

                                            $("#p_remarks").append(title + "<br>");
                                        }
                                    });
                                }
                            }
                        }
                    });
                }
            }]
        });

        $('#filter_process_main_number').combobox({
            url: '<?php echo base_url('planning/mst_process_main/reads'); ?>',
            valueField: 'description',
            textField: 'description',
            prompt: 'Select All',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(pm) {
                $('#filter_process_sub_number').combobox({
                    url: '<?php echo base_url('planning/mst_process_sub/reads?description='); ?>' + pm.description,
                    valueField: 'number',
                    textField: 'name',
                    prompt: 'Select All',
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],

                });
            }
        });

        $('#filter_machine_type').combogrid({
            url: '<?= base_url('planning/mst_machine_type/reads') ?>',
            panelWidth: 400,
            idField: 'number',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select All",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'number',
                    title: 'Specification ID',
                    width: 200
                }, {
                    field: 'name',
                    title: 'Specification Name',
                    width: 200
                }]
            ]
        });

        $('#machine_type_number').combogrid({
            url: '<?= base_url('planning/mst_machine_type/reads') ?>',
            panelWidth: 400,
            idField: 'number',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Machine Specification",
            columns: [
                [{
                    field: 'number',
                    title: 'Specification ID',
                    width: 200
                }, {
                    field: 'name',
                    title: 'Specification Name',
                    width: 200
                }]
            ]
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

    //CELLSTYLE STATUS
    function cellStyler(value, row, index) {
        if (value == 0) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }
    //FORMATTER STATUS
    function cellFormatter(value) {
        if (value == 0) {
            return 'Active';
        } else {
            return 'Not Active';
        }
    };
</script>