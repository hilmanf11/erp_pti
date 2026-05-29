<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
    <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'status',width:80,align:'center',styler:cellStyler, formatter:cellFormatter">Convert PR</th>
            <th rowspan="2" data-options="field:'confirm',width:80,align:'center',styler:cellStyler2, formatter:cellFormatter2">Status</th>
            <th rowspan="2" data-options="field:'request_no',width:120,halign:'center'">Request No</th>
            <th rowspan="2" data-options="field:'request_by',width:150,halign:'center'">Request By</th>
            <th rowspan="2" data-options="field:'request_date',width:100,align:'center'">Request Date</th>
            <th rowspan="2" data-options="field:'category',width:100,align:'center'">Category</th>
            <th rowspan="2" data-options="field:'request_reason',width:250,halign:'center'">Remarks</th>
            <th rowspan="2" data-options="field:'total_qty',width:100,halign:'center',align:'right',formatter:numberformat">Total Qty</th>
            <th rowspan="2" data-options="field:'detail',width:80,halign:'center',formatter:BtnDetail">Detail</th>
            <th colspan="3" data-options="field:'approval',width:100,halign:'center'">Approved</th>
            <th colspan="2" data-options="field:'create',width:100,halign:'center'">Created</th>
        </tr>
        <tr>
            <th data-options="field:'approved_to',width:80,align:'center', styler:cellStylerApproval, formatter:cellFormatterApproval">Status</th>
            <th data-options="field:'approved_by',width:80,align:'center'">By</th>
            <th data-options="field:'approved_date',width:120,align:'center'">Date</th>
            <th data-options="field:'created_by',width:80,align:'center'">By</th>
            <th data-options="field:'created_date',width:120,align:'center'">Date</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 200px; padding:10px;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 70%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Request Date</span>
                    <input style="width:28%;" id="filter_from" class="easyui-datebox" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                    <input style="width:28%;" id="filter_to" class="easyui-datebox" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Request No</span>
                    <input style="width:60%;" id="filter_request_no" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Request By</span>
                    <input style="width:60%;" id="filter_request_by" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part No</span>
                    <input style="width:60%;" id="filter_item_npd_id" class="easyui-textbox">
                </div>
            </div>
        </fieldset>
    </div>
    <?= $button ?>
</div>

<div id="toolbar3">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>

<div id="dlg_request" class="easyui-dialog" title="Detail Request NPD" data-options="closed: true,modal:true" style="width: 96%; height: 550px; top: 0px; left: 0px;">
    <table id="dg_request" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar2" showFooter="true">
        <thead>
            <tr>
                <th rowspan="2" field="ck" checkbox="true"></th>
                <th rowspan="2" data-options="field:'item_npd_id',width:150,halign:'center'">Part No</th>
                <th rowspan="2" data-options="field:'item_npd_name',width:150,halign:'center'">Part Name</th>
                <th rowspan="2" data-options="field:'product_family',width:100,halign:'center'">Product Family</th>
                <th rowspan="2" data-options="field:'maker',width:200,halign:'center'">Maker</th>
                <th rowspan="2" data-options="field:'uom',width:80,align:'center'">Uom</th>
                <th rowspan="2" data-options="field:'qty',width:100,halign:'center',align:'right',formatter:numberformat">Qty</th>
                <th rowspan="2" data-options="field:'requirement_date',width:100,align:'center'">Requirement<br>Date</th>
                <th colspan="4" data-options="field:'procurement',width:100,halign:'center'">Confirmation</th>
                <th colspan="2" data-options="field:'procurement',width:100,halign:'center'">Procurement</th>
            </tr>
            <tr>
                <th data-options="field:'confirm',width:80,align:'center', styler:cellStylerConfirm, formatter:cellFormatterConfirm">Status</th>
                <th data-options="field:'confirm_date',width:130,halign:'center'">Date</th>
                <th data-options="field:'confirm_by',width:150,halign:'center'">By</th>
                <th data-options="field:'confirm_reason',width:200,halign:'center'">Reason</th>
                <th data-options="field:'item_id',width:150,halign:'center'">Part No</th>
                <th data-options="field:'supplier_name',width:200,halign:'center'">Supplier Name</th>
            </tr>
        </thead>
    </table>
