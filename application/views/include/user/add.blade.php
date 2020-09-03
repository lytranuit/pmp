<div class="row clearfix">
    <div class="col-12">
        <form method="POST" action="" id="form-dang-tin">
            <input type="hidden" name="dangtin" value="1" />
            <section class="card card-fluid">
                <h5 class="card-header">
                    <div class="d-inline-block w-100">
                        <button type="submit" class="btn btn-sm btn-primary float-right">{{lang("save")}}</button>
                    </div>
                </h5>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">{{lang("login_identity_label")}}:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input type="text" class="form-control" value="" name="username" required="">
                                </div>
                            </div>

                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">{{lang("login_name_label")}}:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input type="text" class="form-control" value="" name="last_name" minlength="3" required="">
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">{{lang("create_user_password_label")}}:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input type="password" class="form-control" name="newpassword" minlength="6" required="" aria-required="true">
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">{{lang("create_user_password_confirm_label")}}:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input type="password" class="form-control" name="confirmpassword" minlength="6" required="" aria-required="true">
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">{{lang("index_active_link")}}:</b>
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
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">{{lang("index_groups_th")}}:</b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <select name="groups[]" style="width: 200px;" multiple="">
                                        @foreach($groups as $row)
                                        <option value="{{$row['id']}}">{{$row['description']}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </section>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        $("select[name='groups[]']").chosen();
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
                $.ajax({
                    url: path + "user/checkregister",
                    data: $(form).serialize(),
                    dataType: "JSON",
                    success: function(data) {
                        if (data.success == 1) {
                            form.submit();
                            return false;
                        } else {
                            alert(data.msg);
                        }
                    }
                });

                return false;
            }
        });

    })
</script>