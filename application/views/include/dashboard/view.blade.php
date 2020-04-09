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
                        <b class="col-form-label text-sm-right">Phòng/Thiết bị/Nhân viên:<i class="text-danger">*</i></b>
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
                <div style="margin-left:auto">

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
                <div class="chart-container" class="d-none">
                    <canvas id="myChart" height="80vh"></canvas>
                </div>
                <div class="chart-container">
                    <div id="chart-id">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        var date_from = moment();
        var date_to = moment();
        var date_from_prev, date_from_to;
        var ctx = document.getElementById('myChart').getContext('2d');
        var originalLineDraw = Chart.controllers.line.prototype.draw;
        Chart.helpers.extend(Chart.controllers.line.prototype, {
            draw: function () {
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
                labels: {
                    generateLabels: function () {

                    }
                },
                legendCallback: function (chart) {
                    console.log(chart);
                    var text = [];
                    text.push('<ul class="' + chart.id + '-legend">');
                    for (var i = 0; i < chart.data.datasets.length; i++) {
                        text.push('<li><span style="background-color:' +
                                chart.data.datasets[i].backgroundColor +
                                '"></span>');
                        if (chart.data.datasets[i].label) {
                            text.push(chart.data.datasets[i].label);
                        }
                        text.push('</li>');
                    }
                    text.push('</ul>');
                    return text.join('');
                },
                legend: {
                    position: 'right'
                },
                elements: {
                    line: {
                        tension: 0.0000001
                    }
                },
                title: {
                    display: true,
                    text: 'Custom Chart Title'
                },
                scales: {
                    yAxes: [{
                            ticks: {
                                suggestedMin: 0,
                            }
                        }]
                }
            }
        });
        $(document).ready(function () {
            $('#chart-id').highcharts({
                title: {
                    text: 'Monthly Average Temperature',
                    x: -20 //center
                },
                subtitle: {
                    text: 'Source: WorldClimate.com',
                    x: -20
                },
                xAxis: {
                    categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                        'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
                },
//                yAxis: {
//                    plotLines: [{
//                            value: 0,
//                            width: 1,
//                            color: '#808080'
//                        }]
//                },
                legend: {
                    layout: 'vertical',
                    align: 'right',
                    verticalAlign: 'middle',
                    borderWidth: 0
                },
                series: [{
                        name: 'Tokyo',
                        data: [7.0, 6.9, 9.5, 14.5, 18.2, 21.5, 25.2, 26.5, 23.3, 18.3, 13.9, 9.6]
                    }, {
                        name: 'New York',
                        data: [-0.2, 0.8, 5.7, 11.3, 17.0, 22.0, 24.8, 24.1, 20.1, 14.1, 8.6, 2.5]
                    }, {
                        name: 'Berlin',
                        data: [-0.9, 0.6, 3.5, 8.4, 13.5, 17.0, 18.6, 17.9, 14.3, 9.0, 3.9, 1.0]
                    }, {
                        name: 'London',
                        data: [3.9, 4.2, 5.7, 8.5, 11.9, 15.2, 17.0, 16.6, 14.2, 10.3, 6.6, 4.8]
                    }]
            });
            ////DATE RANGE
            $("#export_report").click(function () {
                let html_loading = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
                $(this).prop("disabled", true).html(html_loading);

                // $(".collapse").addClass("show");

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
                location.href = path + "dashboard/savechart?" + str;
            })
            $('#daterange').daterangepicker({
                "startDate": moment().startOf("Y"),
                "endDate": moment(),
                maxDate: moment()
            }, function (start, end, label) {
                console.log('New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')');
            });
            ///EVENT
            $("[name=department_id],[name=target_id]").change(function () {
                drawChart();
            })
            $("#the_selector,#daterange").change(function () {
                drawChart();
            });
            $(".type_data").click(async function () {
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
                    $.each(data, function (k, v) {
                        html += "<option value='" + v.value + "'>" + v.value + "</option>";
                    })
                    $("#the_selector").html(html);
                    $("#the_selector").trigger("change");
                }
            });
            async function drawChart() {
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
                chart.generateLegend();
            }


            $(".type_data.active").trigger("click");
        });
    </script>