<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header">
                Bộ lọc

            </div>
            <div class="card-body" id="form-dang-tin">

                <div class="row">
                    <div class="col-md-3">
                        <b class="col-form-label text-sm-right">Nhà máy:<i class="text-danger">*</i></b>
                        <div class="pt-1">
                            <select class="form-control form-control-sm" name="factory_id">
                                @foreach ($factory as $row)
                                <option value="{{$row->id}}">{{$row->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <b class="col-form-label text-sm-right">Xưởng:<i class="text-danger">*</i></b>
                        <div class="pt-1">
                            <select class="form-control form-control-sm" name="workshop_id">
                                @foreach ($workshop as $row)
                                <option value="{{$row->id}}">{{$row->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <b class="col-form-label text-sm-right">Khu vực:<i class="text-danger">*</i></b>
                        <div class="pt-1">
                            <select class="form-control form-control-sm" name="area_id">
                                @foreach ($area as $area)
                                <option value="{{$area->id}}">{{$area->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <b class="col-form-label text-sm-right">Phòng ban:<i class="text-danger">*</i></b>
                        <div class="pt-1">
                            <select class="form-control form-control-sm" name="department_id">
                                @foreach ($department as $dep)
                                <option value="{{$dep->id}}">{{$dep->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <b class="col-form-label text-sm-right">Phương pháp:<i class="text-danger">*</i></b>
                        <div class="pt-1">
                            <select class="form-control form-control-sm" name="target_id">
                                @foreach ($target as $row)
                                <option value="{{$row->id}}">{{$row->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                Biểu đồ xu hướng
                <div class="btn-group btn-group-toggle" style="margin-left:auto" data-toggle="buttons">
                    <label class="btn btn-light">
                        <input type="radio" name="options" id="option3" value="Week"> Tuần
                    </label>
                    <label class="btn btn-light">
                        <input type="radio" name="options" id="option4" value="Month"> Tháng
                    </label>

                    <label class="btn btn-light">
                        <input type="radio" name="options" id="option4" value="Month"> Quý
                    </label>

                    <label class="btn btn-light">
                        <input type="radio" name="options" id="option4" value="Month"> Nửa năm
                    </label>

                    <label class="btn btn-light">
                        <input type="radio" name="options" id="option5" value="Year"> Năm
                    </label>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="myChart" height="80vh"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var ctx = document.getElementById('myChart').getContext('2d');
    var chart = new Chart(ctx, {
        type: 'line',
        data: []
    });
    $(document).ready(function() {
        $("[name=department_id],[name=target_id]").change(function() {
            drawChart();
        })
        async function drawChart() {
            var department_id = $("[name=department_id]").val();
            var target_id = $("[name=target_id]").val();
            var data = await $.ajax({
                url: path + 'dashboard/chartdata',
                data: {
                    department_id: department_id,
                    target_id: target_id
                },
                dataType: "JSON"
            });
            chart.data = data;
            chart.update();
        }
    });
</script>