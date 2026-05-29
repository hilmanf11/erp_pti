<style>
    @keyframes blink {
        0%, 50%, 100% {
            opacity: 1;
        }
        25%, 75% {
            opacity: 0;
        }
    }

    .blink {
        animation: blink 2s infinite;
        font-size: 20px !important;
        color: red;
        font-weight: bold;
    }
</style>
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead frozen="true">
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'supplier_no',width:80,align:'center'">Vendor ID</th>
            <th data-options="field:'supplier_name',width:250,halign:'center'">Vendor Name</th>
        </tr>
    </thead>
    <thead>
        <tr>
            <th rowspan="2" data-options="field:'supplier_type',width:120,halign:'center'">Supplier Type</th>
            <th rowspan="2" data-options="field:'product_family_name',width:120,halign:'center'">Product Family</th>
            <th rowspan="2" data-options="field:'purchase_request_no',width:180,halign:'center'">PR No</th>
            <th rowspan="2" data-options="field:'purchase_order_no',width:180,halign:'center'">PO No</th>
            <th rowspan="2" data-options="field:'purchase_order_date',width:80,halign:'center'">PO Date</th>
            <th rowspan="2" data-options="field:'revision',width:80,align:'center'">Revision</th>
            <th rowspan="2" data-options="field:'total_qty',width:100,halign:'center',align:'right',formatter:numberformatPrice">Qty</th>
            <th rowspan="2" data-options="field:'currency',width:80,align:'center'">Currency</th>
            <th rowspan="2" data-options="field:'total_amount',width:120,halign:'center',align:'right',formatter:numberformatPrice">Amount</th>
            <th rowspan="2" data-options="field:'detail',width:80,halign:'center',formatter:BtnDetail">Detail</th>
            <th rowspan="2" data-options="field:'print_po',width:80,halign:'center',formatter:BtnPrint">Print PO</th>
            <th colspan="2" data-options="field:'Send_mail',width:150,halign:'center'">Schedule Delivery</th>
            <th rowspan="2" data-options="field:'erp',width:80,align:'center',formatter:BtnPush">Push ERP</th>
            <th rowspan="2" data-options="field:'push_date',width:150,align:'center'">Push Date</th>
            <th colspan="3" data-options="field:'Send_mail',width:100,halign:'center'">Send Mail</th>
            <th colspan="2" data-options="field:'Send_mail',width:100,halign:'center'">Receipt Confirmation</th>
            <th colspan="3" data-options="field:'Send_mail',width:100,halign:'center'">Approved</th>
            <th colspan="2" data-options="field:'Send_mail',width:100,halign:'center'">Created</th>
            <th colspan="2" data-options="field:'Send_mail',width:100,halign:'center'">Updated</th>
        </tr>
        <tr>
            <th data-options="field:'create_sd',width:80,halign:'center',formatter:BtnCreateSd">Create</th>
            <th data-options="field:'print_sd',width:80,halign:'center',formatter:BtnPrintSd">Print</th>
            <th data-options="field:'send',width:80,align:'center',formatter:BtnSend">Send</th>
            <th data-options="field:'email_status',width:80,align:'center', styler:cellStylerSend, formatter:cellFormatterSend">Status</th>
            <th data-options="field:'email_date',width:130,align:'center'">Date</th>
            <th data-options="field:'confirm',width:100,align:'center', styler:cellStylerConfirm, formatter:cellFormatterConfirm">Status</th>
            <th data-options="field:'confirm_date',width:120,align:'center'">Date</th>
            <th data-options="field:'approved_to',width:80,align:'center', styler:cellStylerApproval, formatter:cellFormatterApproval">Status</th>
            <th data-options="field:'approved_by',width:80,align:'center'">By</th>
            <th data-options="field:'approved_date',width:120,align:'center'">Date</th>
            <th data-options="field:'created_by',width:80,align:'center'">By</th>
            <th data-options="field:'created_date',width:120,align:'center'">Date</th>
            <th data-options="field:'updated_by',width:80,align:'center'">By</th>
            <th data-options="field:'updated_date',width:120,align:'center'">Date</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 230px; padding:10px;">
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
                    <input style="width:60%;" id="filter_po" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" id="filter_product_family" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part No</span>
                    <input style="width:60%;" id="filter_part_no" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Status</span>
                    <select style="width:60%;" id="filter_status" class="easyui-combobox" panelHeight="auto">
                        <option value="">All</option>
                        <option value="Send">Send</option>
                        <option value="Unsend">Unsend</option>
                        <option value="Push">Push</option>
                        <option value="Unpush">Unpush</option>
                        <option value="Receipt">Receipt</option>
                        <option value="Confirm">Confirm</option>
                    </select>
                </div>
            </div>
        </fieldset>
        <fieldset style="width: 30%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Generating Process</b></legend>

            <b>Convert PR to PO / Push Data</b>
            <div id="p_upload" class="easyui-progressbar" style="width:100%;"></div>
            <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
            <br>
            <input value="on" readonly="true" class="easyui-checkbox" id="check_pr"> &nbsp; Purchase Request
            <center>
                <a href="#" onclick="confirmPo()" id="confirmPo" style="text-decoration: none;"><div class="blink">New Confirm PO</div></a>
            </center>
        </fieldset>
    </div>
    <?= $button ?>
    <a href="javascript:;" class="easyui-linkbutton" plain="true" onclick="csv()"><i class="fa fa-file"></i> Export to CSV</a>
    <a href="javascript:;" class="easyui-linkbutton" plain="true" onclick="priceSyncAll()"><i class="fa fa-money"></i> Sync Prices</a>
    <!-- <a href="javascript:;" class="easyui-linkbutton" plain="true" onclick="toastr.info('Still Development')"><i class="fa fa-money"></i> Sync Prices</a> -->
