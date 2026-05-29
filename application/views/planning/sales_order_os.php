<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'cust_id',width:80,halign:'center'">Customer ID</th>
            <th data-options="field:'cust_name',width:250,halign:'center'">Customer</th>
            <th data-options="field:'item_id',width:200,halign:'center'">Product No</th>
            <th data-options="field:'item_name',width:200,halign:'center'">Product Name</th>
            <th data-options="field:'circuit_no',width:80,align:'center'">CCT</th>
            <th data-options="field:'total_so',width:80,halign:'center',align:'right',editor:'numberbox'">Qty</th>
            <th hidden data-options="field:'status_so',width:80,align:'center', styler:cellStyler">Status</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 285px; padding:10px;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 50%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Period</span>
                <input style="width:30%;" name="filter_month" id="filter_month" class="easyui-combobox" data-options="prompt:'Month'">
                <input style="width:30%;" name="filter_year" id="filter_year" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Revision</span>
                <input style="width:60%;" name="filter_revision" id="filter_revision" class="easyui-combobox" data-options="prompt:'Revision'" panelHeight="auto">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Customer</span>
                <input style="width:60%;" id="filter_customer_id" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" id="filter_product_no" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                <a href="javascript:;" class="easyui-linkbutton" onclick="dataUploaded()"><i class="fa fa-database"></i> View Uploaded</a>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter('display')"><i class="fa fa-search"></i> Display Data</a>
            </div>
        </fieldset>
        <fieldset style="width: 40%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Process Save Data</b></legend>
            <a href="javascript:;" style="float: left; color:green;" class="easyui-linkbutton" plain="true"><i class="fa fa-check"></i> SUCCESS : <b id="p_success">0</b></a>
            <a href="javascript:;" style="float: right; color:red;" class="easyui-linkbutton" plain="true" onclick="downloadFailed()"><i class="fa fa-times"></i> FAILED : <b id="p_failed">0</b></a>
            <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
            <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
            <div id="p_remarks" class="easyui-panel" style="width:100%; height:120px; padding:10px; margin-top: 10px; overflow: auto;">
                <ul id="remarks">

                </ul>
            </div>
        </fieldset>
    </div>
    <?= $button ?>
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
</div>

<div id="dlg_insert" class="easyui-dialog" title="Edit Data" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" name="product_no" id="product_no" disabled class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product Name</span>
                <input style="width:60%;" name="product_name" id="product_name" disabled class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Circuit No</span>
                <input style="width:60%;" name="circuit_no" id="circuit_no" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Customer No</span>
                <input style="width:60%;" name="customer_id" id="customer_id" class="easyui-combogrid">
            </div>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Customer Name</span>
                <input style="width:60%;" name="customer_name" id="customer_name" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Qty</span>
                <input style="width:30%;" name="qty" class="easyui-numberbox" required>
            </div>
        </fieldset>
    </form>
</div>

<div id="toolbar2" style="height: 35px;">
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="uploaded_edit()"><i class="fa fa-edit"></i> Edit</a>
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="deleted()"><i class="fa fa-trash"></i> Delete</a>
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="uploaded_excel()"><i class="fa fa-file"></i> Export Excel</a>
</div>

<!-- Upload -->
<div id="dlg_uploaded" class="easyui-window" title="Data Uploaded SO" data-options="closed: true, modal:true,minimizable:false,collapsible:false" style="width: 800px; height: 400px; top: 40px;">
    <table id="dg_uploaded" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar2">
        <thead>
            <tr>
                <th field="ck" checkbox="true"></th>
                <th data-options="field:'p_month',width:50,align:'center'">Month</th>
                <th data-options="field:'p_year',width:50,align:'center'">Year</th>
                <th data-options="field:'revision',width:50,align:'center'">Rev</th>
                <th data-options="field:'customer_name',width:250,halign:'center'">Customer</th>
                <th data-options="field:'product_no',width:150,halign:'center'">Product No</th>
                <th data-options="field:'product_name',width:200,halign:'center'">Product Name</th>
                <th data-options="field:'circuit_no',width:80,align:'center'">CCT</th>
                <th data-options="field:'qty',width:50,halign:'center', align:'right'">Qty</th>
                <th hidden data-options="field:'status',width:80,align:'center', styler:cellStyler">Status</th>
                <th data-options="field:'approved_by',width:100,align:'center'">Approved By</th>
                <th data-options="field:'approved_date',width:100,align:'center'">Approved Date</th>
            </tr>
        </thead>
    </table>