</div>

<!-- DIALOG SAVE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1100px; height: 550px; padding:10px; top: 20px; left:10px;">
    <form id="frm_insert" method="post" novalidate>
        <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px;">
                <legend><b>Form Data</b></legend>
                <div style="float: left; width: 50%;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Request No</span>
                        <input style="width:60%;" name="request_no" id="request_no" readonly class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Request Date</span>
                        <input style="width:60%;" name="request_date" id="request_date" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Request By</span>
                        <input style="width:60%;" name="request_by" id="request_by" readonly class="easyui-textbox">
                    </div>
                </div>
                <div style="float: left; width: 50%;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Category</span>
                        <select style="width:60%;" name="category" id="category" class="easyui-combobox" panelHeight="auto">
                            <option value="SAMPLE">SAMPLE</option>
                            <option value="STUDY">STUDY PART</option>
                            <option value="DIEGO">DIE GO</option>
                            <option value="OTHER">OTHER</option>
                        </select>
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Remarks</span>
                        <input style="width:60%; height: 60px;" data-options="multiline:true" name="request_reason" id="request_reason" required class="easyui-textbox">
                    </div>
                </div>
            </fieldset>
        </div>

        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Add Part Request NPD" toolbar="#toolbar3" data-options="singleSelect: true"></table>
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
<iframe id="printout" src="" style="width: 100%;" hidden></iframe>

