<div id="p" class="easyui-panel" title="syncuration" style="width:100%; padding:10px; background:#fafafa;" data-options="collapsible:true">
    <fieldset style="width:30%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
        <legend><b>Get Data Items Finish Good</b></legend>
        <div id="p_uploadItems" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
        <center><b id="p_startItems">0</b> Of <b id="p_finishItems">0</b></center>
        <br>
        <center>
            <a class="easyui-linkbutton" id="syncItems" onclick="syncItems()" data-options="iconCls:'icon-reload'">Sync Items</a>
        </center>
    </fieldset>
    <fieldset style="width:30%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
        <legend><b>Get Data Items Raw Material</b></legend>
        <div id="p_uploadItemsRw" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
        <center><b id="p_startItemsRw">0</b> Of <b id="p_finishItemsRw">0</b></center>
        <br>
        <center>
            <a class="easyui-linkbutton" id="syncItemsRw" onclick="syncItemsRw()" data-options="iconCls:'icon-reload'">Sync Items</a>
        </center>
    </fieldset>
    <fieldset style="width:30%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
        <legend><b>Get Data Lines</b></legend>
        <div id="p_uploadLines" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
        <center><b id="p_startLines">0</b> Of <b id="p_finishLines">0</b></center>
        <br>
        <center>
            <a class="easyui-linkbutton" id="syncLines" onclick="syncLines()" data-options="iconCls:'icon-reload'">Sync Lines</a>
        </center>
    </fieldset>
    <fieldset style="width:30%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
        <legend><b>Get Data Circuit No</b></legend>
        <div id="p_uploadCct" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
        <center><b id="p_startCct">0</b> Of <b id="p_finishCct">0</b></center>
        <br>
        <center>
            <a class="easyui-linkbutton" onclick="syncCct()" data-options="iconCls:'icon-reload'">Sync Circuit No</a>
        </center>
    </fieldset>
    <fieldset style="width:30%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
        <legend><b>Get Data Customer</b></legend>
        <div id="p_uploadCustomer" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
        <center><b id="p_startCustomer">0</b> Of <b id="p_finishCustomer">0</b></center>
        <br>
        <center>
            <a class="easyui-linkbutton" onclick="syncCustomer()" data-options="iconCls:'icon-reload'">Sync Customer</a>
        </center>
    </fieldset>
    <fieldset style="width:30%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
        <legend><b>Get Data Item Lines</b></legend>
        <div id="p_uploadItemLines" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
        <center><b id="p_startItemLines">0</b> Of <b id="p_finishItemLines">0</b></center>
        <br>
        <center>
            <a class="easyui-linkbutton" onclick="syncItemLines()" data-options="iconCls:'icon-reload'">Sync Item Lines</a>
        </center>
    </fieldset>
    <fieldset style="width:30%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
        <legend><b>Get Data Bill Of Material (BOM)</b></legend>
        <div id="p_uploadBom" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
        <center><b id="p_startBom">0</b> Of <b id="p_finishBom">0</b></center>
        <br>
        <center>
            <a class="easyui-linkbutton" onclick="syncBom()" data-options="iconCls:'icon-reload'">Sync BOM</a>
        </center>
    </fieldset>
    <fieldset style="width:30%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
        <legend><b>Get Data WIP MST WOS</b></legend>
        <div id="p_uploadWos" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
        <center><b id="p_startWos">0</b> Of <b id="p_finishWos">0</b></center>
        <br>
        <center>
            <a class="easyui-linkbutton" id="syncWos" onclick="syncWos()" data-options="iconCls:'icon-reload'">Sync WIP MST WOS</a>
        </center>
    </fieldset>
</div>

