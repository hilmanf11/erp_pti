<style>
    @keyframes blink {
        0%, 50%, 100% {
            opacity: 1;
        }
        25%, 75% {
            opacity: 0;
        }
    }

    .blink {
        animation: blink 1s infinite;
        font-size: 20px !important;
        color: red;
        font-weight: bold;
    }
</style>

<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
                <th rowspan="2" field="ck" checkbox="true"></th>
                <th rowspan="2" data-options="field:'purchase_request_no',width:180,halign:'center'">PR No</th>
                <th rowspan="2" data-options="field:'purchase_request_date',width:100,halign:'center'">PR Date</th>
                <th rowspan="2" data-options="field:'supplier_name',width:250,halign:'center'">Vendor Name</th>
                <th rowspan="2" data-options="field:'product_family',width:120,halign:'center'">Product Family</th>
                <th rowspan="2" data-options="field:'total_pr',width:100,halign:'center',align:'right',formatter:numberformat">Total PR</th>
                <th rowspan="2" data-options="field:'detail',width:80,halign:'center',formatter:BtnDetail">Detail</th>
                <th colspan="2" data-options="field:'',width:100,halign:'center'">Create PO</th>
                <th colspan="2" data-options="field:'',width:100,halign:'center'">Confirm PR</th>
                <th colspan="3" data-options="field:'',width:100,halign:'center'">Approved</th>
                <th colspan="2" data-options="field:'',width:100,halign:'center'">Created</th>
            </tr>
            <tr>
                <th data-options="field:'send',width:120,align:'center',formatter:BtnSend">Create</th>
                <th data-options="field:'create_po',width:150,align:'center'">Date</th>
                <th data-options="field:'confirm',width:100,align:'center', styler:cellStylerConfirm, formatter:cellFormatterConfirm">Status</th>
                <th data-options="field:'confirm_date',width:150,align:'center'">Date</th>
                <th data-options="field:'approved_to',width:80,align:'center', styler:cellStylerApproval, formatter:cellFormatterApproval">Status</th>
                <th data-options="field:'approved_by',width:100,align:'center'">By</th>
                <th data-options="field:'approved_date',width:120,align:'center'">Date</th>
                <th data-options="field:'created_by',width:100,align:'center'">By</th>
                <th data-options="field:'created_date',width:120,align:'center'">Date</th>
            </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar3" style="height: auto; padding:10px;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Detail</b></legend>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Purchase Request No</span>
                    <input style="width:60%;" id="d_purchase_request_no" class="easyui-textbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" id="detailEdit" class="easyui-linkbutton" onclick="detailEdit()"><i class="fa fa-edit"></i> Edit Data</a>
                </div>
            </div>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier Name</span>
                    <input style="width:60%;" id="d_supplier" class="easyui-textbox" readonly>
                </div>
            </div>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" id="d_product_family" class="easyui-textbox" readonly>
                </div>
            </div>
        </fieldset>
    </div>
</div>

<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>

<div id="toolbar" style="height: 195px; padding:10px;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 60%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period Cutoff</span>
                    <input style="width:30%;" name="filter_month" id="filter_month" value="<?= date("m") ?>" class="easyui-combobox" data-options="prompt:'Month'">
                    <input style="width:30%;" name="filter_year" id="filter_year" value="<?= date("Y") ?>" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Revision</span>
                    <select style="width:60%;" id="filter_revision" class="easyui-combobox" panelHeight="auto">
                        <option value="0">0</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" id="filter_product_family" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Item ID</span>
                    <input style="width:60%;" id="filter_part_no" class="easyui-combogrid">
                </div>
            </div>
        </fieldset>
        <fieldset style="width: 30%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Generating Process</b></legend>

            <b>Calculation Share Order</b>
            <div id="p_upload" class="easyui-progressbar" style="width:100%;"></div>
            <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
            <br>
            <center>
                <a href="#" onclick="confirmPo()" id="confirmPo" style="text-decoration: none;"><div class="blink">Confirm Edit PO</div></a>
            </center>
        </fieldset>
    </div>
    <a href="javascript:;" class="easyui-linkbutton" plain="true" onclick="adds()"><i class="fa fa-plus"></i> Additional</a>
    <?= $button ?>
</div>

