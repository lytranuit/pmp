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
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Name:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input class="form-control" type='text' name="name" required="" />
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">English Name:</b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input class="form-control" type='text' name="name_en" required="" />
                                </div>
                            </div>
                            <!--                            <div class="form-group row">
                                                            <b class="col-12 col-sm-3 col-form-label text-sm-right">Khu vực:</b>
                                                            <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                                                <select name="areas[]"  style="width: 500px;" multiple="">
                                                                    @foreach($areas as $area)
                                                                    <option value="{{$area->id}}">{{$area->name}}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div> 
                            -->
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Method:</b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <select name="targets[]" style="width: 500px;" multiple="">
                                        @foreach($targets as $target)
                                        <option value="{{$target->id}}">
                                            {{$target->name}}
                                            @if($target->parent_id > 0)
                                            ({{$target->parent->name}})
                                            @endif
                                        </option>
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
<script type='text/javascript'>
    $(document).ready(function() {

        var tin = <?= json_encode($tin) ?>;
        fillForm($("#form-dang-tin"), tin);

        $("select[multiple]").chosen();
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