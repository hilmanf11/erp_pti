<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'customer_name',width:250,halign:'center'">Customer</th>
            <th rowspan="2" data-options="field:'item_id',width:150,halign:'center'">Product No</th>
            <th rowspan="2" data-options="field:'item_name',width:200,halign:'center'">Product Name</th>
            <th rowspan="2" data-options="field:'circuit_no',width:50,halign:'center', align:'center'">CCT</th>
            <th rowspan="2" data-options="field:'os_mpp',width:80,halign:'center', align:'center', formatter: numberformat">OS MPP</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 200px; padding: 10px;">
    <fieldset style="width:60%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <legend><b>Form Filter Data</b></legend>
        <div style="width: 45%;">
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
        <div style="width: 50%;">
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
                <select style="width:30%;" name="p_month" id="p_month" required="true" class="easyui-combobox" data-options="prompt:'Month'">
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
                <input style="width:30%;" name="p_year" id="p_year" required="true" class="easyui-numberbox" data-options="prompt:'Year', validType: 'length[0,4]'">
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
                <input style="width:60%;" name="product_name" id="product_name" required="true" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Customer Nick</span>
                <input style="width:60%;" id="customer_nick" class="easyui-textbox" readonly>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Customer Name</span>
                <input style="width:60%;" id="customer_name" class="easyui-textbox" readonly>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Circuit No</span>
                <input style="width:30%;" name="circuit_no" id="circuit_no" readonly="true" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">OS MPP</span>
                <input style="width:30%;" name="os_mpp" id="os_mpp" required="true" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Status</span>
                <select style="width:30%;" name="status" id="status" class="easyui-combobox" panelHeight="auto">
                    <option value="OPEN" selected>OPEN</option>
                    <option value="CLOSE">CLOSE</option>
                </select>
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
<iframe id="printout" src="<?= base_url('planning/mpp_os/print') ?>" style="width: 100%;" hidden></iframe>

<div id="dlg_display" class="easyui-window" title="Display Data" data-options="closed: true,modal:true" style="width: 1200px; height: 550px;">
    <table id="dg_display" class="easyui-datagrid" style="width: 100%; height: 100%;">
        <thead>
            <tr>
                <th rowspan="2" data-options="field:'customer_name',width:250,halign:'center'">Customer</th>
                <th rowspan="2" data-options="field:'item_id',width:150,halign:'center'">Product No</th>
                <th rowspan="2" data-options="field:'item_name',width:200,halign:'center'">Product Name</th>
                <th rowspan="2" data-options="field:'circuit_no',width:50,halign:'center', align:'center'">CCT</th>
                <th rowspan="2" data-options="field:'os_mpp',width:80,halign:'center', align:'center', formatter: numberformat">OS MPP</th>
                <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
                <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
            </tr>
            <tr>
                <th data-options="field:'created_by',width:100,align:'center'"> By</th>
                <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
                <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
                <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
            </tr>
        </thead>
    </table>
</div>

<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('planning/mpp_os/create') ?>';
        $('#frm_insert').form('clear');
        $("#trans_date").datebox('setValue', '<?= date("Y-m-d") ?>');
        $("#p_year").numberbox('setValue', '<?= date("Y") ?>');
        $("#p_month").combobox('setValue', '<?= date("m") ?>');
    }

    //Edit Data
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('planning/mpp_os/update') ?>?id=' + btoa(row.id);
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
                            url: '<?= base_url('planning/mpp_os/delete') ?>',
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
        window.location.assign('<?= base_url('template/tmp_mpp_os.xls') ?>');
    }

    function filter(act='') {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_customer_id = $("#filter_customer_id").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');

        if(filter_month == "" || filter_year == "" || filter_revision == ""){
            toastr.info("Please Select Period and Revision");
        }else{
            var dg = 'dg';
            if (act == 'display') {
                dg += '_display';
                if (filter_customer_id == '') { filter_customer_id = 'all'; }
                $('#dlg_display').window('open');
            }
            var url = "?filter_customer_id=" + window.btoa(filter_customer_id) +
                "&filter_product_no=" + window.btoa(filter_product_no) +
                "&filter_month=" + window.btoa(filter_month) +
                "&filter_year=" + window.btoa(filter_year) +
                "&filter_revision=" + window.btoa(filter_revision);

            $("#"+dg).datagrid("reload", "<?= base_url('planning/mpp_os/datatables') ?>"+url);

            $("#printout").attr("src", "<?= base_url('planning/mpp_os/print') ?>"+url);
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
        var url = "?filter_customer_id=" + window.btoa(filter_customer_id) +
            "&filter_product_no=" + window.btoa(filter_product_no) +
            "&filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision);

        if(filter_month == "" || filter_year == "" || filter_revision == ""){
            toastr.info("Please Select Period and Revision");
        }else{
            window.location.assign('<?= base_url('planning/mpp_os/print/excel') ?>' + url);
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
                if (row.p_month == null) { return 'background-color:#FFDAD2;'; }
            },
        });
        $('#dg_display').datagrid({
            pagination: true,
            clientPaging: false,
            remoteFilter: false,
            rownumbers: true,
            // fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            rowStyler: function(index, row) {
                if (row.p_month == null) { return 'background-color:#FFDAD2;'; }
            },
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
                            } else {
                                toastr.error(result.message, result.title);
                            }

                            // $('#dlg_insert').dialog('close');
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
                    window.open('<?= base_url('planning/mpp_os/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('planning/mpp_os/upload') ?>',
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
                                url: "<?= base_url('planning/mpp_os/uploadclearFailed') ?>"
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
                                        url: "<?= base_url('planning/mpp_os/uploadCreate') ?>",
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
                                                    url: "<?= base_url('planning/mpp_os/uploadcreateFailed') ?>",
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
                    url: '<?php echo base_url('planning/mpp_os/readRevision?month='); ?>' + month + '&year=' + year,
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
                    url: '<?php echo base_url('planning/mpp_os/readRevision?month='); ?>' + month + '&year=' + year,
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
                $("#customer_nick").textbox('setValue', row.cust_nick);
                $("#customer_name").textbox('setValue', row.cust_name);
                $.ajax({
                    type: "post",
                    url: "<?php echo base_url('planning/mst_data/readCircuitTotal'); ?>",
                    data: "productNo=" + row.item_id,
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
        if (value == "") {
            return 0;
        } else {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 0
            });

            return "<b>" + formatter.format(value) + "</b>";
        }
    }
</script>