
<!-- ============================================================== -->
<!-- pageheader -->
<!-- ============================================================== -->
<div class="row">
    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
        <div class="page-header">
            <h2 class="pageheader-title">Tổng quan</h2>
            <p class="pageheader-text"></p>
            <div class="page-breadcrumb">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#" class="breadcrumb-link">Trang chủ</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Tổng quan</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-inline-block">
                    <h5 class="text-muted">Sản phẩm</h5>
                    <h2 class="mb-0">{{number_format($count_product,0,",",".")}}</h2>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-inline-block">
                    <h5 class="text-muted">Lợi nhuận</h5>
                    <h2 class="mb-0">{{number_format($amount_debt + $amount_sale,0,",",".")}}</h2>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-inline-block">
                    <h5 class="text-muted">Doanh thu trong ngày</h5>
                    <h2 class="mb-0">{{number_format($amount_sale_in_day,0,",",".")}} đ</h2>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-inline-block">
                    <h5 class="text-muted">Ghi nợ</h5>
                    <h2 class="mb-0">{{number_format($amount_debt_has_order,0,",",".")}} đ</h2>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="card" id="doanhthu">
            <div class="card-header">
                <h5>Doanh thu </h5>
                <div class="btn-group btn-group-toggle" data-toggle="buttons">
                    <label class="btn btn-light active">
                        <input type="radio" name="options" id="option1" value="Day" checked="">Ngày
                    </label>
                    <label class="btn btn-light">
                        <input type="radio" name="options" id="option2" value="DayOfWeek"> Thứ
                    </label>
                    <label class="btn btn-light">
                        <input type="radio" name="options" id="option3"value="Week"> Tuần
                    </label>
                    <label class="btn btn-light">
                        <input type="radio" name="options" id="option4"value="Month"> Tháng
                    </label>
                    <label class="btn btn-light">
                        <input type="radio" name="options" id="option5"value="Year"> Năm
                    </label>
                </div>
            </div>
            <div class="card-body">
                <canvas id="revenue" width="400" height="150"></canvas>
            </div>
            <div class="card-body border-top">
                <div class="row">
                    <div class="offset-xl-1 col-xl-3 col-lg-3 col-md-12 col-sm-12 col-12 p-3">
                        <h4> Tổng: {{number_format($amount_sale,0,",",".")}} đ</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="row">
    <div class="col-12 col-md-6">
        <div class="card" id="best_sale">
            <div class="card-header">
                <h5>Số lượng bán </h5>
            </div>
            <div class="card-body">
                <canvas id="chartjs_doughnut" width="400" height="150"></canvas>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Ghi nợ</h5>
            </div>
            <div class="card-body">
                <table id="quanlytin" class="table table-striped table-bordered table-hover" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Mã đơn hàng</th>
                            <th>Ngày đặt hàng</th>
                            <th>Tên khách hàng</th>
                            <th>Tổng số tiền</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var table = $('#quanlytin').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": path + "admin/tableorder",
                "dataType": "json",
                "type": "POST",
                "data": function (d) {
                    d.extra_status = 5;
                }
            },
            "columns": [
                {"data": "id"},
                {"data": "order_date"},
                {"data": "customer_name"},
                {"data": "total_amount"},
                {"data": "action"},
            ]

        });
        $(document).off('click', '.add_paid').on("click", '.add_paid', function (e) {
            e.preventDefault();
            var order_id = $(this).data("order_id");
            var r = confirm("Thanh toán!");
            if (r == true) {
                $.ajax({
                    "url": path + "admin/editorder/" + order_id,
                    "dataType": "json",
                    data: {status: 4, dangtin: true},
                    "type": "POST",
                    success: function () {
                        location.reload();
                    }, error: function () {
                        location.reload();
                    }
                })
            }
        });
    });
</script>




