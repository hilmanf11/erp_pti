    <table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
        <thead frozen="true">
            <th data-options="field:'part_no',width:200,halign:'center'">Part No</th>
            <th data-options="field:'part_name',width:100,halign:'center'">Part Name</th>
        </thead>
        <thead>
            <tr>
                <th rowspan="2" data-options="field:'product_family',width:100,halign:'center'">Product Family</th>
                <th rowspan="2" data-options="field:'vendor_name',width:250,halign:'center'">Vendor Name</th>
                <th rowspan="2" data-options="field:'vendor_type',width:100,halign:'center'">Vendor Type</th>
                <th rowspan="2" data-options="field:'vendor_origin',width:100,halign:'center'">Origin</th>
                <th rowspan="2" data-options="field:'leadtime',width:80,halign:'center'">Leadtime</th>
                <th rowspan="2" data-options="field:'share_order',width:100,halign:'center'">Share Order</th>
                <th rowspan="2" data-options="field:'mpq',width:80,halign:'center', formatter:numberformat">MPQ</th>
                <th rowspan="2" data-options="field:'moq',width:80,halign:'center', formatter:numberformat">MOQ</th>
                <th rowspan="2" data-options="field:'maker_name',width:200,halign:'center'">Maker Name</th>
                <th rowspan="2" data-options="field:'maker_type',width:100,halign:'center'">Maker Type</th>
                <!-- <th rowspan="2" data-options="field:'maker_origin',width:80,halign:'center'">Origin</th> -->
                <!-- <th rowspan="2" data-options="field:'forecast_xbar',width:100,halign:'center', formatter:numberformat">Xbar<br>(-4 Month)</th> -->
                <th colspan="4" data-options="field:'wo',width:240,halign:'center'">WORKORDER</th>
                <th rowspan="2" data-options="field:'total_stock',width:100,halign:'center',align:'right',formatter:numberformat">Stock WHS</th>
                <th rowspan="2" data-options="field:'qty_vendor',width:100,halign:'center',align:'right',formatter:numberformat">OS PO</th>
                <!-- <th colspan="14" data-options="field:'fcst',width:240,halign:'center'">FORECAST</th> -->
                <th colspan="13" data-options="field:'fcst',width:240,halign:'center'">FORECAST</th>
                <th colspan="3" data-options="field:'',width:100,halign:'center'"> Approved</th>
                <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
                <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
            </tr>
            <tr>
                <th data-options="field:'issued_1',width:100,halign:'center',align:'right',formatter:numberformat">WO M-1</th>
                <th data-options="field:'issued_2',width:100,halign:'center',align:'right',formatter:numberformat">WO M-2</th>
                <th data-options="field:'issued_3',width:100,halign:'center',align:'right',formatter:numberformat">WO M-3</th>
                <th data-options="field:'issued_avg',width:100,halign:'center',align:'right',formatter:numberformat">Xbar</th>
                <th data-options="field:'need_1',width:100,halign:'center',align:'right',formatter:numberformat">Need M1</th>
                <th data-options="field:'balance_1',width:100,halign:'center',align:'right',formatter:numberformat">Bal 1</th>
                <th data-options="field:'forecast_1',width:100,halign:'center',align:'right',formatter:numberformat">FC 1</th>
                <th data-options="field:'need_2',width:100,halign:'center',align:'right',formatter:numberformat">Need M2</th>
                <th data-options="field:'balance_2',width:100,halign:'center',align:'right',formatter:numberformat">Bal 2</th>
                <th data-options="field:'forecast_2',width:100,halign:'center',align:'right',formatter:numberformat">FC 2</th>
                <th data-options="field:'need_3',width:100,halign:'center',align:'right',formatter:numberformat">Need M3</th>
                <th data-options="field:'balance_3',width:100,halign:'center',align:'right',formatter:numberformat">Bal 3</th>
                <th data-options="field:'forecast_3',width:100,halign:'center',align:'right',formatter:numberformat">FC 3</th>
                <th data-options="field:'need_4',width:100,halign:'center',align:'right',formatter:numberformat">Need M4</th>
                <th data-options="field:'balance_4',width:100,halign:'center',align:'right',formatter:numberformat">Bal 4</th>
                <th data-options="field:'forecast_4',width:100,halign:'center',align:'right',formatter:numberformat">FC 4</th>
                <th data-options="field:'avg_need',width:100,halign:'center',align:'right',formatter:numberformat">Xbar</th>
                <!-- <th data-options="field:'btnView',width:80,halign:'center',align:'right',formatter:btnUpdateFc">#</th> -->
                <th data-options="field:'approved_to',width:80,align:'center', styler:cellStylerApproval, formatter:cellFormatterApproval">Status</th>
                <th data-options="field:'approved_by',width:80,align:'center'">By</th>
                <th data-options="field:'approved_date',width:120,align:'center'">Date</th>
                <th data-options="field:'created_by',width:120,align:'center'"> By</th>
                <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
                <th data-options="field:'updated_by',width:120,align:'center'"> By</th>
                <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
            </tr>
        </thead>
    </table>
    <div id="toolbar" style="height: 200px; padding:10px;">
        <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
            <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
                <legend><b>Filter Data</b></legend>
                <div style="width: 35%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Period Cutoff</span>
                        <input style="width:30%;" name="filter_month" value="<?= date("m") ?>" id="filter_month" class="easyui-combobox" data-options="prompt:'Month'">
                        <input style="width:30%;" name="filter_year" value="<?= date("Y") ?>" id="filter_year" class="easyui-combobox">
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
                        <a href="javascript:;" class="easyui-linkbutton" onclick="printForecast()"><i class="fa fa-print"></i> Print Forecast</a>
                    </div>
                </div>
                <div style="width: 30%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Supplier</span>
                        <input style="width:60%;" id="filter_supplier" class="easyui-combogrid">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Product Family</span>
                        <input style="width:60%;" id="filter_product_family" class="easyui-combobox">
                    </div>
                </div>
                <div style="width: 30%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Part No</span>
                        <input style="width:60%;" name="item_id" id="item_id" class="easyui-combogrid">
                    </div>
                </div>
            </fieldset>
        </div>
        <?= $button ?><a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="excel2()"><i class="fa fa-file"></i> Export Excel 2</a>
    </div>

    <div id="toolbar2">
        <!-- <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a> -->
    </div>

    <!-- Insert & Update -->
    <div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 90%; height: 400px; padding:10px; top: 20px;">
        <form id="frm_insert" method="post" novalidate>
            <fieldset style="width:60%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                <legend><b>Form Data</b></legend>
                <div style="width: 70%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Period Cutoff</span>
                        <input style="width:30%;" name="p_month" id="p_month" class="easyui-combobox" data-options="prompt:'Month'">
                        <input style="width:30%;" name="p_year" id="p_year" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Revision</span>
                        <select style="width:60%;" id="revision" name="revision" class="easyui-combobox" panelHeight="auto">
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
                        <a href="javascript:;" class="easyui-linkbutton" onclick="Preview()"><i class="fa fa-search"></i> Preview Data</a>
                        <a href="javascript:;" class="easyui-linkbutton" onclick="excelPreview()"><i class="fa fa-file"></i> Export to Excel</a>
                    </div>
                </div>
            </fieldset>
            <fieldset style="width:39%; height:140px; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                <legend><b>Saving Process</b></legend>
                <div id="p_upload" class="easyui-progressbar" style="width:100%;"></div>
                <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
            </fieldset>
            <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Material Lists" toolbar="#toolbar2"></table>
        </form>
    </div>

    <!-- Dialog fc -->
    <div id="dlg_fc" class="easyui-dialog" data-options="closed: true,modal:true" style="width: 500px; height: auto; padding:10px; top: 60px;">
        <form id="frm_fc" method="post" novalidate>
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                <legend><b>Form Data</b></legend>
                <div class="fitem" hidden>
                    <span style="width:35%; margin-bottom:5px; display:inline-block;">ID</span>
                    <input style="width:60%;" name="id" id="id" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; margin-bottom:5px; display:inline-block;">Part No</span>
                    <input style="width:60%;" name="item_id_fc" id="item_id_fc" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; margin-bottom:5px; display:inline-block;">FC 1</span>
                    <input style="width:30%;" name="forecast_1" id="forecast_1" required="" class="easyui-numberbox" data-options="precision:0">
                    <input style="width:30%;" name="forecast_1_mrp" id="forecast_1_mrp" disabled class="easyui-numberbox" data-options="precision:0">
                </div>
                <div class="fitem">
                    <span style="width:35%; margin-bottom:5px; display:inline-block;">FC 2</span>
                    <input style="width:30%;" name="forecast_2" id="forecast_2" required="" class="easyui-numberbox" data-options="precision:0">
                    <input style="width:30%;" name="forecast_2_mrp" id="forecast_2_mrp" disabled class="easyui-numberbox" data-options="precision:0">
                </div>
                <div class="fitem">
                    <span style="width:35%; margin-bottom:5px; display:inline-block;">FC 3</span>
                    <input style="width:30%;" name="forecast_3" id="forecast_3" required="" class="easyui-numberbox" data-options="precision:0">
                    <input style="width:30%;" name="forecast_3_mrp" id="forecast_3_mrp" disabled class="easyui-numberbox" data-options="precision:0">
                </div>
                <div class="fitem">
                    <span style="width:35%; margin-bottom:5px; display:inline-block;">FC 4</span>
                    <input style="width:30%;" name="forecast_4" id="forecast_4" required="" class="easyui-numberbox" data-options="precision:0">
                    <input style="width:30%;" name="forecast_4_mrp" id="forecast_4_mrp" disabled class="easyui-numberbox" data-options="precision:0">
                </div>
                <div class="fitem">
                    <span style="width:35%; margin-bottom:5px; display:inline-block;">Reason</span>
                    <input style="width:60%;" name="reason" id="reason" class="easyui-textbox">
                </div>
            </fieldset>
        </form>
    </div>
    <!-- PDF -->
    <iframe id="printout" src="" style="width: 100%;" hidden></iframe>
    <script>
        function add() {
            $('#dlg_insert').dialog('open');
            $('#dg2').datagrid('loadData', []);
            url_save = '<?= base_url('planning/bom/create') ?>';
            $('#frm_insert').form('clear');
            $('#p_month').combobox('setValue', '<?= date("m") ?>');
            $('#p_year').combobox('setValue', '<?= date("Y") ?>');
            $('#revision').combobox('setValue', '0');
            $('#p_upload').progressbar('setValue', '0');
            $('#p_start').html('0');
            $('#p_finish').html('0');
        }

        //DELETE DATA

        //DELETE DATA
        function deleted() {
            var rows = $('#dg').datagrid('getSelections');
            if (rows.length > 0) {
                $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                    if (r) {
                        for (var i = 0; i < rows.length; i++) {
                            var row = rows[i];
                            if (row.approved_to == "" || row.approved_to == null) {
                                toastr.error("Cannot delete data already Approved");
                            } else {
                                $.ajax({
                                    method: 'post',
                                    url: '<?= base_url('planning/forecast_supplier/delete') ?>',
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

        function filter() {
            var filter_month = $("#filter_month").combobox('getValue');
            var filter_year = $("#filter_year").combobox('getValue');
            var filter_revision = $("#filter_revision").combobox('getValue');
            var filter_product_family = $("#filter_product_family").combobox('getValue');
            var item_id = $("#item_id").combogrid('getValue');
            var filter_supplier = $("#filter_supplier").combogrid('getValue');
            var url = "?filter_month=" + window.btoa(filter_month) +
                "&filter_year=" + window.btoa(filter_year) +
                "&filter_revision=" + window.btoa(filter_revision) +
                "&filter_supplier=" + window.btoa(filter_supplier) +
                "&filter_product_family=" + window.btoa(filter_product_family) +
                "&item_id=" + window.btoa(item_id);
            if (filter_month == "" || filter_year == "") {
                toastr.warning("Please Choose Filter Date!", "Information");
            } else {
                $('#dg').datagrid('loading');
                $.ajax({
                    url: '<?= base_url('planning/forecast_supplier/datatables') ?>' + url,
                    type: 'POST',
                    dataType: 'json',
                    success: function(result) {
                        $('#dg').datagrid('loadData', result).datagrid('loaded');
                    }
                });
            }

            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('planning/forecast_supplier/print') ?>' + url);
        }

        function Preview() {
            var p_month = $("#p_month").combobox('getValue');
            var p_year = $("#p_year").combobox('getValue');
            var revision = $("#revision").combobox('getValue');
            var url = "?filter_month=" + window.btoa(p_month) +
                "&filter_year=" + window.btoa(p_year) +
                "&filter_revision=" + window.btoa(revision);
            if (p_month == "" || p_year == "" || revision == "") {
                toastr.warning("Please Choose Filter Date, Revision, and Supplier!", "Information");
            } else {
                addTable('<?= base_url('planning/forecast_supplier/datatableUpdates') ?>' + url);
            }
        }

        function excelPreview() {
            var p_month = $("#p_month").combobox('getValue');
            var p_year = $("#p_year").combobox('getValue');
            var revision = $("#revision").combobox('getValue');
            var url = "?filter_month=" + window.btoa(p_month) +
                "&filter_year=" + window.btoa(p_year) +
                "&filter_revision=" + window.btoa(revision);
            if (filter_month == "" || filter_year == "" || revision == "") {
                toastr.warning("Please Choose Filter Date, revision!", "Information");
            } else {
                window.location.assign('<?= base_url('planning/forecast_supplier/printPreview/excel') ?>' + url);
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
            var item_id = $("#item_id").combogrid('getValue');
            var filter_supplier = $("#filter_supplier").combogrid('getValue');
            var url = "?filter_month=" + window.btoa(filter_month) +
                "&filter_year=" + window.btoa(filter_year) +
                "&filter_revision=" + window.btoa(filter_revision) +
                "&filter_supplier=" + window.btoa(filter_supplier) +
                "&filter_product_family=" + window.btoa(filter_product_family) +
                "&item_id=" + window.btoa(item_id);
            if (filter_month == "" || filter_year == "") {
                toastr.warning("Please Choose Filter Date!", "Information");
            } else {
                window.location.assign('<?= base_url('planning/forecast_supplier/print/excel') ?>' + url);
            }
        }

        function excel2() {
            var filter_month = $("#filter_month").combobox('getValue');
            var filter_year = $("#filter_year").combobox('getValue');
            var filter_revision = $("#filter_revision").combobox('getValue');
            var filter_product_family = $("#filter_product_family").combobox('getValue');
            var item_id = $("#item_id").combogrid('getValue');
            var filter_supplier = $("#filter_supplier").combogrid('getValue');
            var url = "?filter_month=" + window.btoa(filter_month) +
                "&filter_year=" + window.btoa(filter_year) +
                "&filter_revision=" + window.btoa(filter_revision) +
                "&filter_supplier=" + window.btoa(filter_supplier) +
                "&filter_product_family=" + window.btoa(filter_product_family) +
                "&item_id=" + window.btoa(item_id);
            if (filter_supplier == "") {
                toastr.info("Please Select Supplier");
            } else {
                window.location.assign('<?= base_url('planning/forecast_supplier/print2/excel') ?>' + url);
            }
        }

        function addTable(link = "") {
            $('#dg2').datagrid({
                url: link,
                singleSelect: true,
                rownumbers: true,
                columns: [
                    [{
                            field: 'p_month',
                            hidden: true,
                            width: 80,
                            halign: 'center',
                            title: "Month",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            }
                        }, {
                            field: 'p_year',
                            hidden: true,
                            width: 80,
                            halign: 'center',
                            title: "Year",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            }
                        }, {
                            field: 'revision',
                            hidden: true,
                            width: 80,
                            halign: 'center',
                            title: "Revision",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            }
                        }, {
                            field: 'part_no',
                            width: 150,
                            halign: 'center',
                            title: "Part No",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            }
                        }, {
                            field: 'part_name',
                            width: 150,
                            halign: 'center',
                            title: "Part Name",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            }
                        }, {
                            field: 'product_family',
                            width: 150,
                            halign: 'center',
                            title: "Product Family",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            }
                        }, {
                            field: 'vendor_id',
                            hidden: true,
                            width: 150,
                            halign: 'center',
                            title: "Vendor ID",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            }
                        }, {
                            field: 'vendor_name',
                            width: 150,
                            halign: 'center',
                            title: "Vendor Name",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            }
                        }, {
                            field: 'vendor_type',
                            width: 150,
                            halign: 'center',
                            title: "Vendor Type",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            }
                        }, {
                            field: 'vendor_origin',
                            width: 150,
                            halign: 'center',
                            title: "Origin",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            }
                        }, {
                            field: 'leadtime',
                            width: 80,
                            halign: 'center',
                            title: "Leadtime",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            }
                        }, {
                            field: 'share_order',
                            width: 80,
                            halign: 'center',
                            align: 'right',
                            title: "Share",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            }
                        }, {
                            field: 'mpq',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            title: "MPQ",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            },
                            formatter: numberformat
                        }, {
                            field: 'moq',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            title: "MOQ",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            },
                            formatter: numberformat
                        }, {
                            field: 'maker_name',
                            width: 150,
                            halign: 'center',
                            title: "Maker Name",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            }
                        }, {
                            field: 'maker_type',
                            width: 150,
                            halign: 'center',
                            title: "Maker Type",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            }
                        }, {
                            field: 'total_stock',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            title: "Stock WHS",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            },
                            formatter: numberformat
                        }, {
                            field: 'qty_vendor',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            title: "OS PO",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            },
                            formatter: numberformat
                        }, {
                            field: 'issued_1',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            title: "WO M-1",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            },
                            formatter: numberformat
                        }, {
                            field: 'issued_2',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            title: "WO M-2",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            },
                            formatter: numberformat
                        }, {
                            field: 'issued_3',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            title: "WO M-3",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            },
                            formatter: numberformat
                        }, {
                            field: 'issued_avg',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            title: "Xbar",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            },
                            formatter: numberformat
                        }, {
                            field: 'need_1',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            title: "Need M1",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            },
                            formatter: numberformat
                        }, {
                            field: 'balance_1',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            title: "Bal 1",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            },
                            formatter: numberformat
                        }, {
                            field: 'forecast_1',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            title: "FC 1",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            },
                            formatter: numberformat
                        }, {
                            field: 'need_2',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            title: "Need M2",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            },
                            formatter: numberformat
                        }, {
                            field: 'balance_2',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            title: "Bal 2",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            },
                            formatter: numberformat
                        }, {
                            field: 'forecast_2',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            title: "FC 2",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            },
                            formatter: numberformat
                        }, {
                            field: 'need_3',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            title: "Need M3",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            },
                            formatter: numberformat
                        }, {
                            field: 'balance_3',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            title: "Bal 3",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            },
                            formatter: numberformat
                        }, {
                            field: 'forecast_3',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            title: "FC 3",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            },
                            formatter: numberformat
                        }, {
                            field: 'need_4',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            title: "Need M4",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            },
                            formatter: numberformat
                        }, {
                            field: 'balance_4',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            title: "Bal 4",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            },
                            formatter: numberformat
                        }, {
                            field: 'forecast_4',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            title: "FC 4",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            },
                            formatter: numberformat
                        },
                        {
                            hidden: true,
                            field: 'need_5',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            title: "Need M5",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            },
                            formatter: numberformat
                        }, {
                            hidden: true,
                            field: 'balance_5',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            title: "Bal 5",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            },
                            formatter: numberformat
                        }, {
                            hidden: true,
                            field: 'forecast_5',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            title: "FC 5",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            },
                            formatter: numberformat
                        },
                        {
                            hidden: true,
                            field: 'need_6',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            title: "Need M6",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            },
                            formatter: numberformat
                        }, {
                            hidden: true,
                            field: 'balance_6',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            title: "Bal 6",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            },
                            formatter: numberformat
                        }, {
                            hidden: true,
                            field: 'forecast_6',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            title: "FC 6",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            },
                            formatter: numberformat
                        },
                        {
                            field: 'avg_need',
                            align: 'right',
                            width: 100,
                            halign: 'center',
                            title: "Xbar",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            },
                            formatter: numberformat
                        },
                        {
                            field: 'purchase_order',
                            hidden: true,
                            align: 'right',
                            width: 100,
                            halign: 'center',
                            title: "Purchase Order",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            },
                            formatter: numberformat
                        },
                    ]
                ],
                // onClickCell: onClickCell
            });
        }

        function printForecast() {
            var filter_month = $("#filter_month").combobox('getValue');
            var filter_year = $("#filter_year").combobox('getValue');
            var filter_revision = $("#filter_revision").combobox('getValue');
            var filter_product_family = $("#filter_product_family").combobox('getValue');
            var item_id = $("#item_id").combogrid('getValue');
            var filter_supplier = $("#filter_supplier").combogrid('getValue');
            var url = "?filter_month=" + window.btoa(filter_month) +
                "&filter_year=" + window.btoa(filter_year) +
                "&filter_revision=" + window.btoa(filter_revision) +
                "&filter_supplier=" + window.btoa(filter_supplier) +
                "&filter_product_family=" + window.btoa(filter_product_family) +
                "&item_id=" + window.btoa(item_id);
            if (filter_supplier == "") {
                toastr.info("Please Select Supplier");
            } else {
                window.open("<?= base_url('planning/forecast_supplier/printForecast') ?>" + url, "_blank", 'noopener, noreferrer');
            }

        }

        function reload() {
            window.location.reload();
        }
        $(function() {
            $('#dg').datagrid({
                pagination: true,
                rownumbers: true,
                fit: true,
                pageList: [20, 50, 100, 500, 1000],
                pageSize: 20,
            }).datagrid('enableFilter');

            //SAVE DATA
            $('#dlg_insert').dialog({
                buttons: [{
                    text: 'Send to Approval',
                    iconCls: 'icon-ok',
                    handler: function() {
                        var json = $('#dg2').datagrid('getRows');
                        var totalrows = json.length;
                        // endEditing();

                        $.messager.confirm('Warning', 'Are you sure you want to Send to Approval?', function(r) {
                            if (r) {
                                $.messager.progress({
                                    title: 'Please waiting',
                                    msg: 'Loading data...',
                                    text: 'Processing' // optional
                                });

                                var p_month = $("#p_month").combobox('getValue');
                                var p_year = $("#p_year").combobox('getValue');
                                var revision = $("#revision").combobox('getValue');
                                $.ajax({
                                    url: '<?= base_url('planning/forecast_supplier/delete') ?>',
                                    data: {
                                        p_month: p_month,
                                        p_year: p_year,
                                        revision: revision
                                    },
                                    type: 'POST',
                                    dataType: 'json',
                                    success: function(result) {
                                        $.messager.progress('close');
                                        if (result.theme == "success") {
                                            requestData(totalrows, json);
                                        } else {
                                            var result = eval('(' + result + ')');
                                            toastr.error(result.message);
                                        }
                                    },
                                    error: function(jqXHR, textStatus, errorThrown) {
                                        toastr.error(jqXHR.statusText);
                                        $.messager.alert("Error", jqXHR.statusText, 'error');
                                        $.messager.progress('close');
                                    }
                                });
                            }
                        });

                        function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                            if (value < 100) {
                                value = Math.floor((number / total) * 100);
                                $('#p_upload').progressbar('setValue', value);
                                $('#p_start').html(number);
                                $('#p_finish').html(total);
                                var dataFinal = {
                                    p_month: json[number - 1].p_month,
                                    p_year: json[number - 1].p_year,
                                    revision: json[number - 1].revision,
                                    part_no: json[number - 1].part_no,
                                    part_name: json[number - 1].part_name,
                                    product_family: json[number - 1].product_family,
                                    vendor_id: json[number - 1].vendor_id,
                                    vendor_name: json[number - 1].vendor_name,
                                    vendor_type: json[number - 1].vendor_type,
                                    vendor_origin: json[number - 1].vendor_origin,
                                    leadtime: json[number - 1].leadtime,
                                    share_order: json[number - 1].share_order,
                                    mpq: json[number - 1].mpq,
                                    moq: json[number - 1].moq,
                                    maker_name: json[number - 1].maker_name,
                                    maker_type: json[number - 1].maker_type,
                                    total_stock: json[number - 1].total_stock,
                                    qty_vendor: json[number - 1].qty_vendor,
                                    issued_1: json[number - 1].issued_1,
                                    issued_2: json[number - 1].issued_2,
                                    issued_3: json[number - 1].issued_3,
                                    issued_avg: json[number - 1].issued_avg,
                                    need_1: json[number - 1].need_1,
                                    balance_1: json[number - 1].balance_1,
                                    forecast_1_mrp: json[number - 1].forecast_1,
                                    forecast_1: json[number - 1].forecast_1,
                                    need_2: json[number - 1].need_2,
                                    balance_2: json[number - 1].balance_2,
                                    forecast_2_mrp: json[number - 1].forecast_2,
                                    forecast_2: json[number - 1].forecast_2,
                                    need_3: json[number - 1].need_3,
                                    balance_3: json[number - 1].balance_3,
                                    forecast_3_mrp: json[number - 1].forecast_3,
                                    forecast_3: json[number - 1].forecast_3,
                                    need_4: json[number - 1].need_4,
                                    balance_4: json[number - 1].balance_4,
                                    forecast_4_mrp: json[number - 1].forecast_4,
                                    forecast_4: json[number - 1].forecast_4,
                                    need_5: json[number - 1].need_5,
                                    balance_5: json[number - 1].balance_5,
                                    forecast_5_mrp: json[number - 1].forecast_5,
                                    forecast_5: json[number - 1].forecast_5,
                                    need_6: json[number - 1].need_6,
                                    balance_6: json[number - 1].balance_6,
                                    forecast_6_mrp: json[number - 1].forecast_6,
                                    forecast_6: json[number - 1].forecast_6,
                                    avg_need: json[number - 1].avg_need,
                                    status: 'GENERATE',
                                };
                                $.post('<?= base_url('planning/forecast_supplier/create') ?>', dataFinal, function(note) {
                                    var result = eval('(' + note + ')');
                                    if (result.theme == "success") {
                                        Swal.close();
                                        requestData(total, json, number + 1, value, success + 1, failed + 0);
                                    } else {
                                        Swal.close();
                                        requestData(total, json, number + 1, value, success + 0, failed + 1);
                                    }
                                }).fail(function(jqXHR, textStatus) {
                                    Swal.fire({
                                        title: 'Connection Time Out, Re-connecting..',
                                        showConfirmButton: false,
                                        allowOutsideClick: false,
                                        allowEscapeKey: false,
                                        didOpen: () => {
                                            Swal.showLoading();
                                        },
                                    });
                                    // reconnect
                                    requestData(total, json, number, value, success + 0, failed + 1);
                                });
                            } else {
                                Swal.close();
                                Swal.fire({
                                    title: 'Data Saved Successfully',
                                    icon: 'success',
                                    confirmButtonText: 'Ok',
                                    allowOutsideClick: false,
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        // window.location.reload();
                                    }
                                });
                            }
                        }
                    }
                }]
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
            $('#filter_supplier').combogrid({
                url: '<?= base_url('planning/purchase_orders/readSupplier') ?>',
                panelWidth: 400,
                idField: 'supplier_no',
                textField: 'supplier_name',
                mode: 'remote',
                fitColumns: true,
                prompt: "Select Supplier",
                icons: [{
                    iconCls: 'icon-clear',
                    handler: function(e) {
                        $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                    }
                }],
                onSelect: function(row) {}
            });
            $('#item_id').combogrid({
                url: '<?= base_url('planning/mst_item/reads'); ?>',
                panelWidth: 350,
                idField: 'item_id',
                textField: 'item_id',
                mode: 'remote',
                fitColumns: true,
                pagination: true,
                prompt: "Select Part No 1",
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
                        },
                        {
                            field: 'item_name',
                            title: 'Part Name',
                            width: 100
                        }
                    ]
                ]
            });
            $('#filter_product_family').combobox({
                url: '<?= base_url('planning/generate_mrp/readProductFamily'); ?>',
                valueField: 'pfm_name',
                textField: 'pfm_name',
                prompt: 'Select Product Family',
                icons: [{
                    iconCls: 'icon-clear',
                    handler: function(e) {
                        $(e.data.target).combobox('clear').combobox('textbox').focus();
                    }
                }],
            });
            $('#filter_supplier').combogrid({
                url: '<?= base_url('planning/suppliers/reads') ?>',
                panelWidth: 400,
                idField: 'id',
                textField: 'name',
                mode: 'remote',
                fitColumns: true,
                prompt: "Select Supplier",
                icons: [{
                    iconCls: 'icon-clear',
                    handler: function(e) {
                        $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                    }
                }],
                columns: [
                    [{
                            field: 'number',
                            title: 'ID',
                            width: 80
                        },
                        {
                            field: 'name',
                            title: 'Name',
                            width: 250
                        }
                    ]
                ],
                onSelect: function(val, row) {}
            });

            $('#p_month').combobox({
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
            $('#p_year').combobox({
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
            $('#revision').combobox({
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
        });

        function btnUpdateFc(val, row) {
            var view = "view_update_fc('" + row.id + "','" + row.part_no + "','" + row.forecast_1_mrp + "','" + row.forecast_1 + "','" + row.forecast_2_mrp + "','" + row.forecast_2 + "','" + row.forecast_3_mrp + "','" + row.forecast_3 + "','" + row.forecast_4_mrp + "','" + row.forecast_4 + "','" + row.reason + "')";
            return '<a class="btn btn-warning w-100" onClick="' + view + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-edit"></i> Edit</a>';
        }

        function view_update_fc(id, part_no, forecast_1_mrp, forecast_1, forecast_2_mrp, forecast_2, forecast_3_mrp, forecast_3, forecast_4_mrp, forecast_4, reason) {
            $('#dlg_fc').dialog('setTitle', 'Update FC');
            $("#dlg_fc").dialog('open');
            $('#frm_fc').form('clear');
            $('#id').textbox('setValue', id);
            $('#item_id_fc').textbox('setValue', part_no);
            $('#forecast_1').numberbox('setValue', forecast_1);
            $('#forecast_2').numberbox('setValue', forecast_2);
            $('#forecast_3').numberbox('setValue', forecast_3);
            $('#forecast_4').numberbox('setValue', forecast_4);
            $('#forecast_1_mrp').numberbox('setValue', forecast_1_mrp);
            $('#forecast_2_mrp').numberbox('setValue', forecast_2_mrp);
            $('#forecast_3_mrp').numberbox('setValue', forecast_3_mrp);
            $('#forecast_4_mrp').numberbox('setValue', forecast_4_mrp);
            if (reason !== "null") {
                $('#reason').textbox('setValue', reason);
            }
            url_save = '<?= base_url('planning/forecast_supplier/updatefc') ?>';
        }

        $('#dlg_fc').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_fc').form('submit', {
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
                            $('#dlg_fc').dialog('close');
                            filter();
                        }
                    });
                }
            }]
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

        function numberformat(value, row) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 0
            });
            if (parseFloat(value) >= 0) {
                return "<b>" + formatter.format(value) + "</b>";
            } else {
                return "<b style='color:red;'>" + formatter.format(value) + "</b>";
            }
        }

        function cellStylerApproval(value, row, index) {
            if (row.approved_to == null) {
                return 'background: #FF5F5F; color:white;';
            } else if (row.approved_to == "") {
                return 'background: #53D636; color:white;';
            } else if (row.balance > 0) {
                return 'background: #53D636; color:white;';
            } else {
                return 'background: #FF5F5F; color:white;';
            }
        }

        function cellFormatterApproval(val, row) {
            if (row.approved_to == null) {
                return 'Not Setting';
            } else if (row.approved_to == "") {
                return 'Approved';
            }
            // else if (row.balance > 0) {
            //     return 'Approved';
            // } 
            else {
                return 'Checking';
            }
        }
    </script>