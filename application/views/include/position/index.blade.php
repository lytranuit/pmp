<!-- ============================================================== -->
<!-- pageheader -->
<!-- ============================================================== -->
<div class="row clearfix">
    <div class="col-12">
        <section class="card card-fluid">
            <h5 class="card-header drag-handle">
                <a class="btn btn-success btn-sm" href="{{base_url()}}position/add">Thêm</a>
            </h5>
            <div class="card-body">
                <table id="quanlytin" class="table table-striped table-bordered table-hover" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Mã vị trí</th>
                            <th>Tên vị trí</th>
                            <th>Phòng/Thiết bị/Nhân viên</th>
                            <th>Khu vực</th>
                            <th>Xưởng</th>
                            <th>Nhà máy</th>
                            <th>Tần suất</th>
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
                "url": path + "position/table",
                "dataType": "json",
                "type": "POST",
            },
            "columns": [{
                    "data": "string_id"
                },
                {
                    "data": "name"
                },
                {
                    "data": "department_name"
                },
                {
                    "data": "area_name"
                },
                {
                    "data": "workshop_name"
                },
                {
                    "data": "factory_name"
                },
                {
                    "data": "frequency_name"
                },
                {
                    "data": "action"
                }
            ]

        });
    });
</script>