<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'part_no',width:'100',halign:'center'">Item ID</th>
            <th rowspan="2" data-options="field:'supplier',width:'200',halign:'center'">Supplier</th>
            <th rowspan="2" data-options="field:'maker_name',width:'300',halign:'center'">WIRE Name Maker</th>
            <th rowspan="2" data-options="field:'part_no',width:'100',halign:'center'">Part No</th>
            <th rowspan="2" data-options="field:'id',width:'100',halign:'center',align:'center',formatter:btnHistory">History</th>
            <th colspan="<?= $colspan ?>" data-options="field:''">Unit Packing MOQ Price  & LME Range (USD/MTR)</th>
            <th rowspan="2" data-options="field:'u_date',width:'150',halign:'center',align:'center'">Last Date</th>
            <th rowspan="2" data-options="field:'u_by',width:'100',halign:'center'">By</th>
        </tr>
        <tr>
            <?php foreach ($cols as $key => $value) { ?>
                <th data-options="field:'<?= $key ?>',width:'100',halign:'center',align:'right'"><?= $value ?></th>
            <?php } ?>
        </tr>
    </thead>
</table>

<div id="toolbar">
    <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px;">
        <legend><b>Form Filter Data</b></legend>
        <div style="width: 30%; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">LME Range</span>
                <input style="width:60%;" name="f_lme_range" id="f_lme_range" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Maker</span>
                <input style="width:60%;" name="f_maker" id="f_maker" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </div>
        <div style="width: 30%; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Supplier</span>
                <input style="width:60%;" name="f_supp_id" id="f_supp_id" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Part No</span>
                <input style="width:60%;" name="f_part_no" id="f_part_no" class="easyui-combogrid">
            </div>
        </div>
    </fieldset>
    <?= $button ?>
</div>

<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 800px; height: 550px;">
    <div id="toolbar_insert" style="padding:10px; top: 20px;">
        <form id="frm_insert" method="post" novalidate>
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px;">
                <legend><b>Form Data</b></legend>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Maker</span>
                    <input style="width:60%;" name="maker" id="maker" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part No</span>
                    <input style="width:60%;" name="part_no" id="part_no" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier</span>
                    <input style="width:60%;" name="supp_id" id="supp_id" class="easyui-combogrid">
                </div>
            </fieldset>
        </form><br>
        <a href="javascript:;" id="bnt_add" class="easyui-linkbutton" data-options="plain:true" onclick="addRange()"><i class="fa fa-plus"></i> Add Row</a>
        <a href="javascript:;" id="bnt_del" class="easyui-linkbutton" data-options="plain:true" onclick="delRange()"><i class="fa fa-minus"></i> Delete Row</a>
    </div>
    <table id="dg_insert"></table>
</div>

<!-- Upload -->
<div id="dlg_upload" class="easyui-dialog" title="Upload Data" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_upload" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">File Upload</span>
                <input name="file_upload" style="width: 60%;" required="" accept=".xls" id="file_excel" class="easyui-filebox">
            </div>
        </fieldset>
    </form>
    <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
    <div id="p_remarks" class="easyui-panel" style="width:100%; height:200px; padding:10px; margin-top: 10px;">
        <p>History Upload</p>
        <ul id="remarks">

        </ul>
    </div>
</div>

