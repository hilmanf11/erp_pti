<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width: 99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" data-options="field:'id',halign:'center',align:'center',formatter:actionFormat">Action</th>
            <th rowspan="2" data-options="field:'request_date',halign:'center',align:'center',sortable:'true'">Request Date</th>
            <th rowspan="2" data-options="field:'due_date',halign:'center',align:'center',sortable:'true'">Due Date</th>
            <th rowspan="2" data-options="field:'plant_name',halign:'center',align:'center',sortable:'true'">Plant Name</th>
            <th rowspan="2" data-options="field:'volume_per_month',halign:'center',align:'right',sortable:'true'">Volume/Month</th>
            <th rowspan="2" data-options="field:'volume_per_year',halign:'center',align:'right',sortable:'true'">Volume/Year</th>
            <th rowspan="2" data-options="field:'project_category',halign:'center',sortable:'true'">Category</th>
            <th rowspan="2" data-options="field:'assy_no',halign:'center',sortable:'true'">Assy No</th>
            <th rowspan="2" data-options="field:'model_name',halign:'center',sortable:'true'">Model</th>
            <th rowspan="2" data-options="field:'customers_name',halign:'center',sortable:'true'">Customer</th>
            <th rowspan="2" data-options="field:'business_line_name',halign:'center',sortable:'true'">Business Line</th>
            <th rowspan="2" data-options="field:'application',halign:'center',sortable:'true'">Application</th>
            <th rowspan="2" data-options="field:'masspro',halign:'center',sortable:'true'">Masspro</th>
            <th rowspan="2" data-options="field:'end_user',halign:'center',sortable:'true'">End User</th>
            <th rowspan="2" data-options="field:'item_id',halign:'center',sortable:'true'">Item ID</th>
            <th rowspan="2" data-options="field:'item_name',halign:'center',sortable:'true'">Item Name</th>
            <th rowspan="2" data-options="field:'maker_name',halign:'center',sortable:'true'">Maker</th>
            <th rowspan="2" data-options="field:'qty_unit',halign:'center',align:'right',sortable:'true',formatter:numberFormat">Qty/Unit</th>
            <th rowspan="2" data-options="field:'qty_month',halign:'center',align:'right',sortable:'true',formatter:numberFormat">Qty/Month</th>
            <th rowspan="2" data-options="field:'um_name',halign:'center',sortable:'true'">UoM</th>
            <th rowspan="2" data-options="field:'currency',halign:'center',sortable:'true'">Currency</th>
            <th rowspan="2" data-options="field:'price',halign:'center',sortable:'true'">Price</th>
            <th rowspan="2" data-options="field:'new_price',halign:'center',sortable:'true'">New Price</th>
            <th rowspan="2" data-options="field:'percentage',halign:'center',sortable:'true'">Percentage</th>
            <th rowspan="2" data-options="field:'moq',halign:'center',sortable:'true'">MOQ</th>
            <th rowspan="2" data-options="field:'mpq',halign:'center',sortable:'true'">MPQ</th>
            <th rowspan="2" data-options="field:'spq',halign:'center',sortable:'true'">SPQ</th>
            <th rowspan="2" data-options="field:'leadtime',halign:'center',sortable:'true'">Lead Time</th>
            <th rowspan="2" data-options="field:'status',halign:'center',align:'center',styler:cellStyler, formatter:cellFormatter,sortable:'true'">Status</th>
            <th rowspan="2" data-options="field:'remarks1',halign:'center',sortable:'true'">Remarks 1</th>
            <th rowspan="2" data-options="field:'remarks2',halign:'center',sortable:'true'">Remarks 2</th>
            <th rowspan="2" data-options="field:'remarks3',halign:'center',sortable:'true'">Remarks 3</th>
            <th rowspan="2" data-options="field:'procurement_respons_date',halign:'center',sortable:'true'">Procurement<br>Response Date</th>
            <th rowspan="2" data-options="field:'item_id_maker',halign:'center',sortable:'true'">Item ID Maker</th>
            <th rowspan="2" data-options="field:'quot_req',halign:'center',sortable:'true'">Quot Req</th>
            <th rowspan="2" data-options="field:'quotation',halign:'center',sortable:'true'">Quotation</th>
            <th rowspan="2" data-options="field:'incoterms',halign:'center',sortable:'true'">Incoterms</th>
            <th colspan="2" data-options="field:'',halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',halign:'center'"> Updated</th>
            <th colspan="3" data-options="field:'',halign:'center'"> Approved</th>
        </tr>
        <tr>
            <th data-options="field:'created_by_name',align:'center',sortable:'true'"> By</th>
            <th data-options="field:'created_date',align:'center',sortable:'true'"> Date</th>
            <th data-options="field:'updated_by_name',align:'center',sortable:'true'"> By</th>
            <th data-options="field:'updated_date',align:'center',sortable:'true'"> Date</th>
            <th data-options="field:'approved_to',halign:'center',halign:'center',align:'center', styler:styleApproved, formatter:formatApproved">Approval</th>
            <th data-options="field:'approved_by',align:'center'"> By</th>
            <th data-options="field:'approved_date',align:'center'"> Date</th>
        </tr>
    </thead>
