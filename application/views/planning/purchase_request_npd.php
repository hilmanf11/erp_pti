<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
                <th rowspan="2" field="ck" checkbox="true"></th>
                <th rowspan="2" data-options="field:'purchase_request_no',width:180,halign:'center'">PR No</th>
                <th rowspan="2" data-options="field:'purchase_request_date',width:100,halign:'center'">PR Date</th>
                <th rowspan="2" data-options="field:'supplier_name',width:250,halign:'center'">Vendor Name</th>
                <th rowspan="2" data-options="field:'product_family',width:120,halign:'center'">Product Family</th>
                <th rowspan="2" data-options="field:'total_pr',width:100,halign:'center',align:'right',formatter:numberformat">Total PR</th>
                <th rowspan="2" data-options="field:'detail',width:80,halign:'center',formatter:BtnDetail">Detail</th>
                <th colspan="2" data-options="field:'',width:100,halign:'center'">Create PO</th>
                <th colspan="3" data-options="field:'',width:100,halign:'center'">Approved</th>
                <th colspan="2" data-options="field:'',width:100,halign:'center'">Created</th>
            </tr>
            <tr>
                <th data-options="field:'send',width:120,align:'center',formatter:BtnSend">Create</th>
                <th data-options="field:'create_po',width:150,align:'center'">Date</th>
                <th data-options="field:'approved_to',width:80,align:'center', styler:cellStylerApproval, formatter:cellFormatterApproval">Status</th>
                <th data-options="field:'approved_by',width:100,align:'center'">By</th>
                <th data-options="field:'approved_date',width:120,align:'center'">Date</th>
                <th data-options="field:'created_by',width:100,align:'center'">By</th>
                <th data-options="field:'created_date',width:120,align:'center'">Date</th>
            </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar3" style="height: auto; padding:10px;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Detail</b></legend>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Purchase Request No</span>
                    <input style="width:60%;" id="d_purchase_request_no" class="easyui-textbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" id="detailEdit" class="easyui-linkbutton" onclick="detailEdit()"><i class="fa fa-edit"></i> Edit Data</a>
                </div>
            </div>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier Name</span>
                    <input style="width:60%;" id="d_supplier" class="easyui-textbox" readonly>
                </div>
            </div>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" id="d_product_family" class="easyui-textbox" readonly>
                </div>
            </div>
        </fieldset>
    </div>
</div>

<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>

<div id="toolbar" style="height: 195px; padding:10px;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 60%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Request Date</span>
                    <input style="width:28%;" id="filter_from" class="easyui-datebox" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                    <input style="width:28%;" id="filter_to" class="easyui-datebox" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Vendor Name</span>
                    <input style="width:60%;" id="filter_supplier" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" id="filter_product_family" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Item ID</span>
                    <input style="width:60%;" id="filter_part_no" class="easyui-combogrid">
                </div>
            </div>
        </fieldset>
        <fieldset style="width: 30%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Generating Process</b></legend>

            <b>Calculation Share Order</b>
            <div id="p_upload" class="easyui-progressbar" style="width:100%;"></div>
            <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
        </fieldset>
    </div>
    <?= $button ?>
</div>

