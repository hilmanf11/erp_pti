<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead frozen="true">
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'plant',width:50,halign:'center'">Plant</th>
            <th data-options="field:'supplier_name',width:250,halign:'center'">Vendor Name</th>
            <th data-options="field:'item_supplier',width:150,halign:'center'">Part Vendor</th>
        </tr>
    </thead>
    <thead>
        <tr>
            <th data-options="field:'item_id',width:200,halign:'center'">Part No</th>
            <th data-options="field:'item_name',width:150,halign:'center'">Part Name</th>
            <th data-options="field:'type',width:100,align:'center'">Vendor Type</th>
            <th data-options="field:'mpq',width:80,halign:'center',align:'right',formatter:numberformatComa">MPQ</th>
            <th data-options="field:'moq',width:80,halign:'center',align:'right',formatter:numberformatComa">MOQ</th>
            <th data-options="field:'leadtime',width:80,align:'center'">Leadtime<br></th>
            <th data-options="field:'maker_name',width:150,halign:'center'">Maker</th>
            <th data-options="field:'maker_type',width:100,halign:'center'">Maker Type</th>
            <th data-options="field:'price',width:80,halign:'center',align:'right',formatter:numberformat">Price</th>
            <th data-options="field:'currency',width:80,align:'center'">Currency</th>
            <th data-options="field:'assurance_price',width:100,halign:'center',align:'right',formatter:numberformat">Insurance</th>
            <th data-options="field:'transportation_price',width:100,halign:'center',align:'right',formatter:numberformat">Transportation</th>
            <th data-options="field:'import_duty_percentage',width:100,halign:'center',align:'right',formatter:numberformatComa">Import Duty %</th>
            <th data-options="field:'import_duty_price',width:100,halign:'center',align:'right',formatter:numberformat">Import Duty</th>
            <th data-options="field:'total_price',width:100,halign:'center',align:'right',formatter:numberformat">Total Price</th>

            <!-- <th rowspan="2" data-options="field:'item_id',width:200,halign:'center'">Part No</th>
            <th rowspan="2" data-options="field:'item_name',width:150,halign:'center'">Part Name</th>
            <th rowspan="2" data-options="field:'type',width:100,align:'center'">Vendor Type</th>
            <th rowspan="2" data-options="field:'mpq',width:80,halign:'center',align:'right',formatter:numberformatComa">MPQ</th>
            <th rowspan="2" data-options="field:'moq',width:80,halign:'center',align:'right',formatter:numberformatComa">MOQ</th>
            <th rowspan="2" data-options="field:'leadtime',width:80,align:'center'">Leadtime<br></th>
            <th rowspan="2" data-options="field:'maker_name',width:150,halign:'center'">Maker</th>
            <th rowspan="2" data-options="field:'maker_type',width:100,halign:'center'">Maker Type</th>
            <th rowspan="2" data-options="field:'price',width:80,halign:'center',align:'right',formatter:numberformat">Price</th>
            <th rowspan="2" data-options="field:'currency',width:80,align:'center'">Currency</th>
            <th colspan="3" data-options="field:'',width:100,halign:'center'">Approved</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th> -->
        </tr>
        <!-- <tr>
            <th data-options="field:'approved_to',width:80,align:'center', styler:cellStylerApproval, formatter:cellFormatterApproval">Status</th>
            <th data-options="field:'approved_by',width:100,align:'center'">By</th>
            <th data-options="field:'approved_date',width:120,align:'center'">Date</th>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr> -->
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 220px;">
    <div style="width: 100%;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Plant</span>
                    <input style="width:60%;" name="filter_plant" id="filter_plant" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Vendor</span>
                    <input style="width:60%;" name="filter_suppliers" id="filter_suppliers" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Maker</span>
                    <input style="width:60%;" name="filter_maker" id="filter_maker" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Category</span>
                    <input style="width:60%;" id="filter_category" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part No</span>
                    <input style="width:60%;" name="filter_items" id="filter_items" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part Vendor</span>
                    <input style="width:60%;" name="filter_item_vendor" id="filter_item_vendor" class="easyui-combobox">
                </div>
            </div>
            <div style="width: 30%; float: left;">
                <div class="fitem" style="margin-bottom: 5px;">
                    <span style="width:35%; display:inline-block;">Share Order</span>
                    <input style="width:60%;" name="filter_share_order" id="filter_share_order" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Doouble Item</span>
                    <input name="filter_check" id="filter_check" class="easyui-checkbox" value="1">
                </div>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>

