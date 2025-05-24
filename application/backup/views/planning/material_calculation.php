<div id="f" class="easyui-panel" style="width:100%; background: #F4F4F4; padding: 10px;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 60%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:30%;" name="filter_month" id="filter_month" value="<?= date("m") ?>" class="easyui-combobox" data-options="prompt:'Month'">
                    <input style="width:30%;" name="filter_year" id="filter_year" value="<?= date("Y") ?>" class="easyui-combobox" data-options="prompt:'Year'">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Revision</span>
                    <input style="width:60%;" name="filter_revision" id="filter_revision" value="<?= "0" ?>" class="easyui-combobox" data-options="prompt:'Revision'" panelHeight="auto">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Inventory Cutoff</span>
                    <input style="width:28%;" id="filter_date" class="easyui-datebox" value="<?= date("Y-m-d") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
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
                    <select style="width:60%;" id="filter_status" panelHeight="auto" class="easyui-combobox">
                        <option value="">Choose All</option>
                        <option value="OK">OK</option>
                        <option value="SHORTAGE">SHORTAGE</option>
                    </select>
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
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="material()"><i class="fa fa-file"></i> Material Shortage</a>
</div>

<div id="p" class="easyui-panel" title="Print Preview" style="width:100%;">
    <iframe id="printout" src="" style="width: 100%; height: 430px; border: 0;"></iframe>
</div>
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
                url: "<?= base_url('planning/material_calculation/getdata') ?>",
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

                            $.post('<?= base_url('planning/material_calculation/create') ?>', {
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
                                        url: "<?= base_url('planning/material_calculation/uploadcreateFailed') ?>",
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
            "&filter_status=" + window.btoa(filter_status);

        if (filter_month == "" || filter_year == "" || filter_revision == "" || filter_item_family == "") {
            toastr.warning("Please select Period, Revision and Product Family!", "Information");
        } else {
            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('planning/material_calculation/print') ?>' + url);
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
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
            "&filter_status=" + window.btoa(filter_status);

        if (filter_month == "" || filter_year == "" || filter_revision == "" || filter_item_family == "") {
            toastr.warning("Please select Period, Revision and Product Family!", "Information");
        } else {
            $.messager.alert('Info','Please Wait to Export to Excel');
            window.location.assign('<?= base_url('planning/material_calculation/print/excel') ?>' + url);
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
            window.location.assign('<?= base_url('planning/material_calculation/printMaterialShortage/excel') ?>' + url);
        }
    }

    function reload() {
        window.location.reload();
    }

    function cutoffDate(month, year){
        $.ajax({
            type: "post",
            url: "<?= base_url('planning/material_calculation/cutoffDate') ?>",
            data: "month=" + month + "&year=" + year,
            dataType: "html",
            success: function (response) {
                $("#filter_date").datebox('setValue', response);
            }
        });
    }

    $(function() {
        $("#add").html('Generate');
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

        $('#filter_item_family').combobox({
            url: '<?php echo base_url('planning/material_calculation/readItemFamily'); ?>',
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
                    url: '<?= base_url('planning/material_calculation/readProducts/') ?>' + row.number,
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