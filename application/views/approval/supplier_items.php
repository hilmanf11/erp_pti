<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar"></table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 35px;">
    <a href="javascript:void(0)" id="approveacheck" class="easyui-linkbutton" data-options="plain:true" onclick="approveCheck()"><i class="fa fa-check"></i> Approve from Check</a>
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

	function approveCheck() {
		var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            Swal.fire({
                title: 'Approve Data',
                text: "Are you sure? You want to approve this data!",
                icon: 'warning',
                showCancelButton: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
                confirmButtonText: 'Yes, Approve it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Please Wait...',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                    });

                    requestApprove(rows.length, rows);
                    function requestApprove(total, json, number = 1, value = 0) {
                        if (value < 100) {
                            var row = json[number-1];
                            value = Math.floor((number / total) * 100);

                            $.ajax({
                                method: 'post',
                                url: '<?= base_url('approvals/approve') ?>',
                                data: {
                                    id: row.id,
									tablename: "<?= $table ?>"
                                },
                                success: function(result) {
                                    var result = eval('(' + result + ')');
                                    requestApprove(total, json, number + 1, value);

                                    if (number == total) {
                                        $('#dg').datagrid('reload');
                                        Swal.close();
                                        Swal.fire(
                                            'Approve Completed',
                                            'Approve Data has been completed, You cannot restore data that has been approved',
                                            'success'
                                        );
                                    }
                                },
                                error: function(jqXHR, textStatus, errorThrown) {
                                    toastr.error(jqXHR.statusText);
                                },
                            });
                        }
                    }
                }
            });
        } else {
            toastr.info("Please select one of the data in the table first");
        }
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

    function Itemid(value, row) {
		return "'" + value;
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
            url: '<?= base_url('approvals/approvalSupplierItems/') ?>' + "<?= base64_encode($approved_to) ?>" + "/" + "<?= base64_encode($created_by) ?>" + "/" + "<?= base64_encode($approved_by) ?>",
            pagination: false,
            singleSelect: false,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            columns: [
                [{
					field: 'ck',
					checkbox: "true",
					rowspan: 2
				},{
					field: 'plant',
					width: 80,
					halign: 'center',
					title: "Plant",
					rowspan: 2
				},{
					field: 'supplier_name',
					width: 250,
					halign: 'center',
					title: "Vendor",
					rowspan: 2
				},{
					field: 'item_id',
					width: 200,
					halign: 'center',
					title: "Item No",
					formatter: Itemid,
					rowspan: 2
				},{
					field: 'item_supplier',
					width: 150,
					halign: 'center',
					title: "Item Supplier",
					rowspan: 2
				},{
					field: 'maker_name',
					width: 150,
					halign: 'center',
					title: "Maker",
					rowspan: 2
				},{
					halign: 'center',
					title: "MPQ",
					colspan: 2
				},{
					halign: 'center',
					title: "MOQ",
					colspan: 2
				},{
					halign: 'center',
					title: "Leadtime",
					colspan: 2
				},{
					field: 'share_order',
					width: 80,
					align: 'right',
					halign: 'center',
					title: "Share Order",
					rowspan: 2
				},{
					field: 'remarks',
					width: 200,
					halign: 'center',
					title: "Remarks",
					rowspan: 2
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
					field: 'mpq_bf',
					width: 80,
					halign: 'center',
					title: "Before"
				},{
					field: 'mpq',
					width: 80,
					halign: 'center',
					title: "After"
				},{
					field: 'moq_bf',
					width: 80,
					halign: 'center',
					title: "Before"
				},{
					field: 'moq',
					width: 80,
					halign: 'center',
					title: "After"
				},{
					field: 'leadtime_bf',
					width: 80,
					halign: 'center',
					title: "Before"
				},{
					field: 'leadtime',
					width: 80,
					halign: 'center',
					title: "After"
				}]
            ],
        }).datagrid('enableFilter');
    });
</script>