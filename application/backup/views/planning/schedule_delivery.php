<?php 
    if($this->input->get('filter_month')){
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $filter_customer = base64_decode($this->input->get('filter_customer'));
        $filter_product_no = base64_decode($this->input->get('filter_product_no'));
    }else{
        $filter_month = date("m");
        $filter_year = date("Y");
        $filter_revision = "0";
        $filter_customer = "";
        $filter_product_no = "";
    }

    $firstDate = date("Y-m-01", strtotime(date("$filter_year-$filter_month-01")));
    $endDate = date("Y-m-t", strtotime(date("$filter_year-$filter_month-01")));
?>

<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'sales_order_no',width:150,halign:'center'">Sales Order No</th>
            <th rowspan="2" data-options="field:'customer_name',width:250,halign:'center'">Customer Name</th>
            <th rowspan="2" data-options="field:'product_no',width:150,halign:'center'">Product No</th>
            <th rowspan="2" data-options="field:'product_name',width:200,halign:'center'">Product Name</th>
            <th rowspan="2" data-options="field:'qty',width:100,halign:'center',align:'right'"> Qty</th>
            <?php 
                $tgl = 1;
                while (strtotime($firstDate) <= strtotime($endDate)) {
                    ?>
                    <th rowspan="2" data-options="field:'qty_<?= $tgl ?>',width:60,halign:'center',align:'right'">Date<br><?= $tgl ?></th>
                    <?php
                    $tgl++;
                    $firstDate = date("Y-m-d", strtotime("+1 day", strtotime($firstDate)));
                }
            ?>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_name',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_name',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 200px; padding: 10px;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 60%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:30%;" name="filter_month" id="filter_month" value="<?= $filter_month ?>" class="easyui-combobox" data-options="prompt:'Month'">
                    <input style="width:30%;" name="filter_year" id="filter_year" value="<?= $filter_year ?>" class="easyui-combobox" data-options="prompt:'Year'">
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
                    <span style="width:35%; display:inline-block;">Customer Name</span>
                    <input style="width:60%;" id="filter_customer" value="<?= $filter_customer ?>" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_product_no" value="<?= $filter_product_no ?>" class="easyui-combogrid">
                </div>
            </div>
        </fieldset>
    </div>
    <?= $button ?>
</div>

<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1000px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <select style="width:30%;" name="p_month" id="p_month" class="easyui-combobox" required="" data-options="prompt:'Month'">
                        <option value="01">January</option>
                        <option value="02">February</option>
                        <option value="03">March</option>
                        <option value="04">April</option>
                        <option value="05">May</option>
                        <option value="06">June</option>
                        <option value="07">July</option>
                        <option value="08">August</option>
                        <option value="09">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </select>
                    <input style="width:30%;" name="p_year" id="p_year" required="" class="easyui-numberbox" data-options="prompt:'Year', validType: 'length[0,4]'">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Revision</span>
                    <select style="width:60%;" name="revision" id="revision" class="easyui-combobox" required="" data-options="prompt:'Revision'" panelHeight="auto">
                        <option value="0">Revision 0</option>
                        <option value="1">Revision 1</option>
                        <option value="2">Revision 2</option>
                        <option value="3">Revision 3</option>
                        <option value="4">Revision 4</option>
                        <option value="5">Revision 5</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer No</span>
                    <input style="width:60%;" name="customer_no" id="customer_no" required="" class="easyui-combogrid">
                </div>
                <div class="fitem" hidden="">
                    <span style="width:35%; display:inline-block;">Customer Name</span>
                    <input style="width:60%;" name="customer_name" id="customer_name" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Sales Order No</span>
                    <input style="width:60%;" name="sales_order_no" id="sales_order_no" required="" class="easyui-combobox">
                </div>
                <br>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" name="item_id" id="item_id" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Name</span>
                    <input style="width:60%;" name="item_name" id="item_name" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Qty</span>
                    <input style="width:30%;" name="qty" id="qty" readonly class="easyui-numberbox">
                </div>
            </div>
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                <legend><b>Schedule Delivery Day</b></legend>
                <div id="frm_schedule">

                </div>
            </fieldset>
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
<iframe id="printout" src="<?= base_url('planning/schedule_delivery/print?filter_month='.base64_encode($filter_month)."&filter_year=".base64_encode($filter_year)."&filter_revision=".base64_encode($filter_revision)."&filter_product_no=".base64_encode($filter_product_no)."&filter_customer=".base64_encode($filter_customer)) ?>" style="width: 100%;" hidden></iframe>

