<div class="easyui-accordion" style="width:100%;">
    <div title="Hide Filter" data-options="selected:true" style="padding:10px; background:#F4F4F4;">
        <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
        <fieldset style="width: 99%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float:left;">
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">Part No</span>
                    <input style="width:60%;" id="filter_item_id" name="filter_item_id" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:30%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
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
        var filter_item_id = $("#filter_item_id").combogrid('getValue');

        // if (filter_item_id == "") {
        //     toastr.warning("Please Choose Item Id");
        // } else {
            var url = "?filter_item_id=" + btoa(filter_item_id);

            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('planning/where_used/print') ?>' + url);
        // }
    }

    function excel() {
        var filter_item_id = $("#filter_item_id").combogrid('getValue');

        // if (filter_item_id == "") {
        //     toastr.warning("Please Choose Item Id");
        // } else {
            var url = "?filter_item_id=" + btoa(filter_item_id);
            window.location.assign('<?= base_url('planning/where_used/print/excel') ?>' + url);
        // }
    }

    $(function() {
        //Filter Cutoff
        $('#filter_item_id').combogrid({
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