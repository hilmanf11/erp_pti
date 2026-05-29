<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar"></table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 35px;">
	<a href="javascript:void(0)" id="approveacheck" class="easyui-linkbutton" data-options="plain:true" onclick="approve()"><i class="fa fa-check"></i> Approve</a>
	<a href="javascript:void(0)" id="approveacheck" class="easyui-linkbutton" data-options="plain:true" onclick="disapprove()"><i class="fa fa-times"></i> Disapprove</a>
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

	function approve() {
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

	function disapprove() {
		var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            Swal.fire({
                title: 'Dispprove Data',
                text: "Are you sure? You want to Dispprove this data!",
                icon: 'warning',
                showCancelButton: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
                confirmButtonText: 'Yes, Dispprove it!'
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
								url: '<?= base_url('approvals/disapprove') ?>',
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
											'Dispprove Completed',
											'Dispprove Data has been completed, You cannot restore data that has been Dispproved',
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
				}else{
					Swal.fire(
						'Cannot Process',
						'Please Input your reason to Process Disapprove',
						'error'
					);
				}
            });
        } else {
            toastr.info("Please select one of the data in the table first");
        }
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

    function styleStatusPrice(value, row, index) {
    	var style = "background-color: yellow;";
    	if (parseFloat(row.balance_price) != 0) {
    		if (parseFloat(row.balance_price) > 0) { style = 'background-color: red; color: white;'; }
    		else { style = 'background-color: #34eb46;'; }
    	}
    	return style;
    }

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('approvals/approvalSupplierItemPrices/') ?>' + "<?= base64_encode($approved_to) ?>" + "/" + "<?= base64_encode($created_by) ?>" + "/" + "<?= base64_encode($approved_by) ?>",
            pagination: false,
            singleSelect: false,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            columns: [[
            	{
					field: 'ck',
					checkbox: "true",
					rowspan: 2
				}, {
					field: 'quotation_number',
					halign: 'center',
					title: "Quotation No",
					rowspan: 2
				}, {
					field: 'quotation_date',
					halign: 'center',
					title: "Quotation Date",
					rowspan: 2
				}, {
					field: 'item_id',
					halign: 'center',
					title: "Item No",
					formatter: Itemid,
					rowspan: 2
				}, {
					field: 'supplier_name',
					halign: 'center',
					title: "Vendor",
					rowspan: 2
				}, {
					field: 'maker_name',
					halign: 'center',
					title: "Maker",
					rowspan: 2
				}, {
					field: 'currency',
					halign: 'center',
					title: "Currency",
					rowspan: 2
				}, {
					field: 'price_last',
					halign: 'center',
					title: "Last Price",
					formatter: numberformat,
					rowspan: 2
				}, {
					field: 'price',
					halign: 'center',
					title: "New Price",
					formatter: numberformat,
					rowspan: 2
				}, {
					field: 'balance_price',
					halign: 'center',
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
					field: 'start_date',
					align: 'right',
					halign: 'center',
					title: "Start Date",
					rowspan: 2
				}, {
					halign: 'center',
					title: "Valid Date",
					colspan: 2
				}, {
					field: 'remarks',
					halign: 'center',
					title: "Remarks",
					rowspan: 2
				}
			],[
				{
					field: 'valid_date_bf',
					align: 'right',
					halign: 'center',
					title: "Before"
				}, {
					field: 'valid_date',
					align: 'right',
					halign: 'center',
					title: "After"
				}
			]],
        }).datagrid('enableFilter');
    });
</script>