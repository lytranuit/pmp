<div class="row clearfix">
    <div class="col-12">
        <form method="POST" action="" id="form-dang-tin">
            <input type="hidden" name="parent_id" value="0" />
            <section class="card card-fluid">
                <h5 class="card-header">
                    {{lang("position")}}
                    <div class="ml-auto">
                        <button type="submit" name="dangtin" class="btn btn-sm btn-primary float-right">{{lang("save")}}</button>
                    </div>
                </h5>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">

                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">{{lang("code")}}:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input class="form-control" type='text' name="string_id" required="" />
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">{{lang("login_name_label")}}:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input class="form-control" type='text' name="name" required="" />
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">{{lang("login_name_en_label")}}:</b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input class="form-control" type='text' name="name_en" />
                                </div>
                            </div>

                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">{{lang("object")}}:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <select class="form-control" name="object_id">
                                        @foreach ($object as $row)
                                        <option value="{{$row->id}}">{{$row->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>


                            <div class="form-group row r-system d-none">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">{{lang("system_water")}}:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <select class="form-control" name="system_id">
                                        <option></option>
                                        @foreach ($system as $row)
                                        <option value="{{$row->id}}">{{$row->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <h4 class="text-center">{{lang("location")}}</h4>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">{{lang("factory")}}:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <select class="form-control" name="factory_id">
                                        @foreach ($factory as $row)
                                        <option value="{{$row->id}}">{{$row->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">{{lang("department")}}:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <select class="form-control" name="workshop_id">
                                        <option></option>
                                        @foreach ($workshop as $row)
                                        <option value="{{$row->id}}">{{$row->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row r-area">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">{{lang("area")}}:<i class="text-danger">*</i></b>
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
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">{{lang("room")}}/{{lang("equipment")}}:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <select class="form-control" name="department_id">
                                        <option></option>
                                        @foreach ($department as $dep)
                                        <option value="{{$dep->id}}">{{$dep->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <h4 class="text-center">{{lang("others")}}</h4>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">{{lang("report")}}:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <select class="form-control" name="type_bc">
                                        <option value="Year">{{lang("year")}}</option>
                                        <option value="HalfYear">{{lang("half_year")}}</option>
                                        <option value="Quarter">{{lang("quarter")}}</option>
                                        <option value="Month">{{lang("month")}}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">{{lang("method")}}:</b>
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
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">{{lang("frequency")}}:</b>
                                <div class="col-12 col-sm-4 col-lg-3 pt-1">
                                    <input class="form-control" type='text' name="frequency_name" />
                                </div>
                                <div class="col-12 col-sm-4 col-lg-3 pt-1">
                                    <input class="form-control" type='text' name="frequency_name_en" placeholder="{{lang('login_name_en_label')}}" />
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