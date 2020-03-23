
<div class="row clearfix">
    <div class="col-12">

        <form id="form_advanced_validation" method="POST" novalidate="novalidate">
            <section class="card card-fluid">
                <h5 class="card-header drag-handle">
                    Cài đặt chung
                    <button class="btn btn-primary btn-sm float-right" type="submit" name="settings">Cập nhật</button>
                </h5>
                <div class="card-body">
                    @foreach($tins as $tin)

                    <div class="form-group row">
                        <b class="col-12 col-sm-3 col-form-label text-sm-right">
                            {{$tin->title}}:
                            <p class="small text-muted">{{$tin->comment}}</p>
                        </b>
                        <div class="col-12 col-sm-8 col-lg-6 pt-1">
                            <input type='hidden' name="id[]" value="{{$tin->id}}"/>
                            @if($tin->type == 'varchar')
                            <input class="form-control" type='text' name="value[]" value="{{$tin->value}}"/>
                            @elseif($tin->type == 'text')

                            <textarea class="form-control" name="value[]">{{$tin->value}}</textarea>
                            @elseif($tin->type == 'bool')
                            <?php
                            $checked = "";
                            if ($tin->value != 0)
                                $checked = "checked";
                            ?>
                            <div class="switch-button switch-button-success">
                                <input type="checkbox" {{$checked}} name="value[]" id="switch{{$tin->id}}" value="1">
                                <span>
                                    <label for="switch{{$tin->id}}"></label>
                                </span>
                            </div>
                            @elseif($tin->type == 'page')

                            <textarea class="form-control edit" name="value[]">{{$tin->value}}</textarea>
                            @elseif($tin->type == 'number')

                            <input class="form-control" type='number' name="value[]" value='{{$tin->value}}' />
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="card-footer">
                    <button class="btn btn-primary btn-sm float-right" type="submit" name="settings">Cập nhật</button>
                </div>
            </section>
        </form>
    </div>
</div>

<!-- THAY DOI MAT KHAU Modal-->
<div aria-hidden="true" aria-labelledby="password-modalLabel" class="modal fade" id="password-modal" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="comment-modalLabel">
                    Thay đổi mật khẩu
                </h4>
            </div>
            <div class="modal-body">
                <div class="main">
                    <!--<p>Sign up once and watch any of our free demos.</p>-->
                    <form id="form-password">
                        <div class="form-group">
                            <b class="form-label">Mật khẩu cũ:</b>
                            <div class="form-line">
                                <input type="password" class="form-control" name="password" required="" aria-required="true">
                            </div>
                            <div class="help-info"></div>
                        </div>
                        <div class="form-group">
                            <b class="form-label">Mât khẩu mới</b>
                            <div class="form-line">
                                <input type="password" class="form-control" name="newpassword" minlength="6" required="" aria-required="true">
                            </div>
                        </div>
                        <div class="form-group">
                            <b class="form-label">Xác nhận mật khẩu mới</b>
                            <div class="form-line">
                                <input type="password" class="form-control" name="confirmpassword" minlength="6" required="" aria-required="true">
                            </div>
                        </div>
                        <button class="btn btn-primary waves-effect" type="submit" name="edit_password">Cập nhật</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
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
        $("button[name='edit_password']").click(function (e) {
            e.preventDefault();
            $.ajax({
                url: path + "admin/changepass",
                data: $("#form-password").serialize(),
                dataType: "JSON",
                type: "POST",
                success: function (data) {
                    alert(data.msg);
                    if (data.code == 400) {
                        location.reload();
                    }
                }
            });
            return false;
        });
    })
</script>