$(document).ready(function () {
    $(document).off('click', "[data-type=confirm]").on('click', "[data-type=confirm]", function (e) {
        e.preventDefault();
        var title = $(this).attr("title");
        var r = confirm(title);
        if (r == true) {
            var href = $(this).attr("href");
            location.href = href;
        }
    });
    if ($("#doanhthu").length) {
        drawChart();
        $("#doanhthu [name=options]").change(function () {
            drawChart();
        });
    }
    if ($('#best_sale').length) {
        drawChartBestSale()
    }
})

async function drawChartBestSale() {
    var colorHash = new ColorHash();
    $("#chartjs_doughnut").remove();
    if ($(window).width() < 700) {
        var height = 400;
    } else {
        var height = 150;
    }
    $("#best_sale .card-body").first().append('<canvas id="chartjs_doughnut" width="400" height="' + height + '"></canvas>')
    var data = await $.ajax({
        dataType: "JSON",
        url: path + "ajax/bestsale",
        data: {}
    });
    var labels = data.map(function (item) {
        return item.name;
    });
    var count_sale = data.map(function (item) {
        return item.count_sale;
    })
    var colors = data.map(function (item) {
        return colorHash.hex(item.name);
    })
    var ctx = document.getElementById("chartjs_doughnut").getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                backgroundColor: colors,

                data: count_sale
            }]
        },
        options: {
            legend: {
                display: false,
                position: 'bottom',
                labels: {
                    fontColor: '#71748d',
                    fontFamily: 'Circular Std Book',
                    fontSize: 14,
                }
            },
        }
    });
}
async function drawChart() {
    $("#revenue").remove();
    if ($(window).width() < 700) {
        var height = 400;
    } else {
        var height = 150;
    }
    $("#doanhthu .card-body").first().append('<canvas id="revenue" width="400" height="' + height + '"></canvas>')
    var time_type_check = $("#doanhthu [name=options]:checked").val();
    var data = await $.ajax({
        dataType: "JSON",
        url: path + "ajax/datachart",
        data: { time_type: time_type_check }
    });
    var labels = data.map(function (item) {
        return item.time_type;
    });
    var data_amount = data.map(function (item) {
        return item.amount / 1000;
    })
    var ctx = document.getElementById('revenue').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Doanh thu',
                data: data_amount,
                backgroundColor: "rgba(89, 105, 255,0.5)",
                borderColor: "rgba(89, 105, 255,0.7)",
                borderWidth: 2

            }]
        },
        options: {
            legend: {
                display: true,
                position: 'bottom',

                labels: {
                    fontColor: '#71748d',
                    fontFamily: 'Circular Std Book',
                    fontSize: 14,
                }
            },
        }
    });
}
var fillForm = function (form, data) {
    $('input, select, textarea', form).not("[type=file]").each(function () {
        var type = $(this).attr('type');
        var name = $(this).attr('name');
        if (!name)
            return;
        name = name.replace("[]", "");
        var value = "";
        if ($(this).hasClass("input-tmp"))
            return
        if ($.type(data[name]) !== "undefined") {
            value = data[name];
        } else {
            return;
        }
        switch (type) {
            case 'checkbox':
                $(this).prop('checked', false);
                if (value == true || value == 'true' || value == 1) {
                    $(this).prop('checked', true);
                }
                break;
            case 'radio':
                $(this).removeAttr('checked', 'checked');
                var rdvalue = $(this).val();
                if (rdvalue == value) {
                    $(this).prop('checked', true);
                }
                break;
            default:
                $(this).val(value);
                break;
        }
        //            $('select', form).selectpicker('render');
    });
}