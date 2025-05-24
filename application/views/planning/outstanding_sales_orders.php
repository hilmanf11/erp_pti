<div id="f" class="easyui-panel" style="width:100%; background: #F4F4F4; padding: 10px;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 40%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Customer Name</span>
                <input style="width:60%;" id="filter_customers" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Sales Order</span>
                <input style="width:60%;" id="filter_sales_orders" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Status</span>
                <select style="width:60%;" id="filter_status" class="easyui-combobox" panelHeight="auto">
                    <option value="">Choose All</option>
                    <option value="Outstanding">Outstanding</option>
                    <option value="Close">Close</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </fieldset>
    </div>
    <?= $button ?>
</div>

<div id="p" class="easyui-panel" title="Print Preview" style="width:100%;">
    <iframe id="printout" src="" style="width: 100%; height: 520px; border: 0;"></iframe>
</div>
<script>
    function filter() {
        var filter_customers = $("#filter_customers").combobox('getValue');
        var filter_sales_orders = $("#filter_sales_orders").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_customers=" + window.btoa(filter_customers) +
            "&filter_sales_orders=" + window.btoa(filter_sales_orders) +
            "&filter_status=" + window.btoa(filter_status);

        if (filter_customers == "") {
            toastr.warning("Please Choose Customers!", "Information");
        } else {
            $("#printout").attr('src', '<?= base_url('planning/outstanding_sales_orders/print') ?>' + url);
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_customers = $("#filter_customers").combobox('getValue');
        var filter_sales_orders = $("#filter_sales_orders").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_customers=" + window.btoa(filter_customers) +
            "&filter_sales_orders=" + window.btoa(filter_sales_orders) +
            "&filter_status=" + window.btoa(filter_status);

        if (filter_customers == "") {
            toastr.warning("Please Choose Customers!", "Information");
        } else {
            window.location.assign('<?= base_url('planning/outstanding_sales_orders/print/excel') ?>' + url);
        }
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        $('#filter_customers').combobox({
            url: '<?php echo base_url('planning/mst_data/readCustomers'); ?>',
            valueField: 'number',
            textField: 'name',
            prompt: 'Select Customer',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }]
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