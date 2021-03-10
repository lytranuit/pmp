<div class="row clearfix">
    <div class="col-12">
        <form method="POST" action="" id="form-dang-tin" enctype="multipart/form-data">
            <section class="card card-fluid">
                <h5 class="card-header">
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

                        </div>
                    </div>
                </div>
            </section>
        </form>
    </div>
</div>
<script type='text/javascript'>
    $(document).ready(function() {
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