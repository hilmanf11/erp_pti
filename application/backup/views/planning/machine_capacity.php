<div id="f" class="easyui-panel" style="width:100%; background: #F4F4F4; padding: 10px;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 60%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 80%; float: left;">
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
                    <span style="width:35%; display:inline-block;">Shift</span>
                    <select style="width:60%;" id="filter_shift" panelHeight="auto" class="easyui-combobox">
                        <option value="1">Shift 1</option>
                        <option value="2" selected>Shift 2</option>
                        <option value="3">Shift 3</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Overtime</span>
                    <select style="width:60%;" id="filter_overtime" panelHeight="auto" class="easyui-combobox">
                        <option value="0" selected>0</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                        <option value="6">6</option>
                        <option value="7">7</option>
                        <option value="8">8</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Main Process</span>
                    <input style="width:60%;" id="filter_process_main_number" panelHeight="auto" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Main Sub Process</span>
                    <input style="width:60%;" id="filter_process_sub_number" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
        </fieldset>
        <fieldset style="width: 40%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Result Generate</b></legend>
            <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
            <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
            <div id="p_remarks" class="easyui-panel" style="width:100%; height:150px; padding:10px; margin-top: 10px; overflow: auto;">
                <ul id="remarks">

                </ul>
            </div>
            <a href="javascript:;" style="float: left; color:green;" class="easyui-linkbutton" plain="true"><i class="fa fa-check"></i> SUCCESS : <b id="p_success">0</b></a>
            <a href="javascript:;" style="float: right; color:red;" class="easyui-linkbutton" plain="true" onclick="downloadFailed()"><i class="fa fa-times"></i> FAILED : <b id="p_failed">0</b></a>
        </fieldset>
    </div>
    <?= $button ?>
</div>

<div id="p" class="easyui-panel" title="Print Preview" style="width:100%;">
    <iframe id="printout" src="" style="width: 100%; height: 430px; border: 0;"></iframe>
</div>
<script>
    function add() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_shift = $("#filter_shift").combobox('getValue');
        var filter_overtime = $("#filter_overtime").combobox('getValue');
        var filter_process_main_number = $("#filter_process_main_number").combobox('getValue');
        var filter_process_sub_number = $("#filter_process_sub_number").combobox('getValue');

        if (filter_month == "" || filter_year == "" || filter_process_main_number == "") {
            toastr.warning("Please select filter month, year and Process Main", "Information");
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
                url: "<?= base_url('planning/machine_capacity/getdata') ?>",
                data: "filter_month=" + window.btoa(filter_month) +
                    "&filter_year=" + window.btoa(filter_year) +
                    "&filter_revision=" + window.btoa(filter_revision) + 
                    "&filter_process_main_number=" + window.btoa(filter_process_main_number) + 
                    "&filter_process_sub_number=" + window.btoa(filter_process_sub_number),
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

                            $.post('<?= base_url('planning/machine_capacity/create?filter_month=') ?>' + window.btoa(filter_month) +
                                "&filter_year=" + window.btoa(filter_year) +
                                "&filter_revision=" + window.btoa(filter_revision) + 
                                "&filter_shift=" + window.btoa(filter_shift) + 
                                "&filter_overtime=" + window.btoa(filter_overtime), {
                                data: json[number - 1]
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
                                        url: "<?= base_url('planning/machine_capacity/uploadcreateFailed') ?>",
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
        var filter_shift = $("#filter_shift").combobox('getValue');
        var filter_overtime = $("#filter_overtime").combobox('getValue');
        var filter_process_main_number = $("#filter_process_main_number").combobox('getValue');
        var filter_process_sub_number = $("#filter_process_sub_number").combobox('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_shift=" + window.btoa(filter_shift) +
            "&filter_overtime=" + window.btoa(filter_overtime) +
            "&filter_process_main_number=" + window.btoa(filter_process_main_number) +
            "&filter_process_sub_number=" + window.btoa(filter_process_sub_number);

        if (filter_month == "" || filter_year == "" || filter_process_main_number == "") {
            toastr.warning("Please select Period and Main Process!", "Information");
        } else {
            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('planning/machine_capacity/print') ?>' + url);
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_shift = $("#filter_shift").combobox('getValue');
        var filter_overtime = $("#filter_overtime").combobox('getValue');
        var filter_process_main_number = $("#filter_process_main_number").combobox('getValue');
        var filter_process_sub_number = $("#filter_process_sub_number").combobox('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_shift=" + window.btoa(filter_shift) +
            "&filter_overtime=" + window.btoa(filter_overtime) +
            "&filter_process_main_number=" + window.btoa(filter_process_main_number) +
            "&filter_process_sub_number=" + window.btoa(filter_process_sub_number);

        if (filter_month == "" || filter_year == "" || filter_process_main_number == "") {
            toastr.warning("Please select Period and Main Process!", "Information");
        } else {
            window.location.assign('<?= base_url('planning/machine_capacity/print/excel') ?>' + url);
        }
    }

    function reload() {
        window.location.reload();
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
            }]
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
            }]
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

        $('#filter_process_main_number').combobox({
            url: '<?php echo base_url('planning/mst_process_main/readesc'); ?>',
            valueField: 'description',
            textField: 'description',
            prompt: 'Choose Main Process',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(row) {
                $('#filter_process_sub_number').combobox({
                    url: '<?php echo base_url('planning/mst_process_sub/reads?description='); ?>' + row.description,
                    valueField: 'number',
                    textField: 'name',
                    prompt: 'Choose Sub Process',
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
</script>