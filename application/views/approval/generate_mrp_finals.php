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
		const formatter = new Intl.NumberFormat('id-ID');

		return "<b>" + formatter.format(value) + "</b>";
	}

	function numberformatPrice(value, row){
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2
        });

        return "<b>" + formatter.format(value) + "</b>";
    }

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('approvals/approvalGenerateMrpFinals/') ?>' + "<?= base64_encode($approved_to) ?>" + "/" + "<?= base64_encode($created_by) ?>" + "/" + "<?= base64_encode($approved_by) ?>",
            pagination: false,
            singleSelect: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            columns: [
                [{
					field: 'p_month',
					width: 80,
					align: 'center',
					title: "Month",
					rowspan: 2
				},{
					field: 'p_year',
					width: 80,
					align: 'center',
					title: "Year",
					rowspan: 2
				},{
					field: 'revision',
					width: 80,
					align: 'center',
					title: "Rev",
					rowspan: 2
				},{
					field: 'part_no',
					width: 200,
					halign: 'center',
					title: "Part No",
					rowspan: 2
				}, {
					field: 'part_name',
					width: 150,
					halign: 'center',
					title: "Part Name",
					rowspan: 2
				}, {
					field: 'product_family',
					width: 150,
					halign: 'center',
					title: "Product Family",
					rowspan: 2
				},{
					field: 'mpq',
					width: 80,
					align: 'center',
					title: "MPQ",
					rowspan: 2
				},{
					field: 'moq',
					width: 80,
					align: 'center',
					title: "MOQ",
					rowspan: 2
				},{
					field: 'leadtime',
					width: 100,
					align: 'center',
					title: "Leadtime",
					rowspan: 2
				},{
					field: 'workorder',
					width: 100,
					halign: 'center',
					title: "Workorder",
					colspan: 3
				},{
					field: 'issued_avg',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "AVG",
					rowspan: 2,
					formatter: numberformat,
				},{
					field: 'stock_rm',
					width: 100,
					align: 'center',
					title: "Stock Raw Material",
					colspan: 3
				},{
					field: 'total_stock',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "Total Stock",
					rowspan: 2,
					formatter: numberformat,
				},{
					field: 'qty_supply',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "OS Supply",
					rowspan: 2,
					formatter: numberformat,
				},{
					field: 'qty_wo',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "OS WO",
					rowspan: 2,
					formatter: numberformat,
				},{
					field: 'total_wo',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "Total OS",
					rowspan: 2,
					formatter: numberformat,
				},{
					field: 'period_1',
					width: 100,
					align: 'center',
					title: "Period 1",
					colspan: 2
				},{
					field: 'period_2',
					width: 100,
					align: 'center',
					title: "Period 2",
					colspan: 2
				},{
					field: 'period_3',
					width: 100,
					align: 'center',
					title: "Period 3",
					colspan: 2
				},{
					field: 'period_4',
					width: 100,
					align: 'center',
					title: "Period 4",
					colspan: 2
				},{
					field: 'period_5',
					width: 100,
					align: 'center',
					title: "Period 5",
					colspan: 2
				},{
					field: 'period_6',
					width: 100,
					align: 'center',
					title: "Period 6",
					colspan: 2
				},{
					field: 'total_need',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "Total Need",
					rowspan: 2,
					formatter: numberformat,
				},{
					field: 'purchase_order',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "Purchase Order",
					rowspan: 2,
					formatter: numberformat,
				},{
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
				}],[{
					field: 'issued_1',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "Need 1",
					formatter: numberformat,
				},{
					field: 'issued_2',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "Need 2",
					formatter: numberformat,
				},{
					field: 'issued_3',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "Need 3",
					formatter: numberformat,
				},{
					field: 'qty_rm',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "WHS",
					formatter: numberformat,
				},{
					field: 'qty_wip',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "WIP",
					formatter: numberformat,
				},{
					field: 'qty_vendor',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "OS SUPPLIER",
					formatter: numberformat,
				},{
					field: 'need_1',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "Need 1",
					formatter: numberformat,
				},{
					field: 'balance_1',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "Balance 1",
					formatter: numberformat,
				},{
					field: 'need_2',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "Need 2",
					formatter: numberformat,
				},{
					field: 'balance_2',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "Balance 2",
					formatter: numberformat,
				},{
					field: 'need_3',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "Need 3",
					formatter: numberformat,
				},{
					field: 'balance_3',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "Balance 3",
					formatter: numberformat,
				},{
					field: 'need_4',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "Need 4",
					formatter: numberformat,
				},{
					field: 'balance_4',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "Balance 4",
					formatter: numberformat,
				},{
					field: 'need_5',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "Need 5",
					formatter: numberformat,
				},{
					field: 'balance_5',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "Balance 5",
					formatter: numberformat,
				},{
					field: 'need_6',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "Need 6",
					formatter: numberformat,
				},{
					field: 'balance_6',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "Balance 6",
					formatter: numberformat,
				}]
            ],
        }).datagrid('enableFilter');
    });
</script>