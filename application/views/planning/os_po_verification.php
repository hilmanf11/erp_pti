<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'ven_id',width:80,halign:'center'">Vendor ID</th>
            <th rowspan="2" data-options="field:'ven_name',width:250,halign:'center'">Vendor Name</th>
            <th rowspan="2" data-options="field:'pfm_name',width:120,halign:'center'">Product Family</th>
            <th rowspan="2" data-options="field:'detail',width:100,align:'center', formatter:cellFormatterDetail">Detail</th>
            <th rowspan="2" data-options="field:'ven_cp',width:120,halign:'center'">Contact Person</th>
            <th rowspan="2" data-options="field:'ven_email',width:200,halign:'center'">Email</th>
            <th colspan="3" data-options="field:'',width:300,halign:'center'">Send Mail</th>

        </tr>
        <tr>
            <th data-options="field:'send',width:100,align:'center', formatter:cellFormatterSend">Send</th>
            <th data-options="field:'mail_date',width:100,align:'center'">Date</th>
            <th data-options="field:'mail_send',width:100,align:'center', formatter:cellFormatterStatus">Status</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 275px;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 50%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Filter Data</b></legend>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">POR Date</span>
                <input style="width:28%;" id="filter_from" class="easyui-datebox" value="<?= date("Y-m-d") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Vendor ID</span>
                <input style="width:60%;" id="filter_vendor_id" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product Family</span>
                <input style="width:60%;" id="filter_product_family" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                <a href="javascript:;" class="easyui-linkbutton" onclick="generate()"><i class="fa fa-plus"></i> Generate</a>
                
            </div>
        </fieldset>
        <fieldset style="width: 40%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Generate Data</b></legend>
            <a href="javascript:;" style="float: left; color:green;" class="easyui-linkbutton" plain="true"><i class="fa fa-check"></i> SUCCESS : <b id="p_success">0</b></a>
            <a href="javascript:;" style="float: right; color:red;" class="easyui-linkbutton" plain="true" onclick="downloadFailed()"><i class="fa fa-times"></i> FAILED : <b id="p_failed">0</b></a>
            <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
            <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
            <div id="p_remarks" class="easyui-panel" style="width:100%; height:120px; padding:10px; margin-top: 10px; overflow: auto;">
                <ul id="remarks">

                </ul>
            </div>
        </fieldset>
    </div>
    <?= $button ?>
</div>

<div id="dlg_detail" class="easyui-dialog" title="Detail Purchase Order (Preview)" data-options="closed: true, modal:true" style="width: 1100px; height: 500px; padding:10px; top: 20px;">
    <iframe id="printout_mail" src="" style="width: 100%; height:420px; border: 0;"></iframe>
    <div style="width: 15px; height: 15px; background: red; float: left; margin-right: 20px;"></div> <b>Not Approved</b>
</div>

<iframe id="printout" src="" style="width: 100%;" hidden></iframe>

<script>
    //Add Data
    function generate() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_vendor_id = $("#filter_vendor_id").combobox('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');

        $.messager.confirm('Confirm','Are you sure you want to save this data?',function(r){
            if (r){
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
                    url: "<?= base_url('planning/os_po_verification/readVerificationPo') ?>",
                    data: "filter_from=" + window.btoa(filter_from) +
                        "&filter_vendor_id=" + window.btoa(filter_vendor_id) +
                        "&filter_product_family=" + window.btoa(filter_product_family),
                    dataType: "json",
                    success: function(rows) {
                        Swal.close();

                        if(rows.length > 0){
                            requestData(rows.length, rows);
                        }else{
                            Swal.fire('Not Found!', 'Data Verification PO Not Found!', 'error');
                        }

                        function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                            if (value < 100) {
                                value = Math.floor((number / total) * 100);
                                $('#p_upload').progressbar('setValue', value);
                                $('#p_start').html(number);
                                $('#p_finish').html(total);

                                $.post('<?= base_url('planning/os_po_verification/create') ?>', {
                                    data: json[number - 1], cutoff_date: filter_from
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
    }

    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_vendor_id = $("#filter_vendor_id").combobox('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_vendor_id=" + window.btoa(filter_vendor_id) +
            "&filter_product_family=" + window.btoa(filter_product_family);

        $('#dg').datagrid({
            url: '<?= base_url('planning/os_po_verification/datatables') ?>' + url,
        });

        $("#printout").attr('src', '<?= base_url('planning/os_po_verification/print') ?>' + url);
    }

    function datatableDetails(ven_id, pfm_id, ven_name, pfm_name){
        $("#dlg_detail").dialog('open');

        var filter_from = $("#filter_from").datebox('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_vendor_id=" + window.btoa(ven_id) +
            "&filter_product_family=" + window.btoa(pfm_id);

        $("#printout_mail").attr('src', '<?= base_url('planning/os_po_verification/datatableDetails') ?>' + url);
    }

    function sendMail(ven_id, pfm_id, ven_name, pfm_name){
        var filter_from = $("#filter_from").datebox('getValue');

        $.ajax({
            type: "get",
            url: "<?= base_url('planning/os_po_verification/sendMail') ?>",
            data: "filter_from=" + window.btoa(filter_from) +
                "&filter_vendor_id=" + window.btoa(ven_id) +
                "&filter_product_family=" + window.btoa(pfm_id),
            dataType: "json",
            success: function(rows) {
                Swal.fire(rows.title, rows.message, rows.theme);
            }
        });

    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        $('#dg').datagrid({
            url: '<?= base_url('planning/sales_order_os/datatables') ?>',
            rownumbers: true,
            pagination: true,
            fit: true
        });

        $('#filter_vendor_id').combobox({
            url: '<?php echo base_url('planning/os_po_verification/readVendors'); ?>',
            valueField: 'number',
            textField: 'name',
            prompt: 'Choose Vendor',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $('#filter_product_family').combobox({
            url: '<?php echo base_url('planning/os_po_verification/readProdfam'); ?>',
            valueField: 'pfm_id',
            textField: 'pfm_name',
            prompt: 'Choose Product Family',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
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

    function cellFormatterDetail(value, row) {
        var linked = "datatableDetails('" + row.ven_id + "','" + row.pfm_id + "','" + row.ven_name + "','" + row.pfm_name + "')";
        return '<a href="#" style="background-color: blue; color: white; padding: 20px; text-align: center; text-decoration: none;" onClick="'+linked+'">View</a>';
    };

    function cellFormatterSend(value, row) {
        if(value == "NOT APPROVE"){
            return "<b style='color:red;'>NOT APPROVED</b>";
        }else{
            var linked = "sendMail('" + row.ven_id + "','" + row.pfm_id + "','" + row.ven_name + "','" + row.pfm_name + "')";
            //return '<a href="#" style="background-color: green; color: white; padding: 20px; text-align: center; text-decoration: none;" onClick="'+linked+'">Send</a>';
            return "<b style='color:red;'>DISABLE</b>";
        }
    };

    function cellFormatterStatus(value, row) {
        if(value == 0){
            return "<b style='color:red;'>NOT SEND</b>";
        }else{
            return "<b style='color:green;'>DELIVERED</b>";
        }
    };
</script>