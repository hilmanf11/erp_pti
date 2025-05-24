    <table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
        <thead>
        <tr>
                <th rowspan="2" field="ck" checkbox="true"></th>
                <th rowspan="2" data-options="field:'vendor_id',width:80,align:'center'">Vendor ID</th>
                <th rowspan="2" data-options="field:'vendor_name',width:250,halign:'center'">Vendor Name</th>
                <th rowspan="2" data-options="field:'vendor_type',width:80,halign:'center'">Vendor Type</th>
                <th rowspan="2" data-options="field:'vendor_origin',width:80,halign:'center'">Origin</th>
                <th rowspan="2" data-options="field:'supplier_item_id',width:200,halign:'center'">Item ID</th>
                <th rowspan="2" data-options="field:'item_name',width:100,halign:'center'">Item Name</th>
                <th rowspan="2" data-options="field:'supplier_item_leadtime',width:80,halign:'center'">LeadTime</th>
                <th rowspan="2" data-options="field:'supplier_item_mpq',width:80,halign:'center', formatter:numberformat">MPQ</th>
                <th rowspan="2" data-options="field:'supplier_item_moq',width:80,halign:'center', formatter:numberformat">MOQ</th>
                <th rowspan="2" data-options="field:'maker_name',width:200,halign:'center'">Maker Name</th>
                <th rowspan="2" data-options="field:'maker_type',width:80,halign:'center'">Maker Type</th>
                <!-- <th rowspan="2" data-options="field:'maker_origin',width:80,halign:'center'">Origin</th> -->
                <th rowspan="2" data-options="field:'forecast_xbar',width:100,halign:'center', formatter:numberformat">Xbar<br>(-3 Month)</th>
                <th colspan="3" data-options="field:'Forecast',width:240,halign:'center'">FORECAST</th>
            </tr>
            <tr>
                <th data-options="field:'forecast_1',width:80,halign:'center', formatter:numberformat">M 1</th>
                <th data-options="field:'forecast_2',width:80,halign:'center', formatter:numberformat">M 2</th>
                <th data-options="field:'forecast_3',width:80,halign:'center', formatter:numberformat">M 3</th>
            </tr>
        </thead>
    </table>

    <div id="toolbar" style="height: 230px; padding:10px;">
        <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
            <fieldset style="width: 60%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
                <legend><b>Filter Data</b></legend>
                <div style="width: 50%; float: left;">
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
                        <span style="width:35%; display:inline-block;">Supplier</span>
                        <input style="width:60%;" id="filter_supplier" class="easyui-combogrid">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;"></span>
                        <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                    </div>
                </div>
                <div style="width: 50%; float: left;">
                    <!-- <div class="fitem">
                        <span style="width:35%; display:inline-block;">PO No</span>
                        <input style="width:60%;" id="filter_po" class="easyui-combobox">
                    </div> -->
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Product Family</span>
                        <input style="width:60%;" id="filter_product_family" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                <span style="width:35%; display:inline-block;">Item Id</span>
                <input style="width:60%;" name="item_id" id="item_id" class="easyui-combogrid">
            </div>
                    <!-- <div class="fitem">
                <span style="width:35%; display:inline-block;">Item Id kkk</span>
                <input style="width:60%;" name="item_id" id="item_id" class="easyui-combogrid">
            </div> -->
                </div>
            </fieldset>
        </div>
        <?= $button ?>
    </div>

    <!-- PDF -->
    <iframe id="printout" src="" style="width: 100%;" hidden></iframe>

    <script>
       function filter() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');
        // filter_product_family
        var item_id = $("#item_id").combogrid('getValue');
        var filter_supplier = $("#filter_supplier").combogrid('getValue');
       
        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_product_family=" + window.btoa(filter_product_family) +
            "&item_id=" + window.btoa(item_id); // Corrected closing parenthesis here.

        $("#printout").attr('src', '<?= base_url('planning/os_purchase_order/print') ?>' + url);

        if (filter_month == "" || filter_year == "") {
            toastr.warning("Please Choose Filter Date!", "Information");
        } else {
            console.log('damn6666');
            console.log(url);

            $('#dg').datagrid({
                url: '<?= base_url('planning/forecast_supplier/datatables') ?>' + url,
            });

            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('planning/forecast_supplier/print') ?>' + url);
        }
    }

        function excel() {
            var filter_month = $("#filter_month").combobox('getValue');
            var filter_year = $("#filter_year").combobox('getValue');
            var filter_revision = $("#filter_revision").combobox('getValue');
            var item_id = $("#item_id").combogrid('getValue');
            var filter_product_family = $("#filter_product_family").combobox('getValue');
            // var filter_po = $("#filter_po").combobox('getValue');
            // var filter_part_no = $("#filter_part_no").combogrid('getValue');
            var filter_supplier = $("#filter_supplier").combogrid('getValue');

            var url = "?filter_month=" + window.btoa(filter_month) +
                "&filter_year=" + window.btoa(filter_year) +
                "&filter_revision=" + window.btoa(filter_revision) +
                "&filter_supplier=" + window.btoa(filter_supplier) +
                "&item_id=" + window.btoa(item_id) +
                "&filter_product_family=" + window.btoa(filter_product_family);


            if (filter_month == "" || filter_year == "") {
                toastr.warning("Please Choose Filter Date!", "Information");
            } else {
                window.location.assign('<?= base_url('planning/forecast_supplier/print/excel') ?>' + url);
            }

            if(item_id == ""){
            toastr.info("Please Select Vendor or Item ID");
        }else{
            window.location.assign('<?= base_url('planning/os_purchase_order/print/excel') ?>' + url);
        }
        }

        function reload() {
            window.location.reload();
        }

        function callItemId(id){
            console.log('ask123');
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
                }, {
                    field: 'item_name',
                    title: 'Part Name',
                    width: 100
                }, ]
            ]
    
        });

        }

        $(function() {
            $("#add").html("Generate");
            
            $('#dg').datagrid({
                pagination: true,
                clientPaging: false,
                remoteFilter: true,
                rownumbers: true,
                singleSelect: true,
                fit:true,
                rowStyler: function(index,row){
                    if (row.color == ""){
                        return 'background-color:#FFD2D2;';
                    }
                }
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

            onSelect: function(row){                                
                   
            } 
                 
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
        [{ field: 'item_id', title: 'Part No', width: 200 },
         { field: 'item_name', title: 'Part Name', width: 100 }]
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
    onSelect: function(row) {
        updateItemGridUrl(row); // Update the item_id grid URL
    }
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
        [{ field: 'number', title: 'ID', width: 80 },
         { field: 'name', title: 'Name', width: 250 }]
    ],
    onSelect: function(val, row) {
    }
});


})

function updateItemGridUrl(row) {
    const filterProductFamily = row.pfm_name; // Get selected product family
    let url = '<?= base_url('planning/mst_item/readsparams'); ?>';
    const params = [];

    // Clear previous combogrid data
    $('#item_id').combogrid('clear');

    // Add product_family filter if selected
    if (filterProductFamily) {
        params.push('product_family=' + encodeURIComponent(filterProductFamily));
    }

    if (params.length > 0) {
        url += '?' + params.join('&');
    }


    // Update the combogrid URL and reload the grid
    $('#item_id').combogrid('grid').datagrid({
        url: url,
        method: 'get',
        queryParams: {}, // Clear query params so pagination starts fresh
        loadFilter: function (data) {
            // Ensure the data format matches the expected `total` and `rows` structure
            if (data.rows && data.total) {
                return data;
            }
            return {
                total: data.length || 0,
                rows: data
            };
        },
        onLoadSuccess: function (data) {
        },
        onLoadError: function () {}
    }).datagrid('reload');
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

        function numberformat(value, row) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 0
            });

            return "<b>" + formatter.format(value) + "</b>";
        }
    </script>