<script>
    function syncItems() {
        Swal.fire({
            title: 'Get Data from MRP Banshu',
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });

        $.ajax({
            type: "get",
            url: "<?= base_url('admin/sync/getItems') ?>",
            dataType: "json",
            success: function(rows) {
                Swal.close();
                $("#syncItems").linkbutton("disable");
                requestData(rows['total'], rows);

                function requestData(total, json, number = 1, value = 0) {
                    if (value < 100) {
                        value = Math.floor((number / total) * 100);
                        $('#p_uploadItems').progressbar('setValue', value);
                        $('#p_startItems').html(number);
                        $('#p_finishItems').html(total);

                        $.post('<?= base_url('admin/sync/createItems') ?>', {
                            data: json[number - 1]
                        }, function(note) {
                            var result = eval('(' + note + ')');
                            requestData(total, json, number + 1, value);
                            if (value == 100) {
                                Swal.fire('Good job!', 'Process Sync Data Completed!', 'success');
                                $("#syncItems").linkbutton("enable");
                            }
                        }).fail(function(jqXHR, textStatus) {
                            if (textStatus == "error") {
                                Swal.fire({
                                    title: 'Connection Time Out, Check Your Connection',
                                    showConfirmButton: false,
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    },
                                });

                                requestData(total, json, number, value);
                            }
                        });
                    }
                }
            }
        });
    }

    function syncItemsRw() {
        Swal.fire({
            title: 'Get Data from MRP Banshu',
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });

        $.ajax({
            type: "get",
            url: "<?= base_url('admin/sync/getItemsRw') ?>",
            dataType: "json",
            success: function(rows) {
                Swal.close();
                $("#syncItemsRw").linkbutton("disable");
                requestData(rows['total'], rows);

                function requestData(total, json, number = 1, value = 0) {
                    if (value < 100) {
                        value = Math.floor((number / total) * 100);
                        $('#p_uploadItemsRw').progressbar('setValue', value);
                        $('#p_startItemsRw').html(number);
                        $('#p_finishItemsRw').html(total);

                        $.post('<?= base_url('admin/sync/createItemsRw') ?>', {
                            data: json[number - 1]
                        }, function(note) {
                            var result = eval('(' + note + ')');
                            requestData(total, json, number + 1, value);
                            if (value == 100) {
                                Swal.fire('Good job!', 'Process Sync Data Completed!', 'success');
                                $("#syncItemsRw").linkbutton("enable");
                            }
                        }).fail(function(jqXHR, textStatus) {
                            if (textStatus == "error") {
                                Swal.fire({
                                    title: 'Connection Time Out, Check Your Connection',
                                    showConfirmButton: false,
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    },
                                });

                                requestData(total, json, number, value);
                            }
                        });
                    }
                }
            }
        });
    }

    function syncLines() {
        Swal.fire({
            title: 'Get Data from MRP Banshu',
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });

        $.ajax({
            type: "get",
            url: "<?= base_url('admin/sync/getLines') ?>",
            dataType: "json",
            success: function(rows) {
                Swal.close();
                $("#syncLines").linkbutton("disable");
                requestData(rows['total'], rows);

                function requestData(total, json, number = 1, value = 0) {
                    if (value < 100) {
                        value = Math.floor((number / total) * 100);
                        $('#p_uploadLines').progressbar('setValue', value);
                        $('#p_startLines').html(number);
                        $('#p_finishLines').html(total);

                        $.post('<?= base_url('admin/sync/createLines') ?>', {
                            data: json[number - 1]
                        }, function(note) {
                            var result = eval('(' + note + ')');
                            requestData(total, json, number + 1, value);
                            if (value == 100) {
                                Swal.fire('Good job!', 'Process Sync Data Completed!', 'success');
                                $("#syncLines").linkbutton("enable");
                            }
                        }).fail(function(jqXHR, textStatus) {
                            if (textStatus == "error") {
                                Swal.fire({
                                    title: 'Connection Time Out, Check Your Connection',
                                    showConfirmButton: false,
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    },
                                });

                                requestData(total, json, number, value);
                            }
                        });
                    }
                }
            }
        });
    }

    function syncCustomer() {
        Swal.fire({
            title: 'Get Data Customer from MRP Banshu',
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });

        $.ajax({
            type: "get",
            url: "<?= base_url('admin/sync/getCustomer') ?>",
            dataType: "json",
            success: function(rows) {
                Swal.close();
                $("#syncCustomer").linkbutton("disable");
                requestData(rows['total'], rows);

                function requestData(total, json, number = 1, value = 0) {
                    if (value < 100) {
                        value = Math.floor((number / total) * 100);
                        $('#p_uploadCustomer').progressbar('setValue', value);
                        $('#p_startCustomer').html(number);
                        $('#p_finishCustomer').html(total);

                        $.post('<?= base_url('admin/sync/createCustomer') ?>', {
                            data: json[number - 1]
                        }, function(note) {
                            var result = eval('(' + note + ')');
                            requestData(total, json, number + 1, value);
                            if (value == 100) {
                                Swal.fire('Good job!', 'Process Sync Data Completed!', 'success');
                                $("#syncCustomer").linkbutton("enable");
                            }
                        }).fail(function(jqXHR, textStatus) {
                            if (textStatus == "error") {
                                Swal.fire({
                                    title: 'Connection Time Out, Check Your Connection',
                                    showConfirmButton: false,
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    },
                                });

                                requestData(total, json, number, value);
                            }
                        });
                    }
                }
            }
        });
    }

    function syncCct() {
        Swal.fire({
            title: 'Get Data Circuit No from MRP Banshu',
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });

        $.ajax({
            type: "get",
            url: "<?= base_url('admin/sync/getCircuitNo') ?>",
            dataType: "json",
            success: function(rows) {
                Swal.close();
                $("#syncCct").linkbutton("disable");
                requestData(rows['total'], rows);

                function requestData(total, json, number = 1, value = 0) {
                    if (value < 100) {
                        value = Math.floor((number / total) * 100);
                        $('#p_uploadCct').progressbar('setValue', value);
                        $('#p_startCct').html(number);
                        $('#p_finishCct').html(total);

                        $.post('<?= base_url('admin/sync/createCct') ?>', {
                            data: json[number - 1]
                        }, function(note) {
                            var result = eval('(' + note + ')');
                            requestData(total, json, number + 1, value);
                            if (value == 100) {
                                Swal.fire('Good job!', 'Process Sync Data Completed!', 'success');
                                $("#syncCct").linkbutton("enable");
                            }
                        }).fail(function(jqXHR, textStatus) {
                            if (textStatus == "error") {
                                Swal.fire({
                                    title: 'Connection Time Out, Check Your Connection',
                                    showConfirmButton: false,
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    },
                                });

                                requestData(total, json, number, value);
                            }
                        });
                    }
                }
            }
        });
    }

    function syncItemLines() {
        Swal.fire({
            title: 'Get Data Item Lines from MRP Banshu',
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });

        $.ajax({
            type: "get",
            url: "<?= base_url('admin/sync/getItemLines') ?>",
            dataType: "json",
            success: function(rows) {
                Swal.close();
                $("#syncCct").linkbutton("disable");
                requestData(rows['total'], rows);

                function requestData(total, json, number = 1, value = 0) {
                    if (value < 100) {
                        value = Math.floor((number / total) * 100);
                        $('#p_uploadItemLines').progressbar('setValue', value);
                        $('#p_startItemLines').html(number);
                        $('#p_finishItemLines').html(total);

                        $.post('<?= base_url('admin/sync/createItemLines') ?>', {
                            data: json[number - 1]
                        }, function(note) {
                            var result = eval('(' + note + ')');
                            requestData(total, json, number + 1, value);
                            if (value == 100) {
                                Swal.fire('Good job!', 'Process Sync Data Completed!', 'success');
                                $("#syncCct").linkbutton("enable");
                            }
                        }).fail(function(jqXHR, textStatus) {
                            if (textStatus == "error") {
                                Swal.fire({
                                    title: 'Connection Time Out, Check Your Connection',
                                    showConfirmButton: false,
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    },
                                });

                                requestData(total, json, number, value);
                            }
                        });
                    }
                }
            }
        });
    }

    function syncBom() {
        Swal.fire({
            title: 'Get Data BOM from MRP Banshu',
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });

        $.ajax({
            type: "get",
            url: "<?= base_url('admin/sync/getBom') ?>",
            dataType: "json",
            success: function(rows) {
                Swal.close();
                $("#syncCct").linkbutton("disable");
                requestData(rows['total'], rows);

                function requestData(total, json, number = 1, value = 0) {
                    if (value < 100) {
                        value = Math.floor((number / total) * 100);
                        $('#p_uploadBom').progressbar('setValue', value);
                        $('#p_startBom').html(number);
                        $('#p_finishBom').html(total);

                        $.post('<?= base_url('admin/sync/createBom') ?>', {
                            data: json[number - 1]
                        }, function(note) {
                            var result = eval('(' + note + ')');
                            requestData(total, json, number + 1, value);
                            if (value == 100) {
                                Swal.fire('Good job!', 'Process Sync Data Completed!', 'success');
                                $("#syncCct").linkbutton("enable");
                                Swal.close();
                            }
                        }).fail(function(jqXHR, textStatus) {
                            if (textStatus == "error") {
                                // Swal.fire({
                                //     title: 'Connection Time Out, Check Your Connection',
                                //     showConfirmButton: false,
                                //     allowOutsideClick: false,
                                //     allowEscapeKey: false,
                                //     didOpen: () => {
                                //         Swal.showLoading();
                                //     },
                                // });

                                requestData(total, json, number, value);
                            }
                        });
                    }
                }
            }
        });
    }

    function syncWos() {
        Swal.fire({
            title: 'Get Data WIP MST WOS from MRP Banshu',
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });

        $.ajax({
            type: "get",
            url: "<?= base_url('admin/sync/getWos') ?>",
            dataType: "json",
            success: function(rows) {
                Swal.close();
                $("#syncWos").linkbutton("disable");
                requestData(rows['total'], rows);

                function requestData(total, json, number = 1, value = 0) {
                    if (value < 100) {
                        value = Math.floor((number / total) * 100);
                        $('#p_uploadWos').progressbar('setValue', value);
                        $('#p_startWos').html(number);
                        $('#p_finishWos').html(total);

                        $.post('<?= base_url('admin/sync/createWos') ?>', {
                            data: json[number - 1]
                        }, function(note) {
                            var result = eval('(' + note + ')');
                            requestData(total, json, number + 1, value);
                            if (value == 100) {
                                Swal.fire('Good job!', 'Process Sync Data Completed!', 'success');
                                $("#syncWos").linkbutton("enable");
                            }
                        }).fail(function(jqXHR, textStatus) {
                            if (textStatus == "error") {
                                Swal.fire({
                                    title: 'Connection Time Out, Check Your Connection',
                                    showConfirmButton: false,
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    },
                                });

                                requestData(total, json, number, value);
                            }
                        });
                    }
                }
            }
        });
    }
</script>