</div>

<!-- PDF -->
<iframe id="printout" src="" style="width: 100%;" hidden></iframe>

<div id="dlg_display" class="easyui-window" title="Display Data" data-options="closed: true,modal:true" style="width: 1200px; height: 550px;">
    <table id="dg_display" class="easyui-datagrid" style="width: 100%; height: 100%;">
        <thead>
            <tr>
                <th data-options="field:'cust_id',width:80,halign:'center'">Customer ID</th>
                <th data-options="field:'cust_name',width:250,halign:'center'">Customer</th>
                <th data-options="field:'item_id',width:200,halign:'center'">Product No</th>
                <th data-options="field:'item_name',width:200,halign:'center'">Product Name</th>
                <th data-options="field:'circuit_no',width:80,align:'center'">CCT</th>
                <th data-options="field:'total_so',width:80,halign:'center',align:'right',editor:'numberbox'">Qty</th>
                <th hidden data-options="field:'status_so',width:80,align:'center', styler:cellStyler">Status</th>
            </tr>
        </thead>
    </table>
</div>

<script>
    //Add Data
    function add() {
        var rows = $('#dg').datagrid('getChecked');
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to save this data?', function(r) {
                if (r) {
                    $.ajax({
                        url: "<?= base_url('planning/sales_order_os/uploadclearFailed') ?>"
                    });

                    requestData(rows.length, rows);

                    function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                        if (value < 100) {
                            value = Math.floor((number / total) * 100);
                            $('#p_upload').progressbar('setValue', value);
                            $('#p_start').html(number);
                            $('#p_finish').html(total);

                            $.post('<?= base_url('planning/sales_order_os/create') ?>', {
                                data: json[number - 1],
                                revision: filter_revision,
                                p_month: filter_month,
                                p_year: filter_year
                            }, function(note) {
                                var result = eval('(' + note + ')');
                                if (result.theme == "success") {
                                    Swal.close();
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
                                        url: "<?= base_url('planning/sales_order_os/uploadcreateFailed') ?>",
                                        data: {
                                            data: json[number - 1],
                                            message: result.message
                                        },
                                        cache: false
                                    });

                                    requestData(total, json, number + 1, value, success + 0, failed + 1);
                                }

                                if (value == 100) {
                                    Swal.fire('Good job!', 'Process Save Data Completed!', 'success');
                                }

                                $("#p_remarks").append(title + "<br>");
                            }).fail(function(jqXHR, textStatus) {
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
                            });
                        }
                    }
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //EDIT DATA
    function uploaded_edit() {
        var row = $('#dg_uploaded').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('planning/sales_order_os/update') ?>?id=' + btoa(row.id);
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function dataUploaded() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_customer_id = $("#filter_customer_id").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');
        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_product_no=" + window.btoa(filter_product_no) +
            "&filter_customer_id=" + window.btoa(filter_customer_id);

        if(filter_month == "" || filter_year == "" || filter_revision == ""){
            toastr.info("Please Select Period and Revision");
        }else{
            $('#dlg_uploaded').window('open');
            $('#dg_uploaded').datagrid({
                url: '<?= base_url('planning/sales_order_os/datatables_uploaded') ?>' + url,
                rownumbers: true,
                pagination: true,
                clientPaging: false,
                remoteFilter: true,
            }).datagrid('enableFilter');
        }
    }

    //Delete Data
    function deleted() {
        var rows = $('#dg_uploaded').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('planning/sales_order_os/delete') ?>',
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
                                $('#dg_uploaded').datagrid('reload');
                            }
                        });
                    }
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function filter(act='') {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_customer_id = $("#filter_customer_id").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');

        if (filter_month == "" || filter_year == "") {
            toastr.warning("Please select Period!", "Information");
        } else {
            var dg = 'dg';
            if (act == 'display') {
                dg += '_display';
                if (filter_customer_id == '') { filter_customer_id = 'all'; }
                $('#dlg_display').window('open');
            }
            $('#'+dg).datagrid("reload", "<?= base_url('planning/sales_order_os/datatables') ?>"+
                "?filter_month=" + window.btoa(filter_month) +
                "&filter_year=" + window.btoa(filter_year) +
                "&filter_revision=" + window.btoa(filter_revision) +
                "&filter_product_no=" + window.btoa(filter_product_no) +
                "&filter_customer_id=" + window.btoa(filter_customer_id)
            );
            if (act == '') {
                $("#"+dg).datagrid('enableCellEditing').datagrid('gotoCell', {
                    index: 0,
                    field: 'item_id'
                })
            }
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_customer_id = $("#filter_customer_id").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');
        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_product_no=" + window.btoa(filter_product_no) +
            "&filter_customer_id=" + window.btoa(filter_customer_id);

        if (filter_month == "" || filter_year == "") {
            toastr.warning("Please select Period!", "Information");
        } else {
            window.location.assign('<?= base_url('planning/sales_order_os/print/excel') ?>' + url);
        }
    }

    function uploaded_excel() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_customer_id = $("#filter_customer_id").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');
        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_product_no=" + window.btoa(filter_product_no) +
            "&filter_customer_id=" + window.btoa(filter_customer_id);

        if(filter_month == "" || filter_year == "" || filter_revision == ""){
            toastr.info("Please Select Period and Revision");
        }else{
            window.location.assign('<?= base_url('planning/sales_order_os/uploadedPrint/excel') ?>' + url);
        }
    }

    //Upload Data
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_sales_order_os.xls') ?>');
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        $("#filter_month").combobox('setValue', '<?= date("m") ?>');

        $('#dg').datagrid({rownumbers: true});
        $('#dg_display').datagrid({
            pagination: true,
            rownumbers: true
        }).datagrid('enableFilter');

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

                            $('#dlg_insert').dialog('close');
                            $('#dg_uploaded').datagrid('reload');
                        }
                    });
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
                        url: '<?= base_url('planning/sales_order_os/upload') ?>',
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
                            $("#dlg_upload").dialog('close');
                            $.messager.progress('close');
                            //Clear File
                            $.ajax({
                                url: "<?= base_url('planning/sales_order_os/uploadclearFailed') ?>"
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
                                        url: "<?= base_url('planning/sales_order_os/uploadCreate') ?>",
                                        data: {
                                            "data": json[number - 1]
                                        },
                                        cache: false,
                                        dataType: "json",
                                        success: function(result) {
                                            if (result.theme == "success") {
                                                Swal.close();
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
                                                    url: "<?= base_url('planning/sales_order_os/uploadcreateFailed') ?>",
                                                    data: {
                                                        data: json[number - 1],
                                                        message: result.message
                                                    },
                                                    cache: false
                                                });

                                                requestData(total, json, number + 1, value, success + 0, failed + 1);
                                            }

                                            if (value == 100) {
                                                $.messager.alert('Done', 'Data upload is complete', 'info');
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

        $('#filter_month').combobox({
            url: '<?php echo base_url('planning/mst_data/readMonths'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Month',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onChange: function(row) {
                var month = $("#filter_month").combobox('getValue');
                var year = $("#filter_year").combobox('getValue');

                $('#filter_revision').combobox({
                    url: '<?php echo base_url('planning/sales_order_os/readRevision?month='); ?>' + month + '&year=' + year,
                    valueField: 'revision',
                    textField: 'revision',
                    prompt: 'Choose Revision',
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });
            }
        });

        $('#filter_year').combobox({
            url: '<?php echo base_url('planning/mst_data/readYears'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Year',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onChange: function(row) {
                var month = $("#filter_month").combobox('getValue');
                var year = $("#filter_year").combobox('getValue');

                $('#filter_revision').combobox({
                    url: '<?php echo base_url('planning/sales_order_os/readRevision?month='); ?>' + month + '&year=' + year,
                    valueField: 'revision',
                    textField: 'revision',
                    prompt: 'Choose Revision',
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });
            }
        });

        $('#filter_product_no').combogrid({
            url: '<?= base_url('planning/mst_data/readProducts') ?>',
            panelWidth: 400,
            idField: 'product_no',
            textField: 'product_no',
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
                    field: 'product_no',
                    title: 'Item ID',
                    width: 200
                }, {
                    field: 'product_name',
                    title: 'Item Name',
                    width: 200
                }]
            ]
        });

        $('#filter_customer_id').combobox({
            url: "<?= base_url('planning/mst_data/readCustomers') ?>?setting=<?= $nik ?>",
            valueField: 'number',
            textField: 'name',
            prompt: 'Select Customer',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }]
        });

        $('#customer_id').combobox({
            url: "<?= base_url('planning/mst_data/readCustomers') ?>?setting=<?= $nik ?>",
            valueField: 'number',
            textField: 'name',
            prompt: 'Select Customer',
            onSelect: function(row) {
                $("#customer_name").textbox('setValue', row.name);
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

    function cellStyler(value, row, index) {
        if (value == "OPEN") {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }
</script>