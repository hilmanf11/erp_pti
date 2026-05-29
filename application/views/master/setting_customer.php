<table id="dg" class="easyui-datagrid" style="width:100%;"></table>
<div id="toolbar"><?= $button ?></div>

<div id="dlg_form" class="easyui-dialog" style="width: 500px; height: 550px;">
    <table id="dg-customer" class="easyui-datagrid" style="width: 95%;"></table>
    <div id="toolbar_customer">
        <fieldset style="width: 100%; border: 0px; margin: 10px;">
            <div class="fitem">
                <span style="display:inline-block; margin-right: 10px;">User</span>
                <input style="width: 250px;" id="user_f" class="easyui-combogrid">
            </div>
        </fieldset>
    </div>
</div>

<!-- Upload -->
<div id="dlg_upload" class="easyui-dialog" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_upload" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">File Upload</span>
                <input name="file_upload" style="width: 60%;" required="" accept=".xlsx" id="file_excel" class="easyui-filebox">
            </div>
        </fieldset>
    </form>
    <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
    <div id="p_remarks" class="easyui-panel" style="width:100%; height:200px; padding:10px; margin-top: 10px;">
        <p>History Upload</p>
        <ul id="remarks"></ul>
    </div>
</div>

<!-- PDF -->
<iframe id="printout" src="" style="width: 100%;" hidden></iframe>

<script>
    var add_form = true;
    var dataUpload = [];
    $(function() {
        $('#dg').datagrid({
            url: '<?= base_url('master/setting_customer/datatables') ?>',
            toolbar: '#toolbar',
            remoteFilter: true,
            pagination: true,
            rownumbers: true,
            fit: true,
            columns: [[
                {field: 'name', title: 'User Name', width: '20%', halign: 'center'},
                {field: 'customer', title: 'Customer', width: '78%', halign: 'center'}
            ]],
            onLoadSuccess: function () {
                $("#printout").attr('src', "<?= base_url('master/setting_customer/datatables/print') ?>");
            }
        }).datagrid('enableFilter');
        $('#dg-customer').datagrid({
            toolbar: '#toolbar_customer',
            checkOnSelect: true,
            selectOnCheck: true,
            remoteFilter: true,
            pagination: false,
            rownumbers: true,
            fit: true,
            columns: [[
                {field: 'ck', checkbox: true},
                {field: 'name', title: 'Customer Name', width: '97%', halign: 'center'}
            ]],
            onLoadSuccess: function (data) {
                data.rows.forEach(function (value, key) {
                    if ('checked' in value)
                        { $('#dg-customer').datagrid('selectRow', key); }
                });
            }
        });

        $('#dlg_form').dialog({
            modal: true,
            closed: true,
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    var user = $('#user_f').combogrid('getValue');
                    var customers = $('#dg-customer').datagrid('getChecked');

                    if (user == '') { toastr.warning("Please select user!", "Information"); }
                    else if (customers.length == 0)
                        { toastr.warning("Please select one of the data in the table first!", "Information"); }
                    else {
                        var data = [];
                        customers.forEach(function (value, key) { data.push(value.number); });

                        getprocess(
                            (add_form) ? 'create' : 'update',
                            {user: btoa(user), customer: btoa(data.join(','))}
                        );
                    }
                }
            }]
        });

        $('#user_f').combogrid({
            url: "<?= base_url('master/setting_customer/users') ?>",
            idField: 'number',
            textField: 'name',
            panelWidth: '500',
            remoteFilter: true,
            pagination: false,
            rownumbers: true,
            editable: false,
            columns: [[
                {field: 'number', title: 'User ID', halign: 'center'},
                {field: 'name', title: 'User Name', halign: 'center'}
            ]],
            onSelect: function (index,row) {
                $('#dg-customer').datagrid(
                    'reload',
                    "<?= base_url('master/setting_customer/customer/') ?>"+btoa(row.number)
                );
            }
        }).combogrid('grid').datagrid('enableFilter');

        $('#dlg_upload').dialog({
            title: 'Upload Data',
            closed: true,
            modal: true,
            buttons: [{
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('master/setting_customer/uploadTemplate') ?>',
                        onSubmit: function() {
                            if ($(this).form('validate') == false) {
                                return $(this).form('validate');
                            } else {
                                $.messager.progress({
                                    title: 'Please Wait',
                                    msg: 'Importing Excel to Database'
                                });
                            }
                        },
                        success: function(result) {
                            var json = JSON.parse(result);

                            $.messager.progress('close');
                            if (json.stat != 'ok') { toastr.warning(json.stat, 'Warning'); }
                            else {
                                dataUpload = json.data;
                                $("#p_remarks").html('');
                                uploadProcess();
                            }
                        }
                    });
                }
            }]
        });
    });

    function add() {
        add_form = true;
        $('#dg-customer').datagrid('loadData', []);
        $('#user_f').combogrid('clear').combogrid('enable')
                    .combogrid('grid').datagrid('reload');
        $('#dlg_form').dialog('setTitle', 'Add New').dialog('open');
    }

    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            add_form = false;

            $('#dg-customer').datagrid(
                'reload',
                "<?= base_url('master/setting_customer/customer/') ?>"+btoa(row.user)
            );
            $('#user_f').combogrid('setValue', row.user).combogrid('setText', row.name).combogrid('disable')
                        .combogrid('grid').datagrid('reload');
            $('#dlg_form').dialog('setTitle', 'Update Data').dialog('open');
        } else { toastr.warning("Please select one of the data in the table first!", "Information"); }
    }

    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    var data = [];
                    rows.forEach(function (value, key) { data.push(value.user); });
                    getprocess('delete', {user: btoa(data.join(','))});
                }
            });
        } else { toastr.warning("Please select one of the data in the table first!", "Information"); }
    }

    function upload() { $('#dlg_upload').dialog('open'); }

    function uploadProcess(index = 0) { getprocess('templateProcess', dataUpload[index]); }

    function download_excel() { window.location.assign('<?= base_url('master/setting_customer/template') ?>'); }

    function pdf() { $("#printout").get(0).contentWindow.print(); }

    function excel() { window.location.assign('<?= base_url('master/setting_customer/excel') ?>'); }

    function reload() { window.location.reload(); }

    function getprocess(act, data) {
        $('#dg').datagrid('loading');
        $.ajax({
            url: "<?= base_url('master/setting_customer/'); ?>"+act,
            data: data,
            async: false,
            method: 'POST',
            dataType: 'json',
            success: function (result) {
                if (['create', 'update', 'delete'].includes(act)) {
                    var txt = (['create', 'update'].includes(act)) ? 'Save' : 'Delete';
                    if (result.stat != '') { toastr.error('Failed '+txt+' Data!', 'ERROR'); }
                    else {
                        toastr.success('Success', txt+' Data');
                        $('#dlg_form').dialog('close');
                        $('#dg').datagrid('reload');
                    }
                } else if (act == 'templateProcess') {
                    var next = dataUpload.indexOf(data) + 1;
                    var total = dataUpload.length;

                    $("#p_remarks").append(
                        ((next == 1) ? "" : "<br>") +
                        "<b style='color: " + ((result.theme == "success") ? 'green' : 'red') + ";'>" + result.title + "</b> | " + result.message
                    );
                    $('#p_upload').progressbar('setValue', Math.floor((next / total) * 100));
                    $('#p_finish').html(total);
                    $('#p_start').html(next);

                    if (next <= (total - 1)) { uploadProcess(next); }
                    else { $('#dg').datagrid('reload'); }
                }
                $('#dg').datagrid('loaded');
            }
        });
    }
</script>