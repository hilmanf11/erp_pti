<div class="easyui-accordion" style="width:100%;">
    <div title="Hide Filter" data-options="selected:true" style="padding:10px; background:#F4F4F4;">
        <fieldset style="width: 99%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 40%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:18%;" id="period_month_start" class="easyui-combobox">
                    <input style="width:10%;" id="period_year_start" class="easyui-combobox">&nbsp;&nbsp;to&nbsp;
                    <input style="width:18%;" id="period_month_end" class="easyui-combobox">
                    <input style="width:10%;" id="period_year_end" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Plant</span>
                    <input style="width:60%;" id="plant" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Vendor Name</span>
                    <input style="width:60%;" id="vendor" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Maker Name</span>
                    <input style="width:60%;" id="maker" class="easyui-combobox">
                </div>
            </div>
            <div style="width: 30%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" id="prodfam" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part No</span>
                    <input style="width:60%;" id="part_no" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Display</span>
                    <input style="width:60%;" id="display" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="display()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>
<div class="easyui-panel" title="Print Preview" style="width:100%;padding:10px;" data-options="fit:true">
    <iframe id="printout" src="" style="width: 100%; height: 95%; border: 0;"></iframe>
</div>
<script>
    var now_date = new Date(),
        now_month = (now_date.getMonth() + 1).toString().padStart(2, '0')
        now_year = now_date.getFullYear();
    var periodes = <?= json_encode($periodes) ?>;

    $(function() {
        $('#period_month_start').combobox({
            data: periodes.month,
            valueField: 'id',
            textField: 'name',
            value: now_month
        });

        $('#period_year_start').combobox({
            data: periodes.year,
            valueField: 'id',
            textField: 'name',
            value: now_year
        });

        $('#period_month_end').combobox({
            data: periodes.month,
            valueField: 'id',
            textField: 'name',
            value: now_month
        });

        $('#period_year_end').combobox({
            data: periodes.year,
            valueField: 'id',
            textField: 'name',
            value: now_year
        });

        $('#plant').combobox({
            data: <?= json_encode($plants) ?>,
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Plant',
            value: 'GS'
        });

        $('#vendor').combobox({
            data: <?= json_encode($vendors) ?>,
            valueField: 'id',
            textField: 'name',
            prompt: "Select Vendor Name",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) { $(e.data.target).combobox('clear').combobox('textbox').focus(); }
            }]
        });

        $('#maker').combobox({
            data: <?= json_encode($makers) ?>,
            valueField: 'id',
            textField: 'name',
            prompt: "Select Maker Name",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) { $(e.data.target).combobox('clear').combobox('textbox').focus(); }
            }]
        });

        $('#prodfam').combobox({
            data: <?= json_encode($prodfams) ?>,
            valueField: 'id',
            textField: 'name',
            prompt: "Select Product Family",
            onSelect: function (record) { $('#part_no').combogrid('clear').combogrid('grid').datagrid('load', {pfm: btoa(record.id)}); },
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                    $('#part_no').combogrid('clear').combogrid('grid').datagrid('load', {});
                }
            }]
        });

        $('#part_no').combogrid({
            url: "<?= base_url('planning/monthly_po_report_quantity_amount/readItems') ?>",
            idField: 'id',
            textField: 'id',
            panelWidth: 420,
            fitColumns: true,
            mode: 'remote',
            prompt: "Choose Part No",
            columns: [
                [
                    {field: 'id', title: 'Part No', width: 100, halign: 'center'},
                    {field: 'name', title: 'Part Name', width: 200, halign: 'center'}
                ]
            ],
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) { $(e.data.target).combogrid('clear').combogrid('textbox').focus(); }
            }]
        });

        $('#display').combobox({
            data: <?= json_encode($displays) ?>,
            valueField: 'name',
            textField: 'name',
            prompt: "Select Display"
        });
    });

    function reload() { window.location.reload(); }

    function pdf() { $("#printout").get(0).contentWindow.print(); }

    function excel() { display('excel'); }

    function display(act = 'filter') {
        var period_start = $('#period_year_start').combobox('getValue')+'-'+$('#period_month_start').combobox('getValue');
        var period_end = $('#period_year_end').combobox('getValue')+'-'+$('#period_month_end').combobox('getValue');
        var plant = btoa($('#plant').combobox('getValue'));
        var vendor = btoa($('#vendor').combobox('getValue'));
        var maker = btoa($('#maker').combobox('getValue'));
        var prodfam = btoa($('#prodfam').combobox('getValue'));
        var part_no = btoa($('#part_no').combogrid('getValue'));
        var display = btoa($('#display').combobox('getValue'));

        if (period_start > period_end) { toastr.warning("Field period invalid!"); }
        else if (plant == '') { toastr.warning("Please Select Plant!"); }
        else if (atob(display) != 'Recap' && vendor == '' && prodfam == '' && part_no == '')
            { toastr.warning("Please Select Supplier, Product Family or Part No First!"); }
        else {
            var url = '?period_start='+btoa(period_start)
                +'&period_end='+btoa(period_end)
                +'&display='+display
                +'&plant='+plant;

            if (vendor != '') { url += '&vendor='+vendor; }
            if (maker != '') { url += '&maker='+maker; }
            if (prodfam != '') { url += '&prodfam='+prodfam; }
            if (part_no != '') { url += '&part_no='+part_no; }

            if (act == 'excel') {
                toastr.success("Please Wait...", "Export Excel");
                window.location.assign('<?= base_url('planning/monthly_po_report_quantity_amount/excel') ?>' + url);
            } else {
                $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
                $("#printout").attr('src', '<?= base_url('planning/monthly_po_report_quantity_amount/print') ?>' + url);
            }
        }
    }
</script>