<script>
    function add() {
        $('#dlg_insert').dialog('open');
        $('#frm_insert').form('clear');

        $('#dg2').datagrid('loadData', []);
        $("#request_date").datebox('setValue', "<?= date("Y-m-d") ?>");
        $("#request_by").textbox('setValue', "<?= $this->session->name ?>");
        addTable();
        autoId();
    }

    function autoId(){
        $.ajax({
            type: "post",
            url: "<?= base_url("planning/request_npd/request_no/") ?>",
            dataType: "json",
            success: function (response) {
                $("#request_no").textbox('setValue', response.name);
            }
        });
    }

    //INSERT ADD ROW
    function addTable(urlGet = "") {
        var lastIndex;
        var dg = $('#dg2').datagrid({
            url: urlGet,
            columns: [
                [{
                    field: 'id',
                    width: 200,
                    halign: 'center',
                    title: "ID",
                    hidden: true,
                    editor: {
                        type: 'textbox'
                    }
                },{
                    field: 'item_npd_id',
                    width: 200,
                    halign: 'center',
                    title: "Part No",
                    editor: {
                        type: 'textbox',
                        options: {
                            required: true,
                        }
                    }
                }, {
                    field: 'item_npd_name',
                    width: 150,
                    halign: 'center',
                    title: "Part Name",
                    editor: {
                        type: 'textbox',
                        options: {
                            required: true,
                        }
                    }
                }, {
                    field: 'product_family',
                    width: 150,
                    halign: 'center',
                    title: "Product Family",
                    editor: {
                        type: 'combobox',
                        options: {
                            url: '<?php echo base_url('planning/generate_mrp/readProductFamily'); ?>',
                            valueField: 'pfm_name',
                            textField: 'pfm_name',
                            prompt: 'Select Product Family',
                            required: true,
                        }
                    }
                }, {
                    field: 'maker',
                    width: 200,
                    halign: 'center',
                    title: "Maker",
                    editor: {
                        type: 'combobox',
                        options: {
                            url: '<?php echo base_url('planning/mst_maker/reads'); ?>',
                            valueField: 'name',
                            textField: 'name',
                            prompt: 'Select Maker',
                            required: true,
                        }
                    }
                }, {
                    field: 'qty',
                    width: 80,
                    halign: 'center',
                    title: "Qty",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                        }
                    }
                }, {
                    field: 'uom',
                    width: 80,
                    halign: 'center',
                    title: "UoM",
                    editor: {
                        type: 'combobox',
                        options: {
                            url: '<?php echo base_url('planning/mst_item/readUom'); ?>',
                            valueField: 'name',
                            textField: 'name',
                            prompt: 'Select Unit of Measure',
                            required: true,
                        }
                    }
                }, {
                    field: 'requirement_date',
                    width: 150,
                    halign: 'center',
                    title: "Requirement Date",
                    editor: {
                        type: 'datebox',
                        options: {
                            required: true,
                            formatter:myformatter,
                            parser:myparser, 
                            editable:false
                        }
                    }
                }]
            ],
            onClickRow: function(rowIndex) {
                if (lastIndex != rowIndex) {
                    $(this).datagrid('endEdit', lastIndex);
                    $(this).datagrid('beginEdit', rowIndex);
                }
                lastIndex = rowIndex;
            },
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
        if (endEditing()) {
            $('#dg2').datagrid('appendRow', {
                qty: '0',
                uom: 'PCS',
                requirement_date: "<?= date("Y-m-d") ?>"
            });
            editIndex = $('#dg2').datagrid('getRows').length - 1;
            $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
        }
    }

    function removeit() {
        if (editIndex == undefined) {
            return
        }
        $('#dg2').datagrid('cancelEdit', editIndex).datagrid('deleteRow', editIndex);
        editIndex = undefined;
    }

    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            if (row.total_status == 1) {
                $('#dlg_insert').dialog('open');
                $('#frm_insert').form('load', row);
                
                addTable('<?= base_url('planning/request_npd/datatableUpdates?request_no=') ?>' + window.btoa(row.request_no));
            }else{
                toastr.warning("Cannot Update because status already close");
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function details(request_no) {
        $("#dlg_request").dialog('open');
        var filter_item_npd_id = $("#filter_item_npd_id").textbox('getValue');
        
        $('#dg_request').datagrid({
            url: '<?= base_url('planning/request_npd/datatableDetails/') ?>' + btoa(request_no) + "?filter_item_npd_id=" + btoa(filter_item_npd_id),
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            singleSelect: false,
            fit:true,
        });
    }

    function detailEdit(){
        var row = $('#dg_request').datagrid('getSelected');
        if (row) {
            $('#dlg_update').dialog('open');
            $('#frm_update').form('load', row);

            url_save = '<?= base_url('planning/request_npd/updateDetails') ?>?id=' + btoa(row.id);
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        if(row.total_status > 0){
                            $.ajax({
                                method: 'post',
                                url: '<?= base_url('planning/request_npd/delete') ?>',
                                data: {
                                    request_no: row.request_no
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
                        }else{
                            toastr.error("Cannot delete data because data has been push to MRP");
                        }
                    }
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_request_no = $("#filter_request_no").textbox('getValue');
        var filter_request_by = $("#filter_request_by").textbox('getValue');
        var filter_item_npd_id = $("#filter_item_npd_id").textbox('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_request_no=" + window.btoa(filter_request_no) +
            "&filter_request_by=" + window.btoa(filter_request_by) +
            "&filter_item_npd_id=" + window.btoa(filter_item_npd_id);

        if (filter_from == "" || filter_to == "") {
            toastr.warning("Please Choose Filter Date!", "Information");
        } else {
            $('#dg').datagrid({
                url: '<?= base_url('planning/request_npd/datatables') ?>' + url,
                pagination: true,
                clientPaging: false,
                remoteFilter: true,
                rownumbers: true,
                singleSelect: true,
                fit:true,
            });

            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('planning/request_npd/print') ?>' + url);
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_request_no = $("#filter_request_no").textbox('getValue');
        var filter_request_by = $("#filter_request_by").textbox('getValue');
        var filter_item_npd_id = $("#filter_item_npd_id").textbox('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_request_no=" + window.btoa(filter_request_no) +
            "&filter_request_by=" + window.btoa(filter_request_by) +
            "&filter_item_npd_id=" + window.btoa(filter_item_npd_id);

        if (filter_from == "" || filter_to == "") {
            toastr.warning("Please Choose Filter Date!", "Information");
        } else {
            window.location.assign('<?= base_url('planning/request_npd/print/excel') ?>' + url);
        }
    }

    //Upload Data
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_request_npd.xls') ?>');
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        filter();

        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    var request_no = $("#request_no").textbox('getValue');
                    var request_date = $("#request_date").datebox('getValue');
                    var request_by = $("#request_by").textbox('getValue');
                    var request_reason = $("#request_reason").textbox('getValue');
                    var category = $("#category").combobox('getValue');

                    if (request_reason == "" || request_date == "" || category == "") {
                        toastr.error("please complete your input data");
                    } else {
                        $('#dg2').datagrid('acceptChanges');
                        var rows = $('#dg2').datagrid('getRows');
                        var totalrows = rows.length;
                        endEditing();

                        for (let i = 0; i < totalrows; i++) {
                            if (rows[i].item_npd_id) {
                                $.ajax({
                                    type: "post",
                                    url: '<?= base_url('planning/request_npd/create') ?>',
                                    data: {
                                        request_no: request_no,
                                        request_date: request_date,
                                        request_by: request_by,
                                        category: category,
                                        request_reason: request_reason,
                                        id: rows[i].id,
                                        item_npd_id: rows[i].item_npd_id,
                                        item_npd_name: rows[i].item_npd_name,
                                        product_family: rows[i].product_family,
                                        maker: rows[i].maker,
                                        qty: rows[i].qty,
                                        uom: rows[i].uom,
                                        requirement_date: rows[i].requirement_date
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
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('planning/request_npd/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('planning/request_npd/upload') ?>',
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
                            // $('#dlg_upload').dialog('close');
                            //Clear File
                            $.ajax({
                                url: "<?= base_url('planning/request_npd/uploadclearFailed') ?>"
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
                                        url: "<?= base_url('planning/request_npd/uploadCreate') ?>",
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
                                                    url: "<?= base_url('planning/request_npd/uploadcreateFailed') ?>",
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

    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0
        });

        return "<b>" + formatter.format(value) + "</b>";
    }

    function BtnDetail(val, row) {
        var details = "details('"+row.request_no+"')";
        return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" onClick="'+details+'"><i class="fa fa-eye"></i> View</a>';
    }

    function cellStyler(value, row, index) {
        if (row.total_status == 0) {
            return 'background: #FF5F5F; color:white;';
        } else if (row.total_status == 1) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    function cellFormatter(val, row) {
        if (row.total_status == 0) {
            return 'CLOSE';
        } else if (row.total_status == 1) {
            return 'OPEN';
        } else {
            return 'CANCEL';
        }
    };

    function cellStyler2(value, row, index) {
        if (row.total_data == row.total_confirm) {
            return 'background: #FF5F5F; color:white;';
        } else {
            return 'background: #d6a336ff; color:white;';
        }
    }

    function cellFormatter2(val, row) {
        if (row.total_data == row.total_confirm) {
            return 'CLOSE';
        } else {
            return 'PENDING';
        }
    };

    function cellStylerApproval(value, row, index) {
        if (row.approved_to == null) {
            return 'background: #FF5F5F; color:white;';
        } else if(row.approved_to == ""){
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    function cellFormatterApproval(val, row) {
        if (row.approved_to == null) {
            return 'Not Setting';
        } else if(row.approved_to == ""){
            return 'Approved';
        } else {
            return 'Checking';
        }
    };

    function cellStylerConfirm(value, row, index) {
        if (value == null || value == "") {
            return 'background: #6d83ffff; color:white;';
        } else if(value == "CONFIRMED"){
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    function cellFormatterConfirm(value, row) {
        if (value == null || value == "") {
            return 'PENDING';
        } else if(value == "CONFIRMED"){
            return 'CONFIRMED';
        } else {
            return value;
        }
    };
</script>