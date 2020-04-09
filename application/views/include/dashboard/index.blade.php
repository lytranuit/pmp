<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header">
                Bộ lọc

            </div>
            <div class="card-body" id="form-dang-tin">

                <div class="row">
                    <div class="col-md-3">
                        <b class="col-form-label text-sm-right">Nhà máy:</b>
                        <div class="pt-1">
                            <select class="form-control form-control-sm" name="factory_id">
                                @foreach ($factory as $row)
                                <option value="{{$row->id}}">{{$row->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <b class="col-form-label text-sm-right">Xưởng:</b>
                        <div class="pt-1">
                            <select class="form-control form-control-sm" name="workshop_id">
                                @foreach ($workshop as $row)
                                <option value="{{$row->id}}">{{$row->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <b class="col-form-label text-sm-right">Thời gian:</b>
                        <div class="pt-1">
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
                </div>
            </div>
            <div class="card-body">
                <div class="col-md-12">
                    <div id="target_accordion">

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<script id="target_template" type="x-tmpl-mustache">
    <div class="card">
    <div id="target_<?= '{{id}}' ?>" class="card-header">
    <button type="button" data-toggle="collapse" data-target="#collapse<?= '{{id}}' ?>" aria-expanded="true" class="text-left m-0 p-0 btn btn-link btn-block">
    <?= '{{name}}' ?>
    </button>
    </div>
    <div data-parent="#target_accordion" id="collapse<?= '{{id}}' ?>" aria-labelledby="target_<?= '{{id}}' ?>" class="collapse">
    <div class="card-body">
    <div id="area_<?= '{{id}}' ?>_accordion">

    </div>
    </div>
    </div>
    </div>
</script>
<script id="area_template" type="x-tmpl-mustache">
    <div class="card">
    <div id="area_<?= '{{id}}{{target_id}}' ?>" class="card-header">
    <button type="button" data-toggle="collapse" data-target="#collapse<?= '{{id}}{{target_id}}' ?>" aria-expanded="true" class="text-left m-0 p-0 btn btn-link btn-block">
    <?= '{{name}}' ?>
    </button>
    </div>
    <div data-parent="#area_<?= '{{target_id}}' ?>_accordion" id="collapse<?= '{{id}}{{target_id}}' ?>" aria-labelledby="area_<?= '{{id}}{{target_id}}' ?>" class="collapse">
    <div class="card-body" id="area_<?= '{{id}}{{target_id}}' ?>_body">

    </div>
    </div>
    </div>
</script>

<script id="department_template" type="x-tmpl-mustache">
    <div>
    <div class='chart-container'>
    <div id="myChart<?= '{{id}}{{target_id}}' ?>" class='myChart'></div>
    <input id="value_<?= '{{id}}{{target_id}}' ?>" type="hidden" data-target_id='<?= '{{target_id}}' ?>' data-department_id='<?= '{{id}}' ?>' />
    </div>
    </div>
</script>
<!-- <a id="url" href="">download</a> -->
<script type="text/javascript">
    var date_from = moment();
    var date_to = moment();
    var date_from_prev, date_from_to;
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

    function done() {
        var url = chart.toBase64Image();
        $("#url").attr("href", url);
    }
    $(document).ready(function() {
        $(".page-loader-wrapper").show();
        ////DATE RANGE
        $("#export_report").click(function() {
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

            $(".page-loader-wrapper").show();
            var workshop_id = $("[name=workshop_id]").val();
            let type = $(".type_data.active input").val();
            let selector = $("#the_selector").val();
            let daterange = $("#daterange").val();
            var data = await $.ajax({
                url: path + 'dashboard/getalldatachart',
                data: {
                    workshop_id: workshop_id,
                    type: type,
                    selector: selector,
                    daterange: daterange
                },
                dataType: "JSON"
            });

            $(".page-loader-wrapper").hide();
            $("#target_accordion").empty();
            for (let i = 0; i < data.length; i++) {
                let target = data[i];
                let target_html = $('#target_template').html();
                let rendered = Mustache.render(target_html, target);
                $("#target_accordion").append(rendered)
                let area_list = target['area_list'];
                for (let j = 0; j < area_list.length; j++) {
                    let area = area_list[j];
                    area['target_id'] = target['id'];
                    let area_html = $('#area_template').html();
                    let rendered = Mustache.render(area_html, area);
                    $("#area_" + area['target_id'] + "_accordion").append(rendered);
                    let department_list = area['department_list'];
                    for (let k = 0; k < department_list.length; k++) {
                        let department = department_list[k];
                        department['target_id'] = target['id'];
                        let data = department['data'];
                        let department_html = $('#department_template').html();
                        let rendered = Mustache.render(department_html, department);
                        $("#area_" + area['id'] + area['target_id'] + "_body").append(rendered);
                        let options = {
                            // title: {
                            //     text: 'Solar Employment Growth by Sector, 2010-2016'
                            // },

                            // subtitle: {
                            //     text: 'Source: thesolarfoundation.com'
                            // },
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
                        $('#myChart' + department['id'] + department['target_id']).highcharts(options);

                    }
                }
            }
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

        $(".type_data.active").trigger("click");
    });
</script>