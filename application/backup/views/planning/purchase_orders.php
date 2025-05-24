<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
    <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'supplier_no',width:80,align:'center'">Vendor ID</th>
            <th rowspan="2" data-options="field:'supplier_name',width:250,halign:'center'">Vendor Name</th>
            <th rowspan="2" data-options="field:'product_family_name',width:120,halign:'center'">Product Family</th>
            <th rowspan="2" data-options="field:'purchase_order_no',width:150,halign:'center'">PO No</th>
            <th rowspan="2" data-options="field:'purchase_order_date',width:80,halign:'center'">PO Date</th>
            <th rowspan="2" data-options="field:'detail',width:80,halign:'center',formatter:BtnDetail">Detail</th>
            <th rowspan="2" data-options="field:'print_po',width:80,halign:'center',formatter:BtnPrint">Print PO</th>
            <th rowspan="2" data-options="field:'print_sd',width:80,halign:'center',formatter:BtnPrintSd">Schedule<br>Delivery</th>
            <th rowspan="2" data-options="field:'erp',width:80,align:'center',formatter:BtnPush">Push ERP</th>
            <th rowspan="2" data-options="field:'push_date',width:150,align:'center'">Push Date</th>
            <th colspan="4" data-options="field:'Send_mail',width:100,halign:'center'">Send Mail</th>
            <th colspan="3" data-options="field:'Send_mail',width:100,halign:'center'">Approved</th>
            <th colspan="2" data-options="field:'Send_mail',width:100,halign:'center'">Created</th>
        </tr>
        <tr>
            <th data-options="field:'contact',width:150,halign:'center'">Contact</th>
            <th data-options="field:'email',width:200,halign:'center'">Email</th>
            <th data-options="field:'send',width:80,align:'center',formatter:BtnSend">Send</th>
            <th data-options="field:'email_status',width:80,align:'center', styler:cellStylerSend, formatter:cellFormatterSend">Status</th>
            <th data-options="field:'approved_to',width:80,align:'center', styler:cellStylerApproval, formatter:cellFormatterApproval">Status</th>
            <th data-options="field:'approved_by',width:80,align:'center'">By</th>
            <th data-options="field:'approved_date',width:80,align:'center'">Date</th>
            <th data-options="field:'created_by',width:80,align:'center'">By</th>
            <th data-options="field:'created_date',width:80,align:'center'">Date</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 195px; padding:10px;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 60%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period Cutoff</span>
                    <input style="width:30%;" name="filter_month" value="<?= date("m") ?>" id="filter_month" class="easyui-combobox" data-options="prompt:'Month'">
                    <input style="width:30%;" name="filter_year" value="<?= date("Y") ?>" id="filter_year" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Revision</span>
                    <select style="width:60%;" id="filter_revision" class="easyui-combobox" panelHeight="auto">
                        <option value="0">0</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier</span>
                    <input style="width:60%;" id="filter_supplier" class="easyui-combogrid">
                </div>
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

            <b>Convert PR to PO / Push Data</b>
            <div id="p_upload" class="easyui-progressbar" style="width:100%;"></div>
            <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
            <br>
            <input value="on" readonly="true" class="easyui-checkbox" id="check_pr"> &nbsp; Purchase Request
        </fieldset>
    </div>
    <?= $button ?>
</div>

<div id="toolbar2" style="height: auto; padding:10px;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Detail</b></legend>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Purchase Order No</span>
                    <input style="width:60%;" id="d_purchase_order_no" class="easyui-textbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" id="detailEdit" class="easyui-linkbutton" onclick="detailEdit()"><i class="fa fa-edit"></i> Edit Data</a>
                    <a href="javascript:;" id="pushData" class="easyui-linkbutton c5" onclick="pushData()"><i class="fa fa-database"></i> Push Data</a>
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

<div id="dlg_purchase_order" class="easyui-dialog" title="Detail Purchase Order" data-options="closed: true,modal:true" style="width: 80%; height: 600px; top: 0px; left: 0px;">
    <table id="dg_purchase_order" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar2" showFooter="true">
        <thead>
            <tr>
                <th field="ck" checkbox="true"></th>
                <th data-options="field:'item_id',width:150,halign:'center'">Item No</th>
                <th data-options="field:'item_name',width:200,halign:'center'">Item Name</th>
                <th data-options="field:'uom',width:80,align:'center'">Uom</th>
                <th data-options="field:'qty',width:100,halign:'center',align:'right',formatter:numberformat">Qty</th>
                <th data-options="field:'price',width:100,halign:'center',align:'right',formatter:numberformatPrice">Price</th>
                <th data-options="field:'total',width:150,halign:'center',align:'right',formatter:numberformatPrice">Total</th>
                <th data-options="field:'currency',width:80,align:'center'">Currency</th>
                <th data-options="field:'leadtime',width:80,halign:'center'">Leadtime</th>
                <!-- <th data-options="field:'eta',width:100,halign:'center'">ETA</th> -->
                <th data-options="field:'revision',width:50,align:'center'">Rev</th>
                <th data-options="field:'reason',width:150,halign:'center'">Reason</th>
            </tr>
        </thead>
    </table>
