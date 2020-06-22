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
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Position:<i class="text-danger">*</i></b>
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
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Position Name:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input class="form-control" type='text' name="position_name" required="" readonly="" />
                                </div>
                            </div>



                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Factory:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input class="form-control" type='text' name="factory_name" required="" readonly="" />
                                </div>
                            </div>

                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Department:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input class="form-control" type='text' name="workshop_name" required="" readonly="" />
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">System Water:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input class="form-control" type='text' name="system_name" required="" readonly="" />
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">
                                    Room
                                    <i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input class="form-control" type='text' name="department_name" required="" readonly="" />
                                    <input class="form-control" type='hidden' name="department_id" required="" readonly="" />
                                    <input class="form-control" type='hidden' name="system_id" required="" readonly="" />
                                    <input class="form-control" type='hidden' name="workshop_id" required="" readonly="" />
                                    <input class="form-control" type='hidden' name="factory_id" required="" readonly="" />
                                    <input class="form-control" type='hidden' name="object_id" required="" readonly="" />
                                    <input class="form-control" type='hidden' name="type_bc" required="" readonly="" />
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Frequency:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input class="form-control" type='text' name="frequency_name" required="" readonly="" />
                                </div>
                            </div>

                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Method:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <select class="form-control" name="target_id" id="target_id">
                                        <?= $html_nestable_target ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Date:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input class="form-control" type='date' name="date" required="" value="{{date('Y-m-d')}}" />
                                </div>
                            </div>

                            <div class="form-group row" id="html_value">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Value:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input class="form-control" type='number' name="value" />
                                </div>
                            </div>
                            <div class="form-group row d-none" id="html_value_text">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Value:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input class="form-control" type='text' name="value_text" />
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Comment:</b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <textarea name="note" class="form-control"></textarea>
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

        var tin = <?= json_encode($tin) ?>;
        fillForm($("#form-dang-tin"), tin);
        $("#position").chosen();
        $("#position").change(function() {
            let value = $(this).val();
            if (!(value > 0)) {
                return false;
            }
            $.ajax({
                url: '{{base_url()}}position/get/' + value,
                dataType: "JSON",
                success: function(data) {
                    let {
                        name,
                        factory,
                        workshop,
                        system,
                        department,
                        department_id,
                        frequency_name,
                        factory_id,
                        workshop_id,
                        object_id,
                        system_id,
                        type_bc
                    } = data
                    let factory_name = factory.name;
                    let workshop_name = workshop.name;
                    let system_name = system.name;
                    let department_name = department ? department.name : "";
                    $("input[name='frequency_name']").val(frequency_name);
                    $("input[name='position_name']").val(name);
                    $("input[name='object_id']").val(object_id);
                    $("input[name='system_id']").val(system_id);
                    $("input[name='factory_id']").val(factory_id);
                    $("input[name='workshop_id']").val(workshop_id);
                    $("input[name='department_id']").val(department_id);
                    $("input[name='factory_name']").val(factory_name);
                    $("input[name='workshop_name']").val(workshop_name);
                    $("input[name='system_name']").val(system_name);
                    $("input[name='department_name']").val(department_name);
                    $("input[name='type_bc']").val(type_bc);
                }
            })
        })
        $("#position").change()
        $("#target_id").change(function() {
            let target_id = $(this).val();
            let type = $("#target_id option[value=" + target_id + "]").data("type");
            $("#html_value,#html_value_text").addClass("d-none");
            if (type == "float" || type == "boolean") {
                $("#html_value").removeClass("d-none");
            } else {
                $("#html_value_text").removeClass("d-none");
            }
        })
        $("#target_id").change();
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