<div id="f" class="easyui-panel" style="width:99.5%; background: #F4F4F4; padding: 10px;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 40%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
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
                <span style="width:35%; display:inline-block;">Customer Name</span>
                <input style="width:60%;" id="filter_line_no" class="easyui-combobox">
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
                    <option value="0">0</option>
                    <option value="1">1</option>
                    <option value="2" selected>2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                    <option value="6">6</option>
                    <option value="7">7</option>
                    <option value="8">8</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </fieldset>
        <fieldset style="width: 30%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Process Generate Data</b></legend>
            <a href="javascript:;" style="float: left; color:green;" class="easyui-linkbutton" plain="true"><i class="fa fa-check"></i> SUCCESS : <b id="p_success">0</b></a>
            <a href="javascript:;" style="float: right; color:red;" class="easyui-linkbutton" plain="true" onclick="downloadFailed()"><i class="fa fa-times"></i> FAILED : <b id="p_failed">0</b></a>
            <br><br>
            <p style="margin: 0;">Calculating Data <b id="processLine"></b></p>
            <div id="p_upload" class="easyui-progressbar" style="width:100%;"></div>
            <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
            <div id="p_remarks" class="easyui-panel" style="width:100%; height:150px; padding:10px; margin-top: 10px; overflow: auto;">

            </div>
        </fieldset>
        <fieldset style="width: 30%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Working Calendar</b></legend>
            <div id="showWorkingCalendar">

            </div>
        </fieldset>
    </div>
    <?= $button ?>
</div>

<div id="p" class="easyui-panel" title="Print Preview" style="width:100%;">
    <iframe id="printout" src="" style="width: 100%; height: 450px; border: 0;"></iframe>
