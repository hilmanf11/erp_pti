<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'item_id',width:200,halign:'center'">Product No</th>
            <th data-options="field:'item_name',width:200,halign:'center'">Product Name</th>
            <th data-options="field:'circuit_no',width:100,align:'center'">CCT</th>
            <th data-options="field:'pp',width:150,align:'center',styler:cellStyler">PP</th>
            <th data-options="field:'havcp',width:150,align:'center',styler:cellStyler">HAVCP</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 195px; padding: 10px;">
    <fieldset style="width:99.5%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
        <legend><b>Form Filter Data</b></legend>
        <div class="fitem">
            <span style="width:200px; display:inline-block;">Customer Name</span>
            <input style="width:300px;" name="filter_customer" id="filter_customer" class="easyui-combobox">
        </div>
        <div class="fitem">
            <span style="width:200px; display:inline-block;">Product No</span>
            <input style="width:300px;" name="filter_product_no" id="filter_product_no" class="easyui-combogrid">
        </div>
        <div class="fitem">
            <span style="width:200px; display:inline-block;"></span>
            <a href="#" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
        </div>
    </fieldset>
    <?= $button ?>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('planning/mst_umh_recap/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    function filter() {
        var filter_customer = $("#filter_customer").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');
        var url = "?filter_customer=" + window.btoa(filter_customer) + "&filter_product_no=" + window.btoa(filter_product_no);

        if (filter_customer == "") {
            toastr.warning("Please Select Customer Name", "Customer Name");
        } else {
            $('#dg').datagrid({
                url: '<?= base_url('planning/mst_umh_recap/datatables') ?>' + url
            });

            $("#printout").attr('src', '<?= base_url('planning/mst_umh_recap/print') ?>' + url);
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_customer = $("#filter_customer").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');
        var url = "?filter_customer=" + window.btoa(filter_customer) + "&filter_product_no=" + window.btoa(filter_product_no);

        if (filter_customer == "") {
            toastr.warning("Please Select Customer Name", "Customer Name");
        } else {
            window.location.assign('<?= base_url('planning/mst_umh_recap/print/excel') ?>' + url);
        }
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        $('#dg').datagrid({
            pagination: false,
            rownumbers: true,
            rowStyler: function(index, row) {
                if (row.circuit_no == null) {
                    return 'background-color:#FFD2D2;font-weight:bold;';
                }
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

                            // $('#dlg_insert').dialog('close');
                            $('#dg').datagrid('reload');
                        }
                    });
                }
            }]
        });

        $('#filter_customer').combobox({
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
            onSelect: function(row) {
                $('#filter_product_no').combobox({
                    url: '<?php echo base_url('planning/mst_data/readProducts?customer_id='); ?>' + row.number,
                    valueField: 'item_id',
                    textField: 'item_id',
                    prompt: 'Select Product No',
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });
            }
        });
    });

    function cellStyler(value, row, index) {
        if (value == "FILLED") {
            return 'color:green; font-weight:bold;';
        } else {
            return 'color:red; font-weight:bold;';
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
</script>