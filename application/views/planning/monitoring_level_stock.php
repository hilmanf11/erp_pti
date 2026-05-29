<table id="dg" class="easyui-datagrid" style="width:100%;"></table>

<div id="toolbar">
    <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px;">
        <legend><b>Form Filter Data</b></legend>
        <div style="width: 30%; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Periode</span>
                <input style="width:30%;" id="periode_month" class="easyui-combobox" required>
                <input style="width:30%;" id="periode_year" class="easyui-combobox" required>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Revision</span>
                <input style="width:60%;" id="revision" class="easyui-combobox" required>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product Family</span>
                <input style="width:60%;" id="prodfam" class="easyui-combobox">
            </div>
        </div>
        <div style="width: 30%; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Status ITO</span>
                <input style="width:60%;" id="status" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Maker Type</span>
                <input style="width:60%;" id="type" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter</a>
            </div>
        </div>
    </fieldset>
    <?= $button ?>
</div>

<div id="dlg_detail" class="easyui-dialog" style="width: 98%; height: 500px;">
    <table id="dg_detail" class="easyui-datagrid" style="width: 100%; height: 100%;"></table>
</div>
<script>
    $(function() {
        $('#dg').datagrid({
            toolbar: '#toolbar',
            singleSelect: true,
            pagination: true,
            rownumbers: true,
            fit: true,
            columns: [[]],
            frozenColumns: [[]]
        });

        $('#dlg_detail').dialog({
            modal: true,
            closed: true,
            title: 'Historical Request Shipment'
        });

        $('#dg_detail').datagrid({
            url: '<?= base_url('planning/monitoring_level_stock/datatablesHistory') ?>',
            remoteFilter: true,
            pagination: true,
            rownumbers: true,
            fit: true,
            columns: [[
                {field: 'po', title: 'PO', halign: 'center'},
                {field: 'vendor', title: 'VENDOR', halign: 'center'},
                {field: 'eta', title: 'ETA PO', halign: 'center', align: 'center'},
                {field: 'qty', title: 'QTY PO', halign: 'center', align: 'right'},
                {field: 'os', title: 'OS PO', halign: 'center', align: 'right'},
                {field: 'requested', title: 'REQUESTED', halign: 'center', align: 'right'},
                {field: 'qty_req', title: 'QTY REQ', halign: 'center', align: 'right'},
                {field: 'balance', title: 'BALANCE', halign: 'center', align: 'right', styler: balanceColor},
                {field: 'shipment', title: 'SHIPMENT TYPE', halign: 'center', align: 'center'},
                {field: 'remark', title: 'REMARK', halign: 'center'},
                {field: 'detail', title: 'DETAIL', halign: 'center'}
            ]],
            // onLoadSuccess: function () { $('.easyui-linkbutton').linkbutton(); }
        }).datagrid('enableFilter');

        $('#periode_month').combobox({
            editable: false,
            textField: 'name',
            valueField: 'number',
            data: <?= json_encode($months) ?>
        });
        $('#periode_year').combobox({
            editable: false,
            textField: 'year',
            valueField: 'year',
            data: <?= json_encode($years) ?>
        });
        $('#revision').combobox({
            editable: false,
            textField: 'name',
            valueField: 'number',
            data: <?= json_encode($revision) ?>
        });
        $('#prodfam').combobox({
            prompt: 'ALL',
            textField: 'name',
            valueField: 'name',
            data: <?= json_encode($prodfam) ?>,
            icons: [{
                iconCls: 'icon-clear',
                handler: function (e) { $(e.data.target).combobox('clear'); }
            }]
        });
        $('#status').combobox({
            prompt: 'ALL',
            editable: false,
            textField: 'stat',
            valueField: 'stat',
            data: <?= json_encode($status) ?>,
            icons: [{
                iconCls: 'icon-clear',
                handler: function (e) { $(e.data.target).combobox('clear'); }
            }]
        });
        $('#type').combobox({
            prompt: 'ALL',
            editable: false,
            textField: 'type',
            valueField: 'type',
            data: <?= json_encode($type) ?>,
            icons: [{
                iconCls: 'icon-clear',
                handler: function (e) { $(e.data.target).combobox('clear'); }
            }]
        });
    });

    function filter() {
        var month = btoa($('#periode_month').combobox('getValue'));
        var year = btoa($('#periode_year').combobox('getValue'));
        var revision = btoa($('#revision').combobox('getValue'));
        var prodfam = btoa($('#prodfam').combobox('getValue'));
        var status = btoa($('#status').combobox('getValue'));
        var type = btoa($('#type').combobox('getValue'));

        if ([month, year, revision].includes())
            { toastr.warning("Please select Periode and Revision!", "Information"); }
        else {
            getprocess('datatables', {
                year: year,
                month: month,
                revision: revision,
                prodfam: prodfam,
                status: status,
                type: type,
            });
        }
    }

    function detailHistory(item) {
        $('#dg').datagrid('load', {item: item});
        $('#dlg_detail').dialog('open');
    }

    function excel() {
        var month = btoa($('#periode_month').combobox('getValue'));
        var year = btoa($('#periode_year').combobox('getValue'));
        var revision = btoa($('#revision').combobox('getValue'));
        var prodfam = btoa($('#prodfam').combobox('getValue'));
        var status = btoa($('#status').combobox('getValue'));
        var type = btoa($('#type').combobox('getValue'));

        if ([month, year, revision].includes())
            { toastr.warning("Please select Periode and Revision!", "Information"); }
        else {
            $.messager.alert('Export to excel','Please wait...','info');
            window.location.assign("<?= base_url('planning/monitoring_level_stock/excel') ?>"
                +"?year="+year
                +"&month="+month
                +"&revision="+revision
                +"&prodfam="+prodfam
                +"&status="+status
                +"&type="+type
                +"&time="+Date.now()
            );
        }
    }

    function reload() { window.location.reload(); }

    function getprocess(act, data) {
        var dg = $('#dg');
        dg.datagrid('loading');
        $.ajax({
            url: '<?= base_url('planning/monitoring_level_stock/'); ?>'+act,
            data: data,
            async: false,
            method: 'POST',
            dataType: 'json',
            success: function (result) {
                if (act == 'datatables') {
                    var columns = [[
                        {field: 'class', title: 'CLASS', halign: 'center', align: 'center', rowspan: 2},
                        {field: 'po_lt', title: 'PO L/T', halign: 'center', align: 'right', rowspan: 2},
                        {field: 'moq', title: 'MOQ', halign: 'center', align: 'right', rowspan: 2},
                        {field: 'mpq', title: 'MPQ', halign: 'center', align: 'right', rowspan: 2},
                        {field: 'spq', title: 'SPQ', halign: 'center', align: 'right', rowspan: 2},
                        {field: 'maker', title: 'MAKER TYPE', halign: 'center', align: 'center', rowspan: 2},
                        {field: 'stock_whs', title: 'STOCK WHS', halign: 'center', align: 'right', rowspan: 2},
                        {field: 'os_supply', title: 'OS SUPPLY', halign: 'center', align: 'right', rowspan: 2},
                        {field: 'os_wo', title: 'OS WO', halign: 'center', align: 'right', rowspan: 2},
                        {field: 'eta', title: 'ETA', halign: 'center', align: 'right', rowspan: 2},
                        {field: 'balance', title: 'BALANCE', halign: 'center', align: 'right', rowspan: 2, styler: balanceColor},
                        {field: 'stock_awal', title: 'STOCK AWAL', halign: 'center', align: 'right', rowspan: 2},
                        {field: 'total_eta', title: 'TOTAL ETA', halign: 'center', align: 'right', rowspan: 2},
                        {title: 'TOTAL NEED', halign: 'center', colspan: 3},
                        {title: 'ITO (DAYS)', halign: 'center', colspan: 4},
                        {field: 'id', title: 'REQUEST SHIPMENT', halign: 'center', align: 'center', rowspan: 2, formatter: function (value, row, index) {
                            return `<a href="javascript:;" class="btn btn-primary" onClick="detailHistory('`+btoa(row.part_no)+`')" style="pointer-events: unset; opacity: unset;">Detail</a> `;
                        }}
                    ],[
                        {field: 'rev_0', title: 'REV.00', halign: 'center', align: 'right'},
                        {field: 'rev_1', title: 'REV.01', halign: 'center', align: 'right'},
                        {field: 'rev_2', title: 'REV.02', halign: 'center', align: 'right'},
                        {field: 'ito_std_min', title: 'STD (MIN)', halign: 'center', align: 'right'},
                        {field: 'ito_std_max', title: 'STD (MAX)', halign: 'center', align: 'right'},
                        {field: 'ito_act', title: 'ACT', halign: 'center', align: 'right'},
                        {field: 'ito_status', title: 'STATUS', halign: 'center', align: 'center'}
                    ]];
                    var col1 = [];
                    var col2 = [];

                    var month_name = $('#periode_month').combobox('getText').substring(0, 3), date;
                    for (var i = 1; i <= result.columns.total; i++) {
                        date = new Date(result.columns.format+lpad(i));

                        if (![0,6].includes(date.getDay()) && !result.columns.holiday.includes(i)) {
                            col1.push({title: lpad(i)+' '+month_name+' (W/P '+(col1.length + 1)+')', halign: 'center', colspan: 4});
                            col2.push({field: 'eta_'+i, title: 'ETA', halign: 'center', align: 'right'});
                            col2.push({field: 'need_'+i, title: 'NEED', halign: 'center', align: 'right'});
                            col2.push({field: 'supply_'+i, title: 'SUPPLY', halign: 'center', align: 'right'});
                            col2.push({field: 'balance_'+i, title: 'BALANCE', halign: 'center', align: 'right', styler: balanceColor});
                        }
                    }

                    col1.push({title: 'SAFETY STOCK', halign: 'center', colspan: 2});
                    col2.push({field: 'stock_qty', title: 'QTY', halign: 'center', align: 'right'});
                    col2.push({field: 'stock_balance', title: 'BALANCE', halign: 'center', align: 'right', styler: balanceColor});

                    for (var i = 1; i <= 3; i++) {
                        if (i == 1) { date.setDate(date.getDate() + 1); }
                        else { date.setMonth(date.getMonth() + 1); }
                        for (var j = 1; j <= 4; j++) {
                            col1.push({title: date.toLocaleString('default', { month: 'short' })+' Week '+j, halign: 'center', colspan: 3});
                            col2.push({field: 'week_'+i+'_'+j+'_eta', title: 'ETA', halign: 'center', align: 'right'});
                            col2.push({field: 'week_'+i+'_'+j+'_need', title: 'NEED', halign: 'center', align: 'right'});
                            col2.push({field: 'week_'+i+'_'+j+'_balance', title: 'BALANCE', halign: 'center', align: 'right', styler: balanceColor});
                        }
                    }

                    columns[0].splice(14, 0, ...col1);
                    columns[1].splice(3, 0, ...col2);
                    dg.datagrid('loadData', [])
                        .datagrid('disableFilter')
                        .datagrid({
                            columns: columns,
                            frozenColumns: [[
                                {field: 'part_no', title: 'PART NO', halign: 'center', rowspan: 2},
                                {field: 'similar_part', title: 'SIMILAR PART', halign: 'center', rowspan: 2},
                                {field: 'part_name', title: 'PART NAME', halign: 'center', rowspan: 2},
                                {field: 'prodfam', title: 'PRODUCT FAMILY', halign: 'center', rowspan: 2}
                            ],[]],
                        }).datagrid('enableFilter', [
                            {field: 'similar_part', type: 'label'},
                            {field: 'id', type: 'label'}
                        ]).datagrid('loadData', result.data);
                }
                dg.datagrid('loaded');
            }
        });
    }

    function lpad(text) {
        if (text < 10) { text = '0'+text; }
        return text;
    }

    function balanceColor(value, row, index) {
        return (parseFloat(value) < 0) ? `color: red;` : '';
    }
</script>