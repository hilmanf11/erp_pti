<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar"></table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar">
    <a href="javascript:void(0)" id="approveall" class="easyui-linkbutton" data-options="plain:true" onclick="approveall()"><i class="fa fa-check"></i> Approve ALL</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="export_excel()"><i class="fa fa-file"></i> Export Excel</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="reload()"><i class="fa fa-refresh"></i> Reload</a>
</div>

<script>
    //RELOAD
    function reload() { window.location.reload(); }

    function export_excel() {
    	var tb = $('<table>'), dg = $('#dg');
    	var columns = dg.datagrid('options').columns, new_column = [], idx = [];

    	delete columns[0][0];
    	columns[0].forEach(function (arr, index) {
    		delete arr.rowspan;
			if (arr.colspan == undefined) { new_column.push(arr); }
			else {
				for (var i = 0; i < arr.colspan; i++) {
					idx.push(new_column.length);
					new_column.push(arr);
				}
			}
    	});
    	columns[1].forEach(function (arr, index) {
    		arr.title = new_column[idx[index]].title + ' ' + arr.title;
			new_column[idx[index]] = arr;
    	});

    	tb.datagrid({
	        data: dg.datagrid('getData').rows,
	        columns: [new_column]
	    });
	    setTimeout(function () {
	    	tb.datagrid('toExcel', "approval_<?= $table ?>.xls");
	    },0);
	}

    function approveall() {
    	var dg = $('#dg'), approve = true, datas = [];
    	dg.datagrid('acceptChanges');
    	dg.datagrid('getData').rows.forEach(function (value, key) {
			if (approve && (
    			value.currency == null ||
				value.price == null || parseFloat(value.price) < 0 ||
				value.new_price == null || parseFloat(value.new_price) < 0 ||
				value.percentage == null || parseFloat(value.percentage) < 0 ||
				value.moq == null || parseFloat(value.moq) < 0 ||
				value.mpq == null || parseFloat(value.mpq) < 0 ||
				value.spq == null || parseFloat(value.spq) < 0 ||
				value.leadtime == null || parseFloat(value.leadtime) < 0 ||
				value.procurement_respons_date == null ||
				value.item_id_maker == null ||
				value.quot_req == null ||
				value.quotation == null ||
				value.incoterms == null
			)) { approve = false; toastr.warning("Please fill required field!", "Information"); }
			else { datas.push({
				id: value.id,
				currency: value.currency,
				price: value.price,
				new_price: value.new_price,
				percentage: value.percentage,
				moq: value.moq,
				mpq: value.mpq,
				spq: value.spq,
				leadtime: value.leadtime,
				status: value.status,
				remarks1: value.remarks1,
				remarks2: value.remarks2,
				remarks3: value.remarks3,
				procurement_respons_date: value.procurement_respons_date,
				item_id_maker: value.item_id_maker,
				quot_req: value.quot_req,
				quotation: value.quotation,
				incoterms: value.incoterms
			}); }
    	});

    	if (approve) {
			$.messager.confirm('Warning', 'Are you sure you want to approve all data?', function(r) {
				if (r) {
					$.post('<?= base_url('approvals/approveall') ?>', {
						table_name: "<?= $table ?>",
						approved_to: "<?= $approved_to ?>",
						created_by: "<?= $created_by ?>",
						approved_by: "<?= $approved_by ?>",
						datas: btoa(JSON.stringify(datas))
					}, function(result) {
						var result = eval('(' + result + ')');
						if (result.theme == "success") {
							toastr.success(result.message, result.title);
							$("#dg").datagrid("reload");
						} else {
							toastr.error(result.message, result.title);
						}
					});
				}
			});
    	}
	}

    function numberFormat(value, row) {
		const formatter = new Intl.NumberFormat('id-ID', {minimumFractionDigits: 4});

		return (value != null) ? "<b>" + formatter.format(value) + "</b>" : value;
	}

    function cellStyler(value, row, index) { return (value == 0) ? 'background: #FF5F5F; color:white;' : 'background: #53D636; color:white;'; }
    function cellFormatter(value) { return (value == 0) ? 'OPEN' : 'CLOSED'; }

    function dateformatter(date) {
        var y = date.getFullYear();
        var m = date.getMonth() + 1;
        var d = date.getDate();
        return y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d);
    }

    function dateparser(s) {
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

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('approvals/approvalNewPartPriceProcurements/') ?>' + "<?= base64_encode($approved_to) ?>" + "/" + "<?= base64_encode($created_by) ?>" + "/" + "<?= base64_encode($approved_by) ?>",
            pagination: false,
            singleSelect: false,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            columns: [[
            	{field: 'request_date', halign: 'center', align: 'center', title: "Request Date"},
        		{field: 'due_date', halign: 'center', align: 'center', title: "Due Date"},
            	{field: 'plant_name', halign: 'center', title: "Plant Name"},
            	{field: 'volume_per_month', halign: 'center', align: 'right', title: "Volume/Month"},
            	{field: 'volume_per_year', halign: 'center', align: 'right', title: "Volume/Year"},
            	{field: 'project_category', halign: 'center', title: "Category"},
            	{field: 'assy_no', halign: 'center', title: "Assy No"},
            	{field: 'model_name', halign: 'center', title: "Model"},
            	{field: 'customer_name', halign: 'center', title: "Customer"},
            	{field: 'business_line_name', halign: 'center', title: "Business Line"},
            	{field: 'application', halign: 'center', title: "Application"},
            	{field: 'masspro', halign: 'center', title: "Masspro"},
            	{field: 'end_user', halign: 'center', title: "End User"},
            	{field: 'item_id', halign: 'center', title: "Item ID"},
            	{field: 'item_name', halign: 'center', title: "Item Name"},
            	{field: 'maker_name', halign: 'center', title: "Maker"},
            	{field: 'qty_unit', halign: 'center', align: 'right', title: "Qty/Unit", formatter: numberFormat},
            	{field: 'qty_month', halign: 'center', align: 'right', title: "Qty/Month", formatter: numberFormat},
            	{field: 'um_name', halign: 'center', align: 'center', title: "UoM"},
            	{field: 'currency', halign: 'center', align: 'center', title: "Currency", editor:{
            		type: 'combobox',
            		options: {
            			required: true,
            			url: "<?= base_url('master/currencies/reads') ?>",
            			valueField: "name",
            			textField: "name"
            		}
            	}},
            	{field: 'price', halign: 'center', align: 'right', title: "Price", formatter: numberFormat, editor:{
            		type: 'numberbox',
            		options: {required: true, precision: 4}
            	}},
            	// {field: 'new_price', halign: 'center', align: 'right', title: "New Price", formatter: numberFormat, editor:{
            	// 	type: 'numberbox',
            	// 	options: {required: true, precision: 4}
            	// }},
            	// {field: 'percentage', halign: 'center', align: 'right', title: "Percentage", formatter: numberFormat, editor:{
            	// 	type: 'numberbox',
            	// 	options: {required: true, precision: 2}
            	// }},
            	{field: 'moq', halign: 'center', align: 'right', title: "MOQ", formatter: numberFormat, editor:{
            		type: 'numberbox',
            		options: {required: true, precision: 4}
            	}},
            	{field: 'mpq', halign: 'center', align: 'right', title: "MPQ", formatter: numberFormat, editor:{
            		type: 'numberbox',
            		options: {required: true, precision: 4}
            	}},
            	{field: 'spq', halign: 'center', align: 'right', title: "SPQ", formatter: numberFormat, editor:{
            		type: 'numberbox',
            		options: {required: true, precision: 4}
            	}},
            	{field: 'leadtime', halign: 'center', align: 'right', title: "Lead Time", editor:{
            		type: 'numberbox',
            		options: {required: true}
            	}},
            	{field: 'status', halign: 'center', align: 'center', title: "Status", width: 90, styler: cellStyler, formatter: cellFormatter},
            	{field: 'remarks1', halign: 'center', title: "Remarks 1", editor: 'textbox'},
            	{field: 'remarks2', halign: 'center', title: "Remarks 2", editor: 'textbox'},
            	{field: 'remarks3', halign: 'center', title: "Remarks 3", editor: 'textbox'},
            	{field: 'procurement_respons_date', halign: 'center', title: "Procurement Response Date", editor:{
            		type: 'datebox',
            		options: {required: true, formatter: dateformatter, parser: dateparser}
            	}},
            	{field: 'item_id_maker', halign: 'center', title: "Item ID Maker", editor:{
            		type: 'textbox',
            		options: {required: true}
            	}},
            	{field: 'quot_req', halign: 'center', title: "Quot Req", editor:{
            		type: 'textbox',
            		options: {required: true}
            	}},
            	{field: 'quotation', halign: 'center', title: "Quotation", editor:{
            		type: 'textbox',
            		options: {required: true}
            	}},
            	{field: 'incoterms', halign: 'center', title: "Incoterms", width: 200, editor:{
            		type: 'combobox',
            		options: {
            			required: true,
            			url: "<?= base_url('master/incoterms/reads') ?>",
            			valueField: "name",
            			textField: "name"
            		}
            	}},
            ]],
        }).datagrid('enableCellEditing');
    });
</script>