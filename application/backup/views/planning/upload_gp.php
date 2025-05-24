<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'plant_name',width:120,halign:'center', formatter:plantFormat">Plant</th>
            <th rowspan="2" data-options="field:'product_no',width:150,halign:'center'">Part No</th>
            <th rowspan="2" data-options="field:'supplier_part_code',width:150,halign:'center'">Supplier Part No</th>
            <th rowspan="2" data-options="field:'product_family',width:120,halign:'center'">Product Family</th>
            <th rowspan="2" data-options="field:'supplier_name',width:200,halign:'center'">Supplier</th>
            <th rowspan="2" data-options="field:'maker_name',width:200,halign:'center'">Maker</th>
            <th rowspan="2" data-options="field:'country_origin',width:100,halign:'center'">Country<br>Origin</th>
            <th rowspan="2" data-options="field:'supplier_type',width:100,halign:'center'">Maker<br>Type</th>
            <th rowspan="2" hidden data-options="field:'reference',width:300,halign:'center'">Reference</th>
            <th rowspan="2"data-options="field:'mpq',width:100,halign:'center',align:'right', formatter:numberformat">MPQ</th>
            <th rowspan="2"data-options="field:'moq',width:100,halign:'center',align:'right', formatter:numberformat">MOQ</th>
            <th rowspan="2"data-options="field:'share_order',width:100,halign:'center',align:'right', formatter:numberformat">Shared Order</th>
            <th rowspan="2"data-options="field:'leadtime',width:100,halign:'center',align:'right', formatter:numberformat">Leadtime</th>
            <th rowspan="2"data-options="field:'currency',width:80,align:'center'">Currency</th>
            <th rowspan="2"data-options="field:'price',width:100,halign:'center',align:'right', formatter:numberformatPrice">Price</th>
            <th rowspan="2" data-options="field:'status',width:100,align:'center', styler:cellStyler">Status</th>
            <th colspan="2" data-options="field:'',width:100,align:'center'">Created</th>
            <th colspan="2" data-options="field:'',width:100,align:'center'">Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:100,align:'center'">By</th>
            <th data-options="field:'created_date',width:150,halign:'center'">Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'">By</th>
            <th data-options="field:'updated_date',width:150,halign:'center'">Date</th>
        </tr>
    </thead>
</table>

<div id="toolbar">
    <div class="easyui-accordion" style="width:100%; margin-bottom:5px;">
        <div title="Hide Filter" data-options="onCollapse: function(){ $('#dg').datagrid('reload'); }, onExpand: function(){ $('#dg').datagrid('reload'); }" style="padding:10px; background:#F4F4F4;">
            <fieldset style="width: 60%; border:2px solid #d0d0d0; border-radius:4px; float: left;">
                <legend><b>Form Filter Data</b></legend>
                <div style="width: 49%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Period</span>
                        <input style="width:30%;" name="filter_month" id="filter_month" class="easyui-combobox" data-options="prompt:'Month'">
                        <input style="width:30%;" name="filter_year" id="filter_year" class="easyui-combobox">
                    </div>
                    <div class="fitem" hidden>
                        <span style="width:35%; display:inline-block;">Cutoff</span>
                        <input style="width:60%;" id="filter_cutoff" class="easyui-datebox" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Revision</span>
                        <input style="width:60%;" name="filter_revision" id="filter_revision" class="easyui-combobox" data-options="prompt:'Revision'" panelHeight="auto">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Plant</span>
                        <input style="width:60%;" name="filter_plant" id="filter_plant" class="easyui-combogrid">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;"></span>
                        <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                    </div>
                </div>
                <div style="width: 49%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Status</span>
                        <select style="width:60%;" name="filter_status" id="filter_status" panelHeight="auto" class="easyui-combobox">
                            <option value="">ALL</option>
                            <option value="EMPTY">EMPTY</option>
                            <option value="GENERATE">GENERATE</option>
                        </select>
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Product Family</span>
                        <input style="width:60%;" id="filter_product_family" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Part No</span>
                        <input style="width:60%;" id="filter_product_no" class="easyui-combogrid">
                    </div>
                </div>
            </fieldset>
            <fieldset style="width: 39%; border:2px solid #d0d0d0; border-radius:4px; float: left;">
                <legend><b>Process Save Data</b></legend>
                <a href="javascript:;" style="float: left; color:green;" class="easyui-linkbutton" plain="true"><i class="fa fa-check"></i> SUCCESS : <b id="p_success">0</b></a>
                <a href="javascript:;" style="float: right; color:red;" class="easyui-linkbutton" plain="true" onclick="downloadFailed()"><i class="fa fa-times"></i> FAILED : <b id="p_failed">0</b></a>
                <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
                <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
                <div id="p_remarks" class="easyui-panel" style="width:100%; height:65px; padding:10px; margin-top: 10px; overflow: auto;">
                    <ul id="remarks">

                    </ul>
                </div>
            </fieldset>
        </div>
    </div>
    <?= $button ?>
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

<!-- PDF -->
<iframe id="printout" src="" style="width: 100%;" hidden></iframe>