</table>
<div id="dlg-historical" class="easyui-dialog" style="width: 99.5%; height: 500px;">
    <table id="dg-historical" class="easyui-datagrid" style="width:99%;">
        <thead>
            <tr>
                <th rowspan="2" data-options="field:'request_date',halign:'center',align:'center',sortable:'true'">Request Date</th>
                <th rowspan="2" data-options="field:'due_date',halign:'center',align:'center',sortable:'true'">Due Date</th>
                <th rowspan="2" data-options="field:'plant_name',halign:'center',align:'center',sortable:'true'">Plant Name</th>
                <th rowspan="2" data-options="field:'volume_per_month',halign:'center',align:'right',sortable:'true'">Volume/Month</th>
                <th rowspan="2" data-options="field:'volume_per_year',halign:'center',align:'right',sortable:'true'">Volume/Year</th>
                <th rowspan="2" data-options="field:'project_category',halign:'center',sortable:'true'">Category</th>
                <th rowspan="2" data-options="field:'assy_no',halign:'center',sortable:'true'">Assy No</th>
                <th rowspan="2" data-options="field:'model_name',halign:'center',sortable:'true'">Model</th>
                <th rowspan="2" data-options="field:'customers_name',halign:'center',sortable:'true'">Customer</th>
                <th rowspan="2" data-options="field:'business_line_name',halign:'center',sortable:'true'">Business Line</th>
                <th rowspan="2" data-options="field:'application',halign:'center',sortable:'true'">Application</th>
                <th rowspan="2" data-options="field:'masspro',halign:'center',sortable:'true'">Masspro</th>
                <th rowspan="2" data-options="field:'end_user',halign:'center',sortable:'true'">End User</th>
                <th rowspan="2" data-options="field:'item_id',halign:'center',sortable:'true'">Item ID</th>
                <th rowspan="2" data-options="field:'item_name',halign:'center',sortable:'true'">Item Name</th>
                <th rowspan="2" data-options="field:'maker_name',halign:'center',sortable:'true'">Maker</th>
                <th rowspan="2" data-options="field:'qty_unit',halign:'center',align:'right',sortable:'true',formatter:numberFormat">Qty/Unit</th>
                <th rowspan="2" data-options="field:'qty_month',halign:'center',align:'right',sortable:'true',formatter:numberFormat">Qty/Month</th>
                <th rowspan="2" data-options="field:'um_name',halign:'center',sortable:'true'">UoM</th>
                <th rowspan="2" data-options="field:'currency',halign:'center',sortable:'true'">Currency</th>
                <th rowspan="2" data-options="field:'price',halign:'center',sortable:'true'">Price</th>
                <th rowspan="2" data-options="field:'new_price',halign:'center',sortable:'true'">New Price</th>
                <th rowspan="2" data-options="field:'percentage',halign:'center',sortable:'true'">Percentage</th>
                <th rowspan="2" data-options="field:'moq',halign:'center',sortable:'true'">MOQ</th>
                <th rowspan="2" data-options="field:'mpq',halign:'center',sortable:'true'">MPQ</th>
                <th rowspan="2" data-options="field:'spq',halign:'center',sortable:'true'">SPQ</th>
                <th rowspan="2" data-options="field:'leadtime',halign:'center',sortable:'true'">Lead Time</th>
                <th rowspan="2" data-options="field:'status',halign:'center',align:'center',styler:cellStyler, formatter:cellFormatter,sortable:'true'">Status</th>
                <th rowspan="2" data-options="field:'remarks1',halign:'center',sortable:'true'">Remarks</th>
                <th rowspan="2" data-options="field:'remarks2',halign:'center',sortable:'true'">Remarks</th>
                <th rowspan="2" data-options="field:'remarks3',halign:'center',sortable:'true'">Remarks</th>
                <th rowspan="2" data-options="field:'procurement_respons_date',halign:'center',sortable:'true'">Procurement<br>Response Date</th>
                <th rowspan="2" data-options="field:'item_id_maker',halign:'center',sortable:'true'">Item ID Maker</th>
                <th rowspan="2" data-options="field:'quot_req',halign:'center',sortable:'true'">Quot Req</th>
                <th rowspan="2" data-options="field:'quotation',halign:'center',sortable:'true'">Quotation</th>
                <th rowspan="2" data-options="field:'incoterms',halign:'center',sortable:'true'">Incoterms</th>
                <th colspan="2" data-options="field:'',halign:'center'"> Created</th>
                <th colspan="2" data-options="field:'',halign:'center'"> Updated</th>
                <th colspan="3" data-options="field:'',halign:'center'"> Approved</th>
            </tr>
            <tr>
                <th data-options="field:'created_by_name',align:'center',sortable:'true'"> By</th>
                <th data-options="field:'created_date',align:'center',sortable:'true'"> Date</th>
                <th data-options="field:'updated_by_name',align:'center',sortable:'true'"> By</th>
                <th data-options="field:'updated_date',align:'center',sortable:'true'"> Date</th>
                <th data-options="field:'approved_to',halign:'center',halign:'center',align:'center', styler:styleApproved, formatter:formatApproved">Approval</th>
                <th data-options="field:'approved_by',align:'center'"> By</th>
                <th data-options="field:'approved_date',align:'center'"> Date</th>
            </tr>
        </thead>
    </table>