<div id="dlg_update" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 800px; padding:10px; top: 20px;">
    <form id="frm_update" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="float: left; width: 50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:30%;" name="p_month" disabled class="easyui-combobox" required data-options="prompt:'Month'">
                    <input style="width:30%;" name="p_year" disabled class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Revision</span>
                    <select style="width:60%;" name="revision" disabled required class="easyui-combobox" panelHeight="auto">
                        <option value="0">0</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                    </select>
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Vendor Name</span>
                    <input style="width:60%;" name="supplier_no" disabled class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Vendor Name</span>
                    <input style="width:60%;" name="supplier_name" disabled class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" name="product_family" disabled class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Item ID</span>
                    <input style="width:60%;" name="part_no" disabled class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Item Name</span>
                    <input style="width:60%;" name="part_name" disabled class="easyui-textbox">
                </div>
            </div>
            <div style="float: left; width: 50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Purchase Request No</span>
                    <input style="width:60%;" name="purchase_request_no" id="update_purchase_request_no" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">MPQ / MOQ</span>
                    <input style="width:30%;" name="mpq" id="mpq" readonly class="easyui-numberbox">
                    <input style="width:30%;" name="moq" id="moq" readonly class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Share Order</span>
                    <input style="width:30%;" name="share_order" id="share_order" readonly class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Leadtime</span>
                    <input style="width:30%;" name="leadtime" id="leadtime" readonly class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">MRP Result</span>
                    <input style="width:25%;" name="purchase_order" id="purchase_order" required="true" readonly class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Purchase Request</span>
                    <input style="width:25%;" id="purchase_request_edit" required="true" class="easyui-numberbox">
                    <input style="width:25%;" name="purchase_request" id="purchase_request" required="true" readonly class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Reason</span>
                    <input style="width:50%;" name="reason" id="reason" required class="easyui-textbox">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Status</span>
                    <input style="width:50%;" name="status" id="status" required class="easyui-textbox">
                </div>
            </div>
        </fieldset>
    </form>
</div>

<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1200px; height: 600px; padding:10px; top: 20px; left:10px;">
    <form id="frm_insert" method="post" novalidate>
        <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px;">
                <legend><b>Form Data</b></legend>
                <div style="float: left; width: 50%;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Period</span>
                        <input style="width:30%;" name="p_month" id="p_month" class="easyui-combobox" required data-options="prompt:'Month'">
                        <input style="width:30%;" name="p_year" id="p_year" required class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Revision</span>
                        <select style="width:60%;" name="revision" id="revision" required class="easyui-combobox" panelHeight="auto">
                            <option value="0">0</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                        </select>
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">PR No</span>
                        <input style="width:60%;" name="purchase_request_no" id="purchase_request_no" readonly required class="easyui-textbox">
                    </div>
                </div>
                <div style="float: left; width: 50%;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">PR Date</span>
                        <input style="width:60%;" name="purchase_request_date" id="purchase_request_date" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Vendor Name</span>
                        <input style="width:60%;" name="supplier_no" id="supplier_no" required class="easyui-combobox">
                    </div>
                    <div class="fitem" hidden>
                        <span style="width:35%; display:inline-block;">Vendor Name</span>
                        <input style="width:60%;" name="supplier_name" id="supplier_name" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Product Family</span>
                        <input style="width:60%;" name="product_family" id="product_family" required class="easyui-combobox">
                    </div>
                </div>
            </fieldset>
        </div>

        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Add Purchase Request Additional" toolbar="#toolbar2" data-options="singleSelect: true">
        </table>
    </form>
</div>

<div id="dlg_purchase_request" class="easyui-dialog" title="Detail Purchase Request" data-options="closed: true,modal:true" style="width: 100%; height: 600px; top: 0px; left: 0px;">
    <table id="dg_purchase_request" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar3" showFooter="true"></table>
</div>

