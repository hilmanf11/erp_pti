<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <!-- <th data-options="field:'ven_id',width:80,halign:'center'">Vendor ID</th>
            <th data-options="field:'ven_name',width:250,halign:'center'">Vendor Name</th> -->
            <th rowspan="2" data-options="field:'item_id',width:250,halign:'center'">Item Id</th>
            <th rowspan="2" data-options="field:'item_supplier',width:250,halign:'center'">Item Supplier</th>
            <th rowspan="2" data-options="field:'item_name',width:250,halign:'center'">Item Name</th>
            <th rowspan="2" data-options="field:'pfm_name',width:150,halign:'center'">Product Family</th>
            <th rowspan="2" data-options="field:'ven_name',width:250,halign:'center'">Supplier</th>
            <th rowspan="2" data-options="field:'poc_id',width:200,halign:'center'">Purchase Order</th>
            <th rowspan="2" data-options="field:'poc_date',width:100,halign:'center'">PO Date</th>
            <th rowspan="2" data-options="field:'poc_reqdate',width:100,halign:'center'">Req Date</th>
            <!-- <th data-options="field:'um_id',width:50,halign:'center'">UoM</th> -->
            <th rowspan="2" data-options="field:'moq',width:100,halign:'center',align:'right',formatter: numberformat">MOQ</th>
            <th rowspan="2" data-options="field:'mpq',width:100,halign:'center',align:'right',formatter: numberformat">MPQ</th>
            <!-- <th data-options="field:'por_pocreqqty',width:100,halign:'center',align:'right',formatter: numberformat">Request Qty</th>
            <th data-options="field:'por_receiveqty',width:100,halign:'center',align:'right',formatter: numberformat">Receive Qty</th> -->
            <th rowspan="2" data-options="field:'poc_reqqty',width:100,halign:'center',align:'right',formatter: numberformat">PO Qty</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'">Receipt</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'">On The Way</th>
            <th rowspan="2" data-options="field:'outstanding',width:100,halign:'center',align:'right',formatter: numberformat">Outstanding</th>
            <th rowspan="2" data-options="field:'status',width:100,halign:'center',align:'center',formatter: cellFormatter,styler: cellStyler">Status</th>
            <!-- <th data-options="field:'status',width:100,align:'center',styler:cellStyler, formatter:cellFormatter">Status</th>
            <th data-options="field:'por_emp',width:100,halign:'center'">Receipt By</th> -->
        </tr>
        <tr>
            <th data-options="field:'por_receiveqty',width:100,halign:'center',align:'right',formatter: numberformat">Qty</th>
            <th data-options="field:'viewReceipt',width:80,align:'center',formatter:BtnViewReceipt">View</th>
            <th data-options="field:'bal_otw',width:100,halign:'center',align:'right',formatter: numberformat">Qty</th>
            <th data-options="field:'viewOtw',width:80,align:'center',formatter:BtnViewOtw">View</th>
        </tr>
    </thead>
</table>
<div id="toolbar" style="height: 230px; padding: 10px;">
    <fieldset style="width:100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <legend><b>Filter Data</b></legend>
        <div style="width: 50%;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Period</span>
                <input style="width:28%;" id="filter_from" class="easyui-datebox" value="<?php echo date('Y-m-01'); ?>" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                <input style="width:28%;" id="filter_to" class="easyui-datebox" value="<?php echo date('Y-m-t'); ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Vendor ID</span>
                <input style="width:60%;" name="vendor_id" id="vendor_id" class="easyui-combobox">
            </div>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Trans. Type</span>
                <select style="width:60%;" id="trans_type" class="easyui-combobox" panelHeight="auto">
                    <option value="Recap">Recap</option>
                    <option value="Detail">Detail</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product Family</span>
                <input style="width:60%;" name="product_family" id="product_family" class="easyui-combobox" panelHeight="auto">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </div>
        <div style="width: 45%;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Item Id</span>
                <input style="width:60%;" name="item_id" id="item_id" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">PO Status</span>
                <select style="width:60%;" id="po_status" class="easyui-combobox" panelHeight="auto">
                    <option value="All">All</option>
                    <option value="Open">Open</option>
                    <option value="Closed">Closed</option>
                    <option value="Over">Over</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">PO No</span>
                <input style="width:60%;" name="po_no" id="po_no" class="easyui-combobox">
            </div>
        </div>
    </fieldset>
    <?= $button ?>
    <a href="javascript:;" class="easyui-linkbutton" plain="true" onclick="csv()"><i class="fa fa-file"></i> Export to CSV</a>
