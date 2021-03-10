<div class="row clearfix">
    <div class="col-12">
        <form method="POST" action="" id="form-dang-tin" enctype="multipart/form-data">
            <section class="card card-fluid">
                <h5 class="card-header">
                    @if($object_id == "3")
                    <a href="{{base_url()}}public/upload/template_input/mau nhan vien.xlsx" class="btn btn-primary"
                        style="width:200px;">{{lang("download_template")}}</a>
                    @elseif($object_id == 10 || $object_id == 11)
                    <a href="{{base_url()}}public/upload/template_input/mau vi sinh.xlsx" class="btn btn-primary"
                        style="width:200px;">{{lang("download_template")}}</a>
                    @elseif($object_id == 14 || $object_id == 15)
                    <a href="{{base_url()}}public/upload/template_input/mau tieu phan.xlsx" class="btn btn-primary"
                        style="width:200px;">{{lang("download_template")}}</a>
                    @endif
                    <div class="d-inline-block w-100">
                        <button type="submit" name="dangtin"
                            class="btn btn-sm btn-primary float-right">{{lang("save")}}</button>
                    </div>
                </h5>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">{{lang("file")}}:</b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input type="file" name="files[]" multiple />
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">{{lang("comment")}}:</b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <textarea name="note" class="form-control"></textarea>
                                </div>
                            </div>
                            @if($object_id == 3)
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">{{lang("factory")}}:<i
                                        class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <select class="form-control" name="factory_id">
                                        @foreach ($factory as $row)
                                        <option value="{{$row->id}}">{{pick_language($row,'name')}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">{{lang("department")}}:<i
                                        class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <select class="form-control" name="workshop_id" require>
                                        @foreach ($workshop as $row)
                                        <option value="{{$row->id}}">{{pick_language($row,'name')}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">{{lang("area")}}:<i
                                        class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <select class="form-control" name="area_id" require>
                                        @foreach ($area as $area)
                                        <option value="{{$area->id}}">{{pick_language($area,'name')}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </section>
        </form>
    </div>
</div>
<script type='text/javascript'>
    $(document).ready(function() {
        $("[name=factory_id]").change();
        $.validator.setDefaults({
            debug: true,
            success: "valid",
            ignore: '[readonly=readonly]'
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