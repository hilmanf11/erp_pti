<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar"></table>
<div id="toolbar">
	<fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px;">
        <legend><b>Filter Data</b></legend>
        <div style="width: 30%; float: left;">
            <div class="fitem">
            	<span style="width:35%; display:inline-block;">LME Range</span>
                <input style="width:60%;" name="f_range" id="f_range" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Supplier</span>
                <input style="width:60%;" name="f_supplier" id="f_supplier" class="easyui-combogrid">
            </div>
        </div>
        <div style="width: 30%; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Item ID</span>
                <input style="width:60%;" name="f_item_id" id="f_item_id" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="btnSearch()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </div>
    </fieldset>
	<?= $button ?>
</div>
<iframe id="printout" src="<?= base_url('planning/marketing_price_wire/datatables/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    $(function() {
        $('#dg').datagrid({
            url: '<?= base_url('planning/marketing_price_wire/datatables') ?>',
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            pagination: true,
            fit: true,
            columns: [[
            	{field: 'item', title: 'Item ID', halign: 'center'},
            	{field: 'supp_id', title: 'Supplier ID', halign: 'center'},
            	{field: 'supp_name', title: 'Supplier Name', halign: 'center'},
            	{field: 'supp_type', title: 'Supplier Type', halign: 'center', align: 'center'},
            	{field: 'maker_id', title: 'Maker Code', halign: 'center'},
            	{field: 'maker_name', title: 'Wire Name Maker', halign: 'center'},
            	{field: 'ranges', title: 'LME Range', halign: 'center'},
            	{field: 'price', title: 'Price', halign: 'center', align: 'right'},
            	{field: 'duty', title: 'Import Duty', halign: 'center', align: 'right'},
            	{field: 'insurance', title: 'Insurance', halign: 'center', align: 'right'},
            	{field: 'transport', title: 'Transportation', halign: 'center', align: 'right'},
            	{field: 'total', title: 'Total Price', halign: 'center', align: 'right'}
            ]]
        });

        $('#f_range').combogrid({
            url: '<?= base_url('planning/marketing_price_wire/readRanges'); ?>',
            panelWidth: 550,
            idField: 'range',
            textField: 'range',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select LME Range",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) { $(e.data.target).combogrid('clear').combogrid('textbox').focus(); }
            }],
            columns: [[{field: 'range', title: 'LME Range', halign: 'center', width: '98%'}]]
        });

        $('#f_supplier').combogrid({
            url: '<?= base_url('planning/marketing_price_wire/readSuppliers'); ?>',
            panelWidth: 550,
            idField: 'id',
            textField: 'supplier',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Supplier",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) { $(e.data.target).combogrid('clear').combogrid('textbox').focus(); }
            }],
            columns: [[
            	{field: 'id', title: 'Supplier ID', halign: 'center', width: '29%'},
            	{field: 'supplier', title: 'Supplier Name', halign: 'center', width: '69%'}
            ]]
        });

        $('#f_item_id').combogrid({
            url: '<?= base_url('planning/marketing_price_wire/readItems'); ?>',
            panelWidth: 550,
            idField: 'item',
            textField: 'item',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Item ID",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) { $(e.data.target).combogrid('clear').combogrid('textbox').focus(); }
            }],
            columns: [[{field: 'item', title: 'Item ID', halign: 'center', width: '98%'}]]
        });
    });

	function btnSearch() {
		var range = window.btoa($('#f_range').combogrid('getValue'));
		var item = window.btoa($('#f_item_id').combogrid('getValue'));
		var supplier = window.btoa($('#f_supplier').combogrid('getValue'));

		$('#dg').datagrid('reload', {
			supplier: supplier,
			range: range,
			item: item
		});
        $("#printout").attr('src', '<?= base_url('planning/marketing_price_wire/datatables/print') ?>'
            +'?supplier='+supplier
            +'&range='+range
            +'&item='+item
        );
	}

    function pdf() { $("#printout").get(0).contentWindow.print(); }

    function excel() {
        $.messager.alert("Information", "Export to excel, please wait...", "info");
        window.location.assign('<?= base_url('planning/marketing_price_wire/excel') ?>');
    }

    function reload() { window.location.reload(); }

    function myformatter(date) {
        var y = date.getFullYear();
        var m = date.getMonth() + 1;
        var d = date.getDate();
        return y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d);
    }

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