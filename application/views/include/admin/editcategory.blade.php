<?php
$hinh_preview = isset($tin->hinhanh->src) ? $tin->hinhanh->src : "public/admin/images/avatar-1.jpg";
?>

<div class="row clearfix">
    <div class="col-12">
        <form method="POST" action="" id="form-dang-tin">
            <input type="hidden" name="parent_id" value="0" />
            <section class="card card-fluid">
                <h5 class="card-header">
                    Sửa danh mục
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
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Include Home:</b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <div class="switch-button switch-button-success">
                                        <input type="hidden" name="is_home" value="0">
                                        <input type="checkbox" checked="" name="is_home" id="switch21" value="1">
                                        <span>
                                            <label for="switch21"></label>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Include Menu:</b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <div class="switch-button switch-button-success">
                                        <input type="hidden" name="is_menu" value="0" class="input-tmp">
                                        <input type="checkbox" checked="" name="is_menu" id="switch20" value="1">
                                        <span>
                                            <label for="switch20"></label>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Color:<i class="text-danger">*</i></b>
                                <div class="col-1 pt-1">
                                    <input class="" type='color' name="hex_color" required="" value=""/>
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Image:</b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input type="hidden" name='id_hinhanh' value='{{$tin->id_hinhanh}}' class='id_hinhanh'/>
                                    <div id="hinh_preview">
                                        <img src="<?= base_url() . $hinh_preview; ?>" style="width: 200px;margin-bottom: 10px;"/>
                                    </div>
                                    <input type="file" id="kv-explorer" name="hinhanh[]"/>
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Description:</b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <textarea class="edit" name="description">
                                    
                                    </textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </form>
    </div>
    <div class="col-12">
        <div class="section-block">
            <h3 class="section-title">Sản phẩm có trong danh mục</h3>
        </div>
        <section class="card card-fluid">
            <h5 class="card-header">
                <a href="{{base_url()}}admin/quanlyproduct" class="btn btn-sm btn-success">Thêm sản phẩm</a>
            </h5>
            <div class="card-body">
                <table id="quanlytin" class="table table-striped table-bordered table-hover" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Hình ảnh</th>
                            <th>Mã hàng</th>
                            <th>Sản phẩm</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<script type='text/javascript'>
    $(document).ready(function () {
        var tin = <?= json_encode($tin) ?>;
        fillForm($("#form-dang-tin"), tin);
        $('#quanlytin').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": path + "admin/tableproductbycategory",
                "dataType": "json",
                "type": "POST",
                "data": {category_id: tin['id']}
            },
            "columns": [
                {"data": "id"},
                {"data": "hinhanh"},
                {"data": "code"},
                {"data": "name"},
                {"data": "action"},
            ]

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
            heightMax: 500, // Set the image upload URL.
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
    });
</script>