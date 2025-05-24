<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
    <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'supplier_name',width:250,halign:'center'">Vendor Name</th>
            <th rowspan="2" data-options="field:'purchase_order_no',width:160,halign:'center'">PO No</th>
            <th rowspan="2" data-options="field:'purchase_order_date',width:80,halign:'center'">PO Date</th>
            <th rowspan="2" data-options="field:'total_qty',width:100,halign:'center',align:'right',formatter:numberformatPrice">Qty</th>
            <th rowspan="2" data-options="field:'currency',width:80,align:'center'">Currency</th>
            <th rowspan="2" data-options="field:'total_amount',width:120,halign:'center',align:'right',formatter:numberformatPrice">Amount</th>
            <th rowspan="2" data-options="field:'detail',width:80,halign:'center',formatter:BtnDetail">Detail</th>
            <th rowspan="2" data-options="field:'print_po',width:80,halign:'center',formatter:BtnPrint">Print PO</th>
            <th colspan="3" data-options="field:'Send_mail',width:100,halign:'center'">Approved</th>
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
                    <input style="width:60%;" id="filter_supplier" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">PO No</span>
                    <input style="width:60%;" id="filter_po" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part No</span>
                    <input style="width:60%;" id="filter_part_no" class="easyui-textbox">
                </div>
            </div>
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
                </div>
            </div>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier Name</span>
                    <input style="width:60%;" id="d_supplier" class="easyui-textbox" readonly>
                </div>
            </div>
        </fieldset>
    </div>
</div>

<div id="toolbar3">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>

<div id="dlg_purchase_order" class="easyui-dialog" title="Detail Purchase Order" data-options="closed: true,modal:true" style="width: 90%; height: 550px; top: 0px; left: 0px;">
    <table id="dg_purchase_order" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar2" showFooter="true">
        <thead>
            <tr>
                <th field="ck" checkbox="true"></th>
                <th data-options="field:'item_id',width:150,halign:'center'">Part No</th>
                <th data-options="field:'item_vendor',width:150,halign:'center'">Part Vendor</th>
                <th data-options="field:'item_name',width:100,halign:'center'">Part Name</th>
                <th data-options="field:'uom',width:80,align:'center'">Uom</th>
                <th data-options="field:'leadtime',width:80,halign:'center'">Leadtime</th>
                <th data-options="field:'qty',width:100,halign:'center',align:'right',formatter:numberformat">Qty</th>
                <th data-options="field:'price',width:100,halign:'center',align:'right',formatter:numberformatPrice">Price</th>
                <th data-options="field:'total',width:150,halign:'center',align:'right',formatter:numberformatPrice">Total</th>
                <th data-options="field:'currency',width:80,align:'center'">Currency</th>
                <th data-options="field:'reason',width:150,halign:'center'">Reason</th>
            </tr>
        </thead>
    </table>
</div>

<!-- DIALOG SAVE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1200px; height: 600px; padding:10px; top: 20px; left:10px;">
    <form id="frm_insert" method="post" novalidate>
        <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px;">
                <legend><b>Form Data</b></legend>
                <div style="float: left; width: 50%;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Period</span>
                        <input style="width:30%;" name="p_month" id="p_month" class="easyui-combobox" required data-options="prompt:'Month'">
                        <input style="width:30%;" name="p_year" id="p_year" required class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Revision</span>
                        <select style="width:60%;" name="revision" id="revision" required class="easyui-combobox" panelHeight="auto">
                            <option value="0">0</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                        </select>
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">PO No</span>
                        <input style="width:60%;" name="purchase_order_no" id="purchase_order_no2" required class="easyui-textbox">
                    </div>
                </div>
                <div style="float: left; width: 50%;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">PO Date</span>
                        <input style="width:60%;" name="purchase_order_date" id="purchase_order_date" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">VAT / Payment Term</span>
                        <input style="width:30%;" name="vat" id="vat" required class="easyui-numberbox" data-options="prompt:'VAT'">
                        <input style="width:30%;" name="payment_term" id="payment_term" required class="easyui-numberbox" data-options="prompt:'Payment Term'">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Vendor Name</span>
                        <input style="width:60%;" name="supplier_name" id="supplier_name2" required class="easyui-textbox">
                    </div>
                </div>
            </fieldset>
        </div>

        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Add Purchase Order Additional" toolbar="#toolbar3" data-options="singleSelect: true">
        </table>
    </form>
</div>

<!-- Update Data -->
<div id="dlg_update" class="easyui-dialog" title="Update Data" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <form id="frm_update" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Revision</span>
                <input style="width:20%;" name="revision" id="revision" readonly class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">PO No</span>
                <input style="width:50%;" name="purchase_order_no" id="purchase_order_no" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Supplier Name</span>
                <input style="width:50%;" name="supplier_name" id="supplier_name" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Part No</span>
                <input style="width:50%;" name="item_id" id="item_id" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Part Name</span>
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

