<div id="f" class="easyui-panel" style="width:99.5%; background: #F4F4F4; padding: 10px;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 60%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="float: left; width:50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:30%;" name="filter_month" id="filter_month" value="<?= date("m") ?>" class="easyui-combobox" data-options="prompt:'Month'">
                    <input style="width:30%;" name="filter_year" id="filter_year" value="<?= date("Y") ?>" class="easyui-combobox" data-options="prompt:'Year'">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Revision</span>
                    <input style="width:60%;" name="filter_revision" id="filter_revision" value="<?= "0" ?>" class="easyui-combobox" data-options="prompt:'Revision'" panelHeight="auto">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Customer Name</span>
                    <input style="width:60%;" id="filter_line_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="float: left; width:50%;">
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
            </div>
        </fieldset>
        <fieldset style="width: 20%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Process Generate Data</b></legend>
            <a href="javascript:;" style="float: left; color:green;" class="easyui-linkbutton" plain="true"><i class="fa fa-check"></i> SUCCESS : <b id="p_success">0</b></a>
            <a href="javascript:;" style="float: right; color:red;" class="easyui-linkbutton" plain="true" onclick="downloadFailed()"><i class="fa fa-times"></i> FAILED : <b id="p_failed">0</b></a>
            <br><br>
            <p style="margin: 0;">Sub Process <b id="processLine"></b></p>
            <div id="p_upload" class="easyui-progressbar" style="width:100%;"></div>
            <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
            <br>
            <p style="margin: 0;">Product No <b id="processProduct"></b></p>
            <div id="p_upload2" class="easyui-progressbar" style="width:100%;"></div>
            <center><b id="p_start2">0</b> Of <b id="p_finish2">0</b></center>
        </fieldset>
        <fieldset style="width: 20%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Process Generate Results</b></legend>
            <div id="p_remarks" class="easyui-panel" style="width:100%; height:150px; padding:10px; margin-top: 10px; overflow: auto;">

            </div>
        </fieldset>
    </div>
    <?= $button ?>
</div>

<div id="p" class="easyui-panel" title="Print Preview" style="width:100%;">
    <iframe id="printout" src="" style="width: 100%; height: 520px; border: 0;"></iframe>
