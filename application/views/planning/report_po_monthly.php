<div class="easyui-accordion" style="width:100%;">
    <div title="Hide Filter" data-options="selected:true" style="padding:10px; background:#F4F4F4;">
        <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
        <fieldset style="width: 99%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 30%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period From</span>
                    <input style="width:30%;" name="filter_month" id="filter_month" value="<?= date("m") ?>" class="easyui-combobox" data-options="prompt:'Month'">
                    <input style="width:30%;" name="filter_year" id="filter_year" value="<?= date("Y") ?>" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period To</span>
                    <input style="width:30%;" name="filter_month2" id="filter_month2" value="<?= date("m") ?>" class="easyui-combobox" data-options="prompt:'Month'">
                    <input style="width:30%;" name="filter_year2" id="filter_year2" value="<?= date("Y") ?>" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Vendor</span>
                    <input style="width:60%;" id="filter_supplier" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 30%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" id="filter_product_family" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part No</span>
                    <input style="width:60%;" id="filter_part_no" class="easyui-combogrid">
                </div>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>

<div class="easyui-panel" title="Print Preview" style="width:100%;padding:10px;" data-options="fit:true">
    <iframe id="printout" src="" style="width: 100%; height: 95%; border: 0;"></iframe>
</div>

<script>
    function reload() {
        window.location.reload();
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function filter() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_month2 = $("#filter_month2").combobox('getValue');
        var filter_year2 = $("#filter_year2").combobox('getValue');
        var filter_supplier = $("#filter_supplier").combogrid('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');
        var filter_part_no = $("#filter_part_no").combogrid('getValue');

        if(filter_supplier == "" && filter_product_family == "" && filter_part_no == ""){
            toastr.warning("Please Select Supplier, Product Family or Part No First");
            return false;
        }

        var url = "?filter_month=" + btoa(filter_month) + 
                  "&filter_year=" + btoa(filter_year) + 
                  "&filter_month2=" + btoa(filter_month2) + 
                  "&filter_year2=" + btoa(filter_year2) + 
                  "&filter_supplier=" + btoa(filter_supplier) + 
                  "&filter_product_family=" + btoa(filter_product_family) +
                  "&filter_part_no=" + btoa(filter_part_no);

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('planning/report_po_monthly/print') ?>' + url);
    }

    function excel() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_month2 = $("#filter_month2").combobox('getValue');
        var filter_year2 = $("#filter_year2").combobox('getValue');
        var filter_supplier = $("#filter_supplier").combogrid('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');
        var filter_part_no = $("#filter_part_no").combogrid('getValue');

        if(filter_supplier == "" && filter_product_family == "" && filter_part_no == ""){
            toastr.warning("Please Select Supplier, Product Family or Part No First");
            return false;
        }

        var url = "?filter_month=" + btoa(filter_month) + 
                  "&filter_year=" + btoa(filter_year) + 
                  "&filter_month2=" + btoa(filter_month2) + 
                  "&filter_year2=" + btoa(filter_year2) + 
                  "&filter_supplier=" + btoa(filter_supplier) + 
                  "&filter_product_family=" + btoa(filter_product_family) +
                  "&filter_part_no=" + btoa(filter_part_no);

        window.location.assign('<?= base_url('planning/report_po_monthly/print/excel') ?>' + url);
    }

    $(function() {
        //Filter Cutoff
        $('#filter_month').combobox({
            url: '<?php echo base_url('planning/mst_data/readMonths'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Month',
        });

        $('#filter_year').combobox({
            url: '<?php echo base_url('planning/mst_data/readYears'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Year',
        });

        $('#filter_month2').combobox({
            url: '<?php echo base_url('planning/mst_data/readMonths'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Month',
        });

        $('#filter_year2').combobox({
            url: '<?php echo base_url('planning/mst_data/readYears'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Year',
        });

        $('#filter_supplier').combogrid({
            url: '<?= base_url('planning/suppliers/reads') ?>',
            panelWidth: 400,
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
                    title: 'ID',
                    width: 80
                }, {
                    field: 'name',
                    title: 'Name',
                    width: 250
                }]
            ]
        });

        $('#filter_product_family').combobox({
            url: '<?php echo base_url('planning/mst_item/readFamily'); ?>',
            valueField: 'number',
            textField: 'name',
            prompt: 'Select Product Family',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(row) {
                $('#filter_part_no').combogrid({
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
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                        }
                    }],
                });
            }
        });
    });
</script>