<div id="p" class="easyui-panel" title="Configuration" style="width:100%; padding:10px; background:#fafafa;" data-options="collapsible:true">
    <form id="frm_insert" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:39%; height: 600px; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">ID</span>
                <input style="width:60%;" readonly name="number" value="<?= $config->number ?>" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Name</span>
                <input style="width:60%;" name="name" value="<?= $config->name ?>" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Line Business</span>
                <input style="width:60%;" name="description" value="<?= $config->description ?>" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Address</span>
                <input style="width:60%;" name="address" value="<?= $config->address ?>" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Logo</span>
                <input style="width:60%;" name="logo" class="easyui-filebox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Favicon</span>
                <input style="width:60%;" name="favicon" class="easyui-filebox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Login</span>
                <input style="width:60%;" name="image_login" class="easyui-filebox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Theme</span>
                <select style="width:60%;" name="theme" value="<?= $config->theme ?>" class="easyui-combobox">
                    <option value="default">Default</option>
                    <option value="black">Black</option>
                    <option value="bootstrap">Bootstrap</option>
                    <option value="gray">Gray</option>
                    <option value="material">Material</option>
                    <option value="material-blue">Material Blue</option>
                    <option value="material-teal">Material Teal</option>
                    <option value="metro">Metro</option>
                </select>
            </div>
            <br>
            <center>
                <a class="easyui-linkbutton c6" onclick="saved()" data-options="iconCls:'icon-save'">Save Changes</a>
            </center>
        </fieldset>
    </form>
</div>

<script>
    //Add Data
    function saved() {
        $('#frm_insert').form('submit', {
            url: '<?= base_url('admin/config/update') ?>',
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
</script>