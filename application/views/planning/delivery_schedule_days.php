<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar" data-options="fit: true">
    <thead data-options="frozen:true">
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'part_no',width:150,halign:'center'">Part No</th>
            <th data-options="field:'part_name',width:120,halign:'center'">Part Name</th>
        </tr>
    </thead>
    <thead>
        <tr>
            <th rowspan="2" data-options="field:'po_no',width:180,halign:'center'">PO No</th>
            <th rowspan="2" data-options="field:'product_family_name',width:120,halign:'center'">Product Family</th>
            <th rowspan="2" data-options="field:'supplier_name',width:200,halign:'center'">Supplier</th>
            <th rowspan="2"data-options="field:'moq',width:80,halign:'center',align:'right', formatter:numberformat">MPQ</th>
            <th rowspan="2"data-options="field:'moq',width:80,halign:'center',align:'right', formatter:numberformat">MOQ</th>
            <th rowspan="2"data-options="field:'leadtime',width:80,halign:'center',align:'right', formatter:numberformat">Leadtime</th>
            <th rowspan="2"data-options="field:'qty',width:80,halign:'center',align:'right', formatter:numberformat">Qty PO</th>
            <th rowspan="2" data-options="field:'qty_bf',width:80,halign:'center',align:'right', styler:cellStyler, formatter:numberformat">OS Delivery</th>
            <?php 
                if($this->input->get('filter_month')){
                    $filter_month = base64_decode($this->input->get('filter_month'));
                    $filter_year = base64_decode($this->input->get('filter_year'));
                    $filter_revision = base64_decode($this->input->get('filter_revision'));
                    $filter_item_family = base64_decode($this->input->get('filter_item_family'));
                    $filter_supplier = base64_decode($this->input->get('filter_supplier'));
                    $filter_item_id = base64_decode($this->input->get('filter_item_id'));
                }else{
                    $filter_month = date("m");
                    $filter_year = date("Y");
                    $filter_revision = "0";
                    $filter_item_family = "";
                    $filter_supplier = "";
                    $filter_item_id = "";
                }

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
            <th colspan="3" data-options="field:'period',width:80,halign:'center',align:'right',hstyler: function(value, row, index) { return '<?= $hstyle ?>'; }"><?= $working_date ?></th>
            <?php
                    $firstDate = date("Y-m-d", strtotime("+1 day", strtotime($firstDate)));
                }
            ?>
            <th colspan="2" data-options="field:'',width:100,align:'center'">Created</th>
            <th colspan="2" data-options="field:'',width:100,align:'center'">Updated</th>
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
            <th data-options="field:'plan_<?= $tgl ?>',width:80,halign:'center',align:'right',hstyler: function(value, row, index) { return '<?= $hstyle ?>'; }">Plan</th>
            <th data-options="field:'del_<?= $tgl ?>',width:80,halign:'center',align:'right',hstyler: function(value, row, index) { return '<?= $hstyle ?>'; }">Actual</th>
            <th data-options="field:'os_<?= $tgl ?>',width:80,halign:'center',align:'right', styler:cellStyler, hstyler: function(value, row, index) { return '<?= $hstyle ?>'; }">OS</th>
            <?php
                    $tgl++;
                    $firstDate = date("Y-m-d", strtotime("+1 day", strtotime($firstDate)));
                }
            ?>
            <th data-options="field:'created_by',width:100,halign:'center'">By</th>
            <th data-options="field:'created_date',width:150,halign:'center'">Date</th>
            <th data-options="field:'updated_by',width:100,halign:'center'">By</th>
            <th data-options="field:'updated_date',width:150,halign:'center'">Date</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 200px; padding: 10px;">
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
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier</span>
                    <input style="width:60%;" id="filter_supplier" value="<?= $filter_supplier ?>" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" id="filter_item_family" value="<?= $filter_item_family ?>" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_item_id" value="<?= $filter_item_id ?>" class="easyui-combogrid">
                </div>
            </div>
        </fieldset>

        <fieldset style="width: 35%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Result Generate</b></legend>
            <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
            <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
            <div id="p_remarks" class="easyui-panel" style="width:100%; height:80px; padding:10px; margin-top: 10px; overflow: auto;" hidden>
                <ul id="remarks">

                </ul>
            </div>
            <a href="javascript:;" style="float: left; color:green;" class="easyui-linkbutton" plain="true"><i class="fa fa-check"></i> SUCCESS : <b id="p_success">0</b></a>
            <a href="javascript:;" style="float: right; color:red;" class="easyui-linkbutton" plain="true" onclick="downloadFailed()"><i class="fa fa-times"></i> FAILED : <b id="p_failed">0</b></a>
        </fieldset>
    </div>
    <?= $button ?>
</div>