</div>
<div id="dlg-form" class="easyui-dialog" style="padding: 3px;"><table><tr>
    <td valign="top"><table>
        <tr>
            <td>Request Date</td>
            <td><input id="f_request_date" class="easyui-textbox" readonly></td>
        </tr>
        <tr>
            <td>Due Date</td>
            <td><input id="f_due_date" class="easyui-textbox" readonly></td>
        </tr>
        <tr>
            <td>Plant Name</td>
            <td><input id="f_plant_name" class="easyui-textbox" readonly></td>
        </tr>
        <tr>
            <td>Volume/Month</td>
            <td><input id="f_volume_per_month" class="easyui-textbox" readonly></td>
        </tr>
        <tr>
            <td>Volume/Year</td>
            <td><input id="f_volume_per_year" class="easyui-textbox" readonly></td>
        </tr>
        <tr>
            <td>Category</td>
            <td><input id="f_project_category" class="easyui-textbox" readonly></td>
        </tr>
        <tr>
            <td>Assy No</td>
            <td><input id="f_assy_no" class="easyui-textbox" readonly></td>
        </tr>
        <tr>
            <td>Model</td>
            <td><input id="f_model_name" class="easyui-textbox" readonly></td>
        </tr>
        <tr>
            <td>Customer</td>
            <td><input id="f_customers_name" class="easyui-textbox" readonly></td>
        </tr>
        <tr>
            <td>Business Line</td>
            <td><input id="f_business_line_name" class="easyui-textbox" readonly></td>
        </tr>
        <tr>
            <td>Application</td>
            <td><input id="f_application" class="easyui-textbox" readonly></td>
        </tr>
        <tr>
            <td>Masspro</td>
            <td><input id="f_masspro" class="easyui-textbox" readonly></td>
        </tr>
        <tr>
            <td>End User</td>
            <td><input id="f_end_user" class="easyui-textbox" readonly></td>
        </tr>
        <tr>
            <td>Item ID</td>
            <td><input id="f_item_id" class="easyui-textbox" readonly></td>
        </tr>
        <tr>
            <td>Item Name</td>
            <td><input id="f_item_name" class="easyui-textbox" readonly></td>
        </tr>
        <tr>
            <td>Maker</td>
            <td><input id="f_maker_name" class="easyui-textbox" readonly></td>
        </tr>
        <tr>
            <td>Qty/Unit</td>
            <td><input id="f_qty_unit" class="easyui-textbox" readonly></td>
        </tr>
        <tr>
            <td>Qty/Month</td>
            <td><input id="f_qty_month" class="easyui-textbox" readonly></td>
        </tr>
        <tr>
            <td>UoM</td>
            <td><input id="f_um_name" class="easyui-textbox" readonly></td>
        </tr>
    </table></td>
    <td>&nbsp;</td>
    <td valign="top"><table>
        <tr>
            <td>Currency</td>
            <td><input id="f_currency" class="easyui-combobox" required></td>
        </tr>
        <tr>
            <td>Price</td>
            <td><input id="f_price" class="easyui-numberbox" required></td>
        </tr>
        <tr>
            <td>MOQ</td>
            <td><input id="f_moq" class="easyui-numberbox" required></td>
        </tr>
        <tr>
            <td>MPQ</td>
            <td><input id="f_mpq" class="easyui-numberbox" required></td>
        </tr>
        <tr>
            <td>SPQ</td>
            <td><input id="f_spq" class="easyui-numberbox" required></td>
        </tr>
        <tr>
            <td>Lead Time</td>
            <td><input id="f_leadtime" class="easyui-numberbox" required></td>
        </tr>
        <tr>
            <td>Status</td>
            <td><input id="f_status" class="easyui-textbox" readonly></td>
        </tr>
        <tr>
            <td>Remarks 1</td>
            <td><input id="f_remarks1" class="easyui-textbox" required></td>
        </tr>
        <tr>
            <td>Remarks 2</td>
            <td><input id="f_remarks2" class="easyui-textbox" required></td>
        </tr>
        <tr>
            <td>Remarks 3</td>
            <td><input id="f_remarks3" class="easyui-textbox" required></td>
        </tr>
        <tr>
            <td>Procurement Response Date</td>
            <td><input id="f_procurement_respons_date" class="easyui-datebox" required></td>
        </tr>
        <tr>
            <td>Item ID Maker</td>
            <td><input id="f_item_id_maker" class="easyui-textbox" required></td>
        </tr>
        <tr>
            <td>Quot Req</td>
            <td><input id="f_quot_req" class="easyui-textbox" required></td>
        </tr>
        <tr>
            <td>Quotation</td>
            <td><input id="f_quotation" class="easyui-textbox" required></td>
        </tr>
        <tr>
            <td>Incoterms</td>
            <td><input id="f_incoterms" class="easyui-combobox" required></td>
        </tr>
    </table></td>
