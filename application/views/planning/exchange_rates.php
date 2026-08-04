<table id="dg" class="easyui-datagrid" style="width:100%;"></table>

<div id="toolbar">
    <?= $button ?>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="viewBi()"><i class="fa fa-info"></i> Kurs</a>
</div>

<div id="dlg_form" class="easyui-dialog">
    <table style="margin: 3px;">
        <tr>
            <td>Start Date</td>
            <td rowspan="7">&nbsp;</td>
            <td><input id="start_date" class="easyui-datebox"></td>
        </tr>
        <tr>
            <td>Ending Date</td>
            <td><input id="end_date" class="easyui-datebox"></td>
        </tr>
        <tr>
            <td>Currency From</td>
            <td><input id="currency" class="easyui-combobox"></td>
        </tr>
        <tr>
            <td>Currency To</td>
            <td><input id="currency_to" class="easyui-textbox"></td>
        </tr>
        <tr>
            <td>Selling</td>
            <td><input id="selling" class="easyui-numberbox"></td>
        </tr>
        <tr>
            <td>Buying</td>
            <td><input id="buying" class="easyui-numberbox"></td>
        </tr>
        <tr>
            <td>Middle</td>
            <td><input id="middle" class="easyui-numberbox"></td>
        </tr>
    </table>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('planning/exchange_rates/datatables/print') ?>" hidden></iframe>