<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1000px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 33%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Plant</span>
                    <input style="width:60%;" name="plant" id="plant" required="" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Vendor</span>
                    <input style="width:60%;" name="supplier_id" id="supplier_id" required="" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Category</span>
                    <input style="width:60%;" id="category" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part No</span>
                    <input style="width:60%;" name="item_id" id="item_id" required="" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part Name</span>
                    <input style="width:60%;" name="item_name" id="item_name" disabled class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part Vendor</span>
                    <input style="width:60%;" name="item_supplier" id="item_supplier" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">COO Name</span>
                    <input style="width:60%;" name="coo_name" id="coo_name" class="easyui-textbox">
                </div>
            </div>

            <div style="width: 33%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Maker</span>
                    <input style="width:60%;" name="maker_code" id="maker_code" required class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">MPQ</span>
                    <input style="width:60%;" name="mpq" class="easyui-numberbox" data-options="precision:2">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">MOQ</span>
                    <input style="width:60%;" name="moq" class="easyui-numberbox" data-options="precision:2">
                </div>
                <!-- <div class="fitem">
                    <span style="width:35%; display:inline-block;">Price</span>
                    <input style="width:60%;" name="price" required="" class="easyui-numberbox" data-options="precision:4">
                </div> -->
                <!-- <div class="fitem">
                    <span style="width:35%; display:inline-block;">Valid Date</span>
                    <input style="width:60%;" name="valid_date" class="easyui-datebox" required data-options="formatter:myformatter,parser:myparser, editable:false">
                </div> -->
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Landed Cost</span>
                    <input style="width:60%;" name="landed_cost" class="easyui-numberbox" data-options="precision:2">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Import Duty</span>
                    <input style="width:60%;" name="import_duty" class="easyui-numberbox" data-options="precision:2">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Leadtime</span>
                    <input style="width:60%;" name="leadtime" required="" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Share Order</span>
                    <input style="width:60%;" name="share_order" required="" class="easyui-numberbox">
                </div>
            </div>
            <div style="width: 33%; float: left;">
                <!-- <div class="fitem">
                    <span style="width:35%; display:inline-block;">Currency</span>
                    <input style="width:60%;" name="currency" required class="easyui-textbox">
                </div> -->
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Safety Stock (%)</span>
                    <input style="width:60%;" name="safety_stock" required="" class="easyui-numberbox" data-options="precision:2">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Calculate MPQ</span>
                    <select style="width:60%;" name="calculate" class="easyui-combobox" panelHeight="auto">
                        <option value="1">YES</option>
                        <option value="0">NO</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Remarks</span>
                    <input style="width:60%; height:70px;" name="remarks" required class="easyui-textbox" multiline="true">
                </div>
            </div>
        </fieldset>
    </form>
</div>

