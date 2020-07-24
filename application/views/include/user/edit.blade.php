<div class="row clearfix">
    <div class="col-12">
        <form method="POST" action="" id="form-dang-tin">
            <input type="hidden" name="parent" value="0" />
            <section class="card card-fluid">
                <h5 class="card-header">
                    <div class="d-inline-block w-100">
                        <button type="submit" name="dangtin" class="btn btn-sm btn-primary float-right" value="1">Save</button>
                    </div>
                </h5>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Identity:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input type="text" class="form-control" value="" name="username" required="" readonly="">
                                </div>
                            </div>

                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Name:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input type="text" class="form-control" value="" name="last_name" minlength="3" required="">
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
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Groups:</b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <select name="groups[]" style="width: 200px;" multiple="">
                                        @foreach($groups as $row)
                                        <option value="{{$row['id']}}">{{$row['name']}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                        </div>

                        <div class="col-md-12" style="margin-bottom: 20px;">
                            <a href="#" class='btn btn-primary' data-target="#password-modal" data-toggle="modal">
                                <span>Change password</span>
                            </a>
                        </div>
                    </div>
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
                    Change Password
                </h4>
            </div>
            <div class="modal-body">
                <div class="main">
                    <!--<p>Sign up once and watch any of our free demos.</p>-->
                    <form id="form-password">
                        <input type="hidden" name="id_user" value="{{$tin->id}}" />
                        <div class="form-group">
                            <b class="form-label">New password</b>
                            <div class="form-line">
                                <input type="password" class="form-control" name="newpassword" minlength="6" required="" aria-required="true">
                            </div>
                        </div>
                        <div class="form-group">
                            <b class="form-label">Confirm password</b>
                            <div class="form-line">
                                <input type="password" class="form-control" name="confirmpassword" minlength="6" required="" aria-required="true">
                            </div>
                        </div>
                        <button class="btn btn-primary waves-effect" type="submit" name="edit_password">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        var tin = <?= json_encode($tin) ?>;
        fillForm($("#form-dang-tin"), tin);
        $("select[name='groups[]']").chosen();
        $("button[name='edit_password']").click(function(e) {
            e.preventDefault();
            $.ajax({
                url: path + "admin/changepasswithout",
                data: $("#form-password").serialize(),
                dataType: "JSON",
                type: "POST",
                success: function(data) {
                    alert(data.msg);
                    if (data.code == 400) {
                        location.reload();
                    }
                }
            });
            return false;
        });
        $.validator.setDefaults({
            debug: true,
            success: "valid"
        });
        $("#form-dang-tin").validate({
            highlight: function(input) {
                $(input).parents('.form-line').addClass('error');
            },
            unhighlight: function(input) {
                $(input).parents('.form-line').removeClass('error');
            },
            errorPlacement: function(error, element) {
                $(element).parents('.form-group').append(error);
            },
            submitHandler: function(form) {
                form.submit();
                return false;
            }
        });
    })
</script>