<script>
    var act_save = '';
    $(function() {
        var dg = $('#dg');
        dg.datagrid({
            toolbar: '#toolbar',
            checkOnSelect: true,
            selectOnCheck: true,
            pagination: true,
            rownumbers: true,
            fit: true,
            pageSize: 20,
            pageList: [20, 50, 100, 500, 1000],
            columns: [[
                {field: 'ck', checkbox: true, rowspan: 2},
                {field: 'start_date', title: 'Start Date', halign: 'center', align: 'center', rowspan: 2, width: '7%'},
                {field: 'end_date', title: 'Ending Date', halign: 'center', align: 'center', rowspan: 2, width: '7%'},
                {field: 'currency', title: 'Currency From', halign: 'center', align: 'center', rowspan: 2, width: '7%'},
                {field: 'currency_to', title: 'Currency To', halign: 'center', align: 'center', rowspan: 2, width: '7%'},
                {field: 'selling', title: 'Selling', halign: 'center', align: 'right', rowspan: 2, width: '14%', styler: styleMoney, formatter: convertMoney},
                {field: 'buying', title: 'Buying', halign: 'center', align: 'right', rowspan: 2, width: '14%', styler: styleMoney, formatter: convertMoney},
                {field: 'middle', title: 'Middle', halign: 'center', align: 'right', rowspan: 2, width: '14%', styler: styleMoney, formatter: convertMoney},
                {title: 'Created', halign: 'center', colspan: 2},
                {title: 'Updated', halign: 'center', colspan: 2}
            ],[
                {field: 'created_by', title: 'By', halign: 'center', align: 'center', width: 120},
                {field: 'created_date', title: 'Date', halign: 'center', align: 'center', width: 140},
                {field: 'updated_by', title: 'By', halign: 'center', align: 'center', width: 120},
                {field: 'updated_date', title: 'Date', halign: 'center', align: 'center', width: 140}
            ]],
            // onLoadSuccess: function () { $('.easyui-linkbutton').linkbutton(); }
        }).datagrid('enableFilter', [{
            field: 'start_date',
            type: 'datebox',
            options: {
                editable: false,
                parser: dateparser,
                formatter: dateformatter,
                icons: [{
                    iconCls: 'icon-clear',
                    handler: function(e) {
                        $(e.data.target).datebox('clear');
                        dg.datagrid('removeFilterRule', 'start_date').datagrid('doFilter');
                    }
                }],
                onChange: function (newValue, oldValue) { dg.datagrid('addFilterRule', {
                    field: 'start_date',
                    op: 'equal',
                    value: newValue
                }).datagrid('doFilter'); }
            }
        },{
            field: 'end_date',
            type: 'datebox',
            options: {
                editable: false,
                parser: dateparser,
                formatter: dateformatter,
                icons: [{
                    iconCls: 'icon-clear',
                    handler: function(e) {
                        $(e.data.target).datebox('clear');
                        dg.datagrid('removeFilterRule', 'end_date').datagrid('doFilter');
                    }
                }],
                onChange: function (newValue, oldValue) { dg.datagrid('addFilterRule', {
                    field: 'end_date',
                    op: 'equal',
                    value: newValue
                }).datagrid('doFilter'); }
            }
        },{
            field: 'currency',
            type: 'combobox',
            options: {
                editable: false,
                valueField: 't',
                textField: 't',
                data: <?= json_encode($data_currency) ?>,
                icons: [{
                    iconCls: 'icon-clear',
                    handler: function(e) {
                        $(e.data.target).combobox('clear');
                        dg.datagrid('removeFilterRule', 'currency').datagrid('doFilter');
                    }
                }],
                onChange: function (newValue, oldValue) { dg.datagrid('addFilterRule', {
                    field: 'currency',
                    op: 'equal',
                    value: newValue
                }).datagrid('doFilter'); }
            }
        },{field: 'currency_to', type: 'label'}]);

        $('#dlg_form').dialog({
            modal: true,
            closed: true,
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    var start_date = btoa($('#start_date').datebox('getValue'));
                    var end_date = btoa($('#end_date').datebox('getValue'));
                    var currency = btoa($('#currency').combobox('getValue'));
                    var selling = btoa($('#selling').numberbox('getValue'));
                    var buying = btoa($('#buying').numberbox('getValue'));
                    var middle = btoa($('#middle').numberbox('getValue'));

                    if ([start_date, end_date, currency, selling, buying, middle].includes(''))
                        { toastr.warning('Please fill in all fields!', 'Information'); }
                    else {
                        var data = {
                            start_date: start_date,
                            end_date: end_date,
                            currency: currency,
                            selling: selling,
                            buying: buying,
                            middle: middle,
                            act: (act_save == '') ? 'create' : 'update'
                        };

                        if (data.act == 'update') { data.id = act_save; }

                        getprocess(data);
                    }
                }
            }]
        });

        $('#start_date').datebox({
            required: true,
            editable: false,
            parser: dateparser,
            formatter: dateformatter
        });

        $('#end_date').datebox({
            required: true,
            editable: false,
            parser: dateparser,
            formatter: dateformatter
        });

        $('#currency').combobox({
            required: true,
            editable: false,
            valueField: 't',
            textField: 't',
            data: <?= json_encode($data_currency) ?>
        });

        $('#currency_to').textbox({readonly: true, value: 'IDR'});

        $('#selling').numberbox({required: true, precision: 2});

        $('#buying').numberbox({required: true, precision: 2});

        $('#middle').numberbox({required: true, precision: 2});

          $("#selling").numberbox({
            onChange: function(selling) {
                var buying = $("#buying").numberbox('getValue');
                $("#middle").numberbox('setValue', ((parseFloat(buying) + parseFloat(selling)) / 2));
            }
        });

        $("#buying").numberbox({
            onChange: function(buying) {
                var selling = $("#selling").numberbox('getValue');
                $("#middle").numberbox('setValue', ((parseFloat(buying) + parseFloat(selling)) / 2));
            }
        });

        getprocess({act: 'datatables'});
    });

    function styleMoney(value, row, index) { return 'font-weight: bold;'; }

    function convertMoney(value, row, index) { return 'Rp '+value.toLocaleString('id-ID'); }

    function viewBi() { window.open('https://www.bi.go.id/id/statistik/informasi-kurs/transaksi-bi/default.aspx', '_blank'); }

    function add() {
        act_save = '';
        $('#start_date').datebox('clear');
        $('#end_date').datebox('clear');
        $('#currency').combobox('clear');
        $('#selling').numberbox('clear');
        $('#buying').numberbox('clear');
        $('#middle').numberbox('clear');
        $('#dlg_form').dialog('setTitle', 'Add New').dialog('open');
    }

    function update() {
        var row = $('#dg').datagrid('getSelections');
        if (row.length == 0 || row.length > 1) { toastr.warning("Please select one of the data in the table first!", "Information"); }
        else {
            row = row[0];
            act_save = btoa(row.id);

            $('#start_date').datebox('setValue', row.start_date);
            $('#end_date').datebox('setValue', row.end_date);
            $('#currency').combobox('setValue', row.currency);
            $('#selling').numberbox('setValue', row.selling);
            $('#buying').numberbox('setValue', row.buying);
            $('#middle').numberbox('setValue', row.middle);
            $('#dlg_form').dialog('setTitle', 'Update Data').dialog('open');
        }
    }

    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) { getprocess({act: 'delete', data: btoa(rows.map(obj => obj.id))}); }
            });
        } else { toastr.warning("Please select one of the data in the table first!", "Information"); }
    }

    function upload() { $('#dlg_').dialog('open'); }

    function download_excel() { window.location.assign('<?= base_url('') ?>'); }

    function pdf() { $("#printout").get(0).contentWindow.print(); }

    function excel() { window.location.assign('<?= base_url('planning/exchange_rates/excel') ?>'); }

    function reload() { window.location.reload(); }

    function getprocess(data = null) {
        var act = data.act;
        delete data.act;

        $('#dg').datagrid('loading');
        $.ajax({
            url: '<?= base_url('planning/exchange_rates/'); ?>'+act,
            data: data,
            async: false,
            method: 'POST',
            dataType: 'json',
            success: function (result) {
                $('#dg').datagrid('loaded');
                if (act == 'datatables') {
                    $('#dg').datagrid('loadData', result);
                    $('#printout')[0].contentWindow.location.reload(true);
                } else if (['create', 'update', 'delete'].includes(act)) {
                    var title = (act == 'delete') ? 'Delete Data' : 'Save Data';
                    if (result.stat != '') { toastr.error(result.stat, title); }
                    else {
                        toastr.success('Success', title);
                        getprocess({act: 'datatables'});
                        $('#dlg_form').dialog('close');
                    }
                }
            }
        });
    }

    function dateformatter(date) {
        var y = date.getFullYear();
        var m = date.getMonth() + 1;
        var d = date.getDate();
        return y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d);
    }

    function dateparser(s) {
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