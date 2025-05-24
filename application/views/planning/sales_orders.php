<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'number',width:200,halign:'center'">SO No</th>
            <th rowspan="2" data-options="field:'trans_date',width:100,align:'center'">SO Date</th>
            <th rowspan="2" data-options="field:'customer_no',width:100,align:'center'">Customer No</th>
            <th rowspan="2" data-options="field:'customer_name',width:200,halign:'center'">Customer Name</th>
            <th rowspan="2" data-options="field:'customer_po',width:150,halign:'center'">Customer PO</th>
            <th rowspan="2" data-options="field:'status_is',width:100,align:'center',formatter:statusformat,styler:statusStyle">Status SO</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>

<!-- FORM FILTER DATAGRID -->
<div id="toolbar" style="height: 230px; padding: 10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 40%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Customer</span>
                <input style="width:60%;" name="filter_customers" id="filter_customers" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" name="filter_items" id="filter_items" class="easyui-combogrid">
            </div>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Type</span>
                <select style="width:60%;" id="filter_type" class="easyui-combobox" panelHeight="auto">
                    <option value="">Select All</option>
                    <option value="EXPORT">EXPORT</option>
                    <option value="LOCAL">LOCAL</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Sales Order No</span>
                <input style="width:60%;" id="filter_number" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>
<!-- TOOLBAR DATAGRID -->
<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>

