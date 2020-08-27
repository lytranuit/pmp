<div class="row clearfix">
    <div class="col-12">
        <form method="POST" action="" id="form-dang-tin">
            <input type="hidden" name="parent_id" value="0" />
            <section class="card card-fluid">
                <h5 class="card-header">
                    Position
                    <div class="d-inline-block w-100">
                        <button type="submit" name="dangtin" class="btn btn-sm btn-primary float-right">Save</button>
                    </div>
                </h5>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">

                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Id:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input class="form-control" type='text' name="string_id" required="" />
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Name:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input class="form-control" type='text' name="name" required="" />
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">English Name:</b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input class="form-control" type='text' name="name_en" />
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Object:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <select class="form-control" name="object_id">
                                        @foreach ($object as $row)
                                        <option value="{{$row->id}}">{{$row->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <h4 class="text-center">Location</h4>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Factory:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <select class="form-control" name="factory_id">
                                        @foreach ($factory as $row)
                                        <option value="{{$row->id}}">{{$row->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Department:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <select class="form-control" name="workshop_id">
                                        <option></option>
                                        @foreach ($workshop as $row)
                                        <option value="{{$row->id}}">{{$row->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row r-system d-none">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">System Water:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <select class="form-control" name="system_id">
                                        <option></option>
                                        @foreach ($system as $row)
                                        <option value="{{$row->id}}">{{$row->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row r-area">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Area:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <select class="form-control" name="area_id">
                                        <option></option>
                                        @foreach ($area as $area)
                                        <option value="{{$area->id}}">{{$area->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row r-room">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Room/Equipment:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <select class="form-control" name="department_id">
                                        <option></option>
                                        @foreach ($department as $dep)
                                        <option value="{{$dep->id}}">{{$dep->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <h4 class="text-center">Others</h4>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Report:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <select class="form-control" name="type_bc">
                                        <option value="Year">Year</option>
                                        <option value="HalfYear">Half Year</option>
                                        <option value="Quarter">Quarter</option>
                                        <option value="Month">Month</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Method:</b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <select class="form-control" name="target_id">
                                        <option></option>
                                        @foreach ($target as $row)
                                        <option value="{{$row->id}}">{{$row->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Frequency:</b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input class="form-control" type='text' name="frequency_name" />
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">English Frequency:</b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input class="form-control" type='text' name="frequency_name_en" />
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
    $(document).ready(function() {
        $("[name='object_id']").change(function() {
            let value = $(this).val();
            if (value > 17) {
                $(".r-area,.r-room").addClass("d-none");
                $(".r-system").removeClass("d-none");
            } else {
                $(".r-system").addClass("d-none");
                $(".r-area,.r-room").removeClass("d-none");
            }
        })
        var tin = <?= json_encode($tin) ?>;
        fillForm($("#form-dang-tin"), tin);
        $("[name='object_id']").change();
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
    });
</script>