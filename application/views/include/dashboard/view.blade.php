<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header">
                Filter
            </div>
            <div class="card-body" id="form-dang-tin">

                <div class="row">
                    <div class="col-md-3">
                        <b class="col-form-label text-sm-right">Factory:<i class="text-danger">*</i></b>
                        <div class="pt-1">
                            <select class="form-control form-control-sm factory_id">
                                @foreach ($factory as $row)
                                <option value="{{$row->id}}">{{$row->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <b class="col-form-label text-sm-right">Workshop:<i class="text-danger">*</i></b>
                        <div class="pt-1">
                            <select class="form-control form-control-sm workshop_id">

                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <b class="col-form-label text-sm-right">Time:<i class="text-danger">*</i></b>
                        <div class="pt-1">
                            <div class="btn-group btn-group-toggle" data-toggle="buttons">

                                <label class="btn btn-light type_data">
                                    <input type="radio" name="options" id="option4" value="Custom"> Custom
                                </label>
                                <label class="btn btn-light type_data">
                                    <input type="radio" name="options" id="option4" value="Month"> Month
                                </label>

                                <label class="btn btn-light type_data">
                                    <input type="radio" name="options" id="option4" value="Quarter"> Quarter
                                </label>

                                <label class="btn btn-light type_data">
                                    <input type="radio" name="options" id="option4" value="HalfYear"> Half Year
                                </label>
                                <label class="btn btn-light type_data">
                                    <input type="radio" name="options" id="option6" value="TwoYear"> 2 Years
                                </label>
                                <label class="btn btn-light type_data active">
                                    <input type="radio" name="options" id="option5" value="Year"> Year
                                </label>
                            </div>
                            <select style="width: 200px;" class="form-control form-control-sm btn-group" id="the_selector">
                            </select>
                            <input type="text" id="daterange" class="form-control form-control-sm btn-group" style="width: 200px;" />

                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <b class="col-form-label text-sm-right">Area:</b>
                        <div class="pt-1">
                            <select class="form-control form-control-sm area_id">

                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <b class="col-form-label text-sm-right">
                            @if($object_id == 3)
                            Employee
                            @elseif($object_id == 10)
                            Equipment
                            @elseif($object_id == 11)
                            Department
                            @endif
                        </b>
                        <div class="pt-1">
                            <select class="form-control form-control-sm department_id">

                            </select>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                Trend Chart
                <div style="margin-left:auto">
                    <div class="btn-group">
                        <button class="btn btn-primary btn-sm" id="export_report"><i class="fas fa-print"></i></button>
                    </div>

                </div>
            </div>
            <div class="card-body" id="chart_template">
                <!-- <div class="chart-container" class="d-none">
                    <canvas id="myChart" height="80vh"></canvas>
                </div> -->
            </div>
        </div>
    </div>

    <script id="target_html" type="x-tmpl-mustache">
        <div class="card">
            <div id="target_<?= '{{id}}' ?>" class="card-header">
                <button type="button" data-toggle="collapse" data-target="#collapse<?= '{{id}}' ?>" aria-expanded="true" class="text-left m-0 p-0 btn btn-link btn-block">
                <?= '{{name}}' ?>
                </button>
            </div>
            <div data-parent="#chart_template" id="collapse<?= '{{id}}' ?>" aria-labelledby="target_<?= '{{id}}' ?>" class="collapse">
                <div class="card-body">
                    <div class="chart-container">
                        <div id="chart-<?= '{{id}}' ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </script>
    <script type="text/javascript">
        var date_from = moment();
        var date_to = moment();
        var date_from_prev, date_from_to;
        // var ctx = document.getElementById('myChart').getContext('2d');
        // var originalLineDraw = Chart.controllers.line.prototype.draw;
        // Chart.helpers.extend(Chart.controllers.line.prototype, {
        //     draw: function () {
        //         originalLineDraw.apply(this, arguments);

        //         var chart = this.chart;
        //         var ctx = chart.chart.ctx;

        //         var index = chart.config.data.lineAtIndex;
        //         if (index) {
        //             var xaxis = chart.scales['x-axis-0'];
        //             var yaxis = chart.scales['y-axis-0'];

        //             ctx.save();
        //             ctx.beginPath();
        //             ctx.moveTo(xaxis.getPixelForValue(undefined, index), yaxis.top);
        //             ctx.strokeStyle = 'gray';
        //             ctx.lineTo(xaxis.getPixelForValue(undefined, index), yaxis.bottom);
        //             ctx.stroke();
        //             ctx.restore();
        //         }
        //     }
        // });
        // var chart = new Chart(ctx, {
        //     type: 'line',
        //     data: [],
        //     options: {
        //         legend: {
        //             position: 'right'
        //         },
        //         elements: {
        //             line: {
        //                 tension: 0.0000001
        //             }
        //         },
        //         title: {
        //             display: true,
        //             text: 'Custom Chart Title'
        //         },
        //         scales: {
        //             yAxes: [{
        //                     ticks: {
        //                         suggestedMin: 0,
        //                     }
        //                 }]
        //         }
        //     }
        // });
        // var chart1 = new Highcharts.Chart({
        //     chart: {
        //         renderTo: 'chart-id'
        //     },
        //     title: {
        //         text: 'Solar Employment Growth by Sector, 2010-2016'
        //     },

        //     subtitle: {
        //         text: 'Source: thesolarfoundation.com'
        //     },
        //     xAxis: {
        //         categories: ["2010", "2011", "2012", "2013", "2014", "2015", "2016", "2017"]
        //     },

        //     legend: {
        //         layout: 'vertical',
        //         align: 'right',
        //         verticalAlign: 'middle'
        //     },
        //     series: [{
        //         name: 'Installation',
        //         data: [43934, 52503, 57177, 69658, 97031, 119931, 137133, 154175]
        //     }, {
        //         name: 'Manufacturing',
        //         data: [24916, 24064, 29742, 29851, 32490, 30282, 38121, 40434]
        //     }, {
        //         name: 'Sales & Distribution',
        //         data: [11744, 17722, 16005, 19771, 20185, 24377, 32147, 39387]
        //     }, {
        //         name: 'Project Development',
        //         data: [null, null, 7988, 12169, 15112, 22452, 34400, 34227]
        //     }, {
        //         name: 'Other',
        //         data: [12908, 5948, 8105, 11248, 8989, 11816, 18274, 18111]
        //     }],

        // });
        // var chart_svg = chart1.getSVG({
        //     exporting: {
        //         sourceHeight: 300,
        //         sourceWidth: 1000,
        //     }
        // });

        // canvg(document.getElementById('chart-canvas'), chart_svg)


        // var canvas = document.getElementById("chart-canvas");
        // var img = canvas.toDataURL("image/png");
        // console.log(img);
        $(document).ready(function() {
            $(".page-loader-wrapper").show();
            $(".department_id").change(async function() {
                drawChart();
            });
            $(".area_id").change(async function() {
                $(".page-loader-wrapper").show();
                let value = $(this).val();
                let department = await $.ajax({
                    url: path + "dashboard/getdepartment/" + value,
                    dataType: "JSON"
                });
                let html = "";
                $.each(department, function(k, item) {
                    html += "<option value='" + item.id + "'>" + item.name + "</option>";
                })
                $(".department_id").html(html);
                if ($(".department_id").length)
                    $(".department_id").trigger("change");
                else
                    $(".page-loader-wrapper").hide();
            });
            $(".workshop_id").change(async function() {
                $(".page-loader-wrapper").show();
                let value = $(this).val();
                let area = await $.ajax({
                    url: path + "dashboard/getarea/" + value,
                    dataType: "JSON"
                });
                let html = "";
                $.each(area, function(k, item) {
                    html += "<option value='" + item.id + "'>" + item.name + "</option>";
                })
                $(".area_id").html(html);
                if ($(".department_id").length)
                    $(".area_id").trigger("change");
                else
                    $(".page-loader-wrapper").hide();
            });
            $(".factory_id").change(async function() {
                $(".page-loader-wrapper").show();
                let value = $(this).val();
                let workshop = await $.ajax({
                    url: path + "dashboard/getworkshop/" + value,
                    dataType: "JSON"
                });
                let html = "";
                $.each(workshop, function(k, item) {
                    html += "<option value='" + item.id + "'>" + item.name + "</option>";
                })
                $(".workshop_id").html(html);
                if ($(".area_id").length)
                    $(".workshop_id").trigger("change");
                else
                    $(".page-loader-wrapper").hide();
                /////LOAD SELECTOR
                $(".type_data.active").trigger("click");
            });
            //DATE RANGE
            $("#export_report").click(function() {
                let html_loading = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
                $(this).prop("disabled", true).html(html_loading);

                // $(".collapse").addClass("show");

                var factory_id = $(".factory_id").val();
                var workshop_id = $(".workshop_id").val();
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
            }, function(start, end, label) {
                console.log('New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')');
            });
            ///EVENT
            $("#the_selector,#daterange").change(function() {
                drawChart();
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
            async function drawChart() {

                $(".page-loader-wrapper").show();
                var department_id = $(".department_id").val();
                if (!(department_id > 0)) {
                    return;
                }
                var area_id = $(".area_id").val();
                if (!(area_id > 0)) {
                    return;
                }
                // var target_id = $("[name=target_id]").val();
                let type = $(".type_data.active input").val();
                let selector = $("#the_selector").val();
                let daterange = $("#daterange").val();
                $("#chart_template").empty();
                var all_target = await $.ajax({
                    url: path + 'dashboard/chartdatav3',
                    data: {
                        department_id: department_id,
                        area_id: area_id,
                        // target_id: target_id,
                        type: type,
                        selector: selector,
                        daterange: daterange
                    },
                    dataType: "JSON"
                });
                for (target of all_target) {
                    let data = target['data'];
                    let target_html = $('#target_html').html();

                    let rendered = Mustache.render(target_html, target);
                    $("#chart_template").append(rendered);
                    let options = {
                        // title: {
                        //     text: 'Solar Employment Growth by Sector, 2010-2016'
                        // },

                        // subtitle: {
                        //     text: 'Source: thesolarfoundation.com'
                        // },
                        credits: {
                            enabled: false
                        },
                        legend: {
                            layout: 'vertical',
                            align: 'right',
                            verticalAlign: 'middle'
                        },
                        exporting: {
                            enabled: false
                        }
                    }
                    options = {
                        ...options,
                        ...data
                    };

                    $('#chart-' + target['id']).highcharts(options);
                }
                $(".page-loader-wrapper").hide();
            }


            ///////
            // $(".type_data.active").trigger("click");

            $(".factory_id").trigger("change");
        });
    </script>