</div>

<div id="toolbar2" style="height: auto; padding:10px;">
    <a href="javascript:;" class="easyui-linkbutton" plain="false" onclick="export_excel2()"><i class="fa fa-file"></i> &nbsp; Export Excel</a>
</div>
<div id="dlg_purchase_order_receipt" class="easyui-dialog" title="Detail Receive" data-options="closed: true,modal:true" style="width: 90%; height: 550px; top: 0px; left: 0px;">
    <table id="dg_purchase_order_receipt" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar2" showFooter="true">
        <thead>
            <tr>
                <th field="ck" checkbox="true"></th>
                <th data-options="field:'por_date',width:150,halign:'center'">Receive Date</th>
                <th data-options="field:'full_name',width:150,halign:'center'">Receive By</th>
                <th data-options="field:'por_pocid',width:250,halign:'center'">Purchase Order</th>
                <th data-options="field:'item_id',width:150,halign:'center'">Part No</th>
                <th data-options="field:'item_name',width:100,halign:'center'">Part Name</th>
                <th data-options="field:'por_docno',width:250,halign:'center'">Document No</th>
                <th data-options="field:'por_invno',width:150,halign:'center'">Invoice No</th>
                <th data-options="field:'por_receiveqty',width:100,halign:'center',align:'right',formatter:numberformat">Qty</th>
            </tr>
        </thead>
    </table>
</div>

<div id="toolbar3" style="height: auto; padding:10px;">
    <a href="javascript:;" class="easyui-linkbutton" plain="false" onclick="export_excel3()"><i class="fa fa-file"></i> &nbsp; Export Excel</a>
</div>
<div id="dlg_otw" class="easyui-dialog" title="Detail OTW" data-options="closed: true,modal:true" style="width: 90%; height: 550px; top: 0px; left: 0px;">
    <table id="dg_otw" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar3" showFooter="true">
        <thead>
            <tr>
                <th field="ck" checkbox="true"></th>
                <th data-options="field:'po_no',width:150,halign:'center'">Purchase Order</th>
                <th data-options="field:'item_id',width:150,halign:'center'">Part No</th>
                <th data-options="field:'etd',width:150,halign:'center'">ETD</th>
                <th data-options="field:'eta',width:150,halign:'center'">ETA</th>
                <th data-options="field:'document_no',width:150,halign:'center'">Invoice No</th>
                <th data-options="field:'awb',width:150,halign:'center'">AWB</th>
                <th data-options="field:'qty',width:100,halign:'center',align:'right',formatter:numberformat">Qty PO</th>
                <th data-options="field:'delivery',width:100,halign:'center',align:'right',formatter:numberformat">Plan Delivery</th>
                <th data-options="field:'receive',width:100,halign:'center',align:'right',formatter:numberformat">Qty Scan</th>
                <th data-options="field:'balance',width:100,halign:'center',align:'right',formatter:numberformat">Qty Balance</th>
            </tr>
        </thead>
    </table>