<div id="dlg_update" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 800px; padding:10px; top: 20px;">
    <form id="frm_update" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="float: left; width: 50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:30%;" name="p_month" disabled class="easyui-combobox" required data-options="prompt:'Month'">
                    <input style="width:30%;" name="p_year" disabled class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Revision</span>
                    <select style="width:60%;" name="revision" disabled required class="easyui-combobox" panelHeight="auto">
                        <option value="0">0</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                    </select>
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Vendor Name</span>
                    <input style="width:60%;" name="supplier_no" disabled class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Vendor Name</span>
                    <input style="width:60%;" name="supplier_name" disabled class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" name="product_family" disabled class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Item ID</span>
                    <input style="width:60%;" name="part_no" disabled class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Item Name</span>
                    <input style="width:60%;" name="part_name" disabled class="easyui-textbox">
                </div>
            </div>
            <div style="float: left; width: 50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Purchase Request No</span>
                    <input style="width:60%;" name="purchase_request_no" id="update_purchase_request_no" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">MPQ / MOQ</span>
                    <input style="width:30%;" name="mpq" id="mpq" readonly class="easyui-numberbox">
                    <input style="width:30%;" name="moq" id="moq" readonly class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Share Order</span>
                    <input style="width:30%;" name="share_order" id="share_order" readonly class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Leadtime</span>
                    <input style="width:30%;" name="leadtime" id="leadtime" readonly class="easyui-numberbox">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">MRP Result</span>
                    <input style="width:25%;" name="purchase_order" id="purchase_order" required="true" readonly class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Purchase Request</span>
                    <input style="width:25%;" id="purchase_request_edit" required="true" class="easyui-numberbox">
                    <input style="width:25%;" name="purchase_request" id="purchase_request" required="true" readonly class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Reason</span>
                    <input style="width:50%;" name="reason" id="reason" required class="easyui-textbox">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Status</span>
                    <input style="width:50%;" name="status" id="status" required class="easyui-textbox">
                </div>
            </div>
        </fieldset>
    </form>
</div>

<div id="dlg_purchase_request" class="easyui-dialog" title="Detail Purchase Request" data-options="closed: true,modal:true" style="width: 100%; height: 600px; top: 0px; left: 0px;">
    <table id="dg_purchase_request" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar3" showFooter="true"></table>
</div>

<!-- PDF -->
<iframe id="printout" src="" style="width: 100%;" hidden></iframe>

