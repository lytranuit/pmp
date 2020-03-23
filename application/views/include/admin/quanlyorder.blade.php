
<!-- ============================================================== -->
<!-- pageheader -->
<!-- ============================================================== -->
<div class="row">
    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
        <div class="page-header">
            <h2 class="pageheader-title">Đơn hàng</h2>
            <p class="pageheader-text"></p>
            <div class="page-breadcrumb">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#" class="breadcrumb-link">Trang chủ</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Đơn hàng</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>
<div class="row clearfix">
    <div class="col-12">
        <section class="card card-fluid">
            <div class="card-header">
                <div class="row">
                    <div class="col-2">
                        <select class="form-control" id="status">
                            <option value="0">Search Status</option>
                            <option value="1">Mới đặt hàng</option>
                            <option value="2">Đã xác nhận</option>
                            <option value="3">Đang vận chuyển</option>
                            <option value="4">Đã thanh toán</option>
                            <option value="5">Ghi nợ</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body">

                <table id="quanlytin" class="table table-striped table-bordered table-hover" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Mã đơn hàng</th>
                            <th>Ngày đặt hàng</th>
                            <th>Tên khách hàng</th>
                            <th>Số điện thoại</th>
                            <th>Địa chỉ giao hàng</th>
                            <th>Status</th>
                            <th>Tổng số tiền</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </section>
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
                    d.extra_status = $('#status').val();
                }
            },
            "columns": [
                {"data": "id"},
                {"data": "order_date"},
                {"data": "customer_name"},
                {"data": "customer_phone"},
                {"data": "customer_address"},
                {"data": "status"},
                {"data": "total_amount"},
                {"data": "action"},
            ]

        });
        $('#amount,#paid_amount').inputmask("numeric", {
            radixPoint: ".",
            groupSeparator: ",",
            autoGroup: true,
            suffix: ' VND', //No Space, this will truncate the first character
            rightAlign: false,
        });
        $("#status").change(function () {
            table.ajax.reload();
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