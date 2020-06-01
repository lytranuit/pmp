<!-- ============================================================== -->
<!-- pageheader -->
<!-- ============================================================== -->
<div class="row clearfix">
    <div class="col-12">
        <section class="card card-fluid">
            <h5 class="card-header drag-handle">
                <!--                <a class="btn btn-success btn-sm" href="{{base_url()}}result/add">Thêm</a>-->
            </h5>
            <div class="card-body">
                <table id="quanlytin" class="table table-striped table-bordered table-hover" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Department</th>
                            <th>Report type</th>
                            <th>Time</th>
                            <th>User</th>
                            <th>Import time</th>
                            <th>File</th>
                            <th>Status</th>
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
                "url": path + "report/table",
                "dataType": "json",
                "type": "POST",
            },
            "columns": [{
                    "data": "id"
                },{
                    "data": "workshop_name"
                },
                {
                    "data": "type"
                },
                {
                    "data": "selector"
                },
                {
                    "data": "user_name"
                },
                {
                    "data": "date"
                },
                {
                    "data": "name"
                },
                {
                    "data": "status"
                }
            ]

        });
    });
</script>