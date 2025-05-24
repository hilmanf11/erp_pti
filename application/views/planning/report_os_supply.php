<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
        
    </table> 

<div class="easyui-accordion" style="width:100%;">
    <div title="Hide Filter" data-options="selected:true" style="padding:10px; background:#F4F4F4;">
        <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
        <fieldset style="width: 99%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 30%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:30%;" name="filter_month" id="filter_month" value="<?= date("m") ?>" class="easyui-combobox" data-options="prompt:'Month'">
                    <input style="width:30%;" name="filter_year" id="filter_year" value="<?= date("Y") ?>" class="easyui-combobox">
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
    <div id="pagenasion" style="width: 100%; height: auto; border: 0;"></div>

</div>

<script>
//         function navigatePage() {

// console.log('damnjjjj');

// }
    function reload() {
        window.location.reload();
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function filter() {
        console.log('damnxkxkxk');
    var filter_month = $("#filter_month").combobox('getValue');
    var filter_year = $("#filter_year").combobox('getValue');
    var filter_product_family = $("#filter_product_family").combobox('getValue');
    var filter_part_no = $("#filter_part_no").combogrid('getValue');

    if (filter_product_family == "") {
        toastr.warning("Please Choose Product Family");
    } else {
        var url = "?filter_month=" + btoa(filter_month) + "&filter_year=" + btoa(filter_year) + "&filter_product_family=" + btoa(filter_product_family) + "&filter_part_no=" + btoa(filter_part_no);
        
        // Update iframe for printout
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('planning/report_os_supply/print') ?>' + url);
console.log('damnss');
        // Fetch content for pagenasion
        $.ajax({
            url: '<?= base_url('planning/report_os_supply/pagenasion') ?>' + url,
            method: 'GET',
            success: function(response) {
                console.log('damnssokee',response);
                // Inject the response into the pagenasion element
                $("#pagenasion").html(response);
            },
            error: function(xhr, status, error) {
                console.error("Failed to fetch pagination:", error);
                $("#pagenasion").html("<b>Error loading content</b>");
            }
        });
    }
}




    // $('#dg').datagrid({
    //         url: '<?= base_url('admin/approval/datatables') ?>',
    //         pagination: true,
    //         clientPaging: false,
    //         remoteFilter: true,
    //         rownumbers: true,
    //         fit: true,
    //         pageList: [20, 50, 100, 500, 1000],
    //         pageSize: 20,
    //     }).datagrid('enableFilter');

    function excel() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');
        var filter_part_no = $("#filter_part_no").combogrid('getValue');

        if (filter_product_family == "") {
            toastr.warning("Please Choose Product Family");
        } else {
            var url = "?filter_month=" + btoa(filter_month) + "&filter_year=" + btoa(filter_year) + "&filter_product_family=" + btoa(filter_product_family) + "&filter_part_no=" + btoa(filter_part_no);

            window.location.assign('<?= base_url('planning/report_os_supply/print/excel') ?>' + url);
        }
    }

    $(function() {
        //Filter Cutoff
        // $('#dg').datagrid({
        //         pagination: true,
        //         clientPaging: false,
        //         remoteFilter: true,
        //         rownumbers: true,
        //         singleSelect: true,
        //         fit:true,
        //         rowStyler: function(index,row){
        //             if (row.color == ""){
        //                 return 'background-color:#FFD2D2;';
        //             }
        //         }
        //     });


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
                            title: 'Part Name kkk',
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
<div id="pagination" style="text-align: center; margin-top: 20px;">
    <a href="javascript:void(0);" onclick="loadPage(1)" class="page-link">First</a>
    <a href="javascript:void(0);" onclick="prevPage()" class="page-link">Previous</a>
    <span id="current-page">1</span> / <span id="total-pages"></span>
    <a href="javascript:void(0);" onclick="nextPage()" class="page-link">Next</a>
    <a href="javascript:void(0);" onclick="loadPage(totalPages)" class="page-link">Last</a>
</div>
