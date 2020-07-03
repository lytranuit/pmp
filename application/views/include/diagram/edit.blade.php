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
                                    <input class="form-control" type='text' name="name_en"/>
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Positions:</b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <select class="form-control" name="positions[]" id="position" multiple>
                                        <option></option>
                                        @foreach ($positions as $row)
                                        <option value="{{$row->id}}">{{$row->string_id}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Image:</b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input type="file" class="input_image" accept="image/*" />
                                    <div class="mt-3">
                                        <img src="" class="image img-fluid" />
                                        <input name="image_id" type="hidden" />
                                    </div>
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
        if (tin.image) {
            $(".image").attr("src", path + tin.image.src);
        }
        $(".input_image").change(function() {
            let file = $(this)[0].files[0];
            let m_data = new FormData;
            m_data.append("file", file);
            $.ajax({
                url: path + "ajax/uploadimage",
                data: m_data,
                type: 'POST',
                dataType: "JSON",
                contentType: false, // NEEDED, DON'T OMIT THIS (requires jQuery 1.6+)
                processData: false, // NEEDED, DON'T OMIT THIS
            }).then(function(data) {
                $(".image").attr("src", path + data.src);
                $("[name=image_id]").val(data.id);
            });
        });
        $(".image").click(function() {
            $(".input_image").trigger("change");
        })
        $("#position").chosen();
        // $("select[multiple]").chosen();
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