<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
    <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'customer',width:250,halign:'center'">Customer</th>
            <th rowspan="2" data-options="field:'product_no',width:150,halign:'center'">Product No</th>
            <th rowspan="2" data-options="field:'product_name',width:200,halign:'center'">Product Name</th>
            <th colspan="3" data-options="field:'revision',width:120,halign:'center',align:'center', formatter: numberformat, styler: cellStyler">Production Plan</th>
            <th rowspan="2" data-options="field:'rev_vs_1',width:120,halign:'center',align:'center', formatter: numberdecimalformat, styler: cellStylerPercentage">Rev 0 VS Rev 1(%)</th>
            <th rowspan="2" data-options="field:'rev_vs_2',width:120,halign:'center',align:'center', formatter: numberdecimalformat, styler: cellStylerPercentage">Rev 0 VS Rev 2(%)</th>
            <th rowspan="2" data-options="field:'bal_prod_plan',width:100,halign:'center',align:'right', formatter: numberformat, styler: cellStyler">Total MPS</th>
            <th rowspan="2" data-options="field:'total_mpp',width:80,halign:'center',align:'right', formatter: numberformat, styler: cellStyler">Total MPP</th>
            <th rowspan="2" data-options="field:'balance',width:80,halign:'center',align:'right',formatter: numberformat, styler: cellStyler">Balance</th>
            <th rowspan="2" data-options="field:'balance',width:80,halign:'center',align:'right',formatter: numberformat, styler: cellStyler">Balance</th>
            <th rowspan="2" data-options="field:'persen_mpp',width:80,halign:'center',align:'right',formatter: numberdecimalformat,styler: cellStylerPercentage">% MPP</th>
            <th rowspan="2" data-options="field:'persen_dev',width:80,halign:'center',align:'right',formatter: numberdecimalformat,styler: cellStylerPercentage">% Dev</th>
        </tr>
        <tr>
            <th data-options="field:'revision_0',width:120,halign:'center',align:'right', formatter: numberformat, styler: cellStyler">Rev 0</th>
            <th data-options="field:'revision_1',width:120,halign:'center',align:'right', formatter: numberformat, styler: cellStyler">Rev 1</th>
            <th data-options="field:'revision_2',width:120,halign:'center',align:'right', formatter: numberformat, styler: cellStyler">Rev 2</th>

        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 200px; padding:10px;">
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
                    <span style="width:35%; display:inline-block;">Customer</span>
                    <input style="width:60%;" name="filter_customer_id" id="filter_customer_id" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" name="filter_product_no" id="filter_product_no" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Balance</span>
                    <select style="width:60%;" id="filter_balance" class="easyui-combobox" panelHeight="auto">
                        <option value="A">All</option>
                        <option value="M">Minus</option>
                        <option value="P">Plus</option>
                    </select>
                </div>
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
        var filter_customer_id = $("#filter_customer_id").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');
        var filter_balance = $("#filter_balance").combobox('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_customer_id=" + window.btoa(filter_customer_id) +
            "&filter_product_no=" + window.btoa(filter_product_no) +
            "&filter_balance=" + window.btoa(filter_balance);

        if (filter_month == "" || filter_year == "") {
            toastr.warning("Please Choose Filter Date!", "Information");
        } else {
            $('#dg').datagrid({
                url: '<?= base_url('planning/mps_vs_mpp/datatables') ?>' + url,
                pageList: [10, 20, 30, 40, 50, 200, 500, 1000],
            });

            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('planning/mps_vs_mpp/print') ?>' + url);
        }
    }

    function numericToMonthName(monthNumber) {
        var months = [
            "January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ];

        var monthIndex = parseInt(monthNumber, 10) - 1;
        if (monthIndex < 0 || monthIndex > 11) {
            return "Invalid Month";
        }
        return months[monthIndex];
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }



    function excel() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_customer_id = $("#filter_customer_id").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');
        var filter_balance = $("#filter_balance").combobox('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_customer_id=" + window.btoa(filter_customer_id) +
            "&filter_product_no=" + window.btoa(filter_product_no) +
            "&filter_balance=" + window.btoa(filter_balance);

        if (filter_month == "" || filter_year == "") {
            toastr.warning("Please Choose Filter Date!", "Information");
        } else {
            window.location.assign('<?= base_url('planning/mps_vs_mpp/print/excel') ?>' + url);
        }
    }



    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_mps_vs_mpp.xls') ?>');
    }



    function reload() {
        window.location.reload();
    }



    function numberformat(value) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0
        });

        if (value < 0) {
            return '<span style="color: yellow;">' + "<b>" + formatter.format(value) + "</b>" + '</span>';
        } else {
            return '<span style="color: black;">' + "<b>" + formatter.format(value) + "</b>" + '</span>';
        }
    }

    function cellStyler(value, row, index) {
        if (value < 0) {
            return 'background-color:red;color:yellow;';  // Red background and yellow text for negative values
        }
        return '';  // No background for non-negative values
    }

    function cellStylerPercentage(value, row, index) { //pake ini
        if (value > 20) {
        return 'background-color:red;';  // Red background and white text
    } else if (value < 0) {
        return 'background-color:red; color:yellow';  // Red background and white text for negative values
    }
    return '';   // No background for non-negative values
    }

    function numberdecimalformat(value) {   
        if(value===null){
            value = 0
        }
        
        if (value < 0) {
            return '<span style="color: yellow;">' + "<b>" + parseFloat(value).toFixed(2) + "%</b>" + '</span>';
        } else if (value > 20){
            return '<span style="color: white;">' + "<b>" + parseFloat(value).toFixed(2) + "%</b>" + '</span>';
        }else {
            return '<span style="color: black;">' + "<b>" + parseFloat(value).toFixed(2) + "%</b>" + '</span>';
        }
    }



    $(function() {
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


        $('#filter_product_no').combogrid({
            url: '<?= base_url('planning/mst_data/readProducts') ?>',
            panelWidth: 400,
            idField: 'item_id',
            textField: 'item_id',
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
                    field: 'item_id',
                    title: 'Item ID',
                    width: 200
                }, {
                    field: 'item_name',
                    title: 'Item Name',
                    width: 200
                }]
            ]
        });

        $('#filter_customer_id').combobox({
            url: '<?php echo base_url('planning/mst_data/readCustomers'); ?>',
            valueField: 'number',
            textField: 'name',
            prompt: 'Select Customer',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });
    });

</script>