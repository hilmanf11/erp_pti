<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'cust_name',width:300,halign:'center'">Customer</th>
            <th data-options="field:'item_id',width:200,halign:'center'">Product No</th>
            <th data-options="field:'item_name',width:200,halign:'center'">Product Name</th>
            <th data-options="field:'circuit_no',width:80,halign:'center'">CCT</th>
            <th data-options="field:'qty_1',width:80,halign:'center',align:'right'">Month 1</th>
            <th data-options="field:'qty_2',width:80,halign:'center',align:'right'">Month 2</th>
            <th data-options="field:'qty_3',width:80,halign:'center',align:'right'">Month 3</th>
            <th data-options="field:'qty_4',width:80,halign:'center',align:'right'">Month 4</th>
            <th data-options="field:'qty_5',width:80,halign:'center',align:'right'">Month 5</th>
            <th data-options="field:'qty_6',width:80,halign:'center',align:'right'">Month 6</th>
            <th data-options="field:'xbar',width:80,halign:'center',align:'right'">Xbar</th>
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

<div id="toolbar2" style="height: 35px;">
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="uploaded_edit()"><i class="fa fa-edit"></i> Edit</a>
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="deleted()"><i class="fa fa-trash"></i> Delete</a>
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="uploaded_excel()"><i class="fa fa-file"></i> Export Excel</a>
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
                <span style="width:35%; display:inline-block;">Month 1</span>
                <input style="width:30%;" name="qty_1" class="easyui-numberbox" required>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Month 2</span>
                <input style="width:30%;" name="qty_2" class="easyui-numberbox" required>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Month 3</span>
                <input style="width:30%;" name="qty_3" class="easyui-numberbox" required>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Month 4</span>
                <input style="width:30%;" name="qty_4" class="easyui-numberbox" required>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Month 5</span>
                <input style="width:30%;" name="qty_5" class="easyui-numberbox" required>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Month 6</span>
                <input style="width:30%;" name="qty_6" class="easyui-numberbox" required>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Xbar</span>
                <input style="width:30%;" name="xbar" class="easyui-numberbox" required>
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
            <!-- <div class="fitem">
                <span style="width:35%; display:inline-block;">Action</span>
                <select name="file_action" style="width: 60%;" required="" id="file_action" class="easyui-combobox" panelHeight="auto">
                    <option value="Create">Create</option>
                    <option value="Replace">Replace</option>
                </select>
            </div> -->
        </fieldset>
    </form>
</div>

<!-- Upload -->
<div id="dlg_uploaded" class="easyui-window" title="Data Uploaded SO" data-options="closed: true, modal:true,minimizable:false,collapsible:false" style="width: 800px; height: 400px; top: 40px; overflow: auto;">
    <table id="dg_uploaded" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar2">
        <thead>
            <tr>
                <th field="ck" checkbox="true"></th>
                <th data-options="field:'p_month',width:80,halign:'center'">Month</th>
                <th data-options="field:'p_year',width:80,halign:'center'">Year</th>
                <th data-options="field:'revision',width:80,halign:'center'">Revision</th>
                <th data-options="field:'customer_name',width:300,halign:'center'">Customer</th>
                <th data-options="field:'product_no',width:200,halign:'center'">Product No</th>
                <th data-options="field:'product_name',width:200,halign:'center'">Product Name</th>
                <th data-options="field:'circuit_no',width:80,align:'center'">CCT</th>
                <th data-options="field:'qty_1',width:80,halign:'center',align:'right'">Month 1</th>
                <th data-options="field:'qty_2',width:80,halign:'center',align:'right'">Month 2</th>
                <th data-options="field:'qty_3',width:80,halign:'center',align:'right'">Month 3</th>
                <th data-options="field:'qty_4',width:80,halign:'center',align:'right'">Month 4</th>
                <th data-options="field:'qty_5',width:80,halign:'center',align:'right'">Month 5</th>
                <th data-options="field:'qty_6',width:80,halign:'center',align:'right'">Month 6</th>
                <th data-options="field:'xbar',width:80,halign:'center',align:'right'">Xbar</th>
            </tr>
        </thead>
    </table>
</div>

<!-- PDF -->
<iframe id="printout" src="" style="width: 100%;" hidden></iframe>

