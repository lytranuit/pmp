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
                        <div class="col-lg-6">
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Nhân viên:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <select class="form-control" name="employee_id" id="employee">
                                        <option></option>
                                        @foreach ($employee as $row)
                                        <option value="{{$row->id}}">{{$row->name}} - {{$row->string_id}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Nhà máy:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <select class="form-control" name="factory_id">
                                        @foreach ($factory as $row)
                                        <option value="{{$row->id}}">{{$row->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Xưởng:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <select class="form-control" name="workshop_id">
                                        @foreach ($workshop as $row)
                                        <option value="{{$row->id}}">{{$row->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Khu vực:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <select class="form-control" name="area_id">
                                        @foreach ($area as $area)
                                        <option value="{{$area->id}}">{{$area->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <table class="table text-center">
                                <thead>
                                    <tr>
                                        <th>Ngày lấy mẫu</th>
                                        <th>Đầu <br><i>Head:</i></th>
                                        <th>Mũi <br><i>Noise:</i></th>
                                        <th>Ngực <br><i>Chest:</i></th>
                                        <th>Cẳng tay trái <br><i>Left forearm:</i></th>
                                        <th>Cẳng tay phải <br><i>Right forearm:</i></th>
                                        <th>Dấu găng tay trái <br><i>Left glove print 5 fingers:</i></th>
                                        <th>Dấu găng tay phải <br><i>Right glove print 5 fingers:</i></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <input class="form-control" type='date' name="date" required="" value="{{date('Y-m-d')}}" />
                                        </td>
                                        <td>
                                            <input class="form-control" type='number' name="value_H" />
                                        </td>
                                        <td>
                                            <input class="form-control" type='number' name="value_N" />
                                        </td>
                                        <td>
                                            <input class="form-control" type='number' name="value_C" />
                                        </td>
                                        <td>
                                            <input class="form-control" type='number' name="value_LF" />
                                        </td>
                                        <td>
                                            <input class="form-control" type='number' name="value_RF" />
                                        </td>
                                        <td>
                                            <input class="form-control" type='number' name="value_LG" />
                                        </td>
                                        <td>
                                            <input class="form-control" type='number' name="value_RG" />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

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