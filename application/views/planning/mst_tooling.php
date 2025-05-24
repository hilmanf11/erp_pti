<table id="dg" class="easyui-treegrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'tooling_type_name',width:200,halign:'center'"> Type/Description</th>
            <th rowspan="2" data-options="field:'tooling_code',width:100,halign:'center'">Tooling Code</th>
            <th rowspan="2" data-options="field:'serial_no',width:150,halign:'center'">Serial No</th>
            <th rowspan="2" data-options="field:'qty',width:100,align:'center'"> Qty</th>
            <th rowspan="2" data-options="field:'picture',width:100,align:'center',formatter:FormatterFile"> Picture</th>
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

<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Tooling Code</span>
                <input style="width:60%;" name="tooling_code" id="tooling_code" readonly="true" required="true" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Serial No</span>
                <input style="width:60%;" name="serial_no" id="serial_no" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Description</span>
                <input style="width:60%;" name="description" id="description" required="true" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Tooling Type</span>
                <input style="width:60%;" name="tooling_type_number" id="tooling_type_number" required="true" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Qty</span>
                <input style="width:40%;" name="qty" id="qty" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Picture</span>
                <input style="width:60%;" name="picture" id="picture" class="easyui-filebox">
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
<iframe id="printout" src="<?= base_url('planning/mst_tooling/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('planning/mst_tooling/create') ?>';
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
                    prompt: 'Select Sub Process',
                    onSelect: function(ps) {
                        $('#process_number').combobox({
                            url: '<?php echo base_url('planning/mst_process/reads?number='); ?>' + ps.number,
                            valueField: 'number',
                            textField: 'name',
                            prompt: 'Select Process'
                        });
                    }
                });
            }
        });

        autonumber();
    }

    function autonumber() {
        //Get Autonumber
        $.ajax({
            type: "post",
            url: "<?= base_url('planning/mst_tooling/autonumber') ?>",
            dataType: "text",
            success: function(tooling_code) {
                $('#tooling_code').textbox('setValue', tooling_code);
            }
        });
    }

    //Edit Data
    function update() {
        var row = $('#dg').treegrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('planning/mst_tooling/update') ?>?id=' + btoa(row.id);

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
                        },
                        onSelect: function(ps) {
                            $('#process_number').combobox({
                                url: '<?php echo base_url('planning/mst_process/reads?number='); ?>' + ps.number,
                                valueField: 'number',
                                textField: 'name',
                                prompt: 'Select Process',
                                onLoadSuccess: function() {
                                    $("#process_number").combobox('setValue', row.process_number);
                                },
                            });
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
        var rows = $('#dg').treegrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('planning/mst_tooling/delete') ?>',
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
                                $('#dg').treegrid('reload');
                            }
                        });
                    }
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //Upload Data
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_tooling.xls') ?>');
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function reload() {
        window.location.reload();
    }

    function FormatterFile(val) {
        if (val == "" || val == null) {
            return '';
        } else {
            return '<a style="text-decoration:none;" target="_blank" href="' + val + '"><i class="fa fa-download"></i> Download</a>';
        }
    };

    $(function() {
        $('#dg').treegrid({
            url: '<?= base_url('planning/mst_tooling/datatables') ?>',
            clientPaging: true,
            rownumbers: true,
            idField: 'id',
            treeField: 'tooling_type_name',
            singleSelect: false,
            onBeforeLoad: function(row, param) {
                if (!row) {
                    param.id = 0;
                }
            },
        }).treegrid('enableFilter');

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
                            $('#dg').treegrid('reload');
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
                    window.open('<?= base_url('planning/mst_tooling/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('planning/mst_tooling/upload') ?>',
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
                                url: "<?= base_url('planning/mst_tooling/uploadclearFailed') ?>"
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
                                        url: "<?= base_url('planning/mst_tooling/uploadCreate') ?>",
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
                                                    url: "<?= base_url('planning/mst_tooling/uploadcreateFailed') ?>",
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

        $('#tooling_type_number').combobox({
            url: '<?php echo base_url('planning/mst_tooling_type/reads'); ?>',
            valueField: 'number',
            textField: 'name',
            prompt: 'Select Tooling Type'
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