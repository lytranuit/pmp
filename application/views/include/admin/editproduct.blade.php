<?php
$hinh_preview = isset($tin->hinhanh->src) ? $tin->hinhanh->src : "public/admin/images/avatar-1.jpg";
?>


<div class="row clearfix">
    <div class="col-12">
        <form method="POST" action="" id="form-dang-tin">
            <input type="hidden" name="parent" value="0" />
            <section class="card card-fluid">
                <h5 class="card-header">
                    Sửa sản phẩm
                    <button type="submit" name="dangtin" class="btn btn-sm btn-primary float-right">Save</button>
                </h5>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Name:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input class="form-control" type='text' name="name" required="" />
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Price:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input class="form-control" type='text' name="price" required="" id="price"/>
                                </div>
                            </div>


                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Is Active:</b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <div class="switch-button switch-button-success">
                                        <input type="hidden" name="active" value="0" class="input-tmp">
                                        <input type="checkbox" checked="" name="active" id="switch19" value="1">
                                        <span>
                                            <label for="switch19"></label>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Open Table:</b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <div class="switch-button switch-button-success">
                                        <input type="hidden" name="is_open" value="0" class="input-tmp">
                                        <input type="checkbox" checked="" name="is_open" id="switch20" value="1">
                                        <span>
                                            <label for="switch20"></label>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Image:</b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <div id="hinh_preview">
                                        <img src="<?= base_url() . $hinh_preview; ?>" style="width: 200px;margin-bottom: 10px;"/>
                                    </div>
                                    <input type="file" id="kv-explorer" name="hinhanh[]"/>
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Danh mục:</b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <select class="form-control" name="category_id[]" id="category_id" multiple="">
                                        <?= $html_category ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Mô tả:</b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <textarea rows="4" class="form-control" name="description">
                                    
                                    </textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </form>
    </div>
</div>


<script type='text/javascript'>
    $(document).ready(function () {
        var tin = <?= json_encode($tin) ?>;
        fillForm($("#form-dang-tin"), tin);
        $("#category_id").chosen();
        $("#colors").chosen();
        $("#quanlytin").DataTable();
        $('#price').inputmask("numeric", {
            radixPoint: ".",
            groupSeparator: ",",
            autoGroup: true,
            suffix: ' VND', //No Space, this will truncate the first character
            rightAlign: false,
            oncleared: function () {
                self.Value('');
            }
        });
        $('#quantity').inputmask("numeric", {
            radixPoint: ".",
            groupSeparator: ",",
            autoGroup: true,
            rightAlign: false,
            oncleared: function () {
                self.Value('');
            }
        });
        $("#kv-explorer").fileinput({
            'theme': 'explorer-fa',
            'uploadUrl': path + 'admin/uploadhinhanh',
            'allowedFileExtensions': ['jpg', 'png', 'gif'],
            maxFileCount: 1,
            showPreview: false,
            showRemove: false,
            showUpload: false,
            showCancel: false,
            browseLabel: "",
        }).on("filebatchselected", function (event, files) {
            $("#form-dang-tin .id_hinhanh").remove();
            $(this).fileinput("upload");
        }).on('fileuploaded', function (event, data, previewId, index) {
            var id = data.response.key;
            var img = data.response.initialPreview[0];
            $("#hinh_preview").html(img);
            var append = "<input type='hidden' name='id_hinhanh' value='" + id + "' class='id_hinhanh'/>";
            $("#form-dang-tin").append(append);
        });
//        $("#kv-explorer").parents(".file-input").hide();
        $("#hinh_preview").click(function () {
            $("#kv-explorer").click();
        });
        $('.edit').froalaEditor({
            heightMin: 200,
            heightMax: 500, // Set the image upload URL.// Set custom buttons with separator between them.
            toolbarButtons: ['bold', 'italic', 'underline', 'strikeThrough', 'subscript', 'superscript', 'outdent', 'indent', 'clearFormatting', 'insertTable', 'html'],
            imageUploadURL: '<?= base_url() ?>admin/uploadimage',
            // Set request type.
            imageUploadMethod: 'POST',
            // Set max image size to 5MB.
            imageMaxSize: 5 * 1024 * 1024,
            // Allow to upload PNG and JPG.
            imageAllowedTypes: ['jpeg', 'jpg', 'png', 'gif'],
            htmlRemoveTags: [],
        });
        $.validator.setDefaults({
            debug: true,
            success: "valid"
        });
        $("#form-dang-tin").validate({
            highlight: function (input) {
                $(input).parents('.form-line').addClass('error');
            },
            unhighlight: function (input) {
                $(input).parents('.form-line').removeClass('error');
            },
            errorPlacement: function (error, element) {
                $(element).parents('.form-group').append(error);
            },
            submitHandler: function (form) {
                form.submit();
                return false;
            }
        });
        $(".select_all").click(function () {
            var parent = $(this).parents(".attr_product");
            $(".custom-control-input", parent).prop("checked", true);
        });
        $(".unselect_all").click(function () {
            var parent = $(this).parents(".attr_product");
            $(".custom-control-input", parent).prop("checked", false);
        });
        $(".create_product").click(function () {

            var $this = $(this);
            if ($this.data("running")) {
                return false;
            }
            var size = $(".attr_size .custom-control-input:checked").map(function () {
                return $(this).val();
            }).get();
            if (!size.length) {
                alert("Chọn size");
                return false;
            }
            var id_parent = <?= $tin->id ?>;
            $.ajax({
                type: "POST",
                data: {size: size, id_parent: id_parent},
                url: path + "admin/createmultiproduct",
                beforeSend: function () {
                    $this.data("running", true);
                },
                success: function () {
                    location.reload();
                }
            });
        });
    });
</script>