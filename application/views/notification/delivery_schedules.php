<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar"></table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 35px;">
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
            url: '<?= base_url('notification/notificationDelivery') ?>',
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
				}, {
					field: 'po_no',
					width: 200,
					halign: 'center',
					title: "PO No",
				},{
					field: 'supplier_name',
					width: 200,
					halign: 'center',
					title: "Vendor Name",
				},{
					field: 'product_family',
					width: 100,
					halign: 'center',
					title: "Product Family",
				},{
					field: 'part_no',
					width: 150,
					halign: 'center',
					title: "Part No",
				}, {
					field: 'part_name',
					width: 100,
					halign: 'center',
					title: "Part Name",
				}, {
					field: 'leadtime',
					width: 80,
					halign: 'center',
					title: "Leadtime",
				}, {
					field: 'mpq',
					width: 80,
					halign: 'center',
					title: "MPQ",
				}, {
					field: 'moq',
					width: 80,
					halign: 'center',
					title: "MOQ",
				}, {
					field: 'qty',
					width: 100,
					halign: 'center',
					title: "Qty",
					formatter: numberformat,
				}, {
					field: 'delivey_qty',
					width: 100,
					halign: 'center',
					title: "Delivery Qty",
					formatter: numberformat,
				},{
					field: 'etd',
					width: 100,
					halign: 'center',
					title: "ETD",
				},{
					field: 'eta',
					width: 100,
					halign: 'center',
					title: "ETA",
				}]
            ],
        }).datagrid('enableFilter');
    });
</script>