<div id="target_accordion"></div>

<script id="department_template" type="x-tmpl-mustache">
    <div>
    <h5 class="text-center"><?= '{{name}}' ?></h5>
    <div class='chart-container'>
    <div id="myChart<?= '{{id}}{{target_id}}' ?>" class='myChart'></div>
    <canvas id="value_<?= '{{id}}{{target_id}}' ?>"></canvas>
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
            let target = results[i];
            let area_list = target['area_list'];
            for (let j = 0; j < area_list.length; j++) {
                let area = area_list[j];
                area['target_id'] = target['id'];
                let department_list = area['department_list'];
                for (let k = 0; k < department_list.length; k++) {
                    let department = department_list[k];
                    department['target_id'] = target['id'];
                    let data = department['data'];
                    let department_html = $('#department_template').html();
                    let rendered = Mustache.render(department_html, department);
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
                    $('#myChart' + department['id'] + department['target_id']).highcharts(options);
                    let chart_svg = $('#myChart' + department['id'] + department['target_id']).highcharts().getSVG({
                        exporting: {
                            sourceHeight: 300,
                            sourceWidth: 1000,
                        }
                    });
                    canvg(document.getElementById('value_' + department['id'] + department['target_id']), chart_svg)
                    var canvas = document.getElementById('value_' + department['id'] + department['target_id']);
                    var image = canvas.toDataURL("image/png");

                    let target_id = department['target_id'];
                    let department_id = department['id'];
                    if (image != "data:,") {
                        if (params['type'] != "Custom") {
                            name = [target_id,
                                department_id,
                                params['type'],
                                params['selector']
                            ].join("_");
                        } else {
                            name = [target_id,
                                department_id,
                                params['type'],
                                params['daterange'].split(" ").join("_").split("/").join("_")
                            ].join("_");
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
            }
        }

    });
</script>