</div>

<div id="toolbar2" style="height: auto; padding:10px;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">        
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Detail</b></legend>
            <div style="width: 40%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Purchase Order No</span>
                    <input style="width:60%;" id="d_purchase_order_no" class="easyui-textbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" id="detailEdit" class="easyui-linkbutton" onclick="detailEdit()"><i class="fa fa-edit"></i> Edit Data</a>
                    <a href="javascript:;" id="editSchedule" class="easyui-linkbutton" onclick="editSchedule()"><i class="fa fa-edit"></i> Edit Schedule Delivery</a>
                    <a href="javascript:;" id="pushData" class="easyui-linkbutton c5" onclick="pushData()"><i class="fa fa-database"></i> Push Data</a>
                    <a href="javascript:;" id="sendMail" class="easyui-linkbutton" onclick="sendMail()"><i class="fa fa-send"></i> Send Mail</a>
                    <a href="javascript:;" id="completeMail" class="easyui-linkbutton c1" onclick="completeMail()"><i class="fa fa-check"></i> Complete Mail</a>
                </div>
            </div>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier Name</span>
                    <input style="width:60%;" id="d_supplier" class="easyui-textbox" readonly>
                </div>
                <div class="fitem" id="completeMailDate">
                    <span style="width:35%; display:inline-block;">Send Date</span>
                    <input style="width:60%;" id="d_email_date" class="easyui-datebox" value="<?= date("Y-m-d") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
            </div>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" id="d_product_family" class="easyui-textbox" readonly>
                </div>
            </div>

            <div style="width: 100%; float: left;">
                <div class="alert alert-danger" role="alert" id="sendBlink">
                    <small>Please Recheck before send Mail to</small> <b id="sendMailText"></b>
                </div>
            </div>
        </fieldset>
    </div>
</div>

<div id="toolbar3" style="height: auto; padding:10px;">
    <a href="javascript:;" class="easyui-linkbutton" plain="false" onclick="export_excel3()"><i class="fa fa-file"></i> &nbsp; Export Excel</a>
</div>

<div id="dlg_purchase_order" class="easyui-dialog" title="Detail Purchase Order" data-options="closed: true,modal:true" style="width: 98%; height: 550px; top: 0px; left: 0px;">
    <table id="dg_purchase_order" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar2" showFooter="true">
        <thead>
            <tr>
                <th field="ck" checkbox="true"></th>
                <th data-options="field:'item_id',width:150,halign:'center'">Part No</th>
                <th data-options="field:'item_name',width:100,halign:'center'">Part Name</th>
                <th data-options="field:'uom',width:80,align:'center'">Uom</th>
                <th data-options="field:'mpq',width:80,halign:'center'">MPQ</th>
                <th data-options="field:'moq',width:80,halign:'center'">MOQ</th>
                <th data-options="field:'leadtime',width:80,halign:'center'">Leadtime</th>
                <th data-options="field:'eta',width:100,align:'center'">ETA</th>
                <th data-options="field:'etd',width:100,align:'center'">ETD</th>
                <th data-options="field:'qty',width:100,halign:'center',align:'right',formatter:numberformat">Qty</th>
                <th data-options="field:'price',width:100,halign:'center',align:'right',formatter:numberformatPrice">Price</th>
                <th data-options="field:'total',width:150,halign:'center',align:'right',formatter:numberformatPrice">Total</th>
                <th data-options="field:'currency',width:80,align:'center'">Currency</th>
                <th data-options="field:'sync',width:80,align:'center',formatter:BtnSync">#</th>
                <!-- <th data-options="field:'eta',width:100,halign:'center'">ETA</th> -->
                <th data-options="field:'rev',width:50,align:'center'">Rev</th>
                <th data-options="field:'reason',width:150,halign:'center'">Reason</th>
            </tr>
        </thead>
    </table>
</div>

<div id="dlg_confirm" class="easyui-dialog" title="Confrim PO" toolbar="#toolbar3" data-options="closed: true,modal:true" style="width: 100%; height: 550px; top: 0px; left: 0px;">
    <table id="dg_confirm" class="easyui-datagrid" style="width:100%;">
        <thead>
            <tr>
                <!-- <th data-options="field:'confirm',width:100,halign:'center',formatter:BtnConfirm">Confirm</th> -->
                <th data-options="field:'p_month',width:50,halign:'center'">Month</th>
                <th data-options="field:'p_year',width:50,halign:'center'">Year</th>
                <th data-options="field:'revision',width:50,halign:'center'">Rev</th>
                <th data-options="field:'purchase_request_no',width:180,halign:'center'">PR No</th>
                <th data-options="field:'purchase_request_date',width:100,halign:'center'">PR Date</th>
                <th data-options="field:'purchase_order_no',width:150,halign:'center'">PO No</th>
                <th data-options="field:'purchase_order_date',width:100,halign:'center'">PO Date</th>
                <th data-options="field:'part_no',width:200,halign:'center'">Part No</th>
                <th data-options="field:'part_name',width:120,halign:'center'">Part Name</th>
                <th data-options="field:'supplier_name',width:250,halign:'center'">Vendor Name</th>
                <th data-options="field:'product_family',width:150,halign:'center'">Product Family</th>
                <th data-options="field:'total_pr',width:100,halign:'center',align:'right',formatter:numberformatPrice,styler:cellStylerPrice">Total PR</th>
                <th data-options="field:'total_po',width:100,halign:'center',align:'right',formatter:numberformatPrice,styler:cellStylerPrice">Total PO</th>
                <th data-options="field:'total_amount',width:150,halign:'center',align:'right',formatter:numberformatPrice">Total Amount</th>
                <th data-options="field:'reason_pr',width:350,halign:'center'">Reason PR</th>
                <th data-options="field:'reason_po',width:350,halign:'center'">Reason PO</th>
            </tr>
        </thead>
    </table>
