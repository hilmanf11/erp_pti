<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'po_no',width:150,halign:'center'">PO No</th>
            <th rowspan="2" data-options="field:'supplier_name',width:250,halign:'center'">Supplier Name</th>
            <th rowspan="2" data-options="field:'item_id',width:150,halign:'center'">Product No</th>
            <th rowspan="2" data-options="field:'item_name',width:150,halign:'center'">Product Name</th>
            <th rowspan="2" data-options="field:'product_family',width:150,halign:'center'">Product<br>Family</th>
            <th rowspan="2" data-options="field:'uom',width:80,halign:'center'">UoM</th>
            <th rowspan="2" data-options="field:'qty',width:100,halign:'center',align:'right',formatter:numberformat">Qty</th>
            <th rowspan="2" data-options="field:'delivery',width:100,halign:'center',align:'right',formatter:numberformat">Plan Delivery</th>
            <th rowspan="2" data-options="field:'etd',width:100,align:'center'">ETD</th>
            <th rowspan="2" data-options="field:'eta',width:100,align:'center'">ETA</th>
            <th rowspan="2" data-options="field:'document_no',width:150,halign:'center'">Document No</th>
            <th rowspan="2" data-options="field:'awb',width:150,halign:'center'">AWB</th>
            <th rowspan="2" data-options="field:'receive',width:100,halign:'center',align:'right',formatter:numberformat">Receive</th>
            <th rowspan="2" data-options="field:'balance',width:100,halign:'center',align:'right',formatter:numberformat">Balance</th>
            <!-- <th rowspan="2" data-options="field:'status',width:100,align:'center',styler:cellStyler, formatter:cellFormatter">Status</th> -->
            <th colspan="2" data-options="field:'',width:100,halign:'center'">Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'">Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:100,align:'center'">By</th>
            <th data-options="field:'created_date',width:150,align:'center'">Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'">By</th>
            <th data-options="field:'updated_date',width:150,align:'center'">Date</th>
        </tr>
    </thead>
</table>
<div id="toolbar" style="height: 235px; padding: 10px;">
    <fieldset style="width:70%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <legend><b>Filter Data</b></legend>
        <div style="width: 50%;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Period (ETA)</span>
                <input style="width:28%;" id="filter_from" class="easyui-datebox" value="<?php echo date('Y-m-01'); ?>" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                <input style="width:28%;" id="filter_to" class="easyui-datebox" value="<?php echo date('Y-m-t'); ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Supplier</span>
                <input style="width:60%;" id="filter_supplier" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product Family</span>
                <input style="width:60%;" id="filter_product_family" class="easyui-combobox" panelHeight="auto">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </div>
        <div style="width: 50%;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">PO No</span>
                <input style="width:60%;" id="filter_po_no" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" id="filter_item_id" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Document No</span>
                <input style="width:60%;" id="filter_document" class="easyui-textbox">
            </div>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">PO Status</span>
                <select style="width:30%;" id="filter_status" class="easyui-combobox" panelHeight="auto">
                    <option value="">All</option>
                    <option value="0">Open</option>
                    <option value="1">Close</option>
                </select>
            </div>
        </div>
    </fieldset>
    <?= $button ?>
