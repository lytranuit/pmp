<div class="row clearfix">
    <div class="col-12">
        <form method="POST" action="" id="form-dang-tin">
            <input type="hidden" name="parent_id" value="0" />
            <section class="card card-fluid">
                <h5 class="card-header">
                    <div class="d-inline-block w-100">
                        <button type="submit" name="dangtin" class="btn btn-sm btn-primary float-right">Save</button>
                    </div>
                </h5>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">

                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Mã vị trí:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <select class="form-control" name="position_id" id="position">
                                        <option></option>
                                        @foreach ($positions as $row)
                                        <option value="{{$row->id}}">{{$row->string_id}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Tên vị trí:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input class="form-control" type='text' name="position_name" required="" readonly="" />
                                </div>
                            </div>

                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Phòng:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input class="form-control" type='text' name="department_name" required="" readonly="" />
                                    <input class="form-control" type='hidden' name="department_id" required="" readonly="" />
                                    <input class="form-control" type='hidden' name="area_id" required="" readonly="" />
                                    <input class="form-control" type='hidden' name="workshop_id" required="" readonly="" />
                                    <input class="form-control" type='hidden' name="factory_id" required="" readonly="" />
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Tần suất lấy mẫu:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input class="form-control" type='text' name="frequency_name" required="" readonly="" />
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Phương pháp lấy mẫu:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input class="form-control" type='hidden' name="target_id" required="" readonly="" />
                                    <input class="form-control" type='text' name="target_name" required="" readonly="" />
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Ngày lấy mẫu:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input class="form-control" type='date' name="date" required="" value="{{date('Y-m-d')}}" />
                                </div>
                            </div>

                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Giá trị:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input class="form-control" type='number' name="value" required="" />
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
        $("#position").chosen();
        $("#position").change(function () {
            let value = $(this).val();
            if (!(value > 0)) {
                return false;
            }
            $.ajax({
                url: '{{base_url()}}position/get/' + value,
                dataType: "JSON",
                success: function (data) {
                    let {
                        name,
                        department,
                        target,
                        department_id,
                        target_id,
                        frequency_name,
                        factory_id,
                        workshop_id
                    } = data
                    let department_name = department.name;
                    let target_name = target.name;
                    let area_id = department.area_id;
                    $("input[name='frequency_name']").val(frequency_name);
                    $("input[name='position_name']").val(name);
                    $("input[name='area_id']").val(area_id);
                    $("input[name='factory_id']").val(factory_id);
                    $("input[name='workshop_id']").val(workshop_id);
                    $("input[name='department_id']").val(department_id);
                    $("input[name='department_name']").val(department_name);
                    $("input[name='target_id']").val(target_id);
                    $("input[name='target_name']").val(target_name);
                }
            })
        })
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