<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('planning/schedule_delivery/create') ?>';
        $('#frm_insert').form('clear');
        $("#p_month").combobox("enable");
        $("#p_year").numberbox("enable");

        $("#p_month").combobox('setValue', "<?= date('m') ?>");
        $("#p_year").numberbox('setValue', "<?= date('Y') ?>");
        $("#revision").combobox('setValue', 0);
        $("#qty").numberbox('setValue', 0);

        $("#p_month").combobox("enable");
        $("#p_year").numberbox("enable");
        $("#revision").combobox("enable");

        $.ajax({
            type: "post",
            url: "<?= base_url('planning/schedule_delivery/readSchedule/'.date('m').'/'.date("Y")) ?>",
            dataType: "html",
            success: function (response) {
                $("#frm_schedule").html(response);
            }
        });
    }

    //Edit Data
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);

            $("#p_month").combobox("disable");
            $("#p_year").numberbox("disable");

            url_save = '<?= base_url('planning/schedule_delivery/update') ?>?id=' + btoa(row.id);

            $.ajax({
                type: "post",
                url: "<?= base_url('planning/schedule_delivery/readSchedule/') ?>" + row.p_month + "/" + row.p_year,
                dataType: "html",
                success: function (response) {
                    $("#frm_schedule").html(response);

                    var qty_1 = $("#qty_1").val(row.qty_1);
                    var qty_2 = $("#qty_2").val(row.qty_2);
                    var qty_3 = $("#qty_3").val(row.qty_3);
                    var qty_4 = $("#qty_4").val(row.qty_4);
                    var qty_5 = $("#qty_5").val(row.qty_5);
                    var qty_6 = $("#qty_6").val(row.qty_6);
                    var qty_7 = $("#qty_7").val(row.qty_7);
                    var qty_8 = $("#qty_8").val(row.qty_8);
                    var qty_9 = $("#qty_9").val(row.qty_9);
                    var qty_10 = $("#qty_10").val(row.qty_10);
                    var qty_11 = $("#qty_11").val(row.qty_11);
                    var qty_12 = $("#qty_12").val(row.qty_12);
                    var qty_13 = $("#qty_13").val(row.qty_13);
                    var qty_14 = $("#qty_14").val(row.qty_14);
                    var qty_15 = $("#qty_15").val(row.qty_15);
                    var qty_16 = $("#qty_16").val(row.qty_16);
                    var qty_17 = $("#qty_17").val(row.qty_17);
                    var qty_18 = $("#qty_18").val(row.qty_18);
                    var qty_19 = $("#qty_19").val(row.qty_19);
                    var qty_20 = $("#qty_20").val(row.qty_20);
                    var qty_21 = $("#qty_21").val(row.qty_21);
                    var qty_22 = $("#qty_22").val(row.qty_22);
                    var qty_23 = $("#qty_23").val(row.qty_23);
                    var qty_24 = $("#qty_24").val(row.qty_24);
                    var qty_25 = $("#qty_25").val(row.qty_25);
                    var qty_26 = $("#qty_26").val(row.qty_26);
                    var qty_27 = $("#qty_27").val(row.qty_27);
                    var qty_28 = $("#qty_28").val(row.qty_28);
                    var qty_29 = $("#qty_29").val(row.qty_29);
                    var qty_30 = $("#qty_30").val(row.qty_30);
                    var qty_31 = $("#qty_31").val(row.qty_31);
                }
            });
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
                            url: '<?= base_url('planning/schedule_delivery/delete') ?>',
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

    function hitung(e){
        var last_date = $("#last_date").val();
        var qty = $("#qty").numberbox('getValue');

        var qty_1 = $("#qty_1").val();
        var qty_2 = $("#qty_2").val();
        var qty_3 = $("#qty_3").val();
        var qty_4 = $("#qty_4").val();
        var qty_5 = $("#qty_5").val();
        var qty_6 = $("#qty_6").val();
        var qty_7 = $("#qty_7").val();
        var qty_8 = $("#qty_8").val();
        var qty_9 = $("#qty_9").val();
        var qty_10 = $("#qty_10").val();
        var qty_11 = $("#qty_11").val();
        var qty_12 = $("#qty_12").val();
        var qty_13 = $("#qty_13").val();
        var qty_14 = $("#qty_14").val();
        var qty_15 = $("#qty_15").val();
        var qty_16 = $("#qty_16").val();
        var qty_17 = $("#qty_17").val();
        var qty_18 = $("#qty_18").val();
        var qty_19 = $("#qty_19").val();
        var qty_20 = $("#qty_20").val();
        var qty_21 = $("#qty_21").val();
        var qty_22 = $("#qty_22").val();
        var qty_23 = $("#qty_23").val();
        var qty_24 = $("#qty_24").val();
        var qty_25 = $("#qty_25").val();
        var qty_26 = $("#qty_26").val();
        var qty_27 = $("#qty_27").val();
        var qty_28 = $("#qty_28").val();

        if(last_date == "29"){
            var qty_29 = $("#qty_29").val();
            var qty_30 = 0;
            var qty_31 = 0;
        }else if(last_date == "30"){
            var qty_29 = $("#qty_29").val();
            var qty_30 = $("#qty_30").val();
            var qty_31 = 0;
        }else{
            var qty_29 = $("#qty_29").val();
            var qty_30 = $("#qty_30").val();
            var qty_31 = $("#qty_31").val();
        }

        if($.isNumeric(qty_1)){ var qty_1 = qty_1; }else{ var qty_1 = 0; }
        if($.isNumeric(qty_2)){ var qty_2 = qty_2; }else{ var qty_2 = 0; }
        if($.isNumeric(qty_3)){ var qty_3 = qty_3; }else{ var qty_3 = 0; }
        if($.isNumeric(qty_4)){ var qty_4 = qty_4; }else{ var qty_4 = 0; }
        if($.isNumeric(qty_5)){ var qty_5 = qty_5; }else{ var qty_5 = 0; }
        if($.isNumeric(qty_6)){ var qty_6 = qty_6; }else{ var qty_6 = 0; }
        if($.isNumeric(qty_7)){ var qty_7 = qty_7; }else{ var qty_7 = 0; }
        if($.isNumeric(qty_8)){ var qty_8 = qty_8; }else{ var qty_8 = 0; }
        if($.isNumeric(qty_9)){ var qty_9 = qty_9; }else{ var qty_9 = 0; }
        if($.isNumeric(qty_10)){ var qty_10 = qty_10; }else{ var qty_10 = 0; }
        if($.isNumeric(qty_11)){ var qty_11 = qty_11; }else{ var qty_11 = 0; }
        if($.isNumeric(qty_12)){ var qty_12 = qty_12; }else{ var qty_12 = 0; }
        if($.isNumeric(qty_13)){ var qty_13 = qty_13; }else{ var qty_13 = 0; }
        if($.isNumeric(qty_14)){ var qty_14 = qty_14; }else{ var qty_14 = 0; }
        if($.isNumeric(qty_15)){ var qty_15 = qty_15; }else{ var qty_15 = 0; }
        if($.isNumeric(qty_16)){ var qty_16 = qty_16; }else{ var qty_16 = 0; }
        if($.isNumeric(qty_17)){ var qty_17 = qty_17; }else{ var qty_17 = 0; }
        if($.isNumeric(qty_18)){ var qty_18 = qty_18; }else{ var qty_18 = 0; }
        if($.isNumeric(qty_19)){ var qty_19 = qty_19; }else{ var qty_19 = 0; }
        if($.isNumeric(qty_20)){ var qty_20 = qty_20; }else{ var qty_20 = 0; }
        if($.isNumeric(qty_21)){ var qty_21 = qty_21; }else{ var qty_21 = 0; }
        if($.isNumeric(qty_22)){ var qty_22 = qty_22; }else{ var qty_22 = 0; }
        if($.isNumeric(qty_23)){ var qty_23 = qty_23; }else{ var qty_23 = 0; }
        if($.isNumeric(qty_24)){ var qty_24 = qty_24; }else{ var qty_24 = 0; }
        if($.isNumeric(qty_25)){ var qty_25 = qty_25; }else{ var qty_25 = 0; }
        if($.isNumeric(qty_26)){ var qty_26 = qty_26; }else{ var qty_26 = 0; }
        if($.isNumeric(qty_27)){ var qty_27 = qty_27; }else{ var qty_27 = 0; }
        if($.isNumeric(qty_28)){ var qty_28 = qty_28; }else{ var qty_28 = 0; }
        if($.isNumeric(qty_29)){ var qty_29 = qty_29; }else{ var qty_29 = 0; }
        if($.isNumeric(qty_30)){ var qty_30 = qty_30; }else{ var qty_30 = 0; }
        if($.isNumeric(qty_31)){ var qty_31 = qty_31; }else{ var qty_31 = 0; }

        var total = (parseInt(qty_1) + parseInt(qty_2) + parseInt(qty_3) + parseInt(qty_4) + parseInt(qty_5) + parseInt(qty_6) + parseInt(qty_7) + parseInt(qty_8)
            + parseInt(qty_9) + parseInt(qty_10) + parseInt(qty_11) + parseInt(qty_12) + parseInt(qty_13) + parseInt(qty_14) + parseInt(qty_15) + parseInt(qty_16)
            + parseInt(qty_17) + parseInt(qty_18) + parseInt(qty_19) + parseInt(qty_20) + parseInt(qty_21) + parseInt(qty_22) + parseInt(qty_23) + parseInt(qty_24) + parseInt(qty_25) + parseInt(qty_26) + parseInt(qty_27) + parseInt(qty_28) + parseInt(qty_29) + parseInt(qty_30));

        if(parseInt(total) > parseInt(qty)){
            toastr.error("Total delivery > Qty");
            $(e).val(0);
        }
    }
    
    function filter() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").textbox('getValue');
        var filter_customer = $("#filter_customer").combogrid('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_customer=" + window.btoa(filter_customer) +
            "&filter_product_no=" + window.btoa(filter_product_no);

        if (filter_month == "" || filter_year == "" || filter_revision == "") {
            toastr.warning("Please select Period!", "Information");
        } else {
            window.location.assign(url);
        }
    }

    //Upload Data
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_schedule_delivery.xls') ?>');
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        window.location.assign('<?= base_url('planning/schedule_delivery/print/excel?filter_month='.base64_encode($filter_month)."&filter_year=".base64_encode($filter_year)."&filter_revision=".base64_encode($filter_revision)."&filter_product_no=".base64_encode($filter_product_no)."&filter_customer=".base64_encode($filter_customer)) ?>');
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        $('#dg').datagrid({
            url: '<?= base_url('planning/schedule_delivery/datatables?filter_month='.base64_encode($filter_month)."&filter_year=".base64_encode($filter_year)."&filter_revision=".base64_encode($filter_revision)."&filter_product_no=".base64_encode($filter_product_no)."&filter_customer=".base64_encode($filter_customer)) ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true
        });

        $("#p_month").combobox({
            onChange: function(val){
                var year = $("#p_year").numberbox('getValue');
                $.ajax({
                    type: "post",
                    url: "<?= base_url('planning/schedule_delivery/readSchedule/') ?>" + val + "/" + year,
                    dataType: "html",
                    success: function (response) {
                        $("#frm_schedule").html(response);
                    }
                });
            }
        });

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
                        url: '<?= base_url('planning/schedule_delivery/upload') ?>',
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

                                    $.post('<?= base_url('planning/schedule_delivery/uploadCreate') ?>', {
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

        $('#filter_month').combobox({
            url: '<?php echo base_url('planning/mst_data/readMonths'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Month',
        });

        $('#filter_year').combobox({
            url: '<?php echo base_url('planning/mst_data/readYears'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Year',
        });

        //GET CUSTOMER
        $('#filter_customer').combogrid({
            url: '<?= base_url('planning/mst_data/readCustomers') ?>',
            panelWidth: 420,
            idField: 'number',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Customer",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'number',
                    title: 'Customer No',
                    width: 120
                }, {
                    field: 'name',
                    title: 'Customer Name',
                    width: 250
                }, ]
            ],
            onSelect: function(index, row) {
                //GET PRODUCT NO BY CUSTOMER
                $('#filter_product_no').combogrid({
                    url: '<?= base_url('planning/sales_orders/readProducts/') ?>' + row.number,
                    panelWidth: 420,
                    idField: 'item_id',
                    textField: 'item_name',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Select Product",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                        }
                    }],
                    columns: [
                        [{
                            field: 'item_id',
                            title: 'Product No',
                            width: 100
                        }, {
                            field: 'item_name',
                            title: 'Product Name',
                            width: 200
                        }, ]
                    ]
                });
            }
        });

        //GET CUSTOMER
        $('#customer_no').combogrid({
            url: '<?= base_url('planning/mst_data/readCustomers') ?>',
            panelWidth: 420,
            idField: 'number',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Customer",
            columns: [
                [{
                    field: 'number',
                    title: 'Customer No',
                    width: 120
                }, {
                    field: 'name',
                    title: 'Customer Name',
                    width: 250
                }, ]
            ],
            onSelect: function(index, row) {
                $('#sales_order_no').combobox({
                    url: '<?php echo base_url('planning/schedule_delivery/readSalesOrder/'); ?>' + row.number,
                    valueField: 'number',
                    textField: 'number',
                    prompt: 'Select Sales Order',
                    onSelect: function(so){

                        //GET PRODUCT NO BY CUSTOMER
                        $('#item_id').combogrid({
                            url: '<?= base_url('planning/schedule_delivery/readProducts/') ?>' + so.number,
                            panelWidth: 420,
                            idField: 'item_id',
                            textField: 'item_name',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: "Select Product",
                            columns: [
                                [{
                                    field: 'item_id',
                                    title: 'Product No',
                                    width: 100
                                }, {
                                    field: 'item_name',
                                    title: 'Product Name',
                                    width: 200
                                }, ]
                            ],
                            onSelect: function(valitem, item){
                                $("#item_name").textbox("setValue", item.item_name);
                                $("#qty").numberbox("setValue", item.qty);
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
</script>