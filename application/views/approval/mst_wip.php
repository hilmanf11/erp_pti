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
            url: '<?= base_url('approvals/approvalWip/') ?>' + "<?= base64_encode($approved_to) ?>" + "/" + "<?= base64_encode($created_by) ?>" + "/" + "<?= base64_encode($approved_by) ?>",
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
				}, {
					field: 'p_year',
					width: 80,
					align: 'center',
					title: "Year",
				}, {
					field: 'revision',
					width: 80,
					align: 'center',
					title: "Revision",
				}, {
					field: 'product_no',
					width: 200,
					halign: 'center',
					title: "Product No",
				}, {
					field: 'product_name',
					width: 150,
					halign: 'center',
					title: "Product Name"
				}, {
					field: 'circuit_no',
					width: 80,
					halign: 'center',
					title: "CCT"
				},{
					field: 'pp',
					width: 80,
					halign: 'center',
					align: 'right',
					formatter: numberformat,
					title: "PP",
				},{
					field: 'plant_1',
					width: 80,
					halign: 'center',
					align: 'right',
					formatter: numberformat,
					title: "Plant 1",
				}, {
					field: 'plant_2',
					width: 80,
					halign: 'center',
					align: 'right',
					formatter: numberformat,
					title: "Plant 2",
				}, {
					field: 'plant_3',
					width: 80,
					halign: 'center',
					align: 'right',
					formatter: numberformat,
					title: "Plant 3",
				}, {
					field: 'plant_4',
					width: 80,
					halign: 'center',
					align: 'right',
					formatter: numberformat,
					title: "Plant 4",
				}, {
					field: 'plant_5',
					width: 80,
					halign: 'center',
					align: 'right',
					formatter: numberformat,
					title: "Plant 5",
				}, {
					field: 'plant_6',
					width: 80,
					halign: 'center',
					align: 'right',
					formatter: numberformat,
					title: "Plant 6",
				}, {
					field: 'plant_7',
					width: 80,
					halign: 'center',
					align: 'right',
					formatter: numberformat,
					title: "Plant 7",
				}, {
					field: 'plant_8',
					width: 80,
					halign: 'center',
					align: 'right',
					formatter: numberformat,
					title: "Plant 8",
				}, {
					field: 'plant_9',
					width: 80,
					halign: 'center',
					align: 'right',
					formatter: numberformat,
					title: "Plant 9",
				}, {
					field: 'plant_10',
					width: 80,
					halign: 'center',
					align: 'right',
					formatter: numberformat,
					title: "Plant 10",
				}, {
					field: 'rqa',
					width: 80,
					halign: 'center',
					align: 'right',
					formatter: numberformat,
					title: "RQA",
				},{
					field: 'hav',
					width: 80,
					halign: 'center',
					align: 'right',
					formatter: numberformat,
					title: "HAV",
				},{
					field: 'pgd',
					width: 80,
					halign: 'center',
					align: 'right',
					formatter: numberformat,
					title: "PGD",
				},{
                    field: 'action',
                    width: 80,
                    align: 'center',
                    title: "Action",
                    formatter: function(val, row) {
                        if (val != "-") {
                            var approve = "approve('" + row.id + "','" + "<?= $table ?>" + "')";
                            var disapprove = "disapprove('" + row.id + "','" + "<?= $table ?>" + "')";
                            var a = '<a class="btn btn-success w-50" style="pointer-events: visible; opacity:1;" onclick="' + approve + '"><i class="fa fa-check"></i></a>';
                            var b = '<a class="btn btn-danger w-50" style="pointer-events: visible; opacity:1;" onclick="' + disapprove + '"><i class="fa fa-times"></i></a>';
                            return a + " " + b;
                        }
                    }
                }]
            ],
        }).datagrid('enableFilter');
    });
</script>