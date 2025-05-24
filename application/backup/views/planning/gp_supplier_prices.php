<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'maker_part',width:200,halign:'center'">Part No</th>
            <th data-options="field:'product_family_name',width:100,halign:'center'">Product Family</th>
            <th data-options="field:'supplier_code',width:100,align:'center'">Vendor Code</th>
            <th data-options="field:'supplier_name',width:200,halign:'center'">Vendor Name</th>
            <th data-options="field:'maker',width:150,halign:'center'">Maker</th>
            <th data-options="field:'currency',width:80,align:'center'">Currency</th>
            <th data-options="field:'moq',width:80,halign:'center',align:'right', formatter:numberformatDefaultIdr">MOQ</th>
            <th data-options="field:'mpq',width:80,halign:'center',align:'right', formatter:numberformatDefaultIdr">MPQ</th>
            <th data-options="field:'shipping_lead_time_days',width:80,halign:'center',align:'right', formatter:numberformatDefaultIdr">Leadtime</th>
            <!-- <th data-options="field:'buying_price',width:80,halign:'center',align:'right', formatter:numberformatDefault">Buy Price</th>
            <th data-options="field:'bod_policy',width:80,halign:'center',align:'right', formatter:numberformatDefault">BOD Policy</th>
            <th data-options="field:'lcid',width:80,halign:'center',align:'right', formatter:numberformatDefault">LC + ID</th> -->
            <th data-options="field:'gpnol',width:120,halign:'center',align:'right', formatter:numberformatDefault">Price</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 35px;">
    <?= $button ?>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('planning/gp_supplier_prices/print') ?>" style="width: 100%;" hidden></iframe>

<script>

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        window.location.assign('<?= base_url('planning/gp_supplier_prices/print/excel') ?>');
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        $('#dg').datagrid({
            url: '<?= base_url('planning/gp_supplier_prices/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit:true,
            pageList: [10, 20, 50, 100, 500, 1000],
            pageSize: 20,
        }).datagrid('enableFilter');

    });

    function numberformatDefault(value, row) {
        if (value != null) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 4
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function numberformatDefaultIdr(value, row){
        if (value != null) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 0
            });
            return "<b>" + formatter.format(value) + "</b>";
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