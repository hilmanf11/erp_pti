<table id="dg" class="easyui-treegrid" style="width:100%;" fit="true" toolbar="#toolbar" idField="id" treeField="line_name" rownumbers="true" url="<?= base_url('planning/mst_umh_havcp/datatables') ?>">
    <thead data-options="frozen:true">
        <tr>
            <th data-options="field:'line_name',width:200,halign:'center'">PRODUCT NO</th>
        </tr>
    </thead>
    <thead>
        <tr>
            <?php
            foreach ($mainProcess as $main) {
                echo '<th colspan="' . $main['total'] . '" data-options="width:100"> ' . $main['name'] . '</th>';
            }
            ?>
            <th rowspan="3" data-options="field:'total_umh',width:100,align:'center'">TOTAL UMH</th>
        </tr>
        <tr>
            <?php
            foreach ($subProcess as $sub) {
                echo '<th colspan="' . $sub['total'] . '" data-options="width:100"> ' . $sub['name'] . '</th>';
            }
            ?>
        </tr>
        <tr>
            <?php
            foreach ($process as $proces) {
                $proces_field = "field:'" . $proces['number'] . "',width:100,align:'center', styler:cellStyler";
                echo '<th data-options="' . $proces_field . '"> ' . $proces['name'] . '</th>';
            }
            ?>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 195px; padding: 10px;">
    <fieldset style="width:99.5%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
        <legend><b>Form Filter Data</b></legend>
        <div class="fitem">
            <span style="width:200px; display:inline-block;">Customer Name</span>
            <input style="width:300px;" name="filter_line_no" id="filter_line_no" class="easyui-combobox">
        </div>
        <div class="fitem">
            <span style="width:200px; display:inline-block;">Product No</span>
            <input style="width:300px;" name="filter_product_no" id="filter_product_no" class="easyui-combogrid">
        </div>
        <div class="fitem">
            <span style="width:200px; display:inline-block;"></span>
            <a href="javascript:300px;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
        </div>
    </fieldset>
    <?= $button ?>
</div>

<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" name="product_no" id="product_no" required="true" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Circuit No</span>
                <input style="width:60%;" name="circuit_no" id="circuit_no" readonly="true" class="easyui-textbox">
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
                <span style="width:35%; display:inline-block;">Sub Process</span>
                <input style="width:60%;" name="process_number" id="process_number" required="true" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Cycle Time</span>
                <input style="width:60%;" name="cycle_time" id="cycle_time" required="true" class="easyui-numberbox" precision="2">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Remarks</span>
                <input style="width:60%; height:50px;" name="remarks" id="remarks" class="easyui-textbox" multiline="true">
            </div>
        </fieldset>
    </form>
</div>

