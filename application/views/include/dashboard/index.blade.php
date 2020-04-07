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
                </div>

            </div>
        </div>
        <div class="card">
            <div class="card-header">
                Biểu đồ xu hướng
                <div style="margin-left:auto">
                    <div class="btn-group">
                        <button class="btn btn-primary btn-sm" id="export_report"><i class="fas fa-print"></i></button>

                    </div>
                    <div class="btn-group btn-group-toggle" data-toggle="buttons">

                        <label class="btn btn-light type_data">
                            <input type="radio" name="options" id="option4" value="Custom"> Tùy chỉnh
                        </label>
                        <label class="btn btn-light type_data">
                            <input type="radio" name="options" id="option4" value="Month"> Tháng
                        </label>

                        <label class="btn btn-light type_data">
                            <input type="radio" name="options" id="option4" value="Quarter"> Quý
                        </label>

                        <label class="btn btn-light type_data">
                            <input type="radio" name="options" id="option4" value="HalfYear"> Nửa năm
                        </label>

                        <label class="btn btn-light type_data active">
                            <input type="radio" name="options" id="option5" value="Year"> Năm
                        </label>
                    </div>
                    <select style="width: 200px;" class="form-control form-control-sm btn-group" id="the_selector">
                    </select>
                    <input type="text" id="daterange" class="form-control form-control-sm btn-group" style="width: 200px;" />

                </div>
            </div>
            <div class="card-body">
                <div class="col-md-12">
                    <div id="accordion" class="accordion-wrapper mb-3">
                        <div class="card">
                            <div id="headingOne" class="card-header">
                                <button type="button" data-toggle="collapse" data-target="#collapseOne1" aria-expanded="true" aria-controls="collapseOne" class="text-left m-0 p-0 btn btn-link btn-block">
                                    <h5 class="m-0 p-0">Collapsible Group Item #1</h5>
                                </button>
                            </div>
                            <div data-parent="#accordion" id="collapseOne1" aria-labelledby="headingOne" class="collapse show">
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="myChart" height="80vh"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div id="headingTwo" class="b-radius-0 card-header">
                                <button type="button" data-toggle="collapse" data-target="#collapseOne2" aria-expanded="false" aria-controls="collapseTwo" class="text-left m-0 p-0 btn btn-link btn-block">
                                    <h5 class="m-0 p-0">Collapsible Group Item
                                        #2</h5>
                                </button>
                            </div>
                            <div data-parent="#accordion" id="collapseOne2" class="collapse">
                                <div class="card-body">2. Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa
                                    nesciunt
                                    laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt
                                    sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable
                                    VHS.
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div id="headingThree" class="card-header">
                                <button type="button" data-toggle="collapse" data-target="#collapseOne3" aria-expanded="false" aria-controls="collapseThree" class="text-left m-0 p-0 btn btn-link btn-block">
                                    <h5 class="m-0 p-0">Collapsible Group
                                        Item #3</h5>
                                </button>
                            </div>
                            <div data-parent="#accordion" id="collapseOne3" class="collapse">
                                <div class="card-body">3. Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa
                                    nesciunt
                                    laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt
                                    sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable
                                    VHS.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<!-- <a id="url" href="">download</a> -->
