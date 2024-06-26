<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header">
                {{lang("filter")}}
            </div>
            <div class="card-body" id="form-dang-tin">

                <div class="row">
                    <div class="col-md-3">
                        <b class="col-form-label text-sm-right">{{lang("factory")}}:<i class="text-danger">*</i></b>
                        <div class="pt-1">
                            <select class="form-control form-control-sm factory_id">
                                @foreach ($factory as $row)
                                <option value="{{$row->id}}">
                                    {{language_current() != "vietnamese" ? $row->name_en : $row->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <b class="col-form-label text-sm-right">{{lang("department")}}:<i class="text-danger">*</i></b>
                        <div class="pt-1">
                            <select class="form-control form-control-sm workshop_id">

                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <b class="col-form-label text-sm-right">{{lang("time")}}:<i class="text-danger">*</i></b>
                        <div class="pt-1">
                            <div class="btn-group btn-group-toggle" data-toggle="buttons">

                                <label class="btn btn-light type_data">
                                    <input type="radio" name="options" id="option4" value="Custom"> {{lang("custom")}}
                                </label>
                                <label class="btn btn-light type_data">
                                    <input type="radio" name="options" id="option4" value="Month"> {{lang("month")}}
                                </label>

                                <label class="btn btn-light type_data">
                                    <input type="radio" name="options" id="option4" value="Quarter"> {{lang("quarter")}}
                                </label>

                                <label class="btn btn-light type_data">
                                    <input type="radio" name="options" id="option4" value="HalfYear">
                                    {{lang("half_year")}}
                                </label>
                                <label class="btn btn-light type_data active">
                                    <input type="radio" name="options" id="option5" value="Year"> {{lang("year")}}
                                </label>
                                <label class="btn btn-light type_data">
                                    <input type="radio" name="options" id="option6" value="TwoYear">
                                    {{lang("two_year")}}
                                </label>
                            </div>
                            <select style="width: 200px;" class="form-control form-control-sm btn-group"
                                id="the_selector">
                            </select>
                            <input type="text" id="daterange" class="form-control form-control-sm btn-group"
                                style="width: 200px;" />

                        </div>
                    </div>
                </div>
                @if($object_id > 17)
                <div class="row">
                    <div class="col-md-3">
                        <b class="col-form-label text-sm-right">{{lang("system_water")}}:</b>
                        <div class="pt-1">
                            <select class="form-control form-control-sm system_id">

                            </select>
                        </div>
                    </div>

                </div>
                @else
                <div class="row">
                    <div class="col-md-3">
                        <b class="col-form-label text-sm-right">{{lang("area")}}:</b>
                        <div class="pt-1">
                            <select class="form-control form-control-sm area_id">

                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <b class="col-form-label text-sm-right">
                            @if($object_id == 3)
                            {{lang("employee")}}
                            @elseif($object_id == 10 || $object_id == 14 )
                            {{lang("equipment")}}
                            @elseif($object_id == 11 || $object_id == 15 )
                            {{lang("room")}}
                            @elseif($object_id == 16 || $object_id == 17 )
                            {{lang("room")}}
                            @endif
                        </b>
                        <div class="pt-1">
                            <select class="form-control form-control-sm department_id">

                            </select>
                        </div>
                    </div>

                </div>
                @endif
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                {{lang("trend_chart")}}
                @if($ion_auth_acl->has_permission("access_report"))
                <div style="margin-left:auto">
                    <div class="btn-group">
                        <button class="btn btn-primary btn-sm" id="export_report"><i class="fas fa-print"></i></button>
                    </div>

                </div>
                @endif
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
            <div id="collapse<?= '{{id}}' ?>" aria-labelledby="target_<?= '{{id}}' ?>" class="collapse">
                <div class="card-body">
                    <div class="chart-container">
                        <div class="chart-<?= '{{id}}' ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </script>

    <script id="fre_html" type="x-tmpl-mustache">
        <div class="card">
            <div id="fre_<?= '{{type_bc}}' ?>" class="card-header">
                <button type="button" data-toggle="collapse" data-target="#collapse_1_<?= '{{type_bc}}' ?>" aria-expanded="true" class="text-left m-0 p-0 btn btn-link btn-block">
                <?= '{{name}}' ?>
                </button>
            </div>
            <div id="collapse_1_<?= '{{type_bc}}' ?>" aria-labelledby="fre_<?= '{{type_bc}}' ?>" class="collapse">
                <div class="card-body">
                    <div class="chart-container">
                        <div id="chart_template_<?= '{{type_bc}}' ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </script>
    <script type="text/javascript">
        Highcharts.setOptions({
    	lang: {
      	numericSymbols: null
      }
    });
        var date_from = moment();
        var date_to = moment();
        var date_from_prev, date_from_to;
        var object_id = <?= $object_id ?>;
        $(document).ready(function() {
            $(".page-loader-wrapper").show();
            $(".department_id").change(async function() {

                $(".type_data.active").trigger("click");
                // drawChart();
            });
            $(".system_id").change(async function() {
                $(".type_data.active").trigger("click");
                // drawChartNuoc();
            });

            $("#the_selector").change(function() {
                load_data();
            })
            $(".area_id").change(async function() {
                $(".page-loader-wrapper").show();
                let value = $(this).val();
                let department = await $.ajax({
                    url: path + "dashboard/getdepartment/" + value,
                    dataType: "JSON"
                });
                let html = "";
                $.each(department, function(k, item) {
                    html += "<option value='" + item.id + "'>" + (language != "vietnamese" && object_id != 3 ? item.name_en : item.name) + "</option>";
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
                //console.log(object_id);
                if (object_id > 17) {
                    let system = await $.ajax({
                        url: path + "dashboard/getsystem/" + value,
                        dataType: "JSON"
                    });
                    let html = "";
                    $.each(system, function(k, item) {
                        html += "<option value='" + item.id + "'>" + (language != "vietnamese" ? item.name_en : item.name) + "</option>";
                    })
                    $(".system_id").html(html);
                    $(".system_id").trigger("change");
                    $(".page-loader-wrapper").hide();
                } else {
                    let area = await $.ajax({
                        url: path + "dashboard/getarea/" + value,
                        dataType: "JSON"
                    });
                    let html = "";
                    $.each(area, function(k, item) {
                        html += "<option value='" + item.id + "'>" + (language != "vietnamese" ? item.name_en : item.name) + "</option>";
                    })
                    $(".area_id").html(html);
                    if ($(".department_id").length)
                        $(".area_id").trigger("change");
                    else
                        $(".page-loader-wrapper").hide();
                }
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
                    html += "<option value='" + item.id + "'>" + (language != "vietnamese" ? item.name_en : item.name) + "</option>";
                })
                $(".workshop_id").html(html);
                $(".workshop_id").trigger("change");
                $(".page-loader-wrapper").hide();
                // /////LOAD SELECTOR
                // $(".type_data.active").trigger("click");
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
            $("#daterange").change(function() {
                load_data();
            });
            $(".type_data").click(async function() {
                let value = $("input", this).val();
                $("#daterange").addClass("d-none");
                $("#the_selector").addClass("d-none");
                if (value == "Custom") {
                    $("#daterange").removeClass("d-none");
                } else {

                    $("#the_selector").removeClass("d-none");
                    var department_id = $(".department_id").val();
                    var area_id = $(".area_id").val();
                    var workshop_id = $(".workshop_id").val();
                    var system_id = $(".system_id").val();
                    let data = await $.ajax({
                        url: path + 'dashboard/datedata',
                        data: {
                            type: value,
                            system_id: system_id,
                            workshop_id: workshop_id,
                            department_id: department_id,
                            area_id: area_id,
                        },
                        dataType: "JSON"
                    });
                    let html = "";
                    $.each(data, function(k, v) {
                        html += "<option value='" + v.value + "'>" + v.value + "</option>";
                    })
                    $("#the_selector").html(html);
                    load_data();
                    // $("#the_selector").trigger("change");
                }
            });
            async function drawChart() {

                $(".page-loader-wrapper").show();
                var department_id = $(".department_id").val();
                if (!(department_id > 0)) {

                    $(".page-loader-wrapper").hide();
                    return;
                }
                var area_id = $(".area_id").val();
                if (!(area_id > 0)) {
                    $(".page-loader-wrapper").hide();
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
                    // console.log(target);
                    if (target['parent']) {
                        target['name'] += " (" + target['parent']['name'] + ")";
                    }
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

                    $('.chart-' + target['id']).highcharts(options);
                }
                $(".page-loader-wrapper").hide();
            }

            async function drawChartNuoc() {

                $(".page-loader-wrapper").show();
                var system_id = $(".system_id").val();
                if (!(system_id > 0)) {
                    $(".page-loader-wrapper").hide();
                    return;
                }
                var workshop_id = $(".workshop_id").val();
                let type = $(".type_data.active input").val();
                let selector = $("#the_selector").val();
                let daterange = $("#daterange").val();
                $("#chart_template").empty();
                var all_fre = await $.ajax({
                    url: path + 'dashboard/chartdatanuoc',
                    data: {
                        system_id: system_id,
                        workshop_id: workshop_id,
                        type: type,
                        selector: selector,
                        daterange: daterange
                    },
                    dataType: "JSON"
                });
                // console.log(all_target);
                for (fre of all_fre) {
                    let type_bc = fre['type_bc'];
                    let fre_html = $('#fre_html').html();
                    let data_fre = fre['data'];
                    let rendered = Mustache.render(fre_html, fre);
                    $("#chart_template").append(rendered);
                    for (target of data_fre) {
                        let data = target['data'];
                        let target_html = $('#target_html').html();
                        if (target['parent']) {
                            target['name'] += " (" + target['parent']['name'] + ")";
                        }
                        let rendered = Mustache.render(target_html, target);
                        $("#chart_template_" + type_bc).append(rendered);
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
                        console.log(options);
                        $("#chart_template_" + type_bc + ' .chart-' + target['id']).highcharts(options);
                    }
                }
                $(".page-loader-wrapper").hide();
            }

            function load_data() {
                if (object_id > 17) {
                    drawChartNuoc();
                } else {
                    drawChart();
                }
            }
            ///////
            // $(".type_data.active").trigger("click");

            $(".factory_id").trigger("change");
        });
    </script>