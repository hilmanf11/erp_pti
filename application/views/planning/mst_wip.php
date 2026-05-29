<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead frozen="true">
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'item_id',width:150,halign:'center'">Product No</th>
            <th data-options="field:'item_name',width:200,halign:'center'">Product Name</th>
        </tr>
    </thead>
    <thead>
        <tr>
            <th rowspan="2" data-options="field:'circuit_no',width:50,align:'center'">CCT</th>
            <th colspan="13" data-options="field:'',width:100,halign:'center'">Stock WIP</th>
            <th rowspan="2" data-options="field:'total_wip',width:80,align:'center', formatter: numberformat">Total</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'pp',width:80,align:'center', formatter: numberformat">PP</th>
            <th data-options="field:'plant_1',width:80,align:'center', formatter: numberformat">Plant 1</th>
            <th data-options="field:'plant_2',width:80,align:'center', formatter: numberformat">Plant 2</th>
            <th data-options="field:'plant_3',width:80,align:'center', formatter: numberformat">Plant 3</th>
            <th data-options="field:'plant_4',width:80,align:'center', formatter: numberformat">Plant 4</th>
            <th data-options="field:'plant_5',width:80,align:'center', formatter: numberformat">Plant 5</th>
            <th data-options="field:'plant_6',width:80,align:'center', formatter: numberformat">Plant 6</th>
            <th data-options="field:'plant_7',width:80,align:'center', formatter: numberformat">Plant 7</th>
            <th data-options="field:'plant_8',width:80,align:'center', formatter: numberformat">Plant 8</th>
            <th data-options="field:'plant_9',width:80,align:'center', formatter: numberformat">Plant 9</th>
            <th data-options="field:'plant_10',width:80,align:'center', formatter: numberformat">Plant 10</th>
            <th data-options="field:'rqa',width:80,align:'center', formatter: numberformat">Repair QA</th>
            <th data-options="field:'hav',width:80,align:'center', formatter: numberformat">HAV</th>
            <th data-options="field:'created_name',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_name',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 230px; padding: 10px;">
    <fieldset style="width:40%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <legend><b>Form Filter Data</b></legend>
        <div style="width: 100%;">
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
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" name="filter_product_no" id="filter_product_no" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter('display')"><i class="fa fa-search"></i> Display Data</a>
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
                <span style="width:35%; display:inline-block;">Product ID</span>
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
            <legend><b>Quantity Stock</b></legend>
            <div style="float: left; width: 50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">PP</span>
                    <input style="width:50%;" name="pp" id="pp" required="true" class="easyui-numberbox" precision="2">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Plant 1</span>
                    <input style="width:50%;" name="plant_1" id="plant_1" required="true" class="easyui-numberbox" precision="2">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Plant 2</span>
                    <input style="width:50%;" name="plant_2" id="plant_2" required="true" class="easyui-numberbox" precision="2">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Plant 3</span>
                    <input style="width:50%;" name="plant_3" id="plant_3" required="true" class="easyui-numberbox" precision="2">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Plant 4</span>
                    <input style="width:50%;" name="plant_4" id="plant_4" required="true" class="easyui-numberbox" precision="2">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Plant 5</span>
                    <input style="width:50%;" name="plant_5" id="plant_5" required="true" class="easyui-numberbox" precision="2">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Plant 6</span>
                    <input style="width:50%;" name="plant_6" id="plant_6" required="true" class="easyui-numberbox" precision="2">
                </div>
            </div>
            <div style="float: left; width: 50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Plant 7</span>
                    <input style="width:50%;" name="plant_7" id="plant_7" required="true" class="easyui-numberbox" precision="2">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Plant 8</span>
                    <input style="width:50%;" name="plant_8" id="plant_8" required="true" class="easyui-numberbox" precision="2">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Plant 9</span>
                    <input style="width:50%;" name="plant_9" id="plant_9" required="true" class="easyui-numberbox" precision="2">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Plant 10</span>
                    <input style="width:50%;" name="plant_10" id="plant_10" required="true" class="easyui-numberbox" precision="2">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Repair QA</span>
                    <input style="width:50%;" name="rqa" id="rqa" required="true" class="easyui-numberbox" precision="2">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">HAV</span>
                    <input style="width:50%;" name="hav" id="hav" required="true" class="easyui-numberbox" precision="2">
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
<iframe id="printout" src="<?= base_url('planning/mst_wip/print') ?>" style="width: 100%;" hidden></iframe>

