<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar"></table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 35px;">
	<a href="javascript:void(0)" id="approveall" class="easyui-linkbutton" data-options="plain:true" onclick="approveall()"><i class="fa fa-check"></i> Approve ALL</a>
	<a href="javascript:void(0)" id="disapproveall" class="easyui-linkbutton" data-options="plain:true" onclick="disapproveall()"><i class="fa fa-times"></i> Disapprove ALL</a>
	<a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="export_excel()"><i class="fa fa-file"></i> Export Excel</a>
	<a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="reload()"><i class="fa fa-refresh"></i> Reload</a>
</div>

<script>
	//RELOAD
	function reload() {
		window.location.reload();
	}

	function export_excel() {
		$('#dg').datagrid('toExcel', "approval_<?= $table ?>.xls");
	}

	function approveall() {
		$.messager.confirm('Warning', 'Are you sure you want to approve all data?', function(r) {
			if (r) {
				$.post('<?= base_url('approvals/approveall') ?>', {
					table_name: "<?= $table ?>",
					approved_to: "<?= $approved_to ?>",
					created_by: "<?= $created_by ?>",
					approved_by: "<?= $approved_by ?>",
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

	function disapproveall() {
		$.messager.confirm('Warning', 'Are you sure you want to disapprove all data?', function(r) {
			if (r) {
				Swal.fire({
					title: 'Please Wait for Process Disapprove',
					showConfirmButton: false,
					allowOutsideClick: false,
					allowEscapeKey: false,
					didOpen: () => {
						Swal.showLoading();
					},
				});

				$.post('<?= base_url('approvals/disapproveall') ?>', {
					table_name: "<?= $table ?>",
					approved_to: "<?= $approved_to ?>",
					created_by: "<?= $created_by ?>",
					approved_by: "<?= $approved_by ?>",
				}, function(result) {
					var result = eval('(' + result + ')');
					Swal.close();

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

	function approve(id, tablename) {
		$.messager.confirm('Warning', 'Are you sure you want to approve this data?', function(r) {
			if (r) {
				$.post('<?= base_url('approvals/approve') ?>', {
					id: id,
					tablename: tablename
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

	function disapprove(id, tablename) {
		$.messager.confirm('Warning', 'Are you sure you want to disapprove this data?', function(r) {
			if (r) {
				$.post('<?= base_url('approvals/disapprove') ?>', {
					id: id,
					tablename: tablename
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

	function numberformat(value, row) {
		const formatter = new Intl.NumberFormat('id-ID', {
			minimumFractionDigits: 0
		});
		if (parseFloat(value) >= 0) {
			return "<b>" + formatter.format(value) + "</b>";
		} else {
			return "<b style='color:red;'>" + formatter.format(value) + "</b>";
		}
	}

	function numberformatPrice(value, row) {
		const formatter = new Intl.NumberFormat('id-ID', {
			minimumFractionDigits: 2
		});

		return "<b>" + formatter.format(value) + "</b>";
	}

	$(function() {
		//SETTING DATAGRID EASYUI
		$('#dg').datagrid({
			url: '<?= base_url('approvals/approvalForecastSupplier/') ?>' + "<?= base64_encode($approved_to) ?>" + "/" + "<?= base64_encode($created_by) ?>" + "/" + "<?= base64_encode($approved_by) ?>",
			pagination: true,
			singleSelect: true,
			clientPaging: true,
			remoteFilter: false,
			rownumbers: true,
			fit: true,
			columns: [
				[{
					field: 'p_month',
					width: 80,
					align: 'center',
					title: "Month",
					rowspan: 2
				}, {
					field: 'p_year',
					width: 80,
					align: 'center',
					title: "Year",
					rowspan: 2
				}, {
					field: 'revision',
					width: 80,
					align: 'center',
					title: "Rev",
					rowspan: 2
				}, {
					field: 'part_no',
					width: 150,
					halign: 'center',
					title: "Part No",
					rowspan: 2
				}, {
					field: 'part_name',
					width: 120,
					halign: 'center',
					title: "Part Name",
					rowspan: 2
				}, {
					field: 'product_family',
					width: 120,
					halign: 'center',
					title: "Product Family",
					rowspan: 2
				}, {
					field: 'vendor_name',
					width: 250,
					halign: 'center',
					title: "Vendor Name",
					rowspan: 2
				}, {
					field: 'vendor_type',
					width: 100,
					align: 'center',
					title: "Vendor Type",
					rowspan: 2
				}, {
					field: 'vendor_origin',
					width: 100,
					halign: 'center',
					title: "Origin",
					rowspan: 2
				}, {
					field: 'leadtime',
					width: 80,
					halign: 'center',
					title: "Leadtime",
					rowspan: 2
				}, {
					field: 'share_order',
					width: 80,
					halign: 'center',
					title: "Share Order",
					rowspan: 2
				}, {
					field: 'mpq',
					width: 100,
					halign: 'center',
					title: "MPQ",
					rowspan: 2,
					formatter: numberformat,
				}, {
					field: 'moq',
					width: 100,
					halign: 'center',
					title: "MOQ",
					rowspan: 2,
					formatter: numberformat,
				}, {
					field: 'maker_name',
					width: 150,
					halign: 'center',
					title: "Maker Name",
					rowspan: 2,
				}, {
					field: 'maker_type',
					width: 100,
					halign: 'center',
					title: "Maker Type",
					rowspan: 2
				}, {
					field: 'wo',
					width: 100,
					halign: 'center',
					halign: 'right',
					title: "WORKORDER",
					colspan: 4,
				}, {
					field: 'total_stock',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "Stock WHS",
					rowspan: 2,
					formatter: numberformat,
				}, {
					field: 'qty_vendor',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "OS PO",
					rowspan: 2,
					formatter: numberformat,
				}, {
					field: 'fcst',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "FORECAST",
					colspan: 13,
				}, {
					field: 'action',
					width: 80,
					align: 'center',
					title: "Action",
					rowspan: 2,
					formatter: function(val, row) {
						if (val != "-") {
							var approve = "approve('" + row.id + "','" + "<?= $table ?>" + "')";
							var disapprove = "disapprove('" + row.id + "','" + "<?= $table ?>" + "')";
							var a = '<a class="btn btn-success w-50" style="pointer-events: visible; opacity:1;" onclick="' + approve + '"><i class="fa fa-check"></i></a>';
							var b = '<a class="btn btn-danger w-50" style="pointer-events: visible; opacity:1;" onclick="' + disapprove + '"><i class="fa fa-times"></i></a>';
							return a + " " + b;
						}
					}
				}],
				[{
					field: 'issued_1',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "WO M-1",
					formatter: numberformat,
				}, {
					field: 'issued_2',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "WO M-2",
					formatter: numberformat,
				}, {
					field: 'issued_3',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "WO M-3",
					formatter: numberformat,
				}, {
					field: 'issued_avg',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "Xbar",
					formatter: numberformat,
				}, {
					field: 'need_1',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "Need M1",
					formatter: numberformat,
				}, {
					field: 'balance_1',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "Bal 1",
					formatter: numberformat,
				}, {
					field: 'forecast_1',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "FC 1",
					formatter: numberformat,
				}, {
					field: 'need_2',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "Need M2",
					formatter: numberformat,
				}, {
					field: 'balance_2',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "Bal 2",
					formatter: numberformat,
				}, {
					field: 'forecast_2',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "FC 2",
					formatter: numberformat,
				}, {
					field: 'need_3',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "Need M3",
					formatter: numberformat,
				}, {
					field: 'balance_3',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "Bal 3",
					formatter: numberformat,
				}, {
					field: 'forecast_3',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "FC 3",
					formatter: numberformat,
				}, {
					field: 'need_4',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "Need M4",
					formatter: numberformat,
				}, {
					field: 'balance_4',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "Bal 4",
					formatter: numberformat,
				}, {
					field: 'forecast_4',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "FC 4",
					formatter: numberformat,
				}, {
					field: 'avg_need',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "Xbar",
					formatter: numberformat,
				}, ]
			],
		}).datagrid('enableFilter');
	});
</script>