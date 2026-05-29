<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'customer_name',width:200,halign:'center'">Customer</th>
            <th rowspan="2" data-options="field:'item_id',width:200,halign:'center'">Product No</th>
            <th rowspan="2" data-options="field:'item_name',width:200,halign:'center'">Product Name</th>
            <th rowspan="2" data-options="field:'circuit_no',width:50,halign:'center'">CCT</th>
            <th colspan="12" data-options="field:'',width:100,halign:'center'">Months</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'january',width:80,align:'center'">Month 1</th>
            <th data-options="field:'february',width:80,align:'center'">Month 2</th>
            <th data-options="field:'march',width:80,align:'center'">Month 3</th>
            <th data-options="field:'april',width:80,align:'center'">Month 4</th>
            <th data-options="field:'may',width:80,align:'center'">Month 5</th>
            <th data-options="field:'june',width:80,align:'center'">Month 6</th>
            <th data-options="field:'june',width:80,align:'center'">Month 7</th>
            <th data-options="field:'june',width:80,align:'center'">Month 8</th>
            <th data-options="field:'june',width:80,align:'center'">Month 9</th>
            <th data-options="field:'june',width:80,align:'center'">Month 10</th>
            <th data-options="field:'june',width:80,align:'center'">Month 11</th>
            <th data-options="field:'june',width:80,align:'center'">Month 12</th>
            <th data-options="field:'created_name',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_name',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 195px; padding: 10px;">
    <fieldset style="width:60%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <legend><b>Form Filter Data</b></legend>
        <div style="width: 50%;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Period</span>
                <input style="width:30%;" name="filter_month" id="filter_month" class="easyui-combobox" data-options="prompt:'Month'">
                <input style="width:30%;" name="filter_year" id="filter_year" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Revision</span>
                <input style="width:60%;" name="filter_revision" id="filter_revision" class="easyui-combobox" data-options="prompt:'Revision'" panelHeight="auto">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter('display')"><i class="fa fa-search"></i> Display Data</a>
            </div>
        </div>
        <div style="width: 45%;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Customer</span>
                <input style="width:60%;" name="filter_customer_id" id="filter_customer_id" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" name="filter_product_no" id="filter_product_no" class="easyui-combogrid">
            </div>
        </div>
    </fieldset>
    <?= $button ?>
</div>

<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Period</span>
                <select style="width:30%;" name="p_month" id="p_month" required="true" class="easyui-combobox">
                    <option value="01">January</option>
                    <option value="02">February</option>
                    <option value="03">March</option>
                    <option value="04">April</option>
                    <option value="05">May</option>
                    <option value="06">June</option>
                    <option value="07">July</option>
                    <option value="08">August</option>
                    <option value="09">September</option>
                    <option value="10">October</option>
                    <option value="11">November</option>
                    <option value="12">December</option>
                </select>
                <input style="width:30%;" name="p_year" id="p_year" required="true" class="easyui-numberbox" data-options="validType: 'length[0,4]'">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Revision</span>
                <select style="width:60%;" name="revision" id="revision" required="true" class="easyui-combobox" data-options="prompt:'Select Revision'" panelHeight="auto">
                    <option value="0">Revision 0</option>
                    <option value="1">Revision 1</option>
                    <option value="2">Revision 2</option>
                    <option value="3">Revision 3</option>
                    <option value="4">Revision 4</option>
                    <option value="5">Revision 5</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" name="product_no" id="product_no" required="true" class="easyui-combogrid">
            </div>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Product Name</span>
                <input style="width:60%;" name="product_name" id="product_name" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Customer Code</span>
                <input style="width:60%;" id="customer_code" class="easyui-textbox" readonly>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Customer Name</span>
                <input style="width:60%;" id="customer_name" class="easyui-textbox" readonly>
            </div>
        </fieldset>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Month Qty</b></legend>
            <div style="float: left; width: 50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Month 1</span>
                    <input style="width:50%;" name="month_1" id="month_1" required="true" class="easyui-numberbox" precision="2">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Month 2</span>
                    <input style="width:50%;" name="month_2" id="month_2" required="true" class="easyui-numberbox" precision="2">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Month 3</span>
                    <input style="width:50%;" name="month_3" id="month_3" required="true" class="easyui-numberbox" precision="2">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Month 4</span>
                    <input style="width:50%;" name="month_4" id="month_4" required="true" class="easyui-numberbox" precision="2">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Month 5</span>
                    <input style="width:50%;" name="month_5" id="month_5" required="true" class="easyui-numberbox" precision="2">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Month 6</span>
                    <input style="width:50%;" name="month_6" id="month_6" required="true" class="easyui-numberbox" precision="2">
                </div>
            </div>
            <div style="float: left; width: 50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Month 7</span>
                    <input style="width:50%;" name="month_7" id="month_7" required="true" class="easyui-numberbox" precision="2">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Month 8</span>
                    <input style="width:50%;" name="month_8" id="month_8" required="true" class="easyui-numberbox" precision="2">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Month 9</span>
                    <input style="width:50%;" name="month_9" id="month_9" required="true" class="easyui-numberbox" precision="2">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Month 10</span>
                    <input style="width:50%;" name="month_10" id="month_10" required="true" class="easyui-numberbox" precision="2">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Month 11</span>
                    <input style="width:50%;" name="month_11" id="month_11" required="true" class="easyui-numberbox" precision="2">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Month 12</span>
                    <input style="width:50%;" name="month_12" id="month_12" required="true" class="easyui-numberbox" precision="2">
                </div>
            </div>
        </fieldset>
    </form>
