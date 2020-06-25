<div id="target_accordion"></div>

<script id="department_template" type="x-tmpl-mustache">
    <div>
    <div class='chart-container'>
    <div id="myChart<?= '{{department_id}}_{{target_id}}_{{object_id}}' ?>" class='myChart'></div>
    <canvas id="value_<?= '{{department_id}}_{{target_id}}_{{object_id}}' ?>"></canvas>
    </div>
    </div>
</script>
<!-- <a id="url" href="">download</a> -->
<script type="text/javascript">
    var results = <?= json_encode($results) ?>;
    var params = <?= json_encode($params) ?>;
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
    var count_chart = 0;
    var count_upload = 0;
    $(document).ready(function() {
        $(".page-loader-wrapper").show();
        $("#target_accordion").empty();

        for (let i = 0; i < results.length; i++) {
            let data = results[i]['data'];

            let area_id = results[i]['area_id'];
            let system_id = results[i]['system_id'];
            let target_id = results[i]['target_id'];
            let department_id = results[i]['department_id'];
            let object_id = params['object_id'];

            let obj = {
                target_id: target_id,
                department_id: department_id,
                object_id: object_id
            }
            let department_html = $('#department_template').html();
            let rendered = Mustache.render(department_html, obj);
            $("#target_accordion").append(rendered);
            count_chart++;
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
            $('#myChart' + department_id + "_" + target_id + "_" + object_id).highcharts(options);
            let chart_svg = $('#myChart' + department_id + "_" + target_id + "_" + object_id).highcharts().getSVG({
                exporting: {
                    sourceHeight: 300,
                    sourceWidth: 1000,
                }
            });
            canvg(document.getElementById('value_' + department_id + "_" + target_id + "_" + object_id), chart_svg)
            var canvas = document.getElementById('value_' + department_id + "_" + target_id + "_" + object_id);
            var image = canvas.toDataURL("image/png");


            if (image != "data:,") {
                if (params['type'] != "Custom") {
                    name = [object_id, target_id,
                        department_id,
                        params['type'],
                        params['selector']
                    ].join("_");
                    if (params['object_id'] == "3") {
                        name = [object_id, target_id,
                            area_id,
                            department_id,
                            params['type'],
                            params['selector']
                        ].join("_");
                    }

                } else {
                    name = [object_id, target_id,
                        department_id,
                        params['type'],
                        params['daterange'].split(" ").join("_").split("/").join("_")
                    ].join("_");
                    if (params['object_id'] == "3") {
                        name = [object_id, target_id,
                            area_id,
                            department_id,
                            params['type'],
                            params['daterange'].split(" ").join("_").split("/").join("_")
                        ].join("_");
                    }
                }
                $.ajax({
                    url: path + 'ajax/uploadchart',
                    type: "POST",
                    dataType: "JSON",
                    data: {
                        name: name,
                        image: image
                    },
                    success: function() {
                        count_upload++;
                        console.log(count_upload)
                        console.log(count_chart)
                        if (count_upload >= count_chart) {
                            location.href = path + "report/";
                        }
                    }
                })
            }
        }

    });
</script>