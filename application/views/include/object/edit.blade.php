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
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">{{lang("method")}}:</b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <div class="dd" id="nestable2">
                                        <?= $html_nestable ?>
                                    </div>
                                    <select class="form-control mt-3" id="target_add">
                                        <option value="0">{{lang("add")}}</option>
                                        @foreach($targets as $target)
                                        <option value="{{$target['id']}}">
                                            {{pick_language($target,'name')}}
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
        $('#nestable').nestedSortable({
            forcePlaceholderSize: true,
            items: 'li',
            opacity: .6,
            maxLevels: 2,
            placeholder: 'dd-placeholder',
        });

        var tin = <?= json_encode($tin) ?>;
        fillForm($("#form-dang-tin"), tin);

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
                var arraied = $('#nestable').nestedSortable('toArray', {
                    excludeRoot: true
                });
                // console.log(arraied);
                // die();
                html = "";
                for (let i = 0; i < arraied.length; i++) {
                    html += "<input name='targets[]' type='hidden' value='" + arraied[i]['id'] + "' / > ";
                    html += "<input name='parents[]' type='hidden' value='" + arraied[i]['parent_id'] + "' / > ";
                }
                $(form).append(html);
                form.submit();
                return false;
            }
        });
        $("#target_add").change(function() {
            let target_id = $(this).val();
            if (target_id == 0)
                return;
            let object_id = tin['id'];
            $.ajax({
                url: path + "object/add_target",
                data: {
                    target_id: target_id,
                    object_id: object_id
                },
                dataType: "JSON",
                type: "POST",
                success: function(data) {
                    location.reload();
                }
            })
        })
        $(".dd-item-delete").click(async function() {
            var parent = $(this).closest(".dd-item");
            var id = parent.data("id");
            var array = [id];
            $(".dd-item", parent).each(function() {
                var id = $(this).data("id");
                array.push(id);
            });
            var r = confirm("Delete it?");
            if (r == true) {
                var promiseAll = [];
                for (var i = 0; i < array.length; i++) {
                    var id = array[i]
                    var promise = $.ajax({
                        type: "POST",
                        data: {
                            id: id,
                        },
                        url: path + "object/remove_target"
                    })
                    promiseAll.push(promise);
                }
                await Promise.all(promiseAll);
                location.reload();
            }
        })
    });
</script>