<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead frozen="true">
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'part_no',width:200,halign:'center'">Part No</th>
            <th data-options="field:'part_name',width:120,halign:'center'">Part Name</th>
        </tr>
    </thead>
    <thead>
        <tr>
            <th rowspan="2" data-options="field:'product_family',width:120,halign:'center'">Product Family</th>
            <th colspan="2" data-options="field:'mrp',width:100,halign:'center',hstyler: function(value, row, index) { return 'background-color:#B0CFFF'; }">MRP</th>
            <th colspan="5" data-options="field:'mrp',width:100,halign:'center',hstyler: function(value, row, index) { return 'background-color:#FFF9B0'; }">Purchase Request</th>
            <th colspan="7" data-options="field:'mrp',width:100,halign:'center',hstyler: function(value, row, index) { return 'background-color:#B8FFB0'; }">Purchase Order</th>
        </tr>
        <tr>
            <th data-options="field:'generate_date',width:150,align:'center'">Generate Date</th>
            <!-- <th data-options="field:'generate_by',width:120,align:'center'">Generate By</th> -->
            <th data-options="field:'total_need',width:80,halign:'center',align:'right', formatter: numberformat">Total Need</th>
            <th data-options="field:'pr_date',width:150,align:'center'">Create Date</th>
            <!-- <th data-options="field:'pr_by',width:120,align:'center'">Create By</th> -->
            <th data-options="field:'purchase_request_no',width:180,align:'center'">Purchase Request No</th>
            <th data-options="field:'pr_supplier_name',width:150,halign:'center'">Supplier</th>
            <th data-options="field:'total_pr',width:80,halign:'center',align:'right', formatter: numberformat">Qty</th>
            <th data-options="field:'reason_pr',width:200,halign:'center',align:'right'">Reason</th>
            <!-- <th data-options="field:'pr_approved_by',width:120,align:'center'">Last Approve</th> -->
            <!-- <th data-options="field:'pr_approved_date',width:150,align:'center'">Approve Date</th> -->
            <!-- <th data-options="field:'pr_approved_to',width:120,align:'center'">Next Approve</th> -->
            <th data-options="field:'po_date',width:150,align:'center'">Create Date</th>
            <!-- <th data-options="field:'po_by',width:120,align:'center'">Create By</th> -->
            <th data-options="field:'purchase_order_no',width:180,align:'center'">Purchase Order No</th>
            <th data-options="field:'po_supplier_name',width:150,halign:'center'">Supplier</th>
            <th data-options="field:'total_po',width:80,halign:'center',align:'right', formatter: numberformat">Qty</th>
            <th data-options="field:'reason_po',width:200,halign:'center',align:'right'">Reason</th>
            <!-- <th data-options="field:'po_approved_by',width:120,align:'center'">Last Approve</th> -->
            <!-- <th data-options="field:'po_approved_date',width:150,align:'center'">Approve Date</th> -->
            <!-- <th data-options="field:'po_approved_to',width:120,align:'center'">Next Approve</th> -->
            <th data-options="field:'email_date',width:150,align:'center'">Send Date</th>
            <th data-options="field:'confirm_date',width:150,align:'center'">Confirm Date</th>
        </tr>
    </thead>
</table>
<div id="toolbar" style="height: 230px; padding: 10px;">
    <fieldset style="width:80%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <legend><b>Form Filter Data</b></legend>
        <div style="float: left; width: 50%;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Period</span>
                <input style="width:30%;" name="filter_month" id="filter_month" value="<?= date("m") ?>" class="easyui-combobox" data-options="prompt:'Month'">
                <input style="width:30%;" name="filter_year" id="filter_year" value="<?= date("Y") ?>" class="easyui-combobox" data-options="prompt:'Year'">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Revision</span>
                <input style="width:60%;" name="filter_revision" id="filter_revision" value="<?= "0" ?>" class="easyui-combobox" data-options="prompt:'Revision'" panelHeight="auto">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Supplier Name</span>
                <input style="width:60%;" id="filter_supplier" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </div>
        <div style="float: left; width: 50%;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Purchase Request</span>
                <input style="width:60%;" id="filter_pr" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Purchase Order</span>
                <input style="width:60%;" id="filter_po" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product Family</span>
                <input style="width:60%;" id="filter_product_family" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Part No</span>
                <input style="width:60%;" id="filter_product_no" class="easyui-combogrid">
            </div>
        </div>
    </fieldset>
    <?= $button ?>
