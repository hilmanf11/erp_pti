<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'number',width:150">Number ID</th>
            <th data-options="field:'name',width:200">Name</th>
            <th data-options="field:'username',width:150">Username</th>
            <th data-options="field:'email',width:200">Email</th>
            <th data-options="field:'phone',width:150">Phone</th>
            <th data-options="field:'position',width:200">Position</th>
            <th data-options="field:'role',width:100">Role</th>
            <th data-options="field:'avatar',width:200">File Foto</th>
            <th data-options="field:'actived_is',width:150, styler:cellStyler, formatter:cellFormatter">Status</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 35px;">
    <?= $button ?>
</div>

<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">User ID</span>
                <input style="width:60%;" name="number" id="number" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Fullname</span>
                <input style="width:60%;" name="name" id="name" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Username</span>
                <input style="width:60%;" name="username" id="username" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Password</span>
                <input style="width:60%;" name="password" id="password" required="" class="easyui-passwordbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Position</span>
                <input style="width:60%;" name="position" id="position" required="true" data-options="prompt:'Staff Production'" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Role</span>
                <select style="width:60%;" name="role" id="role" class="easyui-combobox">
                    <option value="ADMIN">ADMIN</option>
                    <option value="PROCUREMENT">PROCUREMENT</option>
                    <option value="MARKETING">MARKETING</option>
                    <option value="ENGINEERING">ENGINEERING</option>
                    <option value="PURCHASING">PURCHASING</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Email</span>
                <input style="width:60%;" name="email" id="email" required="true" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Phone</span>
                <input style="width:60%;" name="phone" id="phone" data-options="buttonText:'+62',buttonAlign:'left'" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Foto Profile</span>
                <input style="width:60%;" name="avatar" id="avatar" class="easyui-filebox" accept=".jpg, .png, .jpeg">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Status</span>
                <select style="width:60%;" name="actived_is" required="" panelHeight="auto" class="easyui-combobox">
                    <option value="0">Active</option>
                    <option value="1">Not Active</option>
                </select>
            </div>
        </fieldset>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('admin/user/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        $('#number').textbox('enable');
        $('#username').textbox('enable');
        $('#password').textbox('enable');
        url_save = '<?= base_url('admin/user/create') ?>';
        $('#frm_insert').form('clear');
    }

    //Edit Data
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            $('#number').textbox('disable');
            $('#username').textbox('disable');
            $('#password').textbox('disable');
            url_save = '<?= base_url('admin/user/update') ?>?id=' + btoa(row.id);
        } else {
            $.messager.alert('Information', 'Please select one of the data in the table first!', 'info');
        }
    }

    //Delete Data
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('admin/user/delete') ?>',
                            data: {
                                id: row.id
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

    $(function() {
        $('#dg').datagrid({
            url: '<?= base_url('admin/user/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        }).datagrid('enableFilter');

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
    });

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        window.location.assign('<?= base_url('admin/user/print/excel') ?>');
    }

    function reload() {
        window.location.reload();
    }

    function cellStyler(value, row, index) {
        if (value == 0) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    function cellFormatter(val) {
        if (val == 0) {
            return 'Active';
        } else {
            return 'Not Active';
        }
    };
</script>