</div>

<div id="dlg_insert" class="easyui-dialog" title="Update Data" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Revision</span>
                <input style="width:20%;" name="revision" id="revision" readonly class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">PO No</span>
                <input style="width:50%;" name="purchase_order_no" id="purchase_order_no" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Supplier No</span>
                <input style="width:50%;" name="supplier_no" id="supplier_no" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Supplier Name</span>
                <input style="width:50%;" name="supplier_name" id="supplier_name" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Item ID</span>
                <input style="width:50%;" name="item_id" id="item_id" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Item Name</span>
                <input style="width:50%;" name="item_name" id="item_name" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Currency</span>
                <input style="width:50%;" name="currency" id="currency" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Qty</span>
                <input style="width:30%;" name="qty" id="qty" required="true" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Price</span>
                <input style="width:30%;" name="price" id="price" class="easyui-numberbox" data-options="precision: 4">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Total</span>
                <input style="width:30%;" name="total" id="total" readonly class="easyui-numberbox" data-options="precision: 4">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Reason</span>
                <input style="width:50%;" name="reason" id="reason" required class="easyui-textbox">
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
    <div id="p_upload2" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start2">0</b> Of <b id="p_finish2">0</b></center>
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
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');
        var filter_part_no = $("#filter_part_no").combogrid('getValue');

        var check_pr = $("#check_pr").checkbox('options');

        if (check_pr.checked == true) {
            if (filter_month != "" && filter_year != "" && filter_revision != "") {
                $.messager.prompt('Generate PO', 'Please input Password Generate', function(r){
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
                            url: "<?= base_url('planning/purchase_orders/getData') ?>",
                            data: "filter_month=" + window.btoa(filter_month) +
                                "&filter_year=" + window.btoa(filter_year) +
                                "&filter_revision=" + window.btoa(filter_revision) +
                                "&filter_product_family=" + window.btoa(filter_product_family) +
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

                                            $.post('<?= base_url('planning/purchase_orders/create') ?>', {
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
                            }
                        });
                    }
                });

                $('.messager-input').attr('type', 'password');
            } else {
                toastr.warning("Please Select Period & Revision", "Information");
            }
        }else{
            toastr.error("Purchase Requests Not Ready All");
        }
    }

    function details(purchase_order_no, supplier, product_family, push="") {
        $("#dlg_purchase_order").dialog('open');

        $("#d_purchase_order_no").textbox('setValue', purchase_order_no);
        $("#d_supplier").textbox('setValue', supplier);
        $("#d_product_family").textbox('setValue', product_family);
        
        if(push == ""){
            $("#pushData").hide();
            $("#detailEdit").show();
        }else{
            $("#pushData").show();
            $("#detailEdit").hide();
        }

        var revision = $("#filter_revision").combobox('getValue');
        var filter_part_no = $("#filter_part_no").combogrid('getValue');
        
        $('#dg_purchase_order').datagrid({
            url: '<?= base_url('planning/purchase_orders/datatableDetails/') ?>' + btoa(purchase_order_no) + "/" + revision + "?filter_part_no=" + btoa(filter_part_no),
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            singleSelect: false,
            fit:true,
        });
    }

    function detailEdit(){
        var row = $('#dg_purchase_order').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');

            $("#purchase_order_no").combogrid({
                url: '<?= base_url('planning/purchase_orders/readPo/') ?>' + row.p_month + '/' + row.p_year + '/' + row.revision + '/' + row.product_family,
                panelWidth: 600,
                idField: 'purchase_order_no',
                textField: 'purchase_order_no',
                mode: 'remote',
                fitColumns: true,
                prompt: "Select PO No",
                columns: [
                    [{
                        field: 'purchase_order_no',
                        title: 'PO No',
                        width: 150
                    }, {
                        field: 'supplier_name',
                        title: 'Name',
                        width: 250
                    }, {
                        field: 'product_family_name',
                        title: 'Product Family',
                        width: 150
                    }]
                ],
                onSelect: function(index, po){
                    $("#supplier_no").textbox("setValue", po.supplier_no);
                    $("#supplier_name").textbox("setValue", po.supplier_name);
                }
            });

            $('#frm_insert').form('load', row);

            url_save = '<?= base_url('planning/purchase_orders/updateDetails') ?>?id=' + btoa(row.id);
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function pushData(){
        var rows = $('#dg_purchase_order').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.prompt('Push Data PO', 'Please input Password to Push', function(r){
                if (r == "PUSHDATA"){
                    $('#dlg_purchase_order').dialog('close');
                    requestData2(rows.length, rows);

                    function requestData2(total, json, number = 1, value = 0, success = 1, failed = 1) {
                        if (value < 100) {
                            value = Math.floor((number / total) * 100);
                            $('#p_upload').progressbar('setValue', value);
                            $('#p_start').html(number);
                            $('#p_finish').html(total);

                            $.post('<?= base_url('planning/purchase_orders/pushData') ?>', {
                                data: json[number - 1]
                            }, function(note) {
                                var result = eval('(' + note + ')');

                                if (result.theme == "success") {
                                    requestData2(total, json, number + 1, value, success + 1, failed + 0);
                                } else {
                                    requestData2(total, json, number + 1, value, success + 0, failed + 1);
                                }

                                if (value == 100) {
                                    Swal.fire('Good job!', 'Process Generate Data Completed!', 'success');
                                    $("#dg").datagrid('reload');
                                }
                            }).fail(function(jqXHR, textStatus) {
                                if (textStatus == "error") {
                                    toastr.error("Connection time out");

                                    requestData2(total, json, number, value, success + 0, failed + 1);
                                }
                            });
                        }
                    }
                }
            });

            $('.messager-input').attr('type', 'password');
        }else{
            toastr.warning("Please select one of the data in the table first!");
        }
    }

    function componentCheck(filter_month, filter_year, filter_revision) {
        $.ajax({
            type: "get",
            url: "<?= base_url('planning/purchase_orders/checkPR') ?>",
            data: "filter_month=" + window.btoa(filter_month) +
                "&filter_year=" + window.btoa(filter_year) +
                "&filter_revision=" + window.btoa(filter_revision),
            dataType: "json",
            success: function(mps) {
                if (mps.theme == "success") {
                    $('#check_pr').checkbox({
                        checked: true
                    });
                } else {
                    $('#check_pr').checkbox({
                        checked: false
                    });
                }
            }
        });
    }

    function filter() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');
        var filter_part_no = $("#filter_part_no").combogrid('getValue');
        var filter_supplier = $("#filter_supplier").combogrid('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_product_family=" + window.btoa(filter_product_family) +
            "&filter_part_no=" + window.btoa(filter_part_no);

        if (filter_month == "" || filter_year == "") {
            toastr.warning("Please Choose Filter Date!", "Information");
        } else {
            $('#dg').datagrid({
                url: '<?= base_url('planning/purchase_orders/datatables') ?>' + url,
            });

            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('planning/purchase_orders/print') ?>' + url);
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_supplier = $("#filter_supplier").combogrid('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');
        var filter_part_no = $("#filter_part_no").combogrid('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_product_family=" + window.btoa(filter_product_family) +
            "&filter_part_no=" + window.btoa(filter_part_no);

        if (filter_month == "" || filter_year == "") {
            toastr.warning("Please Choose Filter Date!", "Information");
        } else {
            window.location.assign('<?= base_url('planning/purchase_orders/print/excel') ?>' + url);
        }
    }

    //Upload Data
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_purchase_orders.xls') ?>');
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
    	$("#add").html("Generate");
        
        $('#dg').datagrid({
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            singleSelect: true,
            fit:true,
            rowStyler: function(index,row){
                if (row.color == ""){
                    return 'background-color:#FFD2D2;';
                }
            }
        });

        $("#qty").numberbox({
            onChange: function(qty){
                var price = $("#price").numberbox("getValue");
                $("#total").numberbox("setValue", (parseInt(qty) * parseInt(price)));
            }
        });

        $("#price").numberbox({
            onChange: function(price){
                var qty = $("#qty").numberbox("getValue");
                $("#total").numberbox("setValue", (parseInt(qty) * parseInt(price)));
            }
        })

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
                            $('#dg_purchase_order').datagrid('reload');
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
                        url: '<?= base_url('planning/purchase_orders/upload') ?>',
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
                            requestData3(json.total, json);

                            function requestData3(total, json, number = 1, value = 0) {
                                if (value < 100) {
                                    value = Math.floor((number / total) * 100);
                                    $('#p_upload2').progressbar('setValue', value);
                                    $('#p_start2').html(number);
                                    $('#p_finish2').html(total);

                                    $.post('<?= base_url('planning/purchase_orders/uploadCreate') ?>', {
                                        data: json[number - 1]
                                    }, function(note) {
                                        var result = eval('(' + note + ')');
                                        if (result.theme == "success") {
                                            var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                        } else {
                                            var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                                        }
                                        $("#p_remarks").append(title + "<br>");
                                        requestData3(total, json, number + 1, value);
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
            onSelect: function(month){
                var filter_year = $("#filter_year").combobox('getValue');
                var filter_revision = $("#filter_revision").combobox('getValue');

                if(filter_year != "" && filter_revision != ""){
                    componentCheck(month.id, filter_year, filter_revision);
                }
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
            onSelect: function(year){
                var filter_month = $("#filter_month").combobox('getValue');
                var filter_revision = $("#filter_revision").combobox('getValue');

                if(filter_month != "" && filter_revision != ""){
                    componentCheck(filter_month, year, filter_revision);
                }
            }
        });

        $('#filter_revision').combobox({
            url: '<?php echo base_url('planning/mst_data/readRevisions'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Revision',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(revision){
                var filter_month = $("#filter_month").combobox('getValue');
                var filter_year = $("#filter_year").combobox('getValue');

                if(filter_month != "" && filter_year != ""){
                    componentCheck(filter_month, filter_year, revision.id);
                }
            }
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

        $('#filter_supplier').combogrid({
            url: '<?= base_url('planning/purchase_orders/readSupplier') ?>',
            panelWidth: 400,
            idField: 'supplier_no',
            textField: 'supplier_name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Supplier",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'supplier_no',
                    title: 'ID',
                    width: 80
                }, {
                    field: 'supplier_name',
                    title: 'Name',
                    width: 250
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

        return "<b>" + formatter.format(value) + "</b>";
    }

    function numberformatPrice(value, row){
        if(row.currency == "IDR"){
            var digit = 0;
        }else{
            var digit = 4;
        }

        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: digit
        });

        return "<b>" + formatter.format(value) + "</b>";
    }

    function BtnDetail(val, row) {
        var details = "details('"+row.purchase_order_no+"','"+row.supplier_name+"','"+row.product_family_name+"')";
        return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" onClick="'+details+'"><i class="fa fa-eye"></i> View</a>';
    }

    function BtnPrint(val, row) {
        return '<a class="btn btn-primary w-100" target="_blank" href="<?= base_url('planning/purchase_orders/print_po/') ?>'+btoa(row.purchase_order_no)+'" style="pointer-events: visible; opacity:1;"><i class="fa fa-print"></i> Print</a>';
    }

    function BtnPrintSd(val, row) {
        if(val > 0){
            return '<a class="btn btn-primary w-100" target="_blank" href="<?= base_url('planning/purchase_orders/print_delivery/') ?>'+btoa(row.purchase_order_no)+'" style="pointer-events: visible; opacity:1;"><i class="fa fa-print"></i> Print</a>';
        } else {
            return '-';
        }
    }

    function BtnPush(val, row) {
        if (row.approved_to == null || row.approved_to == "") {
            var details = "details('"+row.purchase_order_no+"','"+row.supplier_name+"','"+row.product_family_name+"','push')";
            return '<a class="btn btn-danger w-100" style="pointer-events: visible; opacity:1;" onClick="'+details+'"><i class="fa fa-share"></i> Push</a>';
        } else if(row.push == "OK") {
            return 'DONE';
        } else {
            return '-';
        }
    }

    function BtnSend(val, row) {
        if (row.approved_to == null || row.approved_to == "") {
            return '<a class="btn btn-success w-100" style="pointer-events: visible; opacity:1;" onClick=""><i class="fa fa-send"></i> Send</a>';
        } else {
            return '-';
        }
    }

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

    function cellStylerSend(value, row, index) {
        if (value == null) {
            return 'background: #FF5F5F; color:white;';
        } else {
            return 'background: #53D636; color:white;';
        }
    }

    function cellFormatterSend(value, row) {
        if (value == null) {
            return 'Not Send';
        } else {
            return 'Delivered';
        }
    };
</script>