<div id="dlg_confirm" class="easyui-dialog" title="Confrim Edit PO" data-options="closed: true,modal:true" style="width: 100%; height: 600px; top: 0px; left: 0px;">
    <table id="dg_confirm" class="easyui-datagrid" style="width:100%;">
        <thead>
            <tr>
                <th data-options="field:'confirm',width:100,halign:'center',formatter:BtnConfirm">Confirm</th>
                <th data-options="field:'p_month',width:50,halign:'center'">Month</th>
                <th data-options="field:'p_year',width:50,halign:'center'">Year</th>
                <th data-options="field:'revision',width:50,halign:'center'">Rev</th>
                <th data-options="field:'purchase_request_no',width:180,halign:'center'">PR No</th>
                <th data-options="field:'supplier_name',width:250,halign:'center'">Vendor Name</th>
                <th data-options="field:'product_family_name',width:150,halign:'center'">Product Family</th>
                <th data-options="field:'item_id',width:200,halign:'center'">Item ID</th>
                <th data-options="field:'item_name',width:150,halign:'center'">Item Name</th>
                <th data-options="field:'total_qty',width:100,halign:'center',align:'right',formatter:numberformat">Total Qty</th>
                <th data-options="field:'reason',width:250,halign:'center'">Reason</th>
            </tr>
        </thead>
    </table>
</div>

<!-- PDF -->
<iframe id="printout" src="" style="width: 100%;" hidden></iframe>