</div>

<!-- Upload -->
<div id="dlg_upload" class="easyui-dialog" title="Upload Data" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <form id="frm_upload" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">File Upload</span>
                <input name="file_upload" style="width: 60%;" required="" accept=".xls" id="file_excel" class="easyui-filebox">
            </div>
        </fieldset>
    </form>
    <span style="float: left; color:green;">SUCCESS : <b id="p_success">0</b></span><span style="float: right; color:red;"> FAILED : <b id="p_failed">0</b></span>
    <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
    <div id="p_remarks" title="History Upload" class="easyui-panel" style="width:100%; height:200px; padding:10px; margin-top: 10px;">
        <ul id="remarks">

        </ul>
    </div>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('planning/mst_forecast/print') ?>" style="width: 100%;" hidden></iframe>

<div id="dlg_display" class="easyui-window" title="Display Data" data-options="closed: true,modal:true" style="width: 1200px; height: 550px;">
    <table id="dg_display" class="easyui-datagrid" style="width:99.5%;">
        <thead>
            <tr>
                <th rowspan="2" data-options="field:'customer_name',width:200,halign:'center'">Customer</th>
                <th rowspan="2" data-options="field:'item_id',width:200,halign:'center'">Product No</th>
                <th rowspan="2" data-options="field:'item_name',width:200,halign:'center'">Product Name</th>
                <th rowspan="2" data-options="field:'circuit_no',width:50,halign:'center'">CCT</th>
                <th colspan="12" data-options="field:'',width:100,halign:'center'">Months</th>
                <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
                <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
            </tr>
            <tr>
                <th data-options="field:'january',width:80,align:'center'">Month 1</th>
                <th data-options="field:'february',width:80,align:'center'">Month 2</th>
                <th data-options="field:'march',width:80,align:'center'">Month 3</th>
                <th data-options="field:'april',width:80,align:'center'">Month 4</th>
                <th data-options="field:'may',width:80,align:'center'">Month 5</th>
                <th data-options="field:'june',width:80,align:'center'">Month 6</th>
                <th data-options="field:'june',width:80,align:'center'">Month 7</th>
                <th data-options="field:'june',width:80,align:'center'">Month 8</th>
                <th data-options="field:'june',width:80,align:'center'">Month 9</th>
                <th data-options="field:'june',width:80,align:'center'">Month 10</th>
                <th data-options="field:'june',width:80,align:'center'">Month 11</th>
                <th data-options="field:'june',width:80,align:'center'">Month 12</th>
                <th data-options="field:'created_name',width:100,align:'center'"> By</th>
                <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
                <th data-options="field:'updated_name',width:100,align:'center'"> By</th>
                <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
            </tr>
        </thead>
    </table>