<script>
    //Add Data
    function add() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var rows = $('#dg').datagrid('getChecked');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to save this data?', function(r) {
                if (r) {
                    $.ajax({
                        url: "<?= base_url('planning/sales_order/uploadclearFailed') ?>"
                    });

                    requestData(rows.length, rows);

                    function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                        if (value < 100) {
                            value = Math.floor((number / total) * 100);
                            $('#p_upload').progressbar('setValue', value);
                            $('#p_start').html(number);
                            $('#p_finish').html(total);

                            $.post('<?= base_url('planning/sales_order/create') ?>', {
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
                                        url: "<?= base_url('planning/sales_order/uploadcreateFailed') ?>",
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
            url_save = '<?= base_url('planning/sales_order/update') ?>?id=' + btoa(row.id);
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function downloadFailed() {
        window.open('<?= base_url('planning/sales_order/uploadDownloadFailed') ?>', '_blank');
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
            $.ajax({
                type: "post",
                url: "<?= base_url('planning/sales_order/readMonths') ?>",
                data: "month=" + window.btoa(filter_month) + "&year=" + window.btoa(filter_year),
                dataType: "json",
                success: function(monthName) {
                    $('#dg_uploaded').datagrid({
                        url: '<?= base_url('planning/sales_order/datatables_uploaded') ?>' + url,
                        rownumbers: true,
                        pagination: true,
                        clientPaging: false,
                        remoteFilter: true,
                        columns: [
                            [{
                                field: 'ck',
                                checkbox: true
                            }, {
                                field: 'customer_name',
                                title: 'Customer',
                                width: 250,
                                halign: 'center'
                            }, {
                                field: 'product_no',
                                title: 'Product No',
                                width: 150,
                                halign: 'center'
                            }, {
                                field: 'product_name',
                                title: 'Product Name',
                                width: 150,
                                halign: 'center'
                            }, {
                                field: 'circuit_no',
                                title: 'CCT',
                                width: 80,
                                align: 'center'
                            }, {
                                field: 'qty_1',
                                title: monthName[0],
                                width: 80,
                                halign: 'center',
                                align: 'right'
                            }, {
                                field: 'qty_2',
                                title: monthName[1],
                                width: 80,
                                halign: 'center',
                                align: 'right'
                            }, {
                                field: 'qty_3',
                                title: monthName[2],
                                width: 80,
                                halign: 'center',
                                align: 'right'
                            }, {
                                field: 'qty_4',
                                title: monthName[3],
                                width: 80,
                                halign: 'center',
                                align: 'right'
                            }, {
                                field: 'qty_5',
                                title: monthName[4],
                                width: 80,
                                halign: 'center',
                                align: 'right'
                            }, {
                                field: 'qty_6',
                                title: monthName[5],
                                width: 80,
                                halign: 'center',
                                align: 'right'
                            }, {
                                field: 'xbar',
                                title: "Xbar",
                                width: 80,
                                halign: 'center',
                                align: 'right'
                            }]
                        ]
                    }).datagrid('enableFilter');
                }
            });
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
                            url: '<?= base_url('planning/sales_order/delete') ?>',
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

    function filter() {
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

        if(filter_month == "" || filter_year == ""){
            toastr.info("Please Select Period and Revision");
        }else{
            $.ajax({
                type: "post",
                url: "<?= base_url('planning/sales_order/readMonths') ?>",
                data: "month=" + window.btoa(filter_month) + "&year=" + window.btoa(filter_year),
                dataType: "json",
                success: function(monthName) {
                    $('#dg').datagrid({
                        url: '<?= base_url('planning/sales_order/datatables') ?>' + url,
                        columns: [
                            [{
                                field: 'ck',
                                checkbox: true
                            }, {
                                field: 'cust_name',
                                title: 'Customer',
                                width: 300,
                                halign: 'center'
                            }, {
                                field: 'item_id',
                                title: 'Product No',
                                width: 200,
                                halign: 'center'
                            }, {
                                field: 'item_name',
                                title: 'Product Name',
                                width: 200,
                                halign: 'center'
                            }, {
                                field: 'circuit_no',
                                title: 'CCT',
                                width: 80,
                                align: 'center'
                            }, {
                                field: 'qty_1',
                                title: monthName[0],
                                width: 80,
                                halign: 'center',
                                align: 'right'
                            }, {
                                field: 'qty_2',
                                title: monthName[1],
                                width: 80,
                                halign: 'center',
                                align: 'right'
                            }, {
                                field: 'qty_3',
                                title: monthName[2],
                                width: 80,
                                halign: 'center',
                                align: 'right'
                            }, {
                                field: 'qty_4',
                                title: monthName[3],
                                width: 80,
                                halign: 'center',
                                align: 'right'
                            }, {
                                field: 'qty_5',
                                title: monthName[4],
                                width: 80,
                                halign: 'center',
                                align: 'right'
                            }, {
                                field: 'qty_6',
                                title: monthName[5],
                                width: 80,
                                halign: 'center',
                                align: 'right'
                            }, {
                                field: 'xbar',
                                title: "Xbar",
                                width: 80,
                                halign: 'center',
                                align: 'right'
                            }]
                        ]
                    });
                }
            });
        }
    }

    //Upload Data
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_sales_order.xls') ?>');
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

        if(filter_month == "" || filter_year == ""){
            toastr.info("Please Select Period and Revision");
        }else{
            $.messager.alert('Export to Excel', 'Please wait until the excel download appears');
            window.location.assign('<?= base_url('planning/sales_order/print/excel') ?>' + url);
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
            window.location.assign('<?= base_url('planning/sales_order/uploadedPrint/excel') ?>' + url);
        }
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        $("#filter_month").combobox('setValue', '<?= date("m") ?>');
        $('#dg').datagrid({
            url: '<?= base_url('planning/sales_order/datatables') ?>',
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        });

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

        $('#dlg_upload').dialog({
            buttons: [{
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('planning/sales_order/upload') ?>',
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
                            $('#dlg_upload').dialog('close');

                            //Clear File
                            $.ajax({
                                url: "<?= base_url('planning/sales_order/uploadclearFailed') ?>"
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
                                        url: "<?= base_url('planning/sales_order/uploadCreate') ?>",
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
                                                    url: "<?= base_url('planning/sales_order/uploadcreateFailed') ?>",
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
                    url: '<?php echo base_url('planning/sales_order/readRevision?month='); ?>' + month + '&year=' + year,
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
                    url: '<?php echo base_url('planning/mst_forecast/readRevision?month='); ?>' + month + '&year=' + year,
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

        $('#filter_customer_id').combobox({
            url: '<?php echo base_url('planning/mst_data/readCustomers'); ?>',
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
            url: '<?php echo base_url('planning/mst_data/readCustomers'); ?>',
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
</script>