<!-- Upload -->
<div id="dlg_upload" class="easyui-dialog" title="Upload Data" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <form id="frm_upload" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Notes</b></legend>
            <ul>
                <li>All cell format must be <i style="color:red;">"text"</i></li>
                <li>If your file doesn't work when uploading, please save as your file in format extension .xls</li>
            </ul>
        </fieldset>
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
    <div id="p_remarks" class="easyui-panel" title="History Upload" style="width:100%; height:200px; padding:10px; margin-top: 10px;">
        <ul id="remarks">

        </ul>
    </div>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('planning/mst_umh_havcp/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('planning/mst_umh_havcp/create') ?>';
        $('#frm_insert').form('clear');
    }

    //Edit Data
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('planning/mst_umh_havcp/update') ?>?id=' + btoa(row.id);
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
                            url: '<?= base_url('planning/mst_umh_havcp/delete') ?>',
                            data: {
                                line_no: row.line_no,
                                customer_id: row.customer_id,
                                product_no: row.product_no
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
        var filter_line_no = $("#filter_line_no").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');
        var url = "?filter_line_no=" + window.btoa(filter_line_no) + "&filter_product_no=" + window.btoa(filter_product_no);

        $('#dg').treegrid({
            url: '<?= base_url('planning/mst_umh_havcp/datatables') ?>' + url
        });

        $("#printout").attr('src', '<?= base_url('planning/mst_umh_havcp/print') ?>' + url);
    }

    //Upload Data
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    function download_excel() {
        window.location.assign('<?= base_url('planning/mst_umh_havcp/downloadTemplate') ?>');
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_line_no = $("#filter_line_no").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');
        var url = "?filter_line_no=" + window.btoa(filter_line_no) + "&filter_product_no=" + window.btoa(filter_product_no);

        window.location.assign('<?= base_url('planning/mst_umh_havcp/print/excel') ?>' + url);
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        // $('#dg').datagrid({
        //     url: '<?= base_url('planning/mst_umh_havcp/datatables') ?>',
        //     pagination: true,
        //     clientPaging: false,
        //     remoteFilter: true,
        //     rownumbers: true
        // });

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

        //Upload Data
        $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('planning/mst_umh_havcp/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('planning/mst_umh_havcp/upload') ?>',
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
                                url: "<?= base_url('planning/mst_umh_havcp/uploadclearFailed') ?>"
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
                                        url: "<?= base_url('planning/mst_umh_havcp/uploadCreate') ?>",
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
                                                    url: "<?= base_url('planning/mst_umh_havcp/uploadcreateFailed') ?>",
                                                    data: {
                                                        data: json[number - 1],
                                                        message: result.message
                                                    },
                                                    cache: false
                                                });

                                                requestData(total, json, number + 1, value, success + 0, failed + 1);
                                            }

                                            $("#p_remarks").append(title + "<br>");
                                        },
                                        error: function(jqXHR, textStatus) {
                                            if (textStatus == "error") {
                                                Swal.fire({
                                                    title: 'Connection Time Out, Check Your Connection',
                                                    showConfirmButton: false,
                                                    allowOutsideClick: false,
                                                    allowEscapeKey: false,
                                                    didOpen: () => {
                                                        Swal.showLoading();
                                                    },
                                                });

                                                requestData(total, json, number, value, success + 0, failed + 0);
                                            }
                                        }
                                    });
                                }
                            }
                        }
                    });
                }
            }]
        });

        $('#filter_product_no').combogrid({
            url: '<?= base_url('planning/mst_data/readProducts') ?>',
            panelWidth: 400,
            idField: 'item_id',
            textField: 'item_id',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Product No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'item_id',
                    title: 'Item ID',
                    width: 200
                }, {
                    field: 'item_name',
                    title: 'Item Name',
                    width: 200
                }]
            ]
        });

        $('#filter_line_no').combobox({
            url: '<?php echo base_url('planning/mst_line/reads'); ?>',
            valueField: 'line_no',
            textField: 'remarks',
            prompt: 'Select Line No',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
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

        $('#product_no').combogrid({
            url: '<?= base_url('planning/mst_data/readProducts') ?>',
            panelWidth: 400,
            idField: 'item_id',
            textField: 'item_id',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Product No",
            columns: [
                [{
                    field: 'item_id',
                    title: 'Item ID',
                    width: 200
                }, {
                    field: 'item_name',
                    title: 'Item Name',
                    width: 200
                }]
            ],
            onSelect: function(index, row) {
                $("#product_name").textbox('setValue', row.item_name);
                $.ajax({
                    type: "post",
                    url: "<?php echo base_url('planning/mst_data/readCircuitTotal'); ?>",
                    data: "productNo=" + row.item_id,
                    dataType: "json",
                    success: function(data) {
                        $("#circuit_no").textbox('setValue', data[0]['total_cct']);
                    }
                });
            }
        });

        $('#customer_id').combobox({
            url: '<?php echo base_url('planning/mst_data/readCustomers'); ?>',
            valueField: 'cust_id',
            textField: 'cust_name',
            prompt: 'Select Customer',
            onSelect: function(cust) {
                $("#customer_name").textbox('setValue', cust.cust_name);
            }
        });

        $('#line_no').combobox({
            url: '<?php echo base_url('planning/mst_line/reads'); ?>',
            valueField: 'line_no',
            textField: 'remarks',
            prompt: 'Select Line No'
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

    function cellStyler(value, row, index) {
        if (value > 0) {
            return 'background-color:#C8FFCC;';
        } else {
            return 'background-color:#FFC8C8;';
        }
    }
</script>