<!-- UPLOAD DATA -->
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
    <span style="float: left; color:green;">SUCCESS : <b id="p_success">0</b></span><span style="float: right; color:red;"> FAILED : <b id="p_failed">0</b></span>
    <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
    <div id="p_remarks" title="History Upload" class="easyui-panel" style="width:100%; height:200px; padding:10px; margin-top: 10px;">
        <ul id="remarks">
        </ul>
    </div>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('planning/marketing_price/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('planning/marketing_price/create') ?>';
        $('#frm_insert').form('clear');
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            if (row.header == "1") {
                toastr.error("Please Select Detail of Vendor <br>" + row.supplier_name);
            } else {
                $('#dlg_insert').dialog('open');
                $('#frm_insert').form('load', row);
                $("#category").combobox('setValue', row.pfm_name);
                url_save = '<?= base_url('planning/marketing_price/update') ?>?id=' + btoa(row.id);
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //DELETE DATA
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];

                        if (row.state == "closed") {
                            toastr.error("Please Select Detail of Vendor <br>" + row.supplier_name);
                        } else {
                            $.ajax({
                                method: 'post',
                                url: '<?= base_url('planning/marketing_price/delete') ?>',
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
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //UPLOAD DATA
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    //DOWNLOAD TEMPLATE UPLOAD
    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_marketing_price.xls') ?>');
    }

    //FILTER DATA
    function filter() {
        var filter_plant = $("#filter_plant").combogrid('getValue');
        var filter_category = $("#filter_category").combobox('getValue');
        var filter_suppliers = $("#filter_suppliers").combogrid('getValue');
        var filter_maker = $("#filter_maker").combogrid('getValue');
        var filter_items = $("#filter_items").combogrid('getValue');
        var filter_item_vendor = $("#filter_item_vendor").combobox('getValue');
        var filter_share_order = $("#filter_share_order").combobox('getValue');
        var filter_check = $("#filter_check").checkbox('options');

        var url = "?filter_suppliers=" + window.btoa(filter_suppliers) +
            "&filter_items=" + window.btoa(filter_items) +
            "&filter_category=" + window.btoa(filter_category) +
            "&filter_maker=" + window.btoa(filter_maker) +
            "&filter_item_vendor=" + window.btoa(filter_item_vendor) +
            "&filter_share_order=" + window.btoa(filter_share_order) +
            "&filter_check=" + filter_check.checked +
            "&filter_plant=" + window.btoa(filter_plant);

        $('#dg').datagrid({
            url: '<?= base_url('planning/marketing_price/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('planning/marketing_price/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        var filter_plant = $("#filter_plant").combogrid('getValue');
        var filter_category = $("#filter_category").combobox('getValue');
        var filter_suppliers = $("#filter_suppliers").combogrid('getValue');
        var filter_maker = $("#filter_maker").combogrid('getValue');
        var filter_items = $("#filter_items").combogrid('getValue');
        var filter_item_vendor = $("#filter_item_vendor").combobox('getValue');
        var filter_share_order = $("#filter_share_order").combobox('getValue');
        var filter_check = $("#filter_check").checkbox('options');

        var url = "?filter_suppliers=" + window.btoa(filter_suppliers) +
            "&filter_items=" + window.btoa(filter_items) +
            "&filter_category=" + window.btoa(filter_category) +
            "&filter_maker=" + window.btoa(filter_maker) +
            "&filter_item_vendor=" + window.btoa(filter_item_vendor) +
            "&filter_share_order=" + window.btoa(filter_share_order) +
            "&filter_check=" + filter_check.checked +
            "&filter_plant=" + window.btoa(filter_plant);

        window.location.assign('<?= base_url('planning/marketing_price/print/excel') ?>' + url);
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    function numberformat(value, row) {
        if (value != null) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 4
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function numberformatComa(value, row) {
        if (value != null) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function cellStylerApproval(value, row, index) {
        if (row.approved_to == "" || row.approved_to == null) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    function cellFormatterApproval(val, row) {
        if (row.approved_to == "" || row.approved_to == null) {
            return 'Approved';
        } else {
            return 'Checking';
        }
    };

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

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('planning/marketing_price/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        });

        //SAVE DATA
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

        $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('planning/marketing_price/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('planning/marketing_price/upload') ?>',
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
                            //Clear File
                            $.ajax({
                                url: "<?= base_url('planning/marketing_price/uploadclearFailed') ?>"
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
                                        url: "<?= base_url('planning/marketing_price/uploadCreate') ?>",
                                        data: {
                                            "data": json[number - 1]
                                        },
                                        cache: false,
                                        dataType: "json",
                                        success: function(result) {
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
                                                    url: "<?= base_url('planning/marketing_price/uploadcreateFailed') ?>",
                                                    data: {
                                                        data: json[number - 1],
                                                        message: result.message
                                                    },
                                                    cache: false
                                                });
                                                requestData(total, json, number + 1, value, success + 0, failed + 1);
                                            }
                                            $("#p_remarks").append(title + "<br>");
                                        }
                                    });
                                }
                            }
                        }
                    });
                }
            }]
        });

        $('#maker_code').combogrid({
            url: '<?php echo base_url('planning/mst_maker/reads'); ?>',
            panelWidth: 550,
            idField: 'number',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Maker",
            columns: [
                [{
                    field: 'number',
                    title: 'Code',
                    width: 100
                }, {
                    field: 'name',
                    title: 'Name',
                    width: 200
                }, {
                    field: 'type',
                    title: 'Type',
                    width: 100
                }, {
                    field: 'origin',
                    title: 'Origin',
                    width: 100
                }]
            ]
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
                }, {
                    field: 'name',
                    title: 'Name',
                    width: 200
                }]
            ]
        });

        //Get Vendor
        $('#filter_suppliers').combogrid({
            url: '<?= base_url('planning/suppliers/reads') ?>',
            panelWidth: 420,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Vendor",
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
            ]
        });

        $('#filter_maker').combogrid({
            url: '<?php echo base_url('planning/mst_maker/reads'); ?>',
            panelWidth: 550,
            idField: 'number',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Maker",
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
                }, {
                    field: 'name',
                    title: 'Name',
                    width: 200
                }, {
                    field: 'type',
                    title: 'Type',
                    width: 100
                }, {
                    field: 'origin',
                    title: 'Origin',
                    width: 100
                }]
            ]
        });

        $('#filter_item_vendor').combobox({
            url: '<?php echo base_url('planning/supplier_items/readVendor'); ?>',
            valueField: 'item_supplier',
            textField: 'item_supplier',
            prompt: 'Select Part Vendor',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $('#filter_share_order').combobox({
            url: '<?php echo base_url('planning/supplier_items/readShareOrder'); ?>',
            valueField: 'share_order',
            textField: 'share_order',
            prompt: 'Select Share Order',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $('#filter_category').combobox({
            url: '<?php echo base_url('planning/mst_item/readFamily'); ?>',
            valueField: 'number',
            textField: 'name',
            prompt: 'Select Category',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(row) {
                $('#filter_items').combogrid({
                    url: '<?= base_url('planning/mst_item/reads/') ?>' + row.number,
                    panelWidth: 420,
                    idField: 'item_id',
                    textField: 'item_id',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Select Part",
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
                            width: 100
                        }, {
                            field: 'item_name',
                            title: 'Part Name',
                            width: 200
                        }, ]
                    ]
                });
            }
        });

        $('#plant').combogrid({
            url: '<?php echo base_url('planning/plants/reads'); ?>',
            panelWidth: 350,
            idField: 'number',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Plant",
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
        $('#supplier_id').combogrid({
            url: '<?= base_url('planning/suppliers/reads') ?>',
            panelWidth: 700,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Vendor",
            columns: [
                [{
                    field: 'number',
                    title: 'Vendor No',
                    width: 80
                }, {
                    field: 'name',
                    title: 'Vendor Name',
                    width: 250
                }, {
                    field: 'type',
                    title: 'Type',
                    width: 100
                }, {
                    field: 'origin',
                    title: 'Origin',
                    width: 150
                }, ]
            ],
            onSelect: function(val, row) {
                $("#currency").textbox("setValue", row.currency);
            }
        });

        $

        $('#category').combobox({
            url: '<?php echo base_url('planning/mst_item/readFamily'); ?>',
            valueField: 'number',
            textField: 'name',
            prompt: 'Select Category',
            onSelect: function(row) {
                $('#item_id').combogrid({
                    url: '<?= base_url('planning/mst_item/reads/') ?>' + row.number,
                    panelWidth: 420,
                    idField: 'item_id',
                    textField: 'item_id',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Choose Part",
                    columns: [
                        [{
                            field: 'item_id',
                            title: 'Part No',
                            width: 100
                        }, {
                            field: 'item_name',
                            title: 'Part Name',
                            width: 200
                        }, ]
                    ],
                    onSelect: function(val, row) {
                        $("#item_name").textbox('setValue', row.item_name);
                    }
                });
            }
        });

        $('#filter_items').combogrid({
            url: '<?= base_url('planning/mst_item/reads'); ?>',
            panelWidth: 350,
            idField: 'item_id',
            textField: 'item_id',
            mode: 'remote',
            fitColumns: true,
            pagination: true,
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
                    width: 100
                }, ]
            ]
        });
    });
</script>