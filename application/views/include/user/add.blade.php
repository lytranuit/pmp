<div class="row clearfix">
    <div class="col-12">
        <form method="POST" action="" id="form-dang-tin">
            <input type="hidden" name="dangtin" value="1" />
            <section class="card card-fluid">
                <h5 class="card-header">
                    <div class="d-inline-block w-100">
                        <button type="submit" class="btn btn-sm btn-primary float-right">Save</button>
                    </div>
                </h5>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Username:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input type="text" class="form-control" value="" name="username" required="">
                                </div>
                            </div>

                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Tên:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input type="text" class="form-control" value="" name="last_name" minlength="3" required="">
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Mât khẩu:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input type="password" class="form-control" name="newpassword" minlength="6" required="" aria-required="true">
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Xác nhận mật khẩu:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input type="password" class="form-control" name="confirmpassword" minlength="6" required="" aria-required="true">
                                </div>
                            </div>

                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Email:</b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input type="text" class="form-control" value="" name="email">
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Địa chỉ:</b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input type="text" class="form-control" value="" name="address">
                                </div>
                            </div>

                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Số điện thoại:</b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input type="text" class="form-control" value="" name="phone">
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
                var username = $("input[name=username]").val();
                $.ajax({
                    url: path + "admin/checkusername",
                    data: {
                        username: username
                    },
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