<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 99%; height: 500px; padding:10px; top: 20px; left:10px;">
    <form id="frm_insert" method="post" novalidate>
        <div style="float: left; width: 55%;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                <legend><b>Form Data</b></legend>
                <div style="float: left; width: 50%;">
                    <div class="fitem" hidden>
                        <span style="width:30%; display:inline-block;">ID</span>
                        <input style="width:60%;" name="id" id="id" required="" disabled class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:30%; display:inline-block;">Part No</span>
                        <input style="width:60%;" name="part_no" id="part_no" disabled class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:30%; display:inline-block;">Product Family</span>
                        <input style="width:60%;" name="product_family_name" id="product_family_name" disabled class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:30%; display:inline-block;"></span>
                        <a href="javascript:;" class="easyui-linkbutton" onclick="saveData()"><i class="fa fa-save"></i> Save Data</a>
                    </div>
                </div>
                <div style="float: left; width: 50%;">
                    <div class="fitem">
                        <span style="width:30%; display:inline-block;">Supplier</span>
                        <input style="width:60%;" name="supplier_name" id="supplier_name" disabled class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:30%; display:inline-block;">MPQ / MOQ</span>
                        <input style="width:30%;" name="mpq" id="mpq" disabled class="easyui-textbox">
                        <input style="width:30%;" name="moq" id="moq" disabled class="easyui-textbox">
                    </div>
                </div>
            </fieldset>
        </div>
        <div style="float: left; width: 15%;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left; text-align: center; padding-top: 38px; padding-bottom: 38px;">
                <legend><b>Qty PO</b></legend>
                <h1 style="font-size: 30px !important; font-weight: bold;" id="qtyPo">0</h1>
            </fieldset>
        </div>
        <div style="float: left; width: 15%;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left; text-align: center; padding-top: 38px; padding-bottom: 38px;">
                <legend><b>Total Plan</b></legend>
                <h1 style="font-size: 30px !important; font-weight: bold;" id="totalPlan">0</h1>
            </fieldset>
        </div>
        <div style="float: left; width: 15%;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left; text-align: center; padding-top: 38px; padding-bottom: 38px;">
                <legend><b>Total Actual</b></legend>
                <h1 style="font-size: 30px !important; font-weight: bold;" id="totalActual">0</h1>
            </fieldset>
        </div>
        <div id="calendar">

        </div>
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
</div>

<iframe id="printout" src="" style="width: 100%; height:90%; border: 0;" hidden></iframe>