<script>
    function add() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');
        var filter_part_no = $("#filter_part_no").combogrid('getValue');

        if (filter_month != "" && filter_year != "" && filter_revision != "") {
            $.messager.prompt('Generate PR', 'Please input Password Generate, Be careful if data is generated for this period it will be deleted', function(r){
                if (r == "PURCHASEREQUEST"){
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
                        url: "<?= base_url('planning/purchase_request/getDataMrp') ?>",
                        data: "filter_month=" + window.btoa(filter_month) +
                            "&filter_year=" + window.btoa(filter_year) +
                            "&filter_revision=" + window.btoa(filter_revision) +
                            "&filter_product_family=" + window.btoa(filter_product_family) +
                            "&filter_product_no=" + window.btoa(filter_part_no),
                        dataType: "json",
                        success: function(rows) {
                            Swal.close();

                            if(rows['total'] > 0){
                                requestData(rows['total'], rows);

                                function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                                    if (value < 100) {
                                        value = Math.floor((number / total) * 100);
                                        $('#p_upload').progressbar('setValue', value);
                                        $('#p_start').html(number);
                                        $('#p_finish').html(total);

                                        $.post('<?= base_url('planning/purchase_request/create') ?>', {
                                            data: json[number - 1]
                                        }, function(note) {
                                            var result = eval('(' + note + ')');

                                            if (result.theme == "success") {
                                                requestData(total, json, number + 1, value, success + 1, failed + 0);
                                            } else {
                                                requestData(total, json, number + 1, value, success + 0, failed + 1);
                                            }

                                            if (value == 100) {
                                                Swal.fire('Good job!', 'Process Generate Data Completed!', 'success');
                                                filter();
                                            }
                                        }).fail(function(jqXHR, textStatus) {
                                            if (textStatus == "error") {
                                                toastr.error("Connection time out");

                                                requestData(total, json, number, value, success + 0, failed + 1);
                                            }
                                        });
                                    }
                                }
                            }else{
                                toastr.warning("Data MRP Not Found");
                            }
                        },
                        error: function(rows) {
                            Swal.fire('Failed!', 'Generate Purchase Requst is Failed, Please Try Again!', 'error');
                        }
                    });
                }
            });

            $('.messager-input').attr('type', 'password');
        } else {
            Swal.close();
            toastr.warning("Please Select Period & Revision", "Information");
        }
    }

    function adds(){
        $('#dlg_insert').dialog('open');
        $('#frm_insert').form('clear');

        $('#dg2').datagrid('loadData', []);

        $("#purchase_request_date").datebox('setValue', "<?= date("Y-m-d") ?>");
        $("#p_month").combobox('setValue', "<?= date("m") ?>");
        $("#p_year").combobox('setValue', "<?= date("Y") ?>");
        $("#revision").combobox('setValue', "0");
    }

    //INSERT ADD ROW
    function addTable(supplier, prodfam) {
        var lastIndex;
        var dg = $('#dg2').datagrid({
            columns: [
                [{
                    field: 'part_no',
                    width: 200,
                    halign: 'center',
                    title: "Item ID",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('planning/purchase_request/readParts/') ?>' + btoa(supplier) + "/" + btoa(prodfam),
                            panelWidth: 400,
                            idField: 'item_id',
                            textField: 'item_id',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: "Select Part No",
                            columns: [
                                [{
                                    field: 'item_id',
                                    title: 'Part No',
                                    width: 200
                                }, {
                                    field: 'item_name',
                                    title: 'Part Name',
                                    width: 200
                                }]
                            ],
                            onSelect: function(value, rows) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);
                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'part_name'
                                });
                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'mpq'
                                });
                                var ed3 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'moq'
                                });
                                var ed4 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'share_order'
                                });
                                var ed5 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'leadtime'
                                });
                                var ed6 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'purchase_request'
                                });

                                $(ed6.target).numberbox({
                                    onChange: function(val){
                                        var hasil = (Math.ceil(val / rows.mpq) * rows.mpq);
                                        $(ed6.target).numberbox('setValue', hasil);
                                    }
                                });

                                $(ed.target).textbox('setValue', rows.item_name);
                                $(ed2.target).numberbox('setValue', rows.mpq);
                                $(ed3.target).numberbox('setValue', rows.moq);
                                $(ed4.target).numberbox('setValue', rows.share_order);
                                $(ed5.target).numberbox('setValue', rows.leadtime);
                            }
                        }
                    }
                }, {
                    field: 'part_name',
                    width: 150,
                    halign: 'center',
                    title: "Item Name",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'mpq',
                    width: 80,
                    halign: 'center',
                    title: "MPQ",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                            readonly: true,
                        }
                    }
                }, {
                    field: 'moq',
                    width: 80,
                    halign: 'center',
                    title: "MOQ",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                            readonly: true,
                        }
                    }
                }, {
                    field: 'share_order',
                    width: 100,
                    halign: 'center',
                    title: "Share Order",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                            readonly: true,
                        }
                    }
                }, {
                    field: 'leadtime',
                    width: 80,
                    halign: 'center',
                    title: "Leadtime",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                            readonly: true,
                        }
                    }
                }, {
                    field: 'purchase_request',
                    width: 100,
                    halign: 'center',
                    title: "Purchase<br>Request",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                        }
                    }
                }, {
                    field: 'reason',
                    width: 200,
                    halign: 'center',
                    title: "Reason",
                    editor: {
                        type: 'textbox',
                        options: {
                            required: true,
                        }
                    }
                }]
            ],
            onClickRow: function(rowIndex) {
                if (lastIndex != rowIndex) {
                    $(this).datagrid('endEdit', lastIndex);
                    $(this).datagrid('beginEdit', rowIndex);
                }
                lastIndex = rowIndex;
            },
        });
    }
    var editIndex = undefined;

    function endEditing() {
        if (editIndex == undefined) {
            return true
        }
        if ($('#dg2').datagrid('validateRow', editIndex)) {
            $('#dg2').datagrid('endEdit', editIndex);
            editIndex = undefined;
            return true;
        } else {
            return false;
        }
    }

    function append() {
        var product_family = $("#product_family").combobox('getValue');
        if (product_family != "") {
            if (endEditing()) {
                $('#dg2').datagrid('appendRow', {
                    qty: '0'
                });
                editIndex = $('#dg2').datagrid('getRows').length - 1;
                $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
            }
        } else {
            toastr.error("Please Choose Product Family first");
        }
    }

    function removeit() {
        if (editIndex == undefined) {
            return
        }
        $('#dg2').datagrid('cancelEdit', editIndex).datagrid('deleteRow', editIndex);
        editIndex = undefined;
    }

    //Edit Data
    function update() {
        toastr.info("Not Ready this Function");
    }

    //Delete Data
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        if(row.purchase_request == 0){
                            $.ajax({
                                method: 'post',
                                url: '<?= base_url('planning/purchase_request/delete') ?>',
                                data: {
                                    purchase_request_no: row.purchase_request_no
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
                        }else{
                            toastr.error("Cannot delete data because qty > 0");
                        }
                    }
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function filter() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');
        var filter_part_no = $("#filter_part_no").combogrid('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_product_family=" + window.btoa(filter_product_family) +
            "&filter_part_no=" + window.btoa(filter_part_no);

        if (filter_month == "" || filter_year == "") {
            toastr.warning("Please Choose Filter Date!", "Information");
        } else {
            $('#dg').datagrid({
                url: '<?= base_url('planning/purchase_request/datatables') ?>' + url,
            });

            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('planning/purchase_request/print') ?>' + url);
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');
        var filter_part_no = $("#filter_part_no").combogrid('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_product_family=" + window.btoa(filter_product_family) +
            "&filter_part_no=" + window.btoa(filter_part_no);

        if (filter_month == "" || filter_year == "") {
            toastr.warning("Please Choose Filter Date!", "Information");
        } else {
            window.location.assign('<?= base_url('planning/purchase_request/print/excel') ?>' + url);
        }
    }

    function reload() {
        window.location.reload();
    }

    function detailEdit(){
        var row = $('#dg_purchase_request').datagrid('getSelected');
        if (row) {
            $('#dlg_update').dialog('open');
            $('#frm_update').form('load', row);

            $('#update_purchase_request_no').combogrid({
                url: '<?= base_url('planning/purchase_request/readPr/') ?>' + btoa(row.supplier_id) + '/' + btoa(row.product_family),
                panelWidth: 500,
                idField: 'purchase_request_no',
                textField: 'purchase_request_no',
                mode: 'remote',
                fitColumns: true,
                prompt: "Select Purchase Request No",
                columns: [
                    [{
                        field: 'purchase_request_no',
                        title: 'PR No',
                        width: 200
                    }, {
                        field: 'supplier_name',
                        title: 'Vendor Name',
                        width: 250
                    }]
                ]
            });

            $("#update_purchase_request_no").combogrid('setValue', row.purchase_request_no);

            $("#purchase_request_edit").numberbox({
                onChange: function(val){
                    // if(parseInt(val) <= parseInt(row.purchase_order)){
                        if(parseInt(val) == 0){
                            var hasil = Math.ceil(parseInt(val));
                        }else{
                            if(parseInt(val) > row.moq){
                                var hasil = (Math.ceil(parseInt(val) / row.mpq) * row.mpq);
                            }else{
                                var hasil = (Math.ceil(parseInt(val) / row.moq) * row.moq);
                            }
                        }
                            
                        $("#purchase_request").numberbox('setValue', hasil);
                    // }else{
                    //     toastr.error("Your Qty > Mrp Results");
                    //     $("#purchase_request").numberbox('setValue', row.purchase_order);
                    // }
                }
            });
            
            $('#status').textbox('setValue', "EDIT");
            // $('#p_month').combobox('disable');
            // $('#p_year').combobox('disable');
            // $('#revision').combobox('disable');
            // $('#supplier_no').combobox('disable');
            // $('#product_family').combobox('disable');
            // $('#part_no').combogrid('disable');
            // $('#part_name').textbox('disable');

            url_save = '<?= base_url('planning/purchase_request/update') ?>?id=' + btoa(row.id);
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function details(purchase_request_no, supplier, product_family, push="") {
        $("#dlg_purchase_request").dialog('open');

        $("#d_purchase_request_no").textbox('setValue', purchase_request_no);
        $("#d_supplier").textbox('setValue', supplier);
        $("#d_product_family").textbox('setValue', product_family);

        var revision = $("#filter_revision").combobox('getValue');
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_part_no = $("#filter_part_no").combogrid('getValue');

        $.ajax({
            type: "get",
            url: "<?= base_url('planning/purchase_request/readMonths') ?>",
            data: "month=" + filter_month + "&year=" + filter_year,
            dataType: "json",
            success: function(monthName) {
                $('#dg_purchase_request').datagrid({
                    url: '<?= base_url('planning/purchase_request/datatableDetails/') ?>' + btoa(purchase_request_no) + "/" + revision + "?filter_part_no=" + btoa(filter_part_no) + "&filter_month=" + btoa(filter_month) + "&filter_year=" + btoa(filter_year),
                    clientPaging: false,
                    remoteFilter: true,
                    rownumbers: true,
                    singleSelect: false,
                    fit:true,
                    columns: [
                        [{
                            field: 'ck',
                            checkbox: true,
                            rowspan: 2
                        }, {
                            field: 'part_no',
                            title: 'Item ID',
                            width: 150,
                            halign: 'center',
                            rowspan: 2
                        }, {
                            field: 'part_name',
                            title: 'Item Name',
                            width: 100,
                            align: 'center',
                            rowspan: 2
                        }, {
                            field: 'share_order',
                            title: 'Share<br>Order',
                            width: 80,
                            halign: 'center',
                            align: 'right',
                            rowspan: 2
                        }, {
                            field: 'mpq',
                            title: 'MPQ',
                            width: 80,
                            halign: 'center',
                            align: 'right',
                            rowspan: 2
                        },{
                            field: 'moq',
                            title: 'MOQ',
                            width: 80,
                            halign: 'center',
                            align: 'right',
                            rowspan: 2
                        },{
                            field: 'leadtime',
                            title: 'Leadtime',
                            width: 80,
                            halign: 'center',
                            align: 'right',
                            rowspan: 2
                        },{
                            field: 'total_need',
                            title: 'Total Need',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            rowspan: 2,
                            formatter: numberformat
                        },{
                            field: 'purchase_order',
                            title: 'MRP Result',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            rowspan: 2,
                            formatter: numberformat
                        },{
                            field: 'purchase_request',
                            title: 'Purchase<br>Request',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            rowspan: 2,
                            formatter: numberformat
                        },{
                            field: '',
                            title: 'Purchase History',
                            width: 200,
                            align: 'center',
                            colspan: 3
                        }, {
                            field: 'avg',
                            title: 'Xbar',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            rowspan: 2,
                            formatter: numberformat
                        }, {
                            field: '',
                            title: 'Forecast Historical',
                            width: 200,
                            align: 'center',
                            colspan: 6
                        }, {
                            field: '',
                            title: 'Forecast Supplier',
                            width: 100,
                            align: 'center',
                            colspan: 3
                        }, {
                            field: 'avg_forecast',
                            title: 'Xbar',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            rowspan: 2,
                            formatter: numberformat
                        }, {
                            field: 'reason',
                            title: 'Reason',
                            width: 200,
                            halign: 'center',
                            rowspan: 2,
                        }, {
                            field: 'status',
                            title: 'Status',
                            width: 80,
                            align: 'center',
                            rowspan: 2,
                        }],[{
                            field: 'm3',
                            title: monthName[0],
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }, {
                            field: 'm2',
                            title: monthName[1],
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }, {
                            field: 'm1',
                            title: monthName[2],
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }, {
                            field: 'forecast_m3',
                            title: monthName[0],
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }, {
                            field: 'forecast_m2',
                            title: monthName[1],
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }, {
                            field: 'forecast_m1',
                            title: monthName[2],
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }, {
                            field: 'forecast_plus_1',
                            title: monthName[4],
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }, {
                            field: 'forecast_plus_2',
                            title: monthName[5],
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }, {
                            field: 'forecast_plus_3',
                            title: monthName[6],
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }, {
                            field: 'prodplan_1',
                            title: 'M 1',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }, {
                            field: 'prodplan_2',
                            title: 'M 2',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }, {
                            field: 'prodplan_3',
                            title: 'M 3',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }]
                    ]
                });
            }
        });
    }

    function sendPo(purchase_request_no) {
        $.messager.prompt('Generate PO', 'Please input Password Generate, Be careful if data is generated for this period it will be deleted', function(r){
            if (r == "PURCHASEORDER"){
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
                    url: "<?= base_url('planning/purchase_orders/getData') ?>",
                    data: "purchase_request_no=" + window.btoa(purchase_request_no),
                    dataType: "json",
                    success: function(rows) {
                        Swal.close();

                        if(rows['total'] > 0){
                            requestData(rows['total'], rows);

                            function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                                if (value < 100) {
                                    value = Math.floor((number / total) * 100);
                                    $('#p_upload').progressbar('setValue', value);
                                    $('#p_start').html(number);
                                    $('#p_finish').html(total);

                                    $.post('<?= base_url('planning/purchase_orders/create') ?>', {
                                        data: json[number - 1]
                                    }, function(note) {
                                        var result = eval('(' + note + ')');

                                        if (result.theme == "success") {
                                            requestData(total, json, number + 1, value, success + 1, failed + 0);
                                        } else {
                                            requestData(total, json, number + 1, value, success + 0, failed + 1);
                                        }

                                        if (value == 100) {
                                            Swal.fire('Good job!', 'Process Generate Data Completed!', 'success');
                                            $("#dg").datagrid('reload');
                                        }
                                    }).fail(function(jqXHR, textStatus) {
                                        if (textStatus == "error") {
                                            toastr.error("Connection time out");

                                            requestData(total, json, number, value, success + 0, failed + 1);
                                        }
                                    });
                                }
                            }
                        }else{
                            toastr.warning("Data Purchase Requests Not Found");
                        }
                    },
                    error: function(rows) {
                        Swal.close();
                        Swal.fire('Failed!', 'Generate Purchase Requst is Failed, Please Try Again!', 'error');
                    }
                });
            }else{
                toastr.error("Password not Match");
            }
        });
    }

    function checkConfirmPo(){
        // $.ajax({
        //     type: "post",
        //     url: '<?= base_url('planning/purchase_request/datatableConfirm') ?>',
        //     dataType: "json",
        //     success: function (response) {
        //         if(response.total > 0){
        //             $("#confirmPo").show();
        //         }else{
                    $("#confirmPo").hide();
        //         }
        //     }
        // });
    }

    // checkConfirmPo();
    // setInterval(checkConfirmPo, 10000);

    function confirmPo() {
        $("#dlg_confirm").dialog('open');
        
        $('#dg_confirm').datagrid({
            url: '<?= base_url('planning/purchase_request/datatableConfirm') ?>',
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            singleSelect: false,
            fit:true,
        });
    }

    function confirm(purchase_request_no, item_id) {
        $.ajax({
            type: "post",
            url: '<?= base_url('planning/purchase_request/updateConfirm') ?>',
            data: "purchase_request_no=" + purchase_request_no + "&item_id=" + item_id,
            dataType: "json",
            success: function (response) {
                toastr.success("Data has been Confirmed");
                $("#dg_confirm").datagrid("reload");
            }
        });
    }

    $(function() {
    	$("#add").html("Generate");

        $('#dg').datagrid({
            url: '<?= base_url('planning/purchase_request/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        });

        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    var p_month = $("#p_month").combobox('getValue');
                    var p_year = $("#p_year").combobox('getValue');
                    var revision = $("#revision").combobox('getValue');
                    var supplier_no = $("#supplier_no").combobox('getValue');
                    var supplier_name = $("#supplier_name").textbox('getValue');
                    var purchase_request_no = $("#purchase_request_no").textbox('getValue');
                    var purchase_request_date = $("#purchase_request_date").datebox('getValue');
                    var product_family = $("#product_family").combobox('getValue');

                    if (supplier_no == "" || product_family == "") {
                        toastr.error("please complete your input data");
                    } else {
                        var rows = $('#dg2').datagrid('getRows');
                        var totalrows = rows.length;
                        endEditing();
                        for (let i = 0; i < totalrows; i++) {
                            if (rows[i].part_no) {
                                $.ajax({
                                    type: "post",
                                    url: '<?= base_url('planning/purchase_request/createAdds') ?>',
                                    data: {
                                        p_month: p_month,
                                        p_year: p_year,
                                        revision: revision,
                                        purchase_request_no: purchase_request_no,
                                        purchase_request_date: purchase_request_date,
                                        supplier_id: supplier_no,
                                        // supplier_name: supplier_name,
                                        product_family: product_family,
                                        part_no: rows[i].part_no,
                                        part_name: rows[i].part_name,
                                        mpq: rows[i].mpq,
                                        moq: rows[i].moq,
                                        share_order: rows[i].share_order,
                                        leadtime: rows[i].leadtime,
                                        purchase_request: rows[i].purchase_request,
                                        reason: rows[i].reason,
                                        status: "MANUAL"
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
                        $('#dg').datagrid('reload');
                        $('#dlg_insert').dialog('close');
                    }
                }
            }]
        });

        $('#dlg_update').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_update').form('submit', {
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

                            $('#dlg_update').dialog('close');
                            $('#dg_purchase_request').datagrid('reload');
                        }
                    });
                }
            }]
        });

        //ADD
        $('#p_month').combobox({
            url: '<?php echo base_url('planning/mst_data/readMonths'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Month',
        });

        $('#p_year').combobox({
            url: '<?php echo base_url('planning/mst_data/readYears'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Year',
        });

        $('#revision').combobox({
            url: '<?php echo base_url('planning/mst_data/readRevisions'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Revision',
        });

        $('#supplier_no').combobox({
            url: '<?php echo base_url('planning/purchase_request/readSupplier'); ?>',
            valueField: 'supplier_id',
            textField: 'supplier_name',
            prompt: 'Select Supplier',
            onSelect: function(supp){

                $("#supplier_name").textbox('setValue', supp.supplier_name);

                $('#product_family').combobox({
                    url: '<?php echo base_url('planning/purchase_request/readProductFamily/'); ?>' + btoa(supp.supplier_id),
                    valueField: 'product_family',
                    textField: 'product_family',
                    prompt: 'Select Product Family',
                    onSelect: function(row){
                        addTable(supp.supplier_id, row.product_family_no);
                        var p_month = $("#p_month").combobox('getValue');
                        var p_year = $("#p_year").combobox('getValue');
                        
                        var code = "PRMC-IND"+supp.supplier_no+p_year+p_month+"A"+row.product_family_no;
                        $.ajax({
                            type: "post",
                            url: "<?= base_url("planning/purchase_request/purchaseRequestNo/") ?>"+btoa(code)+"/adds",
                            dataType: "html",
                            success: function (response) {
                                $("#purchase_request_no").textbox('setValue', response);
                            }
                        });
                    }
                });
            }
        });

        //FILTER DATA
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
            }],
        });

        $('#filter_product_family').combobox({
            url: '<?php echo base_url('planning/generate_mrp/readProductFamily'); ?>',
            valueField: 'pfm_name',
            textField: 'pfm_name',
            prompt: 'Select Product Family',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(row){
                $('#filter_part_no').combogrid({
                    url: '<?= base_url('planning/generate_mrp/readParts/') ?>' + row.pfm_id,
                    panelWidth: 400,
                    idField: 'item_id',
                    textField: 'item_id',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Select Part No",
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
                            width: 200
                        }, {
                            field: 'item_name',
                            title: 'Part Name',
                            width: 200
                        }]
                    ]
                });
            }
        });

        // $('#filter_part_no').combogrid({
        //     url: '<?= base_url('planning/generate_mrp/readParts') ?>',
        //     panelWidth: 400,
        //     idField: 'item_id',
        //     textField: 'item_id',
        //     mode: 'remote',
        //     fitColumns: true,
        //     prompt: "Select Part No",
        //     icons: [{
        //         iconCls: 'icon-clear',
        //         handler: function(e) {
        //             $(e.data.target).combogrid('clear').combogrid('textbox').focus();
        //         }
        //     }],
        //     columns: [
        //         [{
        //             field: 'item_id',
        //             title: 'Part No',
        //             width: 200
        //         }, {
        //             field: 'item_name',
        //             title: 'Part Name',
        //             width: 200
        //         }]
        //     ]
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

        return formatter.format(value);
    }

    function BtnDetail(val, row) {
        var details = "details('"+row.purchase_request_no+"','"+row.supplier_name+"','"+row.product_family+"')";
        return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" onClick="'+details+'"><i class="fa fa-eye"></i> View</a>';
    }

    function BtnSend(val, row) {
        if (row.approved_to == null || row.approved_to == "") {
            var send = "sendPo('"+row.purchase_request_no+"')";
            return '<a class="btn btn-success w-100" style="pointer-events: visible; opacity:1;" onClick="'+send+'"><i class="fa fa-plus"></i> Create PO</a>';
        } else {
            return '-';
        }
    }

    function BtnConfirm(val, row) {
        var confirm = "confirm('"+row.purchase_request_no+"','"+row.item_id+"')";
        return '<a class="btn btn-success w-100" style="pointer-events: visible; opacity:1;" onClick="'+confirm+'"><i class="fa fa-check"></i> Confirm</a>';
    }

    function cellStylerConfirm(value, row, index) {
        if (value == 0) {
            return 'background: #FF5F5F; color:white;';
        } else {
            return 'background: #53D636; color:white;';
        }
    }

    function cellFormatterConfirm(val, row) {
        if (val == 0) {
            return 'Not Confirm';
        } else {
            return 'Confirmed';
        }
    };

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
</script>