</tr></table></div>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar"><div style="width: 100%;"><?= $button ?></div></div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('master/new_part_price_procurements/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    var getId = '';
    function edit(id) {
        var dg = $("#dg").datagrid("getRows").find(a => a.id == atob(id));
        if (dg.approve_to != '') { toastr.warning("The data is currently being checked!", 'Warning'); }
        else {
            getId = id;
            $("#f_request_date").textbox("setValue", dg.request_date);
            $("#f_due_date").textbox("setValue", dg.due_date);
            $("#f_plant_name").textbox("setValue", dg.plant_name);
            $("#f_volume_per_month").textbox("setValue", dg.volume_per_month);
            $("#f_volume_per_year").textbox("setValue", dg.volume_per_year);
            $("#f_project_category").textbox("setValue", dg.project_category);
            $("#f_assy_no").textbox("setValue", dg.assy_no);
            $("#f_model_name").textbox("setValue", dg.model_name);
            $("#f_customers_name").textbox("setValue", dg.customers_name);
            $("#f_business_line_name").textbox("setValue", dg.business_line_name);
            $("#f_application").textbox("setValue", dg.application);
            $("#f_masspro").textbox("setValue", dg.masspro);
            $("#f_end_user").textbox("setValue", dg.end_user);
            $("#f_item_id").textbox("setValue", dg.item_id);
            $("#f_item_name").textbox("setValue", dg.item_name);
            $("#f_maker_name").textbox("setValue", dg.maker_name);
            $("#f_qty_unit").textbox("setValue", dg.qty_unit);
            $("#f_qty_month").textbox("setValue", dg.qty_month);
            $("#f_um_name").textbox("setValue", dg.um_name);
            $("#f_currency").combobox("setValue", dg.currency);
            $("#f_price").numberbox("setValue", dg.price);
            $("#f_moq").numberbox("setValue", dg.moq);
            $("#f_mpq").numberbox("setValue", dg.mpq);
            $("#f_spq").numberbox("setValue", dg.spq);
            $("#f_leadtime").numberbox("setValue", dg.leadtime);
            $("#f_status").textbox("setValue", (dg.status == 0) ? "OPEN" : "CLOSED");
            $("#f_remarks1").textbox("setValue", dg.remarks1);
            $("#f_remarks2").textbox("setValue", dg.remarks2);
            $("#f_remarks3").textbox("setValue", dg.remarks3);
            $("#f_procurement_respons_date").datebox("setValue", dg.procurement_respons_date);
            $("#f_item_id_maker").textbox("setValue", dg.item_id_maker);
            $("#f_quot_req").textbox("setValue", dg.quot_req);
            $("#f_quotation").textbox("setValue", dg.quotation);
            $("#f_incoterms").combobox("setValue", dg.incoterms);
            $("#dlg-form").dialog("open");
        }
    }

    function historical(id) {
        $("#dg-historical").datagrid("reload", {id:id});
        $("#dlg-historical").dialog("open");
    }

    //PRINT PDF
    function pdf() { $("#printout").get(0).contentWindow.print(); }

    //PRINT EXCEL
    function excel() { window.location.assign('<?= base_url('master/new_part_price_procurements/print/excel') ?>'); }

    //RELOAD
    function reload() { window.location.reload(); }

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('master/new_part_price_procurements/datatables') ?>',
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            rowStyler: function(index, row) {
                if (row.mkt_gp_0 == "0.0000") {
                    return 'background-color:#FF7878;color:white;font-weight:bold;';
                }
            },
            onLoadSuccess: function () { $("#printout").attr("src", "<?= base_url('master/new_part_price_procurements/print') ?>"); }
        }).datagrid("enableFilter");
        $('#dg-historical').datagrid({
            url: '<?= base_url('master/new_part_price_procurements/datatableHistorical') ?>',
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            rowStyler: function(index, row) { if (row.mkt_gp_0 == "0.0000") { return 'background-color:#FF7878;color:white;font-weight:bold;'; } }
        }).datagrid("enableFilter");

        $("#dlg-historical").dialog({title: "Historical", modal: true, closed: true});
        $("#dlg-form").dialog({
            title: "Edit",
            modal: true,
            closed: true,
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    var data = {
                        id: getId,
                        currency: btoa($("#f_currency").combobox("getValue")),
                        price: btoa($("#f_price").numberbox("getValue")),
                        moq: btoa($("#f_moq").numberbox("getValue")),
                        mpq: btoa($("#f_mpq").numberbox("getValue")),
                        spq: btoa($("#f_spq").numberbox("getValue")),
                        leadtime: btoa($("#f_leadtime").numberbox("getValue")),
                        status: btoa($("#f_status").textbox("getValue")),
                        remarks1: btoa($("#f_remarks1").textbox("getValue")),
                        remarks2: btoa($("#f_remarks2").textbox("getValue")),
                        remarks3: btoa($("#f_remarks3").textbox("getValue")),
                        procurement_respons_date: btoa($("#f_procurement_respons_date").datebox("getValue")),
                        item_id_maker: btoa($("#f_item_id_maker").textbox("getValue")),
                        quot_req: btoa($("#f_quot_req").textbox("getValue")),
                        quotation: btoa($("#f_quotation").textbox("getValue")),
                        incoterms: btoa($("#f_incoterms").combobox("getValue"))
                    };
                    if (Object.values(data).includes("")) { toastr.warning("Please fill in all fields!", 'Warning'); }
                    else {
                        $("#dlg-form").dialog("mask", "Loading, please wait...");
                        $.ajax({
                            url: "<?= base_url('master/new_part_price_procurements/saveEdit') ?>",
                            data: data,
                            async: false,
                            method: 'POST',
                            dataType: 'json',
                            success: function (result) {
                                if (result.status != 'ok') { toastr.error("Failed to save!", 'ERROR'); }
                                else {
                                    $('#dg').datagrid("reload");
                                    $("#dlg-form").dialog("close");
                                    toastr.success("Saved successfully", 'SUCCESS');
                                }
                            },
                            complete: function () { $("#dlg-form").dialog("unmask"); }
                        });
                    }
                }
            }]
        });

        $("#f_currency").combobox({
            url: "<?= base_url('master/new_part_price_procurements/currency') ?>",
            valueField: "number",
            textField: "name",
            editable: false
        });
        $("#f_price").numberbox({precision: 4});
        $("#f_moq").numberbox({precision: 4});
        $("#f_mpq").numberbox({precision: 4});
        $("#f_spq").numberbox({precision: 4});
        $("#f_leadtime").numberbox({precision: 4});
        $("#f_procurement_respons_date").datebox({editable: false});
        $("#f_incoterms").combobox({
            url: "<?= base_url('master/new_part_price_procurements/incoterms') ?>",
            valueField: "number",
            textField: "name",
            editable: false
        });
    });

    //CELLSTYLE STATUS
    function cellStyler(value, row, index) {
        if (value == 0) {
            return 'background: #FF5F5F; color:white;';
        } else {
            return 'background: #53D636; color:white;';
        }
    }
    //FORMATTER STATUS
    function actionFormat(value, row) {
        return `<a href="javascript:;" class="btn btn-success" onclick="edit('`+btoa(value)+`')" style="pointer-events: auto; opacity: 1;"><span class="fa fa-edit"></span> Edit</a>`+
        `&nbsp;<a href="javascript:;" class="btn btn-primary" onclick="historical('`+btoa(value)+`')" style="pointer-events: auto; opacity: 1;"><span class="fa fa-book"></span> Historical</a>`;
    }

    function cellFormatter(value) {
        if (value == 0) {
            return 'OPEN';
        } else {
            return 'CLOSED';
        }
    }

    function numberFormat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {minimumFractionDigits: 4});
        return "<b>" + formatter.format(value) + "</b>";
    }

    //CELLSTYLE APPROVE
    function styleApproved(value, row, index) {
        if (value == "") {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    //FORMATTER APPROVAL
    function formatApproved(val) {
        if (val == "") {
            return 'Approved';
        } else {
            return 'Checking';
        }
    }
</script>