<table id="dg" class="easyui-datagrid" style="width:100%;"></table>
<div id="toolbar">
    <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px;">
        <legend><b>Form Filter Data</b></legend>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Plant</span>
                    <input style="width:60%;" name="filter_plant" id="filter_plant" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Vendor</span>
                    <input style="width:60%;" name="filter_suppliers" id="filter_suppliers" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Maker</span>
                    <input style="width:60%;" name="filter_maker" id="filter_maker" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Category</span>
                    <input style="width:60%;" id="filter_category" class="easyui-combobox">
                </div>
            </div>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part No</span>
                    <input style="width:60%;" name="filter_items" id="filter_items" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Status</span>
                    <input style="width:60%;" id="filter_status" class="easyui-combobox">
                </div>
            </div>
    </fieldset>
    <?= $button ?>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= $base_url ?>datatables/print" style="width: 100%;" hidden></iframe>

<script>
    $(function() {
        $("#dg").datagrid({
            url: "",
            toolbar: "#toolbar",
            remoteFilter: true,
            pagination: true,
            rownumbers: true,
            fit: true,
            frozenColumns: [[
                {field: "approval_data", title: "Status", halign: "center", align: "center", formatter: cellFormatterApproval,styler: cellStylerApproval},
                {field: "item_id", title: "Part No", halign: "center"},
                {field: "item_supplier", title: "Part Vendor", halign: "center"},
                {field: "item_name", title: "Part Name", halign: "center"},
                {field: "uom", title: "UoM", halign: "center", align: "center"},
            ]],
            columns: [[
                {field: "maker_name", title: "Maker", halign: "center", rowspan: 2},
                {field: "maker_type", title: "Maker Type", halign: "center", rowspan: 2},
                {field: "maker_origin", title: "Maker Origin", halign: "center", rowspan: 2},
                {field: "quotation_number", title: "Last Quotation No", halign: "center", rowspan: 2},
                {field: "spq", title: "SPQ", halign: "center", align: "right", rowspan: 2, formatter: numberformatComa},
                {field: "mpq", title: "MPQ", halign: "center", align: "right", rowspan: 2, formatter: numberformatComa},
                {field: "moq", title: "MOQ", halign: "center", align: "right", rowspan: 2, formatter: numberformatComa},
                {field: "leadtime", title: "Leadtime", halign: "center", rowspan: 2},
                {field: "currency", title: "Currency", halign: "center", rowspan: 2},
                {field: "incoterm", title: "Incoterm", halign: "center", rowspan: 2},
                {field: "exchange_rate", title: "Exchange Rate", halign: "center", rowspan: 2},
                {field: "price", title: "Price", halign: "center", align: "right", rowspan: 2, formatter: numberformat},
                {title: "Valid Date", halign: "center", colspan: 2},
                {title: "Created", halign: "center", colspan: 2},
                {field: "reason", title: "Reason", halign: "center", rowspan: 2},
            ],[
                {field: "start_date", title: "Start", halign: "center", align: "center"},
                {field: "valid_date", title: "Ending", halign: "center", align: "center", styler: cellStylerValidEnding},
                {field: "create_by", title: "By", halign: "center", align: "center"},
                {field: "create_date", title: "Date", halign: "center", align: "center"}
            ]]
        });

        $('#filter_plant').combogrid({
            url: '<?= base_url('planning/plants/reads') ?>',
            panelWidth: 350,
            idField: 'number',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Plant",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'number',
                    title: 'Code',
                    width: 100
                }, {
                    field: 'name',
                    title: 'Name',
                    width: 200
                }]
            ],
            onSelect: function (index, row) {
                $('#filter_category').combobox({url: "<?= base_url('planning/mst_item/readFamily') ?>?plant="+btoa(row.number)});
                $('#filter_maker').combogrid({url: "<?= base_url('planning/mst_maker/reads') ?>" + "?plant=" + btoa(row.number)});
            }
        });

        $('#filter_suppliers').combogrid({
            url: '<?= base_url('planning/suppliers/reads') ?>',
            panelWidth: 350,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Vendor",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'number',
                    title: 'Code',
                    width: 100
                }, {
                    field: 'name',
                    title: 'Name',
                    width: 200
                }]
            ]
        });

        $('#filter_maker').combogrid({
            url: "<?= base_url('planning/mst_maker/reads') ?>",
            panelWidth: 550,
            idField: 'number',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Maker",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'number',
                    title: 'Code',
                    width: 100
                }, {
                    field: 'name',
                    title: 'Name',
                    width: 200
                }, {
                    field: 'type',
                    title: 'Type',
                    width: 100
                }, {
                    field: 'origin',
                    title: 'Origin',
                    width: 100
                }]
            ]
        });

        $('#filter_category').combobox({
            url: "<?= base_url('planning/mst_item/readFamily') ?>",
            valueField: 'number',
            textField: 'name',
            prompt: 'Select Category',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(row) {
                $('#filter_items').combogrid({
                    url: "<?= $base_url ?>" + 'mst_item/reads/'+row.number + "?plant=" + btoa($('#filter_plant').combogrid("getValue")),
                    panelWidth: 420,
                    idField: 'item_id',
                    textField: 'item_id',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Select Part",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combogrid('clear').combogrid('grid').datagrid('reload', {});
                        }
                    }],
                    columns: [
                        [{
                            field: 'item_id',
                            title: 'Part No',
                            width: 100
                        }, {
                            field: 'item_name',
                            title: 'Part Name',
                            width: 200
                        }, ]
                    ]
                });
            }
        });

        $('#filter_items').combogrid({
            url: '<?= base_url('planning/mst_item/reads') ?>',
            panelWidth: 350,
            idField: 'item_id',
            textField: 'item_id',
            mode: 'remote',
            fitColumns: true,
            pagination: true,
            prompt: "Select Part No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) { $(e.data.target).combogrid('clear').combogrid('textbox').focus(); }
            }],
            columns: [[
                {field: 'item_id', title: 'Part No', width: 200},
                {field: 'item_name', title: 'Part Name', width: 100}
            ]]
        });

        $('#filter_status').combobox({
            valueField: 'v',
            textField: 't',
            prompt: "Select Status",
            data: [
                {v:1, t:"Submission"},
                {v:2, t:"Checking"},
                {v:3, t:"Disapproved"},
                {v:4, t:"Approved"}
            ],
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) { $(e.data.target).combobox('clear').combobox('textbox').focus(); }
            }]
        });

        filter();
    });

    function filter() {
        var filter_plant = $("#filter_plant").combogrid('getValue');
        var filter_suppliers = $("#filter_suppliers").combogrid('getValue');
        var filter_category = $("#filter_category").combobox('getValue');
        var filter_maker = $("#filter_maker").combogrid('getValue');
        var filter_items = $("#filter_items").combogrid('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "&filter_items=" + window.btoa(filter_items) +
            "&filter_category=" + window.btoa(filter_category) +
            "&filter_maker=" + window.btoa(filter_maker) +
            "&filter_status=" + window.btoa(filter_status) +
            "&filter_plant=" + window.btoa(filter_plant) +
            "&filter_suppliers=" + window.btoa(filter_suppliers);

        $('#dg').datagrid("reload", "<?= base_url('planning/supplier_items/datatables?vendor=t') ?>"+url);
        $("#printout").attr("src", "<?= base_url('planning/supplier_items/print?vendor=t') ?>"+url);
    }

    function pdf() { $("#printout").get(0).contentWindow.print(); }

    function excel() {
        var filter_plant = $("#filter_plant").combogrid('getValue');
        var filter_suppliers = $("#filter_suppliers").combogrid('getValue');
        var filter_category = $("#filter_category").combobox('getValue');
        var filter_maker = $("#filter_maker").combogrid('getValue');
        var filter_items = $("#filter_items").combogrid('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "&filter_items=" + window.btoa(filter_items) +
            "&filter_category=" + window.btoa(filter_category) +
            "&filter_maker=" + window.btoa(filter_maker) +
            "&filter_status=" + window.btoa(filter_status) +
            "&filter_plant=" + window.btoa(filter_plant) +
            "&filter_suppliers=" + window.btoa(filter_suppliers);

        window.location.assign("<?= base_url('planning/supplier_items/print/excel?vendor=t') ?>");
    }

    function reload() { window.location.reload(); }

    function cellStylerValidEnding(value, row, index) {
        if (row.valid_date != null) {
            var date = row.valid_date.split("-");
            var now = new Date(), ending = new Date(date[0], (parseInt(date[1]) - 1), date[2]);
            
            return "background: #"+((now > ending) ? "FF5F5F" : "53D636")+"; color:white;";
        }
    }

    function cellFormatterApproval(val, row) {
        var value = 'Checking';
        if (row.status == '2') { value = 'Disapproved'; }
        else if (row.approved == '1') { value = 'Submission'; }
        else if (row.approved_to == "" || row.approved_to == null) { value = 'Approved'; }
        return value;
    }

    function cellStylerApproval(value, row, index) {
        var color = 'ffb25f';
        if (row.status == '2') { color = 'FF5F5F'; }
        else if (row.approved_to == "" || row.approved_to == null) { color = '53D636'; }
        return 'background: #'+color+'; color:white;';
    }

    function numberformat(value, row) {
        if (value != null) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 4
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function numberformatComa(value, row) {
        if (value != null) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }
</script>