</div>
<div id="dlg_insert" class="easyui-dialog" title="Update Data" data-options="closed: true,modal:true" style="width: 800px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier name</span>
                    <input style="width:60%;" name="supplier_id" id="supplier_id" required class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" name="product_family" id="product_family" required class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">PO No</span>
                    <input style="width:60%;" name="po_no" id="po_no" required class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">PO Date</span>
                    <input style="width:60%;" name="po_date" id="po_date" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser,editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" name="item_id" id="item_id" required class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Name</span>
                    <input style="width:60%;" name="item_name" id="item_name" readonly class="easyui-textbox">
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">UoM</span>
                    <input style="width:60%;" name="uom" id="uom" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Qty</span>
                    <input style="width:30%;" name="qty" id="qty" readonly class="easyui-numberbox" data-options="groupSeparator:'.'">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Plan Delivery</span>
                    <input style="width:30%;" name="delivery" id="delivery" required class="easyui-numberbox" data-options="groupSeparator:'.'">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">ETA</span>
                    <input style="width:60%;" name="eta" id="eta" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser,editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">ETD</span>
                    <input style="width:60%;" name="etd" id="etd" class="easyui-datebox" required data-options="formatter:myformatter,parser:myparser,editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Document No</span>
                    <input style="width:60%;" name="document_no" id="document_no" required class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">AWB</span>
                    <input style="width:60%;" name="awb" id="awb" class="easyui-textbox">
                </div>
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
<iframe id="printout" src="" style="width: 100%;" hidden></iframe>
<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('planning/delivery_confirm/create') ?>';
        $('#frm_insert').form('clear');
    }
    //Edit Data
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('planning/delivery_confirm/update') ?>?id=' + btoa(row.id);
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
                            url: '<?= base_url('planning/delivery_confirm/delete') ?>',
                            data: {
                                id: row.id,
                                po_no: row.po_no,
                                item_id: row.item_id
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
        window.location.assign('<?= base_url('template/tmp_delivery_confirm.xls') ?>');
    }

    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_supplier = $("#filter_supplier").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');
        var filter_po_no = $("#filter_po_no").combobox('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');
        var filter_item_id = $("#filter_item_id").combogrid('getValue');
        var filter_document = $("#filter_document").textbox('getValue');

        var url = "?filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_status=" + window.btoa(filter_status) +
            "&filter_po_no=" + window.btoa(filter_po_no) +
            "&filter_product_family=" + window.btoa(filter_product_family) +
            "&filter_item_id=" + window.btoa(filter_item_id) +
            "&filter_document=" + window.btoa(filter_document) +
            "&filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to);

        $('#dg').datagrid({
            url: '<?= base_url('planning/delivery_confirm/datatables') ?>' + url,
            pagination: true,
            rownumbers: true,
            fit: true
        });
        $("#printout").attr('src', '<?= base_url('planning/delivery_confirm/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_supplier = $("#filter_supplier").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');
        var filter_po_no = $("#filter_po_no").combobox('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');
        var filter_item_id = $("#filter_item_id").combogrid('getValue');
        var filter_document = $("#filter_document").textbox('getValue');

        var url = "?filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_status=" + window.btoa(filter_status) +
            "&filter_po_no=" + window.btoa(filter_po_no) +
            "&filter_product_family=" + window.btoa(filter_product_family) +
            "&filter_item_id=" + window.btoa(filter_item_id) +
            "&filter_document=" + window.btoa(filter_document) +
            "&filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to);
        window.location.assign('<?= base_url('planning/delivery_confirm/print/excel') ?>' + url);
    }

    function reload() {
        window.location.reload();
    }
    $(function() {
        filter();
        //Save Data
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    var qty = $("#qty").numberbox('getValue');
                    var delivery = $("#delivery").numberbox('getValue');
                    if (parseInt(qty) >= parseInt(delivery)) {
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
                    } else {
                        toastr.error("Delivery must not be greater than qty");
                    }
                }
            }]
        });
        //Upload Data
        $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('planning/delivery_confirm/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('planning/delivery_confirm/upload') ?>',
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
                                    $.post('<?= base_url('planning/delivery_confirm/uploadCreate') ?>', {
                                        data: json[number - 1]
                                    }, function(note) {
                                        var result = eval('(' + note + ')');
                                        if (result.theme == "success") {
                                            var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                        } else {
                                            var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                                            $.ajax({
                                                type: "POST",
                                                async: true,
                                                url: "<?= base_url('planning/delivery_confirm/uploadcreateFailed') ?>",
                                                data: {
                                                    data: json[number - 1],
                                                    message: result.message
                                                },
                                                cache: false
                                            });
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
        $('#filter_supplier').combobox({
            url: '<?php echo base_url('planning/suppliers/reads'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Supplier',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(supp) {
                $('#filter_product_family').combobox({
                    url: '<?php echo base_url('planning/mst_item/readFamily'); ?>',
                    valueField: 'name',
                    textField: 'name',
                    prompt: 'Choose Product Family',
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                    onSelect: function(po) {
                        $('#filter_po_no').combobox({
                            url: '<?php echo base_url('planning/delivery_confirm/readFilterPo/'); ?>' + btoa(supp.id) + "/" + btoa(po.name),
                            valueField: 'po_no',
                            textField: 'po_no',
                            prompt: 'Choose PO No',
                            icons: [{
                                iconCls: 'icon-clear',
                                handler: function(e) {
                                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                                }
                            }],
                        });
                        $('#filter_item_id').combogrid({
                            url: '<?= base_url('planning/mst_item/reads/') ?>' + po.number,
                            panelWidth: 400,
                            idField: 'item_id',
                            textField: 'item_id',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: "Choose Product No",
                            columns: [
                                [{
                                    field: 'item_id',
                                    title: 'Product No',
                                    width: 200
                                }, {
                                    field: 'item_name',
                                    title: 'Product Name',
                                    width: 150
                                }]
                            ],
                            icons: [{
                                iconCls: 'icon-clear',
                                handler: function(e) {
                                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                                }
                            }],
                        });
                    }
                });
            }
        });
        $('#supplier_id').combobox({
            url: '<?php echo base_url('planning/delivery_confirm/readSupplier'); ?>',
            valueField: 'supplier_id',
            textField: 'name',
            prompt: 'Choose Supplier',
            onSelect: function(supplier) {
                $('#product_family').combobox({
                    url: '<?php echo base_url('planning/delivery_confirm/readProductFamily/'); ?>' + window.btoa(supplier.supplier_id),
                    valueField: 'product_family_name',
                    textField: 'product_family_name',
                    prompt: 'Choose Product Family',
                    onSelect: function(prodfam) {
                        $('#po_no').combobox({
                            url: '<?php echo base_url('planning/delivery_confirm/readPo/'); ?>' + window.btoa(supplier.supplier_id) + "/" + window.btoa(prodfam.product_family_name),
                            valueField: 'purchase_order_no',
                            textField: 'purchase_order_no',
                            prompt: 'Choose PO No',
                            onSelect: function(po) {
                                $("#po_date").datebox('setValue', po.purchase_order_date);
                                $('#item_id').combogrid({
                                    url: '<?= base_url('planning/delivery_confirm/readProducts/') ?>' + btoa(po.purchase_order_no),
                                    panelWidth: 400,
                                    idField: 'item_id',
                                    textField: 'item_id',
                                    mode: 'remote',
                                    fitColumns: true,
                                    prompt: "Choose Product No",
                                    columns: [
                                        [{
                                            field: 'item_id',
                                            title: 'Product No',
                                            width: 200
                                        }, {
                                            field: 'item_name',
                                            title: 'Product Name',
                                            width: 150
                                        }]
                                    ],
                                    onSelect: function(row, item) {
                                        $("#item_name").textbox('setValue', item.item_name);
                                        $("#uom").textbox('setValue', item.uom);
                                        $("#qty").numberbox('setValue', item.qty);
                                        $("#delivery").numberbox('setValue', Math.abs(item.qty - item.delivery));
                                        $("#eta").datebox('setValue', item.eta);
                                    }
                                });
                            }
                        });
                    }
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
    //CELLSTYLE STATUS
    function cellStyler(value, row, index) {
        if (value == "OPEN") {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }
    //FORMATTER STATUS
    function cellFormatter(value) {
        if (value == "OPEN") {
            return 'OPEN';
        } else {
            return 'CLOSED';
        }
    };

    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0
        });
        return "<b>" + formatter.format(value) + "</b>";
    }
</script>