</div>
<script>
    //Add Data
    function add() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_line_no = $("#filter_line_no").combobox('getValue');
        var filter_shift = $("#filter_shift").combobox('getValue');
        var filter_overtime = $("#filter_overtime").combobox('getValue');
        var filter_process_main_number = $("#filter_process_main_number").combobox('getValue');
        var filter_process_sub_number = $("#filter_process_sub_number").combobox('getValue');

        if (filter_month == "" || filter_year == "" || filter_revision == "") {
            toastr.warning("Please select Period, Revision and Line Production!", "Information");
        } else if (filter_process_main_number == "") {
            toastr.warning("Please select Main Process!", "Information");
        } else {
        	$.messager.confirm('Warning', 'Are you sure you want to generate this data?', function(r) {
                if (r) {
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
		                url: "<?= base_url('planning/generate_loadcap_line/getdata') ?>",
		                data: "filter_month=" + window.btoa(filter_month) +
		                    "&filter_year=" + window.btoa(filter_year) +
		                    "&filter_revision=" + window.btoa(filter_revision) +
		                    "&filter_shift=" + window.btoa(filter_shift) +
		                    "&filter_overtime=" + window.btoa(filter_overtime) +
		                    "&filter_process_main_number=" + window.btoa(filter_process_main_number) +
		                    "&filter_process_sub_number=" + window.btoa(filter_process_sub_number) +
		                    "&filter_line_no=" + window.btoa(filter_line_no),
		                dataType: "json",
		                success: function(rows) {
		                    Swal.close();

		                    if (filter_process_main_number == "PP") {
		                        if (rows['total'] <= 0) {
		                            Swal.fire('Empty', 'data for this period is empty, check the completeness of data in UMH and MPS!');
		                        } else {
		                            requestData(rows['total'], rows);

		                            function requestData(total, json, number = 1, value = 0, success = 0, failed = 0) {
		                                if (value < 100) {
		                                    value = Math.floor((number / total) * 100);
		                                    $('#p_upload').progressbar('setValue', value);
		                                    $('#p_start').html(number);
		                                    $('#p_finish').html(total);
		                                    $("#processLine").html(json[number - 1].process_sub_name);

		                                    var total_m_1 = 0;
		                                    var total_m_2 = 0;
		                                    var total_m_3 = 0;
		                                    var total_m_4 = 0;
		                                    var total_m_5 = 0;
		                                    var total_m_6 = 0;

		                                    $.ajax({
		                                        type: "get",
		                                        url: "<?= base_url('planning/generate_loadcap_line/getdata2') ?>",
		                                        data: "filter_month=" + window.btoa(filter_month) +
		                                            "&filter_year=" + window.btoa(filter_year) +
		                                            "&filter_revision=" + window.btoa(filter_revision) +
		                                            "&filter_process_main_number=" + window.btoa(json[number - 1].process_main_number) +
		                                            "&filter_process_sub_number=" + window.btoa(json[number - 1].process_sub_number) +
		                                            "&filter_process_main_name=" + window.btoa(json[number - 1].process_main_name) +
		                                            "&filter_process_sub_name=" + window.btoa(json[number - 1].process_sub_name) +
		                                            "&filter_line_no=" + window.btoa(filter_line_no),
		                                        dataType: "json",
		                                        success: function(rows2) {
		                                            requestData2(rows2['total'], rows2);
		                                            let month_1 = 0;
		                                            let month_2 = 0;
		                                            let month_3 = 0;
		                                            let month_4 = 0;
		                                            let month_5 = 0;
		                                            let month_6 = 0;

		                                            function requestData2(total2, json2, number2 = 1, value2 = 0) {
		                                                if (total2 == 0) {
		                                                    Swal.fire('Data MPS Not Found!');

		                                                    return false;
		                                                }

		                                                if (value2 < 100) {
		                                                    value2 = Math.floor((number2 / total2) * 100);
		                                                    $('#p_upload2').progressbar('setValue', value2);
		                                                    $('#p_start2').html(number2);
		                                                    $('#p_finish2').html(total2);
		                                                    $("#processProduct").html(json2[number2 - 1].product_no);

		                                                    $.ajax({
		                                                        type: "get",
		                                                        url: "<?= base_url('planning/generate_loadcap_line/getdata3') ?>",
		                                                        data: "filter_month=" + window.btoa(filter_month) +
		                                                            "&filter_year=" + window.btoa(filter_year) +
		                                                            "&filter_revision=" + window.btoa(filter_revision) +
		                                                            "&filter_shift=" + window.btoa(filter_shift) +
		                                                            "&filter_overtime=" + window.btoa(filter_overtime) +
		                                                            "&filter_process_main_number=" + window.btoa(json[number - 1].process_main_number) +
		                                                            "&filter_process_sub_number=" + window.btoa(json[number - 1].process_sub_number) +
		                                                            "&filter_process_main_name=" + window.btoa(json[number - 1].process_main_name) +
		                                                            "&filter_process_sub_name=" + window.btoa(json[number - 1].process_sub_name) +
		                                                            "&filter_product_no=" + window.btoa(json2[number2 - 1].product_no) +
		                                                            "&filter_efficiency=" + window.btoa(json[number - 1].efficiency) +
		                                                            "&filter_line_no=" + window.btoa(filter_line_no),
		                                                        dataType: "json",
		                                                        success: function(rows3) {
		                                                            requestData3(rows3['total'], rows3);

		                                                            function requestData3(total3, json3, number3 = 1, value3 = 0) {
		                                                                total_m_1 += json3.month_1;
		                                                                total_m_2 += json3.month_2;
		                                                                total_m_3 += json3.month_3;
		                                                                total_m_4 += json3.month_4;
		                                                                total_m_5 += json3.month_5;
		                                                                total_m_6 += json3.month_6;

		                                                                if (value2 == 100) {
		                                                                    $.post('<?= base_url('planning/generate_loadcap_line/create') ?>', {
		                                                                        data: {
		                                                                            p_month: json3.p_month,
		                                                                            p_year: json3.p_year,
		                                                                            revision: json3.revision,
		                                                                            line_no: json3.line_no,
		                                                                            shift: json3.shift,
		                                                                            overtime: json3.overtime,
		                                                                            process_main_number: json3.process_main_number,
		                                                                            process_main_name: json3.process_main_name,
		                                                                            process_sub_number: json3.process_sub_number,
		                                                                            process_sub_name: json3.process_sub_name,
		                                                                            machine_qty: json3.machine_qty,
		                                                                            efficiency: json3.efficiency,
		                                                                            month_1: total_m_1,
		                                                                            month_2: total_m_2,
		                                                                            month_3: total_m_3,
		                                                                            month_4: total_m_4,
		                                                                            month_5: total_m_5,
		                                                                            month_6: total_m_6,
		                                                                            capacity_1: json3.capacity_1,
		                                                                            capacity_2: json3.capacity_2,
		                                                                            capacity_3: json3.capacity_3,
		                                                                            capacity_4: json3.capacity_4,
		                                                                            capacity_5: json3.capacity_5,
		                                                                            capacity_6: json3.capacity_6
		                                                                        }
		                                                                    }, function(note) {
		                                                                        var result = eval('(' + note + ')');
		                                                                        if (result.theme == "success") {
		                                                                            $('#p_success').html(success);
		                                                                            var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
		                                                                        } else {
		                                                                            $('#p_failed').html(failed);
		                                                                            var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;

		                                                                            //Json Failed
		                                                                            $.ajax({
		                                                                                type: "POST",
		                                                                                async: true,
		                                                                                url: "<?= base_url('planning/generate_loadcap_line/uploadcreateFailed') ?>",
		                                                                                data: {
		                                                                                    data: json2[number2 - 1],
		                                                                                    message: result.message
		                                                                                },
		                                                                                cache: false
		                                                                            });
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
		                                                                        }
		                                                                    });

		                                                                    requestData(total, json, number + 1, value, success + 1, failed + 0);
		                                                                }

		                                                                requestData2(total2, json2, number2 + 1, value2);
		                                                            }
		                                                        }
		                                                    });
		                                                }
		                                            };
		                                        }
		                                    });

		                                    if (value == 100) {
		                                        Swal.fire('Good job!', 'Process Save Data Completed!', 'success');
		                                    }
		                                }
		                            }
		                        }
		                    } else {
		                        if (rows['total'] <= 0) {
		                            Swal.fire('Empty', 'data for this period is empty, check the completeness of data in UMH and MPS!');
		                        } else {
		                            requestData(rows['total'], rows);

		                            function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
		                                if (value < 100) {
		                                    value = Math.floor((number / total) * 100);
		                                    $('#p_upload').progressbar('setValue', value);
		                                    $('#p_start').html(number);
		                                    $('#p_finish').html(total);

		                                    $.post('<?= base_url('planning/generate_loadcap_line/create') ?>', {
		                                        data: json[number - 1]
		                                    }, function(note) {
		                                        var result = eval('(' + note + ')');
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
		                                                url: "<?= base_url('planning/generate_loadcap_line/uploadcreateFailed') ?>",
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
		                    }
		                }
		            });
				}
			});
        }
    }

    function filter() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_line_no = $("#filter_line_no").combobox('getValue');
        var filter_shift = $("#filter_shift").combobox('getValue');
        var filter_overtime = $("#filter_overtime").combobox('getValue');
        var filter_process_main_number = $("#filter_process_main_number").combobox('getValue');
        var filter_process_sub_number = $("#filter_process_sub_number").combobox('getValue');
        
        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_line_no=" + window.btoa(filter_line_no) +
            "&filter_shift=" + window.btoa(filter_shift) +
            "&filter_overtime=" + window.btoa(filter_overtime) +
            "&filter_process_main_number=" + window.btoa(filter_process_main_number) +
            "&filter_process_sub_number=" + window.btoa(filter_process_sub_number);

        if (filter_month == "" || filter_year == "" || filter_revision == "") {
            toastr.warning("Please select Period, Revision and Line Production!", "Information");
        } else {
            $("#printout").attr('src', '<?= base_url('planning/generate_loadcap_line/print') ?>' + url);
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_line_no = $("#filter_line_no").combobox('getValue');
        var filter_shift = $("#filter_shift").combobox('getValue');
        var filter_overtime = $("#filter_overtime").combobox('getValue');
        var filter_process_main_number = $("#filter_process_main_number").combobox('getValue');
        var filter_process_sub_number = $("#filter_process_sub_number").combobox('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_line_no=" + window.btoa(filter_line_no) +
            "&filter_shift=" + window.btoa(filter_shift) +
            "&filter_overtime=" + window.btoa(filter_overtime) +
            "&filter_process_main_number=" + window.btoa(filter_process_main_number) +
            "&filter_process_sub_number=" + window.btoa(filter_process_sub_number);

        if (filter_month == "" || filter_year == "" || filter_revision == "") {
            toastr.warning("Please select Period!", "Information");
        } else {
            window.location.assign('<?= base_url('planning/generate_loadcap_line/print/excel') ?>' + url);
        }
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        $("#add").html('Generate');
        var month = $("#filter_month").combobox('getValue');
        var year = $("#filter_year").combobox('getValue');
        var revision = $("#filter_revision").combobox('getValue');

        $('#dg').datagrid({
            url: '<?= base_url('planning/generate_loadcap_line/datatables') ?>',
            rownumbers: true
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

        $('#filter_line_no').combobox({
            url: '<?php echo base_url('planning/mst_line/reads'); ?>',
            valueField: 'line_no',
            textField: 'remarks',
            prompt: 'Select All',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $('#filter_process_main_number').combobox({
            url: '<?php echo base_url('planning/mst_process_main/readesc'); ?>',
            valueField: 'description',
            textField: 'description',
            prompt: 'Select All',
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
                    prompt: 'Select All',
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