</div>

<script>
    //Add Data
    function add() {
        is_update = false;
        url_save = '<?= base_url('planning/mst_forecast/create') ?>';
        $('#dlg_insert').dialog('open');
        $('#frm_insert').form('clear');
        $("#p_year").numberbox('setValue', '<?= date("Y") ?>');
        $("#p_month").combobox('setValue', '<?= date("m") ?>');
        $("#trans_date").datebox('setValue', '<?= date("Y-m-d") ?>');
        $("#p_month").combobox('enable');
        $("#p_year").numberbox('enable');
        $("#revision").combobox('enable');

        $('#product_no').combogrid('grid').datagrid('reload', "<?= base_url('planning/mst_data/readProducts') ?>");
    }

    //Edit Data
    var is_update = true;
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            $("#p_month").combobox('disable');
            $("#p_year").numberbox('disable');
            $("#revision").combobox('disable');

            var p_month = $("#p_month").combobox('getValue');
            var p_year = $("#p_year").numberbox('getValue');
            var revision = $("#revision").combobox('getValue');
            is_update = true;

            $('#product_no').combogrid('grid').datagrid('reload', "<?= base_url('planning/mst_forecast/readProducts?p_month=') ?>"+p_month+'&p_year='+p_year+'&revision='+revision);
            $("#product_no").combogrid('setValue', row.product_no);
            var product_no = $("#product_no").combogrid('getValue');

            url_save = '<?= base_url('planning/mst_forecast/update') ?>?p_month=' + p_month + '&p_year='+p_year+'&revision=' + revision + '&product_no=' + btoa(product_no);
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
                            url: '<?= base_url('planning/mst_forecast/delete') ?>',
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
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');

        if (filter_month != "" || filter_year != "" || filter_revision != "") {
            window.location.assign('<?= base_url('excel/tmp_forecast?month=') ?>' + window.btoa(filter_month) + "&year=" + window.btoa(filter_year) + "&revision=" + window.btoa(filter_revision));
        } else {
            toastr.warning("Please select period and revision!", "Information");
        }
    }

    function filter(act='') {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_customer_id = $("#filter_customer_id").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');

        if (filter_month == "" || filter_year == "" || filter_revision == "") {
            toastr.info("Please Select Period and Revision");
        } else {
            $.ajax({
                type: "post",
                url: "<?= base_url('planning/mst_forecast/readMonths') ?>",
                data: "month=" + window.btoa(filter_month) + "&year=" + window.btoa(filter_year),
                dataType: "json",
                success: function(monthName) {
                    var dg = 'dg';
                    var columns = [[
                        {field: 'ck', checkbox: true, rowspan: 2},
                        {field: 'customer_name', title: 'Customer', width: 200, halign: 'center', rowspan: 2},
                        {field: 'item_id', title: 'Product No', width: 200, halign: 'center', rowspan: 2},
                        {field: 'item_name', title: 'Product Name', width: 200, halign: 'center', rowspan: 2},
                        {field: 'circuit_no', title: 'CCT', width: 50, align: 'center', rowspan: 2},
                        {field: '', title: 'Months', width: 200, align: 'center', colspan: 12},
                        {field: '', title: 'Created', width: 200, align: 'center', colspan: 2},
                        {field: '', title: 'Updated', width: 200, align: 'center', colspan: 2}
                    ],[
                        {field: 'month_1', title: monthName[0], width: 80, align: 'center', formatter: numberformat},
                        {field: 'month_2', title: monthName[1], width: 80, align: 'center', formatter: numberformat},
                        {field: 'month_3', title: monthName[2], width: 80, align: 'center', formatter: numberformat},
                        {field: 'month_4', title: monthName[3], width: 80, align: 'center', formatter: numberformat},
                        {field: 'month_5', title: monthName[4], width: 80, align: 'center', formatter: numberformat},
                        {field: 'month_6', title: monthName[5], width: 80, align: 'center', formatter: numberformat},
                        {field: 'month_7', title: monthName[6], width: 80, align: 'center', formatter: numberformat},
                        {field: 'month_8', title: monthName[7], width: 80, align: 'center', formatter: numberformat},
                        {field: 'month_9', title: monthName[8], width: 80, align: 'center', formatter: numberformat},
                        {field: 'month_10', title: monthName[9], width: 80, align: 'center', formatter: numberformat},
                        {field: 'month_11', title: monthName[10], width: 80, align: 'center', formatter: numberformat},
                        {field: 'month_12', title: monthName[11], width: 80, align: 'center', formatter: numberformat},
                        {field: 'created_name', title: 'By', width: 100, align: 'center'},
                        {field: 'created_date', title: 'Date', width: 150, align: 'center'},
                        {field: 'updated_name', title: 'By', width: 100, align: 'center'},
                        {field: 'updated_date', title: 'Date', width: 150, align: 'center'}
                    ]];

                    if (act == 'display') {
                        dg += '_display';
                        columns[0].splice(0, 1);
                        if (filter_customer_id == '') { filter_customer_id = 'all'; }
                        $('#dlg_display').window('open');
                    }
                    var url = "?filter_customer_id=" + window.btoa(filter_customer_id) +
                    "&filter_product_no=" + window.btoa(filter_product_no) +
                    "&filter_revision=" + window.btoa(filter_revision) +
                    "&filter_month=" + window.btoa(filter_month) +
                    "&filter_year=" + window.btoa(filter_year);

                    $('#'+dg).datagrid({
                        url: "<?= base_url('planning/mst_forecast/datatables') ?>" + url,
                        pageList: ['10', '20', '30', '40', '50', '100', '500', '1000'],
                        columns: columns,
                        rowStyler: function(index, row) {
                            if (row.p_month == null) {
                                return 'background-color:#FFDAD2;';
                            }
                        }
                    });

                    $("#printout").attr('src', "<?= base_url('planning/mst_forecast/print') ?>" + url);
                }
            });
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_customer_id = $("#filter_customer_id").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');

        if(filter_month == "" || filter_year == "" || filter_revision == ""){
            toastr.info("Please Select Period and Revision");
        }else{
            var url = "?filter_customer_id=" + window.btoa(filter_customer_id) +
                "&filter_product_no=" + window.btoa(filter_product_no) +
                "&filter_revision=" + window.btoa(filter_revision) +
                "&filter_month=" + window.btoa(filter_month) +
                "&filter_year=" + window.btoa(filter_year);

            window.location.assign('<?= base_url('planning/mst_forecast/print/excel') ?>' + url);
        }
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        $("#filter_month").combobox('setValue', '<?= date("m") ?>');

        $('#dg').datagrid({
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        });

        $('#dg_display').datagrid({
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        });

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
                                $('#dlg_insert').dialog('close');
                            } else {
                                toastr.error(result.message, result.title);
                            }

                            $('#dg').datagrid('reload');
                        }
                    });
                }
            }]
        });

        $('#product_no').combogrid({
            url: '',
            panelWidth: 400,
            idField: 'product_no',
            textField: 'product_no',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Product No",
            columns: [[
                {field: 'product_no', title: 'Item ID', width: 200},
                {field: 'product_name', title: 'Item Name', width: 200}
            ]],
            onSelect: function(index, row) {
                $("#product_name").textbox('setValue', row.product_name);
                $("#customer_code").textbox("setValue", row.cust_nick);
                $("#customer_name").textbox("setValue", row.cust_name);
                if (is_update) {
                    $("#month_1").numberbox('setValue', row.month_1);
                    $("#month_2").numberbox('setValue', row.month_2);
                    $("#month_3").numberbox('setValue', row.month_3);
                    $("#month_4").numberbox('setValue', row.month_4);
                    $("#month_5").numberbox('setValue', row.month_5);
                    $("#month_6").numberbox('setValue', row.month_6);
                    $("#month_7").numberbox('setValue', row.month_7);
                    $("#month_8").numberbox('setValue', row.month_8);
                    $("#month_9").numberbox('setValue', row.month_9);
                    $("#month_10").numberbox('setValue', row.month_10);
                    $("#month_11").numberbox('setValue', row.month_11);
                    $("#month_12").numberbox('setValue', row.month_12);
                }
            }
        });

        //Upload Data
        $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('planning/mst_forecast/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('planning/mst_forecast/upload') ?>',
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

                            //Clear File
                            $.ajax({
                                url: "<?= base_url('planning/mst_forecast/uploadclearFailed') ?>"
                            });

                            var json = eval('(' + result + ')');
                            requestData(json.total, json);

                            function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                                if (value < 100) {
                                    value = Math.floor((number / total) * 100);
                                    $('#p_upload').progressbar('setValue', value);
                                    $('#p_start').html(number);
                                    $('#p_finish').html(total);

                                    $.ajax({
                                        type: "POST",
                                        async: true,
                                        url: "<?= base_url('planning/mst_forecast/uploadCreate') ?>",
                                        data: {
                                            "data": json[number - 1]
                                        },
                                        cache: false,
                                        dataType: "json",
                                        success: function(result) {
                                            if (result.theme == "success") {
                                                Swal.close();
                                                $('#p_success').html(success);
                                                var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                                requestData(total, json, number + 1, value, success + 1, failed + 0);
                                            } else {
                                                $('#p_failed').html(failed);
                                                var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;

                                                //Json Failed
                                                $.ajax({
                                                    type: "POST",
                                                    async: true,
                                                    url: "<?= base_url('planning/mst_forecast/uploadcreateFailed') ?>",
                                                    data: {
                                                        data: json[number - 1],
                                                        message: result.message
                                                    },
                                                    cache: false
                                                });

                                                requestData(total, json, number + 1, value, success + 0, failed + 1);
                                            }

                                            if (value == 100) {
                                                $.messager.alert('Done', 'Data upload is complete', 'info');
                                            }

                                            $("#p_remarks").append(title + "<br>");
                                        },
                                        error: function(jqXHR, textStatus) {
                                            if (textStatus == "error") {
                                                Swal.fire({
                                                    title: 'Connection Time Out, Check Your Connection',
                                                    showConfirmButton: false,
                                                    allowOutsideClick: false,
                                                    allowEscapeKey: false,
                                                    didOpen: () => {
                                                        Swal.showLoading();
                                                    },
                                                });

                                                requestData(total, json, number, value, success + 0, failed + 0);
                                            }
                                        }
                                    });
                                }
                            }
                        }
                    });
                }
            }]
        });

        $('#filter_product_no').combogrid({
            url: '<?= base_url('planning/mst_data/readProducts') ?>',
            panelWidth: 400,
            idField: 'product_no',
            textField: 'product_no',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Product No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'product_no',
                    title: 'Item ID',
                    width: 200
                }, {
                    field: 'product_name',
                    title: 'Item Name',
                    width: 200
                }]
            ]
        });

        $('#filter_customer_id').combobox({
            url: "<?= base_url('planning/mst_data/readCustomers') ?>?setting=<?= $nik ?>",
            valueField: 'number',
            textField: 'name',
            prompt: 'Select Customer',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $('#filter_month').combobox({
            url: '<?php echo base_url('planning/mst_data/readMonths'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Month',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onChange: function(row) {
                var month = $("#filter_month").combobox('getValue');
                var year = $("#filter_year").combobox('getValue');

                $('#filter_revision').combobox({
                    url: '<?php echo base_url('planning/mst_forecast/readRevision?month='); ?>' + month + '&year=' + year,
                    valueField: 'revision',
                    textField: 'revision',
                    prompt: 'Choose Revision',
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });
            }
        });

        $('#filter_year').combobox({
            url: '<?php echo base_url('planning/mst_data/readYears'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Year',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onChange: function(row) {
                var month = $("#filter_month").combobox('getValue');
                var year = $("#filter_year").combobox('getValue');

                $('#filter_revision').combobox({
                    url: '<?php echo base_url('planning/mst_forecast/readRevision?month='); ?>' + month + '&year=' + year,
                    valueField: 'revision',
                    textField: 'revision',
                    prompt: 'Choose Revision',
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });
            }
        });
    });

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

    function numberformat(value, row) {
        if(value == ""){
            return 0;
        }else{
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 0
            });

            return "<b>" + formatter.format(value) + "</b>";
        }
    }
</script>