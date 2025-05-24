<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'created_by',width:180">Created By</th>
            <th data-options="field:'created_date',width:180">Created Date</th>
            <th data-options="field:'ip_address',width:100">Ip Address</th>
            <th data-options="field:'action',width:100">Action</th>
            <th data-options="field:'menu',width:180">Module</th>
            <th data-options="field:'description',width:300">Description</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 35px;">
    <?= $button ?>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('admin/log/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    $(function() {
        $('#dg').datagrid({
            url: '<?= base_url('admin/log/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        }).datagrid('enableFilter');
    });

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        window.location.assign('<?= base_url('admin/log/print/excel') ?>');
    }

    function reload() {
        window.location.reload();
    }
</script>