<div id="target_accordion">
    @foreach($results as $result)
    <div class="card">
        <div id="target_{{$result->id}}" class="card-header">
            <button type="button" data-toggle="collapse" data-target="#collapse{{$result->id}}" aria-expanded="true" class="text-left m-0 p-0 btn btn-link btn-block">
                {{$result->name}}
            </button>
        </div>
        <div data-parent="#target_accordion" id="collapse{{$result->id}}" aria-labelledby="target_{{$result->id}}" class="collapse">
            <div class="card-body">
                <div id="area_{{$result->id}}_accordion">
                    @foreach($result->area_list as $area)
                    <div class="card">
                        <div class="card">
                            <div id="area_{{$area->id}}{{$result->id}}" class="card-header">
                                <button type="button" data-toggle="collapse" data-target="#collapse{{$area->id}}{{$result->id}}" aria-expanded="true" class="text-left m-0 p-0 btn btn-link btn-block">
                                    {{$area->name}}
                                </button>
                            </div>
                            <div data-parent="#area_{{$result->id}}_accordion" id="collapse{{$area->id}}{{$result->id}}" aria-labelledby="area_{{$area->id}}{{$result->id}}" class="collapse">
                                <div class="card-body" id="area_{{$area->id}}{{$result->id}}_body">
                                    @foreach($area->department_list as $department)
                                    <div>
                                        <div class='chart-container'>
                                            <div id="myChart{{$department->id}}_{{$result->id}}" class='myChart'></div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
<!-- <a id="url" href="">download</a> -->
<script type="text/javascript">
    var results = <?= json_encode($charts) ?>;
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
    for (key in results) {
        let data = results[key];
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
        $('#myChart' + key).highcharts(options);

    }
</script>