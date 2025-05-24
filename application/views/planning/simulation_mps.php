<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'item_no',width:150,halign:'center'">Part No</th>
            <th rowspan="2" data-options="field:'item_name',width:200,halign:'center'">Part Name</th>
            <th rowspan="2" data-options="field:'product_family',width:150,halign:'center'">Product Family</th>
            <th rowspan="2" data-options="field:'stock_whs',width:100,halign:'center', align:'right', formatter: numberformat">Stock WHS <br> < <span id="t_cutoff"></span></th>
            <th rowspan="2" data-options="field:'os_wo',width:100,halign:'center', align:'right', formatter: numberformat">OS WO<br><span class="aug"></span></th>
            <th rowspan="2" data-options="field:'os_supply',width:100,halign:'center', align:'right', formatter: numberformat">OS Supply<br><span class="aug"></span></th>
            <th rowspan="2" data-options="field:'eta_total',width:100,halign:'center', align:'right', formatter: numberformat">ETA <br><span class="sep"></span></th>
            <th rowspan="2" data-options="field:'os_supply2',width:100,halign:'center', align:'right', formatter: numberformat">Supply<br><span class="sep"></span></th>
            <th rowspan="2" data-options="field:'need_1',width:100,halign:'center', align:'right', formatter: numberformat">Need<br><span class="sep"></span></th>
            <th rowspan="2" data-options="field:'total_need',width:100,halign:'center', align:'right', formatter: numberformat">Total Need<br><span class="sep"></span></th>
            <th rowspan="2" data-options="field:'qtyperassy',width:100,halign:'center', align:'right', formatter: numberformat">Need<br>(Breakdown)</th>
            <th rowspan="2" data-options="field:'freestock',width:100,halign:'center', align:'right', formatter: numberformat">Free Stock<br><span class="sep"></span></th>
            <th rowspan="2" data-options="field:'need_2',width:100,halign:'center', align:'right', formatter: numberformat">Need<br><span class="okt"></span></th>
            <th rowspan="2" data-options="field:'balance',width:100,halign:'center', align:'right', formatter: numberformat">Free Stock<br><span class="okt"></span></th>
            <th rowspan="2" data-options="field:'otw',width:80,halign:'center', align:'right', formatter: numberformat">OTW</th>
            <th rowspan="2" data-options="field:'eta',width:100,halign:'center', align:'center'">ETA</th>
            <th colspan="3" data-options="field:'',width:100,halign:'center'"> Approved</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'approved_to',width:80,align:'center', styler:cellStylerApproval, formatter:cellFormatterApproval">Status</th>
            <th data-options="field:'approved_by',width:80,align:'center'">By</th>
            <th data-options="field:'approved_date',width:120,align:'center'">Date</th>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 200px; padding: 10px;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width:60%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 45%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:30%;" id="filter_month"  class="easyui-combobox" value="<?= date("m") ?>" data-options="prompt:'Month'">
                    <input style="width:30%;" id="filter_year" class="easyui-combobox" value="<?= date("Y") ?>" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Revision</span>
                    <input style="width:60%;" id="filter_revision" class="easyui-combobox" value="0" data-options="prompt:'Revision'" panelHeight="auto">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                    <a href="javascript:;" class="easyui-linkbutton c2" onclick="formula()"><i class="fa fa-list"></i> Formula</a>
                </div>
            </div>
            <div style="width: 50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" id="filter_item_family" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_item_id" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Status</span>
                    <input style="width:60%;" id="filter_status" panelHeight="auto" class="easyui-combobox">
                </div>
            </div>
        </fieldset>
    </div>
    <?= $button ?>
</div>

<!-- Upload -->
<div id="dlg_upload" class="easyui-dialog" title="Upload Data" data-options="closed: true,modal:true" style="width: 500px; height: 500px; padding:10px; top: 20px;">
    <form id="frm_upload" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">File Upload</span>
                <input name="file_upload" style="width: 60%;" required="" accept=".xls" id="file_excel" class="easyui-filebox">
            </div>
        </fieldset>
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Breakdown Data</b></legend>
            <div style="float: left; width: 50%;">
                <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
                <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
            </div>
            <div style="float: left; width: 50%;">
                <div id="p_upload2" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
                <center><b id="p_start2">0</b> Of <b id="p_finish2">0</b></center>
            </div>

            
            <div id="p_remarks" class="easyui-panel" style="width:100%; height:190px; padding:10px; margin-top: 10px; overflow: auto;">
                <ul id="remarks">

                </ul>
            </div>
            <a href="javascript:;" style="float: left; color:green;" class="easyui-linkbutton" plain="true"><i class="fa fa-check"></i> SUCCESS : <b id="p_success">0</b></a>
            <a href="javascript:;" style="float: right; color:red;" class="easyui-linkbutton" plain="true" onclick="downloadFailed()"><i class="fa fa-times"></i> FAILED : <b id="p_failed">0</b></a>
        </fieldset>
    </form>
