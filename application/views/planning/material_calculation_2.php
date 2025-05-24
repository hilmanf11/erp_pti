<table id="dg" class="easyui-datagrid" style="width:99.5%; height: 850px;" toolbar="#toolbar">
    <thead data-options="frozen:true">
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'product_no',width:150,halign:'center'">Part No</th>
            <th data-options="field:'product_name',width:120,halign:'center'">Part Name</th>
        </tr>
    </thead>
    <thead>
        <tr>
            <th rowspan="2" data-options="field:'product_family_name',width:120,halign:'center'">Product Family</th>
            <th rowspan="2" data-options="field:'stock_whs',width:80,halign:'center',align:'right',styler:cellStyler, formatter:numberformat">Stock WHS</th>
            <th rowspan="2" data-options="field:'os_supply',width:80,halign:'center',align:'right',styler:cellStyler, formatter:numberformat">OS Supply</th>
            <th rowspan="2" data-options="field:'os_wo',width:80,halign:'center',align:'right',styler:cellStyler, formatter:numberformat">OS WO</th>
            <th rowspan="2" data-options="field:'stock_hand',width:80,halign:'center',align:'right',styler:cellStyler, formatter:numberformat">Onhand<br>Stock</th>
            <th rowspan="2" data-options="field:'eta',width:80,halign:'center',align:'right',styler:cellStyler, formatter:numberformat">ETA</th>
            <th rowspan="2" data-options="field:'total',width:100,halign:'center',align:'right',styler:cellStyler, formatter:BtnBalance">Total Need</th>
            <th rowspan="2" data-options="field:'total_bal',width:80,halign:'center',align:'right',styler:cellStyler, formatter:numberformat">Total Bal</th>
            <?php 
                if($this->input->get('filter_month')){
                    $filter_month = base64_decode($this->input->get('filter_month'));
                    $filter_year = base64_decode($this->input->get('filter_year'));
                    $filter_revision = base64_decode($this->input->get('filter_revision'));
                    $filter_item_family = base64_decode($this->input->get('filter_item_family'));
                    $filter_item_id = base64_decode($this->input->get('filter_item_id'));
                    $filter_status = $this->input->get('filter_status');
                    $filter_date = base64_decode($this->input->get('filter_date'));
                }else{
                    $filter_month = date("m");
                    $filter_year = date("Y");
                    $filter_revision = "0";
                    $filter_item_family = "";
                    $filter_item_id = "";
                    $filter_status = "";
                    $filter_date = "";
                }

                $next_month = date("F", strtotime("+1 month", strtotime($filter_year."-".$filter_month."-01")));
                $firstDate = date("Y-m-01", strtotime(date("$filter_year-$filter_month-01")));
                $endDate = date("Y-m-t", strtotime(date("$filter_year-$filter_month")));
                while (strtotime($firstDate) <= strtotime($endDate)) {
                    $working_date = date('d M', strtotime($firstDate));

                    if (date('w', strtotime($firstDate)) !== '0' && date('w', strtotime($firstDate)) !== '6') {
                        $hstyle = "";
                    }else{
                        $hstyle = "background-color:#FFB0B0";
                    }
            ?>
            <th colspan="4" data-options="field:'period',width:50,halign:'center',align:'right',hstyler: function(value, row, index) { return '<?= $hstyle ?>'; }"><?= $working_date ?></th>
            <?php
                    $firstDate = date("Y-m-d", strtotime("+1 day", strtotime($firstDate)));
                }
            ?>
            <th rowspan="2" data-options="field:'free_stock',width:80,halign:'center',align:'right',styler:cellStyler, formatter:numberformat">Free<br>Stock</th>
            <th colspan="8" data-options="field:'next_month',width:80,halign:'center'"><?= $next_month ?></th>
            <th rowspan="2" data-options="field:'balance_wip',width:80,halign:'center',align:'right',styler:cellStyler, formatter:numberformat">Balance<br>WIP</th>
            <th rowspan="2" data-options="field:'os_po',width:100,halign:'center',align:'right',styler:cellStyler, formatter:BtnSoPo">OS PO</th>
        </tr>
        <tr>
            <?php 
                $firstDate = date("Y-m-01", strtotime(date("$filter_year-$filter_month-01")));
                $endDate = date("Y-m-t", strtotime(date("$filter_year-$filter_month")));
                $tgl = 1;
                while (strtotime($firstDate) <= strtotime($endDate)) {
                    $working_date = date('d M', strtotime($firstDate));

                    if (date('w', strtotime($firstDate)) !== '0' && date('w', strtotime($firstDate)) !== '6') {
                        $hstyle = "";
                    }else{
                        $hstyle = "background-color:#FFB0B0";
                    }
            ?>
            <th data-options="field:'eta_<?= $tgl ?>',width:80,halign:'center',align:'right',styler:cellStyler, formatter:numberformat, hstyler: function(value, row, index) { return '<?= $hstyle ?>'; }">ETA</th>
            <th data-options="field:'date_<?= $tgl ?>',width:80,halign:'center',align:'right',styler:cellStyler, formatter:numberformat, hstyler: function(value, row, index) { return '<?= $hstyle ?>'; }">Need</th>
            <th data-options="field:'supply_<?= $tgl ?>',width:80,halign:'center',align:'right',styler:cellStyler, formatter:numberformat, hstyler: function(value, row, index) { return '<?= $hstyle ?>'; }">Supply</th>
            <th data-options="field:'bal_<?= $tgl ?>',width:80,halign:'center',align:'right', styler:cellStyler, formatter:numberformat, hstyler: function(value, row, index) { return '<?= $hstyle ?>'; }">Balance</th>
            <?php
                    $tgl++;
                    $firstDate = date("Y-m-d", strtotime("+1 day", strtotime($firstDate)));
                }
            ?>
            <th data-options="field:'next_month_need1',width:80,halign:'center',align:'right',styler:cellStyler, formatter:numberformat">Week 1</th>
            <th data-options="field:'next_month_bal1',width:80,halign:'center',align:'right',styler:cellStyler, formatter:numberformat">Balance</th>
            <th data-options="field:'next_month_need2',width:80,halign:'center',align:'right',styler:cellStyler, formatter:numberformat">Week 2</th>
            <th data-options="field:'next_month_bal2',width:80,halign:'center',align:'right',styler:cellStyler, formatter:numberformat">Balance</th>
            <th data-options="field:'next_month_need3',width:80,halign:'center',align:'right',styler:cellStyler, formatter:numberformat">Week 3</th>
            <th data-options="field:'next_month_bal3',width:80,halign:'center',align:'right',styler:cellStyler, formatter:numberformat">Balance</th>
            <th data-options="field:'next_month_need4',width:80,halign:'center',align:'right',styler:cellStyler, formatter:numberformat">Week 4</th>
            <th data-options="field:'next_month_bal4',width:80,halign:'center',align:'right',styler:cellStyler, formatter:numberformat">Balance</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="padding:10px;">
    <!-- <div class="easyui-accordion" style="width:100%; margin-bottom:5px;"> -->
        <!-- <div title="Hide Filter" style="padding:10px; background:#F4F4F4;"> -->
            <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
                <fieldset style="width: 60%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
                    <legend><b>Form Filter Data</b></legend>
                    <div style="width: 50%; float: left;">
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Period</span>
                            <input style="width:30%;" name="filter_month" id="filter_month" value="<?= $filter_month ?>" class="easyui-combobox" data-options="prompt:'Month'">
                            <input style="width:30%;" name="filter_year" id="filter_year" value="<?= $filter_year ?>" class="easyui-combobox" data-options="prompt:'Year'">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Revision</span>
                            <input style="width:60%;" name="filter_revision" id="filter_revision" value="<?= $filter_revision ?>" class="easyui-combobox" data-options="prompt:'Revision'" panelHeight="auto">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Inventory Cutoff</span>
                            <input style="width:28%;" id="filter_date" class="easyui-datebox" value="<?= $filter_date ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;"></span>
                            <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                        </div>
                    </div>
                    <div style="width: 50%; float: left;">
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Product Family</span>
                            <input style="width:60%;" id="filter_item_family" value="<?= $filter_item_family ?>" class="easyui-combobox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Product No</span>
                            <input style="width:60%;" id="filter_item_id" value="<?= $filter_item_id ?>" class="easyui-combogrid">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Status</span>
                            <input style="width:60%;" id="filter_status" value="<?= $filter_status ?>" panelHeight="auto" class="easyui-combobox">
                        </div>
                    </div>
                </fieldset>

                <fieldset style="width: 35%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
                    <legend><b>Result Generate</b></legend>
                    <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
                    <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
                    <div id="p_remarks" class="easyui-panel" style="width:100%; height:80px; padding:10px; margin-top: 10px; overflow: auto;">
                        <ul id="remarks">

                        </ul>
                    </div>
                    <a href="javascript:;" style="float: left; color:green;" class="easyui-linkbutton" plain="true"><i class="fa fa-check"></i> SUCCESS : <b id="p_success">0</b></a>
                    <a href="javascript:;" style="float: right; color:red;" class="easyui-linkbutton" plain="true" onclick="downloadFailed()"><i class="fa fa-times"></i> FAILED : <b id="p_failed">0</b></a>
                </fieldset>
                
            </div>
            <?= $button ?>
            <!-- <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="material()"><i class="fa fa-file"></i> Material Shortage</a> -->
        <!-- </div>
    </div> -->
