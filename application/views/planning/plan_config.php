<div id="p" class="easyui-panel" title="Configuration" style="width:500px; padding:10px; background:#fafafa;" data-options="collapsible:true, maximizable:true">
    <form id="frm_insert" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Setting Generate MPS</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Finishgood SS</span>
                <input style="width:60%;" name="fg_ss" value="<?= $config->fg_ss ?>" class="easyui-textbox">
            </div>
        </fieldset>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Cutoff Days</b></legend>
            <ul>
                <li>If "YES" then default cutoff 01 - end of month</li>
                <li>If "NO" Please input cutoff days for next month</li>
            </ul>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Current Month</span>
                <?php
                if ($config->cutoff_current == "on") {
                    $on = "checked";
                    $off = "";
                } else {
                    $on = "";
                    $off = "checked";
                }
                ?>
                <input class="easyui-radiobutton" <?= $on ?> name="cutoff_current" id="cutoff_current" value="on"> &nbsp; YES &nbsp;
                <input class="easyui-radiobutton" <?= $off ?> name="cutoff_current" value="off"> &nbsp; NO &nbsp;
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Cutoff</span>
                <input style="width:25%;" name="cutoff_day_from" value="<?= $config->cutoff_day_from ?>" id="cutoff_day_from" class="easyui-textbox" data-options="prompt:'From'">
                <input style="width:25%;" name="cutoff_day_to" value="<?= $config->cutoff_day_to ?>" id="cutoff_day_to" class="easyui-textbox" data-options="prompt:'To'">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Cutoff WP</span>
                <input style="width:25%;" name="wp_day_from" value="<?= $config->wp_day_from ?>" id="wp_day_from" class="easyui-textbox" data-options="prompt:'From'">
                <input style="width:25%;" name="wp_day_to" value="<?= $config->wp_day_to ?>" id="wp_day_to" class="easyui-textbox" data-options="prompt:'To'">
            </div>
        </fieldset>
        <center>
            <a class="easyui-linkbutton c6" onclick="saved()" data-options="iconCls:'icon-save'">Save Changes</a>
        </center>
    </form>
</div>

<script>
    //Add Data
    function saved() {
        $('#frm_insert').form('submit', {
            url: '<?= base_url('planning/plan_config/update') ?>',
            method: 'POST',
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
            }
        });
    }

    $("#cutoff_current").radiobutton({
        onChange: function(row) {
            if (row == true) {
                $("#cutoff_day_from").textbox('disable');
                $("#cutoff_day_to").textbox('disable');
            } else {
                $("#cutoff_day_from").textbox('enable');
                $("#cutoff_day_to").textbox('enable');
            }
        }
    });
</script>