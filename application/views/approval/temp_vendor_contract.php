<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar"></table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar">
    <!-- <a href="javascript:void(0)" id="approveacheck" class="easyui-linkbutton" data-options="plain:true" onclick="approveCheck()"><i class="fa fa-check"></i> Approve from Check</a> -->
    <a href="javascript:void(0)" id="approveall" class="easyui-linkbutton" data-options="plain:true" onclick="approveall()"><i class="fa fa-check"></i> Approve ALL</a>
    <a href="javascript:void(0)" id="disapproveall" class="easyui-linkbutton" data-options="plain:true" onclick="disapproveall()"><i class="fa fa-times"></i> Disapprove ALL</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="export_excel()"><i class="fa fa-file"></i> Export Excel</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="reload()"><i class="fa fa-refresh"></i> Reload</a>
</div>

<script>
    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('approvals/approvalTempVendorContract/') ?>' + "<?= base64_encode($approved_to) ?>" + "/" + "<?= base64_encode($created_by) ?>" + "/" + "<?= base64_encode($approved_by) ?>",
            pagination: true,
            singleSelect: false,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            frozenColumns: [[
            	{
                    field: 'action',
                    align: 'center',
                    title: "Approved",
                    formatter: function(val, row) {
                        if (val != "-") {
                            var approve = "approve('" + row.id + "','" + "<?= $table ?>" + "')";
                            var disapprove = "disapprove('" + row.id + "','" + "<?= $table ?>" + "')";
                            var a = '<a class="btn btn-success w-50" style="pointer-events: visible; opacity:1;" onclick="' + approve + '"><i class="fa fa-check"></i></a>';
                            var b = '<a class="btn btn-danger w-50" style="pointer-events: visible; opacity:1;" onclick="' + disapprove + '"><i class="fa fa-times"></i></a>';
                            return a + " " + b;
                        }
                    }
                }
            ]],
            columns: [
                [{
				// 	field: 'ck',
				// 	checkbox: "true",
				// 	rowspan: 2
				// }, {
					field: 'quotation_number',
					halign: 'center',
					title: "Quotation No",
					rowspan: 2
				}, {
					field: 'quotation_date',
					halign: 'center',
					align: 'center',
					title: "Quotation Date",
					formatter: function (value, row, index) { return dateformatter(new Date(value)); },
					rowspan: 2
				}, {
					field: 'plant',
					halign: 'center',
					align: 'center',
					title: "Plant",
					rowspan: 2
				}, {
					field: 'supplier_name',
					halign: 'center',
					title: "Vendor",
					rowspan: 2
				}, {
					field: 'item_id',
					halign: 'center',
					title: "Item No",
					formatter: Itemid,
					rowspan: 2
				}, {
					field: 'item_supplier',
					halign: 'center',
					title: "Item Supplier",
					rowspan: 2
				}, {
					field: 'maker_name',
					halign: 'center',
					title: "Maker",
					rowspan: 2
				}, {
					field: 'uom_name',
					halign: 'center',
					title: "UOM",
					rowspan: 2
				}, {
					halign: 'center',
					title: "MPQ",
					colspan: 2
				}, {
					halign: 'center',
					title: "MOQ",
					colspan: 2
				}, {
					halign: 'center',
					title: "Leadtime",
					colspan: 2
				}, {
					field: 'currency',
					halign: 'center',
					align: 'center',
					title: "Currency",
					rowspan: 2
				}, {
					halign: 'center',
					title: "Last Price",
					colspan: 2
				}, {
					halign: 'center',
					title: "New Price",
					colspan: 2
				}, {
					field: 'balance_price',
					halign: 'center',
					align: 'right',
					title: "Balance Price",
					formatter: numberformat,
					rowspan: 2
				}, {
					field: 'status_price',
					align: 'center',
					halign: 'center',
					title: "Price Status",
					styler: styleStatusPrice,
					rowspan: 2
				}, {
					field: 'remarks',
					halign: 'center',
					title: "Remarks",
					rowspan: 2
				}],[{
					field: 'mpq_bf',
					halign: 'center',
					align: 'right',
					title: "Before"
				}, {
					field: 'mpq',
					halign: 'center',
					align: 'right',
					title: "After"
				}, {
					field: 'moq_bf',
					halign: 'center',
					align: 'right',
					title: "Before"
				}, {
					field: 'moq',
					halign: 'center',
					align: 'right',
					title: "After"
				}, {
					field: 'leadtime_bf',
					halign: 'center',
					align: 'right',
					title: "Before"
				}, {
					field: 'leadtime',
					halign: 'center',
					align: 'right',
					title: "After"
				}, {
					field: 'price_date',
					halign: 'center',
					align: 'center',
					title: "Valid Date"
				}, {
					field: 'price_last',
					halign: 'center',
					align: 'right',
					title: "Price",
					formatter: numberformat
				}, {
					field: 'valid_date',
					halign: 'center',
					align: 'center',
					title: "Valid Date"
				}, {
					field: 'price',
					halign: 'center',
					align: 'right',
					title: "Price",
					formatter: numberformat
				}]
            ],
        }).datagrid('enableFilter', [{field: 'action', type: 'label'}]);
    });

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
    	var filters = {};
    	$('#dg').datagrid('options').filterRules.forEach(function (obj, index) { filters[obj.field] = obj.value; });
		$.messager.prompt({
			title: 'Warning',
			msg: 'Are you sure you want to accept all data? Please enter password.\npassword : "dataapproved"',
			ok: 'Submit',
			fn: function(r) {
				if (r != undefined) {
					if (r != 'dataapproved') { toastr.warning("The password you entered is incorrect!", "Warning"); }
					else {
						$.post('<?= base_url('approvals/approveall') ?>', {
							table_name: "<?= $table ?>",
							approved_to: "<?= $approved_to ?>",
							created_by: "<?= $created_by ?>",
							approved_by: "<?= $approved_by ?>",
							filters: filters
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
				}
			},
			// onOpen: function() { $(this).children('input.messager-input').attr('placeholder', 'dataapproved'); }
		});
		// $.messager.confirm('Warning', 'Are you sure you want to approve all data?', function(r) {
		// 	if (r) {
		// 		$.post('<?= base_url('approvals/approveall') ?>', {
		// 			table_name: "<?= $table ?>",
		// 			approved_to: "<?= $approved_to ?>",
		// 			created_by: "<?= $created_by ?>",
		// 			approved_by: "<?= $approved_by ?>",
		// 		}, function(result) {
		// 			var result = eval('(' + result + ')');
		// 			if (result.theme == "success") {
		// 				toastr.success(result.message, result.title);
		// 				$("#dg").datagrid("reload");
		// 			} else {
		// 				toastr.error(result.message, result.title);
		// 			}
		// 		});
		// 	}
		// });
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
		$.messager.prompt({
			title: 'Warning',
			msg: 'Are you sure you want to reject this data, include a reason?',
			ok: 'Submit',
			fn: function(r) {
				if (r != undefined) {
					if (r == '') { toastr.warning("Please fill in the reason column!", "Warning"); return false; }
					else {
						$.post('<?= base_url('approvals/disapprove') ?>', {
							id: id,
							tablename: tablename,
							reason: r
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
				}
			}
		});
	}

    function numberformat(value, row) {
		const formatter = new Intl.NumberFormat("en-IN");

		return "<b>" + formatter.format(value) + "</b>";
	}

    function Itemid(value, row) {
		return "'" + value;
	}

	function numberformatPrice(value, row){
        const formatter = new Intl.NumberFormat("en-IN", {
            minimumFractionDigits: 2
        });

        return "<b>" + formatter.format(value) + "</b>";
    }

    function styleStatusPrice(value, row, index) {
    	var style = "background-color: yellow;";
    	if (parseFloat(row.balance_price) != 0) {
    		if (parseFloat(row.balance_price) > 0) { style = 'background-color: red; color: white;'; }
    		else { style = 'background-color: #34eb46;'; }
    	}
    	return style;
    }

    function dateformatter(date) {
        var y = date.getFullYear();
        var m = date.getMonth() + 1;
        var d = date.getDate();
        return y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d);
    }
</script>