<script>
    function scrollToBottom() {
        var p_remarks = document.getElementById('p_remarks');
        p_remarks.scrollTop = p_remarks.scrollHeight;
    }

    //Add Data
    function add() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_cutoff = $("#filter_cutoff").datebox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');
        var filter_plant = $("#filter_plant").combogrid('getValue');

        var rows = $('#dg').datagrid('getChecked');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to save this data?', function(r) {
                if (r) {
                    $.ajax({
                        url: "<?= base_url('planning/upload_gp/uploadclearFailed') ?>"
                    });

                    requestData(rows.length, rows);

                    function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                        if (value < 100) {
                            value = Math.floor((number / total) * 100);
                            $('#p_upload').progressbar('setValue', value);
                            $('#p_start').html(number);
                            $('#p_finish').html(total);

                            $.post('<?= base_url('planning/upload_gp/create') ?>', {
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
                                        url: "<?= base_url('planning/upload_gp/uploadcreateFailed') ?>",
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
                                scrollToBottom();

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
        } else {
            toastr.warning("Please select one of the data in the table first!");
        }
    }

    //EDIT DATA
    function update() {
        toastr.info("Not Available this function");
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
                            url: '<?= base_url('planning/upload_gp/delete') ?>',
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
                                $('#dg_uploaded').datagrid('reload');
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
        var filter_cutoff = $("#filter_cutoff").datebox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');
        var filter_plant = $("#filter_plant").combogrid('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_cutoff=" + window.btoa(filter_cutoff) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_product_no=" + window.btoa(filter_product_no) +
            "&filter_product_family=" + window.btoa(filter_product_family) + 
            "&filter_status=" + window.btoa(filter_status) + 
            "&filter_plant=" + window.btoa(filter_plant);

        if (filter_month == "" || filter_year == "" || filter_revision == "" || filter_cutoff == "" || filter_plant == "" || filter_product_family == "") {
            toastr.warning("Please select Period, Revision, Plant and Part Family!");
        } else {
            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('planning/upload_gp/print') ?>' + url);

            $('#dg').datagrid({
                rownumbers: true,
                url: '<?= base_url('planning/upload_gp/datatables') ?>' + url,
                fit: true,
            });
            // }).datagrid('enableCellEditing').datagrid('gotoCell', {
            //     index: 0,
            //     field: 'product_no'
            // });
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_cutoff = $("#filter_cutoff").datebox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');
        var filter_plant = $("#filter_plant").combogrid('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_cutoff=" + window.btoa(filter_cutoff) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_product_no=" + window.btoa(filter_product_no) +
            "&filter_plant=" + window.btoa(filter_plant) +
            "&filter_status=" + window.btoa(filter_status) + 
            "&filter_product_family=" + window.btoa(filter_product_family);

        if (filter_month == "" || filter_year == "" || filter_revision == "" || filter_cutoff == "" || filter_plant == "") {
            toastr.warning("Please select Period, Revision, Plant and Part Family!");
        } else {
            $.messager.alert('Export to Excel', 'Please wait until the excel download appears');
            window.location.assign('<?= base_url('planning/upload_gp/print/excel') ?>' + url);
        }
    }

    //Upload Data
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_upload_gp.xls') ?>');
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        $("#filter_month").combobox('setValue', '<?= date("m") ?>');

        //Upload Data
        $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('planning/upload_gp/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('planning/upload_gp/upload') ?>',
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
                                url: "<?= base_url('planning/upload_gp/uploadclearFailed') ?>"
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
                                        url: "<?= base_url('planning/upload_gp/uploadCreate') ?>",
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
                                                    url: "<?= base_url('planning/upload_gp/uploadcreateFailed') ?>",
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
            prompt: 'Month',
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
                    url: '<?php echo base_url('planning/upload_gp/readRevision?month='); ?>' + month + '&year=' + year,
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
            prompt: 'Year',
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
                    url: '<?php echo base_url('planning/upload_gp/readRevision?month='); ?>' + month + '&year=' + year,
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

        $('#filter_plant').combogrid({
            url: '<?php echo base_url('planning/plants/reads'); ?>',
            panelWidth: 350,
            idField: 'number',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Plant",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'number',
                    title: 'Code',
                    width: 100
                },{
                    field: 'name',
                    title: 'Name',
                    width: 200
                }]
            ]
        });

        $('#filter_product_family').combobox({
            url: '<?php echo base_url('planning/upload_gp/readProductFamily'); ?>',
            valueField: 'code',
            textField: 'name',
            prompt: 'Select Product Family',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(row){
                $('#filter_product_no').combogrid({
                    url: '<?= base_url('planning/upload_gp/readProducts/') ?>' + row.id,
                    panelWidth: 250,
                    idField: 'maker_part',
                    textField: 'maker_part',
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
                            field: 'maker_part',
                            title: 'Product No',
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

    function numberformatPrice(value, row){
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 4
        });

        return "<b>" + formatter.format(value) + "</b>";
    }

    function plantFormat(value){
        if(value == null){
            return "ALL PLANT";
        }else{
            return value;
        }
    }

    function cellStyler(value, row, index) {
        if (value == "EMPTY") {
            return 'background: #FF5F5F; color:white;';
        } else if(value == "GENERATE"){
            return 'background: #3F7BFF; color:white;';
        } else if(value == "UPLOAD"){
            return 'background: #53D636; color:white;';
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