<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar"></table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 35px;">
	<a href="javascript:void(0)" id="approveacheck" class="easyui-linkbutton" data-options="plain:true" onclick="confirm()"><i class="fa fa-edit"></i> Confirm Data</a>
	<!-- <a href="javascript:void(0)" id="approveacheck" class="easyui-linkbutton" data-options="plain:true" onclick="approve()"><i class="fa fa-check"></i> Approve</a> -->
	<a href="javascript:void(0)" id="approveacheck" class="easyui-linkbutton" data-options="plain:true" onclick="disapprove()"><i class="fa fa-times"></i> Disapprove</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="export_excel()"><i class="fa fa-file"></i> Export Excel</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="reload()"><i class="fa fa-refresh"></i> Reload</a>
</div>

<div id="dlg_insert" class="easyui-dialog" title="Confirm Data" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
			<div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">ID</span>
                <input style="width:60%;" name="id" id="id" readonly class="easyui-textbox">
            </div>
			<div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Table Name</span>
                <input style="width:60%;" name="tablename" id="tablename" value="<?= $table ?>" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Part No</span>
                <input style="width:60%;" name="item_no" id="item_no" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Part Name</span>
                <input style="width:60%;" name="item_name" id="item_name" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Free Stock</span>
                <input style="width:30%;" name="freestock" id="freestock" readonly class="easyui-numberbox">
            </div>
			<div class="fitem">
                <span style="width:35%; display:inline-block;">Need</span>
                <input style="width:30%;" name="qtyperassy" id="qtyperassy" readonly class="easyui-numberbox">
            </div>
			<div class="fitem">
                <span style="width:35%; display:inline-block;">Balance</span>
                <input style="width:30%;" name="balance" id="balance" readonly class="easyui-numberbox">
            </div>
			<div class="fitem">
                <span style="width:35%; display:inline-block;">OTW</span>
                <input style="width:30%;" name="otw" id="otw" required class="easyui-numberbox">
            </div>
			<div class="fitem">
				<span style="width:35%; display:inline-block;">ETA</span>
				<input style="width:60%;" name="eta" id="eta" class="easyui-datebox" required data-options="formatter:myformatter,parser:myparser, editable:false">
			</div>
        </fieldset>
    </form>
</div>

<script>
    //RELOAD
    function reload() {
        window.location.reload();
    }

    function export_excel() {
		$('#dg').datagrid('toExcel', "approval_<?= $table ?>.xls");
	}

	function confirm() {
		var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('approvals_custom/approveSimulationMps') ?>';
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
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

	function numberformatPrice(value, row){
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2
        });

        return "<b>" + formatter.format(value) + "</b>";
    }

	//Format Datepicker
    function myformatter(date) {
        var y = date.getFullYear();
        var m = date.getMonth() + 1;
        var d = date.getDate();
        return y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d);
    }

    //Format Datepicker
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

    $(function() {
		//Save Data
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_insert').form('submit', {
                        url: url_save,
                        onSubmit: function() {
                            return $(this).form('validate');
                        },
                        success: function(result) {
                            var result = eval('(' + result + ')');

                            if (result.theme == "success") {
                                toastr.success(result.message, result.title);
                            } else {
                                toastr.error(result.message, result.title);
                            }

                            $('#dlg_insert').dialog('close');
                            $('#dg').datagrid('reload');
                        }
                    });
                }
            }]
        });

        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('approvals/approvalSimulationMps/') ?>' + "<?= base64_encode($approved_to) ?>" + "/" + "<?= base64_encode($created_by) ?>" + "/" + "<?= base64_encode($approved_by) ?>",
            pagination: true,
            singleSelect: false,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
			pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            columns: [
				[{
					field: 'ck',
					checkbox: "true",
				},{
					field: 'p_month',
					width: 80,
					halign: 'center',
					title: "Month",
				},{
					field: 'p_year',
					width: 80,
					halign: 'center',
					title: "Year",
				},{
					field: 'revision',
					width: 80,
					align: 'center',
					title: "Rev",
				},{
					field: 'cutoff',
					width: 100,
					halign: 'center',
					title: "Cut Off",
				}, {
					field: 'item_no',
					width: 200,
					halign: 'center',
					title: "Part No",
				}, {
					field: 'item_name',
					width: 120,
					halign: 'center',
					title: "Part Name",
				}, {
					field: 'product_family',
					width: 100,
					halign: 'center',
					title: "Product Family",
				}, {
					field: 'freestock',
					width: 120,
					halign: 'center',
					align: 'right',
					title: "Free Stock",
					formatter: numberformat,
				}, {
					field: 'qtyperassy',
					width: 120,
					halign: 'center',
					align: 'right',
					title: "Need",
					formatter: numberformat,
				}, {
					field: 'balance',
					width: 120,
					halign: 'center',
					align: 'right',
					title: "Balance",
					formatter: numberformat,
				}]
			],
        }).datagrid('enableFilter');
    });
</script>