</div>

<!-- PDF -->
<iframe id="printout" src="" style="width: 100%; height: 520px; border: 0;" hidden></iframe>

<script>
    function filter() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_supplier = $("#filter_supplier").combogrid('getValue');
        var filter_pr = $("#filter_pr").combobox('getValue');
        var filter_po = $("#filter_po").combobox('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_pr=" + window.btoa(filter_pr) +
            "&filter_po=" + window.btoa(filter_po) +
            "&filter_product_family=" + window.btoa(filter_product_family) +
            "&filter_product_no=" + window.btoa(filter_product_no);

        if (filter_month == "" || filter_year == "" || filter_revision == "") {
            toastr.warning("Please Period and Revision!", "Information");
        } else {
            $('#dg').datagrid({
                url: '<?= base_url('planning/report_history_po/datatables') ?>' + url,
                // rowStyler: function(index, row) {
                //     if (row.p_month == null) {
                //         return 'background-color:#FFDAD2;';
                //     }
                // }
            });

            $("#printout").attr('src', '<?= base_url('planning/report_history_po/print') ?>' + url);
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_supplier = $("#filter_supplier").combogrid('getValue');
        var filter_pr = $("#filter_pr").combobox('getValue');
        var filter_po = $("#filter_po").combobox('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_pr=" + window.btoa(filter_pr) +
            "&filter_po=" + window.btoa(filter_po) +
            "&filter_product_family=" + window.btoa(filter_product_family) +
            "&filter_product_no=" + window.btoa(filter_product_no);

        if (filter_month == "" || filter_year == "" || filter_revision == "") {
            toastr.warning("Please Period and Revision!", "Information");
        } else {
            window.location.assign('<?= base_url('planning/report_history_po/print/excel') ?>' + url);
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
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
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
            }]
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
            }]
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
            }]
        });

        $('#filter_line_no').combobox({
            url: '<?php echo base_url('planning/mst_line/reads'); ?>',
            valueField: 'line_no',
            textField: 'remarks',
            prompt: 'Select All',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $('#filter_supplier').combogrid({
            url: '<?= base_url('planning/purchase_orders/readSupplier') ?>',
            panelWidth: 400,
            idField: 'id',
            textField: 'name',
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
                    field: 'number',
                    title: 'ID',
                    width: 80
                }, {
                    field: 'name',
                    title: 'Name',
                    width: 250
                }]
            ],
            onSelect: function(val, supplier){
                var filter_month = $("#filter_month").combobox('getValue');
                var filter_year = $("#filter_year").textbox('getValue');
                var filter_revision = $("#filter_revision").combobox('getValue');

                $('#filter_pr').combobox({
                    url: '<?php echo base_url('planning/report_history_po/readPr?filter_month='); ?>' + filter_month + "&filter_year=" + filter_year + "&filter_revision=" + filter_revision + "&filter_supplier=" + btoa(supplier.id),
                    valueField: 'purchase_request_no',
                    textField: 'purchase_request_no',
                    prompt: 'Select All',
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });

                $('#filter_po').combobox({
                    url: '<?php echo base_url('planning/report_history_po/readPo?filter_month='); ?>' + filter_month + "&filter_year=" + filter_year + "&filter_revision=" + filter_revision + "&filter_supplier=" + btoa(supplier.id),
                    valueField: 'purchase_order_no',
                    textField: 'purchase_order_no',
                    prompt: 'Select All',
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });
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
                $('#filter_product_no').combogrid({
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
    
    function numberformat(value, row) {
        if (value == "") {
            return 0;
        } else {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 0
            });

            return "<b>" + formatter.format(value) + "</b>";
        }
    }
</script>