</div>

<div id="dlg-formula" class="easyui-dialog" title="Formula" style="width: 700px; padding:10px; top: 20px;" data-options="closed: true, modal:false">
    <ul>
        <li>STOCK WHS = <b>Total <= Cutoff (Old MRP)</b></li>
        <li>ETA = <b>Total Receipt Qty (Old MRP)</b></li>
        <li>OS WO = <b>Master Upload OS MPP (New ERP)</b></li>
        <li>OS SUPPLY = <b>Master Upload OS Supply (New ERP)</b></li>
        <li>NEED (Current) = <b>Total Need Breakdown Generate MPP</b></li>
        <li>NEED (M + 1) = <b>Total Need M2 Generate MRP</b></li>
        <li>FREESTOCK = <b>((Stock WHS + ETA) - (OS WO + OS SUPPLY + NEED (CURRENT MONTH) + NEED (M + 1)))</b></li>
        <li>NEED = <b>From Upload Data</b></li>
        <li>BALANCE = <b>(FREESTOCK - NEED)</b></li>
    </ul>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('planning/simulation_mps/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    function formula() {
        $("#dlg-formula").dialog('open');
    }

    //Add Data
    function add() {
        toastr.info("Not Ready this function");
    }

    //Edit Data
    function update() {
        toastr.info("Not Ready this function");
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
                            url: '<?= base_url('planning/simulation_mps/delete') ?>',
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
        window.location.assign('<?= base_url('template/tmp_simulation_mps.xls') ?>');
    }

    function filter() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_item_family = $("#filter_item_family").combobox('getValue');
        var filter_item_id = $("#filter_item_id").combogrid('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_item_family=" + window.btoa(filter_item_family) +
            "&filter_item_id=" + window.btoa(filter_item_id) +
            "&filter_status=" + window.btoa(filter_status) +
            "&filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision);

        if(filter_month == "" || filter_year == "" || filter_revision == ""){
            toastr.info("Please Select Period and Revision");
        }else{
            $('#dg').datagrid({
                url: '<?= base_url('planning/simulation_mps/datatables') ?>' + url,
                rowStyler: function(index, row) {
                    if (row.p_month == null) {
                        return 'background-color:#FFDAD2;';
                    }
                },
                onLoadSuccess: function(row){
                    console.log(row);
                    $("#t_cutoff").html(row.rows[0].cutoff);
                    $(".aug").html("("+row.period.t_ossupply_1+")");
                    $(".sep").html("("+row.period.t_ossupply_2+")");
                    $(".okt").html("("+row.period.t_ossupply_3+")");
                }
            });

            $("#printout").attr('src', '<?= base_url('planning/simulation_mps/print') ?>' + url);
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_item_family = $("#filter_item_family").combobox('getValue');
        var filter_item_id = $("#filter_item_id").combogrid('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_item_family=" + window.btoa(filter_item_family) +
            "&filter_item_id=" + window.btoa(filter_item_id) +
            "&filter_status=" + window.btoa(filter_status) +
            "&filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision);

        if(filter_month == "" || filter_year == "" || filter_revision == ""){
            toastr.info("Please Select Period and Revision");
        }else{
            window.location.assign('<?= base_url('planning/simulation_mps/print/excel') ?>' + url);
        }
    }

    function reload() {
        window.location.reload();
    }

    function scrollToBottom() {
        var container = $('#p_remarks');
        container.scrollTop(container.prop("scrollHeight"));
    }

    $(function() {
        $("#filter_month").combobox('setValue', '<?= date("m") ?>');
        $('#dg').datagrid({
            url: '<?= base_url('planning/simulation_mps/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        });

        //Upload Data
        $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('planning/simulation_mps/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('planning/simulation_mps/upload') ?>',
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
                                url: "<?= base_url('planning/simulation_mps/uploadclearFailed') ?>"
                            });

                            var json = eval('(' + result + ')');
                            requestData(json.total, json);

                            function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                                if (value < 100) {
                                    value = Math.floor((number / total) * 100);
                                    $('#p_upload').progressbar('setValue', value);
                                    $('#p_start').html(number);
                                    $('#p_finish').html(total);

                                    $("#p_remarks").append("<hr style='margin:0;'><b style='color: red;'>Assy No</b> | " + json[number - 1].product_no + "<hr style='margin:0;'>");
                                    $.ajax({
                                        type: "POST",
                                        async: true,
                                        url: "<?= base_url('planning/simulation_mps/getData') ?>",
                                        data: json[number - 1],
                                        cache: false,
                                        dataType: "json",
                                        success: function(json2) {
                                            
                                            requestData2(json2.total, json2);
                                            function requestData2(total2, json2, number2 = 1, value2 = 0, success2 = 1, failed2 = 1) {
                                                if (value2 < 100) {
                                                    value2 = Math.floor((number2 / total2) * 100);
                                                    $('#p_upload2').progressbar('setValue', value2);
                                                    $('#p_start2').html(number2);
                                                    $('#p_finish2').html(total2);

                                                    $.ajax({
                                                        type: "POST",
                                                        async: true,
                                                        url: "<?= base_url('planning/simulation_mps/create') ?>",
                                                        data: {
                                                            "data": json2[number2 - 1]
                                                        },
                                                        cache: false,
                                                        dataType: "json",
                                                        success: function(result2) {
                                                            if (result2.theme == "success") {
                                                                $('#p_success').html(success2);
                                                                var title = "<b style='color: green;'>" + result2.title + "</b> | " + result2.message;
                                                                requestData2(total2, json2, number2 + 1, value2, success2 + 1, failed2 + 0);
                                                            } else {
                                                                $('#p_failed').html(failed2);
                                                                var title = "<b style='color: red;'>" + result2.title + "</b> | " + result2.message;

                                                                //Json Failed
                                                                $.ajax({
                                                                    type: "POST",
                                                                    async: true,
                                                                    url: "<?= base_url('planning/simulation_mps/uploadcreateFailed') ?>",
                                                                    data: {
                                                                        data: json2[number2 - 1],
                                                                        message: result2.message
                                                                    },
                                                                    cache: false
                                                                });

                                                                requestData2(total2, json2, number2 + 1, value2, success2 + 0, failed2 + 1);
                                                            }

                                                            if (value2 == 100) {
                                                                // Swal.fire('Good job!', 'Process Save Data Completed!', 'success');
                                                                requestData(total, json, number + 1, value, success + 1, failed + 0);

                                                                if (value == 100) {
                                                                    $('#dlg_upload').dialog('close');
                                                                    Swal.fire('Good job!', 'Process Save Data Completed!', 'success');
                                                                    $("#dg").datagrid('reload');
                                                                }
                                                            }

                                                            $("#p_remarks").append(title + "<br>");
                                                            scrollToBottom();
                                                        }
                                                    });
                                                }
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
        });

        $('#filter_revision').combobox({
            url: '<?php echo base_url('planning/mst_data/readRevisions'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Revision',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }]
        });

        $('#filter_status').combobox({
            valueField: 'id',
            textField: 'name',
            data: [{
                id: 'OK',
                name: 'OK'
            },{
                id: 'SHORTAGE',
                name: 'SHORTAGE'
            }],
            prompt: 'Select Status',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }]
        });

        $('#filter_item_family').combobox({
            url: '<?php echo base_url('planning/simulation_mps/readItemFamily'); ?>',
            valueField: 'number',
            textField: 'name',
            prompt: 'Select Product Family',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(row, val){
                $('#filter_item_id').combogrid({
                    url: '<?= base_url('planning/simulation_mps/readProducts/') ?>' + row.number,
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
            }
        });

        $('#product_no').combogrid({
            url: '<?= base_url('planning/mst_data/readProducts') ?>',
            panelWidth: 400,
            idField: 'item_id',
            textField: 'item_id',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Product No",
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
            ],
            onSelect: function(index, row) {
                $("#product_name").textbox('setValue', row.item_name);
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

            if(value >= 0){
                return "<b>" + formatter.format(value) + "</b>";
            }else{
                return "<b style='color:red;'>" + formatter.format(value) + "</b>";
            }

        }
    }

    function cellStylerApproval(value, row, index) {
        if (row.approved_to == null) {
            return 'background: #FF5F5F; color:white;';
        } else if(row.approved_to == ""){
            return 'background: #53D636; color:white;';
        } else if(row.balance > 0){
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    function cellFormatterApproval(val, row) {
        if (row.approved_to == null) {
            return 'Not Setting';
        } else if(row.approved_to == ""){
            return 'Approved';
        } else if(row.balance > 0){
            return 'Approved';
        } else {
            return 'Checking';
        }
    };
</script>