<script>
    function planTotal(val, id){
        if(val != ""){
            var mpq = $("#mpq").numberbox('getValue');
            if(parseInt(val) > 0){
                var roundMpq = (Math.ceil(parseInt(val) / mpq) * mpq);
            }else{
                var roundMpq = 0;
            }

            $("#plan_" + id).val(roundMpq);

            var total = 0;
            $('.plan').each(function() {
                total += parseInt($(this).val());
            });

            $("#totalPlan").html(total);
        }else{
            toastr.info("Please Input Numberic");
        }
    }

    function delTotal(val, id){
        if(val != ""){
            var mpq = $("#mpq").numberbox('getValue');
            if(parseInt(val) > 0){
                var roundMpq = (Math.ceil(parseInt(val) / mpq) * mpq);
            }else{
                var roundMpq = 0;
            }

            $("#del_" + id).val(roundMpq);

            var total = 0;
            $('.del').each(function() {
                total += parseInt($(this).val());
            });

            $("#totalActual").html(total);
        }else{
            toastr.info("Please Input Numberic");
        }
    }

     //Add Data
    function add() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');

        if (filter_month == "" || filter_year == "" || filter_revision == "") {
            toastr.warning("Please select filter month, year and revision", "Information");
        }else{
            $.messager.prompt('Generate Delivery Schedule', 'Please input Password Generate', function(r){
                if (r == "DELIVERYSCHEDULE"){
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
                        url: "<?= base_url('planning/delivery_schedule_days/getdata') ?>",
                        data: "filter_month=" + window.btoa(filter_month) +
                            "&filter_year=" + window.btoa(filter_year) +
                            "&filter_revision=" + window.btoa(filter_revision),
                        dataType: "json",
                        success: function(rows) {
                            Swal.close();

                            if(rows.total > 0){
                                requestData(rows.total, rows);
                            }else{
                                Swal.fire('Not Found!', 'Data MPP not found!', 'error');
                            }

                            function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                                if (value < 100) {
                                    value = Math.floor((number / total) * 100);
                                    $('#p_upload').progressbar('setValue', value);
                                    $('#p_start').html(number);
                                    $('#p_finish').html(total);

                                    $.post('<?= base_url('planning/delivery_schedule_days/create') ?>', {
                                        data: json[number - 1],
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
                                                url: "<?= base_url('planning/delivery_schedule_days/uploadcreateFailed') ?>",
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
            });

            $('.messager-input').attr('type', 'password');
        }
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            $('#qtyPo').html(row.qty);
            
            $.ajax({
                type: "post",
                url: "<?= base_url('planning/delivery_schedule_days/calendars') ?>",
                data: "p_month=" + row.p_month + "&p_year=" + row.p_year + "&id=" + row.id,
                dataType: "html",
                success: function(response) {
                    $("#calendar").html(response);

                    var totalPlan = 0;
                    $('.plan').each(function() {
                        totalPlan += parseInt($(this).val());
                    });

                    var totalActual = 0;
                    $('.del').each(function() {
                        totalActual += parseInt($(this).val());
                    });

                    $("#totalPlan").html(totalPlan);
                    $("#totalActual").html(totalActual);


                    // setInterval(planTotal, 2000);
                    // setInterval(delTotal, 2000);
                }
            });

            url_save = '<?= base_url('planning/delivery_schedule_days/update') ?>?id=' + btoa(row.id);
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function saveData() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');

        $('#frm_insert').form('submit', {
            url: url_save,
            method: 'POST',
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
                $('#dg').datagrid('reload');
            }
        });
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
                            url: '<?= base_url('planning/delivery_schedule_days/delete') ?>',
                            data: {
                                product_no: row.product_no,
                                p_month: row.p_month,
                                p_year: row.p_year,
                                revision: row.revision
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

    function filter() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_supplier = $("#filter_supplier").combobox('getValue');
        var filter_item_family = $("#filter_item_family").combobox('getValue');
        var filter_item_id = $("#filter_item_id").combogrid('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_item_family=" + window.btoa(filter_item_family) +
            "&filter_item_id=" + window.btoa(filter_item_id);

        if (filter_month == "" || filter_year == "" || filter_revision == "" || filter_item_family == "" && filter_supplier == "") {
                toastr.warning("Please select Period, Revision Supplier or Product Family!", "Information");
        } else {
            window.location.assign(url);
            // $('#dg').datagrid({
            //     rownumbers: true,
            //     url: '<?= base_url('planning/delivery_schedule_days/datatables') ?>' + url
            // });

            // $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            // $("#printout").attr('src', '<?= base_url('planning/delivery_schedule_days/print') ?>' + url);
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_supplier = $("#filter_supplier").combobox('getValue');
        var filter_item_family = $("#filter_item_family").combobox('getValue');
        var filter_item_id = $("#filter_item_id").combogrid('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_item_family=" + window.btoa(filter_item_family) +
            "&filter_item_id=" + window.btoa(filter_item_id);

        if (filter_month == "" || filter_year == "" || filter_revision == "" || filter_item_family == "" && filter_supplier == "") {
            toastr.warning("Please select Period, Revision Supplier or Product Family!", "Information");
        } else {
            $.messager.alert('Info','Please Wait to Export to Excel');
            window.location.assign('<?= base_url('planning/delivery_schedule_days/print/excel') ?>' + url);
        }
    }

    //Upload Data
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_delivery_schedule_days.xls') ?>');
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        $("#add").html('Generate');

        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_supplier = $("#filter_supplier").combobox('getValue');
        var filter_item_family = $("#filter_item_family").combobox('getValue');
        var filter_item_id = $("#filter_item_id").combogrid('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_item_family=" + window.btoa(filter_item_family) +
            "&filter_item_id=" + window.btoa(filter_item_id);

        if (filter_month == "" || filter_year == "" || filter_revision == "" || filter_item_family == "" && filter_supplier == "") {
            toastr.warning("Please select Period, Revision Supplier or Product Family!", "Information");
        }else{
            $('#dg').datagrid({
                url: '<?= base_url('planning/delivery_schedule_days/datatables') ?>' + url,
                pagination: true,
                rownumbers: true,
                singleSelect: true,
                fit: true,
                pageList: [20, 50, 100, 500, 1000],
                pageSize: 20,
            });

            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('planning/delivery_schedule_days/print') ?>' + url);
        }

        //Upload Data
        $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('planning/delivery_schedule_days/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('planning/delivery_schedule_days/upload') ?>',
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
                            $('#dlg_upload').dialog('close');
                            //Clear File
                            $.ajax({
                                url: "<?= base_url('planning/delivery_schedule_days/uploadclearFailed') ?>"
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
                                        url: "<?= base_url('planning/delivery_schedule_days/uploadCreate') ?>",
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
                                                    url: "<?= base_url('planning/delivery_schedule_days/uploadcreateFailed') ?>",
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

        $('#filter_supplier').combobox({
            url: '<?php echo base_url('planning/purchase_request/readSupplier'); ?>',
            valueField: 'supplier_id',
            textField: 'supplier_name',
            prompt: 'Select Supplier',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });


        $('#filter_item_family').combobox({
            url: '<?php echo base_url('planning/delivery_schedule_days/readItemFamily'); ?>',
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
                    url: '<?= base_url('planning/delivery_schedule_days/readProducts/') ?>' + row.number,
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