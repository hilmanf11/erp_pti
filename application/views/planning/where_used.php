<div class="easyui-accordion" style="width:100%;">
    <div title="Hide Filter" data-options="selected:true" style="padding:10px; background:#F4F4F4;">
        <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
        <fieldset style="width: 99%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <form id="frm_upload" method="post" enctype="multipart/form-data" novalidate>
                <div style="width: 30%; float:left;">
                    <div class="fitem">
                        <span style="width:30%; display:inline-block;">Part No</span>
                        <input style="width:60%;" id="filter_item_id" name="filter_item_id" class="easyui-combogrid">
                    </div>
                    <div class="fitem">
                        <span style="width:30%; display:inline-block;"></span>
                        <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                    </div>
                </div>
                <div style="width: 35%; float:left;">
                    <div class="fitem">
                        <span style="width:20%; display:inline-block;">File Upload</span>
                        <input name="file_upload" style="width: 60%;" accept=".xls" id="file_excel" class="easyui-filebox">
                        <a href="javascript:;" class="easyui-linkbutton c1" onclick="download_excel()"><i class="fa fa-download"></i></a>
                        <!-- <a href="javascript:;" class="easyui-linkbutton c2" onclick="upload()"><i class="fa fa-upload"></i> Upload</a> -->
                    </div>
                </div>
                <div style="width: 35%; float:left;">
                    <div class="fitem">
                        <span style="width:20%; display:inline-block;">Period</span>
                        <input style="width:30%;" name="filter_month" id="filter_month" value="<?= date("m") ?>" class="easyui-combobox" data-options="prompt:'Month'">
                        <input style="width:20%;" name="filter_year" id="filter_year" value="<?= date("Y") ?>" class="easyui-combobox" data-options="prompt:'Year'">
                        <input style="width:20%;" name="filter_revision" id="filter_revision" value="0" class="easyui-combobox" data-options="prompt:'Revision'">
                    </div>
                </div>
            </form>
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

    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_where_used.xls') ?>');
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function filter() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_item_id = $("#filter_item_id").combogrid('getValue');
        var file_excel = $("#file_excel").filebox('getValue');

        if(file_excel != ""){
            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");

            $('#frm_upload').form('submit', {
                url: '<?= base_url('planning/where_used/print') ?>',
                success: function(result) {
                    var iframe = $("#printout")[0];
                    var doc = iframe.contentDocument || iframe.contentWindow.document;
                    doc.open();
                    doc.write(result);
                    doc.close();

                }
            });
        }else{
            var url = "?filter_item_id=" + btoa(filter_item_id) + "&filter_month=" + btoa(filter_month) + "&filter_year=" + btoa(filter_year) + "&filter_revision=" + btoa(filter_revision);

            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('planning/where_used/print') ?>' + url);
        }
    }

    function excel() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_item_id = $("#filter_item_id").combogrid('getValue');
        var url = "?filter_item_id=" + btoa(filter_item_id) + "&filter_month=" + btoa(filter_month) + "&filter_year=" + btoa(filter_year) + "&filter_revision=" + btoa(filter_revision);


        $('#frm_upload').form('submit', {
            url: '<?= base_url('planning/where_used/print/excel') ?>' + url,
            success: function(result) {
                $.messager.progress('close');
                var json = eval('(' + result + ')');
            }
        });
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

        $("#file_excel").filebox({
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).filebox('clear').filebox('textbox').focus();
                }
            }],
        });

        $('#filter_month').combobox({
            url: '<?php echo base_url('planning/mst_data/readMonths'); ?>',
            valueField: 'id',
            textField: 'name',
            editable:false,
            prompt: 'Select Month',
        });

        $('#filter_year').combobox({
            url: '<?php echo base_url('planning/mst_data/readYears'); ?>',
            valueField: 'id',
            textField: 'name',
            editable:false,
            prompt: 'Select Year',
        });

        $('#filter_revision').combobox({
            url: '<?php echo base_url('planning/mst_data/readRevisions'); ?>',
            valueField: 'id',
            textField: 'name',
            editable:false,
            prompt: 'Select Revision',
        });
    });
</script>