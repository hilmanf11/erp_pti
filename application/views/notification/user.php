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
            url: '<?= base_url('notification/notificationData/user/') ?>' + "<?= base64_encode($user) ?>" + "/" + "<?= base64_encode($name) ?>",
            pagination: false,
            singleSelect: false,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            // pageList: [20, 50, 100, 500, 1000],
            // pageSize: 20,
            columns: [
                [{
                    field: 'ck',
					checkbox: true
                },{
                    field: 'number',
                    width: 120,
                    halign: 'center',
                    title: "User ID",
                }, {
                    field: 'name',
                    width: 250,
                    halign: 'center',
                    title: "Name"
                }, {
                    field: 'username',
                    width: 100,
                    halign: 'center',
                    title: "Username",
                }, {
                    field: 'email',
                    width: 200,
                    halign: 'center',
                    title: "Email",
                }, {
                    field: 'phone',
                    width: 100,
                    halign: 'center',
                    title: "Phone",
                }, {
                    field: 'position',
                    width: 120,
                    halign: 'center',
                    title: "Position",
                }, {
                    field: 'disapprove',
                    width: 200,
                    halign: 'center',
                    title: "Reason",
                }, {
                    field: 'created_by',
                    width: 120,
                    halign: 'center',
                    title: "Created Date",
                }, {
                    field: 'created_date',
                    width: 150,
                    halign: 'center',
                    title: "Created Date",
                }]
            ],
        }).datagrid('enableFilter');
    });
</script>