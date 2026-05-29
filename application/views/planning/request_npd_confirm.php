<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
    <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'status',width:80,align:'center',styler:cellStyler, formatter:cellFormatter">Convert PR</th>
            <th rowspan="2" data-options="field:'confirm',width:80,align:'center',styler:cellStyler2, formatter:cellFormatter2">Status</th>
            <th rowspan="2" data-options="field:'request_no',width:120,halign:'center'">Request No</th>
            <th rowspan="2" data-options="field:'request_by',width:150,halign:'center'">Request By</th>
            <th rowspan="2" data-options="field:'request_date',width:100,align:'center'">Request Date</th>
            <th rowspan="2" data-options="field:'category',width:100,align:'center'">Category</th>
            <th rowspan="2" data-options="field:'request_reason',width:350,halign:'center'">Remarks</th>
            <th rowspan="2" data-options="field:'total_qty',width:100,halign:'center',align:'right',formatter:numberformat">Total Qty</th>
            <th rowspan="2" data-options="field:'detail',width:80,halign:'center',formatter:BtnDetail">Detail</th>
            <th colspan="2" data-options="field:'create',width:100,halign:'center'">Created</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:120,align:'center'">By</th>
            <th data-options="field:'created_date',width:150,align:'center'">Date</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 200px; padding:10px;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 70%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Request Date</span>
                    <input style="width:28%;" id="filter_from" class="easyui-datebox" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                    <input style="width:28%;" id="filter_to" class="easyui-datebox" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Request No</span>
                    <input style="width:60%;" id="filter_request_no" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Request By</span>
                    <input style="width:60%;" id="filter_request_by" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part No</span>
                    <input style="width:60%;" id="filter_item_npd_id" class="easyui-textbox">
                </div>
            </div>
        </fieldset>
    </div>
    <?= $button ?>
</div>

<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="saveData()"><i class="fa fa-check"></i> Save All</a>
</div>

<div id="dlg_request" class="easyui-dialog" title="Detail Request NPD" data-options="closed: true,modal:true,fit:true" style="width: 100%; top: 0px; left: 0px;">
    <table id="dg_request" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar2" showFooter="true">
        <thead>
            <tr>
                <th rowspan="2" data-options="field:'item_npd_id',width:150,halign:'center'">Part No</th>
                <th rowspan="2" data-options="field:'item_npd_name',width:150,halign:'center'">Part Name</th>
                <th rowspan="2" data-options="field:'product_family',width:100,halign:'center'">Product Family</th>
                <th rowspan="2" data-options="field:'maker',width:200,halign:'center'">Maker</th>
                <th rowspan="2" data-options="field:'uom',width:80,align:'center'">Uom</th>
                <th rowspan="2" data-options="field:'qty',width:100,halign:'center',align:'right',formatter:numberformat">Qty</th>
                <th rowspan="2" data-options="field:'requirement_date',width:100,align:'center'">Requirement<br>Date</th>
                <th rowspan="2" data-options="field:'confirm',width:100,align:'center', styler:cellStylerConfirm, formatter:cellFormatterConfirm, editor: {
                    type: 'combobox',
                    options: {
                        data: [
                            {id: 'CONFIRMED'},
                            {id: 'NOT EXIST'},
                            {id: 'FREE SAMPLE'},
                            {id: 'CANCEL'}
                        ],
                        valueField: 'id',
                        textField: 'id',
                        required: true,
                        panelHeight: 'auto'
                    }
                }">Confirm</th>
                <th rowspan="2" data-options="field:'confirm_reason',width:300,halign:'center',editor: {
                    type: 'textbox',
                    options: {
                        required: true
                    }
                }">Reason</th>
                <th colspan="2" data-options="field:'procurement',width:100,halign:'center'">Procurement</th>
            </tr>
            <tr>
                <th data-options="field:'supplier_id',width:200,halign:'center',editor: {
                    type: 'combogrid',
                    options: {
                        url: '<?= base_url('planning/suppliers/reads') ?>',
                        panelWidth: 420,
                        idField: 'id',
                        textField: 'name',
                        mode: 'remote',
                        fitColumns: true,
                        prompt: 'Select Supplier',
                        icons: [{
                            iconCls: 'icon-clear',
                            handler: function(e) {
                                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                            }
                        }],
                        columns: [
                            [{
                                field: 'number',
                                title: 'Vendor No',
                                width: 80
                            }, {
                                field: 'name',
                                title: 'Vendor Name',
                                width: 250
                            }, ]
                        ],
                        onSelect: function(value, rows) {
                            var dg = $('#dg_request');
                            var row = dg.datagrid('getSelected');
                            var rowIndex = dg.datagrid('getRowIndex', row);

                            var ed = dg.datagrid('getEditor', {
                                index: rowIndex,
                                field: 'item_id'
                            });

                            $(ed.target).combogrid({
                                url: '<?= base_url('planning/request_npd_confirm/readParts/') ?>' + btoa(rows.id),
                                panelWidth: 420,
                                idField: 'item_id',
                                textField: 'item_id',
                                mode: 'remote',
                                fitColumns: true,
                                prompt: 'Select Part No',
                                icons: [{
                                    iconCls: 'icon-clear',
                                    handler: function(e) {
                                        $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                                    }
                                }],
                                columns: [
                                    [{
                                        field: 'item_id',
                                        title: 'Part No',
                                        width: 120
                                    }, {
                                        field: 'item_name',
                                        title: 'Part Name',
                                        width: 200
                                    }, ]
                                ]
                            });
                        }
                    }
                }">Supplier Name</th>
                <th data-options="field:'item_id',width:150,halign:'center',editor: {type: 'combogrid'}">Part No</th>
            </tr>
        </thead>
    </table>
</div>