<script>
    function add() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_supplier = $("#filter_supplier").combobox('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');
        var filter_part_no = $("#filter_part_no").combogrid('getValue');

        if (filter_from != "" && filter_to != "") {
            $.messager.prompt('Generate PR', 'Please input Password Generate, Be careful if data is generated for this period it will be deleted', function(r){
                if (r == "PURCHASEREQUEST"){
                    Swal.fire({
                        title: 'Please Wait for Generating Data',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                    });

                    $.ajax({
                        type: "get",
                        url: "<?= base_url('planning/purchase_request_npd/getDataMrp') ?>",
                        data: "filter_from=" + window.btoa(filter_from) +
                            "&filter_to=" + window.btoa(filter_to) +
                            "&filter_product_family=" + window.btoa(filter_product_family) +
                            "&filter_supplier=" + window.btoa(filter_supplier) +
                            "&filter_product_no=" + window.btoa(filter_part_no),
                        dataType: "json",
                        success: function(rows) {
                            Swal.close();

                            if(rows['total'] > 0){
                                requestData(rows['total'], rows);

                                function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                                    if (value < 100) {
                                        value = Math.floor((number / total) * 100);
                                        $('#p_upload').progressbar('setValue', value);
                                        $('#p_start').html(number);
                                        $('#p_finish').html(total);

                                        $.post('<?= base_url('planning/purchase_request_npd/create') ?>', {
                                            data: json[number - 1]
                                        }, function(note) {
                                            var result = eval('(' + note + ')');

                                            if (result.theme == "success") {
                                                requestData(total, json, number + 1, value, success + 1, failed + 0);
                                            } else {
                                                requestData(total, json, number + 1, value, success + 0, failed + 1);
                                            }

                                            if (value == 100) {
                                                Swal.fire('Good job!', 'Process Generate Data Completed!', 'success');
                                                filter();
                                            }
                                        }).fail(function(jqXHR, textStatus) {
                                            if (textStatus == "error") {
                                                toastr.error("Connection time out");

                                                requestData(total, json, number, value, success + 0, failed + 1);
                                            }
                                        });
                                    }
                                }
                            }else{
                                toastr.warning("Data Request NPD Not Found");
                            }
                        },
                        error: function(rows) {
                            Swal.fire('Failed!', 'Generate Purchase Requst is Failed, Please Try Again!', 'error');
                        }
                    });
                }
            });

            $('.messager-input').attr('type', 'password');
        } else {
            Swal.close();
            toastr.warning("Please Select Period & Revision", "Information");
        }
    }

    //Edit Data
    function update() {
        toastr.info("Not Ready this Function");
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
                            url: '<?= base_url('planning/purchase_request_npd/delete') ?>',
                            data: {
                                purchase_request_no: row.purchase_request_no
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
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');
        var filter_part_no = $("#filter_part_no").combogrid('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_product_family=" + window.btoa(filter_product_family) +
            "&filter_part_no=" + window.btoa(filter_part_no);

        if (filter_from == "" || filter_to == "") {
            toastr.warning("Please Choose Filter Date!", "Information");
        } else {
            $('#dg').datagrid({
                url: '<?= base_url('planning/purchase_request_npd/datatables') ?>' + url,
            });

            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('planning/purchase_request_npd/print') ?>' + url);
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');
        var filter_part_no = $("#filter_part_no").combogrid('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_product_family=" + window.btoa(filter_product_family) +
            "&filter_part_no=" + window.btoa(filter_part_no);

        if (filter_from == "" || filter_to == "") {
            toastr.warning("Please Choose Filter Date!", "Information");
        } else {
            window.location.assign('<?= base_url('planning/purchase_request_npd/print/excel') ?>' + url);
        }
    }

    function reload() {
        window.location.reload();
    }

    function detailEdit(){
        var row = $('#dg_purchase_request').datagrid('getSelected');
        
        if (row) {
            $('#frm_update').form('load', row);

            if(row.approved_to == ""){
                toastr.info("Sorry, you cannot edit the data as the status is already approved");
                return false;
            }

            $('#dlg_update').dialog('open');
            $('#update_purchase_request_no').combogrid({
                url: '<?= base_url('planning/purchase_request_npd/readPr/') ?>' + btoa(row.supplier_id) + '/' + btoa(row.product_family),
                panelWidth: 500,
                idField: 'purchase_request_no',
                textField: 'purchase_request_no',
                mode: 'remote',
                fitColumns: true,
                prompt: "Select Purchase Request No",
                columns: [
                    [{
                        field: 'purchase_request_no',
                        title: 'PR No',
                        width: 200
                    }, {
                        field: 'supplier_name',
                        title: 'Vendor Name',
                        width: 250
                    }]
                ]
            });

            $("#update_purchase_request_no").combogrid('setValue', row.purchase_request_no);

            $("#purchase_request_edit").numberbox({
                onChange: function(val){
                    // if(parseInt(val) <= parseInt(row.purchase_order)){
                        // if(parseInt(val) == 0){
                            var hasil = parseInt(val);
                        // }else{
                        //     if(parseInt(val) > row.moq){
                        //         var hasil = (Math.ceil(parseInt(val) / row.mpq) * row.mpq);
                        //     }else{
                        //         var hasil = (Math.ceil(parseInt(val) / row.moq) * row.moq);
                        //     }
                        // }
                            
                        $("#purchase_request").numberbox('setValue', hasil);
                    // }else{
                    //     toastr.error("Your Qty > Mrp Results");
                    //     $("#purchase_request").numberbox('setValue', row.purchase_order);
                    // }
                }
            });
            
            $('#status').textbox('setValue', "EDIT");
            // $('#p_month').combobox('disable');
            // $('#p_year').combobox('disable');
            // $('#revision').combobox('disable');
            // $('#supplier_no').combobox('disable');
            // $('#product_family').combobox('disable');
            // $('#part_no').combogrid('disable');
            // $('#part_name').textbox('disable');

            url_save = '<?= base_url('planning/purchase_request_npd/update') ?>?id=' + btoa(row.id);
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function details(purchase_request_no, supplier, product_family, push="") {
        $("#dlg_purchase_request").dialog('open');

        $("#d_purchase_request_no").textbox('setValue', purchase_request_no);
        $("#d_supplier").textbox('setValue', supplier);
        $("#d_product_family").textbox('setValue', product_family);

        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_part_no = $("#filter_part_no").combogrid('getValue');

        $.ajax({
            type: "get",
            url: "<?= base_url('planning/purchase_request_npd/readMonths') ?>",
            data: "month=" + filter_from + "&year=" + filter_to,
            dataType: "json",
            success: function(monthName) {
                $('#dg_purchase_request').datagrid('disableFilter');

                $('#dg_purchase_request').datagrid({
                    url: '<?= base_url('planning/purchase_request_npd/datatableDetails/') ?>' + btoa(purchase_request_no) + "?filter_part_no=" + btoa(filter_part_no) + "&filter_from=" + btoa(filter_from) + "&filter_to=" + btoa(filter_to),
                    clientPaging: false,
                    // remoteFilter: true,
                    rownumbers: true,
                    singleSelect: true,
                    fit:true,
                    columns: [
                        [{
                            field: 'ck',
                            checkbox: true,
                            //rowspan: 2
                        }, {
                            field: 'approved_to',
                            title: 'Status',
                            width: 80,
                            align: 'center',
                            styler:cellStylerApproval, 
                            formatter:cellFormatterApproval
                            //rowspan: 2
                        }, {
                            field: 'part_no',
                            title: 'Item ID',
                            width: 200,
                            halign: 'center',
                            //rowspan: 2
                        }, {
                            field: 'part_name',
                            title: 'Item Name',
                            width: 200,
                            halign: 'center',
                            //rowspan: 2
                        }, {
                            field: 'share_order',
                            title: 'Share<br>Order',
                            width: 80,
                            halign: 'center',
                            align: 'right',
                            //rowspan: 2
                        }, {
                            field: 'mpq',
                            title: 'MPQ',
                            width: 80,
                            halign: 'center',
                            align: 'right',
                            //rowspan: 2
                        },{
                            field: 'moq',
                            title: 'MOQ',
                            width: 80,
                            halign: 'center',
                            align: 'right',
                            //rowspan: 2
                        },{
                            field: 'leadtime',
                            title: 'Leadtime',
                            width: 80,
                            halign: 'center',
                            align: 'right',
                            //rowspan: 2
                        },{
                            field: 'total_need',
                            title: 'Total Need',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            //rowspan: 2,
                            formatter: numberformat
                        // },{
                        //     field: 'purchase_order',
                        //     title: 'MRP Result',
                        //     width: 100,
                        //     halign: 'center',
                        //     align: 'right',
                        //     //rowspan: 2,
                        //     formatter: numberformat
                        },{
                            field: 'purchase_request',
                            title: 'Purchase<br>Request',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            //rowspan: 2,
                            formatter: numberformat
                        }, {
                            field: 'reason',
                            title: 'Reason',
                            width: 200,
                            halign: 'center',
                            //rowspan: 2,
                        }, {
                            field: 'status',
                            title: 'Status',
                            width: 80,
                            align: 'center',
                            //rowspan: 2,
                        }]
                    ]
                });

                $('#dg_purchase_request').treegrid('enableFilter');
            }
        });
    }

    function sendPo(purchase_request_no) {
        $.messager.prompt('Generate PO', 'Please input Password Generate, Be careful if data is generated for this period it will be deleted', function(r){
            if (r == "PURCHASEORDER"){
                Swal.fire({
                    title: 'Please Wait for Generating Data',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });

                $.ajax({
                    type: "get",
                    url: "<?= base_url('planning/purchase_order_npd/getData') ?>",
                    data: "purchase_request_no=" + window.btoa(purchase_request_no),
                    dataType: "json",
                    success: function(rows) {
                        Swal.close();

                        if(rows['total'] > 0){
                            requestData(rows['total'], rows);

                            function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                                if (value < 100) {
                                    value = Math.floor((number / total) * 100);
                                    $('#p_upload').progressbar('setValue', value);
                                    $('#p_start').html(number);
                                    $('#p_finish').html(total);

                                    $.post('<?= base_url('planning/purchase_order_npd/create') ?>', {
                                        data: json[number - 1]
                                    }, function(note) {
                                        var result = eval('(' + note + ')');

                                        if (result.theme == "success") {
                                            requestData(total, json, number + 1, value, success + 1, failed + 0);
                                        } else {
                                            requestData(total, json, number + 1, value, success + 0, failed + 1);
                                        }

                                        if (value == 100) {
                                            Swal.fire('Good job!', 'Process Generate Data Completed!', 'success');
                                            $("#dg").datagrid('reload');
                                        }
                                    }).fail(function(jqXHR, textStatus) {
                                        if (textStatus == "error") {
                                            toastr.error("Connection time out");

                                            requestData(total, json, number, value, success + 0, failed + 1);
                                        }
                                    });
                                }
                            }
                        }else{
                            toastr.warning("Data Purchase Requests Not Found");
                        }
                    },
                    error: function(rows) {
                        Swal.close();
                        Swal.fire('Failed!', 'Generate Purchase Requst is Failed, Please Try Again!', 'error');
                    }
                });
            }else{
                toastr.error("Password not Match");
            }
        });
    }

    $(function() {
    	$("#add").html("Convert NPD to PR");

        $('#dg').datagrid({
            url: '<?= base_url('planning/purchase_request_npd/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        });

        $('#dlg_update').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_update').form('submit', {
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

                            $('#dlg_update').dialog('close');
                            $('#dg_purchase_request').datagrid('reload');
                        }
                    });
                }
            }]
        });

        $('#filter_supplier').combobox({
            url: '<?php echo base_url('planning/purchase_request_npd/readSupplier'); ?>',
            valueField: 'supplier_id',
            textField: 'supplier_name',
            prompt: 'Select Supplier',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $('#filter_product_family').combobox({
            url: '<?php echo base_url('planning/generate_mrp/readProductFamily'); ?>',
            valueField: 'pfm_name',
            textField: 'pfm_name',
            prompt: 'Select Product Family',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(row){
                $('#filter_part_no').combogrid({
                    url: '<?= base_url('planning/generate_mrp/readParts/') ?>' + row.pfm_id,
                    panelWidth: 400,
                    idField: 'item_id',
                    textField: 'item_id',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Select Part No",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                        }
                    }],
                    columns: [
                        [{
                            field: 'item_id',
                            title: 'Part No',
                            width: 200
                        }, {
                            field: 'item_name',
                            title: 'Part Name',
                            width: 200
                        }]
                    ]
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

    function cellStyler(value, row, index) {
        if (value == "OPEN") {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0
        });

        return formatter.format(value);
    }

    function BtnDetail(val, row) {
        var details = "details('"+row.purchase_request_no+"','"+row.supplier_name+"','"+row.product_family+"')";
        return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" onClick="'+details+'"><i class="fa fa-eye"></i> View</a>';
    }

    function BtnSend(val, row) {
        if (row.approved_to == null || row.approved_to == "") {
            var send = "sendPo('"+row.purchase_request_no+"')";
            return '<a class="btn btn-success w-100" style="pointer-events: visible; opacity:1;" onClick="'+send+'"><i class="fa fa-plus"></i> Create PO</a>';
        } else {
            return '-';
        }
    }

    function BtnConfirm(val, row) {
        var confirm = "confirm('"+row.purchase_request_no+"','"+row.item_id+"')";
        return '<a class="btn btn-success w-100" style="pointer-events: visible; opacity:1;" onClick="'+confirm+'"><i class="fa fa-check"></i> Confirm</a>';
    }

    function cellStylerConfirm(value, row, index) {
        if (value == 0) {
            return 'background: #FF5F5F; color:white;';
        } else {
            return 'background: #53D636; color:white;';
        }
    }

    function cellFormatterConfirm(val, row) {
        if (val == 0) {
            return 'Not Confirm';
        } else {
            return 'Confirmed';
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
</script>