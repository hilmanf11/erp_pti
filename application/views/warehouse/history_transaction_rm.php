<div class="easyui-accordion" style="width:100%;">
    <div title="Hide Filter" data-options="selected:true" style="padding:10px; background:#F4F4F4;">
        <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
        <fieldset style="width: 99%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 30%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:28%;" id="filter_from" class="easyui-datebox" value="<?php echo date('Y-m-01'); ?>" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                    <input style="width:28%;" id="filter_to" class="easyui-datebox" value="<?php echo date('Y-m-t'); ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
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
            </div>
            <div style="width: 30%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part No</span>
                    <input style="width:60%;" id="filter_part_no" class="easyui-combogrid">
                </div>
            </div>
            <div style="width: 10%; float:left;">
                <div class="fitem" style="margin: 5px;">
                    <span style="width:35%; display:inline-block;">Detail</span>
                    <input name="filter_display" id="filter_display" class="easyui-checkbox" value="DETAIL">
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
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');
        var filter_part_no = $("#filter_part_no").combogrid('getValue');
        var filter_display = $("#filter_display").checkbox('options');

        if (filter_product_family == "") {
            toastr.warning("Please Choose Product Family");
        } else {
            var url = "?filter_from=" + btoa(filter_from) + 
            "&filter_to=" + btoa(filter_to) + 
            "&filter_product_family=" + btoa(filter_product_family) + 
            "&filter_part_no=" + btoa(filter_part_no) +
            "&filter_display=" + filter_display.checked;

            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('warehouse/history_transaction_rm/print') ?>' + url);
        }
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');
        var filter_part_no = $("#filter_part_no").combogrid('getValue');
        var filter_display = $("#filter_display").checkbox('options');

        if (filter_product_family == "") {
            toastr.warning("Please Choose Product Family");
        } else {
            var url = "?filter_from=" + btoa(filter_from) + 
            "&filter_to=" + btoa(filter_to) + 
            "&filter_product_family=" + btoa(filter_product_family) + 
            "&filter_part_no=" + btoa(filter_part_no) +
            "&filter_display=" + filter_display.checked;

            window.location.assign('<?= base_url('warehouse/history_transaction_rm/print/excel') ?>' + url);
        }
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

    $(function() {
        $('#filter_product_family').combobox({
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
                $('#filter_part_no').combogrid({
                    url: '<?= base_url('planning/mst_item/reads/') ?>' + row.number,
                    panelWidth: 400,
                    idField: 'item_id',
                    textField: 'item_id',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Choose Part",
                    columns: [
                        [{
                            field: 'item_id',
                            title: 'Part No',
                            width: 200
                        }, {
                            field: 'item_name',
                            title: 'Part Name',
                            width: 150
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

        $('#filter_part_no').combogrid({
            url: '<?= base_url('planning/mst_item/reads') ?>',
            panelWidth: 400,
            idField: 'item_id',
            textField: 'item_id',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Part",
            columns: [
                [{
                    field: 'item_id',
                    title: 'Part No',
                    width: 200
                }, {
                    field: 'item_name',
                    title: 'Part Name',
                    width: 150
                }, ]
            ],
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
        });
    });
</script>