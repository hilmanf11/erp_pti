<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'created_date',width:150,halign:'center'">Create Date</th>
            <th data-options="field:'created_by',width:100,halign:'center'">Create By</th>
            <th data-options="field:'item_id',width:200,halign:'center'">Part No</th>
            <th data-options="field:'item_name',width:150,halign:'center'">Part Name</th>
            <th data-options="field:'item_supplier',width:150,halign:'center'">Part Vendor</th>
            <th data-options="field:'pfm_name',width:120,halign:'center'">Product Family</th>
            <th data-options="field:'coo_name',width:100,halign:'center'">COO Name</th>
            <th data-options="field:'supplier_name',width:250,halign:'center'">Vendor Name</th>
            <th data-options="field:'type',width:100,align:'center'">Vendor Type</th>
            <th data-options="field:'origin',width:120,halign:'center'">Vendor Origin</th>
            <th data-options="field:'maker_name',width:200,halign:'center'">Maker</th>
            <th data-options="field:'maker_type',width:100,halign:'center'">Maker Type</th>
            <th data-options="field:'maker_origin',width:150,halign:'center'">Maker Origin</th>
            <th data-options="field:'landed_cost',width:100,halign:'center'">Landed Cost</th>
            <th data-options="field:'import_duty',width:100,halign:'center'">Import Duty</th>
            <th data-options="field:'mpq',width:80,halign:'center',align:'right'">MPQ</th>
            <th data-options="field:'moq',width:80,halign:'center',align:'right'">MOQ</th>
            <th data-options="field:'uom',width:80,align:'center'">UoM</th>
            <th data-options="field:'leadtime',width:80,align:'center'">Leadtime<br></th>
            <th data-options="field:'share_order',width:80,align:'center'">Share Order<br></th>
            <th data-options="field:'remarks',width:250,halign:'center'">Remarks</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 200px; padding:10px;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 60%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:30%;" id="filter_from" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    <input style="width:30%;" id="filter_to" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier</span>
                    <input style="width:60%;" id="filter_supplier" class="easyui-combogrid">
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
    </div>
    <?= $button ?>
</div>

<!-- PDF -->
<iframe id="printout" src="" style="width: 100%;" hidden></iframe>

<script>
    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');
        var filter_part_no = $("#filter_part_no").combogrid('getValue');
        var filter_supplier = $("#filter_supplier").combogrid('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_product_family=" + window.btoa(filter_product_family) +
            "&filter_part_no=" + window.btoa(filter_part_no);

        $('#dg').datagrid({
            url: '<?= base_url('planning/supplier_items_log/datatables') ?>' + url,
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('planning/supplier_items_log/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');
        var filter_part_no = $("#filter_part_no").combogrid('getValue');
        var filter_supplier = $("#filter_supplier").combogrid('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_product_family=" + window.btoa(filter_product_family) +
            "&filter_part_no=" + window.btoa(filter_part_no);

        window.location.assign('<?= base_url('planning/supplier_items_log/print/excel') ?>' + url);
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
    	$("#add").html("Generate");
        
        $('#dg').datagrid({
            pagination: true,
            rownumbers: true,
            singleSelect: true,
            fit:true
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
            url: '<?= base_url('planning/suppliers/reads') ?>',
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
                $('#filter_po').combobox({
                    url: '<?php echo base_url('planning/supplier_items_log/readPoSearch?supplier_id='); ?>' + window.btoa(supplier.id),
                    valueField: 'purchase_order_no',
                    textField: 'purchase_order_no',
                    prompt: 'Select PO No',
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
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
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0
        });

        return "<b>" + formatter.format(value) + "</b>";
    }
</script>