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
		$('#dg').datagrid('toExcel', "approval_<?= $table ?>.xls");
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
                    $.messager.prompt('Reason', 'Please input your reason!!', function(message){
                        if (message){
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
                                            tablename: "<?= $table ?>",
                                            message: message
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

    // function Itemid(value, row) {
	// 	return "'" + value;
	// }

	function numberformatPrice(value, row){
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2
        });

        return "<b>" + formatter.format(value) + "</b>";
    }

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('approvals/approvalRequestNpd/') ?>' + "<?= base64_encode($approved_to) ?>" + "/" + "<?= base64_encode($created_by) ?>" + "/" + "<?= base64_encode($approved_by) ?>",
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
				},{
					field: 'request_no',
					width: 120,
					halign: 'center',
					title: "Request No",
				},{
					field: 'request_by',
					width: 150,
					halign: 'center',
					title: "Request By",
				},{
					field: 'request_date',
					width: 120,
					halign: 'center',
					title: "Request Date",
					// formatter: Itemid,
				}, {
					field: 'item_npd_id',
					width: 150,
					halign: 'center',
					title: "Part No",
				}, {
					field: 'item_npd_name',
					width: 150,
					halign: 'center',
					title: "Part Name",
				}, {
					field: 'product_family',
					width: 150,
					halign: 'center',
					title: "Product Family",
				}, {
					field: 'qty',
					width: 80,
					halign: 'center',
					title: "Qty",
				}, {
					field: 'uom',
					width: 80,
					halign: 'center',
					title: "Uom",
				}, {
					field: 'requirement_date',
					width: 120,
					halign: 'center',
					title: "Requirement Date",
				}, {
					field: 'request_reason',
					width: 300,
					halign: 'center',
					title: "Remarks",
				}]
            ],
        }).datagrid('enableFilter');
    });
</script>