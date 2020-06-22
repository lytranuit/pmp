<div class="row clearfix">
    <div class="col-12">
        <form method="POST" action="" id="form-dang-tin">
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
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Workshop:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <select class="form-control" name="workshop_id">
                                        @foreach ($workshop as $row)
                                        <option value="{{$row->id}}">{{$row->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @if($object_id > 17)
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">System Water:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <select class="form-control" name="system_id">
                                        @foreach ($system as $row)
                                        <option value="{{$row->id}}">{{$row->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @else

                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Area:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <select class="form-control" name="area_id">
                                        @foreach ($area as $row)
                                        <option value="{{$row->id}}">{{$row->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @endif
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Method:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <select class="form-control" name="target_id">
                                        <?= $html_nestable_target ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Effective date:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input class="form-control" type='date' name="day_effect" required="" />
                                </div>
                            </div>

                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Acceptance criteria:</b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1 number">
                                    <input class="form-control " type='number' name="standard_limit" />
                                </div>
                                <div class="col-12 col-sm-8 col-lg-3 pt-1 text">
                                    <input class="form-control" type='text' name="standard_limit_text" />
                                </div>
                                <div class="col-12 col-sm-8 col-lg-3 pt-1 text">
                                    <input class="form-control" type='text' placeholder="English Name" name="standard_limit_text_en" />
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Alert limit:</b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1 number">
                                    <input class="form-control" type='number' name="alert_limit" />
                                </div>
                                <div class="col-12 col-sm-8 col-lg-3 pt-1 text">
                                    <input class="form-control" type='text' name="alert_limit_text" />
                                </div>
                                <div class="col-12 col-sm-8 col-lg-3 pt-1 text">
                                    <input class="form-control" type='text' placeholder="English Name" name="alert_limit_text_en" />
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Action limit:</b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1 number">
                                    <input class="form-control " type='number' name="action_limit" />
                                </div>
                                <div class="col-12 col-sm-8 col-lg-3 pt-1 text">
                                    <input class="form-control" type='text' name="action_limit_text" />
                                </div>
                                <div class="col-12 col-sm-8 col-lg-3 pt-1 text">
                                    <input class="form-control" type='text' placeholder="English Name" name="action_limit_text_en" />
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
        $("[name=target_id]").change(function() {
            $(".number,.text").addClass("d-none");
            let val = $(this).val();
            let type = $("[name=target_id] option[value='" + val + "']").data("type");
            if (type == "float") {
                $(".number").removeClass("d-none");
            } else {
                $(".text").removeClass("d-none");
            }
        });
        $("[name=target_id]").trigger("change");
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