</div>

<div id="dlg_ospo" class="easyui-dialog" title="Outstanding Purchase Order" data-options="closed: true,modal:true" style="width: 60%; height: 500px; top: 0px;">
    <table id="dg_ospo" class="easyui-datagrid" style="width:100%;" showFooter="true">
        <thead>
            <tr>
                <th data-options="field:'item_id',width:150,halign:'center'">Part Id</th>
                <th data-options="field:'poc_id',width:200,halign:'center'">PO No</th>
                <th data-options="field:'poc_reqdate',width:100,halign:'center'">PO Date</th>
                <th data-options="field:'request',width:80,halign:'center',align:'right',formatter:numberformat">Request</th>
                <th data-options="field:'receive',width:80,halign:'center',align:'right',formatter:numberformat">Receipt</th>
                <th data-options="field:'balance',width:80,halign:'center',align:'right',formatter:numberformat">Total</th>
                <!-- <th data-options="field:'por_pocreqdate',width:100,align:'center'">ETA</th> -->
                <th data-options="field:'document_no',width:100,align:'center'">Document No</th>
            </tr>
        </thead>
    </table>
</div>

<div id="dlg_balance" class="easyui-dialog" title="Total Need" data-options="closed: true,modal:true" style="width: 40%; height: 500px; top: 0px;">
    <table id="dg_balance" class="easyui-datagrid" style="width:100%;" showFooter="true">
        <thead>
            <tr>
                <th data-options="field:'product_no',width:150,halign:'center'">Product No</th>
                <th data-options="field:'customer_name',width:250,halign:'center'">Customer Name</th>
                <th data-options="field:'total',width:100,halign:'center',align:'right',formatter:numberformat">Qty</th>
            </tr>
        </thead>
    </table>