<div id="dlg_history" class="easyui-dialog" title="History Data" data-options="closed: true, modal: true" style="width: 1000px; height: 500px;"><table id="dg_history" class="easyui-datagrid" style="width: 100%; height: 100%;"></table></div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('planning/lme_prices/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    var url_supp = '<?= base_url('planning/lme_prices/readItemsSupplier'); ?>';
    var url_patno = '<?= base_url('planning/lme_prices/readPartNoBEI'); ?>';
    var url_range = '<?= base_url('planning/lme_prices/readLMERanges'); ?>';
    var url_maker = '<?= base_url('planning/lme_prices/readMakers'); ?>';

    function filter() {
        var f_lme_range = $("#f_lme_range").combogrid('getValue');
        var f_supp_id = $("#f_supp_id").combogrid('getValue');
        var f_part_no = $("#f_part_no").combogrid('getValue');
        var f_maker = $("#f_maker").combogrid('getValue');

        var url = "?f_lme_range=" + window.btoa(f_lme_range) +
            "&f_supp_id=" + window.btoa(f_supp_id) +
            "&f_part_no=" + window.btoa(f_part_no) +
            "&f_maker=" + window.btoa(f_maker);

        $('#dg').datagrid({
            url: '<?= base_url('planning/lme_prices/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('planning/lme_prices/print') ?>' + url);
    }

    //Add Data
    function add() {
        url_save = '<?= base_url('planning/lme_prices/create') ?>';
        $('#maker').combogrid('enable');
        $('#part_no').combogrid('disable');
        $('#supp_id').combogrid('disable');
        $('#bnt_add').linkbutton('disable');
        $('#bnt_del').linkbutton('disable');
        $('#dg_insert').datagrid('loadData', []);
        $('#dlg_insert').dialog('open');
        $('#frm_insert').form('clear');
    }

    //Edit Data
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#maker').combogrid('disable').combogrid('grid').datagrid('reload');
            $('#part_no').combogrid('disable').combogrid('grid').datagrid('reload');
            $('#supp_id').combogrid('disable').combogrid('grid').datagrid('reload');
            $('#maker').combogrid({onLoadSuccess: function (data) { $('#maker').combogrid('setValue',row.maker_code); }});
            $('#part_no').combogrid({onLoadSuccess: function (data) { $('#part_no').combogrid('setValue',row.part_no); }});
            $('#supp_id').combogrid({onLoadSuccess: function (data) { $('#supp_id').combogrid('setValue',row.ven_id); }});
            $('#dg_insert').datagrid('loadData', JSON.parse(row.prices));
            $('#bnt_add').linkbutton('enable');
            $('#bnt_del').linkbutton('enable');
            // $('#frm_insert').form('load', row);
            $('#dlg_insert').dialog('open');
            url_save = '<?= base_url('planning/lme_prices/update') ?>?id=' + btoa(row.id);
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
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
                            url: '<?= base_url('planning/lme_prices/delete') ?>',
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
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //Upload Data
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    function download_excel() {
        window.location.assign('<?= base_url('excel/tmp_lme_price') ?>');
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        window.location.assign('<?= base_url('planning/lme_prices/print/excel') ?>');
    }

    function reload() {
        window.location.reload();
    }

    function addRange() {
        if ($('#maker').combogrid('getValue') == '')
            { toastr.warning("Please fill in the Form Data fields!", "Information"); }
        else { $('#dg_insert').datagrid('appendRow', {range: null, price: null}).datagrid('acceptChanges'); }
    }

    function delRange() {
        var dg = $('#dg_insert');
        var rows = dg.datagrid('getChecked');
        var total = rows.length;
        if (total == 0) { toastr.warning("Please checked one of the data in the table first!", "Information"); }
        else { for (var i = total - 1; i >= 0; i--) {
            dg.datagrid('deleteRow', dg.datagrid('getRowIndex', rows[i])).datagrid('acceptChanges');
        } }
    }

    function btnHistory(value, row, index) {
        return '<a href="javascript:;" class="easyui-linkbutton" onclick="viewHistory('+"'"+window.btoa(value)+"'"+')"><i class="fa fa-search"></i> View</a>';
    }

    function viewHistory(id) {
        $.messager.progress({title: 'Please Wait', msg: 'Loading...'});
        $.post('<?= base_url('planning/lme_prices/historys') ?>', {id:id}, function (result, status) {
            $.messager.progress('close');
            if (result.span.length != 0) {
                $('#dg_history').datagrid({
                    pagination: true,
                    clientPaging: false,
                    remoteFilter: true,
                    rownumbers: true,
                    fit: true,
                    pageList: [20, 50, 100, 500, 1000],
                    pageSize: 20,
                    columns: [[
                        {field: 'part_no', title: 'Item ID', width: '100', halign: 'center', rowspan: 2},
                        {field: 'supplier', title: 'Supplier', width: '200', halign: 'center', rowspan: 2},
                        {field: 'maker_name', title: 'WIRE Name Maker', width: '300', halign: 'center', rowspan: 2},
                        {field: 'part_no', title: 'Part No', width: '100', halign: 'center', rowspan: 2},
                        {title: 'Unit Packing MOQ Price  & LME Range (USD/MTR)', width: '100', halign: 'center', colspan: result.span.length},
                        {field: 'u_date', title: 'Last Date', width: '150', halign: 'center', rowspan: 2},
                        {field: 'u_by', title: 'By', width: '100', halign: 'center', rowspan: 2}
                    ],result.span],
                    data: result.data,
                    onLoadSuccess: function () { $('#dlg_history').dialog('open'); }
                }).datagrid('resize');
            }
        }, "json");
    }

    $(function() {
        $('#dg').datagrid({
            url: '<?= base_url('planning/lme_prices/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            onLoadSuccess: function () { $('.easyui-linkbutton').linkbutton(); }
        });

        $('#f_lme_range').combogrid({
            url: url_range,
            panelWidth: 550,
            idField: 'ranges',
            textField: 'ranges',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select LME Range",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) { $(e.data.target).combogrid('clear').combogrid('textbox').focus(); }
            }],
            columns: [[{field: 'ranges', title: 'LME Range', halign: 'center', width: '98%'}]]
        });

        $('#f_supp_id').combogrid({
            url: url_supp,
            panelWidth: 550,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Supplier",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) { $(e.data.target).combogrid('clear').combogrid('textbox').focus(); }
            }],
            columns: [[
                {field: 'id', title: 'Supplier ID', halign: 'center', width: '49%'},
                {field: 'name', title: 'Supplier Name', halign: 'center', width: '49%'}
            ]]
        });

        $('#f_maker').combogrid({
            url: url_maker,
            panelWidth: 550,
            idField: 'code',
            textField: 'maker',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Maker ID",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) { $(e.data.target).combogrid('clear').combogrid('textbox').focus(); }
            }],
            columns: [[
                {field: 'code', title: 'Maker ID', halign: 'center', width: '49%'},
                {field: 'maker', title: 'Maker Name', halign: 'center', width: '49%'}
            ]]
        });

        $('#f_part_no').combogrid({
            url: url_patno,
            panelWidth: 550,
            idField: 'item',
            textField: 'item',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Part No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) { $(e.data.target).combogrid('clear').combogrid('textbox').focus(); }
            }],
            columns: [[{field: 'item', title: 'Part No', halign: 'center', width: '98%'}]]
        });

        $('#maker').combogrid({
            url: url_maker,
            panelWidth: 550,
            idField: 'code',
            textField: 'maker',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Maker ID",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $('#bnt_add').linkbutton('disable');
                    $('#bnt_del').linkbutton('disable');
                    $('#dg_insert').datagrid('loadData',[]);
                    $('#supp_id').combogrid('clear').combogrid('disable');
                    $('#part_no').combogrid('clear').combogrid('disable');
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [[
                {field: 'code', title: 'Maker ID', halign: 'center', width: '49%'},
                {field: 'maker', title: 'Maker Name', halign: 'center', width: '49%'}
            ]],
            onSelect: function (index, row) {
                $('#part_no').combogrid('clear').combogrid('enable').combogrid('grid')
                    .datagrid('reload', {maker: window.btoa(row.code)});
                $('#supp_id').combogrid('clear').combogrid('disable');
                $('#dg_insert').datagrid('loadData',[]);
            }
        });

        $('#part_no').combogrid({
            url: url_patno,
            panelWidth: 550,
            idField: 'item',
            textField: 'item',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Part No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $('#bnt_add').linkbutton('disable');
                    $('#bnt_del').linkbutton('disable');
                    $('#dg_insert').datagrid('loadData',[]);
                    $('#supp_id').combogrid('clear').combogrid('disable');
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [[{field: 'item', title: 'Part No', halign: 'center', width: '49%'}]],
            onSelect: function (index, row) {
                $('#supp_id').combogrid('clear').combogrid('enable').combogrid('grid')
                    .datagrid('reload', {
                        maker: window.btoa($('#maker').combogrid('getValue')),
                        supp: window.btoa(row.item)
                    });
                $('#dg_insert').datagrid('loadData',[]);
            }
        });

        $('#supp_id').combogrid({
            url: url_supp,
            panelWidth: 550,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Supplier",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $('#bnt_add').linkbutton('disable');
                    $('#bnt_del').linkbutton('disable');
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [[
                {field: 'id', title: 'Supplier ID', halign: 'center', width: '49%'},
                {field: 'name', title: 'Supplier Name', halign: 'center', width: '49%'}
            ]],
            onSelect: function (index, row) {
                $('#bnt_add').linkbutton('enable');
                $('#bnt_del').linkbutton('enable');
                $('#dg_insert').datagrid('loadData',[]);
            }
        });

        var idx = -1;
        $('#dg_insert').datagrid({
            toolbar: '#toolbar_insert',
            rownumbers: true,
            fit: true,
            columns: [[
                {field: 'ck', checkbox: true},
                {field: 'range', title: 'LME Range', halign: 'center', align: 'center', width: '49%', editor: {
                    type: 'combobox',
                    options: {
                        url: url_range,
                        valueField: 'ranges',
                        textField: 'ranges',
                        mode: 'remote',
                        prompt: "Select LME Range",
                        icons: [{
                            iconCls: 'icon-clear',
                            handler: function(e) { $(e.data.target).combobox('clear').combobox('textbox').focus(); }
                        }]
                    }
                }},
                {field: 'price', title: 'LME Price', halign: 'center', align: 'right', width: '49%', editor: {
                    type: 'numberbox',
                    options: {precision: 4}
                }}
            ]],
            onClickCell: function (index, field, value) { idx = index; },
            onEndEdit: function (index, row, changes) {
                if (changes.range != undefined) {
                    var success = true;
                    $('#dg_insert').datagrid('getRows').forEach(function (value, key) {
                        if (key != index && value.range == changes.range) {
                            toastr.warning("LME Range is already in use!", "Information");
                            success = false;
                        }
                    });

                    if (success) { $('#dg_insert').datagrid('acceptChanges'); }
                    else { $('#dg_insert').datagrid('rejectChanges'); }
                }
            }
        }).datagrid('enableCellEditing');

        //Save Data
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    var msg = '';
                    var dg = $('#dg_insert');
                    var data = dg.datagrid('getRows');

                    if (data.length == 0) { msg = "No data to save!"; }
                    else {
                        data.forEach(function (value, key) { dg.datagrid('endEdit', key); });
                        dg.datagrid('acceptChanges');
                        data.forEach(function (value, key) {
                            if (value.range == '' || value.price == '' || value.range == null || value.price == null)
                                { msg = 'Please fill in all data!'; }
                        });
                    }

                    if (msg != '') { toastr.warning(msg, "Information"); }
                    else {
                        $('#frm_insert').form('submit', {
                            url: url_save,
                            onSubmit: function(param) {
                                param.datas = JSON.stringify(dg.datagrid('getRows'));
                                return $(this).form('validate');
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');

                                if (result.theme == "success") {
                                    toastr.success(result.message, result.title);
                                    $('#dlg_insert').dialog('close');
                                    $('#dg').datagrid('reload');
                                } else {
                                    toastr.error(result.message, result.title);
                                }
                            }
                        });
                    }
                }
            }]
        });

        //Upload Data
        $('#dlg_upload').dialog({
            buttons: [{
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('planning/lme_prices/upload') ?>',
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
                            $.messager.progress('close');
                            var json = eval('(' + result + ')');
                            if (json.total == 0) { toastr.error('No Data!', "Information"); }
                            else { requestData(json.total, json); }
                        }
                    });
                }
            }]
        });
    });

    function requestData(total, json, number = 1, value = 0) {
        if (value < 100) {
            value = Math.floor((number / total) * 100);
            $('#p_upload').progressbar('setValue', value);
            $('#p_start').html(number);
            $('#p_finish').html(total);

            $.post('<?= base_url('planning/lme_prices/uploadCreate') ?>', {
                data: json[number - 1]
            }, function(note) {
                var result = eval('(' + note + ')');
                if (result.theme == "success") {
                    var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                } else {
                    var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                }
                $("#p_remarks").append(title + "<br>");
                requestData(total, json, number + 1, value);
            });
        } else { $('#dg').datagrid('reload'); }
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