<!-- PDF -->
<iframe id="printout" src="" style="width: 100%;" hidden></iframe>

<script>
    function saveData(){
        $('#dg_request').datagrid('acceptChanges');
        var rows = $('#dg_request').datagrid('getRows');
        var totalrows = rows.length;

        for (let i = 0; i < totalrows; i++) {
            if (rows[i].item_npd_id) {
                $.ajax({
                    type: "post",
                    url: '<?= base_url('planning/request_npd_confirm/update') ?>',
                    data: {
                        id: rows[i].id,
                        confirm: rows[i].confirm,
                        confirm_reason: rows[i].confirm_reason,
                        supplier_id: rows[i].supplier_id,
                        item_id: rows[i].item_id
                    },
                    dataType: "json",
                    success: function(result) {
                        if (result.theme == "success") {
                            toastr.success(result.message, result.title);
                        } else {
                            toastr.error(result.message, result.title);
                        }
                    }
                });
            }
        }

        $('#dg_request').datagrid('reload');
        $('#dlg_request').dialog('close');
    }
    
    function details(request_no) {
        $("#dlg_request").dialog('open');
        var filter_item_npd_id = $("#filter_item_npd_id").textbox('getValue');
        
        var lastIndex;
        $('#dg_request').datagrid({
            url: '<?= base_url('planning/request_npd_confirm/datatableDetails/') ?>' + btoa(request_no) + "?filter_item_npd_id=" + btoa(filter_item_npd_id),
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            singleSelect: true,
            fit:true,
            onClickRow: function(rowIndex) {
                if ($('#dg_request').datagrid('validateRow', lastIndex)) {

                    if(lastIndex != undefined){
                        var ed = $('#dg_request').datagrid('getEditor', {
                            index: lastIndex,
                            field: 'item_id'
                        });

                        var ed2 = $('#dg_request').datagrid('getEditor', {
                            index: lastIndex,
                            field: 'confirm'
                        });

                        var item_id = $(ed.target).combogrid('getValue');
                        var confirm = $(ed2.target).textbox('getValue');

                        if(confirm == "CONFIRMED" && item_id == ""){
                            toastr.error("If status is CONFIRMED, Please Select Supplier and Part No");
                            return false;
                        }
                    }

                    if (lastIndex != rowIndex) {
                        $(this).datagrid('endEdit', lastIndex);
                        $(this).datagrid('beginEdit', rowIndex);
                    }
                    lastIndex = rowIndex;
                }
            },
        });
    }

    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_request_no = $("#filter_request_no").textbox('getValue');
        var filter_request_by = $("#filter_request_by").textbox('getValue');
        var filter_item_npd_id = $("#filter_item_npd_id").textbox('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_request_no=" + window.btoa(filter_request_no) +
            "&filter_request_by=" + window.btoa(filter_request_by) +
            "&filter_item_npd_id=" + window.btoa(filter_item_npd_id);

        if (filter_from == "" || filter_to == "") {
            toastr.warning("Please Choose Filter Date!", "Information");
        } else {
            $('#dg').datagrid({
                url: '<?= base_url('planning/request_npd_confirm/datatables') ?>' + url,
                pagination: true,
                clientPaging: false,
                remoteFilter: true,
                rownumbers: true,
                singleSelect: true,
                fit:true,
            });

            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('planning/request_npd_confirm/print') ?>' + url);
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_request_no = $("#filter_request_no").textbox('getValue');
        var filter_request_by = $("#filter_request_by").textbox('getValue');
        var filter_item_npd_id = $("#filter_item_npd_id").textbox('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_request_no=" + window.btoa(filter_request_no) +
            "&filter_request_by=" + window.btoa(filter_request_by) +
            "&filter_item_npd_id=" + window.btoa(filter_item_npd_id);

        if (filter_from == "" || filter_to == "") {
            toastr.warning("Please Choose Filter Date!", "Information");
        } else {
            window.location.assign('<?= base_url('planning/request_npd_confirm/print/excel') ?>' + url);
        }
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        filter();
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

    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0
        });

        return "<b>" + formatter.format(value) + "</b>";
    }

    function BtnDetail(val, row) {
        if (row.total_status > 0) {
            var details = "details('"+row.request_no+"')";
            return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" onClick="'+details+'"><i class="fa fa-eye"></i> View</a>';
        }else{
            return '-';
        }
    }

    function cellStylerApproval(value, row, index) {
        if (row.approved_to == null) {
            return 'background: #FF5F5F; color:white;';
        } else if(row.approved_to == ""){
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
        } else {
            return 'Checking';
        }
    };

    function cellStylerConfirm(value, row, index) {
        if (value == null || value == "") {
            return 'background: #ffcc6dff; color:white;';
        } else if(value == "CONFIRMED"){
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    function cellFormatterConfirm(value, row) {
        if (value == null || value == "") {
            return 'PENDING';
        } else if(value == "CONFIRMED"){
            return 'CONFIRMED';
        } else {
            return value;
        }
    };

    function cellStyler(value, row, index) {
        if (row.total_status == 0) {
            return 'background: #FF5F5F; color:white;';
         } else if (row.total_status == 1) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    function cellFormatter(val, row) {
        if (row.total_status == 0) {
            return 'CLOSE';
        } else if (row.total_status == 1) {
            return 'OPEN';
        } else {
            return 'CANCEL';
        }
    };

    function cellStyler2(value, row, index) {
        if (row.total_data == row.total_confirm) {
            return 'background: #FF5F5F; color:white;';
        } else {
            return 'background: #d6a336ff; color:white;';
        }
    }

    function cellFormatter2(val, row) {
        if (row.total_data == row.total_confirm) {
            return 'CLOSE';
        } else {
            return 'PENDING';
        }
    };
</script>