</div>

<iframe id="printout" src="" style="width: 100%; height:95%; border: 0;" hidden></iframe>
<script>
     //Add Data
    function add() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_date = $("#filter_date").datebox('getValue');

        if (filter_month == "" || filter_year == "" || filter_revision == "") {
            toastr.warning("Please select filter month, year and revision", "Information");
        }else{
            Swal.fire({
                title: 'Please Wait for Generating Data',
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            $.ajax({
                type: "get",
                url: "<?= base_url('planning/material_calculation_2/getdata') ?>",
                data: "filter_month=" + window.btoa(filter_month) +
                    "&filter_year=" + window.btoa(filter_year) +
                    "&filter_revision=" + window.btoa(filter_revision) +
                    "&filter_date=" + window.btoa(filter_date),
                dataType: "json",
                success: function(rows) {
                    Swal.close();

                    if(rows.length > 0){
                        requestData(rows.length, rows);
                    }else{
                        Swal.fire('Not Found!', 'Data MPP not found!', 'error');
                    }

                    function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                        if (value < 100) {
                            value = Math.floor((number / total) * 100);
                            $('#p_upload').progressbar('setValue', value);
                            $('#p_start').html(number);
                            $('#p_finish').html(total);

                            $.post('<?= base_url('planning/material_calculation_2/create') ?>', {
                                data: json[number - 1],
                                cutoff: filter_date,
                            }, function(note) {
                                var result = eval('(' + note + ')');
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
                                        url: "<?= base_url('planning/material_calculation_2/uploadcreateFailed') ?>",
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
                            }).fail(function(jqXHR, textStatus) {
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
                            });
                        }
                    }
                }
            });
        }
    }

    function filter() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_item_family = $("#filter_item_family").combobox('getValue');
        var filter_item_id = $("#filter_item_id").combogrid('getValue');
        var filter_status = $("#filter_status").combobox('getValue');
        var filter_date = $("#filter_date").datebox('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_item_family=" + window.btoa(filter_item_family) +
            "&filter_item_id=" + window.btoa(filter_item_id) +
            "&filter_date=" + window.btoa(filter_date) +
            "&filter_status=" + filter_status;

        if (filter_month == "" || filter_year == "" || filter_revision == "" || filter_item_family == "") {
            toastr.warning("Please select Period, Revision and Product Family!", "Information");
        } else {
            window.location.assign(url);
        }
    }

    function pdf() {
        // $("#printout").get(0).contentWindow.print();
        toastr.info("Not Ready to This Function");
    }

    function excel() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_item_family = $("#filter_item_family").combobox('getValue');
        var filter_item_id = $("#filter_item_id").combogrid('getValue');
        var filter_status = $("#filter_status").combobox('getValue');
        var filter_date = $("#filter_date").datebox('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_item_family=" + window.btoa(filter_item_family) +
            "&filter_item_id=" + window.btoa(filter_item_id) +
            "&filter_date=" + window.btoa(filter_date) +
            "&filter_status=" + filter_status;

        if (filter_month == "" || filter_year == "" || filter_revision == "" || filter_item_family == "") {
            toastr.warning("Please select Period, Revision and Product Family!", "Information");
        } else {
            $.messager.alert('Info','Please Wait to Export to Excel');
            window.location.assign('<?= base_url('planning/material_calculation_2/print/excel') ?>' + url);
        }
    }

    function material(){
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_item_family = $("#filter_item_family").combobox('getValue');
        var filter_item_id = $("#filter_item_id").combogrid('getValue');
        var filter_status = $("#filter_status").combobox('getValue');
        var filter_date = $("#filter_date").datebox('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_item_family=" + window.btoa(filter_item_family) +
            "&filter_item_id=" + window.btoa(filter_item_id) +
            "&filter_date=" + window.btoa(filter_date) +
            "&filter_status=" + window.btoa(filter_status);

        if (filter_month == "" || filter_year == "" || filter_revision == "" || filter_item_family == "") {
            toastr.warning("Please select Period, Revision and Product Family!", "Information");
        } else {
            $.messager.alert('Info','Please Wait to Export to Excel');
            window.location.assign('<?= base_url('planning/material_calculation_2/printMaterialShortage/excel') ?>' + url);
        }
    }

    function reload() {
        window.location.reload();
    }

    function cutoffDate(month, year){
        $.ajax({
            type: "post",
            url: "<?= base_url('planning/material_calculation_2/cutoffDate') ?>",
            data: "month=" + month + "&year=" + year,
            dataType: "html",
            success: function (response) {
                $("#filter_date").datebox('setValue', response);
            }
        });
    }

    $(function() {
        $("#add").html('Generate');

        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_item_family = $("#filter_item_family").combobox('getValue');
        var filter_item_id = $("#filter_item_id").combogrid('getValue');
        var filter_status = $("#filter_status").combobox('getValue');
        var filter_date = $("#filter_date").datebox('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_item_family=" + window.btoa(filter_item_family) +
            "&filter_item_id=" + window.btoa(filter_item_id) +
            "&filter_date=" + window.btoa(filter_date) +
            "&filter_status=" + filter_status;

        if (filter_month != "" && filter_year != "" && filter_revision != "") {
            $('#dg').datagrid({
                url: '<?= base_url('planning/material_calculation_2/datatables') ?>' + url,
                pagination: false,
                rownumbers: true,
                singleSelect: true,
                // pageList: [20, 50, 100, 500, 1000],
                // pageSize: 20,
            });

            // $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            // $("#printout").attr('src', '<?= base_url('planning/material_calculation_2/print') ?>' + url);
        }

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
            onSelect: function(row){
                var year = $('#filter_year').combobox('getValue');
                cutoffDate(row.id, year);
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
            onSelect: function(row){
                var month = $('#filter_month').combobox('getValue');
                cutoffDate(month, row.id);
            }
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
            url: '<?php echo base_url('planning/material_calculation_2/readItemFamily'); ?>',
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
                    url: '<?= base_url('planning/material_calculation_2/readProducts/') ?>' + row.number,
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
    });

    function printOsPo(product_no){
        $("#dlg_ospo").dialog('open');

        $('#dg_ospo').datagrid({
            url: '<?= base_url('planning/material_calculation_2/printOsPo') ?>?product_no=' + btoa(product_no),
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            singleSelect: false,
            fit:true,
        });
    }

    function printBalance(product_no){
        $("#dlg_balance").dialog('open');

        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');

        $('#dg_balance').datagrid({
            url: '<?= base_url('planning/material_calculation_2/printBalance') ?>?product_no=' + btoa(product_no) + '&filter_month=' + btoa(filter_month) + "&filter_year=" + btoa(filter_year) + "&filter_revision=" + btoa(filter_revision),
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            singleSelect: false,
            fit:true,
        });
    }

    function BtnSoPo(val, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0
        });

        var os_po = formatter.format(val);
        var details = "printOsPo('"+row.product_no+"')";
        return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" onClick="'+details+'">'+os_po+'</a>';
    }

    function BtnBalance(val, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0
        });

        var balance = formatter.format(val);
        var details = "printBalance('"+row.product_no+"')";
        return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" onClick="'+details+'">'+balance+'</a>';
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

    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0
        });

        return "<b>" + formatter.format(value) + "</b>";
    }

    function cellStyler(value, row, index) {
        if (value >= 0) {
            return 'font-weight:bold;';
        } else {
            return 'color:#FF5F5F; font-weight:bold;';
        }
    }
</script>