</div>

<div id="dlg_insert" class="easyui-dialog" title="Update Data" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Revision</span>
                <input style="width:20%;" name="revision" id="revision" readonly class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">PO No</span>
                <input style="width:50%;" name="purchase_order_no" id="purchase_order_no" class="easyui-combogrid">
            </div>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Supplier ID</span>
                <input style="width:50%;" name="supplier_id" id="supplier_id" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Supplier No</span>
                <input style="width:50%;" name="supplier_no" id="supplier_no" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Supplier Name</span>
                <input style="width:50%;" name="supplier_name" id="supplier_name" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Part No</span>
                <input style="width:50%;" name="item_id" id="item_id" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Part Name</span>
                <input style="width:50%;" name="item_name" id="item_name" readonly class="easyui-textbox">
            </div>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Currency</span>
                <input style="width:50%;" name="currency" id="currency" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Qty</span>
                <input style="width:30%;" name="qty" id="qty" required="true" class="easyui-numberbox">
            </div>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Price</span>
                <input style="width:30%;" name="price" id="price" class="easyui-numberbox" data-options="precision: 4">
            </div>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Total</span>
                <input style="width:30%;" name="total" id="total" readonly class="easyui-numberbox" data-options="precision: 4">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Reason</span>
                <input style="width:50%;" name="reason" id="reason" required class="easyui-textbox">
            </div>
        </fieldset>
    </form>
</div>

<div id="dlg_insert_schedule" class="easyui-dialog" title="Update Data" data-options="closed: true,modal:true" style="width: 800px; padding:10px; top: 20px; left:10px;">
    <form id="frm_insert_schedule" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">PO No</span>
                    <input style="width:60%;" name="po_no" id="sch_po_no" disabled class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier name</span>
                    <input style="width:60%;" name="supplier_name" id="sch_supplier_name" disabled class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" name="product_family" id="sch_product_family" disabled class="easyui-textbox">
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Item ID</span>
                    <input style="width:60%;" name="part_no" id="sch_part_no" disabled class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Item Name</span>
                    <input style="width:60%;" name="part_name" id="sch_part_name" disabled class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Qty</span>
                    <input style="width:30%;" name="qty" id="sch_qty" readonly class="easyui-numberbox" data-options="groupSeparator:'.'">
                </div>
            </div>
            <div style="width: 100%; float: left; margin-top: 20px;">
                <fieldset style="width:32%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                    <legend><b><span id="p1"></span></b></legend>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Qty</span>
                        <input style="width:60%;" name="delivey_qty_1" id="delivey_qty_1" disabled class="easyui-numberbox" data-options="groupSeparator:'.'">
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
                        <input style="width:60%;" name="delivey_qty_2" id="delivey_qty_2" disabled class="easyui-numberbox" data-options="groupSeparator:'.'">
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
                        <input style="width:60%;" name="delivey_qty_3" id="delivey_qty_3" disabled class="easyui-numberbox" data-options="groupSeparator:'.'">
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
                        <input style="width:60%;" name="delivey_qty_4" id="delivey_qty_4" disabled class="easyui-numberbox" data-options="groupSeparator:'.'">
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
                        <input style="width:60%;" name="delivey_qty_5" id="delivey_qty_5" disabled class="easyui-numberbox" data-options="groupSeparator:'.'">
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
                        <input style="width:60%;" name="delivey_qty_6" id="delivey_qty_6" disabled class="easyui-numberbox" data-options="groupSeparator:'.'">
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
            <div style="width: 100%; float: left;">
                <div class="fitem">
                    <span style="width:15%; display:inline-block;">Remarks</span>
                    <input style="width:80%;" name="remarks" id="sch_remarks" required class="easyui-textbox">
                </div>
            </div>
        </fieldset>
    </form>
</div>

<div id="dlg_mail" class="easyui-dialog" title="Send Mail" data-options="closed: true,modal:true" style="width: 900px; height: 550px; padding:10px; top: 20px;">
    <form id="frm_mail" method="post" enctype="multipart/form-data" novalidate>
        <div class="alert alert-warning">
            If there is more than one email, please separate them with semicolons
        </div>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="float: left; width: 50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">From</span>
                    <input style="width:60%;" name="mail_from" id="mail_from" value="procurement01@banshu-ind.com" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">To</span>
                    <input style="width:60%; height: 70px;" name="mail_to" id="mail_to" class="easyui-textbox" data-options="multiline:true">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">CC</span>
                    <input style="width:60%; height: 70px;" name="mail_cc" id="mail_cc" class="easyui-textbox" data-options="multiline:true">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">PO No</span>
                    <input style="width:60%;" name="mail_po" id="mail_po" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Format Mail</span>
                    <select style="width:60%;" name="mail_format" id="mail_format" required class="easyui-combobox" panelHeight="auto">
                        <option value="">Choose Format</option>
                        <option value="Indonesia">Indonesia</option>
                        <option value="English">English</option>
                    </select>
                </div>
            </div>
            <div style="float: left; width: 50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Subject</span>
                    <input style="width:60%;" name="mail_subject" id="mail_subject" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">File Purchase Order</span>
                    <input style="width:60%;" name="attachment" id="attachment" class="easyui-filebox" required accept=".pdf">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">File Delivery Schedule</span>
                    <input style="width:60%;" name="attachment2" id="attachment2" class="easyui-filebox" required accept=".pdf">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">File Forecast</span>
                    <input style="width:60%;" name="attachment3" id="attachment3" class="easyui-filebox" accept=".pdf">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">File Other 1</span>
                    <input style="width:60%;" name="attachment4" id="attachment4" class="easyui-filebox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">File Other 2</span>
                    <input style="width:60%;" name="attachment5" id="attachment5" class="easyui-filebox">
                </div>
            </div>
            <div style="float: left; width: 100%; margin-top: 20px;">
                <div class="easyui-panel" title="Message Mail" style="width:98%; height:200px; padding:10px;">
                    <div id="mail_message">

                    </div>
                </div>
            </div>
        </fieldset>
    </form>
