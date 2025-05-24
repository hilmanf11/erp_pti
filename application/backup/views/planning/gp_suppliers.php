<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'serial_code',width:80,halign:'center'">ID</th>
            <th data-options="field:'code',width:80,halign:'center'">Code</th>
            <th data-options="field:'name',width:200,halign:'center'">Name</th>
            <th data-options="field:'ship_address',width:300,halign:'center'">Ship Address</th>
            <th data-options="field:'bill_address',width:300,halign:'center'">Bill Address</th>
            <th data-options="field:'country_origin',width:100,halign:'center'">Origin</th>
            <th data-options="field:'supplier_type',width:80,halign:'center'">Type</th>
            <th data-options="field:'shipping_lead_time_days',width:80,halign:'center'">Leadtime</th>
            <th data-options="field:'payment_term_days',width:80,halign:'center'">Payment<br>Term</th>
            <th data-options="field:'vat_status',width:80,halign:'center'">Status</th>
            <th data-options="field:'tax_rates',width:80,halign:'center'">Tax Rate</th>
            <th data-options="field:'email',width:150,halign:'center'">Email</th>
            <th data-options="field:'contact_person',width:150,halign:'center'">Contact Person</th>
            <th data-options="field:'default_order',width:100,halign:'center'">Default Order</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 35px;">
    <?= $button ?>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('planning/gp_suppliers/print') ?>" style="width: 100%;" hidden></iframe>

<script>

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        window.location.assign('<?= base_url('planning/gp_suppliers/print/excel') ?>');
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        $('#dg').datagrid({
            url: '<?= base_url('planning/gp_suppliers/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit:true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        }).datagrid('enableFilter');

    });

    function cellStyler(value, row, index) {
        if (value == "EMPTY") {
            return 'background: #FF5F5F; color:white;';
        } else if(value == "GENERATE"){
            return 'background: #3F7BFF; color:white;';
        } else if(value == "UPLOAD"){
            return 'background: #53D636; color:white;';
        }
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
</script>