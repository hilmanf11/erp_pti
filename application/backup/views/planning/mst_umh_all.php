<style>
    #h1 {
        background: #95CDFF;
    }

    #h2 {
        background: #FCFF69;
    }
</style>
<table id="dg" class="easyui-treegrid" style="width:100%;" toolbar="#toolbar" fit="true" idField="id" treeField="line_name" rownumbers="true" url="<?= base_url('planning/mst_umh_all/datatables') ?>">
    <thead data-options="frozen:true">
        <tr>
            <th data-options="field:'line_name',width:200,halign:'center'">PRODUCT NO</th>
        </tr>
    </thead>
    <thead>
        <tr>
            <?php
            foreach ($mainProcess as $main) {
                if($main['name'] == "PP"){
                    $id = "h1";
                }else{
                    $id = "h2";
                }

                echo '<th id="'.$id.'" colspan="' . $main['total'] . '" data-options="width:100"> ' . $main['name'] . '</th>';
            }
            ?>
            <th rowspan="3" data-options="field:'total',width:100,align:'center'">TOTAL</th>
            <th rowspan="3" data-options="field:'total_umh',width:100,align:'center'">UMH</th>
        </tr>
        <tr>
            <?php
            foreach ($subProcess as $sub) {
                if($sub['main'] == "PP"){
                    $id = "h1";
                }else{
                    $id = "h2";
                }
                echo '<th id="'.$id.'" colspan="' . $sub['total'] . '" data-options="width:100"> ' . $sub['name'] . '</th>';
            }
            ?>
        </tr>
        <tr>
            <?php
            foreach ($process as $proces) {
                if($proces['main'] == "PP"){
                    $id = "h1";
                }else{
                    $id = "h2";
                }
                $proces_field = "field:'" . $proces['number'] . "',width:100,align:'center', styler:cellStyler";
                echo '<th id="'.$id.'" data-options="' . $proces_field . '"> ' . $proces['name'] . '</th>';
            }
            ?>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 195px; padding: 10px;">
    <fieldset style="width:99.5%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
        <legend><b>Form Filter Data</b></legend>
        <div class="fitem">
            <span style="width:200px; display:inline-block;">Customer Name</span>
            <input style="width:300px;" name="filter_line_no" id="filter_line_no" class="easyui-combobox">
        </div>
        <div class="fitem">
            <span style="width:200px; display:inline-block;">Product No</span>
            <input style="width:300px;" name="filter_product_no" id="filter_product_no" class="easyui-combogrid">
        </div>
        <div class="fitem">
            <span style="width:200px; display:inline-block;"></span>
            <a href="javascript:300px;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
        </div>
    </fieldset>
    <?= $button ?>
</div>

<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" name="product_no" id="product_no" required="true" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Circuit No</span>
                <input style="width:60%;" name="circuit_no" id="circuit_no" readonly="true" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Main Process</span>
                <input style="width:60%;" name="process_main_number" id="process_main_number" required="true" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Process</span>
                <input style="width:60%;" name="process_sub_number" id="process_sub_number" required="true" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Sub Process</span>
                <input style="width:60%;" name="process_number" id="process_number" required="true" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Cycle Time</span>
                <input style="width:60%;" name="cycle_time" id="cycle_time" required="true" class="easyui-numberbox" precision="2">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Remarks</span>
                <input style="width:60%; height:50px;" name="remarks" id="remarks" class="easyui-textbox" multiline="true">
            </div>
        </fieldset>
    </form>
</div>

<!-- Upload -->
<div id="dlg_upload" class="easyui-dialog" title="Upload Data" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <form id="frm_upload" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Notes</b></legend>
            <ul>
                <li>All cell format must be <i style="color:red;">"text"</i></li>
                <li>If your file doesn't work when uploading, please save as your file in format extension .xls</li>
            </ul>
        </fieldset>
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
    <div id="p_remarks" class="easyui-panel" title="History Upload" style="width:100%; height:200px; padding:10px; margin-top: 10px;">
        <ul id="remarks">

        </ul>
    </div>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('planning/mst_umh_all/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    function filter() {
        var filter_line_no = $("#filter_line_no").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');
        var url = "?filter_line_no=" + window.btoa(filter_line_no) + "&filter_product_no=" + window.btoa(filter_product_no);

        if (filter_line_no == "") {
            toastr.warning("Please Select Customer Name", "Customer Name");
        } else {
            $('#dg').treegrid({
                url: '<?= base_url('planning/mst_umh_all/datatables') ?>' + url
            });

            $("#printout").attr('src', '<?= base_url('planning/mst_umh_all/print') ?>' + url);
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_line_no = $("#filter_line_no").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');
        var url = "?filter_line_no=" + window.btoa(filter_line_no) + "&filter_product_no=" + window.btoa(filter_product_no);

        if (filter_line_no == "") {
            toastr.warning("Please Select Customer Name", "Customer Name");
        } else {
            window.location.assign('<?= base_url('planning/mst_umh_all/print/excel') ?>' + url);
        }
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        $('.h1').css('background','#ccc');

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

        $('#filter_line_no').combobox({
            url: '<?php echo base_url('planning/mst_line/reads'); ?>',
            valueField: 'line_no',
            textField: 'remarks',
            prompt: 'Select Line No',
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
        if (value > 0) {
            return 'background-color:#C8FFCC;';
        } else {
            return 'background-color:#FFC8C8;';
        }
    }
</script>