</div>

<!-- Upload -->
<div id="dlg_upload" class="easyui-dialog" title="Upload Data" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_upload" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">File Upload</span>
                <input name="file_upload" style="width: 60%;" required="" accept=".xls" id="file_excel" class="easyui-filebox">
            </div>
        </fieldset>
    </form>
    <div id="p_upload2" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start2">0</b> Of <b id="p_finish2">0</b></center>
    <div id="p_remarks" class="easyui-panel" style="width:100%; height:200px; padding:10px; margin-top: 10px;">
        <p>History Upload</p>
        <ul id="remarks">

        </ul>
    </div>
</div>

<!-- PDF -->
<iframe id="printout" src="" style="width: 100%;" hidden></iframe>

<script>
    function add() {
        toastr.info("Not Ready this Function");
    }

    function editSchedule(){
        var row = $('#dg_purchase_order').datagrid('getSelected');
        if (row) {
            $('#dlg_insert_schedule').dialog('open');

            $.ajax({
                type: "get",
                url: "<?= base_url('planning/delivery_schedules/readMonths') ?>",
                data: "month=" + row.p_month + "&year=" + row.p_year,
                dataType: "json",
                success: function(monthName) {
                    $("#p1").html(monthName[0]);
                    $("#p2").html(monthName[1]);
                    $("#p3").html(monthName[2]);
                    $("#p4").html(monthName[3]);
                    $("#p5").html(monthName[4]);
                    $("#p6").html(monthName[5]);
                }
            });

            $.ajax({
                type: "post",
                url: "<?= base_url("planning/purchase_orders/get_delivery_schedules") ?>",
                data: "po_no=" + btoa(row.purchase_order_no) + "&part_no=" + btoa(row.item_id),
                dataType: "json",
                success: function (response) {
                    $('#frm_insert_schedule').form('load', response);

                    url_save_schedule = '<?= base_url('planning/delivery_schedules/update') ?>?id=' + btoa(response.id);
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    // function checkConfirmPo(){
    //     $.ajax({
    //         type: "post",
    //         url: '<?= base_url('planning/purchase_orders/datatableConfirm') ?>',
    //         dataType: "json",
    //         success: function (response) {
    //             if(response.total > 0){
    //                 $("#confirmPo").show();
    //             }else{
    //                 $("#confirmPo").hide();
    //             }
    //         }
    //     });
    // }

    // checkConfirmPo();
    // setInterval(checkConfirmPo, 10000000);

    // function confirmPo() {
    //     $("#dlg_confirm").dialog('open');
        
    //     $('#dg_confirm').datagrid({
    //         url: '<?= base_url('planning/purchase_orders/datatableConfirm') ?>',
    //         clientPaging: false,
    //         remoteFilter: true,
    //         rownumbers: true,
    //         singleSelect: false,
    //         fit:true,
    //     });
    // }

    function confirm(purchase_request_no) {
        $.ajax({
            type: "post",
            url: '<?= base_url('planning/purchase_orders/updateConfirm') ?>',
            data: "purchase_request_no=" + purchase_request_no,
            dataType: "json",
            success: function (response) {
                toastr.success("Data has been Confirmed");
                $("#dg_confirm").datagrid("reload");
            }
        });
    }
    
    function details(purchase_order_no, supplier, product_family, push="", email = "", email_cc = "") {
        $("#dlg_purchase_order").dialog('open');

        $("#d_purchase_order_no").textbox('setValue', purchase_order_no);
        $("#d_supplier").textbox('setValue', supplier);
        $("#d_product_family").textbox('setValue', product_family);
        
        if(push == ""){
            $("#pushData").hide();
            $("#detailEdit").show();
            $("#editSchedule").show();
            $("#sendMail").hide();
            $("#completeMail").hide();
            $("#sendBlink").hide();
            $("#completeMailDate").hide();
        }else if(push == "push"){
            $("#pushData").show();
            $("#detailEdit").hide();
            $("#editSchedule").hide();
            $("#sendMail").hide();
            $("#completeMail").hide();
            $("#sendBlink").hide();
            $("#completeMailDate").hide();
        }else{
            $("#pushData").hide();
            $("#detailEdit").hide();
            $("#editSchedule").hide();
            $("#sendMail").show();
            $("#completeMail").show();
            $("#completeMailDate").show();
            $("#sendBlink").show();
            $("#sendMailText").html(email);
            $("#mail_to").textbox("setValue", email);
            $("#mail_cc").textbox("setValue", email_cc);
            $("#mail_po").textbox("setValue", purchase_order_no);
            $("#mail_subject").textbox("setValue", "PO#" + purchase_order_no + "_" + supplier);
        }

        var revision = $("#filter_revision").combobox('getValue');
        var filter_part_no = $("#filter_part_no").combogrid('getValue');

        $('#dg_purchase_order').datagrid('loading');

        $.ajax({
            url: '<?= base_url('planning/purchase_orders/datatableDetails/') ?>' + btoa(purchase_order_no) + "/" + revision + "?filter_part_no=" + btoa(filter_part_no),
            type: 'POST',
            dataType: 'json',
            success: function(result) {
                $('#dg_purchase_order').datagrid('loadData', result).datagrid('loaded');
            }
        });
    }

    function detailEdit(){
        var row = $('#dg_purchase_order').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');

            $("#purchase_order_no").combogrid({
                url: '<?= base_url('planning/purchase_orders/readPo/') ?>' + row.p_month + '/' + row.p_year + '/' + row.revision + '/' + row.product_family,
                panelWidth: 600,
                idField: 'purchase_order_no',
                textField: 'purchase_order_no',
                mode: 'remote',
                fitColumns: true,
                prompt: "Select PO No",
                columns: [
                    [{
                        field: 'purchase_order_no',
                        title: 'PO No',
                        width: 150
                    }, {
                        field: 'supplier_name',
                        title: 'Name',
                        width: 250
                    }, {
                        field: 'product_family_name',
                        title: 'Product Family',
                        width: 150
                    }]
                ],
                onSelect: function(index, po){
                    $("#supplier_id").textbox("setValue", po.supplier_id);
                    $("#supplier_no").textbox("setValue", po.supplier_no);
                    $("#supplier_name").textbox("setValue", po.supplier_name);
                }
            });

            $('#frm_insert').form('load', row);

            url_save = '<?= base_url('planning/purchase_orders/updateDetails') ?>?id=' + btoa(row.id);
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function priceSync(id){
        $.messager.prompt('Sync Price', 'Please input Password to Sync Price', function(r){
            if (r == "PROCUREMENTUMI2024"){
                // toastr.info("Under Development");
                $.ajax({
                    type: "post",
                    url: "<?= base_url('planning/purchase_orders/syncPrice') ?>",
                    data: "id=" + id,
                    dataType: "json",
                    success: function (result) {
                        if (result.theme == "success") {
                            toastr.success(result.message, result.title);
                        } else {
                            toastr.error(result.message, result.title);
                        }
                
                        $("#dg_purchase_order").datagrid('reload');
                    }
                });
            }
        });

        $('.messager-input').attr('type', 'password');
    }

    function priceSyncAll() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.prompt('Sync Price', 'Please input Password to Sync Price', function(r){
                if (r == "PROCUREMENTUMI2024"){
                    Swal.fire({
                        title: 'Please Wait',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                    });

                    requestDataPrice(rows.length, rows);
                    function requestDataPrice(total, json, number = 1, value = 0, success = 1, failed = 1) {
                        if (value < 100) {
                            value = Math.floor((number / total) * 100);
                            
                            var row = json[number - 1];
                            $.ajax({
                                method: 'post',
                                url: '<?= base_url('planning/purchase_orders/syncPriceAll') ?>',
                                data: {
                                    purchase_order_no: row.purchase_order_no
                                },
                                dataType: 'json',
                                success: function(result) {
                                    if (result.theme == "success") {
                                        requestDataPrice(total, json, number + 1, value, success + 1, failed + 0);
                                        toastr.success(result.message);
                                    } else {
                                        toastr.error(result.message);
                                        requestDataPrice(total, json, number + 1, value, success + 1, failed + 0);
                                    }

                                    if (value == 100) {
                                        Swal.close();
                                        Swal.fire('Good job!', 'Process Sync Price Data Completed!', 'success');
                                        $("#dg").datagrid('reload');
                                    }
                                },
                                error: function(jqXHR, textStatus, errorThrown) {
                                    toastr.error(jqXHR.statusText);
                                    $.messager.alert("Error", jqXHR.statusText, 'error');
                                    Swal.close();
                                },
                            });
                        }
                    }
                }
            });

            $('.messager-input').attr('type', 'password');
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function pushData(){
        var rows = $('#dg_purchase_order').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.prompt('Push Data PO', 'Please input Password to Push', function(r){
                if (r == "PUSHDATA"){
                    $('#dlg_purchase_order').dialog('close');
                    requestData2(rows.length, rows);

                    function requestData2(total, json, number = 1, value = 0, success = 1, failed = 1) {
                        if (value < 100) {
                            value = Math.floor((number / total) * 100);
                            $('#p_upload').progressbar('setValue', value);
                            $('#p_start').html(number);
                            $('#p_finish').html(total);

                            $.post('<?= base_url('planning/purchase_orders/pushData') ?>', {
                                data: json[number - 1]
                            }, function(note) {
                                var result = eval('(' + note + ')');

                                if (result.theme == "success") {
                                    requestData2(total, json, number + 1, value, success + 1, failed + 0);
                                } else {
                                    requestData2(total, json, number + 1, value, success + 0, failed + 1);
                                }

                                if (value == 100) {
                                    Swal.fire('Good job!', 'Process Generate Data Completed!', 'success');
                                    $("#dg").datagrid('reload');
                                }
                            }).fail(function(jqXHR, textStatus) {
                                if (textStatus == "error") {
                                    toastr.error("Connection time out");

                                    requestData2(total, json, number, value, success + 0, failed + 1);
                                }
                            });
                        }
                    }
                }
            });

            $('.messager-input').attr('type', 'password');
        }else{
            toastr.warning("Please select one of the data in the table first!");
        }
    }

    function createSd(purchase_order_no) {
        $.messager.prompt('Create Schedule Delivery', 'Please input Password Generate, Be careful if data is generated for this period it will be deleted', function(r){
            if (r == "DELIVERYSCHEDULE"){
                Swal.fire({
                    title: 'Please Wait for Check Data',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });

                $.ajax({
                    type: "get",
                    url: "<?= base_url('planning/delivery_schedules/getData') ?>",
                    data: "purchase_order_no=" + window.btoa(purchase_order_no),
                    dataType: "json",
                    success: function(rows) {
                        Swal.close();

                        if(rows['total'] > 0){
                            requestData(rows['total'], rows);

                            function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                                if (value < 100) {
                                    value = Math.floor((number / total) * 100);
                                    $('#p_upload').progressbar('setValue', value);
                                    $('#p_start').html(number);
                                    $('#p_finish').html(total);

                                    $.post('<?= base_url('planning/delivery_schedules/create') ?>', {
                                        data: json[number - 1]
                                    }, function(note) {
                                        var result = eval('(' + note + ')');

                                        if (result.theme == "success") {
                                            requestData(total, json, number + 1, value, success + 1, failed + 0);
                                        } else {
                                            requestData(total, json, number + 1, value, success + 0, failed + 1);
                                        }

                                        if (value == 100) {
                                            Swal.fire('Good job!', 'Process Generate Data Completed!', 'success');
                                            $("#dg").datagrid('reload');
                                        }
                                    }).fail(function(jqXHR, textStatus) {
                                        if (textStatus == "error") {
                                            toastr.error("Connection time out");

                                            requestData(total, json, number, value, success + 0, failed + 1);
                                        }
                                    });
                                }
                            }
                        }else{
                            toastr.warning("Data in Purchase Order not available Part No type IMPORT");
                        }
                    },
                    error: function(rows) {
                        Swal.close();
                        Swal.fire('Failed!', 'Generate Schedule Delivery is Failed, Please Try Again!', 'error');
                    }
                });
            }
        });
    }

    function sendMail(){
        // toastr.info("Not Available");
        $("#dlg_mail").dialog('open');
        $("#attachment").filebox('clear');
        $("#mail_message").html("");
        $("#mail_format").combobox("clear");

        $("#mail_format").combobox({
            onChange: function(val){
                $.ajax({
                    type: "post",
                    url: "<?= base_url("planning/config_mail/reads/purchase_orders/") ?>" + val,
                    dataType: "json",
                    success: function (response) {
                        if(response == null){
                            toastr.info("Not Available format, please input in master Setting Mail");
                            $("#mail_message").html("");
                        }else{
                            $("#mail_message").html(response.description);
                        }
                    }
                });
            }
        })
    }

    function completeMail(){
        $.messager.prompt('Complete Mail', 'Please input Password to Complete Mail PO', function(r){
            if (r == "COMPLETEMAIL"){
                var purchase_order_no = $("#d_purchase_order_no").textbox('getValue');
                var email_date = $("#d_email_date").datebox('getValue');

                $.ajax({
                    type: "post",
                    url: "<?= base_url("planning/purchase_orders/completeMail") ?>",
                    data: "purchase_order_no=" + purchase_order_no + "&email_date=" + email_date,
                    dataType: "json",
                    success: function (response) {
                        toastr.success(response.message);
                    }
                });
            }
        });
    }

    function componentCheck(filter_month, filter_year, filter_revision) {
        $.ajax({
            type: "get",
            url: "<?= base_url('planning/purchase_orders/checkPR') ?>",
            data: "filter_month=" + window.btoa(filter_month) +
                "&filter_year=" + window.btoa(filter_year) +
                "&filter_revision=" + window.btoa(filter_revision),
            dataType: "json",
            success: function(mps) {
                if (mps.theme == "success") {
                    $('#check_pr').checkbox({
                        checked: true
                    });
                } else {
                    $('#check_pr').checkbox({
                        checked: false
                    });
                }
            }
        });

        $('#filter_po').combobox({
            url: '<?php echo base_url('planning/purchase_orders/readPoSearch?filter_month='); ?>' + window.btoa(filter_month) + '&filter_year=' + btoa(filter_year) + '&filter_revision=' + btoa(filter_revision),
            valueField: 'purchase_order_no',
            textField: 'purchase_order_no',
            prompt: 'Select PO No',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });
    }

    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        if(row.push_date == null){
                            $.ajax({
                                method: 'post',
                                url: '<?= base_url('planning/purchase_orders/delete') ?>',
                                data: {
                                    purchase_order_no: row.purchase_order_no
                                },
                                success: function(result) {
                                    var result = eval('(' + result + ')');
                                },
                                error: function(jqXHR, textStatus, errorThrown) {
                                    toastr.error(jqXHR.statusText);
                                    $.messager.alert("Error", jqXHR.statusText, 'error');
                                },
                                complete: function(data) {
                                    $('#dg').datagrid('reload');
                                }
                            });
                        }else{
                            toastr.error("Cannot delete data because data has been push to MRP");
                        }
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
        var filter_po = $("#filter_po").combobox('getValue');
        var filter_part_no = $("#filter_part_no").combogrid('getValue');
        var filter_supplier = $("#filter_supplier").combogrid('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_po=" + window.btoa(filter_po) +
            "&filter_product_family=" + window.btoa(filter_product_family) +
            "&filter_status=" + window.btoa(filter_status) +
            "&filter_part_no=" + window.btoa(filter_part_no);

        if (filter_month == "" || filter_year == "") {
            toastr.warning("Please Choose Filter Date!", "Information");
        } else {
            $('#dg').datagrid({
                url: '<?= base_url('planning/purchase_orders/datatables') ?>' + url,
            });

            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('planning/purchase_orders/print') ?>' + url);
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
        var filter_po = $("#filter_po").combobox('getValue');
        var filter_part_no = $("#filter_part_no").combogrid('getValue');
        var filter_supplier = $("#filter_supplier").combogrid('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_po=" + window.btoa(filter_po) +
            "&filter_product_family=" + window.btoa(filter_product_family) +
            "&filter_status=" + window.btoa(filter_status) +
            "&filter_part_no=" + window.btoa(filter_part_no);

        if (filter_month == "" || filter_year == "") {
            toastr.warning("Please Choose Filter Date!", "Information");
        } else {
            window.location.assign('<?= base_url('planning/purchase_orders/print/excel') ?>' + url);
        }
    }

    function export_excel3(){
        $('#dg_confirm').datagrid('toExcel','Data Confirm PR to PO.xls');
    }

    function csv() {
        $.messager.prompt('Export CSV', 'Please input Password to Export to CSV', function(r){
            if (r == "CSV"){
                $.messager.alert('Info','Please Wait Export to CSV');
                window.location.assign('<?= base_url('planning/purchase_orders/csv') ?>');
            }
        });
    }

    //Upload Data
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_purchase_orders.xls') ?>');
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
    	$("#add").html("Generate");
        
        $('#dg').datagrid({
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            singleSelect: false,
            fit:true,
            rowStyler: function(index,row){
                if (row.color == ""){
                    return 'background-color:#FFD2D2;';
                }
            }
        });

        $('#dg_purchase_order').datagrid({
            clientPaging: false,
            rownumbers: true,
            singleSelect: false,
            fit:true,
        }).datagrid('enableFilter');

        $("#qty").numberbox({
            onChange: function(qty){
                var price = $("#price").numberbox("getValue");
                $("#total").numberbox("setValue", (parseInt(qty) * parseInt(price)));
            }
        });

        $("#price").numberbox({
            onChange: function(price){
                var qty = $("#qty").numberbox("getValue");
                $("#total").numberbox("setValue", (parseInt(qty) * parseInt(price)));
            }
        })

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
                            $('#dg_purchase_order').datagrid('reload');
                        }
                    });
                }
            }]
        });

        $('#dlg_insert_schedule').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_insert_schedule').form('submit', {
                        url: url_save_schedule,
                        onSubmit: function() {
                            return $(this).form('validate');
                        },
                        success: function(result) {
                            var result = eval('(' + result + ')');

                            if (result.theme == "success") {
                                toastr.success(result.message);
                            } else {
                                toastr.error(result.message);
                            }
                        }
                    });
                }
            }]
        });

        $('#dlg_mail').dialog({
            buttons: [{
                text: 'Send Mail',
                iconCls: 'icon-ok',
                handler: function() {
                    var mail_format = $("#mail_format").combobox('getValue');
                    var d_purchase_order_no = $("#d_purchase_order_no").textbox('getValue');

                    if(mail_format != ""){
                        $.messager.prompt('Send Mail', 'Please input Password to Send Mail', function(r){
                            if (r == "SENDMAIL"){
                                $('#frm_mail').form('submit', {
                                    url: "<?= base_url("planning/purchase_orders/sendMail/") ?>" + btoa(d_purchase_order_no),
                                    onSubmit: function() {
                                        if($(this).form('validate') === true){
                                            $('#dlg_mail').dialog('close');
                                            $('#dlg_purchase_order').dialog('close');
                                            
                                            Swal.fire({
                                                title: 'Please Wait...',
                                                showConfirmButton: false,
                                                allowOutsideClick: false,
                                                allowEscapeKey: false,
                                                didOpen: () => {
                                                    Swal.showLoading();
                                                },
                                            });
                                        }else{
                                            return $(this).form('validate');
                                        }
                                    },
                                    success: function(result) {

                                        var result = eval('(' + result + ')');

                                        if (result.theme == "success") {
                                            toastr.success(result.message, result.title);
                                        } else {
                                            toastr.error(result.message, result.title);
                                        }

                                        Swal.close();
                                        $('#dg').datagrid('reload');
                                    },
                                    error: function(){
                                        toastr.error("Something Error for Send Mail");
                                    }
                                });
                            }
                        });
                    }else{
                        toastr.error("Please Choose Mail Format");
                    }
                }
            }]
        });

        //Upload Data
        $('#dlg_upload').dialog({
            buttons: [{
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('planning/purchase_orders/upload') ?>',
                        onSubmit: function() {
                            if ($(this).form('validate') == false) {
                                return $(this).form('validate');
                            } else {
                                $.messager.progress({
                                    title: 'Please Wait',
                                    msg: 'Importing Excel to Database'
                                });
                            }
                        },
                        success: function(result) {
                            $.messager.progress('close');
                            var json = eval('(' + result + ')');
                            requestData3(json.total, json);

                            function requestData3(total, json, number = 1, value = 0) {
                                if (value < 100) {
                                    value = Math.floor((number / total) * 100);
                                    $('#p_upload2').progressbar('setValue', value);
                                    $('#p_start2').html(number);
                                    $('#p_finish2').html(total);

                                    $.post('<?= base_url('planning/purchase_orders/uploadCreate') ?>', {
                                        data: json[number - 1]
                                    }, function(note) {
                                        var result = eval('(' + note + ')');
                                        if (result.theme == "success") {
                                            var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                        } else {
                                            var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                                        }
                                        $("#p_remarks").append(title + "<br>");
                                        requestData3(total, json, number + 1, value);
                                    });
                                }
                            }
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
            onSelect: function(month){
                var filter_year = $("#filter_year").combobox('getValue');
                var filter_revision = $("#filter_revision").combobox('getValue');

                if(filter_year != "" && filter_revision != ""){
                    componentCheck(month.id, filter_year, filter_revision);
                }
            }
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
            onSelect: function(year){
                var filter_month = $("#filter_month").combobox('getValue');
                var filter_revision = $("#filter_revision").combobox('getValue');

                if(filter_month != "" && filter_revision != ""){
                    componentCheck(filter_month, year, filter_revision);
                }
            }
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
            onSelect: function(revision){
                var filter_month = $("#filter_month").combobox('getValue');
                var filter_year = $("#filter_year").combobox('getValue');

                if(filter_month != "" && filter_year != ""){
                    componentCheck(filter_month, filter_year, revision.id);
                }
            }
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

    function numberformatPrice(value, row){
        if(row.currency == "IDR"){
            var digit = 0;
        }else{
            var digit = 0;
        }

        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: digit
        });

        return "<b>" + formatter.format(value) + "</b>";
    }

    function BtnDetail(val, row) {
        var details = "details('"+row.purchase_order_no+"','"+row.supplier_name+"','"+row.product_family_name+"')";
        return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" onClick="'+details+'"><i class="fa fa-eye"></i> View</a>';
    }

    function BtnConfirm(val, row) {
        if (parseInt(row.total_pr) == parseInt(row.total_po)){
            var confirm = "confirm('"+row.purchase_request_no+"')";
            return '<a class="btn btn-success w-100" style="pointer-events: visible; opacity:1;" onClick="'+confirm+'"><i class="fa fa-check"></i> Confirm</a>';
        }else{
            return '-';
        }
    }

    function BtnPrint(val, row) {
        return '<a class="btn btn-primary w-100" target="_blank" href="<?= base_url('planning/purchase_orders/print_po/') ?>'+btoa(row.purchase_order_no)+'" style="pointer-events: visible; opacity:1;"><i class="fa fa-print"></i> Print</a>';
    }

    function BtnCreateSd(val, row) {
        // if(row.supplier_type == "IMPORT"){
            if (row.print_sd == 0) {
                var send = "createSd('"+row.purchase_order_no+"')";
                return '<a class="btn btn-success w-100" style="pointer-events: visible; opacity:1;" onClick="'+send+'"><i class="fa fa-plus"></i> Create</a>';
            } else {
                return '-';
            }
        // }else{
        //     return '-';
        // }
    }

    function BtnPrintSd(val, row) {
        // if(row.supplier_type == "IMPORT"){
            if(val > 0){
                return '<a class="btn btn-primary w-100" target="_blank" href="<?= base_url('planning/purchase_orders/print_delivery/') ?>'+btoa(row.purchase_order_no)+'" style="pointer-events: visible; opacity:1;"><i class="fa fa-print"></i> Print</a>';
            } else {
                return '-';
            }
        // }else{
        //     return '-';
        // }
    }

    function BtnPush(val, row) {
        if(row.push == "OK") {
            return 'DONE';
        // } else if ((row.approved_to == null || row.approved_to == "") && row.email_status == "SEND") {
        } else if (row.approved_to == null || row.approved_to == "") {
            var details = "details('"+row.purchase_order_no+"','"+row.supplier_name+"','"+row.product_family_name+"','push')";
            return '<a class="btn btn-danger w-100" style="pointer-events: visible; opacity:1;" onClick="'+details+'"><i class="fa fa-share"></i> Push</a>';
        } else {
            return '-';
        }
    }

    function BtnSend(val, row) {
        if(row.email_status == "SENT"){
            return "-";
        } else if (row.approved_to == null || row.approved_to == "") {
            var details = "details('"+row.purchase_order_no+"','"+row.supplier_name+"','"+row.product_family_name+"','mail','"+row.email_to+"','"+row.email_cc+"')";
            return '<a class="btn btn-success w-100" style="pointer-events: visible; opacity:1;" onClick="'+details+'"><i class="fa fa-send"></i> Send</a>';
        } else {
            return '-';
        }
    }
    
    function BtnSync(val, row) {
        if(parseFloat(row.price) == parseFloat(row.dprice) && row.currency == row.dcurrency){
            return "-";
        } else if(val == 0){
            return "-";
        }else{
            var details = "priceSync('"+row.id+"')";
            return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" onClick="'+details+'"><i class="fa fa-history"></i> Sync</a>';
        }
    }

    function cellStylerApproval(value, row, index) {
        if (row.approved_to == null) {
            return 'background: #FF5F5F; color:white;';
        } else if(row.approved_to == ""){
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    function cellFormatterApproval(val, row) {
        if (row.approved_to == null) {
            return 'Not Setting';
        } else if(row.approved_to == ""){
            return 'Approved';
        } else {
            return 'Checking';
        }
    };

    function cellStylerSend(value, row, index) {
        if (value == null) {
            return 'background: #FF5F5F; color:white;';
        } else {
            return 'background: #53D636; color:white;';
        }
    }

    function cellFormatterSend(value, row) {
        if (value == null) {
            return 'Not Send';
        } else {
            return 'Delivered';
        }
    };

    function cellStylerConfirm(value, row, index) {
        if (value == null) {
            return 'background: #FF5F5F; color:white;';
        } else if(value == "Pending"){
            return 'background: #AB6F2B; color:white;';
        }else{
            return 'background: #53D636; color:white;';
        }
    }

    function cellFormatterConfirm(value, row) {
        if (value == null) {
            return 'Not Receipt';
        } else if(value == "Pending"){
            return 'Pending';
        } else {
            return 'Confirm';
        }
    };

    function cellStylerPrice(value,row,index){
        if (parseInt(row.total_pr) == parseInt(row.total_po)){
            return 'background-color:#D1FFC6;';
        }else{
            return 'background-color:#FFC6C6;';
        }
    }
</script>