</div>
<script>
    function calendarCheck(filter_month, filter_year, filter_revision) {
        $.ajax({
            type: "get",
            url: "<?= base_url('planning/generate_mps/checkCalendar') ?>",
            data: "filter_month=" + window.btoa(filter_month) +
                "&filter_year=" + window.btoa(filter_year) +
                "&filter_revision=" + window.btoa(filter_revision),
            dataType: "html",
            success: function(html) {
                $("#showWorkingCalendar").html(html);
            }
        });
    }

    //Add Data
    function add() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_line_no = $("#filter_line_no").combobox('getValue');

        Swal.fire({
            title: 'Please Wait for Read Data',
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });

        $.ajax({
            type: "get",
            url: "<?= base_url('planning/generate_loadcap/getdata') ?>",
            data: "filter_month=" + window.btoa(filter_month) +
                "&filter_year=" + window.btoa(filter_year) +
                "&filter_revision=" + window.btoa(filter_revision) +
                "&filter_line_no=" + window.btoa(filter_line_no),
            dataType: "json",
            success: function(rows) {
                Swal.close();
                if (rows['total'] <= 0) {
                    Swal.fire('Empty', 'data for this period is empty, check the completeness of data in UMH and MPS!');
                } else {
                    requestData(rows['total'], rows);

                    function requestData(total, json, number = 1, value = 0) {
                        if (value < 100) {
                            value = Math.floor((number / total) * 100);
                            $('#p_upload').progressbar('setValue', value);
                            $('#p_start').html(number);
                            $('#p_finish').html(total);

                            $("#processLine").html(json[number - 1].remarks);

                            $.ajax({
                                type: "get",
                                url: "<?= base_url('planning/generate_loadcap/getdata2') ?>",
                                data: "filter_month=" + window.btoa(filter_month) +
                                    "&filter_year=" + window.btoa(filter_year) +
                                    "&filter_revision=" + window.btoa(filter_revision) +
                                    "&filter_line_no=" + window.btoa(json[number - 1].line_no),
                                dataType: "json",
                                success: function(rows2) {
                                    Swal.close();
                                    requestData2(rows2['total'], rows2);

                                    function requestData2(total2, json2, number2 = 1, value2 = 0, success = 0, failed = 0) {

                                        if (value2 < 100) {
                                            value2 = Math.floor((number2 / total2) * 100);

                                            $.post('<?= base_url('planning/generate_loadcap/create') ?>', {
                                                data: json2[number2 - 1]
                                            }, function(note) {
                                                var result = eval('(' + note + ')');
                                                if (result.theme == "success") {
                                                    Swal.close();
                                                    $('#p_success').html(success);
                                                    var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                                    requestData2(total2, json2, number2 + 1, value2, success + 1, failed + 0);
                                                } else {
                                                    $('#p_failed').html(failed);
                                                    var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;

                                                    //Json Failed
                                                    $.ajax({
                                                        type: "POST",
                                                        async: true,
                                                        url: "<?= base_url('planning/generate_loadcap/uploadcreateFailed') ?>",
                                                        data: {
                                                            data: json[number - 1],
                                                            message: result.message
                                                        },
                                                        cache: false
                                                    });

                                                    requestData2(total2, json2, number2 + 1, value2, success + 0, failed + 1);
                                                }

                                                if (value2 == 100) {
                                                    requestData(total, json, number + 1, value);
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

                                                    requestData2(total2, json2, number2 + 1, value2, success + 0, failed + 0);
                                                }
                                            });
                                        }
                                    }
                                }
                            });

                            if (value == 100) {
                                Swal.fire('Good job!', 'Process Save Data Completed!', 'success');
                            }
                        }
                    }
                }
            }
        });
    }

    function downloadFailed() {
        window.open('<?= base_url('planning/generate_loadcap/uploadDownloadFailed') ?>', '_blank');
    }

    function filter() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_line_no = $("#filter_line_no").combobox('getValue');
        var filter_shift = $("#filter_shift").combobox('getValue');
        var filter_overtime = $("#filter_overtime").combobox('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_line_no=" + window.btoa(filter_line_no) +
            "&filter_shift=" + window.btoa(filter_shift) +
            "&filter_overtime=" + window.btoa(filter_overtime);

        if (filter_month == "" || filter_year == "") {
            toastr.warning("Please select Period!", "Information");
        } else {
            calendarCheck(filter_month, filter_year, filter_revision);
            $("#printout").attr('src', '<?= base_url('planning/generate_loadcap/print') ?>' + url);
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_line_no = $("#filter_line_no").combobox('getValue');
        var filter_shift = $("#filter_shift").combobox('getValue');
        var filter_overtime = $("#filter_overtime").combobox('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_line_no=" + window.btoa(filter_line_no) +
            "&filter_shift=" + window.btoa(filter_shift) +
            "&filter_overtime=" + window.btoa(filter_overtime);

        if (filter_month == "" || filter_year == "") {
            toastr.warning("Please select Period!", "Information");
        } else {
            window.location.assign('<?= base_url('planning/generate_loadcap/print/excel') ?>' + url);
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
        calendarCheck(month, year, revision);

        $('#dg').datagrid({
            url: '<?= base_url('planning/generate_loadcap/datatables') ?>',
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

        // $('#filter_process_main_number').combobox({
        //     url: '<?php echo base_url('planning/mst_process_main/reads'); ?>',
        //     valueField: 'number',
        //     textField: 'name',
        //     prompt: 'Select All',
        //     icons: [{
        //         iconCls: 'icon-clear',
        //         handler: function(e) {
        //             $(e.data.target).combobox('clear').combobox('textbox').focus();
        //         }
        //     }],
        //     onSelect: function(pm) {
        //         $('#filter_process_sub_number').combobox({
        //             url: '<?php echo base_url('planning/mst_process_sub/reads?number='); ?>' + pm.number,
        //             valueField: 'number',
        //             textField: 'name',
        //             prompt: 'Select All',
        //             icons: [{
        //                 iconCls: 'icon-clear',
        //                 handler: function(e) {
        //                     $(e.data.target).combobox('clear').combobox('textbox').focus();
        //                 }
        //             }],
        //             onSelect: function(ps) {
        //                 $('#filter_process_number').combobox({
        //                     url: '<?php echo base_url('planning/mst_process/reads?number='); ?>' + ps.number,
        //                     valueField: 'number',
        //                     textField: 'name',
        //                     prompt: 'Select All',
        //                     icons: [{
        //                         iconCls: 'icon-clear',
        //                         handler: function(e) {
        //                             $(e.data.target).combobox('clear').combobox('textbox').focus();
        //                         }
        //                     }]
        //                 });
        //             }
        //         });
        //     }
        // });
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