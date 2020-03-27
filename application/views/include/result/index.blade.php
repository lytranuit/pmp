<!-- ============================================================== -->
<!-- pageheader -->
<!-- ============================================================== -->
<div class="row clearfix">
    <div class="col-12">
        <section class="card card-fluid">
            <h5 class="card-header drag-handle">
                <a class="btn btn-success btn-sm" href="{{base_url()}}result/add">Thêm</a>
            </h5>
            <div class="card-body">
                <table id="quanlytin" class="table table-striped table-bordered table-hover" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Tên Phòng</th>
                            <th>Phương pháp lấy mẫu</th>
                            <th>Tần suất</th>
                            <th>Mã vị trí</th>
                            <th>Vị trí</th>
                            <th>Ngày lấy mẫu</th>
                            <th>Giá trị</th>
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
        $('#quanlytin').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": path + "result/table",
                "dataType": "json",
                "type": "POST",
            },
            "columns": [{
                    "data": "department_name"
                },
                {
                    "data": "target_name"
                },
                {
                    "data": "frequency_name"
                },
                {
                    "data": "position_string_id"
                },
                {
                    "data": "position_name"
                },
                {
                    "data": "date"
                },
                {
                    "data": "value"
                },
                {
                    "data": "action"
                }
            ]

        });
    });
</script>