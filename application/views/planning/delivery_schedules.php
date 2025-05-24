<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar"></table>

<div id="toolbar" style="height: 235px; padding:10px;">
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
                    <span style="width:35%; display:inline-block;">Supplier</span>
                    <input style="width:60%;" id="filter_supplier" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">PO No</span>
                    <input style="width:60%;" id="filter_po_no" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" id="filter_product_family" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Item ID</span>
                    <input style="width:60%;" id="filter_part_no" class="easyui-combogrid">
                </div>
            </div>
        </fieldset>
        <fieldset style="width: 30%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Generating Process</b></legend>
            <div id="p_upload" class="easyui-progressbar" style="width:100%;"></div>
            <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
        </fieldset>
    </div>
    <?= $button ?>
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="excel2()"><i class="fa fa-file"></i> Export Excel 2</a>
</div>

<div id="dlg_insert" class="easyui-dialog" title="Update Data" data-options="closed: true,modal:true" style="width: 800px; padding:10px; top: 20px; left:10px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">PO No</span>
                    <input style="width:60%;" name="po_no" id="po_no" disabled class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier name</span>
                    <input style="width:60%;" name="supplier_name" id="supplier_name" disabled class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" name="product_family" id="product_family" disabled class="easyui-textbox">
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Item ID</span>
                    <input style="width:60%;" name="part_no" id="part_no" disabled class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Item Name</span>
                    <input style="width:60%;" name="part_name" id="part_name" disabled class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Qty</span>
                    <input style="width:30%;" name="qty" id="qty" readonly class="easyui-numberbox" data-options="groupSeparator:'.'">
                </div>
            </div>
            <div style="width: 100%; float: left; margin-top: 20px;">
                <fieldset style="width:32%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                    <legend><b><span id="p1"></span></b></legend>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Qty</span>
                        <input style="width:60%;" name="delivey_qty_1" id="delivey_qty_1" class="easyui-numberbox" data-options="groupSeparator:'.'">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">ETA</span>
                        <input style="width:60%;" name="delivey_date_1" id="delivey_date_1" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">ETD</span>
                        <input style="width:60%;" name="etd_1" id="etd_1" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    </div>
                </fieldset>
                <fieldset style="width:32%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                    <legend><b><span id="p2"></span></b></legend>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Qty</span>
                        <input style="width:60%;" name="delivey_qty_2" id="delivey_qty_2" class="easyui-numberbox" data-options="groupSeparator:'.'">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">ETA</span>
                        <input style="width:60%;" name="delivey_date_2" id="delivey_date_2" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">ETD</span>
                        <input style="width:60%;" name="etd_2" id="etd_2" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    </div>
                </fieldset>
                <fieldset style="width:32%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                    <legend><b><span id="p3"></span></b></legend>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Qty</span>
                        <input style="width:60%;" name="delivey_qty_3" id="delivey_qty_3" class="easyui-numberbox" data-options="groupSeparator:'.'">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">ETA</span>
                        <input style="width:60%;" name="delivey_date_3" id="delivey_date_3" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">ETD</span>
                        <input style="width:60%;" name="etd_3" id="etd_3" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    </div>
                </fieldset>
                <fieldset style="width:32%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                    <legend><b><span id="p4"></span></b></legend>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Qty</span>
                        <input style="width:60%;" name="delivey_qty_4" id="delivey_qty_4" class="easyui-numberbox" data-options="groupSeparator:'.'">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">ETA</span>
                        <input style="width:60%;" name="delivey_date_4" id="delivey_date_4" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">ETD</span>
                        <input style="width:60%;" name="etd_4" id="etd_4" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    </div>
                </fieldset>
                <fieldset style="width:32%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                    <legend><b><span id="p5"></span></b></legend>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Qty</span>
                        <input style="width:60%;" name="delivey_qty_5" id="delivey_qty_5" class="easyui-numberbox" data-options="groupSeparator:'.'">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">ETA</span>
                        <input style="width:60%;" name="delivey_date_5" id="delivey_date_5" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">ETD</span>
                        <input style="width:60%;" name="etd_5" id="etd_5" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    </div>
                </fieldset>
                <fieldset style="width:32%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                    <legend><b><span id="p6"></span></b></legend>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Qty</span>
                        <input style="width:60%;" name="delivey_qty_6" id="delivey_qty_6" class="easyui-numberbox" data-options="groupSeparator:'.'">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">ETA</span>
                        <input style="width:60%;" name="delivey_date_6" id="delivey_date_6" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">ETD</span>
                        <input style="width:60%;" name="etd_6" id="etd_6" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    </div>
                </fieldset>
            </div>
        </fieldset>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="" style="width: 100%;" hidden></iframe>

