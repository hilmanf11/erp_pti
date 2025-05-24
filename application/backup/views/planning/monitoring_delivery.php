<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
    <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'No',width:50,halign:'center'">No</th>
            <th rowspan="2" data-options="field:'Vendor_id',width:100,halign:'center'">Vendor ID</th>
            <th rowspan="2" data-options="field:'Vendor_name',width:100,halign:'center'">Vendor Name</th>
            <th rowspan="2" data-options="field:'Product_family',width:100,halign:'center'">Product Family</th>
            <th colspan="7" data-options="field:'Purchase_order_no',width:450,halign:'center'">Purchase Order No</th>
            <th colspan="5" data-options="field:'Receipt',width:300,halign:'center'">Receipt</th>
            <th colspan="2" data-options="field:'Receipt',width:100,halign:'center'">Status</th>
            <th colspan="4" data-options="field:'Receipt',width:200,halign:'center'">Email</th>

        </tr>
        <tr>
            <th data-options="field:'#',width:50,align:'center'">Po No</th>
            <th data-options="field:'#',width:50,align:'center'">Item ID</th>
            <th data-options="field:'#',width:50,align:'center'">Item Name</th>
            <th data-options="field:'#',width:50,align:'center'">Qty</th>
            <th data-options="field:'#',width:50,align:'center'">Date</th>
            <th data-options="field:'#',width:50,align:'center'">ETD</th>
            <th data-options="field:'#',width:50,align:'center'">ETA</th>

            <th data-options="field:'#',width:50,align:'center'">Doc No</th>
            <th data-options="field:'#',width:50,align:'center'">Date</th>
            <th data-options="field:'#',width:50,align:'center'">Qty</th>
            <th data-options="field:'#',width:50,align:'center'">OS</th>
            <th data-options="field:'#',width:50,align:'center'">Status</th>

            <th data-options="field:'#',width:50,align:'center'">ETD</th>
            <th data-options="field:'#',width:50,align:'center'">ETA</th>

            <th data-options="field:'#',width:50,align:'center'">Send</th>
            <th data-options="field:'#',width:50,align:'center'">Date</th>
            <th data-options="field:'#',width:50,align:'center'">Status</th>
            <th data-options="field:'#',width:50,align:'center'">Sender</th>
           
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 275px;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Filter Data</b></legend>

        <div style="width: 50%; float:left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Period Cutoff</span>
                <input style="width:30%;" name="filter_month" id="filter_month" class="easyui-combobox" data-options="prompt:'Month'">
                <input style="width:30%;" name="filter_year" id="filter_year" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Revision</span>
                <select style="width:60%;" id="filter_revision" class="easyui-combobox" panelHeight="auto">
                    <option value="">Revision</option>
                    <option value="0">0</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Vendor</span>
                <input style="width:60%;" id="filter_vendor" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product Family</span>
                <input style="width:60%;" id="filter_product_no" class="easyui-combobox">
            </div>

            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                <a href="javascript:;" class="easyui-linkbutton" onclick=""><i class="fa fa-remove"></i> Clear Data</a>
            </div>

            </div>
            <div style="width: 45%;float:left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Item Id</span>
                <input style="width:60%;" id="filter_Item_id" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Po No</span>
                <input style="width:60%;" id="filter_po_no" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">ETD Status</span>
                <select style="width:60%;" id="filter_etd" class="easyui-combobox" panelHeight="auto">
                    <option value="">Select ETD</option>
                    <option value="All">All</option>
                    <option value="Near">Near</option>
                    <option value="On_time">On Time</option>
                    <option value="Late">Late</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">ETA Status</span>
                <select style="width:60%;" id="filter_eta" class="easyui-combobox" panelHeight="auto">
                    <option value="">Select ETA</option>
                    <option value="All">All</option>
                    <option value="Near">Near</option>
                    <option value="On_time">On Time</option>
                    <option value="Late">Late</option>
                </select>
            </div>
</div>
        </fieldset>
    </div>
    <?= $button ?>
</div>

<!-- Upload -->

<!-- Upload -->

<!-- PDF -->
<iframe id="printout" src="" style="width: 100%;" hidden></iframe>

<script>
    //Add Data
    function add() {
      
    }

    //EDIT DATA
    function uploaded_edit() {
        
    }

    function dataUploaded() {

        }

    //Delete Data
    function deleted() {
        
    }

    function filter() {
        
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        
    }

    function uploaded_excel() {
       
    }

    //Upload Data
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_sales_order_os.xls') ?>');
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        $("#filter_month").combobox('setValue', '<?= date("m") ?>');

        $('#dg').datagrid({
            url: '<?= base_url('planning/sales_order_os/datatables') ?>',
            rownumbers: true
        });

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

                            $('#dlg_insert').dialog('close');
                            $('#dg_uploaded').datagrid('reload');
                        }
                    });
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
                        url: '<?= base_url('planning/sales_order_os/upload') ?>',
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
                            $("#dlg_upload").dialog('close');
                            $.messager.progress('close');
                            //Clear File
                            $.ajax({
                                url: "<?= base_url('planning/sales_order_os/uploadclearFailed') ?>"
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
                                        url: "<?= base_url('planning/sales_order_os/uploadCreate') ?>",
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
                                                    url: "<?= base_url('planning/sales_order_os/uploadcreateFailed') ?>",
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
                    url: '<?php echo base_url('planning/sales_order_os/readRevision?month='); ?>' + month + '&year=' + year,
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
                    url: '<?php echo base_url('planning/sales_order_os/readRevision?month='); ?>' + month + '&year=' + year,
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
            idField: 'item_id',
            textField: 'item_id',
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
                    field: 'item_id',
                    title: 'Item ID',
                    width: 200
                }, {
                    field: 'item_name',
                    title: 'Item Name',
                    width: 200
                }]
            ]
        });

        $('#filter_customer_id').combobox({
            url: '<?php echo base_url('planning/mst_data/readCustomers'); ?>',
            valueField: 'number',
            textField: 'name',
            prompt: 'Select Customer',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }]
        });

        $('#customer_id').combobox({
            url: '<?php echo base_url('planning/mst_data/readCustomers'); ?>',
            valueField: 'number',
            textField: 'name',
            prompt: 'Select Customer',
            onSelect: function(row) {
                $("#customer_name").textbox('setValue', row.name);
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

    function cellStyler(value, row, index) {
        if (value == "OPEN") {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }
</script>