<!-- ============================================================== -->
<!-- pageheader -->
<!-- ============================================================== -->
<div class="row clearfix">
    <div class="col-12">
        <section class="card card-fluid">
            <h5 class="card-header drag-handle">
                <a class="btn btn-success btn-sm" href="{{base_url()}}limit/add">Thêm</a>
            </h5>
            <div class="card-body">
                <table id="quanlytin" class="table table-striped table-bordered table-hover" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Khu vực</th>
                            <th>Phương pháp lấy mẫu</th>
                            <th>Tiêu chuẩn chấp nhận</th>
                            <th>Giới hạn cảnh báo</th>
                            <th>Giới hạn hành động</th>
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
    $(document).ready(function() {
        $('#quanlytin').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": path + "limit/table",
                "dataType": "json",
                "type": "POST",
            },
            "columns": [{
                    "data": "area_name"
                },
                {
                    "data": "target_name"
                },
                {
                    "data": "standard_limit"
                },
                {
                    "data": "alert_limit"
                },
                {
                    "data": "action_limit"
                },
                {
                    "data": "action"
                }
            ]

        });
    });
</script>