<script>
    function add() {
        toastr.info("Not Ready this Function");
        // var filter_month = $("#filter_month").combobox('getValue');
        // var filter_year = $("#filter_year").combobox('getValue');
        // var filter_revision = $("#filter_revision").combobox('getValue');
        // var filter_product_family = $("#filter_product_family").combobox('getValue');
        // var filter_part_no = $("#filter_part_no").combogrid('getValue');
        // var filter_po_no = $("#filter_po_no").combogrid('getValue');

        // if (filter_month != "" && filter_year != "" && filter_revision != "") {
        //     $.messager.confirm('Generate', 'Are you sure, you want generate this data', function(r){
        //         if (r){
        //             Swal.fire({
        //                 title: 'Please Wait for Generating Data',
        //                 showConfirmButton: false,
        //                 allowOutsideClick: false,
        //                 allowEscapeKey: false,
        //                 didOpen: () => {
        //                     Swal.showLoading();
        //                 },
        //             });

        //             $.ajax({
        //                 type: "get",
        //                 url: "<?= base_url('planning/delivery_schedules/getData') ?>",
        //                 data: "filter_month=" + window.btoa(filter_month) +
        //                     "&filter_year=" + window.btoa(filter_year) +
        //                     "&filter_revision=" + window.btoa(filter_revision) +
        //                     "&filter_product_family=" + window.btoa(filter_product_family) +
        //                     "&filter_product_no=" + window.btoa(filter_part_no) +
        //                     "&filter_po_no=" + window.btoa(filter_po_no),
        //                 dataType: "json",
        //                 success: function(rows) {
        //                     Swal.close();

        //                     if(rows['total'] > 0){
        //                         requestData(rows['total'], rows);

        //                         function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
        //                             if (value < 100) {
        //                                 value = Math.floor((number / total) * 100);
        //                                 $('#p_upload').progressbar('setValue', value);
        //                                 $('#p_start').html(number);
        //                                 $('#p_finish').html(total);

        //                                 $.post('<?= base_url('planning/delivery_schedules/create') ?>', {
        //                                     data: json[number - 1]
        //                                 }, function(note) {
        //                                     var result = eval('(' + note + ')');

        //                                     if (result.theme == "success") {
        //                                         requestData(total, json, number + 1, value, success + 1, failed + 0);
        //                                     } else {
        //                                         requestData(total, json, number + 1, value, success + 0, failed + 1);
        //                                     }

        //                                     if (value == 100) {
        //                                         Swal.fire('Good job!', 'Process Generate Data Completed!', 'success');
        //                                     }
        //                                 }).fail(function(jqXHR, textStatus) {
        //                                     if (textStatus == "error") {
        //                                         toastr.error("Connection time out");

        //                                         requestData(total, json, number, value, success + 0, failed + 1);
        //                                     }
        //                                 });
        //                             }
        //                         }
        //                     }else{
        //                         toastr.warning("Please Generate Purchase Request & Purchase Order First");
        //                     }
        //                 }
        //             });
        //         }
        //     });
        // } else {
        //     toastr.warning("Please Select Period & Revision", "Information");
        // }
    }

    //Edit Data
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);

            $("#delivey_qty_1").numberbox({
                onChange: function(val){
                    var total = (Math.ceil(val / row.mpq) * row.mpq);
                    $("#delivey_qty_1").numberbox('setValue', total);
                }
            });

            $("#delivey_qty_2").numberbox({
                onChange: function(val){
                    var total = (Math.ceil(val / row.mpq) * row.mpq);
                    $("#delivey_qty_2").numberbox('setValue', total);
                }
            });

            $("#delivey_qty_3").numberbox({
                onChange: function(val){
                    var total = (Math.ceil(val / row.mpq) * row.mpq);
                    $("#delivey_qty_3").numberbox('setValue', total);
                }
            });

            $("#delivey_qty_4").numberbox({
                onChange: function(val){
                    var total = (Math.ceil(val / row.mpq) * row.mpq);
                    $("#delivey_qty_4").numberbox('setValue', total);
                }
            });

            $("#delivey_qty_5").numberbox({
                onChange: function(val){
                    var total = (Math.ceil(val / row.mpq) * row.mpq);
                    $("#delivey_qty_5").numberbox('setValue', total);
                }
            });

            $("#delivey_qty_6").numberbox({
                onChange: function(val){
                    var total = (Math.ceil(val / row.mpq) * row.mpq);
                    $("#delivey_qty_6").numberbox('setValue', total);
                }
            });

            url_save = '<?= base_url('planning/delivery_schedules/update') ?>?id=' + btoa(row.id);
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //DELETE DATA
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    Swal.fire({
                        title: 'Please Wait for Delete Data',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                    });

                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('planning/delivery_schedules/deleted') ?>',
                            data: {
                                id: row.id
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');
                                toastr.success(result.message);
                                Swal.close();
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

    function filter() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');
        var filter_part_no = $("#filter_part_no").combogrid('getValue');
        var filter_po_no = $("#filter_po_no").combogrid('getValue');
        var filter_supplier = $("#filter_supplier").combogrid('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_product_family=" + window.btoa(filter_product_family) +
            "&filter_po_no=" + window.btoa(filter_po_no) +
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_part_no=" + window.btoa(filter_part_no);

        if (filter_month == "" || filter_year == "" || filter_revision == "") {
            toastr.warning("Please Choose Filter Date and Revision!", "Information");
        } else {
            $.ajax({
                type: "get",
                url: "<?= base_url('planning/delivery_schedules/readMonths') ?>",
                data: "month=" + filter_month + "&year=" + filter_year,
                dataType: "json",
                success: function(monthName) {
                    $("#p1").html(monthName[0]);
                    $("#p2").html(monthName[1]);
                    $("#p3").html(monthName[2]);
                    $("#p4").html(monthName[3]);
                    $("#p5").html(monthName[4]);
                    $("#p6").html(monthName[5]);

                    $('#dg').datagrid({
                        url: '<?= base_url('planning/delivery_schedules/datatables') ?>' + url,
                        pagination: true,
                        clientPaging: false,
                        remoteFilter: true,
                        rownumbers: true,
                        fit:true,
                        columns: [
                            [{
                                field: 'ck',
                                checkbox: true,
                                rowspan: 2
                            }, {
                                field: 'po_no',
                                title: 'PO No',
                                width: 150,
                                halign: 'center',
                                rowspan: 2
                            }, {
                                field: 'supplier_no',
                                title: 'Vendor No',
                                width: 80,
                                hidden: true,
                                halign: 'center',
                                rowspan: 2
                            }, {
                                field: 'supplier_name',
                                title: 'Vendor Name',
                                width: 250,
                                halign: 'center',
                                rowspan: 2
                            }, {
                                field: 'part_no',
                                title: 'Item ID',
                                width: 150,
                                halign: 'center',
                                rowspan: 2
                            }, {
                                field: 'part_name',
                                title: 'Item Name',
                                width: 100,
                                align: 'center',
                                rowspan: 2
                            }, {
                                field: 'product_family',
                                title: 'Item Family',
                                width: 80,
                                align: 'center',
                                rowspan: 2
                            }, {
                                field: 'mpq',
                                title: 'MPQ',
                                width: 80,
                                halign: 'center',
                                align: 'right',
                                rowspan: 2,
                                formatter: numberformat
                            },{
                                field: 'moq',
                                title: 'MOQ',
                                width: 80,
                                halign: 'center',
                                align: 'right',
                                rowspan: 2,
                                formatter: numberformat
                            },{
                                field: 'leadtime',
                                title: 'Leadtime',
                                width: 80,
                                halign: 'center',
                                align: 'right',
                                rowspan: 2
                            },{
                                field: 'qty',
                                title: 'Qty',
                                width: 100,
                                halign: 'center',
                                align: 'right',
                                rowspan: 2,
                                formatter: numberformat
                            }, {
                                field: '',
                                title: monthName[0],
                                width: 200,
                                align: 'center',
                                colspan: 3
                            }, {
                                field: '',
                                title: monthName[1],
                                width: 200,
                                align: 'center',
                                colspan: 3
                            }, {
                                field: '',
                                title: monthName[2],
                                width: 200,
                                align: 'center',
                                colspan: 3
                            }, {
                                field: '',
                                title: monthName[3],
                                width: 200,
                                align: 'center',
                                colspan: 3
                            }, {
                                field: '',
                                title: monthName[4],
                                width: 200,
                                align: 'center',
                                colspan: 3
                            }, {
                                field: '',
                                title: monthName[5],
                                width: 200,
                                align: 'center',
                                colspan: 3
                            }, {
                                field: 'remarks',
                                title: 'Reason',
                                width: 200,
                                halign: 'center',
                                rowspan: 2,
                            }, {
                                field: '',
                                title: 'Created',
                                width: 200,
                                align: 'center',
                                colspan: 2
                            }, {
                                field: '',
                                title: 'Updated',
                                width: 200,
                                align: 'center',
                                colspan: 2
                            }],[{
                                field: 'delivey_qty_1',
                                title: "Qty",
                                width: 80,
                                halign: 'center',
                                align: 'right',
                                formatter: numberformat
                            }, {
                                field: 'etd_1',
                                title: "ETD",
                                width: 100,
                                align: 'center',
                                formatter: formatDate
                            }, {
                                field: 'delivey_date_1',
                                title: "ETA",
                                width: 100,
                                align: 'center',
                                formatter: formatDate
                            }, {
                                field: 'delivey_qty_2',
                                title: "Qty",
                                width: 80,
                                halign: 'center',
                                align: 'right',
                                formatter: numberformat
                            }, {
                                field: 'etd_2',
                                title: "ETD",
                                width: 100,
                                align: 'center',
                                formatter: formatDate
                            }, {
                                field: 'delivey_date_2',
                                title: "ETA",
                                width: 100,
                                align: 'center',
                                formatter: formatDate
                            }, {
                                field: 'delivey_qty_3',
                                title: "Qty",
                                width: 80,
                                halign: 'center',
                                align: 'right',
                                formatter: numberformat
                            }, {
                                field: 'etd_3',
                                title: "ETD",
                                width: 100,
                                align: 'center',
                                formatter: formatDate
                            }, {
                                field: 'delivey_date_3',
                                title: "ETA",
                                width: 100,
                                align: 'center',
                                formatter: formatDate
                            }, {
                                field: 'delivey_qty_4',
                                title: "Qty",
                                width: 80,
                                halign: 'center',
                                align: 'right',
                                formatter: numberformat
                            }, {
                                field: 'etd_4',
                                title: "ETD",
                                width: 100,
                                align: 'center',
                                formatter: formatDate
                            }, {
                                field: 'delivey_date_4',
                                title: "ETA",
                                width: 100,
                                align: 'center',
                                formatter: formatDate
                            }, {
                                field: 'delivey_qty_5',
                                title: "Qty",
                                width: 80,
                                halign: 'center',
                                align: 'right',
                                formatter: numberformat
                            }, {
                                field: 'etd_5',
                                title: "ETD",
                                width: 100,
                                align: 'center',
                                formatter: formatDate
                            }, {
                                field: 'delivey_date_5',
                                title: "ETA",
                                width: 100,
                                align: 'center',
                                formatter: formatDate
                            }, {
                                field: 'delivey_qty_6',
                                title: "Qty",
                                width: 80,
                                halign: 'center',
                                align: 'right',
                                formatter: numberformat
                            }, {
                                field: 'etd_6',
                                title: "ETD",
                                width: 100,
                                align: 'center',
                                formatter: formatDate
                            }, {
                                field: 'delivey_date_6',
                                title: "ETA",
                                width: 80,
                                align: 'center',
                                formatter: formatDate
                            }, {
                                field: 'created_by',
                                title: 'By',
                                width: 100,
                                halign: 'center',
                            }, {
                                field: 'created_date',
                                title: 'Date',
                                width: 150,
                                halign: 'center',
                            }, {
                                field: 'updated_by',
                                title: 'By',
                                width: 100,
                                halign: 'center',
                            }, {
                                field: 'updated_date',
                                title: 'Date',
                                width: 150,
                                halign: 'center',
                            }]
                        ],
                    });
                }
            });

            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('planning/delivery_schedules/print2') ?>' + url);
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');
        var filter_part_no = $("#filter_part_no").combogrid('getValue');
        var filter_po_no = $("#filter_po_no").combogrid('getValue');
        var filter_supplier = $("#filter_supplier").combogrid('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_product_family=" + window.btoa(filter_product_family) +
            "&filter_po_no=" + window.btoa(filter_po_no) +
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_part_no=" + window.btoa(filter_part_no);

        if (filter_month == "" || filter_year == "" || filter_revision == "") {
            toastr.warning("Please Choose Filter Date and Revision!", "Information");
        } else {
            window.location.assign('<?= base_url('planning/delivery_schedules/print/excel') ?>' + url);
        }
    }

    function excel2(){
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');
        var filter_part_no = $("#filter_part_no").combogrid('getValue');
        var filter_po_no = $("#filter_po_no").combogrid('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_product_family=" + window.btoa(filter_product_family) +
            "&filter_po_no=" + window.btoa(filter_po_no) +
            "&filter_part_no=" + window.btoa(filter_part_no);

        if (filter_month == "" || filter_year == "" || filter_revision == "") {
            toastr.warning("Please Choose Filter Date and Revision!", "Information");
        } else {
            window.location.assign('<?= base_url('planning/delivery_schedules/print2/excel') ?>' + url);
        }
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
    	$("#add").html("Generate");

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

        $('#filter_revision').combobox({
            url: '<?php echo base_url('planning/mst_data/readRevisions'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Revision',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(row){
                var month = $("#filter_month").combobox('getValue');
                var year = $("#filter_year").combobox('getValue');

                $('#filter_po_no').combogrid({
                    url: '<?php echo base_url('planning/delivery_schedules/readPo/'); ?>' + month + "/" + year + "/" + row.id,
                    panelWidth: 450,
                    idField: 'purchase_order_no',
                    textField: 'purchase_order_no',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Select PO No",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                        }
                    }],
                    columns: [
                        [{
                            field: 'purchase_order_no',
                            title: 'PO No',
                            width: 150
                        }, {
                            field: 'supplier_name',
                            title: 'Supplier Name',
                            width: 250
                        }]
                    ]
                });
            }
        });

        $('#filter_supplier').combogrid({
            url: '<?= base_url('planning/purchase_orders/readSupplier') ?>',
            panelWidth: 400,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Supplier",
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
            url: '<?php echo base_url('planning/generate_mrp/readProductFamily'); ?>',
            valueField: 'pfm_name',
            textField: 'pfm_name',
            prompt: 'Select Product Family',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(row){
                $('#filter_part_no').combogrid({
                    url: '<?= base_url('planning/generate_mrp/readParts/') ?>' + row.pfm_id,
                    panelWidth: 400,
                    idField: 'item_id',
                    textField: 'item_id',
                    mode: 'remote',
                    fitColumns: true,
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
                            width: 200
                        }]
                    ]
                });
            }
        });
    });

    //Format Datepicker
    function myformatter(date) {
        var y = date.getFullYear();
        var m = date.getMonth() + 1;
        var d = date.getDate();

        if(y != "1899"){
            return y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d);
        }
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
        if (value == "OPEN") {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0
        });

        return "<b>" + formatter.format(value) + "</b>";
    }

    function formatDate(value){
        if(value == "0000-00-00"){
            return "-";
        }else{
            return value;
        }
    }
</script>