<script type="text/javascript">
    var date_from = moment();
    var date_to = moment();
    var date_from_prev, date_from_to;
    if (document.getElementById('myChart')) {
        var ctx = document.getElementById('myChart').getContext('2d');
        var originalLineDraw = Chart.controllers.line.prototype.draw;
        Chart.helpers.extend(Chart.controllers.line.prototype, {
            draw: function() {
                originalLineDraw.apply(this, arguments);

                var chart = this.chart;
                var ctx = chart.chart.ctx;

                var index = chart.config.data.lineAtIndex;
                if (index) {
                    var xaxis = chart.scales['x-axis-0'];
                    var yaxis = chart.scales['y-axis-0'];

                    ctx.save();
                    ctx.beginPath();
                    ctx.moveTo(xaxis.getPixelForValue(undefined, index), yaxis.top);
                    ctx.strokeStyle = 'gray';
                    ctx.lineTo(xaxis.getPixelForValue(undefined, index), yaxis.bottom);
                    ctx.stroke();
                    ctx.restore();
                }
            }
        });
        var chart = new Chart(ctx, {
            type: 'line',
            data: [],
            options: {
                legend: {
                    position: 'right'
                },
                elements: {
                    line: {
                        tension: 0.0000001
                    }
                },
                bezierCurve: false,
                animation: {
                    onComplete: done
                }
            }
        });
    }

    function done() {
        var url = chart.toBase64Image();
        $("#url").attr("href", url);
    }
    $(document).ready(function() {
        ////DATE RANGE
        $("#export_report").click(function() {
            var workshop_id = $("[name=workshop_id]").val();
            let type = $(".type_data.active input").val();
            let selector = $("#the_selector").val();
            let daterange = $("#daterange").val();
            let obj = {
                workshop_id: workshop_id,
                type: type,
                selector: selector,
                daterange: daterange
            }
            var str = "";
            for (var key in obj) {
                if (str != "") {
                    str += "&";
                }
                str += key + "=" + encodeURIComponent(obj[key]);
            }
            location.href = path + "dashboard/export?" + str;
        })
        $('#daterange').daterangepicker({
            "startDate": moment().startOf("Y"),
            "endDate": moment(),
            maxDate: moment()
        }, function(start, end, label) {
            console.log('New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')');
        });
        ///EVENT
        // $("[name=department_id],[name=target_id]").change(function() {
        //     drawChart();
        // });
        $("[name=workshop_id]").change(function() {
            get_all_data();
        });
        $("#the_selector,#daterange").change(function() {
            get_all_data();
        });
        $(".type_data").click(async function() {
            let value = $("input", this).val();
            $("#daterange").addClass("d-none");
            $("#the_selector").addClass("d-none");
            if (value == "Custom") {
                $("#daterange").removeClass("d-none");
            } else {

                $("#the_selector").removeClass("d-none");
                let data = await $.ajax({
                    url: path + 'dashboard/datedata',
                    data: {
                        type: value
                    },
                    dataType: "JSON"
                });
                let html = "";
                $.each(data, function(k, v) {
                    html += "<option value='" + v.value + "'>" + v.value + "</option>";
                })
                $("#the_selector").html(html);
                $("#the_selector").trigger("change");
            }
        });
        async function get_all_data() {
            var workshop_id = $("[name=workshop_id]").val();
            let type = $(".type_data.active input").val();
            let selector = $("#the_selector").val();
            let daterange = $("#daterange").val();
            var data = await $.ajax({
                url: path + 'dashboard/getalldatachart',
                data: {
                    workshop_id: workshop_id,
                    target_id: target_id,
                    type: type,
                    selector: selector,
                    daterange: daterange
                },
                dataType: "JSON"
            });
        }
        async function drawChart() {
            if (!document.getElementById('myChart'))
                return;
            var department_id = $("[name=department_id]").val();
            var target_id = $("[name=target_id]").val();
            let type = $(".type_data.active input").val();
            let selector = $("#the_selector").val();
            let daterange = $("#daterange").val();
            var data = await $.ajax({
                url: path + 'dashboard/chartdata',
                data: {
                    department_id: department_id,
                    target_id: target_id,
                    type: type,
                    selector: selector,
                    daterange: daterange
                },
                dataType: "JSON"
            });
            // data['lineAtIndex'] = 5
            chart.data = data;
            chart.update();
        }

        // function check_daterange() {
        //     // date_from = 
        //     let type = $(".type_data.active input").val();
        //     let selector = $("#the_selector").val();
        //     // if()

        // }
        ////
        $(".type_data.active").trigger("click");
    });
</script>