<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1000px; height: 600px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px;">
                <legend><b>Form Data</b></legend>
                <div style="width: 50%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Sales Order Date</span>
                        <input style="width:60%;" name="trans_date" id="trans_date" value="<?= date("Y-m-d") ?>" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Customer</span>
                        <input style="width:60%;" required="" id="customer_no" name="customer_no" required="" class="easyui-combogrid">
                    </div>
                </div>
                <div style="width: 50%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Sales Order No</span>
                        <input style="width:60%;" readonly required="" id="number" name="number" class="easyui-textbox">
                    </div>
                </div>
            </fieldset>
        </div>

        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Add Sales Order Product" toolbar="#toolbar2" data-options="singleSelect: true"></table>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('planning/sales_orders/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        $("#customer_no").combobox('enable');
        $("#number").textbox('enable');
        $("#trans_date").datebox('enable');
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);

            $("#customer_no").combobox('disable');
            $("#number").textbox('disable');
            $("#trans_date").datebox('disable');
            
            addTable(row.customer_no, '<?= base_url('planning/sales_orders/datatableUpdates?number=') ?>' + window.btoa(row.number));
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //NOMOR AUTOMATIC
    function number(customer, trans_date) {
        $.ajax({
            type: "post",
            url: "<?= base_url('planning/sales_orders/number/') ?>" + $.trim(customer) + '/' + window.btoa(trans_date),
            dataType: "html",
            success: function(result) {
                $("#number").textbox('setValue', result);
            }
        });
    }

    //INSERT ADD ROW
    function addTable(customer, url = "") {
        var lastIndex;
        var dg = $('#dg2').datagrid({
            url: url,
            columns: [
                [{
                    field: 'item_id',
                    width: 250,
                    halign: 'center',
                    title: "Product No",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('planning/sales_orders/readProducts/') ?>' + customer,
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
                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'price'
                                });
                                // var ed3 = dg.datagrid('getEditor', {
                                //     index: rowIndex,
                                //     field: 'currency'
                                // });
                                var price = $(ed2.target).textbox('setValue', rows.price);
                                // var currency = $(ed3.target).textbox('setValue', rows.currency);
                            }
                        }
                    }
                }, {
                    field: 'customer_po',
                    width: 150,
                    halign: 'center',
                    title: "Customer PO",
                    editor: {
                        type: 'textbox',
                        options: {
                            required: true
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
                            precision: 2
                        }
                    }
                }, {
                    field: 'price',
                    width: 100,
                    halign: 'center',
                    title: "Price",
                    editor: {
                        type: 'textbox',
                        options: {
                            required: true
                        }
                    }
                }, {
                    field: 'total',
                    width: 100,
                    halign: 'center',
                    title: "Total Price",
                    editor: {
                        type: 'textbox',
                        options: {
                            required: true
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
                            required: true
                        }
                    }
                }, {
                    field: 'vat',
                    width: 100,
                    halign: 'center',
                    title: "Tax",
                    editor: {
                        type: 'combobox',
                        options: {
                            data: [{
                                "number": "VAT",
                                "name": "VAT"
                            }, {
                                "number": "NON VAT",
                                "name": "NON VAT"
                            }],
                            valueField: 'number',
                            textField: 'name',
                            panelHeight: 'auto',
                            required: true
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
                var trans_date = $("#trans_date").datebox('getValue');
                var item_id = $(editors[0].target);
                var qty = $(editors[2].target);
                var price = $(editors[3].target);
                var total = $(editors[4].target);

                qty.add(total).textbox({
                    onChange: function() {
                        var f_qty = qty.numberbox('getValue');
                        var f_price = price.textbox('getValue');
                        total.textbox('setValue', ((parseFloat(f_price) * parseFloat(f_qty))).toFixed(4));
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
        var customer_no = $("#customer_no").combogrid('getValue');
        if (customer_no != "") {
            if (endEditing()) {
                $('#dg2').datagrid('appendRow', {
                    qty: '0'
                });
                editIndex = $('#dg2').datagrid('getRows').length - 1;
                $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
            }
        } else {
            toastr.error("Please Choose Customer first");
        }
    }

    function removeit() {
        if (editIndex == undefined) {
            return
        }
        $('#dg2').datagrid('cancelEdit', editIndex).datagrid('deleteRow', editIndex);
        editIndex = undefined;
    }
    //DELETE DATA
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('planning/sales_orders/delete') ?>',
                            data: {
                                number: row.number
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

    //FILTER DATA
    function filter() {
        var filter_customers = $("#filter_customers").combogrid('getValue');
        var filter_items = $("#filter_items").combogrid('getValue');
        var filter_type = $("#filter_type").combobox('getValue');
        var filter_number = $("#filter_number").combogrid('getValue');
        var url = "?filter_customers=" + window.btoa(filter_customers) +
            "&filter_items=" + window.btoa(filter_items) +
            "&filter_number=" + window.btoa(filter_number) +
            "&filter_type=" + window.btoa(filter_type);
        $('#dg').datagrid({
            url: '<?= base_url('planning/sales_orders/datatables') ?>' + url
        });
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('planning/sales_orders/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //EXPORT TO EXCEL
    function excel() {
        var filter_customers = $("#filter_customers").combogrid('getValue');
        var filter_items = $("#filter_items").combogrid('getValue');
        var filter_type = $("#filter_type").combobox('getValue');
        var filter_number = $("#filter_number").combogrid('getValue');
        var url = "?filter_customers=" + window.btoa(filter_customers) +
            "&filter_items=" + window.btoa(filter_items) +
            "&filter_number=" + window.btoa(filter_number) +
            "&filter_type=" + window.btoa(filter_type);
        window.location.assign('<?= base_url('planning/sales_orders/print/excel') ?>' + url);
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }
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
        if (value != null) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }
    $(function() {
        $("#trans_date").datebox({
            onSelect: function(date) {
                $("#customer_no").combogrid('clear');
            }
        });
        
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('planning/sales_orders/datatables') ?>',
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.number + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');
                var filter_customers = $("#filter_customers").combogrid('getValue');
                var filter_items = $("#filter_items").combogrid('getValue');
                var filter_type = $("#filter_type").combobox('getValue');
                var url = "?filter_customers=" + window.btoa(filter_customers) +
                    "&filter_items=" + window.btoa(filter_items) +
                    "&filter_type=" + window.btoa(filter_type);
                ddv.datagrid({
                    url: '<?= base_url('planning/sales_orders/datatables/details?number=') ?>' + window.btoa(row.number) +
                        "&filter_customers=" + window.btoa(filter_customers) +
                        "&filter_items=" + window.btoa(filter_items) +
                        "&filter_type=" + window.btoa(filter_type),
                    singleSelect: true,
                    rownumbers: true,
                    columns: [
                        [{
                            field: 'item_id',
                            title: 'Product No',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'item_name',
                            title: 'Product Name',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'qty',
                            title: 'Qty',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }, {
                            field: 'price',
                            title: 'Price',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: priceformat
                        }, {
                            field: 'total',
                            title: 'Total',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: priceformat
                        }, {
                            field: 'uom',
                            title: 'UoM',
                            width: 100,
                            hidden: true,
                            align: 'center'
                        }, {
                            field: 'currency',
                            title: 'Currency',
                            width: 100,
                            align: 'center'
                        }, {
                            field: 'status_is',
                            title: 'Status',
                            width: 100,
                            align: 'center',
                            formatter: statusformat,
                            styler: statusStyle
                        }]
                    ],
                    onResize: function() {
                        $('#dg').datagrid('fixDetailRowHeight', index);
                    },
                    onLoadSuccess: function() {
                        setTimeout(function() {
                            $('#dg').datagrid('fixDetailRowHeight', index);
                        }, 0);
                    }
                });
                $('#dg').datagrid('fixDetailRowHeight', index);
            }
        });

        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    // toastr.error("Still Maintenance");
                    var customer_no = $("#customer_no").combogrid('getValue');
                    var number = $("#number").textbox('getValue');
                    var trans_date = $("#trans_date").datebox('getValue');

                    if (customer_no == "" || totalrows <= 0) {
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
                                    url: '<?= base_url('planning/sales_orders/create') ?>',
                                    data: {
                                        customer_no: customer_no,
                                        customer_po: rows[i].customer_po,
                                        number: number,
                                        trans_date: trans_date,
                                        item_id: rows[i].item_id,
                                        qty: rows[i].qty,
                                        price: rows[i].price,
                                        currency: rows[i].currency,
                                        total: rows[i].total,
                                        vat: rows[i].vat
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

        //GET SALES ORDER NUMBER
        $("#filter_number").combogrid({
            url: '<?= base_url('planning/sales_orders/reads') ?>',
            panelWidth: 350,
            idField: 'number',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select All",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'number',
                    title: 'Sales Order No',
                    width: 150
                }, {
                    field: 'trans_date',
                    title: 'Sales Order Date',
                    width: 100
                }, ]
            ],
        });

        //GET CUSTOMER
        $('#filter_customers').combogrid({
            url: '<?= base_url('planning/mst_data/readCustomers') ?>',
            panelWidth: 420,
            idField: 'number',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Customer",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'number',
                    title: 'Customer No',
                    width: 120
                }, {
                    field: 'name',
                    title: 'Customer Name',
                    width: 250
                }, ]
            ],
            onSelect: function(index, row) {
                //GET PRODUCT NO BY CUSTOMER
                $('#filter_items').combogrid({
                    url: '<?= base_url('planning/sales_orders/readProducts/') ?>' + row.number,
                    panelWidth: 420,
                    idField: 'item_id',
                    textField: 'item_name',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Select Product",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                        }
                    }],
                    columns: [
                        [{
                            field: 'item_id',
                            title: 'Product No',
                            width: 100
                        }, {
                            field: 'item_name',
                            title: 'Product Name',
                            width: 200
                        }, ]
                    ]
                });
            }
        });

        //GET CUSTOMER
        $('#customer_no').combogrid({
            url: '<?= base_url('planning/mst_data/readCustomers') ?>',
            panelWidth: 420,
            idField: 'number',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Customer",
            columns: [
                [{
                    field: 'number',
                    title: 'Customer No',
                    width: 120
                }, {
                    field: 'name',
                    title: 'Customer Name',
                    width: 250
                }, ]
            ],
            onSelect: function(index, row) {
                var trans_date = $("#trans_date").datebox('getValue');
                addTable(row.number);
                number(row.number, trans_date);
            }
        });
    });

    function priceformat(value, row) {
        if (row.currency == "USD") {
            var digits = 4;
            var currency = 'USD';
            var format = "en-IN";
        } else if (row.currency == "JPY") {
            var digits = 2;
            var currency = 'JPY';
            var format = "ja-JP";
        } else if (row.currency == "EUR") {
            var digits = 2;
            var currency = 'EUR';
            var format = "de-DE";
        } else {
            var digits = 0;
            var currency = 'IDR';
            var format = "id-ID";
        }
        if (value != null) {
            const formatter = new Intl.NumberFormat(format, {
                // style: 'currency',
                // currency: currency,
                minimumFractionDigits: digits
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function statusformat(value, row) {
        if (value == 0) {
            return "<b style='color:green;'>OPEN</b>";
        } else if (value == 1) {
            return "<b style='color:black;'>PRODUCTION</b>";
        } else if (value == 2) {
            return "<b style='color:white;'>DELIVERY</b>";
        } else {
            return "<b style='color:red;'>CLOSED</b>";
        }
    }

    function statusStyle(value, row, index) {
        if (value == 0) {
            return 'background-color:#C8FFCC;';
        } else if (value == 1) {
            return 'background-color:#FFB671;';
        } else if (value == 2) {
            return 'background-color:#00AF15;';
        } else {
            return 'background-color:#FFC8C8;';
        }
    }
</script>