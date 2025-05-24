<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th field="ck" checkbox="true"></th>
            <!-- <th data-options="field:'ven_id',width:80,halign:'center'">Vendor ID</th>
            <th data-options="field:'ven_name',width:250,halign:'center'">Vendor Name</th> -->
            <th data-options="field:'item_id',width:250,halign:'center'">Item Id</th>
            <th data-options="field:'item_name',width:250,halign:'center'">Item Name</th>
            <th data-options="field:'pfm_name',width:150,halign:'center'">Product Family</th>
            <th data-options="field:'ven_name',width:250,halign:'center'">Supplier</th>
            <th data-options="field:'poc_id',width:200,halign:'center'">Purchase Order</th>
            <th data-options="field:'poc_date',width:100,halign:'center'">PO Date</th>
            <th data-options="field:'poc_reqdate',width:100,halign:'center'">Req Date</th>
            <!-- <th data-options="field:'um_id',width:50,halign:'center'">UoM</th> -->
            <th data-options="field:'moq',width:100,halign:'center',align:'right',formatter: numberformat">MOQ</th>
            <th data-options="field:'mpq',width:100,halign:'center',align:'right',formatter: numberformat">MPQ</th>
            <!-- <th data-options="field:'por_pocreqqty',width:100,halign:'center',align:'right',formatter: numberformat">Request Qty</th>
            <th data-options="field:'por_receiveqty',width:100,halign:'center',align:'right',formatter: numberformat">Receive Qty</th> -->
            <th data-options="field:'poc_reqqty',width:100,halign:'center',align:'right',formatter: numberformat">Request</th>
            <th data-options="field:'por_receiveqty',width:100,halign:'center',align:'right',formatter: numberformat">Receipt</th>
            <th data-options="field:'outstanding',width:100,halign:'center',align:'right',formatter: numberformat">Outstanding</th>
            <th data-options="field:'status',width:100,halign:'center',align:'center',formatter: cellFormatter,styler: cellStyler">Status</th>
            <!-- <th data-options="field:'status',width:100,align:'center',styler:cellStyler, formatter:cellFormatter">Status</th>
            <th data-options="field:'por_emp',width:100,halign:'center'">Receipt By</th> -->
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 230px; padding: 10px;">
    <fieldset style="width:100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        
    <legend><b>Filter Data</b></legend>
        <div style="width: 50%;">
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
                <span style="width:35%; display:inline-block;">Item Id</span>
                <input style="width:60%;" name="item_id" id="item_id" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </div>
        <div style="width: 45%;">
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

<!-- PDF -->
<iframe id="printout" src="" style="width: 100%;" hidden></iframe>

<script>

    function filter() {
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
            "&trans_type=" + window.btoa(trans_type);

        if(vendor_id == "" && item_id == ""){
            toastr.info("Please Select Vendor or Item ID");
        }else{
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
            "&trans_type=" + window.btoa(trans_type);

        if(vendor_id == "" && item_id == ""){
            toastr.info("Please Select Vendor or Item ID");
        }else{
            window.location.assign('<?= base_url('planning/os_purchase_order/print/excel') ?>' + url);
        }
    }

    function csv() {
        $.messager.prompt('Export CSV', 'Please input Password "CSV" for Export Data CSV', function(r){
            if (r == "CSV"){
                $.messager.alert('Info','Please Wait Export to CSV');
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
            onSelect: function(vendor){
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
                    onSelect: function(prodfam){
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
                            onSelect: function(val, item){
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
</script>