<div id="dlg_display" class="easyui-window" title="Display Data" data-options="closed: true,modal:true" style="width: 1200px; height: 550px;">
    <table id="dg_display" class="easyui-datagrid" style="width: 100%; height: 100%;">
        <thead frozen="true">
            <tr>
                <th data-options="field:'item_id',width:150,halign:'center'">Product No</th>
                <th data-options="field:'item_name',width:200,halign:'center'">Product Name</th>
            </tr>
        </thead>
        <thead>
            <tr>
                <th rowspan="2" data-options="field:'circuit_no',width:50,align:'center'">CCT</th>
                <th colspan="13" data-options="field:'',width:100,halign:'center'">Stock WIP</th>
                <th rowspan="2" data-options="field:'total_wip',width:80,align:'center', formatter: numberformat">Total</th>
                <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
                <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
            </tr>
            <tr>
                <th data-options="field:'pp',width:80,align:'center', formatter: numberformat">PP</th>
                <th data-options="field:'plant_1',width:80,align:'center', formatter: numberformat">Plant 1</th>
                <th data-options="field:'plant_2',width:80,align:'center', formatter: numberformat">Plant 2</th>
                <th data-options="field:'plant_3',width:80,align:'center', formatter: numberformat">Plant 3</th>
                <th data-options="field:'plant_4',width:80,align:'center', formatter: numberformat">Plant 4</th>
                <th data-options="field:'plant_5',width:80,align:'center', formatter: numberformat">Plant 5</th>
                <th data-options="field:'plant_6',width:80,align:'center', formatter: numberformat">Plant 6</th>
                <th data-options="field:'plant_7',width:80,align:'center', formatter: numberformat">Plant 7</th>
                <th data-options="field:'plant_8',width:80,align:'center', formatter: numberformat">Plant 8</th>
                <th data-options="field:'plant_9',width:80,align:'center', formatter: numberformat">Plant 9</th>
                <th data-options="field:'plant_10',width:80,align:'center', formatter: numberformat">Plant 10</th>
                <th data-options="field:'rqa',width:80,align:'center', formatter: numberformat">Repair QA</th>
                <th data-options="field:'hav',width:80,align:'center', formatter: numberformat">HAV</th>
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
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('planning/mst_wip/create') ?>';
        $('#frm_insert').form('clear');
        $("#p_year").numberbox('setValue', '<?= date("Y") ?>');
        $("#p_month").combobox('setValue', '<?= date("m") ?>');
        $("#trans_date").datebox('setValue', '<?= date("Y-m-d") ?>');
    }

    //Edit Data
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('planning/mst_wip/update') ?>?id=' + btoa(row.id);
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
                            url: '<?= base_url('planning/mst_wip/delete') ?>',
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
        window.location.assign('<?= base_url('template/tmp_wip.xls') ?>');
    }

    function filter(act='') {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');

        if(filter_month == "" || filter_year == "" || filter_revision == ""){
            toastr.info("Please Select Period and Revision");
        }else{
            var dg = 'dg';
            var filter_costomer = '';
            if (act == 'display') {
                dg += '_display';
                filter_costomer = 'all';
                $('#dlg_display').window('open');
            }
            var url = "?filter_month=" + window.btoa(filter_month) +
                "&filter_year=" + window.btoa(filter_year) +
                "&filter_revision=" + window.btoa(filter_revision) +
                "&filter_product_no=" + window.btoa(filter_product_no) +
                "&filter_costomer=" + window.btoa(filter_costomer);
            $('#'+dg).datagrid("reload", "<?= base_url('planning/mst_wip/datatables') ?>" + url);

            $("#printout").attr('src', '<?= base_url('planning/mst_wip/print') ?>' + url);
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');
        var url = "?filter_month=" + window.btoa(filter_month) + "&filter_year=" + window.btoa(filter_year) + "&filter_revision=" + window.btoa(filter_revision) + "&filter_product_no=" + window.btoa(filter_product_no);

        if(filter_month == "" || filter_year == "" || filter_revision == ""){
            toastr.info("Please Select Period and Revision");
        }else{
            $.messager.alert('Export to Excel', 'Please wait until the excel download appears');
            window.location.assign('<?= base_url('planning/mst_wip/print/excel') ?>' + url);
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
            rowStyler: function(index, row) {
                if (row.p_month == null) {
                    return 'background-color:#FFDAD2;';
                }
            }
        });
        $('#dg_display').datagrid({
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            rowStyler: function(index, row) {
                if (row.p_month == null) {
                    return 'background-color:#FFDAD2;';
                }
            }
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

        //Upload Data
        $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('planning/mst_wip/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('planning/mst_wip/upload') ?>',
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
                                url: "<?= base_url('planning/mst_wip/uploadclearFailed') ?>"
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
                                        url: "<?= base_url('planning/mst_wip/uploadCreate') ?>",
                                        data: {
                                            "data": json[number - 1]
                                        },
                                        cache: false,
                                        dataType: "json",
                                        success: function(result) {
                                            if (result.theme == "success") {
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
                                                    url: "<?= base_url('planning/mst_wip/uploadcreateFailed') ?>",
                                                    data: {
                                                        data: json[number - 1],
                                                        message: result.message
                                                    },
                                                    cache: false
                                                });

                                                requestData(total, json, number + 1, value, success + 0, failed + 1);
                                            }

                                            if (value == 100) {
                                                Swal.fire('Good job!', 'Process Save Data Completed!', 'success');
                                            }

                                            $("#p_remarks").append(title + "<br>");
                                        }
                                    });
                                }
                            }
                        }
                    });
                }
            }]
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
                    url: '<?php echo base_url('planning/mst_wip/readRevision?month='); ?>' + month + '&year=' + year,
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
                    url: '<?php echo base_url('planning/mst_wip/readRevision?month='); ?>' + month + '&year=' + year,
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

        $('#product_no').combogrid({
            url: '<?= base_url('planning/mst_data/readProducts') ?>',
            panelWidth: 400,
            idField: 'product_no',
            textField: 'product_no',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Product No",
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
            ],
            onSelect: function(index, row) {
                $("#product_name").textbox('setValue', row.product_name);
                $("#customer_code").textbox("setValue", row.cust_nick);
                $("#customer_name").textbox("setValue", row.cust_name);
                $.ajax({
                    type: "post",
                    url: "<?php echo base_url('planning/mst_data/readCircuitTotal'); ?>",
                    data: "productNo=" + row.product_no,
                    dataType: "json",
                    success: function(data) {
                        $("#circuit_no").textbox('setValue', data[0]['total_cct']);
                    }
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