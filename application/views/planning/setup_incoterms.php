<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'number',width:120,halign:'center'">Shipping Code</th>
            <th rowspan="2" data-options="field:'incoterm_name',width:200,halign:'center'">Incoterm</th>
            <th rowspan="2" data-options="field:'country_name',width:200,halign:'center'">Country Of Origin</th>
            <th rowspan="2" data-options="field:'item_family_name',width:200,halign:'center'">Product Family</th>
            <th rowspan="2" data-options="field:'sub_prodfam',width:200,halign:'center'">Sub Product Family</th>
            <th rowspan="2" data-options="field:'insurance',width:120,halign:'center',align:'right',formatter:numberformatComa">Insurance (%)</th>
            <th rowspan="2" data-options="field:'transportation',width:120,halign:'center',align:'right',formatter:numberformatComa">Transportation (%)</th>
            <th rowspan="2" data-options="field:'import_duty',width:120,halign:'center',align:'right',formatter:numberformatComa">Import Duty (%)</th>
            <th rowspan="2" data-options="field:'total_ddp',width:120,halign:'center',align:'right',formatter:numberformatComa">Total DDP (%)</th>
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

<div id="toolbar" style="height: 35px;">
    <?= $button ?>
</div>

<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Shipping Code</span>
                <input style="width:60%;" name="number" id="number" required="" class="easyui-textbox" readonly>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Incoterm</span>
                <input style="width:60%;" name="incoterm_number" id="incoterm_number" required="" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Country Of Origin</span>
                <input style="width:60%;" name="country_number" id="country_number" required="" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product Family</span>
                <input style="width:60%;" name="pfm_number" id="pfm_number" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product Family</span>
                <input style="width:60%;" name="sub_prodfam" id="sub_prodfam" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Insurance (%)</span>
                <input style="width:60%;" name="insurance" id="insurance" required="true" class="easyui-numberbox" data-options="precision:8">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Transportation (%)</span>
                <input style="width:60%;" name="transportation" id="transportation" required="true" class="easyui-numberbox" data-options="precision:4">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Import Duty (%)</span>
                <input style="width:60%;" name="import_duty" id="import_duty" required="true" class="easyui-numberbox" data-options="precision:4">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Total DDP (%)</span>
                <input style="width:60%;" name="total_ddp" id="total_ddp" required="true" readonly class="easyui-numberbox" data-options="precision:4">
            </div>
        </fieldset>
    </form>
</div>

<!-- Upload -->

<div id="dlg_upload" class="easyui-dialog" title="Upload Data" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_upload" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">File Upload</span>
                <input name="file_upload" style="width: 60%;" required="" accept=".xls" id="file_excel" class="easyui-filebox">
            </div>
        </fieldset>
    </form>

    <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
    <div id="p_remarks" class="easyui-panel" style="width:100%; height:200px; padding:10px; margin-top: 10px;">
        <p>History Upload</p>
        <ul id="remarks">
        </ul>
    </div>
</div>

<!-- PDF -->

<iframe id="printout" src="<?= base_url('planning/setup_incoterms/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('planning/setup_incoterms/create') ?>';
        $('#frm_insert').form('clear');

        autonumber();
    }

    function autonumber() {
        //Get Autonumber
        $.ajax({
            type: "post",
            url: "<?= base_url('planning/setup_incoterms/autonumber') ?>",
            dataType: "text",
            success: function(number) {
                $('#number').textbox('setValue', number);
            }
        });
    }

    //Edit Data
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('planning/setup_incoterms/update') ?>?id=' + btoa(row.id);
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
                            url: '<?= base_url('planning/setup_incoterms/delete') ?>',
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
        window.location.assign('<?= base_url('template/tmp_setup_incoterms.xls') ?>');
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        window.location.assign('<?= base_url('planning/setup_incoterms/print/excel') ?>');
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        $('#dg').datagrid({
            url: '<?= base_url('planning/setup_incoterms/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
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

                            $('#dlg_insert').dialog('close');
                            $('#dg').datagrid('reload');
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
                        url: '<?= base_url('planning/setup_incoterms/upload') ?>',
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
                            var json = eval('(' + result + ')');
                            requestData(json.total, json);

                            function requestData(total, json, number = 1, value = 0) {
                                if (value < 100) {
                                    value = Math.floor((number / total) * 100);
                                    $('#p_upload').progressbar('setValue', value);
                                    $('#p_start').html(number);
                                    $('#p_finish').html(total);

                                    $.post('<?= base_url('planning/setup_incoterms/uploadCreate') ?>', {
                                        data: json[number - 1]
                                    }, function(note) {
                                        var result = eval('(' + note + ')');
                                        if (result.theme == "success") {
                                            var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                        } else {
                                            var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                                        }
                                        $("#p_remarks").append(title + "<br>");
                                        requestData(total, json, number + 1, value);
                                    });
                                }
                            }
                        }
                    });
                }
            }]
        });

        $('#incoterm_number').combogrid({
            url: '<?php echo base_url('master/incoterms/reads'); ?>',
            panelWidth: 350,
            idField: 'number',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Data",
            columns: [
                [{
                    field: 'number',
                    title: 'Code',
                    width: 100
                }, {
                    field: 'name',
                    title: 'Name',
                    width: 200
                }]
            ]
        });

        //Get Part
        $('#country_number').combogrid({
            url: '<?= base_url('master/country_of_origins/reads') ?>',
            panelWidth: 350,
            idField: 'number',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Data",
            columns: [
                [{
                    field: 'number',
                    title: 'code',
                    width: 100
                }, {
                    field: 'name',
                    title: 'Country Name',
                    width: 200
                }]
            ],
        });

        ['insurance', 'transportation', 'import_duty'].forEach(function(id) {
            $('#' + id).numberbox({
                onChange: function() {
                    calculateTotal();
                }
            });
        });

        $('#pfm_number').combobox({
            url: '<?php echo base_url('planning/mst_item/readFamily'); ?>',
            valueField: 'number',
            textField: 'name',
            prompt: 'Select Product Family',
            onSelect: function(row) {
                $('#sub_prodfam').combobox({
                    url: '<?= base_url('planning/setup_incoterms/readSubFamily/') ?>' + row.number,
                    valueField: 'item_name',
                    textField: 'item_name',
                    prompt: 'Select Sub Product Family',
                });
            }
        });
    });

    //Calculate Total DDP
    function calculateTotal() {
        var insurance = parseFloat($('#insurance').numberbox('getValue')) || 0;
        var transportation = parseFloat($('#transportation').numberbox('getValue')) || 0;
        var import_duty = parseFloat($('#import_duty').numberbox('getValue')) || 0;
        var total = insurance + transportation + import_duty;
        $('#total_ddp').numberbox('setValue', total);
    }

    function numberformatComa(value, row) {
        if (value != null) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 4
            });
            return "<b>" + formatter.format(value) + "</b>";
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