</div>
<!-- PDF -->
<iframe id="printout" src="" style="width: 100%;" hidden></iframe>
<script>
    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var vendor_id = $("#vendor_id").combobox('getValue');
        var product_family = $("#product_family").combobox('getValue');
        var item_id = $("#item_id").combogrid('getValue');
        var po_status = $("#po_status").combobox('getValue');
        var po_no = $("#po_no").combobox('getValue');
        var trans_type = $("#trans_type").combobox('getValue');
        var url = "?vendor_id=" + window.btoa(vendor_id) +
            "&product_family=" + window.btoa(product_family) +
            "&item_id=" + window.btoa(item_id) +
            "&po_status=" + window.btoa(po_status) +
            "&po_no=" + window.btoa(po_no) +
            "&trans_type=" + window.btoa(trans_type) +
            "&filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to);

        if (vendor_id == "" && item_id == "" && product_family == "") {
            toastr.info("Please Select Vendor, Product Family or Item ID");
        } else {
            $('#dg').datagrid({
                url: '<?= base_url('planning/os_purchase_order/datatables') ?>' + url,
                pagination: true,
                rownumbers: true,
                fit: true,
                // view: detailview,
                // detailFormatter: function(index, row) {
                //     return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.item_id + '"></table></div>';
                // },
                // onExpandRow: function(index, row) {
                //     var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');
                //     ddv.datagrid({
                //         url: '<?= base_url('planning/os_purchase_order/datatableDetails/') ?>' + window.btoa(row.item_id) + "/" + window.btoa(row.ven_id) + "/" + window.btoa(po_status),
                //         singleSelect: true,
                //         rownumbers: true,
                //         columns: [
                //             [{
                //                 field: 'poc_id',
                //                 title: 'Purchase Order',
                //                 halign: 'center',
                //                 width: 250
                //             }, {
                //                 field: 'poc_date',
                //                 title: 'PO Date',
                //                 align: 'center',
                //                 width: 100
                //             }, {
                //                 field: 'etd',
                //                 title: 'ETD',
                //                 align: 'center',
                //                 width: 100
                //             }, {
                //                 field: 'eta',
                //                 title: 'ETA',
                //                 align: 'center',
                //                 width: 100
                //             }, {
                //                 field: 'poc_reqdate',
                //                 title: 'Request Date',
                //                 align: 'center',
                //                 width: 100
                //             }, {
                //                 field: 'poc_reqqty',
                //                 title: 'Request',
                //                 width: 100,
                //                 halign: 'center',
                //                 align: 'right',
                //                 formatter: numberformat
                //             }, {
                //                 field: 'por_receiveqty',
                //                 title: 'Receipt',
                //                 width: 100,
                //                 halign: 'center',
                //                 align: 'right',
                //                 formatter: numberformat
                //             }, {
                //                 field: 'outstanding',
                //                 title: 'Outstanding',
                //                 width: 100,
                //                 halign: 'center',
                //                 align: 'right',
                //                 formatter: numberformat
                //             }, {
                //                 field: 'status',
                //                 title: 'Status',
                //                 width: 100,
                //                 align: 'right',
                //                 formatter: cellFormatter,
                //                 styler: cellStyler
                //             }]
                //         ],
                //         onResize: function() {
                //             $('#dg').datagrid('fixDetailRowHeight', index);
                //         },
                //         onLoadSuccess: function() {
                //             setTimeout(function() {
                //                 $('#dg').datagrid('fixDetailRowHeight', index);
                //             }, 0);
                //         }
                //     });
                //     $('#dg').datagrid('fixDetailRowHeight', index);
                // }
            });
            $("#printout").attr('src', '<?= base_url('planning/os_purchase_order/print') ?>' + url);
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var vendor_id = $("#vendor_id").combobox('getValue');
        var product_family = $("#product_family").combobox('getValue');
        var item_id = $("#item_id").combogrid('getValue');
        var po_status = $("#po_status").combobox('getValue');
        var po_no = $("#po_no").combobox('getValue');
        var trans_type = $("#trans_type").combobox('getValue');
        var url = "?vendor_id=" + window.btoa(vendor_id) +
            "&product_family=" + window.btoa(product_family) +
            "&item_id=" + window.btoa(item_id) +
            "&po_status=" + window.btoa(po_status) +
            "&po_no=" + window.btoa(po_no) +
            "&trans_type=" + window.btoa(trans_type) +
            "&filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to);

        window.location.assign('<?= base_url('planning/os_purchase_order/print/excel') ?>' + url);
    }

    function csv() {
        $.messager.prompt('Export CSV', 'Please input Password "CSV" for Export Data CSV', function(r) {
            if (r == "CSV") {
                $.messager.alert('Info', 'Please Wait Export to CSV');
                window.location.assign('<?= base_url('planning/os_purchase_order/csv') ?>');
            }
        });
    }

    function reload() {
        window.location.reload();
    }
    $(function() {
        $('#vendor_id').combobox({
            url: '<?php echo base_url('planning/os_purchase_order/readVendors'); ?>',
            valueField: 'ven_id',
            textField: 'ven_name',
            prompt: 'Choose Vendor',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(vendor) {
                $('#product_family').combobox({
                    url: '<?php echo base_url('planning/os_purchase_order/readProdfam/'); ?>' + btoa(vendor.ven_id),
                    valueField: 'pfm_id',
                    textField: 'pfm_name',
                    prompt: 'Choose Product Family',
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                    onSelect: function(prodfam) {
                        $('#item_id').combogrid({
                            url: '<?= base_url('planning/os_purchase_order/readProducts/') ?>' + btoa(vendor.ven_id) + '/' + prodfam.pfm_id,
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
                            ],
                            onSelect: function(val, item) {
                                $('#po_no').combobox({
                                    url: '<?php echo base_url('planning/os_purchase_order/readPo?ven_id='); ?>' + btoa(vendor.ven_id) + '&pfm_id=' + btoa(prodfam.pfm_id) + '&item_id=' + btoa(item.item_id),
                                    valueField: 'por_pocid',
                                    textField: 'por_pocid',
                                    prompt: 'Choose PO No',
                                    icons: [{
                                        iconCls: 'icon-clear',
                                        handler: function(e) {
                                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                                        }
                                    }],
                                });
                            }
                        });
                    }
                });
            }
        });

        $('#product_family').combobox({
            url: '<?php echo base_url('planning/os_purchase_order/readProdfam'); ?>',
            valueField: 'pfm_id',
            textField: 'pfm_name',
            prompt: 'Choose Product Family',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $('#item_id').combogrid({
            url: '<?= base_url('planning/mst_item/reads'); ?>',
            panelWidth: 350,
            idField: 'item_id',
            textField: 'item_id',
            mode: 'remote',
            fitColumns: true,
            pagination: true,
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
                    width: 100
                }, ]
            ]
        });
    });

    function BtnViewReceipt(val, row) {
        var details = "poViewReceipt('" + row.poc_id + "','" + row.item_id + "')";
        return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" onClick="' + details + '"><i class="fa fa-eye"></i> View</a>';
    }

    function poViewReceipt(purchase_order_no, item_id) {
        $("#dlg_purchase_order_receipt").dialog('open');

        $('#dg_purchase_order_receipt').datagrid({
            url: '<?= base_url('planning/os_purchase_order/datatableReceipt/') ?>' + window.btoa(purchase_order_no) + "/" + window.btoa(item_id),
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            singleSelect: false,
            fit: true,
        });
    }

    function BtnViewOtw(val, row) {
        var details = "poViewOtw('" + row.poc_id + "','" + row.item_id + "')";
        return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" onClick="' + details + '"><i class="fa fa-eye"></i> View</a>';
    }

    function poViewOtw(purchase_order_no, item_id) {
        $("#dlg_otw").dialog('open');

        $('#dg_otw').datagrid({
            url: '<?= base_url('planning/os_purchase_order/datatableOtw/') ?>' + window.btoa(purchase_order_no) + "/" + window.btoa(item_id),
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            singleSelect: false,
            fit: true,
        });
    }


    function export_excel2() {
        $('#dg_purchase_order_receipt').datagrid('toExcel', 'Receipt PO.xls');
    }

    function export_excel3() {
        $('#dg_otw').datagrid('toExcel', 'PO Otw.xls');
    }
    //CELLSTYLE STATUS
    function cellStyler(value, row, index) {
        if (value == "OPEN") {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }
    //FORMATTER STATUS
    function cellFormatter(value) {
        if (value == "OPEN") {
            return 'OPEN';
        } else {
            return 'CLOSED';
        }
    };

    function numberformat(value, row) {
        if (value != null) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 0
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
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
</script>