<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar"></table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 35px;">
    <a href="javascript:void(0)" id="approveall" class="easyui-linkbutton" data-options="plain:true" onclick="deleteAll()"><i class="fa fa-check"></i> Delete</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="export_excel()"><i class="fa fa-file"></i> Export Excel</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="reload()"><i class="fa fa-refresh"></i> Reload</a>
</div>

<script>
    //RELOAD
    function reload() {
        window.location.reload();
    }

    function export_excel() {
		$('#dg').datagrid('toExcel', "notification_<?= $table ?>.xls");
	}

	function deleteAll() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('notification/delete') ?>',
                            data: {
                                id: row.id_notification
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                toastr.error(jqXHR.statusText);
                                $.messager.alert("Error", jqXHR.statusText, 'error');
                            },
                            complete: function(data) {
                                $('#dg').datagrid('reload');
                            }
                        });
                    }
                }
            });
        } else {
            $.messager.alert('Information', 'Please select one of the data in the table first!', 'info');
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

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('notification/notificationData/purchase_orders/') ?>' + "<?= base64_encode($user) ?>" + "/" + "<?= base64_encode($name) ?>",
            pagination: false,
            singleSelect: false,
            clientPaging: false,
            rownumbers: true,
            fit: true,
            columns: [
				[{
					field: 'ck',
					checkbox: "true",
				},{
					field: 'p_month',
					width: 50,
					halign: 'center',
					title: "Month",
				},{
					field: 'p_year',
					width: 50,
					halign: 'center',
					title: "Year",
				},{
					field: 'revision',
					width: 50,
					align: 'center',
					title: "Rev",
				},{
					field: 'purchase_request_no',
					width: 180,
					halign: 'center',
					title: "PR NO",
				},{
					field: 'purchase_order_no',
					width: 180,
					halign: 'center',
					title: "PO NO",
				},{
					field: 'purchase_order_date',
					width: 100,
					align: 'center',
					title: "PO Date",
				}, {
					field: 'item_id',
					width: 200,
					halign: 'center',
					title: "Product No",
				}, {
					field: 'item_name',
					width: 100,
					halign: 'center',
					title: "Product Name",
				}, {
					field: 'product_family_name',
					width: 150,
					halign: 'center',
					title: "Product Family",
				}, {
					field: 'uom',
					width: 80,
					align: 'center',
					title: "UoM",
				}, {
					field: 'supplier_name',
					width: 250,
					halign: 'center',
					title: "Supplier"
				}, {
					field: 'qty',
					width: 80,
					halign: 'center',
					align: 'right',
					title: "Qty",
					formatter: numberformat,
				}, {
					field: 'currency',
					width: 80,
					align: 'center',
					title: "Currency",
				}, {
					field: 'price',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "Price",
					formatter: numberformat,
				}, {
					field: 'total',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "Total",
					formatter: numberformat,
				}, {
					field: 'eta',
					width: 100,
					align: 'center',
					title: "ETA",
				},{
					field: 'disapprove',
					width: 250,
					halign: 'center',
					title: "Reason",
				}]
			],
        }).datagrid('enableFilter');
    });
</script>