<!-- PDF -->
<iframe id="printout" src="" style="width: 100%;" hidden></iframe>

<script>
    function add() {
        $('#dlg_insert').dialog('open');
        $('#frm_insert').form('clear');

        $('#dg2').datagrid('loadData', []);

        $("#purchase_order_date").datebox('setValue', "<?= date("Y-m-d") ?>");
        $("#p_month").combobox('setValue', "<?= date("m") ?>");
        $("#p_year").combobox('setValue', "<?= date("Y") ?>");
        $("#revision").combobox('setValue', "0");

        addTable();
    }

    function autoId(p_year, p_month){
        var code = "MC-INDNPD"+p_year+p_month+"-01";

        $.ajax({
            type: "post",
            url: "<?= base_url("planning/purchase_order_npd/purchaseOrderNo/") ?>"+btoa(code),
            dataType: "json",
            success: function (response) {
                $("#purchase_order_no2").textbox('setValue', response.name);
            }
        });
    }

    //INSERT ADD ROW
    function addTable() {
        var lastIndex;
        var dg = $('#dg2').datagrid({
            columns: [
                [{
                    field: 'item_id',
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
                    field: 'item_vendor',
                    width: 150,
                    halign: 'center',
                    title: "Part No Vendor",
                    editor: {
                        type: 'textbox',
                    }
                }, {
                    field: 'item_name',
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
                        type: 'textbox',
                        options: {
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
                        type: 'textbox',
                        options: {
                            required: true,
                        }
                    }
                }, {
                    field: 'price',
                    width: 80,
                    halign: 'center',
                    title: "Price",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                            precision: 2
                        }
                    }
                }, {
                    field: 'total',
                    width: 100,
                    halign: 'center',
                    title: "Total",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                            precision: 2
                        }
                    }
                }, {
                    field: 'currency',
                    width: 80,
                    halign: 'center',
                    title: "Currency",
                    editor: {
                        type: 'textbox',
                        options: {
                            required: true,
                        }
                    }
                }, {
                    field: 'leadtime',
                    width: 80,
                    halign: 'center',
                    title: "Leadtime",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                        }
                    }
                }, {
                    field: 'forecast_1',
                    width: 80,
                    halign: 'center',
                    title: "Forecast 1",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                        }
                    }
                }, {
                    field: 'forecast_2',
                    width: 80,
                    halign: 'center',
                    title: "Forecast 2",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                        }
                    }
                }, {
                    field: 'forecast_3',
                    width: 80,
                    halign: 'center',
                    title: "Forecast 3",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
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
            onBeginEdit: function(rowIndex, row) {
                var editors = $('#dg2').datagrid('getEditors', rowIndex);
                var qty = $(editors[4].target);
                var price = $(editors[6].target);
                var total = $(editors[7].target);

                qty.numberbox({
                    onChange: function() {
                        var qty2 = qty.numberbox('getValue');
                        var price2 = price.numberbox('getValue');

                        total.numberbox('setValue', (qty2 * price2));
                    }
                });

                price.numberbox({
                    onChange: function() {
                        var qty2 = qty.numberbox('getValue');
                        var price2 = price.numberbox('getValue');

                        total.numberbox('setValue', (qty2 * price2));
                    }
                });
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
        var supplier_name2 = $("#supplier_name2").textbox('getValue');
        if (supplier_name2 != "") {
            if (endEditing()) {
                $('#dg2').datagrid('appendRow', {
                    qty: '0'
                });
                editIndex = $('#dg2').datagrid('getRows').length - 1;
                $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
            }
        } else {
            toastr.error("Please Input Vendor Name");
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
        toastr.info("Not Ready this Function");
    }

    function details(purchase_order_no, supplier) {
        $("#dlg_purchase_order").dialog('open');

        $("#d_purchase_order_no").textbox('setValue', purchase_order_no);
        $("#d_supplier").textbox('setValue', supplier);

        var revision = $("#filter_revision").combobox('getValue');
        var filter_part_no = $("#filter_part_no").textbox('getValue');
        
        $('#dg_purchase_order').datagrid({
            url: '<?= base_url('planning/purchase_order_npd/datatableDetails/') ?>' + btoa(purchase_order_no) + "/" + revision + "?filter_part_no=" + btoa(filter_part_no),
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
            $('#dlg_update').dialog('open');
            $('#frm_update').form('load', row);

            url_save = '<?= base_url('planning/purchase_order_npd/updateDetails') ?>?id=' + btoa(row.id);
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
                        if(row.push_date == null){
                            $.ajax({
                                method: 'post',
                                url: '<?= base_url('planning/purchase_order_npd/delete') ?>',
                                data: {
                                    purchase_order_no: row.purchase_order_no
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
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_po = $("#filter_po").textbox('getValue');
        var filter_part_no = $("#filter_part_no").textbox('getValue');
        var filter_supplier = $("#filter_supplier").textbox('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_po=" + window.btoa(filter_po) +
            "&filter_part_no=" + window.btoa(filter_part_no);

        if (filter_month == "" || filter_year == "") {
            toastr.warning("Please Choose Filter Date!", "Information");
        } else {
            $('#dg').datagrid({
                url: '<?= base_url('planning/purchase_order_npd/datatables') ?>' + url,
            });

            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('planning/purchase_order_npd/print') ?>' + url);
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_po = $("#filter_po").textbox('getValue');
        var filter_part_no = $("#filter_part_no").textbox('getValue');
        var filter_supplier = $("#filter_supplier").textbox('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_po=" + window.btoa(filter_po) +
            "&filter_part_no=" + window.btoa(filter_part_no);

        if (filter_month == "" || filter_year == "") {
            toastr.warning("Please Choose Filter Date!", "Information");
        } else {
            window.location.assign('<?= base_url('planning/purchase_order_npd/print/excel') ?>' + url);
        }
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
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
        });

        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    var p_month = $("#p_month").combobox('getValue');
                    var p_year = $("#p_year").combobox('getValue');
                    var revision = $("#revision").combobox('getValue');
                    var supplier_name = $("#supplier_name2").textbox('getValue');
                    var purchase_order_no = $("#purchase_order_no2").textbox('getValue');
                    var purchase_order_date = $("#purchase_order_date").datebox('getValue');
                    var vat = $("#vat").numberbox('getValue');
                    var payment_term = $("#payment_term").numberbox('getValue');

                    if (supplier_name == "" || purchase_order_no == "") {
                        toastr.error("please complete your input data");
                    } else {
                        var rows = $('#dg2').datagrid('getRows');
                        var totalrows = rows.length;
                        endEditing();
                        for (let i = 0; i < totalrows; i++) {
                            if (rows[i].item_id) {
                                $.ajax({
                                    type: "post",
                                    url: '<?= base_url('planning/purchase_order_npd/create') ?>',
                                    data: {
                                        p_month: p_month,
                                        p_year: p_year,
                                        revision: revision,
                                        purchase_order_no: purchase_order_no,
                                        purchase_order_date: purchase_order_date,
                                        supplier_name: supplier_name,
                                        vat: vat,
                                        payment_term: payment_term,
                                        item_id: rows[i].item_id,
                                        item_name: rows[i].item_name,
                                        item_vendor: rows[i].item_vendor,
                                        product_family: rows[i].product_family,
                                        qty: rows[i].qty,
                                        price: rows[i].price,
                                        total: rows[i].total,
                                        currency: rows[i].currency,
                                        uom: rows[i].uom,
                                        leadtime: rows[i].leadtime,
                                        forecast_1: rows[i].forecast_1,
                                        forecast_2: rows[i].forecast_2,
                                        forecast_3: rows[i].forecast_3,
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
                            $('#dg_purchase_order').datagrid('reload');
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
        });

        $('#p_month').combobox({
            url: '<?php echo base_url('planning/mst_data/readMonths'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Month',
            onSelect: function(val){
                var p_year = $("#p_year").combobox('getValue');
                autoId(p_year, val.id);
            }
        });

        $('#p_year').combobox({
            url: '<?php echo base_url('planning/mst_data/readYears'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Year',
            onSelect: function(val){
                var p_month = $("#p_month").combobox('getValue');
                autoId(val.id, p_month);
            }
        });

        $('#revision').combobox({
            url: '<?php echo base_url('planning/mst_data/readRevisions'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Revision',
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
            var digit = 0;
        }

        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: digit
        });

        return "<b>" + formatter.format(value) + "</b>";
    }

    function BtnDetail(val, row) {
        var details = "details('"+row.purchase_order_no+"','"+row.supplier_name+"')";
        return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" onClick="'+details+'"><i class="fa fa-eye"></i> View</a>';
    }

    function BtnPrint(val, row) {
        return '<a class="btn btn-primary w-100" target="_blank" href="<?= base_url('planning/purchase_order_npd/print_po/') ?>'+btoa(row.purchase_order_no)+'" style="pointer-events: visible; opacity:1;"><i class="fa fa-print"></i> Print</a>';
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

    function cellStylerConfirm(value, row, index) {
        if (value == null) {
            return 'background: #FF5F5F; color:white;';
        } else if(value == "Pending"){
            return 'background: #AB6F2B; color:white;';
        }else{
            return 'background: #53D636; color:white;';
        }
    }

    function cellFormatterConfirm(value, row) {
        if (value == null) {
            return 'Not Receipt';
        } else if(value == "Pending"){
            return 'Pending';
        } else {
            return 'Confirm';
        }
    };

    function cellStylerPrice(value,row,index){
        if (parseInt(row.total_pr) == parseInt(row.total_po)){
            return 'background-color:#D1FFC6;';
